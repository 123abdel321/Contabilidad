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
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('novedades_generales create')
                        <button type="button" class="btn btn-primary btn-sm" id="createNovedadGeneral">Agregar novedad general <i class="fas fa-plus-circle"></i></button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.capturas.novedades_generales.novedades_generales-table')

            </div>
        </div>
    </div>

    @include('pages.capturas.novedades_generales.novedades_generales-form')
    
</div>

<script>
    var editarNovedadesGenerales  = @json(auth()->user()->can('novedades_generales update'));
    var eliminarNovedadesGenerales  = @json(auth()->user()->can('novedades_generales delete'));
</script>