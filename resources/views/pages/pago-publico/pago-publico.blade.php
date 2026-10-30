@extends('layouts.app_no_nav')

@section('content')

<link
    href="https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">
<link href="{{ asset('assets/css/login.css') }}?v={{ config('app.version') }}" rel="stylesheet">
<link id="pagestyle" href="{{ asset('assets/css/pagos-publicos.css') }}?v={{ config('app.version') }}" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/animejs@4.0.2/lib/anime.iife.min.js"></script>

<div class="pe-login pp-page" id="ppRoot">

    {{-- Fondo animado (igual al login) --}}
    <div class="pe-login-bg" aria-hidden="true">
        <div class="pe-login-grid"></div>
        <div class="pe-orb pe-orb-1"></div>
        <div class="pe-orb pe-orb-2"></div>
        <div class="pe-orb pe-orb-3"></div>
        <div class="pe-login-stars" id="peStars"></div>
    </div>

    <div class="pp-wrap">
        <div class="pe-login-card pp-card" id="ppCard">

            <div class="pe-login-card-header">
                <div class="pe-login-dots" aria-hidden="true">
                    <span></span><span></span><span></span>
                    <span></span><span></span><span></span>
                </div>
                <div class="pe-login-scan" aria-hidden="true"></div>

                <div class="pe-login-logo">
                    <img src="{{ asset('img/logo_contabilidad.png') }}" alt="Portafolio ERP">
                </div>
                <h1 class="pe-login-card-title">Portafolio ERP</h1>
                <p class="pe-login-card-subtitle">Pago de suscripción</p>
            </div>

            <div class="pe-login-card-body">

                {{-- ESTADO: CARGANDO --}}
                <div id="pp-state-loading" class="pe-login-panel active pp-center">
                    <div class="pe-login-spinner pp-spinner-lg"></div>
                    <p class="pp-muted">Cargando información...</p>
                </div>

                {{-- ESTADO: ERROR --}}
                <div id="pp-state-error" class="pe-login-panel" style="display: none;">
                    <div class="pe-login-alert error" id="pp-error-msg">Link inválido o expirado.</div>
                    <a href="/login" class="pe-login-submit pe-login-submit-secondary pp-block-link">Ir al login</a>
                </div>

                {{-- ESTADO: PAGO --}}
                <div id="pp-state-pay" class="pe-login-panel" style="display: none;">

                    <div class="pp-company pp-reveal">
                        <div class="pp-company-info">
                            <span class="pp-eyebrow">Empresa</span>
                            <h2 class="pp-company-name" id="pp-empresa-nombre">—</h2>
                            <p class="pp-company-nit" id="pp-empresa-nit"></p>
                        </div>

                        <div id="pp-estado-badge" class="pp-estado-badge" style="display: none;">
                            <div class="pp-estado-icon" id="pp-estado-icon"></div>
                            <div class="pp-estado-texto">
                                <div class="pp-estado-titulo" id="pp-estado-titulo">—</div>
                                <div class="pp-estado-subtitulo" id="pp-estado-subtitulo">—</div>
                            </div>
                        </div>
                    </div>

                    <div id="pp-alert-pago"></div>

                    <div class="pp-grid">

                        {{-- ─────────── IZQUIERDA: módulos ─────────── --}}
                        <div class="pp-col-main">

                            <div id="pp-bloque-actual" class="pp-reveal" style="display: none;">
                                <p class="pp-section-title pp-title-ok">
                                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                    Tu suscripción activa
                                </p>
                                <div id="pp-componentes-actuales" class="pp-actuales"></div>
                            </div>

                            <p class="pp-section-title pp-reveal" id="pp-seccion-titulo">
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                                Agregar módulos (opcional)
                            </p>
                            <div id="pp-arbol-componentes" class="pp-arbol">
                                <div class="pp-muted pp-center-sm">Cargando módulos...</div>
                            </div>
                        </div>

                        {{-- ─────────── DERECHA: resumen fijo ─────────── --}}
                        <aside class="pp-col-side">
                            <div class="pp-summary pp-reveal">

                                <p class="pp-summary-title">Resumen de pago</p>

                                <div class="pp-block">
                                    <span class="pp-label">¿Por cuántos meses?</span>
                                    <div class="pp-chips" id="pp-chips">
                                        <button type="button" class="pp-chip" data-m="1">1</button>
                                        <button type="button" class="pp-chip" data-m="3">3</button>
                                        <button type="button" class="pp-chip" data-m="6">6</button>
                                        <button type="button" class="pp-chip" data-m="12">12</button>
                                    </div>
                                    <div class="pp-stepper">
                                        <button type="button" id="pp-btn-restar-mes" class="pp-stepper-btn" aria-label="Restar mes">−</button>
                                        <input type="number" id="pp-meses" value="1" min="1" max="24" class="pp-stepper-input">
                                        <button type="button" id="pp-btn-sumar-mes" class="pp-stepper-btn" aria-label="Sumar mes">+</button>
                                        <span class="pp-stepper-unit">mes(es)</span>
                                    </div>
                                </div>

                                <div id="pp-total-mensual" class="pp-line pp-line-extra" style="display: none;">
                                    <span>Módulos nuevos / mes</span>
                                    <strong>$ 0</strong>
                                </div>

                                <div id="pp-desglose-pago" class="pp-desglose" style="display: none;"></div>

                                <div id="pp-ahorro" class="pp-ahorro" style="display: none;"></div>

                                <div id="pp-total-pagar" class="pp-total">
                                    <span>Total a pagar</span>
                                    <strong id="pp-monto-total">$ 0</strong>
                                </div>

                                <button class="pe-login-submit pp-pay" id="pp-btn-pagar">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                                    </svg>
                                    <span id="pp-btn-pagar-texto">Pagar ahora</span>
                                </button>

                                <ul class="pp-trust">
                                    <li><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>Pago seguro con Wompi</li>
                                    <li><svg viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>Activación inmediata</li>
                                    <li><svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Soporte por WhatsApp</li>
                                </ul>
                            </div>
                        </aside>

                    </div>
                </div>

                {{-- ESTADO: ÉXITO --}}
                <div id="pp-state-success" class="pe-login-panel pp-center pp-success" style="display: none;">
                    <div class="pp-confetti" id="ppConfetti" aria-hidden="true"></div>
                    <svg class="pp-success-check" viewBox="0 0 52 52">
                        <circle class="pp-sc-circle" cx="26" cy="26" r="23"/>
                        <path class="pp-sc-tick" d="M15 27l8 8 14-16"/>
                    </svg>
                    <h2 class="pp-success-title">¡Pago exitoso!</h2>
                    <p class="pp-muted">Tu suscripción ha sido actualizada.</p>
                    <p class="pp-muted pp-small">Serás redirigido al login en unos segundos...</p>
                </div>

                <p class="pe-login-version">
                    v{{ config('app.version', '2.0.0') }} · {{ date('Y') }}
                </p>

            </div>
        </div>
    </div>
</div>

<script>
    window.PP_TOKEN = '{{ $token }}';
    window.PP_WOMPI_PUBLIC_KEY = '{{ config("services.wompi.public_key") }}';
</script>
<script src="{{ asset('assets/js/sistema/pago-publico.js') }}?v={{ config('app.version') }}"></script>

@endsection