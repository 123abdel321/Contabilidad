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
			'cliente.tipo_documento',
			'pagos.forma_pago',
			'resolucion',
			'detalles.cuenta_iva',
			'detalles.cuenta_retencion',
			'detalles.producto'
		];
	}

	protected function customer()
	{
		// type_organization_id: 1 = Persona Jurídica, 2 = Persona Natural
		// tipo_contribuyente: 1 = Persona jurídica, 2 = Persona natural
		$typeOrganizationId = $this->cliente->tipo_contribuyente == 1 ? 1 : 2;

		return [
			'identification_number' => $this->cliente->numero_documento,
			'name' => $this->cliente->nombre_completo,
			'email' => $this->cliente->email,
			'phone' => $this->cliente->telefono_1 ?: $this->cliente->telefono_2,
			'address' => $this->cliente->direccion,
			'merchant_registration' => "0000000-00",
			'type_document_identification_id' => $this->cliente->id_tipo_documento,
			'type_organization_id' => $typeOrganizationId,
		];
	}

	protected function legalMonetaryTotals()
	{
		$subtotal_sin_iva = $this->factura->subtotal;
		$line_extension_amount = $this->iva_inluido
			? $subtotal_sin_iva
			: $this->factura->total_iva + $this->factura->total_rete_fuente + $this->factura->subtotal;

		return [
			'line_extension_amount'  => number_format($line_extension_amount, 2, '.', ''),
			'tax_exclusive_amount'   => number_format($this->factura->subtotal, 2, '.', ''),  // ← SIN restar descuento
			'tax_inclusive_amount'   => number_format($this->factura->total_factura, 2, '.', ''),
			'allowance_total_amount' => "0.00",   // ← se queda en 0.00 (esto sí lo cambiaste bien)
			'charge_total_amount'    => "0.00",
			'payable_amount'         => number_format($this->factura->total_factura, 2, '.', ''),
		];
	}

}
