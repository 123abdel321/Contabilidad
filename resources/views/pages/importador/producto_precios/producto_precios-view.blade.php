<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.importador.producto_precios.producto_precios-header')
            </div>
        </div>

        <div id="card-import-producto-precios" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 20px;">
            @include('pages.importador.producto_precios.producto_precios-table')
            <div style="padding: 5px;"></div>
        </div>

    </div>

    
</div>