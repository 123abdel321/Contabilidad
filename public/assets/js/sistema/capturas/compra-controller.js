// =====================================================================
// VARIABLES GLOBALES
// =====================================================================
var fecha = null;
var compra_table = null;
var idCompraProducto = 0;
var guardarCompra = false;
var $comboProveedorCompras = null;
var createNewNit = false;
var $comboVendedorCompras = null;
var retencionesCompras = [];
var topeRetencionCompra = 0;
var guardandoCompra = false;
var calculaRetencionCompra = null;
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
var modoCapturaCompra = 'compra';

// =====================================================================
// INIT
// =====================================================================
function compraInit() {
    cargarchangesCompra();
    cargarFechasCompras();
    cargarTablasCompras();
    cargarCombosCompras();
    cargarPopoverCompras();
    cargarFormasDePagosCompras();

    $('.water').hide();
}

function cargarPopoverCompras() {
    _compraPopovers();
}

function cargarFechasCompras() {
    const dateNow = new Date();
    const fechaHoraCompra = dateNow.getFullYear() + '-' +
        ("0" + (dateNow.getMonth() + 1)).slice(-2) + '-' +
        ("0" + dateNow.getDate()).slice(-2) + 'T' +
        ("0" + dateNow.getHours()).slice(-2) + ':' +
        ("0" + dateNow.getMinutes()).slice(-2);
    $('#fecha_manual_compra').val(fechaHoraCompra);
}

function cargarchangesCompra() {
    $(document).on('click', '#toggleDocumentoSoporteCompra', toggleModoCapturaCompra);
}

// =====================================================================
// HELPERS PRIVADOS
// =====================================================================
function _compraVal(id, campo) {
    return stringToNumberFloat($('#compra_' + campo + '_' + id).val());
}

function _compraFmt(n) {
    return new Intl.NumberFormat('ja-JP').format(n);
}

function _compraFmtCurrency(n) {
    return formatCurrencyValue(n);
}

function _compraGetRowIndex(id) {
    return getIndexById(id, compra_table);
}

function _compraGetRowData(id) {
    const rows = compra_table.rows().data();
    for (let i = 0; i < rows.length; i++) {
        if (rows[i].id == id) return rows[i];
    }
    return null;
}

function _compraApplyCurrencyMask() {
    $("input[data-type='currency']").on({
        keyup: function (event) {
            if (event.keyCode >= 96 && event.keyCode <= 105 ||
                event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
                formatCurrency($(this));
            }
        },
        blur: function () {
            formatCurrency($(this), "blur");
        }
    });
}

function _compraPopovers() {
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
}

function _compraShowCreate(show) {
    if (show) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }
}

function _compraFocusLater(selector, id) {
    setTimeout(function () {
        const $el = $(selector + id);
        if ($el.length) {
            $el.focus();
            $el.select();
        }
    }, 10);
}

function _compraToastError(err) {
    let mensaje = 'Error';
    try { mensaje = err.responseJSON.message; } catch (e) { }
    const errorsMsg = arreglarMensajeError(mensaje);
    agregarToast('error', 'Creación errada', errorsMsg);
}

// =====================================================================
// COMBOS
// =====================================================================
function cargarCombosCompras() {
    $comboProveedorCompras = $('#id_proveedor_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownCssClass: 'custom-id_proveedor_compra',
        language: {
            noResults: function () {
                createNewNit = true;
                return "No hay resultado";
            },
            searching: function () {
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
                return { results: data.data };
            }
        }
    });

    $comboVendedorCompras = $('#id_vendedor_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
        allowClear: true,
        language: {
            noResults: function () { return "No hay resultado"; },
            searching: function () { return "Buscando.."; },
            inputTooShort: function () { return "Por favor introduce 1 o más caracteres"; }
        },
        ajax: {
            url: 'api/vendedores/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                const data_modified = $.map(data.data, function (obj) {
                    obj.text = obj.nit.nombre_completo;
                    return obj;
                });
                return { results: data_modified };
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
                return { q: params.term, tipo_comprobante: 2, _type: 'query' };
            },
            dataType: 'json',
            processResults: function (data) {
                return { results: data.data };
            }
        }
    });

    $comboBodegaCompra = $('#id_bodega_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function () { return "No hay resultado"; },
            searching: function () { return "Buscando.."; },
            inputTooShort: function () { return "Por favor introduce 1 o más caracteres"; }
        },
        ajax: {
            url: 'api/bodega/combo-bodega',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return { results: data.data };
            }
        }
    });

    if (primeraComprobanteCompra) {
        const dataResolucion = {
            id: primeraComprobanteCompra.id,
            text: primeraComprobanteCompra.codigo + ' - ' + primeraComprobanteCompra.nombre
        };
        const newOption = new Option(dataResolucion.text, dataResolucion.id, false, false);
        $comboComprobanteCompras.append(newOption).trigger('change');
        $comboComprobanteCompras.val(dataResolucion.id).trigger('change');
    }

    if (primeraBodegaCompra && primeraBodegaCompra.length > 0) {
        const dataBodega = {
            id: primeraBodegaCompra[0].id,
            text: primeraBodegaCompra[0].codigo + ' - ' + primeraBodegaCompra[0].nombre
        };
        const newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodegaCompra.append(newOption).trigger('change');
        $comboBodegaCompra.val(dataBodega.id).trigger('change');
    }

    if (!primeraBodegaCompra || !primeraBodegaCompra.length) {
        agregarToast('warning', 'Sin bodegas asignadas', '', true);
    }

    $comboProveedorCompras.on('select2:close', function (event) {
        console.log('comboProveedorCompras select2:close');
        const dataNit = $('#id_proveedor_compra').select2('data');
        if (dataNit && dataNit.length) {
            responsabilidadesCompra = getResponsabilidades(dataNit[0].id_responsabilidades);
            actualizarInfoRetencionCompras();
        }
    });

    initComboResolucionCompra();

    setTimeout(function () {
        $comboProveedorCompras.select2("open");
    }, 10);
}

function initComboResolucionCompra() {
    $('#id_resolucion_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function () { return "No hay resultado"; },
            searching: function () { return "Buscando.."; },
            inputTooShort: function () { return "Por favor introduce 1 o más caracteres"; }
        },
        ajax: {
            url: 'api/resoluciones/combo-resoluciones',
            headers: headers,
            data: function (params) {
                return {
                    q: params.term,
                    tipo_resoluciones: [4],
                    _type: 'query'
                };
            },
            dataType: 'json',
            processResults: function (data) {
                return { results: data.data };
            }
        }
    });

    // Ocultar el contenedor de Select2 al arrancar (modo inicial: compra)
    $('#id_resolucion_compra').next('.select2-container').hide();
    $('#id_resolucion_compra').hide();
}

