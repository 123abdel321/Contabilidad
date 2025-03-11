<div class="modal fade" id="ubicacionesFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textUbicacionesCreate" style="display: none;">Agregar ubicaciones</h5>
                <h5 class="modal-title" id="textUbicacionesUpdate" style="display: none;">Editar ubicaciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="ubicacionesForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_ubicaciones_up" id="id_ubicaciones_up" style="display: none;">
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Codigo</label>
                            <input type="text" class="form-control form-control-sm" name="codigo_ubicaciones" id="codigo_ubicaciones" placeholder="01" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_ubicaciones" id="nombre_ubicaciones" placeholder="Nombre" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Tipo ubicación</label>
                            <select name="id_ubicacion_tipos_ubicaciones" id="id_ubicacion_tipos_ubicaciones" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveUbicaciones"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateUbicaciones"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveUbicacionesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>