var fecha = null;
var venta_table = null;
var venta_table_pagos = null;
var validarFacturaVenta = null;
var validarExistenciasProducto = null;
var idVentaProducto = 0;
var $comboBodegaVenta = null;
var $comboResolucion = null;
var $comboCliente = null;
var $comboVendedor = null;
var porcentajeRetencionVenta = 0;
var topeRetencionVenta = 0;
var guardarVenta = false;
var abrirFormasPagoVentas = false;
var key13PressNewRow = false;
var guardandoVenta = false;
var totalAnticiposDisponibles = 0;
var redondearFactura = false;
var createNewNit = false;

function ventaInit () {

    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_venta').val(fecha);

    venta_table = $('#ventaTable').DataTable({
        dom: '',
        pageLength: 200,
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
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="venta_existencia_${idVentaProducto}" disabled>`;
                }
            },
            {//CANTIDAD
                "data": function (row, type, set, col){
                    return `
                        <div class="input-group" style="height: 30px;">
                            <input type="number" class="form-control form-control-sm" style="min-width: 80px; border-right: solid 1px #b3b3b3; border-top-right-radius: 10px; border-bottom-right-radius: 10px; text-align: right;" id="venta_cantidad_${idVentaProducto}" min="1" value="1" onkeydown="cantidadVentakeyDown(${idVentaProducto}, event)" onfocus="focusCantidadVenta(${idVentaProducto})" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>
                            <i class="fa fa-spinner fa-spin fa-fw venta_producto_load" id="venta_producto_load_${idVentaProducto}" style="display: none;"></i>
                            <div id="venta_cantidad_text_${idVentaProducto}" style="position: absolute; margin-top: 30px; z-index: 9;" class="invalid-feedback">
                            </div>
                        </div>
                    `;
                }
            },
            {//COSTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="venta_costo_${idVentaProducto}" value="0" onkeydown="CostoVentakeyDown(${idVentaProducto}, event)" style="min-width: 100px;" onfocusout="calcularProductoVenta(${idVentaProducto})" onfocus="focusCostoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//% DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="venta_descuento_porcentaje_${idVentaProducto}" value="0"  onkeydown="DescuentoVentakeyDown(${idVentaProducto}, event)" onfocusout="calcularProductoVenta(${idVentaProducto})" onfocus="focusPDescuentoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="venta_descuento_valor_${idVentaProducto}" value="0" onkeydown="DescuentoTotalVentakeyDown(${idVentaProducto}, event)" onfocusout="calcularProductoVenta(${idVentaProducto})" onfocus="focusVDescuentoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 80px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                            <span id="venta_iva_porcentaje_text_${idVentaProducto}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">0%</span>
                            <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" value="0" id="venta_iva_valor_${idVentaProducto}" value="0" disabled>
                        </div>
                    </div>
                    <input type="number" class="form-control form-control-sm" style="min-width: 110px; display: none;" id="venta_iva_porcentaje_${idVentaProducto}" value="0"  onkeydown="DescuentoVentakeyDown(${idVentaProducto}, event)" onfocusout="calcularProductoVenta(${idVentaProducto})">`;
                }
            },
            {//TOTAL
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="venta_total_${idVentaProducto}" value="0" disabled>`;
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
                    dropdownCssClass: 'custom-venta_producto',
                    delay: 250,
                    minimumInputLength: 2,
                    language: {
                        noResults: function() {
                            return "No hay resultado";        
                        },
                        searching: function() {
                            return "Buscando..";
                        },
                        inputTooShort: function () {
                            return "Por favor introduce 2 o más caracteres";
                        }
                    },
                    ajax: {
                        url: 'api/producto/combo-producto',
                        headers: headers,
                        data: function (params) {
                            var query = {
                                q: params.term,
                                id_bodega: $("#id_bodega_venta").val(),
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
                    },
                    templateResult: formatProducto,
                    templateSelection: formatRepoSelection
                });
                
                $("input[data-type='currency']").on({
                    keyup: function(event) {
                        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
                            formatCurrency($(this));
                        }
                    },
                    blur: function() {
                        formatCurrency($(this), "blur");
                    }
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

                if (producto.familia.inventario && ventaExistencias) {
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
                                        <h6 style="font-size: 12px; margin-bottom: 0px; color: black; margin-left: 10px;">${producto.text}</h6>
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
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoVenta(${row.id}, ${anticipos})" onfocusout="calcularVentaPagos(${row.id}, ${anticipos})" onkeypress="changeFormaPago(${row.id}, ${anticipos}, event)" id="venta_forma_pago_${row.id}">`;
            }},
        ],
        initComplete: function () {
            $('#ventaFormaPago').on('draw.dt', function() {
                $("input[data-type='currency']").on({
                    keyup: function(event) {
                        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
                            formatCurrency($(this));
                        }
                    },
                    blur: function() {
                        formatCurrency($(this), "blur");
                    }
                });
            });
        }
    });

    $comboCliente = $('#id_cliente_venta').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownCssClass: 'custom-id_cliente_venta',
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
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

    $comboVendedor = $('#id_vendedor_venta').select2({
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
            url: 'api/vendedores/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                var data_modified = $.map(data.data, function (obj) {
                    obj.text = obj.nit.nombre_completo;
                    return obj;
                });
                return {
                    results: data_modified
                };
            },
        }
    });

    $comboResolucion = $('#id_resolucion_venta').select2({
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

    $comboBodegaVenta = $('#id_bodega_venta').select2({
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

    $('#id_tipo_documento_venta_nit').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitVentaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/nit/combo-tipo-documento',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_ciudad_venta_nit').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitVentaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/ciudades',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    if(primeraResolucionVenta && primeraResolucionVenta.length > 0){
        var dataResolucion = {
            id: primeraResolucionVenta[0].id,
            text: primeraResolucionVenta[0].prefijo + ' - ' + primeraResolucionVenta[0].nombre
        };
        var newOption = new Option(dataResolucion.text, dataResolucion.id, false, false);
        $comboResolucion.append(newOption).trigger('change');
        $comboResolucion.val(dataResolucion.id).trigger('change');
    }

    if(primeraBodegaVenta && primeraBodegaVenta.length > 0){
        var dataBodega = {
            id: primeraBodegaVenta[0].id,
            text: primeraBodegaVenta[0].codigo + ' - ' + primeraBodegaVenta[0].nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodegaVenta.append(newOption).trigger('change');
        $comboBodegaVenta.val(dataBodega.id).trigger('change');
    }

    var column2 = venta_table.column(2);
    var column5 = venta_table.column(5);
    var column6 = venta_table.column(6);

    if (ventaDescuento){
        column5.visible(true);
        column6.visible(true);
    } else {
        column5.visible(false);
        column6.visible(false);
    }

    if (ventaExistencias) column2.visible(true);
    else column2.visible(false);

    $('#id_cliente_venta').on('select2:close', function(event) {
        var data = $(this).select2('data');
        if(data.length){
            loadAnticiposCliente();
            clearFormasPagoVenta();
        }
    });
    
    $('#id_resolucion_venta').on('select2:close', function(event) {
        var data = $(this).select2('data');
        if(data.length){
            document.getElementById('iniciarCapturaVenta').click();
        }
    });

    consecutivoSiguienteVenta();
    loadFormasPagoVenta();

    if (!primeraBodegaVenta.length) {
        agregarToast('warning', 'Sin bodegas asignadas', '', true);
    }

    if (!primeraResolucionVenta.length) {
        agregarToast('warning', 'Sin Resoluciones asigandas', '', true);
    }

    if (primeraNit && ventaRapida) {
        var dataCliente = {
            id: primeraNit.id,
            text: primeraNit.numero_documento + ' - ' + primeraNit.nombre_completo
        };
        var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
        $comboCliente.append(newOption).trigger('change');
        $comboCliente.val(dataCliente.id).trigger('change');

        loadAnticiposCliente();
        
        document.getElementById('iniciarCapturaVenta').click();

    } else {
        setTimeout(function(){
            $comboCliente.select2("open");
        },10);
    }
}

function focusCantidadVenta (idRow) {
    setTimeout(function(){
        $('#venta_cantidad_'+idRow).select();
    },80);
}

function focusCostoVenta (idRow) {
    setTimeout(function(){
        $('#venta_costo_'+idRow).select();
    },80);
}

function focusPDescuentoVenta (idRow) {
    setTimeout(function(){
        $('#venta_descuento_porcentaje_'+idRow).select();
    },80);
}

function focusVDescuentoVenta (idRow) {
    setTimeout(function(){
        $('#venta_descuento_valor_'+idRow).select();
    },80);
}

function loadAnticiposCliente() {

    totalAnticiposDisponibles = 0;
    $('#input-anticipos-venta').hide();
    $('#id_saldo_anticipo_venta').val(0);

    let data = {
        id_nit: $('#id_cliente_venta').val(),
        id_tipo_cuenta: 8
    }

    $.ajax({
        url: base_url + 'extracto-anticipos',
        method: 'GET',
        data: data,
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            var disabled = true;
            if (res.data) {
                var saldo = parseFloat(res.data.saldo);
                if (saldo > 0) {
                    disabled = false;
                    $('#input-anticipos-venta').show();
                    totalAnticiposDisponibles = saldo;
                    $('#id_saldo_anticipo_venta').val(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(saldo));
                }
            }
            var pagosAnticipos = document.getElementsByClassName('anticipos');
            if (pagosAnticipos) { //HIDE ELEMENTS
                for (let index = 0; index < pagosAnticipos.length; index++) {
                    const element = pagosAnticipos[index];
                    element.disabled = disabled;
                }
            }
        }
    }).fail((err) => {
    });
}

$(document).on('keydown', '.custom-venta_producto .select2-search__field', function (event) {

    if (guardandoVenta) {
        return;
    }

    var [iva, retencion, descuento, total, valorBruto] = totalValoresVentas();
    
    if (event.keyCode == 96) {
        abrirFormasPagoVentas = true;
    } else if (event.keyCode == 13){
        if (total > 0) {
            if (abrirFormasPagoVentas) {
                abrirFormasPagoVentas = false;
                $(".venta_producto").select2('close');
                focusFormaPagoVenta(1);
            }
        }
    } else {
        abrirFormasPagoVentas = false;
    }
});

$(document).on('keydown', '.custom-id_cliente_venta .select2-search__field', function (event) {
    if (event.keyCode == 13){
        openModalNewNit();
        var documentoBuscado = $('.select2-search__field').val();
        $comboCliente.select2('close');
        $("#numero_documento_venta_nit").val(documentoBuscado);
    }
});

$("#id_vendedor_venta").on('change', function(event) {
    if ($("#id_vendedor_venta").val()) {
        $("#crearCapturaVenta").show();
        $("#crearCapturaVentaDisabled").hide();
    } else {
        $("#crearCapturaVenta").hide();
        $("#crearCapturaVentaDisabled").show();
    }
});

function consecutivoSiguienteVenta() {
    var id_resolucion = $('#id_resolucion_venta').val();
    var fecha_manual = $('#fecha_manual_venta').val();
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
            if(res.success){
                $("#documento_referencia_venta").val(res.data);
            }
        }).fail((err) => {
        });
    }
}

