<div class="accordion" id="accordionRental">
    <div class="accordion-item border-0 shadow-sm">
        <h5 class="accordion-header" id="filtrosBalance">
            <button class="accordion-button border-bottom font-weight-bold text-dark bg-light collapsed py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                <div class="d-flex align-items-center w-100">
                    <i class="fas fa-file-import me-2 text-primary fs-6"></i>
                    <span class="fw-bold fs-6">Importador de Productos</span>
                </div>
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosBalance" data-bs-parent="#accordionRental">
            <div class="accordion-body p-3 bg-white">
                
                <!-- Sección de carga compacta -->
                <form id="form-importador-productos" enctype="multipart/form-data" class="needs-validation" novalidate>
                    {{ csrf_field() }}
                    
                    <div class="row g-2 align-items-center">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="border-dashed border-2 rounded p-2 bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="far fa-file-excel text-success me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="importador_productos" class="form-label fw-semibold text-dark small mb-1">Seleccionar archivo Excel</label>
                                        <input class="form-control form-control-sm" id="importador_productos" name="importador_productos" type="file" accept=".xlsx,.xls" required>
                                        <div class="invalid-feedback small">
                                            Selecciona un archivo Excel válido.
                                        </div>
                                        <p class="text-muted x-small mb-0 mt-1">Formatos: .xlsx, .xls (Máx. 10MB)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="d-flex flex-wrap gap-2">

                                <div class="row">
                                    <div class="col-12 d-flex align-items-center">
                                        <div style="min-width: 190px;">
                                            <button type="button" class="btn btn-primary btn-sm btn-bg-danger" id="descargarPlantillaProductos" style="margin-bottom: 5px !important; width: 190px;">
                                                <i class="fas fa-download" style="margin-right: 5px; font-size: 15px;"></i>&nbsp;
                                                Descargar Excel
                                            </button>
                                        </div>
                                        <div class="ms-3">
                                            <p class="mb-0 text-muted small">
                                                <strong>Paso 1:</strong> Obtén el formato oficial para importar tus productos.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex align-items-center">
                                        <div style="min-width: 190px;">
                                            <button type="button" class="btn btn-primary btn-sm btn-bg-excel" id="cargarPlantillaProductos" style="margin-bottom: 5px !important; width: 190px;" disabled>
                                                <i class="far fa-file-excel" style="margin-right: 5px; font-size: 15px;"></i>&nbsp;
                                                Cargar plantilla
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm btn-bg-excel-loading" id="cargarPlantillaProductosLoading" style="opacity: 1; box-shadow: none; display: none; width: 190px; margin-bottom: 5px !important;" disabled>
                                                <b style="opacity: 0.3; text-transform: capitalize;">Cargar plantilla</b>
                                                <i style="position: absolute; color: white; font-size: 15px; margin-left: -48px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
                                            </button>
                                        </div>
                                        <div class="ms-3">
                                            <p class="mb-0 text-muted small">
                                                <strong>Paso 2:</strong> Carga el archivo con los datos de tus productos.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex align-items-center">
                                        <div style="min-width: 190px;"> 

                                            <button type="button" class="btn btn-primary btn-sm btn-bg-info" id="importarProductos" style="float: inline-end; width: 190px; margin-bottom: 5px !important;">
                                                <i class="fas fa-upload" style="margin-right: 5px; font-size: 15px;"></i>&nbsp;
                                                Cargar productos
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm btn-bg-info-loading" id="importarProductosLoading" style="opacity: 1; box-shadow: none; display: none; width: 190px; margin-bottom: 5px !important;" disabled>
                                                <b style="opacity: 0.3; text-transform: capitalize;">Cargar productos</b>
                                                <i style="position: absolute; color: white; font-size: 15px; margin-left: -55px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
                                            </button>

                                        </div>
                                        <div class="ms-3">
                                            <p class="mb-0 text-muted small">
                                                <strong>Paso 3:</strong> Inicia el proceso de importación de productos.
                                            </p>
                                        </div>
                                    </div>

                                </div>

                                <button id="importarProductosLoading" class="btn btn-primary btn-sm px-3" style="display:none;" disabled>
                                    <i class="fas fa-spinner fa-spin me-1"></i>
                                    <span class="small">Procesando</span>
                                </button>

                            </div>
                        
                        </div>
                    </div>
                
                </form>
            </div>
        </div>
    </div>
</div>