<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDocumentoInforme" aria-expanded="false" aria-controls="collapseDocumentoInforme">
                Datos de captura
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseDocumentoInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="documentoFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label>Comprobante</label>
                            <select name="id_comprobante_documento" id="id_comprobante_documento" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                <option value="">Ninguno</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha</label>
                            <input name="fecha_manual_documento" id="fecha_manual_documento" class="form-control form-control-sm" type="date">
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Consecutivo</label>
                            <input type="text" class="form-control form-control-sm" name="consecutivo_documento" id="consecutivo_documento">
                        </div>
                        
                        <div class="form-group col-12 col-sm-4 col-md-3 row">
                            <div class="form-check col-12 col-md-12 col-sm-12">
                                <input class="form-check-input" type="radio" name="tipo_factura" id="tipo_factura1" style="margin-left: 5px;" checked>
                                <label class="form-check-label" for="tipo_factura1">
                                    Todas
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12">
                                <input class="form-check-input" type="radio" name="tipo_factura" id="tipo_factura2" style="margin-left: 5px;">
                                <label class="form-check-label" for="tipo_factura2">
                                    Anuladas
                                </label>
                            </div>
                        </div>
                    </div>  
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarDocumento" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarDocumentoLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>