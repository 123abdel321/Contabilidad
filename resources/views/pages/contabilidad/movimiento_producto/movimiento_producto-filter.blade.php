<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMovimientoProductoInforme" aria-expanded="false" aria-controls="collapseMovimientoProductoInforme">
                Filtros informe movimiento de productos 
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseMovimientoProductoInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="movimientoProductoFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Tipo de informe</label>
                            <select name="tipo_informe_movimiento_producto" id="tipo_informe_movimiento_producto" class="form-control form-control-sm">
                                <option value="" selected>General</option>
                                <option value="venta">Ventas</option>
                                <option value="devolución">Devoluciones</option>
                                <option value="compra">Compras</option>
                                <option value="cargue">Cargues</option>
                                <option value="descargue">Descargues</option>
                                <option value="traslado">Traslados</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="producto-text-input" class="form-control-label">Producto</label>
                            <select name="id_producto_movimiento_producto" id="id_producto_movimiento_producto" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label>Cliente</label>
                            <select name="id_cliente_movimiento_producto" id="id_cliente_movimiento_producto" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="fecha_manual_movimiento_producto" class="form-control-label">Fecha</label>
                            <input name="fecha_manual_movimiento_producto" id="fecha_manual_movimiento_producto" class="form-control form-control-sm" required>
                        </div>

                    </div>  
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarMovimientoProducto" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-gold" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;BUSCAR</b>
                    </span>
                    <span id="generarMovimientoProductoLoading" class="badge bg-gradient-info btn-bg-gold-loading" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;BUSCANDO</b>
                    </span>
                </div>
            </div>
        </div>
        
    </div>
</div>