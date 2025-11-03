<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="headingOne">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Filtros de auxiliar
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosAuxiliar" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="auxiliarInformeForm" style="margin-top: 10px;">
                    <div class="row">
                        <input name="id_auxiliar_cargado" id="id_auxiliar_cargado" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="id_nit_auxiliar" style="width: 100%;">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit_auxiliar" id="id_nit_auxiliar">
                                <option value="">Ninguno</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="id_cuenta_auxiliar" style="width: 100%;">Cuenta</label>
                            <select name="id_cuenta_auxiliar" id="id_cuenta_auxiliar" class="form-control form-control-sm">
                                <option value="">Ninguna</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-2">
                            <label for="fecha_manual_auxiliar" class="form-control-label">Fecha</label>
                            <input name="fecha_manual_auxiliar" id="fecha_manual_auxiliar" class="form-control form-control-sm" required>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label class="form-control-label">Niveles</label>

                            <div class="form-check col-12 col-md-12 col-sm-12" 
                                style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="checkbox" name="niveles_auxiliar[]" id="nivel_auxiliar1" value="cuentas_padres" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="nivel_auxiliar1" style="font-size: 11px;">Cuentas Padre</label>
                            </div>

                            <div class="form-check col-12 col-md-12 col-sm-12" 
                                style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="checkbox" name="niveles_auxiliar[]" id="nivel_auxiliar2" value="totales_nits" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="nivel_auxiliar2" style="font-size: 11px;">Totales Nit</label>
                            </div>

                            <div class="form-check col-12 col-md-12 col-sm-12" 
                                style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="checkbox" name="niveles_auxiliar[]" id="nivel_auxiliar3" value="detalles_nits" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="nivel_auxiliar3" style="font-size: 11px;">Detalles Nit</label>
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Documentos</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="errores_auxiliar" id="errores_auxiliar1" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="errores_auxiliar1" style="font-size: 11px;">
                                    Todos
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="errores_auxiliar" id="errores_auxiliar2" style="font-size: 11px;">
                                <label class="form-check-label" for="errores_auxiliar2" style="font-size: 11px;">
                                    Errores
                                </label>
                            </div>
                        </div>

                    </div>

                    <!-- OPCIONES DE TIPO DE DOCUMENTO (OCULTAS) -->
                    <div class="form-group col-12 col-sm-3 col-md-1" style="margin-left: 5px; display: none;">
                        <div class="row">
                            <div class="form-check col-12" style="margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="tipo_documento" id="tipo_documento1" checked>
                                <label class="form-check-label" for="tipo_documento1">Todas</label>
                            </div>
                            <div class="form-check col-12" style="margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="tipo_documento" id="tipo_documento2">
                                <label class="form-check-label" for="tipo_documento2">Anuladas</label>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarAuxiliar" href="javascript:void(0)" class="btn badge bg-gradient-primary btn-bg-gold" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarAuxiliarLoading" class="badge bg-gradient-primary btn-bg-gold-loading" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 13px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <span id="descargarExcelAuxiliar" class="btn badge bg-gradient-success btn-bg-success btn-bg-excel" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                    </span>
                    <span id="descargarExcelAuxiliarLoading" class="badge bg-gradient-info btn-bg-excel-loading" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                    </span>
                    <span id="descargarExcelAuxiliarDisabled" class="badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <!-- BOTON PDF -->
                    <span id="descargarPdfAuxiliar" class="btn badge bg-gradient-success btn-bg-danger" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-pdf" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;PDF</b>
                    </span>
                    <span id="descargarPdfAuxiliarLoading" class="badge bg-gradient-info btn-bg-danger-loading" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;PDF</b>
                    </span>
                    <span id="descargarPdfAuxiliarDisabled" class="badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;PDF</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <!-- BOTON ULTIMO INFORME -->
                    <span id="generarAuxiliarUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarAuxiliarUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>