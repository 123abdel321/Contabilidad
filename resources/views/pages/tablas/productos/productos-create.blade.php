<div class="card card-producto">
    <div class="card-header" style="border-bottom: solid 1px #e4e4e4; padding: 0rem; background-color: #1c4587;">
        <h6 class="card-title" style="margin-top: 5px; color: #FFF; margin-left: 15px;">Información general</h6>
    </div>
    <div class="card-body">

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12" style="text-align: -webkit-center;">

                <label id="text_tipo_producto" style="color: #667793; font-size: 14px;">Crea los bienes y mercancías que vendes, tambien puedes tener el control de tu inventario.</label>
                <label id="text_tipo_servicio" style="display: none; color: #667793; font-size: 14px;">Crea las actividades comerciales o de consultoría que ofreces a tus clientes.</label>
                <label id="text_tipo_combo" style="display: none; color: #667793; font-size: 14px;">Agrupa en un solo ítem un conjunto de productos, servicios o una combinación entre ambos.</label>

                <div id="spacing-producto-type" style="padding: 5px;"></div>

                <input type="radio" class="btn-check" name="options-outlined" id="tipo_producto_producto" onChange="changeProducType()" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="tipo_producto_producto">Producto</label>

                <input type="radio" class="btn-check" name="options-outlined" id="tipo_producto_servicio" onChange="changeProducType()" autocomplete="off">
                <label class="btn btn-outline-primary" for="tipo_producto_servicio">Servicio</label>

                <input type="radio" class="btn-check" name="options-outlined" id="tipo_producto_combo" onChange="changeProducType()" autocomplete="off" disabled>
                <label class="btn btn-outline-primary" for="tipo_producto_combo">Combo</label>
                
            </div>
            <br/>
            <div class="form-group col-6 col-sm-4 col-md-4" >
                <label for="example-text-input" class="form-control-label">Nombre</label>
                <input type="text" class="form-control form-control-sm" name="nombre_producto" id="nombre_producto">
                <div class="invalid-feedback">
                    El campo es requerido
                </div>
            </div>

            <div class="form-group col-6 col-sm-4 col-md-4" >
                <label for="example-text-input" class="form-control-label">Código</label>
                <input type="text" class="form-control form-control-sm" name="codigo_producto" id="codigo_producto">
                <div class="invalid-feedback">
                    El campo es requerido
                </div>
            </div>

            <div class="form-group col-6 col-sm-4 col-md-4">
                <label for="exampleFormControlSelect1">Familia</label>
                <select name="id_familia_producto" id="id_familia_producto" class="form-control form-control-sm">
                </select>
            </div>

            <div class="form-group col-6 col-sm-4 col-md-4" >
                <label for="example-text-input" class="form-control-label">Precio</label>
                <input type="number" class="form-control form-control-sm" name="precio_producto" id="precio_producto">
                <div class="invalid-feedback">
                    El campo es requerido
                </div>
            </div>

            <div class="form-group col-6 col-sm-4 col-md-4" >
                <label for="example-text-input" class="form-control-label">Precio maximo</label>
                <input type="number" class="form-control form-control-sm" name="precio_maximo" id="precio_maximo">
                <div class="invalid-feedback">
                    El campo es requerido
                </div>
            </div>

            <div class="form-group col-6 col-sm-4 col-md-4" >
                <label for="example-text-input" class="form-control-label">Precio inicial</label>
                <input type="number" class="form-control form-control-sm" name="precio_inicial" id="precio_inicial">
                <div class="invalid-feedback">
                    El campo es requerido
                </div>
            </div>

            <div class="form-group col-6 col-sm-4 col-md-4 row" style="margin-bottom: 0.1rem !important;" id="item-maneja-variante">
                <label for="example-text-input" class="form-control-label">Maneja variantes</label>
                <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 15px; cursor: pointer;">
                    <input class="form-check-input" type="radio" name="producto_variantes" id="producto_variantes1" style="font-size: 11px; cursor: pointer;" checked>
                    <label class="form-check-label" for="producto_variantes1" style="font-size: 11px;">
                        Producto sin variantes
                    </label>
                </div>
                <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 15px; cursor: pointer;">
                    <input class="form-check-input" type="radio" name="producto_variantes" id="producto_variantes2" style="font-size: 11px; cursor: pointer;">
                    <label class="form-check-label" for="producto_variantes2" style="font-size: 11px;">
                        Productos con variantes 
                    </label>
                </div>
            </div>


        </div>

    </div>
</div>

<br/>

<div class="card card-producto" id="producto-inventario">
    <div class="card-header" style="border-bottom: solid 1px #e4e4e4; padding: 0rem; background-color: #1c4587;">
        <h6 class="card-title" style="margin-top: 5px; color: #FFF; margin-left: 15px;">Inventario</h6>
    </div>
    <div class="card-body">
        <label style="color: #667793; font-size: 14px; text-align: -webkit-center;">
            Distribuye y controla las cantidades de tus productos
        </label>

        <div id="spacing-producto-type" style="padding: 5px;"></div>

        <ul class="list-group" id="bodegas-contenedor">
            <!-- <li class="list-group-item d-flex justify-content-between align-items-center">
                <div class="item-bodega">
                    <i class="fas fa-box-open" style="font-size: 20px; color: #596cff;"></i>
                </div>
                <div style="text-align: -webkit-center;">
                    <h6>Pruebas internaciones de 09</h6>
                    <label>Cantidad: 0</label>
                </div>
                <div>
                    <div style="padding: 10px; cursor: pointer" onclick="editarBodega()">
                        <i class="fas fa-edit" style="font-size: 15px; color: lawngreen;"></i>
                    </div>
                    <div style="padding: 10px;">
                        <i class="fas fa-trash-alt" style="font-size: 15px; color: red;"></i>
                    </div>
                </div>
            </li> -->
        </ul>

        <br/>

        <div style="text-align: -webkit-center;">
            <button type="button" class="btn btn-outline-success" onclick="agregarBodegaProducto()">
                <i class="fas fa-plus-circle"></i>
                Agregar bodega
            </button>
        </div>

    </div>
</div>

<br/>

<div class="card card-producto" id="producto-variantes" style="display: none;">
    <div class="card-header" style="border-bottom: solid 1px #e4e4e4; padding: 0rem; background-color: #1c4587;">
        <h6 class="card-title" style="margin-top: 5px; color: #FFF; margin-left: 15px;">Variantes</h6>
    </div>
    <div class="card-body">
        <div style="text-align: -webkit-center;">
            <label style="color: #667793; font-size: 14px;">
                Agrega atributos para categorizar tus productos, como talla y color.
            </label>
        </div>

        <div id="spacing-producto-type" style="padding: 5px;"></div>

        <div class="variantes-contenedor" id="variantes-contenedor">
            <!-- <div class="item-variante">
                <span class="badge bg-gradient-info" style="padding: 12px; cursor: pointer;">
                    Color
                </span>
            </div> -->
        </div>

        <br/>

        <div style="text-align: -webkit-center;">
            <button type="button" class="btn btn-outline-success" onclick="agregarVarianteProducto()">
                <i class="fas fa-plus-circle"></i>
                Agregar variante
            </button>
        </div>
    </div>
</div>