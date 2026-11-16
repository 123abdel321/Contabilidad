var novedades_generales_table = null;
var $comboNitNovedades = null;
var $comboConceptoNovedades = null;

var $comboPeriodoFilter = null;
var $comboNitNovedadesFilter = null;
var $comboConceptoNovedadesFilter = null;

// ============================================================
// UTILIDADES
// ============================================================
function pad2(n) { return String(n).padStart(2, '0'); }

function fechaHoyISO() {
    const d = new Date();
    return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
}

function horaAhoraHHMM() {
    const d = new Date();
    return `${pad2(d.getHours())}:${pad2(d.getMinutes())}`;
}

function sumarMinutos(hhmm, minutos) {
    if (!hhmm) return '';
    const [h, m] = hhmm.split(':').map(Number);
    const total = (h * 60 + m + minutos + 1440) % 1440;
    return `${pad2(Math.floor(total / 60))}:${pad2(total % 60)}`;
}

function diffHoras(hhmmDesde, hhmmHasta) {
    if (!hhmmDesde || !hhmmHasta) return 0;
    const [h1, m1] = hhmmDesde.split(':').map(Number);
    const [h2, m2] = hhmmHasta.split(':').map(Number);
    let diff = (h2 * 60 + m2) - (h1 * 60 + m1);
    if (diff < 0) diff += 1440; // cruza medianoche
    return diff / 60;
}

function sincronizarUnidadesConHoras() {
    const conceptoSel = $('#id_concepto_novedades_generales').select2('data')[0];
    if (!conceptoSel || conceptoSel.unidad != 0) return; // solo conceptos de tipo horas

    const hd = $('#hora_desde_novedades_generales').val();
    const hh = $('#hora_hasta_novedades_generales').val();
    if (!hd || !hh) return;

    const horas = diffHoras(hd, hh);
    if (horas > 0) {
        $('#unidades_novedades_generales').val(horas.toFixed(2));
    }
}

function sincronizarHorasConUnidades() {
    const conceptoSel = $('#id_concepto_novedades_generales').select2('data')[0];
    if (!conceptoSel || conceptoSel.unidad != 0) return; // solo tipo horas

    const hd = $('#hora_desde_novedades_generales').val();
    const unidades = parseFloat($('#unidades_novedades_generales').val());
    if (!hd || isNaN(unidades) || unidades <= 0) return;

    // Recalcula hora hasta = hora desde + unidades (en minutos)
    const minutos = Math.round(unidades * 60);
    $('#hora_hasta_novedades_generales').val(sumarMinutos(hd, minutos));
    // NOTA: sin .trigger('change') para evitar loop con sincronizarUnidadesConHoras
}

// ============================================================
// INIT
// ============================================================
function novedadesgeneralesInit() {
    cargarTablasNovedades();
    cargarSelect2Novedades();
    bindEventosNovedades();
}

