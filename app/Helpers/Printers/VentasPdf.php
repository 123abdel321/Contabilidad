<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\PlanCuentas;

class VentasPdf extends AbstractPrinterPdf
{
    public $venta;
	public $empresa;

    public function __construct(Empresa $empresa, FacVentas $venta)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->venta = $venta;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.facturacion.ventas-pos';
	}

    public function name()
	{
		return 'venta_'.uniqid();
	}

    public function paper()
	{
		return '';
	}

    public function data()
    {
        $this->venta->load([
            'cliente',
            'comprobante',
            'detalles',
			'pagos.forma_pago'
        ]);
		// dd($this->venta->pagos[0]->forma_pago);
		$impuestosIva = [];
		$groupImpuestosIva = $this->venta->detalles->groupBy('id_cuenta_venta_iva');

		if (count($groupImpuestosIva) > 0) {
			foreach ($groupImpuestosIva as $key => $gImpuestos) {
				if ($key) {
					$cuentaImpuesto = PlanCuentas::where('id', $key)
						->with('impuesto')
						->first();
					$impuestosIva[] = (object)[
						'nombre' => $cuentaImpuesto->impuesto->nombre,
						'porcentaje' => $cuentaImpuesto->impuesto->porcentaje,
						'base' => $gImpuestos->sum('subtotal'),
						'total'	=> $gImpuestos->sum('iva_valor')
					];
				}
			}
		}
		// dd($this->venta->cliente);
        return [
			'empresa' => $this->empresa,
			'cliente' => $this->venta->cliente,
			'factura' => $this->venta,
			'productos' => $this->venta->detalles,
			'pagos' => $this->venta->pagos,
			'impuestosIva' => $impuestosIva,
			'observacion' => $this->venta->observacion,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user()->username,
			'total_factura' => number_format($this->venta->total_factura)
		];
    }
}