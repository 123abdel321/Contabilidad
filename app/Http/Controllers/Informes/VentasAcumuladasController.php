<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use App\Exports\VentasAcumuladasExport;
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

    public function exportExcel(Request $request)
    {
        try {
            $informeVentasAcumuladas = InfVentasAcumulada::find($request->get('id'));

            if($informeVentasAcumuladas && $informeVentasAcumuladas->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel de ventas acumuladas'
                ]);
            }

            if($informeVentasAcumuladas && $informeVentasAcumuladas->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeVentasAcumuladas->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/ventasacumuladas_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeVentasAcumuladas->exporta_excel = 1;
            $informeVentasAcumuladas->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeVentasAcumuladas->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            Bus::chain([
                function () use ($id_informe, &$fileName) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new VentasAcumuladasExport($id_informe))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $informeVentasAcumuladas) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-ventas-acumuladas-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de Ventas acumuladas generado con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $informeVentasAcumuladas->exporta_excel = 2;
                    $informeVentasAcumuladas->save();
                }
            ])->dispatch();

            return response()->json([
                'success'=>	true,
                'url_file' => '',
                'message'=> 'Se le notificará cuando el informe haya finalizado'
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
