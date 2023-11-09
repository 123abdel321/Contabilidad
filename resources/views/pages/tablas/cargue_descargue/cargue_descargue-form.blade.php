<div class="modal fade" id="cargueDescargueFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textCargueDescargueCreate" style="display: none;">Agregar cargue / descargue</h5>
                <h5 class="modal-title" id="textCargueDescargueUpdate" style="display: none;">Editar cargue / descargue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="cargueDescargueForm" style="margin-top: 10px;">
                    <div class="row">

                        <input type="text" class="form-control" name="id_cargue_descargue_up" id="id_cargue_descargue_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_cargue_descargue" id="nombre_cargue_descargue" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Tipo<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="tipo_cargue_descargue">
                                <option value="0">DESCARGUE</option>
                                <option value="1">CARGUE</option>
                                <option value="2">TRASLADOS</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label>Cliente</label>
                            <select name="id_nit_cargue_descargue" id="id_nit_cargue_descargue" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                            
                            <div class="invalid-feedback">
                                El cliente es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label>Comprobante</label>
                            <select name="id_comprobante_cargue_descargue" id="id_comprobante_cargue_descargue" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                El comprobante es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Cuenta debito</label>
                            <select name="id_cuenta_debito_cargue_descargue" id="id_cuenta_debito_cargue_descargue" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="exampleFormControlSelect1">Cuenta credito</label>
                            <select name="id_cuenta_credito_cargue_descargue" id="id_cuenta_credito_cargue_descargue" class="form-control form-control-sm">
                            </select>
                        </div>

                        
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveCargueDescargue"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateCargueDescargue"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveCargueDescargueLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>