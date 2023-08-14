<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo_contabilidad.png">
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
    <link href="assets/css/sistema/app.css" rel="stylesheet" />
    <!-- SELECT 2 -->
    <link href="assets/css/sistema/select2.min.css" rel="stylesheet" />
    <link href="assets/css/sistema/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
                <div class="min-height-100 bg-dark position-absolute w-100 fondo-sistema">
                    <span class="mask opacity-6 fondo-sistema-color"></span>
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
    <div class="modal fade loader" id="modal-nit-informacion" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
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
    <!-- NOTIFICAIONES TOAST -->
    <div class="contenedor-toast" id="contenedor-toast"></div>
    <!-- LOADING GLOBAL WATER + TE -->
    <div class="water" style="display: none">
        <svg class="tea" width="37" height="48" viewbox="0 0 37 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M27.0819 17H3.02508C1.91076 17 1.01376 17.9059 1.0485 19.0197C1.15761 22.5177 1.49703 29.7374 2.5 34C4.07125 40.6778 7.18553 44.8868 8.44856 46.3845C8.79051 46.79 9.29799 47 9.82843 47H20.0218C20.639 47 21.2193 46.7159 21.5659 46.2052C22.6765 44.5687 25.2312 40.4282 27.5 34C28.9757 29.8188 29.084 22.4043 29.0441 18.9156C29.0319 17.8436 28.1539 17 27.0819 17Z" stroke="var(--secondary)" stroke-width="2"></path>
            <path d="M29 23.5C29 23.5 34.5 20.5 35.5 25.4999C36.0986 28.4926 34.2033 31.5383 32 32.8713C29.4555 34.4108 28 34 28 34" stroke="var(--secondary)" stroke-width="2"></path>
            <path id="teabag" fill="var(--secondary)" fill-rule="evenodd" clip-rule="evenodd" d="M16 25V17H14V25H12C10.3431 25 9 26.3431 9 28V34C9 35.6569 10.3431 37 12 37H18C19.6569 37 21 35.6569 21 34V28C21 26.3431 19.6569 25 18 25H16ZM11 28C11 27.4477 11.4477 27 12 27H18C18.5523 27 19 27.4477 19 28V34C19 34.5523 18.5523 35 18 35H12C11.4477 35 11 34.5523 11 34V28Z"></path>
            <path id="steamL" d="M17 1C17 1 17 4.5 14 6.5C11 8.5 11 12 11 12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke="var(--secondary)"></path>
            <path id="steamR" d="M21 6C21 6 21 8.22727 19 9.5C17 10.7727 17 13 17 13" stroke="var(--secondary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
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
    <script src="assets/js/sistema/dataTables.fixedHeader.min.js"></script>
    <script src="assets/js/sistema/dataTables.fixedColumns.min.js"></script>
    
    <!-- SELECT 2  -->
    <script src="assets/js/sistema/select2.full.min.js"></script>
    <!-- VALIDATE -->
    <script src="assets/js/sistema/jquery.validate.min.js"></script>
    <!-- sweetalert2 -->
    <script src="assets/js/sistema/sweetalert2.all.min.js"></script>
    
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.2.6/jquery.inputmask.bundle.min.js"></script>
    <script>

        $(document).ready(function() {
            $('.water').show();
            $('#containner-dashboard').load('/dashboard', function() {
                $('.water').hide();
            });
            $("#titulo-view").text('Inicio');
        });

        $(document).ajaxError(function myErrorHandler(event, xhr, ajaxOptions, thrownError) {
            console.log('xhr: ',xhr);
            if(xhr.status == 401) {
                document.getElementById('logout-form').submit();
            }
        });
        // Pusher.logToConsole = true;
        const pusher = new Pusher('9ea234cc370d308638af', {cluster: 'us2'});
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
            document.getElementById('sidenav-main-2').click();
        });

        $(document).on('click', '.seleccionar-view', function () {
            var id = this.id.split('-')[1];
            if(id) {
                seleccionarView(id);
            }
        });      

        function generatView(id){
            $('.water').show();
            $('#contenerdores-views').append('<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-'+id+'"></main>');
            $('#footer-navigation').append(generateNewTabButton(id));
            $('#containner-'+id).load('/'+id, function() {
                $('.water').hide();
            });
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
                nombre = 'Captura documentos';
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
                    localStorage.setItem("empresa_nombre", res.empresa);
                    window.location.href = '/home';
                }
            }).fail((err) => {
                $("#button-login-loading").hide();
                $("#button-login").show();
            });
        });

        $(".btn-cerrar").click(function(event){
            console.log('asdasd');
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
            $('.water').show();
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
                $('.water').hide();
            }).fail((err) => {
                swalFire('Error al cargar usuario', '', false);
                $('.water').hide();
            });
        }

        function showNit (id_nit) {

            if(!id_nit) {
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
            $('.water').show();

            $.ajax({
                url: base_url + 'nit/informacion',
                method: 'GET',
                data: {id_nit: id_nit},
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
                $('.water').hide();
            }).fail((err) => {
                swalFire('Error al cargar nit', '', false);
                $('.water').hide();
            });
        }


        // const contenedorBotones = document.getElementById('contenedor-botones');
        const contenedorToast = document.getElementById('contenedor-toast');

        // Event listener para detectar click en los botones
        // contenedorBotones.addEventListener('click', (e) => {
        //     e.preventDefault();

        //     const tipo = e.target.dataset.tipo;

        //     if (tipo === 'exito') {
        //         agregarToast({ tipo: 'exito', titulo: 'Exito!', descripcion: 'La operación fue exitosa.', autoCierre: true });
        //     }
        //     if (tipo === 'error') {
        //         agregarToast({ tipo: 'error', titulo: 'Error', descripcion: 'Hubo un error', autoCierre: true });
        //     }
        //     if (tipo === 'info') {
        //         agregarToast({ tipo: 'info', titulo: 'Info', descripcion: 'Esta es una notificación de información.' });
        //     }
        //     if (tipo === 'warning') {
        //         agregarToast({ tipo: 'warning', titulo: 'Warning', descripcion: 'Ten cuidado' });
        //     }
        // });

        // Event listener para detectar click en los toasts
        contenedorToast.addEventListener('click', (e) => {
            const toastId = e.target.closest('div.toast').id;

            if (e.target.closest('button.btn-cerrar')) {
                cerrarToast(toastId);
            }
        });

        // Función para cerrar el toast
        function cerrarToast(id){
            document.getElementById(id)?.classList.add('cerrando');
        }

        // Función para agregar la clase de cerrando al toast.
        function agregarToast (tipo, titulo, descripcion, autoCierre = false) {
            // Crear el nuevo toast
            const nuevoToast = document.createElement('div');

            // Agregar clases correspondientes
            nuevoToast.classList.add('toast');
            console.log('tipo: ',tipo);
            nuevoToast.classList.add(tipo);
            if (autoCierre) nuevoToast.classList.add('autoCierre');

            // Agregar id del toast
            const numeroAlAzar = Math.floor(Math.random() * 100);
            const fecha = Date.now();
            const toastId = fecha + numeroAlAzar;
            nuevoToast.id = toastId;

            // Iconos
            const iconos = {
                exito: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm10.03 4.97a.75.75 0 0 1 .011 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.75.75 0 0 1 1.08-.022z"
                            />
                        </svg>`,
                error: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                            />
                        </svg>`,
                info: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"
                            />
                        </svg>`,
                warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                            />
                        </svg>`,
            };

            // Plantilla del toast
            var toast = `
                <div class="contenido">
                    <div class="icono">
                        ${iconos[tipo]}
                    </div>
                    <div class="texto">
                        <p class="titulo">${titulo}</p>
                        <p class="descripcion">${descripcion}</p>
                    </div>
                </div>
                <button class="btn-cerrar"  onclick="cerrarToast('${toastId}')" href="javascript:void(0)">
                    <div class="icono">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                            />
                        </svg>
                    </div>
                </button>
            `;

            // Agregar la plantilla al nuevo toast
            nuevoToast.innerHTML = toast;

            // Agregamos el nuevo toast al contenedor
            contenedorToast.appendChild(nuevoToast);

            // Función para menajera el cierre del toast
            const handleAnimacionCierre = (e) => {
                if (e.animationName === 'cierre') {
                    nuevoToast.removeEventListener('animationend', handleAnimacionCierre);
                    nuevoToast.remove();
                }
            };

            if (autoCierre) {
                setTimeout(() => cerrarToast(toastId), 3500);
            }

            // Agregamos event listener para detectar cuando termine la animación
            nuevoToast.addEventListener('animationend', handleAnimacionCierre);
        };

        // var channel = pusher.subscribe('my-channel');
        // channel.bind('notificaciones', function(data) {
        //     agregarToast(data.message.tipo, data.message.titulo, data.message.mensaje, data.message.autoclose);
        // });

    </script>
    @stack('js')
</body>

</html>
