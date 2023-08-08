<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\AuxiliarExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeAuxiliar;
//MODELS
use App\Models\Empresas\Empresa;
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
        
        if ($auxiliar && $request->get('generar') == 'false') {
            return response()->json([
                'success'=>	true,
                'data' => $auxiliar->id,
                'message'=> 'Auxiliar existente'
            ]);
        }
        
        if($auxiliar) {
            InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id)->delete();
            $auxiliar->delete();
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
        $auxiliar = InfAuxiliar::where('id', $request->get('id'))->first();

		$informe = InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id);

        return response()->json([
            'success'=>	true,
            'data' => $informe->get(),
            'message'=> 'Auxiliar generado con exito!'
        ]);
    }

    public function exportExcel(Request $request)
    {
        return (new AuxiliarExport($request))->download('auxiliar.xlsx');
    }

}
