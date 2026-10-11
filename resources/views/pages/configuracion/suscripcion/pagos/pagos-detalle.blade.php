<div class="modal fade" id="pagoDetalleModal" tabindex="-1" role="dialog" aria-labelledby="pagoDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pagoDetalleLabel">Detalle del pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold">Fecha</small>
                        <p class="mb-0 font-weight-bold" id="detallePagoFecha">—</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold">Usuario</small>
                        <p class="mb-0 font-weight-bold" id="detallePagoUsuario">—</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold">Método de pago</small>
                        <p class="mb-0 font-weight-bold" id="detallePagoMetodo">—</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold">Referencia</small>
                        <p class="mb-0 font-weight-bold" id="detallePagoReferencia">—</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <small class="text-muted text-uppercase font-weight-bold">Estado</small>
                        <p class="mb-0" id="detallePagoEstado">—</p>
                    </div>
                </div>

                <hr>

                <h6 class="text-uppercase font-weight-bold mb-3">Componentes pagados</h6>

                <table class="table table-sm table-bordered">
                    <thead style="background-color: #7ea1ff2b;">
                        <tr>
                            <th>Componente</th>
                            <th class="text-end">Cantidad</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody id="detallePagoComponentes">
                        <tr>
                            <td colspan="4" class="text-center text-muted">Sin información</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f0f0f0;">
                            <td colspan="3" class="text-end font-weight-bold">TOTAL</td>
                            <td class="text-end font-weight-bold" id="detallePagoTotal">$ 0</td>
                        </tr>
                    </tfoot>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>