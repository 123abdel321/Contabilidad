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

    .nota_credito-load {
        margin-top: -23px;
        float: right;
        position: initial;
        margin-right: 15px;
    }

    #nota_creditoTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #nota_creditoTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .line-horizontal {
        width: 100%;
        height: 1px;
        border: 1px solid #e3e3e3;
        margin-top: 5px;
        margin-bottom: 10px;
    }

    .nota_credito_producto_load {
        position: absolute;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
        margin-left: 75% !important;
    }

    @media (min-width: 768px) {
        #tabla-captura-nota_credito.col-md-9 {
            flex: 0 0 auto;
            width: 74%;
        }
    }

    @media (min-width: 576px) {
        #totales-nota_credito-card.col-sm-5 {
            flex: 0 0 auto;
            width: 40.5%;
        }
    }

    @media (min-width: 768px) {
        #totales-nota_credito-card.col-md-12 {
            flex: 0 0 auto;
            width: 100% !important;
        }
    }

    .table-captura-nota_credito {
        max-height: 320px;
        overflow: auto;
    }

    .table-captura-nota_credito thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-nota_credito > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

</style>

<div class="nota_credito-capturas-view container-fluid py-2">

    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.capturas.nota_credito.nota_credito-filter')
            </div>
        </div>
    </div>

    <div class="row justify-content-between">

        <div id="tabla-captura-nota_credito" class="card mb-4 col-12 col-sm-12 col-md-9 ml-auto">
            <div id="card-nota_credito" class="card-body" style="content-visibility: auto; overflow: auto; border-radius: 20px;">

                @include('pages.capturas.nota_credito.nota_credito-table')
                <div style="padding: 8px;"></div>

            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-3 ml-auto">
            <div class="row justify-content-between">
                <div id="totales-nota_credito-card" class="card col-12 col-sm-5 col-md-12 ml-auto" style="height: min-content; margin-bottom: 0.5rem !important;">
                    <table class="table table-bordered table-captura-nota_credito" width="100%" style="margin-top: 12px;">
                        <tbody>
                            <tr id="totales_productos_nota_credito" style="display: none;">
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">PRODUCTOS: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="nota_credito_total_productos">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">SUB TOTAL: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="nota_credito_sub_total">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">IVA: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="nota_credito_total_iva">0.00</h6></td>
                            </tr>
                            <tr id="totales_descuento_nota_credito" style="display: none;">
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">DESCUENTO: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="nota_credito_total_descuento">0.00</h6></td>
                            </tr>
                            <tr id="totales_retencion_nota_credito" style="display: none;">
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;" id="nota_credito_texto_retencion">RETENCION: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="nota_credito_total_retencion">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-weight: bold;">TOTAL: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-weight: bold;" id="nota_credito_total_valor">0.00</h6></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="nota_creditoForm" class="card mb-4 col-12 col-sm-7 col-md-12 ml-auto">
                    <div style="min-height: 143px; overflow: auto;">
                        <table id="notaCreditoFormaPago" class="table table-bordered display responsive table-captura-nota_credito" width="100%">
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
                            <h6 id="total_pagado_nota_credito" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; font-size: 13px;">0,00</h6>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <h6 id="total_faltante_nota_credito_text" style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; font-size: 13px;">FALTANTE: </h6>
                        </div>
                        <div class="col-6" style="text-align: end;">
                            <h6 id="total_faltante_nota_credito" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; font-size: 13px;">0,00</h6>
                        </div>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>

    @include('pages.capturas.nota_credito.nota_credito-facturas')

    <script>
        var ivaIncluidoNotaCredito = '<?php echo $iva_incluido; ?>';
    </script>
    
</div>