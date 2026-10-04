<div class="modal fade" id="documentoArchivosModal" tabindex="-1" aria-labelledby="documentoArchivosModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <!-- Encabezado -->
            <div class="modal-header bg-primary bg-gradient text-white border-0 px-3 py-2 align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-paperclip"></i>

                    <div>
                        <h6 class="modal-title mb-0 fw-bold" id="documentoArchivosModalLabel">
                            Archivos adjuntos
                        </h6>

                        <small class="text-white text-opacity-75" style="font-size: 11px;">
                            Archivos asociados al documento
                        </small>
                    </div>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar">
                </button>
            </div>

            <!-- Cuerpo -->
            <div class="modal-body bg-body-tertiary p-2">

                <!-- Loading -->
                <div id="documentoArchivosLoading" class="text-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>

                    <div class="text-muted small">
                        Cargando archivos...
                    </div>
                </div>

                <!-- Sin archivos -->
                <div id="documentoArchivosEmpty" class="text-center py-5" style="display: none;">
                    <i class="fas fa-folder-open text-secondary fa-3x mb-3"></i>

                    <div class="fw-semibold text-dark">
                        No hay archivos adjuntos
                    </div>

                    <small class="text-muted">
                        Este documento no tiene archivos asociados.
                    </small>
                </div>

                <!-- Contenido -->
                <div id="documentoArchivosContent" style="display: none;">

                    <!-- Lista de archivos -->
                    <div id="documentoArchivosLista" class="row g-2 mb-2">
                    </div>

                    <!-- Visor -->
                    <div id="documentoArchivoViewer" class="card border rounded-3 shadow-sm overflow-hidden"
                        style="display: none;">

                        <div class="card-header bg-white border-bottom px-2 py-2">
                            <div class="d-flex align-items-center justify-content-between gap-2">

                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <i id="documentoArchivoViewerIcon" class="fas fa-file text-primary">
                                    </i>

                                    <span id="documentoArchivoViewerNombre" class="fw-semibold text-dark text-truncate"
                                        style="font-size: 12px;">
                                        -
                                    </span>
                                </div>

                                <a id="documentoArchivoViewerAbrir" href="#" target="_blank"
                                    class="btn btn-primary btn-sm rounded-2">
                                    <i class="fas fa-external-link-alt me-1"></i>
                                    Abrir
                                </a>

                            </div>
                        </div>

                        <div id="documentoArchivoViewerContent" style="height: 600px; background: #525659;">
                        </div>

                    </div>

                </div>

            </div>

            <!-- Pie -->
            <div class="modal-footer bg-body-tertiary border-0 px-3 py-2">

                <button type="button" class="btn btn-danger bg-gradient btn-sm rounded-3 px-3 shadow-sm"
                    data-bs-dismiss="modal">

                    <i class="fas fa-times me-1"></i>
                    Cerrar

                </button>

            </div>

        </div>
    </div>
</div>