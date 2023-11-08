<?php

namespace App\Http\Controllers\Informes;

use Illuminate\Http\Request;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeDocumentosGenerales;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfDocumentosGenerales;
use App\Models\Informes\InfDocumentosGeneralesDetalle;

class DocumentosGeneralesController extends Controller
{
    public function index ()
    {
        return view('pages.contabilidad.documentos_generales.documentos_generales-view');
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
        if ($request->get('id_usuario') == "null") $request->merge(['id_usuario' => null]);
        if ($request->get('id_comprobante') == "null") $request->merge(['id_comprobante' => null]);
        if ($request->get('id_centro_costos') == "null") $request->merge(['id_centro_costos' => null]);
        if ($request->get('id_nit') == "null") $request->merge(['id_nit' => null]);

        $requestData = $request->all();

        $documentosGenerales = InfDocumentosGenerales::where('id_empresa', $empresa->id)
            ->where('fecha_hasta', $requestData['fecha_hasta'])
            ->where('fecha_desde', $requestData['fecha_desde'])
            ->where('precio_desde', $requestData['precio_desde'])
            ->where('precio_hasta', $requestData['precio_hasta'])
            ->where('id_nit', $requestData['id_nit'])
            ->where('id_cuenta', $requestData['id_cuenta'])
            ->where('id_usuario', $requestData['id_usuario'])
            ->where('id_comprobante', $requestData['id_comprobante'])
            ->where('id_centro_costos', $requestData['id_centro_costos'])
            ->where('documento_referencia', $requestData['documento_referencia'])
            ->where('consecutivo', $requestData['consecutivo'])
            ->where('concepto', $requestData['concepto'])
            ->where('agrupar', $requestData['agrupar'])
            ->where('agrupado', $requestData['agrupado'])
			->first();
            
        if($documentosGenerales) {
            InfDocumentosGeneralesDetalle::where('id_documentos_generales', $documentosGenerales->id)->delete();
            $documentosGenerales->delete();
        }

        if($requestData['id_cuenta']) {
            $cuenta = PlanCuentas::find($requestData['id_cuenta']);
            $requestData['cuenta'] = $cuenta->cuenta;
        }

        ProcessInformeDocumentosGenerales::dispatch($requestData, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de documentos generales'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $documentosGenerales = InfDocumentosGenerales::where('id', $request->get('id'))->first();
        $informe = InfDocumentosGeneralesDetalle::where('id_documentos_generales', $documentosGenerales->id);

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
            'message'=> 'Auxiliar generado con exito!'
        ]);
    }
}
