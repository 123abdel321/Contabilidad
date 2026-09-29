<?php

namespace App\Pdf\Mappers;

use App\Models\Sistema\ConPagos;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use Illuminate\Support\Carbon;
use App\Pdf\Core\Column;
use App\Pdf\Core\Table;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;

class PagoPdfMapper
{
    use BegDocumentHelpersTrait;

    public static function map(ConPagos $pago, Empresa $empresa, string $claveUrl): array
    {
        $pago->load(['nit', 'detalles.cuenta', 'pagos.forma_pago']);

        // ============================================================
        // 1. CLIENTE (estructura para ClientBlock)
        // ============================================================
        $getNit = Nits::whereId($pago->id_nit)->with('ciudad')->first();
        $cliente = null;

        if ($getNit) {
            $cliente = (object)[
                'titulo' => 'CLIENTE',
                'nombre_cliente' => $getNit->nombre_completo,
                'datos_adicionales' => [
                    (object)[
                        'icono' => 'building',
                        'titulo' => $getNit->tipo_documento->nombre,
                        'valor' => $getNit->numero_documento
                    ],
                    (object)[
                        'icono' => 'location',
                        'titulo' => 'Dirección',
                        'valor' => ($getNit->direccion ?? '') . ($getNit->ciudad ? ' - ' . $getNit->ciudad->nombre_completo : '')
                    ],
                    (object)[
                        'icono' => 'phone',
                        'titulo' => 'Teléfono',
                        'valor' => $getNit->telefono_1 ?? ''
                    ],
                ]
            ];
        }

        // ============================================================
        // 2. INFORMACIÓN DEL DOCUMENTO (InfoBlock)
        // ============================================================
        $infoData = (object)[
            'titulo' => 'PAGO',
            'datos_adicionales' => [
                (object)[
                    'icono' => 'calendar',
                    'titulo' => 'Fecha',
                    'valor' => $pago->fecha_manual ?? ''
                ],
                (object)[
                    'icono' => 'file',
                    'titulo' => 'Comprobante',
                    'valor' => $pago->comprobante->nombre ?? ''
                ],
                (object)[
                    'icono' => 'tag',
                    'titulo' => 'Consecutivo',
                    'valor' => $pago->consecutivo ?? ''
                ],
                (object)[
                    'icono' => 'user',
                    'titulo' => 'Usuario',
                    'valor' => request()->user() ? request()->user()->username : 'Portafolio ERP'
                ],
            ]
        ];

        if ($pago->total_abono) {
            $infoData->datos_adicionales[] = (object)[
                'icono' => 'money',
                'titulo' => 'Total abono',
                'valor' => number_format($pago->total_abono)
            ];
        }

        if ($pago->total_anticipo) {
            $infoData->datos_adicionales[] = (object)[
                'icono' => 'money',
                'titulo' => 'Total anticipo',
                'valor' => number_format($pago->total_anticipo)
            ];
        }

        // ============================================================
        // 3. TABLA DE DETALLES (TableBlock)
        // ============================================================
        $columns = [
            Column::make('cuenta', 'CUENTA')->align('left'),
            Column::make('nombre', 'NOMBRE')->align('left'),
            Column::make('factura', 'FACTURA')->align('left'),
            Column::make('valor', 'VALOR')->align('right')->format('number'),
            Column::make('pago', 'PAGO')->align('right')->format('number'),
            Column::make('anticipo', 'ANTICIPO')->align('right')->format('number'),
            Column::make('saldo', 'SALDO')->align('right')->format('number'),
        ];

        $rows = [];
        foreach ($pago->detalles as $detalle) {
            $rows[] = [
                'cuenta' => $detalle->cuenta->cuenta ?? '',
                'nombre' => $detalle->cuenta->nombre ?? '',
                'factura' => $detalle->documento_referencia ?? '',
                'valor' => $detalle->total_saldo ?? 0,
                'pago' => $detalle->total_abono ?? 0,
                'anticipo' => $detalle->total_anticipo ?? 0,
                'saldo' => $detalle->nuevo_saldo ?? 0,
            ];
        }

        $tabla = Table::make()
            ->title('DETALLE DE PAGOS')
            ->columns($columns)
            ->rows($rows)
            ->toArray();

        // ============================================================
        // 4. RESUMEN (SummaryBlock)
        // ============================================================
        $resumen = [
            'titulo' => null,
        ];

        // ============================================================
        // 5. OBSERVACIONES (NotesBlock) - no hay en este caso
        // ============================================================
        $observacion = null;

        // ============================================================
        // 6. PAGOS (PaymentsBlock)
        // ============================================================
        $pagos = $pago->pagos;

        // ============================================================
        // 7. QR (QrBlock) - usando el mismo patrón que gastos
        // ============================================================
        $qrBase64 = null;
        if ($claveUrl) {
            $baseUrl = config('app.url');
            $url = "{$baseUrl}/documentos-generales-pdf?code={$claveUrl}";
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate($url);
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
        }

        // ============================================================
        // 8. DEVOLVER DATOS PARA BLOQUES
        // ============================================================
        return [
            'titulo' => $pago->comprobante->nombre ?? 'PAGO',
            'empresa' => $empresa,
            'cliente' => $cliente,
            'info_data' => $infoData,
            'consecutivo' => $pago->consecutivo,
            'fecha_manual' => $pago->fecha_manual,
            'tabla' => $tabla,
            'resumen' => $resumen,
            'observacion' => $observacion,
            'pagos' => $pagos,
            'qr_code' => $qrBase64,
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
            'monto_letras' => null,
        ];
    }
}