var extracto_informe_table = null;
var initExtracto = true;
var extractoInformeChanel = pusher.subscribe('informe-extracto-'+localStorage.getItem("notificacion_code"));

function extractoInit() {
    
    initTablesExtractos();
    initCombosExtractos();

    const start = moment().startOf("month");
    const end = moment().endOf("month");

    $("#fecha_manual_extracto").daterangepicker({
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
    }, formatoFecha);

    formatoFecha(start, end, "fecha_manual_extracto");
    initExtracto = false;
}

function initTablesExtractos() {
    extracto_informe_table = $('#extractoInformeTable').DataTable({
        pageLength: 100,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'extractos-informe',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_manual_extracto').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                d.fecha_hasta = $('#fecha_manual_extracto').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                d.documento_referencia = $('#factura_documentos_extracto').val();
                d.id_nit = $('#id_nit_extracto').val();
                d.errores = getErroresExtracto();
            }
        },
        columns: [
            { data:'cuenta'},
            { data:'nombre_cuenta'},
            // { data: function (row, type, set){
            //     if(!row.numero_documento){
            //         return '';
            //     }
            //     var nombre = row.numero_documento + ' - ' +row.nombre_nit;
            //     if(row.razon_social){
            //         nombre = row.numero_documento +' - '+ row.razon_social;
            //     }
                
            //     var html = '<div class="button-user" onclick="showNit('+row.id_nit+')"><i class="far fa-id-card icon-user"></i>&nbsp;'+nombre+'</div>';
            //     return html;
            // }},
            // { data: 'apartamento_nit'},
            // { data: function (row, type, set){
            //     if(!row.codigo_cecos){
            //         return '';
            //     }
            //     return row.codigo_cecos + ' - ' +row.nombre_cecos;
            // }},
            { data: 'documento_referencia'},
            { data: function (row, type, set){
                var saldo_anterior = parseFloat(row.saldo_anterior);
                if (row.cuenta == 'TOTALES') {
                    return saldo_anterior.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
                return saldo_anterior.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }, className: 'dt-body-right'},
            { data: "debito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "credito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: function (row, type, set){
                var saldo_final = parseFloat(row.saldo_final);
                if (row.cuenta == 'TOTALES') {
                    return saldo_final.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }
                return saldo_final.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }, className: 'dt-body-right'},
            { data: function (row, type, set){
                if(!row.codigo_comprobante){
                    return '';
                }
                return row.codigo_comprobante + ' - ' +row.nombre_comprobante;
            }},
            { data:'consecutivo'},
            { data:'fecha_manual'},
            { data:'concepto'},
            { data: function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            { data: function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
        ],
        'rowCallback': function(row, data, index){
            // if(data.errores == 1){
            //     $('td', row).css('background-color', '#ff0000b9');
            //     $('td', row).css('font-weight', 'bold');
            //     $('td', row).css('color', 'white');
            //     return;
            // }
            if(data.nivel == 1){
                $('td', row).css('background-color', '#000');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 2 && data.auxiliar == 0 && data.cuenta.length == 1){//
                $('td', row).css('background-color', 'rgb(57 126 219)');
                $('td', row).css('font-weight', '600');
                return;
            }
            if(data.nivel == 2 && data.auxiliar == 0 && data.cuenta.length == 2){//
                $('td', row).css('background-color', 'rgb(57 126 219 / 80%)');
                $('td', row).css('font-weight', '600');
                return;
            }
            if(data.nivel == 2 && data.auxiliar == 0 && data.cuenta.length == 4){//
                $('td', row).css('background-color', 'rgb(57 126 219 / 60%)');
                $('td', row).css('font-weight', '600');
                return;
            }
            if(data.nivel == 2 && data.auxiliar == 0 && data.cuenta.length == 6){//
                $('td', row).css('background-color', 'rgb(57 126 219 / 40%)');
                $('td', row).css('font-weight', '600');
                return;
            }
            if(data.nivel == 2 && data.auxiliar == 1){//
                $('td', row).css('background-color', 'rgb(57 126 219 / 20%)');
                $('td', row).css('font-weight', '600');
                return;
            }
            if(data.nivel == 3){//
                $('td', row).css('background-color', 'rgb(196 221 255)');
                $('td', row).css('font-weight', '600');
                return;
            }
            if (data.nivel == 5) {
                $('td', row).css('background-color', '#000');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 6){//
                console.log('nivel 6');
                $('td', row).css('borderBottom', '1px solid #bababa');
                return;
            }
        }
    });
}

function initCombosExtractos() {
    $('#id_nit_extracto').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
        ajax: {
            url: base_url + 'nit/combo-nit',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

function getErroresExtracto() {
    if($("input[type='radio']#tipo_errores_extracto0").is(':checked')) return '';
    if($("input[type='radio']#tipo_errores_extracto1").is(':checked')) return 1;

    return '';
}

function loadExtractoById(id_extracto) {
    $('#id_extracto_cargado').val(id_extracto);
    extracto_informe_table.ajax.url(base_url + 'extractos-show?id='+id_extracto).load(function(res) {
        console.log('res: ',res);
        $("#reloadExtractosIconNormal").show();
        $("#reloadExtractosIconLoading").hide();
        if(res.success){
            mostrarTotalesExtracto(res.totales);
            agregarToast('exito', 'Extracto cargado', 'Informe cargado con exito!', true);
        }
    });
}

function mostrarTotalesExtracto(data) {
    if (!data) {
        return;
    }

    var countA = new CountUp('saldo_anterior_extracto', 0, data.saldo_anterior, 2, 0.5);
        countA.start();

    var countB = new CountUp('debito_extracto', 0, data.debito, 2, 0.5);
        countB.start();

    var countC = new CountUp('credito_extracto', 0, data.credito, 2, 0.5);
        countC.start();

    var countD = new CountUp('saldo_final_extracto', 0, data.saldo_final, 2, 0.5);
        countD.start();
}

extractoInformeChanel.bind('notificaciones', function(data) {
    if(data.tipo == "error"){
        $("#reloadExtractosIconNormal").show();
        $("#reloadExtractosIconLoading").hide();
        agregarToast('error', 'Error al cargar informe', data.mensaje, false);
        return;
    }

    if(data.url_file){
        loadExcel(data);
        return;
    }

    if(data.id_extracto){
        $('#id_extracto_cargado').val(data.id_extracto);
        loadExtractoById(data.id_extracto);
        return;
    }
});

$(document).on('click', '#reloadExtracto', function () {

    if (initExtracto) {
        return;
    }

    $("#reloadExtractosIconNormal").hide();
    $("#reloadExtractosIconLoading").show();

    let documento_referencia = "";
    if ($('#factura_documentos_extracto').val()) {
        documento_referencia = $('#factura_documentos_extracto').val();
    }

    var url = base_url + 'extractos-informe';
    url+= '?id_nit='+$('#id_nit_extracto').val();
    url+= '&fecha_desde='+$('#fecha_manual_extracto').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
    url+= '&fecha_hasta='+$('#fecha_manual_extracto').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
    url+= '&documento_referencia='+documento_referencia;
    url+= '&errores='+getErroresExtracto();

    extracto_informe_table.ajax.url(url).load();
});

$("#fecha_manual_extracto").on('change', function(){
    document.getElementById("reloadExtracto").click();
});

$("#id_nit_extracto").on('change', function(){
    document.getElementById("reloadExtracto").click();
});

$("#factura_documentos_extracto").on('change', function(){
    document.getElementById("reloadExtracto").click();
});