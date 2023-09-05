<style>
    .error {
        color: red;
    }
    .edit-comprobante {
        width: 10px;
    }
    .drop-comprobante {
        width: 10px;
    }
    .fa-comprobante {
        margin-left: -5px;
    }
    .productos-maestra {
        border-bottom: 0px solid #dee2e6;
    }
    .productos-maestra > .nav-item {
        background-color: #dfdfdf;
        border-radius: 5px 5px 0px 0px;
    }

    .card-producto {
        box-shadow: -3px 5px 0.5rem 0 rgb(0 0 0 / 25%) !important;
    }

    .item-bodega {
        padding: 25px;
        border: solid 1px #596cff;
        border-radius: 10px;
        /* border-style: dashed; */
    }

    .variantes-contenedor {
        padding: 1rem;
        border: solid 1px #d8d8d8;
        border-radius: 10px;
    }

    .nav-productos.active {
        background-color: #596cff !important;
        color: white !important;
    }
    
    .variantes-listas .list-group-item.active {
        background-color: #2dce89 !important;
        border-color: #2dce89 !important;
        cursor: pointer;
    }

    .variantes-listas .list-group-item{
        cursor: pointer;
    }

    .hide-new-variante{
        display: none !important;
    }

    .item-variante-opcion {
        padding: 5px;
        cursor: pointer;
        border-radius: 5px;
        border: solid 1px #efefef;
        background-color: #efefef;
        box-shadow: 0px 0px 0.2rem 0 rgb(0 0 0 / 25%);
    }

    .item-variante-opcion-active {
        color: #00da00;
        border: solid 1px white !important;
        background-color: white !important;
        box-shadow: 1px 2px 0.2rem 0 rgb(0 0 0 / 25%) !important;
    }

</style>
        
<div class="container-fluid py-2">

    <div class="container-fluid py-2">
        <div class="row">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-4 col-sm-4">
                    <button type="button" class="btn btn-primary btn-sm" id="createProducto">Agregar producto</button>
                    <button type="button" class="btn btn-danger btn-sm" id="cancelProducto" style="display: none;">Cancelar producto</button>
                </div>
                <div class="col-12 col-md-8 col-sm-8">
                    <input type="text" id="searchInputProductos" class="form-control form-control-sm search-table" style="display: none;" placeholder="Buscar">
                </div>
            </div>

            <div id="table-products-view" class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body">

                    @include('pages.tablas.productos.productos-table')

                </div>
            </div>

            <div id="add-products-view" style="content-visibility: auto; overflow: auto; display: none;">
                @include('pages.tablas.productos.productos-create')
                <br/><br/>
            </div>
        </div>
    </div>

    @include('pages.tablas.productos.productos-bodega')
    @include('pages.tablas.productos.productos-variante')
    
</div>

<script>
    var primeraBodegas = JSON.parse('<?php echo $bodegas; ?>');
</script>