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
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4">
                @can('familias create')
                    <button type="button" class="btn btn-primary btn-sm" id="createFamilia">Agregar familias</button>
                @endcan
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInput" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.familias.familias-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.familias.familias-form')
    
</div>

<script>
    var crearFamilias = '<?php echo auth()->user()->can('familias create'); ?>';
    var editarFamilias = '<?php echo auth()->user()->can('familias update'); ?>';
    var eliminarFamilias = '<?php echo auth()->user()->can('familias delete'); ?>';
</script>