var fecha = null;
var pago_table = null;
var calculandoRow = false;
var guardandoPago = false;
var pago_table_pagos = null;
var validarFacturaPago = null;
var totalAnticiposPago = 0;
var $comboNitPagos = null;
var $comboComprobantePagos = null;

function pagoInit () {
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_pago').val(fecha);

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
            }
        },
        columns: [
            {"data":'codigo_cuenta'},
            {
                "data": function (row, type, set, col){
                    if (!row.cuenta_pago) {
                        return 'ANTICIPOS: '+row.nombre_cuenta;
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
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {//VALOR RECIBIDO
                "data": function (row, type, set, col){
                    if (row.cuenta_pago == 'sin_deuda') {
                        return
                    }
                    return `<input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right; padding: 0.05rem 0.5rem !important;" id="pago_valor_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.valor_recibido)}" onkeypress="changeValorRecibidoPagoRow(${row.id}, event)" onfocusout="focusOutValorPagoRow(${row.id})" onfocus="focusValorRecibido(${row.id})" style="min-width: 100px;">`;
                }
            },
            {"data":'nuevo_saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {//CONCEPTO
                "data": function (row, type, set, col){
                    if (row.cuenta_pago == 'sin_deuda') {
                        return
                    }
                    return `<input type="text" class="form-control form-control-sm" id="pago_concepto_${row.id}" placeholder="SIN OBSERVACIÓN" value="${row.concepto}" onkeypress="changeConceptoPagoRow(${row.id}, event)" style="width: 150px !important; padding: 0.05rem 0.5rem !important;" onfocus="this.select();">`;
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
            });
        }
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
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoPago(${row.id}, ${anticipos})" onfocusout="calcularPagosPagos(${row.id})" onkeypress="changeFormaPagoPago(${row.id}, event, ${anticipos})" id="pago_forma_pago_${row.id}">`;
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
            });
        }
    });

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
        $comboComprobantePagos.append(newOption).trigger('change');
        $comboComprobantePagos.val(dataComprobante.id).trigger('change');
    }

    if (pagoFecha) $('#fecha_manual_pago').prop('disabled', false);
    else $('#fecha_manual_pago').prop('disabled', true);

    loadFormasPagoPagos();
}

$(document).on('change', '#id_comprobante_pago', function () {
    consecutivoSiguientePago();
});

$(document).on('click', '#iniciarCapturaPago', function () {
    $('#iniciarCapturaPago').hide();
    $('#iniciarCapturaPagoLoading').show();
    
    
    pago_table.ajax.reload(function () {
        $('#iniciarCapturaPago').show();
        $('#cancelarCapturaPago').show();
        $('#crearCapturaPagoDisabled').show();
        $('#iniciarCapturaPagoLoading').hide();
        loadAnticiposPago();
    });
});

$(document).on('click', '#cancelarCapturaPago', function () {
    cancelarPago();
});

$(document).on('click', '#crearCapturaPago', function () {
    savePago();
});

$(document).on('change', '#id_nit_pago', function () {
    let data = $('#id_nit_pago').select2('data')[0];
    if (data) {
        document.getElementById('iniciarCapturaPago').click();
    }
});

