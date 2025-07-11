<?php
namespace App\Helpers;

use DB;
use Carbon\Carbon;
//MODEL
use App\Models\Sistema\DocumentosGeneral;

class Extracto
{
    public $hora;
    public $fecha;
    public $id_nit;
    public $id_cuenta;
    public $consecutivo;
    public $id_tipo_cuenta;
    public $documento_referencia;
    public $sin_documento_referencia;

    public function __construct($id_nit = null, $id_tipo_cuenta = null, $documento_referencia = null, $fecha = null, $id_cuenta = null, $consecutivo = null, $hora = null)
    {
        $this->id_nit = $id_nit;
        $this->id_cuenta = $id_cuenta;
        $this->id_tipo_cuenta = $id_tipo_cuenta;
        $this->documento_referencia = $documento_referencia;
        $this->fecha_dias = $fecha ?: Carbon::now();
        $this->fecha = $fecha;
        $this->hora = $hora;
        $this->consecutivo = $consecutivo;
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
                "id_tipo_cuenta",
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
                DB::raw('CASE WHEN (SUM(saldo)) < 0 THEN SUM(saldo) ELSE SUM(saldo) END AS saldo'),
            )
            ->orderByRaw('cuenta, fecha_manual ASC')
            ->groupByRaw('documento_referencia, id_cuenta, id_nit');

