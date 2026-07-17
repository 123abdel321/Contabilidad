<!-- A --><td style="{{ $style }}">
    @if ($agrupado == 'id_cuenta')
        @if($documento->nivel == 1)
            {{ $documento->cuenta }}
        @else
            {{ $documento->numero_documento }}
        @endif
    @elseif ($agrupado == 'id_nit')
        @if($documento->nivel == 1)
            {{ $documento->numero_documento }}
        @else
            {{ $documento->cuenta }}
        @endif
    @endif
</td>
<!-- B --><td style="{{ $style }}">
    @if ($agrupado == 'id_cuenta')
        @if($documento->nivel == 1)
            {{ $documento->nombre_cuenta }}
        @else
            {{ $documento->nombre_nit }}
        @endif
    @elseif ($agrupado == 'id_nit')
        @if($documento->nivel == 1)
            {{ $documento->nombre_nit }}
        @else
            {{ $documento->nombre_cuenta }}
        @endif
    @endif
</td>
@if($tipo_informe == 'reteica')
    @if($documento->nit && $documento->nit->actividad_economica)
    <!-- C --><td style="{{ $style }}">{{ $documento->nit->actividad_economica->nombre }}</td>
    @else
    <!-- C --><td style="{{ $style }}"></td>
    @endif
@endif
<!-- E --><td style="{{ $style }} font-weight: bold; text-align: right;">{{ number_format($documento->debito) }}</td>
<!-- F --><td style="{{ $style }} font-weight: bold; text-align: right;">{{ number_format($documento->credito) }}</td>
<!-- G --><td style="{{ $style }} font-weight: bold; text-align: right;">{{ number_format($documento->valor_base) }}</td>
<!-- H --><td style="{{ $style }} font-weight: bold; text-align: right;">{{ number_format($documento->porcentaje_base) }}</td>
@if($nivel == 3)
<!-- J --><td style="{{ $style }}">{{ $documento->fecha_manual }}</td>
<!-- K --><td style="{{ $style }}">{{ $documento->consecutivo }}</td>
<!-- L --><td style="{{ $style }}">{{ $documento->codigo_comprobante }} - {{ $documento->nombre_comprobante }}</td>
<!-- M --><td style="{{ $style }}">{{ $documento->concepto }}</td>
@endif