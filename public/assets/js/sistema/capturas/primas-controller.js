var primas_table = null;
var id_contrato_primas = null;
var primas_table_detalle = null;
var fecha_personalizada_primas = false;

function primasInit(){
    
    initDataTablesPrimas();
    initDateRangePickerPrimas();
    $('.water').hide();
}

function initDataTablesPrimas() {
    primas_table = $('#primasTable').DataTable({
        dom: 'Brtip',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
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
            url: base_url + 'primas',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_manual_primas').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.fecha_hasta = $('#fecha_manual_primas').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
        },

        columns: [
            { data: "numero_documento", className: 'dt-body-right'},
            { data: "empleado"},
            { data: "fecha_inicio"},
            { data: "fecha_fin"},
            { data: "dias", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                data: function (row, type, set){
                    if (row.base) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.base);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return row.base;
                }, 
                className: 'dt-body-right'
            },
            {
                data: function (row, type, set){
                    if (row.promedio) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.promedio);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return row.promedio;
                }, 
                className: 'dt-body-right'
            },
            {
                data: function (row, type, set){
                    if (row.valor) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.valor);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return row.valor;
                }, 
                className: 'dt-body-right'
            },
            {
                data: function (row, type, set){
                    if (row.dias_promedio) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.dias_promedio);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return row.dias_promedio;
                }, 
                className: 'dt-body-right'
            },
            {
                data: function (row, type, set){
                    if (row.editado) {
                        return `<b>Si</b>`;
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= `<span href="javascript:void(0)" class="btn badge bg-gradient-success edit-primas" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    html+= `
                        <span id="detalleprimas_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-info detalle-primas" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>
                        <span id="detallandoprimas_${row.id}" class="badge bg-gradient-info" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                            <b style="opacity: 0.3; text-transform: math-auto;">Ver detalle</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -35px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                        &nbsp;
                    `;
                    return html;
                }
            },
        ],
    });

    primas_table_detalle = $('#primasDetalleTable').DataTable({
        dom: 'Brtip',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
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
            url: base_url + 'primas-detalle',
            data: function ( d ) {
                d.id_contrato = id_contrato_primas;
                d.fecha_desde = $('#fecha_manual_primas').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.fecha_hasta = $('#fecha_manual_primas').data('daterangepicker').endDate.format('YYYY-MM-DD');
            }
        },
        columns: [
            { data: 'fecha_fin_periodo'},
            { data: 'concepto'},
            { data: 'dias'},
            {
                data: function (row, type, set){
                    if (row.valor) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.valor);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return row.valor;
                }, 
                className: 'dt-body-right'
            },
        ]
    });

    if (primas_table) {
        primas_table.on('click', '.edit-primas', function() {
            var tr = $(this).closest('tr');
            var row = primas_table.row(tr);
            var rowData = row.data();

            $('#id_primas_up').val(row.index());
            $('#base_primas').val(new Intl.NumberFormat('ja-JP').format(rowData.base));
            $('#dias_primas').val(rowData.dias);
            $('#promedio_primas').val(new Intl.NumberFormat('ja-JP').format(rowData.promedio));
            $('#valor_primas').val(new Intl.NumberFormat('ja-JP').format(rowData.valor));

            $("#savePrimasEdit").show();
            $("#savePrimasEditLoading").hide();

            $("#textPrimas").html(`${rowData.numero_documento} - ${rowData.empleado}`);
            $('#primasFormModal').modal('show');
        });

        primas_table.on('click', '.detalle-primas', function() {

            const id = this.id.split('_')[1];
            const tr = $(this).closest('tr');
            const row = primas_table.row(tr);
            const rowData = row.data();

            $(`#detalleprimas_${id}`).hide();
            $(`#detallandoprimas_${id}`).show();

            id_contrato_primas = rowData.id_contrato;

            primas_table_detalle.ajax.reload(function(res) {
                $(`#detalleprimas_${id}`).show();
                $(`#detallandoprimas_${id}`).hide();

                $("#primasDetalleModal").modal('show');
                setTimeout(function(){
                    $('#primasDetalleTable').DataTable().columns.adjust();
                },200);
            });
        });
    }
}

