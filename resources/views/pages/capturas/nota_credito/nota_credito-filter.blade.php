<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotaCreditoGeneral" aria-expanded="false" aria-controls="collapseNotaCreditoGeneral">
                Datos de factura devoluciones
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseNotaCreditoGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="notaCreditoFilterForm" class="needs-validation row" style="margin-top: 10px;" onsubmit="return false;" novalidate>

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label>Cliente<span style="color: red">*</span></label>
                        <select name="id_cliente_nota_credito" id="id_cliente_nota_credito" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El cliente es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label>Resolucion<span style="color: red">*</span></label>
                        <select name="id_resolucion_nota_credito" id="id_resolucion_nota_credito" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La resolución es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-2">
                        <label>Bodega<span style="color: red">*</span></label>
                        <select name="id_bodega_nota_credito" id="id_bodega_nota_credito" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La bodega es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-2">
                        <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                        <input name="fecha_manual_nota_credito" id="fecha_manual_nota_credito" class="form-control form-control-sm" type="date" required disabled>
                        <div class="invalid-feedback">
                            La fecha es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-2">
                        <label for="consecutivo_nota_credito" class="form-control-label">No. factura <span style="color: red">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="consecutivo_nota_credito" id="consecutivo_nota_credito" onkeydown="iniciarNotaCredito(event)" required disabled>
                        <i class="fa fa-spinner fa-spin fa-fw nota-credito-load" id="consecutivo_nota_credito_loading" style="display: none; margin-top: -22px; float: right;"></i>
                        <div class="invalid-feedback" id="error_consecutivo_nota_credito">
                            El No. factura requerido
                        </div>
                    </div>
                    
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaNotaCredito" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-gold" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR FACTURA</b>
                    </span>
                    <span id="iniciarCapturaNotaCreditoLoading" class="badge bg-gradient-info btn-bg-gold" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="cancelarCapturaNotaCredito" href="javascript:void(0)" class="btn badge bg-gradient-danger btn-bg-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR NOTA CREDITO</b>
                    </span>
                    <span id="crearCapturaNotaCreditoDisabled" href="javascript:void(0)" class="badge bg-gradient-dark" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR NOTA CREDITO</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaNotaCredito" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; display:none; float: right; margin-left: 5px;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR NOTA CREDITO</b>
                    </span>
                    <span id="movimientoContableNotaCredito" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px; display:none; float: right;">
                        <i class="fa-solid fa-calculator" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">VER MOVIMIENTO CONTABLE</b>
                    </span>
                    <span id="crearCapturaNotaCreditoLoading" class="badge bg-gradient-success btn-bg-excel" style="display:none; min-width: 40px; margin-bottom: 16px; float: right;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>