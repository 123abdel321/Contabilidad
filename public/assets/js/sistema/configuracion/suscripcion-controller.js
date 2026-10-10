var suscripcion_componentes_table = null;
var suscripcion_pagos_table = null;
var $comboComponenteSuscripcion = null;

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
                d.fecha_desde = $('#fecha_desde_estado_cuenta_pagos').val();
                d.fecha_hasta = $('#fecha_hasta_estado_cuenta_pagos').val();
                d.estado = $('#estado_estado_cuenta_pagos').val();
            }
        },
        columns: [
            { "data": 'usuario.username' },
            { "data": 'detalle_json' },
            { "data": 'valor', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {
                "data": function (row, type, set) {
                    if (row.estado == 1) return '<span class="badge rounded-pill bg-success">Confirmado</span>';
                    if (row.estado == 2) return '<span class="badge rounded-pill bg-warning">Pendiente</span>';
                    if (row.estado == 0) return '<span class="badge rounded-pill bg-danger">Rechazado</span>';
                    return;
                }
            },
            { "data": 'updated_at' },
        ]
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
                var dataComponente = {
                    id: data.componente.id,
                    text: data.componente.nombre + ' - ' + data.componente.precio
                };
                var newOption = new Option(dataComponente.text, dataComponente.id, false, false);
                $comboComponenteSuscripcion.append(newOption).val(dataComponente.id).trigger('change');

                // Mostrar cantidad si es tipo 1
                if (parseInt(data.componente.tipo) === 1) {
                    $("#grupoCantidad").show();
                    $("#cantidad_componente_suscripcion").val(data.cantidad || 1);

                    let min = parseInt(data.componente.rango_desde) || 1;
                    let max = parseInt(data.componente.rango_hasta) || 999999;
                    $("#cantidad_componente_suscripcion").attr('min', min).attr('max', max);
                    $("#rangoCantidad").text('Rango permitido: ' + min + ' - ' + max);
                } else {
                    $("#grupoCantidad").hide();
                    $("#cantidad_componente_suscripcion").val(1);
                    $("#rangoCantidad").text('');
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
    $comboComponenteSuscripcion = $('#id_componente_suscripcion').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#componentesSuscripcionFormModal'),
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
            url: 'api/suscripcion-combos',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
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
    return {
        id: $("#id_componentes_up").val(),
        id_componente: $("#id_componente_suscripcion").val(),
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
    $("#precio_componente_suscripcion").val(data.precio);

    if (parseInt(data.tipo) === 1) {
        // Tipo cantidad (ej: Maximo PH)
        $("#grupoCantidad").show();

        let min = parseInt(data.rango_desde) || 1;
        let max = parseInt(data.rango_hasta) || 999999;

        $("#cantidad_componente_suscripcion")
            .attr('min', min)
            .attr('max', max);

        // Si el valor actual está fuera de rango, ajustar al mínimo
        let actual = parseInt($("#cantidad_componente_suscripcion").val()) || 0;
        if (actual < min) {
            $("#cantidad_componente_suscripcion").val(min);
        }

        $("#rangoCantidad").text('Rango permitido: ' + min + ' - ' + max);
    } else {
        // Tipo bandera
        $("#grupoCantidad").hide();
        $("#cantidad_componente_suscripcion").val(1);
        $("#rangoCantidad").text('');
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
    if (suscripcion_pagos_table) {
        suscripcion_pagos_table.columns.adjust().draw(false);
    }
});
$('button[data-bs-target="#componentes_suscripcion"]').on('shown.bs.tab', function () {
    if (suscripcion_componentes_table) {
        suscripcion_componentes_table.columns.adjust().draw(false);
    }
});