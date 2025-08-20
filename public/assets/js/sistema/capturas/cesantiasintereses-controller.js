var cesantias_intereses_table = null;
var id_contrato_cesantias_intereses = null;
var cesantias_intereses_table_detalle = null;
var fecha_personalizada_cesantias_intereses = false;

function cesantiasinteresesInit(){
    
    initDataTablesCesantiasIntereses();
    initDateRangePickerCesantiasIntereses();
    $('.water').hide();
}

function initDataTablesCesantiasIntereses() {
    cesantias_intereses_table = $('#cesantiasInteresesTable').DataTable({
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
            url: base_url + 'cesantias-intereses',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_manual_cesantias_intereses').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.fecha_hasta = $('#fecha_manual_cesantias_intereses').data('daterangepicker').endDate.format('YYYY-MM-DD');
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
                    if (row.cesantias) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.cesantias);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return row.cesantias;
                }, 
                className: 'dt-body-right'
            },
            {
                data: function (row, type, set){
                    if (row.intereses) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.intereses);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return row.intereses;
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
                    html+= `<span href="javascript:void(0)" class="btn badge bg-gradient-success edit-cesantias" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    html+= `
                        <span id="detallecesantias_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-info detalle-cesantias" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>
                        <span id="detallandocesantias_${row.id}" class="badge bg-gradient-info" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
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

    cesantias_intereses_table_detalle = $('#cesantiasInteresesDetalleTable').DataTable({
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
            url: base_url + 'cesantias-intereses-detalle',
            data: function ( d ) {
                d.id_contrato = id_contrato_cesantias_intereses;
                d.fecha_desde = $('#fecha_manual_cesantias_intereses').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.fecha_hasta = $('#fecha_manual_cesantias_intereses').data('daterangepicker').endDate.format('YYYY-MM-DD');
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

    if (cesantias_intereses_table) {
        cesantias_intereses_table.on('click', '.edit-cesantias', function() {
            var tr = $(this).closest('tr');
            var row = cesantias_intereses_table.row(tr);
            var rowData = row.data();

            $('#id_cesantias_intereses_up').val(row.index());
            $('#base_cesantias_intereses').val(new Intl.NumberFormat('ja-JP').format(rowData.base));
            $('#dias_cesantias_intereses').val(rowData.dias);
            $('#promedio_cesantias_intereses').val(new Intl.NumberFormat('ja-JP').format(rowData.promedio));
            $('#valor_cesantias_intereses').val(new Intl.NumberFormat('ja-JP').format(rowData.cesantias));
            $('#intereses_cesantias_intereses').val(new Intl.NumberFormat('ja-JP').format(rowData.intereses));

            $("#saveCesantiasEdit").show();
            $("#saveCesantiasEditLoading").hide();

            $("#textCesantiasIntereses").html(`${rowData.numero_documento} - ${rowData.empleado}`);
            $('#cesantiasInteresesFormModal').modal('show');
        });

        cesantias_intereses_table.on('click', '.detalle-cesantias', function() {

            const id = this.id.split('_')[1];
            const tr = $(this).closest('tr');
            const row = cesantias_intereses_table.row(tr);
            const rowData = row.data();

            $(`#detallecesantias_${id}`).hide();
            $(`#detallandocesantias_${id}`).show();

            id_contrato_cesantias_intereses = rowData.id_contrato;

            cesantias_intereses_table_detalle.ajax.reload(function(res) {
                $(`#detallecesantias_${id}`).show();
                $(`#detallandocesantias_${id}`).hide();

                $("#cesantiasInteresesDetalleModal").modal('show');
                setTimeout(function(){
                    $('#cesantiasInteresesDetalleTable').DataTable().columns.adjust();
                },200);
            });
        });
    }
}

function initDateRangePickerCesantiasIntereses() {
    const start = moment().startOf("year");
    const end = moment().endOf("year");

    $("#fecha_manual_cesantias_intereses").daterangepicker({
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

    formatoFecha(start, end, "fecha_manual_cesantias_intereses");
}

function cargarCesantiasIntereses() {

    $("#cargarCesantiasIntereses").hide();
    $("#cargarCesantiasInteresesLoading").show();

    $("#guardarCesantiasIntereses").hide();
    $("#guardarCesantiasInteresesLoading").hide();
    $("#guardarCesantiasInteresesDisabled").show();
    
    cesantias_intereses_table.ajax.reload(function(res) {
        $("#cargarCesantiasIntereses").show();
        $("#cargarCesantiasInteresesLoading").hide();

        $("#guardarCesantiasIntereses").show();
        $("#guardarCesantiasInteresesLoading").hide();
        $("#guardarCesantiasInteresesDisabled").hide();
    });
}

function guardarCesantiasIntereses(overwrite = false) {
    $("#saveCesantiasIntereses").hide();
    $("#saveCesantiasInteresesLoading").show();

    const data = cesantias_intereses_table.rows().data().toArray();

    $.ajax({
        url: base_url + 'cesantias-intereses',
        method: 'POST',
        data: JSON.stringify({
            fecha_desde: $('#fecha_manual_cesantias_intereses').data('daterangepicker').startDate.format('YYYY-MM-DD'),
            fecha_hasta: $('#fecha_manual_cesantias_intereses').data('daterangepicker').endDate.format('YYYY-MM-DD'),
            fecha_novedad: $("#fecha_novedad_cesantias_intereses").val(),
            fecha_personalizada: getTipoGuardadoCesantiasIntereses(),
            data: data
        }),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#saveCesantiasIntereses").show();
        $("#saveCesantiasInteresesLoading").hide();

        if(res.success){
            agregarToast('exito', 'Cesantías e intereses exitoso', 'Cesantías e intereses generados con exito!', true );
        } else {
            agregarToast('error', 'Cesantías e intereses errada', res.message);
        }
        $("#cesantiasInteresesConfirmModal").modal('hide');
    }).fail((res) => {
        $("#saveCesantiasIntereses").show();
        $("#saveCesantiasInteresesLoading").hide();

        agregarToast('error', 'Cesantías e intereses errada', res.message);
    });
}

function getTipoGuardadoCesantiasIntereses() {
    if($("input[type='radio']#tipo_guardado_fecha1").is(':checked')) return 0;
    if($("input[type='radio']#tipo_guardado_fecha2").is(':checked')) return 1;

    return false;
}

$(document).on('click', '#cargarCesantiasIntereses', function () {
    cargarCesantiasIntereses();
});

$(document).on('click', '#guardarCesantiasIntereses', function () {
    const fechaCesantiasIntereses = new Date();
    const fechaCesantias = fechaCesantiasIntereses.getFullYear()+'-'+("0" + (fechaCesantiasIntereses.getMonth() + 1)).slice(-2)+'-'+("0" + (fechaCesantiasIntereses.getDate())).slice(-2);

    $("#fecha_novedad_cesantias_intereses").val(fechaCesantias);
    $("#cesantiasInteresesConfirmModal").modal('show');
});

$(document).on('click', '#saveCesantiasIntereses', function () {
    guardarCesantiasIntereses();
});

$(document).on('click', '#saveCesantiasEdit', function () {
    var rowIndex = $('#id_causar_provisiones_up').val();

    var row = cesantias_intereses_table.row(rowIndex);
    var rowData = row.data();

    rowData.dias = stringToNumberFloat($('#dias_cesantias_intereses').val()) || 0;
    rowData.promedio = stringToNumberFloat($('#promedio_cesantias_intereses').val()) || 0;
    rowData.cesantias = stringToNumberFloat($('#valor_cesantias_intereses').val()) || 0;
    rowData.intereses = stringToNumberFloat($('#intereses_cesantias_intereses').val()) || 0;
    rowData.editado = true;
    row.data(rowData);

    $('#cesantiasInteresesFormModal').modal('hide');
    agregarToast('exito', 'Cambios guardados', 'Cambios guardados localmente!', true );
});

$('input[name="tipo_guardado_fecha"]').change(function() {
    if ($('#tipo_guardado_fecha1').is(':checked')) {
        $('#fecha_novedad_cesantias_intereses').prop('disabled', true);
    } else {
        $('#fecha_novedad_cesantias_intereses').prop('disabled', false);
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