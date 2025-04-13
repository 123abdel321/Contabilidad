<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\VariablesEntorno;

class VentasPdf extends AbstractPrinterPdf
{
    public $venta;
	public $empresa;
	public $tipoEmpresion;

    public function __construct(Empresa $empresa, FacVentas $venta)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->venta = $venta;
		$this->empresa = $empresa;
		$this->tipoEmpresion = $this->venta->resolucion->tipo_impresion;
	}

    public function view()
	{
		if ($this->tipoEmpresion == 0) {
			return 'pdf.facturacion.ventas-pos';
		}
		return 'pdf.facturacion.ventas';
	}

    public function name()
	{
		return 'venta_'.uniqid();
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
        $this->venta->load([
			'resolucion',
            'cliente',
            'comprobante',
            'detalles',
			'pagos.forma_pago'
        ]);

		$impuestosIva = [];
		$groupImpuestosIva = $this->venta->detalles->groupBy('id_cuenta_venta_iva');

		if (count($groupImpuestosIva) > 0) {
			foreach ($groupImpuestosIva as $key => $gImpuestos) {
				if ($key) {
					$cuentaImpuesto = PlanCuentas::where('id', $key)
						->with('impuesto')
						->first();
					
					if ($cuentaImpuesto->impuesto) {
						$impuestosIva[] = (object)[
							'nombre' => $cuentaImpuesto->impuesto->nombre,
							'porcentaje' => $cuentaImpuesto->impuesto->porcentaje,
							'base' => $gImpuestos->sum('subtotal'),
							'total'	=> $gImpuestos->sum('iva_valor')
						];
					}
				}
			}
		}

		$qrCodeBase64 = null;

		if ($this->venta->fe_codigo_qr) {
			$svg = QrCode::format('svg')->size(300)->generate($this->venta->fe_codigo_qr);
			$qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);
		}

		$observacion_general = VariablesEntorno::where('nombre', 'observacion_venta')->first();
		$observacion_general = $observacion_general ? $observacion_general->valor : NULL;

        return [
			'empresa' => $this->empresa,
			'cliente' => $this->venta->cliente,
			'factura' => $this->venta,
			'qrCode' => $qrCodeBase64,
			'productos' => $this->venta->detalles,
			'pagos' => $this->venta->pagos,
			'impuestosIva' => $impuestosIva,
			'observacion' => $this->venta->observacion,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user()->username,
			'observacion_general' => $observacion_general,
			'total_factura' => number_format($this->venta->total_factura)
		];
    }
}