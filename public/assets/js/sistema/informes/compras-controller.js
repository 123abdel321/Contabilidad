var fechaDesde = null;
var compras_table = null;

function comprasInit() {
    cargarFechasCompras();
    cargarTablasCompras();
    cargarCombosCompras();
    $('.water').hide();
}

/* ============================================================
   DATATABLE
   ============================================================ */
function cargarTablasCompras() {
    var newLenguaje = lenguajeDatatable;
    newLenguaje.sInfo = "Compras del _START_ al _END_ de un total de _TOTAL_ ";

    compras_table = $('#ComprasInformeTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: false,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: newLenguaje,
        scrollX: "100%",
        autoWidth: false,
        fixedColumns: {
            left: 0,
            right: 1,
        },

        rowCallback: function (row, data) {
            if (data.detalle === true) {
                $(row).addClass('compra-detalle');
            } else {
                $(row).addClass('compra-factura');
            }
            $('td', row).each(function () {
                var $td = $(this);
                var hasContent = $td.text().trim() !== ''
                    || $td.find('.btn, .button-user, i, span').length > 0;
                if (!hasContent) $td.addClass('td-empty');
            });
        },

        ajax: {
            type: "GET",
            headers: headers,
            url: base_url + 'compras',
            data: function (d) {
                var drp = $('#fecha_manual_compras').data('daterangepicker');
                d.id_proveedor    = $('#id_proveedor_compras').val();
                d.fecha_desde     = drp ? drp.startDate.format('YYYY-MM-DD HH:mm') : null;
                d.fecha_hasta     = drp ? drp.endDate.format('YYYY-MM-DD HH:mm')   : null;
                d.factura         = $('#factura_compras').val();
                d.consecutivo     = $('#consecutivo_compras').val();
                d.id_comprobante  = $('#id_comprobante_compras').val();
                d.id_bodega       = $('#id_bodega_compras').val();
                d.id_producto     = $('#id_producto_compras').val();
                d.id_usuario      = $('#id_usuario_compras').val();
                d.id_forma_pago   = $('#id_forma_pago_compras').val();
                d.detallar_compra = $('#detallar_compra1').is(':checked') ? 'si' : 'no';
            }
        },

        columns: [
            {"data": "documento_referencia", className: 'nowrap'},
            {"data": "nombre_completo"},
            {"data": "nombre_bodega", className: 'nowrap'},
            {"data": "fecha_manual", className: 'nowrap'},
            {"data": "descripcion"},
            {"data": "cantidad",  render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "costo",     render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "subtotal",  render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "iva_porcentaje", className: 'dt-body-right'},
            {"data": "total_iva", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "descuento_porcentaje", className: 'dt-body-right'},
            {"data": "total_descuento",  render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "total_rete_fuente", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "total_factura",     render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "nombre_usuario", className: 'nowrap'},

            {"data": function (row) {
                if (!row.fecha_creacion) return '';
                if (!row.created_by) {
                    return '<div class="button-user"><i class="fas fa-user-times icon-user-none"></i>&nbsp;' + row.fecha_creacion + '</div>';
                }
                return '<div class="button-user" onclick="showUser(' + row.created_by + ',\`' + row.fecha_creacion + '\`,0)"><i class="fas fa-user icon-user"></i>&nbsp;' + row.fecha_creacion + '</div>';
            }, className: 'cell-date'},

            {"data": function (row) {
                if (!row.fecha_edicion) return '';
                if (!row.updated_by) {
                    return '<div class="button-user"><i class="fas fa-user-times icon-user-none"></i>&nbsp;' + row.fecha_edicion + '</div>';
                }
                return '<div class="button-user" onclick="showUser(' + row.updated_by + ',\`' + row.fecha_edicion + '\`,0)"><i class="fas fa-user icon-user"></i>&nbsp;' + row.fecha_edicion + '</div>';
            }, className: 'cell-date'},

            {"data": function (row) {
                var html = '';
                if (row.id) {
                    html += '<span id="imprimircompra_' + row.id + '" class="btn badge bg-gradient-info imprimir-compra" style="margin-bottom:0;color:#fff;background-color:#fff !important;margin-right:3px;">PDF &nbsp;<i class="fas fa-print"></i></span>';

                    if (eliminarCompras && row.anulado != 1) {
                        html += '<span id="eliminarcompra_' + row.id + '" class="btn badge bg-gradient-danger eliminar-compra" style="margin-bottom:0;color:#fff;background-color:#fff !important;margin-right:3px;">ELIMINAR &nbsp;<i class="fa-solid fa-trash-can"></i></span>';
                        html += '<span id="eliminarcompraloading_' + row.id + '" class="btn badge bg-gradient-danger" style="margin-bottom:0;color:#fff;background-color:#fff !important;margin-right:3px;display:none;" disabled>ELIMINANDO &nbsp;<i class="fa fa-spinner fa-spin"></i></span>';
                    }

                    // Documento soporte: enviar / reenviar FE
                    if (row.es_documento_soporte && !row.fe_codigo_identificador) {
                        html += '<span id="enviarfecompra_' + row.id + '" class="btn badge bg-gradient-primary enviar-fe-compra" style="margin-bottom:0;color:#fff;background-color:#fff !important;margin-right:3px;">Enviar FE &nbsp;<i class="fas fa-share"></i></span>';
                        html += '<span id="enviarfecompraloading_' + row.id + '" class="btn badge bg-gradient-primary" style="margin-bottom:0;color:#fff;background-color:#fff !important;margin-right:3px;display:none;" disabled>Enviando &nbsp;<i class="fa fa-spinner fa-spin"></i></span>';
                    } else if (row.es_documento_soporte && row.fe_codigo_identificador) {
                        html += '<span id="reenviarfecompra_' + row.id + '" class="btn badge bg-gradient-success reenviar-fe-compra" style="margin-bottom:0;color:#fff;background-color:#fff !important;margin-right:3px;">REENVIAR FE &nbsp;<i class="fas fa-envelope"></i></span>';
                        html += '<span id="reenviarfecompraloading_' + row.id + '" class="btn badge bg-gradient-success" style="margin-bottom:0;color:#fff;background-color:#fff !important;margin-right:3px;display:none;" disabled>Reenviando &nbsp;<i class="fa fa-spinner fa-spin"></i></span>';
                    }
                }
                return html;
            }, className: 'cell-actions'}
        ]
    });

    compras_table.ajax.reload(function (res) {
        showTotalsCompras(res);
    });
}

