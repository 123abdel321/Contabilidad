<?php

namespace App\Pdf\Mappers;

use App\Models\Sistema\FacVentas;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\VariablesEntorno;
use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Pdf\Core\Column;
use App\Pdf\Core\Table;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class VentaPdfMapper
{
    use BegDocumentHelpersTrait;

    public static function map(FacVentas $venta, Empresa $empresa, string $claveUrl): array
    {
        $venta->load(['resolucion', 'cliente', 'comprobante', 'detalles', 'pagos.forma_pago']);

        // ============================================================
        // 1. CLIENTE
        // ============================================================
        $cliente = null;
        if ($venta->cliente) {
            $cliente = (object)[
                'titulo' => 'CLIENTE',
                'nombre_cliente' => $venta->cliente->nombre_completo,
                'datos_adicionales' => [
                    (object)[
                        'icono' => 'building',
                        'titulo' => $venta->cliente->tipo_documento->nombre,
                        'valor' => $venta->cliente->numero_documento
                    ],
                    (object)[
                        'icono' => 'location',
                        'titulo' => 'Dirección',
                        'valor' => ($venta->cliente->direccion ?? '') . ($venta->cliente->ciudad ? ' - ' . $venta->cliente->ciudad->nombre : '')
                    ],
                    (object)[
                        'icono' => 'phone',
                        'titulo' => 'Teléfono',
                        'valor' => $venta->cliente->telefono ?? ''
                    ],
                ]
            ];
        }

        // ============================================================
        // 2. INFORMACIÓN DEL DOCUMENTO
        // ============================================================
        $infoData = (object)[
            'titulo' => 'FACTURA',
            'datos_adicionales' => [
                (object)[
                    'icono' => 'calendar',
                    'titulo' => 'Fecha',
                    'valor' => $venta->fecha_manual ?? ''
                ],
                // (object)[
                //     'icono' => 'file',
                //     'titulo' => 'Comprobante',
                //     'valor' => $venta->comprobante->nombre ?? ''
                // ],
                // (object)[
                //     'icono' => 'tag',
                //     'titulo' => 'Consecutivo',
                //     'valor' => $venta->consecutivo ?? ''
                // ],
                (object)[
                    'icono' => 'user',
                    'titulo' => 'Usuario',
                    'valor' => request()->user() ? request()->user()->username : 'Portafolio ERP'
                ],
            ]
        ];

        if ($venta->fe_codigo_identificador && $venta->resolucion) {
            $infoData->datos_adicionales[] = (object)[
                'icono' => 'check',
                'titulo' => 'Validación DIAN',
                'valor' => $venta->fecha_validacion ?? ''
            ];
            $infoData->datos_adicionales[] = (object)[
                'icono' => 'clock',
                'titulo' => 'Vencimiento',
                'valor' => $venta->fecha_vencimiento ?? ''
            ];
        }

        // ============================================================
        // 3. TABLA DE PRODUCTOS
        // ============================================================
        $columns = [
            Column::make('nombre', 'NOMBRE')->align('left'),
            Column::make('cantidad', 'CANTIDAD')->align('center'),
            Column::make('costo', 'COSTO')->align('right')->format('number'),
            Column::make('subtotal', 'SUBTOTAL')->align('right')->format('number'),
            Column::make('descuento', 'DESCUENTO')->align('right')->format('number'),
            Column::make('iva', 'IVA')->align('right')->format('number'),
            Column::make('total', 'TOTAL')->align('right')->format('number'),
        ];

        $rows = [];
        foreach ($venta->detalles as $detalle) {
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
        // 4. RESUMEN
        // ============================================================
        $resumen = [
            'titulo' => 'TOTALES',
            'filas' => [
                ['label' => 'SUBTOTAL', 'value' => $venta->subtotal, 'formatter' => 'number'],
                ['label' => 'IVA', 'value' => $venta->total_iva, 'formatter' => 'number'],
                ['label' => 'RETE FUENTE ' . ($venta->porcentaje_rete_fuente ?? 0) . '%', 'value' => $venta->total_rete_fuente ?? 0, 'formatter' => 'number'],
                ['label' => 'TOTAL', 'value' => $venta->total_factura, 'formatter' => 'number', 'class' => 'resumen-total'],
            ]
        ];

        // ============================================================
        // 5. OBSERVACIONES
        // ============================================================
        $observacionGeneral = VariablesEntorno::where('nombre', 'observacion_venta')->first();
        $observacionGeneral = $observacionGeneral ? $observacionGeneral->valor : null;

        $observacionTexto = $venta->observacion;
        if ($observacionGeneral) {
            $observacionTexto = ($observacionTexto ? $observacionTexto . "\n" : '') . $observacionGeneral;
        }

        // ============================================================
        // 6. PAGOS
        // ============================================================
        $pagos = $venta->pagos;

        // ============================================================
        // 7. QR ERP (para validar en Portafolio ERP)
        // ============================================================
        $qrErp = null;
        if ($claveUrl) {
            $baseUrl = config('app.url');
            $urlValidar = "{$baseUrl}/documentos-generales-pdf?code={$claveUrl}";
            $svgErp = QrCode::format('svg')->size(300)->generate($urlValidar);
            $qrErp = 'data:image/svg+xml;base64,' . base64_encode($svgErp);
        }

        // ============================================================
        // 8. QR DIAN (facturación electrónica)
        // ============================================================
        $qrDian = null;
        $qrInfoDian = null;
        if ($venta->fe_codigo_qr) {
            $svgDian = QrCode::format('svg')->size(300)->generate($venta->fe_codigo_qr);
            $qrDian = 'data:image/svg+xml;base64,' . base64_encode($svgDian);

            if ($venta->fe_codigo_identificador && $venta->resolucion) {
                $qrInfoDian = (object)[
                    'resolucion' => "AUTORIZACION {$venta->resolucion->numero_resolucion} DE {$venta->resolucion->fecha} DE {$venta->resolucion->prefijo}{$venta->resolucion->consecutivo_desde} HASTA {$venta->resolucion->prefijo}{$venta->resolucion->consecutivo_hasta} VIGENCIA {$venta->resolucion->vigencia} MESES",
                    'cufe' => $venta->fe_codigo_identificador,
                ];
            }
        }

        // ============================================================
        // 9. DEVOLVER DATOS
        // ============================================================
        return [
            'titulo' => $venta->comprobante->nombre ?? 'FACTURA',
            'empresa' => $empresa,
            'cliente' => $cliente,
            'info_data' => $infoData,
            'consecutivo' => $venta->consecutivo,
            'fecha_manual' => $venta->fecha_manual,
            'tabla' => $tabla,
            'resumen' => $resumen,
            'observacion' => $observacionTexto,
            'pagos' => $pagos,
            'qr_erp' => $qrErp,
            'qr_dian' => $qrDian,
            'qr_info_dian' => $qrInfoDian,  // <--- clave CORRECTA
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
            'monto_letras' => null,
        ];
    }
}