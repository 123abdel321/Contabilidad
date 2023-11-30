var fecha = null;
var idNotaCreditoProducto = 0;
var facturaDevolucion = {
    fecha_manual: null,
    id_resolucion: null,
    consecutivo: null,
    observacion: null,
    productos: []
};

var nota_credito_table = null;
var nota_credito_table_pagos = null;
var nota_credito_table_facturas = null;

var $comboBodegaNotaCredito = null;
var $comboClienteNotaCredito = null;
var $comboResolucionNotaCredito = null;

function notacreditoInit () {
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_nota_credito').val(fecha);

    nota_credito_table = $('#notaCreditoTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
        deferLoading: 0,
        initialLoad: false,
        autoWidth: true,
        language: lenguajeDatatable,
        ordering: false,
        columns: [
            {"data":'descripcion'},
            {"data":'cantidad', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {//CANTIDAD DEVOLUCIÓN
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_cantidad_${row.id_factura_detalle}" onkeypress="calcularNotaCreditoCantidad(event, ${row.id_factura_detalle})" value="${row.cantidad_devuelta}">`;
                }
            },
            {"data":'costo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 80px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 100px;">
                            <span class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${parseInt(row.descuento_porcentaje)}%</span>
                            <input style="height: 30px; text-align: right;" type="text" class="form-control form-control-sm" value="${parseInt(row.descuento_valor)}" disabled>
                        </div>
                    </div>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 80px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 100px;">
                            <span class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${parseInt(row.porcentaje_iva)}%</span>
                            <input style="height: 30px; text-align: right;" type="text" class="form-control form-control-sm" value="${parseInt(row.valor_iva)}" disabled>
                        </div>
                    </div>`;
                }
            },
            {"data":'total_disponible', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {//TOTAL DEVOLUCIÓN
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_total_${row.id_factura_detalle}" onkeypress="calcularNotaCreditoTotal(event, ${row.id_factura_detalle})" value="${row.total_devolucion}">`;
                }
            },
            {//OBSERVACIÓN
                "data": function (row, type, set, col){
                    return `<input type="text" class="form-control form-control-sm" style="min-width: 200px; text-align: right;" id="nota_credito_observacion_${row.id_factura_detalle}">`;
                }
            },
            {"data":'devolucion_total', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
        ],
        fixedColumns : {
            left: 0,
            right : 1,
        },
        columnDefs: [{
            'orderable': false
        }],
    });

    nota_credito_table_pagos = $('#notaCreditoFormaPago').DataTable({
        dom: '',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scrollX: true,
        ordering: false,
        ajax:  {
            type: "GET",
            headers: headers,
            data: {
                type: 'ventas'
            },
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){
                var anticipos = false;
                var className = '';
                if (row.cuenta.tipos_cuenta.length > 0) {
                    var tiposCuentas = row.cuenta.tipos_cuenta;
                    for (let index = 0; index < tiposCuentas.length; index++) {
                        const tipoCuenta = tiposCuentas[index];
                        if (tipoCuenta.id_tipo_cuenta == 8) {
                            anticipos = true;
                            className = 'anticipos'
                        }
                    }
                }
                return `<input type="number" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoNotaCredito(${row.id}, ${anticipos})" onfocusout="calcularNotaCreditoPagos(${row.id}, ${anticipos})" onkeypress="changeFormaPagoNotaCredito(${row.id}, ${anticipos}, event)" id="nota_credito_forma_pago_${row.id}">`;
            }},
        ],
    });

    nota_credito_table_facturas = $('#facturaDevolucionTable').DataTable({
        pageLength: 15,
        dom: '',
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
            url: base_url + 'facturas',
            headers: headers,
            data: function ( d ) {
                d.id_cliente = $('#id_cliente_nota_credito').val();
                d.id_bodega = $('#id_bodega_nota_credito').val();
                d.id_resolucion = $('#id_resolucion_nota_credito').val();
                d.consecutivo = $("#consecutivo_nota_credito").val();
            }
        },
        columns: [
            {
                "data": function (row, type, set){
                    return row.cliente.nombre_completo;
                }
            },
            {"data":'consecutivo'},
            {"data":'total_factura', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    if (row.centro_costo) {
                        return row.centro_costo.codigo+' - '+row.centro_costo.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if (row.bodega) {
                        return row.bodega.codigo+' - '+row.bodega.nombre;
                    }
                    return '';
                }
            },
            {"data":'fecha_manual'},
            {
                "data": function (row, type, set){
                    var html = `<span href="javascript:void(0)" id="selectventanotacredito_${row.id}" class="btn badge bg-gradient-primary select-venta-nota-credito" style="margin-bottom: 0rem !important">Seleccionar</span>&nbsp;`;

                    return html;
                }
            }
        ]
    });

    if (nota_credito_table_facturas) {
        nota_credito_table_facturas.on('click', '.select-venta-nota-credito', function() {
            var id = this.id.split('_')[1];
            var dataFactura = getDataById(id, nota_credito_table_facturas);
            nota_credito_table.rows().remove().draw();
            $.ajax({
                url: base_url + 'nota-credito/factura-detalle',
                method: 'GET',
                data: {
                    id: dataFactura.id
                },
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    var facturaDetalle = res.data;
                    for (let index = 0; index < facturaDetalle.length; index++) {
                        const detalleVenta = facturaDetalle[index];
                        var data = {
                            data: detalleVenta,
                            id_factura_detalle: detalleVenta.id,
                            id_producto: detalleVenta.id_producto,
                            producto: detalleVenta.producto,
                            descripcion: detalleVenta.descripcion,
                            costo: detalleVenta.costo,
                            cantidad: detalleVenta.cantidad - detalleVenta.cantidad_devuelta,
                            subtotal: detalleVenta.subtotal,
                            total_disponible: detalleVenta.total - detalleVenta.total_devuelto,
                            descuento_valor: detalleVenta.descuento_valor,
                            descuento_porcentaje: detalleVenta.descuento_porcentaje,
                            porcentaje_iva: detalleVenta.iva_porcentaje,
                            valor_iva: detalleVenta.iva_valor,
                            cantidad_devuelta: 0,
                            total_devolucion: 0,
                            observacion: '',
                            devolucion_total: 0
                            
                        };
                        facturaDevolucion.productos.push(data);
                        nota_credito_table.row.add(data).draw();
                    }
                    $("#modalFacturasDevolucion").modal('hide');
                }
            }).fail((err) => {
            });

        });
    }

    $comboClienteNotaCredito = $('#id_cliente_nota_credito').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    $comboResolucionNotaCredito = $('#id_resolucion_nota_credito').select2({
        theme: 'bootstrap-5',
        delay: 250,
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
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboBodegaNotaCredito = $('#id_bodega_nota_credito').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    loadFormasPagoNotasCredito();
}

function loadFormasPagoNotasCredito() {
    var totalRows = nota_credito_table_pagos.rows().data().length;
    if(nota_credito_table_pagos.rows().data().length){
        nota_credito_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            nota_credito_table_pagos.row(0).remove().draw();
        }
    }
    nota_credito_table_pagos.ajax.reload();
}

