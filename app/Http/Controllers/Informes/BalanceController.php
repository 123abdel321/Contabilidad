<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Exports\BalanceExport;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeBalance;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfBalance;
use App\Models\Informes\InfBalanceDetalle;

class BalanceController extends Controller
{
    public $balanceCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.balance.balance-view');
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

        $balance = InfBalance::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('nivel', $request->get('nivel', null))
			->first();

        if($balance) {
            InfBalanceDetalle::where('id_balance', $balance->id)->delete();
            $balance->delete();
        }

        if($request->get('id_cuenta')) {
            $cuenta = PlanCuentas::find($request->get('id_cuenta'));
            $request->request->add(['cuenta' => $cuenta->cuenta]);
        }

        ProcessInformeBalance::dispatch($request->all(), $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de balance'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $balance = InfBalance::where('id', $request->get('id'))->first();
		$informe = InfBalanceDetalle::where('id_balance', $balance->id);
		$total = InfBalanceDetalle::where('id_balance', $balance->id)->orderBy('id', 'desc')->first();
        $descuadre = false;
        $filtros = true;

        $informeTotals = $informe->get();

        $informePaginate = $informe->skip($start)
            ->take($rowperpage);

        if(!$balance->id_cuenta) {
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
            'message'=> 'Balance generado con exito!'
        ]);
    }

    public function find(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $id_cuenta = $request->get('id_cuenta') != 'null' ? $request->get('id_cuenta') : NULL;
        
        $balance = InfBalance::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('id_cuenta', $id_cuenta)
            ->where('nivel', $request->get('nivel', null))
			->first();
            
        if ($balance) {
            return response()->json([
                'success'=>	true,
                'data' => $balance->id,
                'message'=> 'Balance existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Balance no existente'
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $informeBalance = InfBalance::find($request->get('id'));

            if($informeBalance && $informeBalance->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel del auxiliar 12'
                ]);
            }

            if($informeBalance && $informeBalance->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeBalance->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'balance_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeBalance->exporta_excel = 1;
            $informeBalance->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeBalance->save();

            (new BalanceExport($request->get('id')))->store($fileName, 'do_spaces', null, [
                'visibility' => 'public'
            ])->chain([
                event(new PrivateMessageEvent('informe-balance-'.$request->user()['has_empresa'].'_'.$request->user()->id, [
                    'tipo' => 'exito',
                    'mensaje' => 'Excel de Balance generado con exito!',
                    'titulo' => 'Excel generado',
                    'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                    'autoclose' => false
                ])),
                $informeBalance->exporta_excel = 2,
                $informeBalance->save(),
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
