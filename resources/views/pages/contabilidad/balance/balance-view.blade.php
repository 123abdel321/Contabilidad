@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Balance'])

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

                    <div class="accordion" id="accordionRental">
                        <div class="accordion-item">
                            <h5 class="accordion-header" id="filtrosBalance">
                                <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Filtros de balance
                                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                                </button>
                            </h5>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosBalance" data-bs-parent="#accordionRental" style="">
                                <div class="accordion-body text-sm" style="padding: 0 !important;">
                                
                                    <form id="balanceInformeForm" style="margin-top: 10px;">
                                        <div class="row">
                                            <div class="form-group col-md">
                                                <label for="example-text-input" class="form-control-label">Fecha desde</label>
                                                <input name="fecha_desde" id="fecha_desde" class="form-control form-control-sm" type="date" require>
                                            </div>
                                            <div class="form-group col-md">
                                                <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                                                <input name="fecha_hasta" id="fecha_hasta" class="form-control form-control-sm" type="date" require>
                                            </div>
                                            <div class="form-group col-md">
                                                <label for="exampleFormControlSelect1">Cuenta</label>
                                                <select name="id_cuenta" id="id_cuenta" class="form-control form-control-sm">
                                                </select>
                                            </div>
                                        </div> 
                                    </form>
                                    <div class="col-md normal-rem">
                                        <!-- BOTON GENERAR -->
                                        <span id="generarBalance" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                                            <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">BUSCAR</b>
                                        </span>
                                        <span id="generarBalanceLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                                            <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                                            <b style="vertical-align: text-top;">BUSCANDO</b>
                                        </span>
                                        <!-- <span id="descargarExcelBalance" class="btn badge bg-gradient-success" style="min-width: 40px; display:none;">
                                            <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">EXPORTAR</b>
                                        </span> -->
                                        <span id="descargarExcelBalanceDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad;">
                                            <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">EXPORTAR</b>
                                            <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                                        </span>
                                        <span id="descargarPdfBalanceDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad;" >
                                            <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">EXPORTAR</b>
                                            <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                                        </span>
                                    </div>
                                    <!-- <div class="col-md">
                                        <button class="btn btn-primary btn-sm ms-auto" id="generarBalance">Filtrar</button>
                                        <button id="generarBalanceLoading" class="btn btn-primary btn-sm ms-auto" style="display:none; float: left;" disabled>
                                            Cargando
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </button>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-body" style="content-visibility: auto; overflow: auto;">
                    @include('pages.contabilidad.balance.balance-table')
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

        var $comboPadre = $('#id_cuenta').select2({
            theme: 'bootstrap-5',
            delay: 250,
            ajax: {
                url: 'api/plan-cuenta/combo-cuenta',
                headers: headers,
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: data.data
                    };
                }
            }
        });
        
        $('#fecha_desde').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
        $('#fecha_hasta').val(fechaDesde);
        var $validator = $('#balanceInformeForm').validate({
            rules: {
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

        var balance_table = $('#balanceInformeTable').DataTable({
            dom: '',
            autoWidth: true,
            responsive: false,
            processing: true,
            serverSide: true,
            deferLoading: 0,
            initialLoad: false,
            language: lenguajeDatatable,
            'rowCallback': function(row, data, index){
                if(data.cuenta == "TOTALES"){
                    $('td', row).css('background-color', 'rgb(0 255 76 / 56%)');
                    $('td', row).css('font-weight', 'bold');
                }
                if(data.cuenta.length == 1){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.cuenta.length == 2){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 45%)');
                    return;
                }
                if(data.cuenta.length == 4){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 30%)');
                    return;
                }
                
            },
            ajax:  {
                type: "GET",
                url: base_url + 'balances',
                headers: headers,
                data: function ( d ) {
                    d.fecha_desde = $('#fecha_desde').val();
                    d.fecha_hasta = $('#fecha_hasta').val();
                    d.id_cuenta = $('#id_cuenta').val();
                }
            },
            "columns": [
                {"data": function (row, type, set){
                    if(row.cuenta){
                        return row.cuenta +' - '+ row.nombre_cuenta;
                    }
                    return '';
                }},
                {
                    data: 'saldo_anterior',
                    render: $.fn.dataTable.render.number(',', '.', 2, ''),
                    className: "column-number", className: 'dt-body-right'
                },
                {
                    data: 'debito',
                    render: $.fn.dataTable.render.number(',', '.', 2, ''),
                    className: "column-number", className: 'dt-body-right'
                },
                {
                    data: 'credito',
                    render: $.fn.dataTable.render.number(',', '.', 2, ''),
                    className: "column-number", className: 'dt-body-right'
                },
                {
                    data: 'saldo_final',
                    render: $.fn.dataTable.render.number(',', '.', 2, ''),
                    className: "column-number", className: 'dt-body-right'
                },
            ]
        });
        
        $(document).on('click', '#generarBalance', function () {
            $("#generarBalance").hide();
            $("#generarBalanceLoading").show();
            $('#descargarExcelBalance').prop('disabled', true);
            $('.error').hide();
            var $valid = $('#balanceInformeForm').valid();
            if (!$valid) {
                $(".error").show();
                $("#generarBalance").show();
                $("#generarBalanceLoading").hide();
                $validator.focusInvalid();
                return false;
            }else{
                balance_table.ajax.reload(function() {
                    $("#generarBalance").show();
                    $("#generarBalanceLoading").hide();
                    $('#descargarExcelBalance').prop('disabled', false);
                    $('.error').hide();
                },false);
            }
        });

        $(document).on('click', '#descargarExcelBalance', function () {
            var fecha_desde = $('#fecha_desde').val();
            var fecha_hasta = $('#fecha_hasta').val();
            var id_cuenta = $('#id_cuenta').val();
            window.open("/balance-excel?fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"$id_cuenta="+id_cuenta, "_blank");
        });

    </script>
@endpush
