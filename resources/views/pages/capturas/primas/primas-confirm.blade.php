<div class="modal fade" id="primasConfirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-md-down modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" >Agregar primas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                </button>
            </div>
            <div class="modal-body">
                <form id="primasConfirm" style="margin-top: 10px;">
                    <div class="row">

                        <h6 class="section-title bg-light p-2 mb-3">1. Seleccione el periodo donde desea grabar la novedad de intereses:</h6>

                        <div class="form-group col-12">
                            <label for="fecha_novedad_primas" class="form-control-label">Fecha novedad</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_novedad_primas" id="fecha_novedad_primas" required disabled>
                        </div>

                        <div class="form-group col-12 row" style="margin-bottom: 0.1rem !important;">
                            <label for="tipo_guardado_fecha" class="form-control-label"></label>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="tipo_guardado_fecha" id="tipo_guardado_fecha1" style="font-size: 11px;" checked>
                                <label class="form-check-label" for="tipo_guardado_fecha1" style="font-size: 11px;">
                                    Grabar en periodo
                                </label>
                            </div>
                            <div class="form-check col-12 col-md-12 col-sm-12" style="min-height: 0px; margin-bottom: 0px; margin-top: -2px; margin-left: 5px;">
                                <input class="form-check-input" type="radio" name="tipo_guardado_fecha" id="tipo_guardado_fecha2" style="font-size: 11px;">
                                <label class="form-check-label" for="tipo_guardado_fecha2" style="font-size: 11px;">
                                    Grabar en fecha específica
                                </label>
                            </div>
                        </div>

                    </div>  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                
                <button id="savePrimas" type="button" class="btn bg-gradient-success btn-sm" >Guardar</button>
                <button id="savePrimasLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>
            </div>
        </div>
    </div>
</div>