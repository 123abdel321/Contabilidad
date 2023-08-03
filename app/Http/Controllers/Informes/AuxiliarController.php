<?php

namespace App\Http\Controllers\Informes;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\AuxiliarExport;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\PlanCuentas;

class AuxiliarController extends Controller
{
    public $auxiliarCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.auxiliar.auxiliar-view');
    }

    public function generate(Request $request)
    {
        if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
			return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Por favor ingresa un rango de fechas válido para iniciar la busqueda.'
            ]);
		}

        $auxiliares = DB::connection('sam')->select($this->queryAuxiliares($request));
        
        foreach ($auxiliares as $auxiliar) {
            $cuentasAsociadas = $this->getCuentas($auxiliar->cuenta); //return ARRAY PADRES CUENTA
            
            foreach ($cuentasAsociadas as $cuenta) {
                if ($this->hasCuentaData($cuenta)) {
                    $this->sumCuentaData($cuenta, $auxiliar);
                } else {
                    $this->newCuentaData($cuenta, $auxiliar, $cuentasAsociadas);
                }
            }
        }

        $this->addTotalsData($auxiliares);
        $this->addDetilsData($request, $auxiliares);
        $this->addTotalNits($auxiliares);
        $this->addTotalNitsData($auxiliares);

		ksort($this->auxiliarCollection, SORT_STRING | SORT_FLAG_CASE);

        return response()->json([
            'success'=>	true,
            'data' => array_values($this->auxiliarCollection),
            'message'=> 'Auxiliar generado con exito!'
        ]);
    }

    private function queryAuxiliares($request)
    {
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        $wheres = '';

        if($request->has('id_cuenta') && $request->get('id_cuenta')){
            $planCuentas = PlanCuentas::find($request->get('id_cuenta'));
            $wheres.= ' AND PC.cuenta LIKE "'.$planCuentas->cuenta.'%"';
        }

        if($request->has('id_nit') && $request->get('id_nit')){
            $wheres.= ' AND N.id = '.$request->get('id_nit');
        }

        if($request->has('tipo_documento') && $request->get('tipo_documento') == 'anuladas') {
            $wheres.= ' AND DG.anulado = 1';
        }

        $query = "SELECT
                id_nit,
                numero_documento,
                nombre_nit,
                razon_social,
                id_cuenta,
                cuenta,
                nombre_cuenta,
                documento_referencia,
                id_centro_costos,
                codigo_cecos,
                nombre_cecos,
                id_comprobante,
                codigo_comprobante,
                nombre_comprobante,
                consecutivo,
                concepto,
                fecha_manual,
                created_at,
                fecha_creacion,
                fecha_edicion,
                created_by,
                updated_by,
                anulado,
                SUM(saldo_anterior) AS saldo_anterior,
                SUM(debito) AS debito,
                SUM(credito) AS credito,
                SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final,
                SUM(total_columnas) AS total_columnas
            FROM ((
                SELECT
                    N.id AS id_nit,
                    N.numero_documento,
                    CASE
                        WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                        WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                        ELSE NULL
                    END AS nombre_nit,
                    N.razon_social,
                    PC.id AS id_cuenta,
                    PC.cuenta,
                    PC.nombre AS nombre_cuenta,
                    DG.documento_referencia,
                    DG.id_centro_costos,
                    CC.codigo AS codigo_cecos,
                    CC.nombre AS nombre_cecos,
                    DG.id AS id_comprobante,
                    CO.codigo AS codigo_comprobante,
                    CO.nombre AS nombre_comprobante,
                    DG.consecutivo,
                    DG.concepto,
                    DG.fecha_manual,
                    DG.created_at,
                    DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion,
                    DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion,
                    DG.created_by,
                    DG.updated_by,
                    DG.anulado,
                    SUM(debito) - SUM(credito) AS saldo_anterior,
                    0 AS debito,
                    0 AS credito,
                    0 AS saldo_final,
                    1 AS total_columnas
                FROM
                    documentos_generals DG
                    
                LEFT JOIN nits N ON DG.id_nit = N.id
                LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
                LEFT JOIN centro_costos CC ON DG.id_centro_costos = CC.id
                LEFT JOIN comprobantes CO ON DG.id_comprobante = CO.id
                    
                WHERE DG.fecha_manual < '$fechaDesde'
                    $wheres
                    
                GROUP BY DG.id_cuenta, DG.id_nit, DG.documento_referencia
                )
                    UNION
                (
                    SELECT
                        N.id AS id_nit,
                        N.numero_documento,
                        CASE
                            WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                            WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                            ELSE NULL
                        END AS nombre_nit,
                        N.razon_social,
                        PC.id AS id_cuenta,
                        PC.cuenta,
                        PC.nombre AS nombre_cuenta,
                        DG.documento_referencia,
                        DG.id_centro_costos,
                        CC.codigo AS codigo_cecos,
                        CC.nombre AS nombre_cecos,
                        DG.id_comprobante AS id_comprobante,
                        CO.codigo AS codigo_comprobante,
                        CO.nombre AS nombre_comprobante,
                        DG.consecutivo,
                        DG.concepto,
                        DG.fecha_manual,
                        DG.created_at,
                        DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion,
                        DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion,
                        DG.created_by,
                        DG.updated_by,
                        DG.anulado,
                        0 AS saldo_anterior,
                        SUM(DG.debito) AS debito,
                        SUM(DG.credito) AS credito,
                        SUM(DG.debito) - SUM(DG.credito) AS saldo_final,
                        COUNT(DG.id) AS total_columnas
                    FROM
                        documentos_generals DG
                        
                    LEFT JOIN nits N ON DG.id_nit = N.id
                    LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
                    LEFT JOIN centro_costos CC ON DG.id_centro_costos = CC.id
                    LEFT JOIN comprobantes CO ON DG.id_comprobante = CO.id
                        
                    WHERE DG.fecha_manual >= '$fechaDesde'
                        AND DG.fecha_manual <= '$fechaHasta'
                        $wheres
                        
                    GROUP BY DG.id_cuenta, DG.id_nit, DG.documento_referencia
                )) AS auxiliar
            GROUP BY id_cuenta, id_nit, documento_referencia
            ORDER BY cuenta, id_nit, documento_referencia, created_at
        ";
        
        return $query;
    }

    private function queryAuxiliaresDetalle($request, $auxiliares)
    {
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        $wheres = ' AND DG.id_cuenta = '.$auxiliares->id_cuenta;

        if($auxiliares->id_nit){
            $wheres.= ' AND DG.id_nit = '.$auxiliares->id_nit;
        }

        if($auxiliares->documento_referencia){
            $wheres.= ' AND DG.documento_referencia = '.$auxiliares->documento_referencia;
        }

        return "SELECT
            id_nit,
            numero_documento,
            nombre_nit,
            razon_social,
            id_cuenta,
            cuenta,
            nombre_cuenta,
            documento_referencia,
            id_centro_costos,
            codigo_cecos,
            nombre_cecos,
            id_comprobante,
            codigo_comprobante,
            nombre_comprobante,
            consecutivo,
            concepto,
            fecha_manual,
            created_at,
            fecha_creacion,
            fecha_edicion,
            created_by,
            updated_by,
            anulado,
            saldo_anterior,
            debito,
            credito,
            saldo_final
        FROM (SELECT
                N.id AS id_nit,
                N.numero_documento,
                CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END AS nombre_nit,
                N.razon_social,
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
                DG.consecutivo,
                DG.concepto,
                DG.fecha_manual,
                DG.created_at,
                DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion,
                DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion,
                DG.created_by,
                DG.updated_by,
                DG.anulado,
                0 AS saldo_anterior,
                DG.debito,
                DG.credito,
                DG.debito - DG.credito AS saldo_final
            FROM
                documentos_generals DG
                
            LEFT JOIN nits N ON DG.id_nit = N.id
            LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
            LEFT JOIN centro_costos CC ON DG.id_centro_costos = CC.id
            LEFT JOIN comprobantes CO ON DG.id_comprobante = CO.id
                
            WHERE DG.fecha_manual >= '$fechaDesde'
                AND DG.fecha_manual <= '$fechaHasta'
                $wheres
            
            ORDER BY cuenta, id_nit, documento_referencia, created_at
            ) AS auxiliar
            -- ORDER BY cuenta DESC
            ORDER BY cuenta, id_nit, documento_referencia, created_at
        ";
    }

    public function exportExcel(Request $request)
    {
        return (new AuxiliarExport($request))->download('auxiliar.xlsx');
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

    private function newCuentaData($cuenta, $auxiliar, $cuentasAsociadas)
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
        $this->auxiliarCollection[$cuenta] = [
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'id_centro_costos' => '',
            'codigo_cecos' => '',
            'nombre_cecos' =>  '',
            'documento_referencia' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => '',
            'fecha_edicion' => '',
            'created_by' => '',
            'updated_by' => '',
            'anulado' => '',
            'saldo_anterior' => number_format((float)$auxiliar->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$auxiliar->debito, 2, '.', ''),
            'credito' => number_format((float)$auxiliar->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$auxiliar->saldo_final, 2, '.', ''),
            'detalle' => $detalle,
            'detalle_group' => $detalleGroup,
        ];
    }

    private function sumCuentaData($cuenta, $auxiliar)
    {
        $this->auxiliarCollection[$cuenta]['saldo_anterior']+= number_format((float)$auxiliar->saldo_anterior, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['debito']+= number_format((float)$auxiliar->debito, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['credito']+= number_format((float)$auxiliar->credito, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['saldo_final']+= number_format((float)$auxiliar->saldo_final, 2, '.', '');
    }

    private function addTotalsData($auxiliares)
    {
        $debito = 0;
        $credito = 0;
        $saldo_anterior = 0;
        $saldo_final = 0;

        foreach ($auxiliares as $auxiliar) {
            $debito+= number_format((float)$auxiliar->debito, 2, '.', '');
            $credito+= number_format((float)$auxiliar->credito, 2, '.', '');
            $saldo_final+= number_format((float)$auxiliar->saldo_final, 2, '.', '');
            $saldo_anterior+= number_format((float)$auxiliar->saldo_anterior, 2, '.', '');
        }

        $this->auxiliarCollection['9999'] = [
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'id_centro_costos' => '',
            'codigo_cecos' => '',
            'nombre_cecos' =>  '',
            'documento_referencia' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => '',
            'fecha_edicion' => '',
            'created_by' => '',
            'updated_by' => '',
            'anulado' => '',
            'saldo_anterior' => $saldo_anterior,
            'debito' => $debito,
            'credito' => $credito,
            'saldo_final' => $saldo_final,
            'detalle' => false,
            'detalle_group' => false,
        ];
    }

    private function addDetilsData($request, $auxiliares)
    {
        foreach ($auxiliares as $auxiliar) {

            if($auxiliar->total_columnas > 0 ) {
                $auxiliaresDetalle = DB::connection('sam')->select($this->queryAuxiliaresDetalle($request, $auxiliar));
        
                foreach ($auxiliaresDetalle as $auxiliarDetalle) {
                    $cuentaNumero = 1;
                    $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                        $auxiliarDetalle->id_nit.'B'.
                        $auxiliarDetalle->documento_referencia.'B'.
                        $cuentaNumero.'B';
                    while ($this->hasCuentaData($cuentaNueva)) {
                        $cuentaNumero++;
                        $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                            $auxiliarDetalle->id_nit.'B'.
                            $auxiliarDetalle->documento_referencia.'B'.
                            $cuentaNumero.'B';
                    }
                    $this->auxiliarCollection[$cuentaNueva] = [
                        'id_nit' => $auxiliarDetalle->id_nit,
                        'numero_documento' => $auxiliarDetalle->numero_documento,
                        'nombre_nit' => $auxiliarDetalle->nombre_nit,
                        'razon_social' => $auxiliarDetalle->razon_social,
                        'id_cuenta' => $auxiliarDetalle->id_cuenta,
                        'cuenta' => $auxiliarDetalle->cuenta,
                        'nombre_cuenta' => $auxiliarDetalle->nombre_cuenta,
                        'documento_referencia' => $auxiliarDetalle->documento_referencia,
                        'saldo_anterior' => $auxiliarDetalle->saldo_anterior,
                        'id_centro_costos' => $auxiliarDetalle->id_centro_costos,
                        'id_comprobante' => $auxiliarDetalle->id_comprobante,
                        'codigo_comprobante' => $auxiliarDetalle->codigo_comprobante,
                        'nombre_comprobante' => $auxiliarDetalle->nombre_comprobante,
                        'codigo_cecos' => $auxiliarDetalle->codigo_cecos,
                        'nombre_cecos' =>  $auxiliarDetalle->nombre_cecos,
                        'consecutivo' => $auxiliarDetalle->consecutivo,
                        'concepto' => $auxiliarDetalle->concepto,
                        'fecha_manual' => $auxiliarDetalle->fecha_manual,
                        'fecha_creacion' => $auxiliarDetalle->fecha_creacion,
                        'fecha_edicion' => $auxiliarDetalle->fecha_edicion,
                        'created_by' => $auxiliarDetalle->created_by,
                        'updated_by' => $auxiliarDetalle->updated_by,
                        'anulado' => $auxiliarDetalle->anulado,
                        'debito' => $auxiliarDetalle->debito,
                        'credito' => $auxiliarDetalle->credito,
                        'saldo_final' => '0',
                        'detalle' => false,
                        'detalle_group' => false,
                    ];
                }
            }
        }
    }

    private function addTotalNitsData($auxiliaresDetalle)
    {
        foreach ($auxiliaresDetalle as $auxiliarDetalle) {
            $cuentaNumero = 1;
            $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                $auxiliarDetalle->id_nit.'B'.
                $auxiliarDetalle->documento_referencia.'B'.
                $cuentaNumero.'A';
            while ($this->hasCuentaData($cuentaNueva)) {
                $cuentaNumero++;
                $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                    $auxiliarDetalle->id_nit.'B'.
                    $auxiliarDetalle->documento_referencia.'B'.
                    $cuentaNumero.'A';
            }
            $this->auxiliarCollection[$cuentaNueva] = [
                'id_nit' => $auxiliarDetalle->id_nit,
                'numero_documento' => $auxiliarDetalle->numero_documento,
                'nombre_nit' => $auxiliarDetalle->nombre_nit,
                'razon_social' => $auxiliarDetalle->razon_social,
                'id_cuenta' => $auxiliarDetalle->id_cuenta,
                'cuenta' => $auxiliarDetalle->cuenta,
                'nombre_cuenta' => $auxiliarDetalle->nombre_cuenta,
                'documento_referencia' => $auxiliarDetalle->documento_referencia,
                'saldo_anterior' => $auxiliarDetalle->saldo_anterior,
                'id_centro_costos' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->id_centro_costos : '',
                'id_comprobante' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->id_comprobante : '',
                'codigo_comprobante' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->codigo_comprobante : '',
                'nombre_comprobante' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->nombre_comprobante : '',
                'codigo_cecos' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->codigo_cecos : '',
                'nombre_cecos' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->nombre_cecos : '',
                'consecutivo' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->consecutivo : '',
                'concepto' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->concepto : '',
                'fecha_manual' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->fecha_manual : '',
                'fecha_creacion' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->fecha_creacion : '',
                'fecha_edicion' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->fecha_edicion : '',
                'created_by' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->created_by : '',
                'updated_by' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->updated_by : '',
                'anulado' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->anulado : '',
                'debito' => $auxiliarDetalle->debito,
                'credito' => $auxiliarDetalle->credito,
                'saldo_final' => $auxiliarDetalle->saldo_final,
                'detalle' => false,
                'detalle_group' => 'nits',
            ];
            // dd($this->auxiliarCollection);
        }
    }

    private function addTotalNits($auxiliaresDetalle)
    {
        $collecionTotalNits = [];
        foreach ($auxiliaresDetalle as $auxiliarDetalle) {

            $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                $auxiliarDetalle->id_nit.'A';

            $collecionTotalNits[$cuentaNueva][] = [
                'id_nit' => $auxiliarDetalle->id_nit,
                'numero_documento' => $auxiliarDetalle->numero_documento,
                'nombre_nit' => $auxiliarDetalle->nombre_nit,
                'razon_social' => $auxiliarDetalle->razon_social,
                'id_cuenta' => $auxiliarDetalle->id_cuenta,
                'cuenta' => $auxiliarDetalle->cuenta,
                'nombre_cuenta' => $auxiliarDetalle->nombre_cuenta,
                'documento_referencia' => '',
                'saldo_anterior' => $auxiliarDetalle->saldo_anterior,
                'id_centro_costos' => '',
                'id_comprobante' => '',
                'codigo_comprobante' => '',
                'nombre_comprobante' => '',
                'codigo_cecos' => '',
                'nombre_cecos' =>  '',
                'consecutivo' => '',
                'concepto' => '',
                'fecha_manual' => '',
                'fecha_creacion' => '',
                'fecha_edicion' => '',
                'created_by' => '',
                'updated_by' => '',
                'anulado' => '',
                'debito' => $auxiliarDetalle->debito,
                'credito' => $auxiliarDetalle->credito,
                'saldo_final' => $auxiliarDetalle->saldo_final,
                'detalle' => false,
                'detalle_group' => 'nits-totales',
            ];
        }

        foreach ($collecionTotalNits as $key => $collecion) {
            if(count($collecion) > 1) {
                $debito = 0;
                $credito = 0;
                $saldo_final = 0;
                $saldo_anterior = 0;
                foreach ($collecion as $data) {
                    // dd('$data: ',$data);
                    $debito+= $data['debito'];
                    $credito+= $data['credito'];
                    $saldo_final+= $data['saldo_final'];
                    $saldo_anterior+= $data['saldo_anterior'];
                }
                $this->auxiliarCollection[$key] = [
                    'id_nit' => $collecion[0]['id_nit'],
                    'numero_documento' => $collecion[0]['numero_documento'],
                    'nombre_nit' => $collecion[0]['nombre_nit'],
                    'razon_social' => $collecion[0]['razon_social'],
                    'id_cuenta' => $collecion[0]['id_cuenta'],
                    'cuenta' => $collecion[0]['cuenta'],
                    'nombre_cuenta' => $collecion[0]['nombre_cuenta'],
                    'documento_referencia' => $collecion[0]['documento_referencia'],
                    'saldo_anterior' => $saldo_anterior,
                    'id_centro_costos' => '',
                    'id_comprobante' => '',
                    'codigo_comprobante' => '',
                    'nombre_comprobante' => '',
                    'codigo_cecos' => '',
                    'nombre_cecos' =>  '',
                    'consecutivo' => '',
                    'concepto' => '',
                    'fecha_manual' => '',
                    'fecha_creacion' => '',
                    'fecha_edicion' => '',
                    'created_by' => '',
                    'updated_by' => '',
                    'anulado' => '',
                    'debito' => $debito,
                    'credito' => $credito,
                    'saldo_final' => $saldo_final,
                    'detalle' => false,
                    'detalle_group' => 'nits-totales',
                ];
            }
        }
    }

	private function hasCuentaData($cuenta)
	{
		return isset($this->auxiliarCollection[$cuenta]);
	}

}
