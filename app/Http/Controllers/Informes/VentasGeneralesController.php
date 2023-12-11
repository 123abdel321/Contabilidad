<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeVentasGenerales;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfVentasGenerales;
use App\Models\Informes\InfVentasGeneralesDetalle;

class VentasGeneralesController extends Controller
{
    public function index ()
    {
        return view('pages.contabilidad.ventas_generales.ventas_generales-view');
    }

    public function generate (Request $request)
    {
        if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
			return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Por favor ingresar un rango de fechas válido.'
            ]);
		}

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        if ($request->get('id_nit') == "null") $request->merge(['id_nit' => null]);
        if ($request->get('id_cuenta') == "null") $request->merge(['id_cuenta' => null]);
        if ($request->get('id_bodega') == "null") $request->merge(['id_bodega' => null]);
        if ($request->get('id_usuario') == "null") $request->merge(['id_usuario' => null]);
        if ($request->get('id_resolucion') == "null") $request->merge(['id_resolucion' => null]);

        $requestData = $request->all();

        $ventasGenerales = InfVentasGenerales::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $requestData['fecha_hasta'])
            ->where('fecha_desde', $requestData['fecha_desde'])
            ->where('precio_desde', $requestData['precio_desde'])
            ->where('precio_hasta', $requestData['precio_hasta'])
            ->where('id_nit', $requestData['id_nit'])
            ->where('id_usuario', $requestData['id_usuario'])
            ->where('id_bodega', $requestData['id_bodega'])
            ->where('id_resolucion', $requestData['id_resolucion'])
            ->where('consecutivo', $requestData['consecutivo'])
			->first();
            
        if($ventasGenerales) {
            InfVentasGeneralesDetalle::where('id_venta_general', $ventasGenerales->id)->delete();
            $ventasGenerales->delete();
        }

        ProcessInformeVentasGenerales::dispatch($requestData, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de ventas generales'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $ventasGenerales = InfVentasGenerales::where('id', $request->get('id'))->first();
        $informe = InfVentasGeneralesDetalle::where('id_venta_general', $ventasGenerales->id);

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
            'message'=> 'Informe de ventas generales creado con exito!'
        ]);
    }
}
