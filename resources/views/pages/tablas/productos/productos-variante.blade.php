<div class="modal fade" id="variantesProductoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Configurar variantes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row">
                <div class="form-group col-12">
                    <label for="exampleFormid_tipo_documento">Seleccionar variante </label>
                    <select name="id_variante_producto" id="id_variante_producto" class="form-control form-control-sm">
                    </select>
                </div>

                <div id="spacing-producto-type" style="padding: 5px; padding: 5px; border-top: solid 1px #dfdfdf;"></div>


                <div class="row">
                    <div class="col-12 col-sm-6 col-md-6">
                        

                        <ul class="list-group variantes-listas" id="variante_producto_contenedor">
                        </ul>

                        <li id="form-new-variante" class="list-group-item d-flex justify-content-between align-items-center hide-new-variante" style="border-color: white; flex-flow: wrap;">
                            <input id="nombre-variante" type="text" class="form-control form-control-sm" placeholder="Nuevo nombre de variante" onkeydown="keyPressNombreInventario(event)" onfocusout="removeVariante()">
                            <i id="nombre-variante-loaging" class="fa fa-spinner fa-spin fa-fw" style="display: none; left: 100%; margin-left: -40px; margin-right: 10px;"></i>
                            <div>
                                <label style="color: #848484; font-weight: 300;">Presiona "Enter" para crear nueva variante</label>
                            </div>
                        </li>
                        <button id="button-new-variable" type="button" class="btn btn-outline-primary" onclick="agregarVarianteNombre()" style="width: 100%; margin-top: 5px;">
                            Nueva variante
                        </button>

                    </div>
                    <div id="variante_opcion_contenedor" class="col-12 col-sm-6 col-md-6" style="background-color: #1c45870d; min-height: 150px; padding: 10px; border-radius: 10px; padding-top: 3px;">
                        <!-- <div id="contenedor-opciones_">
                            <div class="item-variante-opcion item-variante-opcion-active">
                                <label>ROJO</label>
                                <i class="fas fa-check" style="float: right; margin-top: 5px; margin-right: 5px;"></i>
                            </div>
                        </div> -->
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveBodegaProducto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
            </div>
        </div>
    </div>
</div>