// ============================================================
// DATATABLE
// ============================================================
function cargarTablasNovedades() {
    novedades_generales_table = $('#novedadesGeneralesTable').DataTable({
        pageLength: 30,
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
        fixedColumns: { left: 0, right: 1 },
        ajax: {
            type: "GET",
            headers: headers,
            url: base_url + 'novedades-generales',
            data: function (d) {
                d.id_empleado = $('#id_empleado_novedades_generales_filter').val();
                d.id_periodo_pago = $('#id_periodo_pago_novedades_generales_filter').val();
                d.id_concepto = $('#id_concepto_novedades_generales_filter').val();
            }
        },
        columns: [
            { "data": row => row.periodo_pago
                ? `${row.periodo_pago.fecha_inicio_periodo} al ${row.periodo_pago.fecha_fin_periodo}`
                : 'SIN PERIODO' },
            { "data": row => row.empleado
                ? `${row.empleado.numero_documento} - ${row.empleado.nombre_completo}`
                : 'SIN EMPLEADO' },
            { "data": row => row.concepto
                ? `${row.concepto.codigo} - ${row.concepto.nombre}`
                : 'SIN CONCEPTO' },
            { "data": row => {
                if (row.tipo_unidad == 0) return 'HORAS';
                if (row.tipo_unidad == 1) return 'DÍAS';
                if (row.tipo_unidad == 3) return 'VALOR FIJO';
                return '';
            }},
            { "data": 'unidades' },
            { "data": 'valor', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { "data": row => parseFloat(row.porcentaje) },
            { "data": 'base', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { "data": 'observacion' },
            { "data": 'fecha_inicio' },
            { "data": 'fecha_fin' },
            { "data": 'hora_inicio' },
            { "data": 'hora_fin' },
            { "data": row => {
                if (!row.created_by && !row.fecha_creacion) return '';
                const icon = row.created_by ? 'fa-user icon-user' : 'fa-user-times icon-user-none';
                const cls = row.created_by ? 'button-user' : '';
                const onclick = row.created_by ? `onclick="showUser(${row.created_by},\`${row.fecha_creacion}\`,0)"` : '';
                return `<div class="${cls}" ${onclick}><i class="fas ${icon}"></i>&nbsp;${row.fecha_creacion || ''}</div>`;
            }},
            { "data": row => {
                if (!row.updated_by && !row.fecha_edicion) return '';
                const icon = row.updated_by ? 'fa-user icon-user' : 'fa-user-times icon-user-none';
                const cls = row.updated_by ? 'button-user' : '';
                const onclick = row.updated_by ? `onclick="showUser(${row.updated_by},\`${row.fecha_edicion}\`,0)"` : '';
                return `<div class="${cls}" ${onclick}><i class="fas ${icon}"></i>&nbsp;${row.fecha_edicion || ''}</div>`;
            }},
            {
                "data": row => {
                    let html = '';
                    if (editarNovedadesGenerales) {
                        html += `<span id="editnovedadesgenerales_${row.id}" class="btn badge bg-gradient-success edit-novedadesgenerales"
                                       style="min-width:50px; cursor:pointer;margin-bottom: 0px !important;">Editar</span>&nbsp;`;
                    }
                    if (eliminarNovedadesGenerales) {
                        html += `<span id="deletenovedadesgenerales_${row.id}" class="btn badge bg-gradient-danger drop-novedadesgenerales"
                                       style="min-width:50px; cursor:pointer;margin-bottom: 0px !important;">Eliminar</span>`;
                    }
                    return html;
                }
            },
        ]
    });

    if (novedades_generales_table) {
        novedades_generales_table.on('click', '.edit-novedadesgenerales', function () {
            clearFormNovedadesGenerales();

            $("#textCreateNovedadesGenerales").hide();
            $("#textEditarNovedadesGenerales").show();
            $("#saveNovedadesGeneralesLoading").hide();
            $("#updateNovedadesGenerales").show();
            $("#saveNovedadesGenerales").hide();

            const id = this.id.split('_')[1];
            const data = getDataById(id, novedades_generales_table);

            $("#id_novedades_generales_up").val(data.id);

            if (data.empleado) {
                const opt = new Option(`${data.empleado.numero_documento} - ${data.empleado.nombre_completo}`,
                                       data.empleado.id, false, false);
                $comboNitNovedades.append(opt).val(data.empleado.id).trigger('change');
            }
            if (data.concepto) {
                const opt = new Option(`${data.concepto.codigo} - ${data.concepto.nombre}`,
                                       data.concepto.id, false, false);
                $comboConceptoNovedades.append(opt).val(data.concepto.id).trigger('change');
            }

            $("#unidades_novedades_generales").val(data.unidades);
            $("#valor_novedades_generales").val(data.valor);
            $("#porcentaje_novedades_generales").val(data.porcentaje);
            $("#fecha_desde_novedades_generales").val(data.fecha_inicio);
            $("#fecha_hasta_novedades_generales").val(data.fecha_fin);
            $("#hora_desde_novedades_generales").val(data.hora_inicio);
            $("#hora_hasta_novedades_generales").val(data.hora_fin);

            mostrarDatosNovedadesGenerales(data.concepto);
            actualizarResumenNovedad();

            $("#novedadesGeneralesFormModal").modal('show');
        });

        novedades_generales_table.on('click', '.drop-novedadesgenerales', function () {
            const id = this.id.split('_')[1];
            const data = getDataById(id, novedades_generales_table);

            Swal.fire({
                title: `Eliminar novedad: ${data.concepto.nombre} <br> Empleado: ${data.empleado.nombre_completo}?`,
                text: "No se podrá revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: base_url + 'novedades-generales',
                        method: 'DELETE',
                        data: JSON.stringify({ id: id }),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if (res.success) {
                            novedades_generales_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Novedad eliminada con éxito!', true);
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.responseJSON?.message || 'Error');
                    });
                }
            });
        });
    }

    novedades_generales_table.ajax.reload();
}

