<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('parqueadero create')
                        <button type="button" class="btn btn-primary btn-sm" id="createParqueadero">Agregar vehiculo</button>
                    @endcan
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

            @include('pages.capturas.parqueadero.parqueadero-table')

            </div>
        </div>
    </div>

    @include('pages.capturas.parqueadero.parqueadero-form')
    @include('pages.capturas.parqueadero.parqueadero-venta')
    
</div>

<script>
    var primerNitParqueadero = @json($cliente);
    var primeraBodegaParqueadero = @json($bodegas);
    var ivaIncluidoParqueadero = @json($iva_incluido);
    var primeraResolucionParqueadero = @json($resolucion);
    var editarParqueadero = @json(auth()->user()->can('parqueadero update'));
    var eliminarParqueadero = @json(auth()->user()->can('parqueadero delete'));
</script>