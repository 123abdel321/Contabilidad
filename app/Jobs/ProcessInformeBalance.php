<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfBalance;
use App\Models\Sistema\VariablesEntorno;

class ProcessInformeBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $empresa;
    private $request;
    private $id_usuario;
    private $id_empresa;
    private $id_balance;
    private $cuentaPerdida;
    private $cuentaUtilidad;
    private $connectionName;
    private $chunkSize = 500;

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->connectionName = 'sam_'.Str::random(8);
    }

    public function handle()
    {
        $this->empresa = Empresa::findOrFail($this->id_empresa);
        $this->setDynamicConnection($this->empresa->token_db);
        
        DB::connection('informes')->transaction(function () {
            $this->createBalanceRecord();
            
            if ($this->request['tipo'] == '3') {
                $this->loadUtilityAccounts();
            }

            $batchData = [];
            $insertBatch = function ($data) {
                DB::connection('informes')
                    ->table('inf_balance_detalles')
                    ->insert($data);
            };
            
            switch ($this->request['tipo']) {
                case '1':
                    $this->processDocumentosBalance($batchData, $insertBatch);
                    $this->processTotalesDocumentos($batchData, $insertBatch);
                    break;
                case '2':
                    $this->processTercerosBalance($batchData, $insertBatch);
                    $this->processTotalesDocumentos($batchData, $insertBatch);
                    break;
                case '3':
                    $this->processGeneralBalance($batchData, $insertBatch);
                    $this->processTotalesGeneral($batchData, $insertBatch);
                    break;
            }

            if (!empty($batchData)) {
                $insertBatch($batchData);
            }

            $this->notifySuccess();
        });
    }

    private function setDynamicConnection($database)
    {
        Config::set("database.connections.{$this->connectionName}", [
            'driver' => 'mysql',
            'host' => config("database.connections.sam.host"),
            'database' => $database,
            'username' => config("database.connections.sam.username"),
            'password' => config("database.connections.sam.password"),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        DB::purge($this->connectionName);
    }

    private function createBalanceRecord()
    {
        $this->id_balance = InfBalance::create([
            'id_empresa' => $this->id_empresa,
            'fecha_desde' => $this->request['fecha_desde'],
            'fecha_hasta' => $this->request['fecha_hasta'],
            'cuenta_hasta' => $this->request['cuenta_hasta'],
            'cuenta_desde' => $this->request['cuenta_desde'],
            'id_nit' => $this->request['id_nit'],
            'tipo' => $this->request['tipo'],
            'nivel' => $this->request['nivel'],
        ])->id;
    }

    private function loadUtilityAccounts()
    {
        $this->cuentaPerdida = PlanCuentas::where('cuenta', 
            VariablesEntorno::whereNombre('cuenta_perdida')->value('valor')
        )->first(['id', 'cuenta']);

        $this->cuentaUtilidad = PlanCuentas::where('cuenta',
            VariablesEntorno::whereNombre('cuenta_utilidad')->value('valor')
        )->first(['id', 'cuenta']);
    }

    private function processDocumentosBalance(&$batchData, $insertBatch)
    {
        $query = $this->buildBaseQuery()
            ->select(
                'id_cuenta',
                'cuenta',
                'nombre_cuenta',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(documentos_totales) AS documentos_totales')
            )
            ->groupBy('cuenta')
            ->orderBy('cuenta');

        $this->processChunkedData($query, $batchData, $insertBatch, function ($documento) {
            $cuentasAsociadas = $this->getCuentasPadre($documento->cuenta);
            
            $results = [];
            
            foreach ($cuentasAsociadas as $cuenta) {
                if ($this->shouldIncludeCuenta($cuenta)) {
                    $results[] = $this->buildCuentaData($documento, $cuenta);
                }
            }

            return $results;
        });
    }

    private function processTercerosBalance(&$batchData, $insertBatch)
    {
        $query = $this->buildBaseQuery()
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'id_cuenta',
                'cuenta',
                'nombre_cuenta',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(documentos_totales) AS documentos_totales')
            )
            ->groupBy(['cuenta', 'id_nit'])
            ->orderBy('nombre_nit')
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0');

        $this->processChunkedData($query, $batchData, $insertBatch, function ($documento) {
            return [$this->buildNitData($documento)];
        });
    }

    private function processGeneralBalance(&$batchData, $insertBatch)
    {
        $query = $this->buildGeneralQuery();
        $totales = $query->first();
        
        $cuenta = ($totales->saldo_final > 0) ? $this->cuentaUtilidad->cuenta : $this->cuentaPerdida->cuenta;
        
        foreach ($this->getCuentasPadre($cuenta) as $cuentaPadre) {
            if ($this->shouldIncludeCuenta($cuentaPadre)) {
                $batchData[] = $this->buildCuentaData($totales, $cuentaPadre);
                $this->checkBatchSize($batchData, $insertBatch);
            }
        }
    }

    private function processTotalesDocumentos(&$batchData, $insertBatch)
    {
        // Primero obtenemos los totales del período actual
        $totalesPeriodo = DB::connection($this->connectionName)
            ->table('documentos_generals AS DG')
            ->join('plan_cuentas AS PC', 'DG.id_cuenta', '=', 'PC.id')
            ->where('anulado', 0)
            ->whereBetween('DG.fecha_manual', [$this->request['fecha_desde'], $this->request['fecha_hasta']])
            ->select(
                DB::raw('0 AS saldo_anterior'),
                DB::raw('SUM(DG.debito) AS debito'),
                DB::raw('SUM(DG.credito) AS credito'),
                DB::raw('COUNT(*) AS documentos_totales')
            )
            ->first();

        // Luego obtenemos los saldos anteriores
        $totalesAnterior = DB::connection($this->connectionName)
            ->table('documentos_generals AS DG')
            ->join('plan_cuentas AS PC', 'DG.id_cuenta', '=', 'PC.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->select(
                DB::raw('SUM(DG.debito - DG.credito) AS saldo_anterior'),
                DB::raw('0 AS debito'),
                DB::raw('0 AS credito'),
                DB::raw('COUNT(*) AS documentos_totales')
            )
            ->first();

        // Combinamos los resultados
        $totales = (object)[
            'saldo_anterior' => $totalesAnterior->saldo_anterior ?? 0,
            'debito' => $totalesPeriodo->debito ?? 0,
            'credito' => $totalesPeriodo->credito ?? 0,
            'documentos_totales' => ($totalesPeriodo->documentos_totales ?? 0) + ($totalesAnterior->documentos_totales ?? 0)
        ];

        $total = $totales->saldo_anterior + $totales->debito - $totales->credito;
        
        $batchData[] = [
            'id_balance' => $this->id_balance,
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'auxiliar' => '',
            'saldo_anterior' => number_format((float)$totales->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$totales->debito, 2, '.', ''),
            'credito' => number_format((float)$totales->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$total, 2, '.', ''),
            'documentos_totales' => $totales->documentos_totales
        ];
    }

    private function processTotalesGeneral(&$batchData, $insertBatch)
    {
        $totales = $this->buildGeneralQuery(true)->first();
        
        $batchData[] = [
            'id_balance' => $this->id_balance,
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'auxiliar' => '',
            'saldo_anterior' => number_format((float)$totales->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$totales->debito, 2, '.', ''),
            'credito' => number_format((float)$totales->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$totales->saldo_final, 2, '.', ''),
            'documentos_totales' => $totales->documentos_totales,
        ];
    }

    private function buildBaseQuery()
    {
        $documentosQuery = DB::connection($this->connectionName)
            ->table('documentos_generals AS DG')
            ->select(
                'DG.id_cuenta',
                'PC.cuenta',
                'PC.nombre AS nombre_cuenta',
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("1 AS documentos_totales")
            )
            ->join('plan_cuentas AS PC', 'DG.id_cuenta', '=', 'PC.id')
            ->where('anulado', 0)
            ->whereBetween('DG.fecha_manual', [$this->request['fecha_desde'], $this->request['fecha_hasta']])
            ->when($this->request['cuenta_desde'] ?? false, function ($q) {
                $q->where('PC.cuenta', '>=', $this->request['cuenta_desde']);
            })
            ->when($this->request['cuenta_hasta'] ?? false, function ($q) {
                $q->where('PC.cuenta', '<=', $this->request['cuenta_hasta'])
                ->orWhere('PC.cuenta', 'LIKE', $this->request['cuenta_hasta'].'%');
            });

        $anteriorQuery = DB::connection($this->connectionName)
            ->table('documentos_generals AS DG')
            ->select(
                'DG.id_cuenta',
                'PC.cuenta',
                'PC.nombre AS nombre_cuenta',
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("1 AS documentos_totales")
            )
            ->join('plan_cuentas AS PC', 'DG.id_cuenta', '=', 'PC.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when($this->request['cuenta_desde'] ?? false, function ($q) {
                $q->where('PC.cuenta', '>=', $this->request['cuenta_desde']);
            })
            ->when($this->request['cuenta_hasta'] ?? false, function ($q) {
                $q->where('PC.cuenta', '<=', $this->request['cuenta_hasta'])
                ->orWhere('PC.cuenta', 'LIKE', $this->request['cuenta_hasta'].'%');
            });

        return DB::connection($this->connectionName)
            ->table(DB::raw("({$documentosQuery->toSql()}) AS periodo"))
            ->mergeBindings($documentosQuery)
            ->select(
                'id_cuenta',
                'cuenta',
                'nombre_cuenta',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(documentos_totales) AS documentos_totales')
            )
            ->groupBy('id_cuenta', 'cuenta', 'nombre_cuenta')
            ->unionAll(
                DB::connection($this->connectionName)
                    ->table(DB::raw("({$anteriorQuery->toSql()}) AS anterior"))
                    ->mergeBindings($anteriorQuery)
                    ->select(
                        'id_cuenta',
                        'cuenta',
                        'nombre_cuenta',
                        DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                        DB::raw('SUM(debito) AS debito'),
                        DB::raw('SUM(credito) AS credito'),
                        DB::raw('SUM(documentos_totales) AS documentos_totales')
                    )
                    ->groupBy('id_cuenta', 'cuenta', 'nombre_cuenta')
            );
    }

    private function buildGeneralQuery($total = false)
    {
        $documentosQuery = DB::connection($this->connectionName)
            ->table('documentos_generals AS DG')
            ->select(
                'PC.cuenta',
                DB::raw("0 AS saldo_anterior"),
                DB::raw("SUM(DG.debito) AS debito"),
                DB::raw("SUM(DG.credito) AS credito"),
                DB::raw("SUM(DG.debito - DG.credito) AS saldo_final"),
                DB::raw("COUNT(*) AS documentos_totales")
            )
            ->join('plan_cuentas AS PC', 'DG.id_cuenta', '=', 'PC.id')
            ->where('anulado', 0)
            ->whereBetween('DG.fecha_manual', [$this->request['fecha_desde'], $this->request['fecha_hasta']])
            ->when(!$total, function ($q) {
                $q->where(function($q2) {
                    $q2->whereBetween('PC.cuenta', ['4', '7'])
                       ->orWhere('PC.cuenta', 'LIKE', '7%');
                });
            })
            ->groupBy('PC.cuenta');

        $anteriorQuery = DB::connection($this->connectionName)
            ->table('documentos_generals AS DG')
            ->select(
                'PC.cuenta',
                DB::raw("SUM(DG.debito - DG.credito) AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("COUNT(*) AS documentos_totales")
            )
            ->join('plan_cuentas AS PC', 'DG.id_cuenta', '=', 'PC.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when(!$total, function ($q) {
                $q->where(function($q2) {
                    $q2->whereBetween('PC.cuenta', ['4', '7'])
                       ->orWhere('PC.cuenta', 'LIKE', '7%');
                });
            })
            ->groupBy('PC.cuenta');

        return DB::connection($this->connectionName)
            ->table(DB::raw("({$documentosQuery->toSql()}) AS periodo"))
            ->mergeBindings($documentosQuery)
            ->select(
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_final) AS saldo_final'),
                DB::raw('SUM(documentos_totales) AS documentos_totales')
            )
            ->unionAll(
                DB::connection($this->connectionName)
                    ->table(DB::raw("({$anteriorQuery->toSql()}) AS anterior"))
                    ->mergeBindings($anteriorQuery)
                    ->select(
                        DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                        DB::raw('SUM(debito) AS debito'),
                        DB::raw('SUM(credito) AS credito'),
                        DB::raw('SUM(saldo_final) AS saldo_final'),
                        DB::raw('SUM(documentos_totales) AS documentos_totales')
                    )
            );
    }

    private function processChunkedData($query, &$batchData, $insertBatch, $processor)
    {
        $query->chunk($this->chunkSize, function ($items) use (&$batchData, $insertBatch, $processor) {
            foreach ($items as $item) {
                $results = $processor($item);
                foreach ($results as $result) {
                    $batchData[] = $result;
                    $this->checkBatchSize($batchData, $insertBatch);
                }
            }
        });
    }

    private function checkBatchSize(&$batchData, $insertBatch)
    {
        if (count($batchData) >= $this->chunkSize) {
            $insertBatch($batchData);
            $batchData = [];
        }
    }

    private function getCuentasPadre($cuenta)
    {
        $length = strlen($cuenta);
        $cuentas = [];

        if ($length >= 1) $cuentas[] = substr($cuenta, 0, 1);
        if ($length >= 2) $cuentas[] = substr($cuenta, 0, 2);
        if ($length >= 4) $cuentas[] = substr($cuenta, 0, 4);
        if ($length >= 6) $cuentas[] = substr($cuenta, 0, 6);
        $cuentas[] = $cuenta;

        return array_unique($cuentas);
    }

    private function shouldIncludeCuenta($cuenta)
    {
        $length = strlen($cuenta);
        
        return ($this->request['nivel'] == 1 && $length < 3) ||
               ($this->request['nivel'] == 2 && $length < 5) ||
               ($this->request['nivel'] == 3);
    }

    private function buildCuentaData($source, $cuenta = null)
    {
        $cuentaData = $cuenta ? PlanCuentas::where('cuenta', $cuenta)->first(['id', 'cuenta', 'nombre', 'auxiliar']) : null;

        return [
            'id_balance' => $this->id_balance,
            'id_cuenta' => $cuentaData->id ?? '',
            'cuenta' => $cuentaData->cuenta ?? $cuenta,
            'nombre_cuenta' => $cuentaData->nombre ?? '',
            'auxiliar' => $this->request['tipo'] == '2' ? 0 : ($cuentaData->auxiliar ?? 0),
            'saldo_anterior' => number_format((float)$source->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$source->debito, 2, '.', ''),
            'credito' => number_format((float)$source->credito, 2, '.', ''),
            'saldo_final' => number_format((float)($source->saldo_anterior + $source->debito - $source->credito), 2, '.', ''),
            'documentos_totales' => $source->documentos_totales
        ];
    }

    private function buildNitData($source)
    {
        return [
            'id_balance' => $this->id_balance,
            'id_cuenta' => $source->cuenta,
            'cuenta' => $source->numero_documento,
            'nombre_cuenta' => $source->nombre_nit,
            'auxiliar' => 5,
            'saldo_anterior' => number_format((float)$source->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$source->debito, 2, '.', ''),
            'credito' => number_format((float)$source->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$source->saldo_final, 2, '.', ''),
            'documentos_totales' => $source->documentos_totales
        ];
    }

    private function notifySuccess()
    {
        event(new PrivateMessageEvent(
            'informe-balance-'.$this->empresa->token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con éxito!',
                'titulo' => 'Balance generado',
                'id_balance' => $this->id_balance,
                'autoclose' => false
            ]
        ));
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