<?php

namespace App\Pdf\Mappers;

use App\Models\Empresas\Empresa;
use App\Models\Sistema\Nomina\NomPeriodoPagos;
use App\Models\Sistema\Nomina\NomPeriodoPagoDetalles;
use App\Models\Sistema\VariablesEntorno;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Pdf\Core\Column;
use App\Pdf\Core\Table;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class NominaPdfMapper
{
    use BegDocumentHelpersTrait;

    const BUCKET = 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/';

    public static function map(NomPeriodoPagos $periodo, Empresa $empresa, ?string $claveUrl = null): array
    {
        $periodo->loadMissing(['empleado.tipo_documento', 'contrato', 'detalles.concepto']);

        $nit      = $periodo->empleado;
        $contrato = $periodo->contrato;

        // Empleado
        $nombre    = trim($nit->nombre_completo ?? '');
        $iniciales = collect(explode(' ', $nombre))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');

        $empleado = [
            'nombre'    => $nombre,
            'iniciales' => $iniciales ?: '--',
            'foto'      => $nit->logo_nit ? self::BUCKET . ltrim($nit->logo_nit, '/') : null,
            'documento' => trim(($nit->tipo_documento->nombre ?? 'Doc.') . ' ' . $nit->numero_documento
                            . ($nit->digito_verificacion ? '-' . $nit->digito_verificacion : '')),
            'email'     => $nit->email,
            'telefono'  => $nit->telefono_1,
        ];

        $contratoData = [
            'salario' => $contrato->salario ?? null,
            'inicio'  => $contrato->fecha_inicio_contrato ?? null,
        ];

        // Detalles
        $fmtNum = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, ',', '.'), '0'), ',');
        $devengados  = [];
        $deducciones = [];

        foreach ($periodo->detalles as $d) {
            $valor = (float) $d->valor;
            $cantidad = '';
            if ($d->unidades) {
                if ((int) $d->tipo_unidad === NomPeriodoPagoDetalles::TIPO_UNIDAD_HORAS) $cantidad = $fmtNum($d->unidades) . ' h';
                elseif ((int) $d->tipo_unidad === NomPeriodoPagoDetalles::TIPO_UNIDAD_DIAS) $cantidad = $fmtNum($d->unidades) . ' d';
            }
            $fila = [
                'concepto'    => trim(($d->concepto->codigo ?? '') . ' - ' . ($d->concepto->nombre ?? ''), ' -'),
                'cantidad'    => $cantidad,
                'porcentaje'  => $d->porcentaje ? $fmtNum($d->porcentaje) . '%' : '',
                'observacion' => $d->observacion,
                'valor'       => abs($valor),
            ];
            if ($valor >= 0) $devengados[]  = $fila;
            else             $deducciones[] = $fila;
        }

        $totDev = collect($devengados)->sum('valor');
        $totDed = collect($deducciones)->sum('valor');
        $neto   = $totDev - $totDed;
        $pctDed = $totDev > 0 ? round($totDed / $totDev * 100, 1) : 0;

        // Observación
        $observacion = 'Este documento es un comprobante de pago de nómina. '
            . 'Cualquier inquietud comuníquese con el área de nómina.';
        $general = VariablesEntorno::where('nombre', 'observacion_nomina')->first();
        if ($general && $general->valor) $observacion .= "\n" . $general->valor;

        $estados = [
            NomPeriodoPagos::ESTADO_PENDIENTE => 'Pendiente',
            NomPeriodoPagos::ESTADO_CAUSADO   => 'Causado',
            NomPeriodoPagos::ESTADO_PAGADO    => 'Pagado',
        ];

        // QR
        $qrErp = null;
        if ($claveUrl) {
            $urlValidar = config('app.url') . "/documentos-generales-pdf?code={$claveUrl}";
            $svgErp     = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate($urlValidar);
            $qrErp      = 'data:image/svg+xml;base64,' . base64_encode($svgErp);
        }

        return [
            'bucket'      => self::BUCKET,
            'empresa'     => $empresa,
            'empleado'    => $empleado,
            'contrato'    => $contratoData,
            'periodo'     => [
                'inicio' => $periodo->fecha_inicio_periodo_formatted,
                'fin'    => $periodo->fecha_fin_periodo_formatted,
                'estado' => $estados[$periodo->estado] ?? '',
            ],
            'devengados'  => $devengados,
            'deducciones' => $deducciones,
            'totales'     => [
                'devengado' => $totDev,
                'deduccion' => $totDed,
                'neto'      => $neto,
                'pct_ded'   => $pctDed,
            ],
            'observacion' => $observacion,
            'qr_erp'      => $qrErp,
            'fecha_pdf'   => Carbon::now()->format('d/m/Y H:i'),
        ];
    }
}