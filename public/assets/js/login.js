// ============================================================
// PORTAFOLIO ERP — Login + Register unificado
// ============================================================

$(document).ajaxError(function (event, jqXHR, settings, thrownError) {
    if (jqXHR.status === 401) {
        localStorage.clear();
        window.location.href = '/login';
    }
});

const host = window.location.host;
let base_url, base_web;
var peDesgloseActual = null;
var pePreviewTimeout = null;

if (host.includes("app.portafolioerp.com")) {
    base_url = "https://app.portafolioerp.com/api/";
    base_web = "https://app.portafolioerp.com/";
} else if (host.includes("test.portafolioerp.com")) {
    base_url = "https://test.portafolioerp.com/api/";
    base_web = "https://test.portafolioerp.com/";
} else if (host.includes("localhost:8000")) {
    base_url = 'http://localhost:8000/api/';
    base_web = 'http://localhost:8000/';
}

// ============================================================
// HELPERS
// ============================================================

function peTogglePass() {
    var input = document.getElementById('password_login');
    var icon = document.getElementById('pe-eye-icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

function peAlert(panel, tipo, mensaje) {
    var el = document.getElementById('pe-alert-' + panel);
    if (!el) return;
    el.innerHTML = '<div class="pe-login-alert ' + tipo + '">' + mensaje + '</div>';
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

function formatearMoneda(valor) {
    var num = parseFloat(valor);
    if (isNaN(num)) num = 0;
    return '$ ' + Math.round(num).toLocaleString('es-CO');
}

// ============================================================
// NAVEGACIÓN ENTRE PANTALLAS (LOGIN / REGISTER)
// ============================================================

function peShowScreen(screen) {
    document.getElementById('pe-screen-login').style.display = 'none';
    document.getElementById('pe-screen-register').style.display = 'none';

    if (screen === 'login') {
        document.getElementById('pe-screen-login').style.display = 'block';
        document.getElementById('pe-screen-login').classList.add('active');
    } else {
        document.getElementById('pe-screen-register').style.display = 'block';
        document.getElementById('pe-screen-register').classList.add('active');
    }
}

function peGoStep(n) {
    // Ocultar todos los paneles
    document.querySelectorAll('#pe-screen-register .pe-login-panel').forEach(function (p) {
        p.classList.remove('active');
    });

    // Ocultar steppers done
    document.querySelectorAll('#pe-screen-register .pe-login-step').forEach(function (s) {
        s.classList.remove('active', 'done');
    });

    document.getElementById('pe-panel-' + n).classList.add('active');
    document.getElementById('pe-step-indicator-' + n).classList.add('active');

    for (var i = 1; i < n; i++) {
        document.getElementById('pe-step-indicator-' + i).classList.add('done');
    }

    if (n === 2 && window.peOtpClear) setTimeout(peOtpClear, 150);

    peShowScreen('register');
}

// Link "Regístrate" del login
document.getElementById('pe-link-registro').addEventListener('click', function () {
    peSetVerifyOnly(false);
    peGoStep(1);
});

// Link "Volver al login" del paso 1
document.getElementById('pe-link-volver-login').addEventListener('click', function () {
    peShowScreen('login');
});

// ============================================================
// MODO "SOLO VERIFICAR" + RETOMAR PROGRESO
// ============================================================
var peVerifyOnly = false;

function peSetVerifyOnly(on) {
    peVerifyOnly = on;
    document.getElementById('pe-register-title').textContent = on ? 'Verifica tu correo' : 'Crear cuenta';
    document.getElementById('pe-stepper').style.display = on ? 'none' : '';
    var salir = document.getElementById('pe-btn-salir-verify');
    if (salir) salir.style.display = on ? 'block' : 'none';
}

function peFinalizarLogin(res) {
    localStorage.setItem("empresa_nombre", (res.empresa && res.empresa.razon_social) || '');
    localStorage.setItem("empresa_logo", (res.empresa && res.empresa.logo) || '');
    localStorage.setItem("notificacion_code", res.notificacion_code || '');
    localStorage.setItem("fondo_sistema", res.fondo_sistema || '');

    var itemActiveIn = localStorage.getItem("item_active_menu");
    if (!(itemActiveIn == 0 || itemActiveIn == 1 || itemActiveIn == 2 || itemActiveIn == 3)) {
        localStorage.setItem("item_active_menu", 'contabilidad');
    }
    window.location.href = '/home';
}

function peAplicarNeeds(res) {
    switch (res.needs) {
        case 'verify':
            peSetVerifyOnly(true);
            peGoStep(2);
            break;
        case 'empresa':
            peSetVerifyOnly(false);
            peCargarProgresoYIrAlPaso3();
            break;
        case 'pago':
            peSetVerifyOnly(false);
            peGoStep(4);
            cargarArbolComponentes();
            break;
        default:
            peFinalizarLogin(res);
    }
}

function peReanudarSesion() {
    if (!localStorage.getItem('auth_token')) return;

    $.ajax({
        url: base_url + 'register-mi-progreso',
        method: 'GET',
        headers: peHeaders(),
        global: false,   // evita el handler global de 401 → bucle de recarga
    }).done(function (res) {
        if (res.success && res.needs) {
            peAplicarNeeds(res);
        }
    }).fail(function () {
        localStorage.removeItem('auth_token');
    });
}

// Salir de la verificación
document.getElementById('pe-btn-salir-verify').addEventListener('click', function () {
    localStorage.removeItem('auth_token');
    peSetVerifyOnly(false);
    peShowScreen('login');
});

// ============================================================
// LOGIN
// ============================================================

$("#button-login").click(function () {
    sendDataLogin();
});

function changePassWord(event) {
    if (event.keyCode == 13) {
        sendDataLogin();
    }
}

function sendDataLogin() {
    localStorage.removeItem("auth_token");
    localStorage.removeItem("empresa_nombre");
    localStorage.removeItem("empresa_logo");
    localStorage.removeItem("notificacion_code");
    localStorage.removeItem("fondo_sistema");

    $('#error-login').hide();
    $("#button-login-loading").show();
    $("#button-login").hide();

    $.ajax({
        url: base_web + 'login',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        method: 'POST',
        data: {
            "email": $('#email_login').val(),
            "password": $('#password_login').val(),
        },
        dataType: 'json',
    }).done((res) => {
        $("#button-login-loading").hide();
        $("#button-login").show();

        if (!res.success) {
            $('#error-login').show();
            return;
        }

        // 🔑 Guardar token
        localStorage.setItem("auth_token", res.token_type + ' ' + res.access_token);

        // 🔑 El backend dice qué falta (verify / empresa / pago / null)
        //    peAplicarNeeds decide a dónde ir
        peAplicarNeeds(res);

    }).fail((err) => {
        $("#button-login-loading").hide();
        $("#button-login").show();
        $('#error-login').show();
    });
}

function peCargarProgresoYIrAlPaso3() {

    document.getElementById('pe-rut-label').style.display = 'block';
    document.getElementById('pe-btn-omitir-rut').style.display = 'block';
    document.getElementById('pe-rut-datos').style.display = 'none';

    $.ajax({
        url: base_url + 'register-mi-progreso',
        method: 'GET',
        headers: peHeaders(),
    }).done(function (res) {
        if (res.success && res.data.empresa) {
            var emp = res.data.empresa;
            document.getElementById('pe-nit').value              = emp.nit || '';
            document.getElementById('pe-dv').value               = emp.dv || '';
            document.getElementById('pe-tipo-contribuyente').value = emp.tipo_contribuyente || '';
            document.getElementById('pe-razon-social').value     = emp.razon_social || '';
            document.getElementById('pe-primer-apellido').value  = emp.primer_apellido || '';
            document.getElementById('pe-segundo-apellido').value = emp.segundo_apellido || '';
            document.getElementById('pe-primer-nombre').value    = emp.primer_nombre || '';
            document.getElementById('pe-otros-nombres').value    = emp.otros_nombres || '';
            document.getElementById('pe-direccion').value        = emp.direccion || '';
            document.getElementById('pe-email-empresa').value    = emp.email || '';
            document.getElementById('pe-telefono').value         = emp.telefono || '';

            document.getElementById('pe-rut-datos').style.display = 'block';
            peTogglePersonaNatural();
        }
        peGoStep(3);
    }).fail(function () {
        peGoStep(3);
    });
}

// ============================================================
// REGISTRO — PASO 1: Crear cuenta
// ============================================================

document.getElementById('pe-btn-crear-cuenta').addEventListener('click', function () {
    var btn = this;
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

        localStorage.setItem('auth_token', res.token_type + ' ' + res.access_token);
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        peGoStep(2);
    })
    .fail(function (err) {
        peAlert(1, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    });
});

// ============================================================
// REGISTRO — PASO 2: Verificar código
// ============================================================

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
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        if (!res.success) {
            peAlert(2, 'error', res.message || 'Código incorrecto');
            peOtpError();
            return;
        }
        peOtpOk();
        setTimeout(function () { peAplicarNeeds(res); }, 600);
    })
    .fail(function (err) {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        peAlert(2, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        peOtpError();
    });
});

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

