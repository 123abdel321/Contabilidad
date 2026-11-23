var id_periodo_pago = null
var id_empleado_causar = null
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
    initClickCausar();
    initComboMes('meses_causar_nomina_filter');
}

function initClickCausar() {
    $('#causarNominaBtn').on('click', function() {
        console.log('causarNominaBtn');
        const selectedPeriods = getSelectedPeriodIds();
        
        if (selectedPeriods.length === 0) {
            agregarToast('warning', 'Selección requerida', 'Por favor seleccione al menos un período para causar');
            return;
        }
        
        // Mostrar loading
        $('#causarNominaBtn').hide();
        $('#causarNominaLoading').show();

        // Llamada AJAX para causar nómina
        $.ajax({
            url: base_url + 'causar-nomina',
            method: 'POST',
            data: JSON.stringify({
                ids_periodos_pago: selectedPeriods,
                mes: $('#meses_causar_nomina_filter').val()
            }),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                agregarToast('exito', 'Causación exitosa', 'Nómina causada con éxito!', true);
                causar_nomina_table.ajax.reload();
            } else {
                agregarToast('error', 'Causación errada', res.message);
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Causación errada', errorsMsg);
        }).always(() => {
            $('#causarNominaBtn').show();
            $('#causarNominaLoading').hide();
        });
    });
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
                    let botonesEstado = '';

                    if (row.estado == 0) {
                        botonesEstado = `
                            <span id="causarnomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-primary causar-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Causar</span>
                            <span id="causandonomina_${row.id}" class="badge bg-gradient-danger" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                                <b style="opacity: 0.3; text-transform: math-auto;">Causar</b>
                                <i style="position: absolute; color: white; font-size: 15px; margin-left: -32px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                            </span>
                        `;
                    } else if (row.estado == 1) {
                        botonesEstado = `
                            <span id="descausarnomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger descausar-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Descausar</span>
                            <span id="descausandonomina_${row.id}" class="badge bg-gradient-danger" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                                <b style="opacity: 0.3; text-transform: math-auto;">Descausar</b>
                                <i style="position: absolute; color: white; font-size: 15px; margin-left: -35px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                            </span>
                        `;
                    }

                    const botonDetalle = `
                        <span id="detallenomina_${row.id}_${row.id_empleado}" href="javascript:void(0)" class="btn badge bg-gradient-success detalle-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Ver detalle</span>
                        <span id="detallandonomina_${row.id}" class="badge bg-gradient-success" style="margin-bottom: 0rem !important; min-width: 50px; display: none;">
                            <b style="opacity: 0.3; text-transform: math-auto;">Ver detalle</b>
                            <i style="position: absolute; color: white; font-size: 15px; margin-left: -35px; margin-top: -2px;" class="fas fa-spinner fa-spin"></i>
                        </span>
                    `;

                    const puedeEnviar = (row.estado == 1 || row.estado == 2);
                    const botonEnviar = puedeEnviar ? `
                        <span id="enviarnomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-info enviar-nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Enviar</span>
                    ` : '';

                    return `
                        ${botonesEstado}
                        ${botonDetalle}
                        ${botonEnviar}
                    `;
                }
            }
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

            $('.group-header-causar, .group-footer-causar').remove();

            data.each(function (row, i) {
                const groupKey = `${row.fecha_inicio_periodo_formatted} - ${row.fecha_fin_periodo_formatted}`;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-causar" style="background-color: white; font-weight: bold;">
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
                        <tr class="group-header-causar" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
                            <td colspan="9">
                                <div style="display: flex; align-items: center;">

                                    <div class="group-toggle" title="Clic para expandir o contraer" style="display: flex; align-items: center; white-space: nowrap;">
                                        <span class="toggle-btn">
                                            <i class="fas fa-minus-square toggle-icon"></i>
                                        </span>
                                        <b style="font-weight: 500; font-size: 14px;">Periodo desde <b>${row.fecha_inicio_periodo_formatted}</b> hasta <b>${row.fecha_fin_periodo_formatted}</b></b>
                                        <span class="group-hint">(clic para expandir/contraer)</span>
                                    </div>

                                    <div style="margin-left: 25px; white-space: nowrap;">
                                        <label class="causar-chip" onclick="event.stopPropagation()" title="Marcar para causar el periodo">
                                            <input class="check-causar-periodo" type="checkbox" data-periodo-ids="${uniquePeriodIds.join(',')}">
                                            <span>Seleccionar Periodo (${uniquePeriodIds.length} empleados)</span>
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
                    <tr class="group-footer-causar" style="background-color: white; font-weight: bold;">
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
            $('.group-header-causar').off('click').on('click', function (e) {
                const target = $(e.target);
                if (target.hasClass('form-check-input') || target.closest('.form-check-label').length) {
                    return; 
                }

                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-causar')) {
                    if (!$next.hasClass('group-footer-causar')) $next.toggle();
                    $next = $next.next();
                }

                $icon.toggleClass('fa-minus-square fa-plus-square');
            });

            setTimeout(function() {
                updateCausarNominaButton();
            }, 100);
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
            url: base_url + 'detalle-periodo',
            data: function ( d ) {
                d.id_periodo_pago = id_periodo_pago;
                d.id_empleado = id_empleado_causar
            }
        },
        columns: [
            {
                data: 'concepto',
                defaultContent: '',
                render: function (data, type, row) {
                    if (!data) return '';
                    const codigo = data.codigo || '';
                    const nombre = data.nombre || '';
                    if (!codigo && !nombre) return '';
                    return codigo ? `${codigo} - ${nombre}` : nombre;
                }
            },

            // DEVENGADO
            {
                data: 'devengados',
                className: 'td-dev',
                render: function (d, type) {
                    const v = parseFloat(d) || 0;
                    if (type !== 'display') return v;
                    return v > 0 ? ppMoney.format(v) : '<span class="td-zero">—</span>';
                }
            },

            // DEDUCCIÓN (viene negativa del backend, se muestra en positivo)
            {
                data: 'deducciones',
                className: 'td-ded',
                render: function (d, type) {
                    const v = Math.abs(parseFloat(d) || 0);
                    if (type !== 'display') return v;
                    return v > 0 ? ppMoney.format(v) : '<span class="td-zero">—</span>';
                }
            },

            // HORAS (tipo_unidad 0)
            {
                data: 'unidades',
                className: 'text-center',
                render: function (d, type, row) {
                    return (type === 'display' && row.tipo_unidad == 0 && d) ? d : '';
                }
            },

            // DÍAS (tipo_unidad 1)
            {
                data: 'unidades',
                className: 'text-center',
                render: function (d, type, row) {
                    return (type === 'display' && row.tipo_unidad == 1 && d) ? d : '';
                }
            },

            { data: 'observacion', defaultContent: '' },

            // PORCENTAJE
            {
                data: 'porcentaje',
                className: 'dt-body-right',
                render: function (d, type) {
                    const v = parseFloat(d);
                    if (type !== 'display') return v || 0;
                    return v ? v + '%' : '';
                }
            },

            // BASE
            {
                data: 'base',
                className: 'dt-body-right',
                render: function (d, type) {
                    const v = parseFloat(d);
                    if (type !== 'display') return v || 0;
                    return v ? ppMoney.format(v) : '';
                }
            },

            { data: 'fecha_inicio', defaultContent: '' },
            { data: 'fecha_fin',    defaultContent: '' },
            { data: 'hora_inicio',  defaultContent: '' },
            { data: 'hora_fin',     defaultContent: '' }
        ],
        columnDefs: [
            { targets: 2, className: 'td-dev',
            render: (d, type) => type === 'display'
                ? (parseFloat(d) ? ppMoney.format(d) : '<span class="td-zero">—</span>') : d },
            { targets: 3, className: 'td-ded',
            render: (d, type) => type === 'display'
                ? (parseFloat(d) ? ppMoney.format(Math.abs(d)) : '<span class="td-zero">—</span>') : d },
        ]
    });

    if (causar_nomina_table) {
        causar_nomina_table.on('click', '.calcular-nomina', function() {
            const id = this.id.split('_')[1];
            const data = getDataById(id, causar_nomina_table);

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

        causar_nomina_table.on('click', '.detalle-nomina', function () {

            const id = this.id.split('_')[1];
            const id_empleado = this.id.split('_')[2];

            $(`#detallenomina_${id}_${id_empleado}`).hide();
            $(`#detallandonomina_${id}`).show();

            id_periodo_pago = id;
            id_empleado_causar = id_empleado;

            periodo_pago_detalle_table.ajax.reload(function (res) {
                $(`#detallenomina_${id}_${id_empleado}`).show();
                $(`#detallandonomina_${id}`).hide();

                if (!res.success) {
                    agregarToast('error', 'Consulta errada', 'Error al consultar detalle');
                    return;
                }

                if (!res.data.length) {
                    agregarToast('info', 'Periodo sin detalles', 'El periodo no tiene detalles');
                    return;
                }

                const periodo  = res.data[0].periodo_pago || {};
                const emp      = res.empleado || periodo.empleado || {};
                const t        = res.totales;
                const nombre   = emp.nombre_completo || '—';
                const dv       = emp.digito_verificacion ? `-${emp.digito_verificacion}` : '';

                // Empleado
                const $img = $('#pp_avatar_img');
                $('#pp_avatar_initials').text(ppIniciales(nombre));
                $img.hide().off('load error');

                if (emp.logo_nit) {
                    $img.on('load', function () { $(this).show(); })
                        .on('error', function () { $(this).hide(); })
                        .attr('src', bucketUrl + emp.logo_nit);
                } else {
                    $img.removeAttr('src');
                }
                $('#pp_nombre').text(nombre);
                $('#pp_documento').text(`${emp.numero_documento || '—'}${dv}`);
                $('#pp_email').text(emp.email || 'Sin correo');
                $('#pp_telefono').text(emp.telefono_1 || 'Sin teléfono');

                // Contrato
                const c = emp.contrato_actual || {};
                $('#pp_tipo_contrato').text(ppTermino[c.termino] || '—');
                $('#pp_tipo_salario').text(ppTipoSalario[c.tipo_salario] ?? '—');
                $('#pp_salario_base').text(c.salario ? ppMoney.format(c.salario) : '—');
                $('#pp_inicio_contrato').text(ppFecha(c.fecha_inicio_contrato));

                // Estado (ajusta el mapeo a los valores reales de tu campo "estado")
                const estados = { 0: ['Pendiente', 'pend'], 1: ['Causado', 'ok'] };
                const [txt, cls] = estados[periodo.estado] || ['—', ''];
                $('#pp_estado').text(txt).removeClass('ok pend').addClass(cls);

                // Totales
                ppCountUp('#devengado_detalle_total', t.devengados);
                ppCountUp('#deduccion_detalle_total', t.deducciones);
                ppCountUp('#neto_detalle_total', t.neto);
                $('#pp_footer_neto').text(ppMoney.format(t.neto));

                const pctDed = t.devengados > 0 ? (t.deducciones / t.devengados) * 100 : 0;
                $('#pp_pct_deduccion').text(`${pctDed.toFixed(1)}% del devengado`);
                $('#pp_bar_ded').css('width', pctDed + '%');
                $('#pp_bar_net').css('width', (100 - pctDed) + '%');

                $('#periodoPagoDetalleModal').modal('show');
                setTimeout(() => $('#periodoPagoDetalleTable').DataTable().columns.adjust(), 250);
            });
        });

        causar_nomina_table.on('click', '.causar-nomina', function() {
            const id = this.id.split('_')[1];

            $(`#causarnomina_${id}`).hide();
            $(`#causandonomina_${id}`).show();

            $('#causarNominaBtn').hide();
            $('#causarNominaLoading').show();

            // Llamada AJAX para causar nómina
            $.ajax({
                url: base_url + 'causar-nomina',
                method: 'POST',
                data: JSON.stringify({
                    ids_periodos_pago: [id],
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    agregarToast('exito', 'Causación exitosa', 'Nómina causada con éxito!', true);
                    causar_nomina_table.ajax.reload();
                } else {
                    agregarToast('error', 'Causación errada', res.message);
                }
            }).fail((err) => {
                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Causación errada', errorsMsg);
            }).always(() => {
                $('#causarNominaBtn').show();
                $('#causarNominaLoading').hide();

                $(`#causarnomina_${id}`).show();
                $(`#causandonomina_${id}`).hide();
            });
        });

        causar_nomina_table.on('click', '.descausar-nomina', function() {
            const id = this.id.split('_')[1];

            $(`#descausarnomina_${id}`).hide();
            $(`#descausandonomina_${id}`).show();

            // Llamada AJAX para causar nómina
            $.ajax({
                url: base_url + 'descausar-nomina',
                method: 'POST',
                data: JSON.stringify({
                    ids_periodos_pago: [id],
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    agregarToast('exito', 'Causación exitosa', 'Nómina causada con éxito!', true);
                    causar_nomina_table.ajax.reload();
                } else {
                    agregarToast('error', 'Causación errada', res.message);
                }
            }).fail((err) => {
                var mensaje = err.responseJSON.message;
                var errorsMsg = arreglarMensajeError(mensaje);
                agregarToast('error', 'Causación errada', errorsMsg);
            }).always(() => {
                $(`#descausarnomina_${id}`).show();
                $(`#descausandonomina_${id}`).hide();
            });
        });

        $('#pp_btn_print').on('click', () => window.print()); 
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

            // $('.group-header-causar').remove(); // Limpia grupos anteriores
            $('.group-header-causar-prestaciones-sociales, .group-footer-causar-prestaciones-sociales').remove();

            data.each(function (row, i) {

                const groupKey = row.id_empleado;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-causar-prestaciones-sociales" style="background-color: white; font-weight: bold;">
                                <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                                <td colspan="4"></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header-causar-prestaciones-sociales" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
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
                    <tr class="group-footer-causar-prestaciones-sociales" style="background-color: white; font-weight: bold;">
                        <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                        <td colspan="4"></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-causar-prestaciones-sociales').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-causar-prestaciones-sociales')) {
                    if (!$next.hasClass('group-footer-causar-prestaciones-sociales')) $next.toggle();
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
            // $('.group-header-causar').remove(); // Limpia grupos anteriores
            $('.group-header-causar-seguridad_social, .group-footer-causar-seguridad_social').remove();

            data.each(function (row, i) {

                const groupKey = row.id_empleado;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-causar-seguridad_social" style="background-color: white; font-weight: bold;">
                                <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                                <td colspan="4"></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header-causar-seguridad_social" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
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
                    <tr class="group-footer-causar-seguridad_social" style="background-color: white; font-weight: bold;">
                        <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                        <td colspan="4"></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-causar-seguridad_social').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-causar-seguridad_social')) {
                    if (!$next.hasClass('group-footer-causar-seguridad_social')) $next.toggle();
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
            // $('.group-header-causar').remove(); // Limpia grupos anteriores
            $('.group-header-causar-parafiscales, .group-footer-causar-parafiscales').remove();

            data.each(function (row, i) {

                const groupKey = row.id_empleado;
                const $row = $(rows[i]);

                // Si se detecta nuevo grupo
                if (lastGroup !== groupKey) {
                    // Si había un grupo anterior, insertar su footer al final de sus datos
                    if (lastGroup !== null && $lastGroupRow) {
                        const footerRow = $(`
                            <tr class="group-footer-causar-parafiscales" style="background-color: white; font-weight: bold;">
                                <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                                <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                                <td colspan="4"></td>
                            </tr>
                        `);
                        $lastGroupRow.after(footerRow);
                    }

                    const groupRow = $(`
                        <tr class="group-header-causar-parafiscales" style="background-color: #d9e9ff !important; font-weight: bold; cursor: pointer;" data-group="${groupKey}">
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
                    <tr class="group-footer-causar-parafiscales" style="background-color: white; font-weight: bold;">
                        <td colspan="3" class="text-end" style="letter-spacing: 4px;">TOTALES</td>
                        <td class="text-end"><b style="color: #01a401;">${formatNumberWithSmallDecimals(provision)}</b></td>
                        <td colspan="4"></td>
                    </tr>
                `);
                $lastGroupRow.after(footerRow);
            }

            // Manejo de colapsar/expandir grupos
            $('.group-header-causar-parafiscales').off('click').on('click', function () {
                const $this = $(this);
                const $icon = $this.find('.toggle-icon');
                let $next = $this.next();

                while ($next.length && !$next.hasClass('group-header-causar-parafiscales')) {
                    if (!$next.hasClass('group-footer-causar-parafiscales')) $next.toggle();
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

const ppMoney = new Intl.NumberFormat('es-CO', {
    style: 'currency', currency: 'COP',
    minimumFractionDigits: 0, maximumFractionDigits: 2
});

const ppTipoSalario = { 0: 'Normal', 1: 'Honorarios', 2: 'Integral', 3: 'Servicios', 4: 'Practicante' };
const ppTermino = { 1: 'Término fijo', 2: 'Indefinido', 3: 'Obra o labor', 4: 'Aprendizaje', 5: 'Ocasional' };

function ppFecha(f) {
    if (!f) return '—';
    const [y, m, d] = f.substring(0, 10).split('-');
    const meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
    return `${d}-${meses[+m - 1]}-${y}`;
}

function ppCountUp(selector, target, duration = 700) {
    const el = document.querySelector(selector);
    const start = performance.now();
    function tick(now) {
        const p = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = ppMoney.format(target * eased);
        if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

function ppIniciales(nombre = '') {
    return nombre.split(' ').filter(Boolean).slice(0, 2).map(n => n[0]).join('').toUpperCase() || '--';
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
        activar_prestaciones.ajax.reload();
        return;
    }
    initPrestacionesSociales();
});

$(document).on('click', '#seguridad_social-tab', function () {
    if (seguridad_social_table) {
        seguridad_social_table.ajax.reload();
        return;
    }
    initSeguridadSocial();
});

$(document).on('click', '#parafiscales-tab', function () {
    if (parafiscales_table) {
        parafiscales_table.ajax.reload();
        return;
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

// ============================================================
// ENVIAR COMPROBANTES (individual y por periodo)
// Reemplaza TODO el bloque anterior de estilosEnviarNomina + $(document).on('click', '.enviar-nomina' ...)
// ============================================================

// Estilos del modal (se inyectan una sola vez)
function estilosEnviarNomina() {
    if (document.getElementById('pf-enviar-nomina-css')) return;
    const css = `
        .pf-envio-popup { border-radius: 14px !important; padding: 8px 8px 16px !important; }
        .pf-envio-popup .swal2-title { font-size: 18px; font-weight: 600; padding: 16px 16px 0; }
        .pf-envio-popup .swal2-html-container { margin: 8px 16px 0; }
        .pf-envio-popup .swal2-actions { margin-top: 18px; gap: 8px; }
        .pf-envio-popup .swal2-styled { font-size: 13px; padding: 8px 20px; border-radius: 8px; box-shadow: none !important; }

        .pf-envio-emp {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; margin-bottom: 14px;
            background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px; text-align: left;
        }
        .pf-envio-emp .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #eef3ff; color: #1c4587;
            display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;
        }
        .pf-envio-emp small { display: block; font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
        .pf-envio-emp strong { font-size: 14px; color: #1f2937; }
        .pf-envio-aviso {
            display: flex; align-items: flex-start; gap: 8px;
            font-size: 12px; color: #b45309; background: #fffbeb;
            border: 1px solid #fde68a; border-left: 4px solid #f59e0b;
            border-radius: 8px; padding: 8px 12px; margin-bottom: 14px; text-align: left;
        }
        .pf-envio-aviso i { margin-top: 1px; font-size: 13px; color: #d97706; }

        .pf-envio-canales { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .pf-envio-datos { display: flex; flex-direction: column; gap: 10px; margin: 14px 0 4px; text-align: left; }
        .pf-envio-datos .pf-campo { display: flex; flex-direction: column; gap: 4px; }
        .pf-envio-datos label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
        .pf-envio-datos .pf-input-wrap { display: flex; align-items: center; gap: 8px; border: 1.5px solid #e5e7eb; border-radius: 10px; padding: 8px 12px; background: #fff; transition: border-color .15s; }
        .pf-envio-datos .pf-input-wrap:focus-within { border-color: #1c4587; box-shadow: 0 0 0 3px rgba(28,69,135,.12); }
        .pf-envio-datos .pf-input-wrap i { color: #9ca3af; font-size: 14px; width: 16px; text-align: center; }
        .pf-envio-datos .pf-input-wrap input { border: 0; outline: 0; flex: 1; font-size: 14px; color: #1f2937; background: transparent; }
        .pf-envio-datos .pf-input-wrap input::placeholder { color: #cbd5e1; }
        .pf-envio-datos .pf-hint { font-size: 11px; color: #94a3b8; margin-top: 2px; }
        .pf-envio-datos .pf-hint.warn { color: #b45309; }
        .pf-canal { position: relative; margin: 0; cursor: pointer; }
        .pf-canal input { position: absolute; opacity: 0; pointer-events: none; }
        .pf-canal .box {
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            padding: 14px 8px; border: 1.5px solid #e5e7eb; border-radius: 10px;
            color: #6b7280; transition: all .15s ease;
        }
        .pf-canal .box i { font-size: 22px; }
        .pf-canal .box span { font-size: 13px; font-weight: 600; }
        .pf-canal .box .check {
            position: absolute; top: 8px; right: 8px; font-size: 14px; opacity: 0; transition: opacity .15s;
        }
        .pf-canal:hover .box { border-color: #cbd5e1; }
        .pf-canal input:checked + .box .check { opacity: 1; }
        .pf-canal.wa input:checked + .box { border-color: #25D366; background: #f0fdf4; color: #15803d; }
        .pf-canal.em input:checked + .box { border-color: #0d6efd; background: #eff6ff; color: #1d4ed8; }
        .pf-canal input:focus-visible + .box { box-shadow: 0 0 0 .2rem rgba(13,110,253,.25); }
        /* ===== Variante masiva ===== */
        .pf-envio-masivo .pf-envio-emp.grupo {
            background: linear-gradient(135deg, #eef3ff 0%, #f5f7fd 100%);
            border-color: #c7d2fe;
        }
        .pf-envio-masivo .pf-envio-emp.grupo .avatar {
            background: #1c4587; color: #fff; font-size: 16px;
            width: 42px; height: 42px;
        }
        .pf-envio-masivo .pf-envio-emp.grupo strong { font-size: 15px; }

        .pf-envio-lista {
            margin: 0 0 14px; padding: 10px 12px; text-align: left;
            background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 10px;
            max-height: 132px; overflow-y: auto;
        }
        .pf-envio-lista .pf-item {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 0; font-size: 13px; color: #374151;
            border-bottom: 1px dashed #e5e7eb;
        }
        .pf-envio-lista .pf-item:last-child { border-bottom: 0; }
        .pf-envio-lista .pf-item i { color: #9ca3af; font-size: 12px; }
        .pf-envio-lista .pf-item .doc {
            font-family: 'Courier New', monospace; font-size: 12px; color: #6b7280; margin-left: auto;
        }
        .pf-envio-lista .pf-more {
            padding-top: 6px; font-size: 12px; color: #6b7280; font-style: italic;
        }

        .pf-envio-canales.masivo { grid-template-columns: 1fr 1fr; gap: 12px; }
        .pf-envio-canales.masivo .pf-canal .box { padding: 18px 10px; }
        .pf-envio-canales.masivo .pf-canal .box i { font-size: 26px; }
        .pf-envio-canales.masivo .pf-canal .box span { font-size: 14px; }
        .pf-envio-canales.masivo .pf-canal .box .count {
            display: block; font-size: 11px; font-weight: 500; color: #94a3b8; margin-top: 2px;
        }
    `;
    const style = document.createElement('style');
    style.id = 'pf-enviar-nomina-css';
    style.textContent = css;
    document.head.appendChild(style);
}

// Modal reutilizable. Devuelve una promesa con el arreglo de canales elegidos, o null si cancela.
function modalEnvioComprobante(opts = {}) {
    estilosEnviarNomina();

    const {
        titulo = 'Enviar comprobante',
        etiqueta,
        detalle,
        icono = 'fa-user',
        aviso = '',
        telefono = '',
        email = '',
        permitirEditar = true,
        destinatarios = null, // <-- NUEVO: array de empleados para masivo
        omitidos = 0,
    } = opts;

    const esMasivo = Array.isArray(destinatarios);

    // ---------- Cabecera ----------
    let cabecera;
    if (esMasivo) {
        cabecera = `
            <div class="pf-envio-emp grupo">
                <div class="avatar"><i class="fas fa-users"></i></div>
                <div>
                    <small>Destinatarios</small>
                    <strong>${destinatarios.length} empleado${destinatarios.length === 1 ? '' : 's'}</strong>
                </div>
            </div>
        `;
    } else {
        cabecera = `
            <div class="pf-envio-emp">
                <div class="avatar"><i class="fas ${icono}"></i></div>
                <div>
                    <small>${etiqueta}</small>
                    <strong>${detalle}</strong>
                </div>
            </div>
        `;
    }

    // ---------- Lista de destinatarios (solo masivo) ----------
    let lista = '';
    if (esMasivo && destinatarios.length) {
        const MAX = 4;
        const visibles = destinatarios.slice(0, MAX);
        const restantes = destinatarios.length - visibles.length;

        lista = `
            <div class="pf-envio-lista">
                ${visibles.map((d) => `
                    <div class="pf-item">
                        <i class="fas fa-user-circle"></i>
                        <span>${d.nombre ?? 'Sin nombre'}</span>
                        <span class="doc">${d.documento ?? ''}</span>
                    </div>
                `).join('')}
                ${restantes > 0 ? `<div class="pf-more">y ${restantes} empleado${restantes === 1 ? '' : 's'} más…</div>` : ''}
            </div>
        `;
    }

    // ---------- Aviso de omitidos ----------
    const bloqueAviso = omitidos > 0
        ? `<div class="pf-envio-aviso">
               <i class="fas fa-exclamation-triangle"></i>
               <span>${omitidos} período${omitidos === 1 ? '' : 's'} sin causar ${omitidos === 1 ? 'se omite' : 'se omiten'} del envío.</span>
           </div>`
        : (aviso ? `<div class="pf-envio-aviso"><i class="fas fa-exclamation-triangle"></i><span>${aviso}</span></div>` : '');

    // ---------- Campos editables (solo individual) ----------
    const bloqueDatos = permitirEditar ? `
        <div class="pf-envio-datos">
            <div class="pf-campo">
                <label>Teléfono WhatsApp</label>
                <div class="pf-input-wrap">
                    <i class="fab fa-whatsapp" style="color:#25D366;"></i>
                    <input type="tel" id="envio-telefono" placeholder="Ej: 3001234567" value="${telefono || ''}">
                </div>
                <div class="pf-hint">${telefono ? '' : 'El empleado no tiene teléfono registrado.'}</div>
            </div>
            <div class="pf-campo">
                <label>Correo electrónico</label>
                <div class="pf-input-wrap">
                    <i class="fas fa-envelope" style="color:#0d6efd;"></i>
                    <input type="email" id="envio-email" placeholder="correo@ejemplo.com" value="${email || ''}">
                </div>
                <div class="pf-hint">${email ? '' : 'El empleado no tiene correo registrado.'}</div>
            </div>
        </div>
    ` : '';

    // ---------- Canales ----------
    const claseCanales = esMasivo ? 'pf-envio-canales masivo' : 'pf-envio-canales';
    const labelWa = esMasivo
        ? `<span>WhatsApp</span><span class="count">${destinatarios.length} destinatarios</span>`
        : `<span>WhatsApp</span>`;
    const labelEm = esMasivo
        ? `<span>Correo</span><span class="count">${destinatarios.length} destinatarios</span>`
        : `<span>Correo</span>`;

    return Swal.fire({
        title: titulo,
        html: `
            <div class="${esMasivo ? 'pf-envio-masivo' : ''}">
                ${cabecera}
                ${lista}
                ${bloqueAviso}
                ${bloqueDatos}
                <div class="${claseCanales}">
                    <label class="pf-canal wa">
                        <input type="checkbox" id="canal-whatsapp" checked>
                        <div class="box">
                            <i class="fab fa-whatsapp"></i>
                            ${labelWa}
                            <i class="fas fa-check-circle check"></i>
                        </div>
                    </label>
                    <label class="pf-canal em">
                        <input type="checkbox" id="canal-email" checked>
                        <div class="box">
                            <i class="fas fa-envelope"></i>
                            ${labelEm}
                            <i class="fas fa-check-circle check"></i>
                        </div>
                    </label>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#1c4587',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        focusConfirm: false,
        customClass: { popup: 'pf-envio-popup' },
        didOpen: () => {
            const tel = document.getElementById('envio-telefono');
            if (tel) {
                tel.addEventListener('input', () => {
                    tel.value = tel.value.replace(/[^\d+]/g, '');
                });
            }
        },
        preConfirm: () => {
            const canales = [];
            const tel  = document.getElementById('envio-telefono');
            const mail = document.getElementById('envio-email');
            const telVal  = tel ? tel.value.trim() : '';
            const mailVal = mail ? mail.value.trim() : '';

            if (document.getElementById('canal-whatsapp').checked) {
                if (permitirEditar && tel && !telVal) {
                    Swal.showValidationMessage('Ingresa un teléfono para WhatsApp');
                    return false;
                }
                if (telVal && !/^\d{10,15}$/.test(telVal.replace(/\D/g, ''))) {
                    Swal.showValidationMessage('El teléfono debe tener entre 10 y 15 dígitos');
                    return false;
                }
                canales.push('whatsapp');
            }

            if (document.getElementById('canal-email').checked) {
                if (permitirEditar && mail && !mailVal) {
                    Swal.showValidationMessage('Ingresa un correo electrónico');
                    return false;
                }
                if (mailVal && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mailVal)) {
                    Swal.showValidationMessage('El correo no es válido');
                    return false;
                }
                canales.push('email');
            }

            if (!canales.length) {
                Swal.showValidationMessage('Selecciona al menos un medio de envío');
                return false;
            }

            return { canales, telefono: telVal, email: mailVal };
        },
    }).then((result) => (result.isConfirmed ? result.value : null));
}

// ---------- Enviar a UN empleado (boton "Enviar" de la fila) ----------
$(document).on('click', '.enviar-nomina', function () {
    const id = this.id.split('_')[1];
    const data = getDataById(id, causar_nomina_table);
    console.log('data: ',data);
    modalEnvioComprobante({
        etiqueta: 'Empleado',
        detalle: data.empleado?.nombre_completo ?? 'Sin nombre',
        icono: 'fa-user',
        telefono: data.empleado?.telefono_1 ?? null,
        email: data.empleado?.email ?? null
    }).then((canales) => {
        if (!canales) return;
        enviarComprobanteNomina([id], data.id_empleado, canales);
    });
});

// ---------- Enviar a TODOS los periodos seleccionados (check del encabezado del periodo) ----------
$(document).on('click', '#enviarNominaBtn', function () {
    const seleccionados = getSelectedPeriodIds();

    if (!seleccionados.length) {
        agregarToast('warning', 'Selección requerida', 'Selecciona al menos un periodo para enviar');
        return;
    }

    // Solo se pueden enviar comprobantes de periodos causados (1) o pagados (2)
    const filas = causar_nomina_table.rows().data().toArray();
    const porId = {};
    filas.forEach((r) => { porId[String(r.id)] = r; });

    const enviables = seleccionados.filter((id) => {
        const r = porId[String(id)];
        return r && (r.estado == 1 || r.estado == 2);
    });
    const omitidos = seleccionados.length - enviables.length;

    if (!enviables.length) {
        agregarToast('warning', 'Sin comprobantes', 'Los periodos seleccionados aún no están causados');
        return;
    }

    modalEnvioComprobante({
        titulo: 'Enviar comprobantes',
        etiqueta: 'Destinatarios',
        detalle: `${enviables.length} empleado${enviables.length === 1 ? '' : 's'}`,
        icono: 'fa-users',
        aviso: omitidos > 0
            ? `${omitidos} sin causar ${omitidos === 1 ? 'se omite' : 'se omiten'}.`
            : '',
        permitirEditar: false,
    }).then((payload) => {
        if (!payload) return;
        enviarComprobanteNomina(enviables, null, payload.canales);
    });
});

function updateCausarNominaButton() {
    const seleccionados = $('.check-causar-periodo:checked');

    if (seleccionados.length > 0) {
        $('#causarNominaBtn').show();
        $('#enviarNominaBtn').show();
        $('#enviarNominaCount').text(getSelectedPeriodIds().length);
    } else {
        $('#causarNominaBtn').hide();
        $('#enviarNominaBtn').hide();
    }
}

function enviarComprobanteNomina(idsPeriodos, idEmpleado, canales, telefono = null, email = null) {
    const canalesArr = Array.isArray(canales) ? canales : [canales];
    const idsArr     = Array.isArray(idsPeriodos) ? idsPeriodos : [idsPeriodos];

    $.ajax({
        url: base_url + 'notificar-nomina',
        method: 'POST',
        headers: headers,
        dataType: 'json',
        data: JSON.stringify({
            ids_periodos_pago: idsArr,
            id_empleado: idEmpleado || null,
            canales: canalesArr,
            telefono: telefono || null,
            email: email || null,
        }),
    }).done((res) => {
        if (res.success) {
            agregarToast('exito', 'Envío exitoso', res.message || 'Comprobante enviado!', true);
        } else {
            agregarToast('error', 'Envío errado', res.message || 'No se pudo enviar');
        }
    }).fail((err) => {
        const msg = err.responseJSON?.message || 'Error de red';
        agregarToast('error', 'Envío errado', arreglarMensajeError(msg));
    });
}

// Función para obtener los IDs seleccionados de TODOS los grupos
function getSelectedPeriodIds() {
    const selectedIds = [];
    
    // Buscar todos los checkboxes de grupo seleccionados
    $('.check-causar-periodo:checked').each(function() {
        const periodoIds = $(this).data('periodo-ids');
        if (periodoIds) {
            // Convertir el string de IDs separados por comas en array
            const idsArray = periodoIds.split(',').map(id => id.trim());
            selectedIds.push(...idsArray);
        }
    });
    
    return selectedIds.filter(id => id !== '' && id !== undefined);
}

$(document).on('change', '.check-causar-periodo', function() {
    updateCausarNominaButton();
});

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