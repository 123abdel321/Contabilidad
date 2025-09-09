<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4">
                @can('concepto_gastos create')
                    <span id="createConceptoGasto" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px;">
                        <i class="fa-solid fa-folder-plus" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CREAR CONCEPTO DE GASTO</b>
                    </span>
                @endcan
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInputConceptoGastos" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.concepto_gastos.concepto_gastos-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.concepto_gastos.concepto_gastos-form')
    
</div>

<script>
    var crearConceptoGastos = '<?php echo auth()->user()->can('concepto_gastos create'); ?>';
    var editarConceptoGastos = '<?php echo auth()->user()->can('concepto_gastos update'); ?>';
    var eliminarConceptoGastos = '<?php echo auth()->user()->can('concepto_gastos delete'); ?>';
</script>