// ============================================================
// REGISTRO — PASO 3: RUT + Empresa
// ============================================================

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
        input.value = '';
        labelRut.querySelector('input') && (labelRut.querySelector('input').value = '');

        if (!res.success) {
            labelRut.innerHTML = htmlOriginal;
            peAlert(3, 'error', res.message || 'Error al procesar RUT');
            return;
        }

        labelRut.innerHTML = '<div>✅ RUT cargado — clic para reemplazar</div><small>PDF · Máximo 10 MB</small>';
        document.getElementById('pe-rut-datos').style.display = 'block';

        // 🔑 Ocultar el botón "Omitir RUT" después de cargar
        var btnOmitir = document.getElementById('pe-btn-omitir-rut');
        if (btnOmitir) {
            btnOmitir.style.display = 'none';
        }

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

document.getElementById('pe-btn-omitir-rut').addEventListener('click', function () {
    document.getElementById('pe-rut-label').style.display = 'none';
    document.getElementById('pe-btn-omitir-rut').style.display = 'none';
    document.getElementById('pe-rut-datos').style.display = 'block';
    document.getElementById('pe-tipo-contribuyente').value = '1';
    peTogglePersonaNatural();
});

// ============================================================
// REGISTRO — PASO 3 → PASO 4: Solo validar, no crear todavía
// ============================================================

