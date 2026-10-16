var $comboComponenteSuscripcion = null;
var $comboOpcionSuscripcion = null;
var suscripcion_componentes_table = null;
var suscripcion_pagos_table = null;
var pagosDescuentos = [];
var pagosMesesActuales = 1;
var pagosDesgloseActual = null;
var pagosPendingId = null;
var pagosProcesando = false;

function suscripcionInit() {
    cargarTablasComponentes();
    cargarCombosComponentes();
}

function cargarTablasComponentes() {
    suscripcion_componentes_table = $('#componentesSuscripcionTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns: {
            left: 0,
            right: 1,
        },
        ajax: {
            type: "GET",
            headers: headers,
            url: base_url + 'suscripcion-componentes',
            data: function (d) {
                delete d.columns;
            },
            // Aprovechamos la respuesta para actualizar los 4 cards
            dataSrc: function (json) {
                actualizarCardsConsumo(json);
                return json.data;
            }
        },
        rowCallback: function (row, data, index) {
            if (data.id == 'TOTAL') {
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
            }
        },
        columns: [
            // 1. Componente
            {
                "data": function (row, type, set) {
                    if (row.id == 'TOTAL') return 'TOTAL';
                    if (row.componente) return row.componente.nombre;
                    return '';
                }
            },
            // 2. Precio mensual
            {
                "data": 'precio_mensual',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: 'dt-body-right'
            },
            // 3. Meses ciclo
            {
                "data": function (row) {
                    if (row.id == 'TOTAL') return '';
                    if (row.meses_ciclo == null) return '';
                    return row.meses_ciclo;
                },
                className: 'dt-body-center'
            },
            // 4. Total ciclo
            {
                "data": function (row) {
                    if (row.id == 'TOTAL') return row.precio_mensual;
                    return row.precio_total_ciclo;
                },
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: 'dt-body-right'
            },
            // 5. Cantidad
            {
                "data": function (row, type, set) {
                    return row.cantidad_calc || row.cantidad || 1;
                },
                render: $.fn.dataTable.render.number(',', '.', 0, ''),
                className: 'dt-body-right'
            },
            // 6. Inicio suscripción
            { "data": 'fecha_inicio_suscripcion' },
            // 7. Próximo pago
            { "data": 'fecha_siguiente_cobro' },
            // 8. Días
            {
                "data": function (row, type, set) {
                    if (row.id == 'TOTAL') return '';
                    if (row.dias_totales == null) return '';
                    return row.dias_transcurridos + ' / ' + row.dias_totales;
                },
                className: 'dt-body-center'
            },
            // 9. %
            {
                "data": function (row, type, set) {
                    if (row.id == 'TOTAL') return '';
                    if (row.porcentaje_consumido == null) return '';
                    return row.porcentaje_consumido + '%';
                },
                className: 'dt-body-center'
            },
            // 10. Valor al día
            {
                "data": 'valor_al_dia',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: 'dt-body-right'
            },
            // 11. Estado
            {
                "data": function (row) {
                    if (row.id == 'TOTAL') return '';
                    var pct = parseFloat(row.porcentaje_consumido) || 0;
                    if (pct >= 100) return '<span class="badge rounded-pill bg-danger">Vencido</span>';
                    if (pct >= 80)  return '<span class="badge rounded-pill bg-warning">Por vencer</span>';
                    return '<span class="badge rounded-pill bg-success">Activo</span>';
                },
                className: 'dt-body-center'
            },
            // 12. Acciones
            {
                "data": function (row, type, set) {
                    if (row.id == 'TOTAL') return;
                    var html = '';
                    if (true) html += `<span id="editcomponentesuscripcion_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-componente-suscripcion" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    if (true) html += `<span id="deletecomponentesuscripcion_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-componente-suscripcion" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    suscripcion_pagos_table = $('#pagosSuscripcionTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns: {
            left: 0,
            right: 1,
        },
        ajax: {
            type: "GET",
            headers: headers,
            url: base_url + 'suscripcion-pagos',
            data: function (d) {
                delete d.columns;
            }
        },
        columns: [
            {
                "data": 'created_at',
                render: function (data) {
                    if (!data) return '';
                    var d = new Date(data);
                    var dia = ("0" + d.getDate()).slice(-2);
                    var mes = ("0" + (d.getMonth() + 1)).slice(-2);
                    var anio = d.getFullYear();
                    return dia + '/' + mes + '/' + anio;
                }
            },
            { "data": 'usuario.username' },
            {
                "data": 'valor',
                render: $.fn.dataTable.render.number(',', '.', 2, '$ '),
                className: 'dt-body-right'
            },
            {
                "data": function (row) {
                    try {
                        var detalle = typeof row.detalle_json === 'string'
                            ? JSON.parse(row.detalle_json)
                            : (row.detalle_json || {});
                        return detalle.meses || '—';
                    } catch (e) {
                        return '—';
                    }
                },
                className: 'dt-body-center'
            },
            {
                "data": function (row) {
                    if (row.estado == 1) return '<span class="badge rounded-pill bg-success">Aprobado</span>';
                    if (row.estado == 2) return '<span class="badge rounded-pill bg-warning">Pendiente</span>';
                    if (row.estado == 0) return '<span class="badge rounded-pill bg-danger">Rechazado</span>';
                    return '';
                },
                className: 'dt-body-center'
            },
            {
                "data": function (row) {
                    return `<button type="button" class="btn badge bg-gradient-info ver-detalle-pago" data-id="${row.id}" style="margin-bottom: 0rem !important; min-width: 100px;">
                                <i class="fa-solid fa-eye"></i>&nbsp; Ver detalle
                            </button>`;
                },
                className: 'dt-body-center'
            },
        ],

    });

    if (suscripcion_componentes_table) {
        suscripcion_componentes_table.on('click', '.edit-componente-suscripcion', function () {
            $("#textComponentesCreate").hide();
            $("#textComponentesUpdate").show();
            $("#saveComponentesLoading").hide();
            $("#updateComponentes").show();
            $("#saveComponentes").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, suscripcion_componentes_table);

            $("#id_componentes_up").val(data.id);
            $("#precio_componente_suscripcion").val(data.precio);
            $("#descuento_componente_suscripcion").val(data.descuento || 0);
            $("#fecha_componente_suscripcion").val(data.fecha_siguiente_cobro);

            if (data.componente) {
                var padre = data.componente;

                // 🔑 Si el componente es un hijo, cargar el PADRE en el combo principal
                var idPadre = padre.id_padre;
                var idParaSeleccionar = idPadre ? idPadre : padre.id;
                var textoPadre = idPadre
                    ? (padre.nombre + ' (opción)')
                    : (padre.nombre + ' - ' + padre.precio);

                // Si es hijo, buscar el nombre del padre real
                if (idPadre && window.__componentesCache && window.__componentesCache[idPadre]) {
                    var padreReal = window.__componentesCache[idPadre];
                    textoPadre = padreReal.nombre + ' - ' + padreReal.precio;
                }

                var newOption = new Option(textoPadre, idParaSeleccionar, false, false);
                $comboComponenteSuscripcion.append(newOption).val(idParaSeleccionar).trigger('change');

                // Después del trigger, si es hijo, seleccionar la opción correspondiente
                if (idPadre) {
                    setTimeout(function () {
                        $comboOpcionSuscripcion.val(padre.id).trigger('change');
                    }, 100);
                }
            }

            recalcularTotalEstimado();
            $("#componentesSuscripcionFormModal").modal('show');
        });
    }

    suscripcion_pagos_table.ajax.reload();
    suscripcion_componentes_table.ajax.reload();
}

