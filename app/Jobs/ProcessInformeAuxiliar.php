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
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfAuxiliar;

class ProcessInformeAuxiliar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
    public $id_empresa;
    public $id_auxiliar;
    protected $auxiliarCollection = [];
    protected $cuentasPadres = [];

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
    }

    public function handle()
    {
        $empresa = Empresa::find($this->id_empresa);
        
        // Configurar conexión a la base de datos
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        DB::connection('informes')->beginTransaction();
        
        try {
            // Crear registro principal del auxiliar
            $auxiliar = InfAuxiliar::create([
                'id_empresa' => $this->id_empresa,
                'fecha_desde' => $this->request['fecha_desde'],
                'fecha_hasta' => $this->request['fecha_hasta'],
                'id_cuenta' => $this->request['id_cuenta'],
                'id_nit' => $this->request['id_nit']
            ]);

            $this->id_auxiliar = $auxiliar->id;
            
            // Procesar datos en chunks para reducir memoria
            $this->processAuxiliarData();

            // Insertar datos en chunks
            foreach (array_chunk($this->auxiliarCollection, 233, true) as $chunk) {
                DB::connection('informes')
                    ->table('inf_auxiliar_detalles')
                    ->insert(array_values($chunk));
            }

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-auxiliar-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con éxito!',
                'titulo' => 'Auxiliar generado',
                'id_auxiliar' => $this->id_auxiliar,
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
            throw $exception;
        }
    }

    protected function processAuxiliarData()
    {
        $totals = [
            'debito' => 0,
            'credito' => 0,
            'saldo_anterior' => 0,
            'saldo_final' => 0
        ];

        // Procesar documentos en chunks
        $this->processDocumentosChunks(function($documentos) use (&$totals) {
            foreach ($documentos as $documento) {
                // Acumular totales
                $totals['debito'] += $documento->debito;
                $totals['credito'] += $documento->credito;
                $totals['saldo_final'] += $documento->saldo_final;
                $totals['saldo_anterior'] += $documento->saldo_anterior;

                // Procesar documento individual
                $this->processDocumento($documento);
            }
        });

        // Agregar totales al final
        $this->addTotalGenerales($totals);
    }

    protected function processDocumentosChunks(callable $callback)
    {
        $query = $this->buildAuxiliarQuery();
        
        // Procesar en chunks para reducir memoria
        $query->chunk(233, function($documentos) use ($callback) {
            $callback($documentos);
        });
    }

    protected function buildAuxiliarQuery()
    {
        $query = DB::connection('sam')
            ->table(DB::raw("({$this->getUnionQuery()->toSql()}) AS auxiliardata"))
            ->mergeBindings($this->getUnionQuery())
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
            ->havingRaw('saldo_anterior != 0 OR debito != 0 OR credito != 0 OR saldo_final != 0');

        return $query;
    }

    protected function getUnionQuery()
    {
        $documentosQuery = $this->buildDocumentosQuery();
        $anterioresQuery = $this->buildAnterioresQuery();

        return $documentosQuery->unionAll($anterioresQuery);
    }

    protected function buildDocumentosQuery()
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("COALESCE(NULLIF(N.razon_social, ''), CONCAT_WS(' ', N.primer_nombre, N.primer_apellido)) AS nombre_nit"),
                'N.razon_social',
                'N.apartamentos',
                'PC.id AS id_cuenta',
                'PC.cuenta',
                'PC.naturaleza_cuenta',
                'PC.auxiliar',
                'PC.nombre AS nombre_cuenta',
                'DG.documento_referencia',
                'DG.id_centro_costos',
                'CC.codigo AS codigo_cecos',
                'CC.nombre AS nombre_cecos',
                'CO.id AS id_comprobante',
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
                DB::raw("SUM(DG.debito) AS debito"),
                DB::raw("SUM(DG.credito) AS credito"),
                DB::raw("SUM(DG.debito) - SUM(DG.credito) AS saldo_final"),
                DB::raw("COUNT(DG.id) AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('DG.anulado', 0)
            ->whereBetween('DG.fecha_manual', [$this->request['fecha_desde'], $this->request['fecha_hasta']])
            ->when($this->request['id_cuenta'] ?? false, function($q) {
                $q->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->when($this->request['id_nit'] ?? false, function($q) {
                $q->where('DG.id_nit', $this->request['id_nit']);
            })
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');
    }

    protected function buildAnterioresQuery()
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("COALESCE(NULLIF(N.razon_social, ''), CONCAT_WS(' ', N.primer_nombre, N.primer_apellido)) AS nombre_nit"),
                'N.razon_social',
                'N.apartamentos',
                'PC.id AS id_cuenta',
                'PC.cuenta',
                'PC.naturaleza_cuenta',
                'PC.auxiliar',
                'PC.nombre AS nombre_cuenta',
                'DG.documento_referencia',
                'DG.id_centro_costos',
                'CC.codigo AS codigo_cecos',
                'CC.nombre AS nombre_cecos',
                'CO.id AS id_comprobante',
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
                DB::raw("SUM(DG.debito) - SUM(DG.credito) AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('DG.anulado', 0)
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when($this->request['id_cuenta'] ?? false, function($q) {
                $q->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->when($this->request['id_nit'] ?? false, function($q) {
                $q->where('DG.id_nit', $this->request['id_nit']);
            })
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');
    }

    protected function processDocumento($documento)
    {
        // Procesar totales por NIT
        $this->addTotalNit($documento);
        
        // Procesar detalles del documento si tiene movimientos
        if ($documento->total_columnas > 0) {
            $this->addDocumentoDetalles($documento);
        }
        
        // Procesar totales por cuenta padre
        $this->addTotalesCuentasPadres($documento);
    }

    protected function addTotalNit($documento)
    {
        if (substr($documento->cuenta, 0, 2) == '11' && $documento->debito == 0 && $documento->credito == 0) {
            return;
        }

        $key = $documento->cuenta.'-'.$documento->numero_documento.'A';
        
        if (!isset($this->auxiliarCollection[$key])) {
            $this->auxiliarCollection[$key] = [
                'id_auxiliar' => $this->id_auxiliar,
                'id_nit' => $documento->id_nit,
                'numero_documento' => $documento->numero_documento,
                'nombre_nit' => $documento->nombre_nit,
                'apartamento_nit' => $documento->apartamentos,
                'razon_social' => $documento->razon_social,
                'id_cuenta' => $documento->id_cuenta,
                'cuenta' => $documento->cuenta,
                'naturaleza_cuenta' => '',
                'auxiliar' => '',
                'nombre_cuenta' => $documento->nombre_cuenta,
                'documento_referencia' => '',
                'saldo_anterior' => 0,
                'id_centro_costos' => '',
                'id_comprobante' => '',
                'codigo_comprobante' => '',
                'nombre_comprobante' => '',
                'codigo_cecos' => '',
                'nombre_cecos' => '',
                'consecutivo' => '',
                'concepto' => '',
                'fecha_manual' => '',
                'fecha_creacion' => null,
                'fecha_edicion' => null,
                'created_by' => null,
                'updated_by' => null,
                'debito' => 0,
                'credito' => 0,
                'saldo_final' => 0,
                'detalle' => false,
                'detalle_group' => 'nits-totales',
            ];
        }

        $this->auxiliarCollection[$key]['saldo_anterior'] += $documento->saldo_anterior;
        $this->auxiliarCollection[$key]['debito'] += $documento->debito;
        $this->auxiliarCollection[$key]['credito'] += $documento->credito;
        $this->auxiliarCollection[$key]['saldo_final'] += $documento->saldo_final;
    }

    protected function addDocumentoDetalles($documento)
    {
        $detalles = $this->getDocumentoDetalles($documento);
        
        foreach ($detalles as $index => $detalle) {
            $key = $documento->cuenta.'-'.$documento->numero_documento.'B'.$documento->documento_referencia.'B'.($index+1).'B';
            
            $this->auxiliarCollection[$key] = [
                'id_auxiliar' => $this->id_auxiliar,
                'id_nit' => $detalle->id_nit,
                'numero_documento' => $detalle->numero_documento,
                'nombre_nit' => $detalle->nombre_nit,
                'apartamento_nit' => $detalle->apartamentos,
                'razon_social' => $detalle->razon_social,
                'id_cuenta' => $detalle->id_cuenta,
                'cuenta' => $detalle->cuenta,
                'naturaleza_cuenta' => $detalle->naturaleza_cuenta,
                'auxiliar' => $detalle->auxiliar,
                'nombre_cuenta' => $detalle->nombre_cuenta,
                'documento_referencia' => $detalle->documento_referencia,
                'saldo_anterior' => $detalle->saldo_anterior,
                'id_centro_costos' => $detalle->id_centro_costos,
                'id_comprobante' => $detalle->id_comprobante,
                'codigo_comprobante' => $detalle->codigo_comprobante,
                'nombre_comprobante' => $detalle->nombre_comprobante,
                'codigo_cecos' => $detalle->codigo_cecos,
                'nombre_cecos' => $detalle->nombre_cecos,
                'consecutivo' => $detalle->consecutivo,
                'concepto' => $detalle->concepto,
                'fecha_manual' => $detalle->fecha_manual,
                'fecha_creacion' => $detalle->fecha_creacion,
                'fecha_edicion' => $detalle->fecha_edicion,
                'created_by' => $detalle->created_by,
                'updated_by' => $detalle->updated_by,
                'debito' => $detalle->debito,
                'credito' => $detalle->credito,
                'saldo_final' => 0,
                'detalle' => false,
                'detalle_group' => false,
            ];
        }
    }

    protected function getDocumentoDetalles($documento)
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("COALESCE(NULLIF(N.razon_social, ''), CONCAT_WS(' ', N.primer_nombre, N.primer_apellido)) AS nombre_nit"),
                'N.razon_social',
                'N.apartamentos',
                'PC.id AS id_cuenta',
                'PC.cuenta',
                'PC.naturaleza_cuenta',
                'PC.auxiliar',
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
                'DG.debito',
                'DG.credito',
                DB::raw("DG.debito - DG.credito AS saldo_final")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('DG.anulado', 0)
            ->whereBetween('DG.fecha_manual', [$this->request['fecha_desde'], $this->request['fecha_hasta']])
            ->where('DG.documento_referencia', $documento->documento_referencia)
            ->where('DG.id_cuenta', $documento->id_cuenta)
            ->where('DG.id_nit', $documento->id_nit)
            ->orderByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia, DG.created_at')
            ->get();
    }

    protected function addTotalesCuentasPadres($documento)
    {
        $cuentasPadres = $this->getCuentasPadres($documento->cuenta);
        
        foreach ($cuentasPadres as $cuentaPadre) {
            $this->addCuentaPadre($cuentaPadre, $documento);
        }
    }

    protected function getCuentasPadres($cuenta)
    {
        if (!isset($this->cuentasPadres[$cuenta])) {
            $lengths = [1, 2, 4, 6];
            $cuentas = [];
            
            foreach ($lengths as $length) {
                if (strlen($cuenta) > $length) {
                    $cuentas[] = substr($cuenta, 0, $length);
                }
            }
            
            $cuentas[] = $cuenta;
            $this->cuentasPadres[$cuenta] = $cuentas;
        }
        
        return $this->cuentasPadres[$cuenta];
    }

    protected function addCuentaPadre($cuentaPadre, $documento)
    {
        if (!isset($this->auxiliarCollection[$cuentaPadre])) {
            $cuentaData = PlanCuentas::where('cuenta', $cuentaPadre)->first();
            
            if (!$cuentaData) {
                return;
            }
            
            $isDetail = strlen($cuentaPadre) >= strlen($documento->cuenta);
            $isDetailGroup = strlen($cuentaPadre) >= (strlen($documento->cuenta) - 2);
            
            $this->auxiliarCollection[$cuentaPadre] = [
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
                'nombre_cecos' => '',
                'documento_referencia' => '',
                'id_comprobante' => '',
                'codigo_comprobante' => '',
                'nombre_comprobante' => '',
                'consecutivo' => '',
                'concepto' => '',
                'fecha_manual' => '',
                'fecha_creacion' => null,
                'fecha_edicion' => null,
                'created_by' => null,
                'updated_by' => null,
                'saldo_anterior' => 0,
                'debito' => 0,
                'credito' => 0,
                'saldo_final' => 0,
                'detalle' => $isDetail,
                'detalle_group' => $isDetailGroup,
            ];
        }
        
        $this->auxiliarCollection[$cuentaPadre]['saldo_anterior'] += $documento->saldo_anterior;
        $this->auxiliarCollection[$cuentaPadre]['debito'] += $documento->debito;
        $this->auxiliarCollection[$cuentaPadre]['credito'] += $documento->credito;
        $this->auxiliarCollection[$cuentaPadre]['saldo_final'] += $documento->saldo_final;
    }

    protected function addTotalGenerales($totals)
    {
        $this->auxiliarCollection['9999'] = [
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
            'nombre_cecos' => '',
            'documento_referencia' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => null,
            'fecha_edicion' => null,
            'created_by' => null,
            'updated_by' => null,
            'saldo_anterior' => number_format((float)$totals['saldo_anterior'], 2, '.', ''),
            'debito' => number_format((float)$totals['debito'], 2, '.', ''),
            'credito' => number_format((float)$totals['credito'], 2, '.', ''),
            'saldo_final' => number_format((float)$totals['saldo_final'], 2, '.', ''),
            'detalle' => false,
            'detalle_group' => false,
        ];
    }
}