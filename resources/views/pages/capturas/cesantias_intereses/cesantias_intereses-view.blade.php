
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

        <div id="tablas_cesantias_intereses" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
            <div class="card-body">
                
                @include('pages.capturas.cesantias_intereses.cesantias_intereses-table')

            </div>
        </div>
    </div>
    
</div>

@include('pages.capturas.cesantias_intereses.cesantias_intereses-form')
@include('pages.capturas.cesantias_intereses.cesantias_intereses-confirm')
@include('pages.capturas.cesantias_intereses.cesantias_intereses-detalle')

<script>

</script>