var id_periodo_pago = null
var parafiscales_table = null;
var causar_nomina_table = null;
var activar_prestaciones = false;
var activar_parafiscales = false;
var seguridad_social_table = null;
var activar_seguridad_social = false;
var periodo_pago_detalle_table = null;
var prestaciones_sociales_table = null;

function causarInit() {
    parafiscales_table = null;
    seguridad_social_table = null;
    prestaciones_sociales_table = null;

    activar_prestaciones = false;
    activar_parafiscales = false;
    activar_seguridad_social = false;

    initSelect2Causar();
    initTablesCausar();
    initComboMes('meses_causar_nomina_filter');
}

function initSelect2Causar() {
    const comboMeses = [
        'meses_parafiscales_filter',
        'meses_causar_nomina_filter',
        'meses_seguridad_social_filter',
        'meses_prestaciones_sociales_filter',
    ];

    comboMeses.forEach(combo => {
        $(`#${combo}`).select2({
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
                            <b style="opacity: 0.3; text-transform: math-auto;">Ver detalle</b>
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
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.devengado)}</b></td>
                                <td class="text-end"><b style="color: red;">${formatNumberWithSmallDecimals(groupTotals.deduccion * -1)}</b></td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.neto)}</b></td>
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
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.devengado)}</b></td>
                        <td class="text-end"><b style="color: red;">${formatNumberWithSmallDecimals(groupTotals.deduccion * -1)}</b></td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(groupTotals.neto)}</b></td>
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
        deferRender: true,
        deferLoading: true,
        dom: 'Brtip',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: false,
        fixedHeader: true,
        deferLoading: 0,
        lalanguage: {
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
                return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(row.devengados)}</b>`;
            }, className: 'dt-body-right'},
            {"data": function (row, type, set){  
                const deducciones = parseInt(row.deducciones) ? row.deducciones * -1 : 0;
                return `<b style="color: red; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(deducciones)}</b>`;
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
                return `<b style="color: #01a401;">${new Intl.NumberFormat('ja-JP').format(row.base)}</b>`;
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
        });

        causar_nomina_table.on('click', '.detalle-nomina', function() {

            const id = this.id.split('_')[1];

            $(`#detallenomina_${id}`).hide();
            $(`#detallandonomina_${id}`).show();

            id_periodo_pago = id;
            periodo_pago_detalle_table.ajax.reload(function(res) {
                $(`#detallenomina_${id}`).show();
                $(`#detallandonomina_${id}`).hide();

                if (res.success) {

                    const dataPeriodo = res.data.length ? res.data[0].periodo_pago : null;
                    if (dataPeriodo.empleado) {
                        $("#textPeriodoPagoDetalle").html(`${dataPeriodo.empleado.numero_documento} - ${dataPeriodo.empleado.nombre_completo}`);
                    }

                    const totalesDetalle = res.totales;

                    $("#devengado_detalle_total").html(new Intl.NumberFormat('ja-JP').format(totalesDetalle.devengados));
                    $("#deduccion_detalle_total").html(new Intl.NumberFormat('ja-JP').format(totalesDetalle.deducciones));
                    $("#neto_detalle_total").html(new Intl.NumberFormat('ja-JP').format(totalesDetalle.neto));

                    $("#periodoPagoDetalleModal").modal('show');
                    setTimeout(function(){
                        $('#periodoPagoDetalleTable').DataTable().columns.adjust();
                    },200);

                } else {
                    agregarToast('error', 'Consulta errada', 'Error al consultar detalle');
                }
            });
        });
    }
}

function initComboMes(inputId) {
    // Obtener fecha actual
    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = fecha.getMonth(); // 0-11
    const mesStr = (mes + 1).toString().padStart(2, '0'); // "06"
    const valor = `${anio}-${mesStr}`;
    const texto = `${anio} - ${meses[mes]}`;

    // Crear la opción y asignarla al select2
    const nuevaOpcion = new Option(texto, valor, false, false);
    $(`#${inputId}`).append(nuevaOpcion).val(valor).trigger('change'); 
}

function formatNumberWithSmallDecimals(number) {
    const formatted = new Intl.NumberFormat('ja-JP').format(number);
    const parts = formatted.split('.');
    if (parts.length > 1) {
        return `<span class="integer-part">${parts[0]}</span><span class="decimal-part">.${parts[1]}</span>`;
    }
    return formatted;
}

