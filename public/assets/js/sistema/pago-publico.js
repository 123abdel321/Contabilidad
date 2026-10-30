// ============================================================
// PAGO PÚBLICO — /pagar/{token}
// ============================================================

(function () {
    'use strict';

    const host = window.location.host;
    let base_url;

    if (host.includes("app.portafolioerp.com")) {
        base_url = "https://app.portafolioerp.com/api/";
    } else if (host.includes("test.portafolioerp.com")) {
        base_url = "https://test.portafolioerp.com/api/";
    } else if (host.includes("localhost:8000")) {
        base_url = 'http://localhost:8000/api/';
    }

    const TOKEN = window.PP_TOKEN;
    const WOMPI_PUBLIC_KEY = window.PP_WOMPI_PUBLIC_KEY;

    let ppInfo           = null;
    let ppComponentes    = [];
    let ppMeses          = 1;
    let ppDesglose       = null;
    let ppPreviewTimer   = null;
    let ppIdsContratados = [];
    let ppProcesando     = false;

    // ------------------------------------------------------------
    // ANIMACIONES (anime.js v4) — si no carga o el usuario prefiere
    // menos movimiento, todo funciona igual sin animar.
    // ------------------------------------------------------------
    const A = window.anime;
    const REDUCE = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const CAN_ANIM = !!(A && A.animate && !REDUCE);

    function anim(target, params) {
        if (!CAN_ANIM) return null;
        return A.animate(target, params);
    }

    const $q  = (s, c) => (c || document).querySelector(s);
    const $qa = (s, c) => Array.prototype.slice.call((c || document).querySelectorAll(s));

    function buildStars() {
        const box = $q('#peStars');
        if (!box || !CAN_ANIM) return;
        for (let i = 0; i < 40; i++) {
            const s = document.createElement('span');
            s.style.left = (Math.random() * 100) + '%';
            s.style.top = (Math.random() * 100) + '%';
            s.style.animationDelay = (Math.random() * 6) + 's';
            s.style.animationDuration = (6 + Math.random() * 8) + 's';
            box.appendChild(s);
        }
    }

    function orbsPulse() {
        $qa('.pe-orb').forEach((orb, i) => {
            anim(orb, {
                translateX: [0, (i % 2 ? 1 : -1) * 40],
                translateY: [0, -30],
                scale: [1, 1.12, 1],
                duration: 12000 + i * 3000,
                loop: true,
                ease: 'inOutSine'
            });
        });
    }

    function introCard() {
        anim('#ppCard', { opacity: [0, 1], translateY: [36, 0], scale: [0.97, 1], duration: 850, ease: 'outExpo' });
        anim('.pe-login-logo', { scale: [0.4, 1], rotate: [-180, 0], opacity: [0, 1], duration: 950, delay: 200, ease: 'outBack' });
        anim('.pe-login-card-title', { opacity: [0, 1], translateY: [12, 0], duration: 650, delay: 420, ease: 'outExpo' });
        anim('.pe-login-card-subtitle', { opacity: [0, 1], translateY: [8, 0], duration: 600, delay: 540, ease: 'outExpo' });
    }

    // Entrada del contenido de pago (se llama al mostrar el estado "pay")
    function enterPay() {
        anim('.pp-reveal', {
            opacity: [0, 1],
            translateY: [18, 0],
            duration: 700,
            delay: A && A.stagger ? A.stagger(90) : 0,
            ease: 'outExpo'
        });
    }

    function enterModules() {
        const items = $qa('#pp-arbol-componentes .pe-comp');
        if (!items.length) return;
        anim(items, {
            opacity: [0, 1],
            translateX: [-16, 0],
            duration: 600,
            delay: A && A.stagger ? A.stagger(70, { start: 150 }) : 0,
            ease: 'outExpo'
        });
    }

    // Contador animado del total
    const totalState = { v: 0 };
    let countAnim = null;

    function countTo(target) {
        const el = document.getElementById('pp-monto-total');
        if (!el) return;
        if (!CAN_ANIM) {
            totalState.v = target;
            el.textContent = fmt(target);
            return;
        }
        if (countAnim) countAnim.pause();
        countAnim = A.animate(totalState, {
            v: target,
            duration: 700,
            ease: 'outExpo',
            onUpdate: () => { el.textContent = fmt(totalState.v); },
            onComplete: () => { el.textContent = fmt(target); }
        });
        anim('#pp-total-pagar', { scale: [1, 1.035, 1], duration: 450, ease: 'outQuad' });
    }

    function buttonFeedback() {
        const btn = document.getElementById('pp-btn-pagar');
        if (!btn || !CAN_ANIM) return;
        btn.addEventListener('pointerdown', () => anim(btn, { scale: [1, 0.97], duration: 120, ease: 'outQuad' }));
        ['pointerup', 'pointerleave'].forEach(ev =>
            btn.addEventListener(ev, () => anim(btn, { scale: [0.97, 1], duration: 220, ease: 'outBack' })));
    }

    function shakeAlert() {
        const el = $q('#pp-alert-pago .pe-login-alert');
        if (el) anim(el, { translateX: [0, -6, 6, -4, 4, 0], duration: 420, ease: 'inOutQuad' });
    }

    // Pantalla de éxito: check dibujado + confeti
    function playSuccess() {
        const box = document.getElementById('pp-state-success');
        box.classList.add('pp-run');
        if (!CAN_ANIM) return;

        anim('.pp-sc-circle', { strokeDashoffset: [145, 0], duration: 700, ease: 'outCubic' });
        anim('.pp-sc-tick', { strokeDashoffset: [40, 0], duration: 500, delay: 550, ease: 'outCubic' });
        anim('.pp-success-check', { scale: [0.6, 1], duration: 800, ease: 'outBack' });
        anim('.pp-success-title, .pp-success .pp-muted', {
            opacity: [0, 1], translateY: [12, 0], duration: 600,
            delay: A.stagger ? A.stagger(120, { start: 700 }) : 700, ease: 'outExpo'
        });

        const wrap = document.getElementById('ppConfetti');
        wrap.innerHTML = '';
        const colors = ['#818cf8', '#22d3ee', '#34d399', '#a78bfa', '#fbbf24'];
        const pieces = [];
        for (let i = 0; i < 28; i++) {
            const s = document.createElement('span');
            s.style.background = colors[i % colors.length];
            wrap.appendChild(s);
            pieces.push(s);
        }
        pieces.forEach((s) => {
            const ang = Math.random() * Math.PI * 2;
            const dist = 80 + Math.random() * 130;
            anim(s, {
                translateX: [0, Math.cos(ang) * dist],
                translateY: [0, Math.sin(ang) * dist + 40],
                rotate: [0, (Math.random() - 0.5) * 540],
                opacity: [{ to: 1, duration: 80 }, { to: 0, duration: 900, delay: 400 }],
                duration: 1300,
                delay: 500 + Math.random() * 150,
                ease: 'outExpo'
            });
        });
    }

    // ------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------
    function fmt(v) {
        const n = parseFloat(v) || 0;
        return '$ ' + Math.round(n).toLocaleString('es-CO');
    }

    function showState(name) {
        ['loading', 'error', 'pay', 'success'].forEach(s => {
            document.getElementById('pp-state-' + s).style.display = (s === name) ? 'block' : 'none';
        });
        if (name === 'pay') enterPay();
        if (name === 'success') playSuccess();
    }

    function alertPago(tipo, msg) {
        const el = document.getElementById('pp-alert-pago');
        el.innerHTML = '<div class="pe-login-alert ' + tipo + '">' + msg + '</div>';
        if (tipo === 'error') shakeAlert();
    }

    function headers() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        };
    }

    // Íconos y textos comerciales por id de componente
    const ICO = {
        doc:   '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
        chart: '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>',
        users: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
        cart:  '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/>',
        build: '<path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/>',
        bolt:  '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>'
    };
    const META = {
        1:  { ico: 'chart', nombre: 'Contabilidad',            sub: 'Libros, balances e impuestos automáticos' },
        2:  { ico: 'doc',   nombre: 'Facturación',             sub: 'Cotiza, factura y controla tu cartera' },
        4:  { ico: 'users', nombre: 'Nómina',                  sub: 'Liquida y transmite nómina electrónica' },
        5:  { ico: 'cart',  nombre: 'POS e inventario',        sub: 'Vende en mostrador y controla el stock' },
        6:  { ico: 'build', nombre: 'Máximo PH',               sub: 'Administración de propiedad horizontal' },
        14: { ico: 'bolt',  nombre: 'Facturación electrónica', sub: 'Emite y envía facturas a la DIAN' }
    };
    function meta(c) {
        const m = META[c.id] || {};
        return { ico: ICO[m.ico] || ICO.doc, nombre: m.nombre || c.nombre, sub: m.sub || '' };
    }

    // ------------------------------------------------------------
    // INIT
    // ------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
        buildStars();
        orbsPulse();
        introCard();
        buttonFeedback();
        showState('loading');
        cargarInfo();
    });

    function cargarInfo() {
        fetch(base_url + 'pago-publico/info/' + TOKEN, { headers: headers() })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    document.getElementById('pp-error-msg').textContent = res.message || 'Link inválido.';
                    showState('error');
                    return;
                }

                ppInfo = res.data;
                ppIdsContratados = ppInfo.ids_contratados || [];

                document.getElementById('pp-empresa-nombre').textContent = ppInfo.empresa.razon_social;
                document.getElementById('pp-empresa-nit').textContent = 'NIT: ' + (ppInfo.empresa.nit || '—');

                renderComponentesActuales(ppInfo.componentes_actuales || []);
                renderEstadoSuscripcion();

                if (ppInfo.tiene_suscripcion) {
                    document.getElementById('pp-bloque-actual').style.display = 'block';
                }

                showState('pay');
                marcarChip();
                calcularTotales();
                cargarArbolComponentes();
                bindEventos();
            })
            .catch(err => {
                document.getElementById('pp-error-msg').textContent = 'Error de conexión.';
                showState('error');
                console.error(err);
            });
    }

    function renderEstadoSuscripcion() {
        const badge     = document.getElementById('pp-estado-badge');
        const icon      = document.getElementById('pp-estado-icon');
        const titulo    = document.getElementById('pp-estado-titulo');
        const subtitulo = document.getElementById('pp-estado-subtitulo');
        const btnTexto  = document.getElementById('pp-btn-pagar-texto');

        const estado = ppInfo.estado_suscripcion || 'sin_suscripcion';

        if (estado === 'sin_suscripcion') {
            badge.style.display = 'none';
            btnTexto.textContent = 'Pagar ahora';
            return;
        }

        badge.style.display = 'flex';
        badge.classList.remove('al-dia', 'vencida');

        const fechaFmt = () => {
            const f = new Date(ppInfo.fecha_proximo_cobro + 'T00:00:00');
            return f.toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' });
        };

        if (estado === 'al_dia') {
            badge.classList.add('al-dia');
            icon.innerHTML = '<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
            titulo.textContent = 'Tu suscripción está al día';

            let txt = '';
            if (ppInfo.fecha_proximo_cobro) txt = 'Cubierto hasta el ' + fechaFmt();
            if (ppInfo.dias_restantes !== null) {
                if (ppInfo.dias_restantes === 0) txt += ' · Vence hoy';
                else if (ppInfo.dias_restantes > 0) txt += ' · ' + ppInfo.dias_restantes + ' día(s) restante(s)';
            }
            subtitulo.textContent = txt;
            btnTexto.textContent = 'Extender suscripción';

        } else if (estado === 'vencida') {
            badge.classList.add('vencida');
            icon.innerHTML = '<svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
            titulo.textContent = 'Tu suscripción está vencida';

            let txt = '';
            if (ppInfo.fecha_proximo_cobro) txt = 'Venció el ' + fechaFmt();
            if (ppInfo.dias_restantes !== null && ppInfo.dias_restantes < 0) {
                txt += ' · ' + Math.abs(ppInfo.dias_restantes) + ' día(s) en mora';
            }
            subtitulo.textContent = txt;
            btnTexto.textContent = 'Pagar y reactivar';
        }
    }

    function renderComponentesActuales(comp) {
        let html = '';
        if (!comp.length) {
            html = '<p class="pp-muted pp-center-sm">No tienes componentes aún.</p>';
        } else {
            comp.forEach(c => {
                html += '<div class="pp-actual-item">' +
                    '<svg class="pp-actual-ok" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>' +
                    '<span class="pp-actual-nombre">' + c.nombre + (c.cantidad > 1 ? ' <em>×' + c.cantidad + '</em>' : '') + '</span>' +
                    '<span class="pp-precio">' + fmt(c.precio_mensual) + '<small> / mes</small></span>' +
                '</div>';
            });
        }
        document.getElementById('pp-componentes-actuales').innerHTML = html;
    }

    function cargarArbolComponentes() {
        fetch(base_url + 'suscripcion/componentes-arbol', { headers: headers() })
            .then(r => r.json())
            .then(res => {
                if (!res.success) return;
                ppComponentes = res.data || [];
                renderArbol();
                calcularTotales();
            });
    }

    function renderArbol() {
        let html = '';

        ppComponentes.forEach(c => {
            if (ppIdsContratados.includes(c.id)) return;

            const m = meta(c);
            const icono = '<svg class="pe-comp-ico" viewBox="0 0 24 24">' + m.ico + '</svg>';
            const sub = m.sub ? '<span class="pe-comp-sub">' + m.sub + '</span>' : '';

            if (c.tipo === 0) {
                html += '<div class="pe-comp">' +
                    '<label class="pe-comp-head">' +
                        '<input type="checkbox" class="pp-check-componente" data-id="' + c.id + '" data-precio="' + c.precio + '">' +
                        '<span class="pe-comp-box"></span>' + icono +
                        '<span class="pe-comp-name">' + m.nombre + sub + '</span>' +
                        '<span class="pe-comp-price">' + fmt(c.precio) + '<small>/ mes</small></span>' +
                    '</label>' +
                '</div>';
            } else {
                const hijos = c.hijos.filter(h => !ppIdsContratados.includes(h.id));
                if (hijos.length === 0) return;

                const minimo = Math.min.apply(null, hijos.map(h => parseFloat(h.precio) || 0));

                // pe-check-padre + pp-check-padre: el CSS de login.css muestra los planes con :has(.pe-check-padre:checked)
                html += '<div class="pe-comp">' +
                    '<label class="pe-comp-head">' +
                        '<input type="checkbox" class="pe-check-padre pp-check-padre" data-id="' + c.id + '">' +
                        '<span class="pe-comp-box"></span>' + icono +
                        '<span class="pe-comp-name">' + m.nombre + sub + '</span>' +
                        '<span class="pe-comp-more">Desde ' + fmt(minimo) + '</span>' +
                    '</label>' +
                    '<div class="pe-hijos" data-padre="' + c.id + '">';

                hijos.forEach(h => {
                    html += '<label class="pe-tier">' +
                        '<input type="radio" name="pp-rad-' + c.id + '" class="pp-radio-hijo" data-padre="' + c.id + '" data-id="' + h.id + '" data-precio="' + h.precio + '">' +
                        '<span class="pe-tier-dot"></span>' +
                        '<span>' + h.nombre + '</span>' +
                        '<span class="pe-tier-price">' + fmt(h.precio) + '</span>' +
                    '</label>';
                });

                html += '</div></div>';
            }
        });

        if (html === '') {
            html = '<p class="pp-muted pp-center-sm">Ya tienes todos los módulos contratados. 🎉</p>';
        }

        document.getElementById('pp-arbol-componentes').innerHTML = html;
        enterModules();
    }

    // ------------------------------------------------------------
    // EVENTOS
    // ------------------------------------------------------------
    function marcarChip() {
        $qa('#pp-chips .pp-chip').forEach(b => {
            b.classList.toggle('active', parseInt(b.dataset.m) === ppMeses);
        });
    }

    function setMeses(v) {
        ppMeses = Math.max(1, Math.min(24, v));
        document.getElementById('pp-meses').value = ppMeses;
        marcarChip();
        calcularTotales();
    }

    function bindEventos() {
        document.getElementById('pp-arbol-componentes').addEventListener('change', e => {
            // El CSS muestra/oculta los planes; aquí solo limpiamos radios al desmarcar
            if (e.target.classList.contains('pp-check-padre') && !e.target.checked) {
                document.querySelectorAll('input[name="pp-rad-' + e.target.dataset.id + '"]').forEach(r => r.checked = false);
            }
            calcularTotales();
        });

        document.getElementById('pp-btn-sumar-mes').onclick  = () => setMeses(ppMeses + 1);
        document.getElementById('pp-btn-restar-mes').onclick = () => setMeses(ppMeses - 1);
        document.getElementById('pp-meses').onchange = e => setMeses(parseInt(e.target.value) || 1);
        $qa('#pp-chips .pp-chip').forEach(b => b.onclick = () => setMeses(parseInt(b.dataset.m)));

        document.getElementById('pp-btn-pagar').onclick = pagar;
    }

    function recolectarExtras() {
        const extras = [];
        document.querySelectorAll('.pp-check-componente:checked').forEach(el => {
            const id = parseInt(el.dataset.id);
            if (ppIdsContratados.includes(id)) return;
            extras.push({ id: id, precio: parseFloat(el.dataset.precio) || 0 });
        });
        document.querySelectorAll('.pp-check-padre:checked').forEach(padre => {
            const id = padre.dataset.id;
            const hijo = document.querySelector('input[name="pp-rad-' + id + '"]:checked');
            if (hijo) {
                const idHijo = parseInt(hijo.dataset.id);
                if (ppIdsContratados.includes(idHijo)) return;
                extras.push({ id: idHijo, precio: parseFloat(hijo.dataset.precio) || 0 });
            }
        });
        return extras;
    }

    function calcularTotales() {
        const extras = recolectarExtras();
        const totalExtra = extras.reduce((s, c) => s + c.precio, 0);

        const fila = document.getElementById('pp-total-mensual');
        fila.querySelector('strong').textContent = fmt(totalExtra);
        fila.style.display = totalExtra > 0 ? 'flex' : 'none';

        clearTimeout(ppPreviewTimer);
        ppPreviewTimer = setTimeout(() => pedirPreview(extras), 250);
    }

    function pedirPreview(extras) {
        fetch(base_url + 'pago-publico/preview', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({
                token: TOKEN,
                meses: ppMeses,
                componentes_extra: extras,
            }),
        })
        .then(r => r.json())
        .then(res => {
            const ahorro = document.getElementById('pp-ahorro');

            if (!res.success) {
                document.getElementById('pp-monto-total').textContent = '—';
                ppDesglose = null;
                ahorro.style.display = 'none';
                return;
            }
            ppDesglose = res.data;
            countTo(parseFloat(res.data.total) || 0);

            let html = '';
            res.data.detalle_meses.forEach(d => {
                if (d.monto > 0) {
                    html += '<div class="pp-line"><span>' + d.label + '</span><span>' + fmt(d.monto) + '</span></div>';
                }
            });
            html += '<div class="pp-line pp-line-sub"><span>Subtotal</span><span>' + fmt(res.data.subtotal) + '</span></div>';
            if (res.data.descuento > 0) {
                html += '<div class="pp-line pp-line-save"><span>Descuento ' + res.data.porcentaje + '%</span><span>− ' + fmt(res.data.descuento) + '</span></div>';
            }

            const el = document.getElementById('pp-desglose-pago');
            el.innerHTML = html;
            el.style.display = 'block';

            if (res.data.descuento > 0) {
                ahorro.textContent = '🎉 ¡Estás ahorrando ' + fmt(res.data.descuento) + '!';
                const eraOculto = ahorro.style.display === 'none';
                ahorro.style.display = 'block';
                if (eraOculto) anim(ahorro, { opacity: [0, 1], scale: [0.9, 1], duration: 500, ease: 'outBack' });
            } else {
                ahorro.style.display = 'none';
            }
        });
    }

    // ------------------------------------------------------------
    // PAGO (sin cambios de lógica)
    // ------------------------------------------------------------
    function pagar() {
        if (ppProcesando) return;
        if (!ppDesglose || ppDesglose.total <= 0) {
            alertPago('error', 'Espera mientras se calcula el total...');
            return;
        }

        const extras = recolectarExtras();
        const btn = document.getElementById('pp-btn-pagar');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="pe-login-spinner"></span> Procesando...';
        ppProcesando = true;

        fetch(base_url + 'pago-publico/crear-pago', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({
                token: TOKEN,
                meses: ppMeses,
                componentes_extra: extras,
            }),
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                alertPago('error', res.message || 'Error al crear el pago.');
                btn.disabled = false; btn.innerHTML = original;
                ppProcesando = false;
                return;
            }
            abrirWidget(res.pago_id, res.reference, res.monto, btn, original);
        })
        .catch(() => {
            alertPago('error', 'Error de conexión.');
            btn.disabled = false; btn.innerHTML = original;
            ppProcesando = false;
        });
    }

    function abrirWidget(pagoId, reference, monto, btn, original) {
        const montoCents = Math.round(monto * 100);

        fetch(base_url + 'pago-publico/firma-integridad', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ reference: reference, amount_in_cents: montoCents }),
        })
        .then(r => r.json())
        .then(resFirma => {
            if (!resFirma.success) {
                alertPago('error', 'No se pudo generar la firma.');
                btn.disabled = false; btn.innerHTML = original;
                ppProcesando = false;
                return;
            }

            try {
                const checkout = new WidgetCheckout({
                    currency: 'COP',
                    amountInCents: montoCents,
                    reference: reference,
                    publicKey: WOMPI_PUBLIC_KEY,
                    signature: { integrity: resFirma.signature },
                });

                checkout.open(function (result) {
                    const tx = result.transaction;
                    if (tx.status === 'APPROVED') {
                        reconciliar(pagoId, tx, reference, btn, original);
                    } else {
                        alertPago('error', 'Pago rechazado: ' + tx.status);
                        btn.disabled = false; btn.innerHTML = original;
                        ppProcesando = false;
                    }
                });
            } catch (e) {
                alertPago('error', 'No se pudo abrir el widget: ' + e.message);
                btn.disabled = false; btn.innerHTML = original;
                ppProcesando = false;
            }
        });
    }

    function reconciliar(pagoId, tx, reference, btn, original) {
        fetch(base_url + 'pago-publico/reconciliar', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({
                pago_id: pagoId,
                transaction_id: tx.id,
                reference: reference,
                payment_source_id: tx.payment_source_id || null,
            }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success && res.estado === 1) {
                showState('success');
                setTimeout(() => window.location.href = '/login', 4000);
            } else {
                alertPago('error', res.message || 'Pago no confirmado.');
                btn.disabled = false; btn.innerHTML = original;
                ppProcesando = false;
            }
        })
        .catch(() => {
            alertPago('error', 'Error al confirmar el pago.');
            btn.disabled = false; btn.innerHTML = original;
            ppProcesando = false;
        });
    }

})();