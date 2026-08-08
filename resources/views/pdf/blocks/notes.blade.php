@if(isset($monto_letras))
    <div class="son-texto"><b>SON:</b> {{ $monto_letras }}</div>
@endif
@if(!empty($observacion))
    <div class="son-texto"><b>OBSERVACIÓN:</b> {!! $observacion !!}</div>
@endif