var initVentaAcumulada = true;
var ventas_acumuladas_table = null;
var ventasAcumuladasInformeChanel = pusher.subscribe('informe-ventas-acumuladas-'+localStorage.getItem("notificacion_code"));

function ventasacumuladasInit() {

    initTablesVentasAcumuladas();
    initCombosVentasAcumuladas();

    const start = moment().startOf("month");
    const end = moment().endOf("month");

    $("#fecha_manual_ventas_acumuladas").daterangepicker({
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

    formatoFecha(start, end, "fecha_manual_ventas_acumuladas");
    initVentaAcumulada = false;

    $('.water').hide();
}

function initTablesVentasAcumuladas() {
    ventas_acumuladas_table = $('#VentasAcumuladasInformeTable').DataTable({
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
            url: base_url + 'ventas-acumuladas',
            data: function ( d ) {
                d.id_tipo_informe = $('#id_tipo_informe_ventas_acumuladas').val();
                d.fecha_desde = $('#fecha_manual_ventas_acumuladas').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                d.fecha_hasta = $('#fecha_manual_ventas_acumuladas').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                d.documento_referencia = $('#factura_documentos_extracto').val();
                d.id_nit = $('#id_cliente_ventas_acumuladas').val();
                d.id_resolucion = $('#id_resolucion_ventas_acumuladas').val();
                d.id_bodega = $('#id_bodega_ventas_acumuladas').val();
                d.id_producto = $('#id_producto_ventas_acumuladas').val();
                d.id_usuario = $('#id_usuario_ventas_acumuladas').val();
                d.id_forma_pago = $('#id_forma_pago_ventas_acumuladas').val();
                d.detallar_venta = getDetalleVentasAcumuladas();
            }
        },
        columns: [
            { data: function (row, type, set){
                if(row.nivel == 1) {
                    return row.documento_referencia;
                }
                return '';
            }},
            { data: function (row, type, set){
                if(row.nivel != 1) {
                    return row.nombre_nit;
                }
                return '';
            }},
            { data: function (row, type, set){
                if(row.nivel != 1) {
                    return `${row.codigo_bodega} - ${row.nombre_bodega}`;
                }
                return '';
            }},
            { data: function (row, type, set){
                if(row.nivel != 1) {
                    return row.fecha_manual;
                }
                return '';
            }},
            { data: function (row, type, set){
                if(row.nivel != 1) {
                    return `${row.codigo_producto} - ${row.nombre_producto}`;
                }
                return '';
            }},
            { data: "cantidad", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "costo", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "subtotal", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "iva_porcentaje", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "iva_valor", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "descuento_porcentaje", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "descuento_valor", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "total", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data:'id'},
            { data: function (row, type, set){
                if(row.nivel == 1) {
                    return '';
                }
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            { data: function (row, type, set){
                if(row.nivel == 1) {
                    return '';
                }
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
        ],
        rowCallback: function(row, data, index){
            if(data.nivel == 1){
                $('td', row).css('background-color', '#000');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
        }
    });
}

function initCombosVentasAcumuladas() {
    $('#id_cliente_ventas_acumuladas').select2({
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

    $('#id_bodega_ventas_acumuladas').select2({
        theme: 'bootstrap-5',
        delay: 250,
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
            url: 'api/bodega/combo-bodega',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_usuario_ventas_acumuladas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Usuario",
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
            url: 'api/usuarios/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_producto_ventas_acumuladas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Producto",
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
            url: 'api/producto/combo-producto',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    captura: 'venta',
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_forma_pago_ventas_acumuladas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccionar forma de pago",
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
            url: 'api/forma-pago/combo-forma-pago',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_resolucion_ventas_acumuladas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Resolución",
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
            url: 'api/resoluciones/combo-resoluciones',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_resoluciones: [0, 1],
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

function getDetalleVentasAcumuladas() {
    if($("input[type='radio']#detallar_venta0").is(':checked')) return '';
    if($("input[type='radio']#detallar_venta1").is(':checked')) return 1;

    return '';
}

function loadVentasAcumuladasById(id_venta_acumulada) {
    $('#id_venta_acumulada_cargado').val(id_venta_acumulada);
    ventas_acumuladas_table.ajax.url(base_url + 'ventas-acumuladas-show?id='+id_venta_acumulada).load(function(res) {

        $("#generarVentasAcumuladas").show();
        $("#generarVentasAcumuladasLoading").hide();
        if(res.success){
            agregarToast('exito', 'Ventas acumuladas', 'Informe cargado con exito!', true);
        }
    });
}

function consultarVentaAcumulada() {
    if (initVentaAcumulada) {
        return;
    }

    $("#generarVentasAcumuladas").hide();
    $("#generarVentasAcumuladasLoading").show();

    let documento_referencia = "";
    if ($('#factura_documentos_extracto').val()) {
        documento_referencia = $('#factura_documentos_extracto').val();
    }

    var url = base_url + `ventas-acumuladas`;
    url+= `?id_tipo_informe=${$('#id_tipo_informe_ventas_acumuladas').val()}`;
    url+= `&fecha_desde=${$('#fecha_manual_ventas_acumuladas').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm')}`;
    url+= `&fecha_hasta=${$('#fecha_manual_ventas_acumuladas').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm')}`;
    url+= `&documento_referencia=${documento_referencia}`;
    url+= `&id_nit=${$('#id_cliente_ventas_acumuladas').val()}`;
    url+= `&id_resolucion=${$('#id_resolucion_ventas_acumuladas').val()}`;
    url+= `&id_bodega=${$('#id_bodega_ventas_acumuladas').val()}`;
    url+= `&id_producto=${$('#id_producto_ventas_acumuladas').val()}`;
    url+= `&id_usuario=${$('#id_usuario_ventas_acumuladas').val()}`;
    url+= `&id_forma_pago=${$('#id_forma_pago_ventas_acumuladas').val()}`;
    url+= `&detallar_venta=${getDetalleVentasAcumuladas()}`;

    ventas_acumuladas_table.ajax.url(url).load(function(res) {
        if(res.success) {
            if (res.time) {
                agregarToast('info', 'Generando venta acumulado', 'El informe se esta generando desde las '+res.time+' se le notificará cuando el informe esté generado...', false );
            } else {
                agregarToast('info', 'Generando venta acumulado', 'En un momento se le notificará cuando el informe esté generado...', true );
            }
        }
    });
}

$(document).on('click', '#generarVentasAcumuladas', function () {
    consultarVentaAcumulada();
});

ventasAcumuladasInformeChanel.bind('notificaciones', function(data) {
    console.log('notificaciones: ',data);
    if(data.tipo == "error"){
        $("#generarVentasAcumuladas").show();
        $("#generarVentasAcumuladasLoading").hide();
        agregarToast('error', 'Error al cargar informe', data.mensaje, false);
        return;
    }

    if(data.url_file){
        loadExcel(data);
        return;
    }

    if(data.id_venta_acumulada){
        $('#id_cliente_ventas_acumuladas').val(data.id_venta_acumulada);
        loadVentasAcumuladasById(data.id_venta_acumulada);
        return;
    }
});