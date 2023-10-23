<div class="card card-producto">
    <div class="card-header" style="border-bottom: solid 1px #e4e4e4; padding: 0rem; background-color: #1c4587;">
        <h5 class="card-title" style="margin-top: 5px; color: #FFF; margin-left: 15px;">Información general</h5>
    </div>
    <div class="card-body">
        <form id="newProductoForm" class="needs-invalidation" noinvalidate>

            <div class="row">

                <input type="text" class="form-control form-control-sm" name="id_producto_edit" id="id_producto_edit" style="display: none;">

                <div style="text-align: -webkit-center;">
                    <label id="text_tipo_producto" style="color: #667793; font-size: 14px;">Crea los bienes y mercancías que vendes, tambien puedes tener el control de tu inventario.</label>
                    <label id="text_tipo_servicio" style="display: none; color: #667793; font-size: 14px;">Crea las actividades comerciales o de consultoría que ofreces a tus clientes.</label>
                    <label id="text_tipo_combo" style="display: none; color: #667793; font-size: 14px;">Agrupa en un solo ítem un conjunto de productos, servicios o una combinación entre ambos.</label>
                </div>

                <div id="spacing-producto-type" style="padding: 5px;"></div>

                <div class="row col-12 col-sm-6 col-md-6" style="text-align: -webkit-center;">
                    <div class="form-group col-6 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Código</label>
                        <input type="text" class="form-control form-control-sm" name="codigo_producto" id="codigo_producto" onfocusout="addCodigoProducto()" required>
                        <div class="invalid-feedback">
                            El campo Código es requerido
                        </div>
                    </div>
    
                    <div class="form-group col-6 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="nombre_producto" id="nombre_producto" onfocusout="addNombreProducto()" required>
                        <div class="invalid-feedback">
                            El campo Nombre es requerido
                        </div>
                    </div>
    
                    <div class="form-group col-6 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Familia</label>
                        <select name="id_familia_producto" id="id_familia_producto" class="form-control form-control-sm" required>
                        </select>
                        <div class="invalid-feedback">
                            El campo Familia es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Costo compra</label>
                        <input type="number" class="form-control form-control-sm" name="precio_inicial" id="precio_inicial" onfocus="this.select();" onfocusout="addPrecioInicialProducto()" onkeypress="changeCostoCompra(event)" value="0" required>
                        <div class="invalid-feedback">
                            El campo Precio inicial es requerido
                        </div>
                    </div>
    
                    <div class="form-group col-6 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Valor venta</label>
                        <input type="number" class="form-control form-control-sm" name="precio_producto" id="precio_producto" onfocus="this.select();" onfocusout="actualizarDatosProducto()" onkeypress="changeValorVenta(event)" value="0" required>
                        <div class="invalid-feedback">
                            El campo Precio es requerido
                        </div>
                    </div>
    
                    <div class="form-group col-6 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Precio minimo</label>
                        <input type="number" class="form-control form-control-sm" name="precio_minimo" id="precio_minimo" onfocus="this.select();" onfocusout="actualizarDatosProducto()" onkeypress="changePrecioMinimo(event)" value="0" required>
                        <div class="invalid-feedback">
                            El campo Precio minimo es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Porcentaje utilidad</label>
                        <input type="number" class="form-control form-control-sm" name="porcentaje_utilidad" min="0" id="porcentaje_utilidad" onfocus="this.select();" onfocusout="actualizarDatosProducto()" onkeypress="changePorcentajeUtilidad(event)" value="0" required>
                        <div class="invalid-feedback">
                            El campo Porcentaje utilidad es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-6 col-md-6">
                        <label for="example-text-input" class="form-control-label">Valor utilidad</label>
                        <input type="number" class="form-control form-control-sm" name="valor_utilidad" id="valor_utilidad" onfocus="this.select();" onkeypress="changeValorUtilidad(event)" onfocusout="actualizarDatosProducto()" value="0" required>
                        <div class="invalid-feedback">
                            El campo Valor utilidad es requerido
                        </div>
                    </div>

                    <div class="form-group col-6 col-sm-6 col-md-6 row" style="margin-bottom: 0.1rem !important;" id="item-maneja-variante">
                        <label for="example-text-input" class="form-control-label">Maneja variantes</label>
                        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 15px; cursor: pointer; text-align: left;">
                            <input class="form-check-input" type="radio" name="producto_variantes" id="producto_variantes1" style="font-size: 11px; cursor: pointer;" checked>
                            <label class="form-check-label" for="producto_variantes1" style="font-size: 11px;">
                                Producto sin variantes
                            </label>
                        </div>
                        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 15px; cursor: pointer; text-align: left;">
                            <input class="form-check-input" type="radio" name="producto_variantes" id="producto_variantes2" style="font-size: 11px; cursor: pointer;">
                            <label class="form-check-label" for="producto_variantes2" style="font-size: 11px;">
                                Productos con variantes 
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-6" style="text-align: -webkit-center;">

                    <div class="col-12 col-sm-12 col-md-12" style="text-align: -webkit-center;">

                        <input type="radio" class="btn-check" name="options-outlined" id="tipo_producto_producto" onChange="changeProducType()" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="tipo_producto_producto">Producto</label>

                        <input type="radio" class="btn-check" name="options-outlined" id="tipo_producto_servicio" onChange="changeProducType()" autocomplete="off">
                        <label class="btn btn-outline-primary" for="tipo_producto_servicio">Servicio</label>

                        <input type="radio" class="btn-check" name="options-outlined" id="tipo_producto_combo" onChange="changeProducType()" autocomplete="off" disabled>
                        <label class="btn btn-outline-primary" for="tipo_producto_combo">Combo</label>
                        
                    </div>

                    <div class="justify-content-center col-12 col-sm-12 col-md-12">
                        <div style="text-align: -webkit-center; height: 185px;">
                            <img id="default_produc_img" onclick="document.getElementById('newImagenProducto').click();" src="/img/add_product_img.png" class="img-fluid rounded mx-auto d-bloc" style="width: auto; height: 100%; cursor: pointer; border-radius: 10%;">
                            <img id="new_produc_img" onclick="document.getElementById('newImagenProducto').click();" src="" class="img-fluid rounded mx-auto d-bloc" style="width: auto; height: 100%; cursor: pointer; border-radius: 10%;">
                        </div>
                    </div>
    
                    <input type="file" name="newImagenProducto" id="newImagenProducto" onchange="readURL(this);" style="display: none" />

                    <br/>
                </div>

            </div>

        </form>
    </div>
