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
		// if ($this->tipoEmpresion == 0) {
		// 	return 'pdf.facturacion.ventas-pos';
		// }
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
		// if ($this->tipoEmpresion == 1) return [0, 0, 396, 612];
		return 'A4';
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
				'*',
				DB::raw("SUM(valor) AS valor_sum")
			)
			->with('forma_pago')
			->whereHas('venta', function ($query) {
				$query->where('fecha_manual', '>=', $this->request['fecha_desde'])
					->where('fecha_manual', '<=', $this->request['fecha_hasta'])
					->where('id_bodega', $this->request['id_bodega'])
					->where('codigo_tipo_documento_dian', CodigoDocumentoDianTypes::VENTA_NACIONAL);
			})
			->groupBy('id_forma_pago')
			->get();

		$pagosTotalesSaldo = DocumentosGeneral::where('fecha_manual', '<=', $this->request['fecha_desde'])
			->when($bodega->id_cuenta_cartera ?? false, function ($query) use ($bodega) {
				$query->whereIn('id_cuenta', $bodega->id_cuenta_cartera);
			});

		$pagosTotalesSaldoAnterior = DocumentosGeneral::where('fecha_manual', '<', $this->request['fecha_desde'])
			->when($bodega->id_cuenta_cartera ?? false, function ($query) use ($bodega) {
				$query->whereIn('id_cuenta', $bodega->id_cuenta_cartera);
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
		];
    }
}