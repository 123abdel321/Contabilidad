<?php

namespace App\Helpers\Printers;

use Illuminate\Support\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacPedidos;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\VariablesEntorno;

class PedidosPdf extends AbstractPrinterPdf
{
    public $venta;
	public $empresa;
	public $tipoEmpresion;

    public function __construct(Empresa $empresa, FacPedidos $pedido)
	{
		parent::__construct($empresa);

		copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		$this->pedido = $pedido;
		$this->empresa = $empresa;
	}

    public function view()
	{
		return 'pdf.facturacion.pedidos-pos';
	}

    public function name()
	{
		return 'pedido_'.uniqid();
	}

    public function paper()
	{
		return '';
	}

	public function formatPaper()
	{
		return 'A4';
	}

    public function data()
    {
        $this->pedido->load([
			'cliente',
            'cliente',
            'detalles',
            'ubicacion',
            'centro_costo',
        ]);

		$impuestosIva = [];
		$groupImpuestosIva = $this->pedido->detalles->groupBy('id_cuenta_venta_iva');

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

		$has_observacion = $this->pedido->detalles->contains(function ($detalle) {
			return $detalle->observacion !== null && trim($detalle->observacion) !== '';
		});

        return [
			'empresa' => $this->empresa,
			'cliente' => $this->pedido->cliente,
			'pedido' => $this->pedido,
			'ubicacion' => $this->pedido->ubicacion,
			'productos' => $this->pedido->detalles,
			'impuestosIva' => $impuestosIva,
			'has_observacion' => $has_observacion,
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user()->username,
			// 'observacion_general' => $observacion_general,
			'total_pedido' => number_format($this->pedido->total_factura)
		];
    }
}