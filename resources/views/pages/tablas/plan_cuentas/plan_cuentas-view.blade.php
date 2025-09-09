<style>
    .error {
        color: red;
    }
    .edit-comprobante {
        width: 10px;
    }
    .drop-comprobante {
        width: 10px;
    }
    .fa-comprobante {
        margin-left: -5px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4">
                @can('plan_cuentas create')
                    <span id="createPlanCuenta" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px;">
                        <i class="fa-solid fa-folder-plus" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CREAR CUENTA CONTABLE</b>
                    </span>
                @endcan()
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInputCuenta" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.plan_cuentas.plan_cuentas-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.plan_cuentas.plan_cuentas-form', ['tipoCuenta' => $tipoCuenta])
    
</div>

<script>
    var editarPlanCuenta = '<?php echo auth()->user()->can('plan_cuentas update'); ?>';
    var eliminarPlanCuenta = '<?php echo auth()->user()->can('plan_cuentas delete'); ?>';
</script>