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
                    $('td', row).css('background-color', '#33849e');
                    $('td', row).css('font-weight', '600');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.cuenta.length == 6){//
                    $('td', row).css('background-color', '#9bd8e9ff');
                    $('td', row).css('font-weight', '600');
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
                className: "column-number dt-body-right"
            },
            {
                data: 'enero',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'febrero',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'marzo',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'abril',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'mayo',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'junio',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'julio',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'agosto',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'septiembre',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'octubre',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'noviembre',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'diciembre',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'saldo_final',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'ppto_anterior',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'ppto_movimiento',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'ppto_acumulado',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
            },
            {
                data: 'ppto_diferencia',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number dt-body-right"
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

function showColumnasDateResultados() {
    // Verificar que la DataTable esté inicializada
    if (typeof resultados_table === 'undefined' || resultados_table === null) {
        return;
    }

    // Obtener fechas del filtro
    var start = $('#fecha_manual_resultados').data('daterangepicker').startDate;
    var end = $('#fecha_manual_resultados').data('daterangepicker').endDate;
    var mesInicio = start.month() + 1; // 1 = enero, 12 = diciembre
    var mesFin = end.month() + 1;

    // Lista de meses en el mismo orden que las columnas de la tabla
    var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    // Recorrer todas las columnas de la DataTable
    resultados_table.columns().every(function (index) {
        var col = this;
        var dataSrc = col.dataSrc(); // Obtiene el nombre del campo 'data' de la columna

        // Verificar si esta columna corresponde a un mes
        if (meses.includes(dataSrc)) {
            var mesNum = meses.indexOf(dataSrc) + 1; // Número de mes (1-12)
            // Mostrar si el mes está dentro del rango, ocultar en caso contrario
            var visible = (mesNum >= mesInicio && mesNum <= mesFin);
            col.visible(visible);
        }
        // Las demás columnas (cuenta, nombre, saldos, presupuestos) no se tocan
    });
}

function loadResultadoById(id_resultado) {

    showColumnasDateResultados();

    $('#id_resultado_cargado').val(id_resultado);
    resultados_table.ajax.url(base_url + 'resultados-show?id='+id_resultado).load(function(res) {
        if(res.success){
            $("#generarResultado").show();
            $("#generarResultadoLoading").hide();

            $("#descargarExcelResultado").show();
            $("#descargarExcelResultadoLoading").hide();
            $("#descargarExcelResultadoDisabled").hide();


            agregarToast('exito', 'Resultado cargado', 'Informe cargado con exito!', true);
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
    url+= '&id_cecos='+$('#id_cecos_resultado').val();
    url+= '&id_nit='+$('#id_nit_resultado').val();

    resultados_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando informe', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});

$(document).on('click', '#descargarExcelResultado', function () {

    $("#descargarExcelResultado").hide();
    $("#descargarExcelResultadoLoading").show();
    $("#descargarExcelResultadoDisabled").hide();

    $.ajax({
        url: base_url + 'resultados-excel',
        method: 'POST',
        data: JSON.stringify({id: $('#id_resultado_cargado').val()}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        $("#descargarExcelResultado").show();
        $("#descargarExcelResultadoLoading").hide();
        $("#descargarExcelResultadoDisabled").hide();

        if(res.success){
            if(res.url_file){
                window.open('https://'+res.url_file, "_blank");
                return; 
            }
            agregarToast('info', 'Generando excel', res.message, true);
        }
    }).fail((err) => {

        $("#descargarExcelResultado").show();
        $("#descargarExcelResultadoLoading").hide();
        $("#descargarExcelResultadoDisabled").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Error al generar excel', errorsMsg);
    });
});

channelResultado.bind('notificaciones', function(data) {

    if(data.url_file){
        loadExcel(data);
        return;
    }
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