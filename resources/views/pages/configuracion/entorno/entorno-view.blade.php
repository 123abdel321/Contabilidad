<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent; box-shadow: none;">
            <form id="entornoForm" class="card-body row">
                

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Contabilidad</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link" id="facturacion-tab" data-bs-toggle="tab" data-bs-target="#facturacion" type="button" role="tab" aria-controls="facturacion" aria-selected="false">Facturación</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link" id="nomina-tab" data-bs-toggle="tab" data-bs-target="#nomina" type="button" role="tab" aria-controls="nomina" aria-selected="false">Nomina</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link" id="notificaciones-tab" data-bs-toggle="tab" data-bs-target="#notificaciones" type="button" role="tab" aria-controls="notificaciones" aria-selected="false">Notificaciones</button>
                    </li>
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

                            <div class="col-12 col-sm-6 col-md-4">
                                <div class="form-check form-switch col-12">
                                    <input class="form-check-input" type="checkbox" name="capturar_documento_descuadrado" id="capturar_documento_descuadrado" style="height: 20px;">
                                    <label class="form-check-label" for="capturar_documento_descuadrado">
                                        Documentos descuadrados
                                    </label>
                                </div>
    
                                <div class="form-check form-switch col-12">
                                    <input class="form-check-input" type="checkbox" name="validar_salto_consecutivos" id="validar_salto_consecutivos" style="height: 20px;">
                                    <label class="form-check-label" for="validar_salto_consecutivos">
                                        Validar salto de consecutivos
                                    </label>
                                </div>
    
                                <div class="form-check form-switch col-12">
                                    <input class="form-check-input" type="checkbox" name="ubicacion_maximoph" id="ubicacion_maximoph" style="height: 20px;">
                                    <label class="form-check-label" for="ubicacion_maximoph">
                                        Ubicacion MaximoPh en informes
                                    </label>
                                </div>
                            </div>


                        </div>

                        <br/>

                    </div>
                    <div class="tab-pane fade" id="facturacion" role="tabpanel" aria-labelledby="facturacion-tab">

                        <div class="row" style="margin-top: 10px; padding-left: 10px;">

                            <div class="form-group col-12 col-sm-6 col-md-6">
                                <label for="example-text-input" class="form-control-label">Encabezado ventas regimen</label>
                                <input type="text" class="form-control form-control-sm" name="encabezado_ventas_regimen" id="encabezado_ventas_regimen">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-6" >
                                <label for="id_cliente_venta_defecto" class="form-control-label">Cliente por defecto Ventas </label>
                                <select name="id_cliente_venta_defecto" id="id_cliente_venta_defecto" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-6">
                                <label for="editor-container" class="form-control-label">Observación general factura venta</label>
                                <div id="editor-container" style="height: 150px;"></div>
                                <textarea name="observacion_venta" id="observacion_venta" hidden></textarea>
                            </div>

                            <div class="col-12 col-sm-6 col-md-6">
                                <div class="form-check form-switch col-12">
                                    <input class="form-check-input" type="checkbox" name="iva_incluido" id="iva_incluido" style="height: 20px;">
                                    <label class="form-check-label" for="iva_incluido">
                                        Iva incluido
                                    </label>
                                </div>
    
                                <div class="form-check form-switch col-12">
                                    <input class="form-check-input" type="checkbox" name="vendedores_ventas" id="vendedores_ventas" style="height: 20px;">
                                    <label class="form-check-label" for="vendedores_ventas">
                                        Vendedores captura ventas
                                    </label>
                                </div>
    
                                <div class="form-check form-switch col-12">
                                    <input class="form-check-input" type="checkbox" name="recordar_ultimo_precio_venta" id="recordar_ultimo_precio_venta" style="height: 20px;">
                                    <label class="form-check-label" for="recordar_ultimo_precio_venta">
                                        Recorder ultimo precio ventas
                                    </label>
                                </div>
    
                                <div class="form-check form-switch col-12">
                                    <input class="form-check-input" type="checkbox" name="precio_ponderado" id="precio_ponderado" style="height: 20px;">
                                    <label class="form-check-label" for="precio_ponderado">
                                        Promediar precio en compras
                                    </label>
                                </div>
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

                    <div class="tab-pane fade" id="notificaciones" role="tabpanel" aria-labelledby="notificaciones-tab">
                        <br/>
                        <div id="div-canales-eco" class="row" style="padding: 5px;">
                            <h6 class="section-title bg-light p-2 mb-3">1. Canales de Comunicación</h6> <br/>
                            
                            <div class="row" id="contenedor-canales">
                                
                                <div class="col-12 text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="text-sm mt-2 text-secondary">Obteniendo credenciales...</p>
                                </div>

                            </div>
                            
                        </div>

                        <div id="div-token-eco" class="container-fluid py-2" style="display: none;">
                            <div class="row justify-content-center">
                                <div class="col-lg-4 col-md-6">
                                    <div class="card shadow-sm text-center p-3 border-radius-xl">
                                        
                                        <div class="icon icon-shape icon-md bg-gradient-primary shadow mx-auto mb-2 border-radius-md">
                                            <i class="fas fa-key text-white opacity-10" style="font-size: 1rem;"></i>
                                        </div>

                                        <h6 class="font-weight-bolder mb-1">Autenticación Requerida</h6>
                                        
                                        <p class="text-secondary text-xs mb-3 px-2">
                                            Genera un token de acceso para configurar WhatsApp/Email.
                                        </p>

                                        <div id="btn-container-token">
                                            <button type="button" class="btn btn-sm bg-gradient-dark mb-0 w-100" onclick="generarTokenEco()">
                                                <i class="fas fa-sync-alt me-1"></i> Generar Token
                                            </button>
                                        </div>

                                        <div id="spinner-token" style="display: none;">
                                            <div class="spinner-border spinner-border-sm text-primary mt-2" role="status"></div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <br/>
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