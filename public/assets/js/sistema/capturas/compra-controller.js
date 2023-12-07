var fecha = null;
var compra_table = null;
var compra_table_pagos = null;
var validarFacturaCompra = null;
var idCompraProducto = 0;
var $comboBodegaCompra = null;
var $comboProveedor = null;
var $comboComprobante  = null;
var guardarCompra = false
var porcentajeRetencionCompras = 0;
var topeRetencionCompras = 0;
var abrirFormasPagoCompras = false;
var guardandoCompra = false;

function compraInit () {
    
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_compra').val(fecha);

    compra_table = $('#compraTable').DataTable({
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
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteProductoCompra(${idCompraProducto})" id="delete-producto-compra_${idCompraProducto}"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            {//PRODUCTO
                "data": function (row, type, set, col){
                    return `<select class="form-control form-control-sm combo_producto combo-grid" id="combo_producto_${idCompraProducto}" onchange="changeProductoCompra(${idCompraProducto})" onfocusout="calcularProducto(${idCompraProducto})"></select>`;
                },
            },
            {//EXISTENCIAS
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="compra_existencia_${idCompraProducto}" disabled>`;
                }
            },
            {//CANTIDAD
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="compra_cantidad_${idCompraProducto}" min="1" value="1" onkeydown="CantidadkeyDown(${idCompraProducto}, event)" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//COSTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="compra_costo_${idCompraProducto}" value="0" onkeydown="CostokeyDown(${idCompraProducto}, event)" style="min-width: 100px;" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//% DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="compra_descuento_porcentaje_${idCompraProducto}" value="0"  onkeydown="DescuentokeyDown(${idCompraProducto}, event)" maxlength="2" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="compra_descuento_valor_${idCompraProducto}" value="0" onkeydown="DescuentoTotalkeyDown(${idCompraProducto}, event)" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 80px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 100px;">
                            <span id="compra_iva_porcentaje_text_${idCompraProducto}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">0%</span>
                            <input style="height: 30px; text-align: right;" type="text" class="form-control form-control-sm" value="0" id="compra_iva_valor_${idCompraProducto}" value="0" disabled>
                        </div>
                    </div>
                    <input type="number" class="form-control form-control-sm" style="min-width: 110px; display: none;" id="compra_iva_porcentaje_${idCompraProducto}" value="0"  onkeydown="DescuentoVentakeyDown(${idCompraProducto}, event)" maxlength="2" onfocusout="calcularProducto(${idCompraProducto})">`;
                }
            },
            {//TOTAL
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 90px; text-align: right;" id="compra_total_${idCompraProducto}" value="0" disabled>`;
                }
            },
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#compraTable').on('draw.dt', function() {
                $('.combo_producto').select2({
                    theme: 'bootstrap-5',
                    dropdownCssClass: 'custom-combo_producto',
                    delay: 250,
                    minimumInputLength: 1,
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
                                id_bodega: $("#id_bodega_compra").val(),
                                captura: 'compra',
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

            function formatProducto (producto) {
                if (producto.loading) return producto.text;

                var urlImagen = producto.imagen ?
                    bucketUrl+producto.imagen :
                    '/img/sin_imagen.png';

                var inventario = producto.inventarios.length > 0 ? 
                    producto.inventarios[0].cantidad+' Existencias' :
                    'Sin inventario';

                var color = producto.inventarios.length > 0 ?
                    producto.inventarios[0].cantidad <= 0 ? 
                    '#a30000' : '#1c4587' :
                    '#838383';

                var $container = '';

                if (producto.familia.inventario && ventaExistenciasCompra) {
                    var $container = $(`
                        <div class="row">
                            <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                                <img
                                    style="width: 40px; border-radius: 10%;"
                                        src="${urlImagen}" />
                                </div>
                                <div class="col-9" style="padding-left: 0px !important">
                                    <div class="row" style="margin-left: 5px;">
                                        <div class="col-12" style="padding-left: 0px !important">
                                            <h6 style="font-size: 12px; margin-bottom: 0px; color: black;">${producto.text}</h6>
                                        </div>
                                        <div class="col-12" style="padding-left: 0px !important">
                                            <i class="fas fa-box-open" style="font-size: 11px; color: ${color};"></i>
                                            ${inventario}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    } else {
                        var $container = $(`
                            <div class="row">
                                <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                                    <img
                                        style="width: 40px; border-radius: 10%;"
                                        src="${urlImagen}" />
                                </div>
                                <div class="col-9">
                                    <div class="row">
                                        <div class="col-12" style="padding-left: 0px !important">
                                            <h6 style="font-size: 12px; margin-bottom: 0px; color: black;">${producto.text}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    }


                return $container;
            }

            function formatRepoSelection (producto) {
                return producto.full_name || producto.text;
            }
        }
    });

    compra_table_pagos = $('#compraFormaPago').DataTable({
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
                type: 'compras'
            },
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){
                return `<input type="number" class="form-control form-control-sm" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoCompra(${row.id})" onfocusout="calcularCompraPagos()" onkeypress="changeFormaPagoCompra(${row.id}, event)" id="compra_forma_pago_${row.id}">`;
            }},
        ],
    });

    $comboComprobante = $('#id_comprobante_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    $comboProveedor = $('#id_proveedor_compra').select2({
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
                return "Debes ingresar más caracteres...";
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

    $comboBodegaCompra = $('#id_bodega_compra').select2({
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

    // $('#id_proveedor_compra').on('select2:close', function(event) {
    //     var data = $(this).select2('data');
    //     if(data.length){
    //         setTimeout(function(){
    //             $comboComprobante.select2("open");
    //             $('#id_comprobante_compra').focus();
    //         },10);
    //     }
    // });

    // $("#id_comprobante_compra").on('select2:close', function(event) {
    //     var data = $(this).select2('data');
    //     console.log('id_comprobante_compra close', data.length);
    //     if(data.length){
    //         setTimeout(function(){
    //             $comboBodegaCompra.select2("open");
    //             $('#id_bodega_compra').select();
    //         },10);
    //     }
    // });

    // $('#id_bodega_compra').on('select2:close', function(event) {
    //     var data = $(this).select2('data');
    //     if(data.length){
    //         setTimeout(function(){
    //             $('#fecha_manual_compra').focus();
    //             $('#fecha_manual_compra').select();
    //         },10);
    //     }
    // });

    $("#fecha_manual_compra").on('keydown', function(event) {
        if(event.keyCode == 13){
            event.preventDefault();
            validarFechaManualCompras();
            setTimeout(function(){
                $('#documento_referencia_compra').focus();
                $('#documento_referencia_compra').select();
            },10);
        }
    });

    function validarFechaManualCompras() {
        var fechaManual = $("#fecha_manual_compra").val();
    
        $('#fecha_manual_compra').removeClass("is-valid");
        $('#fecha_manual_compra').removeClass("is-invalid");
    
        if (!fechaManual) {
            $('#fecha_manual_compra').removeClass("is-valid");
            $('#fecha_manual_compra').addClass("is-invalid");
            $('#fecha_manual_compra-feedback').text('La Fecha manual es requerida')
            return;
        }
    
        $.ajax({
            url: base_url + 'anio-cerrado',
            method: 'GET',
            headers: headers,
            dataType: 'json',
        }).done((res) => {
    
            var fechaCierre = new Date(res.data).getTime();
            var fechaManual = new Date($("#fecha_manual_compra").val()).getTime();
    
            if (fechaManual <= fechaCierre) {
                $('#fecha_manual_compra').removeClass("is-valid");
                $('#fecha_manual_compra').addClass("is-invalid");
                $('#fecha_manual_compra-feedback').text('La Fecha se encuentra en un año cerrado');
            }
        }).fail((err) => {
        });
    
    }

    $("#documento_referencia_compra").on('keydown', function(event) {
        if(event.keyCode == 13){
            event.preventDefault();

            calcularCompraPagos();
            clearFormasPagoCompras();
            document.getElementById('iniciarCapturaCompra').click();
        }
    });

    $("#compraFilterForm").submit(function(event) {
        event.preventDefault();
    });

    if (!primeraBodegaCompra.length) {
        agregarToast('warning', 'Sin bodegas asignadas', '', true);
    }

    if(primeraBodegaCompra.length > 0){
        var dataBodega = {
            id: primeraBodegaCompra[0].id,
            text: primeraBodegaCompra[0].codigo + ' - ' + primeraBodegaCompra[0].nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodegaCompra.append(newOption).trigger('change');
        $comboBodegaCompra.val(dataBodega.id).trigger('change');
    }

    if(primerComprobanteCompra){
        var dataComprobante = {
            id: primerComprobanteCompra.id,
            text: primerComprobanteCompra.codigo + ' - ' + primerComprobanteCompra.nombre
        };
        var newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
        $comboComprobante.append(newOption).trigger('change');
        $comboComprobante.val(dataComprobante.id).trigger('change');
    }

    var column2 = compra_table.column(2);
    var column5 = compra_table.column(5);
    var column6 = compra_table.column(6);

    if (agregarDescuentoCompra){
        column5.visible(true);
        column6.visible(true);
    } else {
        column5.visible(false);
        column6.visible(false);
    }

    if (ventaExistenciasCompra) column2.visible(true);
    else column2.visible(false);

    loadFormasPagoCompra()

    setTimeout(function(){
        $comboProveedor.select2("open");
    },10);
}

function loadFormasPagoCompra() {
    var totalRows = compra_table_pagos.rows().data().length;
    if(compra_table_pagos.rows().data().length){
        compra_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            compra_table_pagos.row(0).remove().draw();
        }
    }
    compra_table_pagos.ajax.reload();
}

function focusFormaPagoCompra(idFormaPago) {
    var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
    var totalPagos = totalFormasPagoCompras(idFormaPago);
    var totalFactura = total - totalPagos;

    $('#compra_forma_pago_'+idFormaPago).val(totalFactura < 0 ? 0 : totalFactura);
    $('#compra_forma_pago_'+idFormaPago).select();
}

function calcularCompraPagos(idFormaPago = null) {

    $('#total_faltante_compra').removeClass("is-invalid");

    var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
    var totalPagos = totalFormasPagoCompras();
    var totalFaltante = total - totalPagos;

    if (idFormaPago && totalFaltante < 0) {
        var totalPagoSinActual = totalFormasPagoCompras(idFormaPago);
        $('#compra_forma_pago_'+idFormaPago).val(total - totalPagoSinActual);
        $('#compra_forma_pago_'+idFormaPago).select();
        return;
    }

    var totalPagado = totalFaltante < 0 ? total : totalPagos;
    var totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    document.getElementById('total_pagado_compra').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalPagado);
    document.getElementById('total_faltante_compra').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalFaltante);
}


