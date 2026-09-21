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

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent; box-shadow: none;">
            <div class="card-body row">
    
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <!-- <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Resumen</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link" id="facturacion-tab" data-bs-toggle="tab" data-bs-target="#facturacion" type="button" role="tab" aria-controls="facturacion" aria-selected="false">Suscripción</button>
                    </li> -->
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link active" id="componentes-suscripcion-tab" data-bs-toggle="tab" data-bs-target="#componentes" type="button" role="tab" aria-controls="componentes" aria-selected="false">Componentes</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="tab-porta-15px nav-link" id="pagos-suscripcion-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button" role="tab" aria-controls="pagos" aria-selected="false">Pagos</button>
                    </li>
                </ul>
    
                <div class="tab-content" style="background-color: white; border-top-right-radius: 10px;">
                    <div class="tab-pane fade show active" id="componentes_suscripcion" role="tabpanel" aria-labelledby="componentes-suscripcion-tab">
                        @include('pages.configuracion.suscripcion.componentes.componentes-table')
                    </div>
                    <div class="tab-pane fade" id="pagos_suscripcion" role="tabpanel" aria-labelledby="pagos-suscripcion-tab">
                        <!-- @include('pages.configuracion.suscripcion.pagos.pagos-table') -->
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