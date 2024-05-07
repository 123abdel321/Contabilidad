<style>

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent;">
            <form id="entornoForm" class="card-body row">
                

                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Variables de entorno</button>
                    </li>
                    <!-- <li class="nav-item" role="presentation">
                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Profile</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</button>
                    </li> -->
                </ul>
                <div class="tab-content" id="myTabContent" style="background-color: white;">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                        <div class="row" style="margin-top: 10px; padding-left: 10px;">

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="example-text-input" class="form-control-label">Valor UVT </label>
                                <input type="number" class="form-control form-control-sm" name="valor_uvt" id="valor_uvt">
                            </div>

                            <div class="form-group col-12 col-sm-6 col-md-4" >
                                <label for="example-text-input" class="form-control-label">Porcentaje Iva AIU</label>
                                <input type="number" class="form-control form-control-sm" name="porcentaje_iva_aiu" id="porcentaje_iva_aiu">
                            </div>

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="iva_incluido" id="iva_incluido" style="height: 20px;">
                                <label class="form-check-label" for="iva_incluido">
                                    Iva incluido
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="capturar_documento_descuadrado" id="capturar_documento_descuadrado" style="height: 20px;">
                                <label class="form-check-label" for="capturar_documento_descuadrado">
                                    Documentos descuadrados
                                </label>
                            </div>

                            <div class="form-check form-switch col-12 col-sm-6 col-md-4">
                                <input class="form-check-input" type="checkbox" name="vendedores_ventas" id="vendedores_ventas" style="height: 20px;">
                                <label class="form-check-label" for="vendedores_ventas">
                                    Vendedores ventas
                                </label>
                            </div>

                        </div>

                        <br/>

                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div>
                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>
                </div>
                <div style="background-color: white;">
                    <button type="button" class="btn btn-primary btn-sm" id="updateEntorno">Actualizar datos</button>
                    <button id="updateEntornoLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
                <!--  -->
            </form>
        </div>

    </div>
</div>

<script>
    var variablesEntorno = JSON.parse('<?php echo $variables_entorno; ?>');
</script>