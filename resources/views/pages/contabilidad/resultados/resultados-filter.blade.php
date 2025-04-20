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
                    <input name="id_resultado_cargado" id="id_resultado_cargado" class="form-control form-control-sm" type="text" style="display: none;">
                    <div class="row">

                        <div class="form-group col-12 col-sm-4 col-md-3">
                            <label for="exampleFormControlSelect1" style=" width: 100%;">Tipo informe</label>
                            <select name="tipo_informe_resultado" id="tipo_informe_resultado" class="form-control form-control-sm">
                                <option value="1">Igresos</option>
                                <option value="2">Gastos</option>
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label>Cuenta</label>
                            <select name="id_cuenta_resultado" id="id_cuenta_resultado" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>
                        
                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha desde</label>
                            <input name="fecha_desde_resultado" id="fecha_desde_resultado" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                            <input name="fecha_hasta_resultado" id="fecha_hasta_resultado" class="form-control form-control-sm" type="date" require>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label>Centro costos</label>
                            <select name="id_cecos_resultado" id="id_cecos_resultado" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                        <div class="form-group col-6 col-sm-4 col-md-3">
                            <label>Cedula/Nit</label>
                            <select name="id_nit_resultado" id="id_nit_resultado" class="form-control form-control-sm" style="width: 100%; font-size: 13px;">
                            </select>
                        </div>

                    </div> 
                </form>
                <div class="col-md normal-rem">
                    <!-- BOTON GENERAR -->
                    <span id="generarResultado" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="min-width: 40px; margin-right: 3px;">
                        <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                        <b style="vertical-align: text-top;">BUSCAR</b>
                    </span>
                    <span id="generarResultadoLoading" class="badge bg-gradient-primary" style="display:none; min-width: 40px; margin-right: 3px; margin-bottom: 16px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                        <b style="vertical-align: text-top;">GENERANDO</b>
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