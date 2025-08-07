<div class="accordion" id="accordionRental">
    <div class="accordion-item">
    <h5 class="accordion-header" id="headingOne">
              <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Filtros de informe estado comprobante
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
              </button>
            </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosEstadoComprobante" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="estadoComprobanteInformeForm" style="margin-top: 10px;">
                    <div class="row">
                        <input name="id_estado_comprobante_cargado" id="id_estado_comprobante_cargado" class="form-control form-control-sm" type="text" style="display: none;">
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Año</label>
                            <select name="year_estado_comprobante" id="year_estado_comprobante" class="form-control form-control-sm">
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Mes</label>
                            <select name="month_estado_comprobante" id="month_estado_comprobante" class="form-control form-control-sm">
                                <option value="">Seleccione un mes</option>
                                <option value="01">ENERO</option>
                                <option value="02">FEBRERO</option>
                                <option value="03">MARZO</option>
                                <option value="04">ABRIL</option>
                                <option value="05">MAYO</option>
                                <option value="06">JUNIO</option>
                                <option value="07">JULIO</option>
                                <option value="08">AGOSTO</option>
                                <option value="09">SEPTIEMBRE</option>
                                <option value="10">OCTUBRE</option>
                                <option value="11">NOVIEMBRE</option>
                                <option value="12">DICIEMBRE</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarEstadoComprobante" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarEstadoComprobanteLoading" class="badge bg-gradient-primary" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 13px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <!-- <span id="descargarExcelEstadoComprobante" class="btn badge bg-gradient-success btn-bg-success" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelEstadoComprobanteDisabled" class="badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span> -->
                    <!-- BOTON ULTIMO INFORME -->
                    <span id="generarEstadoComprobanteUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarEstadoComprobanteUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>