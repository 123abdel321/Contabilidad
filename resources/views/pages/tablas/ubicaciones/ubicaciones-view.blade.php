<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('ubicaciones create')
                        <button type="button" class="btn btn-primary btn-sm" id="createUbicaciones">Agregar ubicaciones</button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputUbicaciones" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.ubicaciones.ubicaciones-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.ubicaciones.ubicaciones-form')
    
</div>

<script>
    var editarUbicacion = JSON.parse('<?php echo auth()->user()->can('ubicaciones update'); ?>');
    var eliminarUbicacion = JSON.parse('<?php echo auth()->user()->can('ubicaciones delete'); ?>');
</script>