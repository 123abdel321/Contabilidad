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

    #ventaFormaPago tbody>tr.odd {
        font-size: initial;
        font-weight: 600;
    }

    #ventaFormaPago tbody>tr.even {
        font-size: initial;
        font-weight: 600;
    }

    .venta_producto_load {
        position: absolute;
        margin-left: 110px !important;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                @include('pages.capturas.venta.venta-filter')

            </div>
        </div>

        <div id="card-venta" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 20px;">
            @include('pages.capturas.venta.venta-table')
            <div style="padding: 10px;"></div>
        </div>
    </div>

    @include('pages.capturas.venta.venta-pagos')

    <div class="card mb-4">
        <div class="row card-body" style="content-visibility: auto; text-align: -webkit-center; margin-top: 5px;">
            <div class="col-12 col-sm-6 col-md-6">
            </div>
            <div class="col-12 col-sm-6 col-md-6">
                <table class="table table-bordered" width="100%">
                    <tbody>
                        <tr>
                            <td><h6 style="font-size: 0.9rem;">SUB TOTAL: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="venta_sub_total">0.00</h6></td>
                        </tr>
                        <tr>
                            <td><h6 style="font-size: 0.9rem;">IVA: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="venta_total_iva">0.00</h6></td>
                        </tr>
                        <tr id="totales_descuento" style="display: none;">
                            <td><h6 style="font-size: 0.9rem;">DESCUENTO:: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="venta_total_descuento">0.00</h6></td>
                        </tr>
                        <tr id="totales_retencion" style="display: none;">
                            <td><h6 style="font-size: 0.9rem;" id="venta_texto_retencion">RETENCION:: </h6></td>
                            <td><h6 style="float: right; font-size: 0.9rem;" id="venta_total_retencion">0.00</h6></td>
                        </tr>
                        <tr>
                            <td><h6 style="font-weight: bold;">TOTAL: </h6></td>
                            <td><h6 style="float: right; font-weight: bold;" id="venta_total_valor">0.00</h6></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        var primeraBodegaVenta = JSON.parse('<?php echo $bodegas; ?>');
        var primeraResolucionVenta = JSON.parse('<?php echo $resolucion; ?>');
        var agregarDescuento = '<?php echo auth()->user()->can('ventas descuento'); ?>';
        var ventaExistencias = '<?php echo auth()->user()->can('ventas existencia'); ?>';
    </script>
    
</div>