<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }

    .combo-grid-nit {
        width: 300px !important;
    }

    .combo-grid {
        width: 300px !important;
    }

    .drop-row-grid {
        margin-bottom: 0rem !important;
        font-size: 12px;
        margin-top: 4px;
        border-radius: 50px;
        width: 26px;
    }

    .fa-trash-alt {
        margin-left: -3px;
        margin-top: 1px;
    }
    #documentoReferenciaTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #documentoReferenciaTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .btn-group {
        box-shadow: 0 0px 0px rgba(50, 50, 93, 0.1), 0 0px 0px rgba(0, 0, 0, 0.08);
    }

    .normal_input {
        border-radius: 9px !important;
    }

    .documento-load {
        position: absolute;
        margin-left: 160px !important;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
    }

    .info-factura {
        margin-top: -16px;
        z-index: 999;
        margin-left: 80px !important;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                @include('pages.capturas.documento_general.documento_general-filter')

            </div>
        </div>

        <div class="card cardTotal" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-4 col-md-4 col-sm-4" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px;">DEBITO</p>
                    <h6 id="general_debito" style="margin-top: -15px;">0.00</h6>
                </div>
                <div class="col-4 col-md-4 col-sm-4" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px;">CREDITO</p>
                    <h6 id="general_credito" style="margin-top: -15px;">0.00</h6>
                </div>
                <div class="col-4 col-md-4 col-sm-4">
                    <p style="font-size: 13px; margin-top: 5px;">DIFERENCIA</p>
                    <h6 id="general_diferencia" style="margin-top: -15px;">0.00</h6>
                </div>
            </div>
        </div>
        <div id="card-documento-general" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
            @include('pages.capturas.documento_general.documento_general-table')
        </div>
        
        @include('pages.capturas.documento_general.documento_general-form')
        @include('pages.capturas.documento_general.documento_general-extracto')

    </div>
</div>