var causar_nomina_table = null;
var periodo_pago_detalle_table = null;
var id_periodo_pago = null

function causarInit() {

    initSelect2Causar();
    initTablesCausar();
}

function initSelect2Causar() {
    $('#meses_causar_nomina_filter').select2({
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

function initTablesCausar() {
    causar_nomina_table = $('#causarNominaTable').DataTable({
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
            url: base_url + 'causar-periodos-pago',
            data: function ( d ) {
                d.meses = $('#meses_causar_nomina_filter').val();
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
                        return `<b style="">${new Intl.NumberFormat('ja-JP').format(row.sum_detalles.devengados)}</b>`;
                    }
                    return `<b style="">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    if (row.sum_detalles) {
                        return `<b style="">${new Intl.NumberFormat('ja-JP').format(row.sum_detalles.deducciones)}</b>`;
                    }
                    return `<b style="">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    if (row.sum_detalles) {
                        return `<b style="">${new Intl.NumberFormat('ja-JP').format(row.sum_detalles.neto)}</b>`;
                    }
                    return `<b style="">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, className: 'dt-body-right'
            },
            {
                "data": function (row, type, set){
                    let html = '';
                    // html+= `
                    //     <span id="causarnomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-primary calcular-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Causar</span>
                    //     <span id="causandonomina_${row.id}" class="badge bg-gradient-primary" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                    //         <b style="opacity: 0.3; text-transform: capitalize;">Causar</b>
                    //         <i style="position: absolute; color: white; font-size: 15px; margin-left: -24px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                    //     </span>
                    //     &nbsp;`;
                    html+= `
                        <span id="detallenomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-info detalle-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>
                        <span id="detallandonomina_${row.id}" class="badge bg-gradient-info" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                            <b style="opacity: 0.3; text-transform: capitalize;">Ver detalle</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -35px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                        &nbsp;`;
                    return html;
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

            // $('.group-header').remove(); // Limpia grupos anteriores
            $('.group-header, .group-footer').remove();

            data.each(function (row, i) {
                const groupKey = `${row.fecha_inicio_periodo_formatted} - ${row.fecha_fin_periodo_formatted}`;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer" style="background-color: white; font-weight: bold;">
                                <td colspan="5" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="">${new Intl.NumberFormat('ja-JP').format(groupTotals.devengado)}</b></td>
                                <td class="text-end"><b style="">${new Intl.NumberFormat('ja-JP').format(groupTotals.deduccion)}</b></td>
                                <td class="text-end"><b style="">${new Intl.NumberFormat('ja-JP').format(groupTotals.neto)}</b></td>
                                <td></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
                            <td colspan="9">
                                <i class="fas fa-minus-square toggle-icon" style="margin-right: 5px; color: #003883;"></i>
                                <b style="font-weight: 500; font-size: 14px;">Periodo desde <b>${row.fecha_inicio_periodo_formatted}</b> hasta <b>${row.fecha_fin_periodo_formatted}</b></b>
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
                    <tr class="group-footer" style="background-color: white; font-weight: bold;">
                        <td colspan="5" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="">${new Intl.NumberFormat('ja-JP').format(groupTotals.devengado)}</b></td>
                        <td class="text-end"><b style="">${new Intl.NumberFormat('ja-JP').format(groupTotals.deduccion)}</b></td>
                        <td class="text-end"><b style="">${new Intl.NumberFormat('ja-JP').format(groupTotals.neto)}</b></td>
                        <td></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header')) {
                    if (!$next.hasClass('group-footer')) $next.toggle();
                    $next = $next.next();
                }

                $icon.toggleClass('fa-minus-square fa-plus-square');
            });
        }
    });

    periodo_pago_detalle_table = $('#periodoPagoDetalleTable').DataTable({
        pageLength: -1,
        dom: 'Brtip',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: false,
        fixedHeader: true,
        deferLoading: 0,
        language: lenguajeDatatable,
        ordering: false,
        scrollX: true,
        scrollCollapse: true,
        sScrollX: "100%",
        autoWidth: false,
        info: false,
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'detalle-periodo',
            data: function ( d ) {
                d.id_periodo_pago = id_periodo_pago;
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
            {"data": function (row, type, set){  
                return `<b style="color: #08cc08;">${new Intl.NumberFormat('ja-JP').format(row.devengados)}</b>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){  
                return `<b style="color: red;">${new Intl.NumberFormat('ja-JP').format(row.deducciones)}</b>`;
            }, className: 'dt-body-right'},
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
                return `<b style="color: #08cc08;">${new Intl.NumberFormat('ja-JP').format(row.base)}</b>`;
            }, className: 'dt-body-right'},
            {"data": "fecha_inicio", defaultContent: ""},
            {"data": "fecha_fin", defaultContent: ""},
            {"data": "hora_inicio", defaultContent: ""},
            {"data": "hora_fin", defaultContent: ""}
        ]
    });

    if (causar_nomina_table) {
        causar_nomina_table.on('click', '.calcular-nomina', function() {
            const id = this.id.split('_')[1];
            const data = getDataById(id, causar_nomina_table);

            $(`#causarnomina_${id}`).hide();
            $(`#causandonomina_${id}`).show();

            $.ajax({
                url: base_url + 'calcular-nomina',
                method: 'POST',
                data: JSON.stringify({
                    mes: $('#meses_causar_nomina_filter').val(),
                    id: [id]
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    causar_nomina_table.ajax.reload();
                    agregarToast('exito', 'Causación exitosa', 'Causación generada con exito!', true );
                } else {
                    agregarToast('error', 'Causación errada', res.message);
                }
            }).fail((res) => {
                agregarToast('error', 'Causación errada', res.message);
            });

            // Swal.fire({
            //     title: 'Causar nómina',
            //     html: `Se va a causar la nómina del empleado <b>${empleado.numero_documento} - ${empleado.nombre_completo}</b>`,
            //     type: 'warning',
            //     icon: 'warning',
            //     showCancelButton: true,
            //     confirmButtonColor: '#3085d6',
            //     cancelButtonColor: '#d33',
            //     confirmButtonText: 'Causar!',
            //     reverseButtons: true,
            // }).then((result) => {
            //     if (result.value){

                    
            //     }
            // });
        });

        causar_nomina_table.on('click', '.detalle-nomina', function() {

            const id = this.id.split('_')[1];

            $(`#detallenomina_${id}`).hide();
            $(`#detallandonomina_${id}`).show();

            id_periodo_pago = id;
            periodo_pago_detalle_table.ajax.reload(function(res) {
                $(`#detallenomina_${id}`).show();
                $(`#detallandonomina_${id}`).hide();

                $("#periodoPagoDetalleModal").modal('show');
                setTimeout(function(){
                    $('#periodoPagoDetalleTable').DataTable().columns.adjust();
                },200);
            });
        });
    }
}

$(document).on('click', '#recalcularPeriodos', function () {

    $("#recalcularPeriodos").hide();
    $("#recalcularPeriodosLoading").show();

    $.ajax({
        url: base_url + 'calcular-nomina',
        method: 'POST',
        data: JSON.stringify({
            mes: $('#meses_causar_nomina_filter').val()
        }),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#recalcularPeriodos").show();
        $("#recalcularPeriodosLoading").hide();
        if(res.success){
            causar_nomina_table.ajax.reload();
            agregarToast('exito', 'Calculo periodo exitoso', 'Calculo periodo generado con exito!', true );
        } else {
            agregarToast('error', 'Calculo periodo errado', res.message);
        }
    }).fail((err) => {
        $("#recalcularPeriodos").show();
        $("#recalcularPeriodosLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Calculo periodo errado', errorsMsg);
    });
});

$(document).on('change', '#meses_causar_nomina_filter', function () {
    const meses = $('#meses_causar_nomina_filter').val();
    if (meses) {
        causar_nomina_table.ajax.reload();
    }
});