document.getElementById('pe-btn-continuar-paso4').addEventListener('click', function () {
    // Validar campos mínimos
    var nit = document.getElementById('pe-nit').value.trim();
    var dv  = document.getElementById('pe-dv').value.trim();
    var tipo = document.getElementById('pe-tipo-contribuyente').value;
    var razonSocial = document.getElementById('pe-razon-social').value.trim();
    var primerNombre = document.getElementById('pe-primer-nombre').value.trim();
    var primerApellido = document.getElementById('pe-primer-apellido').value.trim();

    if (!nit) {
        peAlert(3, 'error', 'El NIT es requerido.');
        return;
    }
    if (!dv) {
        peAlert(3, 'error', 'El DV es requerido.');
        return;
    }
    if (!tipo) {
        peAlert(3, 'error', 'Selecciona el tipo de contribuyente.');
        return;
    }
    if (tipo === '1' && !razonSocial) {
        peAlert(3, 'error', 'La razón social es requerida.');
        return;
    }
    if (tipo === '2' && (!primerNombre || !primerApellido)) {
        peAlert(3, 'error', 'El nombre y apellido son requeridos para persona natural.');
        return;
    }

    // Limpiar alerta
    document.getElementById('pe-alert-3').innerHTML = '';

    // Cargar árbol de componentes y avanzar al paso 4
    cargarArbolComponentes();
    peGoStep(4);
});

// Drag & drop RUT
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

    document.getElementById('pe-rut-input').files = files;
    var event = new Event('change', { bubbles: true });
    document.getElementById('pe-rut-input').dispatchEvent(event);
});

// ============================================================
// REGISTRO — PASO 4: Componentes y pago
// ============================================================

var peArbolComponentes = [];
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

var PE_ICO = {
    doc:  '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
    chart:'<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
    users:'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
    cart: '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
    build:'<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>',
    bolt: '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'
};

// meta por id de componente (nombre y subtítulo que ve el cliente)
var PE_COMP_META = {
    1:  { ico: 'chart', nombre: 'Contabilidad',           sub: 'Libros, balances e impuestos automáticos' },
    2:  { ico: 'doc',   nombre: 'Facturación',            sub: 'Cotiza, factura y controla tu cartera' },
    4:  { ico: 'users', nombre: 'Nómina',                 sub: 'Liquida y transmite nómina electrónica' },
    5:  { ico: 'cart',  nombre: 'POS e inventario',       sub: 'Vende en mostrador y controla el stock' },
    6:  { ico: 'build', nombre: 'Máximo PH',              sub: 'Administración de propiedad horizontal' },
    14: { ico: 'bolt',  nombre: 'Facturación electrónica',sub: 'Emite y envía facturas a la DIAN' }
};

