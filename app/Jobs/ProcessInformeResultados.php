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

    public $tipo;
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
        $this->tipo = $this->request['tipo'] == 1 ? '4' : '5';
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
            
            ksort($this->resultadoCollection, SORT_STRING | SORT_FLAG_CASE);

            foreach (array_chunk($this->resultadoCollection,233) as $resultadoCollection){
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

    private function documentosResultado()
    {
        $query = $this->resultadoDocumentosQuery();
        $query->unionAll($this->resultadoAnteriorQuery());
        
        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS resultado"))
            ->mergeBindings($query)
            ->select(
                'id_cuenta',
                'id_nit',
                'numero_documento',
                'nombre_nit',
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
                    $cuentasAsociadas = $this->getCuentas($documento->cuenta);

                    foreach ($cuentasAsociadas as $cuenta) {
                        if ($this->hasCuentaData($cuenta)) {
                            $this->sumCuentaData($cuenta, $documento);
                        } else {
                            $this->newCuentaData($cuenta, $documento);
                        }
                    }
                });
            });
    }

    private function totalesDocumentosResultado()
    {
        $query = $this->resultadoDocumentosQuery();
        $query->unionAll($this->resultadoAnteriorQuery());
        $fecha = explode("-", $this->request['fecha_desde']);

        $totalesA = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS resultado"))
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

        $totalesB = DB::connection('sam')->table('presupuestos AS PR')
            ->select(
                'PD.id_padre',
                'PD.es_grupo',
                'PD.id_presupuesto',
                'PD.cuenta',
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
                DB::raw('SUM(PD.diciembre) AS diciembre'),
            )
            ->leftJoin('presupuesto_detalles AS PD', 'PR.id', 'PD.id_presupuesto')
            ->where('PR.periodo', $fecha[0])
            ->where('cuenta', 'LIKE', '4%')
            ->orWhere('cuenta', 'LIKE', '5%')
            ->where('PD.id_padre', 0)
            ->first();

        $dataPresupuesto = [
            'ppto_anterior' => 0,
            'ppto_movimiento' => 0,
            'ppto_acumulado' => 0,
            'ppto_diferencia' => 0,
            'ppto_porcentaje' => 0,
            'ppto_porcentaje_acumulado' => 0,
        ];

        $mesDesde = $fecha = explode("-", $this->request['fecha_desde'])[1];
        $mesHasta = $fecha = explode("-", $this->request['fecha_hasta'])[1];
        $dataPresupuesto['padre'] = $totalesB->id_padre ? true : false;

        foreach ($this->meses as $mesNumero => $mesNombre) {
            if ($mesDesde >= $mesNumero && $mesHasta <= $mesNumero) {
                $dataPresupuesto['ppto_movimiento']+= $totalesB->{$mesNombre};
            } else if ($mesDesde < $mesNumero){
                $dataPresupuesto['ppto_anterior']+= $totalesB->{$mesNombre};
            }
            $dataPresupuesto['ppto_acumulado']+= $totalesB->{$mesNombre};
        }

        $sum = $totalesA[0]->debito + $totalesA[0]->credito;
        $total = $totalesA[0]->saldo_anterior + $totalesA[0]->debito - $totalesA[0]->credito;

        $pptoPorcentaje = $sum ? ($dataPresupuesto['ppto_movimiento'] / $sum) * 100 : 0;
        $saldoFinal = $total < 0 ? $total * -1 : $total;
        
        $pptoPorcentajeAcumulado = $saldoFinal && $dataPresupuesto['ppto_acumulado'] ? ($dataPresupuesto['ppto_acumulado'] / $saldoFinal) * 100 : 0;
        $dataPresupuesto['ppto_diferencia'] = $dataPresupuesto['ppto_acumulado'] - $saldoFinal;
        $dataPresupuesto['ppto_porcentaje'] = $pptoPorcentaje;
        $dataPresupuesto['ppto_porcentaje_acumulado'] = $pptoPorcentajeAcumulado;

        $this->resultadoCollection['9999'] = [
            'id_resultado' => $this->id_resultado,
            'id_cuenta' => '',
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'auxiliar' => 5,
            'saldo_anterior' => number_format((float)$totalesA[0]->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$totalesA[0]->debito, 2, '.', ''),
            'credito' => number_format((float)$totalesA[0]->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$total, 2, '.', ''),
            'ppto_anterior' => $dataPresupuesto['ppto_anterior'],
            'ppto_movimiento' => $dataPresupuesto['ppto_movimiento'],
            'ppto_acumulado' => $dataPresupuesto['ppto_acumulado'],
            'ppto_diferencia' => $dataPresupuesto['ppto_diferencia'],
            'ppto_porcentaje' => '',
            'ppto_porcentaje_acumulado' => '',
            'documentos_totales' => $totalesA[0]->documentos_totales,
        ];
    }

    private function resultadoDocumentosQuery($cuenta = null)
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
            ->where('PC.cuenta', 'LIKE', $this->tipo.'%')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->when($this->request['cuenta'], function ($query) {
                $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->when($cuenta, function ($query) use ($cuenta){
                $query->where('PC.cuenta', $cuenta);
            });

        return $documentosQuery;
    }

    private function resultadoAnteriorQuery($cuenta = null)
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
            ->where('PC.cuenta', 'LIKE', $this->tipo.'%')
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when($this->request['cuenta'], function ($query) {
                $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->when($cuenta, function ($query) use ($cuenta){
                $query->where('PC.cuenta', $cuenta);
            });

        return $documentosQuery;
    }

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

    private function newCuentaData($cuenta, $resultado)
    {
        $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();
        if(!$cuentaData){
            return;
        }

        $this->resultadoCollection[$cuenta] = [
            'id_resultado' => $this->id_resultado,
            'id_cuenta' => $cuentaData->id,
            'id_nit' => null, // Solo cuentas, no nits
            'numero_documento' => null,
            'nombre_nit' => null,
            'cuenta' => $cuentaData->cuenta,
            'nombre_cuenta' => $cuentaData->nombre,
            'auxiliar' => $cuentaData->auxiliar,
            'saldo_anterior' => (float)$resultado->saldo_anterior, // Sin formateo
            'debito' => (float)$resultado->debito,
            'credito' => (float)$resultado->credito,
            'saldo_final' => (float)$resultado->saldo_final,
            'ppto_anterior' => 0,
            'ppto_movimiento' => 0,
            'ppto_acumulado' => 0,
            'ppto_diferencia' => 0,
            'ppto_porcentaje' => 0,
            'ppto_porcentaje_acumulado' => 0,
            'documentos_totales' => (int)$resultado->documentos_totales,
        ];
    }

    private function getPresupuesto($cuenta, $padre = false)
    {
        $fecha = explode("-", $this->request['fecha_desde']);
        $presupuesto = DB::connection('sam')->table('presupuestos AS PR')
            ->select(
                'PD.id_padre',
                'PD.es_grupo',
                'PD.id_presupuesto',
                'PD.cuenta',
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
                DB::raw('SUM(PD.diciembre) AS diciembre'),
            )
            ->leftJoin('presupuesto_detalles AS PD', 'PR.id', 'PD.id_presupuesto')
            ->where('PR.periodo', $fecha[0]);
            // ->where('PD.id_padre', 0);

        if ($padre) {
            $presupuesto->where('PD.cuenta', 'LIKE', $cuenta.'%')
                ->where('PD.id_padre', 0);
        } else {
            $presupuesto->where('PD.cuenta', $cuenta);
        }

        $presupuesto = $presupuesto->first();
        
        $dataPresupuesto = [
            'cuenta' => $cuenta,
            'padre' => false,
            'ppto_anterior' => 0,
            'ppto_movimiento' => 0,
            'ppto_acumulado' => 0,
            'ppto_diferencia' => 0,
            'ppto_porcentaje' => 0,
            'ppto_porcentaje_acumulado' => 0,
        ];

        if (!$presupuesto) return $dataPresupuesto;

        $mesDesde = intval(explode("-", $this->request['fecha_desde'])[1]);
        $mesHasta = intval(explode("-", $this->request['fecha_hasta'])[1]);
        $dataPresupuesto['padre'] = $presupuesto->id_padre ? true : false;
        foreach ($this->meses as $mesNumero => $mesNombre) {
            if ($mesNumero >= $mesDesde && $mesNumero <= $mesHasta) {
                $dataPresupuesto['ppto_movimiento']+= $presupuesto->{$mesNombre};
            } else if ($mesNumero < $mesDesde){
                $dataPresupuesto['ppto_anterior']+= $presupuesto->{$mesNombre};
            }
            $dataPresupuesto['ppto_acumulado']+= $presupuesto->{$mesNombre};
        }
        return $dataPresupuesto;
    }

    private function procesarPresupuestos()
    {
        foreach ($this->resultadoCollection as $cuenta => $data) {
            $presupuesto = $this->getPresupuesto($cuenta);
            
            $pptoPorcentaje = $data['debito'] != 0 ? 
                ($presupuesto['ppto_movimiento'] / $data['debito']) * 100 : 0;
            
            $pptoPorcentajeAcumulado = $data['saldo_final'] != 0 ? 
                ($presupuesto['ppto_acumulado'] / abs($data['saldo_final'])) * 100 : 0;

            $this->resultadoCollection[$cuenta]['ppto_anterior'] = $presupuesto['ppto_anterior'];
            $this->resultadoCollection[$cuenta]['ppto_movimiento'] = $presupuesto['ppto_movimiento'];
            $this->resultadoCollection[$cuenta]['ppto_acumulado'] = $presupuesto['ppto_acumulado'];
            $this->resultadoCollection[$cuenta]['ppto_diferencia'] = 
                $presupuesto['ppto_acumulado'] - abs($data['saldo_final']);
            $this->resultadoCollection[$cuenta]['ppto_porcentaje'] = $pptoPorcentaje;
            $this->resultadoCollection[$cuenta]['ppto_porcentaje_acumulado'] = $pptoPorcentajeAcumulado;
            
            // Formatear solo al final
            // $this->formatearValoresNumericos($cuenta);
        }
    }

    private function formatearValoresNumericos($cuenta)
    {
        $campos = [
            'saldo_anterior', 'debito', 'credito', 'saldo_final',
            'ppto_anterior', 'ppto_movimiento', 'ppto_acumulado', 'ppto_diferencia',
            'ppto_porcentaje', 'ppto_porcentaje_acumulado'
        ];
        
        foreach ($campos as $campo) {
            $this->resultadoCollection[$cuenta][$campo] = 
                number_format($this->resultadoCollection[$cuenta][$campo], 2, '.', '');
        }
    }

    private function sumCuentaData($cuenta, $resultado)
    {
        $this->resultadoCollection[$cuenta]['saldo_anterior'] += (float)$resultado->saldo_anterior;
        $this->resultadoCollection[$cuenta]['debito'] += (float)$resultado->debito;
        $this->resultadoCollection[$cuenta]['credito'] += (float)$resultado->credito;
        $this->resultadoCollection[$cuenta]['saldo_final'] += (float)$resultado->saldo_final;
    }

    private function addCuentaPpto($cuenta, $ppto)
    {
        $ppto = (object)$ppto;
        $sum = $this->resultadoCollection[$cuenta]['debito'] + $this->resultadoCollection[$cuenta]['credito']; 
        $pptoPorcentaje = $sum ? ($ppto->ppto_movimiento / $sum) * 100 : 0;
        $saldoFinal = $this->resultadoCollection[$cuenta]['saldo_final'] < 0 ? $this->resultadoCollection[$cuenta]['saldo_final'] * -1 : $this->resultadoCollection[$cuenta]['saldo_final'];
        $pptoPorcentajeAcumulado = 0;
        if ($saldoFinal != '0.00' && $ppto->ppto_acumulado) {
            $pptoPorcentajeAcumulado = ($ppto->ppto_acumulado / $saldoFinal) * 100;
        }
        $this->resultadoCollection[$cuenta]['ppto_anterior'] = $ppto->ppto_anterior;
        $this->resultadoCollection[$cuenta]['ppto_movimiento'] = $ppto->ppto_movimiento;
        $this->resultadoCollection[$cuenta]['ppto_acumulado'] = $ppto->ppto_acumulado;
        $this->resultadoCollection[$cuenta]['ppto_diferencia'] = $ppto->ppto_acumulado - $this->resultadoCollection[$cuenta]['saldo_final'];
        $this->resultadoCollection[$cuenta]['ppto_porcentaje'] = $pptoPorcentaje;
        $this->resultadoCollection[$cuenta]['ppto_porcentaje_acumulado'] = $pptoPorcentajeAcumulado;
    }

    private function hasCuentaData($cuenta)
	{
		return isset($this->resultadoCollection[$cuenta]);
	}

    public function failed($exception)
    {
        DB::connection('informes')->rollBack();
        
        // Si no tenemos la empresa, intentamos obtenerla
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

        // Registrar el error en los logs
        logger()->error("Error en ProcessInformeResultados: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }
}
