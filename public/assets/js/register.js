function peAlert(panel, tipo, mensaje) {
    document.getElementById('pe-alert-' + panel).innerHTML =
        '<div class="pe-login-alert ' + tipo + '">' + mensaje + '</div>';
}

function peHeaders() {
    var token = localStorage.getItem('auth_token') || '';
    return {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': token,
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
    };
}

function peGoStep(n) {
    document.querySelectorAll('.pe-login-panel').forEach(function (p) { p.classList.remove('active'); });
    document.querySelectorAll('.pe-login-step').forEach(function (s) { s.classList.remove('active', 'done'); });
    document.getElementById('pe-panel-' + n).classList.add('active');
    document.getElementById('pe-step-indicator-' + n).classList.add('active');
    for (var i = 1; i < n; i++) {
        document.getElementById('pe-step-indicator-' + i).classList.add('done');
    }
}

// PASO 1
document.getElementById('pe-btn-crear-cuenta').addEventListener('click', function () {
    var btn = this;

    // 🔒 Bloquear inmediatamente para evitar doble clic
    if (btn.disabled) return;

    var textoOriginal = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="pe-login-spinner"></span> Creando cuenta...';

    $.ajax({
        url: base_url + 'register-crear-cuenta',
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        data: JSON.stringify({
            nombre:   document.getElementById('pe-nombre').value,
            email:    document.getElementById('pe-email').value,
            password: document.getElementById('pe-password').value,
        }),
    })
    .done(function (res) {
        if (!res.success) {
            peAlert(1, 'error', res.message || 'Error al crear la cuenta');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
            return;
        }

        // 🔑 Guardar token en localStorage (debe ser así)
        localStorage.setItem('auth_token', res.token_type + ' ' + res.access_token);
        
        // Debug temporal: verificar que se guardó
        console.log('TOKEN GUARDADO:', localStorage.getItem('auth_token'));

        peGoStep(2);
    })
    .fail(function (err) {
        peAlert(1, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
});

// PASO 2: verificar
document.getElementById('pe-btn-verificar').addEventListener('click', function () {
    var btn = this;
    if (btn.disabled) return;

    var textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="pe-login-spinner"></span> Verificando...';

    $.ajax({
        url: base_url + 'register-verificar-codigo',
        method: 'POST',
        headers: peHeaders(),
        data: JSON.stringify({ codigo: document.getElementById('pe-codigo').value }),
    })
    .done(function (res) {
        if (!res.success) {
            peAlert(2, 'error', res.message || 'Código incorrecto');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
            return;
        }
        peGoStep(3);
    })
    .fail(function (err) {
        peAlert(2, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
});

// PASO 2: reenviar
document.getElementById('pe-btn-reenviar').addEventListener('click', function () {
    var btn = this;
    if (btn.disabled) return;

    var textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="pe-login-spinner"></span> Reenviando...';

    $.ajax({
        url: base_url + 'register-reenviar-codigo',
        method: 'POST',
        headers: peHeaders(),
    })
    .done(function (res) {
        peAlert(2, res.success ? 'success' : 'error', res.message || 'Código reenviado');
    })
    .fail(function () {
        peAlert(2, 'error', 'Error de conexión');
    })
    .always(function () {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
});

// PASO 3: RUT
document.getElementById('pe-rut-input').addEventListener('change', function () {
    var input = this;
    var file = input.files[0];
    if (!file) return;

    var formData = new FormData();
    formData.append('rut', file);

    var labelRut = document.getElementById('pe-rut-label');
    var htmlOriginal = '<div>📄 Arrastra tu RUT aquí o haz clic para subir</div><small>PDF · Máximo 10 MB</small>';

    labelRut.innerHTML = '<div>⏳ Procesando RUT...</div>';

    $.ajax({
        url: base_url + 'register-cargar-rut',
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Authorization': localStorage.getItem('auth_token') || '',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
        data: formData,
        processData: false,
        contentType: false,
    })
    .done(function (res) {
        // 🔑 RESETEAR SIEMPRE para permitir reemplazo del archivo
        input.value = '';
        labelRut.querySelector('input') && (labelRut.querySelector('input').value = '');

        if (!res.success) {
            labelRut.innerHTML = htmlOriginal;
            peAlert(3, 'error', res.message || 'Error al procesar RUT');
            return;
        }

        labelRut.innerHTML = '<div>✅ RUT cargado — clic para reemplazar</div><small>PDF · Máximo 10 MB</small>';
        document.getElementById('pe-rut-datos').style.display = 'block';

        var d = res.datos;
        document.getElementById('pe-nit').value              = d.nit || '';
        document.getElementById('pe-dv').value               = d.dv || '';
        document.getElementById('pe-tipo-contribuyente').value = d.tipo_contribuyente || '';
        document.getElementById('pe-razon-social').value     = d.razon_social || '';
        document.getElementById('pe-primer-apellido').value  = d.primer_apellido || '';
        document.getElementById('pe-segundo-apellido').value = d.segundo_apellido || '';
        document.getElementById('pe-primer-nombre').value    = d.primer_nombre || '';
        document.getElementById('pe-otros-nombres').value    = d.otros_nombres || '';
        document.getElementById('pe-direccion').value        = d.direccion || '';
        document.getElementById('pe-email-empresa').value    = d.email || '';
        document.getElementById('pe-telefono').value         = d.telefono_1 || '';

        peTogglePersonaNatural();
    })
    .fail(function (err) {
        // 🔑 RESETEAR SIEMPRE en error también
        input.value = '';
        labelRut.innerHTML = htmlOriginal;
        peAlert(3, 'error', err.responseJSON ? err.responseJSON.message : 'Error al procesar el RUT');
    });
});

document.getElementById('pe-tipo-contribuyente').addEventListener('change', peTogglePersonaNatural);

function peTogglePersonaNatural() {
    var tipo = document.getElementById('pe-tipo-contribuyente').value;
    if (tipo === '2') {
        document.getElementById('pe-field-razon-social').style.display = 'none';
        document.getElementById('pe-field-persona-natural').style.display = 'block';
    } else {
        document.getElementById('pe-field-razon-social').style.display = 'block';
        document.getElementById('pe-field-persona-natural').style.display = 'none';
    }
}

// PASO 3: crear empresa
document.getElementById('pe-btn-crear-empresa').addEventListener('click', function () {
    var btn = this;
    if (btn.disabled) return;

    var textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="pe-login-spinner"></span> Creando empresa...';

    var payload = {
        nit:                document.getElementById('pe-nit').value,
        dv:                 document.getElementById('pe-dv').value,
        tipo_contribuyente: document.getElementById('pe-tipo-contribuyente').value,
        razon_social:       document.getElementById('pe-razon-social').value,
        primer_apellido:    document.getElementById('pe-primer-apellido').value,
        segundo_apellido:   document.getElementById('pe-segundo-apellido').value,
        primer_nombre:      document.getElementById('pe-primer-nombre').value,
        otros_nombres:      document.getElementById('pe-otros-nombres').value,
        direccion:          document.getElementById('pe-direccion').value,
        email:              document.getElementById('pe-email-empresa').value,
        telefono:           document.getElementById('pe-telefono').value,
    };

    $.ajax({
        url: base_url + 'register-crear-empresa',
        method: 'POST',
        headers: peHeaders(),
        data: JSON.stringify(payload),
    })
    .done(function (res) {
        if (!res.success) {
            peAlert(3, 'error', res.message || 'Error al crear empresa');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
            return;
        }
        peAlert(3, 'success', 'Empresa creada. Selecciona tu paquete.');
        cargarArbolComponentes();
        peGoStep(4);
    })
    .fail(function (err) {
        peAlert(3, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
});

// Drag & drop para el RUT
var rutLabel = document.getElementById('pe-rut-label');

['dragenter', 'dragover'].forEach(function (eventName) {
    rutLabel.addEventListener(eventName, function (e) {
        e.preventDefault();
        e.stopPropagation();
        rutLabel.style.borderColor = '#818cf8';
        rutLabel.style.background = 'rgba(79, 70, 229, 0.1)';
    });
});

['dragleave', 'drop'].forEach(function (eventName) {
    rutLabel.addEventListener(eventName, function (e) {
        e.preventDefault();
        e.stopPropagation();
        rutLabel.style.borderColor = '';
        rutLabel.style.background = '';
    });
});

rutLabel.addEventListener('drop', function (e) {
    var files = e.dataTransfer.files;
    if (!files || files.length === 0) return;

    // Simular que el usuario eligió el archivo en el input
    document.getElementById('pe-rut-input').files = files;

    // Disparar el change manualmente
    var event = new Event('change', { bubbles: true });
    document.getElementById('pe-rut-input').dispatchEvent(event);
});

// BOTÓN OMITIR RUT
document.getElementById('pe-btn-omitir-rut').addEventListener('click', function () {
    // 🔑 Ocultar el label del RUT y el botón
    document.getElementById('pe-rut-label').style.display = 'none';
    document.getElementById('pe-btn-omitir-rut').style.display = 'none';

    // 🔑 Mostrar el formulario completo
    document.getElementById('pe-rut-datos').style.display = 'block';

    // 🔑 Activar "persona jurídica" por defecto (usuario ajusta si quiere)
    document.getElementById('pe-tipo-contribuyente').value = '1';
    peTogglePersonaNatural();
});

// ============================================================
// PASO 4: COMPONENTES Y PAGO
// ============================================================

var peArbolComponentes = [];
var peSeleccionados = {}; // { idPadre: idHijo || true }
var peMeses = 1;
var pePagosProcesando = false;

function cargarArbolComponentes() {
    $('#pe-arbol-componentes').html('<div class="text-center text-muted small"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');

    $.ajax({
        url: base_url + 'suscripcion/componentes-arbol',
        method: 'GET',
        headers: peHeaders(),
    }).done(function (res) {
        if (!res.success) {
            $('#pe-arbol-componentes').html('<div class="text-danger small">' + res.message + '</div>');
            return;
        }
        peArbolComponentes = res.data || [];
        renderizarArbolComponentes();
    }).fail(function () {
        $('#pe-arbol-componentes').html('<div class="text-danger small">Error al cargar componentes</div>');
    });
}

function renderizarArbolComponentes() {
    var html = '';
    peSeleccionados = {};

    peArbolComponentes.forEach(function (c) {
        if (c.tipo === 0) {
            // Componente simple
            html += '<div style="padding: 0.5rem 0; border-bottom: 1px solid rgba(148,163,184,0.1);">' +
                '<label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #cbd5e1;">' +
                    '<input type="checkbox" class="pe-check-componente" data-id="' + c.id + '" data-precio="' + c.precio + '" data-tipo="0">' +
                    '<span>' + c.nombre + '</span>' +
                    '<span style="margin-left: auto; color: #a5b4fc; font-weight: 600;">' + formatearMoneda(c.precio) + '</span>' +
                '</label>' +
            '</div>';
        } else {
            // Componente tipo 1 con hijos
            html += '<div style="padding: 0.5rem 0; border-bottom: 1px solid rgba(148,163,184,0.1);">' +
                '<label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #cbd5e1; font-weight: 600;">' +
                    '<input type="checkbox" class="pe-check-padre" data-id="' + c.id + '">' +
                    '<span>' + c.nombre + '</span>' +
                '</label>' +
                '<div class="pe-hijos" data-padre="' + c.id + '" style="margin-left: 1.5rem; margin-top: 0.5rem; display: none;">';

            c.hijos.forEach(function (h, idx) {
                html += '<label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #94a3b8; padding: 0.25rem 0;">' +
                    '<input type="radio" name="pe-rad-' + c.id + '" class="pe-radio-hijo" data-padre="' + c.id + '" data-id="' + h.id + '" data-precio="' + h.precio + '">' +
                    '<span>' + h.nombre + '</span>' +
                    '<span style="margin-left: auto; color: #a5b4fc; font-weight: 500;">' + formatearMoneda(h.precio) + '</span>' +
                '</label>';
            });

            html += '</div></div>';
        }
    });

    $('#pe-arbol-componentes').html(html);
    peActualizarTotales();
}

// Toggle hijos al marcar padre
$(document).on('change', '.pe-check-padre', function () {
    var id = $(this).data('id');
    var checked = $(this).is(':checked');
    var $hijos = $('.pe-hijos[data-padre="' + id + '"]');

    if (checked) {
        $hijos.show();
    } else {
        $hijos.hide();
        $('input[name="pe-rad-' + id + '"]').prop('checked', false);
    }
    peActualizarTotales();
});

$(document).on('change', '.pe-check-componente', peActualizarTotales);
$(document).on('change', '.pe-radio-hijo', peActualizarTotales);

function peActualizarTotales() {
    var totalMensual = 0;

    // Componentes simples
    $('.pe-check-componente:checked').each(function () {
        totalMensual += parseFloat($(this).data('precio')) || 0;
    });

    // Componentes tipo 1 (solo si el padre está marcado y hay hijo seleccionado)
    $('.pe-check-padre:checked').each(function () {
        var id = $(this).data('id');
        var $hijo = $('input[name="pe-rad-' + id + '"]:checked');
        if ($hijo.length) {
            totalMensual += parseFloat($hijo.data('precio')) || 0;
        }
    });

    $('#pe-total-mensual strong').text(formatearMoneda(totalMensual));

    // Total a pagar = totalMensual × meses
    var totalPagar = totalMensual * peMeses;
    $('#pe-monto-total').text(formatearMoneda(totalPagar));
}

// Controles de meses
$(document).on('click', '#pe-btn-sumar-mes', function () {
    peMeses = Math.min(24, peMeses + 1);
    $('#pe-meses').val(peMeses);
    peActualizarTotales();
});

$(document).on('click', '#pe-btn-restar-mes', function () {
    peMeses = Math.max(1, peMeses - 1);
    $('#pe-meses').val(peMeses);
    peActualizarTotales();
});

$(document).on('change', '#pe-meses', function () {
    var v = parseInt($(this).val()) || 1;
    v = Math.max(1, Math.min(24, v));
    peMeses = v;
    $(this).val(v);
    peActualizarTotales();
});

// Botón pagar
$(document).on('click', '#pe-btn-pagar', function () {
    if (pePagosProcesando) return;

    // Validar componentes
    var componentes = [];

    $('.pe-check-componente:checked').each(function () {
        componentes.push({
            id: $(this).data('id'),
            precio: parseFloat($(this).data('precio')) || 0,
        });
    });

    var padreSinHijo = false;

    $('.pe-check-padre:checked').each(function () {
        var id = $(this).data('id');
        var $hijo = $('input[name="pe-rad-' + id + '"]:checked');
        if (!$hijo.length) {
            padreSinHijo = true;
            return false;
        }
        componentes.push({
            id: $hijo.data('id'),
            precio: parseFloat($hijo.data('precio')) || 0,
        });
    });

    if (padreSinHijo) {
        peAlert(4, 'error', 'Debes seleccionar una opción en cada módulo padre.');
        return;
    }

    if (componentes.length === 0) {
        peAlert(4, 'error', 'Debes seleccionar al menos un componente.');
        return;
    }

    var btn = $(this);
    var textoOriginal = btn.html();
    btn.prop('disabled', true).html('<span class="pe-login-spinner"></span> Procesando...');
    pePagosProcesando = true;

    // 1. Confirmar en backend
    $.ajax({
        url: base_url + 'suscripcion/registro/confirmar',
        method: 'POST',
        headers: peHeaders(),
        data: JSON.stringify({
            meses: peMeses,
            componentes: componentes,
        }),
    }).done(function (res) {
        if (!res.success) {
            peAlert(4, 'error', res.message || 'Error al procesar');
            btn.prop('disabled', false).html(textoOriginal);
            pePagosProcesando = false;
            return;
        }

        // 2. Abrir widget de Wompi
        peAbrirWidgetRegistro(res.pago_id, res.reference, res.monto, btn, textoOriginal);
    }).fail(function (err) {
        peAlert(4, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        btn.prop('disabled', false).html(textoOriginal);
        pePagosProcesando = false;
    });
});

function peAbrirWidgetRegistro(pagoId, referencia, monto, btn, textoOriginal) {
    var montoCents = Math.round(monto * 100);

    // Firma de integridad
    $.ajax({
        url: base_url + 'suscripcion/wompi/firma-integridad',
        method: 'POST',
        headers: peHeaders(),
        data: JSON.stringify({
            reference: referencia,
            amount_in_cents: montoCents,
        }),
    }).done(function (resFirma) {
        if (!resFirma.success) {
            peAlert(4, 'error', 'No se pudo generar la firma');
            btn.prop('disabled', false).html(textoOriginal);
            pePagosProcesando = false;
            return;
        }

        try {
            var checkout = new WidgetCheckout({
                currency: 'COP',
                amountInCents: montoCents,
                reference: referencia,
                publicKey: 'pub_test_OotfUWWYb4mROOAPONcTs2Uh2CAJQ1ZK',
                signature: { integrity: resFirma.signature },
            });

            checkout.open(function (result) {
                var transaction = result.transaction;

                if (transaction.status === 'APPROVED') {
                    peReconciliarRegistro(pagoId, transaction, referencia, btn, textoOriginal);
                } else {
                    peAlert(4, 'error', 'Pago rechazado: ' + transaction.status);
                    btn.prop('disabled', false).html(textoOriginal);
                    pePagosProcesando = false;
                }
            });
        } catch (e) {
            peAlert(4, 'error', 'No se pudo abrir el widget: ' + e.message);
            btn.prop('disabled', false).html(textoOriginal);
            pePagosProcesando = false;
        }
    });
}

function peReconciliarRegistro(pagoId, transaction, referencia, btn, textoOriginal) {
    $.ajax({
        url: base_url + 'suscripcion/wompi/reconciliar',
        method: 'POST',
        headers: peHeaders(),
        data: JSON.stringify({
            pago_id: pagoId,
            transaction_id: transaction.id,
            reference: referencia,
            payment_source_id: transaction.payment_source_id || null,
        }),
    }).done(function (res) {
        if (res.success && res.estado === 1) {
            peAlert(4, 'success', '¡Pago exitoso! Activando empresa...');
            setTimeout(function () {
                window.location.href = '/home';
            }, 1500);
        } else {
            peAlert(4, 'error', res.message || 'Pago no confirmado');
            btn.prop('disabled', false).html(textoOriginal);
            pePagosProcesando = false;
        }
    }).fail(function (err) {
        peAlert(4, 'error', 'Error al confirmar pago');
        btn.prop('disabled', false).html(textoOriginal);
        pePagosProcesando = false;
    });
}