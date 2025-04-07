<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-12 col-sm-12">
                    <button type="button" class="btn btn-dark btn-sm" id="volverReserva" style="display: none;"><i class="fas fa-step-backward back-icon-button" aria-hidden="true"></i>&nbsp;Volver</button>
                    @can('reserva create')
                        <button type="button" class="btn btn-primary btn-sm" id="createReserva">Agregar Reservas</button>
                    @endcan
                    <button type="button" class="btn btn-info btn-sm" id="detalleReserva">Ver detalle</button>
                    <button type="button" class="btn btn-sm badge btn-light" style="vertical-align: middle; height: 30px;" id="reloadReservas">
                        <i id="reloadReservasIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                        <i id="reloadReservasIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                    </button>
                </div>
            </div>
        </div>

        <div id="calendar_reserva" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                <div class="row" style="padding: 4px;">

                    <div class="form-group  col-12 col-sm-6 col-md-6">
                        <label>Cedula / Nit</label>
                        <select name="id_nit_filter_reserva" id="id_nit_filter_reserva" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                        </select>
                    </div>

                    <div class="form-group  col-12 col-sm-6 col-md-6">
                        <label>Ubicacion</label>
                        <select name="id_ubicacion_filter_reserva" id="id_ubicacion_filter_reserva" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                        </select>
                    </div>

                </div>

                <div id="reserva-fullcalender" style="flex-grow: 1; position: relative;"></div>

            </div>
        </div>

        <div id="tabla_reserva" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px; display: none;">
            <div class="card-body">

                @include('pages.capturas.reserva.reserva-table')

            </div>
        </div>
    </div>

    @include('pages.capturas.reserva.reserva-form')
    @include('pages.capturas.reserva.reserva-evento')
    
</div>

<script>
    var editarReservas = '<?php echo auth()->user()->can('reserva update'); ?>';
    var eliminarReservas = '<?php echo auth()->user()->can('reserva delete'); ?>';
</script>