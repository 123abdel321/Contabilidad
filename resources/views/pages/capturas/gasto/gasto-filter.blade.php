<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGastoGeneral" aria-expanded="false" aria-controls="collapseGastoGeneral">
                Datos captura de gastos
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseGastoGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="gastoFilterForm" class="needs-validation row" style="margin-top: 5px;" novalidate>

                    <input name="editing_gasto" id="editing_gasto" class="form-control form-control-sm" type="text" style="display: none;">

                    <div class="form-group col-12 col-sm-4 col-md-3">
                        <label>Proveedor <span style="color: red">*</span></label>
                        <select name="id_nit_gasto" id="id_nit_gasto" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El proveedor es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-4 col-md-2">
                        <label>Comprobante <span style="color: red">*</span></label>
                        <select name="id_comprobante_gasto" id="id_comprobante_gasto" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El comprobante es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-4 col-md-2">
                        <label>Centro costo <span style="color: red">*</span></label>
                        <select name="id_centro_costos_gasto" id="id_centro_costos_gasto" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El centro de costo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-4 col-md-2">
                        <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                        <input name="fecha_manual_gasto" id="fecha_manual_gasto" class="form-control form-control-sm" type="date" required>
                        <div class="invalid-feedback">
                            La fecha es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-4 col-md-2">
                        <label for="example-text-input" class="form-control-label">No. factura <span style="color: red">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="documento_referencia_gasto" id="documento_referencia_gasto" onkeydown="buscarFacturaGasto(event)" style="background-position: right 0.75rem center !important;" required>
                        <i class="fa fa-spinner fa-spin fa-fw gasto-load" id="documento_referencia_gasto_loading" style="display: none;"></i>
                        <div class="invalid-feedback" id="error_documento_referencia_gasto">
                            El No. factura requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-4 col-md-1">
                        <label for="example-text-input" class="form-control-label">Consecutivo</label>
                        <input type="text" class="form-control form-control-sm" name="consecutivo_gasto" id="consecutivo_gasto" onkeydown="enterConsecutivoGastos(event)" disabled required>
                    </div>

                </form>
                <div class="col-md normal-rem" style="margin-top: -5px;">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaGasto" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR GASTO</b>
                    </span>
                    <span id="iniciarCapturaGastoLoading" class="badge bg-gradient-success" style="display:none; min-width: 40px; margin-bottom: 12px; float: inline-end;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="cancelarCapturaGasto" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR GASTO</b>
                    </span>
                    <span id="agregarGasto" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; display:none;">
                        <i class="fas fa-plus-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR GASTO</b>
                    </span>
                    <span id="crearCapturaGastoDisabled" href="javascript:void(0)" class="badge bg-success" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR GASTO</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaGasto" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR GASTO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>