var fechaDesde = null;
var resultados_table = null;
var generarResultados = false;
var resultadosExistente = false;
var channelResultado = pusher.subscribe('informe-resultado-'+localStorage.getItem("notificacion_code"));

function resultadosInit() {
    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarResultados = false;
    resultadosExistente = false;

    $('#fecha_desde_resultado').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_resultado').val(fechaDesde);

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
                d.fecha_desde = $('#fecha_desde_resultado').val();
                d.fecha_hasta = $('#fecha_hasta_resultado').val();
                d.id_cuenta = $('#id_cuenta_resultado').val();
                d.tipo = $('#tipo_informe_resultado').val();
                d.id_cecos = $('#id_cecos_resultado').val();
                d.id_nit = $('#id_nit_resultado').val();
                d.generar = generarResultados;
            }
        },
        "rowCallback": function(row, data, index){
            if(data.cuenta == "TOTALES"){
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (!data.auxiliar) {
                if(data.cuenta.length == 1){//
                    $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                    $('td', row).css('font-weight', '700');
                    return;
                }
                if(data.cuenta.length == 2){//
                    $('td', row).css('background-color', 'rgb(64 164 209 / 45%)');
                    $('td', row).css('font-weight', '600');
                    return;
                }
                if(data.cuenta.length == 4){//
                    $('td', row).css('background-color', 'rgb(64 164 209 / 30%)');
                    $('td', row).css('font-weight', '600');
                    return;
                }
                if(data.cuenta.length == 6){//
                    $('td', row).css('background-color', 'rgb(64 164 209 / 15%)');
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
            {data: 'ppto_porcentaje'},
            {data: 'ppto_porcentaje_acumulado'},
        ]
    });

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
    url+= '?fecha_desde='+$('#fecha_desde_resultado').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_resultado').val();
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
    if(data.id_resultado){
        $('#id_resultado_cargado').val(data.id_resultado);
        loadResultadoById(data.id_resultado);
        return;
    }
});

function loadResultadoById(id_resultado) {
    resultados_table.ajax.url(base_url + 'resultados-show?id='+id_resultado).load(function(res) {
        console.log('res: ',res);
        if(res.success){
            $("#generarResultado").show();
            $("#generarResultadoLoading").hide();
            // $("#generarResultadoUltimo").hide();
            // $("#generarResultadoUltimoLoading").hide();
            // $('#descargarExcelBalance').prop('disabled', false);
            // $("#descargarExcelBalance").show();
            // $("#descargarExcelBalanceDisabled").hide();

            agregarToast('exito', 'Resultado cargado', 'Informe cargado con exito!', true);
        }
    });
}