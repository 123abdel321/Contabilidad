// ============================================================
// MÓDULO GENÉRICO DE NIT (Clientes / Proveedores / etc.)
// ============================================================

var nitGeneralCallbacks = null;  // { onSave: fn, onUpdate: fn, onClose: fn }
var nitGeneralModo = 'create';   // 'create' | 'edit' | 'view'
var nitGeneralIdActual = null;

/**
 * Abre el modal general.
 *
 * @param {Object} opts
 *   - modo: 'create' | 'edit' | 'view'
 *   - id:   id del NIT (para edit/view)
 *   - onSave: function(data, res)  -> se llama tras guardar/editar OK
 *   - onClose: function()          -> se llama al cerrar el modal
 */
function abrirNitGeneral(opts = {}) {
    nitGeneralCallbacks = opts;
    nitGeneralModo = opts.modo || 'create';
    nitGeneralCaptura = opts.captura || 'venta';
    nitGeneralIdActual = opts.id || null;

    clearFormNitGeneral();

    $("#saveNitVentaGeneral").hide();
    $("#saveNitGastoGeneral").hide();

    if (nitGeneralModo === 'create') {
        $("#textNitGeneralTitle").text('Agregar cliente');
        if (nitGeneralCaptura == 'venta') $("#saveNitVentaGeneral").show().text('Guardar');
        else if (nitGeneralCaptura == 'gasto') $("#saveNitGastoGeneral").show().text('Guardar');
        setFormNitGeneralReadonly(false);
        $("#nitGeneralFormModal").modal('show');
    } else {
        cargarNitGeneral(nitGeneralIdActual, nitGeneralModo, nitGeneralCaptura);
    }
}

// Carga datos y abre el modal en modo edit/view
function cargarNitGeneral(idNit, modo, captura) {
    $.ajax({
        url: base_url + 'nit/' + idNit,
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (!res.success) {
            agregarToast('error', 'Error', res.message || 'No se pudo cargar');
            return;
        }
        llenarFormNitGeneral(res.data);

        if (modo === 'view') {
            $("#textNitGeneralTitle").text('Ver cliente');
            $("#saveNitVentaGeneral").hide();
            $("#saveNitGastoGeneral").hide();
            setFormNitGeneralReadonly(true);
        } else {
            $("#textNitGeneralTitle").text('Editar cliente');
            if (captura == 'venta') $("#saveNitVentaGeneral").show().text('Actualizar');
            else if (captura == 'gasto') $("#saveNitGastoGeneral").show().text('Actualizar');
            setFormNitGeneralReadonly(false);
        }
        $("#nitGeneralFormModal").modal('show');
    }).fail(() => {
        agregarToast('error', 'Error', 'No se pudo cargar el cliente');
    });
}

function llenarFormNitGeneral(d) {
    // Selects con option temporal
    function setSelect($el, id, text) {
        if (id) {
            $el.find('option[value="' + id + '"]').remove();
            $el.append(new Option(text, id, false, false));
            $el.val(id).trigger('change');
        }
    }

    setSelect($("#id_tipo_documento_general_nit"),
        d.id_tipo_documento,
        d.tipo_documento ? (d.tipo_documento.codigo + ' - ' + d.tipo_documento.nombre) : '');

    setSelect($("#id_ciudad_general_nit"),
        d.id_ciudad,
        d.ciudad ? d.ciudad.nombre_completo : '');

    $("#id_nit_general_up").val(d.id);
    $("#numero_documento_general_nit").val(d.numero_documento);
    $("#tipo_contribuyente_general_nit").val(d.tipo_contribuyente).change();
    $("#primer_nombre_general_nit").val(d.primer_nombre);
    $("#otros_nombres_general_nit").val(d.otros_nombres);
    $("#primer_apellido_general_nit").val(d.primer_apellido);
    $("#segundo_apellido_general_nit").val(d.segundo_apellido);
    $("#razon_social_general_nit").val(d.razon_social);
    $("#direccion_general_nit").val(d.direccion);
    $("#email_general_nit").val(d.email);
    $("#telefono_1_general_nit").val(d.telefono_1);
    $("#observaciones_general_nit").val(d.observaciones);
}

