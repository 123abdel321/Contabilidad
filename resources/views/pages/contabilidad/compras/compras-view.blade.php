<style>
    .error { color: red; }
    .column-number { text-align: -webkit-right; }

    /* Fila factura (resumen) — azul claro propio de compras */
    #ComprasInformeTable tbody tr.compra-factura td {
        background-color: rgb(51, 132, 158) !important;
        color: #fff !important;
        font-weight: 600;
        padding-top: 0px;
        padding-bottom: 0px;
    }

    /* Fila detalle (producto) */
    #ComprasInformeTable tbody tr.compra-detalle td {
        background-color: #f8f9fa;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-2">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.compras.compras-filter')
            </div>
        </div>

        <div class="mb-2" id="totales-compras-view" style="content-visibility: auto; overflow: auto; display: block;">
            <div class="row">

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Productos comprados</p>
                            <div style="display: flex;">
                                <h5 id="total_productos_comprados" class="font-weight-bolder">0</h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-success shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-box-2 text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Costo</p>
                            <div style="display: flex;">
                                <h5 class="font-weight-bolder">$&nbsp;</h5>
                                <h5 id="total_costo_compras" class="font-weight-bolder">0</h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-warning shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-money-coins text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total IVA</p>
                            <div style="display: flex;">
                                <h5 class="font-weight-bolder">$&nbsp;</h5>
                                <h5 id="total_iva_compras" class="font-weight-bolder">0</h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-danger shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-money-coins text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Compras</p>
                            <div style="display: flex;">
                                <h5 class="font-weight-bolder">$&nbsp;</h5>
                                <h5 id="total_precio_compras" class="font-weight-bolder">0</h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-briefcase-24 text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.compras.compras-table')
            </div>
        </div>
    </div>
</div>

<script>
    var eliminarCompras = @json(auth()->user()->can('compras delete'));
</script>