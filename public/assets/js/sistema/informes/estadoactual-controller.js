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
            url: base_url + 'estado-actual',
            headers: headers,
            data: function ( d ) {
                d.year = $('#year_estado_actual').val();
                d.month = $('#month_estado_actual').val();
                d.detalle = getDellarEstadoActual();
                d.id_comprobante = $('#id_comprobante_estado_actual').val();
            }
        },
        'rowCallback': function(row, data, index){
            var mes = data.mes;
            var detalleActual = parseInt(getDellarEstadoActual());
            if (data.total == 0 && parseInt(data.diferencia) != 0) {
                $('td', row).css('background-color', 'red');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            
            if(data.total == 4){
                $('td', row).css('background-color', 'rgb(64 164 209 / 70%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.total == 3){
                $('td', row).css('background-color', '#ff8f003b');
                return;
            }
            if(data.total == 2 && detalleActual){
                $('td', row).css('background-color', 'rgb(64 164 209 / 40%)');
                return;
            }
            if(data.total == 2 && !detalleActual && parseFloat(data.diferencia) > 0){
                $('td', row).css('background-color', 'rgb(255 0 0 / 45%)');
                return;
            }
            if(data.total == 1){
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
            if (
                (!isNaN(data.errores) && parseInt(data.errores) > 0) || 
                (typeof data.errores === 'string' && data.errores !== '0' && data.errores.includes('La cuenta'))
            ) {
                $('td', row).css('background-color', 'red');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.total == 2){
                // $('td', row).css('background-color', 'rgb(64 164 209 / 55%)');
                // $('td', row).css('font-weight', 'bold');
                return;
            }
        },
        "columns": [
            { data: 'mes'},
            { data: 'year'},
            { data: 'comprobantes'},
            { data: 'registros', className: 'dt-body-right'},
            { data: 'fecha_manual'},
            { data: 'numero_documento'},
            { data: 'nombre_nit'},
            { data: 'documentos'},
            { data: 'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                var diferencia = parseFloat(row.diferencia);
                if(row.total == 0 && (diferencia > 0 || diferencia < 0)){
                    return '<div class=""><i class="fas fa-exclamation-triangle error-triangle"></i>&nbsp;'+(diferencia).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')+'</div>';
                }
                return diferencia.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }, className: 'dt-body-right'},
            { data: 'concepto'},
            { data: 'errores', className: 'dt-body-right'}
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

    actualizarColumnasEstadoActual();
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
    url+= '?month='+$('#month_estado_actual').val();
    url+= '?id_comprobante='+$('#id_comprobante_estado_actual').val();
    url+= '?detalle='+getDellarEstadoActual();
    url+= '&generar='+generarEstadoActual;

    estado_actual_table.ajax.url(url).load(function(res) {
        if(res.success) {
            actualizarColumnasEstadoActual();
            agregarToast('info', 'Generando estado actual', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });

});

function actualizarColumnasEstadoActual() {
    const nivel = parseInt(getDellarEstadoActual());
    
    const columnFechaManual = estado_actual_table.column(4);
    const columnCedula = estado_actual_table.column(5);
    const columnNit = estado_actual_table.column(6);
    const columnConcepto = estado_actual_table.column(11);

    if (nivel == 1) {
        columnFechaManual.visible(true);
        columnCedula.visible(true);
        columnNit.visible(true);
        columnConcepto.visible(true);
    } else {
        columnFechaManual.visible(false);
        columnCedula.visible(false);
        columnNit.visible(false);
        columnConcepto.visible(false);
    }
}

function loadEstadoActualById(id_estado_actual) {
    estado_actual_table.ajax.url(base_url + 'estado-actual-show?id='+id_estado_actual).load(function(res) {
        if(res.success){
            $("#generarEstadoActual").show();
            $("#generarEstadoActualLoading").hide();
            agregarToast('exito', 'Estado actual cargados', res.mensaje, true);
        }
    });
}

function getDellarEstadoActual() {
    if($("input[type='radio']#detalle_estado_actual0").is(':checked')) return '0';
    if($("input[type='radio']#detalle_estado_actual1").is(':checked')) return '1';

    return '0';
}