// ============================================================
// SELECT2
// ============================================================
function cargarSelect2Novedades() {
    const commonLang = {
        noResults: () => "No hay resultado",
        searching: () => "Buscando...",
        inputTooShort: () => "Introduce 1 o más caracteres"
    };

    $comboNitNovedades = $('#id_empleado_novedades_generales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#novedadesGeneralesFormModal'),
        language: commonLang,
        ajax: {
            url: 'api/nit/empleado-activo',
            headers: headers,
            dataType: 'json',
            data: params => ({ search: params.term }),
            processResults: data => ({ results: data.data })
        }
    });

    $comboConceptoNovedades = $('#id_concepto_novedades_generales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#novedadesGeneralesFormModal'),
        language: commonLang,
        ajax: {
            url: 'api/conceptos-combo',
            headers: headers,
            dataType: 'json',
            processResults: data => ({ results: data.data })
        }
    });

    $comboNitNovedadesFilter = $('#id_empleado_novedades_generales_filter').select2({
        theme: 'bootstrap-5', delay: 250, allowClear: true,
        placeholder: "Seleccione un empleado a filtrar",
        language: commonLang,
        ajax: {
            url: 'api/nit/empleado-activo', headers: headers, dataType: 'json',
            data: params => ({ search: params.term }),
            processResults: data => ({ results: data.data })
        }
    });

    $comboPeriodoFilter = $('#id_periodo_pago_novedades_generales_filter').select2({
        theme: 'bootstrap-5', delay: 250, allowClear: true,
        placeholder: "Seleccione un periodo a filtrar",
        language: commonLang,
        ajax: {
            url: 'api/periodos-pagos-combo', headers: headers, dataType: 'json',
            processResults: data => ({ results: data.data })
        }
    });

    $comboConceptoNovedadesFilter = $('#id_concepto_novedades_generales_filter').select2({
        theme: 'bootstrap-5', delay: 250, allowClear: true,
        placeholder: "Seleccione un concepto a filtrar",
        language: commonLang,
        ajax: {
            url: 'api/conceptos-combo', headers: headers, dataType: 'json',
            processResults: data => ({ results: data.data })
        }
    });
}

// ============================================================
// FORM: limpiar / mostrar
// ============================================================
function clearFormNovedadesGenerales() {
    $("#textCreateNovedadesGenerales").show();
    $("#textEditarNovedadesGenerales").hide();
    $("#saveNovedadesGenerales").show();
    $("#updateNovedadesGenerales").hide();
    $("#saveNovedadesGeneralesLoading").hide();
    $("#horasResumen").hide();

    const hoy = fechaHoyISO();

    // Ocultar todos los divs condicionales
    $('#div-unidades_novedades_generales, #div-valor_novedades_generales, #div-porcentaje_novedades_generales,'
      + '#div-fecha_desde_novedades_generales, #div-fecha_hasta_novedades_generales,'
      + '#div-hora_desde_novedades_generales, #div-hora_hasta_novedades_generales').hide();

    // Reset valores
    $("#id_empleado_novedades_generales").val(null).trigger('change');
    $("#id_concepto_novedades_generales").val(null).trigger('change');
    $("#unidades_novedades_generales").val(0);
    $("#valor_novedades_generales").val(0);
    $("#porcentaje_novedades_generales").val(0);
    $("#fecha_desde_novedades_generales").val(hoy);
    $("#fecha_hasta_novedades_generales").val(hoy);
    $("#hora_desde_novedades_generales").val('');
    $("#hora_hasta_novedades_generales").val('');
    $("#id_novedades_generales_up").val('');

    // Quitar required
    ['#fecha_desde_novedades_generales', '#fecha_hasta_novedades_generales',
     '#hora_desde_novedades_generales', '#hora_hasta_novedades_generales',
     '#unidades_novedades_generales', '#valor_novedades_generales'].forEach(sel => {
        $(sel).prop('required', false);
    });

    // Quitar validación previa
    $('#NovedadesGeneralesForm').removeClass('was-validated');

    actualizarResumenNovedad();
}

