<div class="modal fade" id="contratosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textContratosCreate" style="display: none;">Agregar Contrato</h5>
                <h5 class="modal-title" id="textContratosUpdate" style="display: none;">Editar Contrato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="contratosForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_contratos_up" id="id_contratos_up" style="display: none;">
                        
                        <h6 class="section-title bg-light p-2 mb-3">1. Información Básica del Contrato</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_empleado_contrato_nomina">Empleado <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Empleado:</b> Seleccione el empleado al que se asociará este contrato. Debe estar previamente registrado en el sistema."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="id_empleado_contrato_nomina" id="id_empleado_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_periodo_contrato_nomina">Periodo de Nómina <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Período de nómina:</b> Seleccione el período de liquidación al que pertenece este contrato. Define la frecuencia de pago (quincenal, mensual, etc.)."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="id_periodo_contrato_nomina" id="id_periodo_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_concepto_basico_contrato_nomina">Concepto basico <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Concepto Base:</b> Concepto sobre el cual se calculará el porcentaje. Si se deja vacío, se usará el salario base del empleado según Resolución 2.5.8.1.11 de la DIAN."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="id_concepto_basico_contrato_nomina" id="id_concepto_basico_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>

                        <h6 class="section-title bg-light p-2 mb-3">2. Fechas del Contrato</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="fecha_inicio_contrato_nomina" class="form-control-label">Fecha inicio contrato <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Fecha de inicio:</b> Fecha en que inicia la relación laboral según contrato. Obligatorio para generación de nómina electrónica (Art. 1.2.1.5.5 del Decreto 1627 de 2016)."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input name="fecha_inicio_contrato_nomina" id="fecha_inicio_contrato_nomina" class="form-control form-control-sm" type="date" required>
                            <div class="invalid-feedback">
                                La fecha inicio contrato es requerida
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="fecha_fin_contrato_nomina" class="form-control-label">Fecha final contrato </label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Fecha de finalización:</b> Para contratos a término fijo. <br>Dejar vacío para contratos a término indefinido según Art. 46 del Código Sustantivo del Trabajo."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input name="fecha_fin_contrato_nomina" id="fecha_fin_contrato_nomina" class="form-control form-control-sm" type="date">
                            <div class="invalid-feedback">
                                La fecha final contrato es requerida
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="fecha_inicio_periodo_nomina" class="form-control-label">Fecha inicio periodo</label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Fecha de inicio:</b> Fecha en que inicia la relación laboral según contrato. Obligatorio para generación de nómina electrónica (Art. 1.2.1.5.5 del Decreto 1627 de 2016)."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input name="fecha_inicio_periodo_nomina" id="fecha_inicio_periodo_nomina" class="form-control form-control-sm" type="date" disabled>
                            <div class="invalid-feedback">
                                La fecha inicio periodo es requerida
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="fecha_fin_periodo_nomina" class="form-control-label">Fecha fin periodo</label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Fecha de inicio:</b> Fecha en que inicia la relación laboral según contrato. Obligatorio para generación de nómina electrónica (Art. 1.2.1.5.5 del Decreto 1627 de 2016)."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input name="fecha_fin_periodo_nomina" id="fecha_fin_periodo_nomina" class="form-control form-control-sm" type="date" disabled>
                            <div class="invalid-feedback">
                                La fecha fin periodo es requerida
                            </div>
                        </div>

                        <h6 class="section-title bg-light p-2 mb-3">3. Configuración del Contrato</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="estado_contrato_nomina">Estado <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Estado del contrato:</b><br> 
                                    <b class='mensaje-blanco'>Inactivo:</b> (no genera liquidación)<br>
                                    <b class='mensaje-blanco'>Activo:</b> (genera liquidación)<br>
                                    <b class='mensaje-blanco'>Finalizado:</b> (contrato terminado)."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="estado_contrato_nomina" id="estado_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                <option value="0">Inactivo</option>
                                <option value="1">Activo</option>
                                <option value="2">Finalizado</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                El estado es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="termino_contrato_nomina">Término <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Tipo de término:</b><br>
                                    <b class='mensaje-blanco'>Indefinido:</b> (contrato sin fecha final)<br>
                                    <b class='mensaje-blanco'>Fijo:</b> (término definido, Art. 46 CST)<br>
                                    <b class='mensaje-blanco'>Obra-Labor:</b> (para obra específica, Art. 50 CST)<br>
                                    <b class='mensaje-blanco'>Transitorio:</b> (temporal, Art. 51 CST)"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="termino_contrato_nomina" id="termino_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                <option value="0">Indefinido</option>
                                <option value="1">Fijo</option>
                                <option value="2">Obra-Labor</option>
                                <option value="3">Transitorio</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                El termino es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="tipo_salario_contrato_nomina">Tipo salario <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Tipo de salario:</b><br>
                                    <b class='mensaje-blanco'>Normal: </b> (salario mínimo + prestaciones)<br>
                                    <b class='mensaje-blanco'>Honorarios: </b> (Ley 50 de 1990)<br>
                                    <b class='mensaje-blanco'>Integral: </b> (mínimo 10 SMMLV)<br>
                                    <b class='mensaje-blanco'>Servicios: </b> (contrato de servicios)<br>
                                    <b class='mensaje-blanco'>Practicante: </b> (convenio de aprendizaje)"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="tipo_salario_contrato_nomina" id="tipo_salario_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                <option value="0">Normal</option>
                                <option value="1">Honorarios</option>
                                <option value="2">Integral</option>
                                <option value="3">Servicios</option>
                                <option value="4">Practicante</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                El termino es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="tipo_empleado_contrato_nomina">Tipo empleado <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Tipo de empleado:</b><br>
                                    <b class='mensaje-blanco'>Administrativo: </b> oficina<br>
                                    <b class='mensaje-blanco'>Operativo: </b> producción<br>
                                    <b class='mensaje-blanco'>Ventas: </b> comercial<br>
                                    <b class='mensaje-blanco'>Otros: </b> otros departamentos"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="tipo_empleado_contrato_nomina" id="tipo_empleado_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                <option value="0">Administrativo</option>
                                <option value="1">Operativo</option>
                                <option value="2">Ventas</option>
                                <option value="3">Otros</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                El tipo empleado es requerido
                            </div>
                        </div>

                        <h6 class="section-title bg-light p-2 mb-3">4. Seguridad Social (PILA)</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="tipo_cotizante_contrato_nomina">Tipo cotizante <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Tipo cotizante:</b><br>
                                    <b class='mensaje-blanco'>Dependiente:</b> empleado regular<br>
                                    <b class='mensaje-blanco'>Aprendiz etapa lectiva:</b> formación educativa<br>
                                    <b class='mensaje-blanco'>Aprendiz etapa productiva:</b> práctica laboral"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="tipo_cotizante_contrato_nomina" id="tipo_cotizante_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                <option value="1">Dependiente</option>
                                <option value="12">Aprendices en etapa lectiva</option>
                                <option value="19">Aprendices en etapa productiva</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                El tipo empleado es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="subtipo_cotizante_contrato_nomina">Subtipo cotizante <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Subtipo cotizante:</b><br>
                                    <b class='mensaje-blanco'>Pensionado vejez activo:</b> jubilado que sigue trabajando<br>
                                    <b class='mensaje-blanco'>Cotizante no obligado por edad:</b> mayor de edad pensionable<br>
                                    <b class='mensaje-blanco'>Pensionado con mesada >25 SMLMV:</b> ingresos elevados"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="subtipo_cotizante_contrato_nomina" id="subtipo_cotizante_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                <option value="">Ninguno</option>
                                <option value="1">Dependiente pensionado por vejez activo</option>
                                <option value="3">Cotizante no obligado a cotización a pensiones por edad</option>
                                <option value="9">Cotizante pensionado con mesada superior a 25 SMLMV</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                El tipo cotizante es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nivel_riesgo_arl_compensacion_contrato_nomina">Nivel ARL</label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Porcentaje ARL:</b><br>
                                    <b class='mensaje-blanco'>Riesgo I (Bajo):</b> 0.522%<br>
                                    <b class='mensaje-blanco'>Riesgo II (Medio):</b> 1.044%<br>
                                    <b class='mensaje-blanco'>Riesgo III (Alto):</b> 2.436%<br>
                                    <b class='mensaje-blanco'>Riesgo IV (Muy Alto):</b> 4.35%<br>
                                    <b class='mensaje-blanco'>Riesgo V (Extremo):</b> 6.96%"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="nivel_riesgo_arl_compensacion_contrato_nomina" id="nivel_riesgo_arl_compensacion_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                <option value="">Ninguno</option>
                                <option value="0">0 - 0%</option>
                                <option value="1">I (Bajo) - 0.522%</option>
                                <option value="2">II (Medio) - 1.044%</option>
                                <option value="3">III (Alto) - 2.436</option>
                                <option value="4">IV (Muy Alto) - 4.35%</option>
                                <option value="5">V (Extremo) - 6.96%</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="porcentaje_arl_contrato_nomina" class="form-control-label">Porcentaje ARL</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                            title="<b class='titulo-popover'>Porcentaje ARL:</b> Calculado automáticamente según nivel de riesgo seleccionado."
                            data-toggle="popover" data-html="true"></i>
                            <input type="number" class="form-control form-control-sm text-align-right" name="porcentaje_arl_contrato_nomina" id="porcentaje_arl_contrato_nomina" onfocus="this.select();" value="0" disabled>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_centro_costo_contrato_nomina">Centro de costos <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Centro de costo:</b> Área o departamento al que pertenece el empleado. Usado para distribución contable y análisis de costos."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="id_centro_costo_contrato_nomina" id="id_centro_costo_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6" style="display: none;">
                            <label for="id_oficio_contrato_nomina">Oficio</label>
                            <select name="id_oficio_contrato_nomina" id="id_oficio_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <h6 class="section-title bg-light p-2 mb-3">5. Información Salarial</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="salario_contrato_nomina" class="form-control-label">Salario base <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Salario:</b> Valor del salario base. Para salario integral debe ser mínimo 10 SMMLV (Art. 132 CST). Formato: $1,000,000.00"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="salario_contrato_nomina" id="salario_contrato_nomina" onfocus="this.select();" value="0">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="auxilio_transporte_contrato_nomina">Auxilio transporte <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                            title="<b class='titulo-popover'>Auxilio de transporte:</b> Solo aplica para salarios hasta 2 SMMLV (Art. 7, Ley 15 de 1959). No constituye salario (Art. 132 CST)."
                            data-toggle="popover" data-html="true"></i>
                            <select name="auxilio_transporte_contrato_nomina" id="auxilio_transporte_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_fondo_salud_contrato_nomina">Fondo salud</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;" title="<b class='titulo-popover'>EPS (Entidad Promotora de Salud):</b><br>
                                <b class='mensaje-blanco'>Registro obligatorio:</b> Para todos los empleados<br>
                                <b class='mensaje-blanco'>Ejemplos comunes:</b> Sanitas, Sura, Coomeva<br>
                                <b class='mensaje-blanco'>Tipo en sistema:</b> EPS"
                                data-toggle="popover" data-html="true">
                            </i>
                            <select name="id_fondo_salud_contrato_nomina" id="id_fondo_salud_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_fondo_pension_contrato_nomina">Fondo pensión</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;" title="<b class='titulo-popover'>AFP (Administradora de Fondos de Pensiones):</b><br>
                                <b class='mensaje-blanco'>Registro obligatorio:</b> Para empleados con contrato laboral<br>
                                <b class='mensaje-blanco'>Ejemplos comunes:</b> Porvenir, Protección, Colfondos<br>
                                <b class='mensaje-blanco'>Tipo en sistema:</b> AFP"
                                data-toggle="popover" data-html="true">
                            </i>
                            <select name="id_fondo_pension_contrato_nomina" id="id_fondo_pension_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_fondo_cesantias_contrato_nomina">Fondo cesantías</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;" title="<b class='titulo-popover'>Fondo de Cesantías:</b><br>
                                <b class='mensaje-blanco'>Puede ser:</b> AFP o compañía de seguros autorizada<br>
                                <b class='mensaje-blanco'>Mismo fondo de pensión:</b> En muchos casos<br>
                                <b class='mensaje-blanco'>Tipo en sistema:</b> AFP si usa el mismo fondo"
                                data-toggle="popover" data-html="true">
                            </i>
                            <select name="id_fondo_cesantias_contrato_nomina" id="id_fondo_cesantias_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_fondo_caja_compensacion_contrato_nomina">Caja de compensación</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;" title="<b class='titulo-popover'>CCF (Caja de Compensación Familiar):</b><br>
                                <b class='mensaje-blanco'>Registro obligatorio:</b> Para empleados con salario ≤ 4 SMLMV<br>
                                <b class='mensaje-blanco'>Ejemplos comunes:</b> Compensar, Cafam, Colsubsidio<br>
                                <b class='mensaje-blanco'>Tipo en sistema:</b> CCF"
                                data-toggle="popover" data-html="true">
                            </i>
                            <select name="id_fondo_caja_compensacion_contrato_nomina" id="id_fondo_caja_compensacion_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_fondo_arl_compensacion_contrato_nomina">Administradora de riesgos</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;" title="<b class='titulo-popover'>ARL (Administradora de Riesgos Laborales):</b><br>
                                <b class='mensaje-blanco'>Registro obligatorio:</b> Para todos los empleados<br>
                                <b class='mensaje-blanco'>Ejemplos comunes:</b> Positiva, Sura, Colmena<br>
                                <b class='mensaje-blanco'>Relación:</b> Con nivel de riesgo seleccionado<br>
                                <b class='mensaje-blanco'>Tipo en sistema:</b> ARL"
                                data-toggle="popover" data-html="true">
                            </i>
                            <select name="id_fondo_arl_compensacion_contrato_nomina" id="id_fondo_arl_compensacion_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <h6 class="section-title bg-light p-2 mb-3">6. Retención en la Fuente</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="metodo_retencion_compensacion_contrato_nomina">Método retención <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;" title="<b class='titulo-popover'>Método de retención:</b><br>
                                <b class='mensaje-blanco'>Mensual:</b> cálculo periodo a periodo (Art. 383 ET)<br>
                                <b class='mensaje-blanco'>Anual:</b> cálculo sobre ingreso anual proyectado"
                            data-toggle="popover" data-html="true"></i>
                            <select name="metodo_retencion_compensacion_contrato_nomina" id="metodo_retencion_compensacion_contrato_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                <option value="">Ninguno</option>
                                <option value="0">Método 1 (Mensual)</option>
                                <option value="1">Método 2 (Anual)</option>
                            </select>
                        </div>

                        <div id="div-porcentaje_fijo_contrato_nomina" class="form-group col-12 col-sm-6 col-md-6">
                            <label for="porcentaje_fijo_contrato_nomina" class="form-control-label">% Fijo Retención <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                            title="<b class='titulo-popover'>Porcentaje fijo:</b> Aplica cuando el método es anual. Porcentaje fijo para cálculo de retención."
                            data-toggle="popover" data-html="true"></i>
                            <input type="number" class="form-control form-control-sm text-align-right" name="porcentaje_fijo_contrato_nomina" id="porcentaje_fijo_contrato_nomina" onfocus="this.select();" value="0">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="disminucion_defecto_retencion_contrato_nomina" class="form-control-label">Disminución Base <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Disminución base:</b> Suma de ingresos no constitutivos de renta (Art. 206-2 ET), medicina prepagada, dependientes (Art. 387 ET), etc. Para cálculo de retención en la fuente."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="disminucion_defecto_retencion_contrato_nomina" id="disminucion_defecto_retencion_contrato_nomina" onfocus="this.select();" value="0">
                        </div>

                        <h6 class="section-title bg-light p-2 mb-3">7. Dotación (Art. 230 CST)</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="talla_camisa_contrato_nomina" class="form-control-label">Talla camisa</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                            title="<b class='titulo-popover'>Talla camisa:</b> Para dotación según Art. 230 CST (obligatorio para salarios ≤ 2 SMMLV). Ej: S, M, L, XL."
                            data-toggle="popover" data-html="true"></i>
                            <input type="text" class="form-control form-control-sm" name="talla_camisa_contrato_nomina" id="talla_camisa_contrato_nomina" onfocus="this.select();" placeholder="Ej: S, M, L, XL.">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="talla_pantalon_contrato_nomina" class="form-control-label">Talla pantalón</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                            title="<b class='titulo-popover'>Talla pantalón:</b> Para dotación según Art. 230 CST. Ej: 28, 30, 32, etc."
                            data-toggle="popover" data-html="true"></i>
                            <input type="text" class="form-control form-control-sm" name="talla_pantalon_contrato_nomina" id="talla_pantalon_contrato_nomina" onfocus="this.select();" placeholder="Ej: 28, 30, 32, etc." value="0">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="talla_zapatos_contrato_nomina" class="form-control-label">Talla zapatos</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                            title="<b class='titulo-popover'>Talla zapatos:</b> Para dotación según Art. 230 CST. Ej: 36, 38, 40, etc."
                            data-toggle="popover" data-html="true"></i>
                            <input type="text" class="form-control form-control-sm" name="talla_zapatos_contrato_nomina" id="talla_zapatos_contrato_nomina" onfocus="this.select();" placeholder="Ej: 36, 38, 40, etc." value="0">
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveContratos"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateContratos"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveContratosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>