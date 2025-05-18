<div class="accordion" id="accordionRentalDocumentosGenerales">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocumentosGeneralesInforme" aria-expanded="false" aria-controls="collapseDocumentosGeneralesInforme">
                Filtros resumen cartera
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseDocumentosGeneralesInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="documentosGeneralesFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">
                        <input name="id_documento_general_cargado" id="id_documento_general_cargado" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-6 col-sm-3 col-md-4">
                            <label for="example-text-input" class="form-control-label">Fecha hasta<span style="color: red">*</span></label>
                            <input name="fecha_hasta_resumen_cartera" id="fecha_hasta_resumen_cartera" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-4">
                            <label for="example-text-input" class="form-control-label">Días mora<span style="color: red">*</span></label>
                            <input name="mora_resumen_cartera" id="mora_resumen_cartera" class="form-control form-control-sm" type="number" require>
                        </div>

                        @if ($ubicacion_maximoph)
                            <div class="form-group col-6 col-sm-2 col-md-2 row" style="margin-bottom: 0.1rem !important;">
                                <label for="example-text-input" class="form-control-label">Ubicaciones</label>
                                <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                    <input class="form-check-input" type="radio" name="ubicaciones_resumen_cartera" id="ubicaciones_resumen_cartera0" style="font-size: 11px;">
                                    <label class="form-check-label" for="ubicaciones_resumen_cartera0" style="font-size: 11px;">
                                        No
                                    </label>
                                </div>
                                <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                    <input class="form-check-input" type="radio" name="ubicaciones_resumen_cartera" id="ubicaciones_resumen_cartera1" style="font-size: 11px;" checked>
                                    <label class="form-check-label" for="ubicaciones_resumen_cartera1" style="font-size: 11px;">
                                        Si
                                    </label>
                                </div>
                            </div>
                        @endif


                        <!-- <div class="form-group col-6 col-sm-6 col-md-6">
                            <label>Cedula/Nit</label>
                            <select name="id_nit_resumen_cartera" id="id_nit_resumen_cartera" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div> -->

                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="resumenCarteraGenerales" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="resumenCarteraGeneralesLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>&nbsp;
                    <!-- BOTON EXCEL -->
                    <!-- <span id="descargarExcelResumenCartera" class="btn badge bg-gradient-dark btn-bg-excel" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                    </span>
                    <span id="descargarExcelResumenCarteraDisabled" class="badge bg-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>
                        <b style="vertical-align: text-top;">EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span> -->
                </div>
            </div>
        </div>
    </div>
</div>