function mostrarDatosNovedadesGenerales(novedadGeneral) {
    const fechaHora = ['heds','hens','hrns','heddfs','hrddfs','hendfs','hrndfs'];
    const fecha = ['vacaciones_comunes','vacaciones_compensadas','incapacidades',
                   'licencia_mp','licencia_r','licencia_nr','huelgas_legales'];

    const unidadesDiv = $("#div-unidades_novedades_generales");
    const valorDiv = $("#div-valor_novedades_generales");
    const porcentajeDiv = $("#div-porcentaje_novedades_generales");
    const fechaDesdeDiv = $("#div-fecha_desde_novedades_generales");
    const fechaHastaDiv = $("#div-fecha_hasta_novedades_generales");
    const horaDesdeDiv = $("#div-hora_desde_novedades_generales");
    const horaHastaDiv = $("#div-hora_hasta_novedades_generales");
    const unidadesLabel = $("#text-unidades_novedades_generales");

    // Reset first
    unidadesDiv.hide(); valorDiv.hide(); porcentajeDiv.hide();
    fechaDesdeDiv.hide(); fechaHastaDiv.hide();
    horaDesdeDiv.hide(); horaHastaDiv.hide();

    if (novedadGeneral.unidad == 0) {
        unidadesDiv.show();
        unidadesLabel.html("Horas <span style='color: red'>*</span>");
        $("#unidades_novedades_generales").prop("required", true);
    }
    if (novedadGeneral.unidad == 1) {
        unidadesDiv.show();
        unidadesLabel.html("Días <span style='color: red'>*</span>");
        $("#unidades_novedades_generales").prop("required", true);
    }
    if (novedadGeneral.unidad == 2) {
        valorDiv.show();
        $("#valor_novedades_generales").prop("required", true);
    }
    if (novedadGeneral.porcentaje) {
        porcentajeDiv.show();
        $("#porcentaje_novedades_generales").val(parseFloat(novedadGeneral.porcentaje));
    }
    if (fechaHora.includes(novedadGeneral.tipo_concepto)) {
        fechaDesdeDiv.show(); fechaHastaDiv.show();
        horaDesdeDiv.show(); horaHastaDiv.show();
        ['#fecha_desde_novedades_generales', '#fecha_hasta_novedades_generales',
         '#hora_desde_novedades_generales', '#hora_hasta_novedades_generales']
            .forEach(sel => $(sel).prop('required', true));
    } else if (fecha.includes(novedadGeneral.tipo_concepto)) {
        fechaDesdeDiv.show(); fechaHastaDiv.show();
        horaDesdeDiv.hide(); horaHastaDiv.hide();
        ['#fecha_desde_novedades_generales', '#fecha_hasta_novedades_generales']
            .forEach(sel => $(sel).prop('required', true));
    }

    actualizarResumenNovedad();
}

