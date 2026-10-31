@extends('layouts.app_no_nav')

@section('content')

<link
    href="https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
<link
    id="pagestyle"
    href="{{ asset('assets/css/login.css') }}?v={{ config('app.version') }}"
    rel="stylesheet"
>

<script src="https://cdn.jsdelivr.net/npm/animejs@4.0.2/lib/anime.iife.min.js"></script>

<div class="pe-login" id="peLoginRoot">

    {{-- Fondo animado: orbes + grid + partículas --}}
    <div class="pe-login-bg" aria-hidden="true">
        <div class="pe-login-grid"></div>
        <div class="pe-orb pe-orb-1"></div>
        <div class="pe-orb pe-orb-2"></div>
        <div class="pe-orb pe-orb-3"></div>
        <div class="pe-login-stars" id="peStars"></div>
    </div>

    <div class="pe-login-wrap">

        <aside class="pe-brand">
            <span class="pe-brand-badge"><i></i> Suite empresarial 100% web · Certificado DIAN</span>

            <h2>Todo tu negocio, <span>en una sola plataforma</span></h2>

            <p>
                Portafolio ERP conecta facturación electrónica, contabilidad, nómina, punto de venta
                y propiedad horizontal en una única base de datos. Cada movimiento se sincroniza solo:
                sin copiar datos y sin doble digitación.
            </p>

            <div class="pe-brand-grid">
                <div class="pe-brand-item"><svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>Facturación electrónica</div>
                <div class="pe-brand-item"><svg viewBox="0 0 24 24">
                        <path d="M3 3v18h18" />
                        <path d="m19 9-5 5-4-4-3 3" />
                    </svg>Contabilidad automática</div>
                <div class="pe-brand-item"><svg viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                    </svg>Nómina electrónica</div>
                <div class="pe-brand-item"><svg viewBox="0 0 24 24">
                        <circle cx="9" cy="21" r="1" />
                        <circle cx="20" cy="21" r="1" />
                        <path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6" />
                    </svg>POS e inventario</div>
                <div class="pe-brand-item"><svg viewBox="0 0 24 24">
                        <path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4" />
                    </svg>Máximo PH</div>
                <div class="pe-brand-item"><svg viewBox="0 0 24 24">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                    </svg>Pagos en línea</div>
            </div>

            <div class="pe-brand-stats">
                <div><b>5</b><span>Módulos integrados</span></div>
                <div><b>1</b><span>Base de datos</span></div>
                <div><b>0</b><span>Doble digitación</span></div>
            </div>

            <a class="pe-brand-help"
                href="https://wa.me/573332475846?text=Hola,%20necesito%20ayuda%20con%20Portafolio%20ERP" target="_blank"
                rel="noopener">
                ¿Necesitas ayuda? <strong>Escríbenos por WhatsApp →</strong>
            </a>
        </aside>

        <div class="pe-login-card" id="peLoginCard">

            <div class="pe-login-card-header">
                <div class="pe-login-dots" aria-hidden="true">
                    <span></span><span></span><span></span>
                    <span></span><span></span><span></span>
                </div>
                <div class="pe-login-scan" aria-hidden="true"></div>

                <div class="pe-login-logo">
                    <img src="/img/logo_contabilidad.png" alt="Portafolio ERP">
                </div>
                <h1 class="pe-login-card-title">Portafolio ERP</h1>
                <p class="pe-login-card-subtitle">Suite Empresarial</p>
            </div>

            <div class="pe-login-card-body">

                {{-- PANTALLA: LOGIN DIRECTO (CARGANDO) --}}
                <div id="pe-direct-loading" class="pe-login-panel"
                    style="display: none; text-align: center; padding: 2rem 0;">
                    <div class="pe-login-spinner" style="margin: 0 auto 1rem;"></div>
                    <p style="color: #94a3b8; font-size: 0.875rem;">Iniciando sesión...</p>
                </div>

                {{-- PANTALLA: LOGIN DIRECTO (ERROR) --}}
                <div id="pe-direct-error" style="display: none; padding: 1rem 0;"></div>

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- PANTALLA: LOGIN --}}
                {{-- ═══════════════════════════════════════════════ --}}
                <div id="pe-screen-login" class="pe-login-panel active">
                    <h2 class="pe-login-form-title">Iniciar sesión</h2>

                    <div id="error-login" class="pe-login-alert" style="display: none;">
                        Usuario o contraseña incorrectos
                    </div>

                    <div class="pe-login-field">
                        <label for="email_login">Correo electrónico</label>
                        <div class="pe-login-input-wrap pe-icon-mail">
                            <input type="email" id="email_login" placeholder="tucorreo@empresa.com"
                                autocomplete="email">
                        </div>
                    </div>

                    <div class="pe-login-field">
                        <label for="password_login">Contraseña</label>
                        <div class="pe-login-input-wrap pe-icon-lock">
                            <input type="password" id="password_login" placeholder="••••••••"
                                autocomplete="current-password" onkeypress="changePassWord(event)">
                            <button type="button" class="pe-login-toggle-pass" onclick="peTogglePass()"
                                aria-label="Mostrar contraseña">
                                <svg id="pe-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="button" id="button-login" class="pe-login-submit">
                        Ingresar
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="12 5 19 12 12 19" />
                        </svg>
                    </button>

                    <button type="button" id="button-login-loading" class="pe-login-submit" style="display: none;"
                        disabled>
                        <span class="pe-login-spinner"></span>
                        Verificando...
                    </button>

                    <div class="pe-login-register-row">
                        <span>¿No tienes cuenta?</span>
                        <a href="javascript:void(0)" id="pe-link-registro">Regístrate</a>
                    </div>

                    <p class="pe-login-version">
                        v{{ config('app.version', '2.0.0') }} · {{ date('Y') }}
                    </p>
                </div>

                {{-- ═══════════════════════════════════════════════ --}}
                {{-- PANTALLA: REGISTRO (4 pasos unificados) --}}
                {{-- ═══════════════════════════════════════════════ --}}
                <div id="pe-screen-register" class="pe-login-panel" style="display: none;">

                    <h2 class="pe-login-form-title" id="pe-register-title">Crear cuenta</h2>

                    <div class="pe-login-stepper" id="pe-stepper">
                        <div class="pe-login-step active" id="pe-step-indicator-1">
                            <div class="pe-login-step-circle">1</div>
                            <div class="pe-login-step-label">Cuenta</div>
                        </div>
                        <div class="pe-login-step" id="pe-step-indicator-2">
                            <div class="pe-login-step-circle">2</div>
                            <div class="pe-login-step-label">Verificación</div>
                        </div>
                        <div class="pe-login-step" id="pe-step-indicator-3">
                            <div class="pe-login-step-circle">3</div>
                            <div class="pe-login-step-label">Empresa</div>
                        </div>
                        <div class="pe-login-step" id="pe-step-indicator-4">
                            <div class="pe-login-step-circle">4</div>
                            <div class="pe-login-step-label">Paquete</div>
                        </div>
                    </div>

                    {{-- PASO 1: Cuenta --}}
                    <div class="pe-login-panel active" id="pe-panel-1">
                        <div id="pe-alert-1"></div>

                        <div class="pe-login-field">
                            <label for="pe-nombre">Nombre completo</label>
                            <div class="pe-login-input-wrap">
                                <input type="text" id="pe-nombre" placeholder="Tu nombre">
                            </div>
                        </div>

                        <div class="pe-login-field">
                            <label for="pe-email">Correo electrónico</label>
                            <div class="pe-login-input-wrap">
                                <input type="email" id="pe-email" placeholder="tucorreo@empresa.com">
                            </div>
                        </div>

                        <div class="pe-login-field">
                            <label for="pe-password">Contraseña</label>
                            <div class="pe-login-input-wrap">
                                <input type="password" id="pe-password" placeholder="Mínimo 6 caracteres">
                            </div>
                        </div>

                        <button class="pe-login-submit" id="pe-btn-crear-cuenta">
                            Crear cuenta
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </button>

                        <button type="button" class="pe-login-submit pe-login-submit-secondary"
                            id="pe-link-volver-login" style="margin-top: 0.75rem;">
                            Volver al login
                        </button>
                    </div>

                    {{-- PASO 2: Verificación --}}
                    <div class="pe-login-panel" id="pe-panel-2">
                        <div id="pe-alert-2"></div>

                        <div class="pe-login-field">
                            <label for="pe-codigo">Código de verificación</label>
                        
                            <div class="pe-otp" id="pe-otp">
                                <div class="pe-otp-box"></div>
                                <div class="pe-otp-box"></div>
                                <div class="pe-otp-box"></div>
                                <div class="pe-otp-box"></div>
                                <div class="pe-otp-box"></div>
                                <div class="pe-otp-box"></div>
                        
                                <!-- El input REAL sigue siendo #pe-codigo, así tu JS no cambia.
                                    Va encima de las casillas, invisible, y recibe todo lo que se escriba/pegue. -->
                                <input type="text" id="pe-codigo" class="pe-otp-input"
                                    inputmode="numeric" pattern="[0-9]*"
                                    autocomplete="one-time-code" aria-label="Código de 6 dígitos">
                            </div>
                        </div>

                        <button class="pe-login-submit" id="pe-btn-verificar">
                            Verificar código
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </button>

                        <button class="pe-login-submit pe-login-submit-secondary" id="pe-btn-reenviar">
                            Reenviar código
                        </button>

                        <button type="button" class="pe-login-submit pe-login-submit-secondary" id="pe-btn-salir-verify" style="display:none; margin-top:.5rem;">
                            Usar otra cuenta
                        </button>
                    </div>

                    {{-- PASO 3: Empresa --}}
                    <div class="pe-login-panel" id="pe-panel-3">
                        <div id="pe-alert-3"></div>

                        <label class="pe-login-file" for="pe-rut-input" id="pe-rut-label">
                            <input type="file" id="pe-rut-input" accept="application/pdf">
                            <div>📄 Arrastra tu RUT aquí o haz clic para subir</div>
                            <small>PDF · Máximo 10 MB</small>
                        </label>

                        <button type="button" class="pe-login-submit pe-login-submit-secondary" id="pe-btn-omitir-rut">
                            Omitir RUT y llenar manualmente
                        </button>

                        <div id="pe-rut-datos" style="display: none; margin-top: 1rem;">

                            <div class="pe-login-row">
                                <div class="pe-login-field">
                                    <label>NIT / Documento</label>
                                    <div class="pe-login-input-wrap">
                                        <input type="text" id="pe-nit">
                                    </div>
                                </div>
                                <div class="pe-login-field">
                                    <label>DV</label>
                                    <div class="pe-login-input-wrap">
                                        <input type="text" id="pe-dv" maxlength="1">
                                    </div>
                                </div>
                            </div>

                            <div class="pe-login-field">
                                <label>Tipo de contribuyente</label>
                                <div class="pe-login-input-wrap">
                                    <select id="pe-tipo-contribuyente">
                                        <option value="">Selecciona...</option>
                                        <option value="1">Persona jurídica</option>
                                        <option value="2">Persona natural</option>
                                    </select>
                                </div>
                            </div>

                            <div class="pe-login-field" id="pe-field-razon-social">
                                <label>Razón social</label>
                                <div class="pe-login-input-wrap">
                                    <input type="text" id="pe-razon-social">
                                </div>
                            </div>

                            <div id="pe-field-persona-natural" style="display: none;">
                                <div class="pe-login-row">
                                    <div class="pe-login-field">
                                        <label>Primer apellido</label>
                                        <div class="pe-login-input-wrap">
                                            <input type="text" id="pe-primer-apellido">
                                        </div>
                                    </div>
                                    <div class="pe-login-field">
                                        <label>Segundo apellido</label>
                                        <div class="pe-login-input-wrap">
                                            <input type="text" id="pe-segundo-apellido">
                                        </div>
                                    </div>
                                </div>
                                <div class="pe-login-row">
                                    <div class="pe-login-field">
                                        <label>Primer nombre</label>
                                        <div class="pe-login-input-wrap">
                                            <input type="text" id="pe-primer-nombre">
                                        </div>
                                    </div>
                                    <div class="pe-login-field">
                                        <label>Otros nombres</label>
                                        <div class="pe-login-input-wrap">
                                            <input type="text" id="pe-otros-nombres">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pe-login-field">
                                <label>Dirección</label>
                                <div class="pe-login-input-wrap">
                                    <input type="text" id="pe-direccion">
                                </div>
                            </div>

                            <div class="pe-login-row">
                                <div class="pe-login-field">
                                    <label>Email</label>
                                    <div class="pe-login-input-wrap">
                                        <input type="email" id="pe-email-empresa">
                                    </div>
                                </div>
                                <div class="pe-login-field">
                                    <label>Teléfono</label>
                                    <div class="pe-login-input-wrap">
                                        <input type="text" id="pe-telefono">
                                    </div>
                                </div>
                            </div>

                            <button class="pe-login-submit" id="pe-btn-continuar-paso4">
                                Continuar
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </button>

                        </div>
                    </div>

                    {{-- PASO 4: Componentes --}}
                    <div class="pe-login-panel" id="pe-panel-4">
                        <div id="pe-alert-4"></div>

                        <div id="pe-arbol-componentes">
                            <div class="text-center text-muted small"><i class="fas fa-spinner fa-spin"></i> Cargando...
                            </div>
                        </div>

                        <hr class="pe-login-hr">

                        <div class="pe-login-duration">
                            <span>Duración:</span>
                            <div class="pe-login-duration-ctrl">
                                <button type="button" id="pe-btn-restar-mes"
                                    class="pe-login-submit-secondary pe-login-btn-square">−</button>
                                <input type="number" id="pe-meses" value="1" min="1" max="24">
                                <button type="button" id="pe-btn-sumar-mes"
                                    class="pe-login-submit-secondary pe-login-btn-square">+</button>
                            </div>
                        </div>

                        <div id="pe-total-mensual" class="pe-login-total-row">
                            <span>Total mensual:</span>
                            <strong>$ 0</strong>
                        </div>

                        <div id="pe-total-pagar" class="pe-login-total-pagar">
                            <span>TOTAL A PAGAR:</span>
                            <strong id="pe-monto-total">$ 0</strong>
                        </div>

                        <button class="pe-login-submit" id="pe-btn-pagar" style="margin-top: 1rem;">
                            Pagar y activar
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </button>
                    </div>

                    <p class="pe-login-version">
                        v{{ config('app.version', '2.0.0') }} · {{ date('Y') }}
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    var PE_MODE = '{{ $mode ?? "login" }}';   // 'login' o 'register'
    var PE_STEP = {{ $step ?? 1 }};           // 1, 2, 3 o 4
    var PE_DIRECT_EMAIL = '{{ $email ?? "" }}';
    var PE_DIRECT_CODE = '{{ $code_login ?? "" }}';
    var PP_WOMPI_PUBLIC_KEY = '{{ config("services.wompi.public_key") }}';
