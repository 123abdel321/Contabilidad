<?php

namespace App\Http\Controllers\Informes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\AuxiliarExport;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use App\Helpers\Printers\AuxiliarPdf;
use Illuminate\Support\Facades\Validator;
//JOBS
use App\Jobs\ProcessInformeAuxiliar;
use App\Jobs\GenerateAuxiliarPdfJob;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfAuxiliar;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfAuxiliarDetalle;

class AuxiliarController extends Controller
{
    public $auxiliarCollection = [];

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first('valor')->valor ?? '0';

        return view('pages.contabilidad.auxiliar.auxiliar-view', [
            'ubicacion_maximoph' => $ubicacion_maximoph
        ]);
    }

    public function generate(Request $request)
    {
        try {

            if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> 'Por favor ingresar un rango de fechas válido.'
                ], Response::HTTP_NO_CONTENT);
            }
            
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
    
            $auxiliar = InfAuxiliar::where('id_empresa', $empresa->id)
                ->where('fecha_hasta', $request->get('fecha_hasta'))
                ->where('fecha_desde', $request->get('fecha_desde'))
                ->where('id_cuenta', $request->get('id_cuenta', null))
                ->where('id_nit', $request->get('id_nit', null))
                ->first();

            if ($auxiliar && $auxiliar->estado == 1) {

                $created = Carbon::parse($auxiliar->created_at);
                $now = Carbon::now();

                $diffInSeconds = $created->diffInSeconds($now);
                $diffFormatted = floor($diffInSeconds / 60) . 'm ' . ($diffInSeconds % 60) . 's';

                return response()->json([
                    'success'=>	true,
                    'time' => $created->format('Y-m-d H:i') . " ({$diffFormatted})",
                    'data' => '',
                    'message'=> 'Generando informe de auxiliar'
                ], Response::HTTP_OK);
            }
            
            if($auxiliar) {
                InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id)->delete();
                $auxiliar->delete();
            }
    
            if($request->get('id_cuenta')) {
                $cuenta = PlanCuentas::find($request->get('id_cuenta'));
                $request->request->add(['cuenta' => $cuenta->cuenta]);
            }

            $auxiliar = InfAuxiliar::create([
				'id_empresa' => $empresa->id,
				'fecha_desde' => $request->get('fecha_desde'),
				'fecha_hasta' => $request->get('fecha_hasta'),
				'id_cuenta' => $request->get('id_cuenta', null),
				'id_nit' => $request->get('id_nit', null),
                'estado' => 1
			]);
            
            ProcessInformeAuxiliar::dispatch(
                $request->all(),
                $request->user()->id,
                $empresa->id,
                $auxiliar->id
            );
    
            return response()->json([
                'success'=>	true,
                'time' => null,
                'data' => '',
                'message'=> 'Generando informe de auxiliar'
            ], Response::HTTP_OK);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $auxiliar = InfAuxiliar::where('id', $request->get('id'))->first();

            if (!$auxiliar) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> 'No se encontro el informe auxiliar'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $informe = InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id);
            $total = InfAuxiliarDetalle::where('id_auxiliar', $auxiliar->id)->orderBy('id', 'desc')->first();
            $descuadre = false;
            $filtros = true;

            $informeTotals = $informe->get();

            $informePaginate = $informe->skip($start)
                ->take($rowperpage);
            
            if(!$auxiliar->id_cuenta && !$auxiliar->id_nit) {
                $filtros = false;
                $descuadre = $total->saldo_final > 0 ? true : false;
            }

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $informeTotals->count(),
                'iTotalDisplayRecords' => $informeTotals->count(),
                'data' => $informePaginate->get(),
                'perPage' => $rowperpage,
                'totales' => $total,
                'filtros' => $filtros,
                'descuadre' => $descuadre,
                'message'=> 'Auxiliar generado con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function find(Request $request)
    {
        try {
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        
            $auxiliar = InfAuxiliar::where('id_empresa', $empresa->id)
                ->where('fecha_hasta', $request->get('fecha_hasta'))
                ->where('fecha_desde', $request->get('fecha_desde'))
                ->where('id_cuenta', $request->get('id_cuenta', null))
                ->where('id_nit', $request->get('id_nit', null))
                ->first();
            
            if ($auxiliar) {

                $created = Carbon::parse($auxiliar->created_at);

                return response()->json([
                    'success'=>	true,
                    'time' => $created->format('Y-m-d H:i'),
                    'data' => $auxiliar,
                    'message'=> 'Auxiliar existente'
                ], Response::HTTP_OK);
            }

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Auxiliar no existente'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $informeAuxiliar = InfAuxiliar::find($request->get('id'));

            if($informeAuxiliar && $informeAuxiliar->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel de auxiliar'
                ]);
            }

            if($informeAuxiliar && $informeAuxiliar->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeAuxiliar->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/auxiliar_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeAuxiliar->exporta_excel = 1;
            $informeAuxiliar->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeAuxiliar->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');
            
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            Bus::chain([
                function () use ($id_informe, &$fileName, &$empresa) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new AuxiliarExport($id_informe, $empresa))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $informeAuxiliar) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-auxiliar-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de Auxiliar generado con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $informeAuxiliar->exporta_excel = 2;
                    $informeAuxiliar->save();
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

    public function showPdf(Request $request)
    {
        try {
            $auxiliar = InfAuxiliar::where('id', $request->get('id'))->first();

            if (!$auxiliar) {
                return response()->json([
                    'success'=> false,
                    'data' => [],
                    'message'=> 'El auxiliar no existe'
                ], 422);
            }

            if($auxiliar && $auxiliar->exporta_pdf == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el pdf de auxiliar'
                ]);
            }

            if($auxiliar && $auxiliar->exporta_pdf == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $auxiliar->archivo_pdf,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/auxiliar_'.uniqid().'.pdf';
            $url = $fileName;

            $auxiliar->exporta_pdf = 1;
            $auxiliar->save();

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
            $user_id = $request->user()->id;
            $has_empresa = $request->user()['has_empresa'];

            GenerateAuxiliarPdfJob::dispatch(
                $request->get('id'),
                $user_id,
                $has_empresa,
                $auxiliar
            )->onQueue('pdfs');

            return response()->json([
                'success'=> true,
                'data' => [],
                'message'=> 'Generando PDF... Te notificaremos cuando esté listo.'
            ], 202);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

}