function peMeta(c) {
    var m = PE_COMP_META[c.id] || {};
    return { ico: PE_ICO[m.ico] || PE_ICO.doc, nombre: m.nombre || c.nombre, sub: m.sub || '' };
}

function renderizarArbolComponentes() {
    var html = '';

    peArbolComponentes.forEach(function (c) {
        var m = peMeta(c);
        var icono = '<svg class="pe-comp-ico" viewBox="0 0 24 24">' + m.ico + '</svg>';
        var sub = m.sub ? '<span class="pe-comp-sub">' + m.sub + '</span>' : '';

        if (c.tipo === 0) {
            html += '<div class="pe-comp">' +
                '<label class="pe-comp-head">' +
                    '<input type="checkbox" class="pe-check-componente" data-id="' + c.id + '" data-precio="' + c.precio + '">' +
                    '<span class="pe-comp-box"></span>' + icono +
                    '<span class="pe-comp-name">' + m.nombre + sub + '</span>' +
                    '<span class="pe-comp-price">' + formatearMoneda(c.precio) + '<small>/ mes</small></span>' +
                '</label>' +
            '</div>';
        } else {
            var minimo = Math.min.apply(null, c.hijos.map(function (h) { return parseFloat(h.precio) || 0; }));

            html += '<div class="pe-comp">' +
                '<label class="pe-comp-head">' +
                    '<input type="checkbox" class="pe-check-padre" data-id="' + c.id + '">' +
                    '<span class="pe-comp-box"></span>' + icono +
                    '<span class="pe-comp-name">' + m.nombre + sub + '</span>' +
                    '<span class="pe-comp-more">Desde ' + formatearMoneda(minimo) + '</span>' +
                '</label>' +
                '<div class="pe-hijos" data-padre="' + c.id + '">';

            c.hijos.forEach(function (h) {
                html += '<label class="pe-tier">' +
                    '<input type="radio" name="pe-rad-' + c.id + '" class="pe-radio-hijo" data-padre="' + c.id + '" data-id="' + h.id + '" data-precio="' + h.precio + '">' +
                    '<span class="pe-tier-dot"></span>' +
                    '<span>' + h.nombre + '</span>' +
                    '<span class="pe-tier-price">' + formatearMoneda(h.precio) + '</span>' +
                '</label>';
            });

            html += '</div></div>';
        }
    });

    $('#pe-arbol-componentes').html(html);
    peActualizarTotales();
}

$(document).on('change', '.pe-check-padre', function () {
    var id = $(this).data('id');
    if (!$(this).is(':checked')) {
        $('input[name="pe-rad-' + id + '"]').prop('checked', false);
    }
    peActualizarTotales();
});

$(document).on('change', '.pe-check-componente', peActualizarTotales);
$(document).on('change', '.pe-radio-hijo', peActualizarTotales);

function peActualizarTotales() {
    // Recolectar componentes seleccionados
    var componentes = [];

    $('.pe-check-componente:checked').each(function () {
        componentes.push({
            id: $(this).data('id'),
            precio: parseFloat($(this).data('precio')) || 0,
        });
    });

    $('.pe-check-padre:checked').each(function () {
        var id = $(this).data('id');
        var $hijo = $('input[name="pe-rad-' + id + '"]:checked');
        if ($hijo.length) {
            componentes.push({
                id: $hijo.data('id'),
                precio: parseFloat($hijo.data('precio')) || 0,
            });
        }
    });

    // Total mensual (suma simple, para mostrar el card)
    var totalMensual = 0;
    componentes.forEach(function (c) { totalMensual += c.precio; });
    $('#pe-total-mensual strong').text(formatearMoneda(totalMensual));

    // Si no hay componentes → mostrar $0
    if (componentes.length === 0) {
        $('#pe-monto-total').text('$ 0');
        peDesgloseActual = null;
        return;
    }

    // Debounce para no saturar el backend
    clearTimeout(pePreviewTimeout);
    pePreviewTimeout = setTimeout(function () {
        pePedirPreviewPago(componentes);
    }, 250);
}

