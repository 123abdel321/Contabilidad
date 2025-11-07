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
use App\Models\Informes\InfDocumentosGenerales;

class ProcessInformeDocumentosGenerales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $agrupacion = [];
    public $documentos = [];
    public $id_documentos_generales;
    public $documentosCollection = [];
    

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        if ($this->request['agrupar']) {
            $this->agrupacion = explode(',', $this->request['agrupar']);
        }
    }

    public function handle()
    {
        $empresa = Empresa::find($this->id_empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        DB::connection('informes')->beginTransaction();

        try {

            $documentosGenerales = InfDocumentosGenerales::create([
                'id_empresa' => $this->id_empresa,
                'fecha_desde' => $this->request['fecha_desde'] ?? null,
                'fecha_hasta' => $this->request['fecha_hasta'] ?? null,
                'precio_desde' => $this->request['precio_desde'] ?? null,
                'precio_hasta' => $this->request['precio_hasta'] ?? null,
                'id_nit' => $this->request['id_nit'] ?? null,
                'id_cuenta' => $this->request['id_cuenta'] ?? null,
                'id_usuario' => $this->request['id_usuario'] ?? null,
                'id_comprobante' => $this->request['id_comprobante'] ?? null,
                'id_centro_costos' => $this->request['id_centro_costos'] ?? null,
                'documento_referencia' => $this->request['documento_referencia'] ?? null,
                'consecutivo' => $this->request['consecutivo'] ?? null,
                'concepto' => $this->request['concepto'] ?? null,
                'agrupar' => $this->request['agrupar'] ?? null,
                'agrupado' => $this->request['agrupado'] ?? null,
            ]);

            $this->id_documentos_generales = $documentosGenerales->id;
            
            if ($this->request['agrupar'] && $this->request['agrupado']) $this->documentosGeneralesAgruparNiveles();
            else if (!$this->request['agrupar']) $this->documentosGeneralesSinAgrupar();
            else if ($this->request['agrupar']) $this->documentosGeneralesAgruparNormal();
            
            foreach (array_chunk($this->documentosCollection,233) as $documentosCollection){
                DB::connection('informes')
                    ->table('inf_documentos_generales_detalles')
                    ->insert(array_values($documentosCollection));
			}

            DB::connection('informes')->commit();

            $chanelNotificacion = 'informe-documentos-generales-';
            if ($this->request['cambio_datos']) {
                $chanelNotificacion = 'cambio_datos-';
            }

            event(new PrivateMessageEvent($chanelNotificacion.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Documentos generales generado',
                'id_documento_general' => $this->id_documentos_generales,
                'autoclose' => false
            ]));            

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
    }

    private function documentosGeneralesAgruparNiveles()
    {
        $query = $this->DocumentosGeneralesQuery();

        $orderClause = $this->request['agrupar'] . ", id ASC";
        
        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS documentosgeneralesdata"))
            ->mergeBindings($query)
            ->orderByRaw($orderClause)
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $agrupacionesTotales = [];

                    foreach ($this->agrupacion as $key => $agrupacion) {
                        $agrupacionesTotales[] = $agrupacion;
                        $cuentaDetalle = $this->getCuentaPadre($documento, $agrupacionesTotales);
                        $cuentaPadre = $this->getCuentaPadreNiveles($documento, $agrupacionesTotales);

                        if ($this->hasCuentaData($cuentaPadre)) $this->sumCuentaData($cuentaPadre, $documento);
                        else $this->newCuentaTotalNiveles($cuentaPadre, $documento, $agrupacionesTotales);
                        $this->newCuentaDetalle($cuentaDetalle, $documento);
                    }
                }
                unset($documentos);
            });
            
        ksort($this->documentosCollection, SORT_STRING | SORT_FLAG_CASE);

        $this->addTotalData($query);
    }

    private function documentosGeneralesAgruparNormal()
    {
        $query = $this->DocumentosGeneralesQuery();

        $orderClause = $this->request['agrupar'] . ", id ASC";

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS documentosgeneralesdata"))
            ->mergeBindings($query)
            ->orderByRaw($orderClause)
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $cuentaPadre = $this->getCuentaPadre($documento);
                    if ($this->hasCuentaData($cuentaPadre)) {
                        $this->sumCuentaData($cuentaPadre, $documento);
                    } else {
                        $this->newCuentaTotal($cuentaPadre, $documento);
                    }

                    $this->newCuentaDetalle($cuentaPadre, $documento);
                }
                unset($documentos);
            });
            
        $this->addTotalData($query);
    }

    private function getCuentaPadreNiveles($documento, $agrupacionesTotales)
    {
        $cuenta = '';
        foreach ($agrupacionesTotales as $nameValue) {
            $cuenta.= $documento->{$nameValue} ? $documento->{$nameValue} : '0';
            $cuenta.='-';
        }
        $cuenta = substr_replace($cuenta ,"",-1);

        return $cuenta;
    }

    private function getCuentaPadre($documento)
    {
        $cuenta = '';
        $arrayGroup = explode(',', $this->request['agrupar']);
        
        foreach ($arrayGroup as $nameValue) {
            $cuenta.= $documento->{$nameValue} ? $documento->{$nameValue} : '0';
            $cuenta.='-';
        }
        $cuenta = substr_replace($cuenta ,"",-1);
        
        return $cuenta;
    }

    private function documentosGeneralesSinAgrupar()
    {
        $query = $this->DocumentosGeneralesQuery();

        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS documentosgeneralesdata"))
            ->mergeBindings($query)
            ->orderByRaw('cuenta, id_nit, documento_referencia, id')
            ->chunk(233, function ($documentos) {
                foreach ($documentos as $documento) {
                    $this->documentosCollection[] = [
                        'id_documentos_generales' => $this->id_documentos_generales,
                        'id_nit' => $documento->id_nit,
                        'id_cuenta' => $documento->id_cuenta,
                        'id_usuario' => $documento->created_by,
                        'id_comprobante' => $documento->id_comprobante,
                        'id_centro_costos' => $documento->id_centro_costos,
                        'cuenta' => $documento->cuenta,
                        'nombre_cuenta' => $documento->nombre_cuenta,
                        'numero_documento' => $documento->numero_documento,
                        'base_cuenta' => $documento->base_cuenta,
                        'porcentaje_cuenta' => $documento->porcentaje_cuenta,
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
                        'diferencia' => 0,
                        'nivel' => 0,
                        'anulado' => $documento->anulado,
                        'total_columnas' => '',
                        'fecha_creacion' => $documento->fecha_creacion,
                        'fecha_edicion' => $documento->fecha_edicion,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                    ];
                }
                unset($documentos);
            });
            
        $this->addTotalData($query);
    }  
    
    private function addTotalData($query)
    {
        $totaldata = DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS documentosgeneralesdata"))
            ->mergeBindings($query)
            ->select(
                DB::raw("SUM(debito) AS debito"),
                DB::raw("SUM(credito) AS credito"),
                DB::raw("SUM(debito) - SUM(credito) AS diferencia"),
                DB::raw("SUM(total_columnas) AS total_columnas")
            )
            ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
            ->first();

        $this->documentosCollection[] = [
            'id_documentos_generales' => $this->id_documentos_generales,
            'id_nit' => null,
            'id_cuenta' => null,
            'id_usuario' => null,
            'id_comprobante' => null,
            'id_centro_costos' => null,
            'cuenta' => 'TOTAL',
            'nombre_cuenta' => null,
            'base_cuenta' => null,
            'porcentaje_cuenta' => null,
            'numero_documento' => null,
            'nombre_nit' => null,
            'razon_social' => null,
            'apartamento_nit' => null,
            'codigo_cecos' => null,
            'nombre_cecos' => null,
            'codigo_comprobante' => null,
            'nombre_comprobante' => null,
            'documento_referencia' => null,
            'consecutivo' => null,
            'concepto' => null,
            'fecha_manual' => null,
            'debito' => $totaldata->debito,
            'credito' => $totaldata->credito,
            'diferencia' => $totaldata->diferencia,
            'total_columnas' => $totaldata->total_columnas,
            'nivel' => 99,
            'anulado' => 0,
            'fecha_creacion' => null,
            'fecha_edicion' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    private function DocumentosGeneralesQuery()
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select([
                'DG.id AS id',
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
            ->when(isset($this->request['consecutivo_desde']), function ($query) {
                $query->where('DG.consecutivo', '>=', $this->request['consecutivo_desde']);
            })
            ->when(isset($this->request['consecutivo_hasta']), function ($query) {
                $query->where('DG.consecutivo', '<=', $this->request['consecutivo_hasta']);
            })
            ->when(isset($this->request['concepto']) ? $this->request['concepto'] : false, function ($query) {
                $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
            })
            ->when(isset($this->request['id_usuario']) ? $this->request['id_usuario'] : false, function ($query) {
                $query->where('DG.concepto', 'LIKE', '%'.$this->request['concepto'].'%');
            })
            ->when(true, function ($query) {
                if ($this->request['anulado'] != null) {
                    $query->where('DG.anulado', $this->request['anulado']);
                }
            })
            ;
    }

    private function newCuentaTotal($cuenta, $documento)
    {
        $this->documentosCollection[$cuenta] = [
            'id_documentos_generales' => $this->id_documentos_generales,
            'id_nit' => in_array('id_nit', $this->agrupacion) ? $documento->id_nit : null,
            'id_cuenta' => in_array('id_cuenta', $this->agrupacion) ? $documento->id_cuenta : null,
            'id_usuario' => null,
            'id_comprobante' => $documento->id_comprobante,
            'id_centro_costos' => in_array('id_centro_costos', $this->agrupacion) ? $documento->id_centro_costos : null,
            'cuenta' => in_array('id_cuenta', $this->agrupacion) ? $documento->cuenta : null,
            'nombre_cuenta' => in_array('id_cuenta', $this->agrupacion) ? $documento->nombre_cuenta : null,
            'base_cuenta' => in_array('id_cuenta', $this->agrupacion) ? $documento->base_cuenta : null,
            'porcentaje_cuenta' => in_array('id_cuenta', $this->agrupacion) ? $documento->porcentaje_cuenta : null,
            'numero_documento' => in_array('id_nit', $this->agrupacion) ? $documento->numero_documento : null,
            'nombre_nit' => in_array('id_nit', $this->agrupacion) ? $documento->nombre_nit : null,
            'razon_social' => in_array('id_nit', $this->agrupacion) ? $documento->razon_social : null,
            'apartamento_nit' => in_array('id_nit', $this->agrupacion) ? $documento->apartamentos : null,
            'codigo_cecos' => in_array('id_centro_costos', $this->agrupacion) ? $documento->codigo_cecos : null,
            'nombre_cecos' => in_array('id_centro_costos', $this->agrupacion) ? $documento->nombre_cecos : null,
            'codigo_comprobante' => in_array('id_comprobante', $this->agrupacion) ? $documento->codigo_comprobante : null,
            'nombre_comprobante' => in_array('id_comprobante', $this->agrupacion) ? $documento->nombre_comprobante : null,
            'documento_referencia' => in_array('documento_referencia', $this->agrupacion) ? $documento->documento_referencia : null,
            'consecutivo' => in_array('consecutivo', $this->agrupacion) ? $documento->consecutivo : null,
            'concepto' => null,
            'fecha_manual' => in_array('consecutivo', $this->agrupacion) ? $documento->fecha_manual : null,
            'debito' => $documento->debito,
            'credito' => $documento->credito,
            'diferencia' => $documento->diferencia,
            'total_columnas' => $documento->total_columnas,
            'nivel' => 1,
            'anulado' => $documento->anulado,
            'fecha_creacion' => null,
            'fecha_edicion' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    private function newCuentaTotalNiveles($cuenta, $documento, $agrupacionesTotales)
    {
        $this->documentosCollection[$cuenta] = [
            'id_documentos_generales' => $this->id_documentos_generales,
            'id_nit' => in_array('id_nit', $agrupacionesTotales) ? $documento->id_nit : null,
            'id_cuenta' => in_array('id_cuenta', $agrupacionesTotales) ? $documento->id_cuenta : null,
            'id_usuario' => null,
            'id_comprobante' => in_array('id_comprobante', $agrupacionesTotales) ? $documento->id_comprobante : null,
            'id_centro_costos' => in_array('id_centro_costos', $agrupacionesTotales) ? $documento->id_centro_costos : null,
            'cuenta' => in_array('id_cuenta', $agrupacionesTotales) ? $documento->cuenta : null,
            'nombre_cuenta' => in_array('id_cuenta', $agrupacionesTotales) ? $documento->nombre_cuenta : null,
            'base_cuenta' => in_array('id_cuenta', $this->agrupacion) ? $documento->base_cuenta : null,
            'porcentaje_cuenta' => in_array('id_cuenta', $this->agrupacion) ? $documento->porcentaje_cuenta : null,
            'numero_documento' => in_array('id_nit', $agrupacionesTotales) ? $documento->numero_documento : null,
            'nombre_nit' => in_array('id_nit', $agrupacionesTotales) ? $documento->nombre_nit : null,
            'apartamento_nit' => in_array('id_nit', $agrupacionesTotales) ? $documento->nombre_nit : null,
            'razon_social' => in_array('id_nit', $agrupacionesTotales) ? $documento->razon_social : null,
            'codigo_cecos' => in_array('id_centro_costos', $agrupacionesTotales) ? $documento->codigo_cecos : null,
            'nombre_cecos' => in_array('id_centro_costos', $agrupacionesTotales) ? $documento->nombre_cecos : null,
            'codigo_comprobante' => in_array('id_comprobante', $agrupacionesTotales) ? $documento->codigo_comprobante : null,
            'nombre_comprobante' => in_array('id_comprobante', $agrupacionesTotales) ? $documento->nombre_comprobante : null,
            'documento_referencia' => in_array('documento_referencia', $agrupacionesTotales) ? $documento->documento_referencia : null,
            'consecutivo' => in_array('consecutivo', $agrupacionesTotales) ? $documento->consecutivo : null,
            'concepto' => null,
            'fecha_manual' => null,
            'debito' => $documento->debito,
            'credito' => $documento->credito,
            'diferencia' => $documento->diferencia,
            'total_columnas' => $documento->total_columnas,
            'nivel' => count($agrupacionesTotales),
            'anulado' => $documento->anulado,
            'fecha_creacion' => null,
            'fecha_edicion' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    private function newCuentaDetalle($cuenta, $documento, $detallar = true)
    {
        $this->documentosCollection[$cuenta.'A'.$documento->id] = [
            'id_documentos_generales' => $this->id_documentos_generales,
            'id_nit' => $documento->id_nit,
            'id_cuenta' => $documento->id_cuenta,
            'id_usuario' => $documento->created_by,
            'id_comprobante' => $documento->id_comprobante,
            'id_centro_costos' => $documento->id_centro_costos,
            'cuenta' => $documento->cuenta,
            'nombre_cuenta' => $documento->nombre_cuenta,
            'base_cuenta' =>$documento->base_cuenta,
            'porcentaje_cuenta' =>$documento->porcentaje_cuenta,
            'numero_documento' => $documento->numero_documento,
            'nombre_nit' => $documento->nombre_nit,
            'apartamento_nit' => $documento->apartamentos,
            'razon_social' => $documento->razon_social,
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
            'diferencia' => '',
            'nivel' => 0,
            'anulado' => $documento->anulado,
            'total_columnas' => '',
            'fecha_creacion' => $documento->fecha_creacion,
            'fecha_edicion' => $documento->fecha_edicion,
            'created_by' => $documento->created_by,
            'updated_by' => $documento->updated_by,
        ];
    }

    private function newCuentaDetalle2($documento)
    {
        $this->documentosCollection[] = [
            'id_documentos_generales' => $this->id_documentos_generales,
            'id_nit' => $documento->id_nit,
            'id_cuenta' => $documento->id_cuenta,
            'id_usuario' => $documento->created_by,
            'id_comprobante' => $documento->id_comprobante,
            'id_centro_costos' => $documento->id_centro_costos,
            'cuenta' => $documento->cuenta,
            'nombre_cuenta' => $documento->nombre_cuenta,
            'base_cuenta' =>$documento->base_cuenta,
            'porcentaje_cuenta' =>$documento->porcentaje_cuenta,
            'numero_documento' => $documento->numero_documento,
            'nombre_nit' => $documento->nombre_nit,
            'apartamento_nit' => $documento->apartamentos,
            'razon_social' => $documento->razon_social,
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
            'diferencia' => '',
            'nivel' => 0,
            'anulado' => $documento->anulado,
            'total_columnas' => '',
            'fecha_creacion' => $documento->fecha_creacion,
            'fecha_edicion' => $documento->fecha_edicion,
            'created_by' => $documento->created_by,
            'updated_by' => $documento->updated_by,
        ];
    }

    private function hasCuentaData($cuenta)
	{
		return isset($this->documentosCollection[$cuenta]);
	}

    private function sumCuentaData($cuenta, $data)
    {
        $this->documentosCollection[$cuenta]['debito']+= number_format((float)$data->debito, 2, '.', '');
        $this->documentosCollection[$cuenta]['credito']+= number_format((float)$data->credito, 2, '.', '');
        $this->documentosCollection[$cuenta]['diferencia']+= number_format((float)$data->diferencia, 2, '.', '');
        $this->documentosCollection[$cuenta]['total_columnas']+= number_format((float)$data->total_columnas, 2, '.', '');
    }
}
