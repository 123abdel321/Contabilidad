var fechaDesde = null;
var compras_table = null;

function comprasInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_manual_desde_compras').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_manual_hasta_compras').val(fechaDesde);
    
    compras_table = $('#ComprasInformeTable').DataTable({
        pageLength: 30,
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
            url: base_url + 'compras',
            data: function ( d ) {
                d.id_proveedor = $('#id_proveedor_compras').val();
                d.fecha_desde = $('#fecha_manual_desde_compras').val();
                d.fecha_hasta = $('#fecha_manual_hasta_compras').val();
                d.factura = $('#factura_compras').val();
                d.id_comprobante = $('#id_comprobante_compras').val();
                d.id_bodega = $('#id_bodega_compras').val();
                d.id_producto = $('#id_producto_compras').val();
                d.id_usuario = $('#id_usuario_ventas').val();
                d.detallar_compra = $("input[type='radio']#detallar_compra1").is(':checked') ? 'si' : 'no';
            }
        },
        columns: [
            {"data":'documento_referencia'},
            {"data": "nombre_completo"},
            {"data": "nombre_bodega"},
            {"data":'fecha_manual'},
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
                    if(row.anulado == 1) {
                        return ''
                    }
                    var html = '';
                    html+= '<span id="imprimircompra_'+row.id+'" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-compra" style="margin-bottom: 0rem !important; color: black;">PDF</span>';
                    return html;
    
                }
            }
    
        ]
    });

    $('#id_proveedor_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un proveedor",
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

    $('#id_bodega_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una bodega",
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

    $('#id_comprobante_compras').select2({
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
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 2,
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

    $('#id_usuario_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un usuario",
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

    $('#id_producto_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un producto",
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
    compras_table.ajax.reload();
}

$(document).on('click', '.imprimir-compra', function () {
    var id = this.id.split('_')[1];
    window.open("/compras-print/"+id, "_blank");
});

$(document).on('click', '#generarCompras', function () {

    $("#generarCompras").hide();
    $("#generarComprasLoading").show();

    var url = base_url + 'compras';
    url+= '?id_proveedor='+$('#id_proveedor_compras').val();
    url+= '&fecha_manual='+$('#fecha_manual_compras').val();
    url+= '&consecutivo='+$('#consecutivo_compras').val();

    compras_table.ajax.url(url).load(function(res) {
        $("#generarCompras").show();
        $("#generarComprasLoading").hide();
    });
});

$('input[type=radio][name=detallar_compra]').change(function() {
    $("#generarCompras").hide();
    $("#generarComprasLoading").show();
    if($("input[type='radio']#detallar_compra1").is(':checked')){
        compras_table.column(4).visible(true);
        compras_table.column(5).visible(true);
        compras_table.column(6).visible(true);
        compras_table.column(8).visible(true);
        compras_table.column(10).visible(true);
    } else {
        compras_table.column(4).visible(false);
        compras_table.column(5).visible(false);
        compras_table.column(6).visible(false);
        compras_table.column(8).visible(false);
        compras_table.column(10).visible(false);
    }
    compras_table.ajax.reload(function () {
        $("#generarCompras").show();
        $("#generarComprasLoading").hide();
    });
});