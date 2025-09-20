var pago_table = null;
var pago_anticipos = null;
var guardandoPago = false;
var $comboNitPagos = null;
var pago_table_pagos = null;
var totalAnticiposPago = null;
var validarFacturaPago = null;
var noBuscarDatosPago = false;
var calculandoRowPagos = false;
var pago_table_movimiento = null;
var $comboComprobantePagos = null;
var totalAnticiposPagoCuenta = null;

function pagoInit () {

    cargarFechasPago();
    cargarCombosPago();
    cargarTablasPago();
    cargarFormasPagoPago();

    if (pagoFecha) $('#fecha_manual_pago').prop('disabled', false);
    else $('#fecha_manual_pago').prop('disabled', true);

    if (pagoUpdate) $("#documento_referencia_pago").prop('disabled', false);
    else $("#documento_referencia_pago").prop('disabled', true);

    $('.water').hide();
}

function cargarFechasPago() {
    var dateNow = new Date();
    // Formatear a YYYY-MM-DDTHH:MM (formato que espera datetime-local)
    var fechaHoraPago = dateNow.getFullYear() + '-' + 
        ("0" + (dateNow.getMonth() + 1)).slice(-2) + '-' + 
        ("0" + dateNow.getDate()).slice(-2) + 'T' + 
        ("0" + dateNow.getHours()).slice(-2) + ':' + 
        ("0" + dateNow.getMinutes()).slice(-2);
    $('#fecha_manual_pago').val(fechaHoraPago);
}

