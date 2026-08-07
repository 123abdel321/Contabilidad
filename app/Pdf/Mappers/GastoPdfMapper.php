<?php

namespace App\Pdf\Mappers;

use App\Models\Sistema\ConGastos;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Pdf\Core\Column;
use App\Pdf\Core\Table;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class GastoPdfMapper
{
    use BegDocumentHelpersTrait;

    public static function map(ConGastos $gasto, Empresa $empresa, string $claveUrl): array
    {
        $gasto->load(['cecos', 'proveedor', 'comprobante', 'detalles.concepto', 'pagos.forma_pago']);

        // Construir objeto cliente con la estructura que espera la vista
        $cliente = self::buildCliente($gasto);
        $infoData = self::buildInfoData($gasto);
        $tabla = self::buildTable($gasto);
        $resumen = self::buildSummary($gasto);
        $qrBase64 = self::generateQr($claveUrl);
        $observacion = $gasto->detalles->first()?->observacion ?? '';

        return [
            'titulo' => 'GASTOS',
            'empresa' => $empresa,
            'cliente' => $cliente,
            'info_data' => $infoData,
            'consecutivo' => $gasto->consecutivo,
            'fecha_manual' => $gasto->fecha_manual,
            'tabla' => $tabla,
            'resumen' => $resumen,
            'observacion' => $observacion,
            'pagos' => $gasto->pagos,
            'qr_code' => $qrBase64,
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
            'monto_letras' => (new self())->numeroALetras($gasto->total_gasto),
        ];
    }

    private static function buildCliente(ConGastos $gasto): ?object
    {
        $proveedor = Nits::whereId($gasto->id_proveedor)->with('ciudad')->first();
        if (!$proveedor) return null;

        return (object)[
            'titulo' => 'PROVEEDOR',
            'nombre_cliente' => $proveedor->nombre_completo,
            'datos_adicionales' => [
                (object)[
                    'icono' => 'building',
                    'titulo' => $proveedor->tipo_documento->nombre == 'Cédula de ciudadanía' ? 'Cédula' : $proveedor->tipo_documento->nombre,
                    'valor' => $proveedor->numero_documento
                ],
                (object)[
                    'icono' => 'location',
                    'titulo' => 'Dirección',
                    'valor' => $proveedor->direccion
                ],
                (object)[
                    'icono' => 'phone',
                    'titulo' => 'Teléfono',
                    'valor' => $proveedor->telefono_1
                ],
                (object)[
                    'icono' => 'mail',
                    'titulo' => 'Email',
                    'valor' => $proveedor->email
                ],
            ]
        ];
    }

    private static function buildInfoData(ConGastos $gasto): object
    {
        return (object)[
            'titulo' => 'INFORMACIÓN DEL GASTO',
            'datos_adicionales' => [
                (object)[
                    'icono' => 'box',
                    'titulo' => 'Centro de costos',
                    'valor' => "{$gasto->cecos->codigo} - {$gasto->cecos->nombre}"
                ],
                (object)[
                    'icono' => 'file',
                    'titulo' => 'Documento referencia',
                    'valor' => $gasto->documento_referencia
                ],
                (object)[
                    'icono' => 'ticket',
                    'titulo' => 'Comprobante',
                    'valor' => "{$gasto->comprobante->codigo} - {$gasto->comprobante->nombre}"
                ],
                (object)[
                    'icono' => 'user',
                    'titulo' => 'Usuario',
                    'valor' => request()->user() ? request()->user()->username : 'Portafolio ERP'
                ],
                (object)[
                    'icono' => 'tag',
                    'titulo' => 'Tipo de gasto',
                    'valor' => 'Gasto operacional'
                ],
            ]
        ];
    }

    private static function buildTable(ConGastos $gasto): array
    {
        $columns = [
            Column::make('concepto', 'CONCEPTO')->align('left'),
            Column::make('cuenta', 'CUENTA')->align('left'),
            Column::make('base', 'SUBTOTAL')->align('right')->format('number'),
            Column::make('iva', 'IVA')->align('right')->format('number'),
            Column::make('rete_fuente', 'RETENCIÓN')->align('right')->format('number'),
            Column::make('rete_ica', 'RETEICA')->align('right')->format('number'),
            Column::make('total', 'TOTAL')->align('right')->format('number'),
        ];

        $rows = [];
        foreach ($gasto->detalles as $detalle) {
            $rows[] = [
                'concepto' => ($detalle->concepto?->codigo ?? '') . ' - ' . ($detalle->concepto?->nombre ?? ''),
                'cuenta' => ($detalle->concepto?->cuenta_gasto?->cuenta ?? '') . ' - ' . ($detalle->concepto?->cuenta_gasto?->nombre ?? ''),
                'base' => $detalle->subtotal_neto ?? 0,
                'iva' => $detalle->iva_valor ?? 0,
                'rete_fuente' => $detalle->rete_fuente_valor ?? 0,
                'rete_ica' => $detalle->rete_ica_valor ?? 0,
                'total' => $detalle->total ?? 0,
            ];
        }

        return Table::make()
            ->title('DETALLE DE CONCEPTOS')
            ->columns($columns)
            ->rows($rows)
            ->toArray();
    }

    private static function buildSummary(ConGastos $gasto): array
    {
        $totales = [
            'subtotal' => $gasto->detalles->sum('subtotal'),
            'iva' => $gasto->detalles->sum('iva_valor'),
            'rete_fuente' => $gasto->detalles->sum('rete_fuente_valor'),
            'rete_ica' => $gasto->detalles->sum('rete_ica_valor'),
            'total' => $gasto->detalles->sum('total'),
        ];

        return [
            'titulo' => 'RESUMEN FINANCIERO',
            'filas' => [
                ['label' => 'SUBTOTAL', 'value' => $totales['subtotal'], 'formatter' => 'number'],
                ['label' => 'IVA', 'value' => $totales['iva'], 'formatter' => 'number'],
                ['label' => 'RETENCIÓN EN LA FUENTE', 'value' => $totales['rete_fuente'], 'formatter' => 'number'],
                ['label' => 'RETEICA', 'value' => $totales['rete_ica'], 'formatter' => 'number'],
                ['label' => 'TOTAL A PAGAR', 'value' => $totales['total'], 'formatter' => 'number', 'class' => 'resumen-total'],
            ]
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