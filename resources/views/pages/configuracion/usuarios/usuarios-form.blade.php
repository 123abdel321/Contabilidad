<div class="modal fade" id="usuariosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textUsuariosCreate" style="display: none;">Agregar usuario</h5>
                <h5 class="modal-title" id="textUsuariosUpdate" style="display: none;">Editar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="usuariosForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_usuarios_up" id="id_usuarios_up" style="display: none;">
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Usuario</label>
                            <input type="text" class="form-control form-control-sm" name="usuario" id="usuario" required>
                            <div class="invalid-feedback">
                                El usuario es requerido
                            </div>
                        </div>
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Correo</label>
                            <input type="email" class="form-control form-control-sm" name="email" id="email" required>
                            <div class="invalid-feedback">
                                El correo es requerido
                            </div>
                        </div>
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombres</label>
                            <input type="text" class="form-control form-control-sm" name="firstname" id="firstname">
                        </div>
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Apellidos</label>
                            <input type="text" class="form-control form-control-sm" name="lastname" id="lastname">
                        </div>
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Telefono</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" id="telefono">
                        </div>
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Dirección</label>
                            <input type="text" class="form-control form-control-sm" name="address" id="address">
                        </div>
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Contraseña</label>
                            <input type="password" class="form-control form-control-sm" name="password_usuario" id="password_usuario">
                            <!-- <div class="invalid-feedback">
                                El contraseña es requerida
                            </div> -->
                        </div>
                        <div class="form-group form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Confirmar contraseña</label>
                            <input type="password" class="form-control form-control-sm" name="password_confirm" id="password_confirm">
                            <!-- <div class="invalid-feedback">
                                La confirmación de contraseña es requerida
                            </div> -->
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label>Bodegas a cargo</label>
                            <select name="id_bodega_usuario[]" id="id_bodega_usuario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" multiple="multiple">
                                @foreach ($bodegas as $bodega)
                                    <option value="{{ $bodega->id }}">{{ $bodega->codigo.' - '.$bodega->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label>Resoluciones a cargo</label>
                            <select name="id_resolucion_usuario[]" id="id_resolucion_usuario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" multiple="multiple">
                                @foreach ($resoluciones as $resolucion)
                                    <option value="{{ $resolucion->id }}">{{ $resolucion->prefijo.' - '.$resolucion->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label>Rol usuario</label>
                            <select name="rol_usuario" id="rol_usuario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                @foreach ($roles as $rol)
                                    <option value="{{ $rol->id }}">{{ $rol->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check form-switch col-12 col-sm-6 col-md-6">
                            <input class="form-check-input" type="checkbox" name="facturacion_rapida" id="facturacion_rapida" style="height: 20px;">
                            <label class="form-check-label" for="facturacion_rapida">Facturación pos rapida</label>
                        </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveUsuarios"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateUsuarios"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveUsuariosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>