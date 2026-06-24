<div class="accordion" id="accordionRentalextracto">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseextractoInforme" aria-expanded="false" aria-controls="collapseextractoInforme">
                Filtros extracto
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseextractoInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="extractoFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>

                    <div class="row" style="padding: 4px;">

                        <input name="id_extracto_cargado" id="id_extracto_cargado" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="fecha_manual_extracto" class="form-control-label">Fecha</label>
                            <input name="fecha_manual_extracto" id="fecha_manual_extracto" class="form-control form-control-sm" require>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="id_nit_extracto" style=" width: 100%;">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit_extracto" id="id_nit_extracto">
                                <option value="">Ninguno</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="factura_documentos_extracto" class="form-control-label">No. factura</label>
                            <input name="factura_documentos_extracto" id="factura_documentos_extracto" class="form-control form-control-sm" type="text">
                        </div>
                        
                        <div class="form-group col-6 col-sm-3 col-md-3 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Saldo anterior</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="mostrar_saldo_anterior_extracto" id="mostrar_saldo_anterior_extracto0" style="font-size: 11px;">
                                <label class="form-check-label" for="mostrar_saldo_anterior_extracto0" style="font-size: 11px;">
                                    No
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="mostrar_saldo_anterior_extracto" id="mostrar_saldo_anterior_extracto1" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="mostrar_saldo_anterior_extracto1" style="font-size: 11px;">
                                    Si
                                </label>
                            </div>
                        </div>

                    </div>

                </form>

                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="extractoGenerar" href="javascript:void(0)" class="btn badge bg-gradient-primary btn-bg-gold" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="extractoGenerarLoading" class="badge bg-gradient-primary btn-bg-gold-loading" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
                    </span>&nbsp;
                    <!-- BOTON EXCEL -->
                    <span id="descargarExcelExtracto" class="btn badge bg-gradient-success btn-bg-success btn-bg-excel" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                    </span>
                    <span id="descargarExcelExtractoLoading" class="badge bg-gradient-info btn-bg-excel-loading" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                    </span>
                    <span id="descargarExcelExtractoDisabled" class="badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>