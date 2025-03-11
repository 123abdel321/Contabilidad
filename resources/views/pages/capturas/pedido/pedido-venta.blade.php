<div class="modal fade" id="pedidosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="pedidosVentasForm" style="margin-top: 10px;">
                    <div class="row">

                        <div class="col-12 col-sm-12 col-md-6 row">
                            <div class="form-group col-12 col-sm-12 col-md-12" style="margin-bottom: 1px !important;">
                                <label for="id_resolucion_pedido">Resolucion<span style="color: red">*</span></label>
                                <select name="id_resolucion_pedido" id="id_resolucion_pedido" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                                
                                <div class="invalid-feedback">
                                    La resolución es requerida
                                </div>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-12">
                                <label for="fecha_manual_pedido" class="form-control-label">Fecha <span style="color: red">*</span></label>
                                <input name="fecha_manual_pedido" id="fecha_manual_pedido" class="form-control form-control-sm" type="date" required disabled>
                                <div class="invalid-feedback">
                                    La fecha es requerida
                                </div>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-12">
                                <label for="consecutivo_pedido" class="form-control-label">No. factura <span style="color: red">*</span></label>
                                <input name="consecutivo_pedido" id="consecutivo_pedido" class="form-control form-control-sm" type="text" required disabled>
                            </div>

                            <div class="form-group col-12 col-sm-12 col-md-12">
                                <label for="observacion_pedido" class="form-control-label">Observación </label>
                                <input type="text" class="form-control form-control-sm" name="observacion_pedido" id="observacion_pedido">
                            </div>

                            <div id="input-anticipos-pedido" class="form-group col-12 col-sm-12 col-md-12" style="display: none;">
                                <label for="id_saldo_anticipo_pedido" class="form-control-label">Anticipos <span style="color: red">*</span></label>
                                <input name="id_saldo_anticipo_pedido" id="id_saldo_anticipo_pedido" class="form-control form-control-sm" type="text" disabled style="text-align: right;">
                                <div class="invalid-feedback" id="error-anticipo-cliente-pedido">
                                    Valor superado
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-6">
                            <div class="form-group">
                                <div style="overflow: auto;">
                                    <table id="pedidoFormaPago" class="table table-bordered display responsive table-captura-pedidos" width="100%">
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
                                        <h6 id="total_pagado_pedidos" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0,00</h6>
                                    </div>
                                </div>
    
                                <div class="row">
                                    <div class="col-6">
                                        <h6 id="total_faltante_pedidos_text" style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; text-wrap: nowrap;">FALTANTE: </h6>
                                    </div>
                                    <div class="col-6" style="text-align: end; text-wrap: nowrap;">
                                        <h6 id="total_faltante_pedidos" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px;">0,00</h6>
                                    </div>
                                </div>
    
                                <div id="cambio-totals-pedidos" class="row" style="display: none;">
                                    <div class="col-6">
                                        <h6 style="margin-bottom: 0px; font-weight: bold; margin-left: 4px; color: blue;">CAMBIO: </h6>
                                    </div>
                                    <div class="col-6" style="text-align: end;">
                                        <h6 id="total_cambio_pedidos" style="margin-bottom: 0px; font-weight: bold; margin-right: 25px; color: blue;">0,00</h6>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="savePedidos"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="savePedidosLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>