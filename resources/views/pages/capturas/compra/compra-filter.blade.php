<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCompraGeneral" aria-expanded="false" aria-controls="collapseCompraGeneral">
                Datos de captura
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseCompraGeneral" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="compraFilterForm" class="needs-validation row" style="margin-top:10px;" novalidate>

                    <div class="form-group col-12 col-sm-6 col-md-6 col-lg-4">
                        <label for="id_cliente_compra">Proveedor<span style="color:red">*</span></label>
                        <div class="input-group">
                            <select name="id_cliente_compra" id="id_cliente_compra" class="form-control form-control-sm" style="font-size:13px;" required></select>
                            <span id="btn_ver_cliente_compra" onclick="openModalViewNitCompra()" class="btn badge bg-gradient-light btn-cliente-action" title="Ver proveedor" style="min-width:40px;height:30px;border-radius:0;box-shadow:none;display:none;"><i class="fas fa-eye" style="font-size:15px;margin-top:2px;"></i></span>
                            <span @if($puede_editar_nit) onclick="openModalEditNitCompra()" @endif class="btn badge bg-gradient-light btn-cliente-action {{ !$puede_editar_nit ? 'disabled' : '' }}" title="Editar proveedor" style="min-width:40px;height:30px;border-radius:0;box-shadow:none;"><i class="fas fa-user-edit" style="font-size:15px;margin-top:2px;"></i></span>
                            <span @if($puede_crear_nit) onclick="openModalNewNitCompra()" @endif class="btn badge bg-gradient-light btn-cliente-action {{ !$puede_crear_nit ? 'disabled' : '' }}" title="Crear proveedor" style="min-width:40px;height:30px;border-radius:0 5px 5px 0;box-shadow:none;"><i class="fas fa-user-plus" style="font-size:15px;margin-top:2px;"></i></span>
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-6 col-md-3 col-lg-2">
                        <label for="id_comprobante_compra">Comprobante<span style="color:red">*</span></label>
                        <select name="id_comprobante_compra" id="id_comprobante_compra" class="form-control form-control-sm" style="width:100%;font-size:13px;" required></select>
                        <div class="invalid-feedback">El comprobante es requerido</div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3 col-lg-2">
                        <label for="id_bodega_compra">Bodega<span style="color:red">*</span></label>
                        <select name="id_bodega_compra" id="id_bodega_compra" class="form-control form-control-sm" style="width:100%;font-size:13px;" required></select>
                        <div class="invalid-feedback">La bodega es requerida</div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3 col-lg-2">
                        <label for="fecha_manual_compra">Fecha<span style="color:red">*</span></label>
                        <input name="fecha_manual_compra" id="fecha_manual_compra" class="form-control form-control-sm" type="datetime-local" required>
                        <div class="invalid-feedback">La fecha es requerida</div>
                    </div>

                    <div class="form-group col-6 col-sm-4 col-md-3 col-lg-2">
                        <label for="documento_referencia_compra">No. factura<span style="color:red">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="documento_referencia_compra" id="documento_referencia_compra" onkeydown="buscarFacturaCompra(event)" style="background-position:right 0.75rem center !important;" required>
                        <i class="fa fa-spinner fa-spin fa-fw compra-load" id="documento_referencia_compra_loading" style="display:none;"></i>
                        <div class="invalid-feedback" id="error_documento_referencia_compra">El No. factura es requerido</div>
                    </div>

                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="iniciarCapturaCompra" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-gold" style="min-width: 40px;">
                        <i class="fas fa-folder-open" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">INICIAR COMPRA</b>
                    </span>
                    <span id="iniciarCapturaCompraLoading" class="badge bg-gradient-info btn-bg-gold" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                    <span id="cancelarCapturaCompra" href="javascript:void(0)" class="btn badge bg-gradient-danger btn-bg-danger" style="min-width: 40px; display:none;">
                        <i class="fas fa-times-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CANCELAR COMPRA</b>
                    </span>
                    
                    <span id="agregarCompraProducto" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-info" style="min-width: 40px; display:none;">
                        <i class="fas fa-plus-circle" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR PRODUCTO</b>
                    </span>
                    <!-- GRABAR COMPRA -->
                    <span id="crearCapturaCompraDisabled" href="javascript:void(0)" class="badge bg-gradient-dark" style="min-width: 40px; display:none; float: right; cursor: no-drop; margin-top: 5px;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR COMPRA</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <span id="crearCapturaCompra" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px; display:none; float: right;">
                        <i class="fas fa-save" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">GRABAR COMPRA</b>
                    </span>
                    <span id="crearCapturaCompraLoading" class="badge bg-gradient-success btn-bg-excel" style="display:none; min-width: 40px; margin-bottom: 16px; float: right;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">CARGANDO</b>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>