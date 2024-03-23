<div class="modal fade" id="conceptoGastosFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="textConceptoGastoCreate" style="display: none;">Agregar concepto de gastos</h5>
                <h5 class="modal-title" id="textConceptoGastoUpdate" style="display: none;">Editar concepto de gastos</h5>
                <h5 class="modal-title" id="textConceptoGastoDuplicate" style="display: none;">Duplicar concepto de gastos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">

                <form id="conceptoGastosForm" style="margin-top: 10px;" class="row needs-invalidation" noinvalidate>

                    <input type="text" class="form-control" name="id_concepto_gasto_up" id="id_concepto_gasto_up" style="display: none;">

                    <div class="form-group col-12 col-sm-6 col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Código</label>
                        <input type="text" class="form-control form-control-sm" name="codigo_concepto_gasto" id="codigo_concepto_gasto">
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-12 col-sm-6 col-md-6" >
                        <label for="example-text-input" class="form-control-label">Nombre</label>
                        <input type="text" class="form-control form-control-sm" name="nombre_concepto_gasto" id="nombre_concepto_gasto">
                        <div class="invalid-feedback">
                            El campo es requerido
                        </div>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Cuenta Gasto</label>
                        <select name="id_cuenta_concepto_gasto_gasto" id="id_cuenta_concepto_gasto_gasto" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Cuenta Iva</label>
                        <select name="id_cuenta_concepto_gasto_iva" id="id_cuenta_concepto_gasto_iva" class="form-control form-control-sm">
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Cuenta Retención</label>
                        <select name="id_cuenta_concepto_gasto_retencion" id="id_cuenta_concepto_gasto_retencion" class="form-control form-control-sm">
                        </select>
                    </div>                    

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button id="saveConceptoGasto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="updateConceptoGasto"type="button" class="btn bg-gradient-success btn-sm">Guardar</button>
                <button id="saveConceptoGastoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>