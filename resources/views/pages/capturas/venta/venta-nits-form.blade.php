<div class="modal fade" id="nitVentaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textNitCreate">Agregar cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="ventaNitsForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormid_tipo_documento">Tipo documento </label>
                        <select name="id_tipo_documento_venta_nit" id="id_tipo_documento_venta_nit" class="form-control form-control-sm" required>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Numero documento </label>
                        <input type="text" class="form-control form-control-sm input_decimal" name="numero_documento_venta_nit" id="numero_documento_venta_nit" required>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Tipo contribuyente </label>
                        <select class="form-control form-control-sm" name="tipo_contribuyente_venta_nit" id="tipo_contribuyente_venta_nit" required>
                            <option value="">Seleccionar</option>
                            <option value="1">Persona jurídica</option>
                            <option value="2">Persona natural</option>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Primer nombre</label>
                        <input type="text" class="form-control form-control-sm" name="primer_nombre_venta_nit" id="primer_nombre_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Segundo nombre</label>
                        <input type="text" class="form-control form-control-sm" name="otros_nombres_venta_nit" id="otros_nombres_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Primer apellido</label>
                        <input type="text" class="form-control form-control-sm" name="primer_apellido_venta_nit" id="primer_apellido_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Segundo apellido</label>
                        <input type="text" class="form-control form-control-sm" name="segundo_apellido_venta_nit" id="segundo_apellido_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Razon social</label>
                        <input type="text" class="form-control form-control-sm" name="razon_social_venta_nit" id="razon_social_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Dirección </label>
                        <input type="text" class="form-control form-control-sm" name="direccion_venta_nit" id="direccion_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Email</label>
                        <input type="email" class="form-control form-control-sm" name="email_venta_nit" id="email_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Telefono</label>
                        <input type="text" class="form-control form-control-sm" name="telefono_1_venta_nit" id="telefono_1_venta_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1" style=" width: 100%;">Ciudad</label>
                        <select class="form-control form-control-sm" name="id_ciudad_venta_nit" id="id_ciudad_venta_nit">
                            <option value="">Ninguna</option>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Observaciones</label>
                        <input type="text" class="form-control form-control-sm" name="observaciones_venta_nit" id="observaciones_venta_nit" >
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveNitVenta"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveNitVentaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>