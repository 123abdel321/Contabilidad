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
                        <div class="col-md-7">
                            <div class="border-dashed border-2 rounded p-2 bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="far fa-file-excel text-success me-2"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label for="file" class="form-label fw-semibold text-dark small mb-1">Seleccionar archivo Excel</label>
                                        <input class="form-control form-control-sm" id="file" name="file" type="file" accept=".xlsx,.xls" required>
                                        <div class="invalid-feedback small">
                                            Selecciona un archivo Excel válido.
                                        </div>
                                        <p class="text-muted x-small mb-0 mt-1">Formatos: .xlsx, .xls (Máx. 10MB)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-5">
                            <div class="d-flex flex-wrap gap-2">

                                <button id="descargarPlantillaProductos" class="btn btn-danger btn-sm px-3">
                                    <i class="fas fa-download me-1"></i>
                                    <span class="small">Descargar Excel</span>
                                </button>

                                <button id="cargarPlantillaProductos" class="btn btn-success btn-sm px-3">
                                    <i class="far fa-file-excel me-1"></i>
                                    <span class="small">Cargar plantilla</span>
                                </button>
                                
                                <button id="importarProductos" href="javascript:void(0)" class="btn btn-primary btn-sm px-3" style="display: none;">
                                    <i class="fas fa-upload me-1"></i>
                                    <span class="small">Importar productos</span>
                                </button>
                                
                                <button id="importarProductosLoading" class="btn btn-primary btn-sm px-3" style="display:none;" disabled>
                                    <i class="fas fa-spinner fa-spin me-1"></i>
                                    <span class="small">Procesando</span>
                                </button>
                            </div>
                            
                            <!-- Estado de carga compacto -->
                            <div class="mt-2" id="uploadStatus" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 4px;">
                                        <div id="uploadProgress" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small id="progressText" class="text-muted x-small">0%</small>
                                </div>
                            </div>
                        </div>
                    </div>
                
                </form>
            </div>
        </div>
    </div>
</div>