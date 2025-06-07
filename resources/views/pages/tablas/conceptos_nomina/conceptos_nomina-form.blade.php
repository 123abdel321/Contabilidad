<div class="modal fade" id="conceptosNominaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textConceptosNominaCreate" style="display: none;">Agregar concepto nomina</h5>
                <h5 class="modal-title" id="textConceptosNominaUpdate" style="display: none;">Editar concepto nomina</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="conceptosNominaForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_conceptos_nomina_up" id="id_conceptos_nomina_up" style="display: none;">

                        <h6 style="">1. General</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="tipo_concepto_nomina">Tipo concepto <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info" 
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Tipo de Concepto:</b> Clasificación del concepto según normativa DIAN para nómina electrónica."
                                data-toggle="popover" 
                                data-html="true"
                            >
                            </i>
                            <select name="tipo_concepto_nomina" id="tipo_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                @foreach ($conceptosNomina as $conceptoNomina)
                                    <option value="{{ $conceptoNomina['codigo'] }}">{{ $conceptoNomina['nombre'] }}</option>
                                @endforeach
                            </select>
                            
                            <div class="invalid-feedback">
                                El tipo concepto es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="codigo_concepto_nomina" class="form-control-label">Código <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info" 
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Código de Concepto:</b> Identificador numérico único según tabulación DIAN. Ej: '001' para Salario, '022' para Horas Extras."
                                data-toggle="popover" 
                                data-html="true"
                            >
                            </i>
                            <input type="text" class="form-control form-control-sm" name="codigo_concepto_nomina" id="codigo_concepto_nomina" placeholder="007" onkeypress="focusNexInput(event, 'nombre_concepto_nomina')" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_concepto_nomina" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info" 
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Nombre Descriptivo:</b> Nombre legible para identificar el concepto en reportes y liquidaciones. Ej: 'Salario Básico', 'Bonificación Alimentación'."
                                data-toggle="popover" 
                                data-html="true"
                            >
                            </i>
                            <input type="text" class="form-control form-control-sm" name="nombre_concepto_nomina" id="nombre_concepto_nomina" placeholder="Nombre concepto" onkeypress="focusNexInput(event, 'dias_salario')" required>
                        </div>

                        <h6 style="">2. Cuentas contables <i
                            class="fas fa-info icon-info" 
                            style=""
                            title="<b class='titulo-popover'>Cuenta Contable:</b> Asocie cada concepto a la cuenta correspondiente según el área:<br>
                                • <b class='mensaje-blanco'>Administrativos</b>: Gastos administrativos<br>
                                • <b class='mensaje-blanco'>Operativos</b>: Costos de producción<br>
                                • <b class='mensaje-blanco'>Ventas</b>: Gastos de ventas"
                            data-toggle="popover" 
                            data-html="true"
                        >
                        </i></h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_administrativos_concepto_nomina">Cuenta administrativos</label>
                            <select name="id_cuenta_administrativos_concepto_nomina" id="id_cuenta_administrativos_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" >
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_operativos_concepto_nomina">Cuenta Operativos</label>
                            <select name="id_cuenta_operativos_concepto_nomina" id="id_cuenta_operativos_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" >
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_ventas_concepto_nomina">Cuenta ventas</label>
                            <select name="id_cuenta_ventas_concepto_nomina" id="id_cuenta_ventas_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" >
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_cuenta_otros_concepto_nomina">Cuenta otros</label>
                            <select name="id_cuenta_otros_concepto_nomina" id="id_cuenta_otros_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" >
                            </select>
                        </div>

                        <h6 style="">3. Comportamiento</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="porcentaje_concepto_nomina" class="form-control-label">Porcentaje <span style="color: red">*</span></label>
                            <input type="number" class="form-control form-control-sm" name="porcentaje_concepto_nomina" id="porcentaje_concepto_nomina" placeholder="10" value="0" onkeypress="focusNexInput(event, 'dias_salario')" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_concepto_porcentaje_concepto_nomina">Concepto para porcentaje</label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Concepto Base:</b> Seleccione el concepto sobre el cual se calculará el porcentaje. Si se deja vacío, se usará el salario base del empleado."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="id_concepto_porcentaje_concepto_nomina" id="id_concepto_porcentaje_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="unidad_concepto_nomina">Concepto para porcentaje</label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Unidad de Cálculo:</b><br>
                                        • <b class='mensaje-blanco'>Horas</b>: Valor calculado por horas trabajadas<br>
                                        • <b class='mensaje-blanco'>Días</b>: Valor calculado por días laborados<br>
                                        • <b class='mensaje-blanco'>Valor</b>: Monto fijo independiente del tiempo"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="unidad_concepto_nomina" id="unidad_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                <option value="0">Horas</option>
                                <option value="1">Días</option>
                                <option value="2">Valor</option>
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="valor_mensual_concepto_nomina" class="form-control-label">Valor mensual <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Valor de Referencia:</b> Monto base para cálculos cuando la unidad es Horas/Días. Si está vacío, se usará el salario registrado del empleado."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input type="number" class="form-control form-control-sm" name="valor_mensual_concepto_nomina" id="valor_mensual_concepto_nomina" placeholder="Vacío toma el salario mensual" value="0" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="concepto_fijo_concepto_nomina">Concepto fijo</label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Concepto fijo:</b> Si es fijo al agregar este concepto como novedad en un empleado el sistema recordará agregarlo nuevamente al iniciar otro periodo."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="concepto_fijo_concepto_nomina" id="concepto_fijo_concepto_nomina" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccionar</option>
                                <option value="0">Manual</option>
                                <option value="1">Fijo</option>
                            </select>
                        </div>

                        <h6 style="">4. Bases</h6>

                        <div class="col-12 row" style="margin-top: 5px; padding-left: 25px;">
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_retencion_conceptos_nomina" id="base_retencion_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Retención en la Fuente (Art. 383 ET):</b><br>
                                            <b class='mensaje-blanco'>Incluir:</b> Salario básico, horas extras, bonificaciones habituales<br>
                                            <b class='mensaje-blanco'>Excluir:</b> Auxilio de transporte, prestaciones sociales, viáticos<br>
                                            <b class='mensaje-blanco'>Normativa:</b> Artículo 383 del Estatuto Tributario (Modificado por Ley 2155 de 2021)"
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_retencion_conceptos_nomina">Retención</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_sena_conceptos_nomina" id="base_sena_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Aporte SENA:</b> Active si este concepto hace parte de la base para calcular el 2% de aporte al SENA (Ley 21 de 1982). Aplica sobre nómina mensual."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_sena_conceptos_nomina">SENA</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_icbf_conceptos_nomina" id="base_icbf_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Aporte ICBF:</b> Marque si el concepto se incluye en la base para calcular el 3% de aporte al ICBF (Ley 21 de 1982). No incluye valores exentos como auxilio de transporte."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_icbf_conceptos_nomina">ICBF</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_caja_compensacion_conceptos_nomina" id="base_caja_compensacion_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Cajas de Compensación:</b> Active si el concepto suma para calcular el 4% de aporte a cajas de compensación familiar. Excluye conceptos no salariales."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_caja_compensacion_conceptos_nomina">Caja compensación</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_salud_conceptos_nomina" id="base_salud_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Salud:</b> Marque si el concepto se incluye en la base para calcular el 12.5% de aporte a salud (4% empleado + 8.5% empleador). Aplica sobre salario base."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_salud_conceptos_nomina">Salud</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_pension_conceptos_nomina" id="base_pension_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Pensión:</b> Active si el concepto suma para calcular el 16% de aporte a pensiones (4% empleado + 12% empleador). Incluye salario y extras legales."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_pension_conceptos_nomina">Pensión</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_arl_conceptos_nomina" id="base_arl_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base ARL:</b> Marque si el concepto se incluye en la base para calcular aportes a riesgos laborales (0.522% a 6.96% según riesgo). Solo salario básico."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_arl_conceptos_nomina">ARL</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_vacacion_conceptos_nomina" id="base_vacacion_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Vacaciones:</b> Active si el concepto se incluye en la base para liquidar vacaciones (4.17% por mes trabajado). Solo salario básico y permanencia."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_vacacion_conceptos_nomina">Vacación</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_prima_conceptos_nomina" id="base_prima_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Prima de Servicios:</b> Marque si el concepto suma para liquidar prima (8.33% por semestre). Incluye salario básico, extras y bonos salariales."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_prima_conceptos_nomina">Prima</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_cesantia_conceptos_nomina" id="base_cesantia_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Cesantías:</b> Active si el concepto se incluye en la base para liquidar cesantías (8.33% por año). Incluye salario básico y auxilio de transporte."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_cesantia_conceptos_nomina">Cesantía</label>
                            </div>
                            <div class="form-check form-switch col-6">
                                <input class="form-check-input" type="checkbox" name="base_interes_cesantia_conceptos_nomina" id="base_interes_cesantia_conceptos_nomina" style="height: 20px;">
                                &nbsp;&nbsp;
                                <i
                                    class="fas fa-info icon-info"
                                    style="float: inline-end;"
                                    title="<b class='titulo-popover'>Base Intereses de Cesantías:</b> Marque si el concepto suma para calcular intereses sobre cesantías (12% anual). Aplica sobre el valor liquidado de cesantías."
                                    data-toggle="popover"
                                    data-html="true"
                                ></i>
                                <label class="form-check-label" for="base_interes_cesantia_conceptos_nomina">Interés cesantia</label>
                            </div>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveConceptosNomina"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateConceptosNomina"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveConceptosNominaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>