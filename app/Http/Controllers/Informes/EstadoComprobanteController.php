<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
// use App\Exports\BalanceExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeEstadoComprobante;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfEstadoComprobante;
use App\Models\Informes\InfEstadoComprobanteDetalle;

class EstadoComprobanteController extends Controller
{
    public $cuentasCobrarCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.estado_comprobante.estado_comprobante-view');
    }

    public function generate(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $estadoComprobante = InfEstadoComprobante::where('id_empresa', $empresa->id)
            ->where('year', $request->get('year', null))
            ->where('month', $request->get('month', null))
			->first();
        
        if($estadoComprobante) {
            InfEstadoComprobanteDetalle::where('id_estado_comprobante', $estadoComprobante->id)->delete();
            $estadoComprobante->delete();
        }
        
        $data = $request->except(['columns']);
        
        ProcessInformeEstadoComprobante::dispatch($data, $request->user()->id, $empresa->id);

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

        $estadoComprobante = InfEstadoComprobante::where('id', $request->get('id'))->first();
		$informe = InfEstadoComprobanteDetalle::where('id_estado_comprobante', $estadoComprobante->id);

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
            'message'=> 'Estado comprobante generado con exito!'
        ]);
    }

    public function find(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        $estadoComprobante = InfEstadoComprobante::where('id_empresa', $empresa->id)
            ->where('year', $request->get('year', null))
            ->where('month', $request->get('month', null))
			->first();
            
        if ($estadoComprobante) {
            return response()->json([
                'success'=>	true,
                'data' => $estadoComprobante->id,
                'message'=> 'Estado comprobante existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Estado comprobante no existente'
        ]);
    }

}
