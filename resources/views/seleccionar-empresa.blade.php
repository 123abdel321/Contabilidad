@extends('layouts.app_no_nav', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav_no', ['title' => 'Seleccionar empresa'])

    <style>
        .seleccionar_empresa {
            cursor: pointer;
            max-width: 200px;
            text-align: center;
            border-radius: 10px;
            border: solid 2px;
            box-shadow: 0 0 2rem 0 rgb(136 152 170 / 15%);
        }
        .seleccionar_empresa:hover {
            box-shadow: 0 0 2rem 0 rgb(136 152 170 / 34%);
        }

        .seleccionar_empresa_none {
            max-width: 200px;
            text-align: center;
            border-radius: 10px;
            border: solid 2px;
            background-color: whitesmoke;
        }

        .crear_empresa {
            cursor: pointer;
            max-width: 200px;
            text-align: center;
            border-radius: 10px;
            border: solid 2px #05c705;
            box-shadow: 0 0 2rem 0 rgb(136 152 170 / 15%);
        }
        .crear_empresa:hover {
            box-shadow: 0 0 2rem 0 rgb(136 152 170 / 34%);
        }
        .error {
            color: red;
            font-size: 10px;
        }
    </style>
    
    <div class="container-fluid py-2">
        <div class="card mb-4" style="padding: 30px; align-items: center; min-height: 300px;">
            
            <div class="card-body" style="padding: 0 !important;">

                <div class="row justify-content-center gx-5">

                    <div class="col crear_empresa" id="crear_empresa">
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-12 ">
                                <i class="fas fa-plus-circle" style="font-size: 45px;"></i>
                            </div>
                            <div class="col-12 nombre_empresa">
                                <h6 class="text-center mb-0">Agregar nueva empresa</h6>
                            </div>
                        </div>
                    </div>
                    &nbsp;
                    @foreach ($empresas as $empresa)
                        
                        @if($empresa->estado == 0)
                            <div class="col seleccionar_empresa_none">
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-12 ">
                                        <img src="/img/logo-dark.dd999ba375b928c69aa3.png" class="rounded-circle img-fluid border border-2 border-white" style="width: 50px;">
                                    </div>
                                    <div class="col-12 nombre_empresa">
                                        <h6 class="text-center mb-0">{{ $empresa->nombre }}</h6>
                                    </div>
                                    <div class="col-12" style="margin-bottom: 10px;">
                                        <span class="text-xs">{{ $empresa->nit }}</span>
                                    </div>
                                    <div class="col-12" style="margin-bottom: 10px;">
                                        <span class="text-xs" style="color: tomato; font-weight: bold;">Inactivo temporalmente</span>
                                    </div>
                                </div>
                            </div>
                        @else
                        
                            <div class="col seleccionar_empresa" id="seleccionarempresa_{{$empresa->hash}}">
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col-12 ">
                                        <img src="/img/logo-dark.dd999ba375b928c69aa3.png" class="rounded-circle img-fluid border border-2 border-white" style="width: 50px;">
                                    </div>
                                    <div class="col-12 nombre_empresa">
                                        <h6 class="text-center mb-0">{{ $empresa->nombre }}</h6>
                                    </div>
                                    <div class="col-12" style="margin-bottom: 10px;">
                                        <span class="text-xs">{{ $empresa->nit }}</span>
                                    </div>
                                    <div class="col-12" style="margin-bottom: 10px;">
                                        <span class="text-xs" style="color: green; font-weight: bold;">Activo</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        &nbsp;
                        
                    @endforeach

                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="seleccionarEmpresaFormModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Agregar empresa</h6>
                    <span href="javascript:void(0)" class="badge btn-close" data-bs-dismiss="modal" aria-label="Close" style="color: black; font-size: 13px; cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <form id="seleccionarEmpresaForm" style="margin-top: 10px;">

                        <div class="row">
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Nit</label>
                                <input type="text" class="form-control form-control-sm" name="nit" id="nit">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Digito verificación</label>
                                <input type="number" class="form-control form-control-sm" name="dv" id="dv">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="exampleFormControlSelect1" style="display: unset;">Tipo contribuyente</label>
                                <select class="form-select form-select-sm" name="tipo_contribuyente" id="tipo_contribuyente">
                                    <option value="2">2 - Persona natural</option>
                                    <option value="1">1 - Persona jurídica</option>
                                </select>
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Razon social</label>
                                <input type="text" class="form-control form-control-sm" name="razon_social" id="razon_social">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Primer nombre</label>
                                <input type="text" class="form-control form-control-sm" name="primer_nombre" id="primer_nombre">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Otros nombre</label>
                                <input type="text" class="form-control form-control-sm" name="otros_nombres" id="otros_nombres">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Primer apellido</label>
                                <input type="text" class="form-control form-control-sm" name="primer_apellido" id="primer_apellido">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Segundo apellido</label>
                                <input type="text" class="form-control form-control-sm" name="segundo_apellido" id="segundo_apellido">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Direccion</label>
                                <input type="text" class="form-control form-control-sm" name="direccion" id="direccion">
                            </div>
                            <div class="form-group col-6 col-md-6 col-sm-6">
                                <label for="example-text-input" class="form-control-label" style="display: unset;">Telefono</label>
                                <input type="text" class="form-control form-control-sm" name="telefono" id="telefono">
                            </div>
                            
                        </div>   
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-danger btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button id="saveEmpresa"type="button" class="btn bg-gradient-info btn-sm">Guardar</button>
                    <button id="saveEmpresaLoading" class="btn bg-gradient-info btn-sm" style="display:none;" disabled>
                        Cargando
                        <i class="fas fa-spinner fa-spin"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