function actualizarCardsConsumo(json) {
    if (!json) return;
    $('#consumoTotalMensual').text(formatearMoneda(json.total_componentes || 0));
    $('#consumoTotalCiclo').text(formatearMoneda(json.total_ciclo || 0));
    $('#consumoTotalAlDia').text(formatearMoneda(json.total_al_dia || 0));
    $('#consumoTotalPendiente').text(formatearMoneda(json.total_pendiente || 0));
}

function formatearMoneda(valor) {
    var num = parseFloat(valor);
    if (isNaN(num)) num = 0;
    return '$ ' + num.toLocaleString('es-CO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function cargarCombosComponentes() {
    // Combo principal de componentes
    $comboComponenteSuscripcion = $('#id_componente_suscripcion').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#componentesSuscripcionFormModal'),
        language: {
            noResults: function () { return "No hay resultado"; },
            searching: function () { return "Buscando.."; },
            inputTooShort: function () { return "Por favor introduce 1 o más caracteres"; }
        },
        ajax: {
            url: 'api/suscripcion-combos',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                // 🔑 Guardar TODOS los datos (incluyendo hijos) en un cache
                window.__componentesCache = {};
                (data.data || []).forEach(function (item) {
                    window.__componentesCache[item.id] = item;
                });

                // 🔑 El select2 solo muestra el `text`
                var results = (data.data || []).map(function (item) {
                    return {
                        id: item.id,
                        text: item.text,
                        data: item,   // guardamos todo el objeto
                    };
                });

                return { results: results };
            }
        }
    });

    // Combo de opciones (hijos)
    $comboOpcionSuscripcion = $('#id_opcion_suscripcion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#componentesSuscripcionFormModal'),
        placeholder: 'Selecciona una opción',
        language: {
            noResults: function () { return "No hay resultado"; },
        },
    });
}

