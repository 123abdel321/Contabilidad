<div class="modal fade" id="ventaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar pagos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="ventaForm" style="margin-top: 10px;">
                    <div class="row">
                        <div class="form-group col-12 col-sm-12 col-md-12">
                            <table id="ventaFormaPago" class="table table-bordered display responsive" width="100%">
                                <thead>
                                    <tr style="border: 0px !important;">
                                        <th style="border-radius: 15px 0px 0px 0px !important;">Forma de pago</th>
                                        <th style="border-radius: 0px 15px 0px 0px !important;">Total</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        
                        <div class="line-horizontal"></div>

                        <div class="form-group col-12 col-sm-12 col-md-12">
                            <label for="example-text-input" class="form-control-label">Observación</label>
                            <input type="text" class="form-control form-control-sm" name="observacion_venta" id="observacion_venta">
                            <div class="invalid-feedback">
                                El campo es requerido
                            </div>
                        </div>
                        
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Total pagado</label>
                            <input type="number" class="form-control form-control-sm" name="total_pagado_venta" id="total_pagado_venta" disabled>
                            <div class="invalid-feedback">
                                El campo es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Total faltante</label>
                            <input type="number" class="form-control form-control-sm" name="total_faltante_venta" id="total_faltante_venta" disabled>
                            <div class="invalid-feedback">
                                El total faltando debe ser 0
                            </div>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveVenta"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveVentaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>