/* ============================================================
   COMBOS SELECT2
   ============================================================ */
function cargarCombosCompras() {
    $('#id_proveedor_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un proveedor",
        allowClear: true,
        language: comboLanguageVentas,
        ajax: {
            url: 'api/nit/combo-nit',
            headers: headers,
            dataType: 'json',
            processResults: function (data) { return { results: data.data }; }
        }
    });

    $('#id_bodega_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una bodega",
        allowClear: true,
        language: comboLanguageVentas,
        ajax: {
            url: 'api/bodega/combo-bodega',
            headers: headers,
            dataType: 'json',
            processResults: function (data) { return { results: data.data }; }
        }
    });

    $('#id_comprobante_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        allowClear: true,
        language: comboLanguageVentas,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                return { q: params.term, tipo_comprobante: 2, _type: 'query' };
            },
            dataType: 'json',
            processResults: function (data) { return { results: data.data }; }
        }
    });

    $('#id_usuario_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un usuario",
        allowClear: true,
        language: comboLanguageVentas,
        ajax: {
            url: 'api/usuarios/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) { return { results: data.data }; }
        }
    });

    $('#id_producto_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un producto",
        allowClear: true,
        language: comboLanguageVentas,
        ajax: {
            url: 'api/producto/combo-producto',
            headers: headers,
            data: function (params) {
                return { q: params.term, captura: 'compra', _type: 'query' };
            },
            dataType: 'json',
            processResults: function (data) { return { results: data.data }; }
        }
    });

    $('#id_forma_pago_compras').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccionar forma de pago",
        allowClear: true,
        language: comboLanguageVentas,
        ajax: {
            url: 'api/forma-pago/combo-forma-pago',
            headers: headers,
            data: function (params) { return { q: params.term }; },
            dataType: 'json',
            processResults: function (data) { return { results: data.data }; }
        }
    });
}

/* Helper de lenguaje común para los select2 */
var comboLanguageVentas = {
    noResults: function () { return "No hay resultado"; },
    searching: function () { return "Buscando.."; },
    inputTooShort: function () { return "Por favor introduce 1 o más caracteres"; }
};

/* ============================================================
   FECHAS (daterangepicker igual que ventas)
   ============================================================ */
function cargarFechasCompras() {
    const start = moment().startOf("month");
    const end   = moment().endOf("month");

    $("#fecha_manual_compras").daterangepicker({
        startDate: start,
        endDate: end,
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
        locale: {
            format: "YYYY-MM-DD",
            separator: " - ",
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        },
        ranges: rangoFechas
    }, formatoFecha);

    formatoFecha(start, end, "fecha_manual_compras");
}