$("#id_resolucion_venta").on('change', function(event) {
    consecutivoSiguienteVenta();
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

$(document).on('click', '#iniciarCapturaVenta', function () {
    var form = document.querySelector('#ventaFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        $("#error_documento_referencia_venta").text('El No. factura requerido');
        return;
    }
    
    $("#iniciarCapturaVenta").hide();
    $('#agregarVentaProducto').show();
    $("#crearCapturaVenta").hide();
    $("#cancelarCapturaVenta").show();
    $('#cambio-totals').hide();
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
    } else if(totalRows > 1) {
        clearFormasPagoVenta();
    }

    venta_table.row.add({
        "id": idVentaProducto,
        "cantidad": 1,
        "costo": 0,
        "existencias": 0,
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

$(document).on('click', '#agregarVentaProducto', function () {
    addRowProductoVenta();
});

function calcularProductoVenta (idRow, validarCantidad = false) {
    var costoProducto = stringToNumberFloat($('#venta_costo_'+idRow).val());
    var cantidadProducto = $('#venta_cantidad_'+idRow).val();
    var ivaProducto = $('#venta_iva_porcentaje_'+idRow).val();
    var descuentoProducto = $('#venta_descuento_porcentaje_'+idRow).val();
    var totalPorCantidad = 0;
    var totalIva = 0;
    var totalDescuento = 0;
    var totalProducto = 0;

    if (validarCantidad && !validarExistencias(idRow)) return;
    
    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    if (descuentoProducto > 0) {
        totalDescuento = totalPorCantidad * descuentoProducto / 100;
        $('#venta_descuento_valor_'+idRow).val(formatCurrencyValue(totalDescuento));
    } else {
        $('#venta_descuento_porcentaje_'+idRow).val(formatCurrencyValue(0));
        $('#venta_descuento_valor_'+idRow).val(formatCurrencyValue(0));
    }

    totalProducto = totalPorCantidad - totalDescuento;

    if (ivaProducto > 0) {
        totalIva = (totalPorCantidad - totalDescuento) * ivaProducto / 100;
        if (ivaIncluidoVentas) {
            totalIva = (totalPorCantidad - totalDescuento) - ((totalPorCantidad - totalDescuento) / (1 + (ivaProducto / 100)));
        } else {
            totalIva + totalIva;
        }
        
        $('#venta_iva_valor_'+idRow).val(formatCurrencyValue(totalIva));
    }
    
    $('#venta_total_'+idRow).val(formatCurrencyValue(totalProducto));

    mostrarValoresVentas ();
}

function validarExistencias (idRow) {
    var producto = $('#venta_producto_'+idRow).select2('data')[0];
    var cantidad = $('#venta_cantidad_'+idRow).val();
    var rowProductos = venta_table.rows().data();

    if (ventaNegativa) {
        addRowProductoVenta();
        calcularProductoVenta(idRow);
        return true;
    }

    if (producto !== undefined && producto.familia && producto.familia.inventario){

        if (rowProductos.length > 1) {
            consultarExistencias(idRow);
            return false;
        } else {
            if (cantidad > parseInt(producto.inventarios[0].cantidad)) {
                $('#venta_cantidad_text_'+idRow).text("Se ha superado las existencias");
                $('#venta_cantidad_'+idRow).addClass("is-invalid");
                $('#venta_cantidad_'+idRow).removeClass("is-valid");
                $('#venta_cantidad_'+idRow).val(0);
                setTimeout(function(){
                    $('#venta_cantidad_'+idRow).focus();
                    $('#venta_cantidad_'+idRow).select();
                },10);
                return false;
            } else {
                if (key13PressNewRow) {
                    key13PressNewRow = false;
                    calcularProductoVenta(idRow);
                    addRowProductoVenta();
                }
                $('#venta_cantidad_'+idRow).removeClass("is-invalid");
            }
        }
    } else if (producto !== undefined && !producto.familia) {
        consultarExistencias(idRow);
        return false;
    }
    addRowProductoVenta();
    calcularProductoVenta(idRow);
    return true;
}

function totalCantidadProducto(idRow) {
    
    var idProducto = $('#venta_producto_'+idRow).val();
    var rowProductos = venta_table.rows().data();
    var cantidadActualRow = parseInt($('#venta_cantidad_'+idRow).val());
    var cantidadTotal = 0;

    for (let index = 0; index < rowProductos.length; index++) {
        var producto = $('#venta_producto_'+rowProductos[index].id).val();
         
        if (producto && rowProductos[index].id != idRow && producto == idProducto) {
            var cantidad = parseInt($('#venta_cantidad_'+rowProductos[index].id).val());
            cantidadTotal+= cantidad;
        }
    }

    return [cantidadActualRow, cantidadTotal];
}

function consultarExistencias(idRow) {

    var idproducto = $('#venta_producto_'+idRow).val();
    var bodega = $('#id_bodega_venta').val();
    var [cantidadActualRow, cantidadTotal] = totalCantidadProducto(idRow)

    if (validarExistenciasProducto) {
        validarExistenciasProducto.abort();
    }
    
    $('#venta_producto_load_'+idRow).show();
    setTimeout(function(){
        validarExistenciasProducto = $.ajax({
            url: base_url + 'existencias-producto',
            method: 'GET',
            data: {
                id_producto: idproducto,
                id_bodega: bodega
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarExistenciasProducto = null;
            $('#venta_producto_load_'+idRow).hide();
            if (res.data) {
                if (cantidadActualRow + cantidadTotal > parseInt(res.data.cantidad)) {
                    $('#venta_cantidad_text_'+idRow).text("Se ha superado las existencias");
                    $('#venta_cantidad_'+idRow).addClass("is-invalid");
                    $('#venta_cantidad_'+idRow).removeClass("is-valid");

                    if (1 + cantidadTotal > parseInt(res.data.cantidad)) $('#venta_cantidad_'+idRow).val(0);
                    else $('#venta_cantidad_'+idRow).val(0);
                    
                    setTimeout(function(){
                        $('#venta_cantidad_'+idRow).focus();
                        $('#venta_cantidad_'+idRow).select();
                    },10);

                    return false;
                } else {
                    $('#venta_cantidad_'+idRow).removeClass("is-invalid");
                    if (key13PressNewRow) {
                        key13PressNewRow = false;
                        calcularProductoVenta(idRow);
                        addRowProductoVenta();
                    }
                }
            }
            
        }).fail((err) => {
            $('#venta_producto_load_'+idRow).hide();
            validarExistenciasProducto = null;
            if(err.statusText != "abort") {
            }
        });
    },300);
}

function mostrarValoresVentas () {

    if (guardandoVenta) {
        return;
    }

    var [iva, retencion, descuento, total, valorBruto] = totalValoresVentas();

    if (descuento) $('#totales_descuento').show();
    else $('#totales_descuento').hide();

    if (retencion) $('#totales_retencion').show();
    else $('#totales_retencion').hide();

    if (total) disabledFormasPagoVenta(false);
    else disabledFormasPagoVenta();

    $("#venta_total_iva").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(iva));
    $("#venta_total_descuento").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(descuento));
    $("#venta_total_retencion").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(retencion));
    $("#venta_total_valor").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total));
    $("#venta_sub_total").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valorBruto));
    document.getElementById('total_faltante_venta').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
}

