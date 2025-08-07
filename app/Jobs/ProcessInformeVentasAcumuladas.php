<?php

namespace App\Jobs;

use DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfVentasAcumulada;
use App\Models\Informes\InfVentasAcumuladaDetalle;

class ProcessInformeVentasAcumuladas
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $empresa;
    public $request;
    public $id_usuario;
	public $id_empresa;
    public $timeout = 300;
    public $id_venta_acumulada;
    public $ventaAcumuladaCollection = [];

    public function __construct($request, $id_usuario, $id_empresa, $id_venta_acumulada)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
        $this->id_venta_acumulada = $id_venta_acumulada;
    }

    public function handle()
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $this->empresa = Empresa::find($this->id_empresa);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db);

        try {
            
            DB::connection('informes')->beginTransaction();

            $this->addDataPadres();
            if ($this->request['detallar_venta']) $this->addDataDetalles();

            ksort($this->ventaAcumuladaCollection, SORT_STRING | SORT_FLAG_CASE);
            foreach (array_chunk($this->ventaAcumuladaCollection,233) as $ventaAcumuladaCollection){
                
                DB::connection('informes')
                    ->table('inf_ventas_acumulada_detalles')
                    ->insert(array_values($ventaAcumuladaCollection));
			}

            InfVentasAcumulada::where('id', $this->id_venta_acumulada)->update([
                'estado' => 2
            ]);

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-ventas-acumuladas-'.$this->empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Ventas acumuladas generado',
                'id_venta_acumulada' => $this->id_venta_acumulada,
                'autoclose' => false
            ]));

            $endTime = microtime(true);
            $endMemory = memory_get_usage();

            $executionTime = $endTime - $startTime;
            $memoryUsage = $endMemory - $startMemory;
            
            Log::info("Informe ventas acumuladas ejecutado en {$executionTime} segundos, usando {$memoryUsage} bytes de memoria.");


        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
			throw $exception;
        }
    }

    private function addDataPadres()
    {
        $query = $this->ventasDetalleQuery();
        
        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS ventasAcumuladas"))
            ->mergeBindings($query)
            ->select(
                'id',
                'id_nit',
                'id_producto',
                'id_cliente',
                'id_resolucion',
                'id_comprobante',
                'id_bodega',
                'id_forma_pago',
                'fecha_manual',
                'documento_referencia',
                'nombre_nit',
                'nombre_vendedor',
                'razon_social',
                'numero_documento',
                'numero_documento_vendedor',
                'apartamentos',
                'codigo_productos',
                'nombre_productos',
                'codigo_cecos',
                'nombre_cecos',
                'codigo_bodega',
                'nombre_bodega',
                'id_centro_costos',
                'codigo_comprobante',
                'nombre_comprobante',
                'prefijo_resolucion',
                'nombre_resolucion',
                'nombre_pagos',
                'descripcion',
                'descuento_porcentaje',
                'rete_fuente_porcentaje',
                'iva_porcentaje',
                'observacion',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                DB::raw('SUM(cantidad) AS cantidad'),
                DB::raw('SUM(costo) AS costo'),
                DB::raw('SUM(subtotal) AS subtotal'),
                DB::raw('SUM(descuento_valor) AS descuento_valor'),
                DB::raw('SUM(total) AS total'),
                DB::raw('SUM(iva_valor) AS iva_valor')
            )
            ->orderBy('id', 'DESC')
            ->groupByRaw($this->agrupadoPor())
            ->chunk(233, function ($detalles) {
                foreach ($detalles as $detalle) {
                    $this->getFormatPadreCollection($detalle);
                }
                unset($detalles);//Liberar memoria
            });
    }

    private function addDataDetalles()
    {
        $query = $this->ventasDetalleQuery();
        
        DB::connection('sam')
            ->table(DB::raw("({$query->toSql()}) AS ventasAcumuladas"))
            ->mergeBindings($query)
            ->select(
                'id',
                'id_nit',
                'id_producto',
                'id_cliente',
                'id_resolucion',
                'id_comprobante',
                'id_bodega',
                'id_forma_pago',
                'fecha_manual',
                'documento_referencia',
                'nombre_nit',
                'nombre_vendedor',
                'razon_social',
                'numero_documento',
                'numero_documento_vendedor',
                'apartamentos',
                'codigo_productos',
                'nombre_productos',
                'codigo_cecos',
                'nombre_cecos',
                'codigo_bodega',
                'nombre_bodega',
                'id_centro_costos',
                'codigo_comprobante',
                'nombre_comprobante',
                'prefijo_resolucion',
                'nombre_resolucion',
                'nombre_pagos',
                'descripcion',
                'descuento_porcentaje',
                'rete_fuente_porcentaje',
                'iva_porcentaje',
                'observacion',
                'fecha_creacion',
                'fecha_edicion',
                'created_by',
                'updated_by',
                'cantidad',
                'costo',
                'subtotal',
                'descuento_valor',
                'total',
                'iva_valor',
            )
            ->orderBy('id', 'DESC')
            ->chunk(233, function ($detalles) {
                foreach ($detalles as $detalle) {
                    $this->getFormatDetalleCollection($detalle);
                }
                unset($detalles);//Liberar memoria
            });
    }

    private function ventasDetalleQuery()
    {
        $ventasQuery = DB::connection('sam')->table('fac_venta_detalles AS FVD')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN N.id IS NOT NULL AND N.razon_social IS NOT NULL AND N.razon_social != '' THEN N.razon_social
                    WHEN N.id IS NOT NULL AND (N.razon_social IS NULL OR N.razon_social = '') THEN CONCAT_WS(' ', N.primer_nombre, N.primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                'VE.numero_documento AS numero_documento_vendedor',
                DB::raw("(CASE
                    WHEN VE.id IS NOT NULL AND VE.razon_social IS NOT NULL AND VE.razon_social != '' THEN VE.razon_social
                    WHEN VE.id IS NOT NULL AND (VE.razon_social IS NULL OR VE.razon_social = '') THEN CONCAT_WS(' ', VE.primer_nombre, VE.primer_apellido)
                    ELSE NULL
                END) AS nombre_vendedor"),
                "N.razon_social",
                "N.apartamentos",
                "CC.id AS id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "FR.prefijo AS prefijo_resolucion",
                "FR.nombre AS nombre_resolucion",
                "FB.codigo AS codigo_bodega",
                "FB.nombre AS nombre_bodega",
                "FP.codigo AS codigo_productos",
                "FP.nombre AS nombre_productos",
                'FVD.id',
                'FVD.id_producto',
                'FVD.descripcion',
                'FVD.cantidad',
                'FVD.costo',
                'FVD.subtotal',
                'FVD.descuento_porcentaje',
                'FVD.rete_fuente_porcentaje',
                'FVD.descuento_valor',
                'FVD.iva_porcentaje',
                'FVD.iva_valor',
                'FVD.total',
                'FVD.observacion',
                'FVD.created_by',
                'FVD.updated_by',
                'FV.id_cliente',
                'FV.id_resolucion',
                'FV.id_comprobante',
                'FV.id_bodega',
                'FV.documento_referencia',
                'FV.fecha_manual',
                'FVP.id_forma_pago',
                "FFP.nombre AS nombre_pagos",
                DB::raw("DATE_FORMAT(FVD.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(FVD.updated_at, '%Y-%m-%d %T') AS fecha_edicion")
            )
            ->leftJoin('fac_ventas AS FV', 'FVD.id_venta', 'FV.id')
            ->leftJoin('fac_venta_pagos AS FVP', 'FV.id', 'FVP.id_venta')
            ->leftJoin('fac_formas_pagos AS FFP', 'FVP.id_forma_pago', 'FFP.id')
            ->leftJoin('nits AS N', 'FV.id_cliente', 'N.id')
            ->leftJoin('nits AS VE', 'FV.id_vendedor', 'VE.id')
            ->leftJoin('fac_bodegas AS FB', 'FV.id_bodega', 'FB.id')
            ->leftJoin('centro_costos AS CC', 'FV.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'FV.id_comprobante', 'CO.id')
            ->leftJoin('fac_resoluciones AS FR', 'FV.id_resolucion', 'FR.id')
            ->leftJoin('fac_productos AS FP', 'FVD.id_producto', 'FP.id')

            ->where('FV.fecha_manual', '>=', Carbon::parse($this->request['fecha_desde'])->format('Y-m-d'))
            ->where('FV.fecha_manual', '<=', Carbon::parse($this->request['fecha_hasta'])->format('Y-m-d'))
            ->when(isset($this->request['id_nit']) ? true : false, function ($query) {
                $query->where('FV.id_cliente', $this->request['id_nit']);
            })
            ->when(isset($this->request['id_nit']) ? true : false, function ($query) {
                $query->where('FV.id_cliente', $this->request['id_nit']);
            })
            ->when(isset($this->request['documento_referencia']) ? true : false, function ($query) {
                $query->where('FV.documento_referencia', $this->request['documento_referencia']);
            })
            ->when(isset($this->request['id_resolucion']) ? true : false, function ($query) {
                $query->where('FV.id_resolucion', $this->request['id_resolucion']);
            })
            ->when(isset($this->request['id_bodega']) ? true : false, function ($query) {
                $query->where('FV.id_bodega', $this->request['id_bodega']);
            })
            ->when(isset($this->request['id_usuario']) ? true : false, function ($query) {
                $query->where('FV.created_by', $this->request['id_usuario']);
            })
            ->when(isset($this->request['id_forma_pago']) ? true : false, function ($query) {
                $query->where('FVP.id_forma_pago', $this->request['id_forma_pago']);
            })
            ->when(isset($this->request['id_producto']) ? true : false, function ($query) {
                $query->where('FVD.id_producto', $this->request['id_producto']);
            });

        return $ventasQuery;
    }

    private function getFormatPadreCollection($detalle)
    {
        $key = $this->generarKeyVentaAcumulada($detalle);

        $this->ventaAcumuladaCollection[$key] = [
            'id_venta_acumulada' => $this->id_venta_acumulada,
            'documento_referencia' => $this->generarNombreVentaAcumulada($detalle),
            'id_nit' => $detalle->id_nit,
            'id_cuenta' => '',
            'id_usuario' => $detalle->created_by,
            'id_comprobante' => $detalle->id_comprobante,
            'id_centro_costos' => $detalle->id_centro_costos,
            'numero_documento' => $detalle->numero_documento,
            'nombre_nit' => $detalle->nombre_nit,
            'nombre_vendedor' => $detalle->nombre_vendedor ? "$detalle->numero_documento_vendedor $detalle->nombre_vendedor" : null,
            'razon_social' => $detalle->razon_social,
            'codigo_cecos' => $detalle->codigo_cecos,
            'nombre_cecos' => $detalle->nombre_cecos,
            'codigo_comprobante' => $detalle->codigo_comprobante,
            'nombre_comprobante' => $detalle->nombre_comprobante,
            'codigo_bodega' => $detalle->codigo_bodega,
            'nombre_bodega' => $detalle->nombre_bodega,
            'codigo_producto' => $detalle->codigo_productos,
            'nombre_producto' => $detalle->nombre_productos,
            'consecutivo' => $detalle->documento_referencia,
            'observacion' => $detalle->observacion,
            'fecha_manual' => $detalle->fecha_manual,
            'cantidad' => $detalle->cantidad,
            'costo' => $detalle->costo,
            'subtotal' => $detalle->subtotal,
            'descuento_porcentaje' => '',
            'rete_fuente_porcentaje' => '',
            'descuento_valor' => $detalle->descuento_valor,
            'iva_porcentaje' => '',
            'iva_valor' => $detalle->iva_valor,
            'total' => $detalle->total,
            'fecha_creacion' => $detalle->fecha_creacion,
            'fecha_edicion' => $detalle->fecha_edicion,
            'created_by' => $detalle->created_by,
            'updated_by' => $detalle->updated_by,
            'nivel' => $this->request['detallar_venta'] ? 1 : 0,
        ];
    }

    private function getFormatDetalleCollection($detalle)
    {
        $key = $this->generarKeyVentaAcumulada($detalle);

        $this->ventaAcumuladaCollection["$key-{$detalle->id}"] = [
            'id_venta_acumulada' => $this->id_venta_acumulada,
            'documento_referencia' => $detalle->documento_referencia,
            'id_nit' => $detalle->id_nit,
            'id_cuenta' => '',
            'id_usuario' => $detalle->created_by,
            'id_comprobante' => $detalle->id_comprobante,
            'id_centro_costos' => $detalle->id_centro_costos,
            'numero_documento' => $detalle->numero_documento,
            'nombre_nit' => $detalle->nombre_nit,
            'nombre_vendedor' => $detalle->nombre_vendedor ? "$detalle->numero_documento_vendedor $detalle->nombre_vendedor" : null,
            'razon_social' => $detalle->razon_social,
            'codigo_cecos' => $detalle->codigo_cecos,
            'nombre_cecos' => $detalle->nombre_cecos,
            'codigo_comprobante' => $detalle->codigo_comprobante,
            'nombre_comprobante' => $detalle->nombre_comprobante,
            'codigo_bodega' => $detalle->codigo_bodega,
            'nombre_bodega' => $detalle->nombre_bodega,
            'codigo_producto' => $detalle->codigo_productos,
            'nombre_producto' => $detalle->nombre_productos,
            'consecutivo' => $detalle->documento_referencia,
            'observacion' => $detalle->observacion,
            'fecha_manual' => $detalle->fecha_manual,
            'cantidad' => $detalle->cantidad,
            'costo' => $detalle->costo,
            'subtotal' => $detalle->subtotal,
            'descuento_porcentaje' => $detalle->descuento_porcentaje,
            'rete_fuente_porcentaje' => $detalle->rete_fuente_porcentaje,
            'descuento_valor' => $detalle->descuento_valor,
            'iva_porcentaje' => $detalle->iva_porcentaje,
            'iva_valor' => $detalle->iva_valor,
            'total' => $detalle->total,
            'fecha_creacion' => $detalle->fecha_creacion,
            'fecha_edicion' => $detalle->fecha_edicion,
            'created_by' => $detalle->created_by,
            'updated_by' => $detalle->updated_by,
            'nivel' => 0,
        ];
    }

    private function generarKeyVentaAcumulada($detalle)
    {
        $keyAgrupado = "A";
        
        switch ($this->request['id_tipo_informe']) {
            case '1':
                $keyAgrupado = "A{$detalle->id_resolucion}";
                break;
            case '2':
                $keyAgrupado = "A{$detalle->id_bodega}";
                break;
            case '3':
                $keyAgrupado = "A{$detalle->id_producto}";
                break;
            case '4':
                $keyAgrupado = "A{$detalle->id_forma_pago}";
                break;
            default:
                $keyAgrupado = "A{$detalle->id_nit}";
                break;
        }
        return $keyAgrupado;
    }

    private function generarNombreVentaAcumulada($detalle)
    {
        $nombreAgrupado = "";
        
        switch ($this->request['id_tipo_informe']) {
            case '1':
                $nombreAgrupado = "{$detalle->prefijo_resolucion} - {$detalle->documento_referencia}";
                break;
            case '2':
                $nombreAgrupado = "{$detalle->codigo_bodega} - {$detalle->nombre_bodega}";
                break;
            case '3':
                $nombreAgrupado = "{$detalle->codigo_productos} - {$detalle->nombre_productos}";
                break;
            case '4':
                $nombreAgrupado = "{$detalle->nombre_pagos}";
                break;
            default:
                $nombreAgrupado = "{$detalle->numero_documento} - {$detalle->nombre_nit}";
                break;
        }
        return $nombreAgrupado;
    }

    private function agrupadoPor()
    {
        $agrupadoPor = null;
        
        switch ($this->request['id_tipo_informe']) {
            case '1':
                $agrupadoPor = 'id_resolucion';
                break;
            case '2':
                $agrupadoPor = 'id_bodega';
                break;
            case '3':
                $agrupadoPor = 'id_producto';
                break;
            case '4':
                $agrupadoPor = 'id_forma_pago';
                break;
            default:
                $agrupadoPor = 'id_cliente';
                break;
        }

        return $agrupadoPor;
    }

    public function failed($exception)
    {
        DB::connection('informes')->rollBack();
        
        // Si no tenemos la empresa, intentamos obtenerla
        if (!$this->empresa && $this->id_empresa) {
            $this->empresa = Empresa::find($this->id_empresa);
        }

        $token_db = $this->empresa ? $this->empresa->token_db : 'unknown';

        InfVentasGenerales::where('id', $this->id_extracto)->update([
            'estado' => 0
        ]);

        event(new PrivateMessageEvent(
            'informe-ventas-acumuladas-'.$token_db.'_'.$this->id_usuario, 
            [
                'tipo' => 'error',
                'mensaje' => 'Error al generar el informe: '.$exception->getMessage(),
                'titulo' => 'Error en proceso',
                'autoclose' => false
            ]
        ));

        // Registrar el error en los logs
        logger()->error("Error en ProcessInformeVentasGenerales: ".$exception->getMessage(), [
            'exception' => $exception,
            'request' => $this->request,
            'user_id' => $this->id_usuario,
            'empresa_id' => $this->id_empresa
        ]);
    }
}