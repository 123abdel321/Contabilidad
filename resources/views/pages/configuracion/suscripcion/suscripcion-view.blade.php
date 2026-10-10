<style>

    .dtrg-group {
        font-weight: bold;
        background-color: #f0f0f0;
        padding: 10px;
        text-transform: uppercase;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="row px-3 pt-3">

            {{-- Total mensual --}}
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body p-3">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total mensual</p>
                        <h5 class="font-weight-bolder mb-0" id="consumoTotalMensual">$ 0</h5>
                        <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle"
                             style="width: 35px; height: 35px; margin-top: -45px; float: inline-end;">
                            <i class="fas fa-coins text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total del ciclo --}}
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body p-3">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total del ciclo</p>
                        <h5 class="font-weight-bolder mb-0 text-info" id="consumoTotalCiclo">$ 0</h5>
                        <div class="icon icon-shape bg-gradient-info shadow-primary text-center rounded-circle"
                             style="width: 35px; height: 35px; margin-top: -45px; float: inline-end;">
                            <i class="fas fa-file-invoice-dollar text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Consumido al día --}}
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body p-3">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Consumido al día</p>
                        <h5 class="font-weight-bolder mb-0 text-success" id="consumoTotalAlDia">$ 0</h5>
                        <div class="icon icon-shape bg-gradient-success shadow-primary text-center rounded-circle"
                             style="width: 35px; height: 35px; margin-top: -45px; float: inline-end;">
                            <i class="fas fa-check-circle text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pendiente por consumir --}}
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card">
                    <div class="card-body p-3">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Pendiente por consumir</p>
                        <h5 class="font-weight-bolder mb-0 text-warning" id="consumoTotalPendiente">$ 0</h5>
                        <div class="icon icon-shape bg-gradient-warning shadow-primary text-center rounded-circle"
                             style="width: 35px; height: 35px; margin-top: -45px; float: inline-end;">
                            <i class="fas fa-hourglass-half text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent; box-shadow: none;">
            <div class="card-body row">

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link active" id="componentes-suscripcion-tab" data-bs-toggle="tab" data-bs-target="#componentes_suscripcion" type="button" role="tab" aria-controls="componentes_suscripcion" aria-selected="false">Componentes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link" id="pagos-suscripcion-tab" data-bs-toggle="tab" data-bs-target="#pagos_suscripcion" type="button" role="tab" aria-controls="pagos_suscripcion" aria-selected="false">Pagos</button>
                    </li>
                </ul>

                <div class="tab-content" style="background-color: white; border-top-right-radius: 10px;">
                    <div class="tab-pane fade show active" id="componentes_suscripcion" role="tabpanel" aria-labelledby="componentes-suscripcion-tab">
                        @include('pages.configuracion.suscripcion.componentes.componentes-table')
                    </div>
                    <div class="tab-pane fade" id="pagos_suscripcion" role="tabpanel" aria-labelledby="pagos-suscripcion-tab">
                        @include('pages.configuracion.suscripcion.pagos.pagos-table')
                    </div>
                </div>

            </div>
        </div>

    </div>

    @include('pages.configuracion.suscripcion.componentes.componentes-form')

</div>

<script>
    var esDios = JSON.parse('<?php echo $esDios; ?>');
</script>