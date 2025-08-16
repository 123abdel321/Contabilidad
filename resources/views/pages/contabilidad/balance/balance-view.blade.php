<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                @include('pages.contabilidad.balance.balance-filter')

            </div>
        </div>

        <div class="mb-2" id="totales-balance-view" style="content-visibility: auto; overflow: auto; display: block;">
            <div class="row ">

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Saldo Anterior</p>
                            <div style="display: flex;">
                                <h5 id="balance_anterior" class="font-weight-bolder">
                                    0
                                </h5>
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
                                <h5 id="balance_debito" class="font-weight-bolder">
                                    0
                                </h5>
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
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Venta</p>
                            <div style="display: flex;">
                                <h5 id="balance_credito" class="font-weight-bolder">
                                    0
                                </h5>
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
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Venta</p>
                            <div style="display: flex;">
                                <h5 id="balance_diferencia" class="font-weight-bolder">
                                    0
                                </h5>
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
                @include('pages.contabilidad.balance.balance-table')
            </div>
        </div>
        
    </div>
</div>