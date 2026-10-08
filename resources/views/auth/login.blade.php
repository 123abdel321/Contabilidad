@extends('layouts.app_no_nav')

@section('content')

<style>
    /* ============================================
       LOGIN PORTAFOLIO ERP - Split screen
       ============================================ */
    .pe-login-wrap {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 100vh;
        font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: #ffffff;
    }

    /* ============================================
       PANEL IZQUIERDO: FORMULARIO
       ============================================ */
    .pe-login-form-side {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem 2rem;
        background: #ffffff;
    }

    .pe-login-form-inner {
        width: 100%;
        max-width: 400px;
        display: flex;
        flex-direction: column;
    }

    /* Logo (móvil) */
    .pe-login-logo-mobile {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        text-decoration: none;
        margin-bottom: 2rem;
    }

    .pe-login-logo-mobile img {
        height: 40px;
        width: auto;
    }

    .pe-login-logo-mobile span {
        font-size: 1.125rem;
        font-weight: 800;
        color: #4f46e5;
    }

    /* Header */
    .pe-login-header {
        margin-bottom: 2rem;
    }

    .pe-login-header h1 {
        font-size: 1.875rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.5rem 0;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .pe-login-header p {
        font-size: 0.95rem;
        color: #64748b;
        margin: 0;
    }

    /* Alerta */
    .pe-login-alert {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.875rem 1rem;
        margin-bottom: 1.25rem;
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
        animation: pe-shake 0.4s ease-in-out;
    }

    .pe-login-alert svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    @keyframes pe-shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }

    /* Campos */
    .pe-login-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.125rem;
    }

    .pe-login-field label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
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
        width: 18px;
        height: 18px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        opacity: 0.5;
        pointer-events: none;
        z-index: 2;
        transition: opacity 0.2s ease;
    }

    .pe-login-input-wrap.pe-icon-mail::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpath d='M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z'/%3E%3Cpolyline points='22,6 12,13 2,6'/%3E%3C/svg%3E");
    }

    .pe-login-input-wrap.pe-icon-lock::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Crect x='3' y='11' width='18' height='11' rx='2' ry='2'/%3E%3Cpath d='M7 11V7a5 5 0 0 1 10 0v4'/%3E%3C/svg%3E");
    }

    .pe-login-input-wrap input {
        width: 100%;
        padding: 0.875rem 1rem 0.875rem 2.75rem;
        font-size: 0.9rem;
        font-family: inherit;
        color: #0f172a;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        outline: none;
        transition: all 0.2s ease;
        margin: 0;
        font-weight: 500;
    }

    .pe-login-input-wrap input::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    .pe-login-input-wrap input:hover {
        border-color: #cbd5e1;
    }

    .pe-login-input-wrap input:focus {
        background: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
    }

    /* Toggle password */
    .pe-login-toggle-pass {
        position: absolute;
        right: 0.75rem;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        border-radius: 8px;
        transition: all 0.2s ease;
        z-index: 2;
        padding: 0;
        margin: 0;
        box-shadow: none;
    }

    .pe-login-toggle-pass:hover {
        background: #f1f5f9;
        color: #4f46e5;
    }

    .pe-login-toggle-pass svg {
        width: 17px;
        height: 17px;
    }

    .pe-login-field-error {
        font-size: 0.78rem;
        color: #dc2626;
        margin: 0;
    }

    /* Opciones */
    .pe-login-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.85rem;
    }

    .pe-login-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #475569;
        cursor: pointer;
        user-select: none;
        margin: 0;
        font-weight: 500;
    }

    .pe-login-checkbox input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #4f46e5;
        cursor: pointer;
        margin: 0;
    }

    .pe-login-forgot {
        color: #4f46e5;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .pe-login-forgot:hover {
        color: #4338ca;
        text-decoration: underline;
    }

    /* Botón */
    .pe-login-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.9375rem 1.5rem;
        font-size: 0.9rem;
        font-family: inherit;
        font-weight: 700;
        letter-spacing: 0.02em;
        color: #ffffff !important;
        background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        box-shadow: 0 10px 24px -8px rgba(79, 70, 229, 0.5);
        transition: all 0.25s ease;
        margin: 0;
        text-transform: uppercase;
    }

    .pe-login-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 14px 32px -8px rgba(79, 70, 229, 0.6);
        background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
    }

    .pe-login-submit:active:not(:disabled) {
        transform: translateY(0);
    }

    .pe-login-submit:disabled {
        opacity: 0.9;
        cursor: wait;
    }

    .pe-login-submit svg {
        width: 16px;
        height: 16px;
        transition: transform 0.25s ease;
    }

    .pe-login-submit:hover:not(:disabled) svg {
        transform: translateX(3px);
    }

    .pe-login-spinner {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: pe-spin 0.7s linear infinite;
        display: inline-block;
    }

    @keyframes pe-spin {
        to { transform: rotate(360deg); }
    }

    .pe-login-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
        text-align: center;
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .pe-login-footer p {
        margin: 0;
    }

    /* ============================================
       PANEL DERECHO: BRANDING CON ÓRBITAS
       ============================================ */
    .pe-login-brand-side {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        overflow: hidden;
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
    }

    /* Glow decorativo */
    .pe-login-brand-side::before {
        content: '';
        position: absolute;
        top: -20%;
        right: -20%;
        width: 600px;
        height: 600px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.25) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .pe-login-brand-side::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -15%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .pe-login-brand-inner {
        position: relative;
        z-index: 1;
        text-align: center;
        color: #ffffff;
        max-width: 420px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* ============================================
       SISTEMA DE ÓRBITAS ALREDEDOR DEL LOGO
       ============================================ */
    .pe-login-orbit {
        position: relative;
        width: 280px;
        height: 280px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 2rem;
    }

    /* Anillo de órbita */
    .pe-login-orbit-ring {
        position: absolute;
        inset: 0;
        border-radius: 50%;
        border: 1.5px dashed rgba(255, 255, 255, 0.25);
        animation: pe-orbit-spin 30s linear infinite;
        pointer-events: none;
    }

    .pe-login-orbit-ring::before,
    .pe-login-orbit-ring::after {
        content: '';
        position: absolute;
        width: 6px;
        height: 6px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.9);
    }

    .pe-login-orbit-ring::before {
        top: -3px;
        left: 50%;
        transform: translateX(-50%);
    }

    .pe-login-orbit-ring::after {
        bottom: -3px;
        left: 50%;
        transform: translateX(-50%);
    }

    /* Anillo interior */
    .pe-login-orbit-ring-inner {
        inset: 20%;
        border-style: solid;
        border-color: rgba(255, 255, 255, 0.12);
        animation-duration: 20s;
        animation-direction: reverse;
    }

    @keyframes pe-orbit-spin {
        to { transform: rotate(360deg); }
    }

    /* Logo central */
    .pe-login-orbit-center {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 24px;
        background: rgba(255, 255, 255, 0.98);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        box-shadow:
            0 20px 50px rgba(0, 0, 0, 0.3),
            0 0 0 8px rgba(255, 255, 255, 0.08);
        z-index: 2;
        animation: pe-orbit-pulse 4s ease-in-out infinite;
    }

    .pe-login-orbit-center img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    @keyframes pe-orbit-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.03); }
    }

    /* Satélites orbitales */
    .pe-login-orbit-sat {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 52px;
        height: 52px;
        margin: -26px 0 0 -26px;
        animation: pe-orbit-sat-rotate 24s linear infinite;
        z-index: 3;
        pointer-events: none;
    }

    .pe-login-orbit-sat-inner {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow:
            0 8px 20px rgba(0, 0, 0, 0.25),
            0 0 0 3px rgba(255, 255, 255, 0.15);
        animation: pe-orbit-sat-counter 24s linear infinite;
        padding: 10px;
        color: #4f46e5;
    }

    .pe-login-orbit-sat-inner svg {
        width: 100%;
        height: 100%;
    }

    /* Colores por satélite */
    .pe-login-orbit-sat-2 .pe-login-orbit-sat-inner {
        color: #10b981;
    }

    .pe-login-orbit-sat-3 .pe-login-orbit-sat-inner {
        color: #f59e0b;
    }

    .pe-login-orbit-sat-4 .pe-login-orbit-sat-inner {
        color: #ef4444;
    }

    @keyframes pe-orbit-sat-rotate {
        from { transform: rotate(0deg) translateX(140px) rotate(0deg); }
        to   { transform: rotate(360deg) translateX(140px) rotate(0deg); }
    }

    @keyframes pe-orbit-sat-counter {
        from { transform: rotate(0deg); }
        to   { transform: rotate(-360deg); }
    }

    /* Desfases (4 satélites = 90° cada uno = 6s de 24s) */
    .pe-login-orbit-sat-2 {
        animation-delay: -6s;
    }
    .pe-login-orbit-sat-2 .pe-login-orbit-sat-inner {
        animation-delay: -6s;
    }

    .pe-login-orbit-sat-3 {
        animation-delay: -12s;
    }
    .pe-login-orbit-sat-3 .pe-login-orbit-sat-inner {
        animation-delay: -12s;
    }

    .pe-login-orbit-sat-4 {
        animation-delay: -18s;
    }
    .pe-login-orbit-sat-4 .pe-login-orbit-sat-inner {
        animation-delay: -18s;
    }

    /* Marca de texto */
    .pe-login-brand-inner h2 {
        font-size: 1.75rem;
        font-weight: 900;
        letter-spacing: 0.05em;
        margin: 0 0 0.25rem 0;
        color: #ffffff;
    }

    .pe-login-brand-version {
        font-size: 0.875rem;
        color: rgba(255, 255, 255, 0.7);
        margin: 0 0 1.5rem 0;
        font-weight: 500;
    }

    .pe-login-brand-tagline {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
        margin: 0;
        font-weight: 500;
        letter-spacing: 0.03em;
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 1024px) {
        .pe-login-wrap {
            grid-template-columns: 1fr;
        }

        .pe-login-brand-side {
            display: none;
        }

        .pe-login-logo-mobile {
            display: flex;
        }

        .pe-login-form-side {
            padding: 2.5rem 1.5rem;
        }

        .pe-login-header {
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .pe-login-header h1 {
            font-size: 1.5rem;
        }

        .pe-login-options {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.75rem;
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        .pe-login-orbit-ring,
        .pe-login-orbit-sat,
        .pe-login-orbit-sat-inner,
        .pe-login-orbit-center {
            animation: none !important;
        }
    }
</style>

<!-- ============================================
     LOGIN PORTAFOLIO ERP
     ============================================ -->
<div class="pe-login-wrap">

    <!-- ============================================
         PANEL IZQUIERDO: FORMULARIO
         ============================================ -->
    <div class="pe-login-form-side">
        <div class="pe-login-form-inner">

            <!-- Logo móvil -->
            <a href="/" class="pe-login-logo-mobile">
                <img src="/img/logo_contabilidad.png" alt="Portafolio ERP">
                <span>PORTAFOLIO ERP</span>
            </a>

            <!-- Header -->
            <div class="pe-login-header">
                <h1>Iniciar sesión</h1>
                <p>Ingresa tus credenciales para continuar</p>
            </div>

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

            <!-- Opciones -->
            <!-- <div class="pe-login-options">
                <label class="pe-login-checkbox">
                    <input type="checkbox" name="remember" id="rememberMe">
                    <span>Recordarme</span>
                </label>
                <a href="#" class="pe-login-forgot">¿Olvidaste tu contraseña?</a>
            </div> -->

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

            <!-- Footer -->
            <div class="pe-login-footer">
                <p>&copy; {{ date('Y') }} Portafolio ERP · Todos los derechos reservados</p>
            </div>

        </div>
    </div>

    <!-- ============================================
         PANEL DERECHO: BRANDING CON ÓRBITAS
         ============================================ -->
    <div class="pe-login-brand-side">
        <div class="pe-login-brand-inner">

            <!-- Sistema de órbitas alrededor del logo -->
            <div class="pe-login-orbit">

                <!-- Anillos -->
                <div class="pe-login-orbit-ring"></div>
                <div class="pe-login-orbit-ring pe-login-orbit-ring-inner"></div>

                <!-- Logo central -->
                <div class="pe-login-orbit-center">
                    <img src="/img/logo_contabilidad.png" alt="Portafolio ERP">
                </div>

                <!-- Satélite 1: Facturación -->
                <div class="pe-login-orbit-sat">
                    <div class="pe-login-orbit-sat-inner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                    </div>
                </div>

                <!-- Satélite 2: Contabilidad -->
                <div class="pe-login-orbit-sat pe-login-orbit-sat-2">
                    <div class="pe-login-orbit-sat-inner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"/>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                </div>

                <!-- Satélite 3: Nómina -->
                <div class="pe-login-orbit-sat pe-login-orbit-sat-3">
                    <div class="pe-login-orbit-sat-inner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                </div>

                <!-- Satélite 4: POS -->
                <div class="pe-login-orbit-sat pe-login-orbit-sat-4">
                    <div class="pe-login-orbit-sat-inner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                    </div>
                </div>

            </div>

            <h2>PORTAFOLIO ERP</h2>
            <p class="pe-login-brand-version">{{ config('app.version') }}</p>

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