function totalFormasPagoCompras(idFormaPago = null) {

    var totalPagos = 0;
    var dataPagoCompra = compra_table_pagos.rows().data();

    if(dataPagoCompra.length > 0) {
        for (let index = 0; index < dataPagoCompra.length; index++) {
            
            var ventaPago = parseFloat($('#compra_forma_pago_'+dataPagoCompra[index].id).val());

            if (idFormaPago && idFormaPago == dataPagoCompra[index].id) continue;
            totalPagos+= ventaPago;
        }
    }

    return totalPagos;
}

$(document).on('keydown', '.custom-combo_producto .select2-search__field', function (event) {
    var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

    if (event.keyCode == 96) {
        abrirFormasPagoCompras = true;
        setTimeout(function(){
            abrirFormasPagoCompras = false;
        },500);
    } else if (event.keyCode == 13){
        if (total > 0) {
            if (abrirFormasPagoCompras) {
                $(".combo_producto").select2('close');
                focusFormaPagoCompra(1);
                abrirFormasPagoCompras = false;
            }
        }
    } else {
        abrirFormasPagoCompras = false;
    }
});

function addRowProductoCompra () {

    var rows = compra_table.rows().data();
    var totalRows = rows.length;
    var dataLast = rows[totalRows - 1];

    if (dataLast) {
        var cuentaLast = $('#combo_producto_'+dataLast.id).val();
        if (!cuentaLast) {
            $('#combo_producto_'+dataLast.id).select2('open');
            document.getElementById("card-compra").scrollLeft = 0;
            return;
        }
    } else if(totalRows > 1) {
        clearFormasPagoCompras();
    }

    compra_table.row.add({
        "id": idCompraProducto,
        "cantidad": 1,
        "costo": 0,
        "porcentaje_descuento": 0,
        "valor_descuento": 0,
        "porcentaje_iva": 0,
        "valor_iva": 0,
        "valor_total": 0,
    }).draw(false)

    $('#card-compra').focus();
    document.getElementById("card-compra").scrollLeft = 0;

    $('#combo_producto_'+idCompraProducto).focus();
    $('#combo_producto_'+idCompraProducto).select2('open');
    idCompraProducto++;
}

