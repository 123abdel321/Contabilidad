<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVentasAcumuladasInforme" aria-expanded="false" aria-controls="collapseVentasAcumuladasInforme">
                Filtros informe de ventas acumuladas 
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseVentasAcumuladasInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="ventas_acumuladasFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">

                        <input name="id_venta_acumulada_cargado" id="id_venta_acumulada_cargado" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label>Tipo informe</label>
                            <select name="id_tipo_informe_ventas_acumuladas" id="id_tipo_informe_ventas_acumuladas" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="0">Cliente</option>
                                <option value="1">Resolución</option>
                                <option value="2">Bodega</option>
                                <option value="3">Producto</option>
                                <option value="4">Forma de pago</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label>Cliente</label>
                            <select name="id_cliente_ventas_acumuladas" id="id_cliente_ventas_acumuladas" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_manual_ventas_acumuladas" id="fecha_manual_ventas_acumuladas" class="form-control form-control-sm" require>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Factura</label>
                            <input type="text" class="form-control form-control-sm" name="factura_ventas_acumuladas" id="factura_ventas_acumuladas">
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="resolucion-text-input" class="form-control-label">Resolucion</label>
                            <select name="id_resolucion_ventas_acumuladas" id="id_resolucion_ventas_acumuladas" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label>Bodega</label>
                            <select name="id_bodega_ventas_acumuladas" id="id_bodega_ventas_acumuladas" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="bodega-text-input" class="form-control-label">Producto</label>
                            <select name="id_producto_ventas_acumuladas" id="id_producto_ventas_acumuladas" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="usuario-text-input" class="form-control-label">Usuario</label>
                            <select name="id_usuario_ventas_acumuladas" id="id_usuario_ventas_acumuladas" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="usuario-text-input" class="form-control-label">Forma de pago</label>
                            <select name="id_forma_pago_ventas_acumuladas" id="id_forma_pago_ventas_acumuladas" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Detallar ventas_acumuladas</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="detallar_venta" id="detallar_venta1" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="detallar_venta1" style="font-size: 11px;">
                                    Si
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="detallar_venta" id="detallar_venta2" style="font-size: 11px;">
                                <label class="form-check-label" for="detallar_venta2" style="font-size: 11px;">
                                    No
                                </label>
                            </div>
                        </div>

                    </div>  
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarVentasAcumuladas" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarVentasAcumuladasLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                </div>
            </div>
        </div>
        
    </div>
</div>