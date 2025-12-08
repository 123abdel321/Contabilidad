<div class="container-fluid py-2">
    <div class="row">
        
        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.capturas.pagos.pagos-table')

            </div>
        </div>

        @include('pages.capturas.pagos.pagos-form')
        @include('pages.capturas.pagos.pagos-detalle')

    </div>
</div>

<script>
    var crearPago = @json(auth()->user()->can('parqueadero update'));
</script>