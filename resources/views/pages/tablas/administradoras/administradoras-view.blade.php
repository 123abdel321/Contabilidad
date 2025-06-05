<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('administradoras create')
                        <button type="button" class="btn btn-primary btn-sm" id="createAdministradoras">Agregar <i class="fas fa-plus-circle"></i></button>
                        <button type="button" class="btn btn-dark btn-sm" id="sincronizarAdministradoras">Sincronizar <i class="fas fa-sync"></i></button>
                        <button type="button" class="btn btn-dark btn-sm disabled" id="sincronizarAdministradorasLoading" style="display: none;">Sincronizando <i class="fa fa-refresh fa-spin"></i></button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputAdministradoras" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.administradoras.administradoras-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.administradoras.administradoras-form')
    
</div>

<script>
    var editarAdministradoras = @json(auth()->user()->can('administradoras update'));
    var eliminarAdministradoras = @json(auth()->user()->can('administradoras delete'));
</script>