/* ============================================================
   ACCIONES: BUSCAR / DETALLAR
   ============================================================ */
$(document).on('click', '#generarCompras', function () {
    $("#generarCompras").hide();
    $("#generarComprasLoading").show();
    compras_table.ajax.reload(function (res) {
        showTotalsCompras(res);
        $("#generarCompras").show();
        $("#generarComprasLoading").hide();
    });
});

$('input[type=radio][name=detallar_compra]').change(function () {
    $("#generarCompras").hide();
    $("#generarComprasLoading").show();

    // Columnas que solo tienen sentido en modo detalle:
    // 4=Producto, 5=Cantidad, 6=Costo, 8=Iva%, 10=Desc%
    var showDetail = $('#detallar_compra1').is(':checked');
    [4, 5, 6, 8, 10].forEach(function (idx) {
        compras_table.column(idx).visible(showDetail);
    });

    compras_table.ajax.reload(function (res) {
        showTotalsCompras(res);
        $("#generarCompras").show();
        $("#generarComprasLoading").hide();
    });
});

/* ============================================================
   KPIs (4 cards)
   ============================================================ */
function showTotalsCompras(res) {
    if (!res || !res.success) return;
    if (!res.totalesCompra || !res.totalesCompra[0]) return;

    var t = res.totalesCompra[0];

    new CountUp('total_productos_comprados', 0, parseFloat(t.total_productos_cantidad) || 0).start();
    new CountUp('total_costo_compras',       0, parseFloat(t.total_costo)              || 0).start();
    new CountUp('total_iva_compras',         0, parseFloat(t.total_iva)                || 0).start();
    new CountUp('total_precio_compras',      0, parseFloat(t.total_venta)              || 0).start();
}

/* ============================================================
   BOTONES DE ACCIÓN POR FILA
   ============================================================ */

/* PDF */
$(document).on('click', '.imprimir-compra', function () {
    var id = this.id.split('_')[1];
    window.open("/compras-print/" + id, "_blank");
});

/* Eliminar */
$(document).on('click', '.eliminar-compra', function () {
    var id = this.id.split('_')[1];
    var data = getDataById(id, compras_table);

    Swal.fire({
        title: 'Eliminar compra',
        text: "¿Desea eliminar la compra " + data.documento_referencia + "?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        reverseButtons: true,
    }).then((result) => {
        if (result.value) {
            $("#eliminarcompra_" + id).hide();
            $("#eliminarcompraloading_" + id).show();
            $.ajax({
                url: base_url + 'compras',
                method: 'DELETE',
                data: JSON.stringify({ id_compra: id }),
                headers: headers,
                dataType: 'json',
            }).done(() => {
                compras_table.ajax.reload(function (res) {
                    showTotalsCompras(res);
                });
            }).fail((err) => {
                $("#eliminarcompra_" + id).show();
                $("#eliminarcompraloading_" + id).hide();
                agregarToast('error', 'Eliminar compra', arreglarMensajeError(err.responseJSON.message));
            });
        }
    });
});

/* Enviar documento soporte a la DIAN */
$(document).on('click', '.enviar-fe-compra', function () {
    var id = this.id.split('_')[1];
    $("#enviarfecompra_" + id).hide();
    $("#enviarfecompraloading_" + id).show();
    $.ajax({
        url: base_url + 'compras-fe',
        method: 'POST',
        data: JSON.stringify({ id_compra: id }),
        headers: headers,
        dataType: 'json',
    }).done(() => {
        compras_table.ajax.reload(function (res) {
            showTotalsCompras(res);
        });
    }).fail((err) => {
        $("#enviarfecompra_" + id).show();
        $("#enviarfecompraloading_" + id).hide();
        agregarToast('error', 'Envío documento soporte', arreglarMensajeError(err.responseJSON.message));
    });
});

/* Reenviar documento soporte a la DIAN */
$(document).on('click', '.reenviar-fe-compra', function () {
    var id = this.id.split('_')[1];
    $("#reenviarfecompra_" + id).hide();
    $("#reenviarfecompraloading_" + id).show();
    $.ajax({
        url: base_url + 'compras-fe',
        method: 'POST',
        data: JSON.stringify({ id_compra: id }),
        headers: headers,
        dataType: 'json',
    }).done(() => {
        compras_table.ajax.reload(function (res) {
            showTotalsCompras(res);
        });
    }).fail((err) => {
        $("#reenviarfecompra_" + id).show();
        $("#reenviarfecompraloading_" + id).hide();
        agregarToast('error', 'Reenvío documento soporte', arreglarMensajeError(err.responseJSON.message));
    });
});