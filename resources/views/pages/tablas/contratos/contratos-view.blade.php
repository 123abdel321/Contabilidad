<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('contratos create')
                        <button type="button" class="btn btn-primary btn-sm" id="createContratos">Agregar contrato <i class="fas fa-plus-circle"></i></button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputContratos" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.contratos.contratos-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.contratos.contratos-form')
    
</div>

<script>
    var editarContratos = @json(auth()->user()->can('contratos update'));
    var eliminarContratos = @json(auth()->user()->can('contratos delete'));
</script>