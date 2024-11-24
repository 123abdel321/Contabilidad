<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtrosBalance">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Filtros de balance
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosBalance" data-bs-parent="#accordionRental" >
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="balanceprimaryrmeForm" style="margin-top: 10px;">
                    <input name="id_balance_cargado" id="id_balance_cargado" class="form-control form-control-sm" type="text" style="display: none;">
                    <div class="row">
                        <div class="form-group form-group col-12 col-sm-4 col-md-2">
                            <label for="exampleFormControlSelect1">Tipo informe</label>
                            <select class="form-control form-control-sm tipo_cartera" id="tipo_informe_balance" name="tipo_informe_balance">
                                <option value="1">De prueba</option>
                                <option value="2">De terceros</option>
                                <option value="3">General</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-2">
                            <label for="exampleFormControlSelect1">Cuenta desde</label>
                            <input name="cuenta_desde_balance" id="cuenta_desde_balance" onfocus="this.select()" class="form-control form-control-sm" type="text">
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-2">
                            <label for="exampleFormControlSelect1">Cuenta hasta</label>
                            <input name="cuenta_hasta_balance" id="cuenta_hasta_balance" onfocus="this.select()" class="form-control form-control-sm" type="text">
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-2">
                            <label for="exampleFormControlSelect1">Cedula / Nit</label>
                            <select name="id_nit_balance" id="id_nit_balance" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-2">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_desde_balance" id="fecha_desde_balance" class="form-control form-control-sm" type="date" require>
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-2">
                            <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                            <input name="fecha_hasta_balance" id="fecha_hasta_balance" class="form-control form-control-sm" type="date" require>
                        </div>
                        <div class="form-group col-12 col-sm-4 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                            <label for="example-text-input" class="form-control-label">Niveles</label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="nivel_balance" id="nivel_balance1" style="font-size: 11px;">
                                <label class="form-check-label" for="nivel_balance1" style="font-size: 11px;">
                                    Grupo
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="nivel_balance" id="nivel_balance2" style="font-size: 11px;">
                                <label class="form-check-label" for="nivel_balance2" style="font-size: 11px;">
                                    Cuentas
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="nivel_balance" id="nivel_balance3" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="nivel_balance3" style="font-size: 11px;">
                                    Sub-cuentas
                                </label>
                            </div>
                        </div>
                    </div> 
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarBalance" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarBalanceLoading" class="badge bg-gradient-primary" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <span id="descargarExcelBalance" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelBalanceDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad; margin-right: 3px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <!-- BOTON PDF -->
                    <span id="descargarPdfBalance" class="btn badge bg-gradient-success btn-bg-pdf" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-pdf" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">PDF</b>
                    </span>
                    <span id="descargarPdfBalanceDisabled" class="badge bg-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">PDF</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <!-- BOTON ULTIMO INFORME -->
                    <span id="generarBalanceUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarBalanceUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>