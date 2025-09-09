<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('resoluciones create')
                        <span id="createResoluciones" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px;">
                            <i class="fa-solid fa-folder-plus" style="font-size: 17px;"></i>&nbsp;
                            <b style="vertical-align: text-top;">CREAR RESOLUCIÓN</b>
                        </span>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputResolucion" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.resoluciones.resoluciones-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.resoluciones.resoluciones-form')
    
</div>

<script>
    var editarResoluciones = '<?php echo auth()->user()->can('comprobantes update'); ?>';
    var eliminarResoluciones = '<?php echo auth()->user()->can('comprobantes delete'); ?>';
</script>