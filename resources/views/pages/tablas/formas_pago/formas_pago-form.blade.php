<div class="modal fade" id="formasPagoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textFormasPagoCreate" style="display: none;">Agregar formas de pago</h5>
                <h5 class="modal-title" id="textFormasPagoUpdate" style="display: none;">Editar formas de pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formasPagoForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_formas_pago_up" id="id_formas_pago_up" style="display: none;">
                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label>Cuenta <span style="color: red">*</span></label>
                            <select name="id_cuenta_forma_pago" id="id_cuenta_forma_pago" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                La cuenta es requerida
                            </div>
                        </div>
                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label>Tipo <span style="color: red">*</span></label>
                            <select name="id_tipo_formas_pago" id="id_tipo_formas_pago" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                El tipo de pago es requerido
                            </div>
                        </div>


                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_forma_pago" id="nombre_forma_pago" placeholder="NOMBRE FORMA PAGO" required>
                        </div>
                        
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveFormasPago"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateFormasPago"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveFormasPagoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>