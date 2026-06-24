<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Exports\ImpuestoExport;
use Illuminate\Support\Facades\Bus;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeImpuestos;
use App\Helpers\Printers\ImpuestosPdf;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfImpuestos;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfImpuestosDetalle;

class ImpuestosController extends Controller
{

    public function index ()
    {
        return view('pages.contabilidad.impuestos.impuestos-view');
    }

    public function generate(Request $request)
    {
        if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
			return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Por favor ingresar un rango de fechas válido.'
            ]);
		}
        
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $impuestos = InfImpuestos::where('id_empresa', $empresa->id)
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('agrupar_impuestos', $request->get('agrupar_impuestos', null))
            ->where('tipo_informe', $request->get('tipo_informe', null))
            ->where('nivel', $request->get('nivel', null))
			->first();
        
        if($impuestos) {
            InfImpuestosDetalle::where('id_impuestos', $impuestos->id)->delete();
            $impuestos->delete();
        }
        
        if($request->get('id_cuenta')) {
            $cuenta = PlanCuentas::find($request->get('id_cuenta'));
            $request->merge(['cuenta' => $cuenta->cuenta]);
            $request->merge(['id_tipo_cuenta' => $cuenta->id_tipo_cuenta]);
        }
        
        $data = $request->except(['columns']);
        
        ProcessInformeImpuestos::dispatch($data, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de impuestos'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $impuestos = InfImpuestos::where('id', $request->get('id'))->first();
		$informe = InfImpuestosDetalle::where('id_impuestos', $impuestos->id)->with('nit.actividad_economica');
		$total = InfImpuestosDetalle::where('id_impuestos', $impuestos->id)->orderBy('id', 'desc')->first();

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
            'totales' => $total,
            'message'=> 'Balance generado con exito!'
        ]);
    }

    public function find(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        $impuestos = InfImpuestos::where('id_empresa', $empresa->id)
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('agrupar_impuestos', $request->get('agrupar_impuestos', null))
            ->where('nivel', $request->get('nivel', null))
			->first();
            
        if ($impuestos) {
            return response()->json([
                'success'=>	true,
                'data' => $impuestos->id,
                'message'=> 'Cartera existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Cartera no existente'
        ]);
    }

    public function showPdfRetencion(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        return (new ImpuestosPdf($empresa, $request->all()))
            ->buildPdf()
            ->showPdf();
    }

    public function showPdfReteica(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
        return (new ImpuestosPdf($empresa, $request->all()))
            ->buildPdf()
            ->showPdf();
    }

    public function exportExcel(Request $request)
    {
        try {
            $informeImpuesto = InfImpuestos::find($request->get('id'));
            
            if($informeImpuesto && $informeImpuesto->exporte == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel de auxiliar'
                ]);
            }

            if($informeImpuesto && $informeImpuesto->exporte == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeImpuesto->url_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/impuesto_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeImpuesto->exporte = 1;
            $informeImpuesto->url_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeImpuesto->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            $empresa = Empresa::where('token_db', $has_empresa)->first();

            Bus::chain([
                function () use ($id_informe, &$fileName, &$empresa) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new ImpuestoExport($id_informe, $empresa))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $informeImpuesto) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-impuestos-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de impuestos generado con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $informeImpuesto->exporte = 2;
                    $informeImpuesto->save();
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
