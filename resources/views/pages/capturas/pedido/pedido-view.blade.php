<style>

    .item-ubicacion {
        width: 130px;
    }

    .item-ubicacion .ubicaciones-datos {
        width: 130px;
        text-align: center;
        padding: 5px;
        border: solid 1px #3a5c89;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 5px;
        margin-bottom: 5px;
        background-color: #3a5c89;
        box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 2px 2px 2px rgb(0 0 0 / 57%);
    }

    .item-ubicacion .ubicaciones-datos:hover {
        background-color: #FFF !important;
        border: solid 1px #d3d3d3 !important;
        transform: translateY(-1px);
        box-shadow: 0px 0px 0px 0px rgba(50, 50, 93, 0.1), inset 0px 0px 0px 0px rgb(0 0 0 / 45%) !important;
    }

    .item-ubicacion .ubicaciones-datos:hover .nombre,
    .item-ubicacion .ubicaciones-datos:hover .total {
        color: black;
    }

    .item-ubicacion .ubicaciones-datos .nombre {
        font-weight: 500;
        color: white;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .item-ubicacion .ubicaciones-datos .total {
        font-weight: 800;
        color: white;
    }

    .item-ubicacion .ubicaciones-datos .selected {
        width: 9px;
        height: 9px;
        background-color: #0dff00;
        border-radius: 50%;
        justify-self: center;
        margin-bottom: 4px;
        margin-top: 4px;
    }

    .item-ubicacion .ubicaciones-datos .without {
        width: 9px;
        height: 9px;
        background-color:rgb(255, 0, 0);
        border-radius: 50%;
        justify-self: center;
        margin-bottom: 4px;
        margin-top: 4px;
    }

    .item-ubicacion .ubicaciones-datos .with {
        width: 9px;
        height: 9px;
        background-color:rgb(255, 217, 0);
        border-radius: 50%;
        justify-self: center;
        margin-bottom: 4px;
        margin-top: 4px;
    }

    .ubicaciones-datos.active {
        background-color: ghostwhite !important;
        border: solid 1px #d3d3d3 !important;
        box-shadow: 0px 0px 7px -10px rgba(50, 50, 93, 0.1), inset 1px 1px 3px 0px rgb(0 0 0 / 45%) !important;
    }

    .ubicaciones-datos.active .nombre, 
    .ubicaciones-datos.active .total {
        color: black !important;
    }

    @media (min-width: 576px) {
        #items-pedidos-venta.col-sm-5 {
            flex: 0 0 auto;
            width: 40.5%;
        }
    }

    @media (min-width: 768px) {
        #items-pedidos-venta.col-md-12 {
            flex: 0 0 auto;
            width: 100% !important;
        }
    }

    @media (min-width: 769px) and (max-width: 990px) {
        #table-captura-pedidos.col-md-7 {
            flex: 0 0 auto;
            width: 57%;
        }
    }

    @media (min-width: 991px) {
        #table-captura-pedidos.col-md-7 {
            flex: 0 0 auto;
            width: 66%;
        }
    }

    .table-pedidos-ventas {
        max-height: 320px;
        overflow: auto;
    }

    .table-pedidos-ventas thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-pedidos-ventas > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

    .item-producto {
        box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 2px 2px 2px rgb(0 0 0 / 57%);
        border-radius: 9px;
        border: 0px;
        padding: 2px;
        cursor: pointer;
        margin: 2px;
        /* height: min-content; */
        width: 100px;
        background-color: #FFF;
    }

    .item-producto:hover {
        transform: translateY(-1px);
        box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 0px 0px 0px rgb(0 0 0 / 57%) !important;
    }

    .item-producto .producto-datos {
        margin-bottom: 5px;
    }

    .item-producto .producto-datos .imagen img{
        width: 100%;
        height: 100px;
        object-fit: contain;
        border-radius: 10px;
    }

    .item-producto .producto-datos .nombre {
        font-weight: 500;
        color: black;
        justify-self: center;
        text-align: center;
        font-size: smaller;
    }

    .item-producto .producto-datos .precio {
        font-weight: 800;
        color: black;
        justify-self: center;
    }

    .item-producto .producto-datos .inventario {
        justify-self: center;
    }

    .filter-familias {
        display: flex;
        gap: 5px;
        overflow-x: auto;
        margin-top: 5px;
    }

    .filter-familias span {
        margin-bottom: 4px !important;
    }

    #contenedor-productos-pedidos {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        padding: 3px;
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
        align-content: flex-start;
        max-height: 58vh;
        background-color: ghostwhite;
        padding: 5px;
        border-radius: 5px;
        place-content: space-evenly;
    }

    #contenedor-productos-pedidos-loading {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        padding: 3px;
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
        align-content: flex-start;
        max-height: 70vh;
        background-color: ghostwhite;
        padding: 5px;
        border-radius: 5px;
        place-content: space-evenly;
    }

    .list-group-pedidos .list-group-item{
        padding: 0px;
    }

    .list-group-pedidos .list-group-item .nombre{
        font-weight: 600;
        color: #3f3f3f;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .list-group-pedidos .list-group-item .precio{
        font-weight: 600;
        color: #3f3f3f;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .list-group-pedidos .list-group-item .cantidad {
        display: flex;
        place-items: center;
        border: solid 1px #d7d7d7;
        border-radius: 15px;
        height: 28px;
        place-self: center;
        padding: 0px;
    }

    .list-group-pedidos .list-group-item .cantidad input {
        width: 100%;
        border: none;
        place-items: center;
    }

    .list-group-pedidos .list-group-item .cantidad input:focus-visible {
        outline: thick !important;
    }

    .list-group-pedidos .list-group-item .cantidad i{
        color: #0671ff;
        font-size: 11px;
    }

    .list-group-pedidos .list-group-item .cantidad .agregar{
        width: 30px !important;
        height: 28px;
        place-content: center;
        border-radius: 0px 10px 10px 0px;
        text-align: center;
        cursor: pointer;
    }

    .list-group-pedidos .list-group-item .cantidad .quitar{
        width: 30px !important;
        height: 28px;
        place-content: center;
        border-radius: 10px 0px 0px 10px;
        text-align: center;
        cursor: pointer;
    }

    .list-group-pedidos .list-group-item .cantidad .agregar:hover{
        background-color: #0671ff !important
    }

    .list-group-pedidos .list-group-item .cantidad .quitar:hover{
        background-color: red !important
    }

    .list-group-pedidos .list-group-item .cantidad .agregar:hover i{
        color: #FFF !important
    }

    .list-group-pedidos .list-group-item .cantidad .quitar:hover i{
        color: #FFF !important
    }

    .list-group-pedidos .list-group-item .eliminar{
        place-self: center;
        text-align: center;
    }

    .list-group-pedidos .list-group-item .eliminar i{
        color: red;
        font-size: 15px;
        padding: 5px;
        border-radius: 5px;
        cursor: pointer;
    }

    .list-group-pedidos .list-group-item .eliminar i:hover{
        color: white;
        background-color: red;
    }

    .cliente-pedidos .select2-container--bootstrap-5 {
        height: 30px;
    }

    .table-captura-pedidos {
        max-height: 320px;
        overflow: auto;
    }

    .table-captura-pedidos thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-pedidos > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

