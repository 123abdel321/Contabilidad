<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="id_nit_filter_reserva_table">Cédula / Nit</label>
        <select name="id_nit_filter_reserva_table" id="id_nit_filter_reserva_table" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
        </select>
    </div>

    <div class="form-group  col-12 col-sm-6 col-md-3">
        <label for="id_ubicacion_filter_reserva_table" >ubicacion</label>
        <select name="id_ubicacion_filter_reserva_table" id="id_ubicacion_filter_reserva_table" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
        </select>
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="fecha_desde_reserva_filter" class="form-control-label">Fecha desde</label>
        <input name="fecha_desde_reserva_filter" id="fecha_desde_reserva_filter" class="form-control form-control-sm" type="date">
    </div>

    <div class="form-group col-12 col-sm-6 col-md-3">
        <label for="fecha_hasta_reserva_filter" class="form-control-label">Fecha hasta</label>
        <input name="fecha_hasta_reserva_filter" id="fecha_hasta_reserva_filter" class="form-control form-control-sm" type="date">
    </div>

</div>

<table id="reservaTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Codigo</th>
            <th>Ubicacion</th>
            <th>Cédula / Nit</th>
            <th>Observación</th>
            <th>Fecha/hora inicio</th>
            <th>Fecha/hora fin</th>
            <th>Fecha/hora creación</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>