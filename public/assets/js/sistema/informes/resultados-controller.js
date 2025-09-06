var fechaDesde = null;
var resultados_table = null;
var generarResultados = false;
var resultadosExistente = false;
var channelResultado = pusher.subscribe('informe-resultado-'+localStorage.getItem("notificacion_code"));

function resultadosInit() {

    generarResultados = false;
    resultadosExistente = false;

    cargarTablasResultados();
    cargarCombosResultados();
    cargarFechasResultados();
}

function cargarTablasResultados() {
    resultados_table = $('#resultadoInformeTable').DataTable({
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
        ajax:  {
            type: "GET",
            url: base_url + 'resultados',
            headers: headers,
            data: function ( d ) {
                d.fecha_desde = $('#fecha_manual_resultados').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                d.fecha_hasta = $('#fecha_manual_resultados').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                d.id_cuenta = $('#id_cuenta_resultado').val();
                d.tipo = $('#tipo_informe_resultado').val();
                d.id_cecos = $('#id_cecos_resultado').val();
                d.id_nit = $('#id_nit_resultado').val();
                d.generar = generarResultados;
            }
        },
        "rowCallback": function(row, data, index){
            if(data.cuenta == "TOTALES"){
                $('td', row).css('background-color', '#000');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (!data.auxiliar) {
                if(data.cuenta.length == 1){//
                    $('td', row).css('background-color', 'rgb(33 35 41)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.cuenta.length == 2){//
                    $('td', row).css('background-color', 'rgb(33 35 41 / 80%)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.cuenta.length == 4){//
                    $('td', row).css('background-color', 'rgb(33 35 41 / 60%)');
                    $('td', row).css('font-weight', '600');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.cuenta.length == 6){//
                    $('td', row).css('background-color', 'rgb(33 35 41 / 40%)');
                    $('td', row).css('font-weight', '600');
                    $('td', row).css('color', 'white');
                    return;
                }
            }
        },
        "columns": [
            {data: 'cuenta'},
            {data: 'nombre_cuenta'},
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
            {
                data: 'ppto_anterior',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
            {
                data: 'ppto_movimiento',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
            {
                data: 'ppto_acumulado',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
            {
                data: 'ppto_diferencia',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
            {data: 'ppto_porcentaje', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'ppto_porcentaje_acumulado', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
        ]
    });
}

function cargarCombosResultados() {
    $('#id_nit_resultado').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
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

    $('#id_cecos_resultado').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un centro de costos",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: base_url + 'centro-costos/combo-centro-costo',
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

    $('#id_cuenta_resultado').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: base_url + 'plan-cuenta/combo-cuenta',
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

function cargarFechasResultados() {
    const start = moment().startOf("month");
    const end = moment().endOf("month");
    
    $("#fecha_manual_resultados").daterangepicker({
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
        ranges: rangoFechas
    }, formatoFecha);

    formatoFecha(start, end, "fecha_manual_resultados");
}

function loadResultadosById(id_impuesto) {
    var url = base_url + 'resultados-show?id='+id_impuesto;

    resultados_table.ajax.url(url).load(function(res) {
        
        if(res.success){
            agregarToast('exito', 'Impuestos cargado', 'Informe cargado con exito!', true);
        }
    });
}

$(document).on('click', '#generarResultado', function () {
    resultadosExistente = false;
    $("#generarResultado").hide();
    $("#generarResultadoLoading").show();

    var url = base_url + 'resultados';
    url+= '?fecha_desde='+$('#fecha_manual_resultados').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
    url+= '&fecha_hasta='+$('#fecha_manual_resultados').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
    url+= '&id_cuenta='+$('#id_cuenta_resultado').val();
    url+= '&tipo='+$('#tipo_informe_resultado').val();
    url+= '&id_cecos='+$('#id_cecos_resultado').val();
    url+= '&id_nit='+$('#id_nit_resultado').val();

    resultados_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando informe', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});

channelResultado.bind('notificaciones', function(data) {
    console.log('channelResultado: ',data);
    if(data.tipo == "error"){
        $("#generarResultado").show();
        $("#generarResultadoLoading").hide();

        $('#generarResultadoUltimo').hide();
        $('#generarResultadoUltimoLoading').hide();
        agregarToast('error', 'Error al cargar informe de resultados', data.mensaje, false);
        return;
    }

    if(data.id_resultado){
        $('#id_resultado_cargado').val(data.id_resultado);
        loadResultadoById(data.id_resultado);
        return;
    }
});

function loadResultadoById(id_resultado) {
    console.log('loadResultadoById: ',id_resultado);

    $('#id_resultado_cargado').val(id_resultado);
    resultados_table.ajax.url(base_url + 'resultados-show?id='+id_resultado).load(function(res) {
        console.log('res: ',res);
        if(res.success){
            $("#generarResultado").show();
            $("#generarResultadoLoading").hide();

            agregarToast('exito', 'Resultado cargado', 'Informe cargado con exito!', true);
        }
    });
}