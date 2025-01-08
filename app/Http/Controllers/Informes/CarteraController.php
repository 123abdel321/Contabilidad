<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Exports\CarteraExport;
use Illuminate\Support\Facades\Bus;
use App\Events\PrivateMessageEvent;
use App\Jobs\ProcessInformeCartera;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfCartera;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfCarteraDetalle;

class CarteraController extends Controller
{
    public $cuentasCobrarCollection = [];

    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first();

        $data = [
            'ubicacion_maximoph' => $ubicacion_maximoph && $ubicacion_maximoph->valor ? $ubicacion_maximoph->valor : '0',
        ];

        return view('pages.contabilidad.cartera.cartera-view', $data);
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

        $cartera = InfCartera::where('id_empresa', $empresa->id)
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('agrupar_cartera', $request->get('agrupar_cartera', null))
            ->where('tipo_informe', $request->get('tipo_informe', null))
            ->where('nivel', $request->get('nivel', null))
			->first();
        
        if($cartera) {
            InfCarteraDetalle::where('id_cartera', $cartera->id)->delete();
            $cartera->delete();
        }
        
        if($request->get('id_cuenta')) {
            $cuenta = PlanCuentas::find($request->get('id_cuenta'));
            $request->merge(['cuenta' => $cuenta->cuenta]);
            $request->merge(['id_tipo_cuenta' => $cuenta->id_tipo_cuenta]);
        }
        
        $data = $request->except(['columns']);
        
        ProcessInformeCartera::dispatch($data, $request->user()->id, $empresa->id);

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

        $cartera = InfCartera::where('id', $request->get('id'))->first();
		$informe = InfCarteraDetalle::where('id_cartera', $cartera->id);
		$total = InfCarteraDetalle::where('id_cartera', $cartera->id)->orderBy('id', 'desc')->first();

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
        
        $cartera = InfCartera::where('id_empresa', $empresa->id)
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_cuenta', $request->get('id_cuenta', null))
            ->where('agrupar_cartera', $request->get('agrupar_cartera', null))
            ->where('nivel', $request->get('nivel', null))
			->first();
            
        if ($cartera) {
            return response()->json([
                'success'=>	true,
                'data' => $cartera->id,
                'message'=> 'Cartera existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Cartera no existente'
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $informeCartera = InfCartera::find($request->get('id'));

            if($informeCartera && $informeCartera->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel de cartera'
                ]);
            }

            if($informeCartera && $informeCartera->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeCartera->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'cartera_'.uniqid().'.xlsx';
            $url = $fileName;

            // $informeCartera->exporta_excel = 1;
            // $informeCartera->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeCartera->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            Bus::chain([
                function () use ($id_informe, &$fileName) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new CarteraExport($id_informe))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $informeCartera) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-cartera-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de Cartera generado con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $informeCartera->exporta_excel = 2;
                    $informeCartera->save();
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
