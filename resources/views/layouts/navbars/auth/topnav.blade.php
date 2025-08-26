<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl
        {{ str_contains(Request::url(), 'virtual-reality') == true ? ' mt-3 mx-3 bg-primary' : '' }}" id="navbarBlur"
        data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <h4 class="font-weight-bolder text-white mb-0" id="titulo-view"></h4>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <!-- <p>EMPRESA NOMBRE</p> -->
            </div>
            <ul class="navbar-nav justify-content-end" style="flex-direction: inherit !important;">

                <li class="nav-item px-2 d-flex align-items-center">
                    <div id="nombre-empresa" style="color: aliceblue; text-transform: uppercase; font-size: 16px; font-weight: bold; -webkit-text-stroke-width: 0.3px; -webkit-text-stroke-color: #000000;"></div>
                </li>
                
                <li class="nav-item ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenavMaximo">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>
                <!-- <li class="nav-item px-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0">
                        <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                    </a>
                </li> -->
                <li class="nav-item px-2 d-flex align-items-center">
                    <div type="button" data-bs-toggle="dropdown" aria-expanded="false"
                        {{ !Auth::user()->avatar ? 'style=background-color:#0023ff;height:30px;width:30px;border-radius:50%;text-align:center;align-content:center;color:white;font-weight:600;cursor:pointer;' :
                            'style=background-image:url(https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.Auth::user()->avatar.');background-size:cover;height:30px;width:30px;border-radius:50%;text-align:center;align-content:center;color:white;font-weight:600;cursor:pointer;' }}
                        >
                        @if (!Auth::user()->avatar)
                            @if (Auth::user()->firstname && Auth::user()->lastname)
                                {{ mb_substr(Auth::user()->firstname, 0, 1) }}{{ mb_substr(Auth::user()->lastname, 0, 1) }}
                            @elseif (Auth::user()->firstname)
                                {{ mb_substr(Auth::user()->firstname, 0, 2) }}
                            @else
                                {{ mb_substr(Auth::user()->username, 0, 1) }}
                            @endif
                        @endif
                    </div>
                    
                    <div id="dropdown-perfil" class="dropdown-menu dropdown-menu-top-2">
                        <label id="nombre_usuario_loggin" style="font-weight: 500; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 1; line-clamp: 1; -webkit-box-orient: vertical; width: 160px;">
                            @if (Auth::user()->firstname)
                            &nbsp;&nbsp;&nbsp;&nbsp;{{ strtoupper(Auth::user()->firstname) }} {{ strtoupper(Auth::user()->lastname) }}
                            @else
                                {{ strtoupper(Auth::user()->username) }}
                            @endif
                        </label>
                        <div class="dropdown-divider" style="border-top: 1px solid #d8d8d8;"></div>
                        <!-- <a class="dropdown-item" href="javascript:void(0)" onclick="showProfile()"><i class="fas fa-user-circle"></i>&nbsp;&nbsp;Perfil</a> -->
                        
                        <a href="javascript:void(0)" class="dropdown-item" onclick="closeSessionProfile()"><i class="fas fa-sign-out-alt"></i>&nbsp;&nbsp;Cerrar sesión</a>
                    </div>
                    <!-- <div class="dropdown">
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                        </ul>
                    </div> -->
                    
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
