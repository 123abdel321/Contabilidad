<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\BalanceExport;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\PlanCuentas;

class CarteraController extends Controller
{
    public $cuentasCobrarCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.cartera.cartera-view');
    }

    public function generate(Request $request)
    {
        
    }

}
