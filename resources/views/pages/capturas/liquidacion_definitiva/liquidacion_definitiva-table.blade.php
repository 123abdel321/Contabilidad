<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-6 col-md-4">
        <label for="id_empleado_liquidacion_definitiva_filter">Empleado</label>
        <select name="id_empleado_liquidacion_definitiva_filter" id="id_empleado_liquidacion_definitiva_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
        </select>
    </div>

    <div class="col-12 col-sm-6 col-md-8" style="margin-top: 15px;">
        @can('liquidacion_definitiva create')
            <button type="button" class="btn btn-primary btn-sm" id="causarLiquidacionDefinitiva" style="float: inline-end;">Guardar liquidación definitiva</button>
            <button type="button" class="btn btn-primary btn-sm" id="causarLiquidacionDefinitivaLoading" style="opacity: 1; box-shadow: none; display: none; float: inline-end;" disabled>
                <b style="opacity: 0.3; text-transform: capitalize;">Guardar liquidación definitiva</b>
                <i style="position: absolute; color: white; font-size: 15px; margin-left: -90px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
            </button>
        @endcan
    </div>
    
</div>

<table id="liquidacionDefinitivaTable" class="table table-bordered display responsive" width="100%" style="margin-top: -15px;">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Hide</th>
            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
            <th>Fecha inicio</th>
            <th>Fecha fin</th>
            <th>Días</th>
            <th>Base</th>
            <th>Promedio</th>
            <th>Total</th>
            <th>Observación</th> 
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>