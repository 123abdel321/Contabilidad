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

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label>Comprobante <span style="color: red">*</span></label>
                            <select name="id_comprobante" id="id_comprobante" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            <div class="invalid-feedback">
                                Porfavor seleecionar comprobante
                            </div>
                        </div>

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                            <input name="fecha_manual" id="fecha_manual" class="form-control form-control-sm" type="date" required>
                        </div>

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label for="example-text-input" class="form-control-label">Consecutivo <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="consecutivo" id="consecutivo" requiere>
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
                        <i class="fas fa-file-signature" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR DOCUMENTO</b>
                    </span>
                    <span id="cancelarCapturaDocumentos" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-folder-minus" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR CAPTURA</b>
                    </span>
                    <span id="crearCapturaDocumentosDisabled" href="javascript:void(0)" class="badge bg-success" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR DOCUMENTOS</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaDocumentos" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR DOCUMENTOS</b>
                    </span>
                    
                    
                    <!-- <span id="generarAuxiliarLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                    <span id="descargarExcelAuxiliar" class="btn badge bg-gradient-success" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXPORTAR</b>
                    </span>
                    <span id="descargarExcelAuxiliarDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXPORTAR</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="" class="badge bg-dark" style="min-width: 40px; color: #adadad;" >
                        <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXPORTAR</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span> -->
                </div>
                <div>
                    <!-- <button class="btn btn-success btn-sm ms-auto" id="iniciarCapturaDocumentos">Iniciar captura</button>
                    <button class="btn btn-success btn-sm ms-auto" id="agregarDocumentos" style="display: none">Agregar</button>
                    <button class="btn btn-danger btn-sm ms-auto" id="cancelarCapturaDocumentos" style="display: none">Cancelar</button>
                    <button class="btn btn-info btn-sm ms-auto" id="crearCapturaDocumentos" style="float: right; display: none" disabled><i class="fa fa-upload" aria-hidden="true" style="font-size: 0.8rem;">&nbsp;&nbsp;</i>Grabar documentos</button> -->
                </div>

            </div>
        </div>
    </div>
</div>