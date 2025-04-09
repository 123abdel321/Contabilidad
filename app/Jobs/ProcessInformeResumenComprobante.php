<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Events\PrivateMessageEvent;
//MODELS	
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfResumenComprobante;

class ProcessInformeResumenComprobante implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_resumen_comprobante;
    public $resumenComprobanteCollection = [];

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
			$resumenComprobante = InfResumenComprobante::create([
                'id_empresa' => $this->id_empresa,
				'fecha_desde' => $this->request['fecha_desde'],
                'fecha_hasta' => $this->request['fecha_hasta'],
				'id_comprobante' => $this->request['id_comprobante'],
				'id_cuenta' => $this->request['id_cuenta'],
				'id_nit' => $this->request['id_nit'],
				'agrupado' => $this->request['agrupado'],
				'detalle' => $this->request['detallar'],
				'created_by' => $this->id_usuario,
				'updated_by' => $this->id_usuario,
            ]);

			$this->id_resumen_comprobante = $resumenComprobante->id;

			$this->documentosResumenComprobante();
			$this->agrupadoResumenComprobante();
			$this->detallarResumenComprobante();
			$this->totalesResumenComprobante();

			uksort($this->resumenComprobanteCollection, function($a, $b) {

				$numA = (int) substr(strrchr($a, '-'), 1);
				$numB = (int) substr(strrchr($b, '-'), 1);
				
				return $numA - $numB;
			});
			
			foreach (array_chunk($this->resumenComprobanteCollection,233) as $resumenComprobanteCollection){
                DB::connection('informes')
                    ->table('inf_resumen_comprobante_detalles')
                    ->insert(array_values($resumenComprobanteCollection));
			}

			DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-resumen-comprobantes-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Resumen de comprobantes generado',
                'id_resumen_comprobante' => $this->id_resumen_comprobante,
                'autoclose' => false
            ]));

		} catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
	}
	
	private function documentosResumenComprobante()
	{
		$query = $this->queryResumenComprobantes();
		$query->groupby('id_comprobante')
			->orderByRaw('PC.cuenta, CO.codigo, DG.consecutivo ASC')
			->chunk(233, function ($documentos) {
				$documentos->each(function ($documento) {
					$this->resumenComprobanteCollection[$documento->codigo_comprobante.""] = [
						'id_resumen_comprobante' => $this->id_resumen_comprobante,
						'id_nit' => '',
						'id_cuenta' => '',
						'id_usuario' => '',
						'id_comprobante' => '',
						'id_centro_costos' => '',
						'cuenta' => $documento->codigo_comprobante.' - '.$documento->nombre_comprobante,
						'nombre_cuenta' => '',
						'numero_documento' => $this->request['detallar'] == '1' ? '' : $documento->codigo_comprobante.' - '.$documento->nombre_comprobante,
						'nombre_nit' => '',
						'razon_social' => '',
						'apartamento_nit' => '',
						'codigo_cecos' => '',
						'nombre_cecos' => '',
						'codigo_comprobante' => $documento->codigo_comprobante,
						'nombre_comprobante' => $documento->nombre_comprobante,
						'documento_referencia' => '',
						'consecutivo' => $this->request['detallar'] == '1' ? '' : $documento->codigo_comprobante.' - '.$documento->nombre_comprobante,
						'concepto' => '',
						'fecha_manual' => '',
						'debito' => $documento->debito,
						'credito' => $documento->credito,
						'diferencia' => $documento->diferencia < 0 ? $documento->diferencia * -1 : $documento->diferencia,
						'registros' => $documento->registros,
						'nivel' => $this->nivel(1),
					];
				});
			});
	}

	private function agrupadoResumenComprobante()
	{
		if (!$this->request['agrupado']) return;
		$query = $this->queryResumenComprobantes();
		$query->groupby('id_comprobante', $this->request['agrupado'])
			->orderByRaw('PC.cuenta ASC')
			->chunk(233, function ($documentos) {
				$documentos->each(function ($documento) {
					$key = $documento->{$this->request['agrupado']};
					if ($this->request['agrupado'] == 'id_nit') {
						$key = $documento->numero_documento;
					}
					if ($this->request['agrupado'] == 'id_cuenta') {
						$key = $documento->cuenta;
					}
					$this->resumenComprobanteCollection[$documento->codigo_comprobante.'A'.$key] = [
						'id_resumen_comprobante' => $this->id_resumen_comprobante,
						'id_nit' => $documento->id_nit,
						'id_cuenta' => $documento->id_cuenta,
						'id_usuario' => $documento->created_by,
						'id_comprobante' => $documento->id_comprobante,
						'id_centro_costos' => $documento->id_centro_costos,
						'cuenta' => $this->request['agrupado'] == 'id_cuenta' ? $documento->cuenta : '',
						'nombre_cuenta' => $this->request['agrupado'] == 'id_cuenta' ? $documento->nombre_cuenta : '',
						'numero_documento' => $this->request['agrupado'] == 'id_nit' || $this->request['agrupado'] == 'consecutivo' ? $documento->numero_documento : '',
						'nombre_nit' => $this->request['agrupado'] == 'id_nit' || $this->request['agrupado'] == 'consecutivo' ? $documento->nombre_nit : '',
						'razon_social' => $documento->razon_social,
						'apartamento_nit' => $documento->apartamentos,
						'codigo_cecos' => $documento->codigo_cecos,
						'nombre_cecos' => $documento->nombre_cecos,
						'codigo_comprobante' => $documento->codigo_comprobante,
						'nombre_comprobante' => $documento->nombre_comprobante,
						'documento_referencia' => $documento->documento_referencia,
						'consecutivo' => $this->request['agrupado'] == 'consecutivo' ? $documento->consecutivo : '',
						'concepto' => $documento->concepto,
						'fecha_manual' => $documento->fecha_manual,
						'debito' => $documento->debito,
						'credito' => $documento->credito,
						'diferencia' => $documento->diferencia < 0 ? $documento->diferencia * -1 : $documento->diferencia,
						'registros' => $documento->registros,
						'nivel' => $this->nivel(2),
					];
				});
			});
	}

	private function detallarResumenComprobante()
	{
		if ($this->request['detallar'] != '1') return;
		
		$query = $this->queryResumenComprobanteDetalle()
			->orderByRaw('PC.cuenta, CO.codigo, CAST(DG.consecutivo AS UNSIGNED) ASC')
			->chunk(233, function ($documentos) {
				$documentos->each(function ($documento) {
					$key = $this->request['agrupado'] ? $documento->{$this->request['agrupado']} : $documento->cuenta;
					if ($this->request['agrupado'] == 'id_nit') {
						$key = $documento->numero_documento;
					}
					if ($this->request['agrupado'] == 'id_cuenta') {
						$key = $documento->cuenta;
					}

					$this->resumenComprobanteCollection[$documento->codigo_comprobante.'A'.$key.'B-'.$documento->consecutivo] = [
						'id_resumen_comprobante' => $this->id_resumen_comprobante,
						'id_nit' => $documento->id_nit,
						'id_cuenta' => $documento->id_cuenta,
						'id_usuario' => $documento->created_by,
						'id_comprobante' => $documento->id_comprobante,
						'id_centro_costos' => $documento->id_centro_costos,
						'cuenta' => $documento->cuenta,
						'nombre_cuenta' => $documento->nombre_cuenta,
						'numero_documento' => $documento->numero_documento,
						'nombre_nit' => $documento->nombre_nit,
						'razon_social' => $documento->razon_social,
						'apartamento_nit' => $documento->apartamentos,
						'codigo_cecos' => $documento->codigo_cecos,
						'nombre_cecos' => $documento->nombre_cecos,
						'codigo_comprobante' => $documento->codigo_comprobante,
						'nombre_comprobante' => $documento->nombre_comprobante,
						'documento_referencia' => $documento->documento_referencia,
						'consecutivo' => $documento->consecutivo,
						'concepto' => $documento->concepto,
						'fecha_manual' => $documento->fecha_manual,
						'debito' => $documento->debito,
						'credito' => $documento->credito,
						'diferencia' => $documento->diferencia < 0 ? $documento->diferencia * -1 : $documento->diferencia,
						'registros' => $documento->registros,
						'nivel' => 0,
					];
				});
			});
	}

	private function totalesResumenComprobante()
	{
		$totalesResumen = $this->queryResumenComprobantes()->first();

		$this->resumenComprobanteCollection['99999999999'] = [
			'id_resumen_comprobante' => $this->id_resumen_comprobante,
			'id_nit' => '',
			'id_cuenta' => '',
			'id_usuario' => '',
			'id_comprobante' => '',
			'id_centro_costos' => '',
			'cuenta' => 'TOTALES',
			'nombre_cuenta' => '',
			'numero_documento' => 'TOTALES',
			'nombre_nit' => '',
			'razon_social' => '',
			'apartamento_nit' => '',
			'codigo_cecos' => '',
			'nombre_cecos' => '',
			'codigo_comprobante' => 'TOTALES',
			'nombre_comprobante' => '',
			'documento_referencia' => '',
			'consecutivo' => 'TOTALES',
			'concepto' => '',
			'fecha_manual' => '',
			'debito' => $totalesResumen->debito,
			'credito' => $totalesResumen->credito,
			'diferencia' => $totalesResumen->diferencia < 0 ? $totalesResumen->diferencia * -1 : $totalesResumen->diferencia,
			'registros' => $totalesResumen->registros,
			'nivel' => 4,
		];
	}

	private function queryResumenComprobantes()
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
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
				DB::raw("CONCAT(CO.codigo, ' - ', CO.nombre) AS comprobantes"),
				DB::raw('SUM(debito) AS debito'),
				DB::raw('SUM(credito) AS credito'),
				DB::raw('SUM(debito) - SUM(credito) AS diferencia'),
				DB::raw("COUNT(DG.id) registros")
			)
			->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
			->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
			->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
			->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
			->where('anulado', 0)
			->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
			->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
			->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
			->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('DG.id_cuenta', $this->request['id_cuenta']);
			})
			->when(isset($this->request['id_comprobante']) ? $this->request['id_comprobante'] : false, function ($query) {
				$query->where('DG.id_comprobante', $this->request['id_comprobante']);
			});
	}

	private function queryResumenComprobanteDetalle()
	{
		return DB::connection('sam')->table('documentos_generals AS DG')
			->select(
				'DG.id AS id_documento',
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
				DB::raw("CONCAT(CO.codigo, ' - ', CO.nombre) AS comprobantes"),
				"debito",
				"credito",
				DB::raw('0 AS diferencia'),
				DB::raw("1 AS registros")
			)
			->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
			->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
			->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
			->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
			->where('anulado', 0)
			->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
			->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
			->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
				$query->where('DG.id_nit', $this->request['id_nit']);
			})
			->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
				$query->where('DG.id_cuenta', $this->request['id_cuenta']);
			})
			->when(isset($this->request['id_comprobante']) ? $this->request['id_comprobante'] : false, function ($query) {
				$query->where('DG.id_comprobante', $this->request['id_comprobante']);
			});
	}

	private function nivel ($nivel)
	{
		if ($nivel == 1) {
			if ($this->request['detallar'] == '1') {
				if ($this->request['agrupado']) {
					return 3;
				}
				return 2;
			}
			if ($this->request['agrupado']) {
				return 2;
			}
			return 0;
		}

		if ($nivel == 2) {
			if ($this->request['detallar'] == '1') {
				return 2;
			}
			return 0;
		}
	}

}
