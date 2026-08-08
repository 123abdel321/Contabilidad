<?php

namespace App\Pdf\Mappers;

use App\Models\Sistema\ConRecibos;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Helpers\Extracto;
use App\Pdf\Core\Column;
use App\Pdf\Core\Table;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class ReciboPdfMapper
{
    use BegDocumentHelpersTrait;

    public static function map(ConRecibos $recibo, Empresa $empresa, string $claveUrl): array
    {
        $recibo->load(['nit', 'detalles.cuenta', 'pagos.forma_pago', 'documentos']);

        $nit = self::buildNit($recibo);
        $saldo = 0;
        $saldoAnterior = 0;

        if ($nit) {
            // Calcular saldo actual (igual que en el original)
            $extractos = (new Extracto(
                $nit->id,
                3,
                null,
                $recibo->documentos[0]->fecha_manual
            ))->actual()->get();

            if (isset($extractos)) {
                foreach ($extractos as $extracto) {
                    $saldo += floatval($extracto->saldo);
                }
            }

            // Calcular saldo anterior
            $fechaAnterior = Carbon::parse($recibo->documentos[0]->fecha_manual)->subMinute()->format('Y-m-d H:i:s');
            $extractoAnterior = (new Extracto(
                $nit->id,
                [3, 8],
                null,
                $fechaAnterior
            ))->actual()->get();

            $saldosPorCuenta = $extractoAnterior
                ->filter(fn($item) => $item->id_tipo_cuenta == 8)
                ->sortBy('fecha_manual')
                ->keyBy('id_cuenta')
                ->map(fn($item) => floatval($item->saldo));

            $saldoAnterior = 0;
            if (isset($extractoAnterior)) {
                foreach ($extractoAnterior as $anterior) {
                    if ($anterior->id_tipo_cuenta == 8 || $anterior->id_tipo_cuenta == 4) {
                        $saldoAnterior -= floatval($anterior->saldo);
                    } else {
                        $saldoAnterior += floatval($anterior->saldo);
                    }
                }
            }

            // Asignar nuevo saldo a cada detalle
            foreach ($recibo->detalles as $detalle) {
                $detalle->nuevo_saldo = $saldosPorCuenta->get($detalle->id_cuenta, 0) + $detalle->total_anticipo;
            }

            // Anticipos
            $anticipos = (new Extracto(
                $nit->id,
                [4, 8]
            ))->actual()->get();
            $totalAnticipo = $anticipos->sum('saldo');
        }

        // Construir datos para bloques
        $cliente = self::buildCliente($nit);
        $infoData = self::buildInfoData($recibo);
        $tabla = self::buildTable($recibo);
        $resumen = self::buildSummary($recibo, $saldoAnterior, $saldo, $totalAnticipo ?? 0);
        $pagos = $recibo->pagos;
        $qrBase64 = self::generateQr($claveUrl);

        return [
            'titulo' => $recibo->comprobante->nombre ?? 'RECIBO',
            'empresa' => $empresa,
            'cliente' => $cliente,
            'info_data' => $infoData,
            'consecutivo' => $recibo->consecutivo,
            'fecha_manual' => $recibo->fecha_manual,
            'tabla' => $tabla,
            'resumen' => $resumen,
            'observacion' => null,
            'pagos' => $pagos,
            'qr_code' => $qrBase64,
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
            'monto_letras' => (new self())->numeroALetras($recibo->total_abono),
        ];
    }

    private static function buildNit(ConRecibos $recibo): ?object
    {
        $getNit = Nits::whereId($recibo->id_nit)->with('ciudad')->first();
        if (!$getNit) return null;

        return (object)[
            'id' => $getNit->id,
            'nombre_nit' => $getNit->nombre_completo,
            'telefono' => $getNit->telefono_1,
            'email' => $getNit->email,
            'direccion' => $getNit->direccion,
            'tipo_documento' => $getNit->tipo_documento->nombre,
            'numero_documento' => $getNit->numero_documento,
            'ciudad' => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
            'apartamentos' => $getNit->apartamentos ?? '',
        ];
    }

    private static function buildCliente(?object $nit): ?object
    {
        if (!$nit) return null;

        return (object)[
            'titulo' => 'CLIENTE',
            'nombre_cliente' => $nit->nombre_nit,
            'datos_adicionales' => [
                (object)[
                    'icono' => 'building',
                    'titulo' => $nit->tipo_documento,
                    'valor' => $nit->numero_documento
                ],
                (object)[
                    'icono' => 'location',
                    'titulo' => 'Dirección',
                    'valor' => $nit->direccion . ($nit->ciudad ? ' - ' . $nit->ciudad : '')
                ],
                (object)[
                    'icono' => 'phone',
                    'titulo' => 'Teléfono',
                    'valor' => $nit->telefono
                ],
            ]
        ];
    }

    private static function buildInfoData(ConRecibos $recibo): object
    {
        return (object)[
            'titulo' => 'INFORMACIÓN DEL RECIBO',
            'datos_adicionales' => [
                (object)[
                    'icono' => 'file',
                    'titulo' => 'Comprobante',
                    'valor' => $recibo->comprobante->nombre
                ],
                (object)[
                    'icono' => 'tag',
                    'titulo' => 'Consecutivo',
                    'valor' => $recibo->consecutivo
                ],
                (object)[
                    'icono' => 'calendar',
                    'titulo' => 'Fecha',
                    'valor' => $recibo->fecha_manual
                ],
                (object)[
                    'icono' => 'user',
                    'titulo' => 'Usuario',
                    'valor' => request()->user() ? request()->user()->username : 'Portafolio ERP'
                ],
            ]
        ];
    }

    private static function buildTable(ConRecibos $recibo): array
    {
        $columns = [
            Column::make('cuenta', 'CUENTA')->align('left'),
            Column::make('nombre', 'NOMBRE')->align('left'),
            Column::make('factura', 'FACTURA')->align('left'),
            Column::make('valor', 'VALOR')->align('right')->format('number'),
            Column::make('pago', 'PAGO')->align('right')->format('number'),
            Column::make('saldo', 'SALDO')->align('right')->format('number'),
            Column::make('concepto', 'CONCEPTO')->align('left'),
        ];

        $rows = [];
        foreach ($recibo->detalles as $detalle) {
            $rows[] = [
                'cuenta' => $detalle->cuenta->cuenta ?? '',
                'nombre' => $detalle->cuenta->nombre ?? '',
                'factura' => $detalle->documento_referencia ?? '',
                'valor' => $detalle->total_saldo ?? 0,
                'pago' => $detalle->total_anticipo ? $detalle->total_anticipo : $detalle->total_abono,
                'saldo' => $detalle->nuevo_saldo ?? 0,
                'concepto' => $detalle->concepto ?? '',
            ];
        }

        return Table::make()
            ->title('DETALLE DE RECIBO')
            ->columns($columns)
            ->rows($rows)
            ->toArray();
    }

    private static function buildSummary(ConRecibos $recibo, float $saldoAnterior, float $saldo, float $anticipo): array
    {
        $filas = [
            ['label' => 'Saldo anterior', 'value' => $saldoAnterior, 'formatter' => 'number'],
            ['label' => 'Total recibido', 'value' => $recibo->pagos->sum('valor'), 'formatter' => 'number'],
        ];

        if ($saldo > 0) {
            $filas[] = ['label' => 'Saldo pendiente', 'value' => $saldo, 'formatter' => 'number'];
        }

        if ($anticipo > 0) {
            $filas[] = ['label' => 'Anticipo disponible', 'value' => $anticipo, 'formatter' => 'number'];
        }

        return [
            'titulo' => 'RESUMEN DE PAGO',
            'filas' => $filas,
        ];
    }

    private static function generateQr(string $claveUrl): string
    {
        $baseUrl = config('app.url');
        $url = "{$baseUrl}/documentos-generales-pdf?code={$claveUrl}";
        $svg = QrCode::format('svg')->size(300)->generate($url);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}