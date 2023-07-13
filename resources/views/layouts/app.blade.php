<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo192.png">
    <title>
        Maximo Contable
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <script src="assets/js/sistema/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="assets/css/argon-dashboard.css" rel="stylesheet" />
    <!-- DATATABLE -->
    <link href="assets/css/sistema/dataTables.bootstrap5.min.css" rel="stylesheet" />
    <link href="assets/css/sistema/responsive.bootstrap5.min.css" rel="stylesheet" />
    <!-- SELECT 2 -->
    <link href="assets/css/sistema/select2.min.css" rel="stylesheet" />
    <link href="assets/css/sistema/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        .select2-selection{
            font-size: 13px !important;
        }
        .select2-selection--single{
            font-size: 13px !important;
        }
        .select2-results__option{
            font-size: 13px !important;
        }
        .accordion-button {
            padding: 7px !important;
        }
        .nav-link {
            font-size: 13px !important;
            padding: 5px !important;
        }
        .card .card-body{
            padding: 0.7rem;
        }
        table.dataTable td {
            color: black;
        }

        .search-table {
            max-width: 300px;
            float: right;
        }
        
    </style>
</head>

<body class="{{ $class ?? '' }} ">

    @guest
        @yield('content')
    @endguest

    @auth
        @if (in_array(request()->route()->getName(), ['sign-in-static', 'sign-up-static', 'login', 'register', 'recover-password', 'rtl', 'virtual-reality']))
            @yield('content')
        @else
            @if (!in_array(request()->route()->getName(), ['profile', 'profile-static']))
                <div class="min-height-300 bg-dark position-absolute w-100"></div>
            @elseif (in_array(request()->route()->getName(), ['profile-static', 'profile']))
                <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif
            @include('layouts.navbars.auth.sidenav')
            <main class="main-content border-radius-lg" style="margin-left: 5px;">
                @yield('content')
            </main>
            @include('components.fixed-plugin')
        @endif
    @endauth

    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
       
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="assets/js/argon-dashboard.js"></script>
    <!-- JQUERY --> 
    <script src="assets/js/sistema/jquery-3.5.1.js"></script>
    <!-- DATATABLE -->
    <script src="assets/js/sistema/jquery.dataTables.min.js"></script>
    <script src="assets/js/sistema/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/sistema/dataTables.responsive.min.js"></script>
    <script src="assets/js/sistema/responsive.bootstrap5.min.js"></script>
    <!-- SELECT 2  -->
    <script src="assets/js/sistema/select2.full.min.js"></script>
    <!-- VALIDATE -->
    <script src="assets/js/sistema/jquery.validate.min.js"></script>
    <!-- sweetalert2 -->
    <script src="assets/js/sistema/sweetalert2.all.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.2.6/jquery.inputmask.bundle.min.js"></script>
    <script>
        const base_url = 'http://localhost:8000/api/';
        const base_web = 'http://localhost:8000/';
        // const base_url = 'https://shark-app-stx3h.ondigitalocean.app/api/';
        // const base_web = 'https://shark-app-stx3h.ondigitalocean.app/';
        const dateNow = new Date();
        const auth_token = localStorage.getItem("auth_token");
        const iconNavbarSidenavMaximo = document.getElementById('iconNavbarSidenavMaximo');
        const headers = {
            "Authorization": auth_token,
            "Content-Type": "application/json",
        };
        let body = document.getElementsByTagName('body')[0];
        let className = 'g-sidenav-pinned';
        let sidenav = document.getElementById('sidenav-main');
        let sidenav2 = document.getElementById('sidenav-main-2');
        let buttonMostrarLateral = document.getElementById('button-mostrar-lateral');
        let buttonocultarLateral = document.getElementById('button-ocultar-lateral');
        let iconSidenav = document.getElementById('iconSidenav');

        $(".input_decimal").inputmask({
            alias: 'decimal',
            rightAlign: false,
            groupSeparator: '.',
            autoGroup: true
        });

        $('.form-control').keyup(function() {
            $(this).val($(this).val().toUpperCase());
        });

        $("#nombre-empresa").text(localStorage.getItem("empresa_nombre"));

        if (iconNavbarSidenavMaximo) {
            iconNavbarSidenavMaximo.addEventListener("click", toggleSidenavMaximo);
        }

        if (iconSidenav) {
            iconSidenav.addEventListener("click", toggleSidenavMaximoClose);
        }

        if (sidenav2) {
            sidenav2.addEventListener("click", toggleSidenavMaximo);
        }

        function toggleSidenavMaximo() {
            if (body.classList.contains(className)) {
                console.log('if');
                body.classList.remove(className);
                sidenav.classList.remove('bg-transparent');
                sidenav.classList.add('side-nav-maximo-close');
                sidenav.classList.remove('side-nav-maximo-open');
                buttonMostrarLateral.classList.remove('ocultar');
                buttonocultarLateral.classList.add('ocultar');
                setTimeout(function() {
                    sidenav.classList.remove('bg-white');
                }, 100);
            } else {
                body.classList.add(className);
                console.log('else');
                sidenav.classList.add('bg-white');
                sidenav.classList.remove('bg-transparent');
                iconSidenav.classList.remove('d-none');
                sidenav.classList.add('side-nav-maximo-open');
                sidenav.classList.remove('side-nav-maximo-close');
                buttonMostrarLateral.classList.add('ocultar');
                buttonocultarLateral.classList.remove('ocultar');
                
            }
        }

        function toggleSidenavMaximoOpen() {
            console.log('click');
            body.classList.add(className);
            sidenav.classList.add('bg-white');
            sidenav.classList.remove('bg-transparent');
            iconSidenav.classList.remove('d-none');
            sidenav.classList.add('side-nav-maximo-open');
            sidenav.classList.remove('side-nav-maximo-close');
        }

        function toggleSidenavMaximoClose() {
            body.classList.remove(className);
            setTimeout(function() {
            sidenav.classList.remove('bg-white');
            }, 100);
            sidenav.classList.remove('bg-transparent');
            sidenav.classList.add('side-nav-maximo-close');
            sidenav.classList.remove('side-nav-maximo-open');
        }

        //PERSONAL TABLE LENGUAJE
        const lenguajeDatatable = {
            "sProcessing":     "Cargando <span><i style='font-size: 15px' class='fa fa-spinner fa-spin'></i></span>",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún registro disponible",
            "sInfo":           "Registros del _START_ al _END_ de un total de _TOTAL_ ",
            "sInfoEmpty":      "Registros del 0 al 0 de un total de 0 ",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "<span><i style='font-size: 20px' class='fa fa-spinner fa-spin'></i></span>",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     ">",
                "sPrevious": "<"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }

        $("#button-login").click(function(event){
            $("#button-login-loading").show();
            $("#button-login").hide();
            $.ajax({
                url: base_web + 'login',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'POST',
                data: {
                    "email": $('#email_login').val(),
                    "password": $('#password_login').val(),
                },
                dataType: 'json',
            }).done((res) => {
                $("#button-login-loading").hide();
                $("#button-login").show();
                if(res.success){
                    localStorage.setItem("auth_token", res.token_type+' '+res.access_token);
                    window.location.href = '/seleccionar-empresa';
                }
            }).fail((err) => {
                $("#button-login-loading").hide();
                $("#button-login").show();
            });
        });

        function swalFire(titulo, mensaje, estado = true){
            var status = estado ? 'success' : 'error';
            Swal.fire(
                titulo,
                mensaje,
                status
            )
        }

        function getRowById(idData, tabla) {
            var data = tabla.rows().data();
            for (let index = 0; index < data.length; index++) {
                var element = data[index];
                if(element.id == idData){
                    return index;
                }
            }
            return false;
        }

        function getDataById(idData, tabla) {
            var data = tabla.rows().data();
            for (let index = 0; index < data.length; index++) {
                var element = data[index];
                if(element.id == idData){
                    return element;
                }
            }
            return false;
        }

    </script>
    @stack('js');
</body>

</html>
