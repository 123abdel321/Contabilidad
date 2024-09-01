var fechaDesde = null;
var ventas_table = null;

function ventasInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_manual_desde_ventas').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_manual_hasta_ventas').val(fechaDesde);

    var newLenguaje = lenguajeDatatable;
    newLenguaje.sInfo = "Ventas del _START_ al _END_ de un total de _TOTAL_ ";
    
    ventas_table = $('#VentasInformeTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        'rowCallback': function(row, data, index){
            if (data.detalle == '') {
                $('td', row).css('background-color', 'rgb(214 231 246)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'black');
                return;
            }
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'ventas',
            data: function ( d ) {
                d.id_cliente = $('#id_cliente_ventas').val();
                d.fecha_desde = $('#fecha_manual_desde_ventas').val();
                d.fecha_hasta = $('#fecha_manual_hasta_ventas').val();
                d.factura = $('#factura_ventas').val();
                d.id_resolucion = $('#id_resolucion_ventas').val();
                d.id_bodega = $('#id_bodega_ventas').val();
                d.id_producto = $('#id_producto_ventas').val();
                d.id_usuario = $('#id_usuario_ventas').val();
                d.detallar_venta = $("input[type='radio']#detallar_venta1").is(':checked') ? 'si' : 'no';
            }
        },
        columns: [
            {"data": "documento_referencia"},
            {"data": "nombre_completo"},
            {"data": "nombre_bodega"},
            {"data": "fecha_manual"},
            {"data": "descripcion"},
            {"data": "cantidad", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "costo", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "subtotal", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "iva_porcentaje", className: 'dt-body-right'},
            {"data": "total_iva", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "descuento_porcentaje", className: 'dt-body-right'},
            {"data": "total_descuento", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "total_rete_fuente", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "total_factura", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "nombre_vendedor"},
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
            {
                "data": function (row, type, set){
                    if (row.id) {
                        var html = '';
                        html+= '<span id="imprimirventa_'+row.id+'" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-venta" style="margin-bottom: 0rem !important; color: black; background-color: white !important;">Imprimir</span>';
                        return html;
                    }
                    return ''
                }
            }
    
        ]
    });

    $('#id_resolucion_ventas').select2({
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

    $('#id_cliente_ventas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Cliente",
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
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_bodega_ventas').select2({
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

    $('#id_usuario_ventas').select2({
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

    $('#id_producto_ventas').select2({
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

    $('.water').hide();
    ventas_table.ajax.reload(function (res) {
        showTotalsVentas(res);
    });
}

$(document).on('click', '.imprimir-venta', function () {
    var id = this.id.split('_')[1];
    window.open("/ventas-print/"+id, "_blank");
});

$(document).on('click', '#generarVentas', function () {
    $("#generarVentas").hide();
    $("#generarVentasLoading").show();
    ventas_table.ajax.reload(function (res) {
        showTotalsVentas(res);
        $("#generarVentas").show();
        $("#generarVentasLoading").hide();
    });
});

$('input[type=radio][name=detallar_venta]').change(function() {
    $("#generarVentas").hide();
    $("#generarVentasLoading").show();
    if($("input[type='radio']#detallar_venta1").is(':checked')){
        ventas_table.column(4).visible(true);
        ventas_table.column(5).visible(true);
        ventas_table.column(6).visible(true);
        ventas_table.column(8).visible(true);
        ventas_table.column(10).visible(true);
    } else {
        ventas_table.column(4).visible(false);
        ventas_table.column(5).visible(false);
        ventas_table.column(6).visible(false);
        ventas_table.column(8).visible(false);
        ventas_table.column(10).visible(false);
    }
    ventas_table.ajax.reload(function (res) {
        showTotalsVentas(res);
        $("#generarVentas").show();
        $("#generarVentasLoading").hide();
    });
});

$(document).on('click', '#generarInformeZ', function () {
    var mensajeError = '';
    var errorCount = 0
    
    if (!$('#id_bodega_ventas').val()) {
        errorCount++;
        mensajeError+= errorCount+': La bogeda es requerida <br/>';
    }

    if (!$('#id_resolucion_ventas').val()) {
        errorCount++;
        mensajeError+= errorCount+': La resolución es requerida <br/>';
    }

    // if ($('#fecha_manual_desde_ventas').val() != $('#fecha_manual_hasta_ventas').val()) {
    //     errorCount++;
    //     mensajeError+= errorCount+': La fecha debe estar en el rango de un día <br/>';
    // }

    if (errorCount) {
        agregarToast('warning', 'Informe Z', mensajeError, true);
        return;
    }

    var url = base_web + 'ventas-print-informez';
    $('#fecha_manual_desde_ventas').val() ? url+= '?fecha_desde='+$('#fecha_manual_desde_ventas').val() : null;
    $('#fecha_manual_hasta_ventas').val() ? url+= '&fecha_hasta='+$('#fecha_manual_hasta_ventas').val() : null;
    $('#factura_ventas').val() ? url+= '&factura='+$('#factura_ventas').val() : null;
    $('#factura_ventas').val() ? url+= '&id_resolucion='+$('#factura_ventas').val() : null;
    $('#id_bodega_ventas').val() ? url+= '&id_bodega='+$('#id_bodega_ventas').val() : null;
    $('#id_resolucion_ventas').val() ? url+= '&id_resolucion='+$('#id_resolucion_ventas').val() : null;
    $('#id_producto').val() ? url+= '&id_producto='+$('#id_producto').val() : null;
    $('#id_usuario').val() ? url+= '&id_usuario='+$('#id_usuario').val() : null;
    url+= $("input[type='radio']#detallar_venta1").is(':checked') ? '&detallar_venta=1' : '&detallar_venta=0';

    window.open(url,'_blank');
});

function showTotalsVentas(res) {
    if (!res.success) return;

    var totales = res.totalesVenta[0];
    var totalesNotas = res.totalesNotas[0];
    var total_venta = totales.total_venta - totalesNotas.total_venta;
    var total_costo = totales.total_costo - totalesNotas.total_costo;
    var total_cantidad = totales.total_productos_cantidad - totalesNotas.total_productos_cantidad;
    var total_utilidad = total_venta - total_costo;
    var porcentaje_utilidad = (total_utilidad / totales.total_costo) * 100;

    var countA = new CountUp('total_productos_vendidos', 0, total_cantidad);
        countA.start();

    var countB = new CountUp('total_costo_ventas', 0, total_costo);
        countB.start();

    var countC = new CountUp('total_precio_ventas', 0, total_venta);
        countC.start();

    var countD = new CountUp('total_utilidad_ventas', 0, total_utilidad);
        countD.start();

    var countE = new CountUp('total_porcentaje_ventas', 0, parseFloat(porcentaje_utilidad).toFixed(2));
        countE.start();
}



