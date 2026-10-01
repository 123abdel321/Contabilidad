<div class="modal fade" id="nitDetailModal" tabindex="-1" aria-labelledby="nitDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Encabezado -->
            <div class="modal-header bg-primary bg-gradient text-white border-0 px-4 py-3 align-items-start">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user"></i>
                        <h5 class="modal-title mb-0 fw-bold" id="nitDetailModalLabel">Detalle del cliente</h5>
                    </div>
                    <small class="text-white text-opacity-75">Información del cliente</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body bg-white p-3">

                <!-- Cargando -->
                <div id="nitDetailLoading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                    <div class="text-muted small">Cargando información...</div>
                </div>

                <div id="nitDetailContent" style="display: none;">

                    <!-- Tarjeta de perfil -->
                    <div class="card border border-primary-subtle bg-primary-subtle bg-opacity-25 rounded-4 shadow-sm mb-3">
                        <div class="card-body p-3">

                            <div class="d-flex align-items-center gap-3 mb-3">

                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <img id="nitDetailImagen" src="" alt="Imagen del cliente"
                                         class="rounded-circle border border-4 border-primary-subtle shadow-sm"
                                         style="width: 88px; height: 88px; object-fit: cover; display: none;">
                                    <div id="nitDetailImagenDefault"
                                         class="rounded-circle bg-primary text-white border border-4 border-primary-subtle shadow-sm d-flex align-items-center justify-content-center"
                                         style="width: 88px; height: 88px;">
                                        <i class="fas fa-building fa-2x"></i>
                                    </div>
                                </div>

                                <!-- Nombre y documento -->
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                        <h4 id="nitDetailNombre" class="mb-0 fw-bold text-dark text-break">-</h4>
                                        <span id="nitDetailTipoContribuyente" class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">-</span>
                                    </div>

                                    <div class="d-flex align-items-center flex-wrap gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="d-inline-flex rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                                <i class="fas fa-id-card fa-fw"></i>
                                            </span>
                                            <span class="text-secondary fw-semibold">
                                                <span id="nitDetailTipoDocumento">NIT</span>:
                                            </span>
                                            <strong id="nitDetailNumeroDocumento" class="text-dark">-</strong>
                                        </div>

                                        <div class="vr d-none d-sm-block"></div>

                                        <div id="nitDetailEstado" class="text-success fw-semibold">
                                            <i class="fas fa-circle me-1" style="font-size: 10px;"></i>
                                            Cliente registrado
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contacto -->
                            <div class="bg-white border rounded-3 px-3 py-2">
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 gap-md-3">

                                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-envelope fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Email:</small>
                                            <span id="nitDetailEmail" class="fw-semibold text-dark small text-break">-</span>
                                        </div>
                                    </div>

                                    <div class="vr d-none d-md-block"></div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-phone fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Teléfono:</small>
                                            <span id="nitDetailTelefono" class="fw-semibold text-dark small">-</span>
                                        </div>
                                    </div>

                                    <div class="vr d-none d-md-block"></div>

                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-map-marker-alt fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Ciudad:</small>
                                            <span id="nitDetailCiudad" class="fw-semibold text-dark small">-</span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Información general -->
                    <div class="card border border-primary-subtle rounded-3 shadow-sm mb-3 overflow-hidden">
                        <div class="card-header bg-primary-subtle border-0 px-3 py-2">
                            <div class="d-flex align-items-center gap-2 text-primary-emphasis">
                                <i class="fas fa-user-circle"></i>
                                <span class="fw-bold">Información general</span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="row g-2">

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-id-card fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Tipo de documento:</small>
                                            <span id="nitDetailTipoDocumentoCompleto" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-hashtag fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Número de documento:</small>
                                            <span id="nitDetailNumeroDocumento2" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-user fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Tipo de contribuyente:</small>
                                            <span id="nitDetailContribuyente2" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-building fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Razón social:</small>
                                            <span id="nitDetailRazonSocial" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-user fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Primer nombre:</small>
                                            <span id="nitDetailPrimerNombre" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-user fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Otros nombres:</small>
                                            <span id="nitDetailOtrosNombres" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-user fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Primer apellido:</small>
                                            <span id="nitDetailPrimerApellido" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2 h-100">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-user fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Segundo apellido:</small>
                                            <span id="nitDetailSegundoApellido" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2">
                                        <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                            <i class="fas fa-home fa-fw"></i>
                                        </span>
                                        <div>
                                            <small class="text-secondary d-block lh-sm">Dirección:</small>
                                            <span id="nitDetailDireccion" class="fw-semibold text-dark">-</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="card border border-warning-subtle rounded-3 shadow-sm overflow-hidden">
                        <div class="card-header bg-warning-subtle border-0 px-3 py-2">
                            <div class="d-flex align-items-center gap-2 text-primary-emphasis">
                                <i class="fas fa-comment-alt text-warning-emphasis"></i>
                                <span class="fw-bold">Observaciones</span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="d-flex align-items-center gap-3 bg-body-tertiary border rounded-3 px-3 py-2">
                                <span class="d-inline-flex flex-shrink-0 rounded-2 bg-primary-subtle text-primary p-2 lh-1">
                                    <i class="fas fa-file-alt fa-fw"></i>
                                </span>
                                <div id="nitDetailObservaciones" class="text-dark small">
                                    Sin observaciones
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Pie -->
            <div class="modal-footer bg-body-tertiary border-0 px-4 py-3">
                <button type="button" class="btn btn-danger bg-gradient rounded-3 px-4 shadow-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>