<style>

.dtrg-group {
    font-weight: bold;
    background-color: #f0f0f0;
    padding: 10px;
    text-transform: uppercase;
}

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('causar create')
                        <button type="button" class="btn btn-primary btn-sm" id="recalcularPeriodos">Re-calcular periodos</button>
                        <button type="button" class="btn btn-primary btn-sm" id="recalcularPeriodosLoading" style="opacity: 1; box-shadow: none; display: none;" disabled>
                            <b style="opacity: 0.3; text-transform: capitalize;">Re-calcular periodos</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -65px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
                        </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.capturas.causar_nomina.causar_nomina-table')

            </div>
        </div>

        @include('pages.capturas.causar_nomina.causar_nomina-detalle')

    </div>
    
</div>