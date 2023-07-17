@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Auxiliar'])

    <style>
        .error {
            color: red;
        }
        .column-number {
            text-align: -webkit-right;
        }
        .content-export-btn {
            padding: 10px;
            margin-top: -20px;
        }
        .button-export-excel {
            width: 40px;
            background-color: #006d37;
            padding: 5px;
            height: 30px;
            text-align-last: center;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            float: right;
        }
        .bg-gradient-success{
            background-image: linear-gradient(310deg, #02974d 0%, #006d37 100%);
        }
    </style>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="card mb-4">
                <div class="card-body" style="padding: 0 !important;">

                    <div class="accordion" id="accordionRental">
                        <div class="accordion-item">
                            <h5 class="accordion-header" id="filtrosAuxiliar">
                                <button class="accordion-button border-bottom font-weight-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Filtros de auxiliar
                                    <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                                    <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                                </button>
                            </h5>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="filtrosAuxiliar" data-bs-parent="#accordionRental">
                                <div class="accordion-body text-sm" style="padding: 0 !important;">
                                
                                    <form id="auxiliarInformeForm" style="margin-top: 10px;">
                                        <div class="row">
                                        
                                            <div class="form-group col-6 col-md-3 col-sm-3">
                                                <label for="example-text-input" class="form-control-label">Fecha desde</label>
                                                <input name="fecha_desde" id="fecha_desde" class="form-control form-control-sm" type="date" require>
                                            </div>
                                            <div class="form-group col-6 col-md-3 col-sm-3">
                                                <label for="example-text-input" class="form-control-label">Fecha hasta</label>
                                                <input name="fecha_hasta" id="fecha_hasta" class="form-control form-control-sm" type="date" require>
                                            </div>
                                            <div class="form-group col-6 col-md-3 col-sm-3">
                                                <label for="exampleFormControlSelect1" style=" width: 100%;">Cuenta</label>
                                                <select name="id_cuenta" id="id_cuenta" class="form-control form-control-sm">
                                                </select>
                                            </div>
                                            <div class="form-group col-6 col-md-3 col-sm-3">
                                                <label for="exampleFormControlSelect1" style=" width: 100%;">Nit</label>
                                                <select class="form-control form-control-sm" name="id_nit" id="id_nit">
                                                </select>
                                            </div>
                                        </div>  
                                    </form>
                                    <div class="col-md normal-rem">
                                        <!-- BOTON GENERAR -->
                                        <span id="generarAuxiliar" href="javascript:void(0)" class="btn badge bg-gradient-info" style="min-width: 40px;">
                                            <i class="fas fa-search" style="font-size: 17px;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">BUSCAR</b>
                                        </span>
                                        <span id="generarAuxiliarLoading" class="badge bg-gradient-info" style="display:none; min-width: 40px; margin-bottom: 16px;">
                                            <i class="fas fa-spinner fa-spin" style="font-size: 17px;"></i>
                                            <b style="vertical-align: text-top;">BUSCANDO</b>
                                        </span>
                                        <span id="descargarExcelAuxiliar" class="btn badge bg-gradient-success" style="min-width: 40px; display:none;">
                                            <i class="fas fa-file-excel" style="font-size: 17px;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">EXPORTAR</b>
                                        </span>
                                        <span id="descargarExcelAuxiliarDisabled" class="badge bg-dark" style="min-width: 40px; color: #adadad;">
                                            <i class="fas fa-file-excel" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">EXPORTAR</b>
                                            <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                                        </span>
                                        <span id="" class="badge bg-dark" style="min-width: 40px; color: #adadad;" >
                                            <i class="fas fa-file-pdf" style="font-size: 17px; color: #adadad;"></i>&nbsp;
                                            <b style="vertical-align: text-top;">EXPORTAR</b>
                                            <i class="fas fa-lock" style="color: red; position: absolute; margin-top: -10px; margin-left: 4px;"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body">
                    @include('pages.contabilidad.auxiliar.auxiliar-table')
                </div>
            </div>
            <!-- <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body" style="max-height: 500px;">
                    <table id="auxiliarInformeTableTotal" class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Saldo anterior</th>
                                <th>Debito</th>
                                <th>Credito</th>
                                <th>Saldo final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>TOTALES</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> -->
        </div>
        
    </div>
@endsection

@push('js')
    <script>
        var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
        
        $('#fecha_desde').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
        $('#fecha_hasta').val(fechaDesde);

       

        var auxiliar_table = $('#auxiliarInformeTable').DataTable({
            dom: '',
            responsive: false,
            processing: true,
            serverSide: true,
            deferLoading: 0,
            initialLoad: false,
            language: lenguajeDatatable,
            ordering: false,
            'rowCallback': function(row, data, index){
                if(data.detalle_group == 'nits'){
                    $('td', row).css('background-color', 'antiquewhite');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.cuenta == "TOTALES"){
                    $('td', row).css('background-color', 'rgb(0 255 76 / 56%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.cuenta.length == 1){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.cuenta.length == 2){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 45%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.cuenta.length == 4){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 30%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.detalle_group && !data.detalle){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 15%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.detalle){
                    $('td', row).css('background-color', 'rgb(197 228 241 / 56%)');
                    $('td', row).css('font-weight', 'bold');
                }
            },
            ajax:  {
                type: "GET",
                url: base_url + 'auxiliares',
                headers: headers,
                data: function ( d ) {
                    d.fecha_desde = $('#fecha_desde').val();
                    d.fecha_hasta = $('#fecha_hasta').val();
                    d.id_cuenta = $('#id_cuenta').val();
                    d.id_nit = $('#id_nit').val();
                }
            },
            "columns": [
                {"data": function (row, type, set){
                    return row.cuenta + ' - ' +row.nombre_cuenta;
                }},
                {"data": function (row, type, set){
                    if(!row.numero_documento){
                        return '';
                    }
                    if(row.razon_social){
                        return row.numero_documento +' - '+ row.razon_social;
                    }
                    return row.numero_documento + ' - ' +row.nombre_nit;
                }},
                {"data": function (row, type, set){
                    if(!row.codigo_cecos){
                        return '';
                    }
                    return row.codigo_cecos + ' - ' +row.nombre_cecos;
                }},
                {data: 'documento_referencia'},
                { data: "saldo_anterior", render: $.fn.dataTable.render.number('.', ',', 0, '')},
                { data: "debito", render: $.fn.dataTable.render.number('.', ',', 0, '')},
                { data: "credito", render: $.fn.dataTable.render.number('.', ',', 0, '')},
                { data: "saldo_final", render: $.fn.dataTable.render.number('.', ',', 0, '')},
                {"data": function (row, type, set){
                    if(!row.codigo_comprobante){
                        return '';
                    }
                    return row.codigo_comprobante + ' - ' +row.nombre_comprobante;
                }},
                {"data": function (row, type, set){
                    if(!row.consecutivo){
                        return '';
                    }
                    return row.consecutivo;
                }},
                {"data": function (row, type, set){
                    if(!row.fecha_manual){
                        return '';
                    }
                    return row.fecha_manual;
                }},
                {"data": function (row, type, set){
                    if(!row.concepto){
                        return '';
                    }
                    return row.concepto;
                }},
            ]
        });

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
        
        $(document).on('click', '#generarAuxiliar', function () {
            $("#generarAuxiliar").hide();
            $("#generarAuxiliarLoading").show();
            $('#descargarExcelAuxiliar').prop('disabled', true);
            $("#descargarExcelAuxiliar").hide();
            $("#descargarExcelAuxiliarDisabled").show();
            auxiliar_table.ajax.reload(function() {
                $("#generarAuxiliar").show();
                $("#generarAuxiliarLoading").hide();
                $('#descargarExcelAuxiliar').prop('disabled', false);
                $("#descargarExcelAuxiliar").show();
                $("#descargarExcelAuxiliarDisabled").hide();
                $('.error').hide();
            },false);
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

        $(document).on('click', '#descargarExcelAuxiliar', function () {
            var fecha_desde = $('#fecha_desde').val();
            var fecha_hasta = $('#fecha_hasta').val();
            var numero_documento = $('#numero_documento').val();
            var documento_referencia = $('#documento_referencia').val();
            window.open("/auxiliar-excel?fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"&id_nit="+$('#id_nit').val()+"&id_cuenta="+id_cuenta, "_blank");
        });

        $("#fecha_desde").on('change', function(){
            clearAuxiliar();
        });

        $("#fecha_hasta").on('change', function(){
            clearAuxiliar();
        });

        $("#id_cuenta").on('change', function(){
            clearAuxiliar();
        });

        $("#id_nit").on('change', function(){
            clearAuxiliar();
        });

        function clearAuxiliar()
        {
            $("#descargarExcelAuxiliar").hide();
            $("#descargarExcelAuxiliarDisabled").show();
            // if(auxiliar_table.rows().data().length){
            //     auxiliar_table.clear([]).draw(false);
            //     // auxiliar_table.rows().destroy();
            // }
        }

    </script>
@endpush