function clearFormComponentesSuscripcion() {
    const dateNowSuscripcion = new Date();
    $("#saveComponentes").show();
    $("#updateComponentes").hide();
    $("#saveComponentesLoading").hide();

    $("#textComponentesUpdate").hide();
    $("#textComponentesCreate").show();

    $("#id_componente_suscripcion").val("").trigger('change');
    $("#id_opcion_suscripcion").val(null).trigger('change');
    $("#grupoOpcion").hide();
    $("#cantidad_componente_suscripcion").val(1);
    $("#grupoCantidad").hide();
    $("#rangoCantidad").text('');
    $("#precio_componente_suscripcion").val(0);
    $("#descuento_componente_suscripcion").val(0);
    $("#total_estimado_componente_suscripcion").val('$ 0');
    $("#fecha_componente_suscripcion").val(
        dateNowSuscripcion.getFullYear() + '-' +
        ("0" + (dateNowSuscripcion.getMonth() + 1)).slice(-2) + '-' +
        ("0" + dateNowSuscripcion.getDate()).slice(-2)
    );

    if (esDios) {
        $("#precio_componente_suscripcion").prop('disabled', false);
        $("#descuento_componente_suscripcion").prop('disabled', false);
    } else {
        $("#precio_componente_suscripcion").prop('disabled', true);
        $("#descuento_componente_suscripcion").prop('disabled', true);
    }
}

