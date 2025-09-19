<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePagoGeneral" aria-expanded="false" aria-controls="collapsePagoGeneral">
                Datos de pagos
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapsePagoGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="pagoFilterForm" class="needs-validation row" style="margin-top: 10px;" novalidate>

                    <input type="text" class="form-control" name="id_pago_up" id="id_pago_up" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-md-3">
                        <label>Cédula / Nit<span style="color: red">*</span></label>
                        <select name="id_nit_pago" id="id_nit_pago" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La cédula / nit es requerida
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-3">
                        <label>Comprobante <span style="color: red">*</span></label>
                        <select name="id_comprobante_pago" id="id_comprobante_pago" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El comprobante es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-2" >
                        <label for="example-text-input" class="form-control-label">Total pago</label>
                        <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="total_abono_pago" id="total_abono_pago" onfocus="this.select();" onkeypress="changeTotalAbonoPago(event)"  value="0">
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-2">
                        <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                        <input name="fecha_manual_pago" id="fecha_manual_pago" class="form-control form-control-sm" type="datetime-local" required>
                        <div class="invalid-feedback">
                            La fecha es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-2">
                        <label for="example-text-input" class="form-control-label">Consecutivo</label>
                        <input type="text" class="form-control form-control-sm" name="documento_referencia_pago" id="documento_referencia_pago" onkeydown="buscarFacturaPagos(event)" disabled required>
                    </div>

                    <div id="input_anticipos_pago" class="form-group col-6 col-sm-4 col-md-2" style="display: none;">
                        <label for="example-text-input" class="form-control-label">Anticipos <span style="color: red">*</span></label>
                        <input name="saldo_anticipo_pago" id="saldo_anticipo_pago" class="form-control form-control-sm" type="text" disabled style="text-align: right;">
                        <div class="invalid-feedback" id="error-anticipo-cliente-venta">
                            Valor superado
                        </div>
                    </div>

                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaPago" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-gold" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR PAGO</b>
                    </span>
                    <span id="cancelarCapturaPago" href="javascript:void(0)" class="btn badge bg-gradient-danger btn-bg-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR PAGO</b>
                    </span>
                    <span id="agregarPago" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-info" style="min-width: 40px; display:none;">
                        <i class="fas fa-plus-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR PRODUCTO</b>
                    </span>


                    <span id="crearCapturaPagoDisabled" href="javascript:void(0)" class="badge bg-gradient-dark" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR PAGO</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaPago" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; display:none; float: right; margin-left: 3px;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR PAGO</b>
                    </span>
                    <span id="movimientoContablePago" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px; display:none; float: right;">
                        <i class="fa-solid fa-calculator" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">VER MOVIMIENTO CONTABLE</b>
                    </span>
                    <span id="iniciarCapturaPagoLoading" class="badge bg-gradient-info btn-bg-excel" style="display:none; min-width: 40px; margin-bottom: 16px; float: right;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>