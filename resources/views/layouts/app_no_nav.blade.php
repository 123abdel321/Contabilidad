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
            <main class="main-content border-radius-lg">
                @yield('content')
            </main>
        @endif
    @endauth

    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
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
        const base_url = 'http://localhost:8000/api/';
        const base_web = 'http://localhost:8000/';
        const dateNow = new Date();
        const auth_token = localStorage.getItem("auth_token");
        const headers = {
            "Authorization": auth_token,
            "Content-Type": "application/json",
        };

        var $validator = $('#seleccionarEmpresaForm').validate({
            rules: {
                nit: {
                    required: true
                },
                dv: {
                    required: true
                },
                tipo_contribuyente: {
                    required: true
                },
                razon_social: {
                    required: true
                },
                primer_nombre: {
                    required: true
                },
                primer_apellido: {
                    required: true
                },
                segundo_apellido: {
                    required: true
                },
                direccion: {
                    required: true
                },
                telefono: {
                    required: true
                },
            },
            messages: {
                nit: {
                    required: "El campo Nit es requerido"
                },
                dv: {
                    required: "El campo Digo de verificación es requerido"
                },
                tipo_contribuyente: {
                    required: "El campo Tipo contribuyente es requerido"
                },
                razon_social: {
                    required: "El campo Razon social es requerido"
                },
                primer_nombre: {
                    required: "El campo Primer nombre es requerido"
                },
                primer_apellido: {
                    required: "El campo Primer apellido es requerido"
                },
                segundo_apellido: {
                    required: "El campo Segundo apellido es requerido"
                },
                direccion: {
                    required: "El campo Dirección es requerido"
                },
                telefono: {
                    required: "El campo Telefono es requerido"
                },
            },

            highlight: function(element) {
                $(element).closest('.form-control').removeClass('is-valid').addClass('is-invalid');
            },
            success: function(element) {
                $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
            }
        });

        $("#crear_empresa").click(function(event){
            validarCamposRequeridos();
            $('#seleccionarEmpresaFormModal').modal('show');
        });

        $("#tipo_contribuyente").on('change', function(){
            validarCamposRequeridos();
        });

        function validarCamposRequeridos()
        {
            $(".error").hide();
            var tipo_contribuyente = $('#tipo_contribuyente').val();
            if(tipo_contribuyente == 1){
                $("#razon_social").rules("add",{ required: true });
                $("#primer_nombre").rules("add",{ required: false });
                $("#primer_apellido").rules("add",{ required: false });
                $("#segundo_apellido").rules("add",{ required: false });
                $("#primer_nombre").removeClass("is-invalid");
                $("#primer_apellido").removeClass("is-invalid");
                $("#segundo_apellido").removeClass("is-invalid");
            } else {
                $("#razon_social").rules("add",{ required: false });
                $("#primer_nombre").rules("add",{ required: true });
                $("#primer_apellido").rules("add",{ required: true });
                $("#segundo_apellido").rules("add",{ required: true });
                $("#razon_social").removeClass("is-invalid");
            }
        }

        $("#saveEmpresa").click(function(event){
            var $valid = $('#seleccionarEmpresaForm').valid();
            if (!$valid) {
                $(".error").show();
                $validator.focusInvalid();
                return false;
            }
            $("#saveEmpresa").hide();
            $("#saveEmpresaLoading").show();
            $(".error").hide();
            let data = {
                'nit': $('#nit').val(),
                'dv': $('#dv').val(),
                'tipo_contribuyente': $('#tipo_contribuyente').val(),
                'razon_social': $('#razon_social').val(),
                'primer_nombre': $('#primer_nombre').val(),
                'otros_nombres': $('#otros_nombres').val(),
                'primer_apellido': $('#primer_apellido').val(),
                'segundo_apellido': $('#segundo_apellido').val(),
                'direccion': $('#direccion').val(),
                'telefono': $('#telefono').val()
            };
            $.ajax({
                url: base_url + 'empresa',
                method: 'POST',
                data: JSON.stringify(data),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                swalFire('Creación exitosa', res.message);
                $('#seleccionarEmpresaFormModal').modal('hide');
                setTimeout(function(){
                    window.location.reload();
                }, 2000);
            }).fail((err) => {
                $("#saveEmpresa").show();
                $("#saveEmpresaLoading").hide();
                var errorsMsg = "";
                var mensaje = err.responseJSON.message;
                if(typeof mensaje  === 'object' || Array.isArray(mensaje)){
                    for (field in mensaje) {
                        var errores = mensaje[field];
                        for (campo in errores) {
                            errorsMsg += "- "+errores[campo]+" <br>";
                        }
                    };
                } else {
                    errorsMsg = mensaje
                }
                swalFire('Creación herrada', errorsMsg, false);
            });
        });

        $(".seleccionar_empresa").click(function(event){
            var id = this.id.split('_')[1];
            if(id){
                $.ajax({
                    url: base_url + 'seleccionar-empresa',
                    method: 'POST',
                    data: JSON.stringify({empresa: id}),
                    headers: headers,
                    dataType: 'json',
                }).done((res) => {
                    swalFire('Selección exitosa', 'Empresa seleccionada con exito');
                    localStorage.setItem("empresa_nombre", res.empresa.nombre);
                    window.location.href = '/dashboard';
                }).fail((err) => {
                    $("#saveEmpresa").show();
                    $("#saveEmpresaLoading").hide();
                    var errorsMsg = "";
                    var mensaje = err.responseJSON.message;
                    if(typeof mensaje  === 'object' || Array.isArray(mensaje)){
                        for (field in mensaje) {
                            var errores = mensaje[field];
                            for (campo in errores) {
                                errorsMsg += "- "+errores[campo]+" <br>";
                            }
                        };
                    } else {
                        errorsMsg = mensaje
                    }
                    swalFire('Selección herrada', errorsMsg, false);
                });
            }
            
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
