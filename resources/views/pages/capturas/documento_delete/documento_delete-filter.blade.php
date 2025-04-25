<div class="accordion" id="accordionRentalEliminarDocumentos">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEliminarDocumentosInforme" aria-expanded="false" aria-controls="collapseEliminarDocumentosInforme">
                Filtros Documentos generales a eliminar
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseEliminarDocumentosInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="eliminarDocumentosFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">

                        <div class="form-group col-6 col-sm-3 col-md-2">
                            <label for="example-text-input" class="form-control-label">Fecha desde<span style="color: red">*</span></label>
                            <input name="fecha_desde_eliminar_documentos" id="fecha_desde_eliminar_documentos" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-2">
                            <label for="example-text-input" class="form-control-label">Fecha hasta<span style="color: red">*</span></label>
                            <input name="fecha_hasta_eliminar_documentos" id="fecha_hasta_eliminar_documentos" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-2">
                            <label for="example-text-input" class="form-control-label">Valor desde</label>
                            <input name="precio_desde_eliminar_documentos" id="precio_desde_eliminar_documentos" class="form-control form-control-sm" type="number">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-2">
                            <label for="example-text-input" class="form-control-label">Valor hasta</label>
                            <input name="precio_hasta_eliminar_documentos" id="precio_hasta_eliminar_documentos" class="form-control form-control-sm" type="number">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-2">
                            <label for="example-text-input" class="form-control-label">No. factura</label>
                            <input name="factura_eliminar_documentos" id="factura_eliminar_documentos" class="form-control form-control-sm" type="text">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-2">
                            <label for="example-text-input" class="form-control-label">Consecutivo</label>
                            <input name="consecutivo_eliminar_documentos" id="consecutivo_eliminar_documentos" class="form-control form-control-sm" type="text">
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label>Cedula/Nit</label>
                            <select name="id_nit_eliminar_documentos" id="id_nit_eliminar_documentos" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label>Comprobante</label>
                            <select name="id_comprobante_eliminar_documentos" id="id_comprobante_eliminar_documentos" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label>Centro costos</label>
                            <select name="id_cecos_eliminar_documentos" id="id_cecos_eliminar_documentos" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label>Cuenta</label>
                            <select name="id_cuenta_eliminar_documentos" id="id_cuenta_eliminar_documentos" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <!-- <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Usuario</label>
                            <select name="id_usuario_eliminar_documentos" id="id_usuario_eliminar_documentos" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div> -->

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Concepto</label>
                            <input name="concepto_eliminar_documentos" id="concepto_eliminar_documentos" class="form-control form-control-sm" type="text">
                        </div>
                        
                        <div class="form-group form-group col-12 col-sm-4 col-md-4">
                            <label for="exampleFormControlSelect1">Agrupar por:</label>
                            <select class="form-control form-control-sm" id="agrupar_eliminar_documentos" name="agrupar_eliminar_documentos[]" multiple="multiple">
                                <option value="id_cuenta">Cuenta</option>
                                <option value="id_nit">Cedula/Nits</option>
                                <option value="id_comprobante">Comprobante</option>
                                <option value="id_centro_costos">Centro costos</option>
                                <option value="consecutivo" selected>Documento</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-2 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Agrupado</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="agrupado_eliminar_documentos" id="agrupado_eliminar_documentos0" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="agrupado_eliminar_documentos0" style="font-size: 11px;">
                                    Normal
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="agrupado_eliminar_documentos" id="agrupado_eliminar_documentos1" style="font-size: 11px;">
                                <label class="form-check-label" for="agrupado_eliminar_documentos1" style="font-size: 11px;">
                                    Niveles
                                </label>
                            </div>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Documento</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="anulado_eliminar_generales" id="anulado_eliminar_generales0" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="anulado_eliminar_generales0" style="font-size: 11px;">
                                    Normal
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="anulado_eliminar_generales" id="anulado_eliminar_generales1" style="font-size: 11px;">
                                <label class="form-check-label" for="anulado_eliminar_generales1" style="font-size: 11px;">
                                    Anulado
                                </label>
                            </div>
                        </div>

                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarEliminarDocumentos" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-info" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarEliminarDocumentosLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                    <!-- BOTON ELIMINAR -->
                    <span id="eliminarDocumentos" href="javascript:void(0)" class="btn badge bg-danger btn-bg-danger" style="min-width: 40px; display: none;">
                        <i class="fas fa-trash" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">ELIMINAR DOCUMENTOS</b>
                    </span>
                    <span id="eliminarDocumentosDisabled" class="badge bg-danger" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-trash" style="font-size: 17px; color: #adadad;"></i>
                        <b style="vertical-align: text-top;">ELIMINAR DOCUMENTOS</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>