// ============================================================
// RESUMEN EN VIVO
// ============================================================
function actualizarResumenNovedad() {
    // Empleado
    const empleadoData = $('#id_empleado_novedades_generales').select2('data')[0];
    $('#summaryEmpleado').text(empleadoData ? empleadoData.text : '—');

    // Concepto
    const conceptoData = $('#id_concepto_novedades_generales').select2('data')[0];
    $('#summaryConcepto').text(conceptoData ? conceptoData.text : '—');

    // Duración
    const fd = $('#fecha_desde_novedades_generales').val();
    const fh = $('#fecha_hasta_novedades_generales').val();
    const hd = $('#hora_desde_novedades_generales').val();
    const hh = $('#hora_hasta_novedades_generales').val();
    let dur = '—';
    if (hd && hh) {
        const horas = diffHoras(hd, hh);
        if (horas > 0) {
            dur = `${horas.toFixed(2)} h`;
            $('#horasResumen').show();
            $('#horasResumenValor').text(horas.toFixed(2));
        } else {
            $('#horasResumen').hide();
        }
    } else {
        $('#horasResumen').hide();
    }
    if (dur === '—' && fd && fh) {
        const d1 = new Date(fd), d2 = new Date(fh);
        const dias = Math.round((d2 - d1) / 86400000) + 1;
        if (dias > 0) dur = `${dias} día(s)`;
    }
    $('#summaryDuracion').text(dur);
}

