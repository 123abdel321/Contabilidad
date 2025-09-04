<?php

namespace App\Http\Controllers\Informes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\ResultadoExport;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeResultados;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfResultado;
use App\Models\Informes\InfResultadoDetalle;

class ResultadosController extends Controller
{
    public $resultadoCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.resultados.resultados-view');
    }

    public function generate(Request $request)
    {
        try {

            if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> 'Por favor ingresar un rango de fechas válido.'
                ], Response::HTTP_NO_CONTENT);
            }

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
            $fecha_hasta = Carbon::parse($request->get('fecha_hasta', null))->format('Y-m-d');
            $fecha_desde = Carbon::parse($request->get('fecha_desde', null))->format('Y-m-d');

            $resultado = InfResultado::where('id_empresa', $empresa->id)
                ->where('fecha_hasta', $fecha_hasta)
                ->where('fecha_desde', $fecha_desde)
                ->where('id_cecos', $request->get('id_cecos', null))
                ->where('id_nit', $request->get('id_nit', null))
                ->first();

            if ($resultado && $resultado->estado == 1) {

                $created = Carbon::parse($resultado->created_at);
                $now = Carbon::now();

                $diffInSeconds = $created->diffInSeconds($now);
                $diffFormatted = floor($diffInSeconds / 60) . 'm ' . ($diffInSeconds % 60) . 's';

                return response()->json([
                    'success'=>	true,
                    'time' => $created->format('Y-m-d H:i') . " ({$diffFormatted})",
                    'data' => '',
                    'message'=> 'Generando informe de resultados'
                ], Response::HTTP_OK);
            }

            if($resultado) {
                InfResultadoDetalle::where('id_resultado', $resultado->id)->delete();
                $resultado->delete();
            }

            if($request->get('id_cuenta')) {
                $cuenta = PlanCuentas::find($request->get('id_cuenta'));
                $request->request->add(['cuenta' => $cuenta->cuenta]);
            } else {
                $request->request->add(['cuenta' => false]);
            }

            $resultado = InfResultado::create([
				'id_empresa' => $empresa->id,
				'fecha_desde' => $request->get('fecha_desde'),
				'fecha_hasta' => $request->get('fecha_hasta'),
				'id_cecos' => $request->get('id_cecos', null),
				'id_nit' => $request->get('id_nit', null),
                'estado' => 1
			]);

            ProcessInformeResultados::dispatch(
                $request->all(),
                $request->user()->id,
                $empresa->id,
                $resultado->id
            );

            return response()->json([
                'success'=>	true,
                'time' => null,
                'data' => '',
                'message'=> 'Generando informe de auxiliar'
            ], Response::HTTP_OK);
            

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(Request $request)
    {
        try {

            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $resultado = InfResultado::where('id', $request->get('id'))->first();

            if (!$resultado) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> 'No se encontro el informe resultados'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $informe = InfResultadoDetalle::where('id_resultado', $resultado->id);
            $total = InfResultadoDetalle::where('id_resultado', $resultado->id)->orderBy('id', 'desc')->first();
            $descuadre = false;
            $filtros = true;

            $informeTotals = $informe->get();

            $informePaginate = $informe->skip($start)
                ->take($rowperpage);

            if(!$resultado->id_cuenta) {
                $filtros = false;
                $descuadre = $total->saldo_final > 0 ? true : false;
            }

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $informeTotals->count(),
                'iTotalDisplayRecords' => $informeTotals->count(),
                'data' => $informePaginate->get(),
                'perPage' => $rowperpage,
                'totales' => $total,
                'filtros' => $filtros,
                'descuadre' => $descuadre,
                'message'=> 'Resultado generado con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function find(Request $request)
    {
        try {
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
            $id_cuenta = $request->get('id_cuenta') != 'null' ? $request->get('id_cuenta') : NULL;
            
            $resultado = InfResultado::where('id_empresa', $empresa->id)
                ->where('fecha_hasta', $request->get('fecha_hasta'))
                ->where('fecha_desde', $request->get('fecha_desde'))
                ->where('id_cuenta', $id_cuenta)
                ->where('nivel', $request->get('nivel', null))
                ->first();
                
            if ($resultado) {

                $created = Carbon::parse($resultado->created_at);

                return response()->json([
                    'success'=>	true,
                    'time' => $created->format('Y-m-d H:i'),
                    'data' => $resultado->id,
                    'message'=> 'Resultado existente'
                ], Response::HTTP_OK);
            }

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Resultado no existente'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $informeResultado = InfResultado::find($request->get('id'));

            if($informeResultado && $informeResultado->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel del auxiliar 12'
                ]);
            }

            if($informeResultado && $informeResultado->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeResultado->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/resultado_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeResultado->exporta_excel = 1;
            $informeResultado->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeResultado->save();

            (new ResultadoExport($request->get('id')))->store($fileName, 'do_spaces', null, [
                'visibility' => 'public'
            ])->chain([
                event(new PrivateMessageEvent('informe-resultado-'.$request->user()['has_empresa'].'_'.$request->user()->id, [
                    'tipo' => 'exito',
                    'mensaje' => 'Excel de Resultado generado con exito!',
                    'titulo' => 'Excel generado',
                    'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                    'autoclose' => false
                ])),
                $informeResultado->exporta_excel = 2,
                $informeResultado->save(),
            ]);

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

}
