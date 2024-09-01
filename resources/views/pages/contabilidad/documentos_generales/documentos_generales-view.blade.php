
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
                @include('pages.contabilidad.documentos_generales.documentos_generales-filter')
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.documentos_generales.documentos_generales-table')
            </div>
        </div>
    </div>

    <script>
        
        var ubicacion_maximoph_documentos_generales = JSON.parse('<?php echo $ubicacion_maximoph; ?>');

    </script>

</div>
