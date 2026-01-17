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
        // Usar $this->iva_inluido que ya está disponible desde el constructor
        
        if ($this->iva_inluido) {
            // Cuando el precio INCLUYE IVA
            // Fórmula: subtotal_sin_iva = total_factura - total_iva
            $subtotal_sin_iva = $this->factura->total_factura - $this->factura->total_iva;
        } else {
            // Cuando el precio NO incluye IVA
            // Asumir que subtotal ya es sin IVA
            $subtotal_sin_iva = $this->factura->subtotal;
        }
        
        // Asegurar redondeo correcto
        $subtotal_sin_iva = round($subtotal_sin_iva, 2);
        
        return [
            'line_extension_amount' => number_format($subtotal_sin_iva, 2, '.', ''),
            'tax_exclusive_amount' => number_format($subtotal_sin_iva, 2, '.', ''),
            'tax_inclusive_amount' => number_format($this->factura->total_factura, 2, '.', ''),
            'allowance_total_amount' => number_format($this->factura->total_descuento ?? 0, 2, '.', ''),
            'charge_total_amount' => "0.00",
            'payable_amount' => number_format($this->factura->total_factura, 2, '.', ''),
        ];
    }

}