// ============================================================
// EVENTOS
// ============================================================
// ============================================================
// EVENTOS
// ============================================================
function bindEventosNovedades() {
    // Crear
    $(document).on('click', '#createNovedadGeneral', function () {
        clearFormNovedadesGenerales();
        $("#novedadesGeneralesFormModal").modal('show');
    });

    // Al cambiar concepto
    $(document).on('change', '#id_concepto_novedades_generales', function () {
        const novedadGeneral = $('#id_concepto_novedades_generales').select2('data')[0];
        if (!novedadGeneral) return;
        mostrarDatosNovedadesGenerales(novedadGeneral);
    });

    // Actualizar resumen al cambiar cualquier campo
    $(document).on('change input', '#id_empleado_novedades_generales, #id_concepto_novedades_generales, '
        + '#fecha_desde_novedades_generales, #fecha_hasta_novedades_generales, '
        + '#hora_desde_novedades_generales, #hora_hasta_novedades_generales', function () {
        sincronizarUnidadesConHoras();
        actualizarResumenNovedad();
    });

    // Editar Horas manualmente -> recalcular hora hasta
    $(document).on('change', '#unidades_novedades_generales', function () {
        sincronizarHorasConUnidades();
        actualizarResumenNovedad();
    });

    // Botón "Hoy"
    $(document).on('click', '.btn-set-hoy', function () {
        const t = $(this).data('target');
        $('#' + t).val(fechaHoyISO()).trigger('change');
        actualizarResumenNovedad();
    });
    // Botón "Ayer"
    $(document).on('click', '.btn-set-ayer', function () {
        const t = $(this).data('target');
        const d = new Date(); d.setDate(d.getDate() - 1);
        $('#' + t).val(`${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`).trigger('change');
        actualizarResumenNovedad();
    });
    // Botón "Lunes"
    $(document).on('click', '.btn-set-lunes', function () {
        const t = $(this).data('target');
        const d = new Date();
        const day = d.getDay(); // 0=dom, 1=lun...
        const diff = (day === 0 ? 6 : day - 1);
        d.setDate(d.getDate() - diff);
        $('#' + t).val(`${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`).trigger('change');
        actualizarResumenNovedad();
    });

    // Copiar fecha desde -> hasta
    $(document).on('click', '.btn-copy-fecha', function () {
        const from = $('#' + $(this).data('from')).val();
        $('#' + $(this).data('to')).val(from).trigger('change');
        actualizarResumenNovedad();
    });

    // Hora "Ahora"
    $(document).on('click', '.btn-hora-now', function () {
        const t = $(this).data('target');
        $('#' + t).val(horaAhoraHHMM()).trigger('change');
        sincronizarUnidadesConHoras();
        actualizarResumenNovedad();
    });

    // Hora preset
    $(document).on('click', '.btn-hora-set', function () {
        const t = $(this).data('target');
        $('#' + t).val($(this).data('hora')).trigger('change');
        sincronizarUnidadesConHoras();
        actualizarResumenNovedad();
    });

    // Sumar minutos a hora hasta
    $(document).on('click', '.btn-add-hora', function () {
        const t = $(this).data('target');
        const min = parseInt($(this).data('min'));
        const hd = $('#hora_desde_novedades_generales').val();
        if (!hd) return;
        $('#' + t).val(sumarMinutos(hd, min)).trigger('change');
        sincronizarUnidadesConHoras();
        actualizarResumenNovedad();
    });

    // Copiar hora desde -> hasta
    $(document).on('click', '.btn-copy-hora', function () {
        const from = $('#' + $(this).data('from')).val();
        $('#' + $(this).data('to')).val(from).trigger('change');
        sincronizarUnidadesConHoras();
        actualizarResumenNovedad();
    });

    // Atajo Ctrl+S para guardar/actualizar
    $(document).on('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            if ($('#novedadesGeneralesFormModal').is(':visible')) {
                e.preventDefault();
                if ($('#saveNovedadesGenerales').is(':visible')) {
                    $('#saveNovedadesGenerales').click();
                } else if ($('#updateNovedadesGenerales').is(':visible')) {
                    $('#updateNovedadesGenerales').click();
                }
            }
        }
    });

    // Guardar
    $(document).on('click', '#saveNovedadesGenerales', function () {
        const form = document.querySelector('#NovedadesGeneralesForm');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        const formDataObj = Object.fromEntries(new FormData(form).entries());

        $("#saveNovedadesGenerales").hide();
        $("#updateNovedadesGenerales").hide();
        $("#saveNovedadesGeneralesLoading").show();

        $.ajax({
            url: base_url + 'novedades-generales',
            method: 'POST',
            data: JSON.stringify(formDataObj),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if (res.success) {
                clearFormNovedadesGenerales();
                $("#saveNovedadesGenerales").show();
                $("#saveNovedadesGeneralesLoading").hide();
                $("#novedadesGeneralesFormModal").modal('hide');
                novedades_generales_table.ajax.reload();
                agregarToast('exito', 'Creación exitosa', 'Novedad general creada con éxito!', true);
            }
        }).fail((err) => {
            $('#saveNovedadesGenerales').show();
            $('#saveNovedadesGeneralesLoading').hide();
            const msg = err.responseJSON?.message || 'Error desconocido';
            agregarToast('error', 'Creación errada', arreglarMensajeError(msg));
        });
    });

    // Actualizar
    $(document).on('click', '#updateNovedadesGenerales', function () {
        const form = document.querySelector('#NovedadesGeneralesForm');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        const formDataObj = Object.fromEntries(new FormData(form).entries());

        $("#saveNovedadesGenerales").hide();
        $("#updateNovedadesGenerales").hide();
        $("#saveNovedadesGeneralesLoading").show();

        $.ajax({
            url: base_url + 'novedades-generales',
            method: 'PUT',
            data: JSON.stringify(formDataObj),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if (res.success) {
                clearFormNovedadesGenerales();
                $("#saveNovedadesGenerales").show();
                $("#saveNovedadesGeneralesLoading").hide();
                $("#novedadesGeneralesFormModal").modal('hide');
                novedades_generales_table.ajax.reload();
                agregarToast('exito', 'Actualización exitosa', 'Novedad general actualizada con éxito!', true);
            }
        }).fail((err) => {
            $('#saveNovedadesGenerales').show();
            $('#saveNovedadesGeneralesLoading').hide();
            const msg = err.responseJSON?.message || 'Error desconocido';
            agregarToast('error', 'Actualización errada', arreglarMensajeError(msg));
        });
    });

    // Filtros
    $(document).on('change', '#id_empleado_novedades_generales_filter', () => novedades_generales_table.ajax.reload());
    $(document).on('change', '#id_periodo_pago_novedades_generales_filter', () => novedades_generales_table.ajax.reload());
    $(document).on('change', '#id_concepto_novedades_generales_filter', () => novedades_generales_table.ajax.reload());
}