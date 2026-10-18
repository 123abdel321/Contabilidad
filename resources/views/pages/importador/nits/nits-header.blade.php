<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtrosNitsImport">
            <button class="accordion-button border-bottom font-weight-bold text-dark bg-light collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                <div class="d-flex align-items-center w-100">
                    <i class="fas fa-id-card me-2 text-primary fs-6"></i>
                    <span class="fw-bold fs-6">Importador de Cédulas / NITs</span>
                </div>
            </button>
        </h5>

        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosNitsImport" data-bs-parent="#accordionRental">
            <div class="accordion-body p-3 bg-white">

                <form id="form-importador-nits" enctype="multipart/form-data" class="needs-validation" novalidate>
                    {{ csrf_field() }}

                    <div class="row g-2 align-items-center">
                        {{-- COLUMNA IZQUIERDA: archivo + checkbox --}}
                        <div class="col-12 col-lg-6">
                            <div class="border-dashed border-2 rounded p-2 bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="far fa-file-excel text-success me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="file_import_nits" class="form-label fw-semibold text-dark small mb-1">Seleccionar archivo Excel</label>
                                        <input class="form-control form-control-sm" id="file_import_nits" name="file_import_nits" type="file" accept=".xlsx,.xls" required>
                                        <div class="invalid-feedback small">Selecciona un archivo Excel válido.</div>
                                        <p class="text-muted x-small mb-0 mt-1">Formatos: .xlsx, .xls · Máx. 500 registros</p>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="actualizar_valores" id="actualizar_valores">
                                    <label class="form-check-label fw-semibold text-dark small" for="actualizar_valores">
                                        <i class="fas fa-sync-alt text-warning me-1"></i>
                                        Actualizar datos de NITs existentes
                                    </label>
                                    <div class="text-muted x-small mt-1">
                                        <b>Activado:</b> se actualizan los NITs existentes.<br>
                                        <b>Desactivado:</b> se crean NITs nuevos (los duplicados se marcan como error).
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- COLUMNA DERECHA: pasos 1-2-3 --}}
                        <div class="col-12 col-lg-6">
                            <div class="row g-2">

                                {{-- PASO 1 --}}
                                <div class="col-12 d-flex align-items-center">
                                    <div style="min-width: 200px;">
                                        <button type="button" class="btn btn-primary btn-sm btn-bg-danger" id="descargarPlantillaNits" style="width: 200px;">
                                            <i class="fas fa-download" style="margin-right: 5px; font-size: 15px;"></i>&nbsp;
                                            Descargar Excel
                                        </button>
                                    </div>
                                    <div class="ms-3">
                                        <p class="mb-0 text-muted small"><strong>Paso 1:</strong> Obtén el formato oficial para importar tus cédulas/NITs.</p>
                                    </div>
                                </div>

                                {{-- PASO 2 --}}
                                <div class="col-12 d-flex align-items-center">
                                    <div style="min-width: 200px;">
                                        <button type="button" class="btn btn-primary btn-sm btn-bg-excel" id="cargarPlantillaNits" style="width: 200px;" disabled>
                                            <i class="far fa-file-excel" style="margin-right: 5px; font-size: 15px;"></i>&nbsp;
                                            Cargar plantilla
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm btn-bg-excel-loading" id="cargarPlantillaNitsLoading" style="display: none; width: 200px;" disabled>
                                            <b style="opacity: 0.3; text-transform: capitalize;">Cargar plantilla</b>
                                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -48px;" class="fas fa-spinner fa-spin"></i>
                                        </button>
                                    </div>
                                    <div class="ms-3">
                                        <p class="mb-0 text-muted small"><strong>Paso 2:</strong> Carga el archivo con los datos de tus cédulas/NITs.</p>
                                    </div>
                                </div>

                                {{-- PASO 3 --}}
                                <div class="col-12 d-flex align-items-center">
                                    <div style="min-width: 200px;">
                                        <button type="button" class="btn btn-primary btn-sm btn-bg-info" id="actualizarPlantillaNits" style="width: 200px;" disabled>
                                            <i class="fas fa-upload" style="margin-right: 5px; font-size: 15px;"></i>&nbsp;
                                            Cargar NITs
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm btn-bg-info-loading" id="actualizarPlantillaNitsLoading" style="display: none; width: 200px;" disabled>
                                            <b style="opacity: 0.3; text-transform: capitalize;">Cargar NITs</b>
                                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -55px;" class="fas fa-spinner fa-spin"></i>
                                        </button>
                                    </div>
                                    <div class="ms-3">
                                        <p class="mb-0 text-muted small"><strong>Paso 3:</strong> Inicia el proceso de creación/actualización de NITs.</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>