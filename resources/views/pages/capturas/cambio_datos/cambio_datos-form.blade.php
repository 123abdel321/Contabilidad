<div class="modal fade" id="cambioDatosFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down">
        <div class="modal-content border-0">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i> Configurar Cambio de Datos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <!-- Tabs -->
                <ul class="nav nav-tabs" id="cambioDatosTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-13 nav-link active" id="tab-nits" data-bs-toggle="tab" data-bs-target="#pane-nits" type="button" role="tab">
                            <i class="fas fa-id-card me-1"></i> NITs
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-13 nav-link" id="tab-comprobantes" data-bs-toggle="tab" data-bs-target="#pane-comprobantes" type="button" role="tab">
                            <i class="fas fa-file-invoice me-1"></i> Comprobantes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-13 nav-link" id="tab-centros" data-bs-toggle="tab" data-bs-target="#pane-centros" type="button" role="tab">
                            <i class="fas fa-project-diagram me-1"></i> Centros de Costos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-13 nav-link" id="tab-cuentas" data-bs-toggle="tab" data-bs-target="#pane-cuentas" type="button" role="tab">
                            <i class="fas fa-wallet me-1"></i> Cuentas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-13 nav-link" id="tab-fechas" data-bs-toggle="tab" data-bs-target="#pane-fechas" type="button" role="tab">
                            <i class="fas fa-calendar-alt me-1"></i> Fechas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-13 nav-link" id="tab-consecutivo" data-bs-toggle="tab" data-bs-target="#pane-consecutivos" type="button" role="tab">
                            <i class="fas fa-list-ol me-1"></i> Consecutivos
                        </button>
                    </li>
                </ul>

                <div class="tab-content border-start border-end border-bottom p-4 rounded-bottom">
                    <!-- NITs -->
                    <div class="tab-pane fade show active" id="pane-nits" role="tabpanel">
                        <h6 class="text-primary fw-bold mb-3">
                            Cambiar NIT de los documentos
                        </h6>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="id_nit_destino" class="form-label">Nuevo NIT</label>
                                <select id="id_nit_destino" name="id_nit_destino" class="form-select form-select-sm">
                                    <option value="">Seleccione un NIT...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 small text-muted breadcrumb-item ">
                            <i class="fas fa-info-circle me-1 text-primary"></i>
                            Selecciona el nuevo NIT que se asignará a los documentos seleccionados.
                        </div>
                    </div>

                    <!-- Comprobantes -->
                    <div class="tab-pane fade" id="pane-comprobantes" role="tabpanel">
                        <h6 class="text-primary fw-bold mb-3">
                            Cambiar Comprobante
                        </h6>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="id_comprobante_destino" class="form-label">Comprobante Destino</label>
                                <select id="id_comprobante_destino" name="id_comprobante_destino" class="form-select form-select-sm">
                                    <option value="">Seleccione un comprobante...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 small text-muted breadcrumb-item ">
                            <i class="fas fa-info-circle me-1 text-primary"></i>
                            El comprobante seleccionado reemplazará al actual en los documentos elegidos.
                        </div>
                    </div>

                    <!-- Centros de Costos -->
                    <div class="tab-pane fade" id="pane-centros" role="tabpanel">
                        <h6 class="text-primary fw-bold mb-3">
                            Cambiar Centro de Costos
                        </h6>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="id_centro_costos_destino" class="form-label">Centro de Costos Destino</label>
                                <select id="id_centro_costos_destino" name="id_centro_costos_destino" class="form-select form-select-sm">
                                    <option value="">Seleccione un centro de costos...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 small text-muted breadcrumb-item ">
                            <i class="fas fa-info-circle me-1 text-primary"></i>
                            Aplica un nuevo centro de costos a los registros seleccionados.
                        </div>
                    </div>

                    <!-- Cuentas -->
                    <div class="tab-pane fade" id="pane-cuentas" role="tabpanel">
                        <h6 class="text-primary fw-bold mb-3">
                            Cambiar Cuenta
                        </h6>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="id_cuenta_destino" class="form-label">Cuenta Destino</label>
                                <select id="id_cuenta_destino" name="id_cuenta_destino" class="form-select form-select-sm">
                                    <option value="">Seleccione una cuenta...</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 small text-muted breadcrumb-item ">
                            <i class="fas fa-info-circle me-1 text-primary"></i>
                            Selecciona la nueva cuenta contable para los documentos elegidos.
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="tab-pane fade" id="pane-fechas" role="tabpanel">
                        <h6 class="text-primary fw-bold mb-3">
                            Cambiar Fecha Manual
                        </h6>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="fecha_manual_destino" class="form-label">Nueva Fecha</label>
                                <input 
                                    type="date" 
                                    id="fecha_manual_destino" 
                                    name="fecha_manual_destino" 
                                    class="form-control form-control-sm"
                                >
                            </div>
                        </div>

                        <div class="mt-3 small text-muted breadcrumb-item ">
                            <i class="fas fa-info-circle me-1 text-primary"></i>
                            Establece una nueva fecha manual para los documentos seleccionados.
                        </div>
                    </div>

                    <!-- Consecutivos -->
                    <div class="tab-pane fade" id="pane-consecutivos" role="tabpanel">
                        <h6 class="text-primary fw-bold mb-3">
                            Cambiar Consecutivos
                        </h6>

                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6">
                                <label for="consecutivo_desde_destino" class="form-label">Consecutivo Desde</label>
                                <input 
                                    type="number" 
                                    id="consecutivo_desde_destino" 
                                    name="consecutivo_desde_destino" 
                                    class="form-control form-control-sm" 
                                    placeholder="Ej. 1001"
                                >
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="consecutivo_hasta_destino" class="form-label">Consecutivo Hasta</label>
                                <input 
                                    type="number" 
                                    id="consecutivo_hasta_destino" 
                                    name="consecutivo_hasta_destino" 
                                    class="form-control form-control-sm" 
                                    placeholder="Ej. 1050"
                                >
                            </div>
                        </div>

                        <div class="mt-3 small text-muted breadcrumb-item ">
                            <i class="fas fa-info-circle me-1 text-primary"></i>
                            Define el rango de consecutivos que deseas aplicar a los documentos seleccionados.
                        </div>
                    </div>

                </div>

                <div class="alert alert-warning mt-4" role="alert">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <strong>Atención:</strong> Esta acción no se puede deshacer. Verifique los datos antes de confirmar.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>

                <button id="confirmarCambioDatos" type="button" class="btn btn-success btn-sm">
                    <i class="fas fa-check me-1"></i> Confirmar Cambio
                </button>
                <button id="confirmarCambioLoading" class="btn btn-success btn-sm d-none" disabled>
                    Cargando <i class="fas fa-spinner fa-spin ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
