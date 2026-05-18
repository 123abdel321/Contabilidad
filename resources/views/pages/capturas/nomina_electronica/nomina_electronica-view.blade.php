<div class="container-fluid py-2">
    <div class="row">
        
        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.capturas.nomina_electronica.nomina_electronica-table')

            </div>
        </div>

        @include('pages.capturas.nomina_electronica.nomina_electronica-detalle')

    </div>
</div>

<script>
    var enviarNominaElectronica = @json(auth()->user()->can('nomina_electronica send'));
</script>