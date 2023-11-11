<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }

    .movimiento_inventario_producto_load {
        position: absolute;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
        margin-left: 75% !important;
    }

    .combo-grid-nit {
        min-width: 200px !important;
    }

    .combo-grid {
        min-width: 230px !important;
    }

    .drop-row-grid {
        margin-bottom: 0rem !important;
        font-size: 12px;
        margin-top: 4px;
        border-radius: 50px;
        width: 26px;
    }

    .fa-trash-alt {
        margin-left: -3px;
        margin-top: 1px;
    }
    #documentoReferenciaTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #documentoReferenciaTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .btn-group {
        box-shadow: 0 0px 0px rgba(50, 50, 93, 0.1), 0 0px 0px rgba(0, 0, 0, 0.08);
    }

    .normal_input {
        border-radius: 9px !important;
    }

    #movimientoInventarioTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #movimientoInventarioTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .table-captura-movimiento-inventario {
        max-height: 320px;
        overflow: auto;
    }

    .table-captura-movimiento-inventario thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-movimiento-inventario > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

    @media (min-width: 768px) {
        #tabla-captura-movimiento-inventario.col-md-9 {
            flex: 0 0 auto;
            width: 74%;
        }
    }

    @media (min-width: 576px) {
        #totales-movimiento-inventario-card.col-sm-5 {
            flex: 0 0 auto;
            width: 40.5%;
        }
    }

    @media (min-width: 768px) {
        #totales-movimiento-inventario-card.col-md-12 {
            flex: 0 0 auto;
            width: 100% !important;
        }
    }

    .table-captura-movimiento-inventario thead th {
        padding: 0.3rem 1.2rem !important;
    }

    .table-captura-movimiento-inventario > :not(caption) > * > * {
        padding: 0.1rem 0.1rem;
    }

</style>

<div class="container-fluid py-2">

    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

            @include('pages.capturas.movimiento_inventario.movimiento_inventario-filter')

            </div>
        </div>
    </div>

    <div class="row justify-content-between">

        <div id="tabla-captura-movimiento-inventario" class="card mb-4 col-12 col-sm-12 col-md-9 ml-auto">
            <div id="card-movimiento-inventario" class="card-body" style="content-visibility: auto; overflow: auto; border-radius: 20px;">

                @include('pages.capturas.movimiento_inventario.movimiento_inventario-table')
                <div style="padding: 8px;"></div>

            </div>
        </div>

        <div class="col-12 col-sm-12 col-md-3 ml-auto">
            
            <div class="row justify-content-between">
                <div id="totales-movimiento-inventario-card" class="card col-12 col-sm-5 col-md-12 ml-auto" style="height: min-content; margin-bottom: 0.5rem !important;">
                    <table class="table table-bordered table-captura-movimiento_inventario" width="100%" style="margin-top: 12px;">
                        <tbody>
                            <tr>
                                <td><h6 style="font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;">CANTIDAD: </h6></td>
                                <td><h6 style="float: right; font-size: 0.9rem; margin-bottom: 0px; font-weight: 500;" id="movimiento_inventario_cantidad">0.00</h6></td>
                            </tr>
                            <td><h6 style="margin-bottom: 0px; font-weight: bold;">TOTAL: </h6></td>
                                <td><h6 style="margin-bottom: 0px; float: right; font-weight: bold;" id="movimiento_inventario_total_valor">0.00</h6></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    
</div>