function setFormNitGeneralReadonly(readonly) {
    $("#generalNitsForm").find("input, select, textarea").each(function () {
        if ($(this).is("select")) {
            $(this).prop("disabled", readonly);
        } else if (!$(this).is(":hidden")) {
            $(this).prop("readonly", readonly);
        }
    });
    $("#generalNitsForm").find("select").trigger("change.select2");
}

function clearFormNitGeneral() {
    $("#id_nit_general_up").val('');
    $("#id_tipo_documento_general_nit").val('').change();
    $("#id_ciudad_general_nit").val('').change();
    $("#numero_documento_general_nit").val('');
    $("#tipo_contribuyente_general_nit").val(2).change();
    $("#primer_nombre_general_nit").val('');
    $("#otros_nombres_general_nit").val('');
    $("#primer_apellido_general_nit").val('');
    $("#segundo_apellido_general_nit").val('');
    $("#razon_social_general_nit").val('');
    $("#direccion_general_nit").val('');
    $("#email_general_nit").val('');
    $("#telefono_1_general_nit").val('');
    $("#observaciones_general_nit").val('');
}

// ------------------------------------------------------------
// Guardar (POST/PUT según id oculto)
// ------------------------------------------------------------
$(document).on('click', '#saveNitVentaGeneral, #saveNitGastoGeneral', function () {
    guardarNitGeneral();
});

function guardarNitGeneral() {
    var form = document.querySelector('#generalNitsForm');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $("#saveNitGeneralLoading").show();
    $("#saveNitVentaGeneral").hide();
    $("#saveNitGastoGeneral").hide();

    var idNit = $("#id_nit_general_up").val();
    var esEdicion = !!idNit;

    let data = {
        id_tipo_documento: $("#id_tipo_documento_general_nit").val(),
        numero_documento: $("#numero_documento_general_nit").val(),
        tipo_contribuyente: $("#tipo_contribuyente_general_nit").val(),
        primer_nombre: $("#primer_nombre_general_nit").val(),
        otros_nombres: $("#otros_nombres_general_nit").val(),
        primer_apellido: $("#primer_apellido_general_nit").val(),
        segundo_apellido: $("#segundo_apellido_general_nit").val(),
        razon_social: $("#razon_social_general_nit").val(),
        direccion: $("#direccion_general_nit").val(),
        email: $("#email_general_nit").val(),
        telefono_1: $("#telefono_1_general_nit").val(),
        id_ciudad: $("#id_ciudad_general_nit").val(),
        observaciones: $("#observaciones_general_nit").val(),
    };
    if (esEdicion) data.id = idNit;

    $.ajax({
        url: base_url + 'nit',
        method: esEdicion ? 'PUT' : 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (!res.success) return;

        $("#saveNitGeneralLoading").hide();
        $("#nitGeneralFormModal").modal('hide');

        var callback = nitGeneralCallbacks && (esEdicion ? nitGeneralCallbacks.onUpdate : nitGeneralCallbacks.onSave);
        if (typeof callback === 'function') {
            callback(res.data, res);
        }
    }).fail((err) => {
        $("#saveNitGeneralLoading").hide();
        $("#saveNitVentaGeneral").show();
        $("#saveNitGastoGeneral").show();

        var mensaje = err.responseJSON && err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', esEdicion ? 'Edición errada' : 'Creación errada', errorsMsg);
    });
}

// Al cerrar, limpiar callbacks
$(document).on('hidden.bs.modal', '#nitGeneralFormModal', function () {
    if (nitGeneralCallbacks && typeof nitGeneralCallbacks.onClose === 'function') {
        nitGeneralCallbacks.onClose();
    }
    nitGeneralCallbacks = null;
    nitGeneralModo = 'create';
    nitGeneralIdActual = null;
});

