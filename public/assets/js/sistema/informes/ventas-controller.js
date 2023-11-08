var ventas_table = null;

function ventasInit() {
    
    ventas_table = $('#VentasInformeTable').DataTable({
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
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'ventas',
        },
        columns: [
            {"data":'documento_referencia'},
            {"data": function (row, type, set){  
                if (row.cliente) {
                    return row.cliente.nombre_completo;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.bodega) {
                    return row.bodega.codigo+' - '+row.bodega.nombre;
                }
                return '';
            }},
            {"data":'fecha_manual'},
            {"data": "subtotal", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "total_iva", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
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
                    html+= '<span id="imprimirventa_'+row.id+'" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-venta" style="margin-bottom: 0rem !important; color: black;">PDF</span>';
                    return html;
    
                }
            }
    
        ]
    });

    // $('#id_comprobante_venta').select2({
    //     theme: 'bootstrap-5',
    //     delay: 250,
    //     ajax: {
    //         url: 'api/comprobantes/combo-comprobante',
    //         headers: headers,
    //         dataType: 'json',
    //         processResults: function (data) {
    //             return {
    //                 results: data.data
    //             };
    //         }
    //     }
    // });

    $('#id_cliente_ventas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Cliente",
        allowClear: true,
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

    $('.water').hide();
    ventas_table.ajax.reload();
}

$(document).on('click', '.imprimir-venta', function () {
    var id = this.id.split('_')[1];
    window.open("/ventas-print/"+id, "_blank");
});

$(document).on('click', '#generarVentas', function () {

    $("#generarVentas").hide();
    $("#generarVentasLoading").show();

    var url = base_url + 'ventas';
    url+= '?id_cliente='+$('#id_cliente_ventas').val();
    url+= '&fecha_manual='+$('#fecha_manual_ventas').val();
    url+= '&consecutivo='+$('#consecutivo_ventas').val();

    ventas_table.ajax.url(url).load(function(res) {
        $("#generarVentas").show();
        $("#generarVentasLoading").hide();
    });
});
