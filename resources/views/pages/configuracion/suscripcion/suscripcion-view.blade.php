<style>

    .dtrg-group {
        font-weight: bold;
        background-color: #f0f0f0;
        padding: 10px;
        text-transform: uppercase;
    }

    /* Cards uniformes */
    .suscripcion-card {
        min-height: 110px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .suscripcion-card .card-body {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        padding: 16px !important;
        position: relative;
    }

    .suscripcion-card .card-label {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: #8392ab;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .suscripcion-card .card-value {
        font-size: 20px;
        font-weight: 800;
        margin-bottom: 0;
        line-height: 1.2;
        color: #344767;
    }

    .suscripcion-card .card-sub {
        font-size: 11px;
        color: #8392ab;
        margin-top: 4px;
        min-height: 14px;
        display: block;
    }

    .suscripcion-card .card-icon {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    }

    .suscripcion-card .card-icon i {
        color: white;
        font-size: 15px;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">

        {{-- ============================================================ --}}
        {{-- CARDS PESTAÑA COMPONENTES --}}
        {{-- ============================================================ --}}
        <div class="row px-3 pt-3" id="cards-componentes">

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Total mensual</p>
                            <h5 class="card-value" id="consumoTotalMensual">$ 0</h5>
                            <small class="card-sub"></small>
                        </div>
                        <div class="card-icon bg-gradient-primary">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Total del ciclo</p>
                            <h5 class="card-value text-info" id="consumoTotalCiclo">$ 0</h5>
                            <small class="card-sub"></small>
                        </div>
                        <div class="card-icon bg-gradient-info">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Consumido al día</p>
                            <h5 class="card-value text-success" id="consumoTotalAlDia">$ 0</h5>
                            <small class="card-sub"></small>
                        </div>
                        <div class="card-icon bg-gradient-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Pendiente por consumir</p>
                            <h5 class="card-value text-warning" id="consumoTotalPendiente">$ 0</h5>
                            <small class="card-sub"></small>
                        </div>
                        <div class="card-icon bg-gradient-warning">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- CARDS PESTAÑA PAGOS --}}
        {{-- ============================================================ --}}
        <div class="row px-3 pt-3" id="cards-pagos" style="display: none;">

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Estado suscripción</p>
                            <h5 class="card-value" id="pagosEstadoSuscripcion">
                                <span class="badge rounded-pill bg-secondary">Cargando...</span>
                            </h5>
                            <small class="card-sub"></small>
                        </div>
                        <div class="card-icon bg-gradient-primary">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Próximo cobro</p>
                            <h5 class="card-value text-info" id="pagosProximoCobro">$ 0</h5>
                            <small class="card-sub" id="pagosProximoCobroFecha">&nbsp;</small>
                        </div>
                        <div class="card-icon bg-gradient-info">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Último pago</p>
                            <h5 class="card-value text-success" id="pagosUltimoPago">$ 0</h5>
                            <small class="card-sub" id="pagosUltimoPagoFecha">&nbsp;</small>
                        </div>
                        <div class="card-icon bg-gradient-success">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card suscripcion-card">
                    <div class="card-body">
                        <div>
                            <p class="card-label">Total pagado (año)</p>
                            <h5 class="card-value text-warning" id="pagosTotalAnio">$ 0</h5>
                            <small class="card-sub"></small>
                        </div>
                        <div class="card-icon bg-gradient-warning">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ============================================================ --}}
        {{-- TABS --}}
        {{-- ============================================================ --}}
        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent; box-shadow: none;">
            <div class="card-body row">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link active" id="componentes-suscripcion-tab" data-bs-toggle="tab" data-bs-target="#componentes_suscripcion" type="button" role="tab" aria-controls="componentes_suscripcion" aria-selected="true">Componentes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link" id="pagos-suscripcion-tab" data-bs-toggle="tab" data-bs-target="#pagos_suscripcion" type="button" role="tab" aria-controls="pagos_suscripcion" aria-selected="false">Pagos</button>
                    </li>
                </ul>

                <div class="tab-content" style="background-color: white; border-top-right-radius: 10px;">
                    <div class="tab-pane fade show active" id="componentes_suscripcion" role="tabpanel" aria-labelledby="componentes-suscripcion-tab">
                        @include('pages.configuracion.suscripcion.componentes.componentes-table')
                    </div>
                    <div class="tab-pane fade show" id="pagos_suscripcion" role="tabpanel" aria-labelledby="pagos-suscripcion-tab">
                        @include('pages.configuracion.suscripcion.pagos.pagos-table')
                    </div>
                </div>

            </div>
        </div>

    </div>

    @include('pages.configuracion.suscripcion.componentes.componentes-form')
    @include('pages.configuracion.suscripcion.pagos.pagos-form')
    @include('pages.configuracion.suscripcion.pagos.pagos-detalle')

</div>

<script>
    var esDios = JSON.parse('<?php echo $esDios; ?>');
</script>