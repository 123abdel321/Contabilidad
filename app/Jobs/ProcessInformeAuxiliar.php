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
use App\Models\Informes\InfAuxiliar;

class ProcessInformeAuxiliar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $empresa;
    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_auxiliar;
    public $timeout = 300;
    public $auxiliares = [];
    public $auxiliarCollection = [];

    public function __construct($request, $id_usuario, $id_empresa, $id_auxiliar)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        $this->id_auxiliar = $id_auxiliar;
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
            
            $this->addTotalNits();
            $this->addTotalsData();
            $this->addDetilsData();
            $this->addTotalNitsData();
            $this->addTotalsPadresData();
            
            ksort($this->auxiliarCollection, SORT_STRING | SORT_FLAG_CASE);
            foreach (array_chunk($this->auxiliarCollection,233) as $auxiliarCollection){
                DB::connection('informes')
                    ->table('inf_auxiliar_detalles')
                    ->insert(array_values($auxiliarCollection));
			}

            InfAuxiliar::where('id', $this->id_auxiliar)->update([
                'estado' => 2
            ]);

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-auxiliar-'.$this->empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Auxiliar generado',
                'id_auxiliar' => $this->id_auxiliar,
                'autoclose' => false
            ]));

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;

            Log::info("Tiempo de ejecución del informe auxiliar: {$executionTime} segundos");
            Log::info("Consumo de memoria del informe auxiliar: {$memoryUsage} bytes");

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
			throw $exception;
        }
    }

    private function documentosAuxiliar()
    {
        $query = $this->auxiliarDocumentosQuery();
        $query->unionAll($this->auxiliarAnteriorQuery());

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
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->groupByRaw('id_cuenta, id_nit, documento_referencia')
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $this->auxiliares[] = (object)[
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'naturaleza_cuenta' => $documento->naturaleza_cuenta,
                        'auxiliar' => $documento->auxiliar,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => $documento->documento_referencia,
                        'id_centro_costos' => $documento->id_centro_costos,
                        'apartamentos' => $documento->apartamentos,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => $documento->concepto,
                        'fecha_manual' => $documento->fecha_manual,
                        'created_at' => $documento->created_at,
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'anulado' => $documento->anulado,
                        'saldo_anterior' => $documento->saldo_anterior,
                        'debito' => $documento->debito,
                        'credito' => $documento->credito,
                        'saldo_final' => $documento->saldo_final,
                        'total_columnas' => $documento->total_columnas,
                    ];
                }
                unset($documentos);//Liberar memoria
            });

        return $this->auxiliares;
    }

    private function addTotalsPadresData()
    {
        $query = $this->auxiliarDocumentosQuery();
        $query->unionAll($this->auxiliarAnteriorQuery());

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'cuenta',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->orderBy('id_cuenta')
            ->groupByRaw('id_cuenta')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $cuentasAsociadas = $this->getCuentas($documento->cuenta); //return ARRAY PADRES CUENTA
                    foreach ($cuentasAsociadas as $cuenta) {
                        if ($this->hasCuentaData($cuenta)) {
                            $this->sumCuentaData($cuenta, $documento);
                        } else {
                            $this->newCuentaData($cuenta, $documento, $cuentasAsociadas);
                        }
                    }
                }
                unset($documentos);//Liberar memoria
            });
    }

    private function addTotalsData()
    {
        $query = $this->auxiliarDocumentosQuery();
        $query->unionAll($this->auxiliarAnteriorQuery());
        
        $totales = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
            ->first();

        $this->newCuentaTotales('9999', $totales);
    }

    private function addDetilsData()
    {
        $query = $this->auxiliarDocumentosQuery();
        $query->unionAll($this->auxiliarAnteriorQuery());

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_cuenta',
                'id_nit',
                'documento_referencia'
            )
            ->orderBy('id_cuenta')
            ->groupByRaw('id_cuenta, id_nit, documento_referencia')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $query = $this->auxiliarDocumentosDetallesQuery($documento);
                    $query->chunk(377, function ($detalles) {
                        foreach ($detalles as $detalle) {
                            $cuentaNumero = 1;
                            $cuentaNueva = "{$detalle->cuenta}-{$detalle->numero_documento}B{$detalle->documento_referencia}B{$cuentaNumero}B";
                            while ($this->hasCuentaData($cuentaNueva)) {
                                $cuentaNumero++;
                                $cuentaNueva = "{$detalle->cuenta}-{$detalle->numero_documento}B{$detalle->documento_referencia}B{$cuentaNumero}B";
                            }
                            $this->newCuentaDetilsData($cuentaNueva, $detalle);
                        }
                        unset($detalles);//Liberar memoria
                    });
                    unset($query);//Liberar memoria
                }
                unset($documentos);//Liberar memoria
            });
    }

    private function auxiliarDocumentosDetallesQuery($auxiliar)
    {
        $documentosDetalleQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                'N.razon_social',
                'N.apartamentos',
                'PC.id AS id_cuenta',
                'PC.cuenta',
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                'PC.nombre AS nombre_cuenta',
                'DG.documento_referencia',
                'DG.id_centro_costos',
                'CC.codigo AS codigo_cecos',
                'CC.nombre AS nombre_cecos',
                'DG.id_comprobante',
                'CO.codigo AS codigo_comprobante',
                'CO.nombre AS nombre_comprobante',
                'DG.consecutivo',
                'DG.concepto',
                'DG.fecha_manual',
                'DG.created_at',
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'DG.created_by',
                'DG.updated_by',
                'DG.anulado',
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito"),
                DB::raw("DG.credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->where('DG.documento_referencia', $auxiliar->documento_referencia)
            ->where('DG.id_cuenta', $auxiliar->id_cuenta)
            ->where('DG.id_nit', $auxiliar->id_nit)
            ->orderByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia, created_at');

        return $documentosDetalleQuery;
    }

    private function addTotalNitsData()
    {
        $query = $this->auxiliarDocumentosQuery();
        $query->unionAll($this->auxiliarAnteriorQuery());

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
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->orderByRaw('id_cuenta')
            ->groupByRaw('id_cuenta, id_nit, documento_referencia')
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $cuentaNumero = 1;
                    $cuentaNueva = "{$documento->cuenta}-{$documento->numero_documento}B{$documento->documento_referencia}B{$cuentaNumero}A";
                    while ($this->hasCuentaData($cuentaNueva)) {
                        $cuentaNumero++;
                        $cuentaNueva = "{$documento->cuenta}-{$documento->numero_documento}B{$documento->documento_referencia}B{$cuentaNumero}A";
                    }

                    $this->newCuentaTotalNitsData($cuentaNueva, $documento);
                }
                unset($documentos);//Liberar memoria
            });
    }

    private function getDetalleNits($auxiliar)
    {
        $query = $this->auxiliarDocumentosQuery($auxiliar->documento_referencia, $auxiliar->id_nit, $auxiliar->id_cuenta);
        $query->unionAll($this->auxiliarAnteriorQuery($auxiliar->documento_referencia, $auxiliar->id_nit, $auxiliar->id_cuenta));

        return DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS auxiliardata"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'id_cuenta',
                'cuenta',
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'apartamentos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                'saldo_anterior',
                'debito',
                'credito',
                DB::raw('saldo_anterior + debito - credito AS saldo_final'),
                'total_columnas',
            )
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->get();
    }

    private function addTotalNits()
    {
        $query = $this->auxiliarDocumentosQuery();
        $query->unionAll($this->auxiliarAnteriorQuery());

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
                'naturaleza_cuenta',
                'auxiliar',
                'nombre_cuenta',
                'documento_referencia',
                'id_centro_costos',
                'codigo_cecos',
                'nombre_cecos',
                'id_comprobante',
                'codigo_comprobante',
                'nombre_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'anulado',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->groupByRaw('id_cuenta, id_nit, documento_referencia')
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {

                    if (substr($documento->cuenta, 0, 2) == '11') {
                        if ((float)$documento->debito == 0.0 && (float)$documento->credito == 0.0){
                            continue;
                        }
                    }

                    $formatoCuenta = "{$documento->cuenta}-{$documento->numero_documento}A";

                    if ($this->hasCuentaData($formatoCuenta)) {
                        $this->sumCuentaData($formatoCuenta, $documento);
                        continue;
                    }
                    $this->newCuentaDataNit($formatoCuenta, $documento);
                }
                unset($documentos);//Liberar memoria
            });

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
            'id_auxiliar' => $this->id_auxiliar,
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'naturaleza_cuenta' => $cuentaData->naturaleza_cuenta,
            'auxiliar' => $cuentaData->auxiliar,
            'nombre_cuenta' => $cuentaData->nombre,
            'apartamento_nit' => '',
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
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            // 'anulado' => '',
            'saldo_anterior' => number_format((float)$auxiliar->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$auxiliar->debito, 2, '.', ''),
            'credito' => number_format((float)$auxiliar->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$auxiliar->saldo_final, 2, '.', ''),
            'detalle' => $detalle,
            'detalle_group' => $detalleGroup,
        ];
    }

    private function newCuentaDataNit($cuenta, $documento)
    {
        $this->auxiliarCollection[$cuenta] = [
            'id_auxiliar' => $this->id_auxiliar,
            'id_nit' => $documento->id_nit,
            'numero_documento' => $documento->numero_documento,
            'nombre_nit' => $documento->nombre_nit,
            'razon_social' => $documento->razon_social,
            'apartamento_nit' => $documento->apartamentos,
            'id_cuenta' => $documento->id_cuenta,
            'cuenta' => $documento->cuenta,
            'naturaleza_cuenta' => $documento->naturaleza_cuenta,
            'auxiliar' => '',
            'nombre_cuenta' => $documento->nombre_cuenta,
            'documento_referencia' => $documento->documento_referencia,
            'saldo_anterior' => $documento->saldo_anterior,
            'id_centro_costos' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'codigo_cecos' => '',
            'nombre_cecos' =>  '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            // 'anulado' => '',
            'debito' => $documento->debito,
            'credito' => $documento->credito,
            'saldo_final' => $documento->saldo_final,
            'detalle' => false,
            'detalle_group' => 'nits-totales',
        ];
    }

    private function newCuentaTotales($cuenta, $totales)
    {
        $this->auxiliarCollection[$cuenta] = [
            'id_auxiliar' => $this->id_auxiliar,
            'id_nit' => '',
            'apartamento_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'naturaleza_cuenta' => '',
            'auxiliar' => '',
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
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            // 'anulado' => '',
            'saldo_anterior' => number_format((float)$totales->saldo_anterior, 2, '.', ''),
            'debito' => number_format((float)$totales->debito, 2, '.', ''),
            'credito' => number_format((float)$totales->credito, 2, '.', ''),
            'saldo_final' => number_format((float)$totales->saldo_final, 2, '.', ''),
            'detalle' => false,
            'detalle_group' => false,
        ];
    }

    private function newCuentaDetilsData($cuenta, $documento)
    {
        $this->auxiliarCollection[$cuenta] = [
            'id_auxiliar' => $this->id_auxiliar,
            'id_nit' => $documento->id_nit,
            'numero_documento' => $documento->numero_documento,
            'nombre_nit' => $documento->nombre_nit,
            'apartamento_nit' => $documento->apartamentos,
            'razon_social' => $documento->razon_social,
            'id_cuenta' => $documento->id_cuenta,
            'cuenta' => $documento->cuenta,
            'naturaleza_cuenta' => $documento->naturaleza_cuenta,
            'auxiliar' => $documento->auxiliar,
            'nombre_cuenta' => $documento->nombre_cuenta,
            'documento_referencia' => $documento->documento_referencia,
            'saldo_anterior' => $documento->saldo_anterior,
            'id_centro_costos' => $documento->id_centro_costos,
            'id_comprobante' => $documento->id_comprobante,
            'codigo_comprobante' => $documento->codigo_comprobante,
            'nombre_comprobante' => $documento->nombre_comprobante,
            'codigo_cecos' => $documento->codigo_cecos,
            'nombre_cecos' =>  $documento->nombre_cecos,
            'consecutivo' => $documento->consecutivo,
            'concepto' => $documento->concepto,
            'fecha_manual' => $documento->fecha_manual,
            'fecha_creacion' => $documento->fecha_creacion,
            'fecha_edicion' => $documento->fecha_edicion,
            'created_by' => $documento->created_by,
            'updated_by' => $documento->updated_by,
            'debito' => $documento->debito,
            'credito' => $documento->credito,
            'saldo_final' => 0,
            'detalle' => false,
            'detalle_group' => false,
        ];
    }

    private function newCuentaTotalNitsData($cuenta, $documento)
    {
        $this->auxiliarCollection[$cuenta] = [
            'id_auxiliar' => $this->id_auxiliar,
            'id_nit' => $documento->id_nit,
            'numero_documento' => $documento->numero_documento,
            'nombre_nit' => $documento->nombre_nit,
            'apartamento_nit' => $documento->apartamentos,
            'razon_social' => $documento->razon_social,
            'id_cuenta' => $documento->id_cuenta,
            'cuenta' => $documento->cuenta,
            'naturaleza_cuenta' => $documento->naturaleza_cuenta,
            'auxiliar' => $documento->auxiliar,
            'nombre_cuenta' => $documento->nombre_cuenta,
            'documento_referencia' => $documento->documento_referencia,
            'saldo_anterior' => $documento->saldo_anterior,
            'id_centro_costos' => $documento->documento_referencia ? $documento->id_centro_costos : '',
            'id_comprobante' => $documento->documento_referencia ? $documento->id_comprobante : '',
            'codigo_comprobante' => $documento->documento_referencia ? $documento->codigo_comprobante : '',
            'nombre_comprobante' => $documento->documento_referencia ? $documento->nombre_comprobante : '',
            'codigo_cecos' => $documento->documento_referencia ? $documento->codigo_cecos : '',
            'nombre_cecos' => $documento->documento_referencia ? $documento->nombre_cecos : '',
            'consecutivo' => $documento->documento_referencia ? $documento->consecutivo : '',
            'concepto' => $documento->documento_referencia ? $documento->concepto : '',
            'fecha_manual' => $documento->documento_referencia ? $documento->fecha_manual : '',
            'fecha_creacion' => $documento->documento_referencia ? $documento->fecha_creacion : NULL,
            'fecha_edicion' => $documento->documento_referencia ? $documento->fecha_edicion : NULL,
            'created_by' => $documento->documento_referencia ? $documento->created_by : NULL,
            'updated_by' => $documento->documento_referencia ? $documento->updated_by : NULL,
            // 'anulado' => $detalle[0]->documento_referencia ? $detalle[0]->anulado : '',
            'debito' => $documento->debito,
            'credito' => $documento->credito,
            'saldo_final' => $documento->saldo_final,
            'detalle' => false,
            'detalle_group' => 'nits',
        ];
    }

    private function auxiliarDocumentosQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
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
                "N.razon_social",
                "N.apartamentos",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("SUM(DG.debito) AS debito"),
                DB::raw("SUM(DG.credito) AS credito"),
                DB::raw("SUM(DG.debito) - SUM(DG.credito) AS saldo_final"),
                DB::raw("COUNT(DG.id) AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			})
            ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
            ->when($documento_referencia ? $documento_referencia : false, function ($query, $documento_referencia) {
				$query->where('DG.documento_referencia', $documento_referencia);
			})
            ->when($id_nit ? $id_nit : false, function ($query, $id_nit) {
				$query->where('DG.id_nit', $id_nit);
			})
            ->when($id_cuenta ? $id_cuenta : false, function ($query, $id_cuenta) {
				$query->where('DG.id_cuenta', $id_cuenta);
			})
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');

        return $documentosQuery;
    }

    private function auxiliarAnteriorQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
    {
        $anterioresQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.apartamentos",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("SUM(debito) - SUM(credito) AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			})
            ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
            ->when($documento_referencia ? $documento_referencia : false, function ($query, $documento_referencia) {
				$query->where('DG.documento_referencia', $documento_referencia);
			})
            ->when($id_nit ? $id_nit : false, function ($query, $id_nit) {
				$query->where('DG.id_nit', $id_nit);
			})
            ->when($id_cuenta ? $id_cuenta : false, function ($query, $id_cuenta) {
				$query->where('DG.id_cuenta', $id_cuenta);
			})
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');
            // ->groupBy('DG.id_cuenta', 'DG.id_nit', 'DG.documento_referencia');

        return $anterioresQuery;
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

    private function hasCuentaData($cuenta)
	{
		return isset($this->auxiliarCollection[$cuenta]);
	}

    private function sumCuentaData($cuenta, $auxiliar)
    {
        $this->auxiliarCollection[$cuenta]['saldo_anterior']+= number_format((float)$auxiliar->saldo_anterior, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['debito']+= number_format((float)$auxiliar->debito, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['credito']+= number_format((float)$auxiliar->credito, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['saldo_final']+= number_format((float)$auxiliar->saldo_final, 2, '.', '');
    }

    public function failed($exception)
    {
        DB::connection('informes')->rollBack();
        
        // Si no tenemos la empresa, intentamos obtenerla
        if (!$this->empresa && $this->id_empresa) {
            $this->empresa = Empresa::find($this->id_empresa);
        }

        $token_db = $this->empresa ? $this->empresa->token_db : 'unknown';

        InfAuxiliar::where('id', $this->id_auxiliar)->update([
            'estado' => 0
        ]);

        event(new PrivateMessageEvent(
            'informe-auxiliar-'.$token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'error',
                'mensaje' => 'Error al generar el informe: '.$exception->getMessage(),
                'titulo' => 'Error en proceso',
                'autoclose' => false
            ]
        ));

        // Registrar el error en los logs
        logger()->error("Error en ProcessInformeAuxiliar: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }
}
