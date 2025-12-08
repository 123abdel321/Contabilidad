<?php

namespace App\Helpers\Printers;

use DB;
use App\Helpers\Extracto;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\ConRecibos;
use App\Models\Sistema\PlanCuentas;

class RecibosPdfMultiple extends AbstractPrinterPdf
{
    public $request;
	public $empresa;
	public $dataRecibos;
	public $tipoEmpresion = 1;

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
		return 'pdf.facturacion.recibos_multiples';
	}

    public function name()
	{
		return 'recibos_'.uniqid();
	}

    public function paper()
	{
		if ($this->tipoEmpresion == 1) return 'landscape';
		if ($this->tipoEmpresion == 2) return 'portrait';

		return '';
	}

	public function formatPaper()
	{
		// if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
	}

	public function data()
	{
		$query = $this->RecibosGeneralesQuery();

		DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS recibosgeneralesdata"))
			->mergeBindings($query)
			->groupBy('relation_id')
			->orderBy('id')
			->chunk(233, function ($recibos) {
				foreach ($recibos as $recibo) {
					$reciboData = ConRecibos::with([
							'nit',
							'detalles.cuenta',
							'pagos.forma_pago',
							'documentos'
						])
						->where('id', $recibo->relation_id)
					->first();

					if (!$reciboData) {
						continue;
					}

					$nit = null;
					$saldo = 0;
					$saldoAnterior = 0;
					$getNit = $reciboData->nit;

					if($getNit){ 
						$nit = (object)[
							'nombre_nit' => $getNit->nombre_completo,
							'telefono' =>  $getNit->telefono_1,
							'email' => $getNit->email,
							'direccion' => $getNit->direccion,
							'tipo_documento' => $getNit->tipo_documento->nombre,
							'numero_documento' => $getNit->numero_documento,
							'ciudad' => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
							'apartamentos' => $getNit->apartamentos ? $getNit->apartamentos : ''
						];
					}

					$extractos = (new Extracto(
						$getNit->id,
						3,
						null,
						$recibo->fecha_manual
					))->actual()->get();
					$extractos = $extractos->sortBy('orden, cuenta')->values();

					$fechaAnterior = Carbon::parse($recibo->fecha_manual)->subMinute(); 
					
					$extractoAnterior = (new Extracto(
						$getNit->id,
						[3],
						null,
						$fechaAnterior
					))->actual()->get();
					$extractoAnterior = $extractoAnterior->sortBy('orden, cuenta')->values();

					if (isset($extractos)) {
						foreach ($extractos as $extracto) {
							$saldo+= floatval($extracto->saldo);
						}
					}

					$saldoAnterior = 0;
					if (isset($extractoAnterior)) {
						foreach ($extractoAnterior as $anterior) {
							$saldoAnterior+= floatval($anterior->saldo);
						}
					}

					$this->dataRecibos[] = (object)[
						'nit' => $nit,
						'recibo' => $reciboData,
						'detalles' => $reciboData->detalles,
						'pagos' => $reciboData->pagos,
						'saldo' => $saldo,
						'saldoAnterior' => $saldoAnterior,
					];
				}
				unset($recibos);//Liberar memoria
			});

		return [
			'empresa' => $this->empresa,
			'documentos' => $this->dataRecibos,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user() ? request()->user()->username : 'MaximoPH'
		];
	}

	private function RecibosGeneralesQuery()
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select([
                'DG.id',
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN N.id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN N.id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.apartamentos",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                DB::raw("(CASE
                    WHEN IM.base > 0 THEN (debito + credito) / (IM.porcentaje / 100)
                    ELSE NULL
                END) AS base_cuenta"),
                "IM.porcentaje AS porcentaje_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                DB::raw('CAST(DG.consecutivo AS UNSIGNED) AS consecutivo'),
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                "DG.relation_id",
                "debito",
                "credito",
                DB::raw("debito - credito AS diferencia"),
                DB::raw("1 AS total_columnas"),
                DB::raw("IF(debito - credito < 0, (debito - credito) * -1, debito - credito) AS valor_total")
            ])
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('impuestos AS IM', 'PC.id_impuesto', 'IM.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('DG.fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('DG.fecha_manual', '<=', $this->request['fecha_hasta'])
            ->where(function ($query) {
                $query->when(isset($this->request['precio_desde']), function ($query) {
                    $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) >= ?', [$this->request['precio_desde']]);
                })->when(isset($this->request['precio_hasta']), function ($query) {
                    $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) <= ?', [$this->request['precio_hasta']]);
                });
            })
            ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
                $query->where('DG.id_nit', $this->request['id_nit']);
            })
            ->when(isset($this->request['id_comprobante']) ? $this->request['id_comprobante'] : false, function ($query) {
                $query->where('DG.id_comprobante', $this->request['id_comprobante']);
            })
            ->when(isset($this->request['id_centro_costos']) ? $this->request['id_centro_costos'] : false, function ($query) {
                $query->where('DG.id_centro_costos', $this->request['id_centro_costos']);
            })
            ->when(isset($this->request['id_cuenta']) ? $this->request['id_cuenta'] : false, function ($query) {
                $query->where('PC.cuenta', 'LIKE', $this->request['cuenta'].'%');
            })
            ->when(isset($this->request['documento_referencia']) ? $this->request['documento_referencia'] : false, function ($query) {
                $query->where('DG.documento_referencia', $this->request['documento_referencia']);
            })
            ->when(isset($this->request['consecutivo']) ? $this->request['consecutivo'] : false, function ($query) {
                $query->where('DG.consecutivo', $this->request['consecutivo']);
            })
            ->when(isset($this->request['concepto']) ? $this->request['concepto'] : false, function ($query) {
                $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
            })
            ->when(isset($this->request['id_usuario']) ? $this->request['id_usuario'] : false, function ($query) {
                $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
            });
    }
}