function changeProductoCompra (idRow) {
    var data = $('#combo_producto_'+idRow).select2('data')[0];

    if (data.length == 0) return;
    
    if (data.inventarios.length > 0) {
        var totalInventario = parseInt(data.inventarios[0].cantidad);
        $("#compra_existencia_"+idRow).val(totalInventario);
    } else {
        $("#compra_existencia_"+idRow).val(0);
    }

    if (data.familia.cuenta_compra_iva && data.familia.cuenta_compra_iva.impuesto) {

        $('#compra_iva_porcentaje_'+idRow).val(data.familia.cuenta_compra_iva.impuesto.porcentaje);
        $('#compra_iva_porcentaje_text_'+idRow).text(parseInt(data.familia.cuenta_compra_iva.impuesto.porcentaje)+'%');
    }

    if (data.familia.cuenta_compra_retencion && data.familia.cuenta_compra_retencion.impuesto) {
        var impuestoPorcentaje = parseFloat(data.familia.cuenta_compra_retencion.impuesto.porcentaje);
        var topeValor = parseFloat(data.familia.cuenta_compra_retencion.impuesto.base);
        if (impuestoPorcentaje > porcentajeRetencionCompras) {
            porcentajeRetencionCompras = impuestoPorcentaje;
            topeRetencionCompras = topeValor;
        }
    }

    if (data.familia.id_cuenta_compra_descuento && agregarDescuentoCompra) {
        $('#compra_descuento_valor_'+idRow).prop('disabled', false);
        $('#compra_descuento_porcentaje_'+idRow).prop('disabled', false);
    } else {
        $('#compra_descuento_valor_'+idRow).prop('disabled', true);
        $('#compra_descuento_porcentaje_'+idRow).prop('disabled', true);
    }

    $('#compra_costo_'+idRow).val(parseFloat(data.precio_inicial));
    $('#combo_producto_'+idRow).select2('open');
    $('#compra_cantidad_'+idRow).prop('disabled', false);
    $('#compra_costo_'+idRow).prop('disabled', false);
    
    document.getElementById('compra_texto_retencion').innerHTML = 'RETENCIÓN '+ porcentajeRetencionCompras+'%'+'<br> BASE '+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(topeRetencionCompras);
        
    calcularProducto(idRow);
    clearFormasPagoCompras();
    
    setTimeout(function(){
        $('#compra_cantidad_'+idRow).focus();
        $('#compra_cantidad_'+idRow).select();
    },10);
}

