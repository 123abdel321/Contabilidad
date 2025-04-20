<?php

namespace App\Http\Controllers\Traits;

trait BegFacturacionElectronica
{
	public function setFeFields($factura, $cufe, $nitEmpresa)
	{
		$factura->fe_fecha_validacion = date('Y-m-d');
		$factura->fe_codigo_identificador = $cufe;
		$factura->fe_codigo_qr = $this->buildFeCodigoQr($factura, $nitEmpresa);
		return $factura;
	}

	public function buildFeCodigoQr($factura, $nitEmpresa)
	{
		$factura->loadMissing(['resolucion', 'cliente']);
		$currentHour = date('H:i:s-05:00');
		$valorFactura = $factura->total_factura - $factura->total_iva;
		$valorOtrosImpuestos = $factura->total_rete_fuente;

		return "NumFac=$factura->documento_referencia_fe
				FecFac=$factura->fe_fecha_validacion
				HorFac=$currentHour
				NitFac=$nitEmpresa
				DocAdq={$factura->cliente->numero_documento}
				ValFac=$valorFactura
				ValIva=$factura->total_iva
				ValOtroIm=$valorOtrosImpuestos
				ValTolFac=$factura->total_factura
				CUFE=$factura->fe_codigo_identificador
				QRCode=https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=" . $factura->fe_codigo_identificador;
	}
}
