
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<table>
		<thead>
		<tr>
            <th>Factura</th>
            <th>Cliente</th>
            <th>Bodega</th>
            <th>Fecha</th>
            <th>Código</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Costo</th>
            <th>Subtotal</th>
            <th>Iva</th>
            <th>Iva</th>
            <th>Descuento</th>
            <th>Descuento</th>
            <th>Total</th>
            <th>Vendedor</th>
		</tr>
		</thead>
		<tbody>
		@foreach($documentos as $documento)
			<tr>
                @if($documento->nivel)
                    @include('excel.ventas_acumuladas.celdas', ['style' => 'background-color: black; font-weight: bold; color: white;', 'documento' => $documento])
                @else
                    @include('excel.ventas_acumuladas.celdas', ['style' => 'background-color: #FFF;', 'documento' => $documento])
                @endif
			</tr>
		@endforeach
		</tbody>
	</table>
</html>