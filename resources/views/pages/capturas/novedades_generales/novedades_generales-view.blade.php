<style>
    .error { color: red; }

    /* Tarjeta resumen en el modal */
    .novedad-summary {
        background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
        border: 1px solid #d0d9ff;
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 12px;
    }
    .novedad-summary .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 13px;
    }
    .novedad-summary .summary-item strong { color: #1c4587; }

    /* Inputs mejorados */
    .novedades-form .form-control,
    .novedades-form .form-select {
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
    .novedades-form .form-control:focus,
    .novedades-form .form-select:focus {
        border-color: #7ea1ff;
        box-shadow: 0 0 0 0.2rem rgba(126, 161, 255, .25);
    }

    /* Badge de hora calculada */
    .hora-calculada {
        font-size: 11px;
        background: #e7f1ff;
        color: #0d6efd;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
    }

    /* Atajos de teclado hint */
    .kbd-hint {
        font-size: 10px;
        color: #6c757d;
        margin-left: 6px;
    }
    .kbd-hint kbd {
        background: #e9ecef;
        border-radius: 3px;
        padding: 1px 5px;
        font-size: 10px;
        color: #495057;
    }

    /* Quick date buttons */
    .quick-dates .btn {
        font-size: 11px;
        padding: 2px 8px;
        margin-right: 4px;
    }

    .input-group-sm .btn { padding: .25rem .5rem; }

    /* Fila de resumen de horas */
    .horas-resumen {
        font-size: 12px;
        color: #198754;
        font-weight: 600;
        display: none;
    }

    .accordion-familia > .accordion-item:first-of-type .accordion-button { background-color: #1c4587 !important; color: white; }
    .accordion-familia > .accordion-item:first-of-type .accordion-button.collapsed { background-color: #FFF !important; color: black; }
    .accordion-familia > .accordion-item:last-of-type .accordion-button { background-color: #1c4587 !important; color: white; }
    .accordion-familia > .accordion-item:last-of-type .accordion-button.collapsed { background-color: #FFF !important; color: black; }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('novedades_generales create')
                        <button type="button" class="btn btn-primary btn-sm" id="createNovedadGeneral">
                            Agregar novedad general <i class="fas fa-plus-circle"></i>
                        </button>
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
    var editarNovedadesGenerales = @json(auth()->user()->can('novedades_generales update'));
    var eliminarNovedadesGenerales = @json(auth()->user()->can('novedades_generales delete'));
</script>