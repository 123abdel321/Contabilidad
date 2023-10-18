<style>
    .accordion-usuarios > .accordion-item:first-of-type .accordion-button {
        background-color: #1c4587 !important;
        color: white;
    }

    .accordion-usuarios > .accordion-item:first-of-type .accordion-button.collapsed {
        background-color: #FFF !important;
        color: black;
    }

    .accordion-usuarios > .accordion-item:last-of-type .accordion-button {
        background-color: #1c4587 !important;
        color: white;
    }

    .accordion-usuarios > .accordion-item:last-of-type .accordion-button.collapsed {
        background-color: #FFF !important;
        color: black;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card card-body mb-4" >
            <div class="row justify-content-center align-items-center">
                <div class="col-sm-auto col-4">
                    <div class="avatar avatar-xl position-relative">
                        <img src="https://bucketlistardatos.nyc3.digitaloceanspaces.com/{{ $empresa->logo }}" alt="bruce" class="w-100">
                    </div>
                </div>
                <div class="col-sm-auto col-8 my-auto">
                    <div class="h-100">
                        <h5 class="mb-1 font-weight-bolder">
                            {{ $empresa->razon_social }}
                        </h5>
                    </div>
                </div>
                <div class="col-sm-auto ms-sm-auto mt-sm-0 mt-3 d-flex">
                </div>
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <form id="empresaForm" class="card-body row">
                
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Razon social</label>
                    <input type="text" class="form-control form-control-sm" name=" razon_social_empresa" id="razon_social_empresa" value="{{ $empresa->razon_social }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Nit</label>
                    <input type="text" class="form-control form-control-sm" name=" nit_empresa" id="nit_empresa" value="{{ $empresa->nit }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Digito de verificación</label>
                    <input type="text" class="form-control form-control-sm" name=" dv_empresa" id="dv_empresa" value="{{ $empresa->dv }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Dirección</label>
                    <input type="text" class="form-control form-control-sm" name=" direccion_empresa" id="direccion_empresa" value="{{ $empresa->direccion }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Telefono</label>
                    <input type="text" class="form-control form-control-sm" name=" telefono_empresa" id="telefono_empresa" value="{{ $empresa->telefono }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormControlSelect1" style="display: unset;">Tipo contribuyente</label>
                    <select class="form-select form-select-sm" name="tipo_contribuyente_empresa" id="tipo_contribuyente_empresa" {{ auth()->user()->can('empresa update') ? '' : 'disabled' }}>
                        <option value="1" {{ $empresa->tipo_contribuyente == 1 ? 'selected' : '' }}>Persona jurídica</option>
                        <option value="2" {{ $empresa->tipo_contribuyente == 2 ? 'selected' : '' }}>Persona natural</option>
                    </select>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Razon social</label>
                    <input type="text" class="form-control form-control-sm" name=" razon_social_empresa" id="razon_social_empresa" value="{{ $empresa->razon_social }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Primer nombre</label>
                    <input type="text" class="form-control form-control-sm" name=" primer_nombre_empresa" id="primer_nombre_empresa" value="{{ $empresa->primer_nombre }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Otros nombre</label>
                    <input type="text" class="form-control form-control-sm" name=" otros_nombres_empresa" id="otros_nombres_empresa" value="{{ $empresa->otros_nombres }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Primer apellido</label>
                    <input type="text" class="form-control form-control-sm" name="primer_apellido_empresa" id="primer_apellido_empresa" value="{{ $empresa->primer_apellido }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Segundo apellido</label>
                    <input type="text" class="form-control form-control-sm" name="segundo_apellido_empresa" id="segundo_apellido_empresa" value="{{ $empresa->segundo_apellido }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                    <input class="form-check-input" type="checkbox" name="capturar_documento_descuadrado_empresa" id="capturar_documento_descuadrado_empresa" style="height: 20px;" {{ $capturarDocumentosDescuadrados->valor ? 'checked' : '' }} {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <label class="form-check-label" for="capturar_documento_descuadrado_empresa">Capturar documentos descuadrados</label>
                </div>
                <div class="form-group col-12 col-sm-8 col-md-6">
                    <label for="exampleFormControlSelect1">Responsabilidades tributarias</label>
                    <select class="form-control form-control-sm" id="id_responsabilidades" name="id_responsabilidades[]" multiple="multiple" requiere {{ auth()->user()->can('empresa update') ? '' : 'disabled' }}>
                        @foreach ($responsabilidades as $responsabilidad)
                            <option value="{{ $responsabilidad->id }}">{{ $responsabilidad->codigo.' - '.$responsabilidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" class="btn btn-primary btn-sm" id="updateEmpresa">Actualizar datos</button>
                <button id="updateEmpresaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                    Cargando
                    <i class="fas fa-spinner fa-spin"></i>
                </button>

            </form>
        </div>
    </div>
    
</div>

<script>
    var datosEmpresa = JSON.parse('<?php echo $empresa; ?>');
    var editarEmpresa = '<?php echo auth()->user()->can('empresa update'); ?>';
    var capturarDocumentosDescuadrados = JSON.parse('<?php echo $capturarDocumentosDescuadrados; ?>');
</script>