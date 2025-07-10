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
            url: 'api/nit/empleado-activo',
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
                    html+= '<span href="javascript:void(0)" class="btn badge bg-gradient-success edit-liquidacion" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
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
                            <tr class="group-footer-liquidacion_definitiva" data-group="${groupKey}" style="background-color: white; font-weight: bold;">
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

    if (liquidacion_definitiva_table) {
        liquidacion_definitiva_table.on('click', '.edit-liquidacion', function() {
            var tr = $(this).closest('tr');
            var row = liquidacion_definitiva_table.row(tr);
            var rowData = row.data();

            $('#id_liquidacion_definitiva_up').val(row.index());
            $('#nombre_liquidacion_definitiva').val(rowData.concepto);
            $('#base_liquidacion_definitiva').val(new Intl.NumberFormat('ja-JP').format(rowData.base));
            $('#promedio_liquidacion_definitiva').val(new Intl.NumberFormat('ja-JP').format(rowData.promedio));
            $('#total_liquidacion_definitiva').val(new Intl.NumberFormat('ja-JP').format(rowData.total));
            $('#observacion_liquidacion_definitiva').val(rowData.observacion);

            $("#textLiquidacionDefinitiva").html(`${rowData.numero_documento} - ${rowData.empleado}`);
            $('#liquidacionDefinitivaModal').modal('show');
        });
    }
}

$(document).on('change', '#id_empleado_liquidacion_definitiva_filter', function () {
    const empleado = $('#id_empleado_liquidacion_definitiva_filter').val();
    if (empleado) {
        liquidacion_definitiva_table.ajax.reload();
    }
});

$(document).on('click', '#saveLiquidacionDefinitiva', function () {
    var rowIndex = $('#id_liquidacion_definitiva_up').val();

    var row = liquidacion_definitiva_table.row(rowIndex);
    var rowData = row.data();

    // Guardar el valor anterior para calcular la diferencia
    var oldTotal = parseFloat(rowData.total) || 0;

    rowData.base = stringToNumberFloat($('#base_liquidacion_definitiva').val()) || 0;
    rowData.promedio = stringToNumberFloat($('#promedio_liquidacion_definitiva').val()) || 0;
    rowData.total = stringToNumberFloat($('#total_liquidacion_definitiva').val()) || 0;
    rowData.observacion = $("#observacion_liquidacion_definitiva").val();
    row.data(rowData);

    // Calcular la diferencia
    var newTotal = parseFloat(rowData.total) || 0;
    var difference = newTotal - oldTotal;

    // Actualizar el footer correspondiente
    var groupKey = `${rowData.id_empleado} - ${rowData.fecha_fin_periodo_formatted}`;
    var $footer = $(`.group-footer-liquidacion_definitiva[data-group="${groupKey}"]`);
    
    if ($footer.length) {
        var currentTotalText = $footer.find('td:eq(1) b').text();
        var currentTotal = parseFloat(currentTotalText.replace(/[^0-9.-]/g, '')) || 0;
        var updatedTotal = currentTotal + difference;
        
        $footer.find('td:eq(1) b').text(formatNumberWithSmallDecimals(updatedTotal));
    }

    $('#liquidacionDefinitivaModal').modal('hide');
    agregarToast('exito', 'Cambios guardados', 'Cambios guardados localmente!', true );
});

$(document).on('click', '#causarLiquidacionDefinitiva', function () {
    Swal.fire({
        title: 'Guardar liquidación definitiva',
        html: `Se va a guardar la liquidación definitiva, ¿Desea continuar? </b>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Guardar!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){

            $("#causarLiquidacionDefinitiva").hide();
            $("#causarLiquidacionDefinitivaLoading").show();
            
            var liquidacionDefinitivaData = liquidacion_definitiva_table.rows().data().toArray();

            $.ajax({
                url: base_url + 'liquidacion-definitiva',
                method: 'POST',
                data: JSON.stringify({
                    id_empleado: $('#id_empleado_liquidacion_definitiva_filter').val(),
                    novedades: JSON.stringify(liquidacionDefinitivaData)
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#causarLiquidacionDefinitiva").show();
                $("#causarLiquidacionDefinitivaLoading").hide();

                if(res.success){
                    agregarToast('exito', 'Guardado exitoso', 'Se guardó la liquidación definitiva con exito!', true );
                } else {
                    agregarToast('error', 'Guardado errado', res.message);
                }
            }).fail((err) => {
                $("#causarLiquidacionDefinitiva").show();
                $("#causarLiquidacionDefinitivaLoading").hide();

                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Guardado errado', errorsMsg);
            });
        }
    });
});