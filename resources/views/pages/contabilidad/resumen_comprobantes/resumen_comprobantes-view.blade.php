<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.resumen_comprobantes.resumen_comprobantes-filter')
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.resumen_comprobantes.resumen_comprobantes-table')
            </div>
        </div>
    </div>

    <script>
        
        var ubicacion_maximoph_resumen = JSON.parse('<?php echo $ubicacion_maximoph; ?>');

    </script>
    
</div>