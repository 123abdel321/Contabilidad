var liquidacion_definitiva_table = null;

function liquidaciondefinitivaInit() {

    liquidacion_definitiva_table = null;

    initCombosLiquidacionDefinitiva();
    initTablesLiquidacionDefinitiva();
}

function initCombosLiquidacionDefinitiva() {
    $('#id_empleado_liquidacion_definitiva_filter').select2({
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
}

function initTablesLiquidacionDefinitiva() {
    liquidacion_definitiva_table = $('#liquidacionDefinitivaTable').DataTable({
        dom: 'Brtip',
        paging: false,
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
            url: base_url + 'liquidacion-definitiva',
            data: function ( d ) {
                d.id_empleado = $('#id_empleado_liquidacion_definitiva_filter').val();
            }
        },
        columns: [
            {"data":'id_empleado'},
            {"data":'concepto'},
            {"data":'fecha_inicio'},
            {"data":'fecha_fin'},
            {"data":'dias'},
            {
                "data": function (row, type, set){
                    if (row.base) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.base);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    if (row.promedio) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.promedio);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    if (row.total) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.total);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {"data":'observacion'},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span href="javascript:void(0)" class="btn badge bg-gradient-success edit-prestaciones" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    return html;
                }
            },
        ],
        rowGroup: {
            dataSrc: 'id_empleado',
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
            let totals = 0;
            let $lastGroupRow = null;

            // $('.group-header').remove(); // Limpia grupos anteriores
            $('.group-header, .group-footer').remove();

            data.each(function (row, i) {

                const groupKey = `${row.id_empleado} - ${row.fecha_fin_periodo_formatted}`;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-liquidacion_definitiva" style="background-color: white; font-weight: bold;">
                                <td colspan="6" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(totals)}</b></td>
                                <td colspan="2"></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header-liquidacion_definitiva" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
                            <td colspan="9">
                                <i class="fas fa-minus-square toggle-icon" style="margin-right: 5px; color: #003883;"></i>
                                <b style="font-size: 14px;">${row.numero_documento} - ${row.empleado}</b></b>
                            </td>
                        </tr>
                    `);
                    $row.before(groupRow);

                    lastGroup = groupKey;
                    totals = 0;
                }

                // Sumar los valores
                totals += parseFloat(row.total);

                // Guardar referencia a la última fila del grupo
                $lastGroupRow = $row;
            });

            // Agregar footer del último grupo
            if (lastGroup !== null && $lastGroupRow) {
                const footerRow = $(`
                    <tr class="group-footer-liquidacion_definitiva" style="background-color: white; font-weight: bold;">
                        <td colspan="6" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(totals)}</b></td>
                        <td colspan="2"></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-liquidacion_definitiva').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-liquidacion_definitiva')) {
                    if (!$next.hasClass('group-footer-liquidacion_definitiva')) $next.toggle();
                    $next = $next.next();
                }

                $icon.toggleClass('fa-minus-square fa-plus-square');
            });
        }
    });
}

$(document).on('change', '#id_empleado_liquidacion_definitiva_filter', function () {
    const empleado = $('#id_empleado_liquidacion_definitiva_filter').val();
    if (empleado) {
        liquidacion_definitiva_table.ajax.reload();
    }
});