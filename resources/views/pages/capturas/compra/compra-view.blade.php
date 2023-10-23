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

        <!-- <div class="card cardTotalCompra" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">SUB TOTAL</p>
                    <h6 id="compra_sub_total" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">IVA</p>
                    <h6 id="compra_total_iva" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">DESCUENTO</p>
                    <h6 id="compra_total_descuento" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>  
                <div class="col-6 col-md col-sm" style="border-right: solid 1px #787878;">
                    <p id="compra_texto_retencion" style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">RETENCIÓN</p>
                    <h6 id="compra_total_retencion" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>
                <div class="col-12 col-md- col-sm">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">TOTAL</p>
                    <h6 id="compra_total_valor" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>
            </div>
        </div> -->
        <div id="card-compra" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 20px;">
            @include('pages.capturas.compra.compra-table')
            <div style="padding: 5px;"></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="row card-body" style="content-visibility: auto; text-align: -webkit-center; margin-top: 5px;">
            <div class="col-12 col-sm-6 col-md-6">
            </div>
            <div class="col-12 col-sm-6 col-md-6">
                <table class="table table-bordered" width="100%">
                    <tbody>
                        <tr>
                            <td><h6 style="font-size: 0.9rem;">SUB TOTAL: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="compra_sub_total">0.00</h6></td>
                        </tr>
                        <tr>
                            <td><h6 style="font-size: 0.9rem;">IVA: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="compra_total_iva">0.00</h6></td>
                        </tr>
                        <tr id="totales_descuento_compra" style="display: none;">
                            <td><h6 style="font-size: 0.9rem;">DESCUENTO: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="compra_total_descuento">0.00</h6></td>
                        </tr>
                        <tr id="totales_retencion_compra" style="display: none;">
                            <td><h6 style="font-size: 0.9rem;" id="compra_texto_retencion">RETENCION: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="compra_total_retencion">0.00</h6></td>
                        </tr>
                        <tr>
                            <td><h6 style="font-weight: bold;">TOTAL: </h6></td>
                            <td><h6 style="float: right; font-weight: bold;" id="compra_total_valor">0.00</h6></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        var primeraBodegaCompra = JSON.parse('<?php echo $bodegas; ?>');
    </script>
    
</div>