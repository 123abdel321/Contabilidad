<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Exports\ResumenCarteraExport;
use App\Jobs\ProcessInformeResumenCartera;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfResumenCartera;
use App\Models\Informes\InfResumenCarteraDetalle;

class ResumenCarteraController extends Controller
{

    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first('valor')->valor ?? '0';

        return view('pages.contabilidad.resumen_cartera.resumen_cartera-view', [
            'ubicacion_maximoph' => $ubicacion_maximoph
        ]);
    }

    public function generate(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $resumenCartera = InfResumenCartera::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('dias_mora', $request->get('dias_mora', null))
            ->first();

        if($resumenCartera) {
            InfResumenCartera::where('id', $resumenCartera->id)->delete();
            $resumenCartera->delete();
        }

        $resumenCartera = InfResumenCartera::create([
            'id_empresa' => $empresa->id,
            'fecha_hasta' => $request->get('fecha_hasta'),
            'estado' => 1
        ]);
        
        ProcessInformeResumenCartera::dispatch($request->all(), $request->user()->id, $empresa->id, $resumenCartera->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de resumen cartera'
    	]);
    }

    public function show(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $resumenCartera = InfResumenCartera::where('id', $request->get('id'))->first();

            if (!$resumenCartera) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> 'No se encontro el informe resumen cartera'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $informe = InfResumenCarteraDetalle::where('id_resumen_cartera', $resumenCartera->id);
            $cuentas = json_decode($resumenCartera->cuentas);

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
                'cuentas' => $cuentas,
                'message'=> 'Resumen cartera generado con exito!'
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
            $informeResumenCartera = InfResumenCartera::find($request->get('id'));

            if($informeResumenCartera && $informeResumenCartera->exporte == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel de auxiliar'
                ]);
            }

            if($informeResumenCartera && $informeResumenCartera->exporte == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeResumenCartera->url_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/resumencartera_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeResumenCartera->exporte = 1;
            $informeResumenCartera->url_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeResumenCartera->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            Bus::chain([
                function () use ($id_informe, &$fileName) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new ResumenCarteraExport($id_informe))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $informeResumenCartera) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-resumen-cartera-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de Resumen cartera generado con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $informeResumenCartera->exporte = 2;
                    $informeResumenCartera->save();
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
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}
