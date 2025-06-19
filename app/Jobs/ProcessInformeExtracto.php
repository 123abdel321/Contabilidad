<?php

namespace App\Jobs;

use DB;
use Exception;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
use App\Models\Informes\InfExtracto;

class ProcessInformeExtracto
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

	public $empresa;
    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_extracto;
    public $timeout = 300;
    public $extractoMeses = [];
    public $extractoCollection = [];
    public $meses = ["ENERO","FEBRERO","MARZO","ABRIL","MAYO","JUNIO","JULIO","AGOSTO","SEPTIEMBRE","OCTUBRE","NOVIEMBRE","DICIEMBRE"];

    public function __construct($request, $id_usuario, $id_empresa, $id_extracto)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        $this->id_extracto = $id_extracto;
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

            $this->ordenarMeses();
            
            $this->addMesesData();
            $this->addCuentasData();
            $this->addNitsCuentasData();
            $this->addNitsCuentasDetalleData();
            $this->addTotalData();

            ksort($this->extractoCollection, SORT_STRING | SORT_FLAG_CASE);

            foreach (array_chunk($this->extractoCollection,233) as $extractoCollection){
                DB::connection('informes')
                    ->table('inf_extracto_detalles')
                    ->insert(array_values($extractoCollection));
			}

            InfExtracto::where('id', $this->id_extracto)->update([
                'estado' => 2
            ]);

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-extracto-'.$this->empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Extracto generado',
                'id_extracto' => $this->id_extracto,
                'autoclose' => false
            ]));

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;
            
            Log::info("Informe extracto ejecutado en {$executionTime} segundos, usando {$memoryUsage} bytes de memoria.");

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
			throw $exception;
        }
    }

    private function ordenarMeses()
    {
        $fechaDesde = Carbon::parse($this->request['fecha_desde']);
        $fechaHasta = Carbon::parse($this->request['fecha_hasta']);

        if ($fechaDesde->format('Y-m') === $fechaHasta->format('Y-m')) {
            $this->extractoMeses[] = (object)[
                'fecha_desde' => $fechaDesde->copy()->startOfDay()->format('Y-m-d H:i:s'),
                'fecha_hasta' => $fechaHasta->copy()->endOfDay()->format('Y-m-d H:i:s')
            ];
        } else {
            // Crear un período mensual desde el inicio del mes de fecha_desde hasta el fin del mes de fecha_hasta
            $periodo = CarbonPeriod::create(
                $fechaDesde->copy()->startOfMonth(),
                '1 month',
                $fechaHasta->copy()->endOfMonth()
            );

            foreach ($periodo as $fecha) {
                $inicioMes = $fecha->copy()->startOfMonth()->startOfDay();
                $finMes = $fecha->copy()->endOfMonth()->endOfDay();
                
                // Si es el último mes (más reciente), usar la fecha_hasta proporcionada
                if ($fecha->format('Y-m') === $fechaHasta->format('Y-m')) {
                    $finMes = $fechaHasta->copy()->endOfDay();
                }
                
                // Si es el primer mes (más antiguo), usar la fecha_desde proporcionada
                if ($fecha->format('Y-m') === $fechaDesde->format('Y-m')) {
                    $inicioMes = max($inicioMes, $fechaDesde->copy()->startOfDay());
                }
                
                $this->extractoMeses[] = (object)[
                    'fecha_desde' => $inicioMes->format('Y-m-d H:i:s'),
                    'fecha_hasta' => $finMes->format('Y-m-d H:i:s')
                ];
            }
        }

        $this->extractoMeses = array_reverse($this->extractoMeses);
    }
    
    private function addMesesData()
    {
        foreach ($this->extractoMeses as $extractoMes) {
            $query = $this->extractoDocumentosQuery($extractoMes);
            $query->unionAll($this->extractoAnteriorQuery($extractoMes));

            DB::connection('sam')
                ->table(DB::raw("({$query->toSql()}) AS extractodata"))
                ->mergeBindings($query)
                ->select(
                    'mes',
                    'fecha_manual',
                    DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                    DB::raw('SUM(debito) AS debito'),
                    DB::raw('SUM(credito) AS credito'),
                    DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                    DB::raw('SUM(total_columnas) AS total_columnas')
                )
                ->orderBy('fecha_manual')
                ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
                ->chunk(233, function ($documentos) use ($extractoMes) {
                    foreach ($documentos as $documento) {
                        $inicioMes = date('Y-m', strtotime($extractoMes->fecha_desde));
                        $inicioMesFormat = date('m', strtotime($extractoMes->fecha_desde));
    
                        // $dataMesHeader = $this->getFormatCollection();
                        // $dataMesHeader["cuenta"] = $this->meses[intval($documento->mes)-1];
                        // $dataMesHeader["nivel"] = "1";
    
                        $dataMesFooter = $this->getFormatCollection();
                        $dataMesFooter["cuenta"] = $this->meses[intval($inicioMesFormat)-1];
                        $dataMesFooter["saldo_anterior"] = $documento->saldo_anterior;
                        $dataMesFooter["debito"] = $documento->debito;
                        $dataMesFooter["credito"] = $documento->credito;
                        $dataMesFooter["saldo_final"] = $documento->saldo_final;
                        $dataMesFooter["nivel"] = "1";
    
                        // $this->extractoCollection["$inicioMes-A"] = $dataMesHeader;
                        $this->extractoCollection["$inicioMes-A"] = $dataMesFooter;
                    }
                    unset($documentos);//Liberar memoria
                });
        }

    }

    private function addCuentasData()
    {
        foreach ($this->extractoMeses as $extractoMes) {
            $query = $this->extractoDocumentosQuery($extractoMes);
            $query->unionAll($this->extractoAnteriorQuery($extractoMes));

            DB::connection('sam')
                ->table(DB::raw("({$query->toSql()}) AS extractodata"))
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
                    DB::raw('COUNT(consecutivo) AS total_columnas')
                )
                ->orderBy('id_cuenta')
                ->groupBy(
                    'id_cuenta'
                )
                ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
                ->chunk(233, function ($documentos) use($extractoMes) {
                    foreach ($documentos as $documento) {
                        
                        $inicioMes = date('Y-m', strtotime($extractoMes->fecha_desde));
                        $cuentasAsociadas = $this->getCuentas($documento->cuenta);
                        $addFirstData = true;
                        
                        foreach ($cuentasAsociadas as $cuentaData) {
                            $cuenta = "{$inicioMes}-B{$cuentaData}";
                            if ($this->hasCuentaData($cuenta)) {
                                $this->sumCuentaData($cuenta, $documento);
                            } else {
                                $dataCuenta = $this->getFormatPadreCollection($cuentaData, $documento);
                                $dataCuenta['cuenta'] = $cuentaData;

                                //AGREGAR DATA ADICION A LA CUENTA AUXILIAR
                                if ($addFirstData) {
                                    // $dataCuenta['codigo_comprobante'] = $documento->codigo_comprobante;
                                    // $dataCuenta['nombre_comprobante'] = $documento->nombre_comprobante;
                                    // $dataCuenta['numero_documento'] = $documento->numero_documento;
                                    // $dataCuenta['nombre_nit'] = $documento->nombre_nit;
                                    // $dataCuenta['razon_social'] = $documento->razon_social;
                                    // $dataCuenta['id_nit'] = $documento->id_nit;
                                    // $dataCuenta['consecutivo'] = $documento->consecutivo;
                                    // $dataCuenta['fecha_manual'] = $documento->fecha_manual;
                                    // $dataCuenta['concepto'] = $documento->concepto;
                                    // $dataCuenta['fecha_creacion'] = $documento->fecha_creacion;
                                    // $dataCuenta['fecha_edicion'] = $documento->fecha_edicion;
                                    // $dataCuenta['created_by'] = $documento->created_by;
                                    // $dataCuenta['updated_by'] = $documento->updated_by;
                                }
                                $this->extractoCollection[$cuenta] = $dataCuenta;
                                $addFirstData = false;
                            }
                        }
                    }
                });
        }

    }

    private function addNitsCuentasData()
    {
        foreach ($this->extractoMeses as $extractoMes) {
            $query = $this->extractoDocumentosQuery($extractoMes);
            $query->unionAll($this->extractoAnteriorQuery($extractoMes));

            DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS extractodata"))
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
                DB::raw('COUNT(total_columnas) AS total_columnas')
            )
            ->orderByRaw('id_cuenta')
            ->groupByRaw('id_cuenta, id_nit, documento_referencia')
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) use($extractoMes) {
                
                foreach ($documentos as $documento) {
                    $inicioMes = date('Y-m', strtotime($extractoMes->fecha_desde));
                    $cuentaNueva = "{$inicioMes}-B{$documento->cuenta}";
                    if ($documento->documento_referencia) {
                        $cuentaNueva.= "-{$documento->documento_referencia}";
                    }

                    $this->extractoCollection[$cuentaNueva] = $this->getFormatNitCuentaCollection($documento);
                }
                unset($documentos);//Liberar memoria
            });
        }
    }

    private function addNitsCuentasDetalleData()
    {
        foreach ($this->extractoMeses as $extractoMes) {
            $query = $this->extractoDocumentosQuery($extractoMes);
            $query->unionAll($this->extractoAnteriorQuery($extractoMes));

            DB::connection('sam')
                ->table(DB::raw("({$query->toSql()}) AS extractodata"))
                ->mergeBindings($query)
                ->select(
                    'id_cuenta',
                    'id_nit',
                    'documento_referencia'
                )
                ->orderBy('consecutivo', 'ASC')
                ->groupByRaw('id_cuenta, id_nit, documento_referencia')
                ->chunk(233, function ($documentos) use($extractoMes) {
                    foreach ($documentos as $documento) {
                        $query = $this->extractoDocumentosDetallesQuery($documento, $extractoMes);
                        $query->chunk(377, function ($detalles) use($extractoMes) {
                            foreach ($detalles as $detalle) {
                                $cuentaNumero = 1;

                                $inicioMes = date('Y-m', strtotime($extractoMes->fecha_desde));
                                $cuentaNueva = "{$inicioMes}-B{$detalle->cuenta}";
                                if ($detalle->documento_referencia) {
                                    $cuentaNueva.= "-{$detalle->documento_referencia}";
                                }
                                $cuentaNueva.= "-A{$cuentaNumero}";
                                while ($this->hasCuentaData($cuentaNueva)) {
                                    $cuentaNumero++;
                                    $cuentaNueva = "{$inicioMes}-B{$detalle->cuenta}";
                                    if ($detalle->documento_referencia) {
                                        $cuentaNueva.= "-{$detalle->documento_referencia}";
                                    }
                                    $cuentaNueva.= "-A{$cuentaNumero}";
                                }
                                $this->extractoCollection[$cuentaNueva] = $this->getFormatDetailDocumentoCollection($detalle);
                            }
                            unset($detalles);//Liberar memoria
                        });
                        unset($query);//Liberar memoria
                    }
                    unset($documentos);//Liberar memoria
                });
        }
    }

    private function addTotalData()
    {
        $extractoMes = (object)[
            'fecha_desde' => $this->request['fecha_desde'],
            'fecha_hasta' => $this->request['fecha_hasta']
        ];

        $query = $this->extractoDocumentosQuery($extractoMes);
        $query->unionAll($this->extractoAnteriorQuery($extractoMes));

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

        $this->extractoCollection['9999'] = $this->getFormatTotalesCollection($totales);
    }

    private function extractoDocumentosQuery($meses)
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
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
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS mes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '>=', $meses->fecha_desde)
            ->where('DG.fecha_manual', '<=', $meses->fecha_hasta)
            ->whereIn('PCT.id_tipo_cuenta', [3,4,7,8])
            ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
            ->when($this->request['documento_referencia'] ? true : false, function ($query) {
				$query->where('DG.documento_referencia', $this->request['documento_referencia']);
			});
    }

    private function extractoAnteriorQuery($meses)
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
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
                DB::raw("DATE_FORMAT(fecha_manual, '%m') AS mes"),
                DB::raw("DATE_FORMAT(fecha_manual, '%Y') AS year"),
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->where('DG.fecha_manual', '<', $meses->fecha_desde)
            ->whereIn('PCT.id_tipo_cuenta', [3,4,7,8])
            ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
            ->when($this->request['documento_referencia'] ? true : false, function ($query) {
				$query->where('DG.documento_referencia', $this->request['documento_referencia']);
			});
    }

    private function extractoDocumentosDetallesQuery($extracto, $meses)
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
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->where('anulado', 0)
            // ->whereIn('PCT.id_tipo_cuenta', [3,4,7,8])
            ->where('DG.fecha_manual', '>=', $meses->fecha_desde)
            ->where('DG.fecha_manual', '<=', $meses->fecha_hasta)
            ->where('DG.documento_referencia', $extracto->documento_referencia)
            ->where('DG.id_cuenta', $extracto->id_cuenta)
            ->where('DG.id_nit', $extracto->id_nit)
            ->orderByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia, created_at');

        return $documentosDetalleQuery;
    }

    private function getFormatCollection()
    {
        return [
            'id_extracto' => $this->id_extracto,
            'id_nit' => "",
            'numero_documento' => "",
            'nombre_nit' => "",
            'apartamento_nit' => "",
            'razon_social' => "",
            'id_cuenta' => "",
            'cuenta' => "",
            'naturaleza_cuenta' => "",
            'auxiliar' => "",
            'nombre_cuenta' => "",
            'documento_referencia' => "",
            'id_centro_costos' => "",
            'id_comprobante' => "",
            'codigo_comprobante' => "",
            'nombre_comprobante' => "",
            'codigo_cecos' => "",
            'nombre_cecos' => "",
            'consecutivo' => "",
            'concepto' => "",
            'fecha_manual' => "",
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            'saldo_anterior' => 0,
            'debito' => 0,
            'credito' => 0,
            'saldo_final' => 0,
            'nivel' => 0
        ];
    }

    private function getFormatPadreCollection($cuenta, $documento)
    {
        $cuentaData = PlanCuentas::whereCuenta($cuenta)->first();
        if(!$cuentaData) return;

        return [
            'id_extracto' => $this->id_extracto,
            'id_nit' => "",
            'numero_documento' => "",
            'nombre_nit' => "",
            'apartamento_nit' => "",
            'razon_social' => "",
            'id_cuenta' => $cuentaData->id,
            'cuenta' => $cuentaData->cuenta,
            'naturaleza_cuenta' => $cuentaData->naturaleza_cuenta,
            'auxiliar' => $cuentaData->auxiliar,
            'nombre_cuenta' => $cuentaData->nombre,
            'documento_referencia' => "",
            'id_centro_costos' => "",
            'id_comprobante' => "",
            'codigo_comprobante' => "",
            'nombre_comprobante' => "",
            'codigo_cecos' => "",
            'nombre_cecos' => "",
            'consecutivo' => "",
            'concepto' => "",
            'fecha_manual' => "",
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            'saldo_anterior' => $documento->saldo_anterior,
            'debito' => $documento->debito,
            'credito' => $documento->credito,
            'saldo_final' => $documento->saldo_final,
            'nivel' => 2
        ];
    }

    private function getFormatNitCuentaCollection($documento)
    {
        return [
            'id_extracto' => $this->id_extracto,
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
            'documento_referencia' => '',
            'id_centro_costos' => $documento->documento_referencia ? $documento->id_centro_costos : '',
            'id_comprobante' => $documento->documento_referencia ? $documento->id_comprobante : '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'codigo_cecos' => $documento->documento_referencia ? $documento->codigo_cecos : '',
            'nombre_cecos' => $documento->documento_referencia ? $documento->nombre_cecos : '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => NULL,
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
            'saldo_anterior' => $documento->saldo_anterior,
            'debito' => $documento->debito,
            'credito' => $documento->credito,
            'saldo_final' => $documento->saldo_final,
            'nivel' => 3
        ];
    }

    private function getFormatDetailDocumentoCollection($documento)
    {
        return [
            'id_extracto' => $this->id_extracto,
            'id_nit' => $documento->id_nit,
            'numero_documento' => '',
            'nombre_nit' => '',
            'apartamento_nit' => '',
            'razon_social' => '',
            'id_cuenta' => $documento->id_cuenta,
            'cuenta' => '',
            'naturaleza_cuenta' => $documento->naturaleza_cuenta,
            'auxiliar' => $documento->auxiliar,
            'nombre_cuenta' => '',
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
            'nivel' => 4 
        ];
    }

    private function getFormatTotalesCollection($totales)
    {
        return [
            'id_extracto' => $this->id_extracto,
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
            'saldo_anterior' => $totales ? number_format((float)$totales->saldo_anterior, 2, '.', '') : 0,
            'debito' => $totales ? number_format((float)$totales->debito, 2, '.', '') : 0,
            'credito' => $totales ? number_format((float)$totales->credito, 2, '.', '') : 0,
            'saldo_final' => $totales ? number_format((float)$totales->saldo_final, 2, '.', '') : 0,
            'nivel' => 5
        ];
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

    private function hasCuentaData($cuenta)
	{
		return isset($this->extractoCollection[$cuenta]);
	}

    private function sumCuentaData($cuenta, $extracto)
    {
        $this->extractoCollection[$cuenta]['saldo_anterior']+= number_format((float)$extracto->saldo_anterior, 2, '.', '');
        $this->extractoCollection[$cuenta]['debito']+= number_format((float)$extracto->debito, 2, '.', '');
        $this->extractoCollection[$cuenta]['credito']+= number_format((float)$extracto->credito, 2, '.', '');
        $this->extractoCollection[$cuenta]['saldo_final']+= number_format((float)$extracto->saldo_final, 2, '.', '');
    }

    public function failed($exception)
    {
        DB::connection('informes')->rollBack();
        
        // Si no tenemos la empresa, intentamos obtenerla
        if (!$this->empresa && $this->id_empresa) {
            $this->empresa = Empresa::find($this->id_empresa);
        }

        $token_db = $this->empresa ? $this->empresa->token_db : 'unknown';

        InfExtracto::where('id', $this->id_extracto)->update([
            'estado' => 0
        ]);

        event(new PrivateMessageEvent(
            'informe-extracto-'.$token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'error',
                'mensaje' => 'Error al generar el informe: '.$exception->getMessage(),
                'titulo' => 'Error en proceso',
                'autoclose' => false
            ]
        ));

        // Registrar el error en los logs
        logger()->error("Error en ProcessInformeExtracto: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }

}