$(document).on('click', '#iniciarCapturaNotaCredito', function () {
    nota_credito_table_facturas.ajax.reload();
    $("#modalFacturasDevolucion").modal('show');
});

function calcularNotaCreditoCantidad(event, idDetalle) {
    if(event.keyCode == 13){
        [dataRow, keyRow] = dataTableFactura(idDetalle);

        var cantidadDevolucion = $('#nota_credito_cantidad_'+idDetalle).val();
        var dataDetalle = dataRow.data;
        var cantidadDisponible = dataDetalle.cantidad - dataDetalle.cantidad_devuelta;

        if (cantidadDevolucion > cantidadDisponible) {
            setTimeout(function(){
                $('#nota_credito_cantidad_'+idDetalle).val(cantidadDisponible);
                $('#nota_credito_cantidad_'+idDetalle).focus();
                $('#nota_credito_cantidad_'+idDetalle).select();
            },10);
            return;
        }

        var costo = dataRow.total_disponible / (dataRow.cantidad - dataRow.cantidad_devuelta);

        var descuento = 0;
        if (dataDetalle.descuento_porcentaje > 0) {
            descuento = ((cantidadDevolucion * costo) * dataDetalle.descuento_porcentaje) / 100;
            dataRow.descuento_valor = dataDetalle.descuento_valor - descuento;
        }

        var iva = 0;
        if (dataDetalle.iva_porcentaje > 0) {
            iva = ((cantidadDevolucion * costo) * dataDetalle.iva_porcentaje / 100);
            dataRow.valor_iva = dataDetalle.iva_valor - iva;
        }

        var subtotal = (cantidadDevolucion * costo) - descuento;

        dataRow.cantidad_devuelta = cantidadDevolucion;
        dataRow.cantidad = cantidadDisponible - cantidadDevolucion,
        dataRow.devolucion_total = subtotal + iva;
        dataRow.total_disponible = (dataDetalle.total - dataDetalle.total_devuelto) - (subtotal + iva);

        nota_credito_table.row(keyRow).data(dataRow).draw();
    }
}

