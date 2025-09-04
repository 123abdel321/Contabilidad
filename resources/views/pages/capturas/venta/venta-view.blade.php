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

    .venta-load {
        margin-top: -23px;
        float: right;
        position: initial;
        margin-right: 15px;
    }

    #ventaTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #ventaTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .line-horizontal {
        width: 100%;
        height: 1px;
        border: 1px solid #e3e3e3;
        margin-top: 5px;
        margin-bottom: 10px;
    }

    .venta_producto_load {
        position: absolute;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
        margin-left: 75% !important;
    }

    @media (min-width: 768px) {
        #tabla-captura-ventas.col-md-9 {
            flex: 0 0 auto;
            width: 74%;
        }
    }

    @media (min-width: 576px) {
        #totales-venta-card.col-sm-5 {
            flex: 0 0 auto;
            width: 40.5%;
        }
    }

    @media (min-width: 768px) {
        #totales-venta-card.col-md-12 {
            flex: 0 0 auto;
            width: 100% !important;
        }
    }

    .table-captura-ventas {
        max-height: 320px;
        overflow: auto;
    }

    .table-captura-ventas thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-ventas > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

    #ventaFilterForm .select2-container--bootstrap-5 {
        height: 30px;
    }

</style>

<div class="ventas-capturas-view container-fluid py-2">

    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                @include('pages.capturas.venta.venta-filter', ['vendedores_ventas' => $vendedores_ventas])

            </div>
        </div>
    </div>

    <div class="row justify-content-between">

        <div id="tabla-captura-ventas" class="card mb-4 col-12 col-sm-12 col-md-9 ml-auto">
            <div id="card-venta" class="card-body" style="content-visibility: auto; overflow: auto; border-radius: 20px;">

                @include('pages.capturas.venta.venta-table')
                <div style="padding: 8px;"></div>

            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-3 ml-auto">
            <div class="row justify-content-between">
                <div id="totales-venta-card" class="card col-12 col-sm-5 col-md-12 ml-auto" style="height: min-content; margin-bottom: 0.5rem !important;">
                    <table class="table table-bordered table-captura-ventas" width="100%" style="margin-top: 12px;">
                        <tbody>
                            <tr id="venta_anticipo_disp_view" style="display: none;">
                                <td><h6 id="show-anticipos-venta" style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500; color: #0bb19e; display: flex; cursor: pointer;"><div><i class="fas fa-eye"></i></div>&nbsp;ANTICIPOS DISP: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem; color: #0bb19e;" id="venta_anticipo_disp">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">SUB TOTAL: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="venta_sub_total">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">IVA: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="venta_total_iva">0.00</h6></td>
                            </tr>
                            <tr id="totales_descuento" style="display: none;">
                                <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">DESCUENTO: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="venta_total_descuento">0.00</h6></td>
                            </tr>
                            <tr id="totales_retencion">
                                <td>
                                    <h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">
                                        <i
                                            id="icon_info_retencion_venta"
                                            class="fas fa-info icon-info"
                                            title="<b class='titulo-popover'>Base:</b> 0<br/> <b class='titulo-popover'>Subtotal:</b> 0 <br/> <b class='titulo-popover'>Sin responsablidad:</b> 07 => Retención en la fuente a título de renta"
                                            data-toggle="popover"
                                            data-html="true"
                                        ></i>
                                        <b id="nombre_info_retencion_venta" style="font-weight: 500;">RETENCIÓN:</b>
                                    </h6>
                                </td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="venta_total_retencion">0.00</h6></td>
                            </tr>
                            <tr>
                                <td><h6 style="margin-bottom: 0px; font-weight: bold;">TOTAL: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-weight: bold;" id="venta_total_valor">0.00</h6></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="ventaForm" class="card mb-4 col-12 col-sm-7 col-md-12 ml-auto">
                    <div style="overflow: auto;">
                        <table id="ventaFormaPago" class="table table-bordered display responsive table-captura-ventas" width="100%">
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
                            <h6 style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; text-wrap: nowrap;">PAGADO: </h6>
                        </div>
                        <div class="col-6" style="text-align: end; text-wrap: nowrap;">
                            <h6 id="total_pagado_venta" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0,00</h6>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <h6 id="total_faltante_venta_text" style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; text-wrap: nowrap;">FALTANTE: </h6>
                        </div>
                        <div class="col-6" style="text-align: end; text-wrap: nowrap;">
                            <h6 id="total_faltante_venta" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0,00</h6>
                        </div>
                    </div>

                    <div id="cambio-totals" class="row" style="display: none;">
                        <div class="col-6">
                            <h6 style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; color: blue;">CAMBIO: </h6>
                        </div>
                        <div class="col-6" style="text-align: end;">
                            <h6 id="total_cambio_venta" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; color: blue;">0,00</h6>
                        </div>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>

    <script>
        var ventaExistencias = @json(auth()->user()->can("venta existencia"));
        var ventaDescuento = @json(auth()->user()->can("venta descuento"));
        var ventaNegativa = @json(auth()->user()->can("venta negativa"));
        var ventaRapida = @json(auth()->user()->can("venta rapida"));
        var ventaFecha = @json(auth()->user()->can("venta fecha"));
        
        var primeraBodegaVenta = @json($bodegas);
        var primeraResolucionVenta = @json($resolucion);
        var ivaIncluidoVentas = @json($iva_incluido);
        var vendedoresVentas = @json($vendedores_ventas);
        var primeraNit = @json($cliente);
        var valor_uvt = @json($valor_uvt);

    </script>
    
</div>