function totalValoresVentas() {
    var iva = retencion = descuento = total = redondeo = 0;
    var valorBruto = 0;
    var dataVenta = venta_table.rows().data();

    if(dataVenta.length > 0) {
        
        for (let index = 0; index < dataVenta.length; index++) {
            var producto = $('#venta_producto_'+dataVenta[index].id).val();
             
            if (producto) {
                var cantidad = stringToNumberFloat($('#venta_cantidad_'+dataVenta[index].id).val());
                var costo = stringToNumberFloat($('#venta_costo_'+dataVenta[index].id).val());
                var ivaSum = stringToNumberFloat($('#venta_iva_valor_'+dataVenta[index].id).val());
                var totaSum = stringToNumberFloat($('#venta_total_'+dataVenta[index].id).val());
                var descSum = stringToNumberFloat($('#venta_descuento_valor_'+dataVenta[index].id).val());
    
                descSum= descSum ? descSum : 0;
                iva+= ivaSum ? ivaSum : 0;
                descuento+= descSum ? descSum : 0;
                total+= totaSum ? totaSum : 0;
                valorBruto+= (cantidad*costo) - descSum;
            }
        }

        if (total >= topeRetencionVenta) {
            retencion = porcentajeRetencionVenta ? (valorBruto * porcentajeRetencionVenta) / 100 : 0;
            retencion = retencion;
            total-= retencion;
        }

        if (ivaIncluidoVentas) valorBruto-= iva;
        else total+= iva;

        if (redondearFactura) {
            var totalParaRedondear =  parseFloat(total / 1000);
            var totalRedondeado =  totalParaRedondear.toFixed(2) * 1000;
            redondeo = totalRedondeado - total;
            total = totalRedondeado;
        }
    }

    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoVentas();

    if (total > 0 && (totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
        if (vendedoresVentas && !$("#id_vendedor_venta").val()) {
            $("#crearCapturaVentaDisabled").show();
            $("#crearCapturaVenta").hide();
            $('#id_vendedor_venta').addClass("is-invalid");
            $('#id_vendedor_venta').removeClass("is-valid");
        } else {
            $("#crearCapturaVenta").show();
            $("#crearCapturaVentaDisabled").hide();
            $('#id_vendedor_venta').removeClass("is-invalid");
            $('#id_vendedor_venta').addClass("is-valid");
        }
    } else {
        $("#crearCapturaVenta").hide();
        $("#crearCapturaVentaDisabled").show();
    }

    return [iva, retencion, descuento, total, valorBruto, redondeo];
}

function changeProductoVenta (idRow) {

    var data = $('#venta_producto_'+idRow).select2('data');
    if (data.length == 0) return;
    data = data[0];
    
    if (data.inventarios.length > 0 && data.familia.inventario) {
        var totalInventario = parseFloat(data.inventarios[0].cantidad);
        $("#venta_existencia_"+idRow).val(totalInventario);
        $("#venta_cantidad_"+idRow).attr({"max" : totalInventario});
    }

    if (data.familia.cuenta_venta_iva && data.familia.cuenta_venta_iva.impuesto) {
        
        $('#venta_iva_porcentaje_'+idRow).val(stringToNumberFloat(data.familia.cuenta_venta_iva.impuesto.porcentaje));
        $('#venta_iva_porcentaje_text_'+idRow).text(stringToNumberFloat(data.familia.cuenta_venta_iva.impuesto.porcentaje)+'%');
    }

    if (data.familia.cuenta_venta_retencion && data.familia.cuenta_venta_retencion.impuesto) {
        var impuestoPorcentaje = parseFloat(data.familia.cuenta_venta_retencion.impuesto.porcentaje);
        var topeValor = parseFloat(data.familia.cuenta_venta_retencion.impuesto.base);
        if (impuestoPorcentaje > porcentajeRetencionVenta) {
            porcentajeRetencionVenta = impuestoPorcentaje;
            topeRetencionVenta = topeValor;
        }
    }

    if (data.familia.id_cuenta_venta_descuento && ventaDescuento) {
        $('#venta_descuento_valor_'+idRow).prop('disabled', false);
        $('#venta_descuento_porcentaje_'+idRow).prop('disabled', false);
    } else {
        $('#venta_descuento_valor_'+idRow).prop('disabled', true);
        $('#venta_descuento_porcentaje_'+idRow).prop('disabled', true);
    }

    $('#venta_costo_'+idRow).val(formatCurrencyValue(data.precio));
    $('#venta_producto_'+idRow).select2('open');
    $('#venta_cantidad_'+idRow).prop('disabled', false);
    $('#venta_costo_'+idRow).prop('disabled', false);
    
    document.getElementById('venta_texto_retencion').innerHTML = 'RETENCIÓN '+ porcentajeRetencionVenta+'%';
        
    calcularProductoVenta(idRow);
    clearFormasPagoVenta();
    
    setTimeout(function(){
        $('#venta_cantidad_'+idRow).focus();
        $('#venta_cantidad_'+idRow).select();
    },10);
}

function cantidadVentakeyDown (idRow, event) {
    if(event.keyCode == 13){
        key13PressNewRow = true;
        if (!validarExistencias(idRow)) return;
    }
}

function CostoVentakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoVenta (idRow);
        addRowProductoVenta();
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
        var descuentoProductoValor = stringToNumberFloat($('#venta_descuento_valor_'+idRow).val());
        var costoProducto = stringToNumberFloat($('#venta_costo_'+idRow).val());
        var cantidadProducto = $('#venta_cantidad_'+idRow).val();
        var porcentajeDescuento = descuentoProductoValor * 100 / (costoProducto * cantidadProducto);

        $('#venta_descuento_porcentaje_'+idRow).val(formatCurrencyValue(porcentajeDescuento));
        
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
    idVentaProducto = 0;
    var totalRows = venta_table.rows().data().length;

    if(venta_table.rows().data().length){
        venta_table.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            venta_table.row(0).remove().draw();
        }
        mostrarValoresVentas();
    }

    $("#id_bodega_venta").prop('disabled', false);
    $("#id_resolucion_venta").prop('disabled', false);

    $("#iniciarCapturaVenta").show();
    $('#agregarVentaProducto').hide();
    $("#crearCapturaVenta").hide();
    $("#cancelarCapturaVenta").hide();
    $("#crearCapturaVentaDisabled").hide();
    $('#input-anticipos-venta').hide();

    setTimeout(function(){
        $comboCliente.select2("open");
    },10);
}

