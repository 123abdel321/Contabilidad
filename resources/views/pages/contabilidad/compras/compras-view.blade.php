
<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.compras.compras-filter')
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.compras.compras-table')
            </div>
        </div>
    </div>
</div>
