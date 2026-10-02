<div class="modal fade" id="nitDetailModal" tabindex="-1" aria-labelledby="nitDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 680px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Encabezado -->
            <div class="modal-header bg-primary bg-gradient text-white border-0 px-3 py-2 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-user"></i>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="nitDetailModalLabel">Detalle del cliente</h6>
                        <small class="text-white text-opacity-75" style="font-size: 11px;">Información del cliente</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body bg-body-tertiary p-2">

                <!-- Cargando -->
                <div id="nitDetailLoading" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                    <div class="text-muted small">Cargando información...</div>
                </div>

                <div id="nitDetailContent" style="display: none;">

                    <!-- Tarjeta de perfil -->
                    <div class="card border border-primary-subtle bg-primary-subtle bg-opacity-25 rounded-3 shadow-sm mb-2">
                        <div class="card-body p-2">

                            <div class="d-flex align-items-center gap-2 mb-2">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <img id="nitDetailImagen" src="" alt="Imagen del cliente"
                                         class="rounded-circle border border-3 border-primary-subtle shadow-sm"
                                         style="width: 64px; height: 64px; object-fit: cover; display: none;">
                                    <div id="nitDetailImagenDefault"
                                         class="rounded-circle bg-primary text-white border border-3 border-primary-subtle shadow-sm d-flex align-items-center justify-content-center"
                                         style="width: 64px; height: 64px;">
                                        <i class="fas fa-building fa-2x"></i>
                                    </div>
                                </div>

                                <!-- Nombre y documento -->
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                        <h6 id="nitDetailNombre" class="mb-0 fw-bold text-dark text-break">-</h6>
                                        <span id="nitDetailTipoContribuyente" class="badge rounded-pill bg-success-subtle text-success-emphasis px-2 py-1" style="font-size: 10px;">-</span>
                                    </div>

                                    <div class="d-flex align-items-center flex-wrap gap-2 small">
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="fas fa-id-card text-primary"></i>
                                            <span class="text-secondary">
                                                <span id="nitDetailTipoDocumento">NIT</span>:
                                            </span>
                                            <strong id="nitDetailNumeroDocumento" class="text-dark">-</strong>
                                        </div>

                                        <div class="vr d-none d-sm-block"></div>

                                        <div id="nitDetailEstado" class="text-success fw-semibold">
                                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>
                                            Cliente registrado
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contacto -->
                            <div class="bg-white border rounded-3 px-2 py-1">
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-1 gap-md-2">

                                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                                        <i class="fas fa-envelope text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Email:</small>
                                            <span id="nitDetailEmail" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>

                                    <div class="vr d-none d-md-block"></div>

                                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                                        <i class="fas fa-phone text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Teléfono:</small>
                                            <span id="nitDetailTelefono" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>

                                    <div class="vr d-none d-md-block"></div>

                                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                                        <i class="fas fa-map-marker-alt text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Ciudad:</small>
                                            <span id="nitDetailCiudad" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Información general -->
                    <div class="card border border-primary-subtle rounded-3 shadow-sm mb-2 overflow-hidden">
                        <div class="card-header bg-primary-subtle border-0 px-2 py-1">
                            <div class="d-flex align-items-center gap-2 text-primary-emphasis">
                                <i class="fas fa-user-circle small"></i>
                                <span class="fw-bold" style="font-size: 12px;">Información general</span>
                            </div>
                        </div>

                        <div class="card-body p-2">
                            <div class="row g-1">

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-id-card text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Tipo de documento:</small>
                                            <span id="nitDetailTipoDocumentoCompleto" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-hashtag text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">N° documento:</small>
                                            <span id="nitDetailNumeroDocumento2" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-user text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Contribuyente:</small>
                                            <span id="nitDetailContribuyente2" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-building text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Razón social:</small>
                                            <span id="nitDetailRazonSocial" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-user text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Primer nombre:</small>
                                            <span id="nitDetailPrimerNombre" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-user text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Otros nombres:</small>
                                            <span id="nitDetailOtrosNombres" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-user text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Primer apellido:</small>
                                            <span id="nitDetailPrimerApellido" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1 h-100">
                                        <i class="fas fa-user text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Segundo apellido:</small>
                                            <span id="nitDetailSegundoApellido" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1">
                                        <i class="fas fa-home text-primary small"></i>
                                        <div class="overflow-hidden">
                                            <small class="text-secondary d-block lh-sm" style="font-size: 10px;">Dirección:</small>
                                            <span id="nitDetailDireccion" class="fw-semibold text-dark text-truncate d-block" style="font-size: 12px;">-</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="card border border-warning-subtle rounded-3 shadow-sm overflow-hidden">
                        <div class="card-header bg-warning-subtle border-0 px-2 py-1">
                            <div class="d-flex align-items-center gap-2 text-primary-emphasis">
                                <i class="fas fa-comment-alt text-warning-emphasis small"></i>
                                <span class="fw-bold" style="font-size: 12px;">Observaciones</span>
                            </div>
                        </div>

                        <div class="card-body p-2">
                            <div class="d-flex align-items-center gap-2 bg-body-tertiary border rounded-2 px-2 py-1">
                                <i class="fas fa-file-alt text-primary small"></i>
                                <div id="nitDetailObservaciones" class="text-dark" style="font-size: 12px;">
                                    Sin observaciones
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Pie -->
            <div class="modal-footer bg-body-tertiary border-0 px-3 py-2">
                <button type="button" class="btn btn-danger bg-gradient btn-sm rounded-3 px-3 shadow-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>