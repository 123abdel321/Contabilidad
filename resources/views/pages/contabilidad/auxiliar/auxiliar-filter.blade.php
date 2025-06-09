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
                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha</label>
                            <input name="fecha_manual_auxiliar" id="fecha_manual_auxiliar" class="form-control form-control-sm" require>
                        </div>
                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Cuenta</label>
                            <select name="id_cuenta_auxiliar" id="id_cuenta_auxiliar" class="form-control form-control-sm">
                                <option value="">Ninguna</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit_auxiliar" id="id_nit_auxiliar">
                                <option value="">Ninguno</option>
                            </select>
                        </div>
                        
                    </div>  
                    <div class="form-group col-12 col-sm-3 col-md-1" style="margin-left: 5px; display: none;">
                        <div class="row">
                            <div class="form-check col-12 col-sm-12 col-md-12" style="margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="tipo_documento" id="tipo_documento1" checked>
                                <label class="form-check-label" for="tipo_documento1">
                                    Todas
                                </label>
                            </div>
                            <div class="form-check col-12 col-sm-12 col-md-12" style="margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="tipo_documento" id="tipo_documento2">
                                <label class="form-check-label" for="tipo_documento2">
                                    Anuladas
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarAuxiliar" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarAuxiliarLoading" class="badge bg-gradient-primary" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 13px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <span id="descargarExcelAuxiliar" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelAuxiliarDisabled" class="badge bg-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <!-- BOTON PDF -->
                    <span id="descargarPdfAuxiliar" class="btn badge bg-gradient-success btn-bg-pdf" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-pdf" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">PDF</b>
                    </span>
                    <span id="descargarPdfAuxiliarDisabled" class="badge bg-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">PDF</b>
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