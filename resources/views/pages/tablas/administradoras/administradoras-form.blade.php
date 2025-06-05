<div class="modal fade" id="administradorasFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textAdministradorasCreate" style="display: none;">Agregar Administradora</h5>
                <h5 class="modal-title" id="textAdministradorasUpdate" style="display: none;">Editar Administradora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="administradorasForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_administradoras_up" id="id_administradoras_up" style="display: none;">

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="tipo_administradora">Tipo <span style="color: red">*</span></label>
                            <select name="tipo_administradora" id="tipo_administradora" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="0">EPS</option>
                                <option value="1">AFP</option>
                                <option value="2">ARL</option>
                                <option value="3">CCF</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                la cedula / nit es requerida
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="codigo_administradora" class="form-control-label">Codigo <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="codigo_administradora" id="codigo_administradora" placeholder="EPS001" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_administradora" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_administradora" id="nombre_administradora" placeholder="Nombre EPS" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_nit_administradora">Cedula / Nit <span style="color: red">*</span></label>
                            <select name="id_nit_administradora" id="id_nit_administradora" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                la cedula / nit es requerida
                            </div>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveAdministradoras"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateAdministradoras"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveAdministradorasLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>