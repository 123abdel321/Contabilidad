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
    
    <div class="container-fluid py-2">
        <div class="row">

            <div class="card mb-4">
                <div class="card-body" style="padding: 0 !important;">
                    @include('pages.contabilidad.auxiliar.auxiliar-filter')
                </div>
            </div>

            <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body">
                    @include('pages.contabilidad.auxiliar.auxiliar-table')
                </div>

            </div>
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
            responsive: true,
            processing: true,
            serverSide: true,
            deferLoading: 0,
            initialLoad: false,
            language: lenguajeDatatable,
            ordering: false,
            'rowCallback': function(row, data, index){
                if(data.detalle_group == 'nits-totales'){
                    $('td', row).css('background-color', 'rgb(128 207 120 / 40%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.detalle_group == 'nits'){
                    $('td', row).css('background-color', 'rgb(161 182 193 / 40%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.cuenta == "TOTALES"){
                    $('td', row).css('background-color', 'rgb(0 215 64 / 60%)');
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
                    $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
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
                    d.tipo_documento = $("input[type='radio']#tipo_documento1").is(':checked') ? 'todas' : 'anuladas';
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
                    var nombre = row.numero_documento + ' - ' +row.nombre_nit;
                    if(row.razon_social){
                        nombre = row.numero_documento +' - '+ row.razon_social;
                    }
                    
                    var html = '<div class="button-user" onclick="showNit('+row.numero_documento+')"><i class="far fa-id-card icon-user"></i>&nbsp;'+nombre+'</div>';
                    return html;


                }},
                {"data": function (row, type, set){
                    if(!row.codigo_cecos){
                        return '';
                    }
                    return row.codigo_cecos + ' - ' +row.nombre_cecos;
                }},
                {data: 'documento_referencia'},
                { data: "saldo_anterior",render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
                { data: "debito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
                { data: "credito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
                { data: "saldo_final", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
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
                {"data": function (row, type, set){  
                    var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                    if(!row.created_by && !row.fecha_creacion) return '';
                    if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                    return html;
                }},
                {"data": function (row, type, set){
                    var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                    if(!row.updated_by && !row.fecha_edicion) return '';
                    if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                    return html;
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
