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
    public $meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];

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
            if ($this->request['id_nit']) {
                $this->addCuentasMeses();
                $this->addAbonosMeses();
                $this->descuentoProntoPagoMeses();
                $this->addTotalIndividualCartera();
            } else {
                $this->addResumenCartera();
                $this->addTotalResumenCartera();
            }
            
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
        $tiposCuentasFilter = $this->request['id_nit'] ? [3,4,7,8,11] : [3,4,7,8];
        $query = $this->resumenCarteraQuery($tiposCuentasFilter);

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'cuenta',
                'nombre_cuenta',
                'naturaleza_cuenta',
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final")
            )
            ->groupByRaw('id_cuenta')
            ->orderByRaw('cuenta')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $this->cuentas_orden[] = (object)[
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'naturaleza_cuenta' => $documento->naturaleza_cuenta,
                    ];
                }
                unset($documentos);
            });
    }

    private function addResumenCartera()
    {
        $fechaActual = Carbon::now();
        $diasMora = $this->request['dias_mora'];

        $query = $this->resumenCarteraQuery();


        $consulta = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'documento_referencia',
                'nombre_nit',
                'razon_social',
                'fecha_manual',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'anulado',
                'plazo',
                DB::raw("SUM(debito) AS debito"),
                DB::raw("SUM(credito) AS credito"),
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final"),
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final"),
                DB::raw('DATEDIFF(NOW(), fecha_manual) - plazo AS dias_mora'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos')
            )
            ->groupByRaw('id_nit, id_cuenta')
            ->orderByRaw('apartamentos')
        ->havingRaw('saldo_final != 0');

        if ($diasMora) {
            $consulta->havingRaw('dias_mora >= ?', [$diasMora]);
        }

        $consulta->chunk(233, function ($documentos) {
            foreach ($documentos as $documento) {
                $columnaCuenta = $this->buscarCuenta($documento->cuenta);

                if (!$columnaCuenta) continue;

                if (!$this->hasCuentaData($documento->id_nit)) {
                    $this->newCuentaData($documento);
                }

                $this->resultadoCarteraCollection[$documento->id_nit]["cuenta_$columnaCuenta"] = $documento->saldo_final;
                $this->resultadoCarteraCollection[$documento->id_nit]["saldo_final"]+= $documento->saldo_final;
            }
            
            unset($documentos);
        });
    }

    private function addCuentasMeses()
    {
        $query = $this->resumenCarteraQuery([3,4,7,8,11]);
        // $query = $this->resumenCarteraQuery([8]);

        $consulta = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'documento_referencia',
                'nombre_nit',
                'razon_social',
                'fecha_manual',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'anulado',
                'plazo',
                'id_tipo_cuenta',
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS month"),
                DB::raw("SUM(debito) AS debito"),
                DB::raw("SUM(credito) AS credito"),
                DB::raw('SUM(total_abono) AS total_abono'),
                DB::raw('SUM(total_facturas) AS total_facturas'),
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final"),
                DB::raw('DATEDIFF(NOW(), fecha_manual) - plazo AS dias_mora'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos')
            )
            ->groupByRaw("id_nit, id_cuenta, DATE_FORMAT(fecha_manual, '%Y-%m')")
            ->orderByRaw('fecha_manual')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $columnaCuenta = $this->buscarCuenta($documento->cuenta);

                    if (!$columnaCuenta) {
                        continue;
                    }

                    $indice = $documento->year.'_'.$documento->month;

                    if (!array_key_exists($indice, $this->resultadoCarteraCollection)) {
                        $this->newCuentaData($documento);
                    }

                    $valorTotal = $documento->total_facturas;
                    if ($documento->id_tipo_cuenta == 8) {
                        $valorTotal = $documento->total_abono;
                    }

                    $this->resultadoCarteraCollection[$indice]["cuenta_$columnaCuenta"]+= $valorTotal;
                    $this->resultadoCarteraCollection[$indice]["saldo_final"]+= $documento->saldo_final;      
                }
                
                unset($documentos);
            });
    }

    private function addAbonosMeses()
    {
        $query = $this->resumenCarteraQuery([2]);

        $consulta = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'documento_referencia',
                'nombre_nit',
                'razon_social',
                'fecha_manual',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'anulado',
                'plazo',
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS month"),
                DB::raw("SUM(debito) AS debito"),
                DB::raw("SUM(credito) AS credito"),
                DB::raw('SUM(total_abono) AS total_abono'),
                DB::raw('SUM(total_facturas) AS total_facturas'),
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final"),
                DB::raw('DATEDIFF(NOW(), fecha_manual) - plazo AS dias_mora'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos')
            )
            ->groupByRaw("id_nit, id_cuenta, DATE_FORMAT(fecha_manual, '%Y-%m')")
            ->orderByRaw('fecha_manual')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {

                    $indice = $documento->year.'_'.$documento->month;
                    
                    if (!array_key_exists($indice, $this->resultadoCarteraCollection)) {
                        continue;
                    }
   
                    $this->resultadoCarteraCollection[$indice]["fecha_manual"] = $documento->fecha_manual;                    
                    $this->resultadoCarteraCollection[$indice]["total_abono"]+= $documento->total_facturas;
                }
                
                unset($documentos);
            });
    }

    private function descuentoProntoPagoMeses()
    {
        $query = $this->resumenCarteraQuery([11]);

        $consulta = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'documento_referencia',
                'nombre_nit',
                'razon_social',
                'fecha_manual',
                'apartamentos',
                'id_cuenta',
                'cuenta',
                'anulado',
                'plazo',
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS month"),
                DB::raw("SUM(debito) AS debito"),
                DB::raw("SUM(credito) AS credito"),
                DB::raw('SUM(total_abono) AS total_abono'),
                DB::raw('SUM(total_facturas) AS total_facturas'),
                DB::raw("SUM(debito) - SUM(credito) AS saldo_final"),
                DB::raw('DATEDIFF(NOW(), fecha_manual) - plazo AS dias_mora'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos')
            )
            ->groupByRaw("id_nit, id_cuenta, DATE_FORMAT(fecha_manual, '%Y-%m')")
            ->orderByRaw('fecha_manual')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {

                    $indice = $documento->year.'_'.$documento->month;
                    
                    if (!array_key_exists($indice, $this->resultadoCarteraCollection)) {
                        continue;
                    }
                                      
                    $this->resultadoCarteraCollection[$indice]["saldo_final"]-= $documento->total_facturas;
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

    private function addTotalIndividualCartera()
    {
        $this->newTotalData();

        foreach ($this->resultadoCarteraCollection as $key => $data) {
            
            if ($key != '9999999') {
                $fechaDesde = Carbon::createFromFormat('Y_m', $key)->startOfMonth();
                $query = $this->resumenCarteraQueryAnterior([3,4,7,8], $fechaDesde);
    
                $saldo_anterior = DB::connection('sam')
                    ->table(DB::raw("({$query->toSql()}) AS extractodata"))
                    ->mergeBindings($query)
                    ->select(
                        DB::raw('SUM(saldo_anterior) AS saldo_anterior')
                    )->first();

                $saldo_anterior = $saldo_anterior->saldo_anterior ?? 0;
                $saldo_anterior = $saldo_anterior < 0 ? $saldo_anterior : $saldo_anterior * -1;
                $this->resultadoCarteraCollection[$key]['saldo_final']+= $saldo_anterior;
                $this->resultadoCarteraCollection[$key]['saldo_anterior'] = $saldo_anterior;
            }

            for ($i = 1; $i <= 30; $i++) {
                $this->resultadoCarteraCollection['9999999']["cuenta_$i"]+= $data["cuenta_$i"];
            }
            $this->resultadoCarteraCollection['9999999']["total_abono"]+= $data["total_abono"];
        }
    }

    private function resumenCarteraQuery(array $tiposCuenta = [3,4,7,8])
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id",
                "N.id AS id_nit",
                "N.numero_documento",
                DB::raw("CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit"),
                "N.razon_social",
                "N.apartamentos",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "PC.naturaleza_cuenta",
                "PCT.id_tipo_cuenta",
                "DG.documento_referencia",
                "DG.fecha_manual",
                "DG.anulado",
                "DG.debito",
                "DG.credito",
                DB::raw("IF(PC.naturaleza_cuenta = 0, credito, debito) AS total_abono"),
                DB::raw("IF(PC.naturaleza_cuenta = 0, debito, credito) AS total_facturas"),
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->when($this->request['ubicaciones'], function ($query) {
                $query->whereNotNull('N.apartamentos');
            })
            ->when(!$this->request['proveedor'], function ($query) {
                $query->where(function ($q) {
                    $q->where('N.proveedor', 0)
                    ->orWhereNull('N.proveedor');
                });
            })
            ->when($this->request['fecha_hasta'], function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'].' 23:59:59');
			})
            ->when($this->request['fecha_desde'], function ($query) {
				$query->where('DG.fecha_manual', '>=', $this->request['fecha_desde'].' 00:00:00');
			})
            ->when($this->request['id_nit'], function ($query) {
				$query->where('id_nit', $this->request['id_nit']);
			})
            ->whereIn('PCT.id_tipo_cuenta', $tiposCuenta)
            ->where('anulado', 0);
    }

    private function resumenCarteraQueryAnterior(array $tiposCuenta = [3,4,7,8], $fechaManual)
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id",
                "N.id AS id_nit",
                "N.numero_documento",
                DB::raw("CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END AS nombre_nit"),
                "N.razon_social",
                "N.apartamentos",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "PC.naturaleza_cuenta",
                "PCT.id_tipo_cuenta",
                "DG.documento_referencia",
                "DG.fecha_manual",
                "DG.anulado",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS total_abono"),
                DB::raw("0 AS total_facturas"),
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->when($this->request['ubicaciones'], function ($query) {
                $query->whereNotNull('N.apartamentos');
            })
            ->when(!$this->request['proveedor'], function ($query) {
                $query->where(function ($q) {
                    $q->where('N.proveedor', 0)
                    ->orWhereNull('N.proveedor');
                });
            })
            ->where('DG.fecha_manual', '<', $fechaManual)
            ->when($this->request['id_nit'], function ($query) {
				$query->where('id_nit', $this->request['id_nit']);
			})
            ->whereIn('PCT.id_tipo_cuenta', $tiposCuenta)
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
        $fechaManual = null;
        $indice = $documento->id_nit;
        $totalAbono = $documento->total_abono ?? 0;
        
        if ($this->request['id_nit']) {
            $fechaManual = $this->meses[intval($documento->month) - 1].' '.$documento->year;
            $indice = $documento->year.'_'.$documento->month;
            $totalAbono = 0;
        }

        $this->resultadoCarteraCollection[$indice] = [
            'id_resumen_cartera' => $this->id_resultado_cartera,
            'id_nit' => $documento->id_nit, 
            'nombre_nit' => $documento->nombre_nit, 
            'numero_documento' => $this->request['id_nit'] ? $fechaManual : $documento->numero_documento, 
            'saldo_final' => 0,
            'saldo_anterior' => 0,
            'dias_mora' => $documento->dias_mora < 0 ? 0 : $documento->dias_mora, 
            'ubicacion' => $documento->apartamentos,
            'fecha_manual' => $documento->fecha_manual ? Carbon::parse($documento->fecha_manual)->format('Y-m-d') : null,
            'total_abono' => $totalAbono,
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
        $indice = $this->request['id_nit'] ? '9999999' : 'Z999999';

        $this->resultadoCarteraCollection[$indice] = [
            'id_resumen_cartera' => $this->id_resultado_cartera,
            'id_nit' => null, 
            'nombre_nit' => 'TOTAL', 
            'numero_documento' => '', 
            'saldo_final' => 0,
            'saldo_anterior' => 0,
            'dias_mora' => 0, 
            'ubicacion' => '', 
            'fecha_manual' => null,
            'total_abono' => 0,
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