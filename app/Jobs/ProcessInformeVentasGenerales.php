<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfVentasGenerales;

class ProcessInformeVentasGenerales implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_venta_general;
    public $ventasGeneralesTotal = [];
    public $ventasGeneralesCollection = [];

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        $this->totalVentasGeneralesRow();
    }

    public function handle()
    {
        $empresa = Empresa::find($this->id_empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        DB::connection('informes')->beginTransaction();

        try {

            $ventasGenerales = InfVentasGenerales::create([
                'id_empresa' => $this->id_empresa,
                'fecha_desde' => $this->request['fecha_desde'],
                'fecha_hasta' => $this->request['fecha_hasta'],
                'precio_desde' => $this->request['precio_desde'],
                'precio_hasta' => $this->request['precio_hasta'],
                'id_nit' => $this->request['id_nit'],
                'id_usuario' => $this->request['id_usuario'],
                'id_bodega' => $this->request['id_bodega'],
                'id_resolucion' => $this->request['id_resolucion'],
                'consecutivo' => $this->request['consecutivo'],
            ]);

            $this->id_venta_general = $ventasGenerales->id;

            $this->ventasGenerales();
            $this->ventasGeneralesTotal['id_venta_general'] = $ventasGenerales->id;
            if ($this->ventasGeneralesTotal['total'] <0 ) $this->ventasGeneralesTotal['total'] = $this->ventasGeneralesTotal['total'] * -1;
            $this->ventasGeneralesCollection[] = $this->ventasGeneralesTotal;

            foreach (array_chunk($this->ventasGeneralesCollection,233) as $ventasGeneralesCollection){
                DB::connection('informes')
                    ->table('inf_ventas_generales_detalles')
                    ->insert(array_values($ventasGeneralesCollection));
			}

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-ventas-generales-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Ventas generales generadas',
                'id_venta_general' => $this->id_venta_general,
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();

			throw $exception;
        }
    }

    private function ventasGenerales()
    {
        DB::connection('sam')->table('fac_ventas AS V')
            ->select([
                'V.*',
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN N.id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN N.id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante"
            ])
            ->where('fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
            ->leftJoin('nits AS N', 'V.id_cliente', 'N.id')
            ->leftJoin('centro_costos AS CC', 'V.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'V.id_comprobante', 'CO.id')
            ->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL)
            ->when(isset($this->request['id_nit']) ? $this->request['id_nit'] : false, function ($query) {
                $query->where('V.id_cliente', $this->request['id_nit']);
            })
            ->when(isset($this->request['id_bodega']) ? $this->request['id_bodega'] : false, function ($query) {
                $query->where('V.id_bodega', $this->request['id_bodega']);
            })
            ->when(isset($this->request['id_resolucion']) ? $this->request['id_resolucion'] : false, function ($query) {
                $query->where('V.id_resolucion', $this->request['id_resolucion']);
            })
            ->when(isset($this->request['consecutivo']) ? $this->request['consecutivo'] : false, function ($query) {
                $query->where('V.consecutivo', $this->request['consecutivo']);
            })
            ->when(isset($this->request['id_usuario']) ? $this->request['id_usuario'] : false, function ($query) {
                $query->where('V.created_by', $this->request['id_usuario']);
            })
            // ->when(isset($this->request['id_producto']) ? $this->request['id_producto'] : false, function ($query) {
            //     $query->where('V.id_cliente', $this->request['id_producto']);
            // })
            ->orderByRaw('V.created_at')
            ->chunk(233, function ($ventas) {
                $ventas->each(function ($venta) {

                    $documentosVentas = $this->ventasGeneralesDocumentos($venta->id);

                    if (count($documentosVentas)) {
                        $keyTotal = $this->newCuentaTotal($venta, count($documentosVentas));
                        $this->newCuentaDetalle($documentosVentas, $keyTotal);
                    }
                });
            });
    }

    private function totalVentasGeneralesRow()
    {
        $this->ventasGeneralesTotal = [
            'id_venta_general' => NULL,
            'id_nit' => NULL,
            'id_cuenta' => NULL,
            'id_usuario' => NULL,
            'id_comprobante' => NULL,
            'id_centro_costos' => NULL,
            'cuenta' => NULL,
            'nombre_cuenta' => NULL,
            'numero_documento' => NULL,
            'nombre_nit' => NULL,
            'razon_social' => NULL,
            'codigo_cecos' => NULL,
            'nombre_cecos' => NULL,
            'codigo_comprobante' => NULL,
            'nombre_comprobante' => NULL,
            'documento_referencia' => NULL,
            'consecutivo' => NULL,
            'concepto' => NULL,
            'fecha_manual' => NULL,
            'debito' => 0,
            'credito' => 0,
            'total' => 0,
            'total_columnas' => 0,
            'nivel' => 99,
            'fecha_creacion' => NULL,
            'fecha_edicion' => NULL,
            'created_by' => NULL,
            'updated_by' => NULL,
        ];
    }

    private function newCuentaTotal($venta, $total_columnas = 0)
    {
        $this->ventasGeneralesCollection[] = [
            'id_venta_general' => $this->id_venta_general,
            'id_nit' => $venta->id_cliente,
            'id_cuenta' => NULL,
            'id_usuario' => $venta->created_by,
            'id_comprobante' => $venta->id_comprobante,
            'id_centro_costos' => $venta->id_centro_costos,
            'cuenta' => NULL,
            'nombre_cuenta' => NULL,
            'numero_documento' => $venta->numero_documento,
            'nombre_nit' =>  $venta->nombre_nit,
            'razon_social' => $venta->razon_social,
            'codigo_cecos' => $venta->codigo_cecos,
            'nombre_cecos' => $venta->nombre_cecos,
            'codigo_comprobante' => $venta->codigo_comprobante,
            'nombre_comprobante' => $venta->nombre_comprobante,
            'documento_referencia' => NULL,
            'consecutivo' => $venta->consecutivo,
            'concepto' => NULL,
            'fecha_manual' => $venta->fecha_manual,
            'debito' => NULL,
            'credito' => NULL,
            'total' => $venta->total_factura,
            'total_columnas' => $total_columnas,
            'nivel' => 1,
            'fecha_creacion' => $venta->created_at,
            'fecha_edicion' => $venta->updated_at,
            'created_by' => $venta->created_by,
            'updated_by' => $venta->created_by,
        ];

        return count($this->ventasGeneralesCollection) - 1;
    }

    private function newCuentaDetalle($documentosVentas, $keyTotal)
    {
        $documentosVentas->each(function ($documentoVenta) use($keyTotal) {

            $this->ventasGeneralesCollection[] = [
                'id_venta_general' => $this->id_venta_general,
                'id_nit' => $documentoVenta->id_nit,
                'id_cuenta' => $documentoVenta->id_cuenta,
                'id_usuario' => NULL,
                'id_comprobante' => $documentoVenta->id_comprobante,
                'id_centro_costos' => $documentoVenta->id_centro_costos,
                'cuenta' => $documentoVenta->cuenta,
                'nombre_cuenta' => $documentoVenta->nombre_cuenta,
                'numero_documento' => $documentoVenta->numero_documento,
                'nombre_nit' => $documentoVenta->nombre_nit,
                'razon_social' => $documentoVenta->razon_social,
                'codigo_cecos' => $documentoVenta->codigo_cecos,
                'nombre_cecos' => $documentoVenta->nombre_cecos,
                'codigo_comprobante' => $documentoVenta->codigo_comprobante,
                'nombre_comprobante' => $documentoVenta->nombre_comprobante,
                'documento_referencia' => $documentoVenta->documento_referencia,
                'consecutivo' => $documentoVenta->consecutivo,
                'concepto' => $documentoVenta->concepto,
                'fecha_manual' => $documentoVenta->fecha_manual,
                'debito' => $documentoVenta->debito,
                'credito' => $documentoVenta->credito,
                'total' => $documentoVenta->valor_total,
                'total_columnas' => NULL,
                'nivel' => 0,
                'fecha_creacion' => $documentoVenta->fecha_creacion,
                'fecha_edicion' => $documentoVenta->fecha_edicion,
                'created_by' => $documentoVenta->created_by,
                'updated_by' => $documentoVenta->updated_by,
            ];

            $this->ventasGeneralesCollection[$keyTotal]['debito']+= $documentoVenta->debito;
            $this->ventasGeneralesCollection[$keyTotal]['credito']+= $documentoVenta->credito;
            $this->ventasGeneralesCollection[$keyTotal]['total']+= ($documentoVenta->debito - $documentoVenta->credito);

            $this->ventasGeneralesTotal['debito']+= $documentoVenta->debito;
            $this->ventasGeneralesTotal['credito']+= $documentoVenta->credito;
            $this->ventasGeneralesTotal['total']+= ($documentoVenta->debito - $documentoVenta->credito);
            $this->ventasGeneralesTotal['total_columnas']+= 1;
        });
    }

    private function ventasGeneralesDocumentos($id_venta)
    {
        return DB::connection('sam')->table('documentos_generals AS DG')
            ->select([
                'DG.id',
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN N.id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN N.id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
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
                "debito",
                "credito",
                DB::raw("0 AS diferencia"),
                DB::raw("1 AS total_columnas"),
                DB::raw("IF(debito - credito < 0, (debito - credito) * -1, debito - credito) AS valor_total")
            ])
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->where('relation_type', 4)
            ->where('relation_id', $id_venta)
            ->where(function ($query) {
                $query->when(isset($this->request['precio_desde']), function ($query) {
                    $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) >= ?', [$this->request['precio_desde']]);
                })->when(isset($this->request['precio_hasta']), function ($query) {
                    $query->whereRaw('IF(debito - credito < 0, (debito - credito) * -1, debito - credito) <= ?', [$this->request['precio_hasta']]);
                });
            })
            // ->when(isset($this->request['id_producto']) ? $this->request['id_nit'] : false, function ($query) {
            //     $query->where('V.id_cliente', $this->request['id_nit']);
            // })
            ->get();
    }

}