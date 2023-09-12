<div class="modal fade" id="planCuentaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textPlanCuentaCreate" style="display: none;">Agregar cuenta</h5>
                <h5 class="modal-title" id="textPlanCuentaUpdate" style="display: none;">Editar cuenta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="planCuentaForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_plan_cuenta" id="id_plan_cuenta" style="display: none;">
                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Padre</label>
                            <select name="id_padre" id="id_padre" class="form-control form-control-sm">
                            </select>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="">Cuenta</label>
                            <div class="input-group">
                                <div class="input-group-prepend" style="width: 75px;">
                                    <input id="text_cuenta_padre" class="form-control form-control-sm" type="text" style="border-radius: 10px 0px 0px 10px;" disabled>
                                </div>
                                <input name="cuenta" id="cuenta" class="form-control form-control-sm" type="text" style="padding-left: 8px;" require>
                            </div>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre" id="nombre" requiere>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Naturaleza cuenta<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="naturaleza_cuenta">
                                <option value="0">Debito</option>
                                <option value="1">Credito</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Naturaleza ingresos<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="naturaleza_ingresos">
                                <option value="">Ninguna</option>
                                <option value="0">Debito</option>
                                <option value="1">Credito</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Naturaleza egresos<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="naturaleza_egresos">
                                <option value="">Ninguna</option>
                                <option value="0">Debito</option>
                                <option value="1">Credito</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Naturaleza compras<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="naturaleza_compras">
                                <option value="">Ninguna</option>
                                <option value="0">Debito</option>
                                <option value="1">Credito</option>
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Naturaleza ventas<span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="naturaleza_ventas">
                                <option value="">Ninguna</option>
                                <option value="0">Debito</option>
                                <option value="1">Credito</option>
                            </select>
                        </div>

                        <!-- <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Tipo cuenta <span style="color: red">*</span></label>
                            <select class="form-control form-control-sm" id="id_tipo_cuenta" name="id_tipo_cuenta" requiere>
                                <option value="">Seleccionar</option>
                                @foreach ($tipoCuenta as $cuenta)
                                    <option value="{{ $cuenta->id }}">{{ $cuenta->id.' - '.$cuenta->nombre }}</option>
                                @endforeach
                            </select>
                        </div> -->

                        <div class="form-check col-md-6">
                            <input name="exige_nit" id="exige_nit" type="checkbox" class="form-check-input" style="margin-left: -13px;">
                            <label class="custom-control-label" for="exige_nit">Exige nit</label>
                        </div>

                        <div class="form-check col-md-6">
                            <input name="exige_documento_referencia" id="exige_documento_referencia" type="checkbox" class="form-check-input" style="margin-left: -13px;">
                            <label class="custom-control-label" for="exige_nit">Exige Dcto refe</label>
                        </div>

                        <div class="form-check col-md-6">
                            <input name="exige_concepto" id="exige_concepto" type="checkbox" class="form-check-input" style="margin-left: -13px;">
                            <label class="custom-control-label" for="exige_nit">Exige concepto</label>
                        </div>

                        <div class="form-check col-md-6">
                            <input name="exige_centro_costos" id="exige_centro_costos" type="checkbox" class="form-check-input" style="margin-left: -13px;">
                            <label class="custom-control-label" for="exige_nit">Exige centro costos</label>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="savePlanCuenta"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updatePlanCuenta"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="savePlanCuentaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>