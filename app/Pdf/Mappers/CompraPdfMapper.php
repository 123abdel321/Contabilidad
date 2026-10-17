<?php

namespace App\Pdf\Mappers;

use App\Models\Sistema\FacCompras;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\VariablesEntorno;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Pdf\Core\Column;
use App\Pdf\Core\Table;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class CompraPdfMapper
{
    use BegDocumentHelpersTrait;

    public static function map(FacCompras $compra, Empresa $empresa, string $claveUrl): array
    {
        $compra->load(['proveedor', 'comprobante', 'detalles', 'pagos.forma_pago']);

        // ============================================================
        // 1. PROVEEDOR (reutiliza la estructura de ClientBlock)
        // ============================================================
        $proveedor = null;
        if ($compra->proveedor) {
            $proveedor = (object)[
                'titulo' => 'PROVEEDOR',
                'nombre_cliente' => $compra->proveedor->nombre_completo,
                'datos_adicionales' => [
                    (object)[
                        'icono' => 'building',
                        'titulo' => $compra->proveedor->tipo_documento->nombre,
                        'valor' => $compra->proveedor->numero_documento,
                    ],
                    (object)[
                        'icono' => 'location',
                        'titulo' => 'Dirección',
                        'valor' => ($compra->proveedor->direccion ?? '')
                            . ($compra->proveedor->ciudad ? ' - ' . $compra->proveedor->ciudad->nombre : ''),
                    ],
                    (object)[
                        'icono' => 'phone',
                        'titulo' => 'Teléfono',
                        'valor' => $compra->proveedor->telefono ?? '',
                    ],
                ],
            ];
        }

        // ============================================================
        // 2. INFO DEL DOCUMENTO  (sin DIAN)
        // ============================================================
        $infoData = (object)[
            'titulo' => 'COMPRA',
            'datos_adicionales' => [
                (object)[
                    'icono' => 'calendar',
                    'titulo' => 'Fecha',
                    'valor' => $compra->fecha_manual ?? '',
                ],
                (object)[
                    'icono' => 'user',
                    'titulo' => 'Usuario',
                    'valor' => request()->user() ? request()->user()->username : 'Portafolio ERP',
                ],
            ],
        ];

        // ============================================================
        // 3. TABLA DE PRODUCTOS
        // ============================================================
        $columns = [
            Column::make('nombre', 'NOMBRE')->align('left'),
            Column::make('cantidad', 'CANTIDAD')->align('center'),
            Column::make('costo', 'VALOR')->align('right')->format('number'),
            Column::make('subtotal', 'SUBTOTAL')->align('right')->format('number'),
            Column::make('descuento', 'DESCUENTO')->align('right')->format('number'),
            Column::make('iva', 'IVA')->align('right')->format('number'),
            Column::make('total', 'TOTAL')->align('right')->format('number'),
        ];

        $rows = [];
        foreach ($compra->detalles as $detalle) {
            $cantidad = rtrim(rtrim(number_format($detalle->cantidad, 5, '.', ''), '0'), '.');
            $rows[] = [
                'nombre' => $detalle->descripcion . ($detalle->observacion ? ' ' . $detalle->observacion : ''),
                'cantidad' => $cantidad ?: '0',
                'costo' => $detalle->costo ?? 0,
                'subtotal' => $detalle->subtotal ?? 0,
                'descuento' => $detalle->descuento_valor ?? 0,
                'iva' => $detalle->iva_valor ?? 0,
                'total' => $detalle->total ?? 0,
            ];
        }

        $tabla = Table::make()
            ->title('DETALLE DE PRODUCTOS')
            ->columns($columns)
            ->rows($rows)
            ->toArray();

        // ============================================================
        // 4. RESUMEN  (sin propina, sin DIAN)
        // ============================================================
        $resumen = [
            'titulo' => 'TOTALES',
            'filas' => [
                ['label' => 'SUBTOTAL', 'value' => $compra->subtotal, 'formatter' => 'number'],
                ['label' => 'IVA', 'value' => $compra->total_iva, 'formatter' => 'number'],
                [
                    'label' => 'RETE FUENTE ' . ($compra->porcentaje_rete_fuente ?? 0) . '%',
                    'value' => $compra->total_rete_fuente ?? 0,
                    'formatter' => 'number',
                ],
                [
                    'label' => 'TOTAL',
                    'value' => $compra->total_factura,
                    'formatter' => 'number',
                    'class' => 'resumen-total',
                ],
            ],
        ];

        // ============================================================
        // 5. OBSERVACIONES
        // ============================================================
        $observacionGeneral = VariablesEntorno::where('nombre', 'observacion_compra')->first();
        $observacionGeneral = $observacionGeneral ? $observacionGeneral->valor : null;

        $observacionTexto = $compra->observacion;
        if ($observacionGeneral) {
            $observacionTexto = ($observacionTexto ? $observacionTexto . "\n" : '') . $observacionGeneral;
        }

        // ============================================================
        // 6. PAGOS
        // ============================================================
        $pagos = $compra->pagos;
        // ============================================================
        // 7. QR ERP (validación Portafolio ERP, mismo endpoint polimórfico)
        // ============================================================
        $qrErp = null;
        if ($claveUrl) {
            $baseUrl = config('app.url');
            $urlValidar = "{$baseUrl}/documentos-generales-pdf?code={$claveUrl}";
            $svgErp = QrCode::format('svg')->size(300)->generate($urlValidar);
            $qrErp = 'data:image/svg+xml;base64,' . base64_encode($svgErp);
        }

        // ============================================================
        // 8. RETURN
        // ============================================================
        return [
            'titulo'          => $compra->comprobante->nombre ?? 'COMPRA',
            'empresa'         => $empresa,
            'cliente'         => $proveedor,       // misma clave que usa ClientBlock
            'info_data'       => $infoData,
            'consecutivo'     => $compra->consecutivo,
            'fecha_manual'    => $compra->fecha_manual,
            'tabla'           => $tabla,
            'resumen'         => $resumen,
            'observacion'     => $observacionTexto,
            'pagos'           => $pagos,           // compras no tiene pagos
            'qr_erp'          => $qrErp,
            'qr_dian'         => null,             // no aplica
            'qr_info_dian'    => null,             // no aplica
            'fecha_pdf'       => Carbon::now()->format('Y-m-d H:i:s'),
            'monto_letras'    => null,
        ];
    }
}