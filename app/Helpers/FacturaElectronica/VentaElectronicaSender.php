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
		$ivaIncluido = false; // Define si tu precio unitario ya incluye IVA o no
		
		// 1. LINE EXTENSION AMOUNT (suma de valores de línea sin impuestos)
		if ($ivaIncluido) {
			// Si el precio unitario ya incluye IVA
			$line_extension_amount = $this->calculateSubtotalSinIva();
		} else {
			// Si el precio unitario NO incluye IVA (lo más común)
			$line_extension_amount = $this->factura->subtotal; // Esto debería ser sin IVA
		}
		
		// 2. TAX EXCLUSIVE AMOUNT (base gravable)
		$tax_exclusive_amount = $line_extension_amount 
			+ $this->factura->total_cargos  // Si tienes cargos
			- $this->factura->total_descuentos; // Si tienes descuentos a nivel factura
		
		// 3. TAX INCLUSIVE AMOUNT (total con impuestos)
		$tax_inclusive_amount = $tax_exclusive_amount 
			+ $this->factura->total_iva 
			+ $this->factura->total_otros_impuestos; // Si tienes otros impuestos
		
		// 4. PAYABLE AMOUNT (valor a pagar)
		$payable_amount = $tax_inclusive_amount 
			- $this->factura->total_retenciones; // Si tienes retenciones
		
		return [
			'line_extension_amount' => number_format($line_extension_amount, 2, '.', ''),
			'tax_exclusive_amount' => number_format($tax_exclusive_amount, 2, '.', ''),
			'tax_inclusive_amount' => number_format($tax_inclusive_amount, 2, '.', ''),
			'allowance_total_amount' => number_format($this->factura->total_descuentos ?? 0, 2, '.', ''),
			'charge_total_amount' => number_format($this->factura->total_cargos ?? 0, 2, '.', ''),
			'payable_amount' => number_format($payable_amount, 2, '.', ''),
		];
}

	protected function getSubtotalTax()
	{
		$subtotalTax = 0;
		foreach ($this->factura->detalles as $key => $detalle) {
			if ((int)$detalle->iva_porcentaje) {
				$subtotalTax+= $detalle->subtotal;
			}
		}

		return number_format($subtotalTax, 2, '.', '');
	}

}
