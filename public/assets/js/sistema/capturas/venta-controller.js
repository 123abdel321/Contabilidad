let fecha = null;
let venta_table = null;
let idVentaProducto = 0;
let guardarVenta = false;
let $comboCliente = null;
let createNewNit = false;
let $comboVendedor = null;
let retencionesVentas = [];
let topeRetencionVenta = 0;
let guardandoVenta = false;
let $comboResolucion = null;
let $comboBodegaVenta = null;
let key13PressNewRow = false;
let redondearFactura = false;
let venta_table_pagos = null;
var totalAnticiposVenta = null;
let validarFacturaVenta = null;
let porcentajeRetencionVenta = 0;
let responsabilidadesVenta = [];
let abrirFormasPagoVentas = false;
let totalAnticiposDisponibles = 0;
var totalAnticiposVentaCuenta = null;
let validarExistenciasProducto = null;

function ventaInit () {

    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_venta').val(fecha);

    if (ventaFecha) $("#fecha_manual_venta").prop('disabled', false);
    else $("#fecha_manual_venta").prop('disabled', true);

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
                    return `<select
                            class="form-control form-control-sm venta_producto combo-grid"
                            id="venta_producto_${idVentaProducto}"
                            onchange="changeProductoVenta(${idVentaProducto})"
                            onfocusout="calcularProductoVenta(${idVentaProducto})"
                            inert
                        ></select>`;
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
                    return `<div class="form-group mb-3" style="min-width: 85px;">
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
            {//CONCEPTO
                "data": function (row, type, set, col){
                    return `<input type="text" class="form-control form-control-sm" id="venta_concepto_${row.id}" onkeypress="changeObservacionGasto(${row.id}, event)" onfocus="this.select();" onfocusout="changeObservacionGasto(${row.id})" style="width: 180px !important;" value="${row.concepto}" disabled>`;
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
            {"data": function (row, type, set){
                let styles = "margin-bottom: 0px; font-size: 13px;";
                let stylesInfo = null;
                let naturaleza = 'Dedito - Ventas';
                if (row.cuenta.naturaleza_ventas) {
                    naturaleza = 'Error de naturaleza en egreso';
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
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoVenta(${row.id}, ${anticipos}, ${id_cuenta})" onfocusout="calcularVentaPagos(${row.id}, ${anticipos})" onkeypress="changeFormaPago(${row.id}, ${anticipos}, event,  ${id_cuenta})" id="venta_forma_pago_${row.id}">`;
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
            responsabilidadesVenta = getResponsabilidades(data[0].id_responsabilidades);
            actualizarInfoRetencionVentas();
            
            if (vendedoresVentas) loadVendedorCliente();
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

    if (!primeraBodegaVenta || !primeraBodegaVenta.length) {
        agregarToast('warning', 'Sin bodegas asignadas', '', true);
    }

    if (!primeraResolucionVenta || !primeraResolucionVenta.length) {
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
        responsabilidadesVenta = getResponsabilidades(primeraNit.id_responsabilidades);
        actualizarInfoRetencionVentas();

        if (primeraNit.vendedor) {
            var dataVendedor = {
                id: primeraNit.vendedor.nit.id,
                text: primeraNit.vendedor.nit.numero_documento + ' - ' + primeraNit.vendedor.nit.nombre_completo
            };
            var newOption = new Option(dataVendedor.text, dataVendedor.id, false, false);
            $comboVendedor.append(newOption).trigger('change');
            $comboVendedor.val(dataVendedor.id).trigger('change');
        }

        loadAnticiposCliente();
        
        document.getElementById('iniciarCapturaVenta').click();

    } else {
        setTimeout(function(){
            $comboCliente.select2("open");
        },10);
    }

    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
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
    $('#venta_anticipo_disp_view').hide();
    $('#saldo_anticipo_venta').val(0);
    $('#venta_anticipo_disp').text('0.00');

    if(!$('#id_cliente_venta').val()) return;

    let data = {
        id_nit: $('#id_cliente_venta').val(),
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

            totalAnticiposVenta = 0;
            totalAnticiposVentaCuenta = [];

            if (res.data.length) {
                const anticiposDisponibles = res.data;
                for (let index = 0; index < anticiposDisponibles.length; index++) {
                    const anticipo = anticiposDisponibles[index];

                    let idCuenta = anticipo.id_cuenta;
                    let cuentaExistente = encontrarCuentaVenta(idCuenta);
                    
                    totalAnticiposVenta+= Math.abs(parseFloat(anticipo.saldo));

                    if (cuentaExistente) {
                        cuentaExistente[idCuenta].saldo = (cuentaExistente[idCuenta].saldo || 0) + parseFloat(anticipo.saldo);
                    } else {
                        let nuevoObj = {};
                        nuevoObj[idCuenta] = {
                            'id_cuenta': idCuenta,
                            'saldo': Math.abs(parseFloat(anticipo.saldo))
                        };
                        totalAnticiposVentaCuenta.push(nuevoObj);
                    }
                }

                if (totalAnticiposVenta) {
                    $('#venta_anticipo_disp_view').show();
                    $('#saldo_anticipo_venta').val(new Intl.NumberFormat('ja-JP').format(totalAnticiposVenta));
                    $('#venta_anticipo_disp').text(new Intl.NumberFormat('ja-JP').format(totalAnticiposVenta));
                }
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function encontrarCuentaVenta(idCuenta) {
    if (totalAnticiposVentaCuenta && totalAnticiposVentaCuenta.length) {
        return totalAnticiposVentaCuenta.find(item => item[idCuenta]);
    }
    return false;
}

function loadVendedorCliente() {
    
    let data = {
        id: $('#id_cliente_venta').val(),
    }

    $.ajax({
        url: base_url + 'nit',
        method: 'GET',
        data: data,
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        
        if(res.success){
            if (res.data[0].vendedor) {
                var vendedor = res.data[0].vendedor.nit;
                var dataVendedor = {
                    id: vendedor.id,
                    text: vendedor.numero_documento + ' - ' + vendedor.nombre_completo
                };
                var newOption = new Option(dataVendedor.text, dataVendedor.id, false, false);
                $comboVendedor.append(newOption).trigger('change');
                $comboVendedor.val(dataVendedor.id).trigger('change');
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

$(document).on('keydown', '.custom-venta_producto .select2-search__field', function (event) {

    if (guardandoVenta) {
        return;
    }

    var [iva, retencion, descuento, total, valorBruto] = totalValoresVentas();
    var dataSearch = $('.select2-search__field').val();
    
    if (event.keyCode == 96 && !dataSearch.length) {
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
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
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
        "concepto": "",
    }).draw(false);

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
    actualizarInfoRetencionVentas();
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
        totalDescuento = totalPorCantidad * (descuentoProducto / 100);
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
        }
        
        $('#venta_iva_valor_'+idRow).val(formatCurrencyValue(totalIva));
    }

    if (!ivaIncluidoVentas) {
        totalProducto+= totalIva;
    }
    
    $('#venta_total_'+idRow).val(formatCurrencyValue(totalProducto));

    mostrarValoresVentas();
    actualizarInfoRetencionVentas();
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
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
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

    if (total) disabledFormasPagoVenta(false);
    else disabledFormasPagoVenta();

    var countA = new CountUp('venta_total_iva', 0, iva, 2, 0.5);
        countA.start();

    var countB = new CountUp('venta_total_descuento', 0, descuento, 2, 0.5);
        countB.start();

    var countC = new CountUp('venta_total_retencion', 0, retencion, 2, 0.5);
        countC.start();

    var countD = new CountUp('venta_total_valor', 0, total, 2, 0.5);
        countD.start();
        
    var countE = new CountUp('venta_sub_total', 0, valorBruto, 2, 0.5);
        countE.start();

    var countF = new CountUp('total_faltante_venta', 0, total, 2, 0.5);
        countF.start();
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
                valorBruto+= (cantidad*costo) - descSum;
            }
        }

        if (ivaIncluidoVentas) valorBruto-= iva;

        total = ivaIncluidoVentas ? valorBruto : valorBruto + iva;
        retencion = calcularRetencionVentas(valorBruto, total);

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

    if (ivaIncluidoVentas) total = total+= iva;

    return [iva, retencion, descuento, total, valorBruto, redondeo];
}

function calcularRetencionVentas(valorBruto, total) {

    retencion = 0;

    if (responsabilidadesVenta.includes('7')) {
        [base, porcentaje] = obtenerDatosRetencionVenta(valorBruto);
    
        if (total >= base && porcentaje) {
            retencion = valorBruto * (porcentaje  / 100);
        }
    }

    return retencion;
}

function obtenerDatosRetencionVenta(valorSubtotal) {
    porcentaje = 0;
    base = 0;

    retencionesVentas.forEach(retencion => {
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

function actualizarInfoRetencionVentas() {
    const iconInfo = document.getElementById('icon_info_retencion_venta');
    var [iva, retencion, descuento, total, subtotal] = totalValoresVentas();

    var porcentaje = 0;
    var base = 0;
    var nombre = 'Sin cuenta con retención';
    let total_uvt = 0;

    retencionesVentas.forEach(retencion => {
        if (retencion.base > base ) {
            if (retencion.porcentaje > porcentaje) {
                porcentaje = retencion.porcentaje;
                base = retencion.base;
                total_uvt = retencion.total_uvt;
                nombre = `${retencion.cuenta} - ${retencion.nombre}`;
            }
        }
    });

    $("#nombre_info_retencion_venta").html(`RETENCIÓN %${porcentaje}:`);

    let baseformat = new Intl.NumberFormat('ja-JP').format(base);
    let totalUVTs = new Intl.NumberFormat('ja-JP').format(valor_uvt);
    let valorSubtotal = new Intl.NumberFormat('ja-JP').format(subtotal);
    let responsableRetencion = '';

    if (responsabilidadesVenta.includes('7')) {
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

        var existe = retencionesVentas.findIndex(item => item.id_retencion == data.familia.cuenta_venta_retencion.impuesto.id);
        if (!existe || existe < 0) {
            retencionesVentas.push({
                cuenta: data.familia.cuenta_venta_retencion.cuenta,
                nombre: data.familia.cuenta_venta_retencion.nombre,
                id_retencion: data.familia.cuenta_venta_retencion.impuesto.id,
                porcentaje: parseFloat(data.familia.cuenta_venta_retencion.impuesto.porcentaje),
                base: parseFloat(data.familia.cuenta_venta_retencion.impuesto.base),
                total_uvt: parseFloat(data.familia.cuenta_venta_retencion.impuesto.total_uvt)
            })
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
    $('#venta_concepto_'+idRow).prop('disabled', false);
        
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
        actualizarInfoRetencionVentas();
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
    const dataFormasPago = venta_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {

            const formaPago = dataFormasPago[index];
            const tiposCuentas = formaPago.cuenta.tipos_cuenta;

            if (!tiposCuentas.length) {
                $('#venta_forma_pago_'+formaPago.id).prop('disabled', estado);
                continue;
            }

            for (let index = 0; index < tiposCuentas.length; index++) {
                let isAnticipo = false;
                const tipoCuenta = tiposCuentas[index];

                if (tipoCuenta.id_tipo_cuenta == 8) {
                    isAnticipo = true;
                }

                if (isAnticipo) {
                    let cuentaExistente = encontrarCuentaVenta(formaPago.cuenta.id);
    
                    if (cuentaExistente) {
                        let totalSaldoAnticipos = cuentaExistente[formaPago.cuenta.id].saldo;
                        if (totalSaldoAnticipos) {
                            $('#venta_forma_pago_'+formaPago.id).prop('disabled', estado);
                        }
                    } else {
                        $('#venta_forma_pago_'+formaPago.id).prop('disabled', true);
                    }
                } else {
                    $('#venta_forma_pago_'+formaPago.id).prop('disabled', estado);
                }
            }
        }
    }
}

$(document).on('click', '#crearCapturaVenta', function () {
    validateSaveVenta();
});

function focusFormaPagoVenta(idFormaPago, anticipo = false, id_cuenta = null) {

    if (guardandoVenta) {
        return;
    }

    var [iva, retencion, descuento, total, subtotal] = totalValoresVentas();
    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoVentas(idFormaPago);
    var totalFactura = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    totalFactura = totalFactura < 0 ? 0 : totalFactura;
    var saldoFormaPago = stringToNumberFloat($('#venta_forma_pago_'+idFormaPago).val());

    if (anticipo) {

        let cuentaExistente = encontrarCuentaVenta(id_cuenta);
        if (cuentaExistente) {
            let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
            if ((totalSaldoAnticipos - totalAnticipos) < totalFactura) {
                $('#venta_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalSaldoAnticipos - totalAnticipos));
                $('#venta_forma_pago_'+idFormaPago).select();
                return;
            }
        }
        return;
    }

    if (!saldoFormaPago) {
        $('#venta_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format(totalFactura < 0 ? 0 : totalFactura));
    };
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

    var countA = new CountUp('total_pagado_venta', 0, totalPagado, 2, 0.5);
        countA.start();

    var countB = new CountUp('total_faltante_venta', 0, totalFaltante, 2, 0.5);
        countB.start();
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
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
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
            let costo = stringToNumberFloat($('#venta_costo_'+id_row).val());
            let descuento_porcentaje = stringToNumberFloat($('#venta_descuento_porcentaje_'+id_row).val());
            let descuento_valor = stringToNumberFloat($('#venta_descuento_valor_'+id_row).val());
            let iva_porcentaje = stringToNumberFloat($('#venta_iva_porcentaje_'+id_row).val());
            let iva_valor = stringToNumberFloat($('#venta_iva_valor_'+id_row).val());
            let total = stringToNumberFloat($('#venta_total_'+id_row).val());
            let subtotal = parseInt(cantidad) * costo;
            let concepto = $('#venta_concepto_'+id_row).val();

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
                concepto: concepto ? concepto : null,
            });
        }
    }

    return data;
}

function changeFormaPago(idFormaPago, anticipo, event, id_cuenta) {

    if (guardandoVenta) {
        return;
    }

    if(event.keyCode == 13){

        calcularVentaPagos(idFormaPago);

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoVentas();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresVentas();
        var totalFaltante = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);

        if (!total) {
            return;
        }

        if (anticipo) {

            let cuentaExistente = encontrarCuentaVenta(id_cuenta);
            if (cuentaExistente) {

                let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
                if (totalAnticipos > totalSaldoAnticipos) {
                    $('#venta_forma_pago_'+idFormaPago).val(totalSaldoAnticipos);
                    $('#venta_forma_pago_'+idFormaPago).select();
                    calcularVentaPagos();
                    return;
                } else if (totalAnticipos > (totalFaltante + totalAnticipos)) {
                    $('#venta_forma_pago_'+idFormaPago).val(totalFaltante);
                    $('#venta_forma_pago_'+idFormaPago).select();
                    calcularVentaPagos();
                    return;
                }
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
        const dataPagoVenta = dataVentaPagos[index];
        const input = document.getElementById("venta_forma_pago_"+dataPagoVenta.id);

        if (input.disabled) {
            continue;
        }
        
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoVenta.id;
            obtenerFormaPago = false;
        } else if (dataPagoVenta.id == idFormaPago) {
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
            
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    } else {
        form.classList.add('was-validated');
    }
});

