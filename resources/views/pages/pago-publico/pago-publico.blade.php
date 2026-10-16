@extends('layouts.app_no_nav')

@section('content')

<link id="pagestyle" href="assets/css/pagos-publicos.css" rel="stylesheet" />

<div class="pe-login">
    <div class="pe-login-wrap" style="max-width: 900px;">
        <div class="pe-login-card">

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
                <div id="pp-state-loading" class="pe-login-panel active" style="text-align: center; padding: 2rem 0;">
                    <div class="pe-login-spinner" style="margin: 0 auto 1rem;"></div>
                    <p style="color: #94a3b8; font-size: 0.875rem;">Cargando información...</p>
                </div>

                {{-- ESTADO: ERROR --}}
                <div id="pp-state-error" class="pe-login-panel" style="display: none;">
                    <div class="pe-login-alert error" id="pp-error-msg">
                        Link inválido o expirado.
                    </div>
                    <a href="/login" class="pe-login-submit pe-login-submit-secondary" style="text-align: center; display: block;">
                        Ir al login
                    </a>
                </div>

                {{-- ESTADO: PAGO --}}
                <div id="pp-state-pay" class="pe-login-panel" style="display: none;">

                    <h2 class="pe-login-form-title" id="pp-empresa-nombre" style="margin-bottom: 0.25rem;">—</h2>
                    <p style="color: #94a3b8; font-size: 0.8125rem; margin-bottom: 1.25rem; text-align: center;" id="pp-empresa-nit"></p>

                    <div id="pp-alert-pago"></div>

                    {{-- ═══════════════════════════════════════════════ --}}
                    {{-- GRID DE 2 COLUMNAS --}}
                    {{-- ═══════════════════════════════════════════════ --}}
                    <div class="pp-grid">

                        {{-- ─────────── COLUMNA IZQUIERDA ─────────── --}}
                        <div class="pp-col-left">

                            {{-- Suscripción actual --}}
                            <div id="pp-bloque-actual" style="display: none; margin-bottom: 1.5rem;">
                                <div class="pp-actual-header">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Tu suscripción activa</span>
                                </div>
                                <div id="pp-componentes-actuales"></div>
                            </div>

                            {{-- Duración --}}
                            <div style="margin-bottom: 1.25rem;">
                                <div class="pp-actual-header" style="background: linear-gradient(135deg, rgba(129, 140, 248, 0.15), rgba(99, 102, 241, 0.08)); border-color: rgba(129, 140, 248, 0.35); color: #a5b4fc;">
                                    <i class="fas fa-calendar-alt" style="color: #818cf8;"></i>
                                    <span>Duración del pago</span>
                                </div>
                                <div class="pp-stepper" style="justify-content: center; padding: 0.85rem 0.9rem; background: rgba(2, 6, 23, 0.35); border: 1px solid rgba(148, 163, 184, 0.15); border-radius: 10px;">
                                    <button type="button" id="pp-btn-restar-mes" class="pp-stepper-btn">−</button>
                                    <input type="number" id="pp-meses" value="1" min="1" max="24" class="pp-stepper-input">
                                    <button type="button" id="pp-btn-sumar-mes" class="pp-stepper-btn">+</button>
                                    <span style="color: #cbd5e1; font-size: 0.8125rem; margin-left: 0.5rem; font-weight: 600;">mes(es)</span>
                                </div>
                            </div>

                            {{-- Total mensual --}}
                            <div id="pp-total-mensual" class="pp-resumen-row">
                                <span>Total mensual:</span>
                                <strong style="color: #f1f5f9;">$ 0</strong>
                            </div>

                            {{-- Total a pagar --}}
                            <div id="pp-total-pagar" class="pp-resumen-total">
                                <span>TOTAL A PAGAR</span>
                                <strong id="pp-monto-total">$ 0</strong>
                            </div>

                            {{-- Desglose --}}
                            <div id="pp-desglose-pago" class="pp-desglose" style="display: none;"></div>

                            {{-- Botón pagar --}}
                            <button class="pe-login-submit pp-btn-pagar-full" id="pp-btn-pagar">
                                <i class="fas fa-credit-card" style="margin-right: 0.5rem;"></i>
                                Pagar ahora
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem;">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                            </button>
                        </div>

                        {{-- ─────────── COLUMNA DERECHA ─────────── --}}
                        <div class="pp-col-right">
                            <p class="pp-section-title">➕ Agregar módulos (opcional)</p>
                            <div id="pp-arbol-componentes" class="pp-arbol">
                                <div class="text-center text-muted small">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </div>
                            </div>
                        </div>

                    </div>
                    {{-- FIN GRID --}}

                </div>

                {{-- ESTADO: ÉXITO --}}
                <div id="pp-state-success" class="pe-login-panel" style="display: none; text-align: center; padding: 2rem 0;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                    <h2 class="pe-login-form-title" style="margin-bottom: 0.5rem;">¡Pago exitoso!</h2>
                    <p style="color: #94a3b8; font-size: 0.875rem;">Tu suscripción ha sido actualizada.</p>
                    <p style="color: #64748b; font-size: 0.75rem; margin-top: 1rem;">Serás redirigido al login en unos segundos...</p>
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
</script>
<script src="{{ asset('assets/js/sistema/pago-publico.js') }}"></script>

@endsection