<div class="modal fade" id="periodoPagoDetallePagarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textPeriodoPagoDetalle">Detalle periodo pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>

            <div class="modal-body">

                <table id="periodoPagoDetallePagarTable" class="table table-bordered display responsive" width="100%">
                    <thead style="background-color: #7ea1ff2b;">
                        <tr>
                            <th style="border-radius: 15px 0px 0px 0px !important;">Código</th>
                            <th>Concepto</th>
                            <th>Devengado</th>
                            <th>Deducción</th>
                            <th>Horas</th>
                            <th>Días</th>
                            <th>Observación</th>
                            <th>%</th>
                            <th>Base %</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Hora inicio</th>
                            <th style="border-radius: 0px 15px 0px 0px !important;">Hora fin</th>
                        </tr>
                    </thead>
                </table>

            </div>

            <div class="modal-footer" style="padding: 0px;">
                <div class="row" style="width: 100%;">
                    <div class="col-12 col-sm-4 col-md-4 value-div" style="text-align: -webkit-center;">
                        <b style="color: #4d4d4d; font-size: 15px;">Devengado</b><br>
                        <b style="color: #08cc08; font-size: 18px;" id="devengado_detalle_total">0</b>
                    </div>
                    <div class="col-12 col-sm-4 col-md-4 value-div" style="text-align: -webkit-center;">
                        <b style="color: #4d4d4d; font-size: 15px;">Deducción</b><br>
                        <b style="color: #ff0000; font-size: 18px;" id="deduccion_detalle_total">0</b>
                    </div>
                    <div class="col-12 col-sm-4 col-md-4 value-div" style="text-align: -webkit-center;">
                        <b style="color: #4d4d4d; font-size: 15px;">Neto</b><br>
                        <b style="color: #08cc08; font-size: 18px;" id="neto_detalle_total">0</b>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>