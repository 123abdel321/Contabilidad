var estado_comprobante_table = null;
var generarEstadoComprobante = false;
var $comboYearEstadoComprobante = null;
var $comboComprobantesEstadoComprobante = null;

function estadocomprobanteInit() {
    
    estado_comprobante_table = $('#estadoComprobanteInformeTable').DataTable({
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
            url: base_url + 'estado-comprobante',
            headers: headers,
            data: function ( d ) {
                d.year = $('#year_estado_comprobante').val();
                d.month = $('#month_estado_comprobante').val();
            }
        },
        'rowCallback': function(row, data, index){
            if(data.codigo_comprobante == 'TOTALES'){
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if (parseInt(data.errores) > 1) {
                $('td', row).css('background-color', 'rgb(255 0 0 / 45%)');
                return;
            }
        },
        "columns": [
            { data: 'codigo_comprobante'},
            { data: 'nombre_comprobante'},
            { data: 'nombre_tipo_comprobante'},
            { data: 'year'},
            { data: 'registros'},
            { data: 'documentos'},
            { data: 'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'diferencia', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'errores'}
        ]
    });

    $comboYearEstadoComprobante = $('#year_estado_comprobante').select2({
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
}

var channel = pusher.subscribe('informe-estado-comprobante-'+localStorage.getItem("notificacion_code"));

channel.bind('notificaciones', function(data) {
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_estado_comprobante){
        $('#id_estado_comprobante_cargado').val(data.id_estado_comprobante);
        loadEstadoComprobanteById(data.id_estado_comprobante);
        return;
    }
});

$(document).on('click', '#generarEstadoComprobante', function () {
    generarEstadoComprobante = false;

    $("#generarEstadoComprobante").hide();
    $("#generarEstadoComprobanteLoading").show();

    var url = base_url + 'estado-comprobante';
    url+= '?year='+$('#year_estado_comprobante').val();
    url+= '?id_comprobante='+$('#id_comprobante_estado_comprobante').val();
    url+= '&generar='+generarEstadoComprobante;

    estado_comprobante_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando estado comprobante', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });

});

function loadEstadoComprobanteById(id_estado_comprobante) {
    estado_comprobante_table.ajax.url(base_url + 'estado-comprobante-show?id='+id_estado_comprobante).load(function(res) {
        if(res.success){
            $("#generarEstadoComprobante").show();
            $("#generarEstadoComprobanteLoading").hide();
            agregarToast('exito', 'Estado comprobante cargados', res.mensaje, true);
        }
    });
}
