<div class="modal fade" id="vendedoresFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textVendedoresCreate" style="display: none;">Agregar vendedor</h5>
                <h5 class="modal-title" id="textVendedoresUpdate" style="display: none;">Editar vendedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="vendedoresForm" class="needs-validation row" style="margin-top: 10px;">

                    <input type="text" class="form-control" name="id_vendedor_up" id="id_vendedor_up" style="display: none;">

                    <div class="form-group col-12 col-sm-12 col-md-12">
                        <label>Nit<span style="color: red">*</span></label>
                        <select name="id_nit_vendedor" id="id_nit_vendedor" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        <div class="invalid-feedback">
                            El nit es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Plazo dias</label>
                        <input type="number" class="form-control form-control-sm" name="plazo_dias_vendedor" id="plazo_dias_vendedor" required>
                        <div class="invalid-feedback">
                            El campo Plazo días es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Comisión %</label>
                        <input type="text" class="form-control form-control-sm" name="porcentaje_comision_vendedor" id="porcentaje_comision_vendedor" value="0" required>
                        <div class="invalid-feedback">
                            El campo Comisión % requerido
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveVendedores"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateVendedores"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveVendedoresLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>