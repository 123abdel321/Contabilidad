<div class="modal fade" id="bodegasProductoVarianteFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textBodegaProductoCreate">Bodegas de variantes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="productoBodegaVarianteForm" style="margin-top: 10px;">
                    <div class="row">
                        <div class="form-group col-12 col-sm-12 col-md-12">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Seleccionar Bodega</label>
                            <select class="form-control form-control-sm" name="id_bodega_producto_variante" id="id_bodega_producto_variante">
                                <option value="">Ninguno</option>
                            </select>
                            <div class="invalid-feedback">
                                El campo es requerido
                            </div>
                        </div>

                        <div id="productos_bodegas_contenedor">

                            <!-- <div id="contenedor-variante-bodegas_" class="col-12 col-sm-12 col-md-12 row">
    
                                <div style="padding: 5px; padding: 5px; border-top: solid 1px #dfdfdf; margin-left: 10px;"></div>
                                
                                <div class="form-group col-12 col-sm-6 col-md-6" >
                                    <label for="example-text-input" class="form-control-label">Bodega</label>
                                    <input type="number" class="form-control form-control-sm" name="bodega-producto-variante_" id="bodega-producto-variante_" disabled>
                                </div>
    
                                <div class="form-group col-12 col-sm-6 col-md-6" >
                                    <label for="example-text-input" class="form-control-label">Cantidad</label>
                                    <input type="number" class="form-control form-control-sm" name="cantidad-producto-variante_" id="cantidad-producto-variante_">
                                </div>

                                <div class="col-12 col-sm-12 col-md-12">
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="width: 100%; margin-top: 5px; box-shadow: none; ">
                                        Eliminar
                                    </button>
                                </div>

                            </div> -->

                        </div>
                        
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>