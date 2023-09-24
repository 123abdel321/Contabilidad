<style>
    .side-nav-maximo-open{
        width: 100% !important;
    }
    .side-nav-maximo-close{
        width: 0px !important;
    }
    .ocultar {
        display: none;
    }
    #sidenav-main {
        background-color: #172b4d !important;
    }

    .text-blue {
        -webkit-animation: color_change 2s infinite alternate;
        -moz-animation: color_change 2s infinite alternate;
        -ms-animation: color_change 2s infinite alternate;
        -o-animation: color_change 2s infinite alternate;
        animation: color_change 2s infinite alternate;
    }

    @-webkit-keyframes color_change {
            from { color: skyblue; }
            to { color: darkcyan ; }
        }
        @-moz-keyframes color_change {
            from { color: skyblue; }
            to { color: darkcyan ; }
        }
        @-ms-keyframes color_change {
            from { color: skyblue; }
            to { color: darkcyan ; }
        }
        @-o-keyframes color_change {
            from { color: skyblue; }
            to { color: darkcyan ; }
        }
        @keyframes color_change {
            from { color: skyblue; }
            to { color: darkcyan ; }
        }

</style>
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 side-nav-maximo-close" id="sidenav-main" style="z-index: 99 !important; border-radius: 0px 10px 10px 0px;">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" style="color: white !important;" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('home') }}" target="_blank" style="text-align: -webkit-center;">
            <img src="./img/logo_contabilidad.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold" id="nombre-empresa" style="color: antiquewhite"></span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main" style="height: 100%;">
        <ul class="navbar-nav">

            <li class="nav-item">
                <div data-bs-toggle="collapse" href="#dashboardsExamples" class="nav-link collapsed" aria-controls="dashboardsExamples" role="button" aria-expanded="false" style="color: white;">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-table text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Tablas</span>
                </div>
                <div class="collapse" id="dashboardsExamples" >
                    <ul class="navbar-nav" style="margin-left: 15px; border-left: solid 1px #2dce89; margin-left: 30px;">
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_nit" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Cédulas Nit</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_comprobante" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Comprobantes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_plancuenta" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Plan de cuentas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_cecos" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Centro de costos</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <div data-bs-toggle="collapse" href="#collapseInvetario" class="nav-link collapsed" aria-controls="collapseInvetario" role="button" aria-expanded="false" style="color: white;">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-box-open text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Inventario</span>
                </div>
                <div class="collapse" id="collapseInvetario" >
                    <ul class="navbar-nav" style="margin-left: 15px; border-left: solid 1px #2dce89; margin-left: 30px;">
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_productos" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Productos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_familias" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Familias</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_bodegas" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Bodegas</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#collapseCapturas" class="nav-link collapsed" aria-controls="collapseCapturas" role="button" aria-expanded="false" style="color: white;">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-folder-open text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Capturas</span>
                </a>
                <div class="collapse" id="collapseCapturas" >
                    <ul class="navbar-nav" style="margin-left: 15px; border-left: solid 1px #2dce89; margin-left: 30px;">
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_compra" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Compras</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_documentogeneral" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Documento General</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#collapseInformes" class="nav-link collapsed" aria-controls="collapseInformes" role="button" aria-expanded="false" style="color: white;">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-chart-line text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Informes</span>
                </a>
                <div class="collapse" id="collapseInformes" >
                    <ul class="navbar-nav" style="margin-left: 15px; border-left: solid 1px #2dce89; margin-left: 30px;">
                    
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_auxiliar" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Auxiliar</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_balance" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Balance</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_cartera" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Cartera</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_documentos" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Documentos</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link button-side-nav" id="sidenav_compras" style="margin-left: 25px;">
                                <span class="nav-link-text ms-1">Compras</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <!-- <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'seleccionar-empresa' ? 'active' : '' }}" href="{{ route('seleccionar-empresa') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-building text-blue text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Empresas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'profile' ? 'active' : '' }}" href="{{ route('profile') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-blue text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profile</span>
                </a>
            </li> -->
        </ul>
    </div>
</aside>

<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl" id="sidenav-main-2" style="z-index: 99 !important; width: 11px; cursor: pointer; background-color: #1c4587 !important; border-radius: 0px 7px 7px 0px;">
    <span id="button-mostrar-lateral" class="nav-link-text ms-1" style="margin: 0; position: fixed; top: 50%; transform: translateY(-50%);">
        <i class="fas fa-caret-right" style="color: #FFF;"></i>
    </span>
    <span id="button-ocultar-lateral" class="nav-link-text ms-1 ocultar" style="margin: 0; position: fixed; top: 50%; transform: translateY(-50%);">
        <i class="fas fa-caret-left"  style="color: #FFF;"></i>
    </span>
</aside>