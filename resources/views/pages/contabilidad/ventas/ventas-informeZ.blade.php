<div class="modal fade" id="ventasInformeZModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >Informe Z</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <div class="container row">

                    <div class="form-group form-group col-12 col-sm-6 col-md-6">
                        <label for="exampleFormControlSelect1">Formato impreción<span style="color: red">*</span></label>
                        <select class="form-control form-control-sm" id="tipo_informe_z">
                            <option value="0">POS</option>
                            <option value="1">NORMAL</option>
                        </select>
                    </div>

                    <div class="form-group col-12 col-sm-6 col-md-6 row" style="margin-bottom: 0.1rem !important;">
                        <label for="example-text-input" class="form-control-label">Detallar informe</label>
                        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                            <input class="form-check-input" type="radio" name="detallar_informe_z" id="detallar_informe_z1" style="font-size: 11px;" checked>
                            <label class="form-check-label" for="detallar_informe_z1" style="font-size: 11px;">
                                Si
                            </label>
                        </div>
                        <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                            <input class="form-check-input" type="radio" name="detallar_informe_z" id="detallar_informe_z2" style="font-size: 11px;">
                            <label class="form-check-label" for="detallar_informe_z2" style="font-size: 11px;">
                                No
                            </label>
                        </div>
                    </div>
                </div>

                <iframe
                    id="view-informe-z"
                    title="Inline Frame Example"
                    width="100%"
                    height="100%"
                    src="">
                </iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>