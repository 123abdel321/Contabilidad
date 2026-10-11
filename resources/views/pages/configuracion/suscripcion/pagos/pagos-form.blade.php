<div class="modal fade" id="pagosSuscripcionFormModal" tabindex="-1" role="dialog" aria-labelledby="pagosFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pagosFormLabel">Pagar suscripción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">

                {{-- Total a pagar --}}
                <div class="card bg-gradient-primary mb-3" style="border-radius: 12px;">
                    <div class="card-body p-3 text-center">
                        <p class="text-white text-sm mb-1 text-uppercase font-weight-bold">Total a pagar</p>
                        <h2 class="text-white font-weight-bolder mb-1" id="pagosFormMonto">$ 0</h2>
                        <small class="text-white" id="pagosFormAhorro"></small>
                    </div>
                </div>

                {{-- Selector de meses --}}
                <div class="mb-3">
                    <label class="form-label font-weight-bold text-sm text-uppercase">Duración del pago</label>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnRestarMes" style="width: 42px; height: 42px;">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="pagosFormMeses" class="form-control form-control-sm text-center font-weight-bold"
                               value="1" min="1" max="24"
                               style="width: 80px; height: 42px; font-size: 16px;">
                        <span class="font-weight-bold">mes(es)</span>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnSumarMes" style="width: 42px; height: 42px;">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                {{-- Descuentos rápidos --}}
                <div class="mb-3" id="pagosFormDescuentosRapidos">
                    {{-- Se llena con JS --}}
                </div>

                {{-- Desglose --}}
                <div class="card border mb-3" style="border-radius: 10px;">
                    <div class="card-body p-3">
                        <h6 class="text-uppercase font-weight-bold text-sm mb-3">Desglose</h6>
                        <div id="pagosFormDesglose">
                            <div class="text-center text-muted small">Calculando...</div>
                        </div>
                    </div>
                </div>

                {{-- Info Wompi --}}
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <i class="fa-solid fa-lock"></i>
                    <span>Pago seguro procesado por Wompi</span>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="pagosFormPagarBtn" type="button" class="btn bg-gradient-success btn-sm">
                    <i class="fa-solid fa-credit-card"></i>&nbsp;
                    Pagar con tarjeta
                </button>
            </div>
        </div>
    </div>
</div>