// Inicializar selects del modal general
$(document).on('shown.bs.modal', '#nitGeneralFormModal', function () {
    if (!$("#id_tipo_documento_general_nit").hasClass('select2-hidden-accessible')) {
        $("#id_tipo_documento_general_nit").select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#nitGeneralFormModal'),
            delay: 250,
            ajax: {
                url: 'api/nit/combo-tipo-documento',
                headers: headers,
                dataType: 'json',
                processResults: (data) => ({ results: data.data })
            }
        });
    }
    if (!$("#id_ciudad_general_nit").hasClass('select2-hidden-accessible')) {
        $("#id_ciudad_general_nit").select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#nitGeneralFormModal'),
            delay: 250,
            ajax: {
                url: 'api/ciudades',
                headers: headers,
                dataType: 'json',
                processResults: (data) => ({ results: data.data })
            }
        });
    }
});

function abrirNitDetalle(id) {
    $('#nitDetailLoading').show();
    $('#nitDetailContent').hide();
    $('#nitDetailModal').modal('show');

    $.ajax({
        url: base_url + 'nit/' + id,
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done(function(res) {
        if (!res.success) {
            $('#nitDetailModal').modal('hide');
            agregarToast('error', 'Error', res.message || 'No se pudo cargar el cliente');
            return;
        }

        var d = res.data;

        // Nombre
        var nombre = d.razon_social || [
            d.primer_nombre,
            d.otros_nombres,
            d.primer_apellido,
            d.segundo_apellido
        ].filter(Boolean).join(' ');

        $('#nitDetailNombre').text(nombre || '-');

        // Contribuyente
        var tipoContribuyente = String(d.tipo_contribuyente) === '1'
            ? 'Persona jurídica'
            : 'Persona natural';

        $('#nitDetailTipoContribuyente').text(tipoContribuyente);
        $('#nitDetailContribuyente2').text(tipoContribuyente);

        // Documento
        $('#nitDetailTipoDocumento').text(d.tipo_documento ? d.tipo_documento.codigo : '-');
        $('#nitDetailTipoDocumentoCompleto').text(d.tipo_documento ? d.tipo_documento.codigo + ' - ' + d.tipo_documento.nombre : '-');
        $('#nitDetailNumeroDocumento').text(d.numero_documento || '-');
        $('#nitDetailNumeroDocumento2').text(d.numero_documento || '-');

        // Información general
        $('#nitDetailRazonSocial').text(d.razon_social || '-');
        $('#nitDetailPrimerNombre').text(d.primer_nombre || '-');
        $('#nitDetailOtrosNombres').text(d.otros_nombres || '-');
        $('#nitDetailPrimerApellido').text(d.primer_apellido || '-');
        $('#nitDetailSegundoApellido').text(d.segundo_apellido || '-');
        $('#nitDetailDireccion').text(d.direccion || '-');

        // Contacto
        $('#nitDetailEmail').text(d.email || 'No registrado');
        $('#nitDetailTelefono').text(d.telefono_1 || 'No registrado');
        $('#nitDetailCiudad').text(d.ciudad ? d.ciudad.nombre_completo : 'No registrada');

        // Observaciones
        $('#nitDetailObservaciones').text(d.observaciones || 'Sin observaciones');

        // Imagen
        if (d.logo_nit) {
            $('#nitDetailImagen').attr('src', d.logo_nit).show();
            $('#nitDetailImagenDefault').hide();
        } else {
            $('#nitDetailImagen').hide();
            $('#nitDetailImagenDefault').show();
        }

        $('#nitDetailLoading').hide();
        $('#nitDetailContent').show();

    }).fail(function() {
        $('#nitDetailModal').modal('hide');
        agregarToast('error', 'Error', 'No se pudo cargar el cliente');
    });
}