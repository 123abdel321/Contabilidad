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

    .compra-load {
        margin-top: -23px;
        float: right;
        position: initial;
        margin-right: 15px;
    }

    #compraTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #compraTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                @include('pages.capturas.compra.compra-filter')

            </div>
        </div>

        <div class="card cardTotalCompra" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">SUB TOTAL</p>
                    <h6 id="compra_sub_total" style="margin-top: -15px; color: rgb(0, 2, 255);">$0</h6>
                </div>
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">IVA</p>
                    <h6 id="compra_total_iva" style="margin-top: -15px; color: rgb(0, 2, 255);">$0</h6>
                </div>
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">DESCUENTO</p>
                    <h6 id="compra_total_descuento" style="margin-top: -15px; color: rgb(0, 2, 255);">$0</h6>
                </div>
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p id="compra_texto_retencion" style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">RETENCIÓN</p>
                    <h6 id="compra_total_retencion" style="margin-top: -15px; color: rgb(0, 2, 255);">$0</h6>
                </div>
                <div class="col-12 col-md- col-sm">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">TOTAL</p>
                    <h6 id="compra_total_valor" style="margin-top: -15px; color: rgb(0, 2, 255);">$0</h6>
                </div>
            </div>
        </div>
        <div class="card mb-4" style="border-radius: 0px 0px 20px 20px;">
            <div class="card-body" id="card-compra" style="content-visibility: auto; overflow: auto;">
                @include('pages.capturas.compra.compra-table')
            </div>
        </div>
    </div>

    <script>
        var primeraBodegaCompra = JSON.parse('<?php echo $bodegas; ?>');
    </script>
    
</div>