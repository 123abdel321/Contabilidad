<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtroCartera">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCartera" aria-expanded="false" aria-controls="collapseCartera">
                Filtros de cartera
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseCartera" class="accordion-collapse collapse show" aria-labelledby="filtroCartera" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="carteraInformeForm" style="margin-top: 10px;">
                    <div class="row">
                        <div class="form-group col-md">
                            <label for="exampleFormControlSelect1">Cuenta</label>
                            <select name="id_cuenta" id="id_cuenta" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="form-group col-md">
                            <label for="exampleFormControlSelect1">Nit</label>
                            <select class="form-control form-control-sm" name="id_nit" id="id_nit">
                            </select>
                        </div>
                        <div class="form-group col-md">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_desde" id="fecha_desde" class="form-control form-control-sm" type="date" require>
                        </div>
                        <div class="form-group col-md">
                            <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                            <input name="fecha_hasta" id="fecha_hasta" class="form-control form-control-sm" type="date" require>
                        </div>
                    </div>  
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarCartera" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarCarteraLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                    <span id="descargarExcelCartera" class="btn badge bg-gradient-success" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXPORTAR</b>
                    </span>
                    <span id="descargarExcelCarteraDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXPORTAR</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="descargarPdfCarteraDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad;" >
                        <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">EXPORTAR</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                </div>
                <!-- <div class="col-md">
                    <button class="btn btn-primary btn-sm ms-auto" id="generarCartera">Filtrar</button>
                    <button id="generarCarteraLoading" class="btn btn-primary btn-sm ms-auto" style="display:none; float: left;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div> -->
            </div>
        </div>
    </div>
</div>