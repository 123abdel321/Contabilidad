<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Jobs\ProcessInformeExogena;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfExogena;
use App\Models\Sistema\ExogenaFormato;
use App\Models\Informes\InfExogenaDetalle;
use App\Models\Sistema\ExogenaFormatoColumna;
use App\Models\Sistema\ExogenaFormatoConcepto;

class ExogenaController extends Controller
{

    public function index ()
    {
        return view('pages.contabilidad.exogena.exogena-view');
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

        $exogena = InfExogena::where('id_empresa', $empresa->id)
            ->where('year', $request->get('year'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_exogena_formato', $request->get('id_formato'))
            ->where('id_exogena_formato_concepto', $request->get('id_concepto', null))
			->first();
        
        if($exogena) {
            InfExogenaDetalle::where('id_exogena', $exogena->id)->delete();
            $exogena->delete();
        }
        
        $data = $request->except(['columns']);
        
        ProcessInformeExogena::dispatch($data, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de medios magneticos'
    	]);
    }

    public function comboFormato(Request $request)
    {
        $formato = ExogenaFormato::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(formato) as text")
        );

        if ($request->get("q")) {
            $formato->where('formato', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $formato->paginate(40);
    }

    public function comboFormatoConcepto(Request $request)
    {
        $formatoConcepto = ExogenaFormatoConcepto::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(concepto) as text")
        );

        if ($request->get("q")) {
            $formatoConcepto->where('concepto', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $formatoConcepto->paginate(40);
    }

    public function comboFormatoColumna(Request $request)
    {
        $formatoColumna = ExogenaFormatoColumna::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre) as text")
        );

        if ($request->get("q")) {
            $formatoColumna->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $formatoColumna->paginate(40);
    }
}
