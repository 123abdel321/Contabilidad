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
// HELPERS (safe DOM)
// ============================================================
const $id = (id) => document.getElementById(id);

const on = (id, ev, fn) => {
    const el = $id(id);
    if (el) el.addEventListener(ev, fn);
    return el;
};

function peTogglePass() {
    var input = $id('password_login');
    var icon = $id('pe-eye-icon');
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}

function peAlert(panel, tipo, mensaje) {
    var el = $id('pe-alert-' + panel);
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
    var elLogin = $id('pe-screen-login');
    var elReg   = $id('pe-screen-register');

    if (elLogin) { elLogin.style.display = 'none'; elLogin.classList.remove('active'); }
    if (elReg)   { elReg.style.display = 'none';   elReg.classList.remove('active'); }

    var target = screen === 'login' ? elLogin : elReg;
    if (target) {
        target.style.display = 'block';
        target.classList.add('active');
    }
}

function peGoStep(n) {
    document.querySelectorAll('#pe-screen-register .pe-login-panel').forEach(function (p) {
        p.classList.remove('active');
    });

    document.querySelectorAll('#pe-screen-register .pe-login-step').forEach(function (s) {
        s.classList.remove('active', 'done');
    });

    var panel = $id('pe-panel-' + n);
    if (panel) panel.classList.add('active');

    var ind = $id('pe-step-indicator-' + n);
    if (ind) ind.classList.add('active');

    for (var i = 1; i < n; i++) {
        var el = $id('pe-step-indicator-' + i);
        if (el) el.classList.add('done');
    }

    if (n === 2 && window.peOtpClear) setTimeout(peOtpClear, 150);

    peShowScreen('register');
}

// Link "Regístrate" del login
on('pe-link-registro', 'click', function () {
    peSetVerifyOnly(false);
    peGoStep(1);
});

on('pe-link-volver-login', 'click', function () {
    peShowScreen('login');
});

// ============================================================
// MODO "SOLO VERIFICAR" + RETOMAR PROGRESO
// ============================================================
var peVerifyOnly = false;

function peSetVerifyOnly(on_) {
    peVerifyOnly = on_;

    var title = $id('pe-register-title');
    if (title) title.textContent = on_ ? 'Verifica tu correo' : 'Crear cuenta';

    var stepper = $id('pe-stepper');
    if (stepper) stepper.style.display = on_ ? 'none' : '';

    var salir = $id('pe-btn-salir-verify');
    if (salir) salir.style.display = on_ ? 'block' : 'none';
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
        global: false,
    }).done(function (res) {
        if (res.success && res.needs) {
            peAplicarNeeds(res);
        }
    }).fail(function () {
        localStorage.removeItem('auth_token');
    });
}

