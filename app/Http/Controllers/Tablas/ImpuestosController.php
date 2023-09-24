<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\Impuestos;

class ImpuestosController extends Controller
{
    public function comboImpuesto (Request $request)
    {
        $variantes = Impuestos::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre, ' %', porcentaje) as text")
        );

        if ($request->get("q")) {
            $variantes->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
            $variantes->Orwhere('porcentaje', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $variantes->paginate(40);
    }
}
