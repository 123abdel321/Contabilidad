<div class="accordion" id="accordionRental">
    <div class="accordion-item">
    <h5 class="accordion-header" id="headingOne">
              <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Filtros de informe comprobantes
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
              </button>
            </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosResumenComprobantes" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="estadoActualInformeForm" style="margin-top: 10px;">
                    <div class="row">
                        <input name="id_comprobantes_cargado" id="id_comprobantes_cargado" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Comprobante</label>
                            <select name="id_comprobante_comprobantes" id="id_comprobante_comprobantes" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_desde_comprobantes" id="fecha_desde_comprobantes" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                            <input name="fecha_hasta_comprobantes" id="fecha_hasta_comprobantes" class="form-control form-control-sm" type="date" require>
                        </div>
                        
                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Cuenta</label>
                            <select name="id_cuenta_comprobantes" id="id_cuenta_comprobantes" class="form-control form-control-sm">
                                <option value="">Ninguna</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit_comprobantes" id="id_nit_comprobantes">
                                <option value="">Ninguno</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Agrupar por</label>
                            <select name="agrupar_comprobantes" id="agrupar_comprobantes" class="form-control form-control-sm">
                                <option value="">Seleccionar</option>
                                <option value="id_cuenta">Cuenta</option>
                                <option value="id_nit">Cedula/Nits</option>
                                <option value="consecutivo">Documento</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-2 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Detallar</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="detalle_comprobantes" id="detalle_comprobantes0" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="detalle_comprobantes0" style="font-size: 11px;">
                                    No
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="detalle_comprobantes" id="detalle_comprobantes1" style="font-size: 11px;">
                                <label class="form-check-label" for="detalle_comprobantes1" style="font-size: 11px;">
                                    Si
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarResumenComprobantes" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarResumenComprobantesLoading" class="badge bg-gradient-primary" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 13px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <!-- <span id="descargarExcelResumenComprobantes" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelResumenComprobantesDisabled" class="badge bg-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span> -->
                    <!-- BOTON ULTIMO INFORME -->
                    <span id="generarResumenComprobantesUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarResumenComprobantesUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>