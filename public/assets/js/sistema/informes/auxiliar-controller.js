var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
var generarAuxiliar = false;
var auxiliarExistente = false;
var auxiliar_table = null;

function auxiliarInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarAuxiliar = false;
    auxiliarExistente = false;

    $('#fecha_desde_auxiliar').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_auxiliar').val(fechaDesde);

    auxiliar_table = $('#auxiliarInformeTable').DataTable({
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
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
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
            if(data.naturaleza_cuenta == 0 && parseInt(data.saldo_final) < 0 && data.detalle_group == 'nits') {
                if (data.cuenta.length > 2) {
                    var cuenta = data.cuenta.charAt(0)+data.cuenta.charAt(1);
                    if (!cuenta == '11') {
                        return '<div class=""><i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;'+(saldo_final*-1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+'</div>';
                    }
                }
            }
            if(data.naturaleza_cuenta == 1 && parseInt(data.saldo_final) > 0 && data.detalle_group == 'nits') {
                if (data.cuenta.length > 2) {
                    var cuenta = data.cuenta.charAt(0)+data.cuenta.charAt(1);
                    if (!cuenta == '11') {
                        return '<div class=""><i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;'+(saldo_final*-1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+'</div>';
                    }
                }
            }
            if(data.detalle_group == 'nits-totales'){
                $('td', row).css('background-color', 'rgb(64 164 209 / 25%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.detalle_group == 'nits'){
                $('td', row).css('background-color', 'rgb(64 164 209 / 15%)');
                return;
            }
            if(data.cuenta == "TOTALES"){
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.cuenta.length == 1){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 90%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 2){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 75%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 4){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.detalle == 0 && data.detalle_group == 0){
                return;
            }
            if(data.detalle_group && !data.detalle){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 45%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.detalle){
                $('td', row).css('background-color', 'rgb(64 164 209 / 35%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
        },
        ajax:  {
            type: "GET",
            url: base_url + 'auxiliares',
            headers: headers,
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_auxiliar').val();
                d.fecha_hasta = $('#fecha_hasta_auxiliar').val();
                d.id_cuenta = $('#id_cuenta_auxiliar').val();
                d.id_nit = $('#id_nit_auxiliar').val();
                d.generar = generarAuxiliar;
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
                
                var html = '<div class="button-user" onclick="showNit('+row.id_nit+')"><i class="far fa-id-card icon-user"></i>&nbsp;'+nombre+'</div>';
                return html;
            }},
            { data: 'apartamento_nit'},
            {"data": function (row, type, set){
                if(!row.codigo_cecos){
                    return '';
                }
                return row.codigo_cecos + ' - ' +row.nombre_cecos;
            }},
            { data: 'documento_referencia'},
            { data: "saldo_anterior",render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "debito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "credito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                var saldo_final = parseFloat(row.saldo_final);
                if(row.naturaleza_cuenta == 0 && saldo_final < 0 && row.detalle_group == 'nits') {
                    var cuenta = row.cuenta.charAt(0)+row.cuenta.charAt(1);
                    if (!cuenta == '11') {
                        return '<div class=""><i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;'+(saldo_final*-1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+'</div>';
                    }
                } else if(row.naturaleza_cuenta == 1 && saldo_final > 0 && row.detalle_group == 'nits') {
                    var cuenta = row.cuenta.charAt(0)+row.cuenta.charAt(1);
                    if (!cuenta == '11') {
                        return '<div class=""><i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;'+(saldo_final).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+'</div>';
                    }
                } else if(row.naturaleza_cuenta == 1 && saldo_final < 0) {
                    return (saldo_final*-1).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
                return saldo_final.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }, className: 'dt-body-right'},
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
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
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

    var columnUbicacionMaximoPH = auxiliar_table.column(2);

    if (ubicacion_maximoph) columnUbicacionMaximoPH.visible(true);
    else columnUbicacionMaximoPH.visible(false);

    $('#id_nit_auxiliar').select2({
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

    $('#id_cuenta_auxiliar').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
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
    
    findAuxiliar();
}

$(document).on('click', '#generarAuxiliar', function () {
    generarAuxiliar = false;
    $("#generarAuxiliar").hide();
    $("#generarAuxiliarLoading").show();
    $('#descargarExcelAuxiliar').prop('disabled', true);
    $("#descargarExcelAuxiliar").hide();
    $("#descargarExcelAuxiliarDisabled").show();

    $(".cardTotalAuxiliar").css("background-color", "white");

    $("#auxiliar_anterior").text('$0');
    $("#auxiliar_debito").text('$0');
    $("#auxiliar_credito").text('$0');
    $("#auxiliar_diferencia").text('$0');

    var url = base_url + 'auxiliares';
    url+= '?fecha_desde='+$('#fecha_desde_auxiliar').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_auxiliar').val();
    url+= '&id_cuenta='+$('#id_cuenta_auxiliar').val();
    url+= '&generar='+generarAuxiliar;
    
    auxiliar_table.ajax.url(url).load(function(res) {
        if(res.success) {
            if(res.data){
                Swal.fire({
                    title: '¿Cargar auxiliar?',
                    text: "Auxiliar generado anteriormente, ¿Desea cargarlo?",
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
                        $('#id_auxiliar_cargado').val(res.data);
                        console.log('auxiliares load: ',res.data);
                        loadAuxiliarById(res.data);
                    } else {
                        generarAuxiliar = true;
                        GenerateAuxiliar();
                    }
                })
            } else {
                agregarToast('info', 'Generando auxiliar', 'En un momento se le notificará cuando el informe esté generado...', true );
            }
        }
    });
});

var channel = pusher.subscribe('informe-auxiliar-'+localStorage.getItem("notificacion_code"));

channel.bind('notificaciones', function(data) {
    console.log('notificaciones', data);
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_auxiliar){
        $('#id_auxiliar_cargado').val(data.id_auxiliar);
        loadAuxiliarById(data.id_auxiliar);
        return;
    }
});

function loadAuxiliarById(id_auxiliar) {
    auxiliar_table.ajax.url(base_url + 'auxiliares-show?id='+id_auxiliar).load(function(res) {
        if(res.success){
            $("#generarAuxiliar").show();
            $("#generarAuxiliarLoading").hide();
            $('#descargarExcelAuxiliar').prop('disabled', false);
            $("#descargarExcelAuxiliar").show();
            $("#descargarExcelAuxiliarDisabled").hide();
            $('#generarAuxiliarUltimo').hide();
            $('#generarAuxiliarUltimoLoading').hide();
            if(res.descuadre) {
                Swal.fire(
                    'Auxiliar descuadrado',
                    '',
                    'warning'
                );
            } else {
                agregarToast('exito', 'Auxiliar cargado', 'Informe cargado con exito!', true);
            }
            mostrarTotalesAuxiliar(res.totales, res.filtros);
        }
    });
}

function mostrarTotalesAuxiliar(data, filtros = false) {
    if(!data) {
        return;
    }
    if(!filtros && parseInt(data.saldo_anterior)){
        cambiarColorTotales('#ff0000');
    } else if (!filtros && !parseInt(data.saldo_anterior)){
        cambiarColorTotales('#0002ff');
    } else if (!filtros && parseInt(data.saldo_final)){
        cambiarColorTotales('#ff0000');
    } else if (!filtros && !parseInt(data.saldo_final)) {
        cambiarColorTotales('#0002ff');
    } else {
        cambiarColorTotales('#0002ff');
    }

    $("#auxiliar_anterior").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_anterior));
    $("#auxiliar_debito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.debito));
    $("#auxiliar_credito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.credito));
    $("#auxiliar_diferencia").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_final));
}

function cambiarColorTotales(color) {
    $('#auxiliar_anterior').css("color", color);
    $('#auxiliar_debito').css("color", color);
    $('#auxiliar_credito').css("color", color);
    $('#auxiliar_diferencia').css("color", color);
}

function GenerateAuxiliar() {
    var url = base_url + 'auxiliares';
    url+= '?fecha_desde='+$('#fecha_desde_auxiliar').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_auxiliar').val();
    url+= '&id_cuenta='+$('#id_cuenta_auxiliar').val();
    url+= '&generar='+generarAuxiliar;
    auxiliar_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando cartera', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
}

$('input[type=radio][name=tipo_documento]').change(function() {
    document.getElementById("generarAuxiliar").click();
});

$(document).on('click', '#descargarExcelAuxiliar', function () {
    $.ajax({
        url: base_url + 'auxiliares-excel',
        method: 'POST',
        data: JSON.stringify({id: $('#id_auxiliar_cargado').val()}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            if(res.url_file){
                window.open('https://'+res.url_file, "_blank");
                return; 
            }
            agregarToast('info', 'Generando excel', res.message, true);
        }
    }).fail((err) => {
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
        agregarToast('error', 'Error al generar excel', errorsMsg);
    });
});

$(document).on('click', '#generarAuxiliarUltimo', function () {
    $('#generarAuxiliarUltimo').hide();
    $('#generarAuxiliarUltimoLoading').show();
    loadAuxiliarById(auxiliarExistente);
});

function findAuxiliar() {
    auxiliarExistente = false;
    $('#generarAuxiliarUltimo').hide();
    $('#generarAuxiliarUltimoLoading').show();

    var url = 'auxiliares-find';
    url+= '?fecha_desde='+$('#fecha_desde_auxiliar').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_auxiliar').val();
    url+= '&id_cuenta='+$('#id_cuenta_auxiliar').val();

    $.ajax({
        url: base_url + url,
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#generarAuxiliarUltimoLoading').hide();
        if(res.data){
            auxiliarExistente = res.data;
            $('#generarAuxiliarUltimo').show();
        }
    }).fail((err) => {
        $('#generarAuxiliarUltimoLoading').hide();
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
        agregarToast('error', 'Error consultar auxiliares', errorsMsg, true);
    });
}

$("#fecha_desde_auxiliar").on('change', function(){
    clearAuxiliar();
    findAuxiliar();
});

$("#fecha_hasta_auxiliar").on('change', function(){
    clearAuxiliar();
    findAuxiliar();
});

$("#id_cuenta_auxiliar").on('change', function(){
    clearAuxiliar();
    findAuxiliar();
});

$("#id_nit_auxiliar").on('change', function(){
    clearAuxiliar();
    findAuxiliar();
});

function clearAuxiliar() {
    $("#descargarExcelAuxiliar").hide();
    $("#descargarExcelAuxiliarDisabled").show();
}