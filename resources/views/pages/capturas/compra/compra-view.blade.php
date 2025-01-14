<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }

    .combo-grid-nit {
        min-width: 200px !important;
    }

    .combo-grid {
        min-width: 230px !important;
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

    .table-captura-compras {
        max-height: 320px;
        overflow: auto;
    }

    .table-captura-compras thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-compras > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

    @media (min-width: 768px) {
        #tabla-captura-compras.col-md-9 {
            flex: 0 0 auto;
            width: 74%;
        }
    }

    @media (min-width: 576px) {
        #totales-compra-card.col-sm-5 {
            flex: 0 0 auto;
            width: 40.5%;
        }
    }

    @media (min-width: 768px) {
        #totales-compra-card.col-md-12 {
            flex: 0 0 auto;
            width: 100% !important;
        }
    }

    .table-captura-compras thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-compras > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

    .handsontable .htDimmed {
        color: black !important;
    }

    .handsontable div, .handsontable input, .handsontable table, .handsontable tbody, .handsontable textarea, .handsontable thead {
        box-sizing: border-box !important;
    }

    .htDropdownMenu {
        max-height: 200px;
        overflow-y: auto;
    }

</style>

<div class="container-fluid py-2">

    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

            @include('pages.capturas.compra.compra-filter')

            </div>
        </div>
    </div>

    <div class="row justify-content-between">

        <div id="tabla-captura-compras" class="card mb-4 col-12 col-sm-12 col-md-9 ml-auto">
            <div id="card-compra" class="card-body" style="content-visibility: auto; overflow: auto; border-radius: 20px;">

                <div id="compraTable" class="hot ht-theme-horizon disable-auto-theme" style="margin-bottom: 5px;"></div>
                <div style="padding: 8px;"></div>

            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-3 ml-auto">
            
            <div class="row justify-content-between">
                <div id="totales-compra-card" class="card col-12 col-sm-5 col-md-12 ml-auto" style="height: min-content; margin-bottom: 0.5rem !important;">
                    <table class="table table-bordered table-captura-compras" width="100%" style="margin-top: 12px;">
                        <tbody>
                            <tr>
                                <td><h6 style="font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;">SUB TOTAL: </h6></td>
                                <td><h6 style="float: right; font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;" id="compra_sub_total">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;">IVA: </h6></td>
                                <td><h6 style="float: right; font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;" id="compra_total_iva">0.00</h6></td>
                            </tr>
                            <tr id="totales_descuento_compra" style="display: none;">
                                <td><h6 style="font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;">DESCUENTO: </h6></td>
                                <td><h6 style="float: right; font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;" id="compra_total_descuento">0.00</h6></td>
                            </tr>
                            <tr id="totales_retencion_compra" style="display: none; ">
                                <td><h6 style="font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;" id="compra_texto_retencion">RETENCION: </h6></td>
                                <td><h6 style="float: right; font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;" id="compra_total_retencion">0.00</h6></td>
                            </tr>
                            <tr>
                            <td><h6 style="margin-bottom: 0px; font-weight: bold;">TOTAL: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-weight: bold;" id="compra_total_valor">0.00</h6></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="compraForm" class="card mb-4 col-12 col-sm-7 col-md-12 ml-auto">
                    <div style="overflow: auto;">
                        <table id="compraFormaPago" class="table table-bordered display responsive table-captura-compras" width="100%">
                            <thead>
                                <tr style="border: 0px !important;">
                                    <th style="border-radius: 15px 0px 0px 0px !important;">Pagos</th>
                                    <th style="border-radius: 0px 15px 0px 0px !important;">Total</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <h6 style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; font-size: 13px;">PAGADO: </h6>
                        </div>
                        <div class="col-6" style="text-align: end;">
                            <h6 id="total_pagado_compra" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; font-size: 13px;">0,00</h6>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <h6 id="total_faltante_compra_text" style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; font-size: 13px;">FALTANTE: </h6>
                        </div>
                        <div class="col-6" style="text-align: end;">
                            <h6 id="total_faltante_compra" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; font-size: 13px;">0,00</h6>
                        </div>
                    </div>

                    <div id="cambio-totals" class="row" style="display: none;">
                        <div class="col-6">
                            <h6 style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; color: blue;">CAMBIO: </h6>
                        </div>
                        <div class="col-6" style="text-align: end;">
                            <h6 id="total_cambio_compra" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; color: blue;">0,00</h6>
                        </div>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>

    <script>
        var primeraBodegaCompra = JSON.parse('<?php echo $bodegas; ?>');
        var primerComprobanteCompra = JSON.parse('<?php echo $comprobante; ?>');
        var agregarDescuentoCompra = '<?php echo auth()->user()->can("compra descuento"); ?>';
        var ventaExistenciasCompra = '<?php echo auth()->user()->can("compra existencia"); ?>';
    </script>
    
</div>