function initPrestacionesSociales() {
    prestaciones_sociales_table = $('#prestacionesSocialesTable').DataTable({
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
            url: base_url + 'prestaciones-sociales',
            data: function ( d ) {
                d.meses = $('#meses_prestaciones_sociales_filter').val();
            }
        },
        columns: [
            {"data":'id_empleado'},
            {"data":'concepto'},
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
            {"data":'porcentaje', render: $.fn.dataTable.render.number(',', '.', 4, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    if (row.provision) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.provision);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {"data":'fondo'},
            {"data":'cuenta_debito'},
            {"data":'cuenta_credito'},
            {
                "data": function (row, type, set){
                    if (row.editado) {
                        return `<b>Si</b>`;
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span href="javascript:void(0)" class="btn badge bg-gradient-success edit-prestaciones" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    return html;
                }
            },
        ],
        rowGroup: {
            dataSrc: 'fecha_periodo',
            startRender: null // Desactiva la fila de grupo automática
        },
        columnDefs: [
            { 
                targets: '_all', // Aplica a todas las columnas
                searchable: false,
                orderable: false
            },
            { targets: 0, visible: false } // Oculta la columna del "Empleado"
        ],
        drawCallback: function () {
            const api = this.api();
            const rows = api.rows({ page: 'current' }).nodes();
            const data = api.rows({ page: 'current' }).data();

            let lastGroup = null;
            let provision = 0;
            let $lastGroupRow = null;

            // $('.group-header').remove(); // Limpia grupos anteriores
            $('.group-header-prestaciones-sociales, .group-footer-prestaciones-sociales').remove();

            data.each(function (row, i) {

                const groupKey = row.id_empleado;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-prestaciones-sociales" style="background-color: white; font-weight: bold;">
                                <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                                <td colspan="4"></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header-prestaciones-sociales" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
                            <td colspan="9">
                                <i class="fas fa-minus-square toggle-icon" style="margin-right: 5px; color: #003883;"></i>
                                <b style="font-size: 14px;">${row.numero_documento} - ${row.empleado}</b></b>
                            </td>
                        </tr>
                    `);
                    $row.before(groupRow);

                    lastGroup = groupKey;
                    provision = 0;
                }

                // Sumar los valores
                provision += parseFloat(row.provision);

                // Guardar referencia a la última fila del grupo
                $lastGroupRow = $row;
            });

            // Agregar footer del último grupo
            if (lastGroup !== null && $lastGroupRow) {
                const footerRow = $(`
                    <tr class="group-footer-prestaciones-sociales" style="background-color: white; font-weight: bold;">
                        <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                        <td colspan="4"></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-prestaciones-sociales').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-prestaciones-sociales')) {
                    if (!$next.hasClass('group-footer-prestaciones-sociales')) $next.toggle();
                    $next = $next.next();
                }

                $icon.toggleClass('fa-minus-square fa-plus-square');
            });
        }
    });

    if (prestaciones_sociales_table) {
        prestaciones_sociales_table.on('click', '.edit-prestaciones', function() {
            var tr = $(this).closest('tr');
            var row = prestaciones_sociales_table.row(tr);
            var rowData = row.data();

            $('#id_causar_provisiones_up').val(row.index());
            $('#nombre_causar_provisiones').val(rowData.concepto);
            $('#base_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.base));
            $('#porcentaje_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.porcentaje));
            $('#provision_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.provision));

            $("#saveParafiscales").hide();
            $("#saveSeguridadSocial").hide();
            $("#savePrestacionesSociales").show();
            $("#saveCausarProvisionesLoading").hide();

            $("#textCausarProvisiones").html(`${rowData.numero_documento} - ${rowData.empleado}`);
            $('#causarProvisionModal').modal('show');
        });
    }

    initComboMes('meses_prestaciones_sociales_filter');
    activar_prestaciones = true;
}

function initSeguridadSocial() {
    seguridad_social_table = $('#seguridadSocialTable').DataTable({
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
            url: base_url + 'seguridad-social',
            data: function ( d ) {
                d.meses = $('#meses_seguridad_social_filter').val();
            }
        },
        columns: [
            {"data":'id_empleado'},
            {"data":'concepto'},
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
            {"data":'porcentaje', render: $.fn.dataTable.render.number(',', '.', 4, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    if (row.provision) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.provision);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {"data":'fondo'},
            {"data":'cuenta_debito'},
            {"data":'cuenta_credito'},
            {
                "data": function (row, type, set){
                    if (row.editado) {
                        return `<b>Si</b>`;
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span href="javascript:void(0)" class="btn badge bg-gradient-success edit-seguridad" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    return html;
                }
            },
        ],
        rowGroup: {
            dataSrc: 'fecha_periodo',
            startRender: null // Desactiva la fila de grupo automática
        },
        columnDefs: [
            { 
                targets: '_all', // Aplica a todas las columnas
                searchable: false,
                orderable: false
            },
            { targets: 0, visible: false } // Oculta la columna del "Empleado"
        ],
        drawCallback: function () {
            const api = this.api();
            const rows = api.rows({ page: 'current' }).nodes();
            const data = api.rows({ page: 'current' }).data();

            let lastGroup = null;
            let provision = 0;
            let $lastGroupRow = null;
            // $('.group-header').remove(); // Limpia grupos anteriores
            $('.group-header-seguridad_social, .group-footer-seguridad_social').remove();

            data.each(function (row, i) {

                const groupKey = row.id_empleado;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-seguridad_social" style="background-color: white; font-weight: bold;">
                                <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                                <td colspan="4"></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header-seguridad_social" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
                            <td colspan="9">
                                <i class="fas fa-minus-square toggle-icon" style="margin-right: 5px; color: #003883;"></i>
                                <b style="font-size: 14px;">${row.numero_documento} - ${row.empleado}</b></b>
                            </td>
                        </tr>
                    `);
                    $row.before(groupRow);

                    lastGroup = groupKey;
                    provision = 0;
                }

                // Sumar los valores
                provision += parseFloat(row.provision);

                // Guardar referencia a la última fila del grupo
                $lastGroupRow = $row;
            });

            // Agregar footer del último grupo
            if (lastGroup !== null && $lastGroupRow) {
                const footerRow = $(`
                    <tr class="group-footer-seguridad_social" style="background-color: white; font-weight: bold;">
                        <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                        <td colspan="4"></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-seguridad_social').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-seguridad_social')) {
                    if (!$next.hasClass('group-footer-seguridad_social')) $next.toggle();
                    $next = $next.next();
                }

                $icon.toggleClass('fa-minus-square fa-plus-square');
            });
        }
    });

    if (seguridad_social_table) {
        seguridad_social_table.on('click', '.edit-seguridad', function() {
            var tr = $(this).closest('tr');
            var row = seguridad_social_table.row(tr);
            var rowData = row.data();

            $('#id_causar_provisiones_up').val(row.index());
            $('#nombre_causar_provisiones').val(rowData.concepto);
            $('#base_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.base));
            $('#porcentaje_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.porcentaje));
            $('#provision_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.provision));

            $("#saveParafiscales").hide();
            $("#saveSeguridadSocial").show();
            $("#savePrestacionesSociales").hide();
            $("#saveCausarProvisionesLoading").hide();

            $("#textCausarProvisiones").html(`${rowData.numero_documento} - ${rowData.empleado}`);
            $('#causarProvisionModal').modal('show');
        });
    }

    initComboMes('meses_seguridad_social_filter');
    activar_seguridad_social = true;
}

function initParafiscales() {
    parafiscales_table = $('#parafiscalesTable').DataTable({
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
            url: base_url + 'parafiscales',
            data: function ( d ) {
                d.meses = $('#meses_parafiscales_filter').val();
            }
        },
        columns: [
            {"data":'id_empleado'},
            {"data":'concepto'},
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
            {"data":'porcentaje', render: $.fn.dataTable.render.number(',', '.', 4, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    if (row.provision) {
                        const formatted = new Intl.NumberFormat('ja-JP').format(row.provision);
                        const parts = formatted.split('.');
                        return `<b style="color: #01a401; font-weight: 600;" ${parts.length > 1 ? `data-decimal=".${parts[1]}"` : ''}>${parts[0]}</b>`;
                    }
                    return `<b style="color: #01a401; font-weight: 600;">${new Intl.NumberFormat('ja-JP').format(0)}</b>`;
                }, 
                className: 'dt-body-right'
            },
            {"data":'fondo'},
            {"data":'cuenta_debito'},
            {"data":'cuenta_credito'},
            {
                "data": function (row, type, set){
                    if (row.editado) {
                        return `<b>Si</b>`;
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span href="javascript:void(0)" class="btn badge bg-gradient-success edit-parafiscales" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    return html;
                }
            },
        ],
        rowGroup: {
            dataSrc: 'fecha_periodo',
            startRender: null // Desactiva la fila de grupo automática
        },
        columnDefs: [
            { 
                targets: '_all', // Aplica a todas las columnas
                searchable: false,
                orderable: false
            },
            { targets: 0, visible: false } // Oculta la columna del "Empleado"
        ],
        drawCallback: function () {
            const api = this.api();
            const rows = api.rows({ page: 'current' }).nodes();
            const data = api.rows({ page: 'current' }).data();

            let lastGroup = null;
            let provision = 0;
            let $lastGroupRow = null;
            // $('.group-header').remove(); // Limpia grupos anteriores
            $('.group-header-parafiscales, .group-footer-parafiscales').remove();

            data.each(function (row, i) {

                const groupKey = row.id_empleado;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-parafiscales" style="background-color: white; font-weight: bold;">
                                <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                                <td colspan="4"></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header-parafiscales" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
                            <td colspan="9">
                                <i class="fas fa-minus-square toggle-icon" style="margin-right: 5px; color: #003883;"></i>
                                <b style="font-size: 14px;">${row.numero_documento} - ${row.empleado}</b></b>
                            </td>
                        </tr>
                    `);
                    $row.before(groupRow);

                    lastGroup = groupKey;
                    provision = 0;
                }

                // Sumar los valores
                provision += parseFloat(row.provision);

                // Guardar referencia a la última fila del grupo
                $lastGroupRow = $row;
            });

            // Agregar footer del último grupo
            if (lastGroup !== null && $lastGroupRow) {
                const footerRow = $(`
                    <tr class="group-footer-parafiscales" style="background-color: white; font-weight: bold;">
                        <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                        <td colspan="4"></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-parafiscales').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-parafiscales')) {
                    if (!$next.hasClass('group-footer-parafiscales')) $next.toggle();
                    $next = $next.next();
                }

                $icon.toggleClass('fa-minus-square fa-plus-square');
            });
        }
    });

    if (parafiscales_table) {
        parafiscales_table.on('click', '.edit-parafiscales', function() {
            var tr = $(this).closest('tr');
            var row = parafiscales_table.row(tr);
            var rowData = row.data();

            $('#id_causar_provisiones_up').val(row.index());
            $('#nombre_causar_provisiones').val(rowData.concepto);
            $('#base_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.base));
            $('#porcentaje_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.porcentaje));
            $('#provision_causar_provisiones').val(new Intl.NumberFormat('ja-JP').format(rowData.provision));

            $("#saveParafiscales").show();
            $("#saveSeguridadSocial").hide();
            $("#savePrestacionesSociales").hide();
            $("#saveCausarProvisionesLoading").hide();

            $("#textCausarProvisiones").html(`${rowData.numero_documento} - ${rowData.empleado}`);
            $('#causarProvisionModal').modal('show');
        });
    }

    initComboMes('meses_parafiscales_filter');
    activar_parafiscales = true;
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

$(document).on('change', '#meses_prestaciones_sociales_filter', function () {
    const meses = $('#meses_prestaciones_sociales_filter').val();
    if (meses) {
        prestaciones_sociales_table.ajax.reload();
    }
});

$(document).on('change', '#meses_seguridad_social_filter', function () {
    const meses = $('#meses_seguridad_social_filter').val();
    if (meses) {
        seguridad_social_table.ajax.reload();
    }
});

$(document).on('change', '#meses_parafiscales_filter', function () {
    const meses = $('#meses_parafiscales_filter').val();
    if (meses) {
        parafiscales_table.ajax.reload();
    }
});

$(document).on('click', '#prestaciones-sociales-tab', function () {
    if (activar_prestaciones) {
        return
    }
    initPrestacionesSociales();
});

$(document).on('click', '#seguridad_social-tab', function () {
    if (seguridad_social_table) {
        return
    }
    initSeguridadSocial();
});

$(document).on('click', '#parafiscales-tab', function () {
    if (parafiscales_table) {
        return
    }
    initParafiscales();
});

function actualizarProviciones(provicionada_tabla) {
    var rowIndex = $('#id_causar_provisiones_up').val();

    var row = provicionada_tabla.row(rowIndex);
    var rowData = row.data();

    rowData.base = stringToNumberFloat($('#base_causar_provisiones').val()) || 0;
    rowData.porcentaje = stringToNumberFloat($('#porcentaje_causar_provisiones').val()) || 0;
    rowData.provision = stringToNumberFloat($('#provision_causar_provisiones').val()) || 0;
    rowData.editado = true;
    row.data(rowData);

    $('#causarProvisionModal').modal('hide');
    agregarToast('exito', 'Cambios guardados', 'Cambios guardados localmente!', true );
}

$(document).on('click', '#savePrestacionesSociales', function () {
    actualizarProviciones(prestaciones_sociales_table);
});

$(document).on('click', '#saveSeguridadSocial', function () {
    actualizarProviciones(seguridad_social_table);
});

$(document).on('click', '#saveParafiscales', function () {
    actualizarProviciones(parafiscales_table);
});

$(document).on('click', '#causarPrestaciones', function () {
    Swal.fire({
        title: 'Causar prestaciones sociales',
        html: `Se van a causar las prestaciones sociales, ¿Desea continuar? </b>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Causar!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){

            $("#causarPrestaciones").hide();
            $("#causarPrestacionesLoading").show();
            
            var prestacionesSocialesData = prestaciones_sociales_table.rows().data().toArray();

            $.ajax({
                url: base_url + 'prestaciones-sociales',
                method: 'POST',
                data: JSON.stringify({
                    fecha: $('#meses_prestaciones_sociales_filter').val(),
                    prestaciones: JSON.stringify(prestacionesSocialesData)
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#causarPrestaciones").show();
                $("#causarPrestacionesLoading").hide();

                if(res.success){
                    agregarToast('exito', 'Causación exitosa', 'Causación de prestaciones sociales generados con exito!', true );
                } else {
                    agregarToast('error', 'Causación errada', res.message);
                }
            }).fail((res) => {
                $("#causarPrestaciones").show();
                $("#causarPrestacionesLoading").hide();

                agregarToast('error', 'Causación errada', res.message);
            });
        }
    });
});

$(document).on('click', '#causarSeguridad', function () {
    Swal.fire({
        title: 'Causar seguridad social',
        html: `Se van a causar las seguridades sociales, ¿Desea continuar? </b>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Causar!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){

            $("#causarSeguridad").hide();
            $("#causarSeguridadLoading").show();
            
            var seguridadesSocialesData = seguridad_social_table.rows().data().toArray();

            $.ajax({
                url: base_url + 'seguridad-social',
                method: 'POST',
                data: JSON.stringify({
                    fecha: $('#meses_seguridad_social_filter').val(),
                    prestaciones: JSON.stringify(seguridadesSocialesData)
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#causarSeguridad").show();
                $("#causarSeguridadLoading").hide();

                if(res.success){
                    agregarToast('exito', 'Causación exitosa', 'Causación de seguridad social generados con exito!', true );
                } else {
                    agregarToast('error', 'Causación errada', res.message);
                }
            }).fail((err) => {
                $("#causarSeguridad").show();
                $("#causarSeguridadLoading").hide();

                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Creación errada', errorsMsg);
            });
        }
    });
});

$(document).on('click', '#causarParafiscales', function () {
    Swal.fire({
        title: 'Causar parafiscales',
        html: `Se van a causar las parafiscales, ¿Desea continuar? </b>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Causar!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){

            $("#causarParafiscales").hide();
            $("#causarParafiscalesLoading").show();
            
            var parafiscalesData = parafiscales_table.rows().data().toArray();

            $.ajax({
                url: base_url + 'parafiscales',
                method: 'POST',
                data: JSON.stringify({
                    fecha: $('#meses_parafiscales_filter').val(),
                    prestaciones: JSON.stringify(parafiscalesData)
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#causarParafiscales").show();
                $("#causarParafiscalesLoading").hide();

                if(res.success){
                    agregarToast('exito', 'Causación exitosa', 'Causación de seguridad social generados con exito!', true );
                } else {
                    agregarToast('error', 'Causación errada', res.message);
                }
            }).fail((err) => {
                $("#causarParafiscales").show();
                $("#causarParafiscalesLoading").hide();

                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Creación errada', errorsMsg);
            });
        }
    });
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