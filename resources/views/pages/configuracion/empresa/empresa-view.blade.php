<div class="container-fluid py-2">
    <div class="row">

        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4" style="z-index: 9;">
                <button type="button" class="btn btn-primary btn-sm" id="generateNuevaEmpresa">
                    Crear empresa
                </button>
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="search" id="searchInputEmpresa" class="form-control form-control-sm search-table" onkeydown="searchEmpresas(event)" placeholder="Buscar">
            </div>
        </div>

    </div>

    <div id="items-tabla-empresa" class="card mb-4" style="content-visibility: auto; overflow: auto;">
        <div class="card-body">
            @include('pages.configuracion.empresa.empresa-table')
        </div>
    </div>

    @include('pages.configuracion.empresa.empresa-form')

</div>