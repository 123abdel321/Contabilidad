<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo_contabilidad.png">
    
    <title>Iniciar sesión · Portafolio ERP</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    
    <!-- Nucleo Icons -->
    <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
    
    <!-- Font Awesome -->
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
        /* ... MANTÉN todos tus estilos existentes de select2, datatable, toasts, etc. ... */
        
        /* Añade esto al final para resetear el body cuando se muestra el login */
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
    </style>
</head>

<body class="{{ $class ?? '' }}">

    @guest
        @yield('content')
    @endguest

    <!-- Core JS Files -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="assets/js/argon-dashboard.js"></script>
    
    <!-- JQUERY -->
    <script src="assets/js/sistema/jquery-3.5.1.js"></script>
    
    <!-- DATATABLE -->
    <script src="assets/js/sistema/jquery.dataTables.min.js"></script>
    <script src="assets/js/sistema/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/sistema/dataTables.responsive.min.js"></script>
    <script src="assets/js/sistema/responsive.bootstrap5.min.js"></script>
    
    <!-- SELECT 2 -->
    <script src="assets/js/sistema/select2.full.min.js"></script>
    
    <!-- VALIDATE -->
    <script src="assets/js/sistema/jquery.validate.min.js"></script>
    
    <!-- sweetalert2 -->
    <script src="assets/js/sistema/sweetalert2.all.min.js"></script>

    <script>
        const host = window.location.host;
        let base_url, base_web;

        if (host.includes("app.portafolioerp.com")) {
            base_url = "https://app.portafolioerp.com/api/";
            base_web = "https://app.portafolioerp.com/";
        } else if (host.includes("test.portafolioerp.com")) {
            base_url = "https://test.portafolioerp.com/api/";
            base_web = "https://test.portafolioerp.com/";
        } else if (host.includes("localhost:8000")) {
            base_url = 'http://localhost:8000/api/';
            base_web = 'http://localhost:8000/';
        }

        $("#button-login").click(function(event){
            sendDataLogin();
        });

        function changePassWord(event) {
            if (event.keyCode == 13) {
                sendDataLogin();
            }
        }

        function sendDataLogin() {
            localStorage.setItem("auth_token", "");
            localStorage.setItem("empresa_nombre", "");
            localStorage.setItem("empresa_logo", "");
            localStorage.setItem("notificacion_code", "");
            localStorage.setItem("fondo_sistema", "");

            $('#error-login').hide();
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

                if (res.success) {
                    localStorage.setItem("auth_token", res.token_type + ' ' + res.access_token);
                    localStorage.setItem("empresa_nombre", res.empresa.razon_social);
                    localStorage.setItem("empresa_logo", res.empresa.logo);
                    localStorage.setItem("notificacion_code", res.notificacion_code);
                    localStorage.setItem("fondo_sistema", res.fondo_sistema);

                    var itemMenuActiveIn = localStorage.getItem("item_active_menu");
                    if (itemMenuActiveIn == 0 || itemMenuActiveIn == 1 || itemMenuActiveIn == 2 || itemMenuActiveIn == 3) {
                        // mantener
                    } else {
                        localStorage.setItem("item_active_menu", 'contabilidad');
                    }

                    window.location.href = '/home';
                } else {
                    $('#error-login').show();
                }
            }).fail((err) => {
                $("#button-login-loading").hide();
                $("#button-login").show();
                $('#error-login').show();

                if (err.status == 419) {
                    window.location.href = '/home';
                }
            });
        }

        function loginDirecto() {
            var searchParams = new URLSearchParams(window.location.search);

            if (!searchParams.get('email') || !searchParams.get('code_login')) {
                return;
            }

            $.ajax({
                url: base_web + 'login-direct',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                method: 'POST',
                data: {
                    "email": searchParams.get('email'),
                    "code_login": searchParams.get('code_login'),
                },
                dataType: 'json',
            }).done((res) => {
                $("#button-login-loading").hide();
                $("#button-login").show();

                if (res.success) {
                    localStorage.setItem("auth_token", res.token_type + ' ' + res.access_token);
                    localStorage.setItem("empresa_nombre", res.empresa.razon_social);
                    localStorage.setItem("empresa_logo", res.empresa.logo);
                    localStorage.setItem("notificacion_code", res.notificacion_code);
                    localStorage.setItem("fondo_sistema", res.fondo_sistema);

                    var itemMenuActiveIn = localStorage.getItem("item_active_menu");
                    if (itemMenuActiveIn == 0 || itemMenuActiveIn == 1 || itemMenuActiveIn == 2 || itemMenuActiveIn == 3) {
                        // mantener
                    } else {
                        localStorage.setItem("item_active_menu", 'contabilidad');
                    }

                    window.location.href = '/home';
                } else {
                    $('#error-login').show();
                }
            }).fail((err) => {
                $("#button-login-loading").hide();
                $("#button-login").show();
                $('#error-login').show();
            });
        }

        loginDirecto();
    </script>

</body>
</html>