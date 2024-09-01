<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
// use App\Exports\BalanceExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeEstadoActual;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfEstadoActual;
use App\Models\Informes\InfEstadoActualDetalle;

class EstadoActualController extends Controller
{
    public $cuentasCobrarCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.estado_actual.estado_actual-view');
    }

    public function generate(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $estadoActual = InfEstadoActual::where('id_empresa', $empresa->id)
            ->where('year', $request->get('year', null))
            ->where('month', $request->get('month', null))
            ->where('detalle', $request->get('detalle', null))
            ->where('id_comprobante', $request->get('id_comprobante', null))
			->first();
        
        if($estadoActual) {
            InfEstadoActualDetalle::where('id_estado_actual', $estadoActual->id)->delete();
            $estadoActual->delete();
        }
        
        $data = $request->except(['columns']);
        
        ProcessInformeEstadoActual::dispatch($data, $request->user()->id, $empresa->id);

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

        $estadoActual = InfEstadoActual::where('id', $request->get('id'))->first();
		$informe = InfEstadoActualDetalle::where('id_estado_actual', $estadoActual->id);

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
            'message'=> 'Estado actual generado con exito!'
        ]);
    }

    public function find(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        $estadoActual = InfEstadoActual::where('id_empresa', $empresa->id)
            ->where('year', $request->get('year', null))
            ->where('id_comprobante', $request->get('id_comprobante', null))
			->first();
            
        if ($estadoActual) {
            return response()->json([
                'success'=>	true,
                'data' => $estadoActual->id,
                'message'=> 'Estado actual existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Estado actual no existente'
        ]);
    }

}
