<div class="modal fade" id="cecosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textCecosCreate" style="display: none;">Agregar centros costros</h5>
                <h5 class="modal-title" id="textCecosUpdate" style="display: none;">Editar centros costros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="cecosForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_cecos_up" id="id_cecos_up" style="display: none;">
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Codigo</label>
                            <input type="text" class="form-control form-control-sm" name="codigo" id="codigo" placeholder="123" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre</label>
                            <input type="text" class="form-control form-control-sm" name="nombre" id="nombre" placeholder="Centro costos" required>
                        </div>
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveCecos"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateCecos"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveCecosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>