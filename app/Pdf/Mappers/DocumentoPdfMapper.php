<?php

namespace App\Pdf\Mappers;

use App\Models\Sistema\FacDocumentos;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Ciudades;
use Illuminate\Support\Carbon;
use App\Helpers\Extracto;
use App\Pdf\Core\Column;
use App\Pdf\Core\Table;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentoPdfMapper
{
    use BegDocumentHelpersTrait;

    public static function map(FacDocumentos $factura, Empresa $empresa, string $claveUrl): array
    {
        $factura->load([
            'comprobante',
            'documentos',
            'documentos.nit',
            'documentos.cuenta',
            'documentos.comprobante',
            'documentos.centro_costos',
        ]);

        $nit = null;
        $documentos = [];
        $observacion = null;
        $totalFactura = 0;
        $calcularTotal = false;
        $debitoTotal = 0;
        $creditoTotal = 0;
        $nombreUsuario = 'PROVEEDOR';

        if ($factura->comprobante && $factura->comprobante->tipo_comprobante != 4) {
            $calcularTotal = true;
        }

        if ($factura->comprobante && (
            $factura->comprobante->tipo_comprobante == 0 ||
            $factura->comprobante->tipo_comprobante == 3
        )) {
            $nombreUsuario = 'CLIENTE';
        }

        foreach ($factura->documentos as $documento) {
            if ($documento->documento_referencia) {
                $extracto = (new Extracto(
                    $documento->id_nit,
                    null,
                    $documento->documento_referencia
                ))->actual()->first();

                if (!$nit && $extracto) {
                    $ciudad = '';
                    if ($extracto->id_ciudad) {
                        $ciudadObj = Ciudades::whereId($extracto->id_ciudad)->first();
                        if ($ciudadObj) $ciudad = $ciudadObj->nombre;
                    }
                    $nit = (object)[
                        'nombre_nit'       => $extracto->nombre_nit ?? '',
                        'razon_social'     => $extracto->razon_social ?? '',
                        'telefono'         => $extracto->telefono_1 ?? '',
                        'email'            => $extracto->email ?? '',
                        'direccion'        => $extracto->direccion ?? '',
                        'tipo_documento'   => $extracto->tipo_documento ?? '',
                        'numero_documento' => $extracto->numero_documento ?? '',
                        'ciudad'           => $ciudad,
                    ];
                }

                $documento->saldo = $extracto?->saldo ?? 0;
            }

            if ($documento->concepto && !$observacion) {
                $observacion = $documento->concepto;
            }

            if ($documento->id_nit && !$nit) {
                $getNit = Nits::whereId($documento->id_nit)->with('ciudad')->first();
                if ($getNit) {
                    $nit = (object)[
                        'nombre_nit' => $getNit->nombre_completo,
                        'razon_social' => $getNit->razon_social,
                        'telefono' => $getNit->telefono_1,
                        'email' => $getNit->email,
                        'direccion' => $getNit->direccion,
                        'tipo_documento' => $getNit->tipo_documento->nombre,
                        'numero_documento' => $getNit->numero_documento,
                        'ciudad' => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
                    ];
                }
                $documento->nit = $nit;
            }

            // Cálculo de totales exactamente como en tu original
            if ($calcularTotal && mb_substr($documento->cuenta->cuenta, 0, 2) == '11') {
                $totalFactura += $documento->cuenta->naturaleza_cuenta == 1 ? $documento->debito : $documento->credito;
            }
            if ($calcularTotal && mb_substr($documento->cuenta->cuenta, 0, 2) == '13') {
                $totalFactura += $documento->cuenta->naturaleza_cuenta == 1 ? $documento->debito : $documento->credito;
            }
            if ($calcularTotal && mb_substr($documento->cuenta->cuenta, 0, 2) == '22') {
                $totalFactura += $documento->cuenta->naturaleza_cuenta == 1 ? $documento->debito : $documento->credito;
            }

            if ($factura->comprobante && $factura->comprobante->tipo_comprobante == 4) {
                $debitoTotal += $documento->debito;
                $creditoTotal += $documento->credito;
            }

            $documentos[] = $documento;
        }

        // --- Construir los datos para los bloques (igual que en GastosPdf y RecibosPdf) ---

        // 1. Cliente (si existe nit)
        $cliente = null;
        if ($nit) {
            $cliente = (object)[
                'titulo' => $nombreUsuario,
                'nombre_cliente' => $nit->nombre_nit,
                'datos_adicionales' => [
                    (object)[
                        'icono' => 'building',
                        'titulo' => $nit->tipo_documento ?? 'Documento',
                        'valor' => $nit->numero_documento ?? ''
                    ],
                    (object)[
                        'icono' => 'location',
                        'titulo' => 'Dirección',
                        'valor' => ($nit->direccion ?? '') . ($nit->ciudad ? ' - ' . $nit->ciudad : '')
                    ],
                    (object)[
                        'icono' => 'phone',
                        'titulo' => 'Teléfono',
                        'valor' => $nit->telefono ?? ''
                    ],
                ]
            ];
        }

        // 2. Información adicional del documento
        $infoData = (object)[
            'titulo' => 'INFORMACIÓN DEL DOCUMENTO',
            'datos_adicionales' => [
                (object)[
                    'icono' => 'calendar',
                    'titulo' => 'Fecha',
                    'valor' => $factura->fecha_manual ?? ''
                ],
                (object)[
                    'icono' => 'file',
                    'titulo' => 'Comprobante',
                    'valor' => $factura->comprobante->nombre ?? ''
                ],
                (object)[
                    'icono' => 'tag',
                    'titulo' => 'Consecutivo',
                    'valor' => $factura->consecutivo ?? ''
                ],
                (object)[
                    'icono' => 'user',
                    'titulo' => 'Usuario',
                    'valor' => request()->user() ? request()->user()->username : 'Portafolio ERP'
                ],
            ]
        ];

        if ($totalFactura > 0) {
            $infoData->datos_adicionales[] = (object)[
                'icono' => 'money',
                'titulo' => 'Total',
                'valor' => number_format($totalFactura)
            ];
        }

        // 3. Tabla de documentos
        $tabla = self::buildTable($documentos);

        // 4. Resumen (solo si es tipo 4 muestra débito/crédito)
        $resumen = [
            'titulo' => 'RESUMEN',
            'filas' => []
        ];
        if ($factura->comprobante && $factura->comprobante->tipo_comprobante == 4) {
            $resumen['filas'] = [
                ['label' => 'TOTAL DÉBITO', 'value' => $debitoTotal, 'formatter' => 'number'],
                ['label' => 'TOTAL CRÉDITO', 'value' => $creditoTotal, 'formatter' => 'number'],
            ];
        }

        // 5. QR
        $qrBase64 = self::generateQr($claveUrl);

        // 6. Devolver en el formato que espera el builder
        return [
            'titulo' => $factura->comprobante->nombre ?? 'DOCUMENTO',
            'empresa' => $empresa,
            'cliente' => $cliente,
            'info_data' => $infoData,
            'consecutivo' => $factura->consecutivo,
            'fecha_manual' => $factura->fecha_manual,
            'tabla' => $tabla,
            'resumen' => $resumen,
            'observacion' => $observacion,
            'pagos' => collect(), // No hay pagos en este tipo
            'qr_code' => $qrBase64,
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
            'monto_letras' => null, // No aplica
        ];
    }

    private static function buildTable(array $documentos): array
    {
        $columns = [
            Column::make('cuenta', 'CUENTA')->align('left'),
            Column::make('nit_nombre', 'NOMBRE NIT')->align('left'),
            Column::make('factura', 'FACTURA')->align('left'),
            Column::make('ccosto', 'C. COSTOS')->align('left'),
            Column::make('debito', 'DEBITO')->align('right')->format('number'),
            Column::make('credito', 'CREDITO')->align('right')->format('number'),
            Column::make('saldo', 'SALDO')->align('right')->format('number'),
        ];

        $rows = [];
        foreach ($documentos as $documento) {
            $nitDocumento = '';
            if ($documento->nit) {
                $nitDocumento = ($documento->nit->numero_documento ?? '') . ' - ' . ($documento->nit->nombre_nit ?? $documento->nit->razon_social ?? '');
            }

            $rows[] = [
                'cuenta' => ($documento->cuenta->cuenta ?? '') . ' - ' . ($documento->cuenta->nombre ?? ''),
                'nit_nombre' => $nitDocumento,
                'factura' => $documento->documento_referencia ?? '',
                'ccosto' => ($documento->centro_costos ? $documento->centro_costos->codigo : '') . 
                            ($documento->centro_costos ? ' - ' . $documento->centro_costos->nombre : ''),
                'debito' => $documento->debito ?? 0,
                'credito' => $documento->credito ?? 0,
                'saldo' => $documento->saldo ?? 0,
            ];
        }

        return Table::make()
            ->title('DETALLE DE DOCUMENTOS')
            ->columns($columns)
            ->rows($rows)
            ->toArray();
    }

    private static function generateQr(string $claveUrl): string
    {
        $baseUrl = config('app.url');
        $url = "{$baseUrl}/documentos-generales-pdf?code={$claveUrl}";
        $svg = QrCode::format('svg')->size(300)->generate($url);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}