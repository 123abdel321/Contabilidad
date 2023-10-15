<div class="modal fade" id="familiaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textFamiliaCreate" style="display: none;">Agregar familia</h5>
                <h5 class="modal-title" id="textFamiliaUpdate" style="display: none;">Editar familia</h5>
                <h5 class="modal-title" id="textFamiliaDuplicate" style="display: none;">Duplicar familia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form id="familiasForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_familia_up" id="id_familia_up" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Código</label>
                        <input type="text" class="form-control form-control-sm" name="codigo_familia" id="codigo_familia">
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="nombre_familia" id="nombre_familia">
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-check form-switch col-12 col-sm-6 col-12 col-sm-6 col-md-6" style="margin-left: 12px;">
                        <input class="form-check-input" type="checkbox" name="inventario_familia" id="inventario_familia" style="height: 20px;">
                        <label class="form-check-label" for="inventario_familia">Maneja Inventario</label>
                    </div>

                    <div class="accordion accordion-familia" id="accordionExample" style="margin-top: 10px;">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Cuentas ventas
                            </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                <div class="accordion-body row">
                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Venta</label>
                                        <select name="id_cuenta_venta" id="id_cuenta_venta" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Retención</label>
                                        <select name="id_cuenta_venta_retencion" id="id_cuenta_venta_retencion" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Devolución venta</label>
                                        <select name="id_cuenta_venta_devolucion" id="id_cuenta_venta_devolucion" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Iva</label>
                                        <select name="id_cuenta_venta_iva" id="id_cuenta_venta_iva" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Devolución iva</label>
                                        <select name="id_cuenta_venta_devolucion_iva" id="id_cuenta_venta_devolucion_iva" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Descuento</label>
                                        <select name="id_cuenta_venta_descuento" id="id_cuenta_venta_descuento" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6" id="input-familia-inventario">
                                        <label for="exampleFormControlSelect1">Inventario</label>
                                        <select name="id_cuenta_inventario" id="id_cuenta_inventario" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6" id="input-familia-costos">
                                        <label for="exampleFormControlSelect1">Costos</label>
                                        <select name="id_cuenta_costos" id="id_cuenta_costos" class="form-control form-control-sm">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item" id="inputs-familias-compras">
                            <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Cuentas compras
                            </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                            <div class="accordion-body row">
                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Compra</label>
                                        <select name="id_cuenta_compra" id="id_cuenta_compra" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Retención</label>
                                        <select name="id_cuenta_compra_retencion" id="id_cuenta_compra_retencion" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Devolución compra</label>
                                        <select name="id_cuenta_compra_devolucion" id="id_cuenta_compra_devolucion" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Iva</label>
                                        <select name="id_cuenta_compra_iva" id="id_cuenta_compra_iva" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Devolución iva</label>
                                        <select name="id_cuenta_compra_devolucion_iva" id="id_cuenta_compra_devolucion_iva" class="form-control form-control-sm">
                                        </select>
                                    </div>

                                    <div class="form-group col-12 col-sm-6 col-md-6">
                                        <label for="exampleFormControlSelect1">Descuento</label>
                                        <select name="id_cuenta_compra_descuento" id="id_cuenta_compra_descuento" class="form-control form-control-sm">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveFamilia"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateFamilia"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveFamiliaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>