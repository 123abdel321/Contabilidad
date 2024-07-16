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
			'invoice_lines' => $this->invoiceLines()
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
			'merchant_registration' => "f",
		];
	}
}
