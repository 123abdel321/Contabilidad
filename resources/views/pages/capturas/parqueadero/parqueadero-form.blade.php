<div class="modal fade" id="parqueaderoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textParqueaderoCreate" style="display: none;">Agregar centros costros</h5>
                <h5 class="modal-title" id="textParqueaderoUpdate" style="display: none;">Editar centros costros</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="parqueaderoForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_parqueadero_up" id="id_parqueadero_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_bodega_parqueadero">Bodega<span style="color: red">*</span></label>
                            <select name="id_bodega_parqueadero" id="id_bodega_parqueadero" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                La bodega es requerida
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="consecutivo_bodegas_parqueadero" class="form-control-label">Consecutivo<span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="consecutivo_bodegas_parqueadero" id="consecutivo_bodegas_parqueadero" required disabled>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6" style="margin-bottom: 1px !important;">
                            <label for="id_nit_parqueadero">Cliente<span style="color: red">*</span></label>
                            <select name="id_nit_parqueadero" id="id_nit_parqueadero" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                El cliente es requerida
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6" style="margin-bottom: 1px !important;">
                            <label for="tipo_vehiculo_parqueadero">Tipo vehiculo<span style="color: red">*</span></label>
                            <select name="tipo_vehiculo_parqueadero" id="tipo_vehiculo_parqueadero" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="1">CARRO</option>
                                <option value="2">MOTO</option>
                                <option value="3">OTROS</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Placa<span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="placa_vehiculo_parqueadero" id="placa_vehiculo_parqueadero" placeholder="ABC-123" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Fecha inicio</label>
                            <input type="datetime-local" class="form-control form-control-sm" name="fecha_inicio_parqueadero" id="fecha_inicio_parqueadero" required disabled/>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6" style="margin-bottom: 1px !important;">
                            <label for="id_producto_parqueadero">Tarifa<span style="color: red">*</span></label>
                            <select name="id_producto_parqueadero" id="id_producto_parqueadero" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                La tarifa es requerida
                            </div>
                        </div>
                        
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveParqueadero"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateParqueadero"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveParqueaderoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>