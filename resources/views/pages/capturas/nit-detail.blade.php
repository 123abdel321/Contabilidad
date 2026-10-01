<div class="modal fade" id="nitDetailModal" tabindex="-1" aria-labelledby="nitDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-primary text-white border-0 px-3 py-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-tie"></i>
                        <h5 class="modal-title mb-0" id="nitDetailModalLabel">Detalle del cliente</h5>
                    </div>
                    <small class="text-white-50">Información del cliente</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">

                <div id="nitDetailLoading" class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                    <div class="text-muted small">Cargando información...</div>
                </div>

                <div id="nitDetailContent" style="display: none;">

                    <div class="px-3 py-3 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <img id="nitDetailImagen" src="" alt="Imagen del cliente" class="rounded-circle border" style="width: 65px; height: 65px; object-fit: cover; display: none;">
                                <div id="nitDetailImagenDefault" class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 65px; height: 65px;">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center flex-wrap gap-2">
                                    <h5 id="nitDetailNombre" class="mb-0 fw-bold text-dark">-</h5>
                                    <span id="nitDetailTipoContribuyente" class="badge bg-success-subtle text-success">-</span>
                                </div>

                                <div class="text-muted small">
                                    <i class="fas fa-id-card me-1"></i>
                                    <span id="nitDetailTipoDocumento">NIT</span>
                                    <strong id="nitDetailNumeroDocumento" class="text-dark ms-1">-</strong>
                                </div>

                                <div id="nitDetailEstado" class="text-success small">
                                    <i class="fas fa-circle me-1" style="font-size: 6px;"></i>
                                    Cliente registrado
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-3 py-3">

                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-address-card text-primary me-2"></i>
                            <span class="fw-bold small">Información general</span>
                        </div>

                        <div class="row g-0 border rounded">

                            <div class="col-md-6 border-bottom border-end-md px-3 py-2">
                                <small class="text-muted d-block">Tipo de documento</small>
                                <span id="nitDetailTipoDocumentoCompleto" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-md-6 border-bottom px-3 py-2">
                                <small class="text-muted d-block">Número de documento</small>
                                <span id="nitDetailNumeroDocumento2" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-md-6 border-bottom border-end-md px-3 py-2">
                                <small class="text-muted d-block">Tipo de contribuyente</small>
                                <span id="nitDetailContribuyente2" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-md-6 border-bottom px-3 py-2">
                                <small class="text-muted d-block">Razón social</small>
                                <span id="nitDetailRazonSocial" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-md-6 border-bottom border-end-md px-3 py-2">
                                <small class="text-muted d-block">Primer nombre</small>
                                <span id="nitDetailPrimerNombre" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-md-6 border-bottom px-3 py-2">
                                <small class="text-muted d-block">Otros nombres</small>
                                <span id="nitDetailOtrosNombres" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-md-6 border-bottom border-end-md px-3 py-2">
                                <small class="text-muted d-block">Primer apellido</small>
                                <span id="nitDetailPrimerApellido" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-md-6 border-bottom px-3 py-2">
                                <small class="text-muted d-block">Segundo apellido</small>
                                <span id="nitDetailSegundoApellido" class="fw-semibold small">-</span>
                            </div>

                            <div class="col-12 px-3 py-2">
                                <small class="text-muted d-block">Dirección</small>
                                <span id="nitDetailDireccion" class="fw-semibold small">-</span>
                            </div>

                        </div>


                        <div class="d-flex align-items-center mt-3 mb-2">
                            <i class="fas fa-address-book text-primary me-2"></i>
                            <span class="fw-bold small">Información de contacto</span>
                        </div>

                        <div class="row align-items-center">

                            <div class="col-md-5">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Email</small>
                                        <span id="nitDetailEmail" class="fw-semibold small text-break">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Teléfono</small>
                                        <span id="nitDetailTelefono" class="fw-semibold small">-</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Ciudad</small>
                                        <span id="nitDetailCiudad" class="fw-semibold small">-</span>
                                    </div>
                                </div>
                            </div>

                        </div>


                        <div class="d-flex align-items-center mt-3 mb-2">
                            <i class="fas fa-comment-alt text-primary me-2"></i>
                            <span class="fw-bold small">Observaciones</span>
                        </div>

                        <div id="nitDetailObservaciones" class="bg-light rounded px-3 py-2 text-muted small">
                            Sin observaciones
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer bg-light border-0 px-3 py-2">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>