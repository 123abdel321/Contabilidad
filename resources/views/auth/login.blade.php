@extends('layouts.app_no_nav')

@section('content')

<style>
    /* ============================================
       LOGIN PORTAFOLIO ERP — Rediseño profesional
       ============================================ */
    .pe-login {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 1.5rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: #0a0f1e;
        position: relative;
        overflow: hidden;
        color: #e2e8f0;
    }

    /* Fondo: degradado sutil + cuadrícula técnica */
    .pe-login::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 50% at 50% -20%, rgba(79, 70, 229, 0.35) 0%, transparent 60%),
            radial-gradient(ellipse 60% 40% at 80% 100%, rgba(139, 92, 246, 0.15) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    .pe-login::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(148, 163, 184, 0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(148, 163, 184, 0.04) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: radial-gradient(ellipse 60% 60% at 50% 50%, black 30%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse 60% 60% at 50% 50%, black 30%, transparent 80%);
        pointer-events: none;
        z-index: 0;
    }

    /* ============================================
       TARJETA DE LOGIN
       ============================================ */
    .pe-login-card {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 420px;
        padding: 2.5rem 2.25rem;
        border-radius: 20px;
        background: rgba(15, 23, 42, 0.75);
        border: 1px solid rgba(148, 163, 184, 0.12);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow:
            0 25px 60px -20px rgba(0, 0, 0, 0.6),
            0 0 0 1px rgba(255, 255, 255, 0.03) inset,
            0 1px 0 rgba(255, 255, 255, 0.06) inset;
        animation: pe-login-fade-in 0.7s cubic-bezier(0.22, 1, 0.36, 1);
    }

    @keyframes pe-login-fade-in {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ============================================
       LOGO Y MARCA
       ============================================ */
    .pe-login-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.625rem;
        margin-bottom: 2rem;
        text-decoration: none;
        color: inherit;
    }

    .pe-login-brand-mark {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.2), rgba(139, 92, 246, 0.1));
        border: 1px solid rgba(129, 140, 248, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        box-shadow: 0 4px 12px -4px rgba(79, 70, 229, 0.5);
    }

    .pe-login-brand-mark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .pe-login-brand-name {
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #f1f5f9;
        text-transform: uppercase;
    }

    /* ============================================
       HEADER
       ============================================ */
    .pe-login-header {
        text-align: center;
        margin-bottom: 1.75rem;
    }

    .pe-login-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #f1f5f9;
        margin: 0 0 0.375rem 0;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .pe-login-header p {
        font-size: 0.875rem;
        color: #94a3b8;
        margin: 0;
        font-weight: 400;
    }

    /* ============================================
       ALERTA
       ============================================ */
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

    .pe-login-input-wrap input:focus ~ ::before,
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
       OPCIONES (Recordarme / Olvidé contraseña)
       ============================================ */
    .pe-login-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.8125rem;
    }

    .pe-login-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #94a3b8;
        cursor: pointer;
        user-select: none;
        margin: 0;
        font-weight: 400;
    }

    .pe-login-checkbox input[type="checkbox"] {
        width: 14px;
        height: 14px;
        accent-color: #6366f1;
        cursor: pointer;
        margin: 0;
    }

    .pe-login-forgot {
        color: #a5b4fc;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .pe-login-forgot:hover {
        color: #c7d2fe;
    }

    /* ============================================
       BOTÓN PRINCIPAL
       ============================================ */
    .pe-login-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.8125rem 1.5rem;
        font-size: 0.875rem;
        font-family: inherit;
        font-weight: 600;
        letter-spacing: 0.01em;
        color: #ffffff;
        background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 10px;
        cursor: pointer;
        box-shadow:
            0 1px 0 rgba(255, 255, 255, 0.15) inset,
            0 8px 20px -6px rgba(79, 70, 229, 0.5);
        transition: all 0.2s ease;
        margin: 0;
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
       FOOTER
       ============================================ */
    .pe-login-footer {
        margin-top: 1.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid rgba(148, 163, 184, 0.1);
        text-align: center;
        font-size: 0.75rem;
        color: #64748b;
    }

    .pe-login-footer p {
        margin: 0;
        line-height: 1.5;
    }

    /* ============================================
       MARCA DE FONDO (esquina inferior derecha)
       ============================================ */
    .pe-login-watermark {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        font-size: 0.6875rem;
        color: rgba(148, 163, 184, 0.35);
        letter-spacing: 0.15em;
        text-transform: uppercase;
        font-weight: 500;
        z-index: 1;
        pointer-events: none;
    }

    /* ============================================
       RESPONSIVE
       ============================================ */
    @media (max-width: 480px) {
        .pe-login {
            padding: 1.5rem 1rem;
        }

        .pe-login-card {
            padding: 2rem 1.5rem;
            border-radius: 16px;
        }

        .pe-login-header h1 {
            font-size: 1.375rem;
        }

        .pe-login-options {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.625rem;
        }

        .pe-login-watermark {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .pe-login-card,
        .pe-login-alert {
            animation: none !important;
        }
    }
</style>

<!-- ============================================
     LOGIN PORTAFOLIO ERP
     ============================================ -->
<div class="pe-login">

    <div class="pe-login-card">

        <!-- Marca -->
        <a href="/" class="pe-login-brand">
            <div class="pe-login-brand-mark">
                <img src="/img/logo_contabilidad.png" alt="Portafolio ERP">
            </div>
            <span class="pe-login-brand-name">Portafolio ERP</span>
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

    <!-- Marca de fondo -->
    <div class="pe-login-watermark">ERP Suite · Colombia</div>

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