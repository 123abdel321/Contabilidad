<div class="modal fade" id="causarProvisionModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textCausarProvisiones">Editar prestaciones sociales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
                <form id="causarProvisionesForm" style="margin-top: 10px;">

                    <div class="row">
                        <input type="text" class="form-control" name="id_causar_provisiones_up" id="id_causar_provisiones_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_causar_provisiones" class="form-control-label">Concepto</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_causar_provisiones" id="nombre_causar_provisiones" disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="base_causar_provisiones" class="form-control-label">Base</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="base_causar_provisiones" id="base_causar_provisiones" required disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="porcentaje_causar_provisiones" class="form-control-label">Porcentaje</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="porcentaje_causar_provisiones" id="porcentaje_causar_provisiones" onfocus="this.select();" required>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="provision_causar_provisiones" class="form-control-label">Valor prestación</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="provision_causar_provisiones" id="provision_causar_provisiones" onfocus="this.select();" required>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer" style="padding: 5px;">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="savePrestacionesSociales" type="button" class="btn bg-gradient-success btn-sm" style="display: none;">Actualizar prestaciones</button>
                <button id="saveSeguridadSocial" type="button" class="btn bg-gradient-success btn-sm" style="display: none;">Actualizar seguridad</button>
                <button id="saveParafiscales" type="button" class="btn bg-gradient-success btn-sm" style="display: none;">Actualizar parafiscales</button>
                <button id="saveCausarProvisionesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>

        </div>
    </div>
</div>