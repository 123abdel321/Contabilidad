<?php

namespace App\Helpers\Printers;

use DB;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;


class RetencionPdf extends AbstractPrinterPdf
{
	public $empresa;
	public $request;

    public function __construct(Empresa $empresa, $request)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->request = $request;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.informes.retencion.retencion';
	}

    public function name()
	{
		return 'retencion_'.uniqid();
	}

    public function paper()
	{
		return 'portrait';

		return '';
	}

	public function formatPaper()
	{
		// if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
	}

    public function data()
    {
        $query = $this->impuestosDocumentosQuery();
        $query->unionAll($this->impuestosAnteriorQuery());

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS impuestos"))
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
                'nombre_impuesto',
                'base',
                'total_uvt',
                'porcentaje',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas'),
                DB::raw("(CASE
					WHEN naturaleza_cuenta = 0 AND SUM(debito) < 0 THEN 1
					WHEN naturaleza_cuenta = 1 AND SUM(credito) < 0 THEN 1
					ELSE 0
				END) AS error")
            )
            ->groupByRaw($this->groupString(2))
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->havingRaw('saldo_anterior != 0 OR saldo_final != 0')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $key = $documento->numero_documento.'-A-'.$documento->cuenta;
                    $totalUvt = $documento->total_uvt ? $documento->total_uvt : 0;
                    $totalBase = $documento->base ? $documento->base : 0;
                    $totalImpuesto = $documento->debito + $documento->credito;
                    $totalValorBase = $documento->porcentaje ? $totalImpuesto / $documento->porcentaje : 0;
                    $this->impuestosCollection[$key] = (object)[
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta.' (UVT '.$totalUvt.' - '.number_format($totalBase).')',
                        'documento_referencia' => '',
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
                        'dias_cumplidos' => $documento->dias_cumplidos,
                        'saldo_anterior' => $documento->saldo_anterior,
                        'debito' => $documento->debito,
                        'credito' => $documento->credito,
                        'saldo' => $documento->saldo_final,
                        'valor_base' => $totalValorBase,
                        'porcentaje_base' => $documento->porcentaje ? $documento->porcentaje : 0,
                        'nivel' => 2,
                        'errores' => $documento->error
                    ];
                });
            });

        return [
			'empresa' => $this->empresa,
            'cliente' => Nits::where('id', $this->request['id_nit'])->first(),
            'filtros' => $this->request,
            'detalles' => $this->impuestosCollection,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s')
		];
    }

    private function impuestosDocumentosQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
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
                "N.plazo",
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
                "IM.base",
                "IM.nombre AS nombre_impuesto",
                "IM.total_uvt",
                "IM.porcentaje",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->leftJoin('impuestos AS IM', 'PC.id_impuesto', 'IM.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', $this->tipoCuentas())
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
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			});

        return $documentosQuery;
    }

    private function impuestosAnteriorQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
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
                "N.plazo",
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
                "IM.base",
                "IM.nombre AS nombre_impuesto",
                "IM.total_uvt",
                "IM.porcentaje",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->leftJoin('impuestos AS IM', 'PC.id_impuesto', 'IM.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', $this->tipoCuentas())
            ->when($this->request['fecha_desde'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->request['fecha_desde']);
			})
            ->when($this->request['id_nit'] ? true : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
			->when($this->request['id_cuenta'] ? true : false, function ($query) {
				$query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
			});

        return $anterioresQuery;
    }

    private function groupString($nivel)
    {
        $groupBy = '';
        if ($nivel == 2) {
            $groupBy = 'id_cuenta, id_nit';
        }

        if ($nivel == 3) {
            $groupBy = 'id_cuenta, id_nit, documento_referencia';
        }

        return $groupBy;
    }

    private function tipoCuentas ()
    {
        if ($this->request['tipo_informe'] == 'iva') return [9,16];
        if ($this->request['tipo_informe'] == 'retencion') return [12,13];
        if ($this->request['tipo_informe'] == 'reteica') return [17];
        return [9,12,13,16,17];
    }

}