</style>

<div class="pedidos-capturas-view container-fluid py-2">

    <div class="row justify-content-between">

        <div id="table-captura-pedidos" class="card mb-4 col-12 col-sm-12 col-md-7 col-lg-8 ml-auto" style="padding: 0;">

            <div class="card-body container" style="content-visibility: auto; overflow: auto; border-radius: 20px; padding: 2px 10px 0px 10px !important;">

                <b class="mt-2">Ubicaciones</b>

                <div id="div-item-ubicacion" style="display: flex; gap: 10px;">
                </div>

            </div>

            <div class="card-body container" style="content-visibility: auto; overflow: auto; border-radius: 20px;">

                <div class="filter-familias">
                    <span id="filter-familias-pedido" class="badge btn bg-gradient-dark familia-filter-pedidos" onclick="filtrarProductosPedidos()">TODOS</span>
                    @foreach ($familias as $familia)
                        <span id="filter-familias-pedido-{{ $familia->id }}" class="badge btn bg-gradient-light familia-filter-pedidos" onclick="filtrarProductosPedidos({{ $familia->id }})">{{ $familia->nombre }}</span>
                    @endforeach
                </div>

                <div style="width: 100%;">
                    <div class="row" style="width: 100%;">
                        <div class="col-8" style="display: inline-flex;">
                            <input type="text" id="searchInputPedidos" class="form-control form-control-sm search-table" placeholder="Buscar productos" onkeyup="buscarProductosPedidos()" style="margin-top: 7px; margin-bottom: 5px !important;">
                        </div>
                        <div class="col-4" style="place-self: center;">
                            <div id="count-productos-pedidos" style="place-content: center; font-size: 15px; font-weight: 500; color: darkcyan; margin-left: 10px;">Productos: 0</div>
                        </div>
                    </div>
                    
                </div>

                <div id="contenedor-productos-pedidos">

                </div>

            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-5 col-lg-4 ml-auto" style="max-height: 52vh;">
            <div class="row justify-content-between">
                <div id="totales-venta-card" class="card col-12 col-sm-12 col-md-12 ml-auto" style="height: min-content; margin-bottom: 0.5rem !important;">
                    <div class="mb-4 col-12 col-sm-12 col-md-12 ml-auto">

                        <div id="pedidoFilterForm" class="cliente-pedidos needs-validation row" style="margin-top: 10px;" novalidate>
                            <div class="col-12">
                                <label for="id_cliente_pedido">Cliente<span style="color: red">*</span></label>
                                <div class="input-group">
                                    <select name="id_cliente_pedido" id="id_cliente_pedido" class="form-control form-control-sm" style="font-size: 13px;" required>
                                    </select>
                                    <span id="" href="javascript:void(0)" onclick="openModalNewNit()" class="btn badge bg-gradient-light" style="min-width: 40px; position: static; height: 30px; border-radius: 0px 5px 5px 0px; box-shadow: 0px 0px 0px 0px, 0px 0px 0px 0px; ; margin-bottom: 0px !important;">
                                        <i class="fas fa-user-plus" style="font-size: 15px; margin-top: 2px"></i>
                                    </span>
                                    <div class="invalid-feedback">
                                        El cliente es requerido
                                    </div>
                                </div>
                            </div>

                            @if ($vendedores_pedidos)
                            <div class="form-group col-12" style="margin-bottom: 0px !important;">
                                <label for="id_vendedor_pedido">Vendedor<span style="color: red">*</span></label>
                                <select name="id_vendedor_pedido" id="id_vendedor_pedido" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                                </select>
                                
                                <div class="invalid-feedback">
                                    El vendedor es requerido
                                </div>
                            </div>
                            @endif

                            <div class="form-group col-12 col-sm-6 col-md-6">
                                <label for="id_bodega_pedido">Bodega<span style="color: red">*</span></label>
                                <select name="id_bodega_pedido" id="id_bodega_pedido" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                                </select>
                                
                                <div class="invalid-feedback">
                                    La bodega es requerida
                                </div>
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-6">
                                <label for="example-text-input" class="form-control-label">Consecutivo <span style="color: red">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="consecutivo_bodegas_pedidos" id="consecutivo_bodegas_pedidos" onkeypress="pressConcecutivoPedidos(event)" onfocus="this.select();" required>

                                <div class="invalid-feedback">
                                    El consecutivo es requerido
                                </div>
                            </div>

                        </div>
                    
                        <ul id="lista_productos_seleccionados" class="list-group list-group-pedidos" style="max-height: 38vh; overflow: auto; background-color: ghostwhite;">
                        </ul>

                        <table class="table table-bordered table-captura-pedidos" width="100%" style="margin-top: 12px;">
                            <tbody>
                                <tr>
                                    <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">SUB TOTAL: </h6></td>
                                    <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="pedido_sub_total">0.00</h6></td>
                                </tr>
                                <tr>
                                    <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">IVA: </h6></td>
                                    <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="pedido_total_iva">0.00</h6></td>
                                </tr>
                                <tr id="totales_descuento-pedidos" style="display: none;">
                                    <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;">DESCUENTO: </h6></td>
                                    <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="pedido_total_descuento">0.00</h6></td>
                                </tr>
                                <tr id="totales_retencion-pedidos" style="display: none;">
                                    <td><h6 style="margin-bottom: 0px; font-size: 0.9rem; font-weight: 500;" id="pedido_texto_retencion">RETENCION: </h6></td>
                                    <td><h6 style="margin-bottom: 0px; float: right; font-size: 0.9rem;" id="pedido_total_retencion">0.00</h6></td>
                                </tr>
                                <tr>
                                    <td><h6 style="margin-bottom: 0px; font-weight: bold;">TOTAL: </h6></td>
                                    <td><h6 style="margin-bottom: 0px; float: right; font-weight: bold;" id="pedido_total_valor">0.00</h6></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="row mt-3">
                            <div class="col-6" style="display: flex;">
                                <span id="eliminarPedidos" href="javascript:void(0)" class="badge btn badge bg-gradient-danger" style="width: 100%; float: right; margin-bottom: 0px !important; display: none;">
                                    <i class="fas fa-trash" style="font-size: 17px;"></i>
                                </span>
                                <span id="eliminarPedidosDisabled" href="javascript:void(0)" class="badge bg-danger" style="width: 100%; float: right; margin-bottom: 0px !important; cursor: no-drop;">
                                    <i class="fas fa-trash" style="font-size: 17px;"></i>
                                </span>
                                &nbsp;
                                <span id="imprimirPedidos" href="javascript:void(0)" class="badge btn badge bg-gradient-info" style="width: 100%; float: right; margin-bottom: 0px !important; display: none;">
                                    <i class="fas fa-print" style="font-size: 17px;"></i>
                                </span>
                                <span id="imprimirPedidosDisabled" href="javascript:void(0)" class="badge bg-info" style="width: 100%; float: right; margin-bottom: 0px !important; cursor: no-drop;">
                                    <i class="fas fa-print" style="font-size: 17px;"></i>
                                </span>
                            </div>
                            <div class="col-6">
                                <span id="crearCapturaPedidosLoading" class="badge bg-gradient-success" style="display:none; width: 100%;">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                                    <b style="vertical-align: text-top;">CARGANDO</b>
                                </span>
                                <span id="crearCapturaVentaPedidosDisabled" href="javascript:void(0)" class="badge bg-success" style="width: 100%; float: right; background-color: #2dce899c !important; cursor: no-drop;">
                                    <i class="fas fa-shopping-cart" style="font-size: 17px;"></i>&nbsp;
                                    <b style="vertical-align: text-top;">CREAR VENTA</b>
                                </span>
                                <span id="crearCapturaVentaPedidos" href="javascript:void(0)" class="badge btn badge bg-gradient-success" style="width: 100%; float: right; display: none; margin-bottom: 0px !important;">
                                    <i class="fas fa-shopping-cart" style="font-size: 17px;"></i>&nbsp;
                                    <b style="vertical-align: text-top;">CREAR VENTA</b>
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
        </div>
    </div>

</div>

@include('pages.capturas.pedido.pedido-venta')

<script>
    var primeraBodegaPedido = @json($bodegas);
    var primeraResolucionPedido = @json($resolucion);
    var ivaIncluidoPedido = @json($iva_incluido);
    var vendedoresPedido = @json($vendedores_pedidos);
    var primerNitPedido = @json($cliente);
</script>