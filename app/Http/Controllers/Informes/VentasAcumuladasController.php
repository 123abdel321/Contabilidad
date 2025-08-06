<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeVentasAcumuladas;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfVentasAcumulada;
use App\Models\Informes\InfVentasAcumuladaDetalle;

class VentasAcumuladasController extends Controller
{
    public function index ()
    {
        return view('pages.contabilidad.ventas_acumuladas.ventas_acumuladas-view');
    }

    public function generate (Request $request)
    {
        if (!$request->has('fecha_desde') && $request->get('fecha_hasta')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
			return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Por favor ingresar un rango de fechas válido.'
            ]);
		}

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $ventasAcumulada = InfVentasAcumulada::where('id_empresa', $empresa->id)
            ->where('id_tipo_informe',  $request->get('id_tipo_informe', null))
            ->where('fecha_desde',  $request->get('fecha_desde', null))
            ->where('fecha_hasta',  $request->get('fecha_hasta', null))
            ->where('documento_referencia',  $request->get('documento_referencia', null))
            ->where('id_nit',  $request->get('id_nit', null))
            ->where('id_resolucion',  $request->get('id_resolucion', null))
            ->where('id_bodega',  $request->get('id_bodega', null))
            ->where('id_producto',  $request->get('id_producto', null))
            ->where('id_usuario',  $request->get('id_usuario', null))
            ->where('id_forma_pago',  $request->get('id_forma_pago', null))
            ->where('detallar_venta',  $request->get('detallar_venta', null))
			->first();

        if ($ventasAcumulada && $ventasAcumulada->estado == 1) {
            $created = Carbon::parse($extracto->created_at);
            $now = Carbon::now();

            $diffInSeconds = $created->diffInSeconds($now);
            $diffFormatted = floor($diffInSeconds / 60) . 'm ' . ($diffInSeconds % 60) . 's';

            return response()->json([
                'success'=>	true,
                'time' => $created->format('Y-m-d H:i') . " ({$diffFormatted})",
                'data' => '',
                'message'=> 'Generando informe de ventas acumuladas'
            ], Response::HTTP_OK);
        }

        if($ventasAcumulada) {
            InfVentasAcumuladaDetalle::where('id_venta_acumulada', $ventasAcumulada->id)->delete();
            $ventasAcumulada->delete();
        }

        $ventaAcumuluda = InfVentasAcumulada::create([
            'id_empresa' => $empresa->id,
            'id_tipo_informe' => $request->get('id_tipo_informe'),
            'fecha_desde' => $request->get('fecha_desde'),
            'fecha_hasta' => $request->get('fecha_hasta'),
            'documento_referencia' => $request->get('documento_referencia'),
            'id_nit' => $request->get('id_nit'),
            'id_resolucion' => $request->get('id_resolucion'),
            'id_bodega' => $request->get('id_bodega'),
            'id_producto' => $request->get('id_producto'),
            'id_usuario' => $request->get('id_usuario'),
            'id_forma_pago' => $request->get('id_forma_pago'),
            'detallar_venta' => $request->get('detallar_venta'),
            'estado' => 0
        ]);

        ProcessInformeVentasAcumuladas::dispatch($request->all(), $request->user()->id, $empresa->id, $ventaAcumuluda->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de ventas acumulados'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $ventasAcumuladas = InfVentasAcumulada::where('id', $request->get('id'))->first();
        $informe = InfVentasAcumuladaDetalle::where('id_venta_acumulada', $ventasAcumuladas->id);

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
            'message'=> 'Informe de ventas acumuladas creado con exito!'
        ]);
    }
}
