<div class="modal fade" id="prestacionesSocialesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textprestacionesSociales">Editar prestaciones sociales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
                <form id="prestacionesSocialesForm" style="margin-top: 10px;">

                    <div class="row">
                        <input type="text" class="form-control" name="id_prestaciones_sociales_up" id="id_prestaciones_sociales_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_prestaciones_sociales" class="form-control-label">Concepto</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_prestaciones_sociales" id="nombre_prestaciones_sociales" disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="base_prestaciones_sociales" class="form-control-label">Base</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="base_prestaciones_sociales" id="base_prestaciones_sociales" required disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="porcentaje_prestaciones_sociales" class="form-control-label">Porcentaje</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="porcentaje_prestaciones_sociales" id="porcentaje_prestaciones_sociales" onfocus="this.select();" required>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="provision_prestaciones_sociales" class="form-control-label">Valor prestación</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="provision_prestaciones_sociales" id="provision_prestaciones_sociales" onfocus="this.select();" required>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer" style="padding: 5px;">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="savePrestacionesSociales"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="savePrestacionesSocialesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>

        </div>
    </div>
</div>