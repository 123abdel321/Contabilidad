<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }

    .gasto_producto_load {
        position: absolute;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
        margin-left: 75% !important;
    }

    .combo-grid-nit {
        min-width: 200px !important;
    }

    .combo-grid {
        min-width: 200px !important;
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

    #gastoTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #gastoTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .table-captura-gasto {
        max-height: 320px;
        overflow: auto;
    }

    .gasto-load {
        margin-top: -21px;
        float: right;
        position: initial;
        margin-right: 15px;
    }

    .table-captura-gasto thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-gasto thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-gasto > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

    @media (min-width: 768px) {
        #tabla-captura-gasto.col-md-9 {
            flex: 0 0 auto;
            width: 74%;
        }
    }

    @media (min-width: 576px) {
        #totales-gasto-card.col-sm-5 {
            flex: 0 0 auto;
            width: 40.5%;
        }
    }

    @media (min-width: 768px) {
        #totales-gasto-card.col-md-12 {
            flex: 0 0 auto;
            width: 100% !important;
        }
    }

    .form-control.is-valid {
        background-position: left 0.5rem center !important;
    }

    .form-control.is-invalid {
        background-position: left 0.5rem center !important;
    }

    .table-captura-totales > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

</style>

<div class="container-fluid py-2">

    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

            @include('pages.capturas.gasto.gasto-filter')

            </div>
        </div>
    </div>

    <div class="row justify-content-between">

        <div id="tabla-captura-gasto" class="card mb-4 col-12 col-sm-12 col-md-9 ml-auto">
            <div id="card-gasto" class="card-body" style="content-visibility: auto; overflow: auto; border-radius: 20px;">

            @include('pages.capturas.gasto.gasto-table')
                <div style="padding: 8px;"></div>

            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-3 ml-auto card mb-4" style="padding-left: 0.2rem;padding-right: 0.2rem;">
            
            <div class="justify-content-between">
                
                <div class="mb-4 ml-auto">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-12" style="place-content: center;">
                            <table class="table table-bordered table-captura-totales" width="100%" style="margin-top: 9px;">
                                <tbody>
                                    <tr id="gasto_descuento_disp_view" style="display: none;">
                                        <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">DESCUENTO: </h6></td>
                                        <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="gasto_descuento">0.00</h6></td>
                                    </tr>
                                    <tr>
                                        <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500; ">SUB TOTAL: </h6></td>
                                        <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="gasto_sub_total">0.00</h6></td>
                                    </tr>
                                    <tr id="gasto_aiu_disp_view" style="display: none;">
                                        <td><h6 id="texto_gasto_aiu" style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">AIU: </h6></td>
                                        <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="gasto_aiu">0.00</h6></td>
                                    </tr>
                                    <tr id="gasto_iva_disp_view" style="display: none;">
                                        <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">IVA: </h6></td>
                                        <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="gasto_iva">0.00</h6></td>
                                    </tr>
                                    <tr id="gasto_retencion_disp_view" style="display: none;">
                                        <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">RETENCIÓN: </h6></td>
                                        <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="gasto_retencion">0.00</h6></td>
                                    </tr>
                                    <tr id="gasto_reteica_disp_view" style="display: none;">
                                        <td><h6 id="texto_gasto_reteica" style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">RETEICA: </h6></td>
                                        <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="gasto_reteica">0.00</h6></td>
                                    </tr>
                                    <tr>
                                        <td><h6 style="margin-bottom: 0px; font-weight: bold;">TOTAL: </h6></td>
                                        <td><h6 style="margin-bottom: 0px; float: right; font-weight: bold;" id="gasto_total">0.00</h6></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
    
                        <div class="col-12 col-sm-6 col-md-12">
                            <div style="overflow: auto;">
                                <table id="gastoFormaPago" class="table table-bordered display responsive table-captura-gasto" width="100%">
                                    <thead>
                                        <tr style="border: 0px !important;">
                                            <th style="border-radius: 15px 0px 0px 0px !important;">Pagos</th>
                                            <th style="border-radius: 0px 15px 0px 0px !important;">Total</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="row" id="gasto_faltante_view" style="display: none;">
                                <div class="col-6">
                                    <h6 id="total_faltante_gasto_text" style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; text-wrap: nowrap; color: #fd7e14;">FALTANTE: </h6>
                                </div>
                                <div class="col-6" style="text-align: end; text-wrap: nowrap;">
                                    <h6 id="total_faltante_gasto" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0.00</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>

    <script>
        var gastoIva = @json(auth()->user()->can("gasto iva"));
        var gastoAIU = @json(auth()->user()->can("gasto aiu"));
        var gastoDescuento = @json(auth()->user()->can("gasto descuento"));
        var comprobantesGastos = @json($comprobantes);
        var centrosCostosGastos = @json($centro_costos);
        var porcentajeIvaAIU = @json($porcentaje_iva_aiu);
    </script>
    
</div>