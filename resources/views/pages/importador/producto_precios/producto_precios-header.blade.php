<div class="accordion" id="accordionRental">
    <div class="accordion-item">
        <h5 class="accordion-header" id="filtrosBalance">
            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                Para actualizar tus items sigue estos pasos:
                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
            </button>
        </h5>
        <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosBalance" data-bs-parent="#accordionRental" >
            <div class="accordion-body text-sm" style="padding: 0 !important;">
                <br>
                <div>
                    <p style="font-size: 14px !important; margin-bottom: 0px;" >
                        1. Descarga la plantilla para actualizar precios. Cada plantilla contiene máximo 500 registros &nbsp;
                        <span id="descargarPlantilla" href="javascript:void(0)" class="btn badge bg-gradient-success" style="min-width: 40px; margin-right: 3px; margin-bottom: 0px !important;">
                            <i class="fas fa-download" style="font-size: 17px;"></i>
                            <b style="vertical-align: text-top;">Descargar plantilla</b>
                        </span>
                    </p>
                    <p style="font-size: 14px !important; margin-bottom: 0px;" >2. Realiza los cambios en cada archivo y guárdalo en formato Excel (.xlsx)</p>
                    <p style="font-size: 14px !important; margin-bottom: 0px;" >3. Adjunta el archivo y haz clic en "Actualizar precios"</p>
                </div>
                <br>
                <div class="row">
                    <form id="form-producto-precios" action="{{ route('producto.importar') }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
                        <div class="mb-3 col-6">
                            <input class="form-control form-control-sm" id="file" name="file" type="file">
                        </div>
                        <div class="col-6">

                            <button type="submit" id="cargarPlantilla" href="javascript:void(0)" class="btn badge bg-gradient-primary">
                                <i class="fas fa-upload" style="font-size: 17px;"></i>
                                <b style="vertical-align: text-top;">Actualizar precios</b>
                            </button>
                            <button id="cargarPlantillaLoagind" class="btn badge btn-sm bg-gradient-primary" style="display:none; float: left;" disabled>
                                Cargando
                                <i class="fas fa-spinner fa-spin"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>