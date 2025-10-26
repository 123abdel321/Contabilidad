<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-2">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.capturas.cambio_datos.cambio_datos-filter')
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.capturas.cambio_datos.cambio_datos-table')
            </div>
        </div>
    </div>
</div>

@include('pages.capturas.cambio_datos.cambio_datos-form')

<script>
    var editarCambioDatos = @json(auth()->user()->can('cambio_datos update'));
    var ubicacion_maximoph_cambio_datos = @json($ubicacion_maximoph);
</script>