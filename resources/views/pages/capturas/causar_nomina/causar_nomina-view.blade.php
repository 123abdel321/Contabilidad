<style>

    .dtrg-group {
        font-weight: bold;
        background-color: #f0f0f0;
        padding: 10px;
        text-transform: uppercase;
    }

    .dt-body-right b {
        font-size: 1em;
        position: relative;
    }

    .dt-body-right b::after {
        content: attr(data-decimal);
        font-size: 0.8em;
        opacity: 0.8;
    }

    .integer-part {
        font-size: inherit;
    }
    .decimal-part {
        font-size: 0.8em;
        opacity: 0.8;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent; box-shadow: none;">

            <div class="card-body row">
                <ul class="nav nav-tabs" role="tablist" style="border-bottom: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="causar-nomina-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#causar_nomina" type="button" role="tab" aria-controls="causar_nomina" aria-selected="true">Causar nomina</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prestaciones-sociales-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#prestaciones_sociales" type="button" role="tab" aria-controls="prestaciones_sociales" aria-selected="false">Prestaciones sociales</button>
                    </li>
                </ul>
    
                <div class="tab-content" style="background-color: white; border-top-right-radius: 10px;">
                    <div class="tab-pane fade show active" id="causar_nomina" role="tabpanel" aria-labelledby="causar_nomina_tab">
                        @include('pages.capturas.causar_nomina.causar_nomina-table')
                    </div>
                    <div class="tab-pane fade" id="prestaciones_sociales" role="tabpanel" aria-labelledby="prestaciones_sociales_tab">
                        @include('pages.capturas.causar_nomina.prestaciones_sociales-table')
                    </div>
                </div>
            </div>

        </div>

        @include('pages.capturas.causar_nomina.causar_nomina-detalle')
        @include('pages.capturas.causar_nomina.prestaciones_sociales-form')

    </div>
    
</div>