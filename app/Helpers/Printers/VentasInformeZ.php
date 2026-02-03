<?php

namespace App\Helpers\Printers;

use DB;
use Illuminate\Support\Carbon;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacVentaPagos;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\FacVentaDetalles;
use App\Models\Sistema\DocumentosGeneral;
use App\Models\Sistema\FacProductos;

class VentasInformeZ extends AbstractPrinterPdf
{
    public $empresa;
    public $request;

    public function __construct(Empresa $empresa, $request)
    {
        parent::__construct($empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);
        $this->empresa = $empresa;
        $this->request = $request;
    }

    public function view()
    {
        return 'pdf.facturacion.ventas-informez-pos';
    }

    public function name()
    {
        return 'ventas_informe_z_'.uniqid();
    }

    public function paper()
    {
        return '';
    }

    public function formatPaper()
    {
        return 'A4';
    }

    private function queryTotalesVentaCosto($esNota = false)
    {
        $ventasQuery = FacVentas::query()
            ->join('fac_venta_detalles as FVD', 'fac_ventas.id', '=', 'FVD.id_venta')
            ->join('fac_productos as FP', 'FVD.id_producto', '=', 'FP.id')
            ->select(
                'fac_ventas.*',
                'FVD.*',
                'FP.precio_inicial'
            );

        if ($esNota) {
            $ventasQuery->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO);
        } else {
            $ventasQuery->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL);
        }

        // Aplicar los mismos filtros que en el método generate()
        if (isset($this->request['id_cliente'])) {
            $ventasQuery->where('id_cliente', $this->request['id_cliente']);
        }

        if (isset($this->request['fecha_desde'])) {
            $ventasQuery->where('fecha_manual', '>=', $this->request['fecha_desde']);
        }

        if (isset($this->request['fecha_hasta'])) {
            $ventasQuery->where('fecha_manual', '<=', $this->request['fecha_hasta']);
        }

        if (isset($this->request['factura'])) {
            $ventasQuery->where('documento_referencia', 'LIKE', '%'.$this->request['factura'].'%');
        }

        if (isset($this->request['id_resolucion'])) {
            $ventasQuery->where('id_resolucion', $this->request['id_resolucion']);
        }

        if (isset($this->request['id_bodega'])) {
            $ventasQuery->where('id_bodega', $this->request['id_bodega']);
        }

        if (isset($this->request['id_producto'])) {
            $ventasQuery->whereHas('detalles', function ($query) {
                $query->where('id_producto', '=', $this->request['id_producto']);
            });
        }

        if (isset($this->request['id_forma_pago'])) {
            $ventasQuery->whereHas('pagos', function ($query) {
                $query->where('id_forma_pago', '=', $this->request['id_forma_pago']);
            });
        }

        if (isset($this->request['id_usuario'])) {
            $ventasQuery->where('created_by', $this->request['id_usuario']);
        }

        return $ventasQuery;
    }

    public function data()
    {
        $bodega = FacBodegas::where('id', $this->request['id_bodega'])->first();
        $resolucion = FacResoluciones::where('id', $this->request['id_resolucion'])->first();
        $formasPagoCaja = FacFormasPago::select('id')->where('id_cuenta', $bodega->id_cuenta_cartera)->get();
        $formasPagoCaja = $formasPagoCaja ? $formasPagoCaja->toArray() : [];
        
        $ventas = FacVentas::with('cliente')
            ->where('fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
            ->where('id_bodega', $this->request['id_bodega'])
            ->where('id_resolucion', $this->request['id_resolucion'])
            ->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL)
            ->orderBy('consecutivo', 'DESC')
            ->get();

        // Calcular los totales igual que en el método generate()
        $totalDataVenta = $this->queryTotalesVentaCosto(false)->select(
            DB::raw("SUM(FVD.cantidad) AS total_productos_cantidad"),
            DB::raw("SUM(FP.precio_inicial * FVD.cantidad) AS total_costo"),
            DB::raw("SUM(FVD.total) AS total_venta")
        )->get();

        $totalDataNotas = $this->queryTotalesVentaCosto(true)->select(
            DB::raw("SUM(FVD.cantidad) AS total_productos_cantidad"),
            DB::raw("SUM(FP.precio_inicial * FVD.cantidad) AS total_costo"),
            DB::raw("SUM(FVD.total) AS total_venta")
        )->get();

        $totalesVenta = $totalDataVenta[0] ?? (object)[
            'total_productos_cantidad' => 0,
            'total_costo' => 0,
            'total_venta' => 0
        ];

        $totalesNotas = $totalDataNotas[0] ?? (object)[
            'total_productos_cantidad' => 0,
            'total_costo' => 0,
            'total_venta' => 0
        ];

        // Calcular los mismos totales que en el frontend
        $total_venta = $totalesVenta->total_venta - $totalesNotas->total_venta;
        $total_costo = $totalesVenta->total_costo - $totalesNotas->total_costo;
        $total_cantidad = $totalesVenta->total_productos_cantidad - $totalesNotas->total_productos_cantidad;
        $total_utilidad = $total_venta - $total_costo;
        $porcentaje_utilidad = $totalesVenta->total_costo != 0 
            ? ($total_utilidad / $totalesVenta->total_costo) * 100 
            : 100;

        $pagosTotalesIngresos = FacVentaPagos::with('forma_pago', 'venta.cliente')
            ->when(isset($formasPagoCaja) ? $formasPagoCaja : false, function ($query) use ($formasPagoCaja) {
                $query->whereIn('id_forma_pago', $formasPagoCaja);
            })
            ->whereHas('venta', function ($query) {
                $query->where('fecha_manual', '>=', $this->request['fecha_desde'])
                    ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
                    ->where('id_bodega', $this->request['id_bodega'])
                    ->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL);
            })
            ->get();

        $pagosTotalesEgresos = FacVentaPagos::with('forma_pago', 'venta.cliente')
            ->when(isset($formasPagoCaja) ? $formasPagoCaja : false, function ($query) use ($formasPagoCaja) {
                $query->whereIn('id_forma_pago', $formasPagoCaja);
            })
            ->whereHas('venta', function ($query) {
                $query->where('fecha_manual', '>=', $this->request['fecha_desde'])
                    ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
                    ->where('id_bodega', $this->request['id_bodega'])
                    ->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO);
            })
            ->get();

        $devoluciones = FacVentas::with('documentos')
            ->where('fecha_manual', '>=', $this->request['fecha_desde'])
            ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
            ->where('id_bodega', $this->request['id_bodega'])
            ->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO);

        $pagosTotales = FacVentaPagos::select(
            'id_forma_pago',
            DB::raw("SUM(CASE 
                WHEN venta.codigo_tipo_documento_dian = '".CodigoDocumentoDianTypes::VENTA_NACIONAL."' 
                THEN fac_venta_pagos.valor 
                ELSE -fac_venta_pagos.valor 
            END) AS valor_sum")
        )
        ->with('forma_pago')
        ->join('fac_ventas as venta', 'fac_venta_pagos.id_venta', '=', 'venta.id')
        ->whereHas('venta', function ($query) {
            $query->where('fecha_manual', '>=', $this->request['fecha_desde'])
                ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
                ->where('id_bodega', $this->request['id_bodega'])
                ->whereIn('codigo_tipo_documento_dian', [
                    CodigoDocumentoDianTypes::VENTA_NACIONAL,
                    CodigoDocumentoDianTypes::NOTA_CREDITO
                ]);
        })
        ->groupBy('id_forma_pago')
        ->get();

        $pagosTotalesSaldo = DocumentosGeneral::where('fecha_manual', '<=', $this->request['fecha_desde'])
            ->when($bodega->id_cuenta_cartera ?? false, function ($query) use ($bodega) {
                $query->where('id_cuenta', $bodega->id_cuenta_cartera);
            });

        $pagosTotalesSaldoAnterior = DocumentosGeneral::where('fecha_manual', '<', $this->request['fecha_desde'])
            ->when($bodega->id_cuenta_cartera ?? false, function ($query) use ($bodega) {
                $query->where('id_cuenta', $bodega->id_cuenta_cartera);
            });

        $idsFormasPagoUsadas = [];
        foreach ($pagosTotales as $pagosTotal) {
            $idsFormasPagoUsadas[] = $pagosTotal->id_forma_pago;
        }

        $formasPagoVentas = FacFormasPago::whereHas('cuenta', function ($query) {
                $query->whereNotNull('naturaleza_ventas');
            })
            ->whereNotIn('id', $idsFormasPagoUsadas)
            ->get();

        $devolucionesEfectivo = FacVentaPagos::whereHas('venta', function ($query) {
                $query->where('fecha_manual', '>=', $this->request['fecha_desde'])
                    ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
                    ->where('id_bodega', $this->request['id_bodega'])
                    ->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO)
                    ->whereHas('factura',  function ($q) {
                        $q->where('id_resolucion', $this->request['id_resolucion']);
                });
            })
            ->when(isset($formasPagoCaja) ? $formasPagoCaja : false, function ($query) use ($formasPagoCaja) {
                $query->whereIn('id_forma_pago', $formasPagoCaja);
            })
            ->get();

        $devolucionesTotales = FacVentaPagos::whereHas('venta', function ($query) {
            $query->where('fecha_manual', '>=', $this->request['fecha_desde'])
                ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
                ->where('id_bodega', $this->request['id_bodega'])
                ->whereNotNull('id_factura')
                ->whereHas('factura',  function ($q) {
                    $q->where('id_resolucion', $this->request['id_resolucion']);
                });
            })
            ->get();

        $ventasDetalleIvaXCuenta = FacVentaDetalles::whereHas('venta', function ($query) {
            $query->where('fecha_manual', '>=', $this->request['fecha_desde'])
                ->where('fecha_manual', '<=', $this->request['fecha_hasta'])
                ->where('id_bodega', $this->request['id_bodega'])
                ->where('id_resolucion', $this->request['id_resolucion']);
            })
            ->with('cuenta_iva.impuesto')
            ->groupBy('id_cuenta_venta_iva')
            ->get();
        
        $lastItemventas = count($ventas) - 1;
        $rango_facturas = count($ventas) ? $resolucion->prefijo .' '. $ventas[0]->consecutivo . ' - ' . $ventas[$lastItemventas]->consecutivo : 'SIN FACTURAS';

        return [
            'empresa' => $this->empresa,
            'fecha_desde' => $this->request['fecha_desde'],
            'fecha_hasta' => $this->request['fecha_hasta'],
            'bodega' => $bodega,
            'resolucion' => $resolucion,
            'rango_facturas' => $rango_facturas,
            'count_facturas' => count($ventas),
            'devoluciones' => $devoluciones->count(),
            'devoluciones_efectivo' => $devolucionesEfectivo->sum('valor'),
            'devoluciones_generales' => $devolucionesTotales->sum('valor'),
            'formas_pagos' => $pagosTotales,
            'formas_pagos_sin_uso' => $formasPagoVentas,
            'ventas_iva' => $ventasDetalleIvaXCuenta,
            'ventas' => $ventas,
            'pagos_ingresos' => $pagosTotalesIngresos,
            'pagos_egresos' => $pagosTotalesEgresos,
            'saldo_total' => $pagosTotalesSaldo->sum('debito'),
            'saldo_anterior_total' => $pagosTotalesSaldoAnterior->sum('debito'),
            'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
            
            // Agregar los totales calculados igual que en el frontend
            'totalesVenta' => $totalesVenta,
            'totalesNotas' => $totalesNotas,
            'total_venta_neto' => $total_venta,
            'total_costo_neto' => $total_costo,
            'total_cantidad_neto' => $total_cantidad,
            'total_utilidad' => $total_utilidad,
            'porcentaje_utilidad' => $porcentaje_utilidad,
        ];
    }
}