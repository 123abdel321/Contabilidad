
<div class="row" style="padding: 4px;">

    <input name="id_extracto_cargado" id="id_extracto_cargado" class="form-control form-control-sm" type="text" style="display: none;">

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="fecha_manual_extracto" class="form-control-label">Fecha</label>
        <input name="fecha_manual_extracto" id="fecha_manual_extracto" class="form-control form-control-sm" require>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="id_nit_extracto" style=" width: 100%;">Nit</label>
        <select class="form-control form-control-sm" name="id_nit_extracto" id="id_nit_extracto">
            <option value="">Ninguno</option>
        </select>
    </div>

    <div class="form-group col-12 col-sm-4 col-md-4">
        <label for="factura_documentos_extracto" class="form-control-label">No. factura</label>
        <input name="factura_documentos_extracto" id="factura_documentos_extracto" class="form-control form-control-sm" type="text">
    </div>
    
    <!-- <div class="form-group col-6 col-sm-2 col-md-2 row" style="margin-bottom: 0.1rem !important;">
        <label for="example-text-input" class="form-control-label">Documentos</label>
        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
            <input class="form-check-input" type="radio" name="tipo_errores_extracto" id="tipo_errores_extracto0" style="font-size: 11px;" checked>
            <label class="form-check-label" for="tipo_errores_extracto0" style="font-size: 11px;">
                Todos
            </label>
        </div>
        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
            <input class="form-check-input" type="radio" name="tipo_errores_extracto" id="tipo_errores_extracto1" style="font-size: 11px;">
            <label class="form-check-label" for="tipo_errores_extracto1" style="font-size: 11px;">
                Errores
            </label>
        </div>
    </div> -->

</div>

<table id="extractoInformeTable" class="table nowrap table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Cuenta</th>
            <th>Nombre</th>
            <!-- <th>Nit</th> -->
            <!-- <th>Ubicación</th> -->
            <!-- <th>Centro costos</th> -->
            <th>Factura</th>
            <th>Saldo anterior</th>
            <th>Debito</th>
            <th>Credito</th>
            <th>Saldo final</th>
            <th>Comprobante</th>
            <th>Consec.</th>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Creación registro</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Ultima actualización</th>
        </tr>
    </thead>
</table>