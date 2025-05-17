<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeResumenCartera;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfResumenCartera;
use App\Models\Informes\InfResumenCarteraDetalle;

class ResumenCarteraController extends Controller
{

    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first('valor')->valor ?? '0';

        return view('pages.contabilidad.resumen_cartera.resumen_cartera-view', [
            'ubicacion_maximoph' => $ubicacion_maximoph
        ]);
    }

    public function generate(Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

        $resumenCartera = InfResumenCartera::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('dias_mora', $request->get('dias_mora', null))
            ->first();

        if($resumenCartera) {
            InfResumenCartera::where('id', $resumenCartera->id)->delete();
            $resumenCartera->delete();
        }

        $resumenCartera = InfResumenCartera::create([
            'id_empresa' => $empresa->id,
            'fecha_hasta' => $request->get('fecha_hasta'),
            'estado' => 1
        ]);
        
        ProcessInformeResumenCartera::dispatch($request->all(), $request->user()->id, $empresa->id, $resumenCartera->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de resumen cartera'
    	]);
    }

    public function show(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $resumenCartera = InfResumenCartera::where('id', $request->get('id'))->first();

            if (!$resumenCartera) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> 'No se encontro el informe resumen cartera'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $informe = InfResumenCarteraDetalle::where('id_resumen_cartera', $resumenCartera->id);
            $cuentas = json_decode($resumenCartera->cuentas);

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
                'cuentas' => $cuentas,
                'message'=> 'Resumen cartera generado con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}
