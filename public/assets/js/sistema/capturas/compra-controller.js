var fecha = null;
var compra_table = null;
var idCompraProducto = 0;
var guardarCompra = false;
var $comboCliente = null;
var createNewNit = false;
var $comboVendedor = null;
var retencionesCompras = [];
var topeRetencionCompra = 0;
var guardandoCompra = false;
var $comboComprobanteCompras = null;
var $comboBodegaCompra = null;
var key13PressNewRow = false;
var redondearFactura = false;
var compra_table_pagos = null;
var totalAnticiposCompra = null;
var validarFacturaCompra = null;
var porcentajeRetencionCompra = 0;
var responsabilidadesCompra = [];
var abrirFormasPagoCompras = false;
var totalAnticiposDisponibles = 0;
var totalAnticiposCompraCuenta = null;
var validarExistenciasProducto = null;

function compraInit () {

    cargarFechasCompras();

    compra_table = $('#compraTable').DataTable({
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
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteProductoCompra(${idCompraProducto})" id="delete-producto-compra_${idCompraProducto}"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            {//PRODUCTO
                "data": function (row, type, set, col){
                    return `<select
                            class="form-control form-control-sm compra_producto combo-grid"
                            id="compra_producto_${idCompraProducto}"
                            onchange="changeProductoCompra(${idCompraProducto})"
                            onfocusout="calcularProductoCompra(${idCompraProducto})"
                            inert
                        ></select>`;
                },
            },
            {//EXISTENCIAS
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="compra_existencia_${idCompraProducto}" disabled>`;
                }
            },
            {//CANTIDAD
                "data": function (row, type, set, col){
                    return `
                        <div class="input-group" style="height: 30px;">
                            <input type="number" class="form-control form-control-sm" style="min-width: 80px; border-right: solid 1px #b3b3b3; border-top-right-radius: 10px; border-bottom-right-radius: 10px; text-align: right;" id="compra_cantidad_${idCompraProducto}" min="1" value="1" onkeydown="cantidadComprakeyDown(${idCompraProducto}, event)" onfocus="focusCantidadCompra(${idCompraProducto})" onfocusout="calcularProductoCompra(${idCompraProducto})" disabled>
                            <i class="fa fa-spinner fa-spin fa-fw compra_producto_load" id="compra_producto_load_${idCompraProducto}" style="display: none;"></i>
                            <div id="compra_cantidad_text_${idCompraProducto}" style="position: absolute; margin-top: 30px; z-index: 9;" class="invalid-feedback">
                            </div>
                        </div>
                    `;
                }
            },
            {//COSTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="compra_costo_${idCompraProducto}" value="0" onkeydown="CostoComprakeyDown(${idCompraProducto}, event)" style="min-width: 100px;" onfocusout="calcularProductoCompra(${idCompraProducto})" onfocus="focusCostoCompra(${idCompraProducto})" disabled>`;
                }
            },
            {//% DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="compra_descuento_porcentaje_${idCompraProducto}" value="0"  onkeydown="DescuentoComprakeyDown(${idCompraProducto}, event)" onfocusout="calcularProductoCompra(${idCompraProducto})" onfocus="focusPDescuentoCompra(${idCompraProducto})" disabled>`;
                }
            },
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="compra_descuento_valor_${idCompraProducto}" value="0" onkeydown="DescuentoTotalComprakeyDown(${idCompraProducto}, event)" onfocusout="calcularProductoCompra(${idCompraProducto})" onfocus="focusVDescuentoCompra(${idCompraProducto})" disabled>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 85px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                            <span id="compra_iva_porcentaje_text_${idCompraProducto}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">0%</span>
                            <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" value="0" id="compra_iva_valor_${idCompraProducto}" value="0" disabled>
                        </div>
                    </div>
                    <input type="number" class="form-control form-control-sm" style="min-width: 110px; display: none;" id="compra_iva_porcentaje_${idCompraProducto}" value="0"  onkeydown="DescuentoComprakeyDown(${idCompraProducto}, event)" onfocusout="calcularProductoCompra(${idCompraProducto})">`;
                }
            },
            {//TOTAL
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="compra_total_${idCompraProducto}" value="0" disabled>`;
                }
            },
            {//CONCEPTO
                "data": function (row, type, set, col){
                    return `<input type="text" class="form-control form-control-sm" id="compra_concepto_${row.id}" onkeypress="changeObservacionGasto(${row.id}, event)" onfocus="this.select();" onfocusout="changeObservacionGasto(${row.id})" style="width: 180px !important;" value="${row.concepto}" disabled>`;
                }
            },
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#compraTable').on('draw.dt', function() {
                $('.compra_producto').select2({
                    theme: 'bootstrap-5',
                    dropdownCssClass: 'custom-compra_producto',
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
                                id_bodega: $("#id_bodega_compra").val(),
                                id_cliente: $("#id_cliente_compra").val(),
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

                if (producto.familia.inventario && compraExistencias) {
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
            {"data": function (row, type, set){
                let styles = "margin-bottom: 0px; font-size: 13px;";
                let stylesInfo = null;
                let naturaleza = 'Credito - Compras';
                if (row.cuenta.naturaleza_compras == 0) {
                    naturaleza = 'Error de naturaleza en comprar';
                    stylesInfo = "border: solid 1px red !important; color: red !important;"
                }
                let dataContent = `<b class='titulo-popover'>Cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                if (row.cuenta.tipos_cuenta.length > 0) {
                    var tiposCuentas = row.cuenta.tipos_cuenta;
                    for (let index = 0; index < tiposCuentas.length; index++) {
                        const tipoCuenta = tiposCuentas[index];
                        if (tipoCuenta.id_tipo_cuenta == 8) {
                            styles+= " color: #0bb19e; font-weight: 600;"
                            dataContent = `<b class='titulo-popover'>Anticipos cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                        }
                    }
                }
                return `<p style="${styles}">
                            <i
                                class="fas fa-info icon-info"
                                style="${stylesInfo}";
                                title="${dataContent}"
                                data-toggle="popover"
                                data-html="true"
                            >
                        </i> ${row.nombre}
                    </p>`;
            }},
            {"data": function (row, type, set){
                var anticipos = false;
                let id_cuenta = row.cuenta.id;
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
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoCompra(${row.id}, ${anticipos}, ${id_cuenta})" onfocusout="calcularCompraPagos(${row.id}, ${anticipos})" onkeypress="changeFormaPagoCompras(${row.id}, ${anticipos}, event,  ${id_cuenta})" id="compra_forma_pago_${row.id}">`;
            }},
        ],
        initComplete: function () {
            $('#compraFormaPago').on('draw.dt', function() {
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
                $('[data-toggle="popover"]').popover({
                    trigger: 'hover',
                    html: true,
                    placement: 'top',
                    container: 'body',
                    customClass: 'popover-formas-pagos'
                });
            });
        }
    });

    $comboCliente = $('#id_cliente_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownCssClass: 'custom-id_cliente_compra',
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

    $comboVendedor = $('#id_vendedor_compra').select2({
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

    $comboComprobanteCompras = $('#id_comprobante_compra').select2({
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

    $('#id_tipo_documento_compra_nit').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitCompraFormModal'),
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

    $('#id_ciudad_compra_nit').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitCompraFormModal'),
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

    if(primeraComprobanteCompra){
        var dataResolucion = {
            id: primeraComprobanteCompra.id,
            text: primeraComprobanteCompra.codigo + ' - ' + primeraComprobanteCompra.nombre
        };
        var newOption = new Option(dataResolucion.text, dataResolucion.id, false, false);
        $comboComprobanteCompras.append(newOption).trigger('change');
        $comboComprobanteCompras.val(dataResolucion.id).trigger('change');
    }

    if(primeraBodegaCompra && primeraBodegaCompra.length > 0){
        var dataBodega = {
            id: primeraBodegaCompra[0].id,
            text: primeraBodegaCompra[0].codigo + ' - ' + primeraBodegaCompra[0].nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodegaCompra.append(newOption).trigger('change');
        $comboBodegaCompra.val(dataBodega.id).trigger('change');
    }

    var column2 = compra_table.column(2);
    var column5 = compra_table.column(5);
    var column6 = compra_table.column(6);

    if (compraDescuento){
        column5.visible(true);
        column6.visible(true);
    } else {
        column5.visible(false);
        column6.visible(false);
    }

    if (compraExistencias) column2.visible(true);
    else column2.visible(false);

    $('#id_cliente_compra').on('select2:close', function(event) {
        var data = $(this).select2('data');
        if(data.length){
            loadAnticiposClienteCompras();
            clearFormasPagoCompra();
            responsabilidadesCompra = getResponsabilidades(data[0].id_responsabilidades);
            actualizarInfoRetencionCompras();
        }
    });
    
    $('#id_comprobante_compra').on('select2:close', function(event) {
        var data = $(this).select2('data');
        if(data.length){
            document.getElementById('iniciarCapturaCompra').click();
        }
    });

    loadFormasPagoCompra();

    if (!primeraBodegaCompra || !primeraBodegaCompra.length) {
        agregarToast('warning', 'Sin bodegas asignadas', '', true);
    }

    setTimeout(function(){
        $comboCliente.select2("open");
    },10);

    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
}

function cargarFechasCompras() {
    var dateNow = new Date();

    var fechaHoraCompra = dateNow.getFullYear() + '-' + 
        ("0" + (dateNow.getMonth() + 1)).slice(-2) + '-' + 
        ("0" + dateNow.getDate()).slice(-2) + 'T' + 
        ("0" + dateNow.getHours()).slice(-2) + ':' + 
        ("0" + dateNow.getMinutes()).slice(-2);
    $('#fecha_manual_compra').val(fechaHoraCompra);
}

function focusCantidadCompra (idRow) {
    setTimeout(function(){
        $('#compra_cantidad_'+idRow).select();
    },80);
}

function focusCostoCompra (idRow) {
    setTimeout(function(){
        $('#compra_costo_'+idRow).select();
    },80);
}

function focusPDescuentoCompra (idRow) {
    setTimeout(function(){
        $('#compra_descuento_porcentaje_'+idRow).select();
    },80);
}

function focusVDescuentoCompra (idRow) {
    setTimeout(function(){
        $('#compra_descuento_valor_'+idRow).select();
    },80);
}

function loadAnticiposClienteCompras() {
    totalAnticiposDisponibles = 0;
    $('#input-anticipos-compra').hide();
    $('#compra_anticipo_disp_view').hide();
    $('#saldo_anticipo_compra').val(0);
    $('#compra_anticipo_disp').text('0.00');

    if(!$('#id_cliente_compra').val()) return;

    let data = {
        id_nit: $('#id_cliente_compra').val(),
        id_tipo_cuenta: [8]
    }

    $.ajax({
        url: base_url + 'extracto-anticipos',
        method: 'GET',
        data: data,
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            totalAnticiposCompra = 0;
            totalAnticiposCompraCuenta = [];

            if (res.data.length) {
                const anticiposDisponibles = res.data;
                for (let index = 0; index < anticiposDisponibles.length; index++) {
                    const anticipo = anticiposDisponibles[index];

                    let idCuenta = anticipo.id_cuenta;
                    let cuentaExistente = encontrarCuentaCompra(idCuenta);
                    
                    totalAnticiposCompra+= Math.abs(parseFloat(anticipo.saldo));

                    if (cuentaExistente) {
                        cuentaExistente[idCuenta].saldo = (cuentaExistente[idCuenta].saldo || 0) + parseFloat(anticipo.saldo);
                    } else {
                        let nuevoObj = {};
                        nuevoObj[idCuenta] = {
                            'id_cuenta': idCuenta,
                            'saldo': Math.abs(parseFloat(anticipo.saldo))
                        };
                        totalAnticiposCompraCuenta.push(nuevoObj);
                    }
                }

                if (totalAnticiposCompra) {
                    $('#compra_anticipo_disp_view').show();
                    $('#saldo_anticipo_compra').val(new Intl.NumberFormat('ja-JP').format(totalAnticiposCompra));
                    $('#compra_anticipo_disp').text(new Intl.NumberFormat('ja-JP').format(totalAnticiposCompra));
                }
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function encontrarCuentaCompra(idCuenta) {
    if (totalAnticiposCompraCuenta && totalAnticiposCompraCuenta.length) {
        return totalAnticiposCompraCuenta.find(item => item[idCuenta]);
    }
    return false;
}

function addRowProductoCompra () {

    var rows = compra_table.rows().data();
    var totalRows = rows.length;
    var dataLast = rows[totalRows - 1];

    if (dataLast) {
        var cuentaLast = $('#compra_producto_'+dataLast.id).val();
        if (!cuentaLast) {
            $('#compra_producto_'+dataLast.id).select2('open');
            document.getElementById("card-compra").scrollLeft = 0;
            return;
        }
    } else if(totalRows > 1) {
        clearFormasPagoCompra();
    }

    compra_table.row.add({
        "id": idCompraProducto,
        "cantidad": 1,
        "costo": 0,
        "existencias": 0,
        "porcentaje_descuento": 0,
        "valor_descuento": 0,
        "porcentaje_iva": 0,
        "valor_iva": 0,
        "valor_total": 0,
        "concepto": "",
    }).draw(false);

    $('#card-compra').focus();
    document.getElementById("card-compra").scrollLeft = 0;

    $('#compra_producto_'+idCompraProducto).focus();
    $('#compra_producto_'+idCompraProducto).select2('open');
    idCompraProducto++;
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
    actualizarInfoRetencionCompras();
}

function calcularProductoCompra (idRow, validarCantidad = false) {
    var costoProducto = stringToNumberFloat($('#compra_costo_'+idRow).val());
    var cantidadProducto = $('#compra_cantidad_'+idRow).val();
    var ivaProducto = $('#compra_iva_porcentaje_'+idRow).val();
    var descuentoProducto = $('#compra_descuento_porcentaje_'+idRow).val();
    var totalPorCantidad = 0;
    var totalIva = 0;
    var totalDescuento = 0;
    var totalProducto = 0;

    if (validarCantidad && !validarExistenciasCompras(idRow)) return;
    
    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    if (descuentoProducto > 0) {
        totalDescuento = totalPorCantidad * (descuentoProducto / 100);
        $('#compra_descuento_valor_'+idRow).val(formatCurrencyValue(totalDescuento));
    } else {
        $('#compra_descuento_porcentaje_'+idRow).val(formatCurrencyValue(0));
        $('#compra_descuento_valor_'+idRow).val(formatCurrencyValue(0));
    }

    totalProducto = totalPorCantidad - totalDescuento;

    if (ivaProducto > 0) {
        totalIva = (totalPorCantidad - totalDescuento) * ivaProducto / 100;
        if (ivaIncluidoCompras) {
            totalIva = (totalPorCantidad - totalDescuento) - ((totalPorCantidad - totalDescuento) / (1 + (ivaProducto / 100)));
        }
        
        $('#compra_iva_valor_'+idRow).val(formatCurrencyValue(totalIva));
    }

    if (!ivaIncluidoCompras) {
        totalProducto+= totalIva;
    }

    totalProducto = Math.round(totalProducto * 100) / 100;
    $('#compra_total_'+idRow).val(formatCurrencyValue(totalProducto));

    mostrarValoresCompras();
    actualizarInfoRetencionCompras();
}

function validarExistenciasCompras (idRow) {

    addRowProductoCompra();
    calcularProductoCompra(idRow);
    return true;
}

function totalCantidadProductoCompras(idRow) {
    
    var idProducto = $('#compra_producto_'+idRow).val();
    var rowProductos = compra_table.rows().data();
    var cantidadActualRow = parseInt($('#compra_cantidad_'+idRow).val());
    var cantidadTotal = 0;

    for (let index = 0; index < rowProductos.length; index++) {
        var producto = $('#compra_producto_'+rowProductos[index].id).val();
         
        if (producto && rowProductos[index].id != idRow && producto == idProducto) {
            var cantidad = parseInt($('#compra_cantidad_'+rowProductos[index].id).val());
            cantidadTotal+= cantidad;
        }
    }

    return [cantidadActualRow, cantidadTotal];
}

function mostrarValoresCompras () {

    if (guardandoCompra) {
        return;
    }

    var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

    if (descuento) $('#totales_descuento').show();
    else $('#totales_descuento').hide();

    if (total) disabledFormasPagoCompra(false);
    else disabledFormasPagoCompra();

    var countA = new CountUp('compra_total_iva', 0, iva, 2, 0.5);
        countA.start();

    var countB = new CountUp('compra_total_descuento', 0, descuento, 2, 0.5);
        countB.start();

    var countC = new CountUp('compra_total_retencion', 0, retencion, 2, 0.5);
        countC.start();

    var countD = new CountUp('compra_total_valor', 0, total, 2, 0.5);
        countD.start();
        
    var countE = new CountUp('compra_sub_total', 0, valorBruto, 2, 0.5);
        countE.start();

    var countF = new CountUp('total_faltante_compra', 0, total, 2, 0.5);
        countF.start();
}

function totalValoresCompras() {
    var iva = retencion = descuento = total = redondeo = 0;
    var valorBruto = 0;
    var dataCompra = compra_table.rows().data();

    if(dataCompra.length > 0) {
        
        for (let index = 0; index < dataCompra.length; index++) {
            var producto = $('#compra_producto_'+dataCompra[index].id).val();
             
            if (producto) {
                var cantidad = stringToNumberFloat($('#compra_cantidad_'+dataCompra[index].id).val());
                var costo = stringToNumberFloat($('#compra_costo_'+dataCompra[index].id).val());
                var ivaSum = stringToNumberFloat($('#compra_iva_valor_'+dataCompra[index].id).val());
                var totaSum = stringToNumberFloat($('#compra_total_'+dataCompra[index].id).val());
                var descSum = stringToNumberFloat($('#compra_descuento_valor_'+dataCompra[index].id).val());
    
                descSum= descSum ? descSum : 0;
                iva+= ivaSum ? ivaSum : 0;
                descuento+= descSum ? descSum : 0;
                valorBruto+= (cantidad*costo) - descSum;
            }
        }

        if (ivaIncluidoCompras) valorBruto-= iva;

        total = ivaIncluidoCompras ? valorBruto : valorBruto + iva;
        retencion = calcularRetencionCompras(valorBruto, total);

        if (retencion) {
            total = total - retencion;
        }

        if (redondearFactura) {
            var totalParaRedondear =  parseFloat(total / 1000);
            var totalRedondeado =  totalParaRedondear.toFixed(2) * 1000;
            redondeo = totalRedondeado - total;
            total = totalRedondeado;
        }
    }

    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();

    if (parseFloat(total.toFixed(2)) > 0 && (totalEfectivo + totalOtrosPagos + totalAnticipos) >= parseFloat(total.toFixed(2))) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
        $('#id_vendedor_compra').removeClass("is-invalid");
        $('#id_vendedor_compra').addClass("is-valid");
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }

    if (ivaIncluidoCompras) total = total+= iva;

    return [iva, retencion, descuento, total, valorBruto, redondeo];
}

function calcularRetencionCompras(valorBruto, total) {

    retencion = 0;

    if (responsabilidadesCompra.includes('7')) {
        [base, porcentaje] = obtenerDatosRetencionCompra(valorBruto);
    
        if (total >= base && porcentaje) {
            retencion = valorBruto * (porcentaje  / 100);
        }
    }

    return retencion;
}

function obtenerDatosRetencionCompra(valorSubtotal) {
    porcentaje = 0;
    base = 0;

    retencionesCompras.forEach(retencion => {
        if (retencion.base <= valorSubtotal) {
            if (retencion.base > base ) {
                if (retencion.porcentaje > porcentaje) {
                    porcentaje = retencion.porcentaje;
                    base = retencion.base;
                }
            }
        }
    });

    return [base, porcentaje];
}

function actualizarInfoRetencionCompras() {
    const iconInfo = document.getElementById('icon_info_retencion_compra');
    var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();

    var porcentaje = 0;
    var base = 0;
    var nombre = 'Sin cuenta con retención';
    let total_uvt = 0;

    retencionesCompras.forEach(retencion => {
        if (retencion.base > base ) {
            if (retencion.porcentaje > porcentaje) {
                porcentaje = retencion.porcentaje;
                base = retencion.base;
                total_uvt = retencion.total_uvt;
                nombre = `${retencion.cuenta} - ${retencion.nombre}`;
            }
        }
    });

    $("#nombre_info_retencion_compra").html(`RETENCIÓN %${porcentaje}:`);

    let baseformat = new Intl.NumberFormat('ja-JP').format(base);
    let totalUVTs = new Intl.NumberFormat('ja-JP').format(valor_uvt);
    let valorSubtotal = new Intl.NumberFormat('ja-JP').format(subtotal);
    let responsableRetencion = '';

    if (responsabilidadesCompra.includes('7')) {
        responsableRetencion = `<b class='titulo-popover'>Con responsablidad:</b> 07 => Calcula retención en la fuente`;
    } else {
        responsableRetencion = `<b class='titulo-popover'>Sin responsablidad:</b> 07 => No calcula retención en la fuente`;
    }

    const nuevoTitulo = `
        <b class='titulo-popover'>Cuenta:</b> ${nombre}<br/>
        <b class='titulo-popover'>UVT:</b> ${total_uvt} X ${totalUVTs} = ${baseformat}<br/>
        <b class='titulo-popover'>Subtotal:</b> ${valorSubtotal}<br/>
        ${responsableRetencion}
    `;

    iconInfo.setAttribute('title', nuevoTitulo);
    $(iconInfo).popover('dispose'); // Destruye el actual
    $(iconInfo).popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
}

function changeProductoCompra (idRow) {

    var data = $('#compra_producto_'+idRow).select2('data');
    if (data.length == 0) return;
    data = data[0];
    
    if (data.inventarios.length > 0 && data.familia.inventario) {
        var totalIncomprario = parseFloat(data.inventarios[0].cantidad);
        $("#compra_existencia_"+idRow).val(totalIncomprario);
        $("#compra_cantidad_"+idRow).attr({"max" : totalIncomprario});
    }

    if (data.familia.cuenta_compra_iva && data.familia.cuenta_compra_iva.impuesto) {
        
        $('#compra_iva_porcentaje_'+idRow).val(stringToNumberFloat(data.familia.cuenta_compra_iva.impuesto.porcentaje));
        $('#compra_iva_porcentaje_text_'+idRow).text(stringToNumberFloat(data.familia.cuenta_compra_iva.impuesto.porcentaje)+'%');
    }

    if (data.familia.cuenta_compra_retencion && data.familia.cuenta_compra_retencion.impuesto) {

        var existe = retencionesCompras.findIndex(item => item.id_retencion == data.familia.cuenta_compra_retencion.impuesto.id);
        if (!existe || existe < 0) {
            retencionesCompras.push({
                cuenta: data.familia.cuenta_compra_retencion.cuenta,
                nombre: data.familia.cuenta_compra_retencion.nombre,
                id_retencion: data.familia.cuenta_compra_retencion.impuesto.id,
                porcentaje: parseFloat(data.familia.cuenta_compra_retencion.impuesto.porcentaje),
                base: parseFloat(data.familia.cuenta_compra_retencion.impuesto.base),
                total_uvt: parseFloat(data.familia.cuenta_compra_retencion.impuesto.total_uvt)
            })
        }
    }

    if (data.familia.id_cuenta_compra_descuento && compraDescuento) {
        $('#compra_descuento_valor_'+idRow).prop('disabled', false);
        $('#compra_descuento_porcentaje_'+idRow).prop('disabled', false);
    } else {
        $('#compra_descuento_valor_'+idRow).prop('disabled', true);
        $('#compra_descuento_porcentaje_'+idRow).prop('disabled', true);
    }

    $('#compra_costo_'+idRow).val(formatCurrencyValue(data.precio_inicial));
    $('#compra_producto_'+idRow).select2('open');
    $('#compra_cantidad_'+idRow).prop('disabled', false);
    $('#compra_costo_'+idRow).prop('disabled', false);
    $('#compra_concepto_'+idRow).prop('disabled', false);
        
    calcularProductoCompra(idRow);
    clearFormasPagoCompra();
    
    setTimeout(function(){
        $('#compra_cantidad_'+idRow).focus();
        $('#compra_cantidad_'+idRow).select();
    },10);
}

function cantidadComprakeyDown (idRow, event) {
    if(event.keyCode == 13){
        key13PressNewRow = true;
        if (!validarExistenciasCompras(idRow)) return;
    }
}

function CostoComprakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoCompra (idRow);
        addRowProductoCompra();
    }
}

function DescuentoComprakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoCompra(idRow);
        setTimeout(function(){
            $('#compra_descuento_valor_'+idRow).focus();
            $('#compra_descuento_valor_'+idRow).select();
        },10);
    }
}

function DescuentoTotalComprakeyDown (idRow, event) {
    if(event.keyCode == 13){
        var descuentoProductoValor = stringToNumberFloat($('#compra_descuento_valor_'+idRow).val());
        var costoProducto = stringToNumberFloat($('#compra_costo_'+idRow).val());
        var cantidadProducto = $('#compra_cantidad_'+idRow).val();
        var porcentajeDescuento = descuentoProductoValor * 100 / (costoProducto * cantidadProducto);

        $('#compra_descuento_porcentaje_'+idRow).val(formatCurrencyValue(porcentajeDescuento));
        
        calcularProductoCompra(idRow);
        setTimeout(function(){
            $('#compra_iva_porcentaje_'+idRow).focus();
            $('#compra_iva_porcentaje_'+idRow).select();
        },10);
    }
}

function IvaComprakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoCompra(idRow);
        addRowProductoCompra();
    }
}

function cancelarCompra() {
    idCompraProducto = 0;
    var totalRows = compra_table.rows().data().length;

    if(compra_table.rows().data().length){
        compra_table.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            compra_table.row(0).remove().draw();
        }
        mostrarValoresCompras();
        actualizarInfoRetencionCompras();
    }

    $("#id_bodega_compra").prop('disabled', false);
    $("#id_comprobante_compra").prop('disabled', false);

    $("#iniciarCapturaCompra").show();
    $('#agregarCompraProducto').hide();
    $("#crearCapturaCompra").hide();
    $("#cancelarCapturaCompra").hide();
    $("#crearCapturaCompraDisabled").hide();
    $('#input-anticipos-compra').hide();

    cargarFechasCompras();

    setTimeout(function(){
        $comboCliente.select2("open");
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
    compra_table_pagos.ajax.reload(function(res) {
        disabledFormasPagoCompra();
    });
}

function clearFormasPagoCompra() {
    var dataFormasPago = compra_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#compra_forma_pago_'+formaPago.id).val(0);
        }
    }
    calcularCompraPagos();
}

function disabledFormasPagoCompra(estado = true) {
    const dataFormasPago = compra_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {

            const formaPago = dataFormasPago[index];
            const tiposCuentas = formaPago.cuenta.tipos_cuenta;

            if (!tiposCuentas.length) {
                $('#compra_forma_pago_'+formaPago.id).prop('disabled', estado);
                continue;
            }

            for (let index = 0; index < tiposCuentas.length; index++) {
                let isAnticipo = false;
                const tipoCuenta = tiposCuentas[index];

                if (tipoCuenta.id_tipo_cuenta == 8) {
                    isAnticipo = true;
                }

                if (isAnticipo) {
                    let cuentaExistente = encontrarCuentaCompra(formaPago.cuenta.id);
    
                    if (cuentaExistente) {
                        let totalSaldoAnticipos = cuentaExistente[formaPago.cuenta.id].saldo;
                        if (totalSaldoAnticipos) {
                            $('#compra_forma_pago_'+formaPago.id).prop('disabled', estado);
                        }
                    } else {
                        $('#compra_forma_pago_'+formaPago.id).prop('disabled', true);
                    }
                } else {
                    $('#compra_forma_pago_'+formaPago.id).prop('disabled', estado);
                }
            }
        }
    }
}

