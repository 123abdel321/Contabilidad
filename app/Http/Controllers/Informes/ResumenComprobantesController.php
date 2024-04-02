<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeResumenComprobante;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfResumenComprobante;
use App\Models\Informes\InfResumenComprobanteDetalle;

class ResumenComprobantesController extends Controller
{
    public function index ()
    {
        return view('pages.contabilidad.resumen_comprobantes.resumen_comprobantes-view');
    }

    public function generate(Request $request)
    {
        if ($request->get('init') == "false") {
            return response()->json([
                'data' => '',
            ]);
        }

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        $resumenComprobante = InfResumenComprobante::where('id_empresa', $empresa->id)
            ->where('id_empresa', $request->get('id_empresa', null))
            ->where('fecha_desde', $request->get('fecha_desde', null))
            ->where('fecha_hasta', $request->get('fecha_hasta', null))
            ->where('id_comprobante', $request->get('id_comprobante', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('agrupado', $request->get('agrupado', null))
            ->where('detalle', $request->get('detallar', null))
			->first();

        if ($resumenComprobante) {
            InfResumenComprobanteDetalle::where('id_resumen_comprobante', $resumenComprobante->id)->delete();
            $resumenComprobante->delete();
        }

        $data = $request->except(['columns']);

        ProcessInformeResumenComprobante::dispatch($data, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe resumen de comprobantes'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $resumenComprobante = InfResumenComprobante::where('id', $request->get('id'))->first();
		$informe = InfResumenComprobanteDetalle::where('id_resumen_comprobante', $resumenComprobante->id);

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
            'message'=> 'Resumen de comprobantes generado con exito!'
        ]); 
    }
}
