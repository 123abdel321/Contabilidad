<style>
    /* Solo lo que Bootstrap no trae. Todo va scoped al modal para no tocar el resto del ERP */
    /* Chips de atajos: el tema global agranda .btn, por eso se fuerza el tamaño */
    #novedadesGeneralesFormModal .pf-chips .btn {
        display: inline-flex !important;
        align-items: center;
        width: auto !important;
        min-width: 0 !important;
        height: 22px !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 9px !important;
        font-size: 11px !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        letter-spacing: 0 !important;
        text-transform: none !important;
        box-shadow: none !important;
        transform: none !important;
        border-radius: 999px !important;
    }
    #novedadesGeneralesFormModal .pf-chips { gap: 6px !important; }
    #novedadesGeneralesFormModal .pf-section {
        font-size: .72rem;
        letter-spacing: .06em;
    }
    /* Aviso solo cuando ningun campo dinamico esta visible */
    #novedadesGeneralesFormModal .pf-empty { display: none; }
    #novedadesGeneralesFormModal .novedad-detalle:not(:has(> [id^="div-"]:not([style*="display: none"]))) .pf-empty { display: block; }
    #novedadesGeneralesFormModal .select2-container { width: 100% !important; }
</style>

<div class="modal fade" id="novedadesGeneralesFormModal" tabindex="-1" role="dialog"
     aria-labelledby="novedadesGeneralesFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="textCreateNovedadesGenerales">
                    <i class="fas fa-plus-circle text-primary me-2"></i>Agregar novedad general
                </h5>
                <h5 class="modal-title" id="textEditarNovedadesGenerales" style="display:none;">
                    <i class="fas fa-edit text-primary me-2"></i>Editar novedad general
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <form id="NovedadesGeneralesForm" class="novedades-form needs-invalidation" novalidate>
                    <input type="hidden" name="id" id="id_novedades_generales_up">

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="id_empleado_novedades_generales" class="form-label fw-semibold">
                                Empleado <span class="text-danger">*</span>
                            </label>
                            <select name="id_empleado" id="id_empleado_novedades_generales" class="form-select" required></select>
                            <div class="invalid-feedback">Selecciona un empleado.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="id_concepto_novedades_generales" class="form-label fw-semibold">
                                Concepto <span class="text-danger">*</span>
                            </label>
                            <select name="id_concepto" id="id_concepto_novedades_generales" class="form-select" required></select>
                            <div class="invalid-feedback">Selecciona un concepto.</div>
                        </div>
                    </div>

                    <!-- DETALLE: campos dinamicos segun concepto -->
                    <h6 class="pf-section text-uppercase text-primary fw-bold d-flex align-items-center gap-2 mb-3">
                        Detalle de la novedad <hr class="flex-grow-1 m-0 opacity-25">
                    </h6>
                    <div class="row g-3 novedad-detalle">

                        <div class="col-12 pf-empty">
                            <div class="alert alert-light border border-dashed text-center text-muted small mb-0">
                                <i class="fas fa-info-circle me-1"></i>Selecciona un concepto para ver los campos a diligenciar.
                            </div>
                        </div>

                        <!-- UNIDADES -->
                        <div id="div-unidades_novedades_generales" class="col-12 col-sm-6 col-md-4" style="display:none;">
                            <label id="text-unidades_novedades_generales" for="unidades_novedades_generales" class="form-label fw-semibold">Unidades</label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                   name="unidades" id="unidades_novedades_generales" value="0">
                        </div>

                        <!-- VALOR -->
                        <div id="div-valor_novedades_generales" class="col-12 col-sm-6 col-md-4" style="display:none;">
                            <label for="valor_novedades_generales" class="form-label fw-semibold">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control"
                                       name="valor" id="valor_novedades_generales" value="0">
                            </div>
                        </div>

                        <!-- PORCENTAJE -->
                        <div id="div-porcentaje_novedades_generales" class="col-12 col-sm-6 col-md-4" style="display:none;">
                            <label for="porcentaje_novedades_generales" class="form-label fw-semibold">Porcentaje</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control"
                                       name="porcentaje" id="porcentaje_novedades_generales" value="0">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>

                        <!-- FECHA DESDE -->
                        <div id="div-fecha_desde_novedades_generales" class="col-12 col-sm-6" style="display:none;">
                            <label for="fecha_desde_novedades_generales" class="form-label fw-semibold">
                                <i class="far fa-calendar-alt text-muted me-1"></i>Fecha desde <span class="text-danger">*</span>
                            </label>
                            <input name="fecha_desde" id="fecha_desde_novedades_generales" class="form-control" type="date">
                            <div class="pf-chips d-flex flex-wrap gap-1 mt-2">
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-set-hoy" data-target="fecha_desde_novedades_generales">Hoy</button>
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-set-ayer" data-target="fecha_desde_novedades_generales">Ayer</button>
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-set-lunes" data-target="fecha_desde_novedades_generales">Lunes</button>
                            </div>
                        </div>

                        <!-- FECHA HASTA -->
                        <div id="div-fecha_hasta_novedades_generales" class="col-12 col-sm-6" style="display:none;">
                            <label for="fecha_hasta_novedades_generales" class="form-label fw-semibold">
                                <i class="far fa-calendar-check text-muted me-1"></i>Fecha hasta <span class="text-danger">*</span>
                            </label>
                            <input name="fecha_hasta" id="fecha_hasta_novedades_generales" class="form-control" type="date">
                            <div class="pf-chips d-flex flex-wrap gap-1 mt-2">
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-copy-fecha"
                                        data-from="fecha_desde_novedades_generales" data-to="fecha_hasta_novedades_generales">Igual a desde</button>
                            </div>
                        </div>

                        <!-- HORA DESDE -->
                        <div id="div-hora_desde_novedades_generales" class="col-12 col-sm-6" style="display:none;">
                            <label for="hora_desde_novedades_generales" class="form-label fw-semibold">
                                <i class="far fa-clock text-muted me-1"></i>Hora desde <span class="text-danger">*</span>
                            </label>
                            <input name="hora_desde" id="hora_desde_novedades_generales" class="form-control" type="time">
                            <div class="pf-chips d-flex flex-wrap gap-1 mt-2">
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-hora-set" data-target="hora_desde_novedades_generales" data-hora="18:00">18:00</button>
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-hora-set" data-target="hora_desde_novedades_generales" data-hora="19:00">19:00</button>
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-hora-set" data-target="hora_desde_novedades_generales" data-hora="22:00">22:00</button>
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-hora-now" data-target="hora_desde_novedades_generales">Ahora</button>
                            </div>
                        </div>

                        <!-- HORA HASTA -->
                        <div id="div-hora_hasta_novedades_generales" class="col-12 col-sm-6" style="display:none;">
                            <label for="hora_hasta_novedades_generales" class="form-label fw-semibold">
                                <i class="fas fa-clock text-muted me-1"></i>Hora hasta <span class="text-danger">*</span>
                            </label>
                            <input name="hora_hasta" id="hora_hasta_novedades_generales" class="form-control" type="time">
                            <div class="pf-chips d-flex flex-wrap gap-1 mt-2">
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-add-hora" data-target="hora_hasta_novedades_generales" data-min="60">+1h</button>
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-add-hora" data-target="hora_hasta_novedades_generales" data-min="120">+2h</button>
                                <button type="button" class="btn btn-outline-primary rounded-pill btn-copy-hora"
                                        data-from="hora_desde_novedades_generales" data-to="hora_hasta_novedades_generales">Igual a desde</button>
                            </div>
                            <div id="horasResumen" class="mt-2 small fw-semibold text-success" style="display:none;">
                                <i class="fas fa-stopwatch me-1"></i>Duración calculada:
                                <span id="horasResumenValor">0.00</span> h
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveNovedadesGenerales" type="button" class="btn bg-gradient-success btn-sm">
                    Guardar <span class="kbd-hint"><kbd>Ctrl</kbd>+<kbd>S</kbd></span>
                </button>
                <button id="updateNovedadesGenerales" type="button" class="btn bg-gradient-success btn-sm" style="display:none;">
                    Actualizar <span class="kbd-hint"><kbd>Ctrl</kbd>+<kbd>S</kbd></span>
                </button>
                <button id="saveNovedadesGeneralesLoading" class="btn btn-success btn-sm ms-auto" style="display:none;" disabled>
                    <span class="spinner-border spinner-border-sm"></span> Cargando...
                </button>
            </div>
        </div>
    </div>
</div>