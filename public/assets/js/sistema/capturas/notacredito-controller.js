var fecha = null;
var idNotaCreditoFactura = null;
var guardandoNotaCredito = false;
var totalReteFuente = 0;
var totalFacturaNotaCredito = 0;
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
                    var cantidadActual = parseInt(row.data.cantidad);
                    var cantidadDevuelta = parseInt(row.data.cantidad_devuelta);
                    if ((cantidadActual - cantidadDevuelta) > 0) {
                        return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_cantidad_${row.id_factura_detalle}" onkeypress="calcularNotaCreditoCantidad(event, ${row.id_factura_detalle})" onfocusout="calcularNotaCreditoCantidadOut(${row.id_factura_detalle})" value="${row.cantidad_devuelta}">`;
                    }
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_cantidad_${row.id_factura_detalle}" value="0" disabled>`;
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

            {//OBSERVACIÓN
                "data": function (row, type, set, col){
                    var totalActual = parseFloat(row.data.total);
                    var totalDevuelta = parseFloat(row.data.total_devuelto);
                    var cantidadActual = parseInt(row.data.cantidad);
                    var cantidadDevuelta = parseInt(row.data.cantidad_devuelta);
                    if ((totalActual - totalDevuelta) > 0 || (cantidadActual - cantidadDevuelta) > 0) {
                        return `<input type="text" class="form-control form-control-sm" style="min-width: 200px; text-align: right;" id="nota_credito_observacion_${row.id_factura_detalle}">`;
                    }
                    return `<input type="text" class="form-control form-control-sm" style="min-width: 200px; text-align: right;" id="nota_credito_observacion_${row.id_factura_detalle}" disabled>`;
                }
            },
            {//TOTAL DEVOLUCIÓN
                "data": function (row, type, set, col){
                    var totalActual = parseFloat(row.data.total);
                    var totalDevuelta = parseFloat(row.data.total_devuelto);
                    if ((totalActual - totalDevuelta) > 0) {
                        return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_total_${row.id_factura_detalle}" onkeypress="calcularNotaCreditoTotal(event, ${row.id_factura_detalle})" value="${row.total_devolucion}">`;
                    }
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_total_${row.id_factura_detalle}" value="0" disabled>`;
                }
            },
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
                return `<input type="number" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" onfocus="focusFormaPagoNotaCredito(${row.id})" onfocusout="calcularNotaCreditoPagos(${row.id})" onkeypress="changeFormaPagoNotaCredito(${row.id}, event)" id="nota_credito_forma_pago_${row.id}" value="0">`;
            }},
        ],
    });

    nota_credito_table_facturas = $('#facturaDevolucionTable').DataTable({
        pageLength: 15,
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
            url: base_url + 'facturas',
            headers: headers,
            data: function ( d ) {
                d.id_cliente = $('#id_cliente_nota_credito').val();
                d.id_bodega = $('#id_bodega_nota_credito').val();
                // d.id_resolucion = $('#id_resolucion_nota_credito').val();
                // d.consecutivo = $("#consecutivo_nota_credito").val();
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
            
            idNotaCreditoFactura = dataFactura.id;
            totalReteFuente = dataFactura.total_rete_fuente;
            totalFacturaNotaCredito = dataFactura.total_factura,

            $('#iniciarCapturaNotaCredito').hide();
            $('#iniciarCapturaNotaCreditoLoading').show();

            $("#modalFacturasDevolucion").modal('hide');

            if(dataFactura.cliente){
                var dataCliente = {
                    id: dataFactura.cliente.id,
                    text: dataFactura.cliente.nombre_completo
                };
                var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
                $comboClienteNotaCredito.append(newOption).trigger('change');
                $comboClienteNotaCredito.val(dataCliente.id).trigger('change');
            }

            if(dataFactura.bodega){
                var dataBodega = {
                    id: dataFactura.bodega.id,
                    text: dataFactura.bodega.nombre
                };
                var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
                $comboBodegaNotaCredito.append(newOption).trigger('change');
                $comboBodegaNotaCredito.val(dataBodega.id).trigger('change');
            }

            $comboClienteNotaCredito.prop('disabled', true);
            $comboBodegaNotaCredito.prop('disabled', true);

            nota_credito_table.rows().remove().draw();
            mostrarValoresNotaCredito();

            var form = document.querySelector('#notaCreditoFilterForm');

            if(!form.checkValidity()) form.classList.add('was-validated');

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

                    $('#iniciarCapturaNotaCreditoLoading').hide();
                    $('#cancelarCapturaNotaCredito').show();

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
                }
            }).fail((err) => {
                $('#iniciarCapturaNotaCreditoLoading').hide();
                $('#crearCapturaNotaCreditoDisabled').hide();
                $('#iniciarCapturaNotaCredito').show();

                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Creación errada', errorsMsg);
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
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_resoluciones: [3],
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

function clearFormasPagoNotaCredito() {
    var dataFormasPago = nota_credito_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#nota_credito_forma_pago_'+formaPago.id).val(0);
        }
    }
    calcularNotaCreditoPagos();
}

