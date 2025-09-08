<style>
    #participantes-seleccionados .badge {
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }

    #participantes-seleccionados .badge:hover {
        background-color: #dc3545 !important;
    }

    .btn-seleccionar {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-12 col-sm-12">
                <span id="volverReuniones" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-danger" style="min-width: 40px; display: none;">
                    <i class="fa-solid fa-backward" style="font-size: 17px;"></i>&nbsp;
                    <b style="vertical-align: text-top;">VOLVER</b>
                </span>
                <span id="volverReunionesNits" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-danger" style="min-width: 40px; display: none;">
                    <i class="fa-solid fa-backward" style="font-size: 17px;"></i>&nbsp;
                    <b style="vertical-align: text-top;">VOLVER</b>
                </span>
                @can('reuniones create')
                    <span id="createReunion" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px;">
                        <i class="fa-solid fa-calendar-plus" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">NUEVA REUNIÓN</b>
                    </span>
                    <span id="createReunionNit" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px; display: none;">
                        <i class="fa-solid fa-person-circle-plus" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">AGREGAR PARTICIPANTE</b>
                    </span>
                @endcan
                <span id="verReunionDetalle" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-excel" style="min-width: 40px;">
                    <i class="fa-solid fa-eye" style="font-size: 17px;"></i>&nbsp;
                    <b style="vertical-align: text-top;">VER DETALLE</b>
                </span>
                <!-- <button type="button" class="btn btn-info btn-sm" id="detalleReunion">VER DETALLE</button> -->
                <button type="button" class="btn btn-sm badge btn-light btn-bg-blue-dark" style="vertical-align: middle; height: 30px;" id="reloadReuniones">
                    <i id="reloadReunionesIconLoading" class="fa fa-refresh fa-spin" style="font-size: 16px; color: #2d3257; display: none;"></i>
                    <i id="reloadReunionesIconNormal" class="fas fa-sync-alt" style="font-size: 17px;"></i>&nbsp;
                </button>

            </div>
        </div>

        <div id="calendar_reuniones" class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">
                <div class="row" style="padding: 4px;">
                    <div class="form-group col-6 col-sm-4 col-md-3">
                        <label for="estado_filter_reunion">Estado</label>
                        <select class="form-control form-control-sm" id="estado_filter_reunion" name="estado_filter_reunion">
                            <option value="">TODAS</option>
                            <option value="0">PROGRAMADA</option>
                            <option value="1">EN CURSO</option>
                            <option value="2">FINALIZADA</option>
                            <option value="3">CANCELADA</option>
                        </select>
                    </div>
                </div>

                <div id="reuniones-fullcalender" style="flex-grow: 1; position: relative;"></div>
            </div>
        </div>

        <div id="tabla_reuniones" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px; display: none;">
            <div class="card-body">
                @include('pages.configuracion.reuniones.reuniones-table')
            </div>
        </div>

        <div id="tabla_reuniones_nits" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px; display: none;">
            <div class="card-body">
                @include('pages.configuracion.reuniones.reuniones-nits-table')
            </div>
        </div>

    </div>

    @include('pages.configuracion.reuniones.reuniones-form')
    @include('pages.configuracion.reuniones.reuniones-nits-form')

</div>

<script>
    var editarReuniones = @json(auth()->user()->can('reuniones update'));
    var eliminarReuniones = @json(auth()->user()->can('reuniones delete'));
</script>