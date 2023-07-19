@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Cartera'])

    <style>
        .error {
            color: red;
        }
        .column-number {
            text-align: -webkit-right;
        }
    </style>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="card mb-4">
                <div class="card-body" style="padding: 0 !important;">

                    @include('pages.contabilidad.cartera.cartera-filter')

                </div>
            </div>
            <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body">
                    @include('pages.contabilidad.cartera.cartera-table')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
        
        $('#fecha').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
        $('#fecha').val(fechaDesde);

        var $validator = $('#carteraInformeForm').validate({
            rules: {
                id_tipo_cuenta: {
                    required: false,
                },
                fecha_desde: {
                    required: true,
                    minlength: 5,
                    maxlength: 20,
                },
                fecha_hasta: {
                    required: true,
                    minlength: 3,
                    maxlength: 20,
                }
            },
            messages: {
                id_tipo_cuenta: {
                    required: "El campo tipo cuenta es requerido"
                },
                fecha_desde: {
                    required: "El campo Fecha desde es requerido"
                },
                fecha_hasta: {
                    required: "El campo Fecha hasta es requerido"
                }
            },

            highlight: function(element) {
                $(element).closest('.form-control').removeClass('is-valid').addClass('is-invalid');
            },
            success: function(element) {
                $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
            }
        });

        var cartera_table = $('#CarteraInformeTable').DataTable({
            dom: '',
            autoWidth: true,
            responsive: true,
            processing: true,
            serverSide: true,
            deferLoading: 0,
            initialLoad: false,
            headers: headers,
            language: lenguajeDatatable,
            'rowCallback': function(row, data, index){
                if(data.detalle == 'total'){
                    $('td', row).css('background-color', 'antiquewhite');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
            },
            ajax:  {
                type: "GET",
                url: base_url + 'extracto',
                headers: headers,
                data: function ( d ) {
                    d.id_tipo_cuenta = $('#id_tipo_cuenta').val();
                    d.id_nit = $('#id_nit').val();
                    d.fecha = $('#fecha').val();
                    d.detallar_cartera = $('#detallar_cartera').val();
                }
            },
            "columns": [
                {"data": function (row, type, set){
                    if(row.detalle == 'si' || row.detalle == 'total') {
                        return row.cuenta + ' - ' +row.nombre_cuenta;
                    }
                    return '';      
                }},
                {"data": function (row, type, set){
                    if(!row.numero_documento){
                        return '';
                    }
                    if(row.razon_social){
                        return row.numero_documento +' - '+ row.razon_social;
                    }
                    return row.numero_documento + ' - ' +row.nombre_nit;
                }, responsivePriority: 1, targets: 0},
                {"data": function (row, type, set){
                    if(!row.codigo_comprobante){
                        return '';
                    }
                    return row.codigo_comprobante + ' - ' +row.nombre_comprobante;
                }},
                {data: 'documento_referencia'},
                
                {data: 'total_facturas', render: $.fn.dataTable.render.number('.', ',', 0, ''), className: 'dt-body-right', responsivePriority: 4, targets: -3},
                {data: 'total_abono', render: $.fn.dataTable.render.number('.', ',', 0, ''), className: 'dt-body-right', responsivePriority: 3, targets: -2},
                {data: 'saldo', render: $.fn.dataTable.render.number('.', ',', 0, ''), className: 'dt-body-right', responsivePriority: 2, targets: -1},
                {data: 'fecha_manual'},
                {data: 'dias_cumplidos'},
                {"data": function (row, type, set){
                    if(row.detalle == 'si' || row.detalle == 'total'){
                        console.log(row);
                        var datos = '<b style="color: #374b69;">Telefono: </b> '+row.telefono_1+'<br/>';
                        datos+= '<b style="color: #374b69;">Dirección: </b> '+row.direccion+'<br/>';
                        datos+= '<b style="color: #374b69;">Correo: </b>'+row.email+'<br/>';
                        return datos;
                    }
                    return '';
                }},
                {data: 'concepto'},
            ]
        });

        var $comboCuenta = $('#id_cuenta').select2({
            theme: 'bootstrap-5',
            delay: 250,
            ajax: {
                url: 'api/plan-cuenta/combo-cuenta',
                dataType: 'json',
                headers: headers,
                processResults: function (data) {
                    return {
                        results: data.data
                    };
                }
            }
        });

        var $comboNit = $('#id_nit').select2({
            theme: 'bootstrap-5',
            delay: 250,
            ajax: {
                url: 'api/nit/combo-nit',
                dataType: 'json',
                headers: headers,
                processResults: function (data) {
                    return {
                        results: data.data
                    };
                }
            }
        });

        $(document).on('click', '#generarCartera', function () {
            $("#generarCartera").hide();
            $("#generarCarteraLoading").show();
            // $('#descargarExcelCartera').prop('disabled', true);
            // $("#descargarExcelCartera").hide();
            // $("#descargarExcelCarteraDisabled").show();
            var $valid = $('#carteraInformeForm').valid();
            $('.error').hide();
            if (!$valid) {
                $(".error").show();
                $("#generarCartera").show();
                $("#generarCarteraLoading").hide();
                $validator.focusInvalid();
                return false;
            }else{
                $('.error').hide();
                cartera_table.ajax.reload(function() {
                    $("#generarCartera").show();
                    $("#generarCarteraLoading").hide();
                    // $('#descargarExcelCartera').prop('disabled', false);
                    // $("#descargarExcelCartera").show();
                    // $("#descargarExcelCarteraDisabled").hide();
                    $('.error').hide();
                },false);
            }
        });

    </script>
@endpush
