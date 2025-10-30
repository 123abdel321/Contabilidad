<div class="row" style="padding: 4px;">

    <div class="form-group col-6 col-sm-6 col-md-4" style="margin-bottom: 0px !important;">
        <label for="example-text-input" class="form-control-label">Rango de fechas</label>
        <input name="fecha_manual_primas" id="fecha_manual_primas" class="form-control form-control-sm" require>
    </div>

    <div class="col-6 col-sm-6 col-md-8" style="margin-top: 20px;">
        <!-- GUARDAR -->
        <span id="guardarPrimas" class="btn badge bg-gradient-success btn-bg-success btn-bg-excel" style="min-width: 40px; margin-right: 3px; display: none; float: inline-end; margin-left: 7px; margin-bottom: 0px !important;">
            <i class="fa-solid fa-floppy-disk" style="font-size: 17px;"></i>
            <b style="vertical-align: text-top;">&nbsp;GUARDAR</b>
        </span>
        <span id="guardarPrimasLoading" class="badge bg-gradient-info btn-bg-excel-loading" style="display:none; min-width: 40px; margin-bottom: 16px; float: inline-end; margin-left: 7px; margin-bottom: 0px !important;">
            <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
            <b style="vertical-align: text-top;">&nbsp;GUARDAR</b>
        </span>
        <span id="guardarPrimasDisabled" class="badge bg-gradient-dark" style="min-width: 40px; margin-right: 3px; color: #adadad; float: inline-end; margin-left: 7px; margin-bottom: 0px !important;">
            <i class="fa-solid fa-floppy-disk" style="font-size: 17px; color: #adadad;"></i>
            <b style="vertical-align: text-top;">&nbsp;GUARDAR</b>
            <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
        </span>
        <!-- CARGAR  -->
        <span class="btn badge bg-gradient-info btn-bg-gold" id="cargarPrimas" style="float: inline-end; margin-bottom: 0px !important;">
            <i class="fa-solid fa-folder-open" style="font-size: 17px;"></i>
            <b style="vertical-align: text-top;">&nbsp;CARGAR</b>
        </span>
        <span class="badge bg-gradient-info btn-bg-gold-loading" id="cargarPrimasLoading" style="display:none; min-width: 40px; float: inline-end; margin-bottom: 0px !important;" disabled>
            <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
            <b style="vertical-align: text-top;">&nbsp;CARGAR</b>
        </span>
        
    </div>
    
</div>

<table id="primasTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Número documento</th>
            <th>Empleado</th>
            <th>Fecha inicio</th>
            <th>Fecha fin</th>
            <th>Días</th>
            <th>Base</th>
            <th>Promedio</th>
            <th>Valor</th>
            <th>Días Promedio</th>
            <th>Editado</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Editar</th>
        </tr>
    </thead>
</table>