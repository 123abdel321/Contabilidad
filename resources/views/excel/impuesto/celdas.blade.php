<!-- A --><td style="{{ $style }}">{{ $documento->cuenta }}</td>
<!-- B --><td style="{{ $style }}">{{ $documento->nombre_cuenta }}</td>
@if($tipo_informe == 'reteica')
    @if($documento->nit && $documento->nit->actividad_economica)
    <!-- C --><td style="{{ $style }}">{{ $documento->nit->actividad_economica->nombre }}</td>
    @else
    <!-- C --><td style="{{ $style }}"></td>
    @endif
@endif
<!-- E --><td style="{{ $style }} font-weight: bold;">{{ $documento->debito }}</td>
<!-- F --><td style="{{ $style }} font-weight: bold;">{{ $documento->credito }}</td>
<!-- G --><td style="{{ $style }} font-weight: bold;">{{ $documento->valor_base }}</td>
<!-- H --><td style="{{ $style }} font-weight: bold;">{{ $documento->porcentaje_base }}</td>
@if($nivel == 3)
<!-- J --><td style="{{ $style }}">{{ $documento->fecha_manual }}</td>
<!-- K --><td style="{{ $style }}">{{ $documento->consecutivo }}</td>
<!-- L --><td style="{{ $style }}">{{ $documento->codigo_comprobante }} - {{ $documento->nombre_comprobante }}</td>
<!-- M --><td style="{{ $style }}">{{ $documento->concepto }}</td>
@endif