function loadFormasPagoVenta() {
    var totalRows = venta_table_pagos.rows().data().length;
    if(venta_table_pagos.rows().data().length){
        venta_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            venta_table_pagos.row(0).remove().draw();
        }
    }
    venta_table_pagos.ajax.reload(function(res) {
        disabledFormasPagoVenta();
    });
}

function clearFormasPagoVenta() {
    var dataFormasPago = venta_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#venta_forma_pago_'+formaPago.id).val(0);
        }
    }
    calcularVentaPagos();
}

function disabledFormasPagoVenta(estado = true) {
    var dataFormasPago = venta_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#venta_forma_pago_'+formaPago.id).prop('disabled', estado);
        }
    }

    if (totalAnticiposDisponibles <= 0) {
        var pagosAnticipos = document.getElementsByClassName('anticipos');
        if (pagosAnticipos) { //HIDE ELEMENTS
            for (let index = 0; index < pagosAnticipos.length; index++) {
                const element = pagosAnticipos[index];
                element.disabled = true;
            }
        }
    }
}

$(document).on('click', '#crearCapturaVenta', function () {
    validateSaveVenta();
});

function focusFormaPagoVenta(idFormaPago, anticipo = false) {

    if (guardandoVenta) {
        return;
    }

    var [iva, retencion, descuento, total, subtotal] = totalValoresVentas();
    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoVentas(idFormaPago);
    
    var totalFactura = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    totalFactura = totalFactura < 0 ? 0 : totalFactura;

    if (anticipo) {
        if ((totalAnticiposDisponibles - totalAnticipos) < totalFactura) {
            $('#venta_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalAnticiposDisponibles - totalAnticipos));
            $('#venta_forma_pago_'+idFormaPago).select();
            return;
        }
    }

    $('#venta_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalFactura));
    $('#venta_forma_pago_'+idFormaPago).select();
}

function calcularVentaPagos(idFormaPago) {

    if (guardandoVenta) {
        return;
    }

    if (
        $('#venta_forma_pago_'+idFormaPago).val() == '' ||
        $('#venta_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#venta_forma_pago_'+idFormaPago).val(0);
    }

    $('#total_faltante_venta').removeClass("is-invalid");

    var [iva, retencion, descuento, total, subtotal] = totalValoresVentas();
    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoVentas();
    var totalFaltante = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    
    if ((totalOtrosPagos + totalEfectivo + totalAnticipos) >= total) {
        var totalCambio = (totalEfectivo + totalOtrosPagos + totalAnticipos) - total;
        if(parseInt(totalCambio) > 0)$('#cambio-totals').show();
        document.getElementById('total_cambio_venta').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalCambio);
    } else {
        $('#cambio-totals').hide();
        if (totalFaltante < 0) {
            $('#venta_forma_pago_'+idFormaPago).val(totalFaltante * -1);
            $('#venta_forma_pago_'+idFormaPago).select();
        }
    }
    var totalPagado = totalFaltante < 0 ? total : totalEfectivo + totalOtrosPagos + totalAnticipos;
    var totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    document.getElementById('total_pagado_venta').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalPagado);
    document.getElementById('total_faltante_venta').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalFaltante);
}

