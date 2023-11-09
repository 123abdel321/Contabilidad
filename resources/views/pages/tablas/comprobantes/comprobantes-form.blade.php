<div class="modal fade" id="comprobanteFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textComprobanteCreate" style="display: none;">Agregar comprobante</h5>
                <h5 class="modal-title" id="textComprobanteUpdate" style="display: none;">Editar comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="comprobanteForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_comprobante_up" id="id_comprobante_up" style="display: none;">
                        <div class="form-group col-md-6">
                            <label for="example-text-input" c|lass="form-control-label">Codigo</label>
                            <input type="text" class="form-control form-control-sm" name="codigo_comprobante" id="codigo_comprobante" placeholder="123" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_comprobante" id="nombre_comprobante" placeholder="comprobante" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Tipo comprobante</label>
                            <select class="form-control form-control-sm" id="tipo_comprobante">
                                <option value="0">INGRESOS</option>
                                <option value="1">EGRESOS</option>
                                <option value="2">COMPRAS</option>
                                <option value="3">VENTAS</option>
                                <option value="4">OTROS</option>
                                <option value="4">CIERRE</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Tipo consecutivo</label>
                            <select class="form-control form-control-sm" id="tipo_consecutivo">
                                <option value="0">GENERAL</option>
                                <option value="1">MENSUAL</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Imprimir en capturas</label>
                            <select class="form-control form-control-sm" id="imprimir_en_capturas">
                                <option value="0">NO</option>
                                <option value="1">SI</option>
                            </select>
                        </div>  
                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Tipo de impresión</label>
                            <select class="form-control form-control-sm" id="tipo_impresion">
                                <option value=""></option>
                                <option value="0">POS</option>
                                <option value="1">MEDIA CARTA</option>
                                <option value="2">CARTA</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Consecutivo</label>
                            <input type="number" class="form-control form-control-sm" name="consecutivo_siguiente" id="consecutivo_siguiente" value="1" required>
                        </div>
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveComprobante"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateComprobante"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveComprobanteLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>