$(document).on('click', '#iniciarCapturaNotaCredito', function () {
    nota_credito_table_facturas.ajax.reload();
    $("#modalFacturasDevolucion").modal('show');
});

$("#id_resolucion_nota_credito").on('change', function(event) {
    consecutivoSiguienteNotaCredito();
});

function consecutivoSiguienteNotaCredito() {
    var id_resolucion = $('#id_resolucion_nota_credito').val();
    var fecha_manual = $('#fecha_manual_nota_credito').val();

    $('#consecutivo_nota_credito_loading').show();

    if(id_resolucion && fecha_manual) {

        let data = {
            id_resolucion: id_resolucion,
            fecha_manual: fecha_manual
        }

        $.ajax({
            url: base_url + 'consecutivo',
            method: 'GET',
            data: data,
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            $('#consecutivo_nota_credito_loading').hide();
            if(res.success){
                $("#consecutivo_nota_credito").val(res.data);
            }
        }).fail((err) => {
            $('#consecutivo_nota_credito_loading').hide();

            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function calcularNotaCreditoCantidad(event, idDetalle) {
    if(event.keyCode == 13){
        calcularNotaCreditoPorCantidad(idDetalle);
        clearFormasPagoNotaCredito();
    }
}

function calcularNotaCreditoCantidadOut(idDetalle) {
    calcularNotaCreditoPorCantidad(idDetalle);
    clearFormasPagoNotaCredito();
}

function calcularNotaCreditoPorCantidad (idDetalle) {
    [dataRow, keyRow] = dataTableFactura(idDetalle);

    var cantidadDevolucion = $('#nota_credito_cantidad_'+idDetalle).val();
    var dataDetalle = dataRow.data;
    var cantidadDisponible = dataDetalle.cantidad - dataDetalle.cantidad_devuelta;

    if (cantidadDevolucion > cantidadDisponible) {
        cantidadDevolucion = cantidadDisponible;
        setTimeout(function(){
            $('#nota_credito_cantidad_'+idDetalle).val(cantidadDisponible);
            $('#nota_credito_cantidad_'+idDetalle).focus();
            $('#nota_credito_cantidad_'+idDetalle).select();
        },10);
    }

    var totalDisponible = (dataDetalle.total - dataDetalle.total_devuelto);
    var descuentoProporcion = (dataDetalle.costo * (dataDetalle.descuento_porcentaje / 100));
    var ivaProporcion = (dataDetalle.costo - descuentoProporcion) * (dataDetalle.iva_porcentaje / 100);
    var costoCantidad = dataDetalle.costo - descuentoProporcion + ivaProporcion;
    costoCantidad = costoCantidad * cantidadDevolucion;
    costoCantidad = costoCantidad > totalDisponible ? totalDisponible : costoCantidad;

    var proporcion = costoCantidad / totalDisponible;

    var descuento = 0;
    if (dataDetalle.descuento_porcentaje > 0) {
        descuento = dataDetalle.descuento_valor * proporcion;
        dataRow.descuento_valor = dataDetalle.descuento_valor - descuento;
    }

    var iva = 0;
    if (dataDetalle.iva_porcentaje > 0) {
        iva = dataDetalle.iva_valor * proporcion;
        dataRow.valor_iva = dataDetalle.iva_valor - iva;
    }

    var subtotal = costoCantidad;

    if (ivaIncluidoNotaCredito) {
        subtotal-= iva;
    }

    dataRow.cantidad_devuelta = cantidadDevolucion;
    dataRow.cantidad = cantidadDisponible - cantidadDevolucion,
    dataRow.devolucion_total = subtotal;
    dataRow.total_devolucion = subtotal;
    dataRow.total_disponible = ((dataDetalle.total - dataDetalle.total_devuelto) - (subtotal));
    console.log('dataRow:',dataRow);
    nota_credito_table.row(keyRow).data(dataRow).draw();

    mostrarValoresNotaCredito();
}

function calcularNotaCreditoTotal(event, idDetalle) {
    if(event.keyCode == 13){
        clearFormasPagoNotaCredito();
        
        [dataRow, keyRow] = dataTableFactura(idDetalle);
        
        var totalDevolucion = parseFloat($('#nota_credito_total_'+idDetalle).val());
        var dataDetalle = dataRow.data;
        var totalDisponible = parseFloat(dataDetalle.total) - parseFloat(dataDetalle.total_devuelto);

        if (totalDevolucion > totalDisponible) {
            totalDevolucion = totalDisponible;
            setTimeout(function(){
                $('#nota_credito_total_'+idDetalle).val(totalDisponible);
                $('#nota_credito_total_'+idDetalle).focus();
                $('#nota_credito_total_'+idDetalle).select();
            },10);
        }

        var proporcion = totalDevolucion / dataRow.data.total;

        var descuento = 0;
        if (dataDetalle.descuento_porcentaje > 0) {
            descuento = dataDetalle.descuento_valor * proporcion;
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

        mostrarValoresNotaCredito();
    }
}

function mostrarValoresNotaCredito() {
    var [iva, retencion, descuento, total, valorBruto, productos] = totalValoresNotaCredito();

    if (descuento) $('#totales_descuento_nota_credito').show();
    else $('#totales_descuento_nota_credito').hide();

    if (retencion) $('#totales_retencion_nota_credito').show();
    else $('#totales_retencion_nota_credito').hide();

    if (productos) $('#totales_productos_nota_credito').show();
    else $('#totales_productos_nota_credito').hide();

    $("#nota_credito_total_iva").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(iva));
    $("#nota_credito_total_descuento").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(descuento));
    $("#nota_credito_total_productos").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(productos));
    $("#nota_credito_total_retencion").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(retencion));
    $("#nota_credito_total_valor").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total));
    $("#nota_credito_sub_total").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valorBruto));
    document.getElementById('total_faltante_nota_credito').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
}

