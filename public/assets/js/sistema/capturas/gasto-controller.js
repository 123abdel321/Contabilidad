var dataGasto = [];
var idGastoTable = 0;
var sumarAIU = false;
var fechaGasto = null;
var gasto_table = null;
var gastos_anticipos = null;
var editandoGasto = false;
var calculandoDatos = true;
var gasto_pagos_table = null;
var totalAnticiposGasto = null;
var $comboNitGastos = null;
var guardandoGasto = false;
var retencionesGasto = [];
var porcentajeAIUGastos = 0;
var porcentajeReteica = 0;
var validandoDatosIva = false;
var validarFacturaGastos = false;
var abrirFormasPagoGastos = false;
var $comboCentroCostoGastos = null;
var $comboComprobanteGastos = null;
var totalAnticiposGastoCuenta = null;

function gastoInit () {
    var dateNow = new Date;
    fechaGasto = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_gasto').val(fechaGasto);

    gasto_table = $('#gastoTable').DataTable({
        pageLength: 300,
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
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteGastoRow(${row.id})" id="droprow_'${row.id}'"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            {//CONCEPTO GASTO
                "data": function (row, type, set, col){
                    return `<select class="form-control form-control-sm combo_concepto_gasto combo-grid" id="combo_concepto_gasto_${row.id}"></select>`;
                }
            },
            {//VALOR GASTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm input_number" id="gastovalor_${row.id}" onkeypress="changeValorGasto(${row.id}, event)" onfocus="this.select();" onfocusout="changeValorGasto(${row.id})" style="min-width: 110px !important; text-align: right;" min="0" value="${new Intl.NumberFormat("ja-JP").format(row.valor_gasto)}" disabled>`;
                }
            },
            {//AIU
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 85px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                            <span id="gasto_aiu_porcentaje_text_${row.id}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${row.porcentaje_aiu}%</span>
                            <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" id="gasto_base_aiu_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.base_aiu)}" disabled>
                        </div>
                    </div>`;
                }
            },
            {//PORCENTAJE DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm input_number" id="gastoporcentajedescuento_${row.id}" onkeypress="changePorcentajeDescuentoGasto(${row.id}, event)" onfocus="this.select();" onfocusout="changePorcentajeDescuentoGasto(${row.id})" style="width: 110px !important; text-align: right;" min="0" value="${new Intl.NumberFormat("ja-JP").format(row.porcentaje_descuento_gasto)}" disabled>`;
                }
            },
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm input_number" id="gastovalordescuento_${row.id}" onkeypress="changeValorDescuentoGasto(${row.id}, event)" onfocus="this.select();" onfocusout="changeValorDescuentoGasto(${row.id})" style="width: 110px !important; text-align: right;" min="0" value="${new Intl.NumberFormat("ja-JP").format(row.descuento_gasto)}" disabled>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    if (row.editar_iva) {
                        return  `<input type="text" data-type="currency" class="form-control form-control-sm input_number" onkeypress="changeValorNoIvaGasto(${row.id}, event)" onfocus="validarDatosIva(${row.id})" onfocusout="changeValorNoIvaGasto(${row.id})" style="width: 110px !important; text-align: right;" min="0" id="gasto_no_iva_valor_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.no_valor_iva)}">`;
                    }
                    return `<div class="form-group mb-3" style="min-width: 85px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                            <span id="gasto_iva_porcentaje_text_${row.id}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${row.porcentaje_iva}%</span>
                            <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" id="gasto_iva_valor_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.valor_iva)}" disabled>
                        </div>
                    </div>`;
                }
            },
            {//VALOR RETENCION
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 85px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                            <span id="gasto_retencion_porcentaje_text_${row.id}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${row.porcentaje_retencion}%</span>
                            <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" id="gasto_retencion_valor_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.valor_retencion)}" disabled>
                        </div>
                    </div>`;
                }
            },
            {//VALOR RETEICA
                "data": function (row, type, set, col){
                    return `<div class="form-group mb-3" style="min-width: 85px;">
                        <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                            <span id="gasto_reteica_porcentaje_text_${row.id}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${row.porcentaje_reteica}</span>
                            <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" id="gasto_reteica_valor_${row.id}" value="${new Intl.NumberFormat("ja-JP").format(row.valor_reteica)}" disabled>
                        </div>
                    </div>`;
                }
            },
            {//VALOR TOTAL
                "data": function (row, type, set, col){
                    return new Intl.NumberFormat("ja-JP").format(row.total_valor_gasto);
                }, className: 'dt-body-right'
            },
            {//OBSERVAVACION
                "data": function (row, type, set, col){
                    return `<input type="text" class="form-control form-control-sm" id="gastoobservacion_${row.id}" onkeypress="changeObservacionGasto(${row.id}, event)" onfocus="this.select();" onfocusout="changeObservacionGasto(${row.id})" style="width: 180px !important;" value="${row.observacion}" disabled>`;
                }
            },
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#gastoTable').on('draw.dt', function() {
                $('.combo_concepto_gasto').select2({
                    theme: 'bootstrap-5',
                    dropdownCssClass: 'custom-gasto_conceptogasto',
                    delay: 250,
                    minimumInputLength: 2,
                    ajax: {
                        url: 'api/concepto-gasto/combo',
                        headers: headers,
                        data: function (params) {
                            var query = {
                                q: params.term,
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
                $('.combo_concepto_gasto').on('select2:close', function(event) {
                    var id = this.id.split('_')[3];
                    if (!editandoGasto) changeConceptoGasto(id);
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
        }
    });

    gastos_anticipos = $('#gastoAnticipos').DataTable({
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

    gasto_pagos_table = $('#gastoFormaPago').DataTable({
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
                type: 'gastos'
            },
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data": function (row, type, set){
                let styles = "margin-bottom: 0px; font-size: 13px;";
                let stylesInfo = null;
                let naturaleza = 'Credito - Egreso';
                let anticipos7 = false;
                let anticipos4 = false;
                if (!row.cuenta.naturaleza_egresos) {
                    naturaleza = 'Error de naturaleza en egreso';
                    stylesInfo = "border: solid 1px red !important; color: red !important;"
                }
                let dataContent = `<b>Cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                if (row.cuenta.tipos_cuenta.length > 0) {
                    var tiposCuentas = row.cuenta.tipos_cuenta;
                    for (let index = 0; index < tiposCuentas.length; index++) {
                        const tipoCuenta = tiposCuentas[index];
                        if (tipoCuenta.id_tipo_cuenta == 7) {
                            anticipos7 = true;
                            styles+= " color: #0bb19e; font-weight: 600;"
                            dataContent = `<b>Anticipos cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                        }
                        if (tipoCuenta.id_tipo_cuenta == 4) {
                            anticipos4 = true;
                        }
                    }
                }

                if (anticipos7) {
                    naturaleza = 'Credito - Compra';
                    stylesInfo = null;
                    dataContent = `<b>Anticipos cuenta:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
                }

                if (anticipos4) {
                    naturaleza = 'Credito - Compra';
                    stylesInfo = null;
                    dataContent = `<b>Cuentas por pagar:</b> ${naturaleza}<br/> ${row.cuenta.cuenta} - ${row.cuenta.nombre}`;
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
                        if (tipoCuenta.id_tipo_cuenta == 7) {
                            anticipos = true;
                            className = 'anticipos'
                        }
                    }
                }
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoGasto(${row.id}, ${anticipos}, ${id_cuenta})" onfocusout="calcularGastosPagos(${row.id})" onkeypress="changeFormaPagoGasto(${row.id}, event, ${anticipos}, ${id_cuenta})" id="gasto_forma_pago_${row.id}">`;
            }},
        ],
        initComplete: function () {
            $('#gastoFormaPago').on('draw.dt', function() {
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

    $comboNitGastos = $('#id_nit_gasto').select2({
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
    
    $comboComprobanteGastos = $('#id_comprobante_gasto').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 5,
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

    $comboCentroCostoGastos = $('#id_centro_costos_gasto').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/centro-costos/combo-centro-costo',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
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

    if (comprobantesGastos && comprobantesGastos.length) {
        var dataComprobante = {
            id: comprobantesGastos[0].id,
            text: comprobantesGastos[0].codigo + ' - ' + comprobantesGastos[0].nombre
        };
        var newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
        $comboComprobanteGastos.append(newOption).val(dataComprobante.id).trigger('change');
    }

    if (centrosCostosGastos && centrosCostosGastos.length) {
        var dataCecos = {
            id: centrosCostosGastos[0].id,
            text: centrosCostosGastos[0].codigo + ' - ' + centrosCostosGastos[0].nombre
        };
        var newOption = new Option(dataCecos.text, dataCecos.id, false, false);
        $comboCentroCostoGastos.append(newOption).val(dataCecos.id).trigger('change');
    }

    $comboNitGastos.on('select2:close', function(event) {

        if (editandoGasto) return;

        var dataNit = $('#id_nit_gasto').select2('data');
        var columnValReteIca = gasto_table.column(8);

        if (dataNit && dataNit.length) {
            dataNit = dataNit[0];
            sumarAIU = dataNit.sumar_aiu ? true : false;
            if (dataNit.porcentaje_reteica) {
                porcentajeReteica = parseFloat(dataNit.porcentaje_reteica);
                columnValReteIca.visible(true);
            } else {
                columnValReteIca.visible(false);
            }
        } else {
            porcentajeReteica = 0;
            columnValReteIca.visible(false);
        }

        if (porcentajeReteica) {
            $("#texto_gasto_reteica").html(`RETEICA ${porcentajeReteica}: `);
            $("#gasto_reteica_disp_view").show();
        } else {
            $("#gasto_reteica_disp_view").hide();
        }
    });

    if (gastoUpdate) $("#consecutivo_gasto").prop('disabled', false);
    else $("#consecutivo_gasto").prop('disabled', true);

    dataGasto = [];
    retencionesGasto = [];

    var columnAIU = gasto_table.column(3);//AIU
    var columnPorDescuento = gasto_table.column(4);//% Dscto
    var columnValDescuento = gasto_table.column(5);//Val Dscto

    columnAIU.visible(false);

    if (gastoDescuento) {
        columnPorDescuento.visible(true);
        columnValDescuento.visible(true);
    } else {
        columnPorDescuento.visible(false);
        columnValDescuento.visible(false);
    }

    setTimeout(function(){
        $comboNitGastos.select2("open");
    },10);

    loadFormasPagoGastos();
    
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
}

function loadFormasPagoGastos() {
    gasto_pagos_table.ajax.reload(function(res) {
        disabledFormasPagoGasto(true);
    });
}

function clearFormasPagoGasto() {
    var dataFormasPago = gasto_pagos_table.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#gasto_forma_pago_'+formaPago.id).val(0);
        }
    }
}

function disabledFormasPagoGasto(estado = true) {
    const dataFormasPago = gasto_pagos_table.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {

            const formaPago = dataFormasPago[index];
            const tiposCuentas = formaPago.cuenta.tipos_cuenta;

            if (!tiposCuentas.length) {
                $('#gasto_forma_pago_'+formaPago.id).prop('disabled', estado);
                continue;
            }

            for (let index = 0; index < tiposCuentas.length; index++) {
                let isAnticipo = false;
                const tipoCuenta = tiposCuentas[index];

                if (tipoCuenta.id_tipo_cuenta == 7 || tipoCuenta.id_tipo_cuenta == 3) {
                    isAnticipo = true;
                }

                if (isAnticipo) {
                    let cuentaExistente = encontrarCuentaGasto(formaPago.cuenta.id);
    
                    if (cuentaExistente) {
                        let totalSaldoAnticipos = cuentaExistente[formaPago.cuenta.id].saldo;
                        if (totalSaldoAnticipos) {
                            $('#gasto_forma_pago_'+formaPago.id).prop('disabled', estado);
                        }
                    } else {
                        $('#gasto_forma_pago_'+formaPago.id).prop('disabled', true);
                    }
                } else {
                    $('#gasto_forma_pago_'+formaPago.id).prop('disabled', estado);
                }
            }
        }
    }
}

function consecutivoSiguienteGasto() {
    var id_comprobante = $('#id_comprobante_gasto').val();
    var fecha_manual = $('#fecha_manual_gasto').val();
    if(id_comprobante && fecha_manual) {

        let data = {
            id_comprobante: id_comprobante,
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
                $("#consecutivo_gasto").val(res.data);
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function addRowGastos(openCuenta = true) {
    var rows = gasto_table.rows().data();
    var totalRows = rows.length;
    var dataLast = rows[totalRows - 1];
    if (dataLast) {
        var conceptoGastoLast = $('#combo_concepto_gasto_'+dataLast.id).val();
        if (!conceptoGastoLast) {
            document.getElementById("card-gasto").scrollLeft = 0;
            $('#combo_concepto_gasto_'+dataLast.id).select2('open');
            return;
        }
    }

    idGastoTable++;
    
    let data = {
        "id": idGastoTable,
        "id_concepto": null,
        "editar_iva": false,
        "valor_gasto": 0,
        'porcentaje_aiu': porcentajeAIUGastos,
        'base_aiu': 0,
        "descuento_gasto": 0,
        "porcentaje_descuento_gasto": 0,
        "valor_iva": 0,
        "valor_reteica": 0,
        "no_valor_iva": 0,
        "porcentaje_iva": 0,
        "valor_retencion": 0,
        "porcentaje_retencion": 0,
        "porcentaje_reteica": 0,
        "total_valor_gasto": 0,
        'observacion':  '',
    };

    dataGasto.push(data);
    gasto_table.row.add(data).draw(false);
    document.getElementById("card-gasto").scrollLeft = 0;
    if(openCuenta) $('#combo_concepto_gasto_'+idGastoTable).select2('open');
    mostrarValoresGastos();
}

function changeConceptoGasto(idGasto) {
    let data = $('#combo_concepto_gasto_'+idGasto).select2('data')[0];
    if (!data) return;
    
    var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
    var indexTable = getIndexById(idGasto, gasto_table);
    var proveedor = $comboNitGastos.select2('data')[0];
    
    dataGasto[indexGasto].id_concepto = parseInt(data.id);
    //IVA
    if (data.id_cuenta_iva && data.cuenta_iva.impuesto) {
        dataGasto[indexGasto].porcentaje_iva = parseFloat(data.cuenta_iva.impuesto.porcentaje);
    } else if (!data.id_cuenta_iva && parseInt(proveedor.porcentaje_aiu)) {
        dataGasto[indexGasto].porcentaje_iva = porcentajeIvaAIU; //CALCULA SIEMPRE U CUANDO TIENE PORCENTAJE DE IVA Y ESTA EN EL ENTORNO CONFIGURADO
    } else if (!data.id_cuenta_iva && !parseInt(proveedor.porcentaje_aiu)) {
        dataGasto[indexGasto].editar_iva = true;
    }

    //RETEICA
    if (data.cuenta_reteica && data.cuenta_reteica.id) {
        if (porcentajeReteica) {
            dataGasto[indexGasto].porcentaje_reteica = porcentajeReteica
        }
    }

    //RETENCION
    if (!proveedor.declarante) {
        if (data.cuenta_retencion_declarante && data.cuenta_retencion_declarante.impuesto) {
            var existe = retencionesGasto.findIndex(item => item.id_retencion == data.cuenta_retencion_declarante.impuesto.id);
            if (!existe || existe < 0) {
                retencionesGasto.push({
                    cuenta: data.cuenta_retencion_declarante.cuenta,
                    nombre: data.cuenta_retencion_declarante.nombre,
                    id_retencion: data.cuenta_retencion_declarante.impuesto.id,
                    porcentaje: parseFloat(data.cuenta_retencion_declarante.impuesto.porcentaje),
                    base: parseFloat(data.cuenta_retencion_declarante.impuesto.base),
                });
            }
        }
    } else {
        if (data.cuenta_retencion && data.cuenta_retencion.impuesto) {
            var existe = retencionesGasto.findIndex(item => item.id_retencion == data.cuenta_retencion.impuesto.id);
            if (!existe || existe < 0) {
                retencionesGasto.push({
                    cuenta: data.cuenta_retencion.cuenta,
                    nombre: data.cuenta_retencion.nombre,
                    id_retencion: data.cuenta_retencion.impuesto.id,
                    porcentaje: parseFloat(data.cuenta_retencion.impuesto.porcentaje),
                    base: parseFloat(data.cuenta_retencion.impuesto.base),
                });
            }
        }
    }

    dataGasto[indexGasto].observacion = data.text.split(' - ')[1];

    var dataConcepto = {
        id: data.id,
        text: data.text
    };
    var newOption = new Option(dataConcepto.text, dataConcepto.id, false, false);

    gasto_table.row(indexTable).data(dataGasto[indexGasto]).draw(false);

    setTimeout(function(){
        $('#combo_concepto_gasto_'+idGasto).append(newOption).trigger('change');
        $('#combo_concepto_gasto_'+idGasto).val(data.id).trigger('change');
    },10);

    setTimeout(function(){
        $('#gastoTable tr').find('#gastovalor_'+idGasto).focus();
        $('#gastoTable tr').find('#gastovalor_'+idGasto).select();
    },20);

    setDisabledGastosRow(data, idGasto);
}

function mostrarValoresGastos () {
    
    var [gasto_iva, gasto_reteica, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu] = totalValoresGastos();

    if (gasto_iva) $("#gasto_iva_disp_view").show();
    else $("#gasto_iva_disp_view").hide();
    
    // var columnRetencion = gasto_table.column(7);//Retencion
    // if (gasto_retencion) {
    //     columnRetencion.visible(true);
    //     $("#gasto_retencion_disp_view").show();
    // } else {
    //     columnRetencion.visible(false);
    //     $("#gasto_retencion_disp_view").hide();
    // }

    if (gasto_descuento) $("#gasto_descuento_disp_view").show();
    else $("#gasto_descuento_disp_view").hide();

    var countA = new CountUp('gasto_sub_total', 0, gasto_sub_total, 2, 0.5);
        countA.start();

    var countB = new CountUp('gasto_iva', 0, gasto_iva, 2, 0.5);
        countB.start();

    var countB = new CountUp('gasto_reteica', 0, gasto_reteica, 2, 0.5);
        countB.start();

    var countC = new CountUp('gasto_retencion', 0, gasto_retencion, 2, 0.5);
        countC.start();

    var countD = new CountUp('gasto_descuento', 0, gasto_descuento, 2, 0.5);
        countD.start();

    var countE = new CountUp('gasto_total', 0, gasto_total, 2, 0.5);
        countE.start();

    var countF = new CountUp('gasto_aiu', 0, gasto_aiu, 2, 0.5);
        countF.start();

    if (gasto_total) disabledFormasPagoGasto(false);
    else disabledFormasPagoGasto();
}

function setDisabledGastosRow(data = null, idGasto) {
    if (data) {
        $("#gastovalor_"+idGasto).prop('disabled', false);
        $("#gastoobservacion_"+idGasto).prop('disabled', false);
    } else {
        $("#gastovalor_"+idGasto).prop('disabled', true);
        $("#gastoobservacion_"+idGasto).prop('disabled', true);
    }

    if (data && gastoDescuento) {
        $("#gastoporcentajedescuento_"+idGasto).prop('disabled', false);
        $("#gastovalordescuento_"+idGasto).prop('disabled', false);
    } else {
        $("#gastoporcentajedescuento_"+idGasto).prop('disabled', true);
        $("#gastovalordescuento_"+idGasto).prop('disabled', true);
    }

    if (data && gastoAIU) {
        $("#porcentajeaiu_"+idGasto).prop('disabled', false);
    } else {
        $("#porcentajeaiu_"+idGasto).prop('disabled', true);
    }
}

function changeValorDescuentoGasto (idGasto, event = null) {
    if (editandoGasto) return;
    if(!event || event.keyCode == 13){
        if (!calculandoDatos) return;
        calculandoDatos = false;

        var baseAIU = 0;

        var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
        var valorGasto = redondear(dataGasto[indexGasto].valor_gasto, redondeoGastos);
        var dataConcepto = $('#combo_concepto_gasto_'+idGasto).select2('data')[0];
        var valorDescuento = redondear(stringToNumberFloat($("#gastovalordescuento_"+idGasto).val()), redondeoGastos);
        var valorNoiva = redondear(stringToNumberFloat($("#gasto_no_iva_valor_"+idGasto).val()), redondeoGastos);

        var valorPorcentajeDescuento = (valorDescuento / valorGasto) * 100;
        var valorSubtotal = redondear(valorGasto - (valorDescuento), redondeoGastos);

        if (porcentajeAIUGastos) baseAIU = valorSubtotal * (porcentajeAIUGastos / 100);
        var valorRetencion = 0;
        var valorReteIca = 0;
        var valorIva = 0;

        var [valorRetencion, porcentajeRetencion] = calcularRetencion(null, valorSubtotal - valorNoiva, baseAIU);
        valorRetencion = redondear(valorRetencion, redondeoGastos);

        if (baseAIU) {
            valorReteIca = dataGasto[indexGasto].porcentaje_reteica ? baseAIU * (dataGasto[indexGasto].porcentaje_reteica / 1000) : 0;
            valorIva = dataGasto[indexGasto].porcentaje_iva ? baseAIU * (dataGasto[indexGasto].porcentaje_iva / 100) : 0;
        } else {
            valorReteIca = dataGasto[indexGasto].porcentaje_reteica ? (valorSubtotal - valorNoiva) * (dataGasto[indexGasto].porcentaje_reteica / 1000) : 0;
            valorIva = dataGasto[indexGasto].porcentaje_iva ? valorSubtotal * (dataGasto[indexGasto].porcentaje_iva / 100) : 0;
        }

        valorReteIca = redondear(valorReteIca, redondeoGastos);
        valorIva = redondear(valorIva, redondeoGastos);

        var valorTotal = 0;

        if (sumarAIU) valorTotal = parseFloat((valorSubtotal + valorIva + baseAIU) - (valorRetencion + valorReteIca)).toFixed(2);
        else valorTotal = parseFloat((valorSubtotal + valorIva) - (valorRetencion + valorReteIca)).toFixed(2);

        valorTotal = redondear(valorTotal, redondeoGastos);

        dataGasto[indexGasto].porcentaje_descuento_gasto = valorPorcentajeDescuento;
        dataGasto[indexGasto].descuento_gasto = valorDescuento;
        dataGasto[indexGasto].total_valor_gasto = valorTotal;
        dataGasto[indexGasto].valor_iva = valorIva;
        dataGasto[indexGasto].no_valor_iva = valorNoiva;
        dataGasto[indexGasto].valor_retencion = valorRetencion;
        dataGasto[indexGasto].valor_reteica = valorReteIca;

        updateDataGasto(dataGasto[indexGasto], dataConcepto, idGasto);
        mostrarValoresGastos();
        actualizarInfoRetencion();
        setTimeout(function(){
            calculandoDatos = true;
        },50);
    }
}

function changeValorNoIvaGasto (idGasto, event = null) {
    if (editandoGasto) return;
    if(!event || event.keyCode == 13){
        if (!calculandoDatos) return;
        calculandoDatos = false;

        if ($('#gasto_no_iva_valor_'+idGasto).val() == '') {
            calculandoDatos = true;
            $('#gasto_no_iva_valor_'+idGasto).addClass("is-invalid");
            return;
        }

        var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
        var dataConcepto = $('#combo_concepto_gasto_'+idGasto).select2('data')[0];
        var valorGasto = dataGasto[indexGasto].valor_gasto;
        var valorDescuento = redondear(stringToNumberFloat($("#gastovalordescuento_"+idGasto).val()), redondeoGastos);
        var valorNoiva = redondear(stringToNumberFloat($("#gasto_no_iva_valor_"+idGasto).val()), redondeoGastos);

        var valorPorcentajeDescuento = (valorDescuento / valorGasto) * 100;
        var valorSubtotal = redondear(valorGasto - (valorDescuento), redondeoGastos);
        var valorIva = redondear(valorSubtotal * (dataGasto[indexGasto].porcentaje_iva / 100), redondeoGastos);
        var valorRetencion = redondear(dataGasto[indexGasto].porcentaje_retencion ? (valorSubtotal - valorNoiva) * (dataGasto[indexGasto].porcentaje_retencion / 100) : 0, redondeoGastos);
        var valorReteIca = redondear(dataGasto[indexGasto].porcentaje_reteica ? (valorSubtotal - valorNoiva) * (dataGasto[indexGasto].porcentaje_reteica / 1000) : 0, redondeoGastos);
        var valorTotal = redondear((valorSubtotal) - (valorRetencion + valorReteIca), redondeoGastos);

        dataGasto[indexGasto].porcentaje_descuento_gasto = valorPorcentajeDescuento;
        dataGasto[indexGasto].descuento_gasto = valorDescuento;
        dataGasto[indexGasto].total_valor_gasto = valorTotal;
        dataGasto[indexGasto].valor_iva = valorIva;
        dataGasto[indexGasto].no_valor_iva = valorNoiva;
        dataGasto[indexGasto].valor_retencion = valorRetencion;
        dataGasto[indexGasto].valor_reteica = valorReteIca;

        updateDataGasto(dataGasto[indexGasto], dataConcepto, idGasto);
        mostrarValoresGastos();
        setTimeout(function(){
            calculandoDatos = true;
            setTimeout(function(){
                $('#gastoTable tr').find('#gastoobservacion_'+idGasto).focus();
                $('#gastoTable tr').find('#gastoobservacion_'+idGasto).select();
            },10);
        },50);
    }
}

function changeObservacionGasto (idGasto, event = null) {
    if (editandoGasto) return;
    if(!event || event.keyCode == 13){
        var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
        var dataObservacion = $('#gastoobservacion_'+idGasto).val();
        if (dataGasto[indexGasto].observacion == dataObservacion && !event) return;
        if (!dataObservacion) return;
        if (!calculandoDatos) return;

        calculandoDatos = false;
        var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
        var dataConcepto = $('#combo_concepto_gasto_'+idGasto).select2('data')[0];

        dataGasto[indexGasto].observacion = dataObservacion;

        updateDataGasto(dataGasto[indexGasto], dataConcepto, idGasto);

        setTimeout(function(){
            calculandoDatos = true;
        },50);
    }
    if (event && event.keyCode == 13) {
        setTimeout(function(){
            addRowGastos();
        },100);
    }
}

function changePorcentajeDescuentoGasto (idGasto, event = null) {
    if (editandoGasto) return;
    if(!event || event.keyCode == 13){
        if (!calculandoDatos) return;
        calculandoDatos = false;

        var baseAIU = 0;

        var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
        var dataConcepto = $('#combo_concepto_gasto_'+idGasto).select2('data')[0];
        var valorGasto = dataGasto[indexGasto].valor_gasto;
        var valorNoiva = stringToNumberFloat($("#gasto_no_iva_valor_"+idGasto).val());

        var valorPorcentajeDescuento = stringToNumberFloat($("#gastoporcentajedescuento_"+idGasto).val());
        var valorDescuento = valorGasto * (valorPorcentajeDescuento / 100);
        var valorSubtotal = valorGasto - valorDescuento;

        if (porcentajeAIUGastos) baseAIU = valorSubtotal * (porcentajeAIUGastos / 100);
        var valorRetencion = 0;
        var valorReteIca = 0;
        var valorIva = 0;

        var [valorRetencion, porcentajeRetencion] = calcularRetencion(null, valorSubtotal - valorNoiva, baseAIU);

        if (baseAIU) {
            valorReteIca = dataGasto[indexGasto].porcentaje_reteica ? baseAIU * (dataGasto[indexGasto].porcentaje_reteica / 1000) : 0;
            valorIva = dataGasto[indexGasto].porcentaje_iva ? baseAIU * (dataGasto[indexGasto].porcentaje_iva / 100) : 0;
        } else {
            valorReteIca = dataGasto[indexGasto].porcentaje_reteica ? (valorSubtotal - valorNoiva) * (dataGasto[indexGasto].porcentaje_reteica / 1000) : 0;
            valorIva = dataGasto[indexGasto].porcentaje_iva ? valorSubtotal * (dataGasto[indexGasto].porcentaje_iva / 100) : 0;
        }
        
        var valorTotal = 0;

        if (sumarAIU) valorTotal = parseFloat((valorSubtotal + valorIva + baseAIU) - (valorRetencion + valorReteIca)).toFixed(2);
        else valorTotal = parseFloat((valorSubtotal + valorIva) - (valorRetencion + valorReteIca)).toFixed(2);

        dataGasto[indexGasto].porcentaje_descuento_gasto = valorPorcentajeDescuento;
        dataGasto[indexGasto].descuento_gasto = valorDescuento;
        dataGasto[indexGasto].total_valor_gasto = valorTotal;
        dataGasto[indexGasto].valor_iva = valorIva;
        dataGasto[indexGasto].no_valor_iva = valorNoiva;
        dataGasto[indexGasto].valor_retencion = valorRetencion;
        dataGasto[indexGasto].valor_reteica = valorReteIca;

        updateDataGasto(dataGasto[indexGasto], dataConcepto, idGasto);
        
        mostrarValoresGastos();
        actualizarInfoRetencion();
        setTimeout(function(){
            calculandoDatos = true;
        },50);
    }
}

function changeValorGasto (idGasto, event = null) {
    if (editandoGasto) return;

    if(!event || event.keyCode == 13){
        
        var valorGasto = stringToNumberFloat($("#gastovalor_"+idGasto).val());
        if (!valorGasto) return;
        if (!calculandoDatos) return;
        calculandoDatos = false;
        
        valorGasto = redondear(valorGasto, redondeoGastos);
        
        var baseAIU = 0;

        var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
        var dataConcepto = $('#combo_concepto_gasto_'+idGasto).select2('data')[0];
        var valorDescuento = stringToNumberFloat($("#gastovalordescuento_"+idGasto).val());
        var valorNoiva = stringToNumberFloat($("#gasto_no_iva_valor_"+idGasto).val());

        var valorPorcentajeDescuento = (valorDescuento / valorGasto) * 100;
        var valorSubtotal = redondear(valorGasto - (valorDescuento), redondeoGastos);
        
        if (porcentajeAIUGastos) baseAIU = redondear(valorSubtotal * (porcentajeAIUGastos / 100), redondeoGastos);
        var valorRetencion = 0;
        var valorReteIca = 0;
        var valorIva = 0;

        var [valorRetencion, porcentajeRetencion] = calcularRetencion(null, valorSubtotal - valorNoiva, baseAIU, idGasto);
        valorRetencion = redondear(valorRetencion, redondeoGastos);
        
        if (baseAIU) {
            valorReteIca = dataGasto[indexGasto].porcentaje_reteica ? baseAIU * (dataGasto[indexGasto].porcentaje_reteica / 1000) : 0;
            valorIva = dataGasto[indexGasto].porcentaje_iva ? baseAIU * (dataGasto[indexGasto].porcentaje_iva / 100) : 0;
        } else {
            valorReteIca = dataGasto[indexGasto].porcentaje_reteica ? (valorSubtotal - valorNoiva) * (dataGasto[indexGasto].porcentaje_reteica / 1000) : 0;
            valorIva = dataGasto[indexGasto].porcentaje_iva ? valorSubtotal * (dataGasto[indexGasto].porcentaje_iva / 100) : 0;
        }

        valorReteIca = redondear(valorReteIca, redondeoGastos);
        valorIva = redondear(valorIva, redondeoGastos);

        var valorTotal = 0 ;
        if (sumarAIU) valorTotal = parseFloat((valorSubtotal + valorIva + baseAIU) - (valorRetencion + valorReteIca)).toFixed(2);
        else valorTotal = parseFloat((valorSubtotal + valorIva) - (valorRetencion + valorReteIca)).toFixed(2);

        valorTotal = redondear(valorTotal, redondeoGastos);

        dataGasto[indexGasto].valor_gasto = valorGasto;
        dataGasto[indexGasto].porcentaje_descuento_gasto = valorPorcentajeDescuento;
        dataGasto[indexGasto].descuento_gasto = valorDescuento;
        dataGasto[indexGasto].total_valor_gasto = valorTotal;
        dataGasto[indexGasto].porcentaje_retencion = porcentajeRetencion;
        dataGasto[indexGasto].valor_iva = valorIva;
        dataGasto[indexGasto].base_aiu = baseAIU;
        dataGasto[indexGasto].no_valor_iva = valorNoiva;
        dataGasto[indexGasto].valor_retencion = valorRetencion;
        dataGasto[indexGasto].valor_reteica = valorReteIca;

        updateDataGasto(dataGasto[indexGasto], dataConcepto, idGasto);
        mostrarValoresGastos();
        
        var focusNext = '#gastoobservacion_';
        
        if (dataGasto[indexGasto].editar_iva)  focusNext = '#gasto_no_iva_valor_';

        setTimeout(function(){
            calculandoDatos = true;
            actualizarInfoRetencion();
            setTimeout(function(){
                $('#gastoTable tr').find(focusNext+idGasto).focus();
                $('#gastoTable tr').find(focusNext+idGasto).select();
            },10);
        },50);
    }
}

function ceroEnterGastos () {
    setTimeout(function(){
        calculandoDatos = true;
    },50);
    focusNextFormasPagoGastos();
}

function updateDataGasto(dataGasto, dataConcepto, idGasto) {
    var indexTable = getIndexById(idGasto, gasto_table);
    var newOption = new Option(dataConcepto.text, dataConcepto.id, false, false);

    gasto_table.row(indexTable).data(dataGasto).draw(false);

    setTimeout(function(){
        $('#combo_concepto_gasto_'+idGasto).append(newOption).trigger('change');
        $('#combo_concepto_gasto_'+idGasto).val(dataConcepto.id).trigger('change');
    },10);

    setDisabledGastosRow(dataConcepto, idGasto);
}

function focusFormaPagoGasto (idFormaPago, anticipo = false, id_cuenta = null) {

    if (guardandoGasto) {
        return;
    }

    var [gasto_iva, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu] = totalValoresGastos();
    var [totalPagos, totalAnticipos] = totalFormasPagoGastos(idFormaPago);
    var totalFaltante = gasto_sub_total - (totalPagos + totalAnticipos);
    var saldoFormaPago = stringToNumberFloat($('#gasto_forma_pago_'+idFormaPago).val());

    if (anticipo) {
        let cuentaExistente = encontrarCuentaGasto(id_cuenta);
        if (cuentaExistente) {
            let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
            if ((totalSaldoAnticipos - totalAnticipos) < totalFaltante) {
                $('#gasto_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalSaldoAnticipos - totalAnticipos));
                $('#gasto_forma_pago_'+idFormaPago).select();
                return;
            }
        }
        return;
    }

    if (!saldoFormaPago) {
        $('#gasto_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format(totalFaltante < 0 ? 0 : totalFaltante));
    }
    $('#gasto_forma_pago_'+idFormaPago).select();
}

function encontrarCuentaGasto(idCuenta) {
    if (totalAnticiposGastoCuenta && totalAnticiposGastoCuenta.length) {
        return totalAnticiposGastoCuenta.find(item => item[idCuenta]);
    }
    return false;
}

function totalFormasPagoGastos (idFormaPago = null) {
    var totalPagos = 0;
    var totalAnticipos = 0;
    var dataPagoGasto = gasto_pagos_table.rows().data();

    if(dataPagoGasto.length > 0) {
        for (let index = 0; index < dataPagoGasto.length; index++) {
            
            var gastoPago = stringToNumberFloat($('#gasto_forma_pago_'+dataPagoGasto[index].id).val());

            if (idFormaPago && idFormaPago == dataPagoGasto[index].id) continue;

            if ($('#gasto_forma_pago_'+dataPagoGasto[index].id).hasClass("anticipos")) totalAnticipos+= gastoPago;
            else totalPagos+= gastoPago;
        }
    }

    return [totalPagos, totalAnticipos];
}

function totalValoresGastos () {

    var gasto_iva = 0;
    var gasto_aiu = 0;
    var gasto_total = 0;
    var gasto_reteica = 0;
    var gasto_sub_total = 0;
    var gasto_retencion = 0;
    var gasto_descuento = 0;
    
    dataGasto.forEach(gastoRow => {
        const valorIva = parseFloat(gastoRow.no_valor_iva);
        gasto_iva+= valorIva ?? parseFloat(gastoRow.valor_iva);
        gasto_reteica+= parseFloat(gastoRow.valor_reteica);
        gasto_retencion+= parseFloat(gastoRow.valor_retencion);
        gasto_descuento+= parseFloat(gastoRow.descuento_gasto);
        gasto_total+= parseFloat(gastoRow.total_valor_gasto);
        gasto_sub_total+= parseFloat(gastoRow.valor_gasto) - parseFloat(gastoRow.descuento_gasto)
        gasto_aiu+= parseFloat(gastoRow.base_aiu);
    });

    return [gasto_iva, gasto_reteica, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu];
}

function calcularRetencion (valorSubtotal = null, valorGastoRow, baseAIU = 0, idGasto = null) {

    let calcularRow = false;
    let totalRetencion = 0;
    let porcentaje = 0;
    let base = 0;

    if (!valorSubtotal) {
        calcularRow = true;
        valorSubtotal = valorGastoRow;
        dataGasto.forEach(gastoRow => {
            if (gastoRow.id != idGasto) {
                valorSubtotal+= (gastoRow.valor_gasto - (gastoRow.descuento_gasto + gastoRow.no_valor_iva));
            }
        });
    }

    [base, porcentaje] = obtenerDatosRetencion(valorSubtotal);

    if (baseAIU) {
        totalRetencion = baseAIU * (porcentaje / 100);
    } else {
        if (!calcularRow && porcentaje) totalRetencion = valorSubtotal * (porcentaje / 100);
        if (calcularRow && porcentaje) totalRetencion = valorGastoRow * (porcentaje / 100);
    }

    return [totalRetencion, porcentaje];
}

function obtenerDatosRetencion(valorSubtotal) {
    porcentaje = 0;
    base = 0;

    retencionesGasto.forEach(retencion => {
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

function actualizarInfoRetencion() {
    const iconInfo = document.getElementById('icon_info_retencion');
    var [gasto_iva, gasto_reteica, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu] = totalValoresGastos();
    var porcentaje = 0;
    var base = 0;
    var nombre = '';

    retencionesGasto.forEach(retencion => {
        if (retencion.base > base ) {
            if (retencion.porcentaje > porcentaje) {
                porcentaje = retencion.porcentaje;
                base = retencion.base;
                nombre = `${retencion.cuenta} - ${retencion.nombre}`;
            }
        }
    });

    $("#nombre_info_retencion").html(`RETENCIÓN %${porcentaje}:`);

    let baseformat = new Intl.NumberFormat('ja-JP').format(base);
    let valorSubtotal = new Intl.NumberFormat('ja-JP').format(gasto_sub_total);
    let aiuNombre = '';

    if (porcentajeAIUGastos) {
        aiuValor = new Intl.NumberFormat('ja-JP').format(gasto_aiu);
        aiuNombre = `<b>AIU:</b> ${aiuValor}<br/>`;
    }

    const nuevoTitulo = `
        <b>Cuenta:</b> ${nombre}<br/>
        <b>Base:</b> ${baseformat}<br/>
        <b>Subtotal:</b> ${valorSubtotal}<br/>
        ${aiuNombre}
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


function changeFormaPagoGasto (idFormaPago, event, anticipo, id_cuenta) {
    if(event.keyCode == 13){

        calcularGastosPagos(idFormaPago);

        var [gasto_iva, gasto_reteica, gasto_reteica, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu] = totalValoresGastos();
        var [totalPagos, totalAnticipos] = totalFormasPagoGastos();
        var totalFaltante = gasto_total - (totalPagos + totalAnticipos);
        
        if (anticipo) {
            
            let cuentaExistente = encontrarCuentaGasto(id_cuenta);
            if (cuentaExistente) {

                let totalSaldoAnticipos = cuentaExistente[id_cuenta].saldo;
                if (totalCXP > totalSaldoAnticipos) {
                    var [totalPagos, totalCXP] = totalFormasPagoGastos(idFormaPago);
                    $('#gasto_forma_pago_'+idFormaPago).val(totalSaldoAnticipos);
                    $('#gasto_forma_pago_'+idFormaPago).select();
                    calcularGastosPagos();
                    return;
                } else if (totalCXP > (totalFaltante + totalCXP)) {
                    $('#gasto_forma_pago_'+idFormaPago).val(totalFaltante);
                    $('#gasto_forma_pago_'+idFormaPago).select();
                    calcularGastosPagos();
                    return;
                }
            }
        }

        if (totalFaltante == 0) {
            validateSaveGastos();
            return;
        }

        focusNextFormasPagoGastos(idFormaPago);
    }
}

function focusNextFormasPagoGastos(idFormaPago = null) {
    var dataCompraGastos = gasto_pagos_table.rows().data();
    var idFormaPagoFocus = dataCompraGastos[0].id;
    var obtenerFormaPago = false;

    if(!dataCompraGastos.length > 0) return;

    for (let index = 0; index < dataCompraGastos.length; index++) {
        const dataPagoGasto = dataCompraGastos[index];
        const input = document.getElementById("gasto_forma_pago_"+dataPagoGasto.id);

        if (input.disabled) {
            continue;
        }

        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoGasto.id;
            obtenerFormaPago = false;
        } else if (dataPagoGasto.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoGasto(idFormaPagoFocus);
}

function validateSaveGastos() {
    if (!guardandoGasto) {

        var documento_referencia = $("#documento_referencia_gasto").val();
        if (!documento_referencia) {
            $('#documento_referencia_gasto').addClass("is-invalid");
            $('#documento_referencia_gasto').focus().select();
            return;
        }

        var [totalPagos, totalAnticipos] = totalFormasPagoGastos();
        var [gasto_iva, gasto_reteica, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu] = totalValoresGastos();
        
        if (gasto_total - (totalPagos + totalAnticipos) == 0) {
            guardandoGasto = true;
            saveGasto();
        }
    }
}

function loadAnticiposGasto(fecha_manual = null) {
    totalAnticiposGasto = 0;
    $('#gasto_anticipo_disp_view').hide();
    $('#saldo_anticipo_pago').val(0);
    $('#gasto_anticipo_disp').text('0.00');

    if(!$('#id_nit_gasto').val()) return;
    
    let data = {
        id_nit: $('#id_nit_gasto').val(),
        id_tipo_cuenta: [7],
        fecha_manual: fecha_manual
    }

    $.ajax({
        url: base_url + 'extracto-anticipos',
        method: 'GET',
        data: data,
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            totalAnticiposGasto = 0;
            totalAnticiposGastoCuenta = [];

            if (res.data.length) {
                const anticiposDisponibles = res.data;
                for (let index = 0; index < anticiposDisponibles.length; index++) {
                    const anticipo = anticiposDisponibles[index];

                    let idCuenta = anticipo.id_cuenta;
                    let cuentaExistente = encontrarCuentaGasto(idCuenta);
                    
                    totalAnticiposGasto+= Math.abs(parseFloat(anticipo.saldo));

                    if (cuentaExistente) {
                        cuentaExistente[idCuenta].saldo = (cuentaExistente[idCuenta].saldo || 0) + parseFloat(anticipo.saldo);
                    } else {
                        let nuevoObj = {};
                        nuevoObj[idCuenta] = {
                            'id_cuenta': idCuenta,
                            'saldo': Math.abs(parseFloat(anticipo.saldo))
                        };
                        totalAnticiposGastoCuenta.push(nuevoObj);
                    }
                }

                if (totalAnticiposGasto) {
                    $('#gasto_anticipo_disp_view').show();
                    $('#gasto_anticipo_disp').text(new Intl.NumberFormat('ja-JP').format(totalAnticiposGasto));
                }
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function saveGasto () {
    $("#agregarGasto").hide();
    $("#crearCapturaGasto").hide();
    $("#cancelarCapturaGasto").hide();
    $("#crearCapturaGastoDisabled").hide();
    $("#iniciarCapturaGastoLoading").show();

    let data = {
        gastos: getGastosData(),
        pagos: getGastosPagos(),
        id_proveedor: $('#id_nit_gasto').val(),
        id_comprobante: $("#id_comprobante_gasto").val(),
        id_centro_costos: $("#id_centro_costos_gasto").val(),
        fecha_manual: $("#fecha_manual_gasto").val(),
        documento_referencia: $("#documento_referencia_gasto").val(),
        consecutivo: $("#consecutivo_gasto").val(),
        editing_gasto: $("#editing_gasto").val(),
    }

    disabledFormasPagoGasto();
    
    $.ajax({
        url: base_url + 'gastos',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        
        cancelarGasto();
        consecutivoSiguienteGasto();

        $("#iniciarCapturaGasto").show();
        $("#iniciarCapturaGastoLoading").hide();

        agregarToast('exito', 'Creación exitosa', 'Gasto creado con exito!', true);

        guardandoGasto = false;
        if(res.impresion) {
            window.open("/gasto-print/"+res.impresion, '_blank');
        }

        dataGasto = [];

        setTimeout(function(){
            $('#id_nit_gasto').focus();
            $comboNitGastos.select2("open");
        },10);
    }).fail((err) => {
        guardandoGasto = false;
        
        disabledFormasPagoGasto(false);
        $("#agregarGasto").show();
        $("#crearCapturaGasto").show();
        $("#cancelarCapturaGasto").show();
        $("#iniciarCapturaGastoLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getGastosPagos() {
    var data = [];

    var dataGastoPagos = gasto_pagos_table.rows().data();

    if(!dataGastoPagos.length > 0) return data;

    for (let index = 0; index < dataGastoPagos.length; index++) {
        const datapagoGasto = dataGastoPagos[index];
        var pagoGasto = stringToNumberFloat($('#gasto_forma_pago_'+datapagoGasto.id).val());
        if (pagoGasto > 0) {
            data.push({
                id: datapagoGasto.id,
                valor: pagoGasto
            });
        }
    }

    return data;
}

function getGastosData() {
    var data = [];

    for (let index = 0; index < dataGasto.length; index++) {
        const gasto = dataGasto[index];
        if (gasto.valor_gasto) {
            data.push(gasto);
        }
    }

    return data;
}

function calcularGastosPagos (idFormaPago = null) {

    if (
        parseInt($('#gasto_forma_pago_'+idFormaPago).val()) == '' ||
        $('#gasto_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#gasto_forma_pago_'+idFormaPago).val(0);
    }

    var [gasto_iva, gasto_reteica, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu] = totalValoresGastos();
    var [totalPagos, totalAnticipos] = totalFormasPagoGastos();
    var totalFaltante = gasto_total - (totalPagos + totalAnticipos);

    if (idFormaPago && totalFaltante < 0) {
        var [totalPagos, totalCXP] = totalFormasPagoGastos(idFormaPago);
        $('#gasto_forma_pago_'+idFormaPago).val(new Intl.NumberFormat("ja-JP").format((totalPagos + totalCXP)));
        $('#gasto_forma_pago_'+idFormaPago).select();
        return;
    }

    totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    var countA = new CountUp('total_faltante_gasto', 0, totalFaltante, 2, 0.5);
        countA.start();

    if (!totalFaltante) {
        $("#gasto_faltante_view").hide();
        $('#crearCapturaGasto').show();
        $('#crearCapturaGastoDisabled').hide();
    } else {
        $("#gasto_faltante_view").show();
        $('#crearCapturaGasto').hide();
        $('#crearCapturaGastoDisabled').show();
    }
}

function deleteGastoRow (idGasto) {
    var indexTable = getIndexById(idGasto, gasto_table);
    gasto_table.row(indexTable).remove().draw();
    var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
    dataGasto.splice(indexGasto, 1);
    
    clearFormasPagoGasto();
    mostrarValoresGastos();

    if (gasto_table.rows().data().length == 0) {
        retencionesGasto = [];
    }

    $("#crearCapturaGasto").hide();
    $("#crearCapturaGastoDisabled").show();
}

$(document).on('change', '#id_nit_gasto', function () {
    let data = $('#id_nit_gasto').select2('data')[0];
    if(gasto_table.rows().data().length){
        gasto_table.clear([]).draw();
        gasto_table.row(0).remove().draw();
        mostrarValoresGastos();
        var countE = new CountUp('total_faltante_gasto', 0, 0, 2, 0.5);
            countE.start();
    }
    
    $('#total_faltante_gasto').val('0.00');
    $('#cancelarCapturaGasto').hide();
    $('#input_anticipos_gasto').hide();
    var columnAIU = gasto_table.column(3);//AIU
    porcentajeAIUGastos = 0;
    columnAIU.visible(false);
    if (data && data.porcentaje_aiu && data.porcentaje_aiu != 0) configurarAIU(data.porcentaje_aiu);
    setTimeout(function(){
        $('#documento_referencia_gasto').focus().select();
    },10);

    loadAnticiposGasto();

    if (!calculandoDatos) {
        calculandoDatos = false;
    }

});

$(document).on('change', '#id_comprobante_gasto', function () {
    consecutivoSiguienteGasto();
});

$(document).on('click', '#show-anticipos-gasto', function () {
    $("#gastosFormModal").modal('show');
    gastos_anticipos.ajax.reload();
});

$(document).on('click', '#iniciarCapturaGasto', function () {

    if (gastoUpdate) {

        $("#crearCapturaGasto").hide();
        $("#iniciarCapturaGasto").hide();
        $("#iniciarCapturaGastoLoading").show();

        $.ajax({
            url: base_url + 'gastos',
            method: 'GET',
            data: {
                consecutivo: $("#consecutivo_gasto").val(),
                fecha_manual: $("#fecha_manual_gasto").val(),
                id_comprobante: $("#id_comprobante_gasto").val(),
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if (res.success && res.data) {
                cancelarGasto();
                editandoGasto = false;
                calculandoDatos = true;
                
                const gastos = res.data;
                const pagos = gastos.pagos;
                const detalles = gastos.detalles;

                $("#editing_gasto").val("1");
                $("#fecha_manual_gasto").val(gastos.fecha_manual);
                $("#documento_referencia_gasto").val(gastos.documento_referencia);
                
                if (gastos.nit) {
                    var dataFormato = {
                        id: gastos.nit.id,
                        text: gastos.nit.numero_documento+' - '+gastos.nit.nombre_completo
                    };
                    var newOption = new Option(dataFormato.text, dataFormato.id, false, false);
                    $comboNitGastos.append(newOption).val(dataFormato.id).trigger('change');

                    var columnValReteIca = gasto_table.column(8);

                    sumarAIU = gastos.nit.sumar_aiu ? true : false;
                    if (gastos.nit.porcentaje_reteica) {
                        porcentajeReteica = parseFloat(gastos.nit.porcentaje_reteica);
                        columnValReteIca.visible(true);
                    } else {
                        columnValReteIca.visible(false);
                    }

                    if (porcentajeReteica) {
                        $("#texto_gasto_reteica").html(`RETEICA ${porcentajeReteica}: `);
                        $("#gasto_reteica_disp_view").show();
                    } else {
                        $("#gasto_reteica_disp_view").hide();
                    }

                    if (gastos.nit.porcentaje_aiu) configurarAIU(gastos.nit.porcentaje_aiu)
                }
                
                for (let index = 0; index < detalles.length; index++) {
                    const detalle = detalles[index];
                    addRowGastosData(detalle, gastos.nit);
                }

                agregarPagosGastos(pagos);

                $("#agregarGasto").show();
                $("#cancelarCapturaGasto").show();
                $("#iniciarCapturaGasto").hide();
                $("#iniciarCapturaGastoLoading").hide();
                
                loadAnticiposGasto(gastos.fecha_manual);

                // editandoGasto = false;
            } else {
                $("#editing_gasto").val("0");
                $("#agregarGasto").show();
                $("#cancelarCapturaGasto").show();
                $("#crearCapturaGastoDisabled").show();
                $("#iniciarCapturaGasto").hide();
                $("#iniciarCapturaGastoLoading").hide();
                addRowGastos();
            }
        }).fail((err) => {
            $('#documento_referencia_gasto_loading').hide();
        });
        return;
    }

    $("#editing_gasto").val("0");
    $("#agregarGasto").show();
    $("#cancelarCapturaGasto").show();
    $("#crearCapturaGastoDisabled").show();
    $("#iniciarCapturaGasto").hide();
    $("#iniciarCapturaGastoLoading").hide();
    addRowGastos();
});

$(document).on('click', '#agregarGasto', function () {
    addRowGastos();
});

$(document).on('click', '#crearCapturaGasto', function () {
    validateSaveGastos();
});

$(document).on('click', '#cancelarCapturaGasto', function () {
    cancelarGasto();
});

function agregarPagosGastos(pagos) {
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
        const input = $("#gasto_forma_pago_" + formaPagoId);

        input.val(new Intl.NumberFormat("ja-JP").format(total));
        
        if (total > 0) {
            setTimeout(function(){
                input.prop("disabled", false);
            },100);
        }
    });

    const ultimoPago = pagos[pagos.length - 1].id_forma_pago;
    calcularGastosPagos(ultimoPago);
}

function cancelarGasto() {

    const dateNow = new Date;
    const fechaGasto = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $comboNitGastos.val(0).trigger('change');
    totalAnticiposGasto = 0;
    dataGasto = [];
    idGastoTable = 0;
    retencionesGasto = [];
    gasto_table.clear().draw();
    totalAnticiposGastoCuenta = [];

    clearFormasPagoGasto();
    mostrarValoresGastos();
    
    $('#total_faltante_gasto').val('0.00');
    $('#fecha_manual_gasto').val(fechaGasto);
    $('#agregarGasto').hide();
    $('#crearCapturaGasto').hide();
    $('#iniciarCapturaGasto').show();
    $('#cancelarCapturaGasto').hide();
    $('#input_anticipos_gasto').hide();
    $('#gasto_anticipo_disp_view').hide();
    $('#crearCapturaGastoDisabled').hide();
    $('#documento_referencia_gasto').val('');
}

function addRowGastosData(detalle, nit) {

    idGastoTable++;
    let data = {
        "id": idGastoTable,
        "id_concepto": detalle.id_concepto_gastos,
        "editar_iva": false,
        "valor_gasto": parseFloat(detalle.subtotal) - (parseFloat(detalle.aiu_valor) + parseFloat(detalle.iva_valor)),
        'porcentaje_aiu': porcentajeAIUGastos,
        'base_aiu': 0,
        "descuento_gasto": detalle.descuento_valor,
        "porcentaje_descuento_gasto": detalle.descuento_porcentaje,
        "valor_iva": detalle.iva_valor,
        "valor_reteica": detalle.rete_ica_valor,
        "no_valor_iva": detalle.iva_valor,
        "porcentaje_iva": detalle.iva_porcentaje,
        "valor_retencion": detalle.detalle,
        "porcentaje_retencion": detalle.rete_fuente_porcentaje,
        "porcentaje_reteica": detalle.rete_ica_porcentaje,
        "total_valor_gasto": detalle.total,
        'observacion': detalle.observacion,
    };
    
    //RETENCION
    if (!nit.declarante) {
        if (detalle.cuenta_retencion_declarante && detalle.cuenta_retencion_declarante.impuesto) {
            var existe = retencionesGasto.findIndex(item => item.id_retencion == detalle.cuenta_retencion_declarante.impuesto.id);
            if (!existe || existe < 0) {
                retencionesGasto.push({
                    cuenta: detalle.cuenta_retencion_declarante.cuenta,
                    nombre: detalle.cuenta_retencion_declarante.nombre,
                    id_retencion: detalle.cuenta_retencion_declarante.impuesto.id,
                    porcentaje: parseFloat(detalle.cuenta_retencion_declarante.impuesto.porcentaje),
                    base: parseFloat(detalle.cuenta_retencion_declarante.impuesto.base),
                });
            }
        }
    } else {
        if (detalle.cuenta_retencion && detalle.cuenta_retencion.impuesto) {
            var existe = retencionesGasto.findIndex(item => item.id_retencion == detalle.cuenta_retencion.impuesto.id);
            if (!existe || existe < 0) {
                retencionesGasto.push({
                    cuenta: detalle.cuenta_retencion.cuenta,
                    nombre: detalle.cuenta_retencion.nombre,
                    id_retencion: detalle.cuenta_retencion.impuesto.id,
                    porcentaje: parseFloat(detalle.cuenta_retencion.impuesto.porcentaje),
                    base: parseFloat(detalle.cuenta_retencion.impuesto.base),
                });
            }
        }
    }
    
    dataGasto.push(data);
    gasto_table.row.add(data).draw(false);
    document.getElementById("card-gasto").scrollLeft = 0;

    if (detalle.concepto) {
        var dataFormato = {
            id: detalle.concepto.id,
            text: detalle.concepto.codigo+' - '+detalle.concepto.nombre
        };
        var newOption = new Option(dataFormato.text, dataFormato.id, false, false);
        $("#combo_concepto_gasto_"+idGastoTable).append(newOption).trigger('change');
        $("#combo_concepto_gasto_"+idGastoTable).val(dataFormato.id).trigger('change');
    }

    setDisabledGastosRow(data, idGastoTable);
    // changeConceptoGasto(idGastoTable);
    mostrarValoresGastos();
}

$(document).on('keydown', '.custom-gasto_conceptogasto .select2-search__field', function (event) {

    if (guardandoGasto) {
        return;
    }

    var [gasto_iva, gasto_reteica, gasto_retencion, gasto_descuento, gasto_total, gasto_sub_total, gasto_aiu] = totalValoresGastos();
    var dataSearch = $('.select2-search__field').val();
    
    if (event.keyCode == 96 && !dataSearch.length) {
        abrirFormasPagoGastos = true;
    } else if (event.keyCode == 13){
        if (gasto_sub_total > 0) {
            if (abrirFormasPagoGastos) {
                abrirFormasPagoGastos = false;
                $(".combo_concepto_gasto").select2('close');
                ceroEnterGastos();
            }
        }
    } else {
        abrirFormasPagoGastos = false;
    }
});

function configurarAIU(porcentaje) {
    porcentajeAIUGastos = parseFloat(porcentaje);
    var columnAIU = gasto_table.column(3);//AIU
    columnAIU.visible(true);

    if (porcentajeAIUGastos) {
        $("#texto_gasto_aiu").html(`AIU %${porcentajeAIUGastos}: `);
        $("#gasto_aiu_disp_view").show();
    } else {
        $("#gasto_aiu_disp_view").hide();
    }
}

function validarDatosIva(idGasto) {
    if (!validandoDatosIva) {
        validandoDatosIva = true;
        var indexGasto = dataGasto.findIndex(item => item.id == idGasto);
        if (dataGasto[indexGasto].no_valor_iva == 0) {
            $('#gasto_no_iva_valor_1').val('')
        }
        setTimeout(function(){
            validandoDatosIva = false;
        },50);
    }
}

function buscarFacturaGasto(event) {
    var botonPrecionado = event.key.length == 1 ? event.key : '';
    var documento_referencia = $('#documento_referencia_gasto').val()+''+botonPrecionado;

    if (event.keyCode == 13 && documento_referencia) {
        document.getElementById('iniciarCapturaGasto').click();
        return;
    }

    if (validarFacturaGastos) {
        validarFacturaGastos.abort();
    }

    if (event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
    if (!documento_referencia) return;
    
    $('#documento_referencia_gasto_loading').show();
    
    $('#documento_referencia_gasto').removeClass("is-invalid");
    $('#documento_referencia_gasto').removeClass("is-valid");

    setTimeout(function(){
        validarFacturaGastos= $.ajax({
            url: base_url + 'existe-factura',
            method: 'GET',
            data: {
                id_comprobante: $("#id_comprobante_gasto").val(),
                documento_referencia: documento_referencia
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarFacturaGastos= null;
            $('#documento_referencia_gasto_loading').hide();
            if(res.data == 0){
                $('#documento_referencia_gasto').removeClass("is-invalid");
                $('#documento_referencia_gasto').addClass("is-valid");
            }else {
                $('#documento_referencia_gasto').removeClass("is-valid");
                $('#documento_referencia_gasto').addClass("is-invalid");
                $("#error_documento_referencia_gasto").text('La factura No '+documento_referencia+' ya existe!');
            }
        }).fail((err) => {
            $('#documento_referencia_gasto_loading').hide();
        });
    },100);
}

function enterConsecutivoGastos(event) {
    if (event.keyCode == 13) {
        document.getElementById('iniciarCapturaGasto').click();
        return;
    }
}