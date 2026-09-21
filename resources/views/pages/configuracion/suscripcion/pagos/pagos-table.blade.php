
<div class="row" style="padding: 4px;">
    <div class="row" style="padding: 4px;">

        <div class="col-12" style="margin-top: 15px;">
            <button type="button" class="btn btn-primary btn-sm btn-bg-gold" id="pagosSuscripcionCreate" style="float: inline-end; margin-left: 10px;">
                <i class="fa-solid fa-calculator" style="font-size: 15px;"></i>&nbsp;
                Agregar pagos
            </button>
            <button type="button" class="btn btn-primary btn-sm btn-bg-gold-loading" id="pagosSuscripcionCreateLoading" style="opacity: 1; box-shadow: none; display: none; float: inline-end;" disabled>
                <b style="opacity: 0.3; text-transform: capitalize;">Agregar pagos</b>
                <i style="position: absolute; color: white; font-size: 15px; margin-left: -65px; margin-top: 1px;" class="fas fa-spinner fa-spin"></i>
            </button>
        </div>

    </div>
</div>

<table id="componentesSuscripcionTable" class="table table-bordered display responsive" width="100%">
    <thead style="background-color: #7ea1ff2b;">
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Componente</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Inicio suscripción</th>
            <th>Proximo pago</th>
            <th>Descuento</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
        </tr>
    </thead>
</table>