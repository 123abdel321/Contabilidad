<div class="row" style="padding: 4px;">

    <input type="text" class="form-control" name="id_presupuesto_up" id="id_presupuesto_up" style="display: none;">

    <div class="form-group col-12 col-sm-4 col-md-2" style="align-self: center;">
        <label for="exampleFormControlSelect1">Periodo</label>
        <select class="form-control form-control-sm" id="periodo_presupuesto" name="periodo_presupuesto">
            <option value="2024">2024</option>
            <option value="2025">2025</option>
            <option value="2026">2026</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-2" style="align-self: center;">
        <label for="exampleFormControlSelect1">Tipo</label>
        <select class="form-control form-control-sm" id="tipo_presupuesto" name="tipo_presupuesto">
            <option value="1">Ingresos</option>
            <option value="2">Gastos</option>
        </select>
    </div>

    <div id="div-valor_presupuesto" class="form-group col-12 col-sm-4 col-md-2" style="display: none;">
        <label for="example-text-input" class="form-control-label">Presupuesto</label>
        <input type="text" class="form-control form-control-sm text-align-right" name="valor_presupuesto" id="valor_presupuesto" data-type="currency" onfocus="this.select();" onfocusout="actualizarValorPresupuesto()" onkeydown="enterActualizarValorPresupuesto(event)">
    </div>

    <div id="div-valor_diferencia" class="form-group col-12 col-sm-4 col-md-2" style="display: none;">
        <label for="example-text-input" class="form-control-label">Diferencia</label>
        <input type="text" class="form-control form-control-sm text-align-right" name="valor_diferencia" id="valor_diferencia" disabled>
    </div>

    <div id="div-buscar_presupuesto" class="form-group col-12 col-sm-4 col-md-3" style="display: none;">
        <label for="example-text-input" class="form-control-label">Buscar</label>
        <input type="text" class="form-control form-control-sm" id="searchInputInmuebles" onfocus="this.select();" onkeydown="searchPresupuesto(event)">
    </div>

    <div class="form-group col-12 col-sm-4 col-md-2" style="margin-bottom: unset !important;">
        <button id="generarPresupuestoLoading" type="button" class="btn btn-sm badge btn-primary" style="vertical-align: middle; height: 30px; margin-top: 20px;" disabled>
            <i class="fa fa-refresh fa-spin" style="font-size: 16px; color: white;" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="generarPresupuesto" style="display: none; margin-bottom: unset !important; margin-top: 15px;">Generar</button>
    </div>
</div>

<table id="presupuestoTable" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Cuenta</th>
            <th>Nombre</th>
            <th>Ppto general</th>
            <th>Diferencia</th>
            <th>Enero</th>
            <th>Febrero</th>
            <th>Marzo</th>
            <th>Abril</th>
            <th>Mayo</th>
            <th>Junio</th>
            <th>Julio</th>
            <th>Agosto</th>
            <th>Septiembre</th>
            <th>Octubre</th>
            <th>Noviembre</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Diciembre</th>
            <!-- <th style="border-radius: 0px 15px 0px 0px !important;">Ultima actualización</th> -->
        </tr>
    </thead>
</table>