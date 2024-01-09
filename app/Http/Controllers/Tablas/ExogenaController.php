<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\ExogenaFormato;
use App\Models\Sistema\ExogenaFormatoColumna;
use App\Models\Sistema\ExogenaFormatoConcepto;

class ExogenaController extends Controller
{
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