function toggleModoCapturaCompra() {
    if (modoCapturaCompra === 'compra') {
        modoCapturaCompra = 'documento_soporte';

        // ---- Ocultar comprobante ----
        $('#id_comprobante_compra').prop('disabled', true).prop('required', false);
        $('#id_comprobante_compra').next('.select2-container').hide();
        $('#id_comprobante_compra').hide();

        // ---- Mostrar resolución: destruir + reinicializar + mostrar ----
        if ($('#id_resolucion_compra').data('select2')) {
            $('#id_resolucion_compra').select2('destroy');
        }
        $('#id_resolucion_compra').show().prop('disabled', false).prop('required', true);
        initComboResolucionCompra(); // esto ya oculta el contenedor al final
        // Volver a mostrar el contenedor recién creado
        $('#id_resolucion_compra').next('.select2-container').show();

        $('#label_comprobante_resolucion_compra').html('Resolución<span style="color:red">*</span>');

        // Botón: ahora dice "COMPRA" para volver
        $('#toggleDocumentoSoporteCompraTexto').text('COMPRA');
        $('#titulo-view').text('Documento Soporte');
        $('#textiniciarCapturaCompra').text('INICIAR DOCUMENTO SOPORTE');
        $('#textoCrearCapturaCompraDisabled').text('GRABAR DOCUMENTO SOPORTE');
        $('#crearCapturaCompra').text('GRABAR DOCUMENTO SOPORTE');
        $('#toggleDocumentoSoporteCompra').attr('title', 'Cambiar a Compra');
        $('#toggleDocumentoSoporteCompra').removeClass('bg-gradient-warning').addClass('bg-gradient-primary');


    } else {
        modoCapturaCompra = 'compra';

        // ---- Ocultar resolución ----
        $('#id_resolucion_compra').prop('disabled', true).prop('required', false);
        $('#id_resolucion_compra').next('.select2-container').hide();
        $('#id_resolucion_compra').hide();

        // ---- Mostrar comprobante: destruir + reinicializar + mostrar ----
        if ($('#id_comprobante_compra').data('select2')) {
            $('#id_comprobante_compra').select2('destroy');
        }
        $('#id_comprobante_compra').show().prop('disabled', false).prop('required', true);
        $comboComprobanteCompras = $('#id_comprobante_compra').select2({
            theme: 'bootstrap-5',
            delay: 250,
            ajax: {
                url: 'api/comprobantes/combo-comprobante',
                headers: headers,
                data: function (params) {
                    return { q: params.term, tipo_comprobante: 2, _type: 'query' };
                },
                dataType: 'json',
                processResults: function (data) {
                    return { results: data.data };
                }
            }
        });
        // Restaurar la primera opción por defecto (igual que en cargarCombosCompras)
        if (primeraComprobanteCompra) {
            const dataResolucion = {
                id: primeraComprobanteCompra.id,
                text: primeraComprobanteCompra.codigo + ' - ' + primeraComprobanteCompra.nombre
            };
            const newOption = new Option(dataResolucion.text, dataResolucion.id, false, false);
            $comboComprobanteCompras.append(newOption).trigger('change');
            $comboComprobanteCompras.val(dataResolucion.id).trigger('change');
        }

        $('#label_comprobante_resolucion_compra').html('Comprobante<span style="color:red">*</span>');

        // Botón: ahora dice "DOC. SOPORTE" para volver a cambiar
        $('#toggleDocumentoSoporteCompraTexto').text('DOC. SOPORTE');
        $('#titulo-view').text('Compras');
        $('#textiniciarCapturaCompra').text('INICIAR COMPRA');
        $('#textoCrearCapturaCompraDisabled').text('GRABAR COMPRA');
        $('#crearCapturaCompra').text('GRABAR COMPRA');
        $('#toggleDocumentoSoporteCompra').attr('title', 'Cambiar a Documento Soporte');
        $('#toggleDocumentoSoporteCompra').removeClass('bg-gradient-primary').addClass('bg-gradient-warning');
    }

    // Limpiar documento de referencia y refrescar consecutivo
    $('#documento_referencia_compra').val('');
}

