@extends('layouts.app_no_nav')

@section('content')

<style>
    /* ============================================
       LOGIN PORTAFOLIO ERP — Tarjeta sólida
       ============================================ */
    .pe-login {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1.5rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: #0a0f1e;
        position: relative;
        overflow: hidden;
        color: #e2e8f0;
    }

    /* Fondo: degradado sutil */
    .pe-login::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 70% 50% at 50% 0%, rgba(79, 70, 229, 0.25) 0%, transparent 60%),
            radial-gradient(ellipse 60% 40% at 80% 100%, rgba(139, 92, 246, 0.12) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    /* Cuadrícula técnica sutil */
    .pe-login::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(148, 163, 184, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(148, 163, 184, 0.03) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black 30%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black 30%, transparent 80%);
        pointer-events: none;
        z-index: 0;
    }

    /* ============================================
       CONTENEDOR PRINCIPAL
       ============================================ */
    .pe-login-wrap {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 440px;
        display: flex;
        flex-direction: column;
        align-items: center;
        animation: pe-login-fade-in 0.7s cubic-bezier(0.22, 1, 0.36, 1);
    }

    @keyframes pe-login-fade-in {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ============================================
       TARJETA
       ============================================ */
    .pe-login-card {
        width: 100%;
        background: #081329;
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 20px;
        box-shadow:
            0 25px 60px -20px rgba(0, 0, 0, 0.7),
            0 0 0 1px rgba(255, 255, 255, 0.03) inset,
            0 1px 0 rgba(255, 255, 255, 0.06) inset;
        overflow: hidden;
    }

    /* ============================================
       HEADER DE LA TARJETA (logo + título) CON FONDO ANIMADO
       ============================================ */
    .pe-login-card-header {
        position: relative;
        padding: 2.5rem 2rem 1.5rem;
        text-align: center;
        overflow: hidden;
        background:
            radial-gradient(ellipse 80% 100% at 50% 0%, rgba(79, 70, 229, 0.18) 0%, transparent 70%),
            linear-gradient(180deg, rgba(79, 70, 229, 0.06) 0%, transparent 100%);
        border-bottom: 1px solid rgba(148, 163, 184, 0.08);
        isolation: isolate;
    }

    /* === Aurora 1: luz que se mueve horizontalmente === */
    .pe-login-card-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -30%;
        width: 60%;
        height: 200%;
        background: radial-gradient(ellipse at center,
            rgba(99, 102, 241, 0.35) 0%,
            rgba(99, 102, 241, 0.12) 35%,
            transparent 70%);
        filter: blur(40px);
        animation: pe-aurora-1 12s ease-in-out infinite;
        pointer-events: none;
        z-index: 0;
    }

    @keyframes pe-aurora-1 {
        0%, 100% {
            transform: translateX(0) translateY(0) scale(1);
            opacity: 0.7;
        }
        50% {
            transform: translateX(120%) translateY(-10%) scale(1.2);
            opacity: 1;
        }
    }

    /* === Aurora 2: luz morada que se mueve al contrario === */
    .pe-login-card-header::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 55%;
        height: 200%;
        background: radial-gradient(ellipse at center,
            rgba(139, 92, 246, 0.30) 0%,
            rgba(139, 92, 246, 0.10) 40%,
            transparent 70%);
        filter: blur(45px);
        animation: pe-aurora-2 15s ease-in-out infinite;
        pointer-events: none;
        z-index: 0;
    }

    @keyframes pe-aurora-2 {
        0%, 100% {
            transform: translateX(0) translateY(0) scale(1);
            opacity: 0.6;
        }
        50% {
            transform: translateX(-130%) translateY(10%) scale(1.15);
            opacity: 0.95;
        }
    }

    /* === Partículas flotantes en el header === */
    .pe-login-card-header .pe-login-dots {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 1;
        overflow: hidden;
    }

    .pe-login-card-header .pe-login-dots span {
        position: absolute;
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: rgba(165, 180, 252, 0.9);
        box-shadow: 0 0 8px rgba(165, 180, 252, 0.8);
        animation: pe-dot-float linear infinite;
        opacity: 0;
    }

    .pe-login-card-header .pe-login-dots span:nth-child(1) {
        left: 12%;
        top: 75%;
        animation-duration: 9s;
        animation-delay: 0s;
    }
    .pe-login-card-header .pe-login-dots span:nth-child(2) {
        left: 28%;
        top: 40%;
        animation-duration: 11s;
        animation-delay: 1.5s;
        width: 2px;
        height: 2px;
    }
    .pe-login-card-header .pe-login-dots span:nth-child(3) {
        left: 50%;
        top: 60%;
        animation-duration: 10s;
        animation-delay: 3s;
    }
    .pe-login-card-header .pe-login-dots span:nth-child(4) {
        left: 72%;
        top: 30%;
        animation-duration: 13s;
        animation-delay: 0.8s;
        width: 2px;
        height: 2px;
    }
    .pe-login-card-header .pe-login-dots span:nth-child(5) {
        left: 88%;
        top: 70%;
        animation-duration: 12s;
        animation-delay: 2.2s;
    }
    .pe-login-card-header .pe-login-dots span:nth-child(6) {
        left: 40%;
        top: 85%;
        animation-duration: 14s;
        animation-delay: 4s;
        width: 2px;
        height: 2px;
    }

    @keyframes pe-dot-float {
        0% {
            transform: translateY(20px) scale(0.5);
            opacity: 0;
        }
        15% {
            opacity: 0.9;
        }
        85% {
            opacity: 0.9;
        }
        100% {
            transform: translateY(-70px) scale(1);
            opacity: 0;
        }
    }

    /* === Línea de luz que barre el header === */
    .pe-login-card-header .pe-login-scan {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg,
            transparent 0%,
            transparent 30%,
            rgba(129, 140, 248, 0.9) 50%,
            transparent 70%,
            transparent 100%);
        animation: pe-scan 5s ease-in-out infinite;
        z-index: 2;
        pointer-events: none;
        box-shadow:
            0 0 12px rgba(129, 140, 248, 0.7),
            0 0 24px rgba(129, 140, 248, 0.4);
    }

    @keyframes pe-scan {
        0%, 100% {
            transform: translateX(-100%);
            opacity: 0;
        }
        50% {
            transform: translateX(100%);
            opacity: 1;
        }
    }

    /* === Contenido del header por encima de los efectos === */
    .pe-login-card-header > .pe-login-logo,
    .pe-login-card-header > .pe-login-card-title,
    .pe-login-card-header > .pe-login-card-subtitle {
        position: relative;
        z-index: 3;
    }


    .pe-login-logo {
        width: 56px;
        height: 56px;
        margin: 0 auto 0.75rem;
        border-radius: 14px;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.25), rgba(139, 92, 246, 0.15));
        border: 1px solid rgba(129, 140, 248, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        box-shadow:
            0 8px 20px -8px rgba(79, 70, 229, 0.6),
            0 0 0 1px rgba(255, 255, 255, 0.04) inset;
    }

    .pe-login-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .pe-login-card-title {
        font-size: 1.375rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        color: #ffffff;
        margin: 0 0 0.25rem 0;
        text-transform: uppercase;
        line-height: 1.2;
    }

    .pe-login-card-subtitle {
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.15em;
        color: #64748b;
        margin: 0;
        text-transform: uppercase;
    }

    /* ============================================
       CUERPO DE LA TARJETA
       ============================================ */
    .pe-login-card-body {
        padding: 1.75rem 2rem 2rem;
    }

    /* Subtítulo del form */
    .pe-login-form-title {
        text-align: center;
        font-size: 0.9375rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        color: #cbd5e1;
        margin: 0 0 1.5rem 0;
        text-transform: uppercase;
    }

    /* Alerta de error */
    .pe-login-alert {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.75rem 0.875rem;
        margin-bottom: 1.25rem;
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.25);
        border-radius: 10px;
        color: #fca5a5;
        font-size: 0.8125rem;
        font-weight: 500;
        animation: pe-login-shake 0.4s ease-in-out;
    }

    .pe-login-alert svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    @keyframes pe-login-shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }

    /* ============================================
       CAMPOS
       ============================================ */
    .pe-login-field {
        display: flex;
        flex-direction: column;
        gap: 0.4375rem;
        margin-bottom: 1.125rem;
    }

    .pe-login-field label {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #cbd5e1;
        margin: 0;
    }

    .pe-login-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .pe-login-input-wrap::before {
        content: '';
        position: absolute;
        left: 0.875rem;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        opacity: 0.55;
        pointer-events: none;
        z-index: 2;
        transition: opacity 0.2s ease;
    }

    .pe-login-input-wrap.pe-icon-mail::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpath d='M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z'/%3E%3Cpolyline points='22,6 12,13 2,6'/%3E%3C/svg%3E");
    }

    .pe-login-input-wrap.pe-icon-lock::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Crect x='3' y='11' width='18' height='11' rx='2' ry='2'/%3E%3Cpath d='M7 11V7a5 5 0 0 1 10 0v4'/%3E%3C/svg%3E");
    }

    .pe-login-input-wrap input {
        width: 100%;
        padding: 0.8125rem 1rem 0.8125rem 2.625rem;
        font-size: 0.9rem;
        font-family: inherit;
        font-weight: 400;
        color: #f1f5f9;
        background: rgba(2, 6, 23, 0.5);
        border: 1px solid rgba(148, 163, 184, 0.15);
        border-radius: 10px;
        outline: none;
        transition: all 0.2s ease;
        margin: 0;
    }

    .pe-login-input-wrap input::placeholder {
        color: #64748b;
    }

    .pe-login-input-wrap input:hover {
        border-color: rgba(148, 163, 184, 0.25);
        background: rgba(2, 6, 23, 0.6);
    }

    .pe-login-input-wrap input:focus {
        background: rgba(2, 6, 23, 0.8);
        border-color: rgba(129, 140, 248, 0.6);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
    }

    .pe-login-input-wrap:focus-within::before {
        opacity: 0.9;
    }

    /* Autofill */
    .pe-login-input-wrap input:-webkit-autofill,
    .pe-login-input-wrap input:-webkit-autofill:hover,
    .pe-login-input-wrap input:-webkit-autofill:focus {
        -webkit-text-fill-color: #f1f5f9;
        -webkit-box-shadow: 0 0 0 1000px rgba(2, 6, 23, 0.8) inset;
        transition: background-color 5000s ease-in-out 0s;
        caret-color: #f1f5f9;
    }

    /* Toggle password */
    .pe-login-toggle-pass {
        position: absolute;
        right: 0.625rem;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.2s ease;
        z-index: 2;
        padding: 0;
        margin: 0;
    }

    .pe-login-toggle-pass:hover {
        background: rgba(148, 163, 184, 0.1);
        color: #cbd5e1;
    }

    .pe-login-toggle-pass svg {
        width: 16px;
        height: 16px;
    }

    .pe-login-field-error {
        font-size: 0.75rem;
        color: #fca5a5;
        margin: 0;
    }

    /* ============================================
       BOTÓN
       ============================================ */
    .pe-login-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.8125rem 1.5rem;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        font-family: inherit;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #ffffff;
        background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 10px;
        cursor: pointer;
        box-shadow:
            0 1px 0 rgba(255, 255, 255, 0.15) inset,
            0 8px 20px -6px rgba(79, 70, 229, 0.5);
        transition: all 0.2s ease;
    }

    .pe-login-submit:hover:not(:disabled) {
        background: linear-gradient(180deg, #4f46e5 0%, #4338ca 100%);
        transform: translateY(-1px);
        box-shadow:
            0 1px 0 rgba(255, 255, 255, 0.15) inset,
            0 12px 28px -6px rgba(79, 70, 229, 0.6);
    }

    .pe-login-submit:active:not(:disabled) {
        transform: translateY(0);
    }

    .pe-login-submit:disabled {
        opacity: 0.85;
        cursor: wait;
    }

    .pe-login-submit svg {
        width: 15px;
        height: 15px;
        transition: transform 0.2s ease;
    }

    .pe-login-submit:hover:not(:disabled) svg {
        transform: translateX(2px);
    }

    .pe-login-spinner {
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: pe-login-spin 0.7s linear infinite;
        display: inline-block;
    }

    @keyframes pe-login-spin {
        to { transform: rotate(360deg); }
    }

    /* ============================================
       VERSIÓN (abajo, sutil)
       ============================================ */
    .pe-login-version {
        text-align: center;
        font-size: 0.6875rem;
        color: #475569;
        letter-spacing: 0.1em;
        margin: 1rem 0 0 0;
        font-weight: 500;
        font-variant-numeric: tabular-nums;
    }

    /* ============================================
       POWERED BY (afuera de la tarjeta)
       ============================================ */
    .pe-login-powered {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.75rem;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.25em;
        color: #475569;
        text-transform: uppercase;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .pe-login-powered:hover {
        color: #94a3b8;
    }

    .pe-login-powered img {
        width: 22px;
        height: 22px;
        object-fit: contain;
        opacity: 0.6;
        transition: opacity 0.2s ease;
    }

    .pe-login-powered:hover img {
        opacity: 0.9;
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 480px) {
        .pe-login {
            padding: 1.5rem 1rem;
        }

        .pe-login-card-header {
            padding: 2rem 1.5rem 1.25rem;
        }

        .pe-login-card-header::before,
        .pe-login-card-header::after {
            filter: blur(30px);
        }

        .pe-login-card-body {
            padding: 1.5rem 1.5rem 1.75rem;
        }

        .pe-login-logo {
            width: 48px;
            height: 48px;
            padding: 8px;
        }

        .pe-login-card-title {
            font-size: 1.125rem;
        }

        .pe-login-powered {
            font-size: 0.625rem;
            letter-spacing: 0.2em;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .pe-login-wrap,
        .pe-login-alert,
        .pe-login-card-header::before,
        .pe-login-card-header::after,
        .pe-login-card-header .pe-login-dots span,
        .pe-login-card-header .pe-login-scan {
            animation: none !important;
        }

        .pe-login-card-header .pe-login-scan {
            display: none;
        }

        .pe-login-card-header .pe-login-dots {
            display: none;
        }
    }
</style>

<!-- ============================================
     LOGIN PORTAFOLIO ERP
     ============================================ -->
<div class="pe-login">

    <div class="pe-login-wrap">

        <div class="pe-login-card">

            <!-- Header de la tarjeta -->
                        <!-- Header de la tarjeta -->
            <div class="pe-login-card-header">
                <!-- Partículas flotantes -->
                <div class="pe-login-dots" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <!-- Línea de luz que barre -->
                <div class="pe-login-scan" aria-hidden="true"></div>

                <div class="pe-login-logo">
                    <img src="/img/logo_contabilidad.png" alt="Portafolio ERP">
                </div>
                <h1 class="pe-login-card-title">Portafolio ERP</h1>
                <p class="pe-login-card-subtitle">Suite Empresarial</p>
            </div>

            <!-- Cuerpo -->
            <div class="pe-login-card-body">

                <h2 class="pe-login-form-title">Iniciar sesión</h2>

                <!-- Alerta de error -->
                <div id="error-login" class="pe-login-alert" style="display: none;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>Usuario o contraseña incorrectos</span>
                </div>

                <!-- Campo Email -->
                <div class="pe-login-field">
                    <label for="email_login">Correo electrónico</label>
                    <div class="pe-login-input-wrap pe-icon-mail">
                        <input
                            type="email"
                            id="email_login"
                            name="email"
                            value="{{ old('email') ?? '' }}"
                            placeholder="tucorreo@empresa.com"
                            autocomplete="email">
                    </div>
                    @error('email')
                        <p class="pe-login-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Campo Contraseña -->
                <div class="pe-login-field">
                    <label for="password_login">Contraseña</label>
                    <div class="pe-login-input-wrap pe-icon-lock">
                        <input
                            type="password"
                            id="password_login"
                            name="password"
                            value=""
                            placeholder="••••••••"
                            autocomplete="current-password"
                            onkeypress="changePassWord(event)">
                        <button type="button" class="pe-login-toggle-pass" onclick="peTogglePass()" aria-label="Mostrar contraseña">
                            <svg id="pe-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="pe-login-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botón Ingresar -->
                <button type="button" id="button-login" class="pe-login-submit">
                    Ingresar
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </button>

                <!-- Botón Cargando -->
                <button type="button" id="button-login-loading" class="pe-login-submit" style="display: none;" disabled>
                    <span class="pe-login-spinner"></span>
                    Verificando...
                </button>

                <!-- Versión -->
                <p class="pe-login-version">
                    v{{ config('app.version', '2.0.0') }} · {{ date('Y') }}
                </p>

            </div>

        </div>

    </div>

</div>

<script>
    /* Toggle mostrar/ocultar contraseña */
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
</script>

@endsection