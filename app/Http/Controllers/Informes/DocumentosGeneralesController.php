<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Exports\DocumentoGeneralExport;
use App\Helpers\Printers\RecibosPdfMultiple;
use App\Jobs\ProcessInformeDocumentosGenerales;
use App\Jobs\ProcessGenerateRecibosMultiplePdf;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Informes\InfDocumentosGenerales;
use App\Models\Sistema\FacProductosBodegasMovimiento;
use App\Models\Informes\InfDocumentosGeneralesDetalle;

class DocumentosGeneralesController extends Controller
{
    private $request;

    // Constantes para tipos de relación
    private const RELATION_TYPES = [
        'fac_documentos' => 2,
        'fac_compras' => 3,
        'fac_ventas' => 4,
        'con_recibos' => 6,
        'con_gastos' => 7,
        'con_pagos' => 8,
    ];
    
    // Tipos de resoluciones
    private const TIPO_FACTURA_ELECTRONICA = 'FACTURA_ELECTRONICA';
    private const TIPO_POS = 'POS';
    private const TIPO_DOCUMENTO_EQUIVALENTE = 'DOCUMENTO_EQUIVALENTE';
    private const TIPO_NOTA_CREDITO = 'NOTA_CREDITO';

    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first();

        $data = [
            'ubicacion_maximoph' => $ubicacion_maximoph && $ubicacion_maximoph->valor ? $ubicacion_maximoph->valor : '0',
        ];

