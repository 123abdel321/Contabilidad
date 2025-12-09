var pagar_nomina_table = null;
var id_periodo_nomina_pagar = null;
var pagar_detalle_nomina_table = null;

function pagosInit () {
    initTablesPagos();
    initCombosPagos();
    initFechasPagos();
}

function initCombosPagos() {
    $(`#meses_pagar_nomina_filter`).select2({
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
            url: 'api/causar-meses-combo',
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

function initTablesPagos() {
    pagar_nomina_table = $('#pagosNominaTable').DataTable({
        pageLength: 100,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: {
            ...lenguajeDatatable,
            info: "",
            infoEmpty: "",
            infoFiltered: "",
        },
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'pagar-periodos-pago',
            data: function ( d ) {
                d.meses = $('#meses_pagar_nomina_filter').val();
            }
        },
        columns: [
            {"data":'fecha_periodo'},
            {
                "data": function (row, type, set){
                    if (row.empleado) {
                        return row.empleado.numero_documento
                    }
                    return '';
                }, className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    if (row.empleado) {
                        return row.empleado.nombre_completo
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if (row.estado == 1) {
                        return '<span class="badge rounded-pill bg-info">CAUSADO</span>';
                    }
                    if (row.estado == 2) {
                        return '<span class="badge rounded-pill bg-success">PAGADO</span>';
                    }
                    return '<span class="badge rounded-pill bg-dark">PENDIENTE</span>';
                }
            },
            {"data":'fecha_inicio_periodo_formatted'},
            {"data":'fecha_fin_periodo_formatted'},
            {
                "data": function (row, type, set){
                    if (row.sum_detalles) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.sum_detalles.devengados);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    if (row.sum_detalles) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.sum_detalles.deducciones ? row.sum_detalles.deducciones * -1 : 0);
                        const parts = formatted.split('.');
                        return `<b style="color: red; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: red; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    if (row.sum_detalles) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.sum_detalles.neto);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    var botonPagar = ``;
                    var botonDespagar = ``;
                    if (row.estado == 1 && crearPago) {
                        botonPagar=`
                        <span id="pagarnomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-primary pagar-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Pagar</span>
                        <span id="pagandonomina_${row.id}" class="badge bg-gradient-primary" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                            <b style="opacity: 0.3; text-transform: math-auto;">Pagar</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -23px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                        `;
                    }
                    var botonDetalle = `
                        <span id="detallepagarnomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success detalle-pagar-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>
                        <span id="detallandopagarnomina_${row.id}" class="badge bg-gradient-success" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                            <b style="opacity: 0.3; text-transform: math-auto;">Ver detalle</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -35px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                    `;
                    return `
                        ${botonPagar}
                        ${botonDespagar}
                        ${botonDetalle}
                    `;
                }
            },
        ],
        rowGroup: {
            dataSrc: 'fecha_periodo',
            startRender: null // Desactiva la fila de grupo automática
        },
        columnDefs: [
            { targets: 0, visible: false } // Oculta la columna del "Empleado"
        ],
        drawCallback: function () {
            const api = this.api();
            const rows = api.rows({ page: 'current' }).nodes();
            const data = api.rows({ page: 'current' }).data();

            let lastGroup = null;
            let groupTotals = { devengado: 0, deduccion: 0, neto: 0 };
            let $lastGroupRow = null;

            $('.group-header-pagar, .group-footer-pagar').remove();

            data.each(function (row, i) {
                const groupKey = `${row.fecha_inicio_periodo_formatted} - ${row.fecha_fin_periodo_formatted}`;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-pagar" style="background-color: white; font-weight: bold;">
                                <td colspan="5" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.devengado)}</b></td>
                                <td class="text-end"><b style="color: red;">${formatNumberWithSmallDecimals(groupTotals.deduccion * -1)}</b></td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.neto)}</b></td>
                                <td></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    // Recopilar TODOS los IDs del periodo para este grupo
                    const groupPeriodIds = [];
                    data.each(function (innerRow, j) {
                        const innerGroupKey = `${innerRow.fecha_inicio_periodo_formatted} - ${innerRow.fecha_fin_periodo_formatted}`;
                        if (innerGroupKey === groupKey && innerRow.sum_detalles && innerRow.sum_detalles.id_periodo_pago) {
                            groupPeriodIds.push(innerRow.sum_detalles.id_periodo_pago);
                        }
                    });

                    // Eliminar duplicados (por si acaso)
                    const uniquePeriodIds = [...new Set(groupPeriodIds)];

                    const groupRow = $(`
                        <tr class="group-header-pagar" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
                            <td colspan="9">
                                <div style="display: flex; align-items: center;">
                                    
                                    <div style="display: flex; align-items: center; white-space: nowrap;">
                                        <i class="fas fa-minus-square toggle-icon" style="margin-right: 5px; color: #003883;"></i>
                                        <b style="font-weight: 500; font-size: 14px;">Periodo desde <b>${row.fecha_inicio_periodo_formatted}</b> hasta <b>${row.fecha_fin_periodo_formatted}</b></b>
                                    </div>

                                    <div style="margin-left: 25px; white-space: nowrap;">
                                        <label class="form-check-label" style="font-size: 14px; font-weight: 500; color: #003883; cursor: pointer; display: flex; align-items: center;">
                                            <input class="form-check-input check-pagar-periodo" type="checkbox" data-periodo-ids="${uniquePeriodIds.join(',')}" style="margin-right: 5px; margin-top: 0;">
                                            Pagar Periodo (${uniquePeriodIds.length} empleados)
                                        </label>
                                    </div>

                                </div>
                            </td>
                        </tr>
                    `);
                    
                    $row.before(groupRow);

                    lastGroup = groupKey;
                    groupTotals = { devengado: 0, deduccion: 0, neto: 0 };
                }

                // Sumar los valores
                groupTotals.devengado += parseFloat(row.sum_detalles?.devengados ?? 0);
                groupTotals.deduccion += parseFloat(row.sum_detalles?.deducciones ?? 0);
                groupTotals.neto += parseFloat(row.sum_detalles?.neto ?? 0);

                // Guardar referencia a la última fila del grupo
                $lastGroupRow = $row;
            });

            // Agregar footer del último grupo
            if (lastGroup !== null && $lastGroupRow) {
                const footerRow = $(`
                    <tr class="group-footer-pagar" style="background-color: white; font-weight: bold;">
                        <td colspan="5" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.devengado)}</b></td>
                        <td class="text-end"><b style="color: red;">${formatNumberWithSmallDecimals(groupTotals.deduccion * -1)}</b></td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.neto)}</b></td>
                        <td></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-pagar').off('click').on('click', function (e) {
                const target = $(e.target);
                if (target.hasClass('form-check-input') || target.closest('.form-check-label').length) {
                    return; 
                }

                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-pagar')) {
                    if (!$next.hasClass('group-footer-pagar')) $next.toggle();
                    $next = $next.next();
                }

                $icon.toggleClass('fa-minus-square fa-plus-square');
            });

            setTimeout(function() {
                updatePagarNominaButton();
            }, 100);
        }
    });

    pagar_detalle_nomina_table = $('#periodoPagoDetallePagarTable').DataTable({
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
            url: base_url + 'detalle-periodos-pago',
            data: function ( d ) {
                d.id_periodo_pago = id_periodo_nomina_pagar;
            }
        },
        columns: [
            {"data": function (row, type, set){  
                if (row.concepto) {
                    return row.concepto.codigo
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.concepto) {
                    return row.concepto.nombre
                }
                return '';
            }},
            {
                "data": function (row, type, set){
                    if (row.devengados) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.devengados);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    const deducciones = parseInt(row.deducciones) ? row.deducciones * -1 : 0;
                    if (deducciones) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(deducciones);
                        const parts = formatted.split('.');
                        return `<b style="color: red; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: red; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {
                "data": "unidades",
                render: function(data, type, row) {
                    return row.tipo_unidad == 0 ? data : '';
                }
            },
            {
                "data": "unidades",
                render: function(data, type, row) {
                    return row.tipo_unidad == 1 ? data : '';
                }
            },
            {"data":'observacion'},
            {"data":'porcentaje', render: $.fn.dataTable.render.number(',', '.', 0, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                return `<b style="color: #01a401;">${new Intl.NumberFormat('ja-JP').format(row.base)}</b>`;
            }, className: 'dt-body-right'},
            {"data": "fecha_inicio", defaultContent: ""},
            {"data": "fecha_fin", defaultContent: ""},
            {"data": "hora_inicio", defaultContent: ""},
            {"data": "hora_fin", defaultContent: ""}
        ]
    });

    if (pagar_nomina_table) {
        pagar_nomina_table.on('click', '.detalle-pagar-nomina', function() {

            const id = this.id.split('_')[1];

            $(`#detallepagarnomina_${id}`).hide();
            $(`#detallandopagarnomina_${id}`).show();

            id_periodo_nomina_pagar = id;
            pagar_detalle_nomina_table.ajax.reload(function(res) {
                $(`#detallepagarnomina_${id}`).show();
                $(`#detallandopagarnomina_${id}`).hide();

                if (res.success) {

                    const dataPeriodo = res.data.length ? res.data[0].periodo_pago : null;
                    if (!dataPeriodo) {
                        agregarToast('info', 'Periodo sin detalles', 'El periodo no tiene detalles');
                        return;
                    }

                    if (dataPeriodo.empleado) {
                        $("#textPeriodoPagoDetalle").html(`${dataPeriodo.empleado.numero_documento} - ${dataPeriodo.empleado.nombre_completo}`);
                    }

                    const totalesDetalle = res.totales;

                    const formattedDevengados = new Intl.NumberFormat('ja-JP').format(totalesDetalle.devengados);
                    const partsDevengados = formattedDevengados.split('.');
                    $("#devengado_detalle_total").html(partsDevengados[0]);
                    $("#devengado_detalle_total").attr("data-decimal", partsDevengados[1] ? '.'+partsDevengados[1] : '');

                    const formattedDeducciones = new Intl.NumberFormat('ja-JP').format(totalesDetalle.deducciones);
                    const partsDeducciones = formattedDeducciones.split('.');
                    $("#deduccion_detalle_total").html(partsDeducciones[0]);
                    $("#deduccion_detalle_total").attr("data-decimal", partsDeducciones[1] ? '.'+partsDeducciones[1] : '');

                    const formattedNeto = new Intl.NumberFormat('ja-JP').format(totalesDetalle.neto);
                    const partsNeto = formattedNeto.split('.');
                    $("#neto_detalle_total").html(partsNeto[0]);
                    $("#neto_detalle_total").attr("data-decimal", partsNeto[1] ? '.'+partsNeto[1] : '');

                    $("#periodoPagoDetallePagarModal").modal('show');
                    setTimeout(function(){
                        $('#periodoPagoDetallePagarTable').DataTable().columns.adjust();
                    },200);

                } else {
                    agregarToast('error', 'Consulta errada', 'Error al consultar detalle');
                }
            });
        });

        pagar_nomina_table.on('click', '.pagar-nomina', function() {
            const id = this.id.split('_')[1];

            $(`#pagarnomina_${id}`).hide();
            $(`#pagandonomina_${id}`).show();

            // $('#pagarNominaBtn').hide();
            // $('#pagarNominaLoading').show();

            // Llamada AJAX para causar nómina
            $.ajax({
                url: base_url + 'pagar-periodos-pago',
                method: 'POST',
                data: JSON.stringify({
                    ids_periodos_pago: [id],
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    agregarToast('exito', 'Pago exitoso', 'Nómina pagada con éxito!', true);
                    pagar_nomina_table.ajax.reload();
                } else {
                    agregarToast('error', 'Pago errada', res.message);
                }
            }).fail((err) => {
                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Pago errada', errorsMsg);
            }).always(() => {
                // $('#pagarNominaBtn').show();
                // $('#pagarNominaLoading').hide();

                $(`#pagarnomina_${id}`).show();
                $(`#pagandonomina_${id}`).hide();
            });
        });
    }
}

