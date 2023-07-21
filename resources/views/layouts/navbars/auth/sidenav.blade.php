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
</style>
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 side-nav-maximo-close" id="sidenav-main" style="z-index: 99 !important; border-radius: 0px 10px 10px 0px;">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('home') }}" target="_blank" style="text-align: -webkit-center;">
            <img src="./img/logo192.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold" id="nombre-empresa"></span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main" style="height: 100%;">
        <ul class="navbar-nav">

            <li class="nav-item">
                <div data-bs-toggle="collapse" href="#dashboardsExamples" class="nav-link collapsed" aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Tablas</span>
                </div>
                <div class="collapse {{ Route::currentRouteName() == 'comprobante' || Route::currentRouteName() == 'plan-cuenta' || Route::currentRouteName() == 'nit' ? 'show' : '' }}" id="dashboardsExamples" >
                    <ul class="navbar-nav" style="margin-left: 15px;">
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'nit' ? 'active' : '' }}" href="{{ route('nit') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Nits</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'comprobante' ? 'active' : '' }}" href="{{ route('comprobante') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Comprobantes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'plan-cuenta' ? 'active' : '' }}" href="{{ route('plan-cuenta') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Plan de cuentas</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#collapseCapturas" class="nav-link collapsed" aria-controls="collapseCapturas" role="button" aria-expanded="false">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Capturas</span>
                </a>
                <div class="collapse {{ Route::currentRouteName() == 'documento-general' ? 'show' : '' }}" id="collapseCapturas" >
                    <ul class="navbar-nav" style="margin-left: 15px;">
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'documento-general' ? 'active' : '' }}" href="{{ route('documento-general') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    <i class="ni ni-calendar-grid-58 text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Documento General</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#collapseInformes" class="nav-link collapsed" aria-controls="collapseInformes" role="button" aria-expanded="false">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-ungroup text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Informes</span>
                </a>
                <div class="collapse {{ Route::currentRouteName() == 'auxiliar' || Route::currentRouteName() == 'balance' || Route::currentRouteName() == 'cartera' ? 'show' : '' }}" id="collapseInformes" >
                    <ul class="navbar-nav" style="margin-left: 15px;">
                    
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'auxiliar' ? 'active' : '' }}" href="{{ route('auxiliar') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    
                                    <i class="ni ni-collection text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Auxiliar</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'balance' ? 'active' : '' }}" href="{{ route('balance') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                   
                                    <i class="ni ni-collection text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Balance</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'cartera' ? 'active' : '' }}" href="{{ route('cartera') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    
                                    <i class="ni ni-collection text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Cartera</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Route::currentRouteName() == 'documentos' ? 'active' : '' }}" href="{{ route('documentos') }}">
                                <div
                                    class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                                    
                                    <i class="ni ni-collection text-dark text-sm opacity-10"></i>
                                </div>
                                <span class="nav-link-text ms-1">Documentos</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'seleccionar-empresa' ? 'active' : '' }}" href="{{ route('seleccionar-empresa') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-building text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Empresas</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'profile' ? 'active' : '' }}" href="{{ route('profile') }}">
                    <div
                        class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profile</span>
                </a>
            </li>
        </ul>
    </div>
</aside>

<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl" id="sidenav-main-2" style="z-index: 99 !important; width: 11px; cursor: pointer; background-color: darkcyan !important; border-radius: 0px 7px 7px 0px;">
    <span id="button-mostrar-lateral" class="nav-link-text ms-1" style="margin: 0; position: fixed; top: 50%; transform: translateY(-50%);">
        <i class="fas fa-caret-right" style="color: #FFF;"></i>
    </span>
    <span id="button-ocultar-lateral" class="nav-link-text ms-1 ocultar" style="margin: 0; position: fixed; top: 50%; transform: translateY(-50%);">
        <i class="fas fa-caret-left"  style="color: #FFF;"></i>
    </span>
</aside>