function pePedirPreviewPago(componentes) {
    $.ajax({
        url: base_url + 'suscripcion/registro/preview-pago',
        method: 'POST',
        headers: peHeaders(),
        data: JSON.stringify({
            meses: peMeses,
            componentes: componentes,
        }),
    }).done(function (res) {
        if (!res.success) {
            $('#pe-monto-total').text('—');
            peDesgloseActual = null;
            return;
        }

        peDesgloseActual = res.data;

        // Total a pagar (con proporcional + descuento)
        $('#pe-monto-total').text(formatearMoneda(res.data.total));

        // Mostrar desglose detallado
        var html = '';

        res.data.detalle_meses.forEach(function (item) {
            if (item.monto > 0) {
                html += '<div class="pe-line"><span>' + item.label + '</span><span>' + formatearMoneda(item.monto) + '</span></div>';
            }
        });

        html += '<div class="pe-line pe-line-sub"><span>Subtotal</span><span>' + formatearMoneda(res.data.subtotal) + '</span></div>';

        if (res.data.descuento > 0) {
            html += '<div class="pe-line pe-line-save"><span>🎉 Descuento ' + res.data.porcentaje + '%</span><span>− ' + formatearMoneda(res.data.descuento) + '</span></div>';
        }

        var $desglose = $('#pe-desglose-pago');
        if ($desglose.length === 0) {
            $desglose = $('<div id="pe-desglose-pago" class="pe-desglose"></div>');
            $('#pe-total-mensual').after($desglose);
        }

        $desglose.html(html);

    }).fail(function () {
        $('#pe-monto-total').text('—');
        peDesgloseActual = null;
    });
}

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

$(document).on('click', '#pe-btn-pagar', function () {
    if (pePagosProcesando) return;

    // 1. Validar desglose del backend
    if (!peDesgloseActual || peDesgloseActual.total <= 0) {
        peAlert(4, 'error', 'Espera un momento mientras se calcula el total...');
        return;
    }

    // 2. Recolectar componentes
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

    // ═══════════════════════════════════════════════════════════
    // PASO A: Crear la empresa (estado = 5 = instalación)
    // ═══════════════════════════════════════════════════════════
    var empresaPayload = {
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
        data: JSON.stringify(empresaPayload),
    }).done(function (resEmpresa) {
        if (!resEmpresa.success) {
            peAlert(4, 'error', resEmpresa.message || 'Error al crear la empresa');
            btn.prop('disabled', false).html(textoOriginal);
            pePagosProcesando = false;
            return;
        }

        // 🔑 Guardar datos de la empresa en localStorage (para que el login
        //    los tenga listos cuando el pago se apruebe y recargue la página)
        if (resEmpresa.empresa) {
            localStorage.setItem("empresa_nombre", resEmpresa.empresa.razon_social || '');
            localStorage.setItem("empresa_logo",   resEmpresa.empresa.logo || '');
            localStorage.setItem("notificacion_code", resEmpresa.empresa.notificacion_code || '');
            localStorage.setItem("fondo_sistema",  resEmpresa.empresa.fondo_sistema || '');
            localStorage.setItem("empresa_id",     resEmpresa.empresa.id || '');
        }

        // ═══════════════════════════════════════════════════════
        // PASO B: Confirmar (crear suscripción + componentes + pago)
        // ═══════════════════════════════════════════════════════
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
                peAlert(4, 'error', res.message || 'Error al procesar el pago');
                btn.prop('disabled', false).html(textoOriginal);
                pePagosProcesando = false;
                return;
            }

            // ═══════════════════════════════════════════════════
            // PASO C: Abrir widget Wompi con monto del backend
            // ═══════════════════════════════════════════════════
            peAbrirWidgetRegistro(res.pago_id, res.reference, res.monto, btn, textoOriginal);

        }).fail(function (err) {
            peAlert(4, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
            btn.prop('disabled', false).html(textoOriginal);
            pePagosProcesando = false;
        });

    }).fail(function (err) {
        peAlert(4, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        btn.prop('disabled', false).html(textoOriginal);
        pePagosProcesando = false;
    });
});

function peAbrirWidgetRegistro(pagoId, referencia, monto, btn, textoOriginal) {
    var montoCents = Math.round(monto * 100);

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
                publicKey: PP_WOMPI_PUBLIC_KEY,
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
    }).fail(function () {
        peAlert(4, 'error', 'Error al confirmar pago');
        btn.prop('disabled', false).html(textoOriginal);
        pePagosProcesando = false;
    });
}

// ============================================================
// ARRANQUE
// ============================================================

