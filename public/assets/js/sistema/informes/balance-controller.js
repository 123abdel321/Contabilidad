var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
var generarBalance = false;
var balanceExistente = false;
var balance_table = null;
var channelBalance = pusher.subscribe('informe-balance-'+localStorage.getItem("notificacion_code"));

function balanceInit() {

    generarBalance = false;
    balanceExistente = false;

    const start = moment().startOf("month");
    const end = moment().endOf("month");

    $("#fecha_manual_balance").daterangepicker({
        startDate: start,
        endDate: end,
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
        locale: {
            format: "YYYY-MM-DD",
            separator: " - ",
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        },
        ranges: {
            "Hoy": [moment().startOf('day'), moment().endOf('day')],
            "Ayer": [
                moment().subtract(1, "days").startOf("day"),
                moment().subtract(1, "days").endOf("day")
            ],
            "Últimos 7 días": [
                moment().subtract(6, "days").startOf("day"),
                moment().endOf("day")
            ],
            "Últimos 30 días": [
                moment().subtract(29, "days").startOf("day"),
                moment().endOf("day")
            ],
            "Este mes": [
                moment().startOf("month").startOf("day"),
                moment().endOf("month").endOf("day")
            ],
            "Mes anterior": [
                moment().subtract(1, "month").startOf("month").startOf("day"),
                moment().subtract(1, "month").endOf("month").endOf("day")
            ]
        }
    }, function(start, end) {
        formatoFecha(start, end, "fecha_manual_balance");
    });

    formatoFecha(start, end, "fecha_manual_balance");

    $("#fecha_manual_balance").on('change blur', function() {
        console.log('adasdsss');
        parseManualInput($(this).val(), "fecha_manual_balance");
    });

    balance_table = $('#balanceInformeTable').DataTable({
        pageLength: 100,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scroller: {
            displayBuffer: 20,
            rowHeight: 50,
            loadingIndicator: true
        },
        deferRender: true,
        fixedHeader : {
            header : true,
            footer : true,
            headerOffset: 45
        },
        'rowCallback': function(row, data, index){
            if(data.cuenta == "TOTALES"){
                $('td', row).css('background-color', 'rgb(0 0 0)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (data.balance || data.auxiliar) {
                return;
            }
            if(data.balance){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 10%)');
                return;
            }
            if(data.cuenta && data.cuenta.length == 1){//
                $('td', row).css('background-color', 'rgb(33 35 41)');
                $('td', row).css('font-weight', '700');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.cuenta && data.cuenta.length == 2){//
                if (getNivel() == 1) {
                    return;
                }
                $('td', row).css('background-color', 'rgb(33 35 41 / 70%)');
                $('td', row).css('font-weight', '700');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.cuenta && data.cuenta.length == 4){//
                if (getNivel() == 2) {
                    return;
                }
                $('td', row).css('background-color', '#33849e');
                $('td', row).css('font-weight', '600');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.cuenta && data.cuenta.length == 6){//
                $('td', row).css('background-color', '#9bd8e9ff');
                $('td', row).css('font-weight', '600');
                return;
            }
            if (!data.balance) {
                $('td', row).css('background-color', 'rgb(33 35 41 / 10%)');
                $('td', row).css('font-weight', '700');
                return;
            }
        },
        ajax:  {
            type: "GET",
            url: base_url + 'balances',
            headers: headers,
            data: function ( d ) {
                d.fecha_desde = $('#fecha_manual_balance').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                d.fecha_hasta = $('#fecha_manual_balance').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                d.cuenta_desde = $('#cuenta_desde_balance').val();
                d.cuenta_hasta = $('#cuenta_hasta_balance').val();
                d.tipo = $('#tipo_informe_balance').val();
                d.id_nit = $('#id_nit_balance').val();
            }
        },
        "columns": [
            {"data": function (row, type, set){
                if(row.cuenta && row.balance != 5){
                    return row.cuenta;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if(row.cuenta && row.balance != 5){
                    return row.nombre_cuenta;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if(row.cuenta && row.balance == 5){
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
    balance_table.column(2).visible(false);

    $('#id_nit_balance').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
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

    $("#tipo_informe_balance").on('change', function(){
        var data = $("#tipo_informe_balance").val();
        $('#cuenta_desde_balance').prop('disabled', false);
        $('#cuenta_hasta_balance').prop('disabled', false);
        $("#cuenta_desde_balance").val('');
        $("#cuenta_hasta_balance").val('');
        
        if (data == '3') {
            $('#cuenta_desde_balance').prop('disabled', true);
            $('#cuenta_hasta_balance').prop('disabled', true);
            $("#cuenta_desde_balance").val(1);
            $("#cuenta_hasta_balance").val(3);
        }
        findBalance();
    });

    findBalance();
}

$(document).on('click', '#generarBalance', function () {
    generarConsultaBalance();
});

function generarConsultaBalance() {
    generarBalance = false;
    $("#generarBalance").hide();
    $("#generarBalanceLoading").show();

    $("#descargarExcelBalance").hide();
    $("#descargarExcelBalanceDisabled").show();

    $("#descargarPdfBalance").hide();
    $("#descargarPdfBalanceDisabled").show();

    $(".cardTotalBalance").css("background-color", "white");

    $("#balance_anterior").text('0');
    $("#balance_debito").text('0');
    $("#balance_credito").text('0');
    $("#balance_diferencia").text('0');

    var tipoInformeBalance = $("#tipo_informe_balance").val();
    balance_table.column(2).visible(false);
    if (tipoInformeBalance == '2') balance_table.column(2).visible(true);

    var url = base_url + 'balances';
    url+= '?fecha_desde='+$('#fecha_manual_balance').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
    url+= '&fecha_hasta='+$('#fecha_manual_balance').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
    
    url+= '&cuenta_desde='+$('#cuenta_desde_balance').val();
    url+= '&cuenta_hasta='+$('#cuenta_hasta_balance').val();
    url+= '&tipo='+$('#tipo_informe_balance').val();
    url+= '&nivel='+getNivel();
    url+= '&generar='+generarBalance;

    balance_table.ajax.url(url).load(function(res) {
        $("#generarBalance").show();
        $("#generarBalanceLoading").hide();
        $("#generarBalanceUltimoLoading").hide();
        if(res.success) {
            if(res.data){
                Swal.fire({
                    title: '¿Cargar Balance?',
                    text: "Balance generado anteriormente ¿Desea cargarlo?",
                    type: 'info',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Cargar',
                    cancelButtonText: 'Generar nuevo',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.value){
                        $('#id_balance_cargado').val(res.data);
                        loadBalanceById(res.data);
                    } else {
                        generarBalance = true;
                        GenerateBalance();
                    }
                })
            } else {
                if (res.time) {
                    agregarToast('info', 'Generando balance', 'El informe se esta generando desde las '+res.time+' se le notificará cuando el informe esté generado...', false );
                } else {
                    agregarToast('info', 'Generando balance', 'En un momento se le notificará cuando el informe esté generado...', true );
                }
            }
        } else {
            agregarToast('error', 'Informe balance', res.message, false );
        }
    });
}

channelBalance.bind('notificaciones', function(data) {

    if(data.id_balance){
        $('#id_balance_cargado').val(data.id_balance);
        loadBalanceById(data.id_balance);
        return;
    }

    if(data.url_file){
        $("#descargarExcelBalance").show();
        $("#descargarExcelBalanceLoading").hide();
        $("#descargarExcelBalanceDisabled").hide();

        loadExcel(data);
        return;
    }

    if (data.url_file_pdf) {
        $("#descargarPdfBalance").show();
        $("#descargarPdfBalanceLoading").hide();
        $("#descargarPdfBalanceDisabled").hide();

        loadPdf(data);
        return;
    }

    if(data.tipo == 'error'){
        console.log('data: ',data);
    }
});

function loadBalanceById(id_balance) {
    $('#id_balance_cargado').val(id_balance);
    balance_table.ajax.url(base_url + 'balances-show?id='+id_balance).load(function(res) {
        if(res.success){
            $("#generarBalance").show();
            $("#generarBalanceLoading").hide();
            $("#generarBalanceUltimoLoading").hide();

            $("#descargarExcelBalance").show();
            $("#descargarExcelBalanceDisabled").hide();

            $("#descargarPdfBalance").show();
            $("#descargarPdfBalanceDisabled").hide();

            if ($("#tipo_informe_balance").val() != '3') {
                if(res.descuadre) {
                    Swal.fire(
                        'Balance descuadrado',
                        '',
                        'warning'
                    );
                } else {
                    agregarToast('exito', 'Balance cargado', 'Informe cargado con exito!', true);
                }
            }
            mostrarTotalesBalance(res.totales, res.filtros);
        }
    });
}

function mostrarTotalesBalance(data, filtros = false) {
    if (!data) {
        return;
    }
    if(!filtros && parseInt(data.saldo_anterior)){
        cambiarColorTotalesBalance('#ff0000');
    } else if (!filtros && !parseInt(data.saldo_anterior)){
        cambiarColorTotalesBalance('#0002ff');
    } else if (!filtros && parseInt(data.saldo_final)){
        cambiarColorTotalesBalance('#ff0000');
    } else if (!filtros && !parseInt(data.saldo_final)) {
        cambiarColorTotalesBalance('#0002ff');
    } else {
        cambiarColorTotalesBalance('#0002ff');
    }

    $("#balance_anterior").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_anterior));
    $("#balance_debito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.debito));
    $("#balance_credito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.credito));
    $("#balance_diferencia").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_final));
}

function cambiarColorTotalesBalance(color) {
    $('#balance_anterior').css("color", color);
    $('#balance_debito').css("color", color);
    $('#balance_credito').css("color", color);
    $('#balance_diferencia').css("color", color);
}

function getNivel() {
    if($("input[type='radio']#nivel_balance1").is(':checked')) return 1;
    if($("input[type='radio']#nivel_balance2").is(':checked')) return 2;
    if($("input[type='radio']#nivel_balance3").is(':checked')) return 3;

    return false;
}

function GenerateBalance() {
    var url = base_url + 'balances';
    url+= '?fecha_desde='+$('#fecha_desde_balance').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_balance').val();
    url+= '&cuenta_desde='+$('#cuenta_desde_balance').val();
    url+= '&cuenta_hasta='+$('#cuenta_hasta_balance').val();
    url+= '&tipo='+$('#tipo_informe_balance').val();
    url+= '&nivel='+getNivel();
    url+= '&generar='+generarBalance;

    balance_table.ajax.url(url).load(function(res) {
        if(res.success) {

            agregarToast('info', 'Generando balance', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
}

$(document).on('click', '#descargarExcelBalance', function () {

    $("#descargarExcelBalance").hide();
    $("#descargarExcelBalanceLoading").show();
    $("#descargarExcelBalanceDisabled").hide();

    $.ajax({
        url: base_url + 'balances-excel',
        method: 'POST',
        data: JSON.stringify({id: $('#id_balance_cargado').val()}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        $("#descargarExcelBalance").show();
        $("#descargarExcelBalanceLoading").hide();
        $("#descargarExcelBalanceDisabled").hide();

        if(res.success){
            if(res.url_file){
                window.open('https://'+res.url_file, "_blank");
                return; 
            }
            agregarToast('info', 'Generando excel', res.message, true);
        }
    }).fail((err) => {

        $("#descargarExcelBalance").show();
        $("#descargarExcelBalanceLoading").hide();
        $("#descargarExcelBalanceDisabled").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Error al generar excel', errorsMsg);
    });
});

$(document).on('click', '#descargarPdfBalance', function () {

    $("#descargarPdfBalance").hide();
    $("#descargarPdfBalanceLoading").show();
    $("#descargarPdfBalanceDisabled").hide();

    $.ajax({
        url: base_url + 'balances-pdf',
        method: 'POST',
        data: JSON.stringify({id: $('#id_balance_cargado').val()}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            if(res.url_file){

                $("#descargarPdfBalance").show();
                $("#descargarPdfBalanceLoading").hide();
                $("#descargarPdfBalanceDisabled").hide();

                setTimeout(function(){
                    window.open('https://'+res.url_file, "_blank");
                    agregarToast('info', 'Generando pdf', res.message, true);
                    return;
                },1000);
            } else {
                agregarToast('info', 'Generando pdf', res.message, true);
            }
        }
    }).fail((err) => {

        $("#descargarPdfBalance").show();
        $("#descargarPdfBalanceLoading").hide();
        $("#descargarPdfBalanceDisabled").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Error al generar pdf', errorsMsg);
    });
});

$(document).on('click', '#generarBalanceUltimo', function () {
    $('#generarBalanceUltimo').hide();
    $('#generarBalanceUltimoLoading').show();
    loadBalanceById(balanceExistente);
});

function findBalance() {
    console.log('findBalance');
    balanceExistente = false;
    $('#generarBalanceUltimo').hide();
    $('#generarBalanceUltimoLoading').show();

    var url = 'balances-find';

    url+= '?fecha_desde='+$('#fecha_desde_balance').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_balance').val();
    url+= '&cuenta_desde='+$('#cuenta_desde_balance').val();
    url+= '&cuenta_hasta='+$('#cuenta_hasta_balance').val();
    url+= '&tipo='+$('#tipo_informe_balance').val();
    url+= '&nivel='+getNivel();
    
    $.ajax({
        url: base_url + url,
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#generarBalanceUltimoLoading').hide();
        if(res.data){
            balanceExistente = res.data;
            $('#generarBalanceUltimo').show();
        }
    }).fail((err) => {
        $('#generarBalanceUltimoLoading').hide();
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
        agregarToast('error', 'Error consultar balancees', errorsMsg, true);
    });
}

$("#fecha_desde_balance").on('change', function(){
    clearBalance();
    findBalance();
});

$("#fecha_hasta_balance").on('change', function(){
    clearBalance();
    findBalance();
});

$("#cuenta_desde_balance").on('change', function(){
    var cuentaDesde = $("#cuenta_desde_balance").val();
    $("#cuenta_hasta_balance").val(cuentaDesde);
    clearBalance();
    findBalance();
});

$("#cuenta_hasta_balance").on('change', function(){
    clearBalance();
    findBalance();
});

$(".nivel_balance").on('change', function(){
    clearBalance();
    findBalance();
});

function clearBalance() {
    $("#descargarExcelBalance").hide();
    $("#descargarExcelBalanceDisabled").show();

    $("#descargarPdfBalance").hide();
    $("#descargarPdfBalanceDisabled").show();
}