function savePago() {
    $('#iniciarCapturaPago').hide();
    $('#cancelarCapturaPago').hide();
    $('#crearCapturaPago').hide();
    $('#iniciarCapturaPagoLoading').show();

    let data = {
        pagos: getPagosPagos(),
        movimiento: getMovimientoPago(),
        id_nit: $("#id_nit_pago").val(),
        id_comprobante: $("#id_comprobante_pago").val(),
        fecha_manual: $("#fecha_manual_pago").val(),
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
        consecutivoSiguientePago();
        $('#iniciarCapturaPago').show();
        $('#iniciarCapturaPagoLoading').hide();
        agregarToast('exito', 'Creación exitosa', 'Pago creado con exito!', true);

        if(res.impresion) {
            window.open("/pago-print/"+res.impresion, '_blank');
        }
    }).fail((err) => {
        consecutivoSiguientePago();
        $('#iniciarCapturaPago').show();
        $('#cancelarCapturaPago').show();
        $('#crearCapturaPago').show();
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
    $comboNitPagos.val(0).trigger('change');
    var totalRows = pago_table.rows().data().length;
    totalAnticiposPago = 0;

    if(pago_table.rows().data().length){
        pago_table.clear([]).draw();
        pago_table.row(0).remove().draw();
        mostrarValoresPagos();
        var countE = new CountUp('total_faltante_pago', 0, 0, 2, 0.5);
            countE.start();
    }

    clearFormasPagoPago();
    $('#total_abono_pago').val('0.00');
    $('#saldo_anticipo_pago').val('0');
    $('#pago_anticipo_disp').text('0');
    $('#crearCapturaPago').hide();
    $('#cancelarCapturaPago').hide();
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
    var dataPagos = pago_table.rows().data();
    if(event.keyCode == 13 && dataPagos.length) {
        var totalAbono = stringToNumberFloat($('#total_abono_pago').val());
        var totalSaldo = 0;

        for (let index = 0; index < dataPagos.length; index++) {
            var pago = dataPagos[index];

            if (!pago.cuenta_pago) continue;
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
        console.log('totalSaldo: ',totalSaldo);
        if (totalAbono) {
            $('#total_abono_pago').val(new Intl.NumberFormat("ja-JP").format(totalSaldo));
        }
        mostrarValoresPagos();
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

    if (!((totalAbonos + totalAnticipos) - (totalPagos + totalCXP))) {
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

function focusFormaPagoPago(idFormaPago, anticipo = false) {
    var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresPagos();
    var [totalPagos, totalCXP] = totalFormasPagoPagos(idFormaPago);
    var totalFactura = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);
    var saldoFormaPago = stringToNumberFloat($('#pago_forma_pago_'+idFormaPago).val());

    if (anticipo) {
        if ((totalAnticiposPago - totalCXP) < totalFactura) {
            $('#pago_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalAnticiposPago - totalCXP));
            $('#pago_forma_pago_'+idFormaPago).select();
            return;
        }
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
        $('#crearCapturaPagoDisabled').hide();
    } else {
        $('#crearCapturaPago').hide();
        $('#crearCapturaPagoDisabled').show();
    }
}

function changeFormaPagoPago(idFormaPago, event, anticipo) {
    if(event.keyCode == 13){

        calcularPagosPagos(idFormaPago);

        var [totalPagos, totalCXP] = totalFormasPagoPagos();
        var [totalSaldo, totalAbonos, totalAnticipos] = totalValoresPagos();
        var totalFaltante = (totalAbonos + totalAnticipos) - (totalPagos + totalCXP);

        if (anticipo) {

            if (totalCXP > totalAnticiposPago) {

                var [totalPagos, totalCXP] = totalFormasPagoPagos(idFormaPago);

                $('#pago_forma_pago_'+idFormaPago).val(totalAnticiposPago);
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

function loadAnticiposPago() {
    totalAnticiposPago = 0;
    $('#input_anticipos_pago').hide();
    $('#pago_anticipo_disp_view').hide();
    $('#saldo_anticipo_pago').val(0);
    $('#pago_anticipo_disp').text('0.00');
    

    if(!$('#id_nit_pago').val()) return;
    
    let data = {
        id_nit: $('#id_nit_pago').val(),
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
                    $('#pago_anticipo_disp_view').show();
                    // $('#input_anticipos_pago').show();
                    totalAnticiposPago = saldo;
                    $('#saldo_anticipo_pago').val(new Intl.NumberFormat('ja-JP').format(saldo));
                    $('#pago_anticipo_disp').text(new Intl.NumberFormat('ja-JP').format(saldo));
                }
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
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
    var idFormaPagoFocus = dataCompraPagos[0].id;
    var obtenerFormaPago = false;

    if(!dataCompraPagos.length > 0) return;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoPago = dataCompraPagos[index];
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
    if (calculandoRow) return;
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
        calculandoRow = true;
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
            calculandoRow = false;
        },200); 
    }
}

function consecutivoSiguientePago() {
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
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function loadFormasPagoPagos() {
    var totalRows = pago_table_pagos.rows().data().length;
    if(pago_table_pagos.rows().data().length){
        pago_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            pago_table_pagos.row(0).remove().draw();
        }
    }
    pago_table_pagos.ajax.reload(function(res) {
        disabledFormasPagoPago(true);
    });
}

function disabledFormasPagoPago(estado = true) {
    var dataFormasPago = pago_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#pago_forma_pago_'+formaPago.id).prop('disabled', estado);
        }
    }

    if (totalAnticiposPago <= 0) {
        var pagosAnticipos = document.getElementsByClassName('anticipos');
        if (pagosAnticipos) { //HIDE ELEMENTS
            for (let index = 0; index < pagosAnticipos.length; index++) {
                const element = pagosAnticipos[index];
                element.disabled = true;
            }
        }
    }
}