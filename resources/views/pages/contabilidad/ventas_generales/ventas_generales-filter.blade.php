<div class="accordion" id="accordionRentalVentasGenerales">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseVentasGeneralesInforme" aria-expanded="false" aria-controls="collapseVentasGeneralesInforme">
                Filtros ventas generales
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseVentasGeneralesInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="ventasGeneralesFilterForm" class="needs-validation" style="margin-top: 10px;" novalidate>
                    <div class="row">

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="fecha-desde-text-input" class="form-control-label">Fecha desde<span style="color: red">*</span></label>
                            <input name="fecha_desde_ventas_generales" id="fecha_desde_ventas_generales" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="fecha-hasta-text-input" class="form-control-label">Fecha hasta<span style="color: red">*</span></label>
                            <input name="fecha_hasta_ventas_generales" id="fecha_hasta_ventas_generales" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="precio-hasta-text-input" class="form-control-label">Precio desde</label>
                            <input name="precio_desde_ventas_generales" id="precio_desde_ventas_generales" class="form-control form-control-sm" type="number">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="precio-desde-text-input" class="form-control-label">Precio hasta</label>
                            <input name="precio_hasta_ventas_generales" id="precio_hasta_ventas_generales" class="form-control form-control-sm" type="number">
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="consecutivo-text-input" class="form-control-label">Consecutivo</label>
                            <input name="consecutivo_ventas_generales" id="consecutivo_ventas_generales" class="form-control form-control-sm" type="text">
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="cedula-text-input" class="form-control-label">Cedula/Nit</label>
                            <select name="id_nit_ventas_generales" id="id_nit_ventas_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="resolucion-text-input" class="form-control-label">Resolucion</label>
                            <select name="id_resolucion_ventas_generales" id="id_resolucion_ventas_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="bodega-text-input" class="form-control-label">Bodega</label>
                            <select name="id_bodega_ventas_generales" id="id_bodega_ventas_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="bodega-text-input" class="form-control-label">Producto</label>
                            <select name="id_producto_ventas_generales" id="id_producto_ventas_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-3 col-md-3">
                            <label for="usuario-text-input" class="form-control-label">Usuario</label>
                            <select name="id_usuario_ventas_generales" id="id_usuario_ventas_generales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        

                    </div>
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarVentasGenerales" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarVentasGeneralesLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">BUSCANDO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>