<div class="row" style="padding: 4px;">

    <div class="row" style="padding: 4px;">
        <div class="form-group col-12 col-sm-6 col-md-4">
            <label for="meses_causar_nomina_filter">Meses</label>
            <select name="meses_causar_nomina_filter" id="meses_causar_nomina_filter" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
            </select>
        </div>

        <div class="col-12 col-sm-6 col-md-8" style="margin-top: 15px;">
            @can('causar create')

                <button type="button" class="btn btn-primary btn-sm btn-bg-gold" id="recalcularPeriodos" style="float: inline-end; margin-left: 10px;">
                    <i class="fa-solid fa-calculator" style="font-size: 15px;"></i>&nbsp;
                    Re-calcular periodos
                </button>
                <button type="button" class="btn btn-primary btn-sm btn-bg-gold-loading" id="recalcularPeriodosLoading" style="opacity: 1; box-shadow: none; display: none; float: inline-end;" disabled>
                    <b style="opacity: 0.3; text-transform: capitalize;">Re-calcular periodos</b>
                    <i style="position: absolute; color: white; font-size: 15px; margin-left: -65px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
                </button>

                <!-- Botón de Causar Nómina -->
                <button type="button" class="btn btn-success btn-sm btn-bg-excel" id="causarNominaBtn" style="float: inline-end;">
                    <i class="fa-solid fa-file-invoice" style="font-size: 15px;"></i>&nbsp;
                    Causar Nómina
                </button>
                <button type="button" class="btn btn-success btn-sm btn-bg-excel-loading" id="causarNominaLoading" style="opacity: 1; box-shadow: none; display: none; float: inline-end; margin-left: 10px;" disabled>
                    <b style="opacity: 0.3; text-transform: capitalize;">Causando Nómina</b>
                    <i style="position: absolute; color: white; font-size: 15px; margin-left: -60px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
                </button>
            @endcan
        </div>
    </div>
    
</div>

<table id="causarNominaTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Hide</th>
            <th style="border-radius: 15px 0px 0px 0px !important;">N° documento</th>
            <th>Empleado</th>
            <th>Estado</th>
            <th>Inicio periodo</th>
            <th>Fin periodo</th>
            <th>Devengados</th>
            <th>Deducciones</th>
            <th>Neto</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>