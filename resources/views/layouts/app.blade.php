<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo192.png">
    <title id="titulo-empresa">
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
            margin-bottom: 0.8rem !important;
            width: 100% !important;
            max-width: 400px;
            padding-right: 0px;
            float: right !important;
        }

        label, .form-label {
            margin-bottom: 2px !important;
        }

        .fondo-sistema {
            background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg');
            min-height: 350px !important;
            background-position: center center;
        }

        .btn {
            margin-bottom: 0.8rem !important;
        }

        .form-group {
            margin-bottom: 0.8rem !important;
        }

        .button-user {
            cursor: pointer;
        }

        .navbar-nav > .nav-item > .nav-link.active {
            background-image: linear-gradient(310deg, #344767 0%, #344767 100%) !important;
            color: white !important;
        }

        .navbar-nav > .nav-item > .nav-link.active > .icon > .text-dark {
            color: white !important;
        }

        .icon-user {
            font-size: 15px;
            padding: 5px;
            -webkit-animation: color_change 2s infinite alternate;
            -moz-animation: color_change 2s infinite alternate;
            -ms-animation: color_change 2s infinite alternate;
            -o-animation: color_change 2s infinite alternate;
            animation: color_change 2s infinite alternate;
        }

        .icon-user-none {
            font-size: 15px;
            padding: 5px;
        }

        .form-check-input:checked[type=radio] {
            background-image: linear-gradient(310deg, #344767 0%, #344767 100%);
        }

        .form-check:not(.form-switch) .form-check-input[type=radio]:checked {
            padding: 5px;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--highlighted {
            color: #fff;
            background-color: #596cff;
        }

        .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option.select2-results__option--disabled, .select2-container--bootstrap-5 .select2-dropdown .select2-results__options .select2-results__option[aria-disabled=true] {
            color: #6c757d;
            background-color: #e9ecef;
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

        .dtfh-floatingparent {
            top: 0px !important;
            left: 29px !important;
        }
        thead tr:first-child th {
            background-color: #6f7ff9;
            color: white;
            font-weight: bold;
            font-size: 14px;
            z-index: 12;
            top: -52;
        }

        .footer-navigation {
            position: fixed;
            left: 0;
            bottom: -1px;
            width: 100%;
            z-index: 999;
            text-align: center;
        }

        .footer-navigation .nav {
            justify-content: center;
        }

        .close_item_navigation {
            color: red;
            cursor: pointer;
        }

        .footer-navigation .nav-item .nav-link {
            margin-bottom: -1px;
            background: none;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            margin-left: 1px;
            color: white;
            background-color: #2a3548;
            cursor: pointer;
        }

        .footer-navigation .nav-item .nav-link.active {
            background-color: #FFF !important;
            color: black;
            cursor: context-menu;
        }

        .button-side-nav {
            cursor: pointer;
        }

        #navbar {
            display: flex;
            flex-direction: row;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        .navbar > .container, .navbar > .container-fluid, .navbar > .container-sm, .navbar > .container-md, .navbar > .container-lg, .navbar > .container-xl, .navbar > .container-xxl {
            display: flex;
            flex-wrap: none !important;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-collapse {
            flex-basis: 0%;
            flex-grow: 1;
            align-items: center;
        }

        .collapse {
            display: none;
        }

        .collapse.show {
            display: block !important;
        }

        .collapse .navbar-collapse {
            display: block !important;
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
                <div class="min-height-300 bg-dark position-absolute w-100 fondo-sistema">
                    <span class="mask bg-dark opacity-6"></span>
                </div>
            @elseif (in_array(request()->route()->getName(), ['profile-static', 'profile']))
                <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif
            @include('layouts.navbars.auth.sidenav')
            @include('layouts.navbars.auth.topnav')
            <div id="contenerdores-views" class="tab-content clearfix">
                <main class="tab-pane main-content border-radius-lg change-view active" style="margin-left: 5px;" id="containner-dashboard">
                    
                </main>
            </div>
            <br/>
            @include('components.fixed-plugin')
        @endif
    @endauth
    <!-- MODAL USUARIO ACCIÓN-->
    <div class="modal fade" id="modal-usuario-accion" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
        <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modal-title-usuario-accion">Creado por</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">x</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">  

                    <div class="form-group col-12">
                        <label for="example-text-input" class="form-control-label">Usuario</label>
                        <input id="usuario_accion" class="form-control form-control-sm" type="text" disabled>
                    </div>

                    <div class="form-group col-12">
                        <label for="example-text-input" class="form-control-label">Correo</label>
                        <input id="correo_accion" class="form-control form-control-sm" type="text" disabled>
                    </div>

                    <div class="form-group col-12">
                        <label for="example-text-input" class="form-control-label">Fecha acción</label>
                        <input id="fecha_accion" class="form-control form-control-sm" type="text" disabled>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger ml-auto" data-bs-dismiss="modal">Cerrar</button>
            </div>
            </div>
        </div>
    </div>
    <!-- MODAL NIT INFORMACIÓN-->
    <div class="modal fade" id="modal-nit-informacion" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
        <div class="modal-dialog modal- modal-dialog-centered modal-" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="modal-title-usuario-accion">Cedula Nit</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span >x</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">  
                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Nombre completo</label>
                            <input id="nombre_completo" class="form-control form-control-sm" type="text" disabled>
                        </div>
                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Documento</label>
                            <input id="numero_documento" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Direccion</label>
                            <input id="direccion" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Telefono</label>
                            <input id="telefono_1" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Correo</label>
                            <input id="email" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-6 col-sm-6">
                            <label for="example-text-input" class="form-control-label">Ciudad</label>
                            <input id="ciudad" class="form-control form-control-sm" type="text" disabled>
                        </div>

                        <div class="form-group col-12 col-md-12 col-sm-12">
                            <label for="example-text-input" class="form-control-label">Observaciones</label>
                            <input id="observaciones" class="form-control form-control-sm" type="text" disabled>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger ml-auto" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- FOOTER -->
    @include('layouts.footers.footer')

    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <!-- <script src="assets/js/plugins/perfect-scrollbar.min.js"></script> -->
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
    <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
    
    <!-- SELECT 2  -->
    <script src="assets/js/sistema/select2.full.min.js"></script>
    <!-- VALIDATE -->
    <script src="assets/js/sistema/jquery.validate.min.js"></script>
    <!-- sweetalert2 -->
    <script src="assets/js/sistema/sweetalert2.all.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.2.6/jquery.inputmask.bundle.min.js"></script>
    <script>

        $(document).ready(function() {
            $('#containner-dashboard').load('/dashboard');
            $("#titulo-view").text('Inicio');
        });
        
        // const base_url = 'http://localhost:8000/api/';
        // const base_web = 'http://localhost:8000/';
        const base_url = 'https://shark-app-stx3h.ondigitalocean.app/api/';
        const base_web = 'https://shark-app-stx3h.ondigitalocean.app/';
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

        $('.form-control-sm').keyup(function() {
            $(this).val($(this).val().toUpperCase());
        });

        $("#nombre-empresa").text(localStorage.getItem("empresa_nombre"));
        $("#titulo-empresa").text(localStorage.getItem("empresa_nombre"));

        if (iconNavbarSidenavMaximo) {
            iconNavbarSidenavMaximo.addEventListener("click", toggleSidenavMaximo);
        }

        if (iconSidenav) {
            iconSidenav.addEventListener("click", toggleSidenavMaximoClose);
        }

        if (sidenav2) {
            sidenav2.addEventListener("click", toggleSidenavMaximo);
        }

        $(document).on('click', '.button-side-nav', function () {
            var id = this.id.split('_')[1];
            if($('#containner-'+id).length == 0) {

                generatView(id);
            }
            seleccionarView(id);
        });

        $(document).on('click', '.seleccionar-view', function () {
            var id = this.id.split('-')[1];
            if(id) {
                seleccionarView(id);
            }
        });      

        function generatView(id){
            $('#contenerdores-views').append('<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-'+id+'"></main>');
            $('#footer-navigation').append(generateNewTabButton(id));
            $('#containner-'+id).load('/'+id);
        }

        function seleccionarView(id){

            var nombre = 'Inicio';

            $(".dtfh-floatingparent").remove();
            $('.change-view').removeClass("active");
            $('.seleccionar-view').removeClass("active");
            $('.button-side-nav').removeClass("active");
            $('#containner-'+id).addClass("active");
            $('#tab-'+id).addClass("active");
            $('#sidenav_'+id).addClass("active");

            if(id == 'nit') {
                nombre = 'Cedulas nit'
            } else if(id == 'comprobante') {
                nombre = 'Comprobantes';
            } else if(id == 'plancuenta') {
                nombre = 'Cuentas contables';
            } else if(id == 'auxiliar') {
                nombre = 'Auxiliar';
            } else if(id == 'documentogeneral') {
                nombre = 'Captua documentos';
            } else if(id == 'balance') {
                nombre = 'Balance';
            } else if(id == 'cartera') {
                nombre = 'Cartera';
            } else if(id == 'documentos') {
                nombre = 'Documentos';
            } else if(id == 'cecos') {
                nombre = 'Centro costos';
            }

            $("#titulo-view").text(nombre);
        }

        function generateNewTabView(id){
            var html = '<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-'+id+'"></main>';
        }

        function generateNewTabButton(id){
            var icon = '';
            var nombre = '';

            if(id == 'nit') {
                icon = 'fas fa-book';
                nombre = 'Cedulas nits';
            } else if (id == 'comprobante') {
                icon = 'fas fa-book';
                nombre = 'Comprobantes';
            } else if (id == 'plancuenta') {
                icon = 'fas fa-book';
                nombre = 'Cuentas contables';
            } else if (id == 'documentogeneral') {
                icon = 'fas fa-book';
                nombre = 'Captura documentos';
            } else if (id == 'auxiliar') {
                icon = 'fas fa-book';
                nombre = 'Auxiliar';
            } else if (id == 'balance') {
                icon = 'fas fa-book';
                nombre = 'Balance';
            } else if (id == 'cartera') {
                icon = 'fas fa-book';
                nombre = 'Cartera';
            } else if (id == 'documentos') {
                icon = 'fas fa-book';
                nombre = 'Documentos';
            } else if (id == 'cecos') {
                icon = 'fas fa-book';
                nombre = 'Centro de costos';
            }

            var html = '';
            html+=  '<li class="nav-item" id="lista_view_'+id+'">';
            html+=      '<div class="nav-link col seleccionar-view" id="tab-'+id+'">';
            html+=          '<i class="'+icon+'"></i>&nbsp;';
            html+=          nombre+'&nbsp;&nbsp;';
            html+=          '<i class="fas fa-times-circle close_item_navigation" id="closetab_'+id+'" onclick="closeView(this)"></i>&nbsp;';
            html+=      '</div>';
            html+=  '</li>';
            return html;
        }

        function closeView(nameView) {
            var id = nameView.id.split('_')[1];

            $("#lista_view_"+id).remove();
            $("#containner-"+id).remove();
            setTimeout(() => {
                seleccionarView('dashboard');
            }, 10)
        }

        function toggleSidenavMaximo() {
            if (body.classList.contains(className)) {

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
                    window.location.href = '/home';
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

        function showUser (id_usuario, fecha, creado) {

            if(!id_usuario) {
                return;
            }

            $('#usuario_accion').val('');
            $('#correo_accion').val('');
            $('#fecha_accion').val('');
            $("#modal-title-usuario-accion").html("Buscando usuario ...");

            $('#modal-usuario-accion').modal('show');

            $.ajax({
                url: base_url + 'usuario-accion',
                method: 'GET',
                data: {id: id_usuario},
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){

                    var data = res.data;
                    $('#usuario_accion').val(data.username);
                    $('#correo_accion').val(data.email);
                    $('#fecha_accion').val(fecha);

                    if (creado) {
                        $("#modal-title-usuario-accion").html("Creado por: "+ data.username);
                    } else {
                        $("#modal-title-usuario-accion").html("Actualizado por: "+ data.username);
                    }
                }
            }).fail((err) => {
                swalFire('Error al cargar usuario', '', false);
            });
        }

        function showNit (numero_documento) {

            if(!numero_documento) {
                return;
            }

            $('#numero_documento').val('');
            $('#nombre_completo').val('');
            $('#direccion').val('');
            $('#telefono_1').val('');
            $('#email').val('');
            $('#observaciones').val('');
            $('#ciudad').val('');

            $('#modal-nit-informacion').modal('show');

            $.ajax({
                url: base_url + 'nit/informacion',
                method: 'GET',
                data: {numero_documento: numero_documento},
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){

                    var data = res.data;
                    $('#numero_documento').val(data.numero_documento);
                    $('#nombre_completo').val(data.nombre_nit);
                    $('#direccion').val(data.direccion);
                    $('#telefono_1').val(data.telefono_1);
                    $('#email').val(data.email);
                    $('#observaciones').val(data.observaciones);
                    $('#ciudad').val(data.ciudad.nombre_completo);
                }
            }).fail((err) => {
                swalFire('Error al cargar nit', '', false);
            });
        }

    </script>
    @stack('js')
</body>

</html>
