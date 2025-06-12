<div class="modal fade" id="novedadesGeneralesFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textCreateNovedadesGenerales">Agregar novedad generales</h5>
                <h5 class="modal-title" id="textEditarNovedadesGenerales">Editar novedad generales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="NovedadesGeneralesForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id" id="id_novedades_generales_up" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_empleado_novedades_generales">Empleado</label>
                        <select name="id_empleado" id="id_empleado_novedades_generales" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_concepto_novedades_generales">Concepto</label>
                        <select name="id_concepto" id="id_concepto_novedades_generales" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div id="div-unidades_novedades_generales" class="form-group col-12 col-sm-6 col-md-6" >
                        <label id="text-unidades_novedades_generales" for="unidades_novedades_generales" class="form-control-label"> </label>
                        <input type="number" class="form-control form-control-sm" name="unidades" id="unidades_novedades_generales" value="0">
                    </div>

                    <div id="div-valor_novedades_generales" class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="valor_novedades_generales" class="form-control-label">Valor <span style="color: red">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="valor" id="valor_novedades_generales" value="0">
                    </div>

                    <div id="div-porcentaje_novedades_generales" class="form-group col-12 col-sm-6 col-md-6" >
                        <label for="porcentaje_novedades_generales" class="form-control-label">Porcentaje <span style="color: red">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="porcentaje" id="porcentaje_novedades_generales" value="0">
                    </div>

                    <div id="div-fecha_desde_novedades_generales" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="fecha_desde_novedades_generales" class="form-control-label">Fecha desde<span style="color: red">*</span></label>
                        <input name="fecha_desde" id="fecha_desde_novedades_generales" class="form-control form-control-sm" type="date">
                    </div>

                    <div id="div-fecha_hasta_novedades_generales" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="fecha_hasta_novedades_generales" class="form-control-label">Fecha hasta<span style="color: red">*</span></label>
                        <input name="fecha_hasta" id="fecha_hasta_novedades_generales" class="form-control form-control-sm" type="date">
                    </div>

                    <div id="div-hora_desde_novedades_generales" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="hora_desde_novedades_generales" class="form-control-label">Hora desde<span style="color: red">*</span></label>
                        <input name="hora_desde" id="hora_desde_novedades_generales" class="form-control form-control-sm" type="time">
                    </div>

                    <div id="div-hora_hasta_novedades_generales" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="hora_hasta_novedades_generales" class="form-control-label">Hora hasta<span style="color: red">*</span></label>
                        <input name="hora_hasta" id="hora_hasta_novedades_generales" class="form-control form-control-sm" type="time">
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveNovedadesGenerales"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateNovedadesGenerales"type="button" class="btn bg-gradient-success btn-sm">Actualizar</button>
                <button id="saveNovedadesGeneralesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>