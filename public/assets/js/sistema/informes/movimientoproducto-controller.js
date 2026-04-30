var movimiento_producto_table = null;

function movimientoproductoInit() {
    cargasFechasGenerales("fecha_manual_movimiento_producto");
    cargarTablasMovimientoProducto();
    cargarCombosMovimientoProducto();
}

function cargarTablasMovimientoProducto() {
    var newLenguaje = lenguajeDatatable;
    newLenguaje.sInfo = "Movimientos productos del _START_ al _END_ de un total de _TOTAL_ ";

    movimiento_producto_table = $('#movimientoProductoInformeTable').DataTable({
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
        'rowCallback': function(row, data, index){
            if (data.detalle == '') {
                $('td', row).css('background-color', 'rgb(51, 132, 158)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'movimiento-producto',
            data: function ( d ) {
                d.id_cliente = $('#id_cliente_movimiento_producto').val();
                d.id_producto = $('#id_producto_movimiento_producto').val();
                d.fecha_desde = $('#fecha_manual_movimiento_producto').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                d.fecha_hasta = $('#fecha_manual_movimiento_producto').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                d.tipo_informe = $('#tipo_informe_movimiento_producto').val();
            }
        },
        columns: [
            {"data": "id_producto",
                render: function (type, set, row){
                    if (row.producto) {
                        return `${row.producto.codigo} - ${row.producto.nombre}`;
                    }
                    return 'Sin identificar';
                }
            },
            {
                data: "cantidad_anterior",
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: 'dt-body-right'
            },
            { 
                data: "cantidad",
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: 'dt-body-right'
            },
            {
                data: "cantidad",
                render: function (type, set, row) {
                    const tipo = obtenerTipoMovimiento(row);
                    const anterior = parseFloat(row.cantidad_anterior);
                    const cantidad = parseFloat(row.cantidad);

                    switch (tipo) {
                        case 'creacion':
                        case 'compra':
                        case 'nota_credito':
                        case 'cargue':
                            return anterior + cantidad;
                        case 'venta':
                        case 'descargue':
                        case 'traslado':
                            return anterior - cantidad;
                        default:
                            return anterior;
                    }
                },
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: 'dt-body-right'
            },
            {
                data: "tipo_tranferencia",
                render: function (type, set, row) {
                    const tipo = obtenerTipoMovimiento(row);
                    let badgeClass = 'bg-secondary';
                    let texto = 'Desconocido';

                    switch (tipo) {
                        case 'creacion':
                            badgeClass = 'bg-success';
                            texto = 'Creación';
                            break;
                        case 'compra':
                            badgeClass = 'bg-primary';
                            texto = 'Compra';
                            break;
                        case 'venta':
                            badgeClass = 'bg-danger';
                            texto = 'Venta';
                            break;
                        case 'nota_credito':
                            badgeClass = 'bg-info';
                            texto = 'Nota Crédito';
                            break;
                        case 'cargue':
                            badgeClass = 'bg-success';
                            texto = 'Cargue';
                            break;
                        case 'descargue':
                            badgeClass = 'bg-warning';
                            texto = 'Descargue';
                            break;
                        case 'traslado':
                            badgeClass = 'bg-secondary';
                            texto = 'Traslado';
                            break;
                        case 'movimiento_inventario':
                            badgeClass = 'bg-dark';
                            texto = 'Mov. Inventario';
                            break;
                    }
                    return `<span class="badge ${badgeClass}" title="${texto}">${texto}</span>`;
                }
            },
            {"data": "created_at",
                render: function (type, set, row){
                    var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                    if(!row.updated_by && !row.fecha_edicion) return '';
                    if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                    return html;
                }
            },
    
        ]
    });

    movimiento_producto_table.ajax.reload();
}

function cargarCombosMovimientoProducto() {
    $('#id_cliente_movimiento_producto').select2({
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

    $('#id_producto_movimiento_producto').select2({
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
}

function obtenerTipoMovimiento(row) {
    const relationType = parseInt(row.relation_type);
    
    if (relationType === 1) return 'creacion';
    if (relationType === 3) return 'compra';
    if (relationType === 4) {
        if (row.relation && row.relation.codigo_tipo_documento_dian == '91') {
            return 'nota_credito';
        }
        return 'venta';
    }
    if (relationType === 5) {
        const tipoTransferencia = parseInt(row.tipo_tranferencia);
        if (tipoTransferencia === 1) return 'cargue';
        if (tipoTransferencia === 2) return 'descargue';
        if (tipoTransferencia === 3) return 'traslado';
        return 'movimiento_inventario';
    }

    return 'desconocido';
}

$(document).on('click', '#generarMovimientoProducto', function () {
    $("#generarMovimientoProducto").hide();
    $("#generarMovimientoProductoLoading").show();
    movimiento_producto_table.ajax.reload(function (res) {
        $("#generarMovimientoProducto").show();
        $("#generarMovimientoProductoLoading").hide();
    });
});