// =====================================================================
// TABLAS
// =====================================================================
function cargarTablasCompras() {
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
            { // BORRAR
                "data": function (row, type, set, col) {
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteProductoCompra(${row.id})" id="delete-producto-compra_${row.id}"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            { // PRODUCTO
                "data": function (row, type, set, col) {
                    return `<select
                            class="form-control form-control-sm compra_producto combo-grid"
                            id="compra_producto_${row.id}"
                            onchange="changeProductoCompra(${row.id})"
                            onfocusout="calcularProductoCompra(${row.id})"
                            inert
                        ></select>`;
                },
            },
            { // EXISTENCIAS
                "data": function (row, type, set, col) {
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="compra_existencia_${row.id}" disabled>`;
                }
            },
            { // CANTIDAD
                "data": function (row, type, set, col) {
                    return `
                        <div class="input-group" style="height: 30px;">
                            <input type="number" class="form-control form-control-sm" style="min-width: 80px; border-right: solid 1px #b3b3b3; border-top-right-radius: 10px; border-bottom-right-radius: 10px; text-align: right;" id="compra_cantidad_${row.id}" min="1" value="1" onkeydown="cantidadComprakeyDown(${row.id}, event)" onfocus="focusCantidadCompra(${row.id})" onfocusout="calcularProductoCompra(${row.id})" disabled>
                            <i class="fa fa-spinner fa-spin fa-fw compra_producto_load" id="compra_producto_load_${row.id}" style="display: none;"></i>
                            <div id="compra_cantidad_text_${row.id}" style="position: absolute; margin-top: 30px; z-index: 9;" class="invalid-feedback"></div>
                        </div>
                    `;
                }
            },
            { // COSTO
                "data": function (row, type, set, col) {
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="compra_costo_${row.id}" value="0" onkeydown="CostoComprakeyDown(${row.id}, event)" onfocusout="calcularProductoCompra(${row.id})" onfocus="focusCostoCompra(${row.id})" disabled>`;
                }
            },
            { // % DESCUENTO
                "data": function (row, type, set, col) {
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="compra_descuento_porcentaje_${row.id}" value="0" onkeydown="DescuentoComprakeyDown(${row.id}, event)" onfocusout="calcularProductoCompra(${row.id})" onfocus="focusPDescuentoCompra(${row.id})" disabled>`;
                }
            },
            { // VALOR DESCUENTO
                "data": function (row, type, set, col) {
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="compra_descuento_valor_${row.id}" value="0" onkeydown="DescuentoTotalComprakeyDown(${row.id}, event)" onfocusout="calcularProductoCompra(${row.id})" onfocus="focusVDescuentoCompra(${row.id})" disabled>`;
                }
            },
            { // VALOR IVA
                "data": function (row, type, set, col) {
                    return `<div class="form-group mb-3" style="min-width: 85px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                            <span id="compra_iva_porcentaje_text_${row.id}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">0%</span>
                            <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" value="0" id="compra_iva_valor_${row.id}" disabled>
                        </div>
                    </div>
                    <input type="number" class="form-control form-control-sm" style="min-width: 110px; display: none;" id="compra_iva_porcentaje_${row.id}" value="0" onkeydown="DescuentoComprakeyDown(${row.id}, event)" onfocusout="calcularProductoCompra(${row.id})">`;
                }
            },
            { // TOTAL
                "data": function (row, type, set, col) {
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="compra_total_${row.id}" value="0" disabled>`;
                }
            },
            { // CONCEPTO / OBSERVACIÓN
                "data": function (row, type, set, col) {
                    return `<input type="text" class="form-control form-control-sm" id="compra_concepto_${row.id}" onkeypress="changeObservacionCompra(${row.id}, event)" onfocus="this.select();" onfocusout="changeObservacionCompra(${row.id})" style="width: 180px !important;" value="${row.concepto}" disabled>`;
                }
            },
        ],
        columnDefs: [{ 'orderable': false }],
        initComplete: function () {
            $('#compraTable').on('draw.dt', function () {
                _compraInitSelect2Producto();
                _compraApplyCurrencyMask();
            });
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
        ajax: {
            type: "GET",
            headers: headers,
            data: { type: 'compras' },
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {
                "data": function (row, type, set) {
                    let styles = "margin-bottom: 0px; font-size: 13px;";
                    let stylesInfo = null;
                    let naturaleza = 'Credito - Compras';
                    if (row.cuenta.naturaleza_compras == 0) {
                        naturaleza = 'Error de naturaleza en comprar';
                        stylesInfo = "border: solid 1px red !important; color: red !important;";
                    }
                    let dataContent = `<b class='titulo-popover'>Cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                    if (row.cuenta.tipos_cuenta.length > 0) {
                        for (let i = 0; i < row.cuenta.tipos_cuenta.length; i++) {
                            if (row.cuenta.tipos_cuenta[i].id_tipo_cuenta == 8) {
                                styles += " color: #0bb19e; font-weight: 600;";
                                dataContent = `<b class='titulo-popover'>Anticipos cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                            }
                        }
                    }
                    return `<p style="${styles}">
                            <i class="fas fa-info icon-info" style="${stylesInfo}" title="${dataContent}" data-toggle="popover" data-html="true"></i>
                            ${row.nombre}
                        </p>`;
                }
            },
            {
                "data": function (row, type, set) {
                    let anticipos = false;
                    const id_cuenta = row.cuenta.id;
                    let className = '';
                    if (row.cuenta.tipos_cuenta.length > 0) {
                        for (let i = 0; i < row.cuenta.tipos_cuenta.length; i++) {
                            if (row.cuenta.tipos_cuenta[i].id_tipo_cuenta == 8) {
                                anticipos = true;
                                className = 'anticipos';
                            }
                        }
                    }
                    return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoCompra(${row.id}, ${anticipos}, ${id_cuenta})" onfocusout="calcularCompraPagos(${row.id}, ${anticipos})" onkeypress="changeFormaPagoCompras(${row.id}, ${anticipos}, event, ${id_cuenta})" id="compra_forma_pago_${row.id}">`;
                }
            },
        ],
        initComplete: function () {
            $('#compraFormaPago').on('draw.dt', function () {
                _compraApplyCurrencyMask();
                _compraPopovers();
            });
        }
    });

    if (compraDescuento) {
        compra_table.column(5).visible(true);
        compra_table.column(6).visible(true);
    } else {
        compra_table.column(5).visible(false);
        compra_table.column(6).visible(false);
    }

    if (compraExistencias) compra_table.column(2).visible(true);
    else compra_table.column(2).visible(false);
}

function _compraInitSelect2Producto() {
    $('.compra_producto').select2({
        theme: 'bootstrap-5',
        dropdownCssClass: 'custom-compra_producto',
        delay: 250,
        minimumInputLength: 2,
        language: {
            noResults: function () { return "No hay resultado"; },
            searching: function () { return "Buscando.."; },
            inputTooShort: function () { return "Por favor introduce 2 o más caracteres"; }
        },
        ajax: {
            url: 'api/producto/combo-producto',
            headers: headers,
            data: function (params) {
                return {
                    q: params.term,
                    id_bodega: $("#id_bodega_compra").val(),
                    id_cliente: $("#id_proveedor_compra").val(),
                    captura: 'compra',
                    tipo_producto: modoCapturaCompra == 'documento_soporte' ? [4] : [0,1,3],
                    _type: 'query'
                };
            },
            dataType: 'json',
            processResults: function (data) {
                return { results: data.data };
            }
        },
        templateResult: _compraFormatProducto,
        templateSelection: _compraFormatRepoSelection
    });
}

function _compraFormatProducto(producto) {
    if (producto.loading) return producto.text;

    const urlImagen = producto.imagen ? bucketUrl + producto.imagen : '/img/sin_imagen.png';
    const inventario = producto.inventarios.length > 0
        ? producto.inventarios[0].cantidad + ' Existencias'
        : 'Sin inventario';
    const color = producto.inventarios.length > 0
        ? (producto.inventarios[0].cantidad <= 0 ? '#a30000' : '#1c4587')
        : '#838383';

    if (producto.familia.inventario && compraExistencias) {
        return $(`
            <div class="row">
                <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                    <img style="width: 40px; border-radius: 10%;" src="${urlImagen}" />
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
    }

    return $(`
        <div class="row">
            <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                <img style="width: 40px; border-radius: 10%;" src="${urlImagen}" />
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

function _compraFormatRepoSelection(producto) {
    return producto.full_name || producto.text;
}

// =====================================================================
// PROVEEDOR / RETENCIÓN
// =====================================================================
$(document).on('change', '#id_proveedor_compra', function () {
    const data = $(this).select2('data');
    if (!data.length) return;

    procesarCambioProveedorCompra(data[0]);
    setTimeout(function () {
        $('#documento_referencia_compra').focus().select();
    }, 10);
    loadAnticiposProveedorCompras();
});

function procesarCambioProveedorCompra(data) {
    if (!data) {
        $("#btn_ver_cliente_compra").hide();
        return;
    }

    calculaRetencionCompra = false;
    $("#btn_ver_cliente_compra").show();
    $('#checkRetencionCompras').prop('checked', false);

    if (data.retencion) {
        calculaRetencionCompra = true;
        $('#checkRetencionCompras').prop('checked', true);
    }

    responsabilidadesCompra = getResponsabilidades(data.id_responsabilidades);
    clearFormasPagoCompra();
    changeRetencionCompras();
}

function changeRetencionCompras() {
    calculaRetencionCompra = true;
    $('#totales_retencion_compra').removeClass('totales_retencion_disable');
    if (!$('#checkRetencionCompras').is(':checked')) {
        calculaRetencionCompra = false;
        $('#totales_retencion_compra').addClass('totales_retencion_disable');
    }
    actualizarInfoRetencionCompras();
    mostrarValoresCompras();
}

// =====================================================================
// ANTICIPOS
// =====================================================================
function loadAnticiposProveedorCompras() {
    totalAnticiposDisponibles = 0;
    $('#input-anticipos-compra').hide();
    $('#compra_anticipo_disp_view').hide();
    $('#saldo_anticipo_compra').val(0);
    $('#compra_anticipo_disp').text('0.00');

    if (!$('#id_proveedor_compra').val()) return;

    $.ajax({
        url: base_url + 'extracto-anticipos',
        method: 'GET',
        data: { id_nit: $('#id_proveedor_compra').val(), id_tipo_cuenta: [8] },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (!res.success) return;

        totalAnticiposCompra = 0;
        totalAnticiposCompraCuenta = [];

        if (res.data.length) {
            for (let i = 0; i < res.data.length; i++) {
                const anticipo = res.data[i];
                const idCuenta = anticipo.id_cuenta;
                const cuentaExistente = encontrarCuentaCompra(idCuenta);
                totalAnticiposCompra += Math.abs(parseFloat(anticipo.saldo));

                if (cuentaExistente) {
                    cuentaExistente[idCuenta].saldo = (cuentaExistente[idCuenta].saldo || 0) + parseFloat(anticipo.saldo);
                } else {
                    const nuevoObj = {};
                    nuevoObj[idCuenta] = { id_cuenta: idCuenta, saldo: Math.abs(parseFloat(anticipo.saldo)) };
                    totalAnticiposCompraCuenta.push(nuevoObj);
                }
            }

            if (totalAnticiposCompra) {
                $('#compra_anticipo_disp_view').show();
                $('#saldo_anticipo_compra').val(_compraFmt(totalAnticiposCompra));
                $('#compra_anticipo_disp').text(_compraFmt(totalAnticiposCompra));
            }
        }
    }).fail((err) => {
        _compraToastError(err);
    });
}

function encontrarCuentaCompra(idCuenta) {
    if (totalAnticiposCompraCuenta && totalAnticiposCompraCuenta.length) {
        return totalAnticiposCompraCuenta.find(item => item[idCuenta]);
    }
    return false;
}

// =====================================================================
// FILAS DE PRODUCTOS
// =====================================================================
function addRowProductoCompra() {
    const rows = compra_table.rows().data();
    const totalRows = rows.length;
    const dataLast = rows[totalRows - 1];

    if (dataLast) {
        const cuentaLast = $('#compra_producto_' + dataLast.id).val();
        if (!cuentaLast) {
            $('#compra_producto_' + dataLast.id).select2('open');
            document.getElementById("card-compra").scrollLeft = 0;
            return;
        }
    } else if (totalRows > 1) {
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

    $('#compra_producto_' + idCompraProducto).focus();
    $('#compra_producto_' + idCompraProducto).select2('open');
    idCompraProducto++;
}

function deleteProductoCompra(idRow) {
    const dataCompra = compra_table.rows().data();

    for (let row = 0; row < dataCompra.length; row++) {
        if (dataCompra[row].id == idRow) {
            compra_table.row(row).remove().draw();
            if (!compra_table.rows().data().length) {
                $("#crearCapturaCompraDisabled").show();
                $("#crearCapturaCompra").hide();
            }
        }
    }
    mostrarValoresCompras();
    actualizarInfoRetencionCompras();
}

// =====================================================================
// CÁLCULO DE PRODUCTOS
// =====================================================================
function calcularProductoCompra(idRow, validarCantidad = false) {
    const costoProducto = _compraVal(idRow, 'costo');
    const cantidadProducto = $('#compra_cantidad_' + idRow).val();
    const ivaProducto = $('#compra_iva_porcentaje_' + idRow).val();
    const descuentoProducto = _compraVal(idRow, 'descuento_porcentaje');
    let totalPorCantidad = 0;
    let totalIva = 0;
    let totalDescuento = _compraVal(idRow, 'descuento_valor'); // FIX: ahora lee el descuento real
    let totalProducto = 0;

    if (validarCantidad && !validarExistenciasCompras(idRow)) return;

    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    if (descuentoProducto > 0) {
        totalDescuento = totalPorCantidad * (descuentoProducto / 100);
        $('#compra_descuento_valor_' + idRow).val(_compraFmtCurrency(totalDescuento));
    } else if (totalDescuento > 0) {
        // Descuento ingresado por valor, recalcular porcentaje
        const porcentaje = totalPorCantidad > 0 ? (totalDescuento / totalPorCantidad) * 100 : 0;
        $('#compra_descuento_porcentaje_' + idRow).val(_compraFmtCurrency(porcentaje));
    } else {
        $('#compra_descuento_porcentaje_' + idRow).val(_compraFmtCurrency(0));
        $('#compra_descuento_valor_' + idRow).val(_compraFmtCurrency(0));
        totalDescuento = 0;
    }

    // FIX: el descuento se resta del subtotal
    totalProducto = totalPorCantidad - totalDescuento;

    if (ivaProducto > 0) {
        totalIva = (totalPorCantidad - totalDescuento) * ivaProducto / 100;
        if (ivaIncluidoCompras) {
            totalIva = (totalPorCantidad - totalDescuento) - ((totalPorCantidad - totalDescuento) / (1 + (ivaProducto / 100)));
        }
        $('#compra_iva_valor_' + idRow).val(_compraFmtCurrency(totalIva));
    }

    if (!ivaIncluidoCompras) {
        totalProducto += totalIva;
    }

    totalProducto = Math.round(totalProducto * 100) / 100;
    $('#compra_total_' + idRow).val(_compraFmtCurrency(totalProducto));

    mostrarValoresCompras();
    actualizarInfoRetencionCompras();
}

function validarExistenciasCompras(idRow) {
    addRowProductoCompra();
    calcularProductoCompra(idRow);
    return true;
}

function totalCantidadProductoCompras(idRow) {
    const idProducto = $('#compra_producto_' + idRow).val();
    const rowProductos = compra_table.rows().data();
    const cantidadActualRow = parseInt($('#compra_cantidad_' + idRow).val());
    let cantidadTotal = 0;

    for (let i = 0; i < rowProductos.length; i++) {
        const producto = $('#compra_producto_' + rowProductos[i].id).val();
        if (producto && rowProductos[i].id != idRow && producto == idProducto) {
            cantidadTotal += parseInt($('#compra_cantidad_' + rowProductos[i].id).val());
        }
    }
    return [cantidadActualRow, cantidadTotal];
}

// =====================================================================
// TOTALES
// =====================================================================
function mostrarValoresCompras() {
    if (guardandoCompra) return;

    const [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

    if (descuento) $('#totales_descuento').show();
    else $('#totales_descuento').hide();

    if (total) disabledFormasPagoCompra(false);
    else disabledFormasPagoCompra();

    new CountUp('compra_total_iva', 0, iva, 2, 0.5).start();
    new CountUp('compra_total_descuento', 0, descuento, 2, 0.5).start();
    new CountUp('compra_total_retencion', 0, retencion, 2, 0.5).start();
    new CountUp('compra_total_valor', 0, total, 2, 0.5).start();
    new CountUp('compra_sub_total', 0, valorBruto, 2, 0.5).start();
    new CountUp('total_faltante_compra', 0, total, 2, 0.5).start();
}

function totalValoresCompras() {
    let iva = 0, retencion = 0, descuento = 0, total = 0, redondeo = 0;
    let valorBruto = 0;
    const dataCompra = compra_table.rows().data();

    if (dataCompra.length > 0) {
        for (let i = 0; i < dataCompra.length; i++) {
            const producto = $('#compra_producto_' + dataCompra[i].id).val();
            if (producto) {
                const cantidad = _compraVal(dataCompra[i].id, 'cantidad');
                const costo = _compraVal(dataCompra[i].id, 'costo');
                const ivaSum = _compraVal(dataCompra[i].id, 'iva_valor');
                const descSum = _compraVal(dataCompra[i].id, 'descuento_valor');

                iva += ivaSum ? ivaSum : 0;
                descuento += descSum ? descSum : 0;
                valorBruto += (cantidad * costo) - (descSum ? descSum : 0);
            }
        }

        if (ivaIncluidoCompras) valorBruto -= iva;

        total = ivaIncluidoCompras ? valorBruto : valorBruto + iva;
        retencion = calcularRetencionCompras(valorBruto, total);

        if (retencion) total = total - retencion;

        if (redondearFactura) {
            const totalParaRedondear = parseFloat(total / 1000);
            const totalRedondeado = totalParaRedondear.toFixed(2) * 1000;
            redondeo = totalRedondeado - total;
            total = totalRedondeado;
        }
    }

    const [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();

    if (parseFloat(total.toFixed(2)) > 0 && (totalEfectivo + totalOtrosPagos + totalAnticipos) >= parseFloat(total.toFixed(2))) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
        $('#id_vendedor_compra').removeClass("is-invalid");
        $('#id_vendedor_compra').addClass("is-valid");
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }

    if (ivaIncluidoCompras) total = total + iva;

    return [iva, retencion, descuento, total, valorBruto, redondeo];
}

function calcularRetencionCompras(valorBruto, total) {
    let retencion = 0;
    if (calculaRetencionCompra) {
        const [base, porcentaje] = obtenerDatosRetencionCompra(valorBruto);
        if (total >= base && porcentaje) {
            retencion = valorBruto * (porcentaje / 100);
        }
    }
    return retencion;
}

function obtenerDatosRetencionCompra(valorSubtotal) {
    let porcentaje = 0;
    let base = 0;

    retencionesCompras.forEach(retencion => {
        if (retencion.base <= valorSubtotal) {
            if (retencion.base > base) {
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
    const [iva, retencion, descuento, total, subtotal] = totalValoresCompras();

    let porcentaje = 0;
    let base = 0;
    let nombre = 'Sin cuenta con retención';
    let total_uvt = 0;

    retencionesCompras.forEach(retencion => {
        if (retencion.base > base) {
            if (retencion.porcentaje > porcentaje) {
                porcentaje = retencion.porcentaje;
                base = retencion.base;
                total_uvt = retencion.total_uvt;
                nombre = `${retencion.cuenta} - ${retencion.nombre}`;
            }
        }
    });

    $("#nombre_info_retencion_compra").html(`RETENCIÓN %${porcentaje}:`);

    const baseformat = _compraFmt(base);
    const totalUVTs = _compraFmt(valor_uvt);
    const valorSubtotal = _compraFmt(subtotal);
    const responsableRetencion = calculaRetencionCompra
        ? `<b class='titulo-popover'>Con permiso:</b> Calcula retención en la fuente`
        : `<b class='titulo-popover'>Sin responsablidad:</b> 07 => No calcula retención en la fuente`;

    const nuevoTitulo = `
        <b class='titulo-popover'>Cuenta:</b> ${nombre}<br/>
        <b class='titulo-popover'>UVT:</b> ${total_uvt} X ${totalUVTs} = ${baseformat}<br/>
        <b class='titulo-popover'>Subtotal:</b> ${valorSubtotal}<br/>
        ${responsableRetencion}
    `;

    iconInfo.setAttribute('title', nuevoTitulo);
    $(iconInfo).popover('dispose');
    $(iconInfo).popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
}

// =====================================================================
// CAMBIO DE PRODUCTO
// =====================================================================
function changeProductoCompra(idRow) {
    let data = $('#compra_producto_' + idRow).select2('data');
    if (data.length == 0) return;
    data = data[0];

    if (data.inventarios.length > 0 && data.familia.inventario) {
        const totalInventario = parseFloat(data.inventarios[0].cantidad);
        $("#compra_existencia_" + idRow).val(totalInventario);
        $("#compra_cantidad_" + idRow).attr({ "max": totalInventario });
    }

    if (data.familia.cuenta_compra_iva && data.familia.cuenta_compra_iva.impuesto) {
        const pct = stringToNumberFloat(data.familia.cuenta_compra_iva.impuesto.porcentaje);
        $('#compra_iva_porcentaje_' + idRow).val(pct);
        $('#compra_iva_porcentaje_text_' + idRow).text(pct + '%');
    }

    console.log('data.familia: ', data.familia);

    if (!responsabilidadesCompra.includes('5') && data.familia.cuenta_compra_retencion_nodeclarante && data.familia.cuenta_compra_retencion_nodeclarante.impuesto) {
        const existe = retencionesCompras.findIndex(item => item.id_retencion == data.familia.cuenta_compra_retencion_nodeclarante.impuesto.id);
        if (!existe || existe < 0) {
            retencionesCompras.push({
                cuenta: data.familia.cuenta_compra_retencion_nodeclarante.cuenta,
                nombre: data.familia.cuenta_compra_retencion_nodeclarante.nombre,
                id_retencion: data.familia.cuenta_compra_retencion_nodeclarante.impuesto.id,
                porcentaje: parseFloat(data.familia.cuenta_compra_retencion_nodeclarante.impuesto.porcentaje),
                base: parseFloat(data.familia.cuenta_compra_retencion_nodeclarante.impuesto.base),
                total_uvt: parseFloat(data.familia.cuenta_compra_retencion_nodeclarante.impuesto.total_uvt)
            });
        }
    } else if (data.familia.cuenta_compra_retencion && data.familia.cuenta_compra_retencion.impuesto) {
        const existe = retencionesCompras.findIndex(item => item.id_retencion == data.familia.cuenta_compra_retencion.impuesto.id);
        if (!existe || existe < 0) {
            retencionesCompras.push({
                cuenta: data.familia.cuenta_compra_retencion.cuenta,
                nombre: data.familia.cuenta_compra_retencion.nombre,
                id_retencion: data.familia.cuenta_compra_retencion.impuesto.id,
                porcentaje: parseFloat(data.familia.cuenta_compra_retencion.impuesto.porcentaje),
                base: parseFloat(data.familia.cuenta_compra_retencion.impuesto.base),
                total_uvt: parseFloat(data.familia.cuenta_compra_retencion.impuesto.total_uvt)
            });
        }
    }

    if (data.familia.id_cuenta_compra_descuento && compraDescuento) {
        $('#compra_descuento_valor_' + idRow).prop('disabled', false);
        $('#compra_descuento_porcentaje_' + idRow).prop('disabled', false);
    } else {
        $('#compra_descuento_valor_' + idRow).prop('disabled', true);
        $('#compra_descuento_porcentaje_' + idRow).prop('disabled', true);
    }

    $('#compra_costo_' + idRow).val(_compraFmtCurrency(data.precio_inicial));
    $('#compra_producto_' + idRow).select2('open');
    $('#compra_cantidad_' + idRow).prop('disabled', false);
    $('#compra_costo_' + idRow).prop('disabled', false);
    $('#compra_concepto_' + idRow).prop('disabled', false);

    calcularProductoCompra(idRow);
    clearFormasPagoCompra();

    setTimeout(function () {
        $('#compra_cantidad_' + idRow).focus();
        $('#compra_cantidad_' + idRow).select();
    }, 10);
}

// =====================================================================
// OBSERVACIÓN / CONCEPTO (reemplaza el changeObservacionGasto pegado de gastos)
// =====================================================================
function changeObservacionCompra(idRow, event = null) {
    if (guardandoCompra) return;

    // Enter: avanzar a siguiente fila / abrir formas de pago según el flujo existente
    if (event && event.keyCode == 13) {
        // el flujo original no hacía nada más en compras; se respeta
        return;
    }
    // Sin event (onfocusout) no hace nada; se respeta comportamiento original.
}

// =====================================================================
// FOCOS
// =====================================================================
function focusCantidadCompra(idRow) {
    setTimeout(function () { $('#compra_cantidad_' + idRow).select(); }, 80);
}
function focusCostoCompra(idRow) {
    setTimeout(function () { $('#compra_costo_' + idRow).select(); }, 80);
}
function focusPDescuentoCompra(idRow) {
    setTimeout(function () { $('#compra_descuento_porcentaje_' + idRow).select(); }, 80);
}
function focusVDescuentoCompra(idRow) {
    setTimeout(function () { $('#compra_descuento_valor_' + idRow).select(); }, 80);
}

// =====================================================================
// KEYDOWN
// =====================================================================
function cantidadComprakeyDown(idRow, event) {
    if (event.keyCode == 13) {
        key13PressNewRow = true;
        if (!validarExistenciasCompras(idRow)) return;
    }
}

function CostoComprakeyDown(idRow, event) {
    if (event.keyCode == 13) {
        calcularProductoCompra(idRow);
        addRowProductoCompra();
    }
}

function DescuentoComprakeyDown(idRow, event) {
    if (event.keyCode == 13) {
        calcularProductoCompra(idRow);
        setTimeout(function () {
            $('#compra_descuento_valor_' + idRow).focus();
            $('#compra_descuento_valor_' + idRow).select();
        }, 10);
    }
}

function DescuentoTotalComprakeyDown(idRow, event) {
    if (event.keyCode == 13) {
        const descuentoProductoValor = _compraVal(idRow, 'descuento_valor');
        const costoProducto = _compraVal(idRow, 'costo');
        const cantidadProducto = $('#compra_cantidad_' + idRow).val();
        const porcentajeDescuento = descuentoProductoValor * 100 / (costoProducto * cantidadProducto);

        $('#compra_descuento_porcentaje_' + idRow).val(_compraFmtCurrency(porcentajeDescuento));

        calcularProductoCompra(idRow);
        setTimeout(function () {
            $('#compra_iva_porcentaje_' + idRow).focus();
            $('#compra_iva_porcentaje_' + idRow).select();
        }, 10);
    }
}

function IvaComprakeyDown(idRow, event) {
    if (event.keyCode == 13) {
        calcularProductoCompra(idRow);
        addRowProductoCompra();
    }
}

// =====================================================================
// CANCELAR
// =====================================================================
function cancelarCompra() {
    idCompraProducto = 0;
    const totalRows = compra_table.rows().data().length;

    if (totalRows) {
        compra_table.clear([]).draw();
        for (let i = 0; i < totalRows; i++) {
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
    $("#toggleDocumentoSoporteCompra").show();

    cargarFechasCompras();

    setTimeout(function () {
        $comboProveedorCompras.select2("open");
    }, 10);
}

// =====================================================================
// FORMAS DE PAGO
// =====================================================================
function cargarFormasDePagosCompras() {
    const totalRows = compra_table_pagos.rows().data().length;
    if (totalRows) {
        compra_table_pagos.clear([]).draw();
        for (let i = 0; i < totalRows; i++) {
            compra_table_pagos.row(0).remove().draw();
        }
    }
    compra_table_pagos.ajax.reload(function (res) {
        disabledFormasPagoCompra();
    });
}

function clearFormasPagoCompra() {
    const dataFormasPago = compra_table_pagos.rows().data();
    if (dataFormasPago.length) {
        for (let i = 0; i < dataFormasPago.length; i++) {
            $('#compra_forma_pago_' + dataFormasPago[i].id).val(0);
        }
    }
    calcularCompraPagos();
}

function disabledFormasPagoCompra(estado = true) {
    const dataFormasPago = compra_table_pagos.rows().data();
    if (!dataFormasPago.length) return;

    for (let i = 0; i < dataFormasPago.length; i++) {
        const formaPago = dataFormasPago[i];
        const tiposCuentas = formaPago.cuenta.tipos_cuenta;

        if (!tiposCuentas.length) {
            $('#compra_forma_pago_' + formaPago.id).prop('disabled', estado);
            continue;
        }

        for (let j = 0; j < tiposCuentas.length; j++) {
            let isAnticipo = false;
            if (tiposCuentas[j].id_tipo_cuenta == 8) isAnticipo = true;

            if (isAnticipo) {
                const cuentaExistente = encontrarCuentaCompra(formaPago.cuenta.id);
                if (cuentaExistente) {
                    const totalSaldoAnticipos = cuentaExistente[formaPago.cuenta.id].saldo;
                    if (totalSaldoAnticipos) {
                        $('#compra_forma_pago_' + formaPago.id).prop('disabled', estado);
                    }
                } else {
                    $('#compra_forma_pago_' + formaPago.id).prop('disabled', true);
                }
            } else {
                $('#compra_forma_pago_' + formaPago.id).prop('disabled', estado);
            }
        }
    }
}

function focusFormaPagoCompra(idFormaPago, anticipo = false, id_cuenta = null) {
    if (guardandoCompra) return;

    const [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
    const [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras(idFormaPago);
    let totalFactura = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    totalFactura = totalFactura < 0 ? 0 : totalFactura;
    const saldoFormaPago = _compraVal(idFormaPago, 'forma_pago');

    if (anticipo) {
        const cuentaExistente = encontrarCuentaCompra(id_cuenta);
        if (cuentaExistente) {
            const totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
            if ((totalSaldoAnticipos - totalAnticipos) < totalFactura) {
                $('#compra_forma_pago_' + idFormaPago).val(_compraFmtCurrency(totalSaldoAnticipos - totalAnticipos));
                $('#compra_forma_pago_' + idFormaPago).select();
                return;
            }
        }
        return;
    }

    if (!saldoFormaPago) {
        $('#compra_forma_pago_' + idFormaPago).val(_compraFmt(totalFactura < 0 ? 0 : totalFactura));
    }
    $('#compra_forma_pago_' + idFormaPago).select();
}

function calcularCompraPagos(idFormaPago) {
    if (guardandoCompra) return;

    if ($('#compra_forma_pago_' + idFormaPago).val() == '' ||
        $('#compra_forma_pago_' + idFormaPago).val() < 0) {
        $('#compra_forma_pago_' + idFormaPago).val(0);
    }

    $('#total_faltante_compra').removeClass("is-invalid");

    const [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
    const totalFactura = parseFloat(total.toFixed(2));

    const [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();
    let totalFaltante = totalFactura - (totalEfectivo + totalOtrosPagos + totalAnticipos);

    if ((totalOtrosPagos + totalEfectivo + totalAnticipos) >= totalFactura) {
        const totalCambio = (totalEfectivo + totalOtrosPagos + totalAnticipos) - totalFactura;
        if (parseInt(totalCambio) > 0) $('#cambio-totals').show();
        document.getElementById('total_cambio_compra').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalCambio);
    } else {
        $('#cambio-totals').hide();
        if (totalFaltante < 0) {
            $('#compra_forma_pago_' + idFormaPago).val(totalFaltante * -1);
            $('#compra_forma_pago_' + idFormaPago).select();
        }
    }
    const totalPagado = totalFaltante < 0 ? totalFactura : totalEfectivo + totalOtrosPagos + totalAnticipos;
    totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    new CountUp('total_pagado_compra', 0, totalPagado, 2, 0.5).start();
    new CountUp('total_faltante_compra', 0, totalFaltante, 2, 0.5).start();
}

function totalFormasPagoCompras(idFormaPago = null) {
    let totalEfectivo = 0, totalAnticipos = 0, totalOtrosPagos = 0;
    const dataPagoCompra = compra_table_pagos.rows().data();

    if (dataPagoCompra.length > 0) {
        for (let i = 0; i < dataPagoCompra.length; i++) {
            const compraPago = _compraVal(dataPagoCompra[i].id, 'forma_pago');
            if (idFormaPago && idFormaPago == dataPagoCompra[i].id) continue;

            if (dataPagoCompra[i].id == 1) totalEfectivo += compraPago;
            else if ($('#compra_forma_pago_' + dataPagoCompra[i].id).hasClass("anticipos")) totalAnticipos += compraPago;
            else totalOtrosPagos += compraPago;
        }
    }
    return [totalEfectivo, totalOtrosPagos, totalAnticipos];
}

function changeFormaPagoCompras(idFormaPago, anticipo, event, id_cuenta) {
    if (guardandoCompra) return;

    if (event.keyCode == 13) {
        calcularCompraPagos(idFormaPago);

        const [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();
        const [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();
        const totalFaltante = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);

        if (!total) return;

        if (anticipo) {
            const cuentaExistente = encontrarCuentaCompra(id_cuenta);
            if (cuentaExistente) {
                const totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
                if (totalAnticipos > totalSaldoAnticipos) {
                    $('#compra_forma_pago_' + idFormaPago).val(totalSaldoAnticipos);
                    $('#compra_forma_pago_' + idFormaPago).select();
                    calcularCompraPagos();
                    return;
                } else if (totalAnticipos > (totalFaltante + totalAnticipos)) {
                    $('#compra_forma_pago_' + idFormaPago).val(totalFaltante);
                    $('#compra_forma_pago_' + idFormaPago).select();
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
    const dataCompraPagos = compra_table_pagos.rows().data();
    if (!dataCompraPagos.length) return;

    let idFormaPagoFocus = dataCompraPagos[0].id;
    let obtenerFormaPago = false;

    for (let i = 0; i < dataCompraPagos.length; i++) {
        const input = document.getElementById("compra_forma_pago_" + dataCompraPagos[i].id);
        if (input.disabled) continue;

        if (obtenerFormaPago) {
            idFormaPagoFocus = dataCompraPagos[i].id;
            obtenerFormaPago = false;
        } else if (dataCompraPagos[i].id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoCompra(idFormaPagoFocus);
}

// =====================================================================
// GUARDAR
// =====================================================================
function validateSaveCompra() {
    $('#total_faltante_compra_text').css("color", "#484848");
    $('#total_faltante_compra').css("color", "#484848");

    if (!guardandoCompra) {
        const [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoCompras();
        const [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();
        const totalFactura = parseFloat(total.toFixed(2));

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= totalFactura) {
            guardandoCompra = true;
            saveCompra();
        } else {
            $('#total_faltante_compra_text').css("color", "red");
            $('#total_faltante_compra').css("color", "red");
        }
    }
}

function saveCompra() {
    $("#crearCapturaCompra").hide();
    $("#crearCapturaCompraDisabled").hide();
    $("#crearCapturaCompraLoading").show();

    var data = {
        pagos: getComprasPagos(),
        productos: getProductosCompra(),
        id_bodega: $("#id_bodega_compra").val(),
        id_proveedor: $("#id_proveedor_compra").val(),
        fecha_manual: $("#fecha_manual_compra").val(),
        documento_referencia: $("#documento_referencia_compra").val(),
        observacion: $("#observacion_compra").val(),
        modo: modoCapturaCompra,
    };

    if (modoCapturaCompra === 'documento_soporte') {
        data.id_resolucion = $("#id_resolucion_compra").val();
    } else {
        data.id_comprobante = $("#id_comprobante_compra").val();
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
        if (res.success) {
            agregarToast('exito', 'Creación exitosa', 'Compra creada con exito!', true);
            $("#crearCapturaCompra").show();
            $("#crearCapturaCompraLoading").hide();

            if (res.impresion) window.open("/compras-print/" + res.impresion, '_blank');
            idCompraProducto = 0;

            $('#iniciarCapturaCompra').hide();
            $('#iniciarCapturaCompraLoading').hide();
            $("#toggleDocumentoSoporteCompra").hide();
            $('#documento_referencia_compra').val('');

            var totalRows = compra_table.rows().data().length;
            for (var i = 0; i < totalRows; i++) {
                compra_table.row(0).remove().draw();
            }

            setTimeout(function () {
                $('#id_proveedor_compra').focus();
                $comboProveedorCompras.select2("open");
            }, 10);

            loadAnticiposProveedorCompras();
            disabledFormasPagoCompra();
            cargarFechasCompras();
        } else {
            guardandoCompra = false;
            $("#crearCapturaCompra").show();
            $("#crearCapturaCompraLoading").hide();
            disabledFormasPagoCompra(false);
            _compraToastError(res);
        }
    }).fail((err) => {
        guardandoCompra = false;
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraLoading").hide();
        _compraToastError(err);
    });
}

function getComprasPagos() {
    const data = [];
    const dataCompraPagos = compra_table_pagos.rows().data();
    if (!dataCompraPagos.length) return data;

    for (let i = 0; i < dataCompraPagos.length; i++) {
        const pagoCompra = _compraVal(dataCompraPagos[i].id, 'forma_pago');
        if (pagoCompra > 0) {
            data.push({ id: dataCompraPagos[i].id, valor: pagoCompra });
        }
    }
    return data;
}

function getProductosCompra() {
    const data = [];
    const dataCompraProductos = compra_table.rows().data();
    if (!dataCompraProductos.length) return data;

    for (let i = 0; i < dataCompraProductos.length; i++) {
        const id_row = dataCompraProductos[i].id;
        const id_producto = $('#compra_producto_' + id_row).val();
        const cantidad = parseFloat($('#compra_cantidad_' + id_row).val());

        if (id_producto && cantidad) {
            const costo = _compraVal(id_row, 'costo');
            const descuento_porcentaje = _compraVal(id_row, 'descuento_porcentaje');
            const descuento_valor = _compraVal(id_row, 'descuento_valor');
            const iva_porcentaje = _compraVal(id_row, 'iva_porcentaje');
            const iva_valor = _compraVal(id_row, 'iva_valor');
            const total = _compraVal(id_row, 'total');
            let subtotal = cantidad * costo;
            const concepto = $('#compra_concepto_' + id_row).val();

            if (ivaIncluidoCompras) subtotal -= iva_valor;

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

// =====================================================================
// NIT (modal nuevo — se conserva el flujo existente)
// =====================================================================
function openModalNewNitCompra() {
    abrirNitGeneral({
        modo: 'create',
        captura: 'compra',
        onSave: function (dataNit) {
            const dataCliente = {
                id: dataNit.id,
                text: dataNit.numero_documento + ' - ' + dataNit.nombre_completo
            };
            const newOption = new Option(dataCliente.text, dataCliente.id, false, false);
            $comboProveedorCompras.append(newOption).val(dataCliente.id).trigger('change');

            procesarCambioProveedorCompra(dataNit);
            agregarToast('exito', 'Creación exitosa', 'Cliente creado con éxito!', true);
            document.getElementById('iniciarCapturaCompra').click();
        }
    });
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

function openModalViewNitCompra() {
    const data = $('#id_proveedor_compra').select2('data');
    if (!data.length) {
        agregarToast('warning', 'Sin cliente', 'Selecciona un cliente primero');
        return;
    }
    abrirNitDetalle(data[0].id);
}

function openModalEditNitCompra() {
    const data = $('#id_proveedor_compra').select2('data');
    if (!data.length) {
        agregarToast('warning', 'Sin cliente', 'Selecciona un cliente primero');
        return;
    }
    abrirNitGeneral({
        modo: 'edit',
        id: data[0].id,
        onUpdate: function (dataNit) {
            const $sel = $('#id_proveedor_compra');
            const optText = dataNit.numero_documento + ' - ' + dataNit.nombre_completo;

            $sel.find("option[value='" + dataNit.id + "']").remove();
            const newOption = new Option(optText, dataNit.id, true, true);
            $sel.append(newOption);
            $sel.val(dataNit.id).trigger('change');

            procesarCambioProveedorCompra(dataNit);
            agregarToast('exito', 'Edición exitosa', 'Cliente actualizado con éxito!', true);
        }
    });
}

// Guardado manual del NIT desde modal (se conserva el flujo existente)
$(document).on('click', '#saveNitCompra', function () {
    const form = document.querySelector('#compraNitsForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $("#saveNitCompraLoading").show();
    $("#saveNitCompra").hide();

    const data = {
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
    };

    $.ajax({
        url: base_url + 'nit',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            clearFormNitsCompra();
            $("#saveNitCompra").show();
            $("#saveNitCompraLoading").hide();
            $("#nitCompraFormModal").modal('hide');

            const dataCliente = {
                id: res.data.id,
                text: res.data.numero_documento + ' - ' + res.data.nombre_completo
            };
            const newOption = new Option(dataCliente.text, dataCliente.id, false, false);
            $comboProveedorCompras.append(newOption).trigger('change');
            $comboProveedorCompras.val(dataCliente.id).trigger('change');

            agregarToast('exito', 'Creación exitosa', 'Cedula nit creada con exito!', true);
            document.getElementById('iniciarCapturaCompra').click();
        }
    }).fail((err) => {
        $('#saveNitCompra').show();
        $('#saveNitCompraLoading').hide();
        _compraToastError(err);
    });
});

// =====================================================================
// FACTURA / DOCUMENTO
// =====================================================================
function buscarFacturaCompra(event) {
    const botonPrecionado = event.key.length == 1 ? event.key : '';
    let documento_referencia = $('#documento_referencia_compra').val() + '' + botonPrecionado;

    if (event.keyCode == 13 && documento_referencia) {
        document.getElementById('iniciarCapturaCompra').click();
        return;
    }

    if (validarFacturaCompra) validarFacturaCompra.abort();
    if (event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
    if (!documento_referencia) return;

    $('#documento_referencia_compra_loading').show();
    $('#documento_referencia_compra').removeClass("is-invalid");
    $('#documento_referencia_compra').removeClass("is-valid");

    setTimeout(function () {
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
            if (!res.data) {
                $('#documento_referencia_compra').removeClass("is-invalid");
                $('#documento_referencia_compra').addClass("is-valid");
            } else {
                $('#documento_referencia_compra').removeClass("is-valid");
                $('#documento_referencia_compra').addClass("is-invalid");
                $("#error_documento_referencia_compra").text('La factura No ' + documento_referencia + ' ya existe!');
            }
        }).fail((err) => {
            $('#documento_referencia_compra_loading').hide();
        });
    }, 100);
}

// =====================================================================
// EVENTOS GLOBALES
// =====================================================================
$(document).on('keydown', '.custom-compra_producto .select2-search__field', function (event) {
    if (guardandoCompra) return;

    const [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();
    const dataSearch = $('.select2-search__field').val();

    if (event.keyCode == 96 && !dataSearch.length) {
        abrirFormasPagoCompras = true;
    } else if (event.keyCode == 13) {
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

$(document).on('keydown', '.custom-id_proveedor_compra .select2-search__field', function (event) {
    if (event.keyCode == 13) {
        openModalNewNitCompras();
        const documentoBuscado = $('.select2-search__field').val();
        $comboProveedorCompras.select2('close');
        $("#numero_documento_compra_nit").val(documentoBuscado);
    }
});

$("#id_vendedor_compra").on('change', function (event) {
    if ($("#id_vendedor_compra").val()) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }
});

$("#fecha_manual_compra").on('keydown', function (event) {
    if (event.keyCode == 13) {
        event.preventDefault();
        setTimeout(function () {
            $('#documento_referencia_compra').focus();
            $('#documento_referencia_compra').select();
        }, 10);
    }
});

$("#documento_referencia_compra").on('keydown', function (event) {
    if (event.keyCode == 13) {
        event.preventDefault();
        document.getElementById('iniciarCapturaCompra').click();
    }
});

$(document).on('click', '#iniciarCapturaCompra', function () {
    const form = document.querySelector('#compraFilterForm');

    // Limpiar estados previos
    $('#id_comprobante_compra').removeClass('is-invalid');
    $('#id_resolucion_compra').removeClass('is-invalid').next('.select2-container').find('.select2-selection').removeClass('is-invalid');

    // Validación explícita según el modo (feedback visible)
    if (modoCapturaCompra === 'documento_soporte') {
        if (!$('#id_resolucion_compra').val()) {
            $('#id_resolucion_compra').addClass('is-invalid');
            $('#id_resolucion_compra').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            agregarToast('warning', 'Resolución requerida', 'Debes seleccionar una resolución para continuar', true);
            $('#id_resolucion_compra').select2('open');
            return;
        }
    } else {
        if (!$('#id_comprobante_compra').val()) {
            $('#id_comprobante_compra').addClass('is-invalid');
            $('#id_comprobante_compra').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            agregarToast('warning', 'Comprobante requerido', 'Debes seleccionar un comprobante para continuar', true);
            $('#id_comprobante_compra').select2('open');
            return;
        }
    }

    // Validación HTML5 del resto del form (fecha, factura, proveedor, bodega)
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $documentoReferencia = $("#documento_referencia_compra").val();

    $("#iniciarCapturaCompra").hide();
    $('#agregarCompraProducto').show();
    $("#crearCapturaCompra").hide();
    $("#cancelarCapturaCompra").show();
    $('#cambio-totals').hide();
    $("#crearCapturaCompraDisabled").show();
    $("#toggleDocumentoSoporteCompra").hide();

    addRowProductoCompra();
});

$(document).on('click', '#agregarCompraProducto', function () {
    addRowProductoCompra();
});

$(document).on('click', '#cancelarCapturaCompra', function () {
    cancelarCompra();
});

$(document).on('click', '#crearCapturaCompra', function () {
    validateSaveCompra();
});