<div class="offcanvas offcanvas-end" tabindex="-1" id="offCanvasEditProducto" aria-labelledby="offCanvasEditProductoLabel">

    <div class="offcanvas-header offcanvas-header-maximo">
        <h5 id="id_producto_name_edit" class="offcanvas-title" style="font-size: 18px; text-align: -webkit-center; font-weight: bold;"></h5>
        <button id="producto_close_edit" type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" style="background-color: #088aa3; margin-top: 10px; margin-left: auto; margin-right: 0.5rem;"></button>
    </div>

    <div class="offcanvas-body wrapper row">

        <input type="text" class="form-control" name="id_producto_edit" id="id_producto_edit" style="display: none;">

        <div class="form-group col-12" style="margin-bottom: 0px !important;">
            <label for="producto_edit_cantidad" class="form-control-label">Cantidad</label>
            <input type="number" class="form-control form-control-sm" name="producto_edit_cantidad" id="producto_edit_cantidad" placeholder="123" onfocus="this.select();" onkeydown="cantidadPedidoEditkeyDown(event)" onfocusout="calcularPedidoProductoEdit()" style="text-align: right;" required>
            <div id="producto_edit_cantidad_validate" style="position: absolute; margin-top: 30px; z-index: 9;" class="invalid-feedback">
            </div>
        </div>

        <div class="form-group col-12" style="margin-bottom: 0px !important;">
            <label for="producto_edit_precio" class="form-control-label">Precio</label>
            <input type="number" class="form-control form-control-sm" name="producto_edit_precio" id="producto_edit_precio" placeholder="123" onfocus="this.select();" onkeydown="precioPedidoEditkeyDown(event)" onfocusout="calcularPedidoProductoEdit()" style="text-align: right;" required>
        </div>

        <div class="form-group col-12" style="margin-bottom: 0px !important;">
            <label for="producto_edit_porcentaje_descuento" class="form-control-label">% Dscto</label>
            <input type="number" class="form-control form-control-sm" name="producto_edit_porcentaje_descuento" id="producto_edit_porcentaje_descuento" placeholder="123" onkeydown="descuentoPorcentajePedidoEditkeyDown(event)" onfocusout="calcularPedidoProductoEdit()" onfocus="this.select();" style="text-align: right;" required>
        </div>

        <div class="form-group col-12" style="margin-bottom: 0px !important;">
            <label for="producto_edit_descuento" class="form-control-label">Dscto</label>
            <input type="number" class="form-control form-control-sm" name="producto_edit_descuento" id="producto_edit_descuento" placeholder="123" onfocus="this.select();" onkeydown="descuentoPedidoEditkeyDown(event)" onfocusout="descuentoPedidoEditFocusOut()" style="text-align: right;" required>
        </div>

        <div class="form-group col-12" style="margin-bottom: 0px !important;">
            <label for="producto_edit_iva" class="form-control-label">Iva</label>
            <div class="input-group input-group-sm">
                <span id="producto_edit_porcentaje_iva" class="input-group-text" style="background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">0%</span>
                <input style="text-align: right;" type="text" class="form-control form-control-sm" value="0" id="producto_edit_iva" value="0" disabled>
            </div>
        </div>

        <div class="form-group col-12" style="margin-bottom: 0px !important;">
            <label for="producto_edit_observacion" class="form-control-label">Observación</label>
            <textarea  rows="2" class="form-control form-control-sm" name="producto_edit_observacion" id="producto_edit_observacion" onfocus="this.select();"></textarea>
        </div>

        <div class="form-group col-12" style="place-content: center;">
            <p id="producto_edit_total" style="font-size: 20px; margin-bottom: 0px; place-self: center; font-weight: bold; color: #434343;">12343</p>
        </div>
        
    </div>

    <div class="offcanvas-footer">
        <div class="container row" style="margin-left: 0px !important;">
            <button id="guardarEditProducto"type="button" class="btn bg-gradient-success btn-sm col-12">Confirmar</button>
        </div>
    </div>

</div>