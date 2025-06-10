<div class="modal fade" id="configuracionProvisionesFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textEditarConfiguracionProvisiones">Editar provisiones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="configuracionProvisionesForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_configuracion_provisiones_up" id="id_configuracion_provisiones_up" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-12 col-sm-6 col-md-6" >
                        <label for="porcentaje_configuracion_provisiones" class="form-control-label">Porcentaje <span style="color: red">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="porcentaje_configuracion_provisiones" id="porcentaje_configuracion_provisiones" value="0">
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_cuenta_administrativos_configuracion_provisiones">Cuenta administrativos</label>
                        <select name="id_cuenta_administrativos_configuracion_provisiones" id="id_cuenta_administrativos_configuracion_provisiones" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_cuenta_operativos_configuracion_provisiones">Cuenta operativo</label>
                        <select name="id_cuenta_operativos_configuracion_provisiones" id="id_cuenta_operativos_configuracion_provisiones" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_cuenta_venta_configuracion_provisiones">Cuenta venta</label>
                        <select name="id_cuenta_venta_configuracion_provisiones" id="id_cuenta_venta_configuracion_provisiones" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_cuenta_otros_configuracion_provisiones">Cuenta otros</label>
                        <select name="id_cuenta_otros_configuracion_provisiones" id="id_cuenta_otros_configuracion_provisiones" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_cuenta_por_pagar_configuracion_provisiones">Cuenta por pagar</label>
                        <select name="id_cuenta_por_pagar_configuracion_provisiones" id="id_cuenta_por_pagar_configuracion_provisiones" class="form-control form-control-sm">
                        </select>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="updateConfiguracionProvisiones"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveConfiguracionProvisionesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>