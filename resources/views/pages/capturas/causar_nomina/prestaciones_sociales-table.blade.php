<div class="row" style="padding: 4px;">

    <div class="form-group col-12 col-sm-6 col-md-4">
        <label for="meses_prestaciones_sociales_filter">Meses</label>
        <select name="meses_prestaciones_sociales_filter" id="meses_prestaciones_sociales_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
        </select>
    </div>

    <div class="col-12 col-sm-6 col-md-8" style="margin-top: 15px;">
        @can('causar create')
            <button type="button" class="btn btn-primary btn-sm" id="causarPrestaciones" style="float: inline-end;">Causar provisión</button>
            <button type="button" class="btn btn-primary btn-sm" id="causarPrestacionesLoading" style="opacity: 1; box-shadow: none; display: none; float: inline-end;" disabled>
                <b style="opacity: 0.3; text-transform: capitalize;">Causar provisión</b>
                <i style="position: absolute; color: white; font-size: 15px; margin-left: -55px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
            </button>
        @endcan
    </div>
    
</div>

<table id="prestacionesSocialesTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Hide</th>
            <th style="border-radius: 15px 0px 0px 0px !important;">Concepto</th>
            <th>Base</th>
            <th>Porcentaje</th>
            <th>Provisión</th>
            <th>Fondo</th>
            <th>Cuenta débito</th>
            <th>Cuenta crédito</th>
            <th>Editado</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>