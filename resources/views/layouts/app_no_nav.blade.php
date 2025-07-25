<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo_contabilidad.png">
    <title>
        Portafolio ERP
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
            background-image: url(/img/fondo-erp.png);
            height: 100% !important;
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
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
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @-moz-keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @-ms-keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @-o-keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        @keyframes color_change {
            from { color: cornflowerblue; }
            to { color: aqua; }
        }
        .dtfh-floatingparent {
            top: 0px !important;
            /* left: 29px !important; */
        }
        thead tr:first-child th {
            background-color: #1c4587;
            color: white;
            font-weight: bold;
            font-size: 14px;
            z-index: 12;
            top: -52;
        }

        .footer-navigation {
            margin: 0;
            position: fixed;
            margin-top: -15px;
            top: 100%;
            left: 50%;
            transform: translate(-50%, -50%);
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
            color: #FFF !important;
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

        tr.odd:hover {
            background-color: #1c45872b;
        }

        tr.even:hover {
            background-color: #1c45872b;
        }

        tr.odd:focus {
            background-color: #1c45872b;
        }

        tr.even:focus {
            background-color: #1c45872b;
        }

        td.dtfc-fixed-right {
            background-color: white;
            border-left: solid 1px #e9ecef !important;
        }

        th.dtfc-fixed-right {
            right: 0px !important;
            border-left: solid 1px #e9ecef !important;
        }

        .dark-version td.dtfc-fixed-right {
            background-color: #111c44 !important;
        }

        .btn {
            box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 2px 2px 2px rgb(0 0 0 / 57%);
        }

        .btn:hover {
            box-shadow: 0px 7px 14px rgba(50, 50, 93, 0.1), 4px 3px 6px rgb(0 0 0 / 80%) !important;
        }

        .btn-close {
            color: black;
            place-self: baseline;
        }

        .navbar-vertical .navbar-nav .nav-item .nav-link[data-bs-toggle=collapse]:after {
            color: #fff;
        }

        /* Toast */
        .contenedor-toast {
            position: fixed;
            right: 20px;
            bottom: 40px;
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column-reverse;
            gap: 20px;
            z-index: 9999 !important;
        }

        .toast {
            background: #ccc;
            display: flex;
            justify-content: space-between;
            border-radius: 10px;
            overflow: hidden;
            animation-name: apertura;
            animation-duration: 200ms;
            animation-timing-function: ease-out;
            position: relative;
            width: 100% !important;
        }

        .toast.exito {
            background: var(--bs-success);
            color: white !important;
        }
        .toast.error {
            background: var(--bs-danger);
            color: white !important;
        }
        .toast.info {
            background: var(--bs-info);
            color: white !important;
        }
        .toast.warning {
            background: var(--bs-warning);
            color: white !important;
        }

        .toast .contenido {
            display: grid;
            grid-template-columns: 30px auto;
            align-items: center;
            gap: 15px;
            padding: 15px;
            z-index: 9;
        }

        .toast .icono {
            color: rgba(0, 0, 0, 0.4);
        }

        .toast .titulo {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .toast .descripcion {
            font-size: 14px;
        }

        .toast .texto {
            transform: translateY(10%);
        }

        .toast .btn-cerrar {
            background: rgba(0, 0, 0, 0.1);
            border: none;
            cursor: pointer;
            padding: 0px 5px;
            transition: 0.3s ease all;
        }

        .toast .btn-cerrar:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        .toast .btn-cerrar .icono {
            width: 20px;
            height: 20px;
            color: #fff;
        }

        @keyframes apertura {
            from {
                transform: translateY(100px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .toast.cerrando {
            animation-name: cierre;
            animation-duration: 200ms;
            animation-timing-function: ease-out;
            animation-fill-mode: forwards;
        }

        @keyframes cierre {
            from {
                transform: translateX(0);
            }
            to {
                transform: translateX(calc(100% + 40px));
            }
        }

        .toast.autoCierre::after {
            content: '';
            width: 100%;
            height: 4px;
            background: rgba(0, 0, 0, 0.5);
            position: absolute;
            bottom: 0;
            animation-name: autoCierre;
            animation-duration: 5s;
            animation-timing-function: ease-out;
            animation-fill-mode: forwards;
        }

        @keyframes autoCierre {
            from {
                width: 100%;
            }
            to {
                width: 0%;
            }
        }

        svg.tea {
            --secondary: #33406f;
            position: absolute;
            top: 40%;
            left: 50%;
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
        svg.tea #teabag {
            transform-origin: top center;
            transform: rotate(3deg);
            animation: swing 2s infinite;
        }
        svg.tea #steamL {
            stroke-dasharray: 13;
            stroke-dashoffset: 13;
            animation: steamLarge 2s infinite;
        }
        svg.tea #steamR {
            stroke-dasharray: 9;
            stroke-dashoffset: 9;
            animation: steamSmall 2s infinite;
        }
        @-moz-keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @-webkit-keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @-o-keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @keyframes swing {
        50% {
            transform: rotate(-3deg);
        }
        }
        @-moz-keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @-webkit-keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @-o-keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @keyframes steamLarge {
        0% {
            stroke-dashoffset: 13;
            opacity: 0.6;
        }
        100% {
            stroke-dashoffset: 39;
            opacity: 0;
        }
        }
        @-moz-keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }
        @-webkit-keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }
        @-o-keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }
        @keyframes steamSmall {
        10% {
            stroke-dashoffset: 9;
            opacity: 0.6;
        }
        80% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        100% {
            stroke-dashoffset: 27;
            opacity: 0;
        }
        }

        .water{
            width:100px;
            height: 100px;
            background-color: skyblue;
            border-radius: 50%;
            position: fixed;
            z-index: 99999;
            box-shadow: inset 0 0 30px 0 rgba(0,0,0,.5), 0 4px 10px 0 rgba(0,0,0,.5);
            overflow: hidden;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .water:before, .water:after{
            content:'';
            position: absolute;
            width:100px;
            height: 100px;
            top: -30px;
            background-color: #fff;
        }
        .water:before{
            border-radius: 45%;
            background:rgba(255,255,255,.7);
            animation:wave 5s linear infinite;
        }
        .water:after{
            border-radius: 35%;
            background:rgba(255,255,255,.3);
            animation:wave 5s linear infinite;
        }
        @keyframes wave{
            0%{
                transform: rotate(0);
            }
            100%{
                transform: rotate(360deg);
            }
        }

    </style>
</head>

<body class="{{ $class ?? '' }} ">

    @guest
        @yield('content')
    @endguest

    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <!-- <script src="assets/js/plugins/perfect-scrollbar.min.js"></script> -->
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    
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
            if(event.keyCode == 13) {
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
                if(res.success){
                    localStorage.setItem("auth_token", res.token_type+' '+res.access_token);
                    localStorage.setItem("empresa_nombre", res.empresa.razon_social);
                    localStorage.setItem("empresa_logo", res.empresa.logo);
                    localStorage.setItem("notificacion_code", res.notificacion_code);
                    localStorage.setItem("fondo_sistema", res.fondo_sistema);
                    var itemMenuActiveIn = localStorage.getItem("item_active_menu");
                    if (itemMenuActiveIn == 0 || itemMenuActiveIn == 1 || itemMenuActiveIn == 2 || itemMenuActiveIn == 3) {
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
                if(res.success){
                    localStorage.setItem("auth_token", res.token_type+' '+res.access_token);
                    localStorage.setItem("empresa_nombre", res.empresa.razon_social);
                    localStorage.setItem("empresa_logo", res.empresa.logo);
                    localStorage.setItem("notificacion_code", res.notificacion_code);
                    localStorage.setItem("fondo_sistema", res.fondo_sistema);
                    var itemMenuActiveIn = localStorage.getItem("item_active_menu");
                    if (itemMenuActiveIn == 0 || itemMenuActiveIn == 1 || itemMenuActiveIn == 2 || itemMenuActiveIn == 3) {
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
