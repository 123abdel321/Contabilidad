<div class="modal fade" id="nitGeneralFormModal" tabindex="-1" role="dialog" aria-labelledby="nitGeneralFormModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textNitGeneralTitle">Agregar cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="generalNitsForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="hidden" name="id_nit_general_up" id="id_nit_general_up">

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_tipo_documento_general_nit">Tipo documento </label>
                        <select name="id_tipo_documento_general_nit" id="id_tipo_documento_general_nit" class="form-control form-control-sm" required>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="numero_documento_general_nit" class="form-control-label">Numero documento </label>
                        <input type="text" class="form-control form-control-sm input_decimal" name="numero_documento_general_nit" id="numero_documento_general_nit" required>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="tipo_contribuyente_general_nit">Tipo contribuyente </label>
                        <select class="form-control form-control-sm" name="tipo_contribuyente_general_nit" id="tipo_contribuyente_general_nit" required>
                            <option value="">Seleccionar</option>
                            <option value="1">Persona jurídica</option>
                            <option value="2">Persona natural</option>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="primer_nombre_general_nit" class="form-control-label">Primer nombre</label>
                        <input type="text" class="form-control form-control-sm" name="primer_nombre_general_nit" id="primer_nombre_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="otros_nombres_general_nit" class="form-control-label">Segundo nombre</label>
                        <input type="text" class="form-control form-control-sm" name="otros_nombres_general_nit" id="otros_nombres_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="primer_apellido_general_nit" class="form-control-label">Primer apellido</label>
                        <input type="text" class="form-control form-control-sm" name="primer_apellido_general_nit" id="primer_apellido_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="segundo_apellido_general_nit" class="form-control-label">Segundo apellido</label>
                        <input type="text" class="form-control form-control-sm" name="segundo_apellido_general_nit" id="segundo_apellido_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="razon_social_general_nit" class="form-control-label">Razon social</label>
                        <input type="text" class="form-control form-control-sm" name="razon_social_general_nit" id="razon_social_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="direccion_general_nit" class="form-control-label">Dirección </label>
                        <input type="text" class="form-control form-control-sm" name="direccion_general_nit" id="direccion_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="email_general_nit" class="form-control-label">Email</label>
                        <input type="email" class="form-control form-control-sm" name="email_general_nit" id="email_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="telefono_1_general_nit" class="form-control-label">Telefono</label>
                        <input type="text" class="form-control form-control-sm" name="telefono_1_general_nit" id="telefono_1_general_nit" >
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_ciudad_general_nit" style=" width: 100%;">Ciudad</label>
                        <select class="form-control form-control-sm" name="id_ciudad_general_nit" id="id_ciudad_general_nit">
                            <option value="">Ninguna</option>
                        </select>
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="observaciones_general_nit" class="form-control-label">Observaciones</label>
                        <input type="text" class="form-control form-control-sm" name="observaciones_general_nit" id="observaciones_general_nit" >
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-12 col-sm-6 col-md-6">
                        <input class="form-check-input" type="checkbox" name="retencion_general_nit" id="retencion_general_nit" style="height: 20px;" checked>
                        <label class="form-check-label" for="retencion_general_nit">Calcula Retención</label>
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-12 col-sm-6 col-md-6">
                        <input class="form-check-input" type="checkbox" name="proveedor_general_nit" id="proveedor_general_nit" style="height: 20px;" checked>
                        <label class="form-check-label" for="proveedor_general_nit">Proveedor</label>
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-12 col-sm-6 col-md-6" id="div_sumar_aiu">
                        <input class="form-check-input" type="checkbox" name="sumar_aiu_general_nits" id="sumar_aiu_general_nits" style="height: 20px;">
                        <label class="form-check-label" for="sumar_aiu_general_nits">Sumar calculo AIU</label>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveNitVentaGeneral"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveNitGastoGeneral"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveNitGeneralLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>