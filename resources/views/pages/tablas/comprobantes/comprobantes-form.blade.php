<div class="modal fade" id="comprobanteFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textComprobanteCreate" style="display: none;">Agregar comprobante</h5>
                <h5 class="modal-title" id="textComprobanteUpdate" style="display: none;">Editar comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="comprobanteForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_comprobante" id="id_comprobante" style="display: none;">
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Codigo</label>
                            <input type="text" class="form-control form-control-sm" name="codigo" id="codigo" placeholder="123">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre</label>
                            <input type="text" class="form-control form-control-sm" name="nombre" id="nombre" placeholder="comprobante">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Tipo comprobante</label>
                            <select class="form-control form-control-sm" id="tipo_comprobante">
                                <option value="0">Ingresos</option>
                                <option value="1">Egresos</option>
                                <option value="2">Compras</option>
                                <option value="3">Ventas</option>
                                <option value="4">Otros</option>
                                <option value="4">Cierre</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleFormControlSelect1">Tipo consecutivo</label>
                            <select class="form-control form-control-sm" id="tipo_consecutivo">
                                <option value="0">Normal</option>
                                <option value="1">Mensual</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="example-text-input" class="form-control-label">Consecutivo</label>
                            <input type="number" class="form-control form-control-sm" name="consecutivo_siguiente" id="consecutivo_siguiente" value="1">
                        </div>
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveComprobante"type="button" class="btn bg-gradient-primary btn-sm">Guardar</button>
                <button id="updateComprobante"type="button" class="btn bg-gradient-primary btn-sm">Guardar</button>
                <button id="saveComprobanteLoading" class="btn btn-primary btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>