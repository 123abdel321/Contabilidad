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
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfDocumentosGenerales;
use App\Models\Informes\InfDocumentosGeneralesDetalle;

class DocumentosGeneralesController extends Controller
{
    private $request;

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

            // Primero obtenemos los documentos que se van a eliminar
            $documentos = DB::connection('sam')->table('documentos_generals AS DG')
                ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
                ->where('anulado', 0)
                ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
                ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
                ->where(function ($query) {
                    $query->when(isset($this->request['precio_desde']), function ($query) {
                        $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) >= ?', [$this->request['precio_desde']]);
                    })->when(isset($this->request['precio_hasta']), function ($query) {
                        $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) <= ?', [$this->request['precio_hasta']]);
                    });
                })
                ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
                    $query->where('DG.id_nit', $this->request['id_nit']);
                })
                ->when(isset($this->request['id_comprobante']) ? $this->request['id_comprobante'] : false, function ($query) {
                    $query->where('DG.id_comprobante', $this->request['id_comprobante']);
                })
                ->when(isset($this->request['id_centro_costos']) ? $this->request['id_centro_costos'] : false, function ($query) {
                    $query->where('DG.id_centro_costos', $this->request['id_centro_costos']);
                })
                ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
                    $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
                })
                ->when(isset($this->request['documento_referencia']) ? $this->request['documento_referencia'] : false, function ($query) {
                    $query->where('DG.documento_referencia', $this->request['documento_referencia']);
                })
                ->when(isset($this->request['consecutivo']) ? $this->request['consecutivo'] : false, function ($query) {
                    $query->where('DG.consecutivo', $this->request['consecutivo']);
                })
                ->when(isset($this->request['concepto']) ? $this->request['concepto'] : false, function ($query) {
                    $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
                })
                ->when(isset($this->request['id_usuario']) ? $this->request['id_usuario'] : false, function ($query) {
                    $query->where('DG.created_by', $this->request['id_usuario']);
                })
                ->select('DG.*', 'PC.naturaleza_cuenta')
                ->get();

            // Validamos si los documentos tienen abonos
            $documentosConAbonos = [];
            foreach ($documentos as $doc) {
                
                if ($doc->documento_referencia) {
                    $esCausacion = $this->esCausacionDocumento($doc);
                    $esIngreso = $this->esIngresoDocumento($doc);

                    if ($esCausacion && !$esIngreso) {
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
            }

            // Si hay documentos con abonos, retornamos error
            if (count($documentosConAbonos) > 0) {
                // Crear tabla HTML con los documentos que tienen abonos
                $html = "<div style='margin-bottom: 10px; font-weight: bold;'>No se pueden eliminar los documentos</div>";
                $html .= "<div style='color: #FFF; margin-bottom: 15px;'>Los siguientes documentos tienen abonos pendientes:</div>";
                
                // Tabla simple, compacta
                $html .= "<table style='width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px;'>";
                
                // Encabezado
                $html .= "<tr style='background-color: #f8f9fa;'>";
                $html .= "<th style='border: 1px solid #dee2e6; padding: 8px 10px; text-align: left;'>DOCUMENTO</th>";
                $html .= "<th style='border: 1px solid #dee2e6; padding: 8px 10px; text-align: right;'>TOTAL ABONOS</th>";
                $html .= "</tr>";
                
                // Cuerpo - Documentos con abonos
                foreach ($documentosConAbonos as $item) {
                    $html .= "<tr>";
                    $html .= "<td style='border: 1px solid #dee2e6; color: #FFF; padding: 6px 10px;'><strong>" . $item['documento'] . "</strong></td>";
                    $html .= "<td style='border: 1px solid #dee2e6; color: #FFF; padding: 6px 10px; text-align: right;'>" . 
                            number_format($item['total_abono'], 2) . "</td>";
                    $html .= "</tr>";
                }
                
                // Total general (opcional)
                $totalAbonos = array_sum(array_column($documentosConAbonos, 'total_abono'));
                $html .= "<tr style='background-color: #dc3545; color: white;'>";
                $html .= "<td style='border: 1px solid #dc3545; padding: 8px 10px; font-weight: bold;'>TOTAL</td>";
                $html .= "<td style='border: 1px solid #dc3545; padding: 8px 10px; text-align: right; font-weight: bold;'>" . 
                        number_format($totalAbonos, 2) . "</td>";
                $html .= "</tr>";
                
                $html .= "</table>";
                
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $html
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            // Si no hay abonos, procedemos con la eliminación
            DB::connection('sam')->table('documentos_generals AS DG')
                ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
                ->where('anulado', 0)
                ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
                ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
                ->where(function ($query) {
                    $query->when(isset($this->request['precio_desde']), function ($query) {
                        $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) >= ?', [$this->request['precio_desde']]);
                    })->when(isset($this->request['precio_hasta']), function ($query) {
                        $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) <= ?', [$this->request['precio_hasta']]);
                    });
                })
                ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
                    $query->where('DG.id_nit', $this->request['id_nit']);
                })
                ->when(isset($this->request['id_comprobante']) ? $this->request['id_comprobante'] : false, function ($query) {
                    $query->where('DG.id_comprobante', $this->request['id_comprobante']);
                })
                ->when(isset($this->request['id_centro_costos']) ? $this->request['id_centro_costos'] : false, function ($query) {
                    $query->where('DG.id_centro_costos', $this->request['id_centro_costos']);
                })
                ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
                    $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
                })
                ->when(isset($this->request['documento_referencia']) ? $this->request['documento_referencia'] : false, function ($query) {
                    $query->where('DG.documento_referencia', $this->request['documento_referencia']);
                })
                ->when(isset($this->request['consecutivo']) ? $this->request['consecutivo'] : false, function ($query) {
                    $query->where('DG.consecutivo', $this->request['consecutivo']);
                })
                ->when(isset($this->request['concepto']) ? $this->request['concepto'] : false, function ($query) {
                    $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
                })
                ->when(isset($this->request['id_usuario']) ? $this->request['id_usuario'] : false, function ($query) {
                    $query->where('DG.id_usuario', $this->request['id_usuario']);
                })
                ->delete();

            // Eliminamos los registros relacionados que ya no tienen documentos
            DB::connection('sam')->table('fac_documentos')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('documentos_generals')
                        ->where('documentos_generals.relation_type', 2)
                        ->whereRaw('documentos_generals.relation_id = fac_documentos.id');
                })
                ->delete();

            DB::connection('sam')->table('fac_compras')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('documentos_generals')
                        ->where('documentos_generals.relation_type', 3)
                        ->whereRaw('documentos_generals.relation_id = fac_compras.id');
                })
                ->delete();

            DB::connection('sam')->table('fac_ventas')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('documentos_generals')
                        ->where('documentos_generals.relation_type', 4)
                        ->whereRaw('documentos_generals.relation_id = fac_ventas.id');
                })
                ->delete();

            DB::connection('sam')->table('con_recibos')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('documentos_generals')
                        ->where('documentos_generals.relation_type', 6)
                        ->whereRaw('documentos_generals.relation_id = con_recibos.id');
                })
                ->delete();

            DB::connection('sam')->table('con_gastos')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('documentos_generals')
                        ->where('documentos_generals.relation_type', 7)
                        ->whereRaw('documentos_generals.relation_id = con_gastos.id');
                })
                ->delete();

            DB::connection('sam')->table('con_pagos')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('documentos_generals')
                        ->where('documentos_generals.relation_type', 8)
                        ->whereRaw('documentos_generals.relation_id = con_pagos.id');
                })
                ->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Documentos eliminados con éxito!'
            ], 200);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
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
