<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    @can('conceptos_nomina create')
                        <span id="createConceptosNomina" href="javascript:void(0)" class="btn badge bg-gradient-success btn-bg-gold" style="min-width: 40px;">
                            <i class="fa-solid fa-folder-plus" style="font-size: 17px;"></i>&nbsp;
                            <b style="vertical-align: text-top;">CREAR CONTO DE NOMINA</b>
                        </span>
                    @endcan
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInputConceptosNomina" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.conceptos_nomina.conceptos_nomina-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.conceptos_nomina.conceptos_nomina-form', ['conceptosNomina' => $conceptosNomina])
    
</div>

<script>
    var editarConceptosNomina = @json(auth()->user()->can('conceptos_nomina update'));
    var eliminarConceptosNomina = @json(auth()->user()->can('conceptos_nomina delete'));
</script>