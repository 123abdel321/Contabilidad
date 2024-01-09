<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVentaGeneral" aria-expanded="false" aria-controls="collapseVentaGeneral">
                Datos de captura
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseVentaGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="ventaFilterForm" class="needs-validation row" style="margin-top: 10px;" novalidate>
                    @if ($vendedores_ventas)
                        <div class="col-6 col-sm-4 col-md-2">
                    @else
                        <div class="col-6 col-sm-4 col-md-4">
                    @endif
                        <label>Cliente<span style="color: red">*</span></label>
                        <div class="input-group">
                            <select name="id_cliente_venta" id="id_cliente_venta" class="form-control form-control-sm" style="font-size: 13px;" required>
                            </select>
                            <span id="" href="javascript:void(0)" onclick="openModalNewNit()" class="btn badge bg-gradient-light" style="min-width: 40px; position: static; height: 30px; border-radius: 0px 5px 5px 0px; box-shadow: 0px 0px 0px 0px, 0px 0px 0px 0px;">
                                <i class="fas fa-user-plus" style="font-size: 15px; margin-top: 2px;"></i>
                            </span>
                            <div class="invalid-feedback">
                                El cliente es requerido
                            </div>
                        </div>
                    </div>

                    @if ($vendedores_ventas)
                        <div class="form-group col-6 col-sm-4 col-md-2">
                            <label>Vendedor<span style="color: red">*</span></label>
                            <select name="id_vendedor_venta" id="id_vendedor_venta" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                            
                            <div class="invalid-feedback">
                                El vendedor es requerido
                            </div>
                        </div>
                    @endif

                    <div class="form-group col-6 col-sm-4 col-md-2">
                        <label>Resolucion<span style="color: red">*</span></label>
                        <select name="id_resolucion_venta" id="id_resolucion_venta" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La resolución es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-2">
                        <label>Bodega<span style="color: red">*</span></label>
                        <select name="id_bodega_venta" id="id_bodega_venta" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La bodega es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-2">
                        <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                        <input name="fecha_manual_venta" id="fecha_manual_venta" class="form-control form-control-sm" type="date" required disabled>
                        <div class="invalid-feedback">
                            La fecha es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-2">
                        <label for="example-text-input" class="form-control-label">No. factura <span style="color: red">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="documento_referencia_venta" id="documento_referencia_venta" required disabled>
                        <i class="fa fa-spinner fa-spin fa-fw venta-load" id="documento_referencia_venta_loading" style="display: none;"></i>
                        <div class="invalid-feedback" id="error_documento_referencia_venta">
                            El No. factura requerida
                        </div>
                    </div>

                    <div id="input-anticipos-venta" class="form-group col-6 col-sm-4 col-md-2" style="display: none;">
                        <label for="example-text-input" class="form-control-label">Anticipos <span style="color: red">*</span></label>
                        <input name="id_saldo_anticipo_venta" id="id_saldo_anticipo_venta" class="form-control form-control-sm" type="text" disabled style="text-align: right;">
                        <div class="invalid-feedback" id="error-anticipo-cliente-venta">
                            Valor superado
                        </div>
                    </div>
                    
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaVenta" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR VENTA</b>
                    </span>
                    <span id="iniciarCapturaVentaLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="cancelarCapturaVenta" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR VENTA</b>
                    </span>
                    <span id="crearCapturaVentaDisabled" href="javascript:void(0)" class="badge bg-success" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR VENTA</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="agregarVentaProducto" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; display:none;">
                        <i class="fas fa-plus-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR PRODUCTO</b>
                    </span>
                    <span id="crearCapturaVenta" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR VENTA</b>
                    </span>
                    <span id="crearCapturaVentaLoading" class="badge bg-gradient-success" style="display:none; min-width: 40px; margin-bottom: 16px; float: right;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>