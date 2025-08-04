<?php

namespace App\Helpers\FacturaElectronica;

class NotaCreditoElectronicaSender extends AbstractFESender
{
	private $endpoint = '/credit-note';

	public function getEndpoint(): string
	{
		return $this->endpoint;
	}

	public function getExtraParams(): array
	{
		return [
			'payment_form' => $this->paymentForm(),
			'customer' => $this->customer(),
			'legal_monetary_totals' => $this->legalMonetaryTotals(),
			'credit_note_lines' => $this->invoiceLines(),
			'billing_reference' => $this->creditNote()
		];
	}

	private function creditNote()
	{
		$data = [
			"number" => $this->factura->factura->consecutivo,
			"uuid" =>  $this->factura->factura->cufe,
			"issue_date" => date_format(date_create($this->factura->created_at), 'Y-m-d')
		];
		return $data;
	}

	public function getRelationShips() : array
	{
		return [
			'cliente',
			'pagos',
			'resolucion',
			'detalles.cuenta_iva',
			'detalles.cuenta_retencion',
			'detalles.producto',
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
			'line_extension_amount' => number_format($this->factura->subtotal, 2, '.', ''), // Total con Impuestos
			'tax_exclusive_amount' => $this->factura->total_iva ? number_format($this->factura->subtotal, 2, '.', '') : "0.00", // Total sin impuestos pero con descuentos
			'tax_inclusive_amount' => $this->factura->total_factura + $this->factura->total_rete_fuente, // Total con Impuestos
			'allowance_total_amount' => $this->factura->total_descuento, // Descuentos nivel de factura
			'charge_total_amount' => "0.00", // Cargos
			'payable_amount' => $this->factura->total_factura + $this->factura->total_rete_fuente, // Valor total a pagar
		];
	}
}
