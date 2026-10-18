var import_nits_table = null;

function importnitsInit() {
    import_nits_table = $('#importNits').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        language: lenguajeDatatable,
        sScrollX: "100%",
        ajax: {
            type: "GET",
            headers: headers,
            url: base_url + 'nits-cache-import',
        },
        rowCallback: function (row, data) {
            $('td', row).css('background-color', '');
            if (data.total_errores && parseInt(data.total_errores) > 0) {
                $('td', row).css('background-color', '#ff00005e');
            }
        },
        columns: [
            {
                "data": function (row) {
                    if (parseInt(row.total_errores) > 0) {
                        return `<span class="badge bg-danger" style="font-size: 11px; padding: 4px 8px;">
                            <i class="fas fa-exclamation-circle"></i> Fila ${row.id}: Errores
                        </span>`;
                    }
                    return `<span class="badge bg-success" style="font-size: 11px; padding: 4px 8px;">
                        <i class="fas fa-check"></i> Fila ${row.id}: Listo
                    </span>`;
                }
            },
            {
                "data": function (row) {
                    if (row.accion === 'actualizar') {
                        return `<span class="badge bg-warning text-dark" style="font-size: 11px; padding: 4px 8px;">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </span>`;
                    }
                    return `<span class="badge bg-info text-dark" style="font-size: 11px; padding: 4px 8px;">
                        <i class="fas fa-plus"></i> Crear
                    </span>`;
                }
            },
            { "data": 'tipo_documento' },
            { "data": 'numero_documento' },
            { "data": 'digito_verificacion' },
            { "data": 'primer_nombre' },
            { "data": 'otros_nombres' },
            { "data": 'primer_apellido' },
            { "data": 'segundo_apellido' },
            { "data": 'razon_social' },
            { "data": 'direccion' },
            { "data": 'ciudad_display' },   // 👈 NUEVO
            { "data": 'email' },
            { "data": 'telefono_1' },
            { "data": 'plazo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { "data": 'cupo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { "data": 'observaciones' },
            { "data": 'email_1' },
            { "data": 'email_2' },
            { "data": 'errores' },
            { "data": 'total_errores' }
        ]
    });
}

/* ============================================================
   Helpers
   ============================================================ */
function nitsSetBtnCargarNitsEnabled(enabled) {
    $('#actualizarPlantillaNits').prop('disabled', !enabled);
    if (enabled) {
        $('#actualizarPlantillaNits').show();
    } else {
        $('#actualizarPlantillaNits').hide();
    }
}

function nitsReloadTablaYHabilitarBoton() {
    import_nits_table.ajax.reload(function (res) {
        if (res.success && res.iTotalRecords > 0) {
            nitsSetBtnCargarNitsEnabled(true);
        } else {
            nitsSetBtnCargarNitsEnabled(false);
        }
    });
}

/* ============================================================
   Habilitar Paso 2 cuando hay archivo
   ============================================================ */
$(document).on('change', '#file_import_nits', function () {
    $('#cargarPlantillaNits').prop('disabled', !this.files.length);
});

/* ============================================================
   PASO 1: Descargar plantilla
   ============================================================ */
$(document).on('click', '#descargarPlantillaNits', function () {
    $.ajax({
        url: 'importnits-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done(function (res) {
        window.open(res.url, '_blank');
    }).fail(function () {
        agregarToast('error', 'Descarga errada', 'No se pudo generar la plantilla.', true);
    });
});

/* ============================================================
   PASO 2: Cargar plantilla a tabla temporal (validación en backend)
   ============================================================ */
$(document).on('click', '#cargarPlantillaNits', function () {
    if (!$('#file_import_nits')[0].files.length) {
        agregarToast('error', 'Sin archivo', 'Debes seleccionar un archivo Excel.', true);
        return;
    }

    $('#cargarPlantillaNits').hide();
    $('#cargarPlantillaNitsLoading').show();
    nitsSetBtnCargarNitsEnabled(false);

    if (import_nits_table) {
        import_nits_table.clear().draw();
    }

    var form = document.getElementById('form-importador-nits');
    var data = new FormData(form);
    var xhr = new XMLHttpRequest();

    xhr.open('POST', 'importnits-importar');
    xhr.send(data);

    xhr.onload = function (res) {
        $('#cargarPlantillaNits').show();
        $('#cargarPlantillaNitsLoading').hide();

        try {
            var responseData = JSON.parse(res.currentTarget.response);

            if (responseData.success) {
                nitsReloadTablaYHabilitarBoton();
                agregarToast('exito', 'Datos cargados', responseData.message, true);
            } else {
                var mensaje = responseData.message;
                var errorsMsg = typeof mensaje === 'string'
                    ? mensaje
                    : Object.values(mensaje).flat().join('<br/>');
                agregarToast('error', 'Carga errada', errorsMsg, true);
            }
        } catch (e) {
            agregarToast('error', 'Carga errada', 'Error al procesar la respuesta del servidor.', true);
        }
    };

    xhr.onerror = function () {
        $('#cargarPlantillaNits').show();
        $('#cargarPlantillaNitsLoading').hide();
        agregarToast('error', 'Carga errada', 'Error de red al cargar el archivo.', true);
    };
});

/* ============================================================
   PASO 3: Cargar NITs al sistema (crear / actualizar según check)
   ============================================================ */
$(document).on('click', '#actualizarPlantillaNits', function () {
    $('#actualizarPlantillaNits').hide();
    $('#actualizarPlantillaNitsLoading').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'nits-actualizar-import',
        headers: headers,
        dataType: 'json',
    }).done(function (res) {
        $('#actualizarPlantillaNits').show();
        $('#actualizarPlantillaNitsLoading').hide();

        nitsReloadTablaYHabilitarBoton();
        agregarToast('exito', 'NITs procesados', res.message, true);
    }).fail(function (err) {
        $('#actualizarPlantillaNits').show();
        $('#actualizarPlantillaNitsLoading').hide();

        var mensaje = err.responseJSON && err.responseJSON.message
            ? err.responseJSON.message
            : 'Error al procesar los NITs.';
        agregarToast('error', 'Importación errada', mensaje, true);
    });
});

/* ============================================================
   Inicialización (llamar desde el layout como ya haces con importnitsInit)
   ============================================================ */
$(document).ready(function () {
    if ($('#importNits').length) {
        importnitsInit();
    }
});