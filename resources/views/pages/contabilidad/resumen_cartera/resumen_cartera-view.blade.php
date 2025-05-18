
<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.resumen_cartera.resumen_cartera-filter', ['ubicacion_maximoph' => $ubicacion_maximoph])
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.resumen_cartera.resumen_cartera-table')
            </div>
        </div>
    </div>

    <script>
        
        var ubicacion_maximoph_resumen_cartera = JSON.parse('<?php echo $ubicacion_maximoph; ?>');

    </script>

</div>
