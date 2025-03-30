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

    .item-img-producto {
        padding: 15px;
        border: solid 1px #596cff;
        border-radius: 10px;
        border-style: dashed;
        margin-left: 10px;
        width: 60px;
        text-align: -webkit-center;
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
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-9 col-sm-9">
                @can('productos create')
                    <button type="button" class="btn btn-primary btn-sm" id="createProducto">Agregar producto</button>
                    <button type="button" class="btn btn-info btn-sm" id="saveNewProducto"  style="display: none; margin-right: 10px;">Guardar producto</button>
                    <button type="button" class="btn btn-info btn-sm" id="saveEditProducto"  style="display: none; margin-right: 10px;">Actualizar producto</button>
                    <button id="saveNewProductoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left; opacity: 1;" disabled>
                        Creando producto
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="cancelProducto" style="display: none;">Cancelar producto</button>
                @endcan
                <button type="button" class="btn btn-light btn-sm" id="reloadProducto" style="padding: 8px;">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="col-12 col-md-3 col-sm-3">
                <input type="text" id="searchInputProductos" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>
        
        <div id="totales-products-view" style="content-visibility: auto; overflow: auto; display: none;">
            <div class="row ">

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Productos / Existencias</p>
                            <div style="display: flex;">
                                <h5 id="total_bodegas_producto" class="font-weight-bolder">
                                    0
                                </h5>
                                <h5 id="" class="font-weight-bolder">
                                    &nbsp;/&nbsp;
                                </h5>
                                <h5 id="total_productos_producto" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-success shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-box-2 text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Costo</p>
                            <div style="display: flex;">
                                <h5 class="font-weight-bolder">
                                    $&nbsp;
                                </h5>
                                <h5 id="total_costo_producto" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-warning shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-money-coins text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Venta</p>
                            <div style="display: flex;">
                                <h5 class="font-weight-bolder">
                                    $&nbsp;
                                </h5>
                                <h5 id="total_precio_producto" class="font-weight-bolder">
                                    0
                                </h5>
                            </div>
                            <div class="icon icon-shape bg-gradient-danger shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-money-coins text-lg opacity-10" style="top: 8px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-3 col-sm-6" style="margin-top: 5px; padding-bottom: 5px;">
                    <div class="card">
                        <div class="card-body p-2">
                            <p class="text-sm mb-0 text-uppercase font-weight-bold">Utilidad valor / Utilidad %</p>
                                <div style="display: flex;">
                                    <h5 class="font-weight-bolder">
                                        $&nbsp;
                                    </h5>
                                    <h5 id="total_utilidad_producto" class="font-weight-bolder">
                                        0
                                    </h5>
                                    <h5 id="" class="font-weight-bolder">
                                        &nbsp;/&nbsp;
                                    </h5>
                                    <h5 id="total_porcentaje_producto" class="font-weight-bolder">
                                        0
                                    </h5>
                                    <h5 class="font-weight-bolder">
                                        %
                                    </h5>
                                </div>
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle" style="width: 30px !important; height: 30px !important; margin-top: -45px; float: inline-end;">
                                <i class="ni ni-briefcase-24 text-lg opacity-10" style="top: 6px !important;" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div id="table-products-view" class="card mb-4" style="content-visibility: auto; overflow: auto; margin-top: 10px;">
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
@include('pages.tablas.productos.productos-variante-bodega')
    

<script>
    let primeraBodegas = @json($bodegas);
    let editarProductos = @json(auth()->user()->can('productos update'));
    let eliminarProductos = @json(auth()->user()->can('productos delete'));
    let parqueaderoProductos = @json(auth()->user()->can('parqueadero productos'));
</script>