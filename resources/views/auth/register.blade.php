@extends('layouts.app_no_nav')

@section('content')

<div class="pe-login">

    <div class="pe-login-wrap">

        <div class="pe-login-card">

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

                <h2 class="pe-login-form-title">Crear cuenta</h2>

                {{-- Stepper interno --}}
                <div class="pe-login-stepper">
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
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </div>

                {{-- PASO 2: Verificación --}}
                <div class="pe-login-panel" id="pe-panel-2">
                    <div id="pe-alert-2"></div>

                    <div class="pe-login-field">
                        <label for="pe-codigo">Código de verificación</label>
                        <div class="pe-login-input-wrap">
                            <input type="text" id="pe-codigo" placeholder="000000" maxlength="6">
                        </div>
                    </div>

                    <button class="pe-login-submit" id="pe-btn-verificar">
                        Verificar código
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>

                    <button class="pe-login-submit pe-login-submit-secondary" id="pe-btn-reenviar">
                        Reenviar código
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

                    <div id="pe-rut-datos" style="display: none;">

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

                        <button class="pe-login-submit" id="pe-btn-crear-empresa">
                            Crear empresa
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </button>

                    </div>
                </div>

                {{-- PASO 4: Componentes y Pago --}}
                <div class="pe-login-panel" id="pe-panel-4">
                    <div id="pe-alert-4"></div>

                    <div id="pe-arbol-componentes">
                        <div class="text-center text-muted small"><i class="fas fa-spinner fa-spin"></i> Cargando componentes...</div>
                    </div>

                    <hr style="border-color: rgba(148,163,184,0.15); margin: 1rem 0;">

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span style="color: #cbd5e1; font-size: 0.875rem;">Duración:</span>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <button type="button" id="pe-btn-restar-mes" class="pe-login-submit-secondary" style="width: 32px; height: 32px; padding: 0; margin: 0;">−</button>
                            <input type="number" id="pe-meses" value="1" min="1" max="24" style="width: 60px; text-align: center; background: rgba(2,6,23,0.5); border: 1px solid rgba(148,163,184,0.15); border-radius: 8px; color: #f1f5f9; padding: 0.4rem;">
                            <button type="button" id="pe-btn-sumar-mes" class="pe-login-submit-secondary" style="width: 32px; height: 32px; padding: 0; margin: 0;">+</button>
                        </div>
                    </div>

                    <div id="pe-total-mensual" style="display: flex; justify-content: space-between; padding: 0.5rem 0; color: #cbd5e1; font-size: 0.875rem;">
                        <span>Total mensual:</span>
                        <strong style="color: #f1f5f9;">$ 0</strong>
                    </div>

                    <div id="pe-total-pagar" style="display: flex; justify-content: space-between; padding: 0.75rem; background: rgba(79,70,229,0.1); border-radius: 10px; margin-top: 0.5rem;">
                        <span style="font-weight: 700; color: #a5b4fc;">TOTAL A PAGAR:</span>
                        <strong style="color: #fff; font-size: 1.125rem;" id="pe-monto-total">$ 0</strong>
                    </div>

                    <button class="pe-login-submit" id="pe-btn-pagar" style="margin-top: 1rem;">
                        Pagar y activar
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
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

@endsection