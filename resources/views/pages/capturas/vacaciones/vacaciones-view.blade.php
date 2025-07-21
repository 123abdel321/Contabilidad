<style>
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('vacaciones create')
                        <button type="button" class="btn btn-primary btn-sm" id="createVacaciones">Agregar vacaciones <i class="fas fa-plus-circle"></i></button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.capturas.vacaciones.vacaciones-table')

            </div>
        </div>
    </div>

    @include('pages.capturas.vacaciones.vacaciones-form')
    
</div>

<script>
    var editarVacaciones  = @json(auth()->user()->can('vacaciones update'));
    var eliminarVacaciones  = @json(auth()->user()->can('vacaciones delete'));
</script>