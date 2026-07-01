<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
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
use App\Models\Informes\InfResultado;

class ProcessInformeResultados implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $empresa;
    public $request;
    public $id_usuario;
    public $id_empresa;
    public $id_resultado;
    public $timeout = 300;
    public $resultados = [];
    public $resultadoCollection = [];
    public $meses = [
        1 => 'enero',
        2 => 'febrero',
        3 => 'marzo',
        4 => 'abril',
        5 => 'mayo',
        6 => 'junio',
        7 => 'julio',
        8 => 'agosto',
        9 => 'septiembre',
        10 => 'octubre',
        11 => 'noviembre',
        12 => 'diciembre',
    ];

    public function __construct($request, $id_usuario, $id_empresa, $id_resultado)
    {
        $this->request = $request;
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->id_resultado = $id_resultado;
    }

    public function handle()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $this->empresa = Empresa::find($this->id_empresa);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);

        DB::connection('informes')->beginTransaction();

        try {
            $this->documentosResultado();
            $this->procesarPresupuestos();
            $this->totalesDocumentosResultado();
            
            ksort($this->resultadoCollection, SORT_STRING | SORT_FLAG_CASE);

            foreach (array_chunk($this->resultadoCollection, 233) as $resultadoCollection) {
                DB::connection('informes')
                    ->table('inf_resultado_detalles')
                    ->insert(array_values($resultadoCollection));
            }

            InfResultado::where('id', $this->id_resultado)->update([
                'estado' => 2
            ]);

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-resultado-'.$this->empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Resultado generado',
                'id_resultado' => $this->id_resultado,
                'autoclose' => false
            ]));

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;
            
            Log::info("Informe resultados ejecutado en {$executionTime} segundos, usando {$memoryUsage} bytes de memoria.");

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
            throw $exception;
        }
    }

    /**
     * [MODIFICADO] Procesa documentos y los acumula por mes
     */
    private function documentosResultado()
    {
        // PRIMERO: Cargar todas las cuentas con presupuesto (estructura con meses)
        $this->cargarCuentasConPresupuesto();
        
        // SEGUNDO: Procesar movimientos contables y sumarlos a las cuentas existentes
        // Obtener movimientos netos agrupados por cuenta y mes
        $query = $this->resultadoDocumentosMensualQuery();
        
        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS resultado"))
            ->mergeBindings($query)
            ->select(
                'cuenta',
                'mes',
                DB::raw('SUM(neto_mes) AS neto_mes'),
                DB::raw('COUNT(*) AS documentos_totales')
            )
            ->groupBy('cuenta', 'mes')
            ->orderBy('cuenta')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    // Obtener todas las cuentas padre (incluyendo la propia)
                    $cuentasAsociadas = $this->getCuentas($documento->cuenta);

                    foreach ($cuentasAsociadas as $cuenta) {
                        if ($this->hasCuentaData($cuenta)) {
                            $this->sumCuentaDataMensual($cuenta, $documento->mes, (float)$documento->neto_mes);
                        } else {
                            $this->newCuentaDataMensual($cuenta, $documento->mes, (float)$documento->neto_mes);
                        }
                    }
                });
            });

        // TERCERO: Calcular saldo_anterior (neto antes de fecha_desde)
        $saldosAnteriores = $this->obtenerSaldoAnterior();
        foreach ($saldosAnteriores as $item) {
            $cuentasAsociadas = $this->getCuentas($item->cuenta);
            foreach ($cuentasAsociadas as $cuenta) {
                if ($this->hasCuentaData($cuenta)) {
                    $this->resultadoCollection[$cuenta]['saldo_anterior'] += (float)$item->saldo_anterior;
                } else {
                    $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();
                    if ($cuentaData) {
                        $this->resultadoCollection[$cuenta] = $this->crearEstructuraBase($cuentaData);
                        $this->resultadoCollection[$cuenta]['saldo_anterior'] = (float)$item->saldo_anterior;
                    }
                }
            }
        }

        // CUARTO: Calcular saldo_final = saldo_anterior + suma de todos los meses
        $this->calcularSaldoFinal();
    }

    /**
     * [NUEVO] Consulta que devuelve el neto (débito - crédito) agrupado por cuenta y mes
     */
    private function resultadoDocumentosMensualQuery()
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'PC.cuenta',
                DB::raw('MONTH(DG.fecha_manual) AS mes'),
                DB::raw('SUM(DG.debito - DG.credito) AS neto_mes')
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->where(function($query) {
                $query->where('PC.cuenta', 'LIKE', '5%')
                    ->orWhere('PC.cuenta', 'LIKE', '4%');
            })
            ->where('DG.anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->when($this->request['cuenta'], function ($query) {
                $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->groupBy('PC.cuenta', 'mes');
    }

    /**
     * [NUEVO] Obtiene saldo anterior (neto antes de fecha_desde)
     */
    private function obtenerSaldoAnterior()
    {
        return DB::connection('sam')
            ->table('documentos_generals AS DG')
            ->join('plan_cuentas AS PC', 'DG.id_cuenta', '=', 'PC.id')
            ->where(function($query) {
                $query->where('PC.cuenta', 'LIKE', '5%')
                      ->orWhere('PC.cuenta', 'LIKE', '4%');
            })
            ->where('DG.anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when($this->request['cuenta'] ?? null, function ($query) {
                $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->select(
                'PC.cuenta',
                DB::raw('SUM(DG.debito - DG.credito) AS saldo_anterior')
            )
            ->groupBy('PC.cuenta')
            ->get();
    }

    /**
     * [MODIFICADO] Inicializa cuentas con presupuesto (estructura con meses)
     */
    private function cargarCuentasConPresupuesto()
    {
        $fecha = explode("-", $this->request['fecha_desde']);
        $anio = $fecha[0];
        
        $cuentasConPresupuesto = DB::connection('sam')
            ->table('presupuesto_detalles AS PD')
            ->join('presupuestos AS PR', 'PD.id_presupuesto', '=', 'PR.id')
            ->where('PR.periodo', $anio)
            ->where(function($query) {
                $query->where('PD.cuenta', 'LIKE', '5%')
                    ->orWhere('PD.cuenta', 'LIKE', '4%');
            })
            ->where('PD.auxiliar', 1)
            ->select('PD.cuenta', 'PD.nombre', 'PD.id_padre')
            ->distinct()
            ->get();
        
        foreach ($cuentasConPresupuesto as $cuenta) {
            $cuentaData = PlanCuentas::whereCuenta($cuenta->cuenta)->first();
            if (!$cuentaData) continue;
            
            if (!$this->hasCuentaData($cuenta->cuenta)) {
                $this->resultadoCollection[$cuenta->cuenta] = $this->crearEstructuraBase($cuentaData);
            }
        }
    }

    /**
     * [NUEVO] Crea la estructura base con meses
     */
    private function crearEstructuraBase($cuentaData)
    {
        $base = [
            'id_resultado' => $this->id_resultado,
            'id_cuenta' => $cuentaData->id,
            'id_nit' => null,
            'numero_documento' => null,
            'nombre_nit' => null,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'auxiliar' => $cuentaData->auxiliar,
            'saldo_anterior' => 0.00,
            'saldo_final' => 0.00,
            'ppto_anterior' => 0,
            'ppto_movimiento' => 0,
            'ppto_acumulado' => 0,
            'ppto_diferencia' => 0,
            'ppto_porcentaje' => 0,
            'ppto_porcentaje_acumulado' => 0,
            'documentos_totales' => 0,
        ];

        foreach ($this->meses as $mesNombre) {
            $base[$mesNombre] = 0.00;
        }

        return $base;
    }

    /**
     * [NUEVO] Suma un neto mensual a una cuenta existente
     */
    private function sumCuentaDataMensual($cuenta, $mes, $neto)
    {
        if ($this->hasCuentaData($cuenta)) {
            $nombreMes = $this->meses[$mes] ?? null;
            if ($nombreMes) {
                $this->resultadoCollection[$cuenta][$nombreMes] += $neto;
            }
            // Contar documentos solo para la cuenta original (no padres)
            // En este contexto, contamos cada grupo de mes como un "documento"
            $this->resultadoCollection[$cuenta]['documentos_totales'] += 1;
        }
    }

    /**
     * [NUEVO] Crea una nueva cuenta y le asigna un neto mensual
     */
    private function newCuentaDataMensual($cuenta, $mes, $neto)
    {
        $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();
        if (!$cuentaData) return;

        $this->resultadoCollection[$cuenta] = $this->crearEstructuraBase($cuentaData);
        
        $nombreMes = $this->meses[$mes] ?? null;
        if ($nombreMes) {
            $this->resultadoCollection[$cuenta][$nombreMes] = $neto;
        }
        $this->resultadoCollection[$cuenta]['documentos_totales'] = 1;
    }

    /**
     * [NUEVO] Calcula saldo_final = saldo_anterior + suma de todos los meses
     */
    private function calcularSaldoFinal()
    {
        foreach ($this->resultadoCollection as $cuenta => &$data) {
            $sumaMeses = 0;
            foreach ($this->meses as $mesNombre) {
                $sumaMeses += $data[$mesNombre] ?? 0;
            }
            $data['saldo_final'] = $data['saldo_anterior'] + $sumaMeses;
        }
    }

    /**
     * [MODIFICADO] Ahora trabaja con meses en lugar de débito/crédito
     */
    private function totalesDocumentosResultado()
    {
        // Sumar todos los saldos y movimientos de todas las cuentas
        $totales = [
            'saldo_anterior' => 0,
            'saldo_final' => 0,
            'documentos_totales' => 0,
        ];

        foreach ($this->meses as $mesNombre) {
            $totales[$mesNombre] = 0;
        }

        foreach ($this->resultadoCollection as $cuenta => $data) {
            if ($cuenta === '9999') continue;

            $totales['saldo_anterior'] += $data['saldo_anterior'];
            $totales['saldo_final'] += $data['saldo_final'];
            $totales['documentos_totales'] += $data['documentos_totales'];

            foreach ($this->meses as $mesNombre) {
                $totales[$mesNombre] += $data[$mesNombre] ?? 0;
            }
        }

        // Obtener presupuestos totales (igual que antes)
        $fecha = explode("-", $this->request['fecha_desde']);
        $anio = $fecha[0];
        $mesDesde = intval($fecha[1]);
        $mesHasta = intval(explode("-", $this->request['fecha_hasta'])[1]);

        $presupuestoTotal = DB::connection('sam')
            ->table('presupuestos AS PR')
            ->join('presupuesto_detalles AS PD', 'PR.id', '=', 'PD.id_presupuesto')
            ->where('PR.periodo', $anio)
            ->where(function($query) {
                $query->where('PD.cuenta', 'LIKE', '4%')
                      ->orWhere('PD.cuenta', 'LIKE', '5%');
            })
            ->where('PD.id_padre', 0)
            ->select(
                DB::raw('SUM(PD.enero) AS enero'),
                DB::raw('SUM(PD.febrero) AS febrero'),
                DB::raw('SUM(PD.marzo) AS marzo'),
                DB::raw('SUM(PD.abril) AS abril'),
                DB::raw('SUM(PD.mayo) AS mayo'),
                DB::raw('SUM(PD.junio) AS junio'),
                DB::raw('SUM(PD.julio) AS julio'),
                DB::raw('SUM(PD.agosto) AS agosto'),
                DB::raw('SUM(PD.septiembre) AS septiembre'),
                DB::raw('SUM(PD.octubre) AS octubre'),
                DB::raw('SUM(PD.noviembre) AS noviembre'),
                DB::raw('SUM(PD.diciembre) AS diciembre')
            )
            ->first();

        $pptoAnterior = 0;
        $pptoMovimiento = 0;
        $pptoAcumulado = 0;

        if ($presupuestoTotal) {
            foreach ($this->meses as $num => $nombre) {
                $valor = (float)$presupuestoTotal->{$nombre};
                if ($num >= $mesDesde && $num <= $mesHasta) {
                    $pptoMovimiento += $valor;
                } elseif ($num < $mesDesde) {
                    $pptoAnterior += $valor;
                }
                $pptoAcumulado += $valor;
            }
        }

        $sumaMesesPeriodo = 0;
        foreach ($this->meses as $num => $nombre) {
            if ($num >= $mesDesde && $num <= $mesHasta) {
                $sumaMesesPeriodo += $totales[$nombre];
            }
        }

        $saldoFinalAbs = abs($totales['saldo_final']);
        $pptoPorcentaje = ($sumaMesesPeriodo != 0) 
            ? ($pptoMovimiento / $sumaMesesPeriodo) * 100 
            : 0;
        $pptoPorcentajeAcumulado = ($saldoFinalAbs != 0) 
            ? ($pptoAcumulado / $saldoFinalAbs) * 100 
            : 0;

        $this->resultadoCollection['9999'] = [
            'id_resultado' => $this->id_resultado,
            'id_cuenta' => '',
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'auxiliar' => 5,
            'saldo_anterior' => number_format($totales['saldo_anterior'], 2, '.', ''),
            'saldo_final' => number_format($totales['saldo_final'], 2, '.', ''),
            'ppto_anterior' => $pptoAnterior,
            'ppto_movimiento' => $pptoMovimiento,
            'ppto_acumulado' => $pptoAcumulado,
            'ppto_diferencia' => $pptoAcumulado - $saldoFinalAbs,
            'ppto_porcentaje' => number_format($pptoPorcentaje, 2, '.', ''),
            'ppto_porcentaje_acumulado' => number_format($pptoPorcentajeAcumulado, 2, '.', ''),
            'documentos_totales' => $totales['documentos_totales'],
        ];

        foreach ($this->meses as $mesNombre) {
            $this->resultadoCollection['9999'][$mesNombre] = number_format($totales[$mesNombre], 2, '.', '');
        }
    }

    /**
     * [MODIFICADO] Usa meses para calcular porcentajes
     */
    private function procesarPresupuestos()
    {
        $mesDesde = intval(explode("-", $this->request['fecha_desde'])[1]);
        $mesHasta = intval(explode("-", $this->request['fecha_hasta'])[1]);

        foreach ($this->resultadoCollection as $cuenta => &$data) {
            if ($cuenta === '9999') continue;

            $presupuesto = $this->getPresupuesto($cuenta);
            
            $data['ppto_anterior'] = $presupuesto['ppto_anterior'];
            $data['ppto_movimiento'] = $presupuesto['ppto_movimiento'];
            $data['ppto_acumulado'] = $presupuesto['ppto_anterior'] + $presupuesto['ppto_movimiento'];

            $sumaMesesPeriodo = 0;
            foreach ($this->meses as $num => $nombre) {
                if ($num >= $mesDesde && $num <= $mesHasta) {
                    $sumaMesesPeriodo += $data[$nombre] ?? 0;
                }
            }

            $saldoFinalAbs = abs($data['saldo_final']);
            $data['ppto_diferencia'] = $data['ppto_acumulado'] - $saldoFinalAbs;

            $data['ppto_porcentaje'] = ($sumaMesesPeriodo != 0) 
                ? ($presupuesto['ppto_movimiento'] / $sumaMesesPeriodo) * 100 
                : 0;
            $data['ppto_porcentaje_acumulado'] = ($saldoFinalAbs != 0) 
                ? ($data['ppto_acumulado'] / $saldoFinalAbs) * 100 
                : 0;
        }
    }

    /**
     * [SIN CAMBIOS] getPresupuesto - se mantiene igual
     */
    private function getPresupuesto($cuenta, $padre = false)
    {
        $fecha = explode("-", $this->request['fecha_desde']);
        $anio = $fecha[0];
        $query = DB::connection('sam')
            ->table('presupuestos AS PR')
            ->join('presupuesto_detalles AS PD', 'PR.id', '=', 'PD.id_presupuesto')
            ->where('PR.periodo', $anio)
            ->where('PD.cuenta', $cuenta);

        if ($padre) {
            $query->where('PD.id_padre', 0);
        }

        $presupuesto = $query->select(
            'PD.id_padre',
            'PD.es_grupo',
            DB::raw('SUM(PD.enero) AS enero'),
            DB::raw('SUM(PD.febrero) AS febrero'),
            DB::raw('SUM(PD.marzo) AS marzo'),
            DB::raw('SUM(PD.abril) AS abril'),
            DB::raw('SUM(PD.mayo) AS mayo'),
            DB::raw('SUM(PD.junio) AS junio'),
            DB::raw('SUM(PD.julio) AS julio'),
            DB::raw('SUM(PD.agosto) AS agosto'),
            DB::raw('SUM(PD.septiembre) AS septiembre'),
            DB::raw('SUM(PD.octubre) AS octubre'),
            DB::raw('SUM(PD.noviembre) AS noviembre'),
            DB::raw('SUM(PD.diciembre) AS diciembre')
        )->first();

        $dataPresupuesto = [
            'ppto_anterior' => 0,
            'ppto_movimiento' => 0,
            'ppto_acumulado' => 0,
        ];

        if (!$presupuesto) return $dataPresupuesto;

        $mesDesde = intval(explode("-", $this->request['fecha_desde'])[1]);
        $mesHasta = intval(explode("-", $this->request['fecha_hasta'])[1]);

        foreach ($this->meses as $num => $nombre) {
            $valor = (float)$presupuesto->{$nombre};
            if ($num >= $mesDesde && $num <= $mesHasta) {
                $dataPresupuesto['ppto_movimiento'] += $valor;
            } elseif ($num < $mesDesde) {
                $dataPresupuesto['ppto_anterior'] += $valor;
            }
            $dataPresupuesto['ppto_acumulado'] += $valor;
        }

        return $dataPresupuesto;
    }

    /**
     * [SIN CAMBIOS] getCuentas - se mantiene igual
     */
    private function getCuentas($cuenta)
    {
        $dataCuentas = NULL;
        if(strlen($cuenta) > 6){
            $dataCuentas =[
                $cuenta,
                mb_substr($cuenta, 0, 6),
                mb_substr($cuenta, 0, 4),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 1),
            ];
        } else if (strlen($cuenta) > 4) {
            $dataCuentas =[
                $cuenta,
                mb_substr($cuenta, 0, 4),
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 1),
            ];
        } else if (strlen($cuenta) > 2) {
            $dataCuentas =[
                $cuenta,
                mb_substr($cuenta, 0, 2),
                mb_substr($cuenta, 0, 1),
            ];
        } else if (strlen($cuenta) > 1) {
            $dataCuentas =[
                $cuenta,
                mb_substr($cuenta, 0, 1),
            ];
        } else {
            $dataCuentas =[
                $cuenta,
            ];
        }

        return $dataCuentas;
    }

    /**
     * [SIN CAMBIOS] hasCuentaData
     */
    private function hasCuentaData($cuenta)
    {
        return isset($this->resultadoCollection[$cuenta]);
    }

    /**
     * [SIN CAMBIOS] failed
     */
    public function failed($exception)
    {
        DB::connection('informes')->rollBack();
        
        if (!$this->empresa && $this->id_empresa) {
            $this->empresa = Empresa::find($this->id_empresa);
        }

        $token_db = $this->empresa ? $this->empresa->token_db : 'unknown';

        InfResultado::where('id', $this->id_resultado)->update([
            'estado' => 0
        ]);

        event(new PrivateMessageEvent(
            'informe-resultado-'.$token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'error',
                'mensaje' => 'Error al generar el informe: '.$exception->getMessage(),
                'titulo' => 'Error en proceso',
                'autoclose' => false
            ]
        ));

        logger()->error("Error en ProcessInformeResultados: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }
}