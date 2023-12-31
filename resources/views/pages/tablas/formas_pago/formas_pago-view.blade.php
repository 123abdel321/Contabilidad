<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('formas_pago create')
                        <button type="button" class="btn btn-primary btn-sm" id="createFormaPago">Agregar forma de pago</button>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputFormaPago" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.formas_pago.formas_pago-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.formas_pago.formas_pago-form')
    
</div>

<script>
    var editarFormaPago = '<?php echo auth()->user()->can('formas_pago update'); ?>';
    var eliminarFormaPago = '<?php echo auth()->user()->can('formas_pago delete'); ?>';
</script>