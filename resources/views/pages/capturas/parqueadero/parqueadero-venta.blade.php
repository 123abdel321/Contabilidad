<div class="modal fade" id="parqueaderoVentaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="parqueaderoTexto" class="modal-title" style="font-size: 35px;">Agregar pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="parqueaderoVentasForm" style="margin-top: 10px;">
                    <div class="row">

                        <div id="total_tiempo_parqueadero" style="text-align: center; margin-bottom: 12px; font-size: 20px; font-weight: bold; color: #5a5a5a;"></div>

                        <div class="col-12 col-sm-12 col-md-6 row">
                            <div class="form-group col-12 col-sm-12 col-md-12" style="margin-bottom: 1px !important;">
                                <label for="id_resolucion_parqueadero">Resolucion<span style="color: red">*</span></label>
                                <select name="id_resolucion_parqueadero" id="id_resolucion_parqueadero" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                                
                                <div class="invalid-feedback">
                                    La resolución es requerida
                                </div>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-12">
                                <label for="fecha_manual_parqueadero" class="form-control-label">Fecha <span style="color: red">*</span></label>
                                <input name="fecha_manual_parqueadero" id="fecha_manual_parqueadero" class="form-control form-control-sm" type="date" required disabled>
                                <div class="invalid-feedback">
                                    La fecha es requerida
                                </div>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-12">
                                <label for="consecutivo_parqueadero" class="form-control-label">No. factura <span style="color: red">*</span></label>
                                <input name="consecutivo_parqueadero" id="consecutivo_parqueadero" class="form-control form-control-sm" type="text" required disabled>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-12">
                                <label for="observacion_parqueadero" class="form-control-label">Observación </label>
                                <input type="text" class="form-control form-control-sm" name="observacion_parqueadero" id="observacion_parqueadero">
                            </div>

                            <div id="input-anticipos-parqueadero" class="form-group col-12 col-sm-12 col-md-12" style="display: none;">
                                <label for="id_saldo_anticipo_parqueadero" class="form-control-label">Anticipos <span style="color: red">*</span></label>
                                <input name="id_saldo_anticipo_parqueadero" id="id_saldo_anticipo_parqueadero" class="form-control form-control-sm" type="text" disabled style="text-align: right;">
                                <div class="invalid-feedback" id="error-anticipo-cliente-parqueadero">
                                    Valor superado
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <div style="overflow: auto;">
                                    <table id="parqueaderoFormaPago" class="table table-bordered display responsive table-captura-parqueadero" width="100%">
                                        <thead>
                                            <tr style="border: 0px !important;">
                                                <th style="border-radius: 15px 0px 0px 0px !important;">Pagos</th>
                                                <th style="border-radius: 0px 15px 0px 0px !important;">Total</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
    
                                <div class="row">
                                    <div class="col-6">
                                        <h6 style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; text-wrap: nowrap;">PAGADO: </h6>
                                    </div>
                                    <div class="col-6" style="text-align: end; text-wrap: nowrap;">
                                        <h6 id="total_pagado_parqueadero" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0,00</h6>
                                    </div>
                                </div>
    
                                <div class="row">
                                    <div class="col-6">
                                        <h6 id="total_faltante_parqueadero_text" style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; text-wrap: nowrap;">FALTANTE: </h6>
                                    </div>
                                    <div class="col-6" style="text-align: end; text-wrap: nowrap;">
                                        <h6 id="total_faltante_parqueadero" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0,00</h6>
                                    </div>
                                </div>
    
                                <div id="cambio-totals-parqueadero" class="row" style="display: none;">
                                    <div class="col-6">
                                        <h6 style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; color: blue;">CAMBIO: </h6>
                                    </div>
                                    <div class="col-6" style="text-align: end;">
                                        <h6 id="total_cambio_parqueadero" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; color: blue;">0,00</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveParqueaderoVenta"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveParqueaderoVentaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>