function calcularNotaCreditoTotal(event, idDetalle) {
    if(event.keyCode == 13){
        [dataRow, keyRow] = dataTableFactura(idDetalle);
        
        var totalDevolucion = $('#nota_credito_total_'+idDetalle).val();
        var proporcion = totalDevolucion / dataRow.data.total;
        var dataDetalle = dataRow.data;

        if (totalDevolucion > dataRow.total_disponible) {
            setTimeout(function(){
                $('#nota_credito_total_'+idDetalle).val(dataRow.total_disponible);
                $('#nota_credito_total_'+idDetalle).focus();
                $('#nota_credito_total_'+idDetalle).select();
            },10);
            return;
        }

        var descuento = 0;
        if (dataDetalle.descuento_porcentaje > 0) {
            descuento = dataDetalle.total_devuelto * proporcion;
            dataRow.descuento_valor = dataDetalle.descuento_valor - descuento;
        }

        var iva = 0;
        if (dataDetalle.iva_porcentaje > 0) {
            iva = dataDetalle.iva_valor * proporcion;
            dataRow.valor_iva = dataDetalle.iva_valor - iva;
        }

        var subtotal = totalDevolucion - iva;

        dataRow.devolucion_total = subtotal + iva;
        dataRow.total_devolucion = subtotal + iva;
        dataRow.total_disponible = (dataDetalle.total - dataDetalle.total_devuelto) - (subtotal + iva);

        nota_credito_table.row(keyRow).data(dataRow).draw();

        // mostrarValoresNotaCredito();
    }
}

function mostrarValoresNotaCredito() {
    var [iva, retencion, descuento, total, valorBruto] = totalValoresNotaCredito();
}

function totalValoresNotaCredito() {
    var iva = retencion = descuento = total = 0;
    var valorBruto = 0;
    var dataNotaCredito = nota_credito_table.rows().data();

    if(dataNotaCredito.length > 0) {

        $("#crearCapturaNotaCreditoDisabled").hide();

        for (let index = 0; index < dataNotaCredito.length; index++) {
        }

    } else {
        $("#crearCapturaNotaCredito").hide();
        $("#crearCapturaNotaCreditoDisabled").show();
    }

    if (total > 0) {
        $("#crearCapturaNotaCredito").show();
        $("#crearCapturaNotaCreditoDisabled").hide();
    } else {
        $("#crearCapturaNotaCredito").hide();
        $("#crearCapturaNotaCreditoDisabled").show();
    }

    return [iva, retencion, descuento, total, valorBruto];
}

function dataTableFactura(idDetalle) {
    var dataTable = nota_credito_table.rows().data();

    for (let index = 0; index < dataTable.length; index++) {
        const element = dataTable[index];
        if (element.id_factura_detalle == idDetalle) {
            return [element, index];
        }
    }

    return [[], false];
}

