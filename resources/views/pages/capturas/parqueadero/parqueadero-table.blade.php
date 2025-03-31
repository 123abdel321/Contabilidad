<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-4 col-md-4" >
        <label for="placa_parqueadero_filter" class="form-control-label">Placa - Buscar / Agregar</label>
        <input type="text" class="form-control form-control-sm" name="placa_parqueadero_filter" id="placa_parqueadero_filter" onkeypress="buscarPlacaParqueadero(event)">
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="id_nit_parqueadero_filter">Cédula / nit</label>
        <select name="id_nit_parqueadero_filter" id="id_nit_parqueadero_filter" class="form-control form-control-sm">
        </select>
    </div>
    
    <div class="form-group col-12 col-sm-4 col-md-4" style="margin-bottom: 1px !important;">
        <label for="tipo_vehiculo_parqueadero_filter">Tipo vehiculo<span style="color: red">*</span></label>
        <select name="tipo_vehiculo_parqueadero_filter" id="tipo_vehiculo_parqueadero_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
            <option value="0">TODOS</option>
            <option value="1">CARRO</option>
            <option value="2">MOTO</option>
            <option value="3">OTROS</option>
        </select>
    </div>

</div>

<table id="parqueaderoTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Tipo</th>
            <th>Placa</th>
            <th>Cliente</th>
            <th>Concepto</th>
            <th>Fecha entrada</th>
            <th>Fecha salida</th>
            <th>Consecutivo</th>
            <th>Ultima actualización</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>