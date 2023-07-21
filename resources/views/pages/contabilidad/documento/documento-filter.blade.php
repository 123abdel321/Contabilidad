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

                        <input name="editing_documento" id="editing_documento" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label>Comprobante</label>
                            <select name="id_comprobante" id="id_comprobante" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                <option value="">Ninguno</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label for="example-text-input" class="form-control-label">Fecha</label>
                            <input name="fecha_manual" id="fecha_manual" class="form-control form-control-sm" type="date">
                        </div>

                        <div class="form-group col-6 col-md-4 col-sm-4">
                            <label for="example-text-input" class="form-control-label">Consecutivo</label>
                            <input type="text" class="form-control form-control-sm" name="consecutivo" id="consecutivo">
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