function initFechasPagos (inputId) {
    // Obtener fecha actual
    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = fecha.getMonth(); // 0-11
    const mesStr = (mes + 1).toString().padStart(2, '0'); // "06"
    const valor = `${anio}-${mesStr}`;
    const texto = `${anio} - ${meses[mes]}`;

    // Crear la opción y asignarla al select2
    const nuevaOpcion = new Option(texto, valor, false, false);
    $(`#meses_pagar_nomina_filter`).append(nuevaOpcion).val(valor).trigger('change');
}

// Función para actualizar el estado del botón de Pagar Nómina
function updatePagarNominaButton() {
    const selectedCheckboxes = $('.check-pagar-periodo:checked');
    
    if (selectedCheckboxes.length > 0) {
        $('#pagarNominaBtn').show();
    } else {
        $('#pagarNominaBtn').hide();
    }
}

// Función para obtener los IDs seleccionados de TODOS los grupos
function getSelectedPeriodIds() {
    const selectedIds = [];
    
    // Buscar todos los checkboxes de grupo seleccionados
    $('.check-pagar-periodo:checked').each(function() {
        const periodoIds = $(this).data('periodo-ids');
        if (periodoIds) {
            // Convertir el string de IDs separados por comas en array
            const idsArray = periodoIds.split(',').map(id => id.trim());
            selectedIds.push(...idsArray);
        }
    });
    
    return selectedIds.filter(id => id !== '' && id !== undefined);
}

$(document).on('change', '.check-pagar-periodo', function() {
    console.log('updatePagarNominaButton');
    updatePagarNominaButton();
});

$(document).on('change', '#meses_pagar_nomina_filter', function () {
    const meses = $('#meses_pagar_nomina_filter').val();
    if (meses && pagar_nomina_table) {
        pagar_nomina_table.ajax.reload();
    }
});