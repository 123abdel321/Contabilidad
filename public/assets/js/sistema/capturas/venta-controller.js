var fecha = null;
var venta_table = null;
var venta_table_pagos = null;
var validarFacturaVenta = null;
var idVentaProducto = 0;
var $comboResolucion = null;
var $comboCliente = null;
var porcentajeRetencionVenta = 0;
var topeRetencionVenta = 0;

function ventaInit () {

    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_venta').val(fecha);

    venta_table = $('#ventaTable').DataTable({
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
            {//BORRAR
                "data": function (row, type, set, col){
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteProductoVenta(${idVentaProducto})" id="delete-producto-venta_${idVentaProducto}"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            {//PRODUCTO
                "data": function (row, type, set, col){
                    return `<select class="form-control form-control-sm venta_producto combo-grid" id="venta_producto_${idVentaProducto}" onchange="changeProductoVenta(${idVentaProducto})" onfocusout="calcularProductoVenta(${idVentaProducto})"></select>`;
                },
            },
            {//EXISTENCIAS
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_existencia_${idVentaProducto}" disabled>`;
                }
            },
            {//CANTIDAD
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_cantidad_${idVentaProducto}" min="1" value="1" onkeydown="CantidadVentakeyDown(${idVentaProducto}, event)" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//COSTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_costo_${idVentaProducto}" value="0" onkeydown="CostoVentakeyDown(${idVentaProducto}, event)" style="min-width: 100px;" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//% DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_descuento_porcentaje_${idVentaProducto}" value="0"  onkeydown="DescuentoVentakeyDown(${idVentaProducto}, event)" maxlength="2" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_descuento_valor_${idVentaProducto}" value="0" onkeydown="DescuentoTotalVentakeyDown(${idVentaProducto}, event)" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//% IVA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_iva_porcentaje_${idVentaProducto}" value="0" onkeydown="IvaVentakeyDown(${idVentaProducto}, event)" maxlength="2" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_iva_valor_${idVentaProducto}" value="0" onkeydown="IvaTotalkeyDown(${idVentaProducto}, event)" disabled>`;
                }
            },
            {//TOTAL
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_total_${idVentaProducto}" value="0" disabled>`;
                }
            },
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#ventaTable').on('draw.dt', function() {
                $('.venta_producto').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    minimumInputLength: 1,
                    ajax: {
                        url: 'api/producto/combo-producto',
                        headers: headers,
                        data: function (params) {
                            var query = {
                                q: params.term,
                                id_bodega: $("#id_bodega_venta").val(),
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
                    },
                    templateResult: formatProducto,
                    templateSelection: formatRepoSelection
                });
            });

            function formatProducto (repo) {

                if (repo.loading) return repo.text;

                var urlImagen = repo.imagen ?
                    bucketUrl+repo.imagen :
                    '/img/sin_imagen.png';

                var inventario = repo.inventarios.length > 0 ? 
                    repo.inventarios[0].cantidad+' Existencias' :
                    'Sin inventario';

                var color = repo.inventarios.length > 0 ?
                    repo.inventarios[0].cantidad <= 0 ? 
                    '#a30000' : '#1c4587' :
                    '#838383';

                var $container = $(`
                    <div class="row">
                        <div class="col-3" style="display: flex; justify-content: center; align-items: center; padding-left: 0px;">
                            <img
                                style="width: 40px; border-radius: 10%;"
                                src="${urlImagen}" />
                        </div>
                        <div class="col-9" style="padding-left: 0px !important">
                            <div class="row">
                                <div class="col-12" style="padding-left: 0px !important">
                                    <h6 style="font-size: 14px; margin-bottom: 0px; color: black;">${repo.text}</h6>
                                </div>
                                <div class="col-12" style="padding-left: 0px !important">
                                    <i class="fas fa-box-open" style="font-size: 11px; color: ${color};"></i>
                                    ${inventario}
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                return $container;
            }

            function formatRepoSelection (repo) {
                return repo.full_name || repo.text;
            }
        }
    });

    venta_table_pagos = $('#ventaFormaPago').DataTable({
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
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){
                return `<input type="number" class="form-control form-control-sm" style="min-width: 100px;" value="0" onfocus="focusFormaPago(${row.id})" onfocusout="calcularVentaPagos(${row.id})" id="venta_forma_pago_${row.id}">`;
            }},
        ],
    });

    $comboCliente = $('#id_cliente_venta').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    $comboResolucion = $('#id_resolucion_venta').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    $comboBodega = $('#id_bodega_venta').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    if(primeraResolucionVenta){
        var dataResolucion = {
            id: primeraResolucionVenta.id,
            text: primeraResolucionVenta.prefijo + ' - ' + primeraResolucionVenta.nombre
        };
        var newOption = new Option(dataResolucion.text, dataResolucion.id, false, false);
        $comboResolucion.append(newOption).trigger('change');
        $comboResolucion.val(dataResolucion.id).trigger('change');
    }

    if(primeraBodegaVenta){
        var dataBodega = {
            id: primeraBodegaVenta.id,
            text: primeraBodegaVenta.codigo + ' - ' + primeraBodegaVenta.nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodega.append(newOption).trigger('change');
        $comboBodega.val(dataBodega.id).trigger('change');
    }

    setTimeout(function(){
        $comboCliente.select2("open");
    },10);
}

$('#id_cliente_venta').on('select2:close', function(event) {
    var data = $(this).select2('data');
    if(data.length){
        setTimeout(function(){
            $('#id_resolucion_venta').focus();
            $comboResolucion.select2("open");
        },10);
    }
});

$('#id_resolucion_venta').on('select2:close', function(event) {
    var data = $(this).select2('data');
    if(data.length){
        setTimeout(function(){
            $('#fecha_manual_venta').focus();
            $('#fecha_manual_venta').select();
        },10);
    }
});

$("#fecha_manual_venta").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        setTimeout(function(){
            $('#documento_referencia_venta').focus();
            $('#documento_referencia_venta').select();
        },10);
    }
});

$("#documento_referencia_venta").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        document.getElementById('iniciarCapturaVenta').click();
    }
});

function buscarFacturaVenta(event) {

    if (validarFacturaVenta) {
        validarFacturaVenta.abort();
    }
    
    $('#documento_referencia_venta_loading').show();

    var botonPrecionado = event.key.length == 1 ? event.key : '';
    var documento_referencia = $('#documento_referencia_venta').val()+''+botonPrecionado;
    
    $('#documento_referencia_venta').removeClass("is-invalid");
    $('#documento_referencia_venta').removeClass("is-valid");

    if(event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
    
    setTimeout(function(){
        validarFacturaVenta = $.ajax({
            url: base_url + 'existe-factura',
            method: 'GET',
            data: {documento_referencia: documento_referencia},
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarFacturaVenta = null;
            $('#documento_referencia_venta_loading').hide();
            if(res.data == 0){
                $('#documento_referencia_venta').removeClass("is-invalid");
                $('#documento_referencia_venta').addClass("is-valid");
            }else {
                $('#documento_referencia_venta').removeClass("is-valid");
                $('#documento_referencia_venta').addClass("is-invalid");
                $("#error_documento_referencia_venta").text('La factura ');
            }
        }).fail((err) => {
            $('#documento_referencia_venta_loading').hide();
        });
    },100);
}

$(document).on('click', '#iniciarCapturaVenta', function () {
    var form = document.querySelector('#ventaFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        $("#error_documento_referencia_venta").text('El No. factura requerido');
        return;
    }
    
    $("#iniciarCapturaVenta").hide();
    $("#crearCapturaVenta").hide();
    $("#cancelarCapturaVenta").show();
    $("#crearCapturaVentaDisabled").show();

    addRowProductoVenta();
});

function addRowProductoVenta () {

    var rows = venta_table.rows().data();
    var totalRows = rows.length;
    var dataLast = rows[totalRows - 1];

    if (dataLast) {
        var cuentaLast = $('#venta_producto_'+dataLast.id).val();
        if (!cuentaLast) {
            $('#venta_producto_'+dataLast.id).select2('open');
            document.getElementById("card-venta").scrollLeft = 0;
            return;
        }
    }
    
    venta_table.row.add({
        "id": idVentaProducto,
        "cantidad": 1,
        "costo": 0,
        "porcentaje_descuento": 0,
        "valor_descuento": 0,
        "porcentaje_iva": 0,
        "valor_iva": 0,
        "valor_total": 0,
    }).draw(false)

    $('#card-venta').focus();
    document.getElementById("card-venta").scrollLeft = 0;

    $('#venta_producto_'+idVentaProducto).focus();
    $('#venta_producto_'+idVentaProducto).select2('open');
    idVentaProducto++;
}

function deleteProductoVenta (idRow) {
    let dataVenta = venta_table.rows().data();

    for (let row = 0; row < dataVenta.length; row++) {
        let element = dataVenta[row];
        if(element.id == idRow) {
            venta_table.row(row).remove().draw();
            if(!venta_table.rows().data().lengt){
                $("#crearCapturaVentaDisabled").show();
                $("#crearCapturaVenta").hide();
            }
        }
    }
    mostrarValoresVentas();
}

$(document).on('click', '#agregarVenta', function () {
    $('#agregarVenta').hide();
    $('#iniciarCapturaVentaLoading').show();
    setTimeout(function(){
        addRowProductoVenta();
        $('#agregarVenta').show();
        $('#iniciarCapturaVentaLoading').hide();
    },100);
});

function calcularProductoVenta (idRow) {
    var costoProducto = $('#venta_costo_'+idRow).val();
    var cantidadProducto = $('#venta_cantidad_'+idRow).val();
    var ivaProducto = $('#venta_iva_porcentaje_'+idRow).val();
    var descuentoProducto = $('#venta_descuento_porcentaje_'+idRow).val();
    var totalPorCantidad = 0;
    var totalIva = 0;
    var totalDescuento = 0;
    var totalProducto = 0;
    
    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    if (descuentoProducto > 0) {
        totalDescuento = totalPorCantidad * descuentoProducto / 100;
        $('#venta_descuento_valor_'+idRow).val(totalDescuento);
    }

    if (ivaProducto > 0) {
        totalIva = (totalPorCantidad - totalDescuento) * ivaProducto / 100;
        $('#venta_iva_valor_'+idRow).val(totalIva);
    }

    totalProducto = totalPorCantidad - totalDescuento + totalIva;
    $('#venta_total_'+idRow).val(totalProducto);

    mostrarValoresVentas ();
}

function mostrarValoresVentas () {
    var [iva, retencion, descuento, total] = totalValoresVentas();

    $("#venta_total_iva").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(iva));
    $("#venta_total_descuento").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(descuento));
    $("#venta_total_retencion").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(retencion));
    $("#venta_total_valor").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total));
}

function totalValoresVentas() {
    var iva = retencion = descuento = total = 0;
    var valorBruto = 0;
    var dataVenta = venta_table.rows().data();

    if(dataVenta.length > 0) {
        
        $("#crearCapturaVentaDisabled").hide();
        
        for (let index = 0; index < dataVenta.length; index++) {
            var producto = $('#venta_producto_'+dataVenta[index].id).val();
             
            if (producto) {
                var cantidad = $('#venta_cantidad_'+dataVenta[index].id).val();
                var costo = $('#venta_costo_'+dataVenta[index].id).val();
                var ivaSum = $('#venta_iva_valor_'+dataVenta[index].id).val();
                var totaSum = $('#venta_total_'+dataVenta[index].id).val();
                var descSum = $('#venta_descuento_valor_'+dataVenta[index].id).val();
    
                iva+= parseFloat(ivaSum ? ivaSum : 0);
                descuento+= parseFloat(descSum ? descSum : 0);
                total+= parseFloat(totaSum ? totaSum : 0);
                valorBruto+= (cantidad*costo) - descSum;
            }
        }
        if (total >= topeRetencionVenta) {
            retencion = porcentajeRetencionVenta ? (valorBruto * porcentajeRetencionVenta) / 100 : 0;
            total = total - retencion;
        }
    } else {
        $("#crearCapturaVenta").hide();
        $("#crearCapturaVentaDisabled").show();
    }

    if (total > 0) {
        $("#crearCapturaVenta").show();
        $("#crearCapturaVentaDisabled").hide();
    } else {
        $("#crearCapturaVenta").hide();
        $("#crearCapturaVentaDisabled").show();
    }

    return [iva, retencion, descuento, total];
}

function changeProductoVenta (idRow) {
    var data = $('#venta_producto_'+idRow).select2('data')[0];

    if (data.length == 0) return
    
    if (data.inventarios.length > 0) {
        var totalInventario = parseFloat(data.inventarios[0].cantidad);
        $("#venta_existencia_"+idRow).val(totalInventario);
        $("#venta_cantidad_"+idRow).attr({"max" : totalInventario});
    }

    if (data.familia.cuenta_venta_iva && data.familia.cuenta_venta_iva.impuesto) {
        $('#venta_iva_porcentaje_'+idRow).prop('disabled', false);
        $('#venta_iva_porcentaje_'+idRow).val(data.familia.cuenta_venta_iva.impuesto.porcentaje);
    }

    if (data.familia.cuenta_venta_retencion && data.familia.cuenta_venta_retencion.impuesto) {
        var impuestoPorcentaje = parseFloat(data.familia.cuenta_venta_retencion.impuesto.porcentaje);
        var topeValor = parseFloat(data.familia.cuenta_venta_retencion.impuesto.base);
        if (impuestoPorcentaje > porcentajeRetencionVenta) {
            porcentajeRetencionVenta = impuestoPorcentaje;
            topeRetencionVenta = topeValor;
        }
    }

    $('#venta_costo_'+idRow).val(parseFloat(data.precio));
    $('#venta_producto_'+idRow).select2('open');
    $('#venta_cantidad_'+idRow).prop('disabled', false);
    $('#venta_costo_'+idRow).prop('disabled', false);
    $('#venta_descuento_porcentaje_'+idRow).prop('disabled', false);
    $('#venta_descuento_valor_'+idRow).prop('disabled', false);
    $('#venta_iva_porcentaje_'+idRow).prop('disabled', false);
    
    document.getElementById('venta_texto_retencion').innerHTML = 'RETENCIÓN '+ porcentajeRetencionVenta+'%';
        
    calcularProductoVenta(idRow);
    
    setTimeout(function(){
        $('#venta_cantidad_'+idRow).focus();
        $('#venta_cantidad_'+idRow).select();
    },10);
}

function CantidadVentakeyDown (idRow, event) {
    if(event.keyCode == 13){

        var dataProductos = $('#venta_producto_'+idRow).select2("data");
        var existencias = $('#venta_existencia_'+idRow).val();
        var cantidad = $('#venta_cantidad_'+idRow).val();

        if (dataProductos[0].inventarios.length > 0 && cantidad > existencias) {
            $('#venta_cantidad_'+idRow).val(existencias);
            setTimeout(function(){
                $('#venta_cantidad_'+idRow).focus();
                $('#venta_cantidad_'+idRow).select();
            },10);

        } else {
            calcularProductoVenta(idRow);
            setTimeout(function(){
                $('#venta_costo_'+idRow).focus();
                $('#venta_costo_'+idRow).select();
            },10);
        }
    }
}

function CostoVentakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoVenta (idRow);
        setTimeout(function(){
            $('#venta_descuento_porcentaje_'+idRow).focus();
            $('#venta_descuento_porcentaje_'+idRow).select();
        },10);
    }
}

function DescuentoVentakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoVenta(idRow);
        setTimeout(function(){
            $('#venta_descuento_valor_'+idRow).focus();
            $('#venta_descuento_valor_'+idRow).select();
        },10);
    }
}

function DescuentoTotalVentakeyDown (idRow, event) {
    if(event.keyCode == 13){
        var descuentoProductoValor = $('#venta_descuento_valor_'+idRow).val();
        var costoProducto = $('#venta_costo_'+idRow).val();
        var cantidadProducto = $('#venta_cantidad_'+idRow).val();
        var porcentajeDescuento = descuentoProductoValor * 100 / (costoProducto * cantidadProducto);

        $('#venta_descuento_porcentaje_'+idRow).val(porcentajeDescuento);
        calcularProductoVenta(idRow);
        setTimeout(function(){
            $('#venta_iva_porcentaje_'+idRow).focus();
            $('#venta_iva_porcentaje_'+idRow).select();
        },10);
    }
}

function IvaVentakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoVenta(idRow);
        addRowProductoVenta();
    }
}

$(document).on('click', '#cancelarCapturaVenta', function () {
    cancelarVenta();
});

function cancelarVenta() {
    var totalRows = venta_table.rows().data().length;
    idVentaProducto = 0;
    if(venta_table.rows().data().length){
        venta_table.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            documento_general_table.row(0).remove().draw();
        }
        mostrarValoresVentas();
    }
}

function loadFormasPago() {
    var totalRows = venta_table_pagos.rows().data().length;
    idVentaProducto = 0;
    if(venta_table_pagos.rows().data().length){
        venta_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            venta_table_pagos.row(0).remove().draw();
        }
    }
    venta_table_pagos.ajax.reload();
}

$(document).on('click', '#crearCapturaVenta', function () {
    loadFormasPago();
    var [iva, retencion, descuento, total] = totalValoresVentas();
    $('#total_pagado_venta').val(0);
    $('#total_faltante_venta').val(total);
    $("#ventaFormModal").modal('show');
});

function focusFormaPago(idFormaPago) {
    var totalFaltante = parseFloat($('#total_faltante_venta').val());
    var pagoActual = parseInt($('#venta_forma_pago_'+idFormaPago).val());
    $('#venta_forma_pago_'+idFormaPago).val(totalFaltante + pagoActual);
    $('#venta_forma_pago_'+idFormaPago).select();
}

function calcularVentaPagos(idFormaPago) {
    $('#total_faltante_venta').removeClass("is-invalid");

    var [iva, retencion, descuento, total] = totalValoresVentas();
    var totalVentaPagos = totalFormasPagoVentas();
    
    if (totalVentaPagos > total) {
        var totalFaltanteActual = $('#total_faltante_venta').val();
        $('#venta_forma_pago_'+idFormaPago).val(totalFaltanteActual);
        $('#venta_forma_pago_'+idFormaPago).select();
    } else {
        $('#total_pagado_venta').val(totalVentaPagos);
        $('#total_faltante_venta').val(total - totalVentaPagos);
    }
}

function totalFormasPagoVentas() {
    var totalPago = 0;
    var dataPagoVenta = venta_table_pagos.rows().data();

    if(dataPagoVenta.length > 0) {
        for (let index = 0; index < dataPagoVenta.length; index++) {
            var ventaPago = parseFloat($('#venta_forma_pago_'+dataPagoVenta[index].id).val());
            totalPago+= ventaPago;
        }
    }

    return totalPago;
}

$(document).on('click', '#saveVenta', function () {
    var totalFaltante = $('#total_faltante_venta').val();
    $('#total_faltante_venta').removeClass("is-invalid");

    if(totalFaltante > 0){
        $('#total_faltante_venta').addClass("is-invalid");
        $('#total_faltante_venta').removeClass("is-valid");
        return;
    }

    saveVenta();
});

function saveVenta() {

    $("#saveVenta").hide();
    $("#saveVentaLoading").show();

    let data = {
        pagos: getVentasPagos(),
        productos: getProductosVenta(),
        id_bodega: $("#id_bodega_venta").val(),
        id_cliente: $("#id_cliente_venta").val(),
        fecha_manual: $("#fecha_manual_venta").val(),
        id_resolucion: $("#id_resolucion_venta").val(),
        documento_referencia: $("#documento_referencia_venta").val(),
        observacion: $("#observacion_venta").val(),
    }

    $.ajax({
        url: base_url + 'ventas',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            if(res.impresion) {
                window.open("/ventas-print/"+res.impresion, "", "_blank");
            }
            idVentaProducto = 0;
            $("#ventaFormModal").modal('hide');
            $('#iniciarCapturaVenta').hide();
            $('#iniciarCapturaVentaLoading').hide();
            $('#documento_referencia_venta').val('');

            var totalRows = venta_table.rows().data().length;
            for (let index = 0; index < totalRows; index++) {
                venta_table.row(0).remove().draw();
            }

            mostrarValoresVentas();
            agregarToast('exito', 'Creación exitosa', 'Venta creada con exito!', true);
            setTimeout(function(){
                $comboCliente.select2("open");
            },10);
        } else {
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
        $("#saveVenta").show();
        $("#saveVentaLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        if (typeof mensaje === 'object') {
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += field+": "+errores[campo]+" <br>";
                }
                agregarToast('error', 'Creación errada', errorsMsg);
            };
        } else {

            agregarToast('error', 'Creación errada', mensaje);
        }
    });

}

function getVentasPagos() {
    var data = [];

    var dataVentaPagos = venta_table_pagos.rows().data();

    if(!dataVentaPagos.length > 0) return data;

    for (let index = 0; index < dataVentaPagos.length; index++) {
        const dataPagoVenta = dataVentaPagos[index];
        var pagoVenta = $('#venta_forma_pago_'+dataPagoVenta.id).val();
        if (pagoVenta > 0) {
            data.push({
                id: dataPagoVenta.id,
                valor: pagoVenta
            });
        }
    }

    return data;
}

function getProductosVenta() {
    var data = [];

    var dataVentaProductos = venta_table.rows().data();

    if(!dataVentaProductos.length > 0) return data;

    for (let index = 0; index < dataVentaProductos.length; index++) {

        const id_row = dataVentaProductos[index].id;
        var id_producto = $('#venta_producto_'+id_row).val();
        var cantidad = $('#venta_cantidad_'+id_row).val();
        
        if (id_producto && cantidad) {
            var costo = $('#venta_costo_'+id_row).val();
            var descuento_porcentaje = $('#venta_descuento_porcentaje_'+id_row).val();
            var descuento_valor = $('#venta_descuento_valor_'+id_row).val();
            var iva_porcentaje = $('#venta_iva_porcentaje_'+id_row).val();
            var iva_valor = $('#venta_iva_valor_'+id_row).val();
            var total = $('#venta_total_'+id_row).val();

            data.push({
                id_producto: parseInt(id_producto),
                cantidad: parseInt(cantidad),
                costo: costo ? parseFloat(costo) : 0,
                subtotal: parseInt(cantidad) * parseFloat(costo),
                descuento_porcentaje: descuento_porcentaje ? parseFloat(descuento_porcentaje) : 0,
                descuento_valor: descuento_valor ? parseFloat(descuento_valor) : 0,
                iva_porcentaje: iva_porcentaje ? parseFloat(iva_porcentaje) : 0,
                iva_valor: iva_valor ? parseFloat(iva_valor) : 0,
                total: total ? parseFloat(total) : 0,
            });
        }
    }

    return data;
}