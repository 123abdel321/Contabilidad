<div class="accordion" id="accordionRentalCambioDatos">
    <div class="accordion-item">
        <h5 class="accordion-header">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCambioDatosInforme" aria-expanded="false" aria-controls="collapseCambioDatosInforme">
                Filtros cambios en los datos
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseCambioDatosInforme" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
            <div class="accordion-body text-sm" style="padding: 0 !important;">

                <form id="cambiosDatosFilterForm" class="needs-validation mt-3" style="margin-top: 10px; margin-bottom: 10px;" novalidate>
                    <div class="row">

                        <input type="hidden" name="id_cambio_datos_cargado" id="id_cambio_datos_cargado">

                        <!-- Comprobante -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="id_comprobante_cambio_datos" class="form-label">Comprobante <span style="color: red">*</span></label>
                            <select name="id_comprobante_cambio_datos" id="id_comprobante_cambio_datos" class="form-select form-select-sm" required>
                            </select>
                        </div>

                        <!-- Fecha -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="fecha_manual_cambio_datos" class="form-label">Fecha</label>
                            <input name="fecha_manual_cambio_datos" id="fecha_manual_cambio_datos" class="form-control form-control-sm">
                        </div>

                        <!-- Cedula/Nit -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="id_nit_cambio_datos" class="form-label">Cédula/Nit</label>
                            <select name="id_nit_cambio_datos" id="id_nit_cambio_datos" class="form-select form-select-sm">
                            </select>
                        </div>

                        <!-- Centro costos -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="id_cecos_cambio_datos" class="form-label">Centro de costos</label>
                            <select name="id_cecos_cambio_datos" id="id_cecos_cambio_datos" class="form-select form-select-sm">
                            </select>
                        </div>

                        <!-- Cuenta -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="id_cuenta_cambio_datos" class="form-label">Cuenta</label>
                            <select name="id_cuenta_cambio_datos" id="id_cuenta_cambio_datos" class="form-select form-select-sm">
                            </select>
                        </div>

                        <!-- Valor documento -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label">Valor del documento</label>
                            <div class="" style="display: flex;">
                                <input id="precio_desde_cambio_datos" name="precio_desde_cambio_datos" placeholder="Ej: 50.000" class="form-control form-control-sm" style="height: 28px; border-bottom-left-radius: 5px; border-top-left-radius: 5px; border-bottom-right-radius: 0px; border-top-right-radius: 0px; border-right: solid 1px #cfcfcf;">
                                <span class="input-group-text" style="height: 28px; border-radius: 0px; border-left: solid 0px; border-right: solid 0px;">Hasta</span>
                                <input id="precio_hasta_cambio_datos" name="precio_hasta_cambio_datos" placeholder="Ej: 100.000" class="form-control form-control-sm" style="height: 28px; border-bottom-right-radius: 5px; border-top-right-radius: 5px; border-bottom-left-radius: 0px; border-top-left-radius: 0px;">
                            </div>
                        </div>

                        <!-- Factura -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="documento_referencia_cambio_datos" class="form-label">Doc. Referencia</label>
                            <input name="documento_referencia_cambio_datos" id="documento_referencia_cambio_datos" type="text" class="form-control form-control-sm">
                        </div>

                        <!-- Consecutivo -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label">Consecutivo</label>
                            <div class="" style="display: flex;">
                                <input id="consecutivo_desde_cambio_datos" name="consecutivo_desde_cambio_datos" class="form-control form-control-sm" placeholder="Ej: 1" style="height: 28px; border-bottom-left-radius: 5px; border-top-left-radius: 5px; border-bottom-right-radius: 0px; border-top-right-radius: 0px; border-right: solid 1px #cfcfcf;">
                                <span class="input-group-text" style="height: 28px; border-radius: 0px; border-left: solid 0px; border-right: solid 0px;">Hasta</span>
                                <input id="consecutivo_hasta_cambio_datos" name="consecutivo_hasta_cambio_datos" class="form-control form-control-sm" placeholder="Ej: 100" style="height: 28px; border-bottom-right-radius: 5px; border-top-right-radius: 5px; border-bottom-left-radius: 0px; border-top-left-radius: 0px;">
                            </div>
                        </div>

                        <!-- Concepto -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="concepto_cambio_datos" class="form-label form-control-sm">Concepto</label>
                            <input name="concepto_cambio_datos" id="concepto_cambio_datos" type="text" class="form-control form-control-sm">
                        </div>

                        <!-- Agrupar por -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="agrupar_cambio_datos" class="form-label form-control-sm">Agrupar por</label>
                            <select class="form-select form-select-sm" id="agrupar_cambio_datos" name="agrupar_cambio_datos">
                                <option value="id_cuenta">Cuenta</option>
                                <option value="id_nit">Cédula/Nits</option>
                                <option value="id_comprobante">Comprobante</option>
                                <option value="id_centro_costos">Centro costos</option>
                                <option value="consecutivo" selected>Documento</option>
                            </select>
                        </div>

                    </div>
                </form>

                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarCambioDatos" href="javascript:void(0)" class="btn badge bg-gradient-info btn-bg-gold" style="min-width: 40px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;BUSCAR</b>
                    </span>
                    <span id="generarCambioDatosLoading" class="badge bg-gradient-info btn-bg-gold-loading" style="display:none; min-width: 40px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;BUSCANDO</b>
                    </span>
                    <!-- BOTON PDF -->
                    <span id="descargarCambioDatos" class="btn badge bg-gradient-dark btn-bg-danger" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fa-solid fa-file-signature" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;CAMBIAR DATOS</b>
                    </span>
                    <span id="descargarCambioDatosLoading" class="badge bg-gradient-dark btn-bg-danger-loading" style="min-width: 40px; margin-right: 3px; display:none;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;CAMBIAR DATOS</b>
                    </span>
                    <span id="descargarCambioDatosDisabled" class="badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; margin-top: 5px;">
                        <i class="fa-solid fa-file-signature" style="font-size: 17px; color: #adadad;"></i>
                        <b style="vertical-align: text-top;">&nbsp;CAMBIAR DATOS</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>