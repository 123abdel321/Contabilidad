<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocumentoGeneral" aria-expanded="false" aria-controls="collapseDocumentoGeneral">
                Datos de captura
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseDocumentoGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="documentoFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">

                        <input name="editing_documento" id="editing_documento" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-12 col-md-4 col-sm-4">
                            <label>Comprobante <span style="color: red">*</span></label>
                            <select name="id_comprobante" id="id_comprobante" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            <div class="invalid-feedback">
                                El comprobante es requerido
                            </div>
                        </div>

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                            <input name="fecha_manual" id="fecha_manual" class="form-control form-control-sm" onkeypress="changeFecha(event)" type="date" required>
                        </div>

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label for="example-text-input" class="form-control-label">Consecutivo <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="consecutivo" id="consecutivo" onkeypress="changeConcecutivo(event)" requiere>
                        </div>
                    </div>  
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaDocumentos" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR CAPTURA</b>
                    </span>
                    <span id="iniciarCapturaDocumentosLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="agregarDocumentos" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; display:none;">
                        <i class="fas fa-plus-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR DOCUMENTO</b>
                    </span>
                    <span id="cancelarCapturaDocumentos" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="min-width: 40px; display:none;">
                        <!-- <i class="fas fa-folder-minus" style="font-size: 17px;"></i> -->
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR CAPTURA</b>
                    </span>
                    <span id="crearCapturaDocumentosDisabled" href="javascript:void(0)" class="badge bg-success" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR DOCUMENTO</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaDocumentos" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR DOCUMENTO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>