function focusFormaPagoCompra(idFormaPago, anticipo = false, id_cuenta = null) {

    if (guardandoCompra) {
        return;
    }

    var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras(idFormaPago);
    var totalFactura = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    totalFactura = totalFactura < 0 ? 0 : totalFactura;
    var saldoFormaPago = stringToNumberFloat($('#compra_forma_pago_'+idFormaPago).val());

    if (anticipo) {

        let cuentaExistente = encontrarCuentaCompra(id_cuenta);
        if (cuentaExistente) {
            let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
            if ((totalSaldoAnticipos - totalAnticipos) < totalFactura) {
                $('#compra_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalSaldoAnticipos - totalAnticipos));
                $('#compra_forma_pago_'+idFormaPago).select();
                return;
            }
        }
        return;
    }

    if (!saldoFormaPago) {
        $('#compra_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format(totalFactura < 0 ? 0 : totalFactura));
    };
    $('#compra_forma_pago_'+idFormaPago).select();
}

function calcularCompraPagos(idFormaPago) {

    if (guardandoCompra) {
        return;
    }

    if (
        $('#compra_forma_pago_'+idFormaPago).val() == '' ||
        $('#compra_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#compra_forma_pago_'+idFormaPago).val(0);
    }

    $('#total_faltante_compra').removeClass("is-invalid");

    var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();

    total = parseFloat(total.toFixed(2));

    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();
    var totalFaltante = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    
    if ((totalOtrosPagos + totalEfectivo + totalAnticipos) >= total) {
        var totalCambio = (totalEfectivo + totalOtrosPagos + totalAnticipos) - total;
        if(parseInt(totalCambio) > 0)$('#cambio-totals').show();
        document.getElementById('total_cambio_compra').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalCambio);
    } else {
        $('#cambio-totals').hide();
        if (totalFaltante < 0) {
            $('#compra_forma_pago_'+idFormaPago).val(totalFaltante * -1);
            $('#compra_forma_pago_'+idFormaPago).select();
        }
    }
    var totalPagado = totalFaltante < 0 ? total : totalEfectivo + totalOtrosPagos + totalAnticipos;
    var totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    var countA = new CountUp('total_pagado_compra', 0, totalPagado, 2, 0.5);
        countA.start();

    var countB = new CountUp('total_faltante_compra', 0, totalFaltante, 2, 0.5);
        countB.start();
}

function totalFormasPagoCompras(idFormaPago = null) {
    var totalEfectivo = 0;
    var totalAnticipos = 0;
    var totalOtrosPagos = 0;

    var dataPagoCompra = compra_table_pagos.rows().data();

    if(dataPagoCompra.length > 0) {
        for (let index = 0; index < dataPagoCompra.length; index++) {
            
            var compraPago = stringToNumberFloat($('#compra_forma_pago_'+dataPagoCompra[index].id).val());
            
            if (idFormaPago && idFormaPago == dataPagoCompra[index].id) continue;

            if (dataPagoCompra[index].id == 1) totalEfectivo+= compraPago;
            else if ($('#compra_forma_pago_'+dataPagoCompra[index].id).hasClass("anticipos")) totalAnticipos+= compraPago;
            else totalOtrosPagos+= compraPago;
        }
    }

    return [totalEfectivo, totalOtrosPagos, totalAnticipos];
}

function validateSaveCompra() {
    $('#total_faltante_compra_text').css("color","#484848");
    $('#total_faltante_compra').css("color","#484848");

    if (!guardandoCompra) {

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

        total = parseFloat(total.toFixed(2));

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
            
            guardandoCompra = true;
            saveCompra();
        } else {
            $('#total_faltante_compra_text').css("color","red");
            $('#total_faltante_compra').css("color","red");
        }
    }
}