// Salir de la verificación
on('pe-btn-salir-verify', 'click', function () {
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
    var email = $('#email_login').val();
    var password = $('#password_login').val();

    if (!email || !password) {
        $('#error-login').text('Completa todos los campos').show();
        return;
    }

    localStorage.removeItem("auth_token");
    localStorage.removeItem("empresa_nombre");
    localStorage.removeItem("empresa_logo");
    localStorage.removeItem("notificacion_code");
    localStorage.removeItem("fondo_sistema");

    $('#error-login').hide();
    $("#button-login-loading").show();
    $("#button-login").hide();

    grecaptcha.ready(function () {
        grecaptcha.execute(PE_RECAPTCHA_SITE_KEY, { action: 'login' }).then(function (token) {
            $.ajax({
                url: base_web + 'login',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                method: 'POST',
                data: {
                    "email": email,
                    "password": password,
                    "g-recaptcha-response": token
                },
                dataType: 'json',
            }).done((res) => {
                $("#button-login-loading").hide();
                $("#button-login").show();

                if (!res.success) {
                    $('#error-login').text(res.message || 'Usuario o contraseña incorrectos').show();
                    return;
                }

                localStorage.setItem("auth_token", res.token_type + ' ' + res.access_token);
                peAplicarNeeds(res);

            }).fail((err) => {
                $("#button-login-loading").hide();
                $("#button-login").show();
                $('#error-login').text('Usuario o contraseña incorrectos').show();
            });
        }).catch(function (e) {
            console.error('Error reCAPTCHA:', e);
            $("#button-login-loading").hide();
            $("#button-login").show();
            $('#error-login').text('Error de seguridad. Recarga la página.').show();
        });
    });
}

function peCargarProgresoYIrAlPaso3() {
    var rutLabel = $id('pe-rut-label');
    var btnOmitir = $id('pe-btn-omitir-rut');
    var rutDatos = $id('pe-rut-datos');
    if (rutLabel) rutLabel.style.display = 'block';
    if (btnOmitir) btnOmitir.style.display = 'block';
    if (rutDatos) rutDatos.style.display = 'none';

    $.ajax({
        url: base_url + 'register-mi-progreso',
        method: 'GET',
        headers: peHeaders(),
    }).done(function (res) {
        if (res.success && res.data.empresa) {
            var emp = res.data.empresa;
            var setVal = function (id, v) { var el = $id(id); if (el) el.value = v || ''; };
            setVal('pe-nit', emp.nit);
            setVal('pe-dv', emp.dv);
            setVal('pe-tipo-contribuyente', emp.tipo_contribuyente);
            setVal('pe-razon-social', emp.razon_social);
            setVal('pe-primer-apellido', emp.primer_apellido);
            setVal('pe-segundo-apellido', emp.segundo_apellido);
            setVal('pe-primer-nombre', emp.primer_nombre);
            setVal('pe-otros-nombres', emp.otros_nombres);
            setVal('pe-direccion', emp.direccion);
            setVal('pe-email-empresa', emp.email);
            setVal('pe-telefono', emp.telefono);

            if (rutDatos) rutDatos.style.display = 'block';
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

on('pe-btn-crear-cuenta', 'click', function () {
    var btn = this;
    if (btn.disabled) return;

    var nombre   = ($id('pe-nombre')   || {}).value || '';
    var email    = ($id('pe-email')    || {}).value || '';
    var password = ($id('pe-password') || {}).value || '';

    if (!nombre || !email || !password) {
        peAlert(1, 'error', 'Completa todos los campos.');
        return;
    }

    var textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="pe-login-spinner"></span> Creando cuenta...';

    grecaptcha.ready(function () {
        grecaptcha.execute(PE_RECAPTCHA_SITE_KEY, { action: 'register' }).then(function (token) {
            $.ajax({
                url: base_url + 'register-crear-cuenta',
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
                data: JSON.stringify({
                    nombre:   nombre,
                    email:    email,
                    password: password,
                    'g-recaptcha-response': token,
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
                peSetVerifyOnly(false);
                peGoStep(2);
            })
            .fail(function (err) {
                peAlert(1, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            });

        }).catch(function (e) {
            console.error('Error al obtener token de reCAPTCHA:', e);
            peAlert(1, 'error', 'Error de verificación de seguridad. Recarga la página.');
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        });
    });
});

// ============================================================
// REGISTRO — PASO 2: Verificar código
// ============================================================

on('pe-btn-verificar', 'click', function () {
    var btn = this;
    if (btn.disabled) return;

    var textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="pe-login-spinner"></span> Verificando...';

    var codigoEl = $id('pe-codigo');
    var codigo = codigoEl ? codigoEl.value : '';

    $.ajax({
        url: base_url + 'register-verificar-codigo',
        method: 'POST',
        headers: peHeaders(),
        data: JSON.stringify({ codigo: codigo }),
    })
    .done(function (res) {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        if (!res.success) {
            peAlert(2, 'error', res.message || 'Código incorrecto');
            if (window.peOtpError) peOtpError();
            return;
        }
        if (window.peOtpOk) peOtpOk();
        setTimeout(function () { peAplicarNeeds(res); }, 600);
    })
    .fail(function (err) {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
        peAlert(2, 'error', err.responseJSON ? err.responseJSON.message : 'Error de conexión');
        if (window.peOtpError) peOtpError();
    });
});

on('pe-btn-reenviar', 'click', function () {
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

on('pe-rut-input', 'change', function () {
    var input = this;
    var file = input.files[0];
    if (!file) return;

    var formData = new FormData();
    formData.append('rut', file);

    var labelRut = $id('pe-rut-label');
    if (!labelRut) return;

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
        var inner = labelRut.querySelector('input');
        if (inner) inner.value = '';

        if (!res.success) {
            labelRut.innerHTML = htmlOriginal;
            peAlert(3, 'error', res.message || 'Error al procesar RUT');
            return;
        }

        labelRut.innerHTML = '<div>✅ RUT cargado — clic para reemplazar</div><small>PDF · Máximo 10 MB</small>';

        var rutDatos = $id('pe-rut-datos');
        if (rutDatos) rutDatos.style.display = 'block';

        var btnOmitir = $id('pe-btn-omitir-rut');
        if (btnOmitir) btnOmitir.style.display = 'none';

        var d = res.datos || {};
        var setVal = function (id, v) { var el = $id(id); if (el) el.value = v || ''; };
        setVal('pe-nit', d.nit);
        setVal('pe-dv', d.dv);
        setVal('pe-tipo-contribuyente', d.tipo_contribuyente);
        setVal('pe-razon-social', d.razon_social);
        setVal('pe-primer-apellido', d.primer_apellido);
        setVal('pe-segundo-apellido', d.segundo_apellido);
        setVal('pe-primer-nombre', d.primer_nombre);
        setVal('pe-otros-nombres', d.otros_nombres);
        setVal('pe-direccion', d.direccion);
        setVal('pe-email-empresa', d.email);
        setVal('pe-telefono', d.telefono_1);

        peTogglePersonaNatural();
    })
    .fail(function (err) {
        input.value = '';
        labelRut.innerHTML = htmlOriginal;
        peAlert(3, 'error', err.responseJSON ? err.responseJSON.message : 'Error al procesar el RUT');
    });
});

on('pe-tipo-contribuyente', 'change', peTogglePersonaNatural);

function peTogglePersonaNatural() {
    var tipoEl = $id('pe-tipo-contribuyente');
    if (!tipoEl) return;
    var tipo = tipoEl.value;

    var fieldRazon = $id('pe-field-razon-social');
    var fieldNat   = $id('pe-field-persona-natural');

    if (tipo === '2') {
        if (fieldRazon) fieldRazon.style.display = 'none';
        if (fieldNat)   fieldNat.style.display = 'block';
    } else {
        if (fieldRazon) fieldRazon.style.display = 'block';
        if (fieldNat)   fieldNat.style.display = 'none';
    }
}

on('pe-btn-omitir-rut', 'click', function () {
    var rutLabel = $id('pe-rut-label');
    var btnOmitir = $id('pe-btn-omitir-rut');
    var rutDatos = $id('pe-rut-datos');
    var tipo = $id('pe-tipo-contribuyente');

    if (rutLabel) rutLabel.style.display = 'none';
    if (btnOmitir) btnOmitir.style.display = 'none';
    if (rutDatos) rutDatos.style.display = 'block';
    if (tipo) tipo.value = '1';
    peTogglePersonaNatural();
});

// ============================================================
// REGISTRO — PASO 3 → PASO 4
// ============================================================

on('pe-btn-continuar-paso4', 'click', function () {
    var getVal = function (id) { var el = $id(id); return el ? el.value.trim() : ''; };

    var nit            = getVal('pe-nit');
    var dv             = getVal('pe-dv');
    var tipo           = getVal('pe-tipo-contribuyente');
    var razonSocial    = getVal('pe-razon-social');
    var primerNombre   = getVal('pe-primer-nombre');
    var primerApellido = getVal('pe-primer-apellido');

    if (!nit) { peAlert(3, 'error', 'El NIT es requerido.'); return; }
    if (!dv)  { peAlert(3, 'error', 'El DV es requerido.'); return; }
    if (!tipo){ peAlert(3, 'error', 'Selecciona el tipo de contribuyente.'); return; }
    if (tipo === '1' && !razonSocial) { peAlert(3, 'error', 'La razón social es requerida.'); return; }
    if (tipo === '2' && (!primerNombre || !primerApellido)) {
        peAlert(3, 'error', 'El nombre y apellido son requeridos para persona natural.');
        return;
    }

    var alert3 = $id('pe-alert-3');
    if (alert3) alert3.innerHTML = '';

    cargarArbolComponentes();
    peGoStep(4);
});

// Drag & drop RUT
var rutLabelDrop = $id('pe-rut-label');
if (rutLabelDrop) {
    ['dragenter', 'dragover'].forEach(function (eventName) {
        rutLabelDrop.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            rutLabelDrop.style.borderColor = '#818cf8';
            rutLabelDrop.style.background = 'rgba(79, 70, 229, 0.1)';
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        rutLabelDrop.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            rutLabelDrop.style.borderColor = '';
            rutLabelDrop.style.background = '';
        });
    });

    rutLabelDrop.addEventListener('drop', function (e) {
        var files = e.dataTransfer.files;
        if (!files || files.length === 0) return;

        var inp = $id('pe-rut-input');
        if (!inp) return;

        inp.files = files;
        inp.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

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
    var componentes = [];

    $('.pe-check-componente:checked').each(function () {
        componentes.push({ id: $(this).data('id'), precio: parseFloat($(this).data('precio')) || 0 });
    });

    $('.pe-check-padre:checked').each(function () {
        var id = $(this).data('id');
        var $hijo = $('input[name="pe-rad-' + id + '"]:checked');
        if ($hijo.length) {
            componentes.push({ id: $hijo.data('id'), precio: parseFloat($hijo.data('precio')) || 0 });
        }
    });

    var totalMensual = 0;
    componentes.forEach(function (c) { totalMensual += c.precio; });
    $('#pe-total-mensual strong').text(formatearMoneda(totalMensual));

    if (componentes.length === 0) {
        $('#pe-monto-total').text('$ 0');
        peDesgloseActual = null;
        return;
    }

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
        data: JSON.stringify({ meses: peMeses, componentes: componentes }),
    }).done(function (res) {
        if (!res.success) {
            $('#pe-monto-total').text('—');
            peDesgloseActual = null;
            return;
        }

        peDesgloseActual = res.data;
        $('#pe-monto-total').text(formatearMoneda(res.data.total));

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

    if (!peDesgloseActual || peDesgloseActual.total <= 0) {
        peAlert(4, 'error', 'Espera un momento mientras se calcula el total...');
        return;
    }

    var componentes = [];
    $('.pe-check-componente:checked').each(function () {
        componentes.push({ id: $(this).data('id'), precio: parseFloat($(this).data('precio')) || 0 });
    });

    var padreSinHijo = false;
    $('.pe-check-padre:checked').each(function () {
        var id = $(this).data('id');
        var $hijo = $('input[name="pe-rad-' + id + '"]:checked');
        if (!$hijo.length) { padreSinHijo = true; return false; }
        componentes.push({ id: $hijo.data('id'), precio: parseFloat($hijo.data('precio')) || 0 });
    });

    if (padreSinHijo) { peAlert(4, 'error', 'Debes seleccionar una opción en cada módulo padre.'); return; }
    if (componentes.length === 0) { peAlert(4, 'error', 'Debes seleccionar al menos un componente.'); return; }

    var btn = $(this);
    var textoOriginal = btn.html();
    btn.prop('disabled', true).html('<span class="pe-login-spinner"></span> Procesando...');
    pePagosProcesando = true;

    var getVal = function (id) { var el = $id(id); return el ? el.value : ''; };
    var empresaPayload = {
        nit:                getVal('pe-nit'),
        dv:                 getVal('pe-dv'),
        tipo_contribuyente: getVal('pe-tipo-contribuyente'),
        razon_social:       getVal('pe-razon-social'),
        primer_apellido:    getVal('pe-primer-apellido'),
        segundo_apellido:   getVal('pe-segundo-apellido'),
        primer_nombre:      getVal('pe-primer-nombre'),
        otros_nombres:      getVal('pe-otros-nombres'),
        direccion:          getVal('pe-direccion'),
        email:              getVal('pe-email-empresa'),
        telefono:           getVal('pe-telefono'),
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

        if (resEmpresa.empresa) {
            localStorage.setItem("empresa_nombre", resEmpresa.empresa.razon_social || '');
            localStorage.setItem("empresa_logo",   resEmpresa.empresa.logo || '');
            localStorage.setItem("notificacion_code", resEmpresa.empresa.notificacion_code || '');
            localStorage.setItem("fondo_sistema",  resEmpresa.empresa.fondo_sistema || '');
            localStorage.setItem("empresa_id",     resEmpresa.empresa.id || '');
        }

        $.ajax({
            url: base_url + 'suscripcion/registro/confirmar',
            method: 'POST',
            headers: peHeaders(),
            data: JSON.stringify({ meses: peMeses, componentes: componentes }),
        }).done(function (res) {
            if (!res.success) {
                peAlert(4, 'error', res.message || 'Error al procesar el pago');
                btn.prop('disabled', false).html(textoOriginal);
                pePagosProcesando = false;
                return;
            }
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
        data: JSON.stringify({ reference: referencia, amount_in_cents: montoCents }),
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
            setTimeout(function () { window.location.href = '/home'; }, 1500);
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
    if (typeof PE_MODE === 'undefined') return;

    if (PE_MODE === 'login-direct') return;

    if (PE_MODE === 'register') {
        peGoStep(PE_STEP || 1);
    } else {
        peShowScreen('login');
        peReanudarSesion();
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
    if (typeof PE_MODE !== 'undefined' && PE_MODE === 'login-direct' && PE_DIRECT_EMAIL && PE_DIRECT_CODE) {
        peLoginDirecto();
    }
});

// ============================================================
// LOGIN DIRECTO
// ============================================================

function peLoginDirecto() {
    var elLogin = $id('pe-screen-login');
    var elReg   = $id('pe-screen-register');
    var elCarga = $id('pe-direct-loading');

    if (elLogin) elLogin.style.display = 'none';
    if (elReg)   elReg.style.display = 'none';
    if (elCarga) elCarga.style.display = 'block';

    $.ajax({
        url: base_web + 'login-direct',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json',
        },
        data: { email: PE_DIRECT_EMAIL, code_login: PE_DIRECT_CODE },
        dataType: 'json',
    }).done(function (res) {
        if (!res.success) {
            peDirectError(res.message || 'No se pudo iniciar sesión.');
            return;
        }

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
    var elCarga = $id('pe-direct-loading');
    if (elCarga) elCarga.style.display = 'none';

    var errorEl = $id('pe-direct-error');
    if (!errorEl) return;

    errorEl.style.display = 'block';
    errorEl.innerHTML =
        '<div class="pe-login-alert error">' + msg + '</div>' +
        '<a href="/login" class="pe-login-submit pe-login-submit-secondary" style="text-align:center;display:block;margin-top:1rem;">Ir al login</a>';
}

// ============================================================
// OTP 6 CASILLAS
// ============================================================
(function () {
    var wrap  = $id('pe-otp');
    var input = $id('pe-codigo');
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
        var digitos = texto.replace(/\D/g, '').slice(0, 6);
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