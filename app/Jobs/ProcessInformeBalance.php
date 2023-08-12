<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\PrivateMessageEvent;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfBalance;

class ProcessInformeBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_balance;
    public $balances = [];
    public $balanceCollection = [];

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
    }

    public function handle()
    {
		$empresa = Empresa::find($this->id_empresa);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        DB::connection('informes')->beginTransaction();
        
        try {
            $balance = InfBalance::create([
				'id_empresa' => $this->id_empresa,
				'fecha_desde' => $this->request['fecha_desde'],
				'fecha_hasta' => $this->request['fecha_hasta'],
				'id_cuenta' => $this->request['id_cuenta'],
				'nivel' => $this->request['nivel'],
			]);

            $this->id_balance = $balance->id;

            $this->documentosBalance();
            $this->totalesDocumentosBalance();

            foreach (array_chunk($this->balanceCollection,233) as $balanceCollection){
                DB::connection('informes')
                    ->table('inf_balance_detalles')
                    ->insert(array_values($balanceCollection));
            }

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-balance-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Balance generado',
                'id_balance' => $this->id_balance,
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
    }

    private function documentosBalance()
    {
        $query = $this->balanceDocumentosQuery();
        $query->unionAll($this->balanceAnteriorQuery());
        
        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS balance"))
            ->mergeBindings($query)
            ->select(
                'id_cuenta',
                'cuenta',
                'nombre_cuenta',
                'created_by',
                'updated_by',
                'fecha_manual',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw('SUM(documentos_totales) AS documentos_totales')
            )
            ->groupByRaw('cuenta')
            ->orderByRaw('cuenta')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $cuentasAsociadas = $this->getCuentas($documento->cuenta); //return ARRAY PADRES CUENTA

                    foreach ($cuentasAsociadas as $cuenta) {
                        $addCuenta = false;

                        if($this->request['nivel'] == 1 && strlen($cuenta) < 3) $addCuenta = true;
                        if($this->request['nivel'] == 2 && strlen($cuenta) < 5) $addCuenta = true;
                        if($this->request['nivel'] == 3) $addCuenta = true;

                        if($addCuenta) {
                            if ($this->hasCuentaData($cuenta)) $this->sumCuentaData($cuenta, $documento);
                            else $this->newCuentaData($cuenta, $documento);
                        }
                    }
                });
            });
    }

    private function totalesDocumentosBalance()
    {
        $query = $this->balanceDocumentosQuery();
        $query->unionAll($this->balanceAnteriorQuery());

        $totales = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS balance"))
            ->mergeBindings($query)
            ->select(
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_final) AS saldo_final'),
                DB::raw('SUM(documentos_totales) AS documentos_totales')
            )
            ->orderByRaw('cuenta')
            ->get();

        $this->balanceCollection['9999'] = [
            'id_balance' => $this->id_balance,
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'auxiliar' => '',
            'saldo_anterior' => number_format((float)$totales[0]->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$totales[0]->debito, 2, '.', ''),
            'credito' => number_format((float)$totales[0]->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$totales[0]->saldo_final, 2, '.', ''),
            'documentos_totales' => $totales[0]->documentos_totales,
        ];
    }

    private function balanceDocumentosQuery()
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.created_by",
                "DG.updated_by",
                "DG.fecha_manual",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("SUM(DG.debito) AS debito"),
                DB::raw("SUM(DG.credito) AS credito"),
                DB::raw("SUM(DG.debito) - SUM(DG.credito) AS saldo_final"),
                DB::raw("COUNT(DG.id) AS documentos_totales")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			})
            ->groupByRaw('DG.id_cuenta');

        return $documentosQuery;
    }

    private function balanceAnteriorQuery()
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.created_by",
                "DG.updated_by",
                "DG.fecha_manual",
                DB::raw("SUM(debito) - SUM(credito) AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("COUNT(DG.id) AS documentos_totales")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
                $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->groupByRaw('DG.id_cuenta');

        return $documentosQuery;
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

        if(!$cuentaData){
            return;
        }

        $this->balanceCollection[$cuenta] = [
            'id_balance' => $this->id_balance,
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'auxiliar' => $cuentaData->auxiliar,
            'saldo_anterior' => number_format((float)$balance->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$balance->debito, 2, '.', ''),
            'credito' => number_format((float)$balance->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$balance->saldo_final, 2, '.', ''),
            'documentos_totales' => $balance->documentos_totales
        ];
    }

    private function sumCuentaData($cuenta, $balance)
    {
        $this->balanceCollection[$cuenta]['saldo_anterior']+= number_format((float)$balance->saldo_anterior, 2, '.', '');
        $this->balanceCollection[$cuenta]['debito']+= number_format((float)$balance->debito, 2, '.', '');
        $this->balanceCollection[$cuenta]['credito']+= number_format((float)$balance->credito, 2, '.', '');
        $this->balanceCollection[$cuenta]['saldo_final']+= number_format((float)$balance->saldo_final, 2, '.', '');
    }

    private function hasCuentaData($cuenta)
	{
		return isset($this->balanceCollection[$cuenta]);
	}
}