        return view('pages.contabilidad.documentos_generales.documentos_generales-view', $data);
    }

    public function generate (Request $request)
    {
        if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
			return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Por favor ingresar un rango de fechas válido.'
            ]);
		}

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        if ($request->get('id_nit') == "null") $request->merge(['id_nit' => null]);
        if ($request->get('id_cuenta') == "null") $request->merge(['id_cuenta' => null]);
        if ($request->get('id_usuario') == "null") $request->merge(['id_usuario' => null]);
        if ($request->get('id_comprobante') == "null") $request->merge(['id_comprobante' => null]);
        if ($request->get('id_centro_costos') == "null") $request->merge(['id_centro_costos' => null]);
        if ($request->get('id_nit') == "null") $request->merge(['id_nit' => null]);

        $fechaHasta = $request->get('fecha_hasta');
        $fechaDesde = $request->get('fecha_desde');
        $fechaHastaFormateada = $fechaHasta ? Carbon::parse($fechaHasta)->format('Y-m-d') : null;
        $fechaDesdeFormateada = $fechaDesde ? Carbon::parse($fechaDesde)->format('Y-m-d') : null;

        $documentosGenerales = InfDocumentosGenerales::where('id_empresa', $empresa->id)
            ->where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $fechaHastaFormateada) 
            ->where('fecha_desde', $fechaDesdeFormateada)
            ->where('precio_desde', $request->get('precio_desde', null))
            ->where('precio_hasta', $request->get('precio_hasta', null))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            // ->where('id_usuario', $request->get('id_usuario', null))
            ->where('id_comprobante', $request->get('id_comprobante', null))
            ->where('id_centro_costos', $request->get('id_centro_costos', null))
            ->where('documento_referencia', $request->get('documento_referencia', null))
            ->where('consecutivo', $request->get('consecutivo', null))
            ->where('concepto', $request->get('concepto', null))
            ->where('agrupar', $request->get('agrupar', null))
            ->where('agrupado', $request->get('agrupado', null))
			->first();

        $cambioDatos = false;
        if ($request->get('cambio_datos')) {
            $cambioDatos = true;
        }

        $request->request->add(['cambio_datos' => $cambioDatos]);

            
        if($documentosGenerales) {
            InfDocumentosGeneralesDetalle::where('id_documentos_generales', $documentosGenerales->id)->delete();
            $documentosGenerales->delete();
        }
        
        if($request->get('id_cuenta')) {
            $cuenta = PlanCuentas::find($request->get('id_cuenta'));
            if ($cuenta) {
                $request->request->add(['cuenta' => $cuenta->cuenta]);
            }
        }

        ProcessInformeDocumentosGenerales::dispatch($request->all(), $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de documentos generales'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $documentosGenerales = InfDocumentosGenerales::where('id', $request->get('id'))->first();
        if (!$documentosGenerales) {
            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => 0,
                'iTotalDisplayRecords' => 0,
                'data' => [],
                'perPage' => 0,
                'message'=> 'Documentos generado con exito!'
            ]);
        }
        $informe = InfDocumentosGeneralesDetalle::where('id_documentos_generales', $documentosGenerales->id);

        $informeTotals = $informe->get();

        $informePaginate = $informe->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $informeTotals->count(),
            'iTotalDisplayRecords' => $informeTotals->count(),
            'data' => $informePaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Documentos generado con exito!'
        ]);
    }

    public function delete(Request $request)
    {
        $this->request = $request->all();
        
        try {
            DB::connection('sam')->beginTransaction();

            // Obtener documentos con filtros
            $documentos = $this->obtenerDocumentosConFiltros();
            
            // Validar documentos y obtener información necesaria
            $validacion = $this->validarDocumentos($documentos);
            if (!$validacion['es_valido']) {
                return $this->respuestaError($validacion['mensaje'], $validacion['datos'] ?? []);
            }
            
            // Eliminar documentos principales
            $this->eliminarDocumentosPrincipales();
            
            // Eliminar registros huérfanos
            $this->eliminarRegistrosHuerfanos();
            
            // Devolver inventario si es necesario
            $this->devolverInventarioSiNecesario($validacion);
            
            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Documentos eliminados con éxito!'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            dd(  $e->getLine() , $e->getMessage());
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    
    /**
     * Obtiene los documentos aplicando todos los filtros
     */
    private function obtenerDocumentosConFiltros()
    {
        $query = DB::connection('sam')->table('documentos_generals AS DG')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->select('DG.*', 'PC.naturaleza_cuenta');
        
        // Aplicar filtros dinámicos
        $query = $this->aplicarFiltrosPrecio($query);
        $query = $this->aplicarFiltrosGenerales($query);
        
        return $query->get();
    }
    
    /**
     * Aplica filtros de rango de precio
     */
    private function aplicarFiltrosPrecio($query)
    {
        if (isset($this->request['precio_desde'])) {
            $query->whereRaw('ABS(debito - credito) >= ?', [$this->request['precio_desde']]);
        }
        
        if (isset($this->request['precio_hasta'])) {
            $query->whereRaw('ABS(debito - credito) <= ?', [$this->request['precio_hasta']]);
        }
        
        return $query;
    }
    
    /**
     * Aplica filtros generales (nit, comprobante, centro costos, etc)
     */
    private function aplicarFiltrosGenerales($query)
    {
        $filtros = [
            'id_nit' => ['campo' => 'DG.id_nit', 'operador' => '='],
            'id_comprobante' => ['campo' => 'DG.id_comprobante', 'operador' => '='],
            'id_centro_costos' => ['campo' => 'DG.id_centro_costos', 'operador' => '='],
            'documento_referencia' => ['campo' => 'DG.documento_referencia', 'operador' => '='],
            'consecutivo' => ['campo' => 'DG.consecutivo', 'operador' => '='],
            'id_usuario' => ['campo' => 'DG.created_by', 'operador' => '='],
        ];
        
        foreach ($filtros as $key => $config) {
            if (isset($this->request[$key])) {
                $query->where($config['campo'], $config['operador'], $this->request[$key]);
            }
        }
        
        // Filtro especial para cuenta
        if (isset($this->request['id_cuenta']) && isset($this->request['cuenta'])) {
            $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'] . '%');
        }
        
        // Filtro especial para concepto (LIKE)
        if (isset($this->request['concepto'])) {
            $query->where('DG.concepto', 'LIKE', '%' . $this->request['concepto'] . '%');
        }
        
        return $query;
    }
    
    /**
     * Valida todos los documentos antes de eliminar
     */
    private function validarDocumentos($documentos)
    {
        $notasAdevolver = [];
        $ventasAdevolver = [];
        $documentosConAbonos = [];
        
        foreach ($documentos as $doc) {
            $comprobante = Comprobantes::find($doc->id_comprobante);
            $resolucion = $comprobante ? $comprobante->resolucion : null;
            
            // Validar factura electrónica
            $resultado = $this->validarFacturaElectronica($resolucion);
            if ($resultado) return $resultado;
            
            // Validar POS y documentos equivalentes
            $resultado = $this->validarPosYEquivalentes($resolucion, $doc, $ventasAdevolver);
            if ($resultado) return $resultado;
            
            // Registrar notas crédito
            $this->registrarNotasCredito($resolucion, $doc, $notasAdevolver);
            
            // Validar abonos
            $this->validarAbonosDocumento($doc, $comprobante, $documentosConAbonos);
        }
        
        // Si hay documentos con abonos, retornar error
        if (!empty($documentosConAbonos)) {
            return [
                'es_valido' => false,
                'mensaje' => $this->generarTablaAbonos($documentosConAbonos),
                'datos' => []
            ];
        }
        
        return [
            'es_valido' => true,
            'notasAdevolver' => $notasAdevolver,
            'ventasAdevolver' => $ventasAdevolver
        ];
    }
    
    /**
     * Valida si el documento es una factura electrónica
     */
    private function validarFacturaElectronica($resolucion)
    {
        if ($resolucion && $resolucion->tipo_resolucion == FacResoluciones::TIPO_FACTURA_ELECTRONICA) {
            return [
                'es_valido' => false,
                'mensaje' => 'Las ventas electronicas no pueden ser eliminadas',
                'datos' => []
            ];
        }
        return null;
    }
    
    /**
     * Valida documentos POS y equivalentes
     */
    private function validarPosYEquivalentes($resolucion, $doc, &$ventasAdevolver)
    {
        if ($resolucion && in_array($resolucion->tipo_resolucion, [
            FacResoluciones::TIPO_POS, 
            FacResoluciones::TIPO_DOCUEMNTO_EQUIVALENTE
        ])) {
            $notaCredito = FacVentas::where('id_factura', $doc->relation_id)->first();
            
            if ($notaCredito) {
                return [
                    'es_valido' => false,
                    'mensaje' => 'Las ventas con notas credito asociadas no pueden ser eliminadas',
                    'datos' => []
                ];
            }
            
            $ventasAdevolver[$doc->relation_id] = true;
        }
        return null;
    }
    
    /**
     * Registra notas crédito para devolución de inventario
     */
    private function registrarNotasCredito($resolucion, $doc, &$notasAdevolver)
    {
        if ($resolucion && $resolucion->tipo_resolucion == FacResoluciones::TIPO_NOTA_CREDITO) {
            $notasAdevolver[$doc->relation_id] = true;
        }
    }
    
    /**
     * Valida si un documento tiene abonos pendientes
     */
    private function validarAbonosDocumento($doc, $comprobante, &$documentosConAbonos)
    {
        if (!$doc->documento_referencia || !$comprobante) {
            return;
        }
        
        $esCausacion = $this->esCausacionDocumento($doc);
        $esIngreso = $this->esIngresoDocumento($doc);
        
        if ($esCausacion && $comprobante->tipo_comprobante != Comprobantes::TIPO_INGRESOS) {
            $extracto = (new Extracto(
                $doc->id_nit,
                null,
                $doc->documento_referencia,
                null,
                $doc->id_cuenta
            ))->actual()->first();
            
            if ($extracto && $extracto->total_abono > 0) {
                $documentosConAbonos[] = [
                    'documento' => $doc->documento_referencia,
                    'total_abono' => $extracto->total_abono
                ];
            }
        }
    }
    
    /**
     * Genera tabla HTML con los documentos que tienen abonos
     */
    private function generarTablaAbonos($documentosConAbonos)
    {
        $totalAbonos = array_sum(array_column($documentosConAbonos, 'total_abono'));
        
        $html = "<div style='margin-bottom: 10px; font-weight: bold;'>No se pueden eliminar los documentos</div>";
        $html .= "<div style='color: #FFF; margin-bottom: 15px;'>Los siguientes documentos tienen abonos pendientes:</div>";
        $html .= "<table style='width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px;'>";
        
        // Encabezado
        $html .= "<tr style='background-color: #f8f9fa;'>";
        $html .= "<th style='border: 1px solid #dee2e6; padding: 8px 10px; text-align: left;'>DOCUMENTO</th>";
        $html .= "<th style='border: 1px solid #dee2e6; padding: 8px 10px; text-align: right;'>TOTAL ABONOS</th>";
        $html .= "?</tr>";
        
        // Cuerpo
        foreach ($documentosConAbonos as $item) {
            $html .= "<tr>";
            $html .= "<td style='border: 1px solid #dee2e6; color: #FFF; padding: 6px 10px;'><strong>{$item['documento']}</strong></td>";
            $html .= "<td style='border: 1px solid #dee2e6; color: #FFF; padding: 6px 10px; text-align: right;'>" . 
                    number_format($item['total_abono'], 2) . "</td>";
            $html .= "?</tr>";
        }
        
        // Total
        $html .= "<tr style='background-color: #dc3545; color: white;'>";
        $html .= "<td style='border: 1px solid #dc3545; padding: 8px 10px; font-weight: bold;'>TOTAL</td>";
        $html .= "<td style='border: 1px solid #dc3545; padding: 8px 10px; text-align: right; font-weight: bold;'>" . 
                number_format($totalAbonos, 2) . "</td>";
        $html .= "?</tr>";
        $html .= "</table>";
        
        return $html;
    }
    
    /**
     * Elimina los documentos principales
     */
    private function eliminarDocumentosPrincipales()
    {
        $query = DB::connection('sam')->table('documentos_generals AS DG')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta']);
        
        // Aplicar los mismos filtros que en la consulta original
        $query = $this->aplicarFiltrosPrecio($query);
        $query = $this->aplicarFiltrosGenerales($query);
        
        $query->delete();
    }
    
    /**
     * Elimina registros huérfanos de todas las tablas relacionadas
     */
    private function eliminarRegistrosHuerfanos()
    {
        $tablas = [
            'fac_documentos' => self::RELATION_TYPES['fac_documentos'],
            'fac_compras' => self::RELATION_TYPES['fac_compras'],
            'fac_ventas' => self::RELATION_TYPES['fac_ventas'],
            'con_recibos' => self::RELATION_TYPES['con_recibos'],
            'con_gastos' => self::RELATION_TYPES['con_gastos'],
            'con_pagos' => self::RELATION_TYPES['con_pagos'],
        ];
        
        foreach ($tablas as $tabla => $relationType) {
            DB::connection('sam')->table($tabla)
                ->whereNotExists(function($query) use ($relationType, $tabla) {
                    $query->select(DB::raw(1))
                        ->from('documentos_generals')
                        ->where('documentos_generals.relation_type', $relationType)
                        ->whereRaw("documentos_generals.relation_id = {$tabla}.id");
                })
                ->delete();
        }
    }
    
    /**
     * Devuelve inventario si hay notas crédito o ventas para devolver
     */
    private function devolverInventarioSiNecesario($validacion)
    {
        if (!empty($validacion['notasAdevolver'])) {
            $this->devolverInventarioNotasCredito($validacion['notasAdevolver']);
        }
        
        if (!empty($validacion['ventasAdevolver'])) {
            $this->devolverInventarioVentas($validacion['ventasAdevolver']);
        }
    }
    
    /**
     * Retorna respuesta de error formateada
     */
    private function respuestaError($mensaje, $datos = [])
    {
        return response()->json([
            "success" => false,
            'data' => $datos,
            "message" => $mensaje
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function devolverInventarioNotasCredito($notasAdevolver)
    {
        foreach ($notasAdevolver as $idNota => $use) {
            $notaCredito = FacVentas::where('id', $idNota)
                ->with(['detalles' => function($query) {
                    $query->with('producto.familia');
                }])
                ->first();
            
            if (!$notaCredito) {
                continue;
            }

            // Procesar cada detalle de la nota crédito
            foreach ($notaCredito->detalles as $detalle) {
                $producto = $detalle->producto;
                
                // Verificar si el producto maneja inventario
                if (!$producto || !$producto->familia || !$producto->familia->inventario) {
                    continue;
                }

                // Obtener la bodega de la nota crédito
                $bodega = FacBodegas::find($notaCredito->id_bodega);
                if (!$bodega) {
                    continue;
                }

                // Buscar el producto en la bodega
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $bodega->id)
                    ->where('id_producto', $producto->id)
                    ->first();

                if ($bodegaProducto) {
                    // Guardar cantidad anterior para el movimiento
                    $cantidadAnterior = $bodegaProducto->cantidad;
                    
                    // REVERTIR: Restar la cantidad que se había sumado en la nota crédito
                    $bodegaProducto->cantidad -= $detalle->cantidad;
                    $bodegaProducto->updated_by = request()->user()->id;
                    $bodegaProducto->save();

                    // Crear movimiento inverso
                    $movimientoInverso = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id,
                        'id_bodega' => $bodega->id,
                        'cantidad_anterior' => $cantidadAnterior,
                        'cantidad' => $detalle->cantidad,
                        'tipo_tranferencia' => 4,
                        'inventario' => $producto->familia->inventario ? 1 : 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $movimientoInverso->relation()->associate($notaCredito);
                    $notaCredito->bodegas()->save($movimientoInverso);
                }
            }
        }
    }

    private function devolverInventarioVentas($ventasAdevolver)
    {
        foreach ($ventasAdevolver as $idVenta => $use) {
            $venta = FacVentas::where('id', $idVenta)
                ->with(['detalles' => function($query) {
                    $query->with('producto.familia');
                }])
                ->first();
            
            if (!$venta) {
                continue;
            }

            // Procesar cada detalle de la venta
            foreach ($venta->detalles as $detalle) {
                $producto = $detalle->producto;
                
                // Verificar si el producto maneja inventario
                if (!$producto || !$producto->familia || !$producto->familia->inventario) {
                    continue;
                }

                // Obtener la bodega de la venta
                $bodega = FacBodegas::find($venta->id_bodega);
                if (!$bodega) {
                    continue;
                }

                // Buscar el producto en la bodega
                $bodegaProducto = FacProductosBodegas::where('id_bodega', $bodega->id)
                    ->where('id_producto', $producto->id)
                    ->first();

                if ($bodegaProducto) {
                    $cantidadAnterior = $bodegaProducto->cantidad;
                    
                    // Para ventas, se había RESTADO inventario, ahora hay que SUMARLO
                    $bodegaProducto->cantidad += $detalle->cantidad;
                    $bodegaProducto->updated_by = request()->user()->id;
                    $bodegaProducto->save();

                    // Crear movimiento de reversión
                    $movimientoReversion = new FacProductosBodegasMovimiento([
                        'id_producto' => $producto->id,
                        'id_bodega' => $bodega->id,
                        'cantidad_anterior' => $cantidadAnterior,
                        'cantidad' => $detalle->cantidad,
                        'tipo_tranferencia' => 3, // 3 = Reversión de venta
                        'inventario' => $producto->familia->inventario ? 1 : 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $movimientoReversion->relation()->associate($venta);
                    $venta->bodegas()->save($movimientoReversion);
                }
            }
        }
    }

    // Función auxiliar para determinar si un documento es causación
    private function esCausacionDocumento($doc)
    {
        // Determina la naturaleza basado en el saldo del documento
        $naturaleza = $doc->naturaleza_cuenta == PlanCuentas::DEBITO ? 'debito' : 'credito';
        
        // Un documento es causación si tiene saldo en la naturaleza de su cuenta
        return $doc->{$naturaleza} > 0;
    }

    private function esIngresoDocumento($doc)
    {
        // Aquí asumimos que los ingresos son aquellos documentos asociados a comprobantes de tipo ingreso
        $comprobante = Comprobantes::find($doc->id_comprobante);
        return $comprobante && $comprobante->tipo_comprobante == Comprobantes::TIPO_INGRESOS;
    }

    public function exportExcel(Request $request)
    {
        try {
            $infDocumentosGenerales = InfDocumentosGenerales::find($request->get('id'));

            if($infDocumentosGenerales && $infDocumentosGenerales->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel del auxiliar 12'
                ]);
            }

            if($infDocumentosGenerales && $infDocumentosGenerales->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $infDocumentosGenerales->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/documento_general_'.uniqid().'.xlsx';
            
            $url = $fileName;

            $infDocumentosGenerales->exporta_excel = 1;
            $infDocumentosGenerales->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $infDocumentosGenerales->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            Bus::chain([
                function () use ($id_informe, &$fileName) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new DocumentoGeneralExport($id_informe))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $infDocumentosGenerales) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-documentos-generales-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de documentos generado con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $infDocumentosGenerales->exporta_excel = 2;
                    $infDocumentosGenerales->save();
                }
            ])->dispatch();

            return response()->json([
                'success'=>	true,
                'url_file' => '',
                'message'=> 'Se le notificará cuando el informe haya finalizado'
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function exportPdf(Request $request)
    {
        try {

            if (!$request->get('id_comprobante')) {
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> "El comprobante es obligatorio"
                ]);
            }
            $comprobante = Comprobantes::where('id', $request->get('id_comprobante'))->first();
            if (!$comprobante) {
                logger()->critical("Error showGeneralPdf: el comprobante id: {$request->get('id_comprobante')} no existe; consecutivo: {$consecutivo}");
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> "El comprobante: {$request->get('id_comprobante')} no existe"
                ]);
            }

            if ($comprobante->tipo_comprobante != Comprobantes::TIPO_INGRESOS) {
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> "El comprobante: {$comprobante->nombre} no esta permitido para pdf"
                ]);
            }

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            ProcessGenerateRecibosMultiplePdf::dispatch($empresa, $request->all(), $request->user()->id);

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> 'Generando facturas pdf, se notificará apenas finalice'
            ], 200);
            

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

}
