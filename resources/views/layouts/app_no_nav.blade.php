<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon.png">
    <link rel="icon" type="image/png" href="/img/logo_contabilidad.png">
    
    <title>Portafolio ERP</title>
    
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

    <link id="pagestyle" href="assets/css/login.css" rel="stylesheet" />
    <link id="pagestyle" href="assets/css/register.css" rel="stylesheet" />
    
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
    </style>
</head>

<body class="{{ $class ?? '' }}">

    {{-- 🔑 El content SIEMPRE se renderiza, logueado o no --}}
    @yield('content')

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

    <!-- WOMPI -->
    <script src="https://checkout.wompi.co/widget.js"></script>

    <!-- SISTEMA -->
    <script src="assets/js/login.js"></script>
    
</body>
</html>