</div>

<br/>

<div class="card card-producto" id="producto-inventario" style="display: none;">
    <div class="card-header" style="border-bottom: solid 1px #e4e4e4; padding: 0rem; background-color: #1c4587;">
        <h5 class="card-title" style="margin-top: 5px; color: #FFF; margin-left: 15px;">Inventario</h5>
    </div>
    <div class="card-body" style="text-align: -webkit-center;">
        <label style="color: #667793; font-size: 14px;">
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
        <h5 class="card-title" style="margin-top: 5px; color: #FFF; margin-left: 15px;">Variantes</h5>
    </div>
    <div class="card-body">
        <div style="text-align: -webkit-center;">
            <label style="color: #667793; font-size: 14px;">
                Agrega atributos para categorizar tus productos, como talla y color.
            </label>
        </div>

        <div id="spacing-producto-type" style="padding: 5px;"></div>

        <div class="card">
            <div class="card-body" style="margin-top: 10px;">
                <div class="row item-variante" id="variantes-contenedor">
                    <!-- <div class="col" id="varianteid_">
                        <button type="button" class="btn btn btn-outline-dark">
                            COLOR <span class="badge bg-info">4</span>
                        </button>
                    </div> -->
                    <!-- <span class="badge bg-gradient-light" style="padding: 12px; cursor: pointer;">
                        Color
                    </span> -->
                </div>
            </div>
            <div id="btn-modal-variantes" class="card-footer text-muted btn" onclick="agregarVarianteProducto()" style="background-color: #596cff; color: white !important; text-align: -webkit-center; padding: 0.8rem;">
                <i class="fas fa-plus-circle"></i> <label style="color: white;">Agregar variante</label>
            </div>
        </div>

        <div id="spacing-producto-type" style="padding: 10px;"></div>

        <div id="contenedor-variantes-generales" style="display: none;">

            <div style="text-align: -webkit-center;">
                <label style="color: #667793; font-size: 14px;">
                    Productos con variantes
                </label>
            </div>

            <div id="spacing-producto-type" style="padding: 5px;"></div>

            <div class="card" style="overflow-y: auto;">

                <div class="card-body" style="padding: 0.2rem;">
                    <table id="productosVariantesTable" class="table table-bordered display responsive" width="100%">
                        <thead style="background-color: #7ea1ff2b;">
                            <tr>
                                <th style="border-radius: 15px 0px 0px 0px !important;">Variantes</th>
                                <th>Código</th>
                                <th>Costo compra</th>
                                <th>Precio minimo</th>
                                <th>Precio Inicial</th>
                                <th>Bodegas</th>
                                <th style="border-radius: 0px 15px 0px 0px !important;">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>

        <!-- <div style="text-align: -webkit-center;">
            <button type="button" class="btn btn-outline-success" >
                <i class="fas fa-plus-circle"></i>
                Agregar variante
            </button>
        </div> -->
    </div>
</div>

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

        <div class="variantes-productos-contenedor" id="variantes-productos-contenedor">
        </div>
    </div>
</div>