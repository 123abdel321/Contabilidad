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

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                <div class="accordion" id="accordionRental">
                    <div class="accordion-item">
                        <h5 class="accordion-header">
                            <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeleccionarEmpresa" aria-expanded="false" aria-controls="collapseSeleccionarEmpresa">
                                Empresas
                                <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                                <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                            </button>
                        </h5>
                        <div id="collapseSeleccionarEmpresa" class="accordion-collapse collapse show" data-bs-parent="#accordionRental">
                            <div class="accordion-body text-sm" style="padding: 0 !important;">

                                <div class="row row-cols-auto mt-3 mb-3" style="place-content: center;">

                                    @foreach ($empresas as $empresaItem)
                                        <div class="col" style="max-width: 200px; align-self: center;">
                                            <div class="card bg-white shadow border-radius-lg">
                                                <ul class="list-group list-group-flush">
                                                    <li class="list-group-item">
                                                        <div class="text-center" styl="padding: 10px 0px 0px 0px;">
                                                            <a href="javascript:;">
                                                                @if($empresaItem->logo)
                                                                    <img class="w-50 border-radius-md" src="https://bucketlistardatos.nyc3.digitaloceanspaces.com/{{ $empresaItem->logo }}">
                                                                @else
                                                                    <img class="w-50 border-radius-md" src="/img/logo_contabilidad.png">
                                                                @endif
                                                            </a>
                                                            <h6 class="mt-3 mb-1" style="font-size: 14px;">{{ $empresaItem->razon_social }}</h6>
                                                            <h6 class="mb-1" style="font-size: 13px;">{{ $empresaItem->nit }} - {{ $empresaItem->dv }}</h6>
                                                            @if ($empresaItem->estado == 1)
                                                                <h6 class="mb-1" style="font-size: 13px; color: green; font-weight: bold;">ACTIVO</h6>
                                                            @elseif ($empresaItem->estado == 2)
                                                                <h6 class="mb-1" style="font-size: 13px; color: brown; font-weight: bold;">PERIODO DE GRACIA</h6>
                                                            @elseif ($empresaItem->estado == 3)
                                                                <h6 class="mb-1" style="font-size: 13px; color: red; font-weight: bold;">SIN PAGO</h6>
                                                            @elseif ($empresaItem->estado == 4)
                                                                <h6 class="mb-1" style="font-size: 13px; color: #909090; font-weight: bold;">RETIRADO</h6>
                                                            @elseif ($empresaItem->estado == 5)
                                                                <h6 class="mb-1" style="font-size: 13px; color: blue; font-weight: bold;">INSTALANDO</h6>
                                                            @else
                                                                <h6 class="mb-1" style="font-size: 13px; color: #909090; font-weight: bold;">INACTIVO TEMPORALMENTE</h6>
                                                            @endif
                                                        </div>
                                                    </li>
                                                    @if (!$empresaItem->estado || $empresaItem->estado == 3 || $empresaItem->estado == 4 || $empresaItem->estado == 5)
                                                        <li class="list-group-item" style="width: 100%; border-radius: 0px 0px 10px 10px;">
                                                            <div style="text-align-last: center; border-radius: 30px;">
                                                                SELECCIONAR
                                                            </div>
                                                        </li>
                                                    @else
                                                        @if ($empresa->hash == $empresaItem->hash)
                                                            <li class="list-group-item" style="width: 100%; background-color: #0fac3a; color: white; border-radius: 0px 0px 10px 10px;">
                                                                <div style="text-align-last: center; border-radius: 30px;">
                                                                    SELECCIONADA
                                                                </div>
                                                            </li>
                                                        @else
                                                            <li class="list-group-item" style="width: 100%; background-color: #1c4587; color: white; cursor:pointer; border-radius: 0px 0px 10px 10px;" onclick="seleccionarEmpresa('{{ $empresaItem->hash }}')">
                                                                <div style="text-align-last: center; border-radius: 30px;">
                                                                    SELECCIONAR
                                                                </div>
                                                            </li>
                                                        @endif
                                                        
                                                    @endif
                                                    
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach

                                    </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <form id="empresaForm" class="card-body row">
                
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Razon social</label>
                    <input type="text" class="form-control form-control-sm" name=" razon_social_empresa" id="razon_social_empresa" value="{{ $empresaItem->razon_social }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Nit</label>
                    <input type="text" class="form-control form-control-sm" name=" nit_empresa" id="nit_empresa" value="{{ $empresaItem->nit }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Digito de verificación</label>
                    <input type="text" class="form-control form-control-sm" name=" dv_empresa" id="dv_empresa" value="{{ $empresaItem->dv }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Dirección</label>
                    <input type="text" class="form-control form-control-sm" name=" direccion_empresa" id="direccion_empresa" value="{{ $empresaItem->direccion }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormid_tipo_documento">Telefono</label>
                    <input type="text" class="form-control form-control-sm" name=" telefono_empresa" id="telefono_empresa" value="{{ $empresaItem->telefono }}" required {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <div class="invalid-feedback">
                        El campo es requerido
                    </div>
                </div>

                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="exampleFormControlSelect1" style="display: unset;">Tipo contribuyente</label>
                    <select class="form-select form-select-sm" name="tipo_contribuyente_empresa" id="tipo_contribuyente_empresa" {{ auth()->user()->can('empresa update') ? '' : 'disabled' }}>
                        <option value="1" {{ $empresaItem->tipo_contribuyente == 1 ? 'selected' : '' }}>Persona jurídica</option>
                        <option value="2" {{ $empresaItem->tipo_contribuyente == 2 ? 'selected' : '' }}>Persona natural</option>
                    </select>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Primer nombre</label>
                    <input type="text" class="form-control form-control-sm" name=" primer_nombre_empresa" id="primer_nombre_empresa" value="{{ $empresaItem->primer_nombre }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Otros nombre</label>
                    <input type="text" class="form-control form-control-sm" name=" otros_nombres_empresa" id="otros_nombres_empresa" value="{{ $empresaItem->otros_nombres }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Primer apellido</label>
                    <input type="text" class="form-control form-control-sm" name="primer_apellido_empresa" id="primer_apellido_empresa" value="{{ $empresaItem->primer_apellido }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label" style="display: unset;">Segundo apellido</label>
                    <input type="text" class="form-control form-control-sm" name="segundo_apellido_empresa" id="segundo_apellido_empresa" value="{{ $empresaItem->segundo_apellido }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <!-- <div class="form-check form-switch col-12 col-sm-4 col-md-3">
                    <input class="form-check-input" type="checkbox" name="capturar_documento_descuadrado_empresa" id="capturar_documento_descuadrado_empresa" style="height: 20px;" {{ $capturarDocumentosDescuadrados->valor ? 'checked' : '' }} {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                    <label class="form-check-label" for="capturar_documento_descuadrado_empresa">Capturar documentos descuadrados</label>
                </div> -->
                <div class="form-group col-12 col-sm-4 col-md-3">
                    <label for="example-text-input" class="form-control-label">Fecha ultimo cierre</label>
                    <input name="fecha_ultimo_cierre" id="fecha_ultimo_cierre" class="form-control form-control-sm" type="date" value="{{ $empresaItem->fecha_ultimo_cierre }}" {{ auth()->user()->can('empresa update') ? '' : 'readonly' }}>
                </div>
                <div class="form-group col-12 col-sm-8 col-md-6">
                    <label for="exampleFormControlSelect1">Responsabilidades tributarias</label>
                    <select class="form-control form-control-sm" id="id_responsabilidades" name="id_responsabilidades[]" multiple="multiple" requiere {{ auth()->user()->can('empresa update') ? '' : 'disabled' }}>
                        @foreach ($responsabilidades as $responsabilidad)
                            <option value="{{ $responsabilidad->id }}">{{ $responsabilidad->codigo.' - '.$responsabilidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                @can('empresa update')
                    <button type="button" class="btn btn-primary btn-sm" id="updateEmpresa">Actualizar datos</button>
                    <button id="updateEmpresaLoading" class="btn btn-success btn-sm ms-auto" style="display:none; float: left;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                @endcan

            </form>
        </div>

    </div>
</div>

<script>
    var datosEmpresa = JSON.parse('<?php echo $empresaItem; ?>');
    var editarEmpresa = '<?php echo auth()->user()->can('empresa update'); ?>';
    var capturarDocumentosDescuadrados = JSON.parse('<?php echo $capturarDocumentosDescuadrados; ?>');
</script>