<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
    .content-export-btn {
        padding: 10px;
        margin-top: -20px;
    }
    .button-export-excel {
        width: 40px;
        background-color: #006d37;
        padding: 5px;
        height: 30px;
        text-align-last: center;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        float: right;
    }
    
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9; margin-top: 7px;">
            <div class="col-12 col-md-8 col-sm-8">

                <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadExtracto">
                    <i id="reloadExtractosIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                    <i id="reloadExtractosIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                </button>
            </div>
        </div>

        <div style="content-visibility: auto; overflow: auto; display: block; margin-top: -5px;">
            <div class="row ">
                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Saldo Anterior</p>
                            <div style="display: flex;">
                                <h5 id="saldo_anterior_extracto" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="fas fa-coins text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Debito</p>
                            <div style="display: flex;">
                                <h5 id="debito_extracto" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-success shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="fas fa-coins text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Credito</p>
                            <div style="display: flex;">
                                <h5 id="credito_extracto" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-warning shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="fas fa-coins text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card" style="height: 100%;">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Saldo final</p>
                            <div style="display: flex;">
                                <h5 id="saldo_final_extracto" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-danger shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="fas fa-coins text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
    
                @include('pages.contabilidad.extracto.extracto-table')
    
            </div>
        </div>
    </div>

    <script>
        var ubicacion_maximoph = JSON.parse('<?php echo $ubicacion_maximoph; ?>');
    </script>
</div>