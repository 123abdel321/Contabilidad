<div class="modal fade" id="bodegasProductoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textBodegaProductoCreate" style="display: none;">Agregar bodega</h5>
                <h5 class="modal-title" id="textBodegaProductoUpdate" style="display: none;">Editar bodega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cecosForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_bodega_producto_up" id="id_bodega_producto_up" style="display: none;">
                        <div class="form-group col-12 col-sm-12 col-md-12">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Bodega</label>
                            <select class="form-control form-control-sm" name="id_bodega_producto" id="id_bodega_producto">
                                <option value="">Ninguno</option>
                            </select>
                            <div class="invalid-feedback">
                                El campo es requerido
                            </div>
                        </div>
                        <div class="form-group col-12 col-sm-12 col-md-12" >
                            <label for="example-text-input" class="form-control-label">Cantidad</label>
                            <input type="number" class="form-control form-control-sm" name="cantidad_bodega_producto" id="cantidad_bodega_producto">
                            <div class="invalid-feedback">
                                El campo es requerido
                            </div>
                        </div>
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveBodegaProducto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateBodegaProducto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
            </div>
        </div>
    </div>
</div>