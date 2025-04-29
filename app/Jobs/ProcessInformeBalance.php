<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
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
use App\Models\Sistema\VariablesEntorno;

class ProcessInformeBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
    public $id_empresa;
    public $id_balance;
    public $cuentaPerdida;
    public $cuentaUtilidad;
    public $balanceCollection = [];

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
    }

    public function handle()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $empresa = Empresa::find($this->id_empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        DB::connection('informes')->beginTransaction();

        if ($this->request['tipo'] == '3') {
            $this->cuentaPerdida = VariablesEntorno::whereNombre('cuenta_perdida')->first()->valor;
            $this->cuentaUtilidad = VariablesEntorno::whereNombre('cuenta_utilidad')->first()->valor;

            $this->cuentaPerdida = PlanCuentas::where('cuenta', $this->cuentaPerdida)->first();
            $this->cuentaUtilidad = PlanCuentas::where('cuenta', $this->cuentaUtilidad)->first();
        }

        try {
            $balance = InfBalance::create([
                'id_empresa' => $this->id_empresa,
                'fecha_desde' => $this->request['fecha_desde'],
                'fecha_hasta' => $this->request['fecha_hasta'],
                'cuenta_hasta' => $this->request['cuenta_hasta'],
                'cuenta_desde' => $this->request['cuenta_desde'],
                'id_nit' => $this->request['id_nit'],
                'tipo' => $this->request['tipo'],
                'nivel' => $this->request['nivel'],
            ]);

            $this->id_balance = $balance->id;

            $this->documentosBalance();
            if ($this->request['tipo'] == '2') $this->tercerosBalance();
            if ($this->request['tipo'] == '3') $this->generalBalance();
            if ($this->request['tipo'] == '3') $this->totalesGeneralBalance();
            else $this->totalesDocumentosBalance();

            ksort($this->balanceCollection, SORT_STRING | SORT_FLAG_CASE);

            foreach (array_chunk($this->balanceCollection, 233) as $balanceCollection) {
                DB::connection('informes')
                    ->table('inf_balance_detalles')
                    ->insert(array_values($balanceCollection));
            }
            
            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-balance-' . $empresa->token_db . '_' . $this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Balance generado',
                'id_balance' => $this->id_balance,
                'autoclose' => false
            ]));

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;

            Log::info("Tiempo de ejecución del informe: {$executionTime} segundos");
            Log::info("Consumo de memoria del informe: {$memoryUsage} bytes");

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
                foreach ($documentos as $documento) {
                    $cuentasAsociadas = $this->getCuentas($documento->cuenta);

                    foreach ($cuentasAsociadas as $cuenta) {
                        if ($this->mustAddAccount($cuenta)) {
                            if ($this->hasCuentaData($cuenta)) $this->sumCuentaData($cuenta, $documento);
                            else $this->newCuentaData($cuenta, $documento);
                        }
                    }
                }
                unset($documentos); // Liberar memoria
            });
    }

    private function tercerosBalance()
    {
        $query = $this->balanceDocumentosQuery();
        $query->unionAll($this->balanceAnteriorQuery());

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS balance"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
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
            ->groupByRaw('cuenta, id_nit')
            ->orderByRaw('nombre_nit')
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $nit) {
                    $this->newNitData($nit);
                }
                unset($documentos); // Liberar memoria
            });
    }

    private function generalBalance()
    {
        $query = $this->balanceDocumentosGeneralQuery();
        $query->unionAll($this->balanceAnteriorGeneralQuery());

        $totales = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS balance"))
            ->mergeBindings($query)
            ->select(
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(saldo_final) AS saldo_final'),
                DB::raw('SUM(documentos_totales) AS documentos_totales')
            )
            ->orderByRaw('cuenta')
            ->first();

        $cuentasAsociadas = $totales->saldo_final > 0
            ? $this->getCuentas($this->cuentaUtilidad->cuenta)
            : $this->getCuentas($this->cuentaPerdida->cuenta);

        foreach ($cuentasAsociadas as $cuenta) {
            if ($this->mustAddAccount($cuenta)) {
                if ($this->hasCuentaData($cuenta)) $this->sumCuentaData($cuenta, $totales);
                else $this->newCuentaData($cuenta, $totales);
            }
        }
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

        $total = $totales[0]->saldo_anterior + $totales[0]->debito - $totales[0]->credito;
        $this->balanceCollection['9999'] = [
            'id_balance' => $this->id_balance,
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'auxiliar' => '',
            'saldo_anterior' => number_format((float)$totales[0]->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$totales[0]->debito, 2, '.', ''),
            'credito' => number_format((float)$totales[0]->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$total, 2, '.', ''),
            'documentos_totales' => $totales[0]->documentos_totales,
        ];
    }

    private function totalesGeneralBalance()
    {
        $query = $this->balanceDocumentosGeneralQuery(true);
        $query->unionAll($this->balanceAnteriorGeneralQuery(true));

        $totales = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS balance"))
            ->mergeBindings($query)
            ->select(
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(saldo_final) AS saldo_final'),
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
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "DG.id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.created_by",
                "DG.updated_by",
                "DG.fecha_manual",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS documentos_totales")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->when(isset($this->request['cuenta_desde']), function ($query) {
                $query->where('PC.cuenta', '>=', (string)$this->request['cuenta_desde']);
            })
            ->when(isset($this->request['cuenta_hasta']), function ($query) {
                $query->where('PC.cuenta', '<=', (string)$this->request['cuenta_hasta'])
                    ->orWhere('PC.cuenta', 'LIKE', (string)$this->request['cuenta_hasta'] . '%');
            })
            ->when(isset($this->request['id_nit']), function ($query) {
                $query->where('DG.id_nit', 'LIKE', $this->request['id_nit'] . '%');
            });

        return $documentosQuery;
    }

    private function balanceAnteriorQuery()
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "DG.id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.created_by",
                "DG.updated_by",
                "DG.fecha_manual",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS documentos_totales")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when(isset($this->request['cuenta_desde']), function ($query) {
                $query->where('PC.cuenta', '>=', (string)$this->request['cuenta_desde']);
            })
            ->when(isset($this->request['cuenta_hasta']), function ($query) {
                $query->where('PC.cuenta', '<=', (string)$this->request['cuenta_hasta'])
                    ->orWhere('PC.cuenta', 'LIKE', (string)$this->request['cuenta_hasta'] . '%');
            })
            ->when(isset($this->request['id_nit']), function ($query) {
                $query->where('DG.id_nit', 'LIKE', $this->request['id_nit'] . '%');
            });

        return $documentosQuery;
    }

    private function balanceDocumentosGeneralQuery($total = false)
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "DG.id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.created_by",
                "DG.updated_by",
                "DG.fecha_manual",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS documentos_totales")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->when(!$total, function ($query) {
                $query->where('PC.cuenta', '>=', '4')
                    ->where('PC.cuenta', '<=', '7')
                    ->orWhere('PC.cuenta', 'LIKE', '7%');
            })
            ->when(isset($this->request['id_nit']), function ($query) {
                $query->where('DG.id_nit', 'LIKE', $this->request['id_nit'] . '%');
            });

        return $documentosQuery;
    }

    private function balanceAnteriorGeneralQuery($total = false)
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "DG.id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.created_by",
                "DG.updated_by",
                "DG.fecha_manual",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS documentos_totales")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when(!$total, function ($query) {
                $query->where('PC.cuenta', '>=', '4')
                    ->where('PC.cuenta', '<=', '7')
                    ->orWhere('PC.cuenta', 'LIKE', '7%');
            })
            ->when(isset($this->request['id_nit']), function ($query) {
                $query->where('DG.id_nit', 'LIKE', $this->request['id_nit'] . '%');
            });

        return $documentosQuery;
    }

    private function getCuentas($cuenta)
    {
        $dataCuentas = NULL;

        if (strlen($cuenta) > 6) {
            $dataCuentas = [
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 4),
                mb_substr($cuenta, 0, 6),
                $cuenta,
            ];
        } elseif (strlen($cuenta) > 4) {
            $dataCuentas = [
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 4),
                $cuenta,
            ];
        } elseif (strlen($cuenta) > 2) {
            $dataCuentas = [
                mb_substr($cuenta, 0, 1),
                mb_substr($cuenta, 0, 2),
                $cuenta,
            ];
        } elseif (strlen($cuenta) > 1) {
            $dataCuentas = [
                mb_substr($cuenta, 0, 1),
                $cuenta,
            ];
        } else {
            $dataCuentas = [
                $cuenta,
            ];
        }

        return $dataCuentas;
    }

    private function newCuentaData($cuenta, $balance)
    {
        $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();

        if (!$cuentaData) {
            return;
        }

        $this->balanceCollection[$cuenta] = [
            'id_balance' => $this->id_balance,
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'auxiliar' => $this->request['tipo'] == '2' ? 0 : $cuentaData->auxiliar,
            'saldo_anterior' => number_format((float)$balance->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$balance->debito, 2, '.', ''),
            'credito' => number_format((float)$balance->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$balance->saldo_final, 2, '.', ''),
            'documentos_totales' => $balance->documentos_totales
        ];
    }

    private function newNitData($data)
    {
        $this->balanceCollection[$data->cuenta . $data->numero_documento] = [
            'id_balance' => $this->id_balance,
            'id_cuenta' => $data->cuenta,
            'cuenta' => $data->numero_documento,
            'nombre_cuenta' => $data->nombre_nit,
            'auxiliar' => 5,
            'saldo_anterior' => number_format((float)$data->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$data->debito, 2, '.', ''),
            'credito' => number_format((float)$data->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$data->saldo_final, 2, '.', ''),
            'documentos_totales' => $data->documentos_totales
        ];
    }

    private function sumCuentaData($cuenta, $balance)
    {
        $this->balanceCollection[$cuenta]['saldo_anterior'] += number_format((float)$balance->saldo_anterior, 2, '.', '');
        $this->balanceCollection[$cuenta]['debito'] += number_format((float)$balance->debito, 2, '.', '');
        $this->balanceCollection[$cuenta]['credito'] += number_format((float)$balance->credito, 2, '.', '');
        $this->balanceCollection[$cuenta]['saldo_final'] += number_format((float)$balance->saldo_final, 2, '.', '');
    }

    private function hasCuentaData($cuenta)
    {
        return isset($this->balanceCollection[$cuenta]);
    }

    private function mustAddAccount($cuenta)
    {
        if ($this->request['nivel'] == 1 && strlen($cuenta) < 3) return true;
        if ($this->request['nivel'] == 2 && strlen($cuenta) < 5) return true;
        if ($this->request['nivel'] == 3) return true;
        return false;
    }

    public function failed(Exception $exception)
    {
        DB::connection('informes')->rollBack();

        // Si no tenemos la empresa, intentamos obtenerla
        if (!$this->empresa && $this->id_empresa) {
            $this->empresa = Empresa::find($this->id_empresa);
        }

        $token_db = $this->empresa ? $this->empresa->token_db : 'unknown';

        event(new PrivateMessageEvent(
            'informe-balance-'.$token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'error',
                'mensaje' => 'Error al generar el informe: '.$exception->getMessage(),
                'titulo' => 'Error en proceso',
                'autoclose' => false
            ]
        ));

        // Registrar el error en los logs
        logger()->error("Error en ProcessInformeBalance: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }
}
