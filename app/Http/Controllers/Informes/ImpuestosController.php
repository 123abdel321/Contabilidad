<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
// use App\Exports\BalanceExport;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeImpuestos;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfImpuestos;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfImpuestosDetalle;

class ImpuestosController extends Controller
{

    public function index ()
    {
        return view('pages.contabilidad.impuestos.impuestos-view');
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

        $impuestos = InfImpuestos::where('id_empresa', $empresa->id)
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('agrupar_impuestos', $request->get('agrupar_impuestos', null))
            ->where('tipo_informe', $request->get('tipo_informe', null))
            ->where('nivel', $request->get('nivel', null))
			->first();
        
        if($impuestos) {
            InfImpuestosDetalle::where('id_impuestos', $impuestos->id)->delete();
            $impuestos->delete();
        }
        
        if($request->get('id_cuenta')) {
            $cuenta = PlanCuentas::find($request->get('id_cuenta'));
            $request->merge(['cuenta' => $cuenta->cuenta]);
            $request->merge(['id_tipo_cuenta' => $cuenta->id_tipo_cuenta]);
        }
        
        $data = $request->except(['columns']);
        
        ProcessInformeImpuestos::dispatch($data, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de impuestos'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $impuestos = InfImpuestos::where('id', $request->get('id'))->first();
		$informe = InfImpuestosDetalle::where('id_impuestos', $impuestos->id)->with('nit.actividad_economica');
		$total = InfImpuestosDetalle::where('id_impuestos', $impuestos->id)->orderBy('id', 'desc')->first();

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
        
        $impuestos = InfImpuestos::where('id_empresa', $empresa->id)
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('agrupar_impuestos', $request->get('agrupar_impuestos', null))
            ->where('nivel', $request->get('nivel', null))
			->first();
            
        if ($impuestos) {
            return response()->json([
                'success'=>	true,
                'data' => $impuestos->id,
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
