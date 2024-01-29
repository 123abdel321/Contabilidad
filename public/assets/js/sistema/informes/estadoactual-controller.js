var estado_actual_table = null;
var generarEstadoActual = false;
var $comboYearEstadoActual = null;
var $comboComprobantesEstadoActual = null;

function estadoactualInit() {
    
    estado_actual_table = $('#estadoActualInformeTable').DataTable({
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
            url: base_url + 'auxiliares',
            headers: headers,
            data: function ( d ) {
                d.year = $('#year_estado_actual').val();
                d.id_comprobante = $('#id_comprobante_estado_actual').val();
            }
        },
        'rowCallback': function(row, data, index){
            var mes = data.mes;
            if(mes == 'TOTALES'){
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (mes.split('TOTALES').length > 1) {
                $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
        },
        "columns": [
            { data: 'mes'},
            { data: 'year'},
            { data: 'comprobantes'},
            { data: 'registros'},
            { data: 'documentos'},
            { data: 'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'diferencia', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'errores'}
        ]
    });

    $comboYearEstadoActual = $('#year_estado_actual').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un año",
        allowClear: true,
        ajax: {
            url: 'api/year-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboComprobantesEstadoActual = $('#id_comprobante_estado_actual').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Comprobante",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

var channel = pusher.subscribe('informe-estado-actual-'+localStorage.getItem("notificacion_code"));

channel.bind('notificaciones', function(data) {
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_estado_actual){
        $('#id_estado_actual_cargado').val(data.id_estado_actual);
        loadEstadoActualById(data.id_estado_actual);
        return;
    }
});

$(document).on('click', '#generarEstadoActual', function () {
    generarEstadoActual = false;

    $("#generarEstadoActual").hide();
    $("#generarEstadoActualLoading").show();

    var url = base_url + 'estado-actual';
    url+= '?year='+$('#year_estado_actual').val();
    url+= '?id_comprobante='+$('#id_comprobante_estado_actual').val();
    url+= '&generar='+generarEstadoActual;

    estado_actual_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando estado actual', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });

});

function loadEstadoActualById(id_estado_actual) {
    estado_actual_table.ajax.url(base_url + 'estado-actual-show?id='+id_estado_actual).load(function(res) {
        if(res.success){
            $("#generarEstadoActual").show();
            $("#generarEstadoActualLoading").hide();
            agregarToast('exito', 'Estado actual cargados', res.mensaje, true);
        }
    });
}
