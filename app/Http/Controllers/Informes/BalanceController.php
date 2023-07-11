<?php

namespace App\Http\Controllers\Informes;

use DB;
use Illuminate\Http\Request;
use App\Exports\BalanceExport;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\PlanCuentas;

class BalanceController extends Controller
{
    public $balanceCollection = [];

    public function index ()
    {
        return view('pages.contabilidad.balance.balance-view');
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
        
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        $wheres = '';

        if($request->has('id_cuenta') && $request->get('id_cuenta')){
            $planCuentas = PlanCuentas::find($request->get('id_cuenta'));
            $wheres.= ' AND PC.cuenta LIKE "'.$planCuentas->cuenta.'%"';
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
                SUM(saldo_anterior) AS saldo_anterior,
                SUM(debito) AS debito,
                SUM(credito) AS credito,
                SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final
            FROM ((
                SELECT
                    N.id AS id_nit,
                    N.numero_documento,
                    CONCAT(N.otros_nombres, ' ', N.primer_apellido) AS nombre_nit,
                    N.razon_social,
                    PC.id AS id_cuenta,
                    PC.cuenta,
                    PC.nombre AS nombre_cuenta,
                    DG.documento_referencia,
                    SUM(debito) - SUM(credito) AS saldo_anterior,
                    0 AS debito,
                    0 AS credito,
                    0 AS saldo_final
                FROM
                    documentos_generals DG
                    
                LEFT JOIN nits N ON DG.id_nit = N.id
                LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
                    
                WHERE DG.fecha_manual < '$fechaDesde'
                    $wheres
                    
                GROUP BY PC.cuenta
                )
                    UNION
                (
                    SELECT
                        N.id AS id_nit,
                        N.numero_documento,
                        CONCAT(N.otros_nombres, ' ', N.primer_apellido) AS nombre_nit,
                        N.razon_social,
                        PC.id AS id_cuenta,
                        PC.cuenta,
                        PC.nombre AS nombre_cuenta,
                        DG.documento_referencia,
                        0 AS saldo_anterior,
                        SUM(DG.debito) AS debito,
                        SUM(DG.credito) AS credito,
                        SUM(DG.debito) - SUM(DG.credito) AS saldo_final
                    FROM
                        documentos_generals DG
                        
                    LEFT JOIN nits N ON DG.id_nit = N.id
                    LEFT JOIN plan_cuentas PC ON DG.id_cuenta = PC.id
                        
                    WHERE DG.fecha_manual >= '$fechaDesde'
                        AND DG.fecha_manual <= '$fechaHasta'
                        $wheres
                        
                    GROUP BY PC.cuenta
                )) AS auxiliar
                GROUP BY cuenta
                ORDER BY cuenta";
                    
        $balances = DB::connection('sam')->select($query);

        $detalleCuentas = true;

        if($detalleCuentas){
            foreach ($balances as $balance) {
                $cuentasAsociadas = $this->getCuentas($balance->cuenta); //return ARRAY PADRES CUENTA
                foreach ($cuentasAsociadas as $cuenta) {
                    if ($this->hasCuentaData($cuenta)) {
                        $this->sumCuentaData($cuenta, $balance);
                    } else {
				        $this->newCuentaData($cuenta, $balance);
                    }
                }
            }
            $this->addTotalsData($balances);
            return response()->json([
                'success'=>	true,
                'data' => array_values($this->balanceCollection),
                'total' => [],
                'message'=> 'Balance generado con exito!'
            ]);
        }

        return response()->json([
    		'success'=>	true,
    		'data' => $balances,
    		'message'=> 'Balance generado con exito!'
    	]);
    }

    public function exportExcel(Request $request)
    {
        return (new BalanceExport($request))->download('balance.xlsx');
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

    private function newCuentaData($cuenta, $balance)
    {
        $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();
        
        $this->balanceCollection[$cuenta] = [
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'saldo_anterior' => number_format((float)$balance->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$balance->debito, 2, '.', ''),
            'credito' => number_format((float)$balance->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$balance->saldo_final, 2, '.', '')
        ];
    }

    private function sumCuentaData($cuenta, $balance)
    {
        $this->balanceCollection[$cuenta]['saldo_anterior']+= number_format((float)$balance->saldo_anterior, 2, '.', '');
        $this->balanceCollection[$cuenta]['debito']+= number_format((float)$balance->debito, 2, '.', '');
        $this->balanceCollection[$cuenta]['credito']+= number_format((float)$balance->credito, 2, '.', '');
        $this->balanceCollection[$cuenta]['saldo_final']+= number_format((float)$balance->saldo_final, 2, '.', '');
    }

    private function addTotalsData($balances)
    {
        $debito = 0;
        $credito = 0;
        $saldo_anterior = 0;
        $saldo_final = 0;

        foreach ($balances as $balance) {
            $debito+= number_format((float)$balance->debito, 2, '.', '');
            $credito+= number_format((float)$balance->credito, 2, '.', '');
            $saldo_final+= number_format((float)$balance->saldo_final, 2, '.', '');
            $saldo_anterior+= number_format((float)$balance->saldo_anterior, 2, '.', '');
        }

        $this->balanceCollection['9999'] = [
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'saldo_anterior' => $saldo_anterior,
            'debito' => $debito,
            'credito' => $credito,
            'saldo_final' => $saldo_final
        ];
    }

	private function hasCuentaData($cuenta)
	{
		return isset($this->balanceCollection[$cuenta]);
	}

}
