<div class="modal fade" id="pagosModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textPagos">Editar liquidación definitiva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
                <form id="pagosForm" style="margin-top: 10px;">

                    <div class="row">
                        <input type="text" class="form-control" name="id_pagos_up" id="id_pagos_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_pagos" class="form-control-label">Concepto</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_pagos" id="nombre_pagos" disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="base_pagos" class="form-control-label">Base</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="base_pagos" id="base_pagos" required disabled>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="promedio_pagos" class="form-control-label">Promedio</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="promedio_pagos" id="promedio_pagos" onfocus="this.select();" required>
                        </div>
    
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="total_pagos" class="form-control-label">Total</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="total_pagos" id="total_pagos" onfocus="this.select();" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="observacion_pagos" class="form-control-label">Observación</label>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="observacion_pagos" id="observacion_pagos" onfocus="this.select();" required placeholder="LIQUIDACION DEFINITIVA">
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer" style="padding: 5px;">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="savePagos" type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="savePagosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>

        </div>
    </div>
</div>