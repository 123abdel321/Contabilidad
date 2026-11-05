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
			'pagos.forma_pago.cuenta.tipos_cuenta',
			'resolucion',
			'detalles.cuenta_iva',
			'detalles.cuenta_retencion',
			'detalles.producto'
		];
	}

	protected function customer()
	{
		// Obtener el tipo de documento desde la relación o por código
		// Ajusta según cómo tengas la relación en tu modelo Cliente
		$tipoDocumento = $this->cliente->tipo_documento; // o el nombre de tu relación
		$codigoTipoDoc = $tipoDocumento->codigo ?? null;

		// Determinar si es persona natural o jurídica
		// Códigos DIAN: 11,12,13,21,22,41,42,43,91,99 → Persona Natural
		// 31, 50 → Persona Jurídica (NIT)
		$codigosPersonaNatural = ['11', '12', '13', '21', '22', '41', '42', '43', '91', '99'];
		
		if (in_array($codigoTipoDoc, $codigosPersonaNatural)) {
			$typeOrganizationId = 2; // Persona Natural
		} elseif (in_array($codigoTipoDoc, ['31', '50'])) {
			$typeOrganizationId = 1; // Persona Jurídica
		} else {
			// Fallback usando el campo tipo_contribuyente de la tabla nits
			// 1 = Persona jurídica, 2 = Persona natural
			$typeOrganizationId = $this->cliente->tipo_contribuyente == 1 ? 1 : 2;
		}

		return [
			'identification_number' => $this->cliente->numero_documento,
			'name' => $this->cliente->nombre_completo,
			'email' => $this->cliente->email,
			'phone' => $this->cliente->telefono_1 ?: $this->cliente->telefono_2,
			'address' => $this->cliente->direccion,
			'merchant_registration' => "0000000-00",
			'type_document_identification_id' => $tipoDocumento->id ?? null,
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
