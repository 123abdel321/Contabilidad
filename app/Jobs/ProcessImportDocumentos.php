<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Events\PrivateMessageEvent;
//TRAITS
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//HELPER
use App\Helpers\Documento;
//MODELS
use App\Models\User;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\DocumentosImport;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacProductosBodegas;

class ProcessImportDocumentos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use BegConsecutiveTrait;

    public $timeout = 3600; // 1 hora
    public $tries = 3;
    public $backoff = [60, 180, 300];
    protected $id_usuario;
    protected $id_empresa;
    protected $totalRecords = 0;
    protected $processedRecords = 0;
    protected $correctRecords = 0;
    protected $errorRecords = 0;
    public $documentosAgrupados = [];

    public function __construct($id_empresa, $id_usuario)
    {
        $this->id_empresa = $id_empresa;
        $this->id_usuario = $id_usuario;
    }

    public function handle()
    {
        $this->empresa = Empresa::find($this->id_empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);

        $canal = "importador-documentos-" . $this->empresa->token_db . '_' . $this->id_usuario;

        try {
            // Contar total de registros a procesar
            $this->totalRecords = DocumentosImport::where(function ($query) {
                $query->where('errores', 0)
                    ->orWhereNull('errores')
                    ->orWhere('errores', '');
                })
                ->count();

            // Enviar evento de inicio
            event(new PrivateMessageEvent($canal, [
                'name' => 'progress',
                'tipo' => 'info',
                'mensaje' => 'Iniciando carga de documentos al sistema...',
                'titulo' => 'Carga de documentos',
                'progress' => 0,
                'processed' => 0,
                'total' => $this->totalRecords,
                'stage' => 'preparing'
            ]));

            DB::connection('sam')->beginTransaction();

            // Procesar en chunks para evitar memory issues
            DocumentosImport::where(function ($query) {
                $query->where('errores', 0)
                    ->orWhereNull('errores')
                    ->orWhere('errores', '');
                })
                ->chunkById(1000, function ($imports) {
                    foreach ($imports as $import) {
                        $fechaFormat = date("Ym", strtotime($import->fecha_manual));
                        $this->documentosAgrupados[$fechaFormat.'-'.$import->codigo_comprobante.'-'.$import->consecutivo][] = $import;
                    }
                });

            if (count($this->documentosAgrupados)) {
                foreach ($this->documentosAgrupados as $documentoImportado) {
                    $this->processedRecords++;
                    $debito = 0;
                    $credito = 0;
                    $tokenFactura = $this->generateTokenDocumento();
                    $comprobante = Comprobantes::where('codigo', $documentoImportado[0]->codigo_comprobante)->first();

                    $facDocumento = FacDocumentos::create([
                        'id_comprobante' => $comprobante->id,
                        'fecha_manual' => $documentoImportado[0]->fecha_manual,
                        'consecutivo' => $documentoImportado[0]->consecutivo,
                        'token_factura' => $tokenFactura,
                        'debito' => 0,
                        'credito' => 0,
                        'saldo_final' => 0,
                        'created_by' => $this->id_usuario,
                        'updated_by' => $this->id_usuario,
                    ]);

                    $primerIdNit = null;
                    $documentoGeneral = new Documento(
                        $comprobante->id,
                        $facDocumento,
                        $documentoImportado[0]->fecha_manual,
                        $documentoImportado[0]->consecutivo
                    );

                    foreach ($documentoImportado as $data) {
                        $debito+= $data->debito;
                        $credito+= $data->credito;

                        $cecos = CentroCostos::where('codigo', $data->codigo_cecos)->first();
                        $nit = Nits::where('numero_documento', $data->documento_nit)->first();
                        $cuenta = PlanCuentas::where('cuenta', $data->cuenta_contable)->first();

                        $doc = [
                            'id_cuenta' => $cuenta->id,
                            'id_nit' => $nit ? $nit->id : '',
                            'documento_referencia' => $data->documento_referencia,
                            'id_centro_costos' => $cecos ? $cecos->id : '',
                            'concepto' => $data->concepto,
                            'fecha_manual' => $data->fecha_manual,
                            'consecutivo' => $data->consecutivo,
                            'id_comprobante' => $comprobante->id,
                            'debito' => $data->debito,
                            'credito' => $data->credito,
                            'saldo' => '',
                            'naturaleza' => '',
                        ];

                        $naturaleza = null;

                        if ($doc['debito'] > 0) {
                            $naturaleza = PlanCuentas::DEBITO;
                        } else if ($doc['credito'] > 0) {
                            $naturaleza = PlanCuentas::CREDITO;
                        }

                        if (array_key_exists('cuenta', $doc)) {
                            $doc['id_cuenta'] = PlanCuentas::whereCuenta($doc['cuenta'])->value('id');
                            unset($doc['cuenta']);
                        }

                        if (array_key_exists('codigo_centro_costos', $doc)) {
                            $doc['id_centro_costos'] = CentroCostos::whereCodigo($doc['codigo_centro_costos'])->value('id');
                            unset($doc['codigo_centro_costos']);
                        }

                        if (array_key_exists('numero_documento', $doc)) {
                            $doc['id_nit'] = Nits::whereNumeroDocumento($doc['numero_documento'])->value('id');
                            if(!$primerIdNit) $primerIdNit = $doc['id_nit'];
                            unset($doc['numero_documento']);
                        } else {
                            if(!$primerIdNit) $primerIdNit = $doc['id_nit'];
                        }

                        $doc['created_by'] = $this->id_usuario;
                        $doc['updated_by'] = $this->id_usuario;
                        $doc['consecutivo'] = $data->consecutivo;

                        $doc = new DocumentosGeneral($doc);
                        $documentoGeneral->addRow($doc, $naturaleza);
                    }

                    if (!$documentoGeneral->save()) {

                        // ── Recolectar el error del documento ──
                        $this->erroresDocumentos[] = [
                            'comprobante'          => $documentoImportado[0]->codigo_comprobante,
                            'consecutivo'          => $documentoImportado[0]->consecutivo,
                            'fecha'                => $documentoImportado[0]->fecha_manual,
                            'nit'                  => $documentoImportado[0]->documento_nit,
                            'documento_referencia' => $documentoImportado[0]->documento_referencia,
                            'errores'              => $documentoGeneral->getErrors(),
                        ];

                        DB::connection('sam')->rollback();

                        // ── Recortar el payload para no exceder el límite de Pusher (10 KB) ──
                        $erroresParaEnviar = [];
                        $totalOriginal     = count($this->erroresDocumentos);

                        foreach (array_slice($this->erroresDocumentos, 0, 3) as $err) {
                            $detalle = [];
                            foreach ($err['errores'] as $grupo => $mensajes) {
                                if (is_array($mensajes)) {
                                    foreach ($mensajes as $campo => $msg) {
                                        $detalle[] = $this->htmlATextoPlano((string) $msg);   // 👈
                                    }
                                } else {
                                    $detalle[] = $this->htmlATextoPlano((string) $mensajes); // 👈
                                }
                            }

                            $erroresParaEnviar[] = [
                                'nit'                  => $err['nit'],
                                'documento_referencia' => $err['documento_referencia'],
                                'comprobante'          => $err['comprobante'],
                                'consecutivo'          => $err['consecutivo'],
                                'fecha'                => $err['fecha'],
                                'detalle'              => implode(' • ', array_slice($detalle, 0, 3)),
                            ];
                        }

                        // ── Avisar al frontend ──
                        event(new PrivateMessageEvent($canal, [
                            'name'          => 'progress',
                            'tipo'          => 'error',
                            'mensaje'       => 'La importación fue cancelada por errores de validación.',
                            'titulo'        => 'Carga de documentos',
                            'progress'      => 0,
                            'processed'     => 0,
                            'total'         => $this->totalRecords,
                            'stage'         => 'completed_with_errors',
                            'errores'       => $erroresParaEnviar,
                            'truncado'      => $totalOriginal > 3,
                            'total_errores' => $totalOriginal,
                        ]));

                        return;
                    }

                    $facDocumento->debito = $debito;
                    $facDocumento->credito = $credito;
                    $facDocumento->id_nit = $primerIdNit;
                    $facDocumento->saldo_final = $debito - $credito;
                    $facDocumento->updated_by = $this->id_usuario;
                    $facDocumento->save();

                    $this->updateConsecutivo($comprobante->id, $documentoImportado[0]->consecutivo);

                    $this->processedRecords++;

                    // Enviar evento de progreso cada 34 registros procesados
                    if ($this->processedRecords % 34 === 0) {
                        $progress = $this->totalRecords > 0
                            ? round(($this->processedRecords / $this->totalRecords) * 100)
                            : 0;

                        event(new PrivateMessageEvent($canal, [
                            'name' => 'progress',
                            'tipo' => 'info',
                            'mensaje' => 'Cargando documentos al sistema...',
                            'titulo' => 'Carga de documentos',
                            'progress' => $progress,
                            'processed' => $this->processedRecords,
                            'total' => $this->totalRecords,
                            'stage' => 'processing'
                        ]));
                    }
                }
            }

            // Eliminar registros procesados correctamente
            DocumentosImport::where(function ($query) {
                $query->where('errores', 0)
                    ->orWhereNull('errores')
                    ->orWhere('errores', '');
                })
            ->delete();

            DB::connection('sam')->commit();

            // Enviar evento de completado exitoso
            event(new PrivateMessageEvent($canal, [
                'name' => 'progress',
                'tipo' => 'success',
                'mensaje' => '¡Carga de documentos completada exitosamente!',
                'titulo' => 'Carga de documentos',
                'progress' => 100,
                'processed' => $this->processedRecords,
                'total' => $this->totalRecords,
                'stage' => 'completed'
            ]));

            // Enviar evento final de importación
            event(new PrivateMessageEvent($canal, [
                'name' => 'import',
                'tipo' => 'exito',
                'mensaje' => 'Importador de documentos finalizado totalmente!',
                'titulo' => 'Importador de documentos',
                'autoclose' => false
            ]));

        } catch (\Exception $exception) {
            DB::connection('sam')->rollback();

            // Enviar evento de error
            event(new PrivateMessageEvent($canal, [
                'name' => 'progress',
                'tipo' => 'error',
                'mensaje' => 'Error durante la carga de documentos: ' . $exception->getMessage(),
                'titulo' => 'Carga de documentos',
                'progress' => 0,
                'processed' => $this->processedRecords,
                'total' => $this->totalRecords,
                'stage' => 'error'
            ]));

            throw $exception;
        }
    }
    
    private function processDocumentos(DocumentosImport $import)
    {
        DB::transaction(function () use ($import) {
            try {

                // Buscar o crear producto
                $producto = FacProductos::Create([
                    'codigo' => $import->codigo,
                    'nombre' => $import->nombre,
                    'id_familia' => $import->id_familia,
                    'precio_inicial' => $import->costo,
                    'precio_minimo' => $import->costo,
                    'precio' => $import->venta,
                    'porcentaje_utilidad' => $margen,
                    'tipo_producto' => 0,
                    'estado' => 1
                ]);

                if ($import->existencias) {
                    FacProductosBodegas::where('id_producto', $producto->id)
                        ->where('id_bodega', $import->id_bodega)
                        ->update([
                            'cantidad' => $import->existencias
                        ]);
                }
                
                $this->correctRecords++;
                
            } catch (\Exception $e) {
                $this->errorRecords++;
                // Puedes registrar el error si lo necesitas
                // Log::error("Error procesando producto {$import->codigo}: " . $e->getMessage());
            }
        });
    }

    private function generateTokenDocumento()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    private function htmlATextoPlano(string $html): string
    {
        // 1) Convertir tags "de bloque" en saltos de línea / separadores
        $html = preg_replace('/<\/?(div|p|tr|li|h[1-6])[^>]*>/i', ' | ', $html);
        $html = preg_replace('/<\/?(td|th)[^>]*>/i', ' - ', $html);
        $html = preg_replace('/<br\s*\/?>/i', ' | ', $html);

        // 2) Ahora sí, quitar cualquier tag restante
        $texto = strip_tags($html);

        // 3) Decodificar entidades (&nbsp;, &aacute;, etc.)
        $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 4) Colapsar separadores repetidos y espacios
        $texto = preg_replace('/\s*\|\s*(\|\s*)+/', ' | ', $texto);   // múltiples " | "
        $texto = preg_replace('/\s*-\s*(-\s*)+/', ' - ', $texto);     // múltiples " - "
        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = trim($texto, " |-\t\n\r\0\x0B");

        return $texto;
    }
}