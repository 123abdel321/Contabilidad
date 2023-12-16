<div class="modal fade" id="resolucionesFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textResolucionesCreate" style="display: none;">Agregar resoluciones</h5>
                <h5 class="modal-title" id="textResolucionesUpdate" style="display: none;">Editar resoluciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="resolucionesForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_resoluciones_up" id="id_resoluciones_up" style="display: none;">
                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label>Comprobante <span style="color: red">*</span></label>
                            <select name="id_comprobante_resolucion" id="id_comprobante_resolucion" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                El comprobante es requerido
                            </div>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Nombre</label>
                            <input type="text" class="form-control form-control-sm" name="nombre_resolucion" id="nombre_resolucion" placeholder="NOMBRE RESOLUCIÓN" required>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Prefijo</label>
                            <input type="text" class="form-control form-control-sm" name="prefijo_resolucion" id="prefijo_resolucion" placeholder="RSO" required>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Consecutivo</label>
                            <input type="text" class="form-control form-control-sm" name="consecutivo_resolucion" id="consecutivo_resolucion" placeholder="1" required>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Numero resolucion</label>
                            <input type="text" class="form-control form-control-sm" name="numero_resolucion_resolucion" id="numero_resolucion_resolucion" placeholder="1" required>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Tipo resolución</label>
                            <select class="form-control form-control-sm" id="tipo_resolucion_resolucion" name="tipo_resolucion_resolucion" required>
                            
                                <option value="0">POS</option>
                                <option value="1">Facturacion electronica</option>
                                <option value="2">Nota debito</option>
                                <option value="3">Nota credito</option>
                                <option value="4">Documento Equivalente/Soporte</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Tipo impreción</label>
                            <select class="form-control form-control-sm" id="tipo_impresion_resolucion" name="tipo_impresion_resolucion" required>
                                <option value="0">POS</option>
                                <option value="1">Media Carta</option>
                                <option value="2">Carta</option>
                                <option value="3">Personalizada</option>
                            </select>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Fecha <span style="color: red">*</span></label>
                            <input name="fecha_manual_compra" id="fecha_resolucion" name="fecha_resolucion" class="form-control form-control-sm" type="date" required>
                            <div class="invalid-feedback">
                                La fecha es requerido
                            </div>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">vigencia (en meses)</label>
                            <input type="number" class="form-control form-control-sm" name="vigencia_resolucion" id="vigencia_resolucion" placeholder="6" required>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Consecutivo desde</label>
                            <input type="number" class="form-control form-control-sm" name="consecutivo_desde_resolucion" id="consecutivo_desde_resolucion" placeholder="1" required>
                        </div>
                        <div class="form-group col-12 col-sm-6 col-md-6">
                            <label for="example-text-input" class="form-control-label">Consecutivo desde</label>
                            <input type="number" class="form-control form-control-sm" name="consecutivo_hasta_resolucion" id="consecutivo_hasta_resolucion" placeholder="100" required>
                        </div>
                        
                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveResoluciones"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateResoluciones"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveResolucionesLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>