function totalValoresNotaCredito() {
    var iva = retencion = descuento = total = productos = 0;
    var valorBruto = 0;
    var dataNotaCredito = nota_credito_table.rows().data();
    var totalDisponible = 0;

    if(dataNotaCredito.length > 0) {
        $("#crearCapturaNotaCreditoDisabled").hide();

        for (let index = 0; index < dataNotaCredito.length; index++) {
            const notaCredito = dataNotaCredito[index];

            if (notaCredito.total_devolucion) {
                var proporcion = notaCredito.total_devolucion / notaCredito.data.total;
                totalDisponible+= parseFloat(notaCredito.data.total) - parseFloat(notaCredito.data.total_devuelto);
                productos+= parseInt(notaCredito.cantidad_devuelta);
                total+= notaCredito.total_devolucion;
                valorBruto+= notaCredito.total_devolucion;
                
                if (notaCredito.data.descuento_porcentaje > 0) {
                    descuento+= notaCredito.data.descuento_valor * proporcion;
                    valorBruto-= notaCredito.data.descuento_valor * proporcion;
                }
    
                if (notaCredito.data.iva_porcentaje > 0) {
                    console.log(notaCredito.data.iva_valor);
                    iva+= notaCredito.data.iva_valor * proporcion;
                    if (ivaIncluidoNotaCredito){
                        valorBruto-= notaCredito.data.iva_valor * proporcion;
                    }
                }

                if (totalReteFuente) {
                    retencion+= totalReteFuente * proporcion;
                    total-= totalReteFuente * proporcion;
                }
            }
        }

    } else {
        $("#crearCapturaNotaCredito").hide();
        $("#crearCapturaNotaCreditoDisabled").show();
    }

    if (total > 0 && !guardandoNotaCredito) {
        $("#crearCapturaNotaCredito").show();
        $("#crearCapturaNotaCreditoDisabled").hide();
    } else if (!guardandoNotaCredito){
        $("#crearCapturaNotaCredito").hide();
        $("#crearCapturaNotaCreditoDisabled").show();
    }

    return [iva, retencion, descuento, total, valorBruto, productos];
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

function focusFormaPagoNotaCredito(idFormaPago) {
    var [iva, retencion, descuento, total, subtotal] = totalValoresNotaCredito();
    var totalPagos = totalFormasPagoNotasCredito(idFormaPago);
    
    var totalFactura = total - totalPagos;

    // if (anticipo) {
    //     if ((totalAnticiposDisponibles - totalAnticipos) < totalFactura) {
    //         $('#nota_credito_forma_pago_'+idFormaPago).val(totalAnticiposDisponibles - totalAnticipos);
    //         $('#nota_credito_forma_pago_'+idFormaPago).select();
    //         return;
    //     }
    // }

    $('#nota_credito_forma_pago_'+idFormaPago).val(totalFactura);
    $('#nota_credito_forma_pago_'+idFormaPago).select();
}

function totalFormasPagoNotasCredito(idFormaPago = null) {

    var totalPagos = 0;
    var dataPagoNotaCredito = nota_credito_table_pagos.rows().data();

    if(dataPagoNotaCredito.length > 0) {
        for (let index = 0; index < dataPagoNotaCredito.length; index++) {
            
            var ventaPago = parseFloat($('#nota_credito_forma_pago_'+dataPagoNotaCredito[index].id).val());
            if (idFormaPago && idFormaPago == dataPagoNotaCredito[index].id) continue;
            totalPagos+= ventaPago;
        }
    }

    return totalPagos;
}

function calcularNotaCreditoPagos(idFormaPago = null) {

    if (
        $('#nota_credito_forma_pago_'+idFormaPago).val() == '' ||
        $('#nota_credito_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#nota_credito_forma_pago_'+idFormaPago).val(0);
    }

    $('#total_faltante_nota_credito').removeClass("is-invalid");

    var [iva, retencion, descuento, total, subtotal] = totalValoresNotaCredito();
    var totalPagos = totalFormasPagoNotasCredito();
    var totalFaltante = total - totalPagos;

    if (idFormaPago && totalFaltante < 0) {
        var totalPagoSinActual = totalFormasPagoNotasCredito(idFormaPago);
        $('#nota_credito_forma_pago_'+idFormaPago).val(total - totalPagoSinActual);
        $('#nota_credito_forma_pago_'+idFormaPago).select();
        return;
    }

    var totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    document.getElementById('total_pagado_nota_credito').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalPagos);
    document.getElementById('total_faltante_nota_credito').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalFaltante);
}