function saveCompra() {

    $("#crearCapturaCompra").hide();
    $("#crearCapturaCompraDisabled").hide();
    $("#crearCapturaCompraLoading").show();
    
    let data = {
        pagos: getComprasPagos(),
        productos: getProductosCompra(),
        id_bodega: $("#id_bodega_compra").val(),
        id_proveedor: $("#id_cliente_compra").val(),
        fecha_manual: $("#fecha_manual_compra").val(),
        id_comprobante: $("#id_comprobante_compra").val(),
        documento_referencia: $("#documento_referencia_compra").val(),
        observacion: $("#observacion_compra").val(),
    }

    disabledFormasPagoCompra();

    $.ajax({
        url: base_url + 'compras',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoCompra = false;
        if(res.success){

            agregarToast('exito', 'Creación exitosa', 'Compra creada con exito!', true);

            $("#crearCapturaCompra").show();
            $("#crearCapturaCompraLoading").hide();

            if(res.impresion) {
                window.open("/compras-print/"+res.impresion, '_blank');
            }
            idCompraProducto = 0;

            $('#iniciarCapturaCompra').hide();
            $('#iniciarCapturaCompraLoading').hide();
            $('#documento_referencia_compra').val('');

            var totalRows = compra_table.rows().data().length;
            for (let index = 0; index < totalRows; index++) {
                compra_table.row(0).remove().draw();
            }

            setTimeout(function(){
                $('#id_cliente_compra').focus();
                $comboCliente.select2("open");
            },10);

            loadAnticiposClienteCompras();
            disabledFormasPagoCompra();
            cargarFechasCompras();

        } else {

            guardandoCompra = false;
            $("#crearCapturaCompra").show();
            $("#crearCapturaCompraLoading").hide();
            
            disabledFormasPagoCompra(false);
            var mensaje = res.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    }).fail((err) => {
        guardandoCompra = false;
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraLoading").hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });

}

function getComprasPagos() {
    var data = [];

    var dataCompraPagos = compra_table_pagos.rows().data();

    if(!dataCompraPagos.length > 0) return data;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoCompra = dataCompraPagos[index];
        var pagoCompra = stringToNumberFloat($('#compra_forma_pago_'+dataPagoCompra.id).val());
        if (pagoCompra > 0) {
            data.push({
                id: dataPagoCompra.id,
                valor: pagoCompra
            });
        }
    }

    return data;
}

function getProductosCompra() {
    var data = [];

    var dataCompraProductos = compra_table.rows().data();

    if(!dataCompraProductos.length > 0) return data;

    for (let index = 0; index < dataCompraProductos.length; index++) {

        const id_row = dataCompraProductos[index].id;
        var id_producto = $('#compra_producto_'+id_row).val();
        var cantidad = parseFloat($('#compra_cantidad_'+id_row).val());
        
        if (id_producto && cantidad) {
            let costo = stringToNumberFloat($('#compra_costo_'+id_row).val());
            let descuento_porcentaje = stringToNumberFloat($('#compra_descuento_porcentaje_'+id_row).val());
            let descuento_valor = stringToNumberFloat($('#compra_descuento_valor_'+id_row).val());
            let iva_porcentaje = stringToNumberFloat($('#compra_iva_porcentaje_'+id_row).val());
            let iva_valor = stringToNumberFloat($('#compra_iva_valor_'+id_row).val());
            let total = stringToNumberFloat($('#compra_total_'+id_row).val());
            let subtotal = cantidad * costo;
            let concepto = $('#compra_concepto_'+id_row).val();

            if(ivaIncluidoCompras) {
                subtotal-= iva_valor;
            }

            data.push({
                id_producto: parseInt(id_producto),
                cantidad: cantidad,
                costo: costo ? costo : 0,
                subtotal: subtotal,
                descuento_porcentaje: descuento_porcentaje ? descuento_porcentaje : 0,
                descuento_valor: descuento_valor ? descuento_valor : 0,
                iva_porcentaje: iva_porcentaje ? iva_porcentaje : 0,
                iva_valor: iva_valor ? iva_valor : 0,
                total: total ? total : 0,
                concepto: concepto ? concepto : null,
            });
        }
    }

    return data;
}

function changeFormaPagoCompras(idFormaPago, anticipo, event, id_cuenta) {

    if (guardandoCompra) {
        return;
    }

    if(event.keyCode == 13){

        calcularCompraPagos(idFormaPago);

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();
        var totalFaltante = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);

        if (!total) {
            return;
        }

        if (anticipo) {

            let cuentaExistente = encontrarCuentaCompra(id_cuenta);
            if (cuentaExistente) {

                let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
                if (totalAnticipos > totalSaldoAnticipos) {
                    $('#compra_forma_pago_'+idFormaPago).val(totalSaldoAnticipos);
                    $('#compra_forma_pago_'+idFormaPago).select();
                    calcularCompraPagos();
                    return;
                } else if (totalAnticipos > (totalFaltante + totalAnticipos)) {
                    $('#compra_forma_pago_'+idFormaPago).val(totalFaltante);
                    $('#compra_forma_pago_'+idFormaPago).select();
                    calcularCompraPagos();
                    return;
                }
            }
        }

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
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
        const input = document.getElementById("compra_forma_pago_"+dataPagoCompra.id);

        if (input.disabled) {
            continue;
        }
        
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoCompra.id;
            obtenerFormaPago = false;
        } else if (dataPagoCompra.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoCompra(idFormaPagoFocus);
}

function openModalNewNitCompras() {
    clearFormNitsCompra();
    $("#nitCompraFormModal").modal('show');
}

function clearFormNitsCompra() {
    $("#id_tipo_documento_compra_nit").val('').change();
    $("#id_ciudad_compra_nit").val('').change();
    $("#observaciones_compra_nit").val('');
    $("#numero_documento_compra_nit").val('');
    $("#tipo_contribuyente_compra_nit").val(2).change();
    $("#primer_apellido_compra_nit").val('');
    $("#segundo_apellido_compra_nit").val('');
    $("#primer_nombre_compra_nit").val('');
    $("#otros_nombres_compra_nit").val('');
    $("#razon_social_compra_nit").val('');
    $("#telefono_1_compra_nit").val('');
    $("#direccion_compra_nit").val('');
    $("#email_compra_nit").val('');
}

$(document).on('click', '#saveNitCompra', function () {
    var form = document.querySelector('#compraNitsForm');

    if(form.checkValidity()){

        $("#saveNitCompraLoading").show();
        $("#saveNitCompra").hide();

        let data = {
            id_tipo_documento: $("#id_tipo_documento_compra_nit").val(),
            numero_documento: $("#numero_documento_compra_nit").val(),
            tipo_contribuyente: $("#tipo_contribuyente_compra_nit").val(),
            primer_apellido: $("#primer_apellido_compra_nit").val(),
            segundo_apellido: $("#segundo_apellido_compra_nit").val(),
            primer_nombre: $("#primer_nombre_compra_nit").val(),
            otros_nombres: $("#otros_nombres_compra_nit").val(),
            razon_social: $("#razon_social_compra_nit").val(),
            direccion: $("#direccion_compra_nit").val(),
            email: $("#email_compra_nit").val(),
            telefono_1: $("#telefono_1_compra_nit").val(),
            id_ciudad: $("#id_ciudad_compra_nit").val(),
            observaciones: $("#observaciones_compra_nit").val(),
        }

        $.ajax({
            url: base_url + 'nit',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                clearFormNitsCompra();
                $("#saveNitCompra").show();
                $("#saveNitCompraLoading").hide();
                $("#nitCompraFormModal").modal('hide');

                var dataCliente = {
                    id: res.data.id,
                    text: res.data.numero_documento + ' - ' + res.data.nombre_completo
                };
                var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
                $comboCliente.append(newOption).trigger('change');
                $comboCliente.val(dataCliente.id).trigger('change');

                agregarToast('exito', 'Creación exitosa', 'Cedula nit creada con exito!', true);

                document.getElementById('iniciarCapturaCompra').click();
            }
        }).fail((err) => {
            $('#saveNitCompra').show();
            $('#saveNitCompraLoading').hide();
            
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    } else {
        form.classList.add('was-validated');
    }
});

$(document).on('click', '#cancelarCapturaCompra', function () {
    cancelarCompra();
});

$(document).on('click', '#crearCapturaCompra', function () {
    validateSaveCompra();
});

$('#id_cliente_compra').on('select2:close', function(event) {
    var data = $(this).select2('data');
    if(data.length){
        setTimeout(function(){
            $('#documento_referencia_compra').focus();
        },10);
    }
});

$("#fecha_manual_compra").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        setTimeout(function(){
            $('#documento_referencia_compra').focus();
            $('#documento_referencia_compra').select();
        },10);
    }
});

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
            if(res.data == 0 || !res.data){
                $('#documento_referencia_compra').removeClass("is-invalid");
                $('#documento_referencia_compra').addClass("is-valid");
                
                calcularCompraPagos();
                clearFormasPagoCompra();
                document.getElementById('iniciarCapturaCompra').click();

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

$(document).on('keydown', '.custom-compra_producto .select2-search__field', function (event) {

    if (guardandoCompra) {
        return;
    }

    var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();
    var dataSearch = $('.select2-search__field').val();
    
    if (event.keyCode == 96 && !dataSearch.length) {
        abrirFormasPagoCompras = true;
    } else if (event.keyCode == 13){
        if (total > 0) {
            if (abrirFormasPagoCompras) {
                abrirFormasPagoCompras = false;
                $(".compra_producto").select2('close');
                focusFormaPagoCompra(1);
            }
        }
    } else {
        abrirFormasPagoCompras = false;
    }
});

$(document).on('keydown', '.custom-id_cliente_compra .select2-search__field', function (event) {
    if (event.keyCode == 13){
        openModalNewNitCompras();
        var documentoBuscado = $('.select2-search__field').val();
        $comboCliente.select2('close');
        $("#numero_documento_compra_nit").val(documentoBuscado);
    }
});

$("#id_vendedor_compra").on('change', function(event) {
    if ($("#id_vendedor_compra").val()) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }
});

$("#fecha_manual_compra").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        setTimeout(function(){
            $('#documento_referencia_compra').focus();
            $('#documento_referencia_compra').select();
        },10);
    }
});

$("#documento_referencia_compra").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        document.getElementById('iniciarCapturaCompra').click();
    }
});

$(document).on('click', '#iniciarCapturaCompra', function () {
    var form = document.querySelector('#compraFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        $("#error_documento_referencia_compra").text('El No. factura requerido');
        return;
    }
    
    $("#iniciarCapturaCompra").hide();
    $('#agregarCompraProducto').show();
    $("#crearCapturaCompra").hide();
    $("#cancelarCapturaCompra").show();
    $('#cambio-totals').hide();
    $("#crearCapturaCompraDisabled").show();

    addRowProductoCompra();
});

$(document).on('click', '#agregarCompraProducto', function () {
    addRowProductoCompra();
});
