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
    public $carteras = [];
    public $id_cartera = 0;
    public $carteraCollection = [];

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        if ($this->request['id_cuenta']) {
            $cuenta = PlanCuentas::find($this->request['id_cuenta']);
            $this->request['cuenta'] = $cuenta->cuenta;
        }
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
				'nivel' => $this->request['nivel'],
			]);

            $this->id_cartera = $cartera->id;

            $this->nivelUnoCartera();
            if ($this->request['nivel'] != '1') $this->nivelDosCartera();
            if ($this->request['nivel'] == '3') $this->nivelTresCartera();
            $this->totalesCartera();
            
            ksort($this->carteraCollection, SORT_STRING | SORT_FLAG_CASE);
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

    private function nivelUnoCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        return DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
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
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->groupByRaw($this->request['agrupar_cartera'])
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $key = '';
                    if ($this->request['agrupar_cartera'] == 'id_nit') {
                        $key = $documento->numero_documento;
                    }
                    if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                        $key = $documento->cuenta;
                    }
                    $this->carteraCollection[$key] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'apartamento_nit' => $documento->apartamentos,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
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
                        'dias_cumplidos' => '',
                        'mora' => '',
                        'saldo_anterior' => $documento->saldo_anterior,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo_final,
                        'nivel' => 1,
                    ];
                });
            });
    }

    private function nivelDosCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
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
                'plazo',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->groupByRaw($this->groupString(2))
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->chunk(233, function ($documentos) {
                
                $documentos->each(function ($documento) {
                    $key = '';
                    if ($this->request['agrupar_cartera'] == 'id_nit') {
                        $key = $documento->numero_documento.'-A-'.$documento->cuenta;
                    }
                    if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                        $nombreKey = str_replace(' ', '', $documento->nombre_nit);
                        $key = $documento->cuenta.'-A-'.$nombreKey;
                    }
                    $mora = $documento->dias_cumplidos - $documento->plazo;
                    $this->carteraCollection[$key] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'apartamento_nit' => $documento->apartamentos,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
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
                        'mora' => $mora < 0 ? 0 : $mora,
                        'saldo_anterior' => $documento->saldo_anterior,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo_final,
                        'nivel' => 2,
                    ];
                });
            });
    }

    private function nivelTresCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
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
                'plazo',
                DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                DB::raw('SUM(debito) AS debito'),
                DB::raw('SUM(credito) AS credito'),
                DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->groupByRaw($this->groupString(3))
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->chunk(233, function ($documentos) {
                $documentos->each(function ($documento) {
                    $this->contador++;
                    $key = '';
                    if ($this->request['agrupar_cartera'] == 'id_nit') {
                        $key = $documento->numero_documento.'-A-'.$documento->cuenta.'-B-'.$this->contador;
                    }
                    if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                        $nombreKey = str_replace(' ', '', $documento->nombre_nit);
                        $key = $documento->cuenta.'-A-'.$nombreKey.'-B-'.$this->contador;
                    }
                    $mora = $documento->dias_cumplidos - $documento->plazo;
                    $this->carteraCollection[$key] = [
                        'id_cartera' => $this->id_cartera,
                        'id_nit' => $documento->id_nit,
                        'numero_documento' => $documento->numero_documento,
                        'nombre_nit' => $documento->nombre_nit,
                        'razon_social' => $documento->razon_social,
                        'apartamento_nit' => $documento->apartamentos,
                        'id_cuenta' => $documento->id_cuenta,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'documento_referencia' => $documento->documento_referencia,
                        'id_centro_costos' => $documento->id_centro_costos,
                        'id_comprobante' => $documento->id_comprobante,
                        'codigo_comprobante' => $documento->codigo_comprobante,
                        'nombre_comprobante' => $documento->nombre_comprobante,
                        'codigo_cecos' => $documento->codigo_cecos,
                        'nombre_cecos' => $documento->nombre_cecos,
                        'consecutivo' => $documento->consecutivo,
                        'concepto' => $documento->concepto,
                        'fecha_manual' => $documento->fecha_manual,
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                        'dias_cumplidos' => $documento->dias_cumplidos,
                        'mora' => $mora < 0 ? 0 : $mora,
                        'saldo_anterior' => $documento->saldo_anterior,
                        'total_abono' => $documento->total_abono,
                        'total_facturas' => $documento->total_facturas,
                        'saldo' => $documento->saldo_final,
                        'nivel' => 3,
                    ];
                });
            });
    }

    private function totalesCartera()
    {
        $query = $this->carteraDocumentosQuery();
        $query->unionAll($this->carteraAnteriorQuery());

        $total = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS cartera"))
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
                DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
                DB::raw('SUM(total_columnas) AS total_columnas')
            )
            ->orderByRaw('created_at')
            ->first();

        $this->carteraCollection['99999999999'] = [
            'id_cartera' => $this->id_cartera,
            'id_nit' => '',
            'numero_documento' => '',
            'nombre_nit' => '',
            'razon_social' => '',
            'apartamento_nit' => '',
            'id_cuenta' => '',
            'cuenta' => 'TOTALES',
            'nombre_cuenta' => '',
            'documento_referencia' => '',
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
            'dias_cumplidos' => '',
            'mora' => '',
            'saldo_anterior' => $total ? $total->saldo_anterior : 0,
            'total_abono' => $total ? $total->total_abono : 0,
            'total_facturas' => $total ? $total->total_facturas : 0,
            'saldo' => $total ? $total->saldo_final : 0,
            'nivel' => 0,
        ];
    }

    private function carteraDocumentosQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
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
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', $this->request['tipo_informe'] == 'por_cobrar' ? [3,7] : [4,8])
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

    private function carteraAnteriorQuery($documento_referencia = NULL, $id_nit = NULL, $id_cuenta = NULL)
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
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', $this->request['tipo_informe'] == 'por_cobrar' ? [3,7] : [4,8])
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
            if ($this->request['agrupar_cartera'] == 'id_nit') {
                $groupBy = 'id_cuenta, id_nit';
            }
            if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                $groupBy = 'id_nit, id_cuenta';
            }
        }

        if ($nivel == 3) {
            if ($this->request['agrupar_cartera'] == 'id_nit') {
                $groupBy = 'id_cuenta, id_nit, documento_referencia';
            }
            if ($this->request['agrupar_cartera'] == 'id_cuenta') {
                $groupBy = 'id_nit, id_cuenta, documento_referencia';
            }
        }

        return $groupBy;
    }
}