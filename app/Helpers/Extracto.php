<?php
namespace App\Helpers;

use DB;
use Carbon\Carbon;
//MODEL
use App\Models\Sistema\DocumentosGeneral;

class Extracto
{
    public $id_nit;
    public $id_tipo_cuenta;
    public $documento_referencia;
    public $fecha;

    public function __construct($id_nit = null, $id_tipo_cuenta = null, $documento_referencia = null, $fecha = null)
    {
        $this->id_nit = $id_nit;
        $this->id_tipo_cuenta = $id_tipo_cuenta;
        $this->documento_referencia = $documento_referencia;
        $this->fecha = $fecha;
    }

    public function actual()
    {
        $query = $this->queryActual();
        // $query->unionAll($this->queryAnterior());

        $extracto = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS extracto"))
            ->mergeBindings($query)
            ->select(
                'id_nit',
                "tipo_documento",
                'numero_documento',
                'id_ciudad',
                'nombre_nit',
                'razon_social',
                'telefono_1',
                'telefono_2',
                'email',
                'direccion',
                'plazo',
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
                'tipo_comprobante',
                'consecutivo',
                'concepto',
                'fecha_manual',
                'created_at',
                'naturaleza_ingresos',
                'naturaleza_egresos',
                'naturaleza_compras',
                'naturaleza_ventas',
                'naturaleza_cuenta',
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                'dias_cumplidos',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                DB::raw('SUM(total_abono) AS total_abono'),
                DB::raw('SUM(total_facturas) AS total_facturas'),
                DB::raw('SUM(saldo) AS saldo'),
            )
            ->groupByRaw('documento_referencia, id_cuenta, id_nit');

        return $extracto;
    }

    public function anticipos()
    {
        $fecha = Carbon::now();

        $query = $this->queryAnticipos();

        $anticipo = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS documentosanticipos"))
            ->mergeBindings($query)
            ->select(
                "id_nit",
                "id_cuenta",
                "id_comprobante",
                "id_centro_costos",
                "fecha_manual",
                "consecutivo",
                "documento_referencia",
                "naturaleza_cuenta",
                DB::raw('IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono'),
                DB::raw('IF(naturaleza_cuenta = 0, SUM(debito - credito), SUM(credito - debito)) AS saldo'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
            )
            ->groupByRaw('id_nit')
            ->where('fecha_manual', '<=', $fecha);

        return $anticipo;
    }

    public function actual2()
	{
        $fecha = Carbon::now();

		return DocumentosGeneral::select(
			"documentos_generals.id",
			"fecha_manual",
			"id_nit",
			"id_cuenta",
			"id_comprobante",
			"id_centro_costos",
			"documento_referencia",
			"credito",
			"debito",
			"concepto",
			"t2.naturaleza_cuenta",
			DB::raw("IF(naturaleza_cuenta=0,SUM(debito),SUM(credito)) as total_factura"),
			DB::raw("IF(naturaleza_cuenta=0,SUM(credito),SUM(debito)) as total_abono"),
			DB::raw("IF(naturaleza_cuenta=0,SUM(debito-credito),SUM(credito-debito)) as saldo"),
			DB::raw("DATEDIFF('{$fecha}', fecha_manual) AS dias_cumplidos")
		)
			->join('plan_cuentas AS t2', "id_cuenta", "=", "t2.id")
			->where("anulado", 0)
			->where("id_nit", $this->id_nit)
            ->when($this->id_tipo_cuenta ? $this->id_tipo_cuenta : false, function ($query) {
				$query->where('PC.id_tipo_cuenta', $this->id_tipo_cuenta);
			})
			->when($this->documento_referencia, function ($query, $documento_referencia) {
				$query->where('documento_referencia', $documento_referencia);
			}, function ($query) {
				$query->havingRaw("IF(naturaleza_cuenta=0,SUM(debito-credito),SUM(credito-debito)) > 0");
			})
			->when($this->fecha, function ($query, $fecha) {
				$query->where('fecha_manual', $fecha);
			})
			->groupBy("documento_referencia", "id_cuenta", "id_nit");
	}

    public function queryAnticipos()
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id_nit",
                "DG.id_cuenta",
                "DG.id_comprobante",
                "DG.id_centro_costos",
                "DG.fecha_manual",
                "DG.consecutivo",
                "DG.documento_referencia",
                "DG.debito",
                "DG.credito",
                "DG.concepto",
                "DG.anulado",
                "PC.naturaleza_cuenta"
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->where('anulado', 0)
            ->when($this->id_nit ? $this->id_nit : false, function ($query) {
				$query->where('DG.id_nit', $this->id_nit);
			})
            ->when($this->id_tipo_cuenta ? $this->id_tipo_cuenta : false, function ($query) {
				$query->where('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
			});
    }

    public function queryActual()
    {
        $fecha = Carbon::now();

        $queryActual = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "N.id AS id_nit",
                "TD.nombre AS tipo_documento",
                "N.numero_documento",
                "N.id_ciudad",
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.telefono_1",
                "N.telefono_2",
                "N.email",
                "N.direccion",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "DG.id_comprobante AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "CO.tipo_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                "PC.naturaleza_ingresos",
                "PC.naturaleza_egresos",
                "PC.naturaleza_compras",
                "PC.naturaleza_ventas",
                "PC.naturaleza_cuenta",
                DB::raw("SUM(DG.debito) AS debito"),
                DB::raw("SUM(DG.credito) AS credito"),
                DB::raw("DATEDIFF('$fecha', DG.fecha_manual) AS dias_cumplidos"),
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(PC.naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw("IF(
                    PC.naturaleza_cuenta = 0,
                    SUM(DG.debito - DG.credito),
                    SUM(DG.credito - DG.debito)
                ) AS saldo")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->leftJoin('tipos_documentos AS TD', 'N.id_tipo_documento', 'TD.id')
            ->where('anulado', 0)
            ->when($this->id_nit ? $this->id_nit : false, function ($query) {
				$query->where('N.id', $this->id_nit);
			})
            ->when($this->id_tipo_cuenta ? $this->id_tipo_cuenta : false, function ($query) {
				$query->where('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
			})
            ->when($this->documento_referencia ? $this->documento_referencia : false, function ($query) {
				$query->where('DG.documento_referencia', $this->documento_referencia);
			})
            ->when($this->documento_referencia ? $this->documento_referencia : false, function ($query) {
				$query->where('DG.documento_referencia', $this->documento_referencia);
			})
            ->when($this->fecha ? $this->fecha : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->fecha);
			})
            ->when($this->documento_referencia ? false : true, function ($query) {
                $query->havingRaw("IF(PC.naturaleza_cuenta=0, SUM(DG.debito - DG.credito), SUM(DG.credito - DG.debito)) > 0");
			})
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');

        return $queryActual;
    }

    public function queryAnterior()
    {
        
    }

}