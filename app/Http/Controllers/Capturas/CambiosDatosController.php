<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Jobs\ProcessConsultarFE;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;  
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Relations\Relation;
//MODELS
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;

class CambiosDatosController extends Controller
{
    public function index (Request $request)
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first();

        $data = [
            'ubicacion_maximoph' => $ubicacion_maximoph && $ubicacion_maximoph->valor ? $ubicacion_maximoph->valor : '0',
        ];

        return view('pages.capturas.cambio_datos.cambio_datos-view', $data);
    }

    public function change(Request $request)
    {
        // 1. Obtener datos de la solicitud
        $filtros = $request->get('filtros');
        $tipo_cambio = $request->get('tipo_cambio');
        $datos_destino = $request->except(['filtros', 'tipo_cambio']); 
        
        // 2. Obtener el Query Builder Eloquent
        $query = $this->documentosGeneralesQuery($filtros);

        $total_items = $query->count();
        
        try {
            DB::connection('sam')->beginTransaction();

            $query->chunkById(500, function ($documentos) use ($tipo_cambio, $datos_destino) {
                
                foreach ($documentos as $documento) {

                    $resultado = $this->aplicarCambioDocumento($documento, $tipo_cambio, $datos_destino);
                    
                    if ($resultado['error']) {
                        throw new \Exception($resultado['mensaje'], 500); 
                    }
                }
            });

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true, 
                'data' => [],
                'message' => 'El proceso de cambio de datos ha finalizado sin errores. Total: ' . $total_items . ' documentos afectados.',
            ], 200);

        } catch (\Exception $e) {
            DB::connection('sam')->rollBack();
            
            return response()->json([
                'success' => false, 
                'message' => 'Proceso revertido. Error en Documento ' . ($documento->id ?? 'desconocido') . ': ' . $e->getMessage(),
                'line' => $e->getLine()
            ], 400);
        }
    }

    private function documentosGeneralesQuery($filtros)
    {
        $datos = DocumentosGeneral::with([
                'nit',
                'cuenta.impuesto', // si PlanCuentas tiene relación con Impuestos
                'centro_costos',
                'comprobante',
                'relation'
            ])
            ->select([
                'id',
                'id_nit',
                'id_cuenta',
                'id_comprobante',
                'id_centro_costos',
                'documento_referencia',
                'relation_id',
                'relation_type',
                'concepto',
                'fecha_manual',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at',
                'anulado',
                'debito',
                'credito',
                'consecutivo',
                \DB::raw("debito - credito AS diferencia"),
                \DB::raw("IF(debito - credito < 0, (debito - credito) * -1, debito - credito) AS valor_total"),
                \DB::raw("1 AS total_columnas"),
            ])
            ->where('anulado', 0)
            // 1. Filtro de fechas
            ->when(!empty($filtros['fecha_desde']), function ($query) use ($filtros) {
                $query->where('fecha_manual', '>=', $filtros['fecha_desde']);
            })
            ->when(!empty($filtros['fecha_hasta']), function ($query) use ($filtros) {
                $query->where('fecha_manual', '<=', $filtros['fecha_hasta']);
            })
            // 2. Filtros de precios
            ->when(!empty($filtros['precio_desde']), function ($query) use ($filtros) {
                $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) >= ?', [$filtros['precio_desde']]);
            })
            ->when(!empty($filtros['precio_hasta']), function ($query) use ($filtros) {
                $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) <= ?', [$filtros['precio_hasta']]);
            })
            // 3. Filtros por relaciones
            ->when(!empty($filtros['id_nit']), function ($query) use ($filtros) {
                $query->where('id_nit', $filtros['id_nit']);
            })
            ->when(!empty($filtros['id_comprobante']), function ($query) use ($filtros) {
                $query->where('id_comprobante', $filtros['id_comprobante']);
            })
            ->when(!empty($filtros['id_centro_costos']), function ($query) use ($filtros) {
                $query->where('id_centro_costos', $filtros['id_centro_costos']);
            })
            ->when(!empty($filtros['id_cuenta']), function ($query) use ($filtros) {
                $query->where('id_cuenta', $filtros['id_cuenta']);
            })
            // 4. Filtro de referencia
            ->when(!empty($filtros['documento_referencia']), function ($query) use ($filtros) {
                $query->where('documento_referencia', $filtros['documento_referencia']);
            })
            // 5. Rango de consecutivo
            ->when(!empty($filtros['consecutivo_desde']), function ($query) use ($filtros) {
                $query->where('consecutivo', '>=', $filtros['consecutivo_desde']);
            })
            ->when(!empty($filtros['consecutivo_hasta']), function ($query) use ($filtros) {
                $query->where('consecutivo', '<=', $filtros['consecutivo_hasta']);
            })
            // 6. Filtro de concepto
            ->when(!empty($filtros['concepto']), function ($query) use ($filtros) {
                $query->where('concepto', 'LIKE', '%'.$filtros['concepto'].'%');
            });

        return $datos;
    }

    private function aplicarCambioDocumento($documento, $tipo_cambio, $datos_destino)
    {
        $error = false;
        $mensaje = 'Cambio aplicado con éxito';
        $updateDataDG = []; // Datos para actualizar documentos_generals
        $updateDataCabecera = []; // Datos para actualizar la cabecera (FacDocumentos, ConRecibos, etc.)

        // =========================================================================
        // CAMBIO DE NIT
        // =========================================================================
        if ($tipo_cambio === 'nit') {
            $id_nit_destino = $datos_destino['id_nit_destino'] ?? null;
            
            if ($documento->exige_nit == 1 && empty($id_nit_destino)) {
                $error = true;
                $mensaje = 'La cuenta asociada al documento (' . $documento->cuenta . ') exige NIT y no se proporcionó un NIT de destino.';
            }

            if (!$error) {
                $updateDataDG['id_nit'] = $id_nit_destino;
                $updateDataCabecera['id_nit'] = $id_nit_destino; // Mapeo común para las cabeceras
            }
        }
        // =========================================================================
        // CAMBIO DE COMPROBANTE
        // =========================================================================
        elseif ($tipo_cambio === 'comprobante') {
            $id_comprobante_destino = $datos_destino['id_comprobante_destino'] ?? null;
            
            if (empty($id_comprobante_destino)) {
                $error = true;
                $mensaje = 'Debe especificar un Comprobante de destino válido.';
            }

            if (!$error) {
                $updateDataDG['id_comprobante'] = $id_comprobante_destino;
                $updateDataCabecera['id_comprobante'] = $id_comprobante_destino; // Mapeo común
            }
        }
        // =========================================================================
        // CAMBIO DE CENTRO DE COSTOS
        // =========================================================================
        elseif ($tipo_cambio === 'centro_costos') {
            $id_centro_costos_destino = $datos_destino['id_centro_costos_destino'] ?? null;

            // 1.1. Validar requisito: exige_centro_costos (De la cuenta actual)
            if ($documento->exige_centro_costos == 1 && empty($id_centro_costos_destino)) {
                $error = true;
                $mensaje = 'La cuenta asociada al documento (' . $documento->cuenta . ') exige Centro de Costos y no se proporcionó uno de destino.';
            }

            // 1.2. Aplicar el cambio
            if (!$error) {
                $updateDataDG['id_centro_costos'] = $id_centro_costos_destino;
                $updateDataCabecera['id_centro_costos'] = $id_centro_costos_destino; // Mapeo común
            }
        }
        // =========================================================================
        // CAMBIO DE CUENTA
        // =========================================================================
        elseif ($tipo_cambio === 'cuenta') {
            $id_cuenta_destino = $datos_destino['id_cuenta_destino'] ?? null;
            
            if (empty($id_cuenta_destino)) {
                $error = true;
                $mensaje = 'Debe especificar una Cuenta de destino válida.';
            } else {
                $nuevaCuenta = PlanCuenta::find($id_cuenta_destino);
                
                if (!$nuevaCuenta) {
                    $error = true;
                    $mensaje = "La cuenta destino con ID {$id_cuenta_destino} no fue encontrada.";
                } else {
                    
                    // --- 1. Exigencia de NIT ---
                    if ($nuevaCuenta->exige_nit == 1 && empty($documento->id_nit)) {
                        $error = true;
                        $mensaje = 'La cuenta destino (' . $nuevaCuenta->cuenta . ') exige NIT, pero el documento actual no tiene un NIT asignado.';
                    }

                    // --- 2. Exigencia de Documento de Referencia ---
                    if (!$error && $nuevaCuenta->exige_documento_referencia == 1 && empty($documento->documento_referencia)) {
                        $error = true;
                        $mensaje = 'La cuenta destino (' . $nuevaCuenta->cuenta . ') exige Documento de Referencia, pero el documento actual no lo tiene.';
                    }

                    // --- 3. Exigencia de Concepto ---
                    if (!$error && $nuevaCuenta->exige_concepto == 1 && empty($documento->concepto)) {
                        $error = true;
                        $mensaje = 'La cuenta destino (' . $nuevaCuenta->cuenta . ') exige Concepto, pero el documento actual no lo tiene.';
                    }

                    // --- 4. Exigencia de Centro de Costos ---
                    if (!$error && $nuevaCuenta->exige_centro_costos == 1 && empty($documento->id_centro_costos)) {
                        $error = true;
                        $mensaje = 'La cuenta destino (' . $nuevaCuenta->cuenta . ') exige Centro de Costos, pero el documento actual no lo tiene asignado.';
                    }
                    
                    // Aplicar el cambio
                    if (!$error) {
                        $updateDataDG['id_cuenta'] = $id_cuenta_destino;
                    }
                }
            }
        }
        // =========================================================================
        // CAMBIO DE FECHA
        // =========================================================================
        elseif ($tipo_cambio === 'fecha') {
            $fecha_destino = $datos_destino['fecha_manual_destino'] ?? null;
            
            if (empty($fecha_destino) || !strtotime($fecha_destino)) {
                $error = true;
                $mensaje = 'Debe especificar una Fecha de destino válida.';
            }

            if (!$error) {
                // La fecha se debe actualizar tanto en el detalle como en la cabecera
                $updateDataDG['fecha_manual'] = $fecha_destino;
                $updateDataCabecera['fecha_manual'] = $fecha_destino; // Mapeo común
            }
        }
        
        // --- 2. Validación de Duplicados (Se aplica después de preparar el cambio) ---
        if (!$error && !empty($updateDataDG)) {
            $errorDuplicado = $this->validarDuplicado($documento, $updateDataDG);
            if ($errorDuplicado['error']) {
                $error = true;
                $mensaje = $errorDuplicado['mensaje'];
            }
        }

        // --- 3. Ejecución del UPDATE (DocumentosGeneral y Cabecera) ---
        if (!$error && !empty($updateDataDG)) {
            try {
                // 3.1. Actualizar el detalle (documentos_generals)
                DB::connection('sam')->table('documentos_generals')
                    ->where('id', $documento->id)
                    ->update($updateDataDG);
                
                // 3.2. Actualizar la cabecera (relacionada)
                if (!empty($updateDataCabecera)) {
                    $this->actualizarCabecera($documento, $updateDataCabecera);
                }
                
            } catch (\Exception $e) {
                $error = true;
                $mensaje = 'Error al actualizar DB (Documento General o Cabecera): ' . $e->getMessage();
            }
        }
        
        return ['error' => $error, 'mensaje' => $mensaje];
    }

    private function validarDuplicado($documento, $updateData)
    {
        // Las columnas originales combinadas con las nuevas
        $checkData = array_merge([
            'id_nit' => $documento->id_nit,
            'id_cuenta' => $documento->id_cuenta,
            'fecha_manual' => substr($documento->fecha_manual, 0, 10),
            'debito' => $documento->debito,
            'credito' => $documento->credito,
        ], $updateData);

        // Formatear la fecha manualmente si se modificó
        if (isset($checkData['fecha_manual'])) {
            $fecha_check = substr($checkData['fecha_manual'], 0, 10);
        } else {
            $fecha_check = substr($documento->fecha_manual, 0, 10);
        }

        $duplicado = DB::connection('sam')->table('documentos_generals')
            ->where('id', '!=', $documento->id) // No es el mismo documento
            ->where('id_nit', $checkData['id_nit'] ?? null)
            ->where('id_cuenta', $checkData['id_cuenta'] ?? null)
            ->whereRaw("DATE(fecha_manual) = ?", [$fecha_check])
            ->where('debito', $checkData['debito'])
            ->where('credito', $checkData['credito'])
            ->exists();

        if ($duplicado) {
            return [
                'error' => true,
                'mensaje' => 'La actualización del documento (ID: ' . $documento->id . ') genera un registro duplicado basado en las claves (NIT, Cuenta, Fecha, Débito, Crédito).'
            ];
        }
        
        return ['error' => false, 'mensaje' => ''];
    }

    private function actualizarCabecera($documento, $updateData)
    {
        $cabecera = $documento->relation;
        if (!$cabecera) {
            return;
        }

        $dataToUpdate = [];
        foreach ($updateData as $key => $value) {
            if (in_array($key, $cabecera->getFillable()) || $this->checkColumnExists($cabecera->getTable(), $key)) {
                $dataToUpdate[$key] = $value;
            }
        }

        if (!empty($dataToUpdate)) {
            $cabecera->update($dataToUpdate);
        }
    }

}