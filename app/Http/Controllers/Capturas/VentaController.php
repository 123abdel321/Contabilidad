<?php

namespace App\Http\Controllers\Capturas;

use DB;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacResoluciones;

class VentaController extends Controller
{

    public function index ()
    {
        $data = [
            'bodegas' => FacBodegas::first(),
            'resolucion' => FacResoluciones::first(),
        ];
        return view('pages.capturas.venta.venta-view', $data);
    }

}