        return $extracto;
    }

    public function actualFechaAntes()
    {
        $query = $this->queryActualFechaAntes();

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
                "id_tipo_cuenta",
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
                DB::raw('CASE WHEN (SUM(saldo)) < 0 THEN SUM(saldo) * -1 ELSE SUM(saldo) END AS saldo'),
            )
            ->orderByRaw('cuenta, fecha_manual ASC')
            ->groupByRaw('documento_referencia, id_cuenta, id_nit');

        return $extracto;
    }

    public function anticipos($sinDocumentos = null)
    {
        $this->fecha = $this->fecha ?? Carbon::now();
        $this->sin_documento_referencia = $sinDocumentos;

        $query = $this->queryAnticipos();

        $anticipo = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS documentosanticipos"))
            ->mergeBindings($query)
            ->select(
                "id_nit",
                "id_cuenta",
                "id_comprobante",
                "id_centro_costos",
                "cuenta",
                "nombre",
                "fecha_manual",
                "consecutivo",
                "documento_referencia",
                "naturaleza_cuenta",
                "forma_pago_id",
                DB::raw('IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono'),
                DB::raw('IF(naturaleza_cuenta = 0, SUM(debito - credito), SUM(credito - debito)) AS saldo'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
            )
            ->groupByRaw('id_nit, id_cuenta')
            ->havingRaw("IF(naturaleza_cuenta = 0, SUM(debito - credito), SUM(credito - debito)) != 0");

        return $anticipo;
    }

    public function anticiposDiscriminados()
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
                "cuenta",
                "nombre",
                "fecha_manual",
                "consecutivo",
                "documento_referencia",
                "naturaleza_cuenta",
                DB::raw('IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono'),
                DB::raw('IF(naturaleza_cuenta = 0, SUM(debito - credito), SUM(credito - debito)) AS saldo'),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
            )
            ->groupByRaw('id_nit, id_cuenta, documento_referencia')
            ->havingRaw("IF(naturaleza_cuenta = 0, SUM(debito - credito), SUM(credito - debito)) != 0")
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
                if (is_array($this->id_tipo_cuenta)) $query->whereIn('PC.id_tipo_cuenta', $this->id_tipo_cuenta);
                else $query->where('PC.id_tipo_cuenta', $this->id_tipo_cuenta);
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

    public function completo()
	{
		$where = '';
		$whereFecha = '';
		$whereTipoCuenta = '';
		$having = '';

		if ($this->id_tipo_cuenta) {
			$whereTipoCuenta = "where cuentas.id_tipo_cuenta = {$this->id_tipo_cuenta} ";
		}

		if ($this->id_cuenta) {
			$idCuentas = implode(', ', $this->id_cuenta);
			$where = "id_cuenta in ($idCuentas) and ";
		}

		if ($this->fecha) {
			$whereFecha = ($this->id_tipo_cuenta ? " and " : "where ") . "fecha_manual <= '{$this->fecha}'";
		}

		if ($this->documento_referencia) {
			$where .= "documento_referencia = '{$this->documento_referencia}' and";
		} else {
			$having = "having IF(naturaleza_cuenta = 0, SUM(debito - credito), SUM(credito - debito)) > 0";
		}

        $query = "
            SELECT
                id_documento AS id,
                extracto.fecha_manual,
                extracto.id_nit,
                extracto.id_cuenta,
                extracto.id_comprobante,
                extracto.id_centro_costos,
                extracto.documento_referencia,
                extracto.credito,
                extracto.debito,
                extracto.concepto,
                cuentas.naturaleza_cuenta,
                IF(naturaleza_cuenta = 0, SUM(extracto.credito), SUM(extracto.debito)) as total_abono,
                IF(
                    naturaleza_cuenta = 0,
                    SUM(extracto.debito - extracto.credito),
                    SUM(extracto.credito - extracto.debito)
                ) as saldo,
                DATEDIFF('".$this->fecha_dias."', extracto.fecha_manual) AS dias_cumplidos
            FROM
                (
                    SELECT
                        id AS id_documento,
                        fecha_manual,
                        id_nit,
                        id_cuenta,
                        id_comprobante,
                        id_centro_costos,
                        documento_referencia,
                        credito,
                        debito,
                        concepto
                    FROM
                        documentos_generals
                    WHERE
                        ".$where."
                        id_nit = ".$this->id_nit."
                        AND anulado = 0
                ) extracto
                INNER JOIN plan_cuentas cuentas ON extracto.id_cuenta = cuentas.id
                ".$whereTipoCuenta."
                ".$whereFecha."
            GROUP BY
                extracto.documento_referencia,
                extracto.id_cuenta,
                extracto.id_nit,
                extracto.fecha_manual,
                extracto.id_comprobante,
                extracto.id_centro_costos,
                extracto.credito,
                extracto.debito,
                extracto.concepto,
                cuentas.naturaleza_cuenta,
                id_documento
            ".$having."
        ";

        $extracto = DB::connection('sam')->select($query);

		return collect($extracto);
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
                "PC.naturaleza_cuenta",
                "PC.cuenta",
                "PC.nombre",
                "FP.id AS forma_pago_id"
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'DG.id_cuenta', 'PCT.id_cuenta')
            ->leftJoin('fac_formas_pagos AS FP', 'PC.id', 'FP.id_cuenta')
            ->where('anulado', 0)
            ->when($this->id_nit ? $this->id_nit : false, function ($query) {
				$query->where('DG.id_nit', $this->id_nit);
			})
            ->when($this->id_tipo_cuenta ? $this->id_tipo_cuenta : false, function ($query) {
                if (is_array($this->id_tipo_cuenta)) $query->whereIn('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
                else $query->where('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
			})
            ->when($this->fecha ? $this->fecha : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->fecha);
			})
            ->when($this->sin_documento_referencia ? $this->sin_documento_referencia : false, function ($query) {
                if (is_array($this->sin_documento_referencia)) $query->whereNotIn('DG.documento_referencia', $this->sin_documento_referencia);
                else $query->whereNot('DG.documento_referencia', $this->sin_documento_referencia);
			})
            ->when($this->documento_referencia ? $this->documento_referencia : false, function ($query) {
                if (is_array($this->documento_referencia)) $query->whereIn('DG.documento_referencia', $this->sin_documento_referencia);
                else $query->where('DG.documento_referencia', $this->documento_referencia);
			})
            ->when($this->id_cuenta ? $this->id_cuenta : false, function ($query) {
                if (is_array($this->id_cuenta)) $query->whereIn('PC.id', $this->id_cuenta);
                else $query->where('PC.id', $this->id_cuenta);
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
                "PCT.id_tipo_cuenta",
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
            ->when($this->id_cuenta ? $this->id_cuenta : false, function ($query) {
				$query->where('PC.id', $this->id_cuenta);
			})
            ->when($this->id_tipo_cuenta ? $this->id_tipo_cuenta : false, function ($query) {
                if (is_array($this->id_tipo_cuenta)) $query->whereIn('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
                else $query->where('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
			})
            ->when($this->documento_referencia ? $this->documento_referencia : false, function ($query) {
				$query->where('DG.documento_referencia', $this->documento_referencia);
			})
            ->when($this->fecha ? $this->fecha : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->fecha);
			})
            ->when($this->documento_referencia ? false : true, function ($query) {
                $query->havingRaw("IF(PC.naturaleza_cuenta=0, SUM(DG.debito - DG.credito), SUM(DG.credito - DG.debito)) != 0");
			})
            ->when($this->consecutivo ? true : false, function ($query) {
                $query->where('DG.consecutivo', $this->consecutivo);
			})
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');

        return $queryActual;
    }

    public function queryActualFechaAntes()
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
                "PCT.id_tipo_cuenta",
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
            ->when($this->id_cuenta ? $this->id_cuenta : false, function ($query) {
				$query->where('PC.id', $this->id_cuenta);
			})
            ->when($this->id_tipo_cuenta ? $this->id_tipo_cuenta : false, function ($query) {
                if (is_array($this->id_tipo_cuenta)) $query->whereIn('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
                else $query->where('PCT.id_tipo_cuenta', $this->id_tipo_cuenta);
			})
            ->when($this->documento_referencia ? $this->documento_referencia : false, function ($query) {
				$query->where('DG.documento_referencia', $this->documento_referencia);
			})
            ->when($this->documento_referencia ? $this->documento_referencia : false, function ($query) {
				$query->where('DG.documento_referencia', $this->documento_referencia);
			})
            ->when($this->fecha ? $this->fecha : false, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->fecha);
			})
            ->when($this->documento_referencia ? false : true, function ($query) {
                $query->havingRaw("IF(PC.naturaleza_cuenta=0, SUM(DG.debito - DG.credito), SUM(DG.credito - DG.debito)) != 0");
			})
            ->when($this->consecutivo, function ($query) {
                $query->where('DG.consecutivo', $this->consecutivo);
			})
            ->groupByRaw('DG.id_cuenta, DG.id_nit, DG.documento_referencia');

        return $queryActual;
    }

    public function queryAnterior()
    {
        
    }

}