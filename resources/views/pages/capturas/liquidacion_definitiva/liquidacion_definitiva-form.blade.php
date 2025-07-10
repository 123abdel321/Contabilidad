<div class="modal fade" id="liquidacionDefinitivaModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textLiquidacionDefinitiva">Editar liquidación definitiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
                <form id="liquidacionDefinitivaesForm" style="margin-top: 10px;">

                    <div class="row">
                        <input type="text" class="form-control" name="id_liquidacion_definitiva_up" id="id_liquidacion_definitiva_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_liquidacion_definitiva" class="form-control-label">Concepto</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_liquidacion_definitiva" id="nombre_liquidacion_definitiva" disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="base_liquidacion_definitiva" class="form-control-label">Base</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="base_liquidacion_definitiva" id="base_liquidacion_definitiva" required disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="promedio_liquidacion_definitiva" class="form-control-label">Promedio</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="promedio_liquidacion_definitiva" id="promedio_liquidacion_definitiva" onfocus="this.select();" required>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="total_liquidacion_definitiva" class="form-control-label">Total</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="total_liquidacion_definitiva" id="total_liquidacion_definitiva" onfocus="this.select();" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="observacion_liquidacion_definitiva" class="form-control-label">Observación</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="observacion_liquidacion_definitiva" id="observacion_liquidacion_definitiva" onfocus="this.select();" required placeholder="LIQUIDACION DEFINITIVA">
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer" style="padding: 5px;">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveLiquidacionDefinitiva" type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveLiquidacionDefinitivaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>

        </div>
    </div>
</div>