function changeFormaPagoNotaCredito(idFormaPago, event) {
    if(event.keyCode == 13){

        calcularNotaCreditoPagos(idFormaPago);

        var totalPagos = totalFormasPagoNotasCredito();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresNotaCredito();

        if (!total) {
            return;
        }

        if (totalPagos >= total) {
            validateSaveNotaCredito();
            return;
        }
        
        focusNextFormasPagoNotasCredito(idFormaPago);
    }
}

function focusNextFormasPagoNotasCredito(idFormaPago) {
    var dataNotaCreditoPagos = nota_credito_table_pagos.rows().data();
    var idFormaPagoFocus = dataNotaCreditoPagos[0].id;
    var obtenerFormaPago = false;

    if(!dataNotaCreditoPagos.length > 0) return;

    for (let index = 0; index < dataNotaCreditoPagos.length; index++) {
        const dataPagoNotaCredito = dataNotaCreditoPagos[index];
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoNotaCredito.id;
            obtenerFormaPago = false;
        } else if (dataPagoNotaCredito.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoNotaCredito(idFormaPagoFocus);
}

function focusFormaPagoNotaCredito(idFormaPago) {
    var [iva, retencion, descuento, total, subtotal] = totalValoresNotaCredito();
    var totalPagos = totalFormasPagoNotasCredito(idFormaPago);
    var totalFactura = total - totalPagos;

    $('#nota_credito_forma_pago_'+idFormaPago).val(totalFactura < 0 ? 0 : totalFactura);
    $('#nota_credito_forma_pago_'+idFormaPago).select();
}

function validateSaveNotaCredito() {
    $('#total_faltante_nota_credito_text').css("color","#484848");
    $('#total_faltante_nota_credito').css("color","#484848");

    if (!guardandoNotaCredito) {

        var [iva, retencion, descuento, total, subtotal] = totalValoresNotaCredito();
        var totalPagos = totalFormasPagoNotasCredito();

        if (totalPagos >= total) {
            guardandoNotaCredito = true;
            saveNotaCredito(); 
        } else {
            $('#total_faltante_nota_credito_text').css("color","red");
            $('#total_faltante_nota_credito').css("color","red");
            return;
        }
    }
}

$(document).on('click', '#crearCapturaNotaCredito', function () {
    validateSaveNotaCredito();
});

$(document).on('click', '#cancelarCapturaNotaCredito', function () {
    cancelarNotaCredito();
});

function saveNotaCredito() {
    
    $("#iniciarCapturaNotaCredito").hide();
    $("#iniciarCapturaNotaCreditoLoading").hide();
    $("#crearCapturaNotaCreditoDisabled").hide();
    $("#crearCapturaNotaCredito").hide();
    $("#cancelarCapturaNotaCredito").hide();
    $('#crearCapturaNotaCreditoLoading').show();

    let data = {
        id_factura: idNotaCreditoFactura,
        pagos: getNotasCreditoPagos(),
        productos: getProductosNotaCredito(),
        id_resolucion: $("#id_resolucion_nota_credito").val(),
        fecha_manual: $("#fecha_manual_nota_credito").val(),
        documento_referencia: $("#consecutivo_nota_credito").val(),
    }

    $.ajax({
        url: base_url + 'nota-credito',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoNotaCredito = false;
        if(res.success){
            // if(res.impresion) {
            //     window.open("/compras-print/"+res.impresion, "", "_blank");
            // }
            idNotaCreditoFactura = null;
            $('#iniciarCapturaNotaCredito').hide();
            $('#iniciarCapturaNotaCreditoLoading').hide();
            $('#consecutivo_nota_credito').val('');

            var totalRows = nota_credito_table.rows().data().length;
            for (let index = 0; index < totalRows; index++) {
                nota_credito_table.row(0).remove().draw();
            }

            mostrarValoresNotaCredito();
            agregarToast('exito', 'Creación exitosa', 'Nota Credito creada con exito!', true);
            cancelarNotaCredito();
        } else {
            $("#agregarNotaCredito").show();
            $("#crearCapturaNotaCredito").show();
            $("#iniciarCapturaNotaCredito").hide();
            $("#cancelarCapturaNotaCredito").show();
            $("#crearCapturaNotaCreditoDisabled").hide();
            $("#iniciarCapturaNotaCreditoLoading").hide();
            
            var mensaje = res.mensages;
            var errorsMsg = "";
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += "- "+errores[campo]+" <br>";
                }
            };
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    }).fail((err) => {
        guardandoNotaCredito = false;
        $("#agregarNotaCredito").show();
        $("#crearCapturaNotaCredito").show();
        $("#iniciarCapturaNotaCredito").hide();
        $("#cancelarCapturaNotaCredito").show();
        $("#crearCapturaNotaCreditoLoading").hide();
        $("#crearCapturaNotaCreditoDisabled").hide();
        $("#iniciarCapturaNotaCreditoLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function cancelarNotaCredito() {
    idNotaCreditoFactura = 0;
    var totalRows = nota_credito_table.rows().data().length;

    if(nota_credito_table.rows().data().length){
        nota_credito_table.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            nota_credito_table.row(0).remove().draw();
        }
        mostrarValoresNotaCredito();
    }

    $comboBodegaNotaCredito.val('').change();
    $comboClienteNotaCredito.val('').change();
    $comboBodegaNotaCredito.prop('disabled', false);
    $comboClienteNotaCredito.prop('disabled', false);
    $comboClienteNotaCredito.prop('disabled', false);

    $('#consecutivo_nota_credito').val('');

    $('#iniciarCapturaNotaCredito').show();
    $("#crearCapturaNotaCredito").hide();
    $("#cancelarCapturaNotaCredito").hide();
    $("#crearCapturaNotaCreditoLoading").hide();
    $("#crearCapturaNotaCreditoDisabled").hide();
    $("#iniciarCapturaNotaCreditoLoading").hide();

    consecutivoSiguienteNotaCredito();
}

function getProductosNotaCredito() {
    var data = [];

    var dataNotaCredito = nota_credito_table.rows().data();
    if(dataNotaCredito.length > 0){
        for (let index = 0; index < dataNotaCredito.length; index++) {

            const notaCredito = dataNotaCredito[index];
            var cantidadDevuelta = parseInt(notaCredito.cantidad_devuelta);
            var totalDevuelto = parseInt(notaCredito.total_devolucion);

            if (cantidadDevuelta || totalDevuelto) {
                data.push({
                    id_producto: notaCredito.id_producto,
                    id_factura_detalle: notaCredito.id_factura_detalle,
                    cantidad: notaCredito.cantidad_devuelta,
                    total_devolucion: notaCredito.total_devolucion
                });
            }
        }
    }
    return data;
}

function getNotasCreditoPagos() {
    var data = [];

    var dataNotaCreditoPagos = nota_credito_table_pagos.rows().data();

    if(!dataNotaCreditoPagos.length > 0) return data;

    for (let index = 0; index < dataNotaCreditoPagos.length; index++) {
        const datapagoCompraNotaCredito = dataNotaCreditoPagos[index];
        var pagoCompraNotaCredito = $('#nota_credito_forma_pago_'+datapagoCompraNotaCredito.id).val();
        if (pagoCompraNotaCredito > 0) {
            data.push({
                id: datapagoCompraNotaCredito.id,
                valor: pagoCompraNotaCredito
            });
        }
    }

    return data;
}