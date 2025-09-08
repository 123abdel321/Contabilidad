<div class="modal fade" id="reunionNitsFormModal" tabindex="-1" role="dialog" aria-labelledby="reunionFormModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <div class="modal-content" style="margin-top: 10px;">
            <div class="modal-header">
                <h5 class="modal-title">Agregar participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <form id="form-reunion-nit" class="row needs-validation" novalidate>

                    <div class="form-group  col-12">
                        <label>Cédula / Nit<span style="color: red">*</span></label>
                        <select name="id_nit_reunion" id="id_nit_reunion" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La cédula / nit es requerida
                        </div>
                    </div>

                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="saveReunionNit" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button type="button" id="saveReunionNitLoading" class="btn btn-success btn-sm ms-auto" style="display:none;" disabled>
                    Guardando <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>