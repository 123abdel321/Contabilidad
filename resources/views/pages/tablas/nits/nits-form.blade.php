<div class="modal fade" id="nitFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textNitCreate" style="display: none;">Agregar Cedulas nit</h5>
                <h5 class="modal-title" id="textNitUpdate" style="display: none;">Editar Cedulas nit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="nitsForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_nit" id="id_nit" style="display: none;">
                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="exampleFormid_tipo_documento">Tipo documento </label>
                        <select name="id_tipo_documento" id="id_tipo_documento" class="form-control form-control-sm">
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4" >
                        <label for="example-text-input" class="form-control-label">Numero documento </label>
                        <input type="text" class="form-control form-control-sm input_decimal" name="numero_documento" id="numero_documento">
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="exampleFormControlSelect1">Tipo contribuyente </label>
                        <select class="form-control form-control-sm" name="tipo_contribuyente" id="tipo_contribuyente" required>
                            <option value="">Seleccionar</option>
                            <option value="1">Persona jurídica</option>
                            <option value="2">Persona natural</option>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Primer nombre</label>
                        <input type="text" class="form-control form-control-sm" name="primer_nombre" id="primer_nombre" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Segundo nombre</label>
                        <input type="text" class="form-control form-control-sm" name="otros_nombres" id="otros_nombres" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Primer apellido</label>
                        <input type="text" class="form-control form-control-sm" name="primer_apellido" id="primer_apellido" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Segundo apellido</label>
                        <input type="text" class="form-control form-control-sm" name="segundo_apellido" id="segundo_apellido" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Razon social</label>
                        <input type="text" class="form-control form-control-sm" name="razon_social" id="razon_social" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Dirección </label>
                        <input type="text" class="form-control form-control-sm" name="direccion" id="direccion" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Email</label>
                        <input type="email" class="form-control form-control-sm" name="email" id="email" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Telefono</label>
                        <input type="text" class="form-control form-control-sm" name="telefono_1" id="telefono_1" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="exampleFormControlSelect1" style=" width: 100%;">Ciudad</label>
                        <select class="form-control form-control-sm" name="id_ciudad" id="id_ciudad">
                            <option value="">Ninguna</option>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-4 col-sm-4">
                        <label for="example-text-input" class="form-control-label">Observaciones</label>
                        <input type="text" class="form-control form-control-sm" name="observaciones" id="observaciones" >
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveNit"type="button" class="btn bg-gradient-info btn-sm">Guardar</button>
                <button id="updateNit"type="button" class="btn bg-gradient-info btn-sm">Guardar</button>
                <button id="saveNitLoading" class="btn btn-info btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>