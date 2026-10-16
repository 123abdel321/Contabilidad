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

    let ppInfo          = null;
    let ppComponentes   = [];
    let ppMeses         = 1;
    let ppDesglose      = null;
    let ppPreviewTimer  = null;
    let ppIdsContratados = [];
    let ppProcesando    = false;

    // ------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------
    function fmt(v) {
        const n = parseFloat(v) || 0;
        return '$ ' + n.toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function showState(name) {
        ['loading', 'error', 'pay', 'success'].forEach(s => {
            document.getElementById('pp-state-' + s).style.display = (s === name) ? 'block' : 'none';
        });
    }

    function alertPago(tipo, msg) {
        const el = document.getElementById('pp-alert-pago');
        el.innerHTML = '<div class="pe-login-alert ' + tipo + '">' + msg + '</div>';
    }

    function headers() {
        return {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        };
    }

    // ------------------------------------------------------------
    // INIT
    // ------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', () => {
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
        const badge    = document.getElementById('pp-estado-badge');
        const icon     = document.getElementById('pp-estado-icon');
        const titulo   = document.getElementById('pp-estado-titulo');
        const subtitulo = document.getElementById('pp-estado-subtitulo');
        const btnTexto = document.getElementById('pp-btn-pagar-texto');
        const secTitulo = document.getElementById('pp-seccion-titulo');

        const estado = ppInfo.estado_suscripcion || 'sin_suscripcion';

        if (estado === 'sin_suscripcion') {
            badge.style.display = 'none';
            btnTexto.textContent = 'Pagar ahora';
            return;
        }

        badge.style.display = 'flex';
        badge.classList.remove('al-dia', 'vencida');

        if (estado === 'al_dia') {
            badge.classList.add('al-dia');
            icon.innerHTML = '<i class="fas fa-check-circle"></i>';
            titulo.textContent = 'Tu suscripción está al día';

            // Calcular hasta cuándo está cubierto
            let subtituloTxt = '';
            if (ppInfo.fecha_proximo_cobro) {
                const fecha = new Date(ppInfo.fecha_proximo_cobro + 'T00:00:00');
                const fechaFmt = fecha.toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' });
                subtituloTxt = 'Cubierto hasta el ' + fechaFmt;
            }

            // Días restantes
            if (ppInfo.dias_restantes !== null) {
                if (ppInfo.dias_restantes === 0) {
                    subtituloTxt += ' · Vence hoy';
                } else if (ppInfo.dias_restantes > 0) {
                    subtituloTxt += ' · ' + ppInfo.dias_restantes + ' día(s) restante(s)';
                }
            }

            subtitulo.textContent = subtituloTxt;
            btnTexto.textContent = 'Extender suscripción';
            secTitulo.textContent = '➕ Agregar módulos (opcional)';

        } else if (estado === 'vencida') {
            badge.classList.add('vencida');
            icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            titulo.textContent = 'Tu suscripción está vencida';

            let subtituloTxt = '';
            if (ppInfo.fecha_proximo_cobro) {
                const fecha = new Date(ppInfo.fecha_proximo_cobro + 'T00:00:00');
                const fechaFmt = fecha.toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric' });
                subtituloTxt = 'Venció el ' + fechaFmt;
            }
            if (ppInfo.dias_restantes !== null && ppInfo.dias_restantes < 0) {
                subtituloTxt += ' · ' + Math.abs(ppInfo.dias_restantes) + ' día(s) en mora';
            }

            subtitulo.textContent = subtituloTxt;
            btnTexto.textContent = 'Pagar y reactivar';
            secTitulo.textContent = '➕ Agregar módulos (opcional)';
        }
    }

    function renderComponentesActuales(comp) {
        let html = '';
        if (!comp.length) {
            html = '<p style="color: #64748b; font-size: 0.8125rem; text-align: center; padding: 0.75rem;">No tienes componentes aún.</p>';
        } else {
            comp.forEach(c => {
                html += '<div class="pp-actual-item">' +
                    '<span class="pp-actual-nombre">' + c.nombre + (c.cantidad > 1 ? ' <span style="color:#94a3b8;">×' + c.cantidad + '</span>' : '') + '</span>' +
                    '<span class="pp-precio">' + fmt(c.precio_mensual) + '</span>' +
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

                // 🔑 Calcular el total inicial al cargar
                calcularTotales();
            });
    }

        function renderArbol() {
        let html = '';

        ppComponentes.forEach(c => {
            // 🔑 Si el componente padre ya está contratado, saltarlo
            if (ppIdsContratados.includes(c.id)) return;

            if (c.tipo === 0) {
                // Componente simple
                html += '<label class="pp-item">' +
                    '<div class="pp-item-left">' +
                        '<input type="checkbox" class="pp-check pp-check-componente" data-id="' + c.id + '" data-precio="' + c.precio + '">' +
                        '<span class="pp-item-nombre">' + c.nombre + '</span>' +
                    '</div>' +
                    '<span class="pp-item-precio">' + fmt(c.precio) + '</span>' +
                '</label>';
            } else {
                // 🔑 Filtrar hijos ya contratados
                const hijosDisponibles = c.hijos.filter(h => !ppIdsContratados.includes(h.id));

                // Si no quedan hijos disponibles, saltar el padre entero
                if (hijosDisponibles.length === 0) return;

                html += '<div style="margin-bottom: 0.35rem;">' +
                    '<label class="pp-item pp-item-padre">' +
                        '<div class="pp-item-left">' +
                            '<input type="checkbox" class="pp-check pp-check-padre" data-id="' + c.id + '">' +
                            '<span class="pp-item-nombre">' + c.nombre + '</span>' +
                        '</div>' +
                    '</label>' +
                    '<div class="pp-hijos" data-padre="' + c.id + '">';

                hijosDisponibles.forEach(h => {
                    html += '<label class="pp-item pp-item-hijo">' +
                        '<div class="pp-item-left">' +
                            '<input type="radio" name="pp-rad-' + c.id + '" class="pp-radio pp-radio-hijo" data-padre="' + c.id + '" data-id="' + h.id + '" data-precio="' + h.precio + '">' +
                            '<span class="pp-item-nombre">' + h.nombre + '</span>' +
                        '</div>' +
                        '<span class="pp-item-precio">' + fmt(h.precio) + '</span>' +
                    '</label>';
                });

                html += '</div></div>';
            }
        });

        if (html === '') {
            html = '<p style="color: #64748b; font-size: 0.75rem; text-align: center; padding: 1rem;">Ya tienes todos los módulos contratados.</p>';
        }

        document.getElementById('pp-arbol-componentes').innerHTML = html;
    }

    function bindEventos() {
        document.getElementById('pp-arbol-componentes').addEventListener('change', e => {
            if (e.target.classList.contains('pp-check-padre')) {
                const id = e.target.dataset.id;
                const hijos = document.querySelector('.pp-hijos[data-padre="' + id + '"]');
                if (e.target.checked) {
                    hijos.style.display = 'block';
                } else {
                    hijos.style.display = 'none';
                    document.querySelectorAll('input[name="pp-rad-' + id + '"]').forEach(r => r.checked = false);
                }
            }
            calcularTotales();
        });

        document.getElementById('pp-btn-sumar-mes').onclick = () => { ppMeses = Math.min(24, ppMeses + 1); document.getElementById('pp-meses').value = ppMeses; calcularTotales(); };
        document.getElementById('pp-btn-restar-mes').onclick = () => { ppMeses = Math.max(1, ppMeses - 1); document.getElementById('pp-meses').value = ppMeses; calcularTotales(); };
        document.getElementById('pp-meses').onchange = e => {
            let v = parseInt(e.target.value) || 1;
            v = Math.max(1, Math.min(24, v));
            ppMeses = v; e.target.value = v;
            calcularTotales();
        };

        document.getElementById('pp-btn-pagar').onclick = pagar;
    }

    function recolectarExtras() {
        const extras = [];
        document.querySelectorAll('.pp-check-componente:checked').forEach(el => {
            const id = parseInt(el.dataset.id);
            if (ppIdsContratados.includes(id)) return;   // 🔑 ya contratado, saltar
            extras.push({ id: id, precio: parseFloat(el.dataset.precio) || 0 });
        });
        document.querySelectorAll('.pp-check-padre:checked').forEach(padre => {
            const id = padre.dataset.id;
            const hijo = document.querySelector('input[name="pp-rad-' + id + '"]:checked');
            if (hijo) {
                const idHijo = parseInt(hijo.dataset.id);
                if (ppIdsContratados.includes(idHijo)) return;   // 🔑 ya contratado, saltar
                extras.push({ id: idHijo, precio: parseFloat(hijo.dataset.precio) || 0 });
            }
        });
        return extras;
    }

    function calcularTotales() {
        const extras = recolectarExtras();
        const totalExtra = extras.reduce((s, c) => s + c.precio, 0);
        document.getElementById('pp-total-mensual').querySelector('strong').textContent = fmt(totalExtra);

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
            if (!res.success) {
                document.getElementById('pp-monto-total').textContent = '—';
                ppDesglose = null;
                return;
            }
            ppDesglose = res.data;
            document.getElementById('pp-monto-total').textContent = fmt(res.data.total);

            let html = '';
            res.data.detalle_meses.forEach(d => {
                if (d.monto > 0) {
                    html += '<div style="display:flex; justify-content:space-between; font-size:0.8125rem; color:#cbd5e1; padding:0.15rem 0;">' +
                        '<span>' + d.label + '</span><span>' + fmt(d.monto) + '</span></div>';
                }
            });
            html += '<hr style="border-color: rgba(148,163,184,0.15); margin: 0.5rem 0;">';
            html += '<div style="display:flex; justify-content:space-between; font-size:0.8125rem; color:#cbd5e1;"><span>Subtotal</span><span>' + fmt(res.data.subtotal) + '</span></div>';
            if (res.data.descuento > 0) {
                html += '<div style="display:flex; justify-content:space-between; font-size:0.8125rem; color:#22c55e;"><span>Descuento ' + res.data.porcentaje + '%</span><span>− ' + fmt(res.data.descuento) + '</span></div>';
            }
            html += '<hr style="border-color: rgba(148,163,184,0.15); margin: 0.5rem 0;">';

            // 🔑 CAMBIO AQUÍ: la etiqueta del TOTAL cambia según si está al día
            const esAlDia = ppInfo && ppInfo.estado_suscripcion === 'al_dia';
            html += '<div style="display:flex; justify-content:space-between; font-weight:700;">' +
                    '<span>' + (esAlDia ? 'TOTAL A EXTENDER' : 'TOTAL') + '</span>' +
                    '<span>' + fmt(res.data.total) + '</span></div>';

            const el = document.getElementById('pp-desglose-pago');
            el.innerHTML = html;
            el.style.display = 'block';
        });
    }

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