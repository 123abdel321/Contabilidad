<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-2">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.movimiento_producto.movimiento_producto-filter')
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.movimiento_producto.movimiento_producto-table')
            </div>
        </div>

    </div>
</div>

<script>
</script>