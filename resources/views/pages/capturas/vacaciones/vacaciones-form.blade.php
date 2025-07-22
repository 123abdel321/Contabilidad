<div class="modal fade" id="vacacionesFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="textVacacionesCreate" style="display: none;">Agregar vacaciones</h5>
                <h5 class="modal-title" id="textVacacionesUpdate" style="display: none;">Editar vacaciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
                
                <form id="vacacionesForm">
                    <div class="row">
                        <input type="text" class="form-control" name="id_vacaciones_up" id="id_vacaciones_up" style="display: none;">
                        <input type="text" class="form-control" name="json_detalle_vacaciones" id="json_detalle_vacaciones" style="display: none;">

                        <h6 class="section-title bg-light p-2 mb-3">1. Información General</h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="id_empleado_vacaciones">Empleado <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Empleado:</b> Seleccione el empleado al que se asociará este contrato. Debe estar previamente registrado en el sistema."
                                data-toggle="popover" data-html="true"></i>
                            <select name="id_empleado_vacaciones" id="id_empleado_vacaciones" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required></select>
                            <div class="invalid-feedback">El empleado es requerido</div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="metodo_vacaciones">Método de liquidación <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Método:</b> Seleccione 'Fijo' si el salario del empleado es estable, o 'Variable' si se promedia con otros conceptos."
                                data-toggle="popover" data-html="true"></i>
                            <select name="metodo_vacaciones" id="metodo_vacaciones" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                <option value="">Seleccione un método de liquidación</option>
                                <option value="0">Fijo</option>
                                <option value="1">Variable</option>
                            </select>
                            <div class="invalid-feedback">El método de liquidación es requerido</div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="dias_habiles_vacaciones">Días hábiles de vacaciones <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Días hábiles:</b> Indique la cantidad de días hábiles de vacaciones que se disfrutarán."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="integer" class="form-control form-control-sm text-align-right" name="dias_habiles_vacaciones" id="dias_habiles_vacaciones" onfocus="this.select();" value="0" onfocusout="focusOutCalcularFechaFin()" onkeypress="enterPressCalcularFechaFin(event)" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="dias_no_habiles_vacaciones">Días no hábiles <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Días no hábiles:</b> Domingos o festivos dentro del período de vacaciones que no cuentan como hábiles."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="integer" class="form-control form-control-sm text-align-right" name="dias_no_habiles_vacaciones" id="dias_no_habiles_vacaciones" onfocus="this.select();" value="0" onfocusout="focusOutCalcularFechaFin()" onkeypress="enterPressCalcularFechaFin(event)" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="dias_compensados_vacaciones">Días compensados en dinero <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Días compensados:</b> Días de vacaciones que serán pagados en dinero en lugar de ser disfrutados."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="integer" class="form-control form-control-sm text-align-right" name="dias_compensados_vacaciones" id="dias_compensados_vacaciones" onfocus="this.select();" value="0" onfocusout="focusOutCalcularFechaFin()" onkeypress="enterPressCalcularFechaFin(event)" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="fecha_inicio_vacaciones">Fecha de inicio <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Fecha de inicio:</b> Fecha en la que el empleado comenzará a disfrutar sus vacaciones."
                                data-toggle="popover" data-html="true"></i>
                            <input name="fecha_inicio_vacaciones" id="fecha_inicio_vacaciones" class="form-control form-control-sm" type="date" required onfocusout="focusOutCalcularFechaFin()" onkeypress="enterPressCalcularFechaFin(event)" required>
                            <div class="invalid-feedback">La fecha de inicio es requerida</div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="fecha_fin_vacaciones">Fecha de fin <span style="color: red">*</span></label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Fecha de fin:</b> Fecha en la que finalizarán las vacaciones, calculada según los días asignados."
                                data-toggle="popover" data-html="true"></i>
                            <input name="fecha_fin_vacaciones" id="fecha_fin_vacaciones" class="form-control form-control-sm" type="date" required disabled>
                            <div class="invalid-feedback">La fecha de fin es requerida</div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="observacion_vacaciones">Observación</label>
                            <input name="observacion_vacaciones" id="observacion_vacaciones" class="form-control form-control-sm" type="text" placeholder="Opcional: agregue detalles relevantes de esta solicitud.">
                        </div>

                        <h6 class="section-title bg-light p-2 mb-3">
                            2. Valores (reportados a la DIAN)
                            <button type="button" class="btn btn-sm badge btn-info" style="vertical-align: middle; height: 30px; margin-bottom: 0px !important; float: inline-end;" onclick="calcularVacaciones()">
                                <i id="reloadCalculoVacacionesLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                                <i id="reloadCalculoVacacionesNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                            </button>
                        </h6>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="salario_dia_vacaciones">Salario diario</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Salario diario:</b> Valor del salario diario que se usará para liquidar las vacaciones según el método seleccionado."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="salario_dia_vacaciones" id="salario_dia_vacaciones" style="font-size: 15px; font-weight: 600;" disabled>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="promedio_otros_vacaciones">Promedio de otros conceptos</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Promedio de otros conceptos:</b> Promedio de ingresos variables (horas extras, recargos) que se incluirán en la liquidación de vacaciones si aplica."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="promedio_otros_vacaciones" id="promedio_otros_vacaciones" style="font-size: 15px; font-weight: 600;" disabled>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="valor_dia_vacaciones">Valor día de vacación</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Valor día de vacación:</b> Resultado de sumar el salario diario y el promedio de otros conceptos para la liquidación de vacaciones."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_dia_vacaciones" id="valor_dia_vacaciones" style="font-size: 15px; font-weight: 600;" disabled>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="total_disfrutado_vacaciones">Total disfrutado</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Total disfrutado:</b> Valor total de las vacaciones que serán disfrutadas por el empleado."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="total_disfrutado_vacaciones" id="total_disfrutado_vacaciones" style="font-size: 15px; font-weight: 600;" disabled>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="total_compensado_vacaciones">Total compensado</label>
                            <i class="fas fa-info icon-info" style="float: inline-end;"
                                title="<b class='titulo-popover'>Total compensado:</b> Valor total de las vacaciones que serán compensadas en dinero al empleado."
                                data-toggle="popover" data-html="true"></i>
                            <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="total_compensado_vacaciones" id="total_compensado_vacaciones" style="font-size: 15px; font-weight: 600;" disabled>
                        </div>
                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveVacaciones"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateVacaciones"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveVacacionesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>

        </div>
    </div>
</div>