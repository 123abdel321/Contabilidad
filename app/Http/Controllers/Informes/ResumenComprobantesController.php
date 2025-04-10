<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use App\Exports\ResumenComprobanteExport;
use App\Jobs\ProcessInformeResumenComprobante;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfResumenComprobante;
use App\Models\Informes\InfResumenComprobanteDetalle;

class ResumenComprobantesController extends Controller
{
    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first();

        $data = [
            'ubicacion_maximoph' => $ubicacion_maximoph && $ubicacion_maximoph->valor ? $ubicacion_maximoph->valor : '0',
        ];

        return view('pages.contabilidad.resumen_comprobantes.resumen_comprobantes-view', $data);
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

    public function exportExcel(Request $request)
    {
        try {
            $infResumenComprobante = InfResumenComprobante::find($request->get('id'));

            if($infResumenComprobante && $infResumenComprobante->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel del comprobante'
                ]);
            }

            if($infResumenComprobante && $infResumenComprobante->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $infResumenComprobante->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'resumen_comprobante_'.uniqid().'.xlsx';
            
            $url = $fileName;

            $infResumenComprobante->exporta_excel = 1;
            $infResumenComprobante->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $infResumenComprobante->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            Bus::chain([
                function () use ($id_informe, &$fileName) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new ResumenComprobanteExport($id_informe))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $infResumenComprobante) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-resumen-comprobantes-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de resumen comprobante con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $infResumenComprobante->exporta_excel = 2;
                    $infResumenComprobante->save();
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
            ], 422);
        }
    }
}
