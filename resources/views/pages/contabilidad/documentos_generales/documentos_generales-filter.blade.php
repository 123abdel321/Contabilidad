<div class="accordion" id="accordionRentalDocumentosGenerales">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocumentosGeneralesInforme" aria-expanded="false" aria-controls="collapseDocumentosGeneralesInforme">
                Filtros Documentos generales
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseDocumentosGeneralesInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="documentosGeneralesFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">
                        <input name="id_documento_general_cargado" id="id_documento_general_cargado" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="fecha_manual_documentos_generales" class="form-control-label">Fecha<span style="color: red">*</span></label>
                            <input name="fecha_manual_documentos_generales" id="fecha_manual_documentos_generales" class="form-control form-control-sm" require>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Valor desde</label>
                            <input name="precio_desde_documentos_generales" id="precio_desde_documentos_generales" class="form-control form-control-sm" type="number">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Valor hasta</label>
                            <input name="precio_hasta_documentos_generales" id="precio_hasta_documentos_generales" class="form-control form-control-sm" type="number">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="factura_documentos_generales" class="form-control-label">No. factura</label>
                            <input name="factura_documentos_generales" id="factura_documentos_generales" class="form-control form-control-sm" type="text">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="consecutivo_documentos_generales" class="form-control-label">Consecutivo</label>
                            <input name="consecutivo_documentos_generales" id="consecutivo_documentos_generales" class="form-control form-control-sm" type="text">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="concepto_documentos_generales" class="form-control-label">Concepto</label>
                            <input name="concepto_documentos_generales" id="concepto_documentos_generales" class="form-control form-control-sm" type="text">
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="id_nit_documentos_generales">Cedula/Nit</label>
                            <select name="id_nit_documentos_generales" id="id_nit_documentos_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="id_comprobante_documentos_generales">Comprobante</label>
                            <select name="id_comprobante_documentos_generales" id="id_comprobante_documentos_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="id_cecos_documentos_generales">Centro costos</label>
                            <select name="id_cecos_documentos_generales" id="id_cecos_documentos_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="id_cuenta_documentos_generales">Cuenta</label>
                            <select name="id_cuenta_documentos_generales" id="id_cuenta_documentos_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <!-- <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Usuario</label>
                            <select name="id_usuario_documentos_generales" id="id_usuario_documentos_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div> -->

                        <div class="form-group form-group col-12 col-sm-3 col-md-3">
                            <label for="agrupar_documentos_generales">Agrupar por:</label>
                            <select class="form-control form-control-sm" id="agrupar_documentos_generales" name="agrupar_documentos_generales[]" multiple="multiple">
                                <option value="id_cuenta">Cuenta</option>
                                <option value="id_nit">Cedula/Nits</option>
                                <option value="id_comprobante">Comprobante</option>
                                <option value="id_centro_costos">Centro costos</option>
                                <option value="consecutivo" selected>Documento</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Agrupado</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="agrupado_documentos_generales" id="agrupado_documentos_generales0" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="agrupado_documentos_generales0" style="font-size: 11px;">
                                    Normal
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="agrupado_documentos_generales" id="agrupado_documentos_generales1" style="font-size: 11px;">
                                <label class="form-check-label" for="agrupado_documentos_generales1" style="font-size: 11px;">
                                    Niveles
                                </label>
                            </div>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Documento</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="anulado_documentos_generales" id="anulado_documentos_generales0" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="anulado_documentos_generales0" style="font-size: 11px;">
                                    Normal
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="anulado_documentos_generales" id="anulado_documentos_generales1" style="font-size: 11px;">
                                <label class="form-check-label" for="anulado_documentos_generales1" style="font-size: 11px;">
                                    Anulado
                                </label>
                            </div>
                        </div>

                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarDocumentosGenerales" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarDocumentosGeneralesLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <span id="descargarExcelDocumento" class="btn badge bg-gradient-dark btn-bg-excel" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelDocumentoDisabled" class="badge bg-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>