(function peInit() {
    // 🔑 Login directo se maneja aparte (no tocar)
    if (PE_MODE === 'login-direct') {
        return;
    }

    if (PE_MODE === 'register') {
        peGoStep(PE_STEP || 1);
    } else {
        peShowScreen('login');
        peReanudarSesion();   // ← NUEVO: si hay token, retoma donde quedó
    }

    if (PE_MODE === 'register' && PE_STEP === 3) {
        peCargarProgresoYIrAlPaso3();
    }

    if (PE_MODE === 'register' && PE_STEP === 4) {
        peGoStep(4);
        cargarArbolComponentes();
    }
})();

document.addEventListener('DOMContentLoaded', function () {
    if (PE_MODE === 'login-direct' && PE_DIRECT_EMAIL && PE_DIRECT_CODE) {
        peLoginDirecto();
    }
});

// ============================================================
// LOGIN DIRECTO — bypass de email/contraseña
// ============================================================

function peLoginDirecto() {
    // Ocultar pantallas normales
    document.getElementById('pe-screen-login').style.display = 'none';
    document.getElementById('pe-screen-register').style.display = 'none';

    // Mostrar pantalla de carga
    document.getElementById('pe-direct-loading').style.display = 'block';

    $.ajax({
        url: base_web + 'login-direct',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
        },
        data: {
            email:      PE_DIRECT_EMAIL,
            code_login: PE_DIRECT_CODE,
        },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            peDirectError(res.message || 'No se pudo iniciar sesión.');
            return;
        }

        // Guardar token y datos
        localStorage.setItem('auth_token', res.token_type + ' ' + res.access_token);
        localStorage.setItem('empresa_nombre', res.empresa?.razon_social || '');
        localStorage.setItem('empresa_logo', res.empresa?.logo || '');
        localStorage.setItem('notificacion_code', res.notificacion_code || '');
        localStorage.setItem('fondo_sistema', res.fondo_sistema || '');

        window.location.href = '/home';
    }).fail(function (err) {
        peDirectError(err.responseJSON?.message || 'Error de conexión.');
    });
}

function peDirectError(msg) {
    document.getElementById('pe-direct-loading').style.display = 'none';

    var errorEl = document.getElementById('pe-direct-error');
    errorEl.style.display = 'block';
    errorEl.innerHTML =
        '<div class="pe-login-alert error">' + msg + '</div>' +
        '<a href="/login" class="pe-login-submit pe-login-submit-secondary" style="text-align:center;display:block;margin-top:1rem;">Ir al login</a>';
}

// ============================================================
// OTP 6 CASILLAS
// ============================================================
(function () {
    var wrap  = document.getElementById('pe-otp');
    var input = document.getElementById('pe-codigo');
    if (!wrap || !input) return;

    var boxes = wrap.querySelectorAll('.pe-otp-box');

    function paint() {
        var v = input.value;
        boxes.forEach(function (b, i) {
            b.textContent = v[i] || '';
            b.classList.toggle('is-filled', i < v.length);
            b.classList.toggle('is-active',
                document.activeElement === input && i === Math.min(v.length, boxes.length - 1));
        });
    }

    input.addEventListener('input', function () {
        input.value = input.value.replace(/\D/g, '').slice(0, 6);
        wrap.classList.remove('is-error');
        paint();
    });
    input.addEventListener('focus', function () {
        setTimeout(function () { input.setSelectionRange(input.value.length, input.value.length); }, 0);
        paint();
    });
    input.addEventListener('blur', paint);
    input.addEventListener('click', function () {
        input.setSelectionRange(input.value.length, input.value.length);
    });
    input.addEventListener('paste', function (e) {
        var texto = (e.clipboardData || window.clipboardData).getData('text') || '';
        var digitos = texto.replace(/\D/g, '').slice(0, 6);   // quita espacios, saltos de línea y todo lo que no sea número
        e.preventDefault();
        input.value = digitos;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    wrap.addEventListener('click', function () { input.focus(); });

    window.peOtpClear = function () {
        input.value = '';
        wrap.classList.remove('is-error', 'is-ok');
        paint();
        input.focus();
    };
    window.peOtpError = function () {
        wrap.classList.remove('is-error'); void wrap.offsetWidth; wrap.classList.add('is-error');
    };
    window.peOtpOk = function () { wrap.classList.add('is-ok'); };

    paint();
})();