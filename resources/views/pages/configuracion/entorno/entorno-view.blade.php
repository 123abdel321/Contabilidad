<style>

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent;">
            <form id="entornoForm" class="card-body row">
                

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Variables de entorno</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="facturacion-tab" data-bs-toggle="tab" data-bs-target="#facturacion" type="button" role="tab" aria-controls="facturacion" aria-selected="false">Facturación</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nomina-tab" data-bs-toggle="tab" data-bs-target="#nomina" type="button" role="tab" aria-controls="nomina" aria-selected="false">Nomina</button>
                    </li>
                    <!-- 
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</button>
                    </li> -->
                </ul>
                <div class="tab-content" id="myTabContent" style="background-color: white;">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                        <div class="row" style="margin-top: 10px; padding-left: 10px;">

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="cuenta_utilidad" class="form-control-label">Cuenta utilidad </label>
                                <input type="text" class="form-control form-control-sm" name="cuenta_utilidad" id="cuenta_utilidad">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="cuenta_perdida" class="form-control-label">Cuenta perdida </label>
                                <input type="text" class="form-control form-control-sm" name="cuenta_perdida" id="cuenta_perdida">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="valor_uvt" class="form-control-label">Valor UVT </label>
                                <input type="number" class="form-control form-control-sm" name="valor_uvt" id="valor_uvt">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="porcentaje_iva_aiu" class="form-control-label">Porcentaje Iva AIU</label>
                                <input type="number" class="form-control form-control-sm" name="porcentaje_iva_aiu" id="porcentaje_iva_aiu">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="redondeo_gastos" class="form-control-label">Redondeo Gastos</label>
                                <input type="number" class="form-control form-control-sm" name="redondeo_gastos" id="redondeo_gastos">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="fecha_ultimo_cierre" class="form-control-label">Fecha ultimo cierre</label>
                                <input type="date" class="form-control form-control-sm" name="fecha_ultimo_cierre" id="fecha_ultimo_cierre">
                            </div>

                            <!-- <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="example-text-input" class="form-control-label">Observación general factura venta </label>
                                <textarea type="text" class="form-control form-control-sm" name="observacion_venta" id="observacion_venta" rows="3"></textarea>
                            </div> -->

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="iva_incluido" id="iva_incluido" style="height: 20px;">
                                <label class="form-check-label" for="iva_incluido">
                                    Iva incluido
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="capturar_documento_descuadrado" id="capturar_documento_descuadrado" style="height: 20px;">
                                <label class="form-check-label" for="capturar_documento_descuadrado">
                                    Documentos descuadrados
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="vendedores_ventas" id="vendedores_ventas" style="height: 20px;">
                                <label class="form-check-label" for="vendedores_ventas">
                                    Vendedores captura ventas
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="ubicacion_maximoph" id="ubicacion_maximoph" style="height: 20px;">
                                <label class="form-check-label" for="ubicacion_maximoph">
                                    Ubicacion MaximoPh en informes
                                </label>
                            </div>

                        </div>

                        <br/>

                    </div>
                    <div class="tab-pane fade" id="facturacion" role="tabpanel" aria-labelledby="facturacion-tab">
                        <div class="row" style="margin-top: 10px; padding-left: 10px;">
                            <div class="form-group col-12">
                                <label for="editor-container" class="form-control-label">Observación general factura venta</label>
                                <div id="editor-container" style="height: 150px;"></div>
                                <textarea name="observacion_venta" id="observacion_venta" hidden></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nomina" role="tabpanel" aria-labelledby="nomina-tab">
                        <div class="row" style="margin-top: 10px; padding-left: 10px;">

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="salario_minimo" class="form-control-label">Valor Salario minimo </label>
                                <i class="fas fa-info icon-info" style="float: inline-end;" title="
                                    <b class='titulo-popover'>Salario mínimo:</b><br>
                                    Valor oficial vigente del salario mínimo mensual legal." data-toggle="popover" data-html="true"></i>
                                <input type="text" data-type="currency" class="form-control form-control-sm" name="salario_minimo" id="salario_minimo" style="text-align: right;">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="subsidio_transporte" class="form-control-label">Valor Subsidio transporte </label>
                                <i class="fas fa-info icon-info" style="float: inline-end;" title="
                                    <b class='titulo-popover'>Subsidio de transporte:</b><br>
                                    Valor mensual otorgado a trabajadores que ganen hasta 2 salarios mínimos." data-toggle="popover" data-html="true"></i>
                                <input type="text" data-type="currency" class="form-control form-control-sm" name="subsidio_transporte" id="subsidio_transporte" style="text-align: right;">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="cuenta_x_pagar_empleados" class="form-control-label">Cuenta por pagar empleados </label>
                                <i class="fas fa-info icon-info" style="float: inline-end;" title="
                                    <b class='titulo-popover'>Cuenta por pagar empleados:</b><br>
                                    Cuenta contable donde se registran las obligaciones laborales pendientes de pago." data-toggle="popover" data-html="true"></i>
                                <input type="text" class="form-control form-control-sm" name="cuenta_x_pagar_empleados" id="cuenta_x_pagar_empleados">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="cuenta_contable_pago_nomina" class="form-control-label">Cuenta contable pago </label>
                                <i class="fas fa-info icon-info" style="float: inline-end;" title="
                                    <b class='titulo-popover'>Cuenta contable de pago:</b><br>
                                    Cuenta donde se registra el pago real de la nómina." data-toggle="popover" data-html="true"></i>
                                <input type="text" class="form-control form-control-sm" name="cuenta_contable_pago_nomina" id="cuenta_contable_pago_nomina">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="cuenta_bancaria_nomina" class="form-control-label">Numero cuenta bancaria </label>
                                <i class="fas fa-info icon-info" style="float: inline-end;" title="
                                    <b class='titulo-popover'>Cuenta bancaria:</b><br>
                                    Número de cuenta bancaria desde la cual se realiza el pago de nómina." data-toggle="popover" data-html="true"></i>
                                <input type="text" class="form-control form-control-sm" name="cuenta_bancaria_nomina" id="cuenta_bancaria_nomina">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="tipo_cuenta_banco" class="form-control-label">Tipo cuenta banco </label>
                                <i class="fas fa-info icon-info" style="float: inline-end;" title="
                                    <b class='titulo-popover'>Tipo de cuenta:</b><br>
                                    Selecciona si la cuenta es de ahorros o corriente." data-toggle="popover" data-html="true"></i>
                                <select name="tipo_cuenta_banco" id="tipo_cuenta_banco" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                    <option value="ahorros">Ahorros</option>
                                    <option value="corrientes">Corrientes</option>
                                </select>
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="id_comprobante_nomina" class="form-control-label">Comprobante nomina </label>
                                <select name="id_comprobante_nomina" id="id_comprobante_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="id_comprobante_parafiscales" class="form-control-label">Comprobante parafiscales </label>
                                <select name="id_comprobante_parafiscales" id="id_comprobante_parafiscales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="id_comprobante_seguridad_social" class="form-control-label">Comprobante seguridad social </label>
                                <select name="id_comprobante_seguridad_social" id="id_comprobante_seguridad_social" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="id_comprobante_prestaciones_sociales" class="form-control-label">Comprobante prestaciones sociales </label>
                                <select name="id_comprobante_prestaciones_sociales" id="id_comprobante_prestaciones_sociales" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="no_exonerado_parafiscales" id="no_exonerado_parafiscales" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i class="fas fa-info icon-info" style="float: inline-end;" title="
                                    <b class='titulo-popover'>No exonerado:</b><br>
                                    Indica si la empresa NO está exonerada del pago de aportes parafiscales (SENA, ICBF, cajas)." data-toggle="popover" data-html="true"></i>
                                <label class="form-check-label" for="no_exonerado_parafiscales">
                                    No exonerado parafiscales
                                </label>
                            </div>

                            <br/><br/><br/>

                        </div> 
                    </div>
                </div>
                <div style="background-color: white;">
                    <button type="button" class="btn btn-primary btn-sm" id="updateEntorno">Actualizar datos</button>
                    <button id="updateEntornoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
                <!--  -->
            </form>
        </div>

    </div>
</div>

<script>
    var variablesEntorno = JSON.parse('<?php echo $variables_entorno; ?>');
</script>