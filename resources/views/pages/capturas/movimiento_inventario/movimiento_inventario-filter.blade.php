<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMovimientoInventarioGeneral" aria-expanded="false" aria-controls="collapseMovimientoInventarioGeneral">
                Datos de movimiento inventario
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseMovimientoInventarioGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="movimientoInventarioFilterForm" class="needs-validation row" style="margin-top: 10px;" novalidate>
                
                    <div class="form-group col-12 col-sm-3 col-md-2">
                        <label for="exampleFormControlSelect1">Tipo<span style="color: red">*</span></label>
                        <select class="form-control form-control-sm" id="tipo_movimiento_inventario" onchange="changeTipoMovimientoInventario()">
                            <option value="1">CARGUE</option>
                            <option value="0">DESCARGUE</option>
                            <option value="2">TRASLADOS</option>
                        </select>
                    </div>

                    <div class="form-group col-6 col-sm-3 col-md-2">
                        <label>Concepto <span style="color: red">*</span></label>
                        <select name="id_cargue_descargue_movimiento_inventario" id="id_cargue_descargue_movimiento_inventario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" onchange="changeCargueDescargue()" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            El concepto es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-3 col-md-2">
                        <label>Bodega origen<span style="color: red">*</span></label>
                        <select name="id_bodega_origen_movimiento_inventario" id="id_bodega_origen_movimiento_inventario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La bodega origen es requerida
                        </div>
                    </div>

                    <div id="div_bodega_destino_movimiento_inventario" class="form-group col-6 col-sm-3 col-md-2" style="display: none;">
                        <label>Bodega destino<span style="color: red">*</span></label>
                        <select name="id_bodega_destino_movimiento_inventario" id="id_bodega_destino_movimiento_inventario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                        </select>
                        
                        <div class="invalid-feedback">
                            La bodega destino es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-3 col-md-2">
                        <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                        <input name="fecha_manual_movimiento_inventario" id="fecha_manual_movimiento_inventario" class="form-control form-control-sm" type="date" required disabled>
                        <div class="invalid-feedback">
                            La fecha es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-3 col-md-2">
                        <label>Cédula / Nit<span style="color: red">*</span></label>
                        <select name="id_nit_movimiento_inventario" id="id_nit_movimiento_inventario" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                        </select>
                        
                        <div class="invalid-feedback">
                            La cédula / nit es requerida
                        </div>
                    </div>

                    <div class="form-group col-6 col-md-2 col-sm-2">
                        <label for="example-text-input" class="form-control-label">Consecutivo</label>
                        <input type="text" class="form-control form-control-sm" name="consecutivo_movimiento_inventario" id="consecutivo_movimiento_inventario" disabled required>
                    </div>

                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaMovimientoInventario" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR MOVIMIENTO</b>
                    </span>
                    <span id="iniciarCapturaMovimientoInventarioLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="cancelarCapturaMovimientoInventario" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR MOVIMIENTO</b>
                    </span>
                    <span id="agregarMovimientoInventario" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; display:none;">
                        <i class="fas fa-plus-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR PRODUCTO</b>
                    </span>
                    <span id="crearCapturaMovimientoInventarioDisabled" href="javascript:void(0)" class="badge bg-success" style="min-width: 40px; display:none; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR MOVIMIENTO</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaMovimientoInventario" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR MOVIMIENTO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>