function totalFormasPagoVentas(idFormaPago = null) {
    var totalEfectivo = 0;
    var totalAnticipos = 0;
    var totalOtrosPagos = 0;

    var dataPagoVenta = venta_table_pagos.rows().data();

    if(dataPagoVenta.length > 0) {
        for (let index = 0; index < dataPagoVenta.length; index++) {
            
            var ventaPago = stringToNumberFloat($('#venta_forma_pago_'+dataPagoVenta[index].id).val());
            
            if (idFormaPago && idFormaPago == dataPagoVenta[index].id) continue;

            if (dataPagoVenta[index].id == 1) totalEfectivo+= ventaPago;
            else if ($('#venta_forma_pago_'+dataPagoVenta[index].id).hasClass("anticipos")) totalAnticipos+= ventaPago;
            else totalOtrosPagos+= ventaPago;
        }
    }

    return [totalEfectivo, totalOtrosPagos, totalAnticipos];
}

function validateSaveVenta() {
    $('#total_faltante_venta_text').css("color","#484848");
    $('#total_faltante_venta').css("color","#484848");

    if (!guardandoVenta) {

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoVentas();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresVentas();

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
            
            guardandoVenta = true;
            saveVenta();
        } else {
            $('#total_faltante_venta_text').css("color","red");
            $('#total_faltante_venta').css("color","red");
        }
    }
}

