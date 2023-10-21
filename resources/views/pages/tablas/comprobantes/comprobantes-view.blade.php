<style>
    .error {
        color: red;
    }
    .fa-comprobante {
        margin-left: -5px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-4 col-sm-4">
                    @can('comprobantes create')
                        <button type="button" class="btn btn-primary btn-sm" id="createComprobante">Agregar comprobante</button>
                    @endcan
                </div>
                <div class="col-12 col-md-8 col-sm-8">
                    <input type="text" id="searchInput" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.comprobantes.comprobantes-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.comprobantes.comprobantes-form')
    
</div>

<script>
    var editarComprobante = '<?php echo auth()->user()->can('comprobantes update'); ?>';
    var eliminarComprobante = '<?php echo auth()->user()->can('comprobantes delete'); ?>';
</script>