</script>

<script>
    /* ============================================================
       ANIMACIONES DEL LOGIN (anime.js)
       Solo diseño — no toca la lógica existente.
       ============================================================ */
    (function () {
        'use strict';

        var A = window.anime;
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!A || !A.animate || reduce) return;

        var animate = A.animate;
        var stagger = A.stagger;
        var $ = function (s, c) { return (c || document).querySelector(s); };
        var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

        function buildStars() {
            var box = $('#peStars');
            if (!box) return;
            for (var i = 0; i < 40; i++) {
                var s = document.createElement('span');
                s.style.left = (Math.random() * 100) + '%';
                s.style.top = (Math.random() * 100) + '%';
                s.style.animationDelay = (Math.random() * 6) + 's';
                s.style.animationDuration = (6 + Math.random() * 8) + 's';
                box.appendChild(s);
            }
        }

        function introCard() {
            var card = $('#peLoginCard');
            if (!card) return;

            animate(card, {
                opacity: [0, 1],
                translateY: [40, 0],
                scale: [0.96, 1],
                duration: 900,
                ease: 'outExpo'
            });

            var logo = $('.pe-login-logo');
            if (logo) {
                animate(logo, {
                    scale: [0.4, 1],
                    rotate: [-180, 0],
                    opacity: [0, 1],
                    duration: 1000,
                    delay: 250,
                    ease: 'outBack'
                });
            }

            var title = $('.pe-login-card-title');
            if (title) {
                animate(title, {
                    opacity: [0, 1],
                    translateY: [12, 0],
                    duration: 700,
                    delay: 500,
                    ease: 'outExpo'
                });
            }

            var subtitle = $('.pe-login-card-subtitle');
            if (subtitle) {
                animate(subtitle, {
                    opacity: [0, 1],
                    translateY: [8, 0],
                    duration: 600,
                    delay: 650,
                    ease: 'outExpo'
                });
            }

            var fields = $$('.pe-login-card-body .pe-login-field');
            if (fields.length) {
                animate(fields, {
                    opacity: [0, 1],
                    translateY: [16, 0],
                    duration: 700,
                    delay: stagger(90, { start: 750 }),
                    ease: 'outExpo'
                });
            }

            var buttons = $$('.pe-login-card-body .pe-login-submit');
            if (buttons.length) {
                animate(buttons, {
                    opacity: [0, 1],
                    translateY: [14, 0],
                    duration: 700,
                    delay: stagger(100, { start: 1100 }),
                    ease: 'outExpo'
                });
            }
        }

        function orbsPulse() {
            $$('.pe-orb').forEach(function (orb, i) {
                animate(orb, {
                    translateX: [0, (i % 2 ? 1 : -1) * 40],
                    translateY: [0, -30],
                    scale: [1, 1.12, 1],
                    duration: 12000 + i * 3000,
                    loop: true,
                    ease: 'inOutSine'
                });
            });
        }

        function cardGlow() {
            var card = $('#peLoginCard');
            if (!card) return;
            if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

            card.addEventListener('pointermove', function (e) {
                var r = card.getBoundingClientRect();
                var x = ((e.clientX - r.left) / r.width) * 100;
                var y = ((e.clientY - r.top) / r.height) * 100;
                card.style.setProperty('--gx', x + '%');
                card.style.setProperty('--gy', y + '%');
            });
        }

        function watchAlerts() {
            $$('.pe-login-alert').forEach(function (el) {
                var obs = new MutationObserver(function () {
                    if (el.style.display !== 'none' && el.textContent.trim() !== '') {
                        animate(el, {
                            translateX: [0, -6, 6, -4, 4, 0],
                            duration: 420,
                            ease: 'inOutQuad'
                        });
                    }
                });
                obs.observe(el, { attributes: true, attributeFilter: ['style', 'class'], childList: true, subtree: true });
            });
        }

        function watchStepper() {
            var steps = $$('.pe-login-step');
            if (!steps.length) return;

            steps.forEach(function (step) {
                var obs = new MutationObserver(function () {
                    if (step.classList.contains('active')) {
                        var circle = step.querySelector('.pe-login-step-circle');
                        if (circle) {
                            animate(circle, {
                                scale: [0.6, 1.15, 1],
                                duration: 500,
                                ease: 'outBack'
                            });
                        }
                    }
                });
                obs.observe(step, { attributes: true, attributeFilter: ['class'] });
            });
        }

        function buttonFeedback() {
            $$('.pe-login-submit').forEach(function (btn) {
                btn.addEventListener('pointerdown', function () {
                    animate(btn, { scale: [1, 0.97], duration: 120, ease: 'outQuad' });
                });
                btn.addEventListener('pointerup', function () {
                    animate(btn, { scale: [0.97, 1], duration: 220, ease: 'outBack' });
                });
                btn.addEventListener('pointerleave', function () {
                    animate(btn, { scale: [0.97, 1], duration: 220, ease: 'outBack' });
                });
            });
        }

        function boot() {
            buildStars();
            orbsPulse();
            introCard();
            cardGlow();
            watchAlerts();
            watchStepper();
            buttonFeedback();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
</script>

@endsection