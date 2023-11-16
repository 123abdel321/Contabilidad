var fecha = null;
var nota_credito_table = null;
var nota_credito_table_pagos = null;
var nota_credito_table_facturas = null;

var $comboBodegaNotaCredito = null;
var $comboClienteNotaCredito = null;
var $comboResolucionNotaCredito = null;

function notacreditoInit () {
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_nota_credito').val(fecha);

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
                return `<input type="number" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoNotaCredito(${row.id}, ${anticipos})" onfocusout="calcularNotaCreditoPagos(${row.id}, ${anticipos})" onkeypress="changeFormaPagoNotaCredito(${row.id}, ${anticipos}, event)" id="nota_credito_forma_pago_${row.id}">`;
            }},
        ],
    });

    nota_credito_table_facturas = $('#facturaDevolucionTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
        deferLoading: 0,
        initialLoad: false,
        autoWidth: true,
        language: lenguajeDatatable,
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
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
                d.id_resolucion = $('#id_resolucion_nota_credito').val();
                d.consecutivo = $("#consecutivo_nota_credito").val();
            }
        },
        columns: [
            {"data":'id_nit'},
            {"data":'consecutivo'},
            {"data":'total_factura', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'id_centro_costos'},
            {"data":'fecha_manual'},
            {
                "data": function (row, type, set){
                    var html = `<span href="javascript:void(0)" class="btn badge bg-gradient-primary select-documento" style="margin-bottom: 0rem !important" onclick="seleccionarNotaCredito(${row})">Seleccionar</span>&nbsp;`;

                    return html;
                }
            }
        ]
    });

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

$(document).on('click', '#iniciarCapturaNotaCredito', function () {
    nota_credito_table_facturas.ajax.reload();
    $("#modalFacturasDevolucion").modal('show');
});

