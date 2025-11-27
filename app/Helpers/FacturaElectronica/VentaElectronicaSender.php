<?php

namespace App\Helpers\FacturaElectronica;

class VentaElectronicaSender extends AbstractFESender
{
	private $endpoint = '/invoice';

	public function getEndpoint(): string
	{
		return $this->endpoint;
	}

	public function getExtraParams(): array
	{
		return [
			'payment_form' => $this->paymentForm(),
			'customer' => $this->customer(),
			'invoice_lines' => $this->invoiceLines(),
			'legal_monetary_totals' => $this->legalMonetaryTotals()
		];
	}

	public function getRelationShips(): array
	{
		return [
			'cliente',
			'pagos',
			'resolucion',
			'detalles.cuenta_iva',
			'detalles.cuenta_retencion',
			'detalles.producto'
		];
	}

	protected function customer()
	{
		return [
			'identification_number' => $this->cliente->numero_documento,
			'name' => $this->cliente->nombre_completo,
			'email' => $this->cliente->email,
			'phone' => $this->cliente->telefono_1 ? $this->cliente->telefono_1 : $this->cliente->telefono_2,
			'address' => $this->cliente->direccion,
			'merchant_registration' => "0000000-00",
		];
	}

	protected function legalMonetaryTotals()
	{
		return [
			'line_extension_amount' => number_format($this->factura->subtotal - $this->factura->total_iva, 2, '.', ''), // Total con Impuestos
			'tax_exclusive_amount' => $this->factura->total_iva ? number_format($this->factura->subtotal - $this->factura->total_iva, 2, '.', '') : "0.00", // Total sin impuestos pero con descuentos
			'tax_inclusive_amount' => $this->factura->total_factura + $this->factura->total_rete_fuente, // Total con Impuestos
			'allowance_total_amount' => $this->factura->total_descuento, // Descuentos nivel de factura
			'charge_total_amount' => "0.00", // Cargos
			'payable_amount' => $this->factura->total_factura + $this->factura->total_rete_fuente, // Valor total a pagar
		];
	}

}