function buscarFacturaCompra(event) {

    if (validarFacturaCompra) {
        validarFacturaCompra.abort();
    }

    var botonPrecionado = event.key.length == 1 ? event.key : '';
    var documento_referencia = $('#documento_referencia_compra').val()+''+botonPrecionado;

    if (event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
    if (!documento_referencia) return;
    
    $('#documento_referencia_compra_loading').show();
    
    $('#documento_referencia_compra').removeClass("is-invalid");
    $('#documento_referencia_compra').removeClass("is-valid");

    setTimeout(function(){
        validarFacturaCompra = $.ajax({
            url: base_url + 'existe-factura',
            method: 'GET',
            data: {
                id_comprobante: $("#id_comprobante_compra").val(),
                documento_referencia: documento_referencia
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarFacturaCompra = null;
            $('#documento_referencia_compra_loading').hide();
            if(res.data == 0){
                $('#documento_referencia_compra').removeClass("is-invalid");
                $('#documento_referencia_compra').addClass("is-valid");
            }else {
                $('#documento_referencia_compra').removeClass("is-valid");
                $('#documento_referencia_compra').addClass("is-invalid");
                $("#error_documento_referencia_compra").text('La factura No '+documento_referencia+' ya existe!');
            }
        }).fail((err) => {
            $('#documento_referencia_compra_loading').hide();
        });
    },100);
}

function CantidadkeyDown (idRow, event) {
    if(event.keyCode == 13){
        
        var cantidad = $('#compra_cantidad_'+idRow).val();

        if (cantidad <= 0) {
            $('#compra_cantidad_'+idRow).val(1);
            setTimeout(function(){
                $('#compra_cantidad_'+idRow).focus();
                $('#compra_cantidad_'+idRow).select();
            },10);
        } else {
            calcularProducto(idRow);
            setTimeout(function(){
                $('#compra_costo_'+idRow).focus();
                $('#compra_costo_'+idRow).select();
            },10);
        }
    }
}

function CostokeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProducto (idRow);
        addRowProductoCompra();
    }
}

function DescuentokeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProducto(idRow);
        setTimeout(function(){
            $('#compra_descuento_valor_'+idRow).focus();
            $('#compra_descuento_valor_'+idRow).select();
        },10);
    }
}

function DescuentoTotalkeyDown (idRow, event) {
    if(event.keyCode == 13){
        var descuentoProductoValor = $('#compra_descuento_valor_'+idRow).val();
        var costoProducto = $('#compra_costo_'+idRow).val();
        var cantidadProducto = $('#compra_cantidad_'+idRow).val();
        var porcentajeDescuento = descuentoProductoValor * 100 / (costoProducto * cantidadProducto);

        $('#compra_descuento_porcentaje_'+idRow).val(porcentajeDescuento);
        calcularProducto(idRow);
        setTimeout(function(){
            $('#compra_iva_porcentaje_'+idRow).focus();
            $('#compra_iva_porcentaje_'+idRow).select();
        },10);
    }
}

function IvakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProducto(idRow);
        addRowProductoCompra();
    }
}

$(document).on('click', '#cancelarCapturaCompra', function () {
    cancelarCompra();
});

function cancelarCompra() {
    idCompraProducto = 0;
    var totalRows = compra_table.rows().data().length;

    if(compra_table.rows().data().length){
        compra_table.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            compra_table.row(0).remove().draw();
        }
        mostrarValoresCompras();
    }

    $("#id_bodega_compra").prop('disabled', false);

    $('#agregarCompra').hide();
    $("#iniciarCapturaCompra").show();
    $('#agregarCompraProducto').hide();
    $("#crearCapturaCompra").hide();
    $("#cancelarCapturaCompra").hide();
    $("#crearCapturaCompraDisabled").hide();

    setTimeout(function(){
        $comboProveedor.select2("open");
    },10);
}

$(document).on('click', '#iniciarCapturaCompra', function () {
    var form = document.querySelector('#compraFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        $("#error_documento_referencia_compra").text('El No. factura requerido');
        return;
    }

    $("#id_bodega_compra").prop('disabled', true);
    
    $("#iniciarCapturaCompra").hide();
    $("#agregarCompra").show();
    $("#cancelarCapturaCompra").show();
    $("#crearCapturaCompraDisabled").show();

    addRowProductoCompra();
});

$(document).on('click', '#agregarCompra', function () {
    addRowProductoCompra();
});

$(document).on('click', '#crearCapturaCompra', function () {
    validateSaveCompra();
});