function saveVenta() {

    $("#crearCapturaVenta").hide();
    $("#crearCapturaVentaDisabled").hide();
    $("#crearCapturaVentaLoading").show();
    
    let data = {
        pagos: getVentasPagos(),
        productos: getProductosVenta(),
        id_bodega: $("#id_bodega_venta").val(),
        id_cliente: $("#id_cliente_venta").val(),
        fecha_manual: $("#fecha_manual_venta").val(),
        id_resolucion: $("#id_resolucion_venta").val(),
        id_vendedor: $("#id_vendedor_venta").val(),
        documento_referencia: $("#documento_referencia_venta").val(),
        observacion: $("#observacion_venta").val(),
    }

    disabledFormasPagoVenta();

    $.ajax({
        url: base_url + 'ventas',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoVenta = false;
        if(res.success){

            agregarToast('exito', 'Creación exitosa', 'Venta creada con exito!', true);

            $("#crearCapturaVenta").show();
            $("#crearCapturaVentaLoading").hide();

            if(res.impresion) {
                window.open("/ventas-print/"+res.impresion, '_blank');
            }
            idVentaProducto = 0;

            $('#iniciarCapturaVenta').hide();
            $('#iniciarCapturaVentaLoading').hide();
            $('#documento_referencia_venta').val('');

            var totalRows = venta_table.rows().data().length;
            for (let index = 0; index < totalRows; index++) {
                venta_table.row(0).remove().draw();
            }

            if (ventaRapida) {
                addRowProductoVenta();
            } else {
                setTimeout(function(){
                    $('#id_cliente_venta').focus();
                    $comboCliente.select2("open");
                },10);
            }

            consecutivoSiguienteVenta();
            loadAnticiposCliente();
            disabledFormasPagoVenta();

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
        guardandoVenta = false;
        $("#crearCapturaVenta").show();
        $("#crearCapturaVentaLoading").hide();
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
        var pagoVenta = stringToNumberFloat($('#venta_forma_pago_'+dataPagoVenta.id).val());
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
            var costo = stringToNumberFloat($('#venta_costo_'+id_row).val());
            var descuento_porcentaje = stringToNumberFloat($('#venta_descuento_porcentaje_'+id_row).val());
            var descuento_valor = stringToNumberFloat($('#venta_descuento_valor_'+id_row).val());
            var iva_porcentaje = stringToNumberFloat($('#venta_iva_porcentaje_'+id_row).val());
            var iva_valor = stringToNumberFloat($('#venta_iva_valor_'+id_row).val());
            var total = stringToNumberFloat($('#venta_total_'+id_row).val());
            var subtotal = parseInt(cantidad) * costo;

            if(ivaIncluidoVentas) {
                subtotal-= iva_valor;
            }

            data.push({
                id_producto: parseInt(id_producto),
                cantidad: parseInt(cantidad),
                costo: costo ? costo : 0,
                subtotal: subtotal,
                descuento_porcentaje: descuento_porcentaje ? descuento_porcentaje : 0,
                descuento_valor: descuento_valor ? descuento_valor : 0,
                iva_porcentaje: iva_porcentaje ? iva_porcentaje : 0,
                iva_valor: iva_valor ? iva_valor : 0,
                total: total ? total : 0,
            });
        }
    }

    return data;
}

function changeFormaPago(idFormaPago, anticipo, event) {

    if (guardandoVenta) {
        return;
    }

    if(event.keyCode == 13){

        calcularVentaPagos(idFormaPago);

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoVentas();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresVentas();

        if (!total) {
            return;
        }

        if (anticipo) {
            if (totalAnticipos > totalAnticiposDisponibles || totalAnticipos > total) {

                var [efectivo, pagos, totalOtrosAnticipos] = totalFormasPagoVentas(idFormaPago);

                $('#venta_forma_pago_'+idFormaPago).val(totalAnticiposDisponibles - totalOtrosAnticipos);
                $('#venta_forma_pago_'+idFormaPago).select();
                return;
            }
        }

        if (vendedoresVentas && !$("#id_vendedor_venta").val()) {
            return;
        }

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
            validateSaveVenta();
            return;
        }
        focusNextFormasPagoVentas(idFormaPago);
    }
}

