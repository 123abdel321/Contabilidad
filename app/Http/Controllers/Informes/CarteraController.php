<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
// use App\Exports\BalanceExport;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeCartera;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfCartera;
use App\Models\Informes\InfCarteraDetalle;

class CarteraController extends Controller
{
    public $cuentasCobrarCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.cartera.cartera-view');
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

        $cartera = InfCartera::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_cartera'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('detallar_cartera', $request->get('detallar_cartera', null))
			->first();
        
        if($cartera) {
            InfCarteraDetalle::where('id_cartera', $cartera->id)->delete();
            $cartera->delete();
        }
        
        if($request->get('id_cuenta')) {
            $cuenta = PlanCuentas::find($request->get('id_cuenta'));
            $request->merge(['cuenta' => $cuenta->cuenta]);
            $request->merge(['id_tipo_cuenta' => $cuenta->id_tipo_cuenta]);
        }
        
        $data = $request->except(['columns']);
        
        ProcessInformeCartera::dispatch($data, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de cartera'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $cartera = InfCartera::where('id', $request->get('id'))->first();
		$informe = InfCarteraDetalle::where('id_cartera', $cartera->id);
		$total = InfCarteraDetalle::where('id_cartera', $cartera->id)->orderBy('id', 'desc')->first();

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
            'totales' => $total,
            'message'=> 'Balance generado con exito!'
        ]);
    }

    public function find(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        $cartera = InfCartera::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_cartera'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('detallar_cartera', $request->get('detallar_cartera', null))
			->first();
            
        if ($cartera) {
            return response()->json([
                'success'=>	true,
                'data' => $cartera->id,
                'message'=> 'Cartera existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Cartera no existente'
        ]);
    }

}
