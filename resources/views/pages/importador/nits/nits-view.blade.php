<style>
    .table-import-nits thead {
        padding: 0.3rem 1.2rem !important;
    }
    .table-import-nits > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }
    .table-import-nits {
        max-height: 320px;
        overflow: auto;
    }
</style>
<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.importador.nits.nits-header')
            </div>
        </div>

        <div id="card-import-producto-precios" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 20px;">
        @include('pages.importador.nits.nits-table')
            <div style="padding: 5px;"></div>
        </div>

    </div>

</div>