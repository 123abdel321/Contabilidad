var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
var generarCartera = false;
var carteraExistente = false;
var cartera_table = null;

function carteraInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarCartera = false;
    carteraExistente = false;

    $('#fecha_cartera').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_cartera').val(fechaDesde);

    cartera_table = $('#CarteraInformeTable').DataTable({
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
            if(data.detalle_group == 'nits'){
                if(!$("input[type='radio']#detallar_cartera1").is(':checked')) {
                    return;
                }
                $('td', row).css('background-color', 'rgb(128 207 120 / 40%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta == "TOTALES"){
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
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
            if(data.detalle_group == '1' && data.detalle != '1'){
                $('td', row).css('background-color', 'rgb(64 164 209 / 15%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.detalle == '1'){
                $('td', row).css('background-color', 'rgb(197 228 241 / 56%)');
                $('td', row).css('font-weight', 'bold');
            }
        },
        ajax:  {
            type: "GET",
            url: base_url + 'extracto',
            headers: headers,
            data: function( result ) {
                return result;
            }
        },
        "columns": [
            {"data": function (row, type, set){
                if(!row.cuenta) {
                    return '';
                }
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
            }, responsivePriority: 1, targets: 0},
            
            {data: 'documento_referencia'},
            {data: 'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 4, targets: -3},
            {data: 'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 3, targets: -2},
            {data: 'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 2, targets: -1},
            {"data": function (row, type, set){
                if(!row.codigo_comprobante){
                    return '';
                }
                return row.codigo_comprobante + ' - ' +row.nombre_comprobante;
            }, visible: false},
            {data: 'fecha_manual'},
            {data: 'dias_cumplidos', responsivePriority: 5, targets: -4},
            {data: 'plazo'},
            {"data": function (row, type, set){
                if(row.plazo > 0){
                    var mora = row.dias_cumplidos - row.plazo;
                    if(mora <= 0) {
                        return 0
                    }
                    return mora;
                }
                return row.dias_cumplidos;
            }},
            {data: 'concepto'},
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

    $('#id_cuenta_cartera').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            data: function (params) {
                var query = {
                    search: params.term,
                    cartera: true
                }
                return query;
            },
            dataType: 'json',
            headers: headers,
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_nit_cartera').select2({
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

    findCartera();
}

$('input[type=radio][name=detallar_cartera]').change(function() {
    if(!$("input[type='radio']#detallar_cartera1").is(':checked')){
        cartera_table.column( 6 ).visible( false );
    } else {
        cartera_table.column( 6 ).visible( true );
    }
    document.getElementById("generarCartera").click();
});

function loadCarteraById(id_cartera) {
    cartera_table.ajax.url(base_url + 'cartera-show?id='+id_cartera).load(function(res) {
        console.log('res: ',res, id_cartera);
        if(res.success){
            $("#generarCartera").show();
            $("#generarCarteraLoading").hide();
            $("#generarCarteraUltimoLoading").hide();
            $('#descargarExcelCartera').prop('disabled', false);
            $("#descargarExcelCartera").show();
            $("#descargarExcelCarteraDisabled").hide();
            $('#generarCarteraUltimo').hide();
            $('#generarCarteraUltimoLoading').hide();

            agregarToast('exito', 'Cartera cargado', 'Informe cargado con exito!', true);
            
            mostrarTotalesCartera(res.totales);
        }
    });
}

function mostrarTotalesCartera(data) {
    console.log('mostrarTotalesCartera: ',data);
    if(!data) {
        return;
    }

    $("#cartera_facturas").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.total_facturas));
    $("#cartera_abonos").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.total_abono));
    $("#cartera_diferencia").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo));
}

$(document).on('click', '#generarCartera', function () {
    generarCartera = false;
    $("#generarCartera").hide();
    $("#generarCarteraLoading").show();
    $('#descargarExcelCartera').prop('disabled', true);
    $("#descargarExcelCartera").hide();

    $("#cartera_facturas").text('$0');
    $("#cartera_abonos").text('$0');
    $("#cartera_diferencia").text('$0');

    var url = base_url + 'cartera';
    url+= '?id_nit='+$('#id_nit_cartera').val();
    url+= '&id_cuenta='+$('#id_cuenta_cartera').val();
    url+= '&fecha_cartera='+$('#fecha_cartera').val();
    url+= '&detallar_cartera='+getDetalleCartera();

    cartera_table.ajax.url(url).load(function(res) {
        if(res.success) {
            if(res.data){
                Swal.fire({
                    title: '¿Cargar Cartera?',
                    text: "Cartera generado anteriormente ¿Desea cargarlo?",
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
                        $('#id_cartera_cargado').val(res.data);
                        loadCarteraById(res.data);
                    } else {
                        generarCartera = true;
                        GenerateCartera();
                    }
                })
            } else {
                agregarToast('info', 'Generando cartera', 'En un momento se le notificará cuando el informe esté generado...', true );
            }
        }
    });

});

function getDetalleCartera() {
    if($("input[type='radio']#detallar_cartera1").is(':checked')) return 1;
    if($("input[type='radio']#detallar_cartera2").is(':checked')) return '';

    return '';
}

function findCartera() {
    carteraExistente = false;
    $('#generarCarteraUltimo').hide();
    $('#generarCarteraUltimoLoading').show();

    var url = base_url + 'cartera-find';
    url+= '?id_nit='+$('#id_nit_cartera').val();
    url+= '&id_cuenta='+$('#id_cuenta_cartera').val();
    url+= '&fecha_cartera='+$('#fecha_cartera').val();
    url+= '&detallar_cartera='+getDetalleCartera();
    
    $.ajax({
        url: url,
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#generarCarteraUltimoLoading').hide();
        if(res.data){
            carteraExistente = res.data;
            $('#generarCarteraUltimo').show();
        }
    }).fail((err) => {
        $('#generarCarteraUltimoLoading').hide();
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
        agregarToast('error', 'Error al consultar cartera', errorsMsg, true);
    });
}

function GenerateCartera() {
    var url = base_url + 'cartera-find';
    url+= '?id_nit='+$('#id_nit_cartera').val();
    url+= '&id_cuenta='+$('#id_cuenta_cartera').val();
    url+= '&fecha_cartera='+$('#fecha_cartera').val();
    url+= '&detallar_cartera='+getDetalleCartera();
    url+= '&generar='+generarCartera;

    cartera_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando cartera', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
}

$(document).on('click', '#generarCarteraUltimo', function () {
    $('#generarCarteraUltimo').hide();
    $('#generarCarteraUltimoLoading').show();
    loadCarteraById(carteraExistente);
});

var channelCartera = pusher.subscribe('informe-cartera-'+localStorage.getItem("notificacion_code"));

channelCartera.bind('notificaciones', function(data) {
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_cartera){
        $('#id_cartera_cargado').val(data.id_cartera);
        loadCarteraById(data.id_cartera);
        return;
    }
});

$("#id_cuenta_cartera").on('change', function(){
    clearCartera();
    findCartera();
});

$("#id_nit_cartera").on('change', function(){
    clearCartera();
    findCartera();
});

$("#fecha_cartera").on('change', function(){
    clearCartera();
    findCartera();
});

$(".detallar_cartera").on('change', function(){
    clearCartera();
    findCartera();
});

function clearCartera() {
    $("#descargarExcelCartera").hide();
    $("#descargarExcelCarteraDisabled").show();
}