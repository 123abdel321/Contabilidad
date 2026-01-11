<div class="modal fade" id="contratosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textContratosCreate" style="display: none;">Agregar Contrato</h5>
                <h5 class="modal-title" id="textContratosUpdate" style="display: none;">Editar Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div style="margin-top: 10px;" class="row">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveContratos"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateContratos"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveContratosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>