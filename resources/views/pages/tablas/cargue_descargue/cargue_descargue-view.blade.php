<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('cargue_descargue create')
                        <button type="button" class="btn btn-primary btn-sm" id="createCargueDescargue">Agregar Carge / Descargue</button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputCargueDescargue" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.cargue_descargue.cargue_descargue-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.cargue_descargue.cargue_descargue-form')
    
</div>

<script>
    var editarCargueDescargue = '<?php echo auth()->user()->can('cargue_descargue update'); ?>';
    var eliminarCargueDescargue = '<?php echo auth()->user()->can('cargue_descargue delete'); ?>';
</script>