function focusNextFormasPagoVentas(idFormaPago) {
    var dataVentaPagos = venta_table_pagos.rows().data();
    var idFormaPagoFocus = dataVentaPagos[0].id;
    var obtenerFormaPago = false;

    if(!dataVentaPagos.length > 0) return;

    for (let index = 0; index < dataVentaPagos.length; index++) {
        const dataPagoCompra = dataVentaPagos[index];
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoCompra.id;
            obtenerFormaPago = false;
        } else if (dataPagoCompra.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoVenta(idFormaPagoFocus);
}

function openModalNewNit() {
    clearFormNitsVenta();
    $("#nitVentaFormModal").modal('show');
}

function clearFormNitsVenta() {
    $("#id_tipo_documento_venta_nit").val('').change();
    $("#id_ciudad_venta_nit").val('').change();
    $("#observaciones_venta_nit").val('');
    $("#numero_documento_venta_nit").val('');
    $("#tipo_contribuyente_venta_nit").val(2).change();
    $("#primer_apellido_venta_nit").val('');
    $("#segundo_apellido_venta_nit").val('');
    $("#primer_nombre_venta_nit").val('');
    $("#otros_nombres_venta_nit").val('');
    $("#razon_social_venta_nit").val('');
    $("#telefono_1_venta_nit").val('');
    $("#direccion_venta_nit").val('');
    $("#email_venta_nit").val('');
}

$(document).on('click', '#saveNitVenta', function () {
    var form = document.querySelector('#ventaNitsForm');

    if(form.checkValidity()){

        $("#saveNitVentaLoading").show();
        $("#saveNitVenta").hide();

        let data = {
            id_tipo_documento: $("#id_tipo_documento_venta_nit").val(),
            numero_documento: $("#numero_documento_venta_nit").val(),
            tipo_contribuyente: $("#tipo_contribuyente_venta_nit").val(),
            primer_apellido: $("#primer_apellido_venta_nit").val(),
            segundo_apellido: $("#segundo_apellido_venta_nit").val(),
            primer_nombre: $("#primer_nombre_venta_nit").val(),
            otros_nombres: $("#otros_nombres_venta_nit").val(),
            razon_social: $("#razon_social_venta_nit").val(),
            direccion: $("#direccion_venta_nit").val(),
            email: $("#email_venta_nit").val(),
            telefono_1: $("#telefono_1_venta_nit").val(),
            id_ciudad: $("#id_ciudad_venta_nit").val(),
            observaciones: $("#observaciones_venta_nit").val(),
        }

        $.ajax({
            url: base_url + 'nit',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                clearFormNitsVenta();
                $("#saveNitVenta").show();
                $("#saveNitVentaLoading").hide();
                $("#nitVentaFormModal").modal('hide');

                var dataCliente = {
                    id: res.data.id,
                    text: res.data.numero_documento + ' - ' + res.data.nombre_completo
                };
                var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
                $comboCliente.append(newOption).trigger('change');
                $comboCliente.val(dataCliente.id).trigger('change');

                agregarToast('exito', 'Creación exitosa', 'Cedula nit creada con exito!', true);

                document.getElementById('iniciarCapturaVenta').click();
            }
        }).fail((err) => {
            $('#saveNitVenta').show();
            $('#saveNitVentaLoading').hide();
            var errorsMsg = "";
            var mensaje = err.responseJSON.message;
            if(typeof mensaje  === 'object' || Array.isArray(mensaje)){
                for (field in mensaje) {
                    var errores = mensaje[field];
                    for (campo in errores) {
                        errorsMsg += "- "+errores[campo]+" <br>";
                    }
                };
            } else {
                errorsMsg = mensaje
            }
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    } else {
        form.classList.add('was-validated');
    }
});

