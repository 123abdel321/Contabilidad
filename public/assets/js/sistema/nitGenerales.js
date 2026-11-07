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
 *   - captura: 'venta' | 'gasto'
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
        $("#textNitGeneralTitle").text('Agregar Cedula/Nit');
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
            $("#textNitGeneralTitle").text('Ver Cedula/Nit');
            $("#saveNitVentaGeneral").hide();
            $("#saveNitGastoGeneral").hide();
            setFormNitGeneralReadonly(true);
        } else {
            $("#textNitGeneralTitle").text('Editar Cedula/Nit');
            if (captura == 'venta') $("#saveNitVentaGeneral").show().text('Actualizar');
            else if (captura == 'gasto') $("#saveNitGastoGeneral").show().text('Actualizar');
            setFormNitGeneralReadonly(false);
        }
        $("#nitGeneralFormModal").modal('show');
    }).fail(() => {
        agregarToast('error', 'Error', 'No se pudo cargar la cedula/nit');
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
    $("#digito_verificacion_general_nit").val(d.digito_verificacion != null ? d.digito_verificacion : '');
    $("#tipo_contribuyente_general_nit").val(d.tipo_contribuyente).change();
    $("#primer_nombre_general_nit").val(d.primer_nombre);
    $("#otros_nombres_general_nit").val(d.otros_nombres);
    $("#primer_apellido_general_nit").val(d.primer_apellido);
    $("#segundo_apellido_general_nit").val(d.segundo_apellido);
    $("#razon_social_general_nit").val(d.razon_social);
    $("#direccion_general_nit").val(d.direccion);
    $("#email_general_nit").val(d.email);
    $("#telefono_1_general_nit").val(d.telefono_1);

    if (d.sumar_aiu) {
        $('#sumar_aiu_general_nits').prop('checked', true);
    } else {
        $('#sumar_aiu_general_nits').prop('checked', false);
    }

    if (d.proveedor) {
        $('#proveedor_general_nit').prop('checked', true);
    } else {
        $('#proveedor_general_nit').prop('checked', false);
    }

    if (d.retencion) {
        $('#retencion_general_nit').prop('checked', true);
    } else {
        $('#retencion_general_nit').prop('checked', false);
    }

    if (d.notificacion_email) {
        $('#notificacion_email_general_nit').prop('checked', true);
    } else {
        $('#notificacion_email_general_nit').prop('checked', false);
    }

    if (d.notificacion_whatsapp) {
        $('#notificacion_whatsapp_general_nit').prop('checked', true);
    } else {
        $('#notificacion_whatsapp_general_nit').prop('checked', false);
    }

    // Responsabilidades (array de ids)
    var idsResp = [];
    if (d.id_responsabilidades) {
        idsResp = Array.isArray(d.id_responsabilidades)
            ? d.id_responsabilidades
            : String(d.id_responsabilidades).split(',').filter(Boolean);
    }

    var $selResp = $("#id_responsabilidades_general_nit");
    $selResp.empty();

    if (d.responsabilidades && d.responsabilidades.length) {
        d.responsabilidades.forEach(function (r) {
            $selResp.append(new Option(r.codigo + ' - ' + r.nombre, r.id, false, false));
        });
    } else {
        // Si no vienen los objetos completos, al menos agregamos los ids
        idsResp.forEach(function (id) {
            $selResp.append(new Option(id, id, false, false));
        });
    }
    $selResp.val(idsResp).trigger('change');

    // Validar/avisar DV con los datos cargados (sin sobrescribir el guardado)
    evaluarDigitoVerificacionGeneral(false);
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
    $("#digito_verificacion_general_nit").val('').removeClass('is-valid is-invalid');
    $("#dv_feedback_general_nit").hide().text('').removeClass('text-success text-danger');
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
    $("#id_responsabilidades_general_nit").val([]).trigger('change');

    $('#retencion_general_nit').prop('checked', false);
    $('#notificacion_email_general_nit').prop('checked', true);
    $('#notificacion_whatsapp_general_nit').prop('checked', false);
    $('#proveedor_general_nit').prop('checked', false);
    $('#sumar_aiu_general_nits').prop('checked', false);
}

// ------------------------------------------------------------
// CÁLCULO Y VALIDACIÓN DEL DÍGITO DE VERIFICACIÓN (Módulo 11)
// ------------------------------------------------------------
function calcularDigitoVerificacionJS(nit) {
    nit = String(nit || '').replace(/[^0-9]/g, '');
    if (nit === '') return null;

    var factores = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
    var invertido = nit.split('').reverse().join('');
    var suma = 0;

    for (var i = 0; i < invertido.length; i++) {
        if (factores[i] === undefined) return null; // longitud no soportada
        suma += parseInt(invertido[i], 10) * factores[i];
    }

    var residuo = suma % 11;
    return residuo < 2 ? residuo : 11 - residuo;
}

/**
 * Evalúa el DV actual contra el esperado.
 * @param {boolean} autocompletarSiVacio  Si true y el DV está vacío, lo llena con el esperado.
 *                                        Si false (modo edición), solo avisa si difiere.
 */
function evaluarDigitoVerificacionGeneral(autocompletarSiVacio) {
    var $dv = $("#digito_verificacion_general_nit");
    var $fb = $("#dv_feedback_general_nit");

    var numero = ($("#numero_documento_general_nit").val() || '').replace(/[^0-9]/g, '');
    var dvManual = ($dv.val() || '').replace(/[^0-9]/g, '');

    // Limpiar estado
    $dv.removeClass('is-valid is-invalid');
    $fb.hide().text('').removeClass('text-success text-danger').css('color', '');

    if (!numero) return;

    var dvEsperado = calcularDigitoVerificacionJS(numero);
    if (dvEsperado === null) {
        $dv.addClass('is-invalid');
        $fb.show().text('Número demasiado largo para calcular el DV.').addClass('text-danger');
        return;
    }

    // DV vacío
    if (dvManual === '') {
        if (autocompletarSiVacio) {
            $dv.val(dvEsperado);
            $dv.addClass('is-valid');
            $fb.show().text('DV calculado automáticamente.').addClass('text-success');
        } else {
            // En edición, no sobrescribir: solo indicar el esperado
            $fb.show()
               .html('El DV esperado para este número es <strong>' + dvEsperado + '</strong>.')
               .addClass('text-danger');
        }
        return;
    }

    // Comparar
    if (parseInt(dvManual, 10) === dvEsperado) {
        $dv.addClass('is-valid');
        $fb.show().text('Dígito de verificación correcto.').addClass('text-success');
    } else {
        $dv.addClass('is-invalid');
        $fb.show()
           .html('DV incorrecto. El esperado es <strong>' + dvEsperado + '</strong>.')
           .addClass('text-danger');
    }
}

// Solo dígitos mientras escribe el numero_documento (sin calcular todavía)
$(document).on('input', '#numero_documento_general_nit', function () {
    var limpio = $(this).val().replace(/[^0-9]/g, '');
    if (limpio !== $(this).val()) $(this).val(limpio);
});

// Calcular al salir del numero_documento
$(document).on('blur', '#numero_documento_general_nit', function () {
    evaluarDigitoVerificacionGeneral(true); // autocompleta solo si está vacío
});

// Solo dígitos y 1 carácter en el DV
$(document).on('input', '#digito_verificacion_general_nit', function () {
    var limpio = $(this).val().replace(/[^0-9]/g, '').slice(0, 1);
    if (limpio !== $(this).val()) $(this).val(limpio);
});

// Validar el DV cuando pierde el foco (no en cada tecla)
$(document).on('blur', '#digito_verificacion_general_nit', function () {
    evaluarDigitoVerificacionGeneral(false);
});

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

    // Validación final del DV antes de enviar
    var numero = ($("#numero_documento_general_nit").val() || '').replace(/[^0-9]/g, '');
    var dvEsperado = calcularDigitoVerificacionJS(numero);
    var dvActual = ($("#digito_verificacion_general_nit").val() || '').replace(/[^0-9]/g, '');

    if (numero && dvEsperado !== null) {
        if (dvActual === '') {
            // Si no puso DV, lo autocompletamos
            $("#digito_verificacion_general_nit").val(dvEsperado);
            dvActual = String(dvEsperado);
        }
        if (parseInt(dvActual, 10) !== dvEsperado) {
            evaluarDigitoVerificacionGeneral(false);
            agregarToast('error', 'Dígito de verificación inválido',
                'El DV ingresado no coincide con el calculado (' + dvEsperado + ').');
            return;
        }
    }

    $("#saveNitGeneralLoading").show();
    $("#saveNitVentaGeneral").hide();
    $("#saveNitGastoGeneral").hide();

    var idNit = $("#id_nit_general_up").val();
    var esEdicion = !!idNit;

    let data = {
        id_tipo_documento: $("#id_tipo_documento_general_nit").val(),
        numero_documento: $("#numero_documento_general_nit").val(),
        digito_verificacion: $("#digito_verificacion_general_nit").val(),
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
        id_responsabilidades: $("#id_responsabilidades_general_nit").val(),
        sumar_aiu: $("input[type='checkbox']#sumar_aiu_general_nits").is(':checked') ? '1' : '',
        proveedor: $("input[type='checkbox']#proveedor_general_nit").is(':checked') ? '1' : '',
        retencion: $("input[type='checkbox']#retencion_general_nit").is(':checked') ? '1' : '',
        notificacion_email:    $("input[type='checkbox']#notificacion_email_general_nit").is(':checked')    ? '1' : '',
        notificacion_whatsapp: $("input[type='checkbox']#notificacion_whatsapp_general_nit").is(':checked') ? '1' : '',
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
    if (!$("#id_responsabilidades_general_nit").hasClass('select2-hidden-accessible')) {
        $("#id_responsabilidades_general_nit").select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#nitGeneralFormModal'),
            delay: 250,
            closeOnSelect: false,
            placeholder: 'Seleccionar responsabilidades',
            language: {
                noResults: () => "No hay resultado",
                searching: () => "Buscando..",
                inputTooShort: () => "Por favor introduce 1 o más caracteres"
            },
            ajax: {
                url: 'api/nit/responsabilidades',
                headers: headers,
                dataType: 'json',
                data: (params) => ({
                    q: params.term || '',
                    page: params.page || 1
                }),
                processResults: (data, params) => {
                    params.page = params.page || 1;
                    return {
                        results: (data.data || []).map(r => ({
                            id: r.id,
                            text: r.text
                        })),
                        pagination: {
                            more: data.current_page < data.last_page
                        }
                    };
                }
            }
        });
    }

    // Al abrir el modal, si ya hay número, evaluar el DV (en edición no sobrescribe)
    evaluarDigitoVerificacionGeneral(nitGeneralModo === 'create');
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

        // Documento (con DV si existe)
        var numDoc = d.numero_documento || '-';
        if (d.digito_verificacion !== null && d.digito_verificacion !== undefined && d.digito_verificacion !== '') {
            numDoc = numDoc + '-' + d.digito_verificacion;
        }

        $('#nitDetailTipoDocumento').text(d.tipo_documento ? d.tipo_documento.codigo : '-');
        $('#nitDetailTipoDocumentoCompleto').text(d.tipo_documento ? d.tipo_documento.codigo + ' - ' + d.tipo_documento.nombre : '-');
        $('#nitDetailNumeroDocumento').text(numDoc);
        $('#nitDetailNumeroDocumento2').text(numDoc);

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
            $('#nitDetailImagen').attr('src', 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/'+d.logo_nit).show();
            const elemento = document.getElementById("nitDetailImagenDefault");
            elemento.style.setProperty("display", "none", "important");
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