function cargarCombosPago() {
    $comboNitPagos = $('#id_nit_pago').select2({
        theme: 'bootstrap-5',
        delay: 250,
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
    
    $comboComprobantePagos= $('#id_comprobante_pago').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 1,
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

    if (comprobantesPagos && comprobantesPagos.length == 1) {
        var dataComprobante = {
            id: comprobantesPagos[0].id,
            text: comprobantesPagos[0].codigo + ' - ' + comprobantesPagos[0].nombre
        };
        var newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
        $comboComprobantePagos.append(newOption).val(dataComprobante.id).trigger('change');
    }
}

function cargarTablasPago() {
    pago_table = $('#pagoTable').DataTable({
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
            url: base_url + 'pagos',
            data: function ( d ) {
                d.id_nit = $('#id_nit_pago').val();
                d.fecha_manual = $('#fecha_manual_pago').val();
                d.id_comprobante = $("#id_comprobante_pago").val();
                d.consecutivo = $("#documento_referencia_pago").val();
            }
        },
        columns: [
            {
                "data": function (row, type, set, col){
                    const saldo = parseInt(row.saldo);
                    if (saldo < 0) {
                        return `<i
                            class="fas fa-info icon-info"
                            style="border: solid 1px red !important; color: red !important;";
                            title="<b class='titulo-popover-error'>Documento referencia</b> <br/> Error en el cruce de los documentos de referencia"
                            data-toggle="popover"
                            data-html="true">
                            </i>
                        ${row.codigo_cuenta}`;
                    }
                    if (!row.cuenta_pago && !row.id_forma_pago) {
                        return `<i
                                class="fas fa-info icon-info"
                                style="border: solid 1px #e29300 !important; color: #e29300 !important;";
                                title="<b>Anticipos cuenta</b> <br/> Sin forma de pago registrada"
                                data-toggle="popover"
                                data-html="true"
                            ></i>
                            ${row.codigo_cuenta}`;
                    }
                    return row.codigo_cuenta;
                }
            },
            {
                "data": function (row, type, set, col){
                    if (!row.cuenta_pago) {
                        return row.nombre_cuenta;
                    }
                    return row.nombre_cuenta;
                }, className: 'dt-body-left'
            },
            {"data":'fecha_manual', className: 'dt-body-left'},
            {"data":'plazo', className: 'dt-body-right'},
            {"data":'dias_cumplidos', className: 'dt-body-right'},
            {//DOCUMENTO REFERENCIA
                "data": function (row, type, set, col){
                    if (row.cuenta_pago) {
                        return row.documento_referencia;
                    }
                    var isValid = row.documento_referencia ? 'is-valid' : '';
                    return `
                        <div class="input-group">
                            <input type="text" class="form-control ${isValid} form-control-sm" style="text-align: right; height: 25px; border-radius: 7px; padding: 5px;" id="pago_documentorefe_${row.id}" onkeypress="changeDocumentoRefePagoRow(${row.id}, event)" onfocusout="focusOutDocumentoReferencia(${row.id})" value="${row.documento_referencia}" style="min-width: 100px;">
                            <i class="fa fa-spinner fa-spin fa-fw documento-load" id="documentopago_load_${row.id}" style="display: none; position: absolute; color: #76b2b2; margin-left: 2px; margin-top: 5px; z-index: 99;"></i>
                            <div class="valid-feedback info-factura" style="margin-top: -5px;"></div>
                            <div class="invalid-feedback info-factura" style="margin-top: -5px;">Factura existente</div>
                        </div>
                    `;
                }, className: 'dt-body-left'
            },
            {//SALDO
                "data": function (row, type, set, col){
                    const saldo = parseInt(row.saldo);
                    if (saldo < 0) {
                        return `<p style="margin-bottom: 0px; font-size: 13px; color: red; font-weight: 600;">${new Intl.NumberFormat("ja-JP").format(row.saldo * -1)}</p>`;
                    }
                    return new Intl.NumberFormat("ja-JP").format(row.saldo);
                }, className: 'dt-body-right'   
            },
            {//VALOR RECIBIDO
                "data": function (row, type, set, col){
                    if (row.cuenta_pago == 'sin_deuda') {
                        return
                    }
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right; padding: 0.05rem 0.5rem !important;" id="pago_valor_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.valor_recibido)}" onkeypress="changeValorRecibidoPagoRow(${row.id}, event)" onfocusout="focusOutValorPagoRow(${row.id})" onfocus="focusValorRecibido(${row.id})" style="min-width: 100px;">`;
                }
            },
            {//NUEVO SALDO
                "data": function (row, type, set, col){
                    const saldo = parseInt(row.saldo);
                    if (saldo < 0) {
                        return `<p style="margin-bottom: 0px; font-size: 13px; color: red; font-weight: 600;">${new Intl.NumberFormat("ja-JP").format(row.nuevo_saldo * -1)}</p>`;
                    }
                    return new Intl.NumberFormat("ja-JP").format(row.nuevo_saldo);
                }, className: 'dt-body-right'   
            },
            {//CONCEPTO
                "data": function (row, type, set, col){
                    if (row.cuenta_pago == 'sin_deuda') {
                        return
                    }
                    return `<input type="text" class="form-control form-control-sm" id="pago_concepto_${row.id}" placeholder="SIN OBSERVACIÓN" value="${row.concepto}" onkeypress="changeConceptoPagoRow(${row.id}, event)" onblur="outFocusConceptoPagoRow(${row.id})" style="width: 150px !important; padding: 0.05rem 0.5rem !important;" onfocus="this.select();">`;
                }
            }
        ],
        'rowCallback': function(row, data, index){
            if (data.cuenta_pago == 'sin_deuda') {
                $('td', row).css('background-color', 'rgb(11 177 158)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(!data.cuenta_pago) {
                $('td', row).css('background-color', 'rgb(64 164 209 / 21%)');
                return;
            }
        },
        initComplete: function () {
            $('#pagoTable').on('draw.dt', function() {
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

    pago_anticipos = $('#pagoAnticipos').DataTable({
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
            url: base_url + 'extracto',
            data: function ( d ) {
                d.id_nit = $('#id_nit_pago').val();
                d.id_tipo_cuenta = [7];
                d.fecha_manual = $('#fecha_manual_pago').val().replace('T', ' ') + ':00';
            }
        },
        columns: [
            {"data":'cuenta'},
            {"data":'nombre_cuenta'},
            {"data":'fecha_manual'},
            {"data":'documento_referencia'},
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
        ]
    });

    pago_table_pagos = $('#pagoFormaPago').DataTable({
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
                type: 'egresos'
            },
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data": function (row, type, set){
                let styles = "margin-bottom: 0px; font-size: 13px;";
                let stylesInfo = null;
                let naturaleza = 'Credito - Egreso';
                if (!row.cuenta.naturaleza_egresos) {
                    naturaleza = 'Error de naturaleza en egreso';
                    stylesInfo = "border: solid 1px red !important; color: red !important;"
                }
                let dataContent = `<b class='titulo-popover'>Cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                if (row.cuenta.tipos_cuenta.length > 0) {
                    var tiposCuentas = row.cuenta.tipos_cuenta;
                    for (let index = 0; index < tiposCuentas.length; index++) {
                        const tipoCuenta = tiposCuentas[index];
                        if (tipoCuenta.id_tipo_cuenta == 7) {
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
                let anticipos = false;
                let id_cuenta = row.cuenta.id;
                let className = '';
                if (row.cuenta.tipos_cuenta.length > 0) {
                    var tiposCuentas = row.cuenta.tipos_cuenta;
                    for (let index = 0; index < tiposCuentas.length; index++) {
                        const tipoCuenta = tiposCuentas[index];
                        if (tipoCuenta.id_tipo_cuenta == 7 || tipoCuenta.id_tipo_cuenta == 3) {
                            anticipos = true;
                            className = 'anticipos'
                        }
                    }
                }
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoPago(${row.id}, ${anticipos}, ${id_cuenta})" onfocusout="calcularPagosPagos(${row.id})" onkeypress="changeFormaPagoPago(${row.id}, event, ${anticipos}, ${id_cuenta})" id="pago_forma_pago_${row.id}">`;
            }},
        ],
        initComplete: function () {
            $('#pagoFormaPago').on('draw.dt', function() {
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

    pago_table_movimiento = $('#pagoMovimientoTable').DataTable({
        pageLength: -1,
        deferRender: true,
        deferLoading: true,
        dom: 'Brtip',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: false,
        fixedHeader: true,
        deferLoading: 0,
        language: {
            ...lenguajeDatatable,
            info: "",
            infoEmpty: "",
            infoFiltered: "",
        },
        ordering: false,
        scrollX: true,
        scrollCollapse: true,
        sScrollX: "100%",
        autoWidth: false,
        info: false,
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'pagos-movimiento',
            data: function ( d ) {
                d.id_nit = $("#id_nit_pago").val(),
                d.pagos = JSON.stringify(getPagosPagos()),
                d.movimiento = JSON.stringify(getMovimientoPago())
            }
        },
        rowCallback: function(row, data, index){
            if(data.id_cuenta == "TOTALES"){
                $('td', row).css('background-color', '#000');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
        },
        columns: [
            {
                "data": function (row, type, set){
                    if(row.id_cuenta == "TOTALES") return "TOTALES";
                    if(row.cuenta) return row.cuenta.cuenta;
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.cuenta) return row.cuenta.nombre;
                    return '';
                }
            },
            {"data":'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    const diferencia = parseFloat(row.concepto);
                    if(row.id_cuenta == "TOTALES") {
                        return diferencia.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }
                    return row.concepto;
                }
            }
        ]
    });
}

function buscarFacturaPagos(event) {

    if(event.keyCode != 13) return;

    document.getElementById('iniciarCapturaPago').click();
}

function agregarPagosPagos(pagos) {
    if (!pagos.length) return;

    const sumasPorFormaPago = {};

    pagos.forEach(pago => {
        const formaPagoId = pago.id_forma_pago;
        const valor = parseFloat(pago.valor);

        if (!sumasPorFormaPago[formaPagoId]) {
            sumasPorFormaPago[formaPagoId] = 0;
        }

        sumasPorFormaPago[formaPagoId] += valor;
    });

    Object.entries(sumasPorFormaPago).forEach(([formaPagoId, total]) => {
        const input = $("#pago_forma_pago_" + formaPagoId);

        input.val(new Intl.NumberFormat("ja-JP").format(total));
        
        if (total > 0) {
            setTimeout(function(){
                input.prop("disabled", false);
            },100);
        }
    });

    const ultimoPago = pagos[pagos.length - 1].id_forma_pago;
    calcularPagosPagos(ultimoPago);
}

$(document).on('change', '#id_comprobante_pago', function () {
    consecutivoSiguientePago();
});

$(document).on('click', '#iniciarCapturaPago', function () {

    $("#crearCapturaPago").hide();
    $('#iniciarCapturaPago').hide();
    $('#cancelarCapturaPago').hide();
    $("#movimientoContablePago").hide();
    $('#crearCapturaPagoDisabled').hide();
    $('#iniciarCapturaPagoLoading').show();
    
    reloadTablePagos();
});

$(document).on('click', '#show-anticipos-pagos', function () {
    $("#pagosFormModal").modal('show');
    pago_anticipos.ajax.reload();
});

function reloadTablePagos() {
    pago_table.ajax.reload(function (res) {
        $('#iniciarCapturaPago').show();
        $('#crearCapturaPagoDisabled').show();
        $('#iniciarCapturaPagoLoading').hide();
        $('#total_abono_pago').prop('disabled', false);

        let factura = res.edit;

        if (factura) {
            $('#cancelarCapturaPago').show();
            const anticiposEditados = res.anticipos;

            noBuscarDatosPago = true;
            $("#id_pago_up").val(factura.id);

            var dataFormato = {
                id: factura.nit.id,
                text: factura.nit.numero_documento+' - '+factura.nit.nombre_completo
            };
            var newOption = new Option(dataFormato.text, dataFormato.id, false, false);
            $comboNitPagos.append(newOption).trigger('change');
            $comboNitPagos.val(dataFormato.id).trigger('change');

            $('#fecha_manual_pago').val(normalizarFecha(factura.fecha_manual));
            $('#total_abono_pago').val(factura.total_abono);
            agregarPagosPagos(factura.pagos);
            loadAnticiposPago(factura.fecha_manual, anticiposEditados);
        } else {
            loadAnticiposPago();
        }

        mostrarValoresPagos();

        if (res.errores) {
            disabledInputsPagos();
            disabledFormasPagoRecibo(false);
            $('#crearCapturaRecibo').hide();
            $('#total_abono_recibo').prop('disabled', true);
            agregarToast('error', 'Recibo con errores', 'Error en el cruce de los documentos de referencia');
        }

        if (!factura) {
            let data = $('#id_nit_pago').select2('data')[0];
            if (data) {
                $('#cancelarCapturaPago').show();
            }
        }
    });
}

$(document).on('click', '#cancelarCapturaPago', function () {
    cancelarPago();
});

$(document).on('click', '#crearCapturaPago', function () {
    savePago();
});

$(document).on('click', '#movimientoContablePago', function () {
    $("#pagoMovimientoModal").modal('show');
    pago_table_movimiento.ajax.reload();
});

$(document).on('change', '#id_nit_pago', function () {
    let data = $('#id_nit_pago').select2('data')[0];
    if (data && !noBuscarDatosPago) {
        noBuscarDatosPago = false;
        document.getElementById('iniciarCapturaPago').click();
    }
    if (!noBuscarDatosPago) {
        noBuscarDatosPago = false;
    }
});

function savePago() {
    $('#iniciarCapturaPago').hide();
    $('#cancelarCapturaPago').hide();
    $('#crearCapturaPago').hide();
    $('#movimientoContablePago').hide();
    $('#iniciarCapturaPagoLoading').show();

    let data = {
        id_pago: $("#id_pago_up").val(),
        pagos: getPagosPagos(),
        movimiento: getMovimientoPago(),
        id_nit: $("#id_nit_pago").val(),
        id_comprobante: $("#id_comprobante_pago").val(),
        fecha_manual: $('#fecha_manual_pago').val().replace('T', ' ') + ':00',
        consecutivo: $("#documento_referencia_pago").val(),
    }

    disabledFormasPagoPago();

    $.ajax({
        url: base_url + 'pagos',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        cancelarPago();
        $('#iniciarCapturaPago').show();
        $('#iniciarCapturaPagoLoading').hide();
        agregarToast('exito', 'Creación exitosa', 'Pago creado con exito!', true);

        guardandoPago = false
        if(res.impresion) {
            window.open("/pago-print/"+res.impresion, '_blank');
        }
    }).fail((err) => {
        disabledFormasPagoPago(false);
        $('#iniciarCapturaPago').show();
        $('#cancelarCapturaPago').show();
        $('#crearCapturaPago').show();
        $('#movimientoContablePago').show();
        $('#iniciarCapturaPagoLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getPagosPagos() {
    var data = [];

    var dataPagoPagos = pago_table_pagos.rows().data();

    if(!dataPagoPagos.length > 0) return data;

    for (let index = 0; index < dataPagoPagos.length; index++) {
        const dataPagoCompra = dataPagoPagos[index];
        var pagoPago = stringToNumberFloat($('#pago_forma_pago_'+dataPagoCompra.id).val());
        if (pagoPago > 0) {
            data.push({
                id: dataPagoCompra.id,
                valor: pagoPago
            });
        }
    }

    return data;
}

function getMovimientoPago() {
    var data = [];
    var dataPagos = pago_table.rows().data();

    if(!dataPagos.length) return data;

    for (let index = 0; index < dataPagos.length; index++) {
        var pago = dataPagos[index];

        if (pago.valor_recibido) {
            data.push(pago);
        }
    }
    return data;
}

function cancelarPago() {

    const dateNow = new Date;
    
    $comboNitPagos.val(0).trigger('change');
    totalAnticiposPago = 0;
    pago_table.clear().draw();
    totalAnticiposPagoCuenta = [];

    consecutivoSiguientePago();
    clearFormasPagoPago();

    var fechaHoraPago = dateNow.getFullYear() + '-' + 
        ("0" + (dateNow.getMonth() + 1)).slice(-2) + '-' + 
        ("0" + dateNow.getDate()).slice(-2) + 'T' + 
        ("0" + dateNow.getHours()).slice(-2) + ':' + 
        ("0" + dateNow.getMinutes()).slice(-2);

    $('#fecha_manual_pago').val(fechaHoraPago);
    $('#total_abono_pago').val('0.00');
    $('#saldo_anticipo_pago').val('0');
    $('#pago_anticipo_disp').text('0');
    $('#crearCapturaPago').hide();
    $('#cancelarCapturaPago').hide();
    $('#movimientoContablePago').hide();
    $('#input_anticipos_pago').hide();
    $('#pago_anticipo_disp_view').hide();
    $('#crearCapturaPagoDisabled').hide();
}

function clearFormasPagoPago() {
    var dataFormasPago = pago_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#pago_forma_pago_'+formaPago.id).val(0);
        }
    }
}

function changeTotalAbonoPago(event) {
    const dataPagos = pago_table.rows().data();
    if(event.keyCode == 13 && dataPagos.length) {
        let totalAbono = stringToNumberFloat($('#total_abono_pago').val());
        let dataAnticipo = {
            'index': null,
            'pago': null
        };
        let totalSaldo = 0;

        for (let index = 0; index < dataPagos.length; index++) {

            const pago = dataPagos[index];

            if (!pago.cuenta_pago) {
                if (!dataAnticipo.pago) {
                    dataAnticipo.index = index;
                    dataAnticipo.pago = pago;
                }
                continue;
            }

            if (pago.cuenta_pago == "sin_deuda") continue;

            if (totalAbono <= 0) {
                pago.valor_recibido = 0;
                pago.nuevo_saldo = pago.saldo;
            } else {
                totalSaldo+= parseFloat(pago.saldo);
                if (pago.saldo >= totalAbono) {
                    pago.valor_recibido = totalAbono;
                    pago.nuevo_saldo = pago.saldo - totalAbono;
                    totalAbono = 0;
                } else if (totalAbono >= pago.saldo) {
                    pago.valor_recibido = pago.saldo;
                    pago.nuevo_saldo = 0;
                    totalAbono-= pago.saldo;
                }
            }

            if (!pago.concepto && pago.valor_recibido > 0) {
                if (pago.nuevo_saldo == 0) pago.concepto = 'CANCELO DEUDA';
                else pago.concepto = 'ABONO DEUDA';
            }

            pago_table.row(index).data(pago);
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
        }

        if (totalAbono) {
            
            dataAnticipo.pago.nuevo_saldo = totalAbono;
            dataAnticipo.pago.valor_recibido = totalAbono;
            dataAnticipo.pago.documento_referencia = $('#documento_referencia_pago').val();
            dataAnticipo.pago.concepto = "ANTICIPO RECIBO";
            totalSaldo+= parseFloat(totalAbono);
            totalAbono = 0;
            pago_table.row(dataAnticipo.index).data(dataAnticipo.pago);
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
        }

        mostrarValoresPagos();

        const dataPagoPagado = pago_table_pagos.rows().data();
        if(dataPagoPagado.length) {
            for (let index = 0; index < dataPagoPagado.length; index++) {

                const formaPago = dataPagoPagado[index];
                const input = document.getElementById("pago_forma_pago_"+formaPago.id);

                if (input.disabled) {
                    continue;
                }

                focusFormaPagoPago(formaPago.id);
                break;
            }
        }
    }
}

function changeConceptoPagoRow(idRow, event) {
    if (!idRow) return;

    if (event.keyCode == 13) {
        var concepto = $("#pago_concepto_"+idRow).val();
        var data = getDataById(idRow, pago_table);
        data.concepto = concepto;
        pago_table.row(idRow-1).data(data);
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
    }
}

function outFocusConceptoPagoRow(idRow) {
    if (!idRow) return;

    var concepto = $("#pago_concepto_"+idRow).val();
    var data = getDataById(idRow, pago_table);
    data.concepto = concepto;
    pago_table.row(idRow-1).data(data);
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
}

function mostrarValoresPagos() {
    
    var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresPagos();

    if (totalAnticipos) {
        $('#pago_anticipo_view').show();
    } else {
        $('#pago_anticipo_view').hide();
    }

    if ((totalAbonos+totalAnticipos)) disabledFormasPagoPago(false);
    else disabledFormasPagoPago();

    var [totalPagos, totalCXP] = totalFormasPagoPagos();

    var countA = new CountUp('pago_abono', 0, totalAbonos, 2, 0.5);
        countA.start();

    var countB = new CountUp('pago_saldo', 0, totalSaldo, 2, 0.5);
        countB.start();

    var countC = new CountUp('pago_anticipo', 0, totalAnticipos, 2, 0.5);
        countC.start();

    var countD = new CountUp('pago_total', 0, totalSaldo - totalAbonos, 2, 0.5);
        countD.start();

    if (!totalSaldo && !totalAbonos && !totalAnticipos) {
        var countE = new CountUp('total_faltante_pago', 0, 0, 2, 0.5);
            countE.start();
    } else {
        var countE = new CountUp('total_faltante_pago', 0, (totalAbonos + totalAnticipos) - (totalPagos + totalCXP), 2, 0.5);
            countE.start();
    }

    if (!totalSaldo) {
        $('#crearCapturaPago').hide();
        $('#cancelarCapturaPago').hide();
        $('#movimientoContableRecibo').hide();
        $('#crearCapturaPagoDisabled').show();
    } else {
        $('#movimientoContablePago').show();
    }

    if (!((totalAbonos + totalAnticipos) - (totalPagos + totalCXP)) && $('#id_nit_pago').val()) {
        $('#crearCapturaPago').show();
        $('#crearCapturaPagoDisabled').hide();
    } else {
        $('#crearCapturaPago').hide();
        $('#crearCapturaPagoDisabled').show();
    }
}

function actualizarTotalAbono() {
    var dataPagos = pago_table.rows().data();
    var totalAbonos = 0;

    for (let index = 0; index < dataPagos.length; index++) {
        var pago = dataPagos[index];
     
        if (pago.cuenta_pago) {//ABONOS
            totalAbonos+= parseFloat(pago.valor_recibido);
        }
    }

    $('#total_abono_pago').val(new Intl.NumberFormat("ja-JP").format(totalAbonos));
}

function focusFormaPagoPago(idFormaPago, anticipo = false, id_cuenta = null) {
    var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresPagos();
    var [totalPagos, totalCXP] = totalFormasPagoPagos(idFormaPago);
    var totalFactura = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);
    var saldoFormaPago = stringToNumberFloat($('#pago_forma_pago_'+idFormaPago).val());

    if (anticipo) {

        let cuentaExistente = encontrarCuentaPago(id_cuenta);
        if (cuentaExistente) {
            let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
            if ((totalSaldoAnticipos - totalCXP) < totalFactura) {
                $('#pago_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalSaldoAnticipos - totalCXP));
                $('#pago_forma_pago_'+idFormaPago).select();
                return;
            }
        }
        return;
    }

    if (!saldoFormaPago) {
        $('#pago_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format(totalFactura < 0 ? 0 : totalFactura));
    }
    $('#pago_forma_pago_'+idFormaPago).select();
}

function totalValoresPagos() {
    var totalSaldo = 0;
    var totalAbonos = 0;
    var totalAnticipos = 0;

    var dataPagos = pago_table.rows().data();

    for (let index = 0; index < dataPagos.length; index++) {
        var pago = dataPagos[index];
        if (!pago.cuenta_pago) {//ANTICIPOS
            totalAnticipos+= pago.valor_recibido;
        } else if (parseFloat(pago.valor_recibido)){//PAGOS
            totalAbonos+= parseFloat(pago.valor_recibido);
        }
        if (parseFloat(pago.saldo)) {
            totalSaldo+= parseFloat(pago.saldo);
        }
    }

    return [totalSaldo, totalAbonos, totalAnticipos];
}

function totalFormasPagoPagos(idFormaPago = null) {

    var totalPagos = 0;
    var totalAnticipos = 0;
    var dataPagoPago = pago_table_pagos.rows().data();

    if(dataPagoPago.length > 0) {
        for (let index = 0; index < dataPagoPago.length; index++) {
            
            var ventaPago = stringToNumberFloat($('#pago_forma_pago_'+dataPagoPago[index].id).val());

            if (idFormaPago && idFormaPago == dataPagoPago[index].id) continue;

            if ($('#pago_forma_pago_'+dataPagoPago[index].id).hasClass("anticipos")) totalAnticipos+= ventaPago;
            else totalPagos+= ventaPago;
        }
    }

    return [totalPagos, totalAnticipos];
}

function calcularPagosPagos(idFormaPago = null) {

    if (
        parseInt($('#pago_forma_pago_'+idFormaPago).val()) == '' ||
        $('#pago_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#pago_forma_pago_'+idFormaPago).val(0);
    }

    var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresPagos();
    var [totalPagos, totalCXP] = totalFormasPagoPagos();
    var totalFaltante = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);

    if (idFormaPago && totalFaltante < 0) {
        var [totalPagos, totalCXP] = totalFormasPagoPagos(idFormaPago);
        $('#pago_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format(totalAbonos - (totalPagos + totalCXP)));
        $('#pago_forma_pago_'+idFormaPago).select();
        return;
    }

    totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    var countE = new CountUp('total_faltante_pago', 0, totalFaltante, 2, 0.5);
        countE.start();

    if (!totalFaltante) {
        $('#crearCapturaPago').show();
        $('#movimientoContablePago').show();
        $('#crearCapturaPagoDisabled').hide();
    } else {
        $('#crearCapturaPago').hide();
        $('#movimientoContablePago').hide();
        $('#crearCapturaPagoDisabled').show();
    }
}

function changeFormaPagoPago(idFormaPago, event, anticipo, id_cuenta) {
    if(event.keyCode == 13){

        calcularPagosPagos(idFormaPago);

        var [totalPagos, totalCXP] = totalFormasPagoPagos();
        var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresPagos();
        var totalFaltante = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);

        if (anticipo) {

            let cuentaExistente = encontrarCuentaPago(id_cuenta);
            if (cuentaExistente) {

                let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
                if (totalCXP > totalSaldoAnticipos) {
                    $('#pago_forma_pago_'+idFormaPago).val(totalSaldoAnticipos);
                    $('#pago_forma_pago_'+idFormaPago).select();
                    calcularPagosPagos();
                    return;
                } else if (totalCXP > (totalFaltante + totalCXP)) {
                    $('#pago_forma_pago_'+idFormaPago).val(totalFaltante);
                    $('#pago_forma_pago_'+idFormaPago).select();
                    calcularPagosPagos();
                    return;
                }
            }
        }

        if (totalFaltante == 0) {
            validateSavePagos();
            return;
        }
        
        focusNextFormasPagoPagos(idFormaPago);
    }
}

function validateSavePagos() {
    $('#total_faltante_pago_text').css("color","#484848");
    $('#total_faltante_pago').css("color","#484848");

    if (!guardandoPago) {

        var [totalPagos, totalCXP] = totalFormasPagoPagos();
        var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresPagos();
        
        if ((totalPagos + totalCXP) >= (totalAbonos + totalAnticipos)) {
            guardandoPago = true;
            savePago();
        } else {
            $('#total_faltante_pago_text').css("color","red");
            $('#total_faltante_pago').css("color","red");
            return;
        }
    }
}

function loadAnticiposPago(fecha_manual = null, anticiposEditados = null) {
    totalAnticiposPago = 0;
    $('#input_anticipos_pago').hide();
    $('#pago_anticipo_disp_view').hide();
    $('#saldo_anticipo_pago').val(0);
    $('#pago_anticipo_disp').text('0.00');

    if(!$('#id_nit_pago').val()) return;
    
    let data = {
        id_nit: $('#id_nit_pago').val(),
        id_tipo_cuenta: [7],
        fecha_manual: fecha_manual,
        sin_documento: anticiposEditados
    }

    $.ajax({
        url: base_url + 'extracto-anticipos',
        method: 'GET',
        data: data,
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            totalAnticiposPago = 0;
            totalAnticiposPagoCuenta = [];

            if (res.data.length) {
                const anticiposDisponibles = res.data;
                for (let index = 0; index < anticiposDisponibles.length; index++) {
                    const anticipo = anticiposDisponibles[index];

                    let idCuenta = anticipo.id_cuenta;
                    let cuentaExistente = encontrarCuentaPago(idCuenta);
                    
                    totalAnticiposPago+= Math.abs(parseFloat(anticipo.saldo));

                    if (cuentaExistente) {
                        cuentaExistente[idCuenta].saldo = (cuentaExistente[idCuenta].saldo || 0) + parseFloat(anticipo.saldo);
                    } else {
                        let nuevoObj = {};
                        nuevoObj[idCuenta] = {
                            'id_cuenta': idCuenta,
                            'saldo': Math.abs(parseFloat(anticipo.saldo))
                        };
                        totalAnticiposPagoCuenta.push(nuevoObj);
                    }
                }

                if (totalAnticiposPago) {
                    $('#pago_anticipo_disp_view').show();
                    $('#saldo_anticipo_pago').val(new Intl.NumberFormat('ja-JP').format(totalAnticiposPago));
                    $('#pago_anticipo_disp').text(new Intl.NumberFormat('ja-JP').format(totalAnticiposPago));
                }
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function encontrarCuentaPago(idCuenta) {
    if (totalAnticiposPagoCuenta && totalAnticiposPagoCuenta.length) {
        return totalAnticiposPagoCuenta.find(item => item[idCuenta]);
    }
    return false;
}

function changeDocumentoRefePagoRow(idRow, event) {
    var documentoReferencia = $('#pago_documentorefe_'+idRow).val();
    var comprobante = $('#id_comprobante_pago').val();

    $('#pago_documentorefe_'+idRow).removeClass("is-invalid");
    $('#pago_documentorefe_'+idRow).removeClass("is-valid");

    if (event.keyCode == 13 && documentoReferencia) {
        $('#documentopago_load_'+idRow).show();
        var data = getDataById(idRow, pago_table);
        
        if (validarFacturaPago) {
            validarFacturaPago.abort();
        }
        setTimeout(function(){
            validarFacturaPago = $.ajax({
                url: base_url + 'existe-factura',
                method: 'GET',
                data: {
                    id_comprobante: comprobante,
                    documento_referencia: documentoReferencia
                },
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                data.documento_referencia = documentoReferencia;
                validarFacturaPago = null;
                $('#documentopago_load_'+idRow).hide();

                if(res.data == 0) $('#pago_documentorefe_'+idRow).addClass("is-valid");
                else $('#pago_documentorefe_'+idRow).removeClass("is-valid");

                setTimeout(function(){
                    $('#pago_valor_'+idRow).focus();
                    $('#pago_valor_'+idRow).select();
                },10);
                pago_table.row(idRow-1).data(data);
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
            }).fail((err) => {
                validarFacturaPago = null;
                if(err.statusText != "abort") {
                    $('#documentopago_load_'+idRow).hide();
                }
            });
        },300);
    }
}

function focusOutDocumentoReferencia(idRow) {
    var data = getDataById(idRow, pago_table);
    var documentoReferencia = $('#pago_documentorefe_'+idRow).val();
    var comprobante = $('#id_comprobante_pago').val();
    $('#pago_documentorefe_'+idRow).removeClass("is-invalid");
    $('#pago_documentorefe_'+idRow).removeClass("is-valid");
        
    if (!validarFacturaPago && documentoReferencia && comprobante) {
        $('#documentopago_load_'+idRow).show();
        setTimeout(function(){
            validarFacturaPago = $.ajax({
                url: base_url + 'existe-factura',
                method: 'GET',
                data: {
                    id_comprobante: comprobante,
                    documento_referencia: documentoReferencia
                },
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $('#documentopago_load_'+idRow).hide();
                data.documento_referencia = documentoReferencia;
                validarFacturaPago = null;
                $('#documentopago_load_'+idRow).hide();
                
                if(res.data == 0) $('#pago_documentorefe_'+idRow).addClass("is-valid");
                else $('#pago_documentorefe_'+idRow).removeClass("is-valid");

                setTimeout(function(){
                    $('#pago_valor_'+idRow).focus();
                    $('#pago_valor_'+idRow).select();
                },10);
                pago_table.row(idRow-1).data(data);
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
            }).fail((err) => {
                $('#documentopago_load_'+idRow).hide();
                validarFacturaPago = null;
                if(err.statusText != "abort") {
                    $('#documentopago_load_'+idRow).hide();
                }
            });
        },80);
    }
}

function focusNextFormasPagoPagos(idFormaPago) {
    var dataCompraPagos = pago_table_pagos.rows().data();
    var idFormaPagoFocus = primeraFormaPago(dataCompraPagos, "pago_forma_pago")
    var obtenerFormaPago = false;

    if(!dataCompraPagos.length > 0) return;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoPago = dataCompraPagos[index];
        const input = document.getElementById("pago_forma_pago_"+dataPagoPago.id);

        if (input.disabled) {
            continue;
        }

        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoPago.id;
            obtenerFormaPago = false;
        } else if (dataPagoPago.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoPago(idFormaPagoFocus);
}

function focusValorRecibido(idRow) {
    var data = getDataById(idRow, pago_table);
    var dataAbono = stringToNumberFloat($("#pago_valor_"+idRow).val());
    if (!dataAbono && data.cuenta_pago) {
        $('#pago_valor_'+idRow).val(new Intl.NumberFormat("ja-JP").format(data.saldo));
    }
    setTimeout(function(){
        $('#pago_valor_'+idRow).select();
    },10);
}

function focusOutValorPagoRow(idRow) {
    if (calculandoRowPagos) return;
    var data = getDataById(idRow, pago_table);
    var valorRecibido = stringToNumberFloat($("#pago_valor_"+idRow).val());
    if (!valorRecibido) return;

    if (data.cuenta_pago) {//ABONOS
        var valorRecibido = stringToNumberFloat($("#pago_valor_"+idRow).val());
    
        if (data.saldo >= valorRecibido) {
            data.valor_recibido = valorRecibido;
            data.nuevo_saldo = data.saldo - valorRecibido;
        } else if (valorRecibido >= data.saldo) {
            data.valor_recibido = data.saldo;
            data.nuevo_saldo = 0;
            $("#pago_valor_"+idRow).val(new Intl.NumberFormat("ja-JP").format(data.saldo));
        }
        
        if (!data.concepto && data.valor_recibido > 0) {
            if (data.nuevo_saldo == 0) data.concepto = 'CANCELO DEUDA';
            else data.concepto = 'ABONO DEUDA';
        }
    } else {//ANTICIPOS
        var anticipoRecibido = stringToNumberFloat($("#pago_valor_"+idRow).val());
        data.valor_recibido = anticipoRecibido;
        data.nuevo_saldo = anticipoRecibido;

        if (!data.concepto) data.concepto = 'ANTICIPO PAGO';
    }

    pago_table.row(idRow-1).data(data);
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
    mostrarValoresPagos();
    actualizarTotalAbono();
}

function changeValorRecibidoPagoRow(idRow, event) {
    if (!idRow) return;
    if(event.keyCode == 13) {
        calculandoRowPagos = true;
        var data = getDataById(idRow, pago_table);

        if (data.cuenta_pago) {//ABONOS
            var valorRecibido = stringToNumberFloat($("#pago_valor_"+idRow).val());
            
            if (data.saldo >= valorRecibido) {
                data.valor_recibido = valorRecibido;
                data.nuevo_saldo = data.saldo - valorRecibido;
            } else if (valorRecibido >= data.saldo) {
                data.valor_recibido = data.saldo;
                data.nuevo_saldo = 0;
                $("#pago_valor_"+idRow).val(new Intl.NumberFormat("ja-JP").format(data.saldo));
            }
            
            if (!data.concepto && data.valor_recibido > 0) {
                if (data.nuevo_saldo == 0) data.concepto = 'CANCELO DEUDA';
                else data.concepto = 'ABONO DEUDA';
            }
        } else {//ANTICIPOS
            var anticipoRecibido = stringToNumberFloat($("#pago_valor_"+idRow).val());
            data.valor_recibido = anticipoRecibido;
            data.nuevo_saldo = anticipoRecibido;

            if (!data.concepto) data.concepto = 'ANTICIPO PAGADO';
        }

        pago_table.row(idRow-1).data(data);
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
        mostrarValoresPagos();
        actualizarTotalAbono();
        setTimeout(function(){
            $('#pago_concepto_'+idRow).focus();
            $('#pago_concepto_'+idRow).select();
        },80);
        setTimeout(function(){
            calculandoRowPagos = false;
        },200); 
    }
}

function consecutivoSiguientePago() {
    var dateNow = new Date;
    var id_comprobante = $('#id_comprobante_pago').val();
    var fecha_manual = $('#fecha_manual_pago').val();
    var fecha_manual_hoy = fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    
    if(id_comprobante && fecha_manual) {

        let data = {
            id_comprobante: id_comprobante,
            fecha_manual: pagoFecha ? fecha_manual : fecha_manual_hoy
        }

        $.ajax({
            url: base_url + 'consecutivo',
            method: 'GET',
            data: data,
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                $("#documento_referencia_pago").val(res.data);
                setTimeout(function(){
                    reloadTablePagos();
                },10);
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function cargarFormasPagoPago() {
    pago_table_pagos.ajax.reload(function(res) {
        disabledFormasPagoPago(true);
    });
}

function disabledInputsPagos(estado = true) {
    const dataPagos = pago_table.rows().data();

    if (dataPagos.length) {
        for (let index = 0; index < dataPagos.length; index++) {
            const pago = dataPagos[index];

            $('#pago_valor_'+pago.id).prop('disabled', estado);
            $('#pago_concepto_'+pago.id).prop('disabled', estado);
        }
    }
}

function disabledFormasPagoPago(estado = true) {
    const dataFormasPago = pago_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {

            const formaPago = dataFormasPago[index];
            const tiposCuentas = formaPago.cuenta.tipos_cuenta;

            if (!tiposCuentas.length) {
                $('#pago_forma_pago_'+formaPago.id).prop('disabled', estado);
                continue;
            }

            for (let index = 0; index < tiposCuentas.length; index++) {
                let isAnticipo = false;
                const tipoCuenta = tiposCuentas[index];

                if (tipoCuenta.id_tipo_cuenta == 7 || tipoCuenta.id_tipo_cuenta == 3) {
                    isAnticipo = true;
                }

                if (isAnticipo) {
                    let cuentaExistente = encontrarCuentaPago(formaPago.cuenta.id);
    
                    if (cuentaExistente) {
                        let totalSaldoAnticipos = cuentaExistente[formaPago.cuenta.id].saldo;
                        if (totalSaldoAnticipos) {
                            $('#pago_forma_pago_'+formaPago.id).prop('disabled', estado);
                        }
                    } else {
                        $('#pago_forma_pago_'+formaPago.id).prop('disabled', true);
                    }
                } else {
                    $('#pago_forma_pago_'+formaPago.id).prop('disabled', estado);
                }
            }
        }
    }
}