<div class="modal fade" id="impuestoFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textImpuestoCreate" style="display: none;">Agregar impuesto | Valor UVT: {{ number_format($valor_uvt) }}</h5>
                <h5 class="modal-title" id="textImpuestoUpdate" style="display: none;">Editar impuesto | Valor UVT: {{ number_format($valor_uvt) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="impuestoForm" style="margin-top: 10px;">
                    <div class="row">
                        <input type="text" class="form-control" name="id_impuesto_up" id="id_impuesto_up" style="display: none;">

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="id_tipo_impuesto_impuesto">Tipo impuesto <span style="color: red">*</span></label>
                            <select name="id_tipo_impuesto_impuesto" id="id_tipo_impuesto_impuesto" class="form-control form-control-sm" style="width: 100%; font-size: 13px;" required>
                            </select>
                            
                            <div class="invalid-feedback">
                                El tipo impuesto es requerido
                            </div>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="nombre_impuesto" class="form-control-label">Nombre <span style="color: red">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="nombre" id="nombre_impuesto" placeholder="Retención sobre el IVA" required>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="base_impuesto" class="form-control-label">Base <span style="color: red">*</span></label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="base" id="base_impuesto" placeholder="0.00" value="0" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="total_uvt_impuesto" class="form-control-label">Total UVT <span style="color: red">*</span></label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="total_uvt" id="total_uvt_impuesto" value="0" placeholder="0.00" onkeyup="actualizarBase()" required>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="porcentaje_impuesto" class="form-control-label">Porcentaje (%) <span style="color: red">*</span></label>
                            <input type="number" step="0.01" class="form-control form-control-sm" name="porcentaje" id="porcentaje_impuesto" value="0" placeholder="0.00" required>
                        </div>
                    </div>  
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveImpuesto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateImpuesto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveImpuestoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>