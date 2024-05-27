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
use Carbon\Carbon;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Informes\InfCartera;

class ProcessInformeCartera implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $contador = 10000;
    public $contadorNivel2 = 0;
    public $carteras = [];
    public $id_cartera = 0;
    public $carteraCollection = [];

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
            $cartera = InfCartera::create([
				'id_empresa' => $this->id_empresa,
				'id_nit' => $this->request['id_nit'],
				'id_cuenta' => $this->request['id_cuenta'],
				'fecha_desde' => $this->request['fecha_desde'],
				'fecha_hasta' => $this->request['fecha_hasta'],
				'agrupar_cartera' => $this->request['agrupar_cartera'],
				'detallar_cartera' => $this->request['detallar_cartera'],
			]);

            $this->id_cartera = $cartera->id;

            $this->gruposDocumentosGenerales();
            $this->totalesDocumentosGenerales();
            if ($this->request['detallar_cartera']) $this->documentosGenerales();
            if ($this->request['agrupar_cartera'] == 'id_cuenta') $this->totalesNitsGenerales();
            // if ($this->request['detallar_cartera'] == 'id_nit') $this->totalesCuentasGenerales();

            ksort($this->carteraCollection, SORT_STRING | SORT_FLAG_CASE);
            // dd($this->carteraCollection);
            foreach (array_chunk($this->carteraCollection,233) as $carteraCollection){
                DB::connection('informes')
                    ->table('inf_cartera_detalles')
                    ->insert(array_values($carteraCollection));
            }

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-cartera-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Cartera generada',
                'id_cartera' => $this->id_cartera,
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
    }

    private function documentosGenerales()
    {
        $query = $this->documentosCartetaQuery();
        $query->groupByRaw('id_nit, id_cuenta, consecutivo')
            ->orderByRaw('cuenta, nombre_nit, created_at')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $this->contador++;
                    $this->carteraCollection[$documento->cuenta.'-B'.$this->contador] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => $documento->documento_referencia,
                        'saldo_anterior' => '',
                        'dias_cumplidos' => $documento->dias_cumplidos,
                        'id_centro_costos' => $documento->id_centro_costos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => '',
                        'fecha_manual' => $documento->fecha_manual ,
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo,
                        'nivel' => 3,
                    ];
                    $contadorDetalle = 0;
                    $detallesDocumento = $this->detalleCartetaQuery($documento)->get();
                    if (count($detallesDocumento) > 1) {
                        foreach ($detallesDocumento as $detalleDocumento) {
                            $contadorDetalle++;
                            $this->carteraCollection[$documento->cuenta.'-B'.$this->contador.'-'.$contadorDetalle] = [
                                'id_cartera' => $this->id_cartera,
                                'id_nit' => $detalleDocumento->id_nit,
                                'numero_documento' => '',
                                'nombre_nit' => '',
                                'razon_social' => $detalleDocumento->razon_social,
                                'id_cuenta' => $detalleDocumento->id_cuenta,
                                'cuenta' => '',
                                'nombre_cuenta' => '',
                                'documento_referencia' => $detalleDocumento->documento_referencia,
                                'saldo_anterior' => '',
                                'dias_cumplidos' => $detalleDocumento->dias_cumplidos,
                                'id_centro_costos' => $detalleDocumento->id_centro_costos,
                                'id_comprobante' => $detalleDocumento->id_comprobante,
                                'codigo_comprobante' => $detalleDocumento->codigo_comprobante,
                                'nombre_comprobante' => $detalleDocumento->nombre_comprobante,
                                'codigo_cecos' => $detalleDocumento->codigo_cecos,
                                'nombre_cecos' => $detalleDocumento->nombre_cecos,
                                'consecutivo' => $detalleDocumento->consecutivo,
                                'concepto' => $detalleDocumento->concepto,
                                'fecha_manual' => $detalleDocumento->fecha_manual ,
                                'fecha_creacion' => $detalleDocumento->fecha_creacion,
                                'fecha_edicion' => $detalleDocumento->fecha_edicion,
                                'created_by' => $detalleDocumento->created_by,
                                'updated_by' => $detalleDocumento->updated_by,
                                'total_abono' => $detalleDocumento->total_abono,
                                'total_facturas' => $detalleDocumento->total_facturas,
                                'saldo' => $detalleDocumento->saldo,
                                'nivel' => 4,
                            ];
                        }
                    }
                });
            });
    }

    private function gruposDocumentosGenerales()
    {
        $query = $this->documentosCartetaQuery();
        // $query->unionAll($this->anteriorCartetaQuery());
        $query->groupByRaw($this->request['agrupar_cartera'])
            ->orderByRaw($this->request['agrupar_cartera'])
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $key = '';
                    if ($this->request['agrupar_cartera'] == 'id_nit') {
						$key = $documento->numero_documento;
					}
					if ($this->request['agrupar_cartera'] == 'id_cuenta') {
						$key = $documento->cuenta;
					}
                    $this->carteraCollection[$key.'-A'] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => '',
                        'nombre_nit' => '',
                        'razon_social' => '',
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => '',
                        'saldo_anterior' => $documento->saldo_anterior,
                        'dias_cumplidos' => '',
                        'id_centro_costos' => $documento->id_centro_costos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => '',
                        'fecha_manual' => '',
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo,
                        'nivel' => 1,
                    ];
                });
            });
    }

    private function totalesNitsGenerales()
    {
        $query = $this->documentosCartetaQuery();
        $query->groupByRaw('id_nit, id_cuenta')
            ->orderByRaw('nombre_nit')
            ->chunk(233, function ($documentos) {
                $this->contadorNivel2 = 0;
                $documentos->each(function ($documento) {
                    $this->contadorNivel2++;
                    $this->carteraCollection[$documento->cuenta.'-A'.$this->contadorNivel2] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => $documento->documento_referencia,
                        'saldo_anterior' => '',
                        'dias_cumplidos' => $documento->dias_cumplidos,
                        'id_centro_costos' => $documento->id_centro_costos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => '',
                        'fecha_manual' => $documento->fecha_manual ,
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo,
                        'nivel' => 2,
                    ];
                });
            });
    }

    private function totalesDocumentosGenerales()
    {
        $query = $this->documentosCartetaQuery();
        $total = $query->orderByRaw('id_cuenta')
            ->first();

        $this->carteraCollection['99999999999'] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'id_cuenta' => '',
            'cuenta' => '',
            'nombre_cuenta' => '',
            'documento_referencia' => '',
            'saldo_anterior' => '',
            'dias_cumplidos' => '',
            'id_centro_costos' => '',
            'id_comprobante' => '',
            'codigo_comprobante' => '',
            'nombre_comprobante' => '',
            'codigo_cecos' => '',
            'nombre_cecos' => '',
            'consecutivo' => '',
            'concepto' => '',
            'fecha_manual' => '',
            'fecha_creacion' => '',
            'fecha_edicion' => '',
            'created_by' => '',
            'updated_by' => '',
            'total_abono' => $total->total_abono,
            'total_facturas' => $total->total_facturas,
            'saldo' => $total->saldo,
            'nivel' => 0,
        ];
    }

    private function documentosCartetaQuery()
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
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
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
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
				DB::raw("CONCAT(CO.codigo, ' - ', CO.nombre) AS comprobantes"),
                DB::raw("0 AS saldo_anterior"),
				DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw("IF(
                    PC.naturaleza_cuenta = 0,
                    SUM(DG.debito - DG.credito),
                    SUM(DG.credito - DG.debito)
                ) AS saldo"),
				DB::raw("COUNT(DG.id) registros")
			)
			->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
			->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
			->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
			->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
			->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
			->where('anulado', 0)
			->whereIn('PCT.id_tipo_cuenta', [3,4])
            ->when($this->request['fecha_desde'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '>=', $this->request['fecha_desde']);
			})
            ->when($this->request['fecha_hasta'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->request['fecha_hasta']);
			})
            ->when($this->request['id_nit'] ? true : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
			->when($this->request['id_cuenta'] ? true : false, function ($query) {
				$query->where('DG.id_cuenta', $this->request['id_cuenta']);
			})
            ->groupByRaw($this->request['agrupar_cartera']);
	}

    private function detalleCartetaQuery($documento)
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
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
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
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
				DB::raw("CONCAT(CO.codigo, ' - ', CO.nombre) AS comprobantes"),
                DB::raw("0 AS saldo_anterior"),
				DB::raw("IF(PC.naturaleza_cuenta = 0, credito, debito) AS total_abono"),
                DB::raw("IF(PC.naturaleza_cuenta = 0, debito, credito) AS total_facturas"),
                DB::raw("IF(
                    PC.naturaleza_cuenta = 0,
                    DG.debito - DG.credito,
                    DG.credito - DG.debito
                ) AS saldo"),
				DB::raw("1 AS registros")
			)
			->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
			->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
			->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
			->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
			->where('anulado', 0)
            ->when($documento->id_nit ? true : false, function ($query) use($documento) {
				$query->where('DG.id_nit', $documento->id_nit);
			})
			->when($documento->id_cuenta ? true : false, function ($query) use($documento) {
				$query->where('DG.id_cuenta', $documento->id_cuenta);
			})
            ->when($documento->consecutivo ? true : false, function ($query) use($documento) {
				$query->where('DG.consecutivo', $documento->consecutivo);
			})
            ->when($documento->id_comprobante ? true : false, function ($query) use($documento) {
				$query->where('DG.id_comprobante', $documento->id_comprobante);
			});
	}

    private function anteriorCartetaQuery()
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
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
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
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
				DB::raw("CONCAT(CO.codigo, ' - ', CO.nombre) AS comprobantes"),
                DB::raw("SUM(debito) - SUM(credito) AS saldo_anterior"),
				DB::raw("0 AS total_abono"),
                DB::raw("0 AS total_facturas"),
                DB::raw("0 AS saldo"),
				DB::raw("0 AS registros")
			)
			->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
			->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
			->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
			->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
			->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
			->where('anulado', 0)
			->whereIn('PCT.id_tipo_cuenta', [3,4])
            ->where('DG.fecha_manual', '<', $this->request['fecha_desde'])
            ->when($this->request['id_nit'] ? true : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
			->when($this->request['id_cuenta'] ? true : false, function ($query) {
				$query->where('DG.id_cuenta', $this->request['id_cuenta']);
			})
            ->groupByRaw($this->request['agrupar_cartera']);
	}

    

}