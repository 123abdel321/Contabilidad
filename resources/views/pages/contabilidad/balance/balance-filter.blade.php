<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtrosBalance">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Filtros de balance
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosBalance" data-bs-parent="#accordionRental" style="">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="balanceInformeForm" style="margin-top: 10px;">
                    <div class="row">
                        <div class="form-group col-md">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_desde" id="fecha_desde" class="form-control form-control-sm" type="date" require>
                        </div>
                        <div class="form-group col-md">
                            <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                            <input name="fecha_hasta" id="fecha_hasta" class="form-control form-control-sm" type="date" require>
                        </div>
                        <div class="form-group col-md">
                            <label for="exampleFormControlSelect1">Cuenta</label>
                            <select name="id_cuenta" id="id_cuenta" class="form-control form-control-sm">
                            </select>
                        </div>
                    </div> 
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarBalance" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarBalanceLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                    <!-- <span id="descargarExcelBalance" class="btn badge bg-gradient-success" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXPORTAR</b>
                    </span> -->
                    <span id="descargarExcelBalanceDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad; margin-right: 3px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="descargarPdfBalanceDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad; margin-right: 3px;">
                        <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">PDF</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                </div>
                <!-- <div class="col-md">
                    <button class="btn btn-primary btn-sm ms-auto" id="generarBalance">Filtrar</button>
                    <button id="generarBalanceLoading" class="btn btn-primary btn-sm ms-auto" style="display:none; float: left;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div> -->
            </div>
        </div>
    </div>
</div>