function initDateRangePickerPrimas() {
    const start = moment().startOf("year");
    const end = moment().endOf("year");

    $("#fecha_manual_primas").daterangepicker({
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

    formatoFecha(start, end, "fecha_manual_primas");
}

function cargarPrimas() {

    $("#cargarPrimas").hide();
    $("#cargarPrimasLoading").show();

    $("#guardarPrimas").hide();
    $("#guardarPrimasLoading").hide();
    $("#guardarPrimasDisabled").show();
    
    primas_table.ajax.reload(function(res) {
        $("#cargarPrimas").show();
        $("#cargarPrimasLoading").hide();

        $("#guardarPrimas").show();
        $("#guardarPrimasLoading").hide();
        $("#guardarPrimasDisabled").hide();
    });
}

function guardarPrimas(overwrite = false) {
    $("#savePrimas").hide();
    $("#savePrimasLoading").show();

    const data = primas_table.rows().data().toArray();

    $.ajax({
        url: base_url + 'primas',
        method: 'POST',
        data: JSON.stringify({
            fecha_desde: $('#fecha_manual_primas').data('daterangepicker').startDate.format('YYYY-MM-DD'),
            fecha_hasta: $('#fecha_manual_primas').data('daterangepicker').endDate.format('YYYY-MM-DD'),
            fecha_novedad: $("#fecha_novedad_primas").val(),
            fecha_personalizada: getTipoGuardadoPrimas(),
            data: data
        }),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#savePrimas").show();
        $("#savePrimasLoading").hide();

        if(res.success){
            agregarToast('exito', 'Primas exitoso', 'Primas generados con exito!', true );
        } else {
            agregarToast('error', 'Primas errada', res.message);
        }
        $("#primasConfirmModal").modal('hide');
    }).fail((res) => {
        $("#savePrimas").show();
        $("#savePrimasLoading").hide();

        agregarToast('error', 'Primas errada', res.message);
    });
}

function getTipoGuardadoPrimas() {
    if($("input[type='radio']#tipo_guardado_fecha1").is(':checked')) return 0;
    if($("input[type='radio']#tipo_guardado_fecha2").is(':checked')) return 1;

    return false;
}

$(document).on('click', '#cargarPrimas', function () {
    cargarPrimas();
});

$(document).on('click', '#guardarPrimas', function () {
    const fechaPrimas = new Date();
    const fechaPrimasSet = fechaPrimas.getFullYear()+'-'+("0" + (fechaPrimas.getMonth() + 1)).slice(-2)+'-'+("0" + (fechaPrimas.getDate())).slice(-2);

    $("#fecha_novedad_primas").val(fechaPrimasSet);
    $("#primasConfirmModal").modal('show');
});

$(document).on('click', '#savePrimas', function () {
    guardarPrimas();
});

$(document).on('click', '#savePrimasEdit', function () {
    var rowIndex = $('#id_causar_provisiones_up').val();

    var row = primas_table.row(rowIndex);
    var rowData = row.data();

    rowData.dias = stringToNumberFloat($('#dias_primas').val()) || 0;
    rowData.promedio = stringToNumberFloat($('#promedio_primas').val()) || 0;
    rowData.primas = stringToNumberFloat($('#valor_primas').val()) || 0;
    rowData.editado = true;
    row.data(rowData);

    $('#primasFormModal').modal('hide');
    agregarToast('exito', 'Cambios guardados', 'Cambios guardados localmente!', true );
});

$('input[name="tipo_guardado_fecha"]').change(function() {
    if ($('#tipo_guardado_fecha1').is(':checked')) {
        $('#fecha_novedad_primas').prop('disabled', true);
    } else {
        $('#fecha_novedad_primas').prop('disabled', false);
    }
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