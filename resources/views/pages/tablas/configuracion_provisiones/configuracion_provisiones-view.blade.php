<style>
    .error {
        color: red;
    }
    .edit-comprobante {
        width: 10px;
    }
    .drop-comprobante {
        width: 10px;
    }
    .fa-comprobante {
        margin-left: -5px;
    }

    /* .accordion-familia > .accordion-button {
        background-color: #1c4587 !important;
        color: white;
    } */

    .accordion-familia > .accordion-item:first-of-type .accordion-button {
        background-color: #1c4587 !important;
        color: white;
    }

    .accordion-familia > .accordion-item:first-of-type .accordion-button.collapsed {
        background-color: #FFF !important;
        color: black;
    }

    .accordion-familia > .accordion-item:last-of-type .accordion-button {
        background-color: #1c4587 !important;
        color: white;
    }

    .accordion-familia > .accordion-item:last-of-type .accordion-button.collapsed {
        background-color: #FFF !important;
        color: black;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.configuracion_provisiones.configuracion_provisiones-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.configuracion_provisiones.configuracion_provisiones-form')
    
</div>

<script>
    var editarConfiguracionProvisiones  = '<?php echo auth()->user()->can('configuracion_provisiones update'); ?>';
</script>