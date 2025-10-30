
<style>
    .error {
        color: red;
    }
    .edit-inmueble {
        width: 10px;
    }
    .drop-inmueble {
        width: 10px;
    }
    .fa-inmueble {
        margin-left: -5px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div id="tablas_primas" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
                
                @include('pages.capturas.primas.primas-table')

            </div>
        </div>
    </div>
    
</div>

@include('pages.capturas.primas.primas-form')
@include('pages.capturas.primas.primas-confirm')
@include('pages.capturas.primas.primas-detalle')

<script>

</script>