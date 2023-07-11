<div class="modal fade" id="documentoGeneralFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textDocumentoFormCreate" style="display: none;">Agregar documento</h5>
                <h5 class="modal-title" id="textDocumentoFormUpdate" style="display: none;">Editar documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="documentoGeneralForm" style="margin-top: 10px;" class="needs-validation row" novalidate>
                    <input type="text" class="form-control form-control-sm" name="id_documento" id="id_documento" style="display: none;">
                    <div class="form-group col-md-6">
                        <label for="exampleFormControlSelect1">Cuenta</label>
                        <select name="id_cuenta" id="id_cuenta" class="form-control form-control-sm" required>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="exampleFormControlSelect1">Nit</label>
                        <select name="id_nit" id="id_nit" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="exampleFormControlSelect1">Centro costos</label>
                        <select name="id_centro_costos" id="id_centro_costos" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="example-text-input" class="form-control-label">Dcto Refe </label>
                        <input type="text" class="form-control form-control-sm" name="documento_referencia" id="documento_referencia">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="example-text-input" class="form-control-label">Debito </label>
                        <input type="number" class="form-control form-control-sm" name="debito" id="debito">
                    </div>

                    <div class="form-group col-md-6">
                        <label for="example-text-input" class="form-control-label">Credito </label>
                        <input type="number" class="form-control form-control-sm" name="credito" id="credito">
                    </div>

                    <div class="form-group col-md-12">
                        <label for="example-text-input" class="form-control-label">Concepto </label>
                        <input type="text" class="form-control form-control-sm" name="concepto" id="concepto">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveDocumentoGeneral"type="button" class="btn bg-gradient-info btn-sm">Guardar</button>
                <button id="updateDocumentoGeneral"type="button" class="btn bg-gradient-info btn-sm">Guardar</button>
                <button id="saveDocumentoGeneralLoading" class="btn btn-info btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>