function guardarComponenteSuscripcion() {
    const form = document.querySelector('#componentesForm');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        const firstInvalidInput = form.querySelector(':invalid');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
        }
        return;
    }

    $("#saveComponentes").hide();
    $("#updateComponentes").hide();
    $("#saveComponentesLoading").show();

    let data = getDataComponentes();

    $.ajax({
        url: base_url + 'suscripcion-componentes',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            clearFormComponentesSuscripcion();
            $("#saveComponentes").show();
            $("#saveComponentesLoading").hide();
            $("#componentesSuscripcionFormModal").modal('hide');
            suscripcion_componentes_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Componente agregado con exito!', true);
        }
    }).fail((err) => {
        $('#saveComponentes').show();
        $('#saveComponentesLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getDataComponentes() {
    var idComponente = $("#id_componente_suscripcion").val();

    // 🔑 Si hay una opción seleccionada (hijo), enviar el id del hijo
    var idOpcion = $("#id_opcion_suscripcion").val();
    if (idOpcion) {
        idComponente = idOpcion;
    }

    return {
        id: $("#id_componentes_up").val(),
        id_componente: idComponente,
        cantidad: $("#cantidad_componente_suscripcion").val(),
        precio: $("#precio_componente_suscripcion").val(),
        fecha_inicio: $("#fecha_componente_suscripcion").val(),
        descuento: $("#descuento_componente_suscripcion").val(),
    }
}

// ============================================================
// EVENTOS
// ============================================================

$(document).on('click', '#componentesSuscripcionCreate', function () {
    clearFormComponentesSuscripcion();
    $("#componentesSuscripcionFormModal").modal('show');
});

$(document).on('click', '#saveComponentes', function () {
    guardarComponenteSuscripcion();
});

// Antes estaba en el blade de pagos, ahora queda en componentes:
$(document).on('click', '#refrescarConsumoActual', function () {
    suscripcion_componentes_table.ajax.reload();
});

$("#id_componente_suscripcion").on('change', function (event) {
    let data = $(this).select2('data');
    if (data.length <= 0) return;

    data = data[0];
    var item = data.data || null;

    if (!item) return;

    // Resetear campos
    $("#grupoOpcion").hide();
    $("#grupoCantidad").hide();
    $("#rangoCantidad").text('');
    $("#precio_componente_suscripcion").val(0);
    $("#descuento_componente_suscripcion").val(0);
    $("#id_opcion_suscripcion").val(null).trigger('change');

    if (parseInt(item.tipo) === 1) {
        // ═══════════════════════════════════════════════════
        // 🔑 COMPONENTE TIPO 1 → Mostrar selector de opciones (hijos)
        // ═══════════════════════════════════════════════════
        $("#grupoOpcion").show();

        // Cargar los hijos en el combo de opciones
        var opciones = item.hijos || [];

        $comboOpcionSuscripcion.empty().append('<option></option>');
        opciones.forEach(function (hijo) {
            var precioFormateado = formatearMoneda(hijo.precio);
            var textoOpcion = hijo.text + ' — ' + precioFormateado;

            var opt = new Option(textoOpcion, hijo.id, false, false);
            $(opt).data('precio', hijo.precio);
            $(opt).data('rango_desde', hijo.rango_desde);
            $(opt).data('rango_hasta', hijo.rango_hasta);
            $comboOpcionSuscripcion.append(opt);
        });
        $comboOpcionSuscripcion.val(null).trigger('change');
    } else {
        // ═══════════════════════════════════════════════════
        // COMPONENTE TIPO 0 → Precio fijo directo
        // ═══════════════════════════════════════════════════
        $("#precio_componente_suscripcion").val(item.precio);
        $("#grupoOpcion").hide();
    }

    recalcularTotalEstimado();
});

$("#id_opcion_suscripcion").on('change', function () {
    let data = $(this).select2('data');
    if (data.length <= 0) {
        $("#precio_componente_suscripcion").val(0);
        recalcularTotalEstimado();
        return;
    }

    data = data[0];
    var idOpcion = data.id;

    // Buscar el hijo en el cache para obtener su precio
    var itemPadre = window.__componentesCache ? Object.values(window.__componentesCache).find(function (p) {
        return p.hijos && p.hijos.some(function (h) { return h.id == idOpcion; });
    }) : null;

    if (itemPadre) {
        var hijo = itemPadre.hijos.find(function (h) { return h.id == idOpcion; });
        if (hijo) {
            $("#precio_componente_suscripcion").val(hijo.precio);
        }
    }

    recalcularTotalEstimado();
});

$(document).on('input', '#cantidad_componente_suscripcion', recalcularTotalEstimado);
$(document).on('input', '#precio_componente_suscripcion', recalcularTotalEstimado);
$(document).on('input', '#descuento_componente_suscripcion', recalcularTotalEstimado);

function recalcularTotalEstimado() {
    let precio    = parseFloat($("#precio_componente_suscripcion").val()) || 0;
    let descuento = parseFloat($("#descuento_componente_suscripcion").val()) || 0;
    let cantidad  = parseInt($("#cantidad_componente_suscripcion").val()) || 1;

    let total = (precio - descuento) * cantidad;
    if (total < 0) total = 0;

    $("#total_estimado_componente_suscripcion").val(formatearMoneda(total));
}

$('button[data-bs-target="#pagos_suscripcion"]').on('shown.bs.tab', function () {
    $('#cards-componentes').hide();
    $('#cards-pagos').show();

    if (suscripcion_pagos_table) {
        suscripcion_pagos_table.columns.adjust().draw(false);
    }

    cargarResumenPagos();
});

$('button[data-bs-target="#componentes_suscripcion"]').on('shown.bs.tab', function () {
    $('#cards-componentes').show();
    $('#cards-pagos').hide();

    if (suscripcion_componentes_table) {
        suscripcion_componentes_table.columns.adjust().draw(false);
    }

    cargarResumenPagos();
});

$('button[data-bs-target="#componentes_suscripcion"]').on('shown.bs.tab', function () {
    $('#cards-pagos').hide();
    $('#cards-componentes').show();

    if (suscripcion_componentes_table) {
        suscripcion_componentes_table.columns.adjust().draw(false);
    }
});

// ============================================================
// PAGOS — Resumen cards
// ============================================================

function cargarResumenPagos() {
    $.ajax({
        url: base_url + 'suscripcion-pagos-resumen',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) return;

        var d = res.data;

        // 1. Estado suscripción
        var estadoHtml = '';
        if (d.estado_suscripcion === 'activa') {
            estadoHtml = '<span class="badge rounded-pill bg-success">Activa</span>';
        } else if (d.estado_suscripcion === 'por_vencer') {
            estadoHtml = '<span class="badge rounded-pill bg-warning">Por vencer</span>';
        } else if (d.estado_suscripcion === 'vencida') {
            estadoHtml = '<span class="badge rounded-pill bg-danger">Vencida</span>';
        } else {
            estadoHtml = '<span class="badge rounded-pill bg-secondary">Sin suscripción</span>';
        }
        $('#pagosEstadoSuscripcion').html(estadoHtml);

        // 2. Próximo cobro
        $('#pagosProximoCobro').text(formatearMoneda(d.monto_siguiente_pago || 0));
        $('#pagosProximoCobroFecha').text(d.fecha_siguiente_pago ? 'Fecha: ' + d.fecha_siguiente_pago : '');

        // 3. Último pago
        if (d.ultimo_pago) {
            $('#pagosUltimoPago').text(formatearMoneda(d.ultimo_pago.valor || 0));
            $('#pagosUltimoPagoFecha').text('Fecha: ' + (d.ultimo_pago.fecha || ''));
        } else {
            $('#pagosUltimoPago').text('$ 0');
            $('#pagosUltimoPagoFecha').text('Sin pagos');
        }

        // 4. Total pagado año
        $('#pagosTotalAnio').text(formatearMoneda(d.total_pagado_anio || 0));
    });
}

// ============================================================
// PAGOS — Botón "Pagar ahora"
// ============================================================

$(document).on('click', '#pagosSuscripcionCreate', function () {
    pagosMesesActuales = 1;
    $('#pagosFormMeses').val(1);

    // Cargar descuentos disponibles
    $.ajax({
        url: base_url + 'suscripcion/wompi/descuentos',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done(function (res) {
        if (res.success) {
            pagosDescuentos = res.data || [];
            renderizarDescuentosRapidos();
        }
    });

    // Calcular preview inicial
    calcularPreviewPago();

    $('#pagosSuscripcionFormModal').modal('show');
});

// ============================================================
// PAGOS — Refrescar
// ============================================================

$(document).on('click', '#refrescarPagosSuscripcion', function () {
    if (suscripcion_pagos_table) {
        suscripcion_pagos_table.ajax.reload();
    }
    cargarResumenPagos();
});

// ============================================================
// PAGOS — Ver detalle
// ============================================================

$(document).on('click', '.ver-detalle-pago', function () {
    var id = $(this).data('id');
    var row = getDataById(id, suscripcion_pagos_table);

    if (!row) return;

    // Encabezado
    var fecha = row.created_at ? new Date(row.created_at) : null;
    var fechaStr = fecha
        ? ("0" + fecha.getDate()).slice(-2) + '/' +
          ("0" + (fecha.getMonth() + 1)).slice(-2) + '/' +
          fecha.getFullYear()
        : '—';

    $('#detallePagoFecha').text(fechaStr);
    $('#detallePagoUsuario').text(row.usuario ? row.usuario.username : '—');

    // Parsear detalle_json
    var detalle = {};
    try {
        detalle = typeof row.detalle_json === 'string'
            ? JSON.parse(row.detalle_json)
            : (row.detalle_json || {});
    } catch (e) {
        detalle = {};
    }

    $('#detallePagoMetodo').text(detalle.metodo_pago || '—');
    $('#detallePagoReferencia').text(detalle.wompi_transaction_id || '—');

    // Estado
    var estadoHtml = '';
    if (row.estado == 1) estadoHtml = '<span class="badge rounded-pill bg-success">Aprobado</span>';
    else if (row.estado == 2) estadoHtml = '<span class="badge rounded-pill bg-warning">Pendiente</span>';
    else if (row.estado == 0) estadoHtml = '<span class="badge rounded-pill bg-danger">Rechazado</span>';
    $('#detallePagoEstado').html(estadoHtml);

    // Tabla de componentes
    var html = '';
    var total = 0;

    if (detalle.componentes && detalle.componentes.length > 0) {
        detalle.componentes.forEach(function (c) {
            var precio = parseFloat(c.precio) || 0;
            var cant = parseInt(c.cantidad) || 1;
            var subtotal = precio * cant;
            total += subtotal;

            html += '<tr>' +
                '<td>' + (c.nombre || '—') + '</td>' +
                '<td class="text-end">' + cant + '</td>' +
                '<td class="text-end">' + formatearMoneda(precio) + '</td>' +
                '<td class="text-end">' + formatearMoneda(subtotal) + '</td>' +
            '</tr>';
        });
    } else {
        html = '<tr><td colspan="4" class="text-center text-muted">Sin componentes</td></tr>';
    }

    $('#detallePagoComponentes').html(html);

    // Total: usar el valor del pago si existe, sino la suma
    var totalFinal = parseFloat(row.valor) || total;
    $('#detallePagoTotal').text(formatearMoneda(totalFinal));

    $('#pagoDetalleModal').modal('show');
});

// ============================================================
// WOMPI - Pago con tarjeta
// ============================================================

var wompiAcceptanceToken = null;

$(document).on('click', '#pagosFormPagarBtn', function () {
    if (!pagosDesgloseActual || pagosDesgloseActual.total <= 0) {
        agregarToast('error', 'Error', 'Monto inválido');
        return;
    }

    var btn = $(this);
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

    $('#pagosSuscripcionFormModal').modal('hide');

    setTimeout(function () {
        btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
        abrirWidgetWompi(btn);
    }, 400);
});

function abrirWidgetWompi(btn) {
    if (pagosProcesando) {
        agregarToast('info', 'En proceso', 'Ya hay un pago en proceso.');
        return;
    }

    if (typeof WidgetCheckout === 'undefined') {
        btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
        agregarToast('error', 'Error', 'El widget de Wompi no está cargado.');
        return;
    }

    var montoCents = Math.round(pagosDesgloseActual.total * 100);

    if (montoCents <= 0) {
        btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
        agregarToast('error', 'Error', 'Monto inválido');
        return;
    }

    pagosProcesando = true;

    $.ajax({
        url: base_url + 'suscripcion/wompi/crear-pago-pendiente',
        method: 'POST',
        data: JSON.stringify({
            meses: pagosMesesActuales,
        }),
        headers: headers,
        dataType: 'json',
    }).done(function (resPendiente) {
        if (!resPendiente.success) {
            pagosProcesando = false;
            btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
            agregarToast('error', 'Error', resPendiente.message || 'No se pudo crear el pago pendiente');
            return;
        }

        pagosPendingId = resPendiente.pago_id;
        var referencia = resPendiente.reference;

        $.ajax({
            url: base_url + 'suscripcion/wompi/firma-integridad',
            method: 'POST',
            data: JSON.stringify({
                reference: referencia,
                amount_in_cents: montoCents,
            }),
            headers: headers,
            dataType: 'json',
        }).done(function (resFirma) {
            if (!resFirma.success) {
                pagosProcesando = false;
                btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
                agregarToast('error', 'Error', resFirma.message || 'No se pudo generar la firma');
                return;
            }

            try {
                var checkout = new WidgetCheckout({
                    currency: 'COP',
                    amountInCents: montoCents,
                    reference: referencia,
                    publicKey: 'pub_test_OotfUWWYb4mROOAPONcTs2Uh2CAJQ1ZK',
                    signature: { integrity: resFirma.signature },
                });

                checkout.open(function (result) {
                    var transaction = result.transaction;
                    console.log('transaction: ', transaction);

                    if (transaction.status === 'APPROVED') {
                        reconciliarPago(transaction, referencia, btn);
                    } else {
                        pagosProcesando = false;
                        btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
                        agregarToast('error', 'Pago rechazado', 'La transacción no fue aprobada.');
                        $('#pagosSuscripcionFormModal').modal('show');
                    }
                });
            } catch (e) {
                pagosProcesando = false;
                btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
                agregarToast('error', 'Error', 'No se pudo abrir el widget: ' + e.message);
            }
        }).fail(function () {
            pagosProcesando = false;
            btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
            agregarToast('error', 'Error', 'Error de conexión al generar la firma');
        });
    }).fail(function () {
        pagosProcesando = false;
        btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
        agregarToast('error', 'Error', 'Error de conexión al crear el pago pendiente');
    });
}

// ============================================================
// PAGOS — Preview y selector de meses
// ============================================================

function renderizarDescuentosRapidos() {
    var html = '<small class="text-muted d-block mb-1">Promociones:</small>';
    pagosDescuentos.forEach(function (d) {
        if (d.meses <= 1) return;
        html += `<button type="button" class="btn btn-sm btn-outline-info me-1 mb-1 btn-descuento-rapido" data-meses="${d.meses}">
                    ${d.meses} meses · ${d.descuento_porcentaje}% off
                </button>`;
    });
    $('#pagosFormDescuentosRapidos').html(html);
}

$(document).on('click', '.btn-descuento-rapido', function () {
    var meses = parseInt($(this).data('meses'));
    pagosMesesActuales = meses;
    $('#pagosFormMeses').val(meses);
    calcularPreviewPago();
});

$(document).on('click', '#btnSumarMes', function () {
    pagosMesesActuales = Math.min(24, pagosMesesActuales + 1);
    $('#pagosFormMeses').val(pagosMesesActuales);
    calcularPreviewPago();
});

$(document).on('click', '#btnRestarMes', function () {
    pagosMesesActuales = Math.max(1, pagosMesesActuales - 1);
    $('#pagosFormMeses').val(pagosMesesActuales);
    calcularPreviewPago();
});

$(document).on('change', '#pagosFormMeses', function () {
    var v = parseInt($(this).val()) || 1;
    v = Math.max(1, Math.min(24, v));
    pagosMesesActuales = v;
    $(this).val(v);
    calcularPreviewPago();
});

function calcularPreviewPago() {
    $('#pagosFormDesglose').html('<div class="text-center text-muted small"><i class="fas fa-spinner fa-spin"></i> Calculando...</div>');

    $.ajax({
        url: base_url + 'suscripcion/wompi/preview-pago',
        method: 'POST',
        data: JSON.stringify({ meses: pagosMesesActuales }),
        headers: headers,
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            $('#pagosFormDesglose').html('<div class="text-danger small">' + (res.message || 'Error') + '</div>');
            return;
        }

        var d = res.data;
        pagosDesgloseActual = d;

        // Total y ahorro
        $('#pagosFormMonto').text(formatearMoneda(d.total));
        if (d.descuento > 0) {
            $('#pagosFormAhorro').html('<i class="fas fa-gift"></i> Ahorras ' + formatearMoneda(d.descuento) + ' (' + d.porcentaje + '%)');
        } else {
            $('#pagosFormAhorro').text('');
        }

        // Desglose
        var html = '';
        d.detalle_meses.forEach(function (item) {
            if (item.monto > 0) {
                html += '<div class="d-flex justify-content-between small mb-1">' +
                    '<span>' + item.label + '</span>' +
                    '<span>' + formatearMoneda(item.monto) + '</span>' +
                '</div>';
            }
        });

        html += '<hr class="my-2">';
        html += '<div class="d-flex justify-content-between small mb-1"><span>Subtotal</span><span>' + formatearMoneda(d.subtotal) + '</span></div>';

        if (d.descuento > 0) {
            html += '<div class="d-flex justify-content-between small mb-1 text-success"><span>Descuento ' + d.porcentaje + '%</span><span>− ' + formatearMoneda(d.descuento) + '</span></div>';
        }

        html += '<hr class="my-2">';
        html += '<div class="d-flex justify-content-between font-weight-bold"><span>TOTAL</span><span>' + formatearMoneda(d.total) + '</span></div>';

        $('#pagosFormDesglose').html(html);
    }).fail(function () {
        $('#pagosFormDesglose').html('<div class="text-danger small">Error de conexión</div>');
    });
}

function reconciliarPago(transaction, referencia, btn) {
    if (!pagosPendingId) {
        pagosProcesando = false;
        agregarToast('error', 'Error', 'No hay pago pendiente para reconciliar');
        return;
    }

    $.ajax({
        url: base_url + 'suscripcion/wompi/reconciliar',
        method: 'POST',
        data: JSON.stringify({
            pago_id:           pagosPendingId,
            transaction_id:    transaction.id,
            reference:         referencia,
            payment_source_id: transaction.payment_source_id || null,
        }),
        headers: headers,
        dataType: 'json',
    }).done(function (res) {
        pagosProcesando = false;
        btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');

        if (res.success) {
            if (res.estado === 1) {
                agregarToast('exito', 'Pago exitoso', 'Tu suscripción ha sido activada.');
            } else if (res.estado === 0) {
                agregarToast('error', 'Pago rechazado', 'La transacción fue rechazada.');
            } else {
                agregarToast('info', 'Pago pendiente', 'La transacción está siendo procesada.');
            }

            suscripcion_pagos_table.ajax.reload();
            cargarResumenPagos();
        } else {
            agregarToast('error', 'Error', res.message || 'No se pudo reconciliar el pago');
        }
    }).fail(function (err) {
        pagosProcesando = false;
        btn.prop('disabled', false).html('<i class="fa-solid fa-credit-card"></i>&nbsp; Pagar con tarjeta');
        var msg = err.responseJSON ? err.responseJSON.message : 'Error de conexión';
        agregarToast('error', 'Error', msg);
    });
}