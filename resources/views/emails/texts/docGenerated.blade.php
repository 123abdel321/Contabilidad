<p>
	{{ $empresa->razon_social }} le ha generado el documento electrónico <b>{{ $factura->documento_referencia_fe }}</b> por un valor de
	<b>COP {{ number_format($factura->total_factura, 0) }}</b>.
	adjuntos se encuentran la representación gráfica y el xml correspondiente.
</p>