function saveCompra() {
    
    ocultarBotonesCabezaCompra();

    $('#iniciarCapturaCompraLoading').show();

    let data = {
        pagos: getComprasPagos(),
        productos: getProductosCompra(),
        id_proveedor: $("#id_proveedor_compra").val(),
        id_bodega: $("#id_bodega_compra").val(),
        id_comprobante: $("#id_comprobante_compra").val(),
        fecha_manual: $("#fecha_manual_compra").val(),
        documento_referencia: $("#documento_referencia_compra").val(),
    }
    $.ajax({
        url: base_url + 'compras',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoCompra = false;
        if(res.success){
            if(res.impresion) {
                window.open("/compras-print/"+res.impresion, "", "_blank");
            }
            idCompraProducto = 0;
            $('#iniciarCapturaCompra').hide();
            $('#iniciarCapturaCompraLoading').hide();
            $('#documento_referencia_compra').val('');

            var totalRows = compra_table.rows().data().length;
            for (let index = 0; index < totalRows; index++) {
                compra_table.row(0).remove().draw();
            }

            mostrarValoresCompras();
            agregarToast('exito', 'Creación exitosa', 'Compra creada con exito!', true);
            cancelarCompra();
        } else {
            $("#agregarCompra").show();
            $("#crearCapturaCompra").show();
            $("#iniciarCapturaCompra").hide();
            $("#cancelarCapturaCompra").show();
            $("#crearCapturaCompraDisabled").hide();
            $("#iniciarCapturaCompraLoading").hide();
            
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
        guardandoCompra = false;
        $("#agregarCompra").show();
        $("#crearCapturaCompra").show();
        $("#iniciarCapturaCompra").hide();
        $("#cancelarCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
        $("#iniciarCapturaCompraLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        for (field in mensaje) {
            var errores = mensaje[field];
            for (campo in errores) {
                errorsMsg += field+": "+errores[campo]+" <br>";
            }
            
        };
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function ocultarBotonesCabezaCompra () {
    $("#iniciarCapturaCompra").hide();
    $("#iniciarCapturaCompraLoading").hide();
    $("#agregarCompra").hide();
    $("#crearCapturaCompraDisabled").hide();
    $("#crearCapturaCompra").hide();
}

function getProductosCompra(){
    var data = [];

    var dataDocumento = compra_table.rows().data();
    if(dataDocumento.length > 0){
        for (let index = 0; index < dataDocumento.length; index++) {

            const id_row = dataDocumento[index].id;
            var id_producto = $('#combo_producto_'+id_row).val();
            var cantidad = $('#compra_cantidad_'+id_row).val();
            
            if (id_producto && cantidad) {
                var costo = $('#compra_costo_'+id_row).val();
                var descuento_porcentaje = $('#compra_descuento_porcentaje_'+id_row).val();
                var descuento_valor = $('#compra_descuento_valor_'+id_row).val();
                var iva_porcentaje = $('#compra_iva_porcentaje_'+id_row).val();
                var iva_valor = $('#compra_iva_valor_'+id_row).val();
                var total = $('#compra_total_'+id_row).val();

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
    }
    return data;
}

function calcularProducto (idRow) {
    var costoProducto = $('#compra_costo_'+idRow).val();
    var cantidadProducto = $('#compra_cantidad_'+idRow).val();
    var ivaProducto = $('#compra_iva_porcentaje_'+idRow).val();
    var descuentoProducto = $('#compra_descuento_porcentaje_'+idRow).val();
    var totalPorCantidad = 0;
    var totalIva = 0;
    var totalDescuento = 0;
    var totalProducto = 0;
    
    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    if (descuentoProducto > 0) {
        totalDescuento = totalPorCantidad * descuentoProducto / 100;
        $('#compra_descuento_valor_'+idRow).val(totalDescuento);
    }

    if (ivaProducto > 0) {
        totalIva = (totalPorCantidad - totalDescuento) * ivaProducto / 100;
        $('#compra_iva_valor_'+idRow).val(totalIva);
    }

    totalProducto = totalPorCantidad - totalDescuento + totalIva;
    $('#compra_total_'+idRow).val(totalProducto);

    mostrarValoresCompras ();
}

function mostrarValoresCompras () {
    var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

    if (descuento) $('#totales_descuento_compra').show();
    else $('#totales_descuento_compra').hide();

    if (retencion) $('#totales_retencion_compra').show();
    else $('#totales_retencion_compra').hide();

    $("#compra_total_iva").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(iva));
    $("#compra_total_descuento").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(descuento));
    $("#compra_total_retencion").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(retencion));
    $("#compra_total_valor").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total));
    $("#compra_sub_total").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valorBruto));
    document.getElementById('total_faltante_compra').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
}

