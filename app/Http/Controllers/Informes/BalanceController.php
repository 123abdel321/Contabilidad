<?php

namespace App\Http\Controllers\Informes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Exports\BalanceExport;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeBalance;
use App\Helpers\Printers\BalancePdf;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfBalance;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfBalanceDetalle;

class BalanceController extends Controller
{
    public $balanceCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.balance.balance-view');
    }

    public function generate(Request $request)
    {
        try {

            if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> 'Por favor ingresar un rango de fechas válido.'
                ]);
            }
    
            if ($request->get('tipo') == '3') {
                $cuentaPerdida = VariablesEntorno::whereNombre('cuenta_perdida')->first();
                $cuentaPerdida = $cuentaPerdida ? $cuentaPerdida->valor : '';
                $cuentaUtilidad = VariablesEntorno::whereNombre('cuenta_utilidad')->first();
                $cuentaUtilidad = $cuentaUtilidad ? $cuentaUtilidad->valor : '';
        
                if (!$cuentaPerdida && !$cuentaUtilidad) {
                    return response()->json([
                        'success'=>	false,
                        'data' => [],
                        'message'=> 'Se necesita configurar cuenta utilidad y cuenta perdida en el entorno.'
                    ]);
                }
        
                $cuentaPerdida = PlanCuentas::where('cuenta', $cuentaPerdida)->first();
                $cuentaUtilidad = PlanCuentas::where('cuenta', $cuentaUtilidad)->first();
        
                if (!$cuentaPerdida && !$cuentaUtilidad) {
                    return response()->json([
                        'success'=>	false,
                        'data' => [],
                        'message'=> 'La cuenta utilidad y la cuenta perdida no existen en el plan de cuentas.'
                    ]);
                }
        
                if (!$cuentaPerdida) {
                    return response()->json([
                        'success'=>	false,
                        'data' => [],
                        'message'=> 'La cuenta perdida no existen en el plan de cuentas.'
                    ]);
                }
        
                if (!$cuentaUtilidad) {
                    return response()->json([
                        'success'=>	false,
                        'data' => [],
                        'message'=> 'La cuenta utilidad no existen en el plan de cuentas.'
                    ]);
                }
            }
    
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
            
            $balance = InfBalance::where('id_empresa', $empresa->id)
                ->where('fecha_hasta', $request->get('fecha_hasta'))
                ->where('fecha_desde', $request->get('fecha_desde'))
                ->where('cuenta_hasta', $request->get('cuenta_hasta'))
                ->where('cuenta_desde', $request->get('cuenta_desde'))
                ->where('id_nit', $request->get('id_nit', null))
                ->where('tipo', $request->get('tipo', null))
                ->where('nivel', $request->get('nivel', null))
                ->first();
    
            if ($balance && $balance->estado == 1) {

                $created = Carbon::parse($balance->created_at);
                $now = Carbon::now();

                $diffInSeconds = $created->diffInSeconds($now);
                $diffFormatted = floor($diffInSeconds / 60) . 'm ' . ($diffInSeconds % 60) . 's';

                return response()->json([
                    'success'=>	true,
                    'time' => $created->format('Y-m-d H:i') . " ({$diffFormatted})",
                    'data' => '',
                    'message'=> 'Generando informe de balance'
                ], Response::HTTP_OK);
            }
    
            if($balance) {
                InfBalanceDetalle::where('id_balance', $balance->id)->delete();
                $balance->delete();
            }

            $balance = InfBalance::create([
                'id_empresa' => $empresa->id,
                'fecha_desde' => $request->get('fecha_desde'),
                'fecha_hasta' => $request->get('fecha_hasta'),
                'cuenta_hasta' => $request->get('cuenta_hasta'),
                'cuenta_desde' => $request->get('cuenta_desde'),
                'estado' => 1,
                'id_nit' => $request->get('id_nit', null),
                'tipo' => $request->get('tipo', null),
                'nivel' => $request->get('nivel', null)
            ]);
    
            ProcessInformeBalance::dispatch($request->all(), $request->user()->id, $empresa->id, $balance->id);
    
            return response()->json([
                'success'=>	true,
                'time' => null,
                'data' => '',
                'message'=> 'Generando informe de balance'
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

    public function show (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $balance = InfBalance::where('id', $request->get('id'))->first();
		$informe = InfBalanceDetalle::where('id_balance', $balance->id);
		$total = InfBalanceDetalle::where('id_balance', $balance->id)->orderBy('id', 'desc')->first();
        $descuadre = false;
        $filtros = true;

        $informeTotals = $informe->get();

        $informePaginate = $informe->skip($start)
            ->take($rowperpage);

        if(!$balance->id_cuenta) {
            $filtros = false;
            $descuadre = $total->saldo_final != 0 ? true : false;
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
            'message'=> 'Balance generado con exito!'
        ]);
    }

    public function find (Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $id_cuenta = $request->get('id_cuenta') != 'null' ? $request->get('id_cuenta') : NULL;
        
        $balance = InfBalance::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('cuenta_hasta', $request->get('cuenta_hasta'))
            ->where('cuenta_desde', $request->get('cuenta_desde'))
            ->where('tipo', $request->get('tipo', null))
            ->where('nivel', $request->get('nivel', null))
			->first();
            
        if ($balance) {
            return response()->json([
                'success'=>	true,
                'data' => $balance->id,
                'message'=> 'Balance existente'
            ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Balance no existente'
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $informeBalance = InfBalance::find($request->get('id'));

            if($informeBalance && $informeBalance->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel del auxiliar 12'
                ]);
            }

            if($informeBalance && $informeBalance->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeBalance->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'balance_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeBalance->exporta_excel = 1;
            $informeBalance->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeBalance->save();

            $has_empresa = $request->user()['has_empresa'];
            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            Bus::chain([
                function () use ($id_informe, &$fileName) {
                    // Almacena el archivo en DigitalOcean Spaces o donde lo necesites
                    (new BalanceExport($id_informe))->store($fileName, 'do_spaces', null, [
                        'visibility' => 'public'
                    ]);
                },
                function () use ($user_id, $has_empresa, $url, $informeBalance) {
                    // Lanza el evento cuando el proceso termine
                    event(new PrivateMessageEvent('informe-balance-'.$has_empresa.'_'.$user_id, [
                        'tipo' => 'exito',
                        'mensaje' => 'Excel de Balance generado con exito!',
                        'titulo' => 'Excel generado',
                        'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                        'autoclose' => false
                    ]));
                    
                    // Actualiza el informe auxiliar
                    $informeBalance->exporta_excel = 2;
                    $informeBalance->save();
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

    public function showPdf(Request $request, $id)
	{
        $detalle = InfBalanceDetalle::where('id_balance', $id)->get();

		if(!count($detalle)) {
			return response()->json([
				'success'=>	false,
				'data' => [],
				'message'=> 'El balance no existe'
			], 422);
		}

		$empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

		// $data = (new BalancePdf($empresa, $detalle))->buildPdf()->getData();
		// return view('pdf.informes.balance.balance', $data);

        return (new BalancePdf($empresa, $detalle))
            ->buildPdf()
            ->showPdf();
	}

}
