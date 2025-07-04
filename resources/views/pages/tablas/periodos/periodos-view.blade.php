<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('periodos create')
                        <button type="button" class="btn btn-primary btn-sm" id="createPeriodos">Agregar <i class="fas fa-plus-circle"></i></button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputPeriodos" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.periodos.periodos-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.periodos.periodos-form')
    
</div>

<script>
    var editarPeriodos = @json(auth()->user()->can('periodos update'));
    var eliminarPeriodos = @json(auth()->user()->can('periodos delete'));
</script>