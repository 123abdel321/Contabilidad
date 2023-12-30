<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Exports\AuxiliarExport;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeAuxiliar;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfAuxiliar;
use App\Models\Informes\InfAuxiliarDetalle;

class AuxiliarController extends Controller
{
    public $auxiliarCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.auxiliar.auxiliar-view');
    }

    public function generate(Request $request)
    {
        if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
			return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Por favor ingresar un rango de fechas válido.'
            ]);
		}

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $auxiliar = InfAuxiliar::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('id_nit', $request->get('id_nit', null))
			->first();
        
        if($auxiliar) {
            InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id)->delete();
            $auxiliar->delete();
        }

        if($request->get('id_cuenta')) {
            $cuenta = PlanCuentas::find($request->get('id_cuenta'));
            $request->request->add(['cuenta' => $cuenta->cuenta]);
        }
        
        ProcessInformeAuxiliar::dispatch($request->all(), $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de auxiliar'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $auxiliar = InfAuxiliar::where('id', $request->get('id'))->first();
        $informe = InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id);
        $total = InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id)->orderBy('id', 'desc')->first();
        $descuadre = false;
        $filtros = true;

        $informeTotals = $informe->get();

        $informePaginate = $informe->skip($start)
            ->take($rowperpage);
        
        if(!$auxiliar->id_cuenta && !$auxiliar->id_nit) {
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
            'message'=> 'Auxiliar generado con exito!'
        ]);
    }

    public function find(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        $auxiliar = InfAuxiliar::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('id_nit', $request->get('id_nit', null))
			->first();
        
        if ($auxiliar) {
            return response()->json([
                'success'=>	true,
                'data' => $auxiliar->id,
                'message'=> 'Auxiliar existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Auxiliar no existente'
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $informeAuxiliar = InfAuxiliar::find($request->get('id'));

            if($informeAuxiliar && $informeAuxiliar->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel del auxiliar 12'
                ]);
            }

            if($informeAuxiliar && $informeAuxiliar->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeAuxiliar->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'auxiliar_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeAuxiliar->exporta_excel = 1;
            $informeAuxiliar->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeAuxiliar->save();

            (new AuxiliarExport($request->get('id')))->store($fileName, 'do_spaces', null, [
                'visibility' => 'public'
            ])->chain([
                event(new PrivateMessageEvent('informe-auxiliar-'.$request->user()['has_empresa'].'_'.$request->user()->id, [
                    'tipo' => 'exito',
                    'mensaje' => 'Excel de Auxiliar generado con exito!',
                    'titulo' => 'Excel generado',
                    'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                    'autoclose' => false
                ])),
                $informeAuxiliar->exporta_excel = 2,
                $informeAuxiliar->save(),
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
