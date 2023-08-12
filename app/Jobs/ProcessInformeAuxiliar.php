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
use App\Models\Informes\InfAuxiliar;

class ProcessInformeAuxiliar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_auxiliar;
    public $auxiliares = [];
    public $auxiliarCollection = [];

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

            $auxiliar = InfAuxiliar::create([
				'id_empresa' => $this->id_empresa,
				'fecha_desde' => $this->request['fecha_desde'],
				'fecha_hasta' => $this->request['fecha_hasta'],
				'id_cuenta' => $this->request['id_cuenta'],
				'id_nit' => $this->request['id_nit']
			]);

            $this->id_auxiliar = $auxiliar->id;
            
            $auxiliares = $this->documentosAuxiliar();

            foreach ($auxiliares as $auxiliar) {
                $cuentasAsociadas = $this->getCuentas($auxiliar->cuenta); //return ARRAY PADRES CUENTA
                foreach ($cuentasAsociadas as $cuenta) {
                    if ($this->hasCuentaData($cuenta)) {
                        $this->sumCuentaData($cuenta, $auxiliar);
                    } else {
                        $this->newCuentaData($cuenta, $auxiliar, $cuentasAsociadas);
                    }
                }
            }

            $this->addTotalsData($auxiliares);
            $this->addDetilsData($auxiliares);
            $this->addTotalNits($auxiliares);
            $this->addTotalNitsData($auxiliares);
            
            ksort($this->auxiliarCollection, SORT_STRING | SORT_FLAG_CASE);

            foreach (array_chunk($this->auxiliarCollection,233) as $auxiliarCollection){
                DB::connection('informes')
                    ->table('inf_auxiliar_detalles')
                    ->insert(array_values($auxiliarCollection));
			}

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-auxiliar-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Auxiliar generado',
                'id_auxiliar' => $this->id_auxiliar,
                'autoclose' => false
            ]));

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
            ->table(DB::raw("({$query->toSql()}) AS auxiliar"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                'numero_documento',
                'nombre_nit',
                'razon_social',
                'id_cuenta',
                'cuenta',
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
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $this->auxiliares[] = (object)[
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => $documento->documento_referencia,
                        'id_centro_costos' => $documento->id_centro_costos,
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
                });
            });

        return $this->auxiliares;
    }

    private function auxiliarDocumentosQuery()
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
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "DG.id AS id_comprobante",
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
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');

        return $documentosQuery;
    }

    private function auxiliarAnteriorQuery()
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
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "DG.id AS id_comprobante",
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
            ->where('DG.fecha_manual', '<=', $this->request['fecha_desde'])
            ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			})
            ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
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
            'nombre_cuenta' => $cuentaData->nombre,
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

    private function sumCuentaData($cuenta, $auxiliar)
    {
        $this->auxiliarCollection[$cuenta]['saldo_anterior']+= number_format((float)$auxiliar->saldo_anterior, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['debito']+= number_format((float)$auxiliar->debito, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['credito']+= number_format((float)$auxiliar->credito, 2, '.', '');
        $this->auxiliarCollection[$cuenta]['saldo_final']+= number_format((float)$auxiliar->saldo_final, 2, '.', '');
    }

    private function addTotalsData($auxiliares)
    {
        $debito = 0;
        $credito = 0;
        $saldo_anterior = 0;
        $saldo_final = 0;

        foreach ($auxiliares as $auxiliar) {
            $debito+= number_format((float)$auxiliar->debito, 2, '.', '');
            $credito+= number_format((float)$auxiliar->credito, 2, '.', '');
            $saldo_final+= number_format((float)$auxiliar->saldo_final, 2, '.', '');
            $saldo_anterior+= number_format((float)$auxiliar->saldo_anterior, 2, '.', '');
        }

        $this->auxiliarCollection['9999'] = [
            'id_auxiliar' => $this->id_auxiliar,
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
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
            'saldo_anterior' => $saldo_anterior,
            'debito' => $debito,
            'credito' => $credito,
            'saldo_final' => $saldo_final,
            'detalle' => false,
            'detalle_group' => false,
        ];
    }

    private function addDetilsData($auxiliares)
    {
        foreach ($auxiliares as $auxiliar) {
            if($auxiliar->total_columnas > 0 ) {

                $query = $this->auxiliarDocumentosDetallesQuery($auxiliar);
                $auxiliaresDetalle = $query->get();

                $auxiliaresDetalle->each(function ($auxiliarDetalle) {
                    $cuentaNumero = 1;
                    $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                        $auxiliarDetalle->id_nit.'B'.
                        $auxiliarDetalle->documento_referencia.'B'.
                        $cuentaNumero.'B';
                    while ($this->hasCuentaData($cuentaNueva)) {
                        $cuentaNumero++;
                        $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                            $auxiliarDetalle->id_nit.'B'.
                            $auxiliarDetalle->documento_referencia.'B'.
                            $cuentaNumero.'B';
                    }
                    $this->auxiliarCollection[$cuentaNueva] = [
                        'id_auxiliar' => $this->id_auxiliar,
                        'id_nit' => $auxiliarDetalle->id_nit,
                        'numero_documento' => $auxiliarDetalle->numero_documento,
                        'nombre_nit' => $auxiliarDetalle->nombre_nit,
                        'razon_social' => $auxiliarDetalle->razon_social,
                        'id_cuenta' => $auxiliarDetalle->id_cuenta,
                        'cuenta' => $auxiliarDetalle->cuenta,
                        'nombre_cuenta' => $auxiliarDetalle->nombre_cuenta,
                        'documento_referencia' => $auxiliarDetalle->documento_referencia,
                        'saldo_anterior' => $auxiliarDetalle->saldo_anterior,
                        'id_centro_costos' => $auxiliarDetalle->id_centro_costos,
                        'id_comprobante' => $auxiliarDetalle->id_comprobante,
                        'codigo_comprobante' => $auxiliarDetalle->codigo_comprobante,
                        'nombre_comprobante' => $auxiliarDetalle->nombre_comprobante,
                        'codigo_cecos' => $auxiliarDetalle->codigo_cecos,
                        'nombre_cecos' =>  $auxiliarDetalle->nombre_cecos,
                        'consecutivo' => $auxiliarDetalle->consecutivo,
                        'concepto' => $auxiliarDetalle->concepto,
                        'fecha_manual' => $auxiliarDetalle->fecha_manual,
                        'fecha_creacion' => $auxiliarDetalle->fecha_creacion,
                        'fecha_edicion' => $auxiliarDetalle->fecha_edicion,
                        'created_by' => $auxiliarDetalle->created_by,
                        'updated_by' => $auxiliarDetalle->updated_by,
                        'debito' => $auxiliarDetalle->debito,
                        'credito' => $auxiliarDetalle->credito,
                        'saldo_final' => '0',
                        'detalle' => false,
                        'detalle_group' => false,
                    ];
                });
        
            }
        }
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
                'PC.id AS id_cuenta',
                'PC.cuenta',
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
            // ->when($auxiliar-> ? $this->request['id_cuenta'] : false, function ($query) {
			// 	$query->where('PC.id_cuenta', $this->request['id_cuenta']);
			// })
            // ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
			// 	$query->where('N.id_nit', $this->request['id_nit']);
			// })
            ->orderByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia, created_at');

        return $documentosDetalleQuery;
    }

    private function addTotalNitsData($auxiliaresDetalle)
    {
        foreach ($auxiliaresDetalle as $auxiliarDetalle) {
            $cuentaNumero = 1;
            $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                $auxiliarDetalle->id_nit.'B'.
                $auxiliarDetalle->documento_referencia.'B'.
                $cuentaNumero.'A';
            while ($this->hasCuentaData($cuentaNueva)) {
                $cuentaNumero++;
                $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                    $auxiliarDetalle->id_nit.'B'.
                    $auxiliarDetalle->documento_referencia.'B'.
                    $cuentaNumero.'A';
            }
            $this->auxiliarCollection[$cuentaNueva] = [
                'id_auxiliar' => $this->id_auxiliar,
                'id_nit' => $auxiliarDetalle->id_nit,
                'numero_documento' => $auxiliarDetalle->numero_documento,
                'nombre_nit' => $auxiliarDetalle->nombre_nit,
                'razon_social' => $auxiliarDetalle->razon_social,
                'id_cuenta' => $auxiliarDetalle->id_cuenta,
                'cuenta' => $auxiliarDetalle->cuenta,
                'nombre_cuenta' => $auxiliarDetalle->nombre_cuenta,
                'documento_referencia' => $auxiliarDetalle->documento_referencia,
                'saldo_anterior' => $auxiliarDetalle->saldo_anterior,
                'id_centro_costos' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->id_centro_costos : '',
                'id_comprobante' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->id_comprobante : '',
                'codigo_comprobante' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->codigo_comprobante : '',
                'nombre_comprobante' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->nombre_comprobante : '',
                'codigo_cecos' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->codigo_cecos : '',
                'nombre_cecos' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->nombre_cecos : '',
                'consecutivo' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->consecutivo : '',
                'concepto' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->concepto : '',
                'fecha_manual' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->fecha_manual : '',
                'fecha_creacion' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->fecha_creacion : NULL,
                'fecha_edicion' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->fecha_edicion : NULL,
                'created_by' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->created_by : NULL,
                'updated_by' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->updated_by : NULL,
                // 'anulado' => $auxiliarDetalle->documento_referencia ? $auxiliarDetalle->anulado : '',
                'debito' => $auxiliarDetalle->debito,
                'credito' => $auxiliarDetalle->credito,
                'saldo_final' => $auxiliarDetalle->saldo_final,
                'detalle' => false,
                'detalle_group' => 'nits',
            ];
        }
    }

    private function addTotalNits($auxiliaresDetalle)
    {
        $collecionTotalNits = [];
        foreach ($auxiliaresDetalle as $auxiliarDetalle) {

            $cuentaNueva = $auxiliarDetalle->cuenta.'-'.
                $auxiliarDetalle->id_nit.'A';

            $collecionTotalNits[$cuentaNueva][] = [
                'id_nit' => $auxiliarDetalle->id_nit,
                'numero_documento' => $auxiliarDetalle->numero_documento,
                'nombre_nit' => $auxiliarDetalle->nombre_nit,
                'razon_social' => $auxiliarDetalle->razon_social,
                'id_cuenta' => $auxiliarDetalle->id_cuenta,
                'cuenta' => $auxiliarDetalle->cuenta,
                'nombre_cuenta' => $auxiliarDetalle->nombre_cuenta,
                'documento_referencia' => '',
                'saldo_anterior' => $auxiliarDetalle->saldo_anterior,
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
                'debito' => $auxiliarDetalle->debito,
                'credito' => $auxiliarDetalle->credito,
                'saldo_final' => $auxiliarDetalle->saldo_final,
                'detalle' => false,
                'detalle_group' => 'nits-totales',
            ];
        }

        foreach ($collecionTotalNits as $key => $collecion) {
            if(count($collecion) > 1) {
                $debito = 0;
                $credito = 0;
                $saldo_final = 0;
                $saldo_anterior = 0;
                foreach ($collecion as $data) {
                    $debito+= $data['debito'];
                    $credito+= $data['credito'];
                    $saldo_final+= $data['saldo_final'];
                    $saldo_anterior+= $data['saldo_anterior'];
                }
                $this->auxiliarCollection[$key] = [
                    'id_auxiliar' => $this->id_auxiliar,
                    'id_nit' => $collecion[0]['id_nit'],
                    'numero_documento' => $collecion[0]['numero_documento'],
                    'nombre_nit' => $collecion[0]['nombre_nit'],
                    'razon_social' => $collecion[0]['razon_social'],
                    'id_cuenta' => $collecion[0]['id_cuenta'],
                    'cuenta' => $collecion[0]['cuenta'],
                    'nombre_cuenta' => $collecion[0]['nombre_cuenta'],
                    'documento_referencia' => $collecion[0]['documento_referencia'],
                    'saldo_anterior' => $saldo_anterior,
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
                    'debito' => $debito,
                    'credito' => $credito,
                    'saldo_final' => $saldo_final,
                    'detalle' => false,
                    'detalle_group' => 'nits-totales',
                ];
            }
        }
    }

	private function hasCuentaData($cuenta)
	{
		return isset($this->auxiliarCollection[$cuenta]);
	}
}
