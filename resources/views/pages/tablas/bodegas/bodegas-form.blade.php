<div class="modal fade" id="bodegasFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textBodegasCreate" style="display: none;">Agregar bodega</h5>
                <h5 class="modal-title" id="textBodegasUpdate" style="display: none;">Editar bodega</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="bodegasForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>
                    <div class="row">
                        <input type="text" class="form-control" name="id_bodega" id="id_bodega" style="display: none;">

                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="example-text-input" c|lass="form-control-label">Codigo</label>
                            <input type="text" class="form-control form-control-sm" name="codigo_bodega" id="codigo_bodega" placeholder="123" required>
                            <div class="invalid-feedback">
                                El campo es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="example-text-input" class="form-control-label">Nombre</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_bodega" id="nombre_bodega" placeholder="Principal" required>
                            <div class="invalid-feedback">
                                El campo es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="example-text-input" class="form-control-label">Ubicación</label>
                            <input type="text" class="form-control form-control-sm" name="ubicacion_bodega" id="ubicacion_bodega">
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="exampleFormControlSelect1">Cuenta cartera</label>
                            <select name="id_cuenta_cartera_bodega" id="id_cuenta_cartera_bodega" class="form-control form-control-sm">
                            </select>
                        </div>

                        <div class="form-group col-12 col-sm-6 col-md-4">
                            <label for="exampleFormControlSelect1">Centro de costos</label>
                            <select name="id_centro_costos_bodega" id="id_centro_costos_bodega" class="form-control form-control-sm">
                            </select>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveBodegas"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateBodegas"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveBodegasLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>