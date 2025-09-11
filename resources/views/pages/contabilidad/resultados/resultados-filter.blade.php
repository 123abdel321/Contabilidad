<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtrosResultado">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Filtros de resultado
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosResultado" data-bs-parent="#accordionRental" >
            <div class="accordion-body text-sm" style="padding: 0 !important;">
            
                <form id="resultadoprimaryrmeForm" style="margin-top: 10px;">
                    
                    <div class="row">

                        <input name="id_resultado_cargado" id="id_resultado_cargado" class="form-control form-control-sm" type="text" style="display: none;">

                        <div class="form-group col-12 col-sm-4 col-md-4">
                            <label for="example-text-input" class="form-control-label">Fecha</label>
                            <input name="fecha_manual_resultados" id="fecha_manual_resultados" class="form-control form-control-sm" require>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-4">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Tipo informe</label>
                            <select name="tipo_informe_resultado" id="tipo_informe_resultado" class="form-control form-control-sm">
                                <option value="1">Igresos</option>
                                <option value="2">Gastos</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-4">
                            <label>Cuenta</label>
                            <select name="id_cuenta_resultado" id="id_cuenta_resultado" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-4">
                            <label>Centro costos</label>
                            <select name="id_cecos_resultado" id="id_cecos_resultado" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-4">
                            <label>Cedula/Nit</label>
                            <select name="id_nit_resultado" id="id_nit_resultado" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                    </div> 
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarResultado" href="javascript:void(0)" class="btn badge bg-gradient-primary btn-bg-gold" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarResultadoLoading" class="badge bg-gradient-primary btn-bg-gold-loading" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
                    </span>
                    <!-- BOTON EXCEL -->
                    <span id="descargarExcelResultado" class="btn badge bg-gradient-success btn-bg-success btn-bg-excel" style="min-width: 40px; display:none;">
                        <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                    </span>
                    <span id="descargarExcelResultadoLoading" class="badge bg-gradient-success btn-bg-success btn-bg-excel-loading" style="min-width: 40px; display:none;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                    </span>
                    <span id="descargarExcelResultadoDisabled" class="badge bg-gradient-dark" style="min-width: 40px; color: #adadad; margin-right: 3px;">
                        <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                        <b style="vertical-align: text-top;">&nbsp;EXCEL</b>
                        <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                    </span>
                    <!-- BOTON ULTIMO INFORME -->
                    <span id="generarResultadoUltimo" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px; margin-right: 3px; float: right; display:none;">
                        <i class="fas fa-history" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">CARGAR ULTIMO INFORME</b>
                    </span>
                    <div id="generarResultadoUltimoLoading" class="spinner-border spinner-erp" style="display:none;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>