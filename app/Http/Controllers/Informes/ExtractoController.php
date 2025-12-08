<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeExtracto;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfExtracto;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Informes\InfExtractoDetalle;

class ExtractoController extends Controller
{
    public $messages;
    public $carteraCollection = [];

    public function __construct()
    {
        $this->messages = [
            'id_nit.exists' => 'El id debe existir en la tabla de nits.',
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute: :input seleccionado es inválido.',
            'numeric' => 'El :attribute debe ser un número.',
			'string' => 'El :attribute debe ser texto.',
			'min' => 'El campo :attribute debe ser mayor que cero.',
			'max' => 'El campo :attribute debe tener menos de :max caracteres.',
			'date' => 'El campo :attribute debe ser una fecha válida.',
			'array' => 'El campo :attribute debe ser un arreglo.',
			'formas_pago.required' => 'Seleccione las formas de pago.'
        ];
    }

    public function index ()
    {
        $ubicacion_maximoph = VariablesEntorno::where('nombre', 'ubicacion_maximoph')->first('valor')->valor ?? '0';

        return view('pages.contabilidad.extracto.extracto-view', [
            'ubicacion_maximoph' => $ubicacion_maximoph
        ]);
    }

    public function generateInforme(Request $request)
    {
        try {

            if (!$request->has('fecha_desde') || !$request->has('fecha_hasta')) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>"Por favor ingresar un rango de fechas válido"
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            $extracto = InfExtracto::where('id_empresa', $empresa->id)
                ->where('fecha_hasta', $request->get('fecha_hasta'))
                ->where('fecha_desde', $request->get('fecha_desde'))
                ->where('id_nit', $request->get('id_nit', null))
                ->where('errores', $request->get('errores', null))
                ->first();

            if ($extracto && $extracto->estado == 1) {

                $created = Carbon::parse($extracto->created_at);
                $now = Carbon::now();

                $diffInSeconds = $created->diffInSeconds($now);
                $diffFormatted = floor($diffInSeconds / 60) . 'm ' . ($diffInSeconds % 60) . 's';

                return response()->json([
                    'success'=>	true,
                    'time' => $created->format('Y-m-d H:i') . " ({$diffFormatted})",
                    'data' => '',
                    'message'=> 'Generando informe de extracto'
                ], Response::HTTP_OK);
            }
            
            if($extracto) {
                InfExtractoDetalle::where('id_extracto', $extracto->id)->delete();
                $extracto->delete();
            }

            $extracto = InfExtracto::create([
                'id_empresa' => $empresa->id,
                'fecha_desde' => $request->get('fecha_desde'),
                'fecha_hasta' => $request->get('fecha_hasta'),
                'id_nit' => $request->get('id_nit', null),
                'errores' => $request->get('errores', null),
                'estado' => 0
            ]);
            
            ProcessInformeExtracto::dispatch($request->all(), $request->user()->id, $empresa->id, $extracto->id);
    
            return response()->json([
                'success'=>	true,
                'time' => null,
                'data' => '',
                'message'=> 'Generando informe de extracto'
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

            $extracto = InfExtracto::where('id', $request->get('id'))->first();

            if (!$extracto) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> 'No se encontro el informe extracto'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $informe = InfExtractoDetalle::where('id_extracto', $extracto->id);
            $total = InfExtractoDetalle::where('id_extracto', $extracto->id)->orderBy('id', 'DESC')->first();

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
                'message'=> 'Extracto generado con exito!'
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function extracto(Request $request)
    {
        $rules = [
			"id_nit" => "nullable|exists:sam.nits,id",
			"numero_documento" => "nullable|exists:sam.nits,numero_documento",
			"id_tipo_cuenta" => "nullable|exists:sam.tipo_cuentas,id",
            "id_cuenta" => "nullable|exists:sam.plan_cuentas,id",
		];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        if ($request->has('detallar_cartera') && $request->get('detallar_cartera')) {
            $extractos = DB::connection('sam')->select($this->queryExtracto($request));
    
            $extractosDetalle = DB::connection('sam')->select($this->queryExtractoDetalle($request));
            //AGREGAR ROWS SUMA TOTAL CUENTAS PADRE
            foreach ($extractos as $extracto) {
                $cuentasAsociadas = $this->getCuentas($extracto->cuenta); //return ARRAY PADRES CUENTA
                
                foreach ($cuentasAsociadas as $cuenta) {
                    if ($this->hasCuentaData($cuenta)) {
                        $this->sumCuentaData($cuenta, $extracto);
                    } else {
                        $this->newCuentaData($cuenta, $extracto, $cuentasAsociadas);
                    }
                }
            }
            //AGREGAR ROWS DETALLE EXTRACTO
            $this->addDetilsData($extractosDetalle);
            //AGREGAR ROW TOTALES
            $this->addTotalsData($extractos);
            //AGREGAR ROWS EXTRACTO
            $this->addTotalNitsData($extractos);
    
            ksort($this->carteraCollection, SORT_STRING | SORT_FLAG_CASE);

            return response()->json([
                'success'=>	true,
                'data' => array_values($this->carteraCollection),
                'message'=> 'Extracto generado con exito!'
            ]);
    
            return response()->json([
                'success'=>	true,
                'data' => $extracto,
                'message'=> 'Extracto generado con exito!'
            ]);
        } else {

            $editando = $request->has('editando') ? $request->get('editando') : false;
            $fechaHora = Carbon::parse($request->get('fecha_manual'));
            $fechaManual = $fechaHora->toDateTimeString();

            $horaManual = null;

            $request->get('fecha_manual', null);
            if ($editando) {
                $horaManual = $fechaHora->format('H:i:s');
            }

            $extractos = (new Extracto(
                $request->get('id_nit', null),
                $request->get('id_tipo_cuenta', null),
                $request->get('documento_referencia', null),
                $fechaManual,
                $request->get('id_cuenta', null)
            ))->anticiposDiscriminados();

            $extractos = $extractos->sortBy('orden, cuenta')->values();
            
            return response()->json([
                'success'=>	true,
                'data' => $extracto->get(),
                'message'=> 'Extracto consultados con exito!'
            ]);
        }

        return response()->json([
            'success'=>	false,
            'data' => [],
            'message'=> 'Extracto consultados con exito!'
        ]);
    }

    public function extractoAnticipos(Request $request)
    {
        $fechaManual = null;
        $tiposCuentas = [8];
        $sin_documento = null;

        if ($request->get('id_tipo_cuenta')) {
            $tiposCuentas = $request->get('id_tipo_cuenta');
        }

        if ($request->has('fecha_manual')) {
            $fechaManual = $request->get('fecha_manual');
        }

        if ($request->has('sin_documento')) {
            $sin_documento = $request->get('sin_documento');
        }
        
        $extractos = (new Extracto(
            $request->get('id_nit'),
            $tiposCuentas,
            null,
            $fechaManual
        ))->anticipos($sin_documento)->get();

        $extractos = $extractos->sortBy('orden, cuenta')->values();

        return response()->json([
            'success'=>	true,
            'data' => $extractos,
            'message'=> 'Anticipos consultados con exito!'
        ]);
    }

    public function existeFactura(Request $request)
    {
        $query = DocumentosGeneral::query();

        if ($request->has('documento_referencia')) {
            $query->where('documento_referencia', $request->get('documento_referencia'));
        }

        if ($request->has('id_comprobante')) {
            $query->where('id_comprobante', $request->get('id_comprobante'));
        }

        // if ($request->has('fecha_manual')) {
        //     $query->where('fecha_manual', $request->get('fecha_manual'));
        // }

        if ($request->has('id_cuenta')) {
            $query->where('id_cuenta', $request->get('id_cuenta'));
        }

        if ($request->has('id_nit')) {
            $query->where('id_nit', $request->get('id_nit'));
        }

        return response()->json([
            'success'=>	true,
            'data' => $query->first(),
            'message'=> 'Factura consultada con exito!'
        ]);
    }

    private function queryExtracto($request)
    {
        $wheres = '';
        $fecha = Carbon::now();

        if($request->has('id_tipo_cuenta') && $request->get('id_tipo_cuenta')) {
            $wheres.= ' AND PCT.id_tipo_cuenta = '.$request->get('id_tipo_cuenta');
        }

        if($request->has('id_nit') && $request->get('id_nit')){
            $wheres.= ' AND N.id = '.$request->get('id_nit');
        } else if ($request->has('numero_documento')) {
            $nit = Nits::whereNumeroDocumento($request->get("numero_documento"))->first();
            if($nit) {
                $wheres.= ' AND N.id = '.$nit->id;
            }
        }

        if($request->has('documento_referencia') && $request->get('documento_referencia')) {
            $wheres.= ' AND DG.documento_referencia = '.$request->get('documento_referencia');
        }

        if($request->has('fecha') && $request->get('fecha')) {
            $wheres.= " AND DG.fecha_manual <= '{$request->get('fecha')}'";
            $fecha = $request->get('fecha');
        }

        $query = "SELECT
                N.id AS id_nit,
                N.numero_documento,
                CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END AS nombre_nit,
                N.razon_social,
                N.telefono_1,
                N.telefono_2,
                N.email,
                N.direccion,
                N.plazo,
                PC.id AS id_cuenta,
                PC.cuenta,
                PC.nombre AS nombre_cuenta,
                DG.documento_referencia,
                DG.id_centro_costos,
                CC.codigo AS codigo_cecos,
                CC.nombre AS nombre_cecos,
                DG.id_comprobante AS id_comprobante,
                '' AS codigo_comprobante,
                '' AS nombre_comprobante,
                CO.tipo_comprobante,
                DG.consecutivo,
                DG.concepto,
                DG.fecha_manual,
                DG.created_at,
                PC.naturaleza_ingresos,
                PC.naturaleza_egresos,
                PC.naturaleza_compras,
                PC.naturaleza_ventas,
                PC.naturaleza_cuenta,
                SUM(DG.debito) AS debito,
                SUM(DG.credito) AS credito,
                DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos,
                'si' AS detalle,
                DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion,
                DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion,
                DG.created_by,
                DG.updated_by,
                IF(PC.naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono,
                IF(PC.naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas,
                IF(
                    PC.naturaleza_cuenta = 0,
                    SUM(debito - credito),
                    SUM(credito - debito)
                ) AS saldo
            FROM
                documentos_generals DG
                    
            LEFT JOIN nits N ON DG.id_nit = N.id
            LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
            LEFT JOIN plan_cuentas_tipos PCT ON PC.id = PCT.id_cuenta
            LEFT JOIN centro_costos CC ON DG.id_centro_costos = CC.id
            LEFT JOIN comprobantes CO ON DG.id_comprobante = CO.id
                    
            WHERE DG.documento_referencia IS NOT NULL
                $wheres
                    
            GROUP BY DG.id_cuenta, DG.id_nit, DG.documento_referencia

            HAVING IF(naturaleza_cuenta = 0, SUM(debito - credito), SUM(credito - debito)) > 0
        ";

        return $query;
    }

    private function queryExtractoDetalle($request)
    {

        $wheres = '';
        $fecha = Carbon::now();

        if($request->has('id_tipo_cuenta') && $request->get('id_tipo_cuenta')) {
            $wheres.= ' AND PC.id_tipo_cuenta = '.$request->get('id_tipo_cuenta');
        }

        if($request->has('id_nit') && $request->get('id_nit')){
            $wheres.= ' AND N.id = '.$request->get('id_nit');
        } else if ($request->has('numero_documento')) {
            $nit = Nits::whereNumeroDocumento($request->get("numero_documento"))->first();
            if($nit) {
                $wheres.= ' AND N.id = '.$nit->id;
            }
        }

        if($request->has('documento_referencia') && $request->get('documento_referencia')) {
            $wheres.= ' AND DG.documento_referencia = '.$request->get('documento_referencia');
        }

        if($request->has('fecha') && $request->get('fecha')) {
            $wheres.= " AND DG.fecha_manual <= '{$request->get('fecha')}'";
            $fecha = $request->get('fecha');
        }

        $query = "SELECT
                N.id AS id_nit,
                N.numero_documento,
                CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END AS nombre_nit,
                N.razon_social,
                N.telefono_1,
                N.telefono_2,
                N.email,
                N.direccion,
                N.plazo,
                PC.id AS id_cuenta,
                PC.cuenta,
                PC.nombre AS nombre_cuenta,
                DG.documento_referencia,
                DG.id_centro_costos,
                CC.codigo AS codigo_cecos,
                CC.nombre AS nombre_cecos,
                DG.id_comprobante,
                CO.codigo AS codigo_comprobante,
                CO.nombre AS nombre_comprobante,
                CO.tipo_comprobante,
                DG.consecutivo,
                DG.concepto,
                DG.fecha_manual,
                DG.created_at,
                PC.naturaleza_ingresos,
                PC.naturaleza_egresos,
                PC.naturaleza_compras,
                PC.naturaleza_ventas,
                PC.naturaleza_cuenta,
                DG.debito,
                DG.credito,
                DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos,
                'detalle' AS detalle,
                DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion,
                DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion,
                DG.created_by,
                DG.updated_by,
                IF(PC.naturaleza_cuenta = 0, credito, debito) AS total_abono,
                IF(PC.naturaleza_cuenta = 0, debito, credito) AS total_facturas,
                IF(
                    PC.naturaleza_cuenta = 0,
                    debito - credito,
                    credito - debito
                ) AS saldo
            FROM
                documentos_generals DG
                
            LEFT JOIN nits N ON DG.id_nit = N.id
            LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
            LEFT JOIN centro_costos CC ON DG.id_centro_costos = CC.id
            LEFT JOIN comprobantes CO ON DG.id_comprobante = CO.id
                
            WHERE DG.documento_referencia IS NOT NULL
                $wheres
            
            ORDER BY cuenta, id_nit, documento_referencia, created_at
        ";

        return $query;
    }

    private function getCuentas($cuenta)
    {
        $dataCuentas = NULL;

        if(strlen($cuenta) > 6){
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 4),
                mb_substr($cuenta, 0, 6),
                $cuenta,
            ];
        } else if (strlen($cuenta) > 4) {
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 4),
                $cuenta,
            ];
        } else if (strlen($cuenta) > 2) {
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                $cuenta,
            ];
        } else if (strlen($cuenta) > 1) {
            $dataCuentas =[
                mb_substr($cuenta, 0, 1),
                $cuenta,
            ];
        } else {
            $dataCuentas =[
                $cuenta,
            ];
        }

        return $dataCuentas;
    }

    private function newCuentaData($cuenta, $extracto, $cuentasAsociadas)
    {
        $detalle = false;
        $detalleGroup = false;

        if(strlen($cuenta) >= strlen($cuentasAsociadas[count($cuentasAsociadas)-1])){
            $detalle = true;
        }

        if(strlen($cuenta) >= strlen($cuentasAsociadas[count($cuentasAsociadas)-2])){
            $detalleGroup = true;
        }
        
        $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();
        if(!$cuentaData){
            return;
        }
        $this->carteraCollection[$cuenta] = [
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'telefono_1' => '',
            'telefono_2' => '',
            'email' => '',
            'direccion' => '',
            'plazo' => '',
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'documento_referencia' => '',
            'id_centro_costos' => '',
            'codigo_cecos' => '',
            'nombre_cecos' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'tipo_comprobante' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'created_at' => '',
            'naturaleza_ingresos' => '',
            'naturaleza_egresos' => '',
            'naturaleza_compras' => '',
            'naturaleza_ventas' => '',
            'naturaleza_cuenta' => '',
            'debito' => '',
            'credito' => '',
            'dias_cumplidos' => '',
            'detalle' => '',
            'fecha_creacion' => '',
            'fecha_edicion' => '',
            'created_by' => '',
            'updated_by' => '',
            'total_abono' => number_format((float)$extracto->total_abono, 2, '.', ''),
            'total_facturas' => number_format((float)$extracto->total_facturas, 2, '.', ''),
            'saldo' => number_format((float)$extracto->saldo, 2, '.', ''),
            'detalle' => $detalle,
            'detalle_group' => $detalleGroup,
        ];
    }
    
    private function addTotalsData($extractos)
    {
        $total_abono = 0;
        $total_facturas = 0;
        $saldo = 0;

        foreach ($extractos as $extracto) {
            $total_abono+= number_format((float)$extracto->total_abono, 2, '.', '');
            $total_facturas+= number_format((float)$extracto->total_facturas, 2, '.', '');
            $saldo+= number_format((float)$extracto->saldo, 2, '.', '');
        }

        $this->carteraCollection['9999'] = [
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'telefono_1' => '',
            'telefono_2' => '',
            'email' => '',
            'plazo' => '',
            'direccion' => '',
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'documento_referencia' => '',
            'id_centro_costos' => '',
            'codigo_cecos' => '',
            'nombre_cecos' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'tipo_comprobante' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'created_at' => '',
            'naturaleza_ingresos' => '',
            'naturaleza_egresos' => '',
            'naturaleza_compras' => '',
            'naturaleza_ventas' => '',
            'naturaleza_cuenta' => '',
            'debito' => '',
            'credito' => '',
            'dias_cumplidos' => '',
            'detalle' => '',
            'fecha_creacion' => '',
            'fecha_edicion' => '',
            'created_by' => '',
            'updated_by' => '',
            'total_abono' => $total_abono,
            'total_facturas' => $total_facturas,
            'saldo' => $saldo,
            'detalle' => false,
            'detalle_group' => false,
        ];
    }

    private function addDetilsData($extractosDetalle)
    {
        foreach ($extractosDetalle as $extractoDetalle) {
            $cuentaNumero = 1;
            $cuentaNueva = $extractoDetalle->cuenta.'-'.
                $extractoDetalle->id_nit.'B'.
                $extractoDetalle->documento_referencia.'B'.
                $cuentaNumero.'B';
            while ($this->hasCuentaData($cuentaNueva)) {
                $cuentaNumero++;
                $cuentaNueva = $extractoDetalle->cuenta.'-'.
                    $extractoDetalle->id_nit.'B'.
                    $extractoDetalle->documento_referencia.'B'.
                    $cuentaNumero.'B';
            }
            $this->carteraCollection[$cuentaNueva] = [
                'id_nit' => $extractoDetalle->id_nit,
                'numero_documento' => '',
                'nombre_nit' => '',
                'razon_social' => '',
                'telefono_1' => $extractoDetalle->telefono_1,
                'telefono_2' => $extractoDetalle->telefono_2,
                'email' => $extractoDetalle->email,
                'direccion' => $extractoDetalle->direccion,
                'plazo' => $extractoDetalle->plazo,
                'id_cuenta' => $extractoDetalle->id_cuenta,
                'cuenta' => '',
                'nombre_cuenta' => '',
                'documento_referencia' => '',
                'id_centro_costos' => $extractoDetalle->id_centro_costos,
                'codigo_cecos' => $extractoDetalle->codigo_cecos,
                'nombre_cecos' => $extractoDetalle->nombre_cecos,
                'id_comprobante' => $extractoDetalle->id_comprobante,
                'codigo_comprobante' => $extractoDetalle->codigo_comprobante,
                'nombre_comprobante' => $extractoDetalle->nombre_comprobante,
                'tipo_comprobante' => $extractoDetalle->tipo_comprobante,
                'consecutivo' => $extractoDetalle->consecutivo,
                'concepto' => $extractoDetalle->concepto,
                'fecha_manual' => $extractoDetalle->fecha_manual,
                'created_at' => $extractoDetalle->created_at,
                'naturaleza_ingresos' => $extractoDetalle->naturaleza_ingresos,
                'naturaleza_egresos' => $extractoDetalle->naturaleza_egresos,
                'naturaleza_compras' => $extractoDetalle->naturaleza_compras,
                'naturaleza_ventas' => $extractoDetalle->naturaleza_ventas,
                'naturaleza_cuenta' => $extractoDetalle->naturaleza_cuenta,
                'debito' => $extractoDetalle->debito,
                'credito' => $extractoDetalle->credito,
                'dias_cumplidos' => $extractoDetalle->dias_cumplidos,
                'detalle' => $extractoDetalle->detalle,
                'fecha_creacion' => $extractoDetalle->fecha_creacion,
                'fecha_edicion' => $extractoDetalle->fecha_edicion,
                'created_by' => $extractoDetalle->created_by,
                'updated_by' => $extractoDetalle->updated_by,
                'total_abono' => $extractoDetalle->total_abono,
                'total_facturas' => $extractoDetalle->total_facturas,
                'saldo' => $extractoDetalle->saldo,
                'detalle' => false,
                'detalle_group' => false,
            ];
        }
    }

    private function addTotalNitsData($extractosDetalle)
    {
        foreach ($extractosDetalle as $extractoDetalle) {
            $cuentaNumero = 1;
            $cuentaNueva = $extractoDetalle->cuenta.'-'.
                $extractoDetalle->id_nit.'B'.
                $extractoDetalle->documento_referencia.'A'.
                $cuentaNumero.'B';
            while ($this->hasCuentaData($cuentaNueva)) {
                $cuentaNumero++;
                $cuentaNueva = $extractoDetalle->cuenta.'-'.
                    $extractoDetalle->id_nit.'B'.
                    $extractoDetalle->documento_referencia.'A'.
                    $cuentaNumero.'B';
            }
            $this->carteraCollection[$cuentaNueva] = [
                'id_nit' => $extractoDetalle->id_nit,
                'numero_documento' => $extractoDetalle->numero_documento,
                'nombre_nit' => $extractoDetalle->nombre_nit,
                'razon_social' => $extractoDetalle->razon_social,
                'telefono_1' => $extractoDetalle->telefono_1,
                'telefono_2' => $extractoDetalle->telefono_2,
                'email' => $extractoDetalle->email,
                'direccion' => $extractoDetalle->direccion,
                'plazo' => $extractoDetalle->plazo,
                'id_cuenta' => $extractoDetalle->id_cuenta,
                'cuenta' => $extractoDetalle->cuenta,
                'nombre_cuenta' => $extractoDetalle->nombre_cuenta,
                'documento_referencia' => $extractoDetalle->documento_referencia,
                'id_centro_costos' => $extractoDetalle->id_centro_costos,
                'codigo_cecos' => $extractoDetalle->codigo_cecos,
                'nombre_cecos' => $extractoDetalle->nombre_cecos,
                'id_comprobante' => $extractoDetalle->id_comprobante,
                'codigo_comprobante' => $extractoDetalle->codigo_comprobante,
                'nombre_comprobante' => $extractoDetalle->nombre_comprobante,
                'tipo_comprobante' => $extractoDetalle->tipo_comprobante,
                'consecutivo' => $extractoDetalle->consecutivo,
                'concepto' => $extractoDetalle->concepto,
                'fecha_manual' => $extractoDetalle->fecha_manual,
                'created_at' => $extractoDetalle->created_at,
                'naturaleza_ingresos' => $extractoDetalle->naturaleza_ingresos,
                'naturaleza_egresos' => $extractoDetalle->naturaleza_egresos,
                'naturaleza_compras' => $extractoDetalle->naturaleza_compras,
                'naturaleza_ventas' => $extractoDetalle->naturaleza_ventas,
                'naturaleza_cuenta' => $extractoDetalle->naturaleza_cuenta,
                'debito' => $extractoDetalle->debito,
                'credito' => $extractoDetalle->credito,
                'dias_cumplidos' => $extractoDetalle->dias_cumplidos,
                'detalle' => $extractoDetalle->detalle,
                'fecha_creacion' => $extractoDetalle->fecha_creacion,
                'fecha_edicion' => $extractoDetalle->fecha_edicion,
                'created_by' => $extractoDetalle->created_by,
                'updated_by' => $extractoDetalle->updated_by,
                'total_abono' => $extractoDetalle->total_abono,
                'total_facturas' => $extractoDetalle->total_facturas,
                'saldo' => $extractoDetalle->saldo,
                'detalle' => false,
                'detalle_group' => 'nits',
            ];
        }
    }

    private function sumCuentaData($cuenta, $extracto)
    {
        // $this->carteraCollection[$cuenta]['saldo_anterior']+= number_format((float)$auxiliar->saldo_anterior, 2, '.', '');
        $this->carteraCollection[$cuenta]['total_abono']+= number_format((float)$extracto->total_abono, 2, '.', '');
        $this->carteraCollection[$cuenta]['total_facturas']+= number_format((float)$extracto->total_facturas, 2, '.', '');
        $this->carteraCollection[$cuenta]['saldo']+= number_format((float)$extracto->saldo, 2, '.', '');
    }

    private function hasCuentaData($cuenta)
	{
		return isset($this->carteraCollection[$cuenta]);
	}
}
