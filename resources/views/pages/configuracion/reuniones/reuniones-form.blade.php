<div class="modal fade" id="reunionFormModal" tabindex="-1" role="dialog" aria-labelledby="reunionFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <div class="modal-content" style="margin-top: 10px;">
            <div class="modal-header">
                <h5 class="modal-title" id="textReunionCreate">Agregar reunión</h5>
                <h5 class="modal-title" id="textReunionUpdate" style="display: none;">Editar reunión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <form id="form-reunion" class="row needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="id_reunion_up" id="id_reunion_up">

                    <div class="form-group col-12 col-sm-12 col-md-12">
                        <label for="titulo_reunion" class="form-control-label">Título de la reunión <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="titulo_reunion" id="titulo_reunion" required>
                        <div class="invalid-feedback">Por favor ingresa un título para la reunión</div>
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-12">
                        <label for="descripcion_reunion" class="form-control-label">Descripción</label>
                        <textarea class="form-control form-control-sm" id="descripcion_reunion" name="descripcion_reunion" rows="3"></textarea>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="fecha_inicio_reunion" class="form-control-label">Fecha inicio <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="fecha_inicio_reunion" id="fecha_inicio_reunion" required>
                        <div class="invalid-feedback">Selecciona una fecha de inicio</div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="fecha_fin_reunion" class="form-control-label">Fecha fin <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="fecha_fin_reunion" id="fecha_fin_reunion" required>
                        <div class="invalid-feedback">Selecciona una fecha de fin</div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="hora_inicio_reunion" class="form-control-label">Hora inicio <span class="text-danger">*</span></label>
                        <input type="time" class="form-control form-control-sm" name="hora_inicio_reunion" id="hora_inicio_reunion" required>
                        <div class="invalid-feedback">Selecciona una hora de inicio</div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="hora_fin_reunion" class="form-control-label">Hora fin <span class="text-danger">*</span></label>
                        <input type="time" class="form-control form-control-sm" name="hora_fin_reunion" id="hora_fin_reunion" required>
                        <div class="invalid-feedback">Selecciona una hora de fin</div>
                    </div>

                    <div class="form-group col-12 col-sm-12 col-md-12">
                        <label for="lugar_reunion" class="form-control-label">Lugar</label>
                        <input type="text" class="form-control form-control-sm" name="lugar_reunion" id="lugar_reunion" placeholder="Ej: Sala de juntas, Oficina 101, Enlace Zoom...">
                    </div>

                    <!-- <div class="form-group col-12 col-sm-12 col-md-12">
                        <label for="participantes_reunion" class="form-control-label">Participantes <span class="text-danger">*</span></label>
                        <select name="participantes_reunion[]" id="participantes_reunion" class="form-control form-control-sm" multiple="multiple" required style="width: 100%;">
                        </select>
                        <div class="invalid-feedback">Selecciona al menos un participante</div>
                        <small class="form-text text-muted">Busca y selecciona los usuarios que asistirán a la reunión</small>
                    </div> -->

                    <!-- Participantes seleccionados -->
                    <div>
                        <div class="card mb-4" style="padding-right: 0px; padding-left: 0px;">
                            <div class="card-header bg-primary text-white" styl="padding: 10px;">
                                <h6 class="mb-0" style="color: white">Participantes Seleccionados</h6>
                            </div>
                            <div class="card-body">
                                <div id="participantes-seleccionados" class="d-flex flex-wrap" style="padding: 5px;">
                                    <span class="text-muted">No hay participantes seleccionados</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de nits con DataTables -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Seleccionar Participantes</h6>
                        </div>
                        <div class="card-body p-0">
                            <table id="nitTableReuniones" class="table table-bordered display responsive" width="100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>Documento</th>
                                        <th>Nombre/Razón Social</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Ciudad</th>
                                        <th width="100px">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                    
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="saveReunion" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button type="button" id="saveReunionLoading" class="btn btn-success btn-sm ms-auto" style="display:none;" disabled>
                    Guardando <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>