function totalValoresCompras() {
    var iva = retencion = descuento = total = 0;
    var valorBruto = 0;
    var dataCompra = compra_table.rows().data();

    if(dataCompra.length > 0) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
        
        for (let index = 0; index < dataCompra.length; index++) {
            var producto = $('#combo_producto_'+dataCompra[index].id).val();
             
            if (producto) {
                var cantidad = $('#compra_cantidad_'+dataCompra[index].id).val();
                var costo = $('#compra_costo_'+dataCompra[index].id).val();
                var ivaSum = $('#compra_iva_valor_'+dataCompra[index].id).val();
                var totaSum = $('#compra_total_'+dataCompra[index].id).val();
                var descSum = $('#compra_descuento_valor_'+dataCompra[index].id).val();

                iva+= parseInt(ivaSum ? ivaSum : 0);
                descuento+= parseInt(descSum ? descSum : 0);
                total+= parseInt(totaSum ? totaSum : 0);
                valorBruto+= (cantidad*costo) - parseInt(descSum ? descSum : 0);
            }
        }
        if (total >= topeRetencionCompras) {
            retencion = porcentajeRetencionCompras ? (valorBruto * porcentajeRetencionCompras) / 100 : 0;
            total = total - retencion;
        }
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }

    return [iva, retencion, descuento, total, valorBruto];
}

function deleteProductoCompra (idRow) {
    let dataCompra = compra_table.rows().data();

    for (let row = 0; row < dataCompra.length; row++) {
        let element = dataCompra[row];
        if(element.id == idRow) {
            compra_table.row(row).remove().draw();
            if(!compra_table.rows().data().lengt){
                $("#crearCapturaCompraDisabled").show();
                $("#crearCapturaCompra").hide();
            }
        }
    }
    mostrarValoresCompras();
}

function changeFormaPagoCompra(idFormaPago, event) {
    if(event.keyCode == 13){

        calcularCompraPagos(idFormaPago);

        var totalPagos = totalFormasPagoCompras();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

        if (!total) {
            return;
        }

        if (totalPagos >= total) {
            validateSaveCompra();
            return;
        }
        
        focusNextFormasPagoCompras(idFormaPago);
    }
}

function focusNextFormasPagoCompras(idFormaPago) {
    var dataCompraPagos = compra_table_pagos.rows().data();
    var idFormaPagoFocus = dataCompraPagos[0].id;
    var obtenerFormaPago = false;

    if(!dataCompraPagos.length > 0) return;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoCompra = dataCompraPagos[index];
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoCompra.id;
            obtenerFormaPago = false;
        } else if (dataPagoCompra.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoCompra(idFormaPagoFocus);
}

function clearFormasPagoCompras() {
    var dataCompraPagos = compra_table_pagos.rows().data();

    if(!dataCompraPagos.length > 0) return;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoCompra = dataCompraPagos[index];
        $('#compra_forma_pago_'+dataPagoCompra.id).val(0);
    }

    calcularCompraPagos();
}

function getComprasPagos() {
    var data = [];

    var dataCompraPagos = compra_table_pagos.rows().data();

    if(!dataCompraPagos.length > 0) return data;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoCompra = dataCompraPagos[index];
        var pagoCompra = $('#compra_forma_pago_'+dataPagoCompra.id).val();
        if (pagoCompra > 0) {
            data.push({
                id: dataPagoCompra.id,
                valor: pagoCompra
            });
        }
    }

    return data;
}

function validateSaveCompra() {
    $('#total_faltante_compra_text').css("color","#484848");
    $('#total_faltante_compra').css("color","#484848");

    if (!guardandoCompra) {

        var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
        var totalPagos = totalFormasPagoCompras();

        if (totalPagos >= total) {
            guardandoCompra = true;
            saveCompra(); 
        } else {
            $('#total_faltante_compra_text').css("color","red");
            $('#total_faltante_compra').css("color","red");
            return;
        }
    }
}