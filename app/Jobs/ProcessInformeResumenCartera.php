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
use App\Models\Informes\InfResumenCartera;

class ProcessInformeResumenCartera implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $empresa;
    public $request;
    public $id_usuario;
	public $id_empresa;
    public $timeout = 300;
    public $cuentas_orden;
    public $id_resultado_cartera;
    public $resultadoCarteraCollection = [];

    public function __construct($request, $id_usuario, $id_empresa, $id_resultado_cartera)
    {
        $this->request = $request;
        $this->cuentas_orden = [];
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        $this->id_resultado_cartera = $id_resultado_cartera;
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

            $this->addCuentasOrden();
            $this->addResumenCartera();
            $this->addTotalResumenCartera();
            
            ksort($this->resultadoCarteraCollection, SORT_STRING | SORT_FLAG_CASE);
            foreach (array_chunk($this->resultadoCarteraCollection,233) as $resultadoCarteraCollection){
                
                DB::connection('informes')
                    ->table('inf_resumen_cartera_detalles')
                    ->insert(array_values($resultadoCarteraCollection));
			}

            InfResumenCartera::where('id', $this->id_resultado_cartera)->update([
                'estado' => 2,
                'cuentas' => json_encode($this->cuentas_orden)
            ]);
            
            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-resumen-cartera-'.$this->empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Resumen cartera generado',
                'id_resumen_cartera' => $this->id_resultado_cartera,
                'autoclose' => false
            ]));

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;
            
            Log::info("Informe resumen cartera ejecutado en {$executionTime} segundos, usando {$memoryUsage} bytes de memoria.");

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
			throw $exception;
        }
    }

    private function addCuentasOrden()
    {
        $query = $this->resumenCarteraQuery();

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'cuenta',
                'nombre_cuenta',
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final")
            )
            ->groupByRaw('id_cuenta')
            ->orderByRaw('cuenta')
            ->havingRaw('saldo_final != 0')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $this->cuentas_orden[] = (object)[
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                    ];
                }
                unset($documentos);
            });
            ;
    }

    private function addResumenCartera()
    {
        $fechaActual = Carbon::now();
        $query = $this->resumenCarteraQuery();

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'anulado',
                'plazo',
                DB::raw("DATEDIFF('{$fechaActual}', fecha_manual) AS dias_mora"),
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final"),
                DB::raw("COUNT(id) AS total_columnas")
            )
            ->groupByRaw('id_nit, id_cuenta')
            ->orderByRaw('cuenta')
            ->havingRaw('saldo_final != 0')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    
                    $columnaCuenta = $this->buscarCuenta($documento->cuenta);

                    if (!$columnaCuenta) continue;

                    if (!$this->hasCuentaData($documento->id_nit)) {
                        $this->newCuentaData($documento);
                    }

                    $mora = $documento->dias_mora - $documento->plazo;
                    $this->resultadoCarteraCollection[$documento->id_nit]["cuenta_$columnaCuenta"] = $documento->saldo_final;
                    $this->resultadoCarteraCollection[$documento->id_nit]["saldo_final"]+= $documento->saldo_final;

                    if ($mora > 0 && $this->resultadoCarteraCollection[$documento->id_nit]["dias_mora"] > $mora) {
                        $this->resultadoCarteraCollection[$documento->id_nit]["dias_mora"] = $mora;
                    }
                }
                
                unset($documentos);
            });
    }

    private function addTotalResumenCartera()
    {
        $query = $this->resumenCarteraQuery();

        $this->newTotalData();

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'dias_mora',
                'anulado',
                'plazo',
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final"),
                DB::raw("COUNT(id) AS total_columnas")
            )
            ->groupByRaw('id_cuenta')
            ->orderByRaw('cuenta')
            ->havingRaw('saldo_final != 0')
            ->chunk(233, function ($documentos) {
                
                foreach ($documentos as $documento) {
                    
                    $columnaCuenta = $this->buscarCuenta($documento->cuenta);
                    
                    $this->resultadoCarteraCollection['Z999999']["cuenta_$columnaCuenta"] = $documento->saldo_final;
                    $this->resultadoCarteraCollection['Z999999']["saldo_final"]+= $documento->saldo_final;
                }
                
                unset($documentos);
            });
    }

    private function resumenCarteraQuery()
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id",
                "N.id AS id_nit",
                "N.numero_documento",
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.apartamentos",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.fecha_manual",
                "DG.anulado",
                "DG.debito",
                "DG.credito"
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->whereIn('PCT.id_tipo_cuenta', [3,4,7,8])
            ->where('anulado', 0);
    }

    private function buscarCuenta($buscarCuenta)
    {
        $key = collect($this->cuentas_orden)->search(function ($item) use ($buscarCuenta) {
            return isset($item->cuenta) && $item->cuenta === $buscarCuenta;
        });
        
        return $key !== false ? $key + 1 : null;
    }

    private function newCuentaData($documento)
    {
        $this->resultadoCarteraCollection[$documento->id_nit] = [
            'id_resumen_cartera' => $this->id_resultado_cartera,
            'id_nit' => $documento->id_nit, 
            'nombre_nit' => $documento->nombre_nit, 
            'numero_documento' => $documento->numero_documento, 
            'saldo_final' => 0, 
            'dias_mora' => 0, 
            'ubicacion' => $documento->apartamentos, 
            'cuenta_1' => 0, 
            'cuenta_2' => 0, 
            'cuenta_3' => 0, 
            'cuenta_4' => 0, 
            'cuenta_5' => 0, 
            'cuenta_6' => 0, 
            'cuenta_7' => 0, 
            'cuenta_8' => 0, 
            'cuenta_9' => 0, 
            'cuenta_10' => 0, 
            'cuenta_11' => 0, 
            'cuenta_12' => 0, 
            'cuenta_13' => 0, 
            'cuenta_14' => 0, 
            'cuenta_15' => 0, 
            'cuenta_16' => 0, 
            'cuenta_17' => 0, 
            'cuenta_18' => 0, 
            'cuenta_19' => 0, 
            'cuenta_20' => 0, 
            'cuenta_21' => 0,
            'cuenta_22' => 0,
            'cuenta_23' => 0,
            'cuenta_24' => 0,
            'cuenta_25' => 0,
            'cuenta_26' => 0,
            'cuenta_27' => 0,
            'cuenta_28' => 0,
            'cuenta_29' => 0,
            'cuenta_30' => 0,
        ];
    }

    private function newTotalData()
    {
        $this->resultadoCarteraCollection['Z999999'] = [
            'id_resumen_cartera' => $this->id_resultado_cartera,
            'id_nit' => null, 
            'nombre_nit' => 'TOTAL', 
            'numero_documento' => '', 
            'saldo_final' => 0, 
            'dias_mora' => 0, 
            'ubicacion' => '', 
            'cuenta_1' => 0, 
            'cuenta_2' => 0, 
            'cuenta_3' => 0, 
            'cuenta_4' => 0, 
            'cuenta_5' => 0, 
            'cuenta_6' => 0, 
            'cuenta_7' => 0, 
            'cuenta_8' => 0, 
            'cuenta_9' => 0, 
            'cuenta_10' => 0, 
            'cuenta_11' => 0, 
            'cuenta_12' => 0, 
            'cuenta_13' => 0, 
            'cuenta_14' => 0, 
            'cuenta_15' => 0, 
            'cuenta_16' => 0, 
            'cuenta_17' => 0, 
            'cuenta_18' => 0, 
            'cuenta_19' => 0, 
            'cuenta_20' => 0, 
            'cuenta_21' => 0,
            'cuenta_22' => 0,
            'cuenta_23' => 0,
            'cuenta_24' => 0,
            'cuenta_25' => 0,
            'cuenta_26' => 0,
            'cuenta_27' => 0,
            'cuenta_28' => 0,
            'cuenta_29' => 0,
            'cuenta_30' => 0,
        ];
    }

    private function hasCuentaData($id_nit)
	{
		return isset($this->resultadoCarteraCollection[$id_nit]);
	}

    public function failed($exception)
    {
        DB::connection('informes')->rollBack();
        
        // Si no tenemos la empresa, intentamos obtenerla
        if (!$this->empresa && $this->id_empresa) {
            $this->empresa = Empresa::find($this->id_empresa);
        }

        $token_db = $this->empresa ? $this->empresa->token_db : 'unknown';

        InfResumenCartera::where('id', $this->id_resultado_cartera)->update([
            'estado' => 0
        ]);
        
        event(new PrivateMessageEvent(
            'informe-resumen-cartera-'.$token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'error',
                'mensaje' => 'Error al generar el informe: '.$exception->getMessage(),
                'titulo' => 'Error en proceso',
                'autoclose' => false
            ]
        ));

        // Registrar el error en los logs
        logger()->error("Error en ProcessInformeResumenCartera: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }
}