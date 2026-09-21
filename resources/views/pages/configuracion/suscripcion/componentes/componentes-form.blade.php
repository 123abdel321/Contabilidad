<div class="modal fade" id="componentesSuscripcionFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textComponentesCreate" style="display: none;">Agregar componentes</h5>
                <h5 class="modal-title" id="textComponentesUpdate" style="display: none;">Editar componente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="componentesForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_componentes_up" id="id_componentes_up" style="display: none;">

                        <div class="form-group col-12">
                            <label for="id_componente_suscripcion">Componente</label>
                            <select name="id_componente_suscripcion" id="id_componente_suscripcion" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                        </div>

                        <div class="form-group col-12">
                            <label for="precio_componente_suscripcion" class="form-control-label">Precio componente</label>
                            <input name="precio_componente_suscripcion" id="precio_componente_suscripcion"type="number" class="form-control form-control-sm text-align-right" onfocus="this.select();" value="0" disabled>
                        </div>

                        <div class="form-group col-12">
                            <label for="fecha_componente_suscripcion" class="form-control-label">Fecha inicio suscripción</label>
                            <input name="fecha_componente_suscripcion" id="fecha_componente_suscripcion" class="form-control form-control-sm" type="date" required>
                        </div>

                        <div class="form-group col-12">
                            <label for="descuento_componente_suscripcion" class="form-control-label">Descuento</label>
                            <input name="descuento_componente_suscripcion" id="descuento_componente_suscripcion"type="number" class="form-control form-control-sm text-align-right" onfocus="this.select();" value="0" disabled>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveComponentes"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateComponentes"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveComponentesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>