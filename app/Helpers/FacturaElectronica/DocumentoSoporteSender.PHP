<?php

namespace App\Helpers\FacturaElectronica;

class DocumentoSoporteSender extends AbstractFESender
{
	private $endpoint = '/support-document';

	public function getEndpoint(): string
	{
		return $this->endpoint;
	}

	public function getExtraParams(): array
	{
		return [
			'payment_form' => $this->paymentForm(),
			'seller' => $this->seller(),
			'invoice_lines' => $this->invoiceLines()
		];
	}

	public function getRelationShips(): array
	{
		return [
			'proveedor',
			'pagos.forma_pago',
			'resolucion',
			'detalles.cuenta_iva',
			'detalles.cuenta_retencion',
			'detalles.producto'
		];
	}

	protected function seller()
	{
		return [
			'identification_number' => $this->proveedor->numero_documento,
			'dv' => $this->proveedor->digito_verificacion,
			'name' => $this->proveedor->nombre_completo,
			'email' => $this->proveedor->email,
			'phone' => $this->proveedor->telefono_1 ? $this->proveedor->telefono_1 : $this->proveedor->telefono_2,
			'address' => $this->proveedor->direccion,
			'merchant_registration' => "f",
			'municipality_id' => "1",
			'type_document_identification_id' => $this->proveedor->id_tipo_documento,
			'postal_zone_code' => null,
		];
	}
}
