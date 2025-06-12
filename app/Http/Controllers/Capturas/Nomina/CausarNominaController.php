<?php

namespace App\Http\Controllers\Capturas\Nomina;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nomina\NomPeriodoPagos;

class CausarNominaController extends Controller
{
    protected $messages = null;

    public function __construct()
    {
        $this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El camNipo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
    }

    public function comboPeriodoPago(Request $request)
    {
        $periodoPago = NomPeriodoPagos::select(
            '*',
            DB::raw("CONCAT(fecha_inicio_periodo, ' al ', fecha_fin_periodo) as text")
        );

        return $periodoPago->orderBy('id', 'DESC')->paginate(20);
    }
}