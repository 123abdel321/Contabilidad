<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompraGeneral" aria-expanded="false" aria-controls="collapseCompraGeneral">
                Datos de captura
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseCompraGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="compraFilterForm" class="needs-validation row" style="margin-top: 10px;" novalidate>

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label>Proveedor<span style="color: red">*</span></label>
                        <select name="id_proveedor_compra" id="id_proveedor_compra" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El proveedor es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label>Bodega<span style="color: red">*</span></label>
                        <select name="id_bodega_compra" id="id_bodega_compra" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La bodega es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                        <input name="fecha_manual_compra" id="fecha_manual_compra" class="form-control form-control-sm" type="date" required>
                        <div class="invalid-feedback">
                            La fecha es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label for="example-text-input" class="form-control-label">No. factura <span style="color: red">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="documento_referencia_compra" id="documento_referencia_compra" onkeydown="buscarFacturaCompra(event)" required>
                        <i class="fa fa-spinner fa-spin fa-fw compra-load" id="documento_referencia_compra_loading" style="display: none;"></i>
                        <div class="invalid-feedback" id="error_documento_referencia_compra">
                            El No. factura requerido
                        </div>
                    </div>
                    
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaCompra" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR COMPRA</b>
                    </span>
                    <span id="iniciarCapturaCompraLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="agregarCompra" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; display:none;">
                        <i class="fas fa-plus-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR PRODUCTO</b>
                    </span>
                    <span id="cancelarCapturaCompra" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR COMPRA</b>
                    </span>
                    <span id="crearCapturaCompraDisabled" href="javascript:void(0)" class="badge bg-success" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR COMPRA</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaCompra" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR COMPRA</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>