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
        $subtotal_sin_iva = $this->factura->subtotal;
		$line_extension_amount = $this->factura->total_iva + $this->factura->total_rete_fuente + $this->factura->subtotal;
        
        return [
			'line_extension_amount' => number_format($line_extension_amount, 2, '.', ''), // Suma de los valores de todas las líneas SIN impuestos (base gravable)
			'tax_exclusive_amount' => number_format($this->factura->subtotal, 2, '.', ''), // Total sin impuestos (igual al subtotal cuando no hay otros impuestos)
			'tax_inclusive_amount' => number_format($this->factura->total_factura, 2, '.', ''), // Total CON impuestos incluidos (subtotal + IVA + otros impuestos)
			'allowance_total_amount' => number_format($this->factura->total_descuento ?? 0, 2, '.', ''), // Total de descuentos aplicados a la factura
			'charge_total_amount' => "0.00", // Total de cargos adicionales (fletes, recargos, etc.)
			'payable_amount' => number_format($this->factura->total_factura, 2, '.', ''), // Valor final a pagar por el cliente (normalmente igual a tax_inclusive_amount)
		];
    }

}
