<div class="modal fade" id="cesantiasInteresesFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textCesantiasIntereses">Editar prestaciones sociales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="cesantiasInteresesForm" style="margin-top: 10px;">

                    <div class="row">
                        <input type="text" class="form-control" name="id_cesantias_intereses_up" id="id_cesantias_intereses_up" style="display: none;">
    
                        <div class="form-group col-12">
                            <label for="base_cesantias_intereses" class="form-control-label">Base</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="base_cesantias_intereses" id="base_cesantias_intereses" required disabled>
                        </div>

                        <div class="form-group col-12">
                            <label for="dias_cesantias_intereses" class="form-control-label">Días</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="dias_cesantias_intereses" id="dias_cesantias_intereses" onfocus="this.select();" required>
                        </div>
    
                        <div class="form-group col-12">
                            <label for="promedio_cesantias_intereses" class="form-control-label">Promedio</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="promedio_cesantias_intereses" id="promedio_cesantias_intereses" onfocus="this.select();" required>
                        </div>

                        <div class="form-group col-12">
                            <label for="valor_cesantias_intereses" class="form-control-label">Cesantías</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_cesantias_intereses" id="valor_cesantias_intereses" onfocus="this.select();" required>
                        </div>

                        <div class="form-group col-12">
                            <label for="intereses_cesantias_intereses" class="form-control-label">Intereses</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="intereses_cesantias_intereses" id="intereses_cesantias_intereses" onfocus="this.select();" required>
                        </div>

                    </div>

                </form>
            </div>

            <div class="modal-footer" style="padding: 5px;">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveCesantiasEdit" type="button" class="btn bg-gradient-success btn-sm" >Actualizar</button>
                <button id="saveCesantiasEditLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>

        </div>
    </div>
</div>