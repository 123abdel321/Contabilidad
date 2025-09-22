<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExogenaInforme" aria-expanded="false" aria-controls="collapseExogenaInforme">
                Filtros medios magneticos
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseExogenaInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="exogenaFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">

                        <div class="form-group col-12 col-sm-3 col-md-3">
                        <label for="id_year_exogena" class="form-control-label">Año</label>
                            <select name="id_year_exogena" id="id_year_exogena" class="form-control form-control-sm" required>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="id_formato_exogena" class="form-control-label">Formato</label>
                            <select name="id_formato_exogena" id="id_formato_exogena" class="form-control form-control-sm" required>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="id_concepto_exogena" class="form-control-label">Concepto</label>
                            <select name="id_concepto_exogena" id="id_concepto_exogena" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-3 col-md-3">
                            <label for="id_nit_exogena" class="form-control-label">Nit</label>
                            <select name="id_nit_exogena" id="id_nit_exogena" class="form-control form-control-sm">
                            </select>
                        </div>

                    </div>  
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarExogena" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-gold" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarExogenaLoading" class="badge bg-gradient-info btn-bg-gold-loading" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 13px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>