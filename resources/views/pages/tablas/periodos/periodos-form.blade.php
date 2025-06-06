<div class="modal fade" id="periodosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textPeriodosCreate" style="display: none;">Agregar Periodo</h5>
                <h5 class="modal-title" id="textPeriodosUpdate" style="display: none;">Editar Periodo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="periodosForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_periodos_up" id="id_periodos_up" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="nombre_periodo" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre_periodo" id="nombre_periodo" placeholder="Mensual" onkeypress="focusNexInput(event, 'dias_salario')" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="dias_salario" class="form-control-label">Días salario <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Días salario:</b> Días de salario que conforman el periodo. Si es quincena serían 15, si es mensual 30, o si es otro la cantidad de días que se pagan en salario por cada periodo."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input type="text" class="form-control form-control-sm" name="dias_salario" id="dias_salario" placeholder="15" onkeypress="focusNexInput(event, 'horas_dia')" onfocusout="actualizaHorasPeriodo()" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="horas_dia" class="form-control-label">Horas al día <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Horas al día:</b> Horas laboradas al día. Ej. <b class='mensaje-blanco'>8:</b> turno completo, <b class='mensaje-blanco'>4:</b> medio tiempo, <b class='mensaje-blanco'>0:</b> especificado por el usuario."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input type="text" class="form-control form-control-sm" name="horas_dia" id="horas_dia" placeholder="8" onkeypress="focusNexInput(event, 'tipo_dia_pago')" onfocusout="actualizaHorasPeriodo()" required>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="horas_periodo" class="form-control-label">Horas en periodo <span style="color: red">*</span></label>
                            <input type="number" class="form-control form-control-sm" name="horas_periodo" id="horas_periodo" placeholder="0" required disabled>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="tipo_dia_pago">Tipo día de pago <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Nivel:</b> Cómo se calculará la fecha para el pago del periodo. Si es <b class='mensaje-blanco'>Ordinal</b> se calcula según los números ordinales del mes, por ejemplo para quincena el 15vo y 31vo día (ya que 31 se interpreta como el último del mes). Si es <b class='mensaje-blanco'>Calendario</b> se calcula de corrido según el calendario."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <select name="tipo_dia_pago" id="tipo_dia_pago" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" onchange="changeTipoDiaPago()" required>
                                <option value="">Seleccionar</option>
                                <option value="0">Ordinal</option>
                                <option value="1">Calendario</option>
                            </select>
                            
                            <div class="invalid-feedback">
                                El tipo día de pago es requerido
                            </div>
                        </div>

                         <div id="input-periodo_dias_ordinales" class="form-group col-12 col-sm-6 col-md-6" style="display: none;">
                            <label for="periodo_dias_ordinales" class="form-control-label">Días ordinales <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Días ordinales:</b> ías ordinales separados por coma en los que se cuenta como pago. Ejemplo, para quincena “15,31” (se paga el 15vo y el último día del mes)."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input type="text" class="form-control form-control-sm" name="periodo_dias_ordinales" id="periodo_dias_ordinales" onkeypress="enterPeriodo(event)" placeholder="14">
                        </div>

                        <div id="input-periodo_dias_calendario" class="form-group col-12 col-sm-6 col-md-6" style="display: none;">
                            <label for="periodo_dias_calendario" class="form-control-label">Días calendario <span style="color: red">*</span></label>
                            <i
                                class="fas fa-info icon-info"
                                style="float: inline-end;"
                                title="<b class='titulo-popover'>Días calendario:</b> Número de días que se contaran seguidos en el calendario. Ejemplo, si es catorcenal sería 14."
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            <input type="text" class="form-control form-control-sm" name="periodo_dias_calendario" id="periodo_dias_calendario" onkeypress="enterPeriodo(event)" placeholder="14">
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="savePeriodos"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updatePeriodos"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="savePeriodosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>