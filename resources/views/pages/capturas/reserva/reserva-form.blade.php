<div class="modal fade" id="reservaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" style="contain: content;" role="document">
        <div  class="modal-content" style="margin-top: 10px;" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="textReservaCreate" style="display: block;">Reservas</h5>
                <!-- <h5 class="modal-title" id="textReservaUpdate" style="display: none;">Editar Reserva</h5> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            
            <div class="modal-body">
                <form id="form-reserva" class="row">

                    <input type="text" class="form-control" name="id_reserva_up" id="id_reserva_up" style="display: none;">

                    <div class="form-group col-6 col-sm-6 col-md-6">
                        <label for="id_ubicacion_reserva">Ubicacion</label>
                        <select class="form-control form-control-sm" id="id_ubicacion_reserva" name="id_ubicacion_reserva">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="id_nit_reserva">Cédula / Nit <span style="color: red">*</span></label>
                        <select name="id_nit_reserva" id="id_nit_reserva" class="form-control form-control-sm" required>
                        </select>
                    </div>

                    <div id="input_hora_inicio_reserva" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="fecha_inicio_reserva" class="form-control-label">Fecha inicio <span style="color: red">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="fecha_inicio_reserva" id="fecha_inicio_reserva">
                    </div>

                    <div id="input_hora_fin_reserva" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="fecha_fin_reserva" class="form-control-label">Fecha fin <span style="color: red">*</span></label>
                        <input type="date" class="form-control form-control-sm" name="fecha_fin_reserva" id="fecha_fin_reserva">
                    </div>

                    <div id="input_hora_inicio_reserva" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="hora_inicio_reserva" class="form-control-label">Hora inicio <span style="color: red">*</span></label>
                        <input type="time" class="form-control form-control-sm" name="hora_inicio_reserva" id="hora_inicio_reserva">
                    </div>

                    <div id="input_hora_fin_reserva" class="form-group col-12 col-sm-6 col-md-6">
                        <label for="hora_fin_reserva" class="form-control-label">Hora fin <span style="color: red">*</span></label>
                        <input type="time" class="form-control form-control-sm" name="hora_fin_reserva" id="hora_fin_reserva">
                    </div>

                    <div id="" style="display: block;" class="form-group col-12 col-sm-12 col-md-12">
                        <label for="observacion_reserva" class="form-control-label">Observación<span style="color: red">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="observacion_reserva" id="observacion_reserva" onfocus="this.select();" required>
                    </div>

                </form>
            </div>
            
            <div class="modal-footer">
                <span href="javascript:void(0)" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </span>
                <button id="saveReserva" href="javascript:void(0)" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveReservaLoading" class="btn btn-success btn-sm ms-auto" style="display:none;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>