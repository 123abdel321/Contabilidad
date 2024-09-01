<?php

namespace App\Http\Controllers\Informes;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeDocumentosGenerales;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Informes\InfDocumentosGenerales;
use App\Models\Informes\InfDocumentosGeneralesDetalle;

class DocumentosGeneralesController extends Controller
{
    private $request;

    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first();

        $data = [
            'ubicacion_maximoph' => $ubicacion_maximoph && $ubicacion_maximoph->valor ? $ubicacion_maximoph->valor : '0',
        ];

        return view('pages.contabilidad.documentos_generales.documentos_generales-view', $data);
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
            'message'=> 'Documentos generado con exito!'
        ]);
    }

    public function delete(Request $request)
    {
        $this->request = $request->all();
        try {
            DB::connection('sam')->beginTransaction();

            DB::connection('sam')->table('documentos_generals AS DG')
                ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
                ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
                ->leftJoin('impuestos AS IM', 'PC.id_impuesto', 'IM.id')
                ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
                ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
                ->where('anulado', 0)
                ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
                ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
                ->where(function ($query) {
                    $query->when(isset($this->request['precio_desde']), function ($query) {
                        $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) >= ?', [$this->request['precio_desde']]);
                    })->when(isset($this->request['precio_hasta']), function ($query) {
                        $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) <= ?', [$this->request['precio_hasta']]);
                    });
                })
                ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
                    $query->where('DG.id_nit', $this->request['id_nit']);
                })
                ->when(isset($this->request['id_comprobante']) ? $this->request['id_comprobante'] : false, function ($query) {
                    $query->where('DG.id_comprobante', $this->request['id_comprobante']);
                })
                ->when(isset($this->request['id_centro_costos']) ? $this->request['id_centro_costos'] : false, function ($query) {
                    $query->where('DG.id_centro_costos', $this->request['id_centro_costos']);
                })
                ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
                    $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
                })
                ->when(isset($this->request['documento_referencia']) ? $this->request['documento_referencia'] : false, function ($query) {
                    $query->where('DG.documento_referencia', $this->request['documento_referencia']);
                })
                ->when(isset($this->request['consecutivo']) ? $this->request['consecutivo'] : false, function ($query) {
                    $query->where('DG.consecutivo', $this->request['consecutivo']);
                })
                ->when(isset($this->request['concepto']) ? $this->request['concepto'] : false, function ($query) {
                    $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
                })
                ->when(isset($this->request['id_usuario']) ? $this->request['id_usuario'] : false, function ($query) {
                    $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
                })
            ->delete();

            DB::connection('sam')->commit();

            return response()->json([
				'success'=>	true,
				'message'=> 'Documentos eliminados con exito!'
			], 200);

        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

}
