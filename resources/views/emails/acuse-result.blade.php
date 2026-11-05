@php
    $estadoProcesando = ($ok === null);
    $ok        = (bool) ($ok ?? false);
    $titulo    = $estadoProcesando
        ? 'Registrando tu acuse…'
        : ($ok ? 'Acuse registrado' : 'No se pudo registrar el acuse');
    $subtitulo = $estadoProcesando
        ? 'Estamos enviando tu acuse a la DIAN. Esto puede tardar unos segundos.'
        : ($ok
            ? 'Tu acuse de recibo fue enviado correctamente a la DIAN.'
            : 'Ocurrió un problema al procesar tu acuse.');

    $raw          = is_array($detalle ?? null) ? ($detalle['raw'] ?? null) : null;
    $rawArray     = $raw ? (array) $raw : null;
    $errorTecnico = $rawArray['message'] ?? null;
    $detalleJson  = !$ok && !$estadoProcesando && !empty($detalle)
        ? json_encode($detalle, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : null;

    // Resumen de la factura (opcional, llega desde el controlador en el GET)
    $factura = $factura ?? null;

    $waTexto = 'Hola, tuve un problema con el acuse de recibo'
        . ($factura ? ' de la factura ' . $factura['documento'] . ' de ' . $factura['empresa'] : ' de una factura electrónica') . '.';
    $wa = 'https://wa.me/573332475846?text=' . rawurlencode($waTexto);

    $cardClass = $estadoProcesando ? 'ac-load' : ($ok ? 'ac-ok' : 'ac-bad');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0a0f1e">
    <link rel="icon" type="image/png" href="{{ asset('img/logo_contabilidad.png') }}">
    <title>{{ $titulo }} · Portafolio ERP</title>

    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/login.css') }}?v={{ config('app.version') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/animejs@4.0.2/lib/anime.iife.min.js"></script>

    <style>
        body { margin: 0; background: #0a0f1e; }

        .ac-wrap { position: relative; z-index: 1; width: 100%; max-width: 500px; }
        .ac-card { max-width: none; justify-self: auto; }

        /* Colores por estado */
        .ac-ok   { --ac: #10b981; --ac-soft: rgba(16, 185, 129, .14); --ac-line: rgba(16, 185, 129, .35); --ac-text: #34d399; }
        .ac-bad  { --ac: #ef4444; --ac-soft: rgba(239, 68, 68, .14);  --ac-line: rgba(239, 68, 68, .35);  --ac-text: #f87171; }
        .ac-load { --ac: #6366f1; --ac-soft: rgba(99, 102, 241, .14); --ac-line: rgba(99, 102, 241, .35); --ac-text: #a5b4fc; }

        /* Estado */
        .ac-status { text-align: center; padding: .4rem 0 .6rem; }
        .ac-icon { position: relative; width: 84px; height: 84px; margin: 0 auto 1.1rem; }
        .ac-icon::before {
            content: ""; position: absolute; inset: -16px; border-radius: 50%;
            background: radial-gradient(circle, var(--ac) 0%, transparent 65%);
            opacity: .3; filter: blur(10px); animation: acGlow 3.2s ease-in-out infinite;
        }
        .ac-icon svg { position: relative; width: 100%; height: 100%; }
        .ac-ring {
            fill: var(--ac-soft); stroke: var(--ac); stroke-width: 2.5;
            stroke-dasharray: 145; stroke-dashoffset: 145; animation: acDraw .8s ease forwards;
        }
        .ac-mark {
            fill: none; stroke: var(--ac); stroke-width: 4; stroke-linecap: round; stroke-linejoin: round;
            stroke-dasharray: 50; stroke-dashoffset: 50; animation: acDraw .5s .6s ease forwards;
        }
        .ac-bad .ac-icon { animation: acShake .5s .95s ease; }

        /* Spinner (estado "procesando") */
        .ac-track { fill: var(--ac-soft); stroke: var(--ac); stroke-width: 2.5; opacity: .25; }
        .ac-ring-spin {
            fill: none; stroke: var(--ac); stroke-width: 3; stroke-linecap: round;
            stroke-dasharray: 36 109; stroke-dashoffset: 0;
            transform-box: fill-box; transform-origin: center;
            animation: acSpin 1s linear infinite;
        }
        @keyframes acSpin { to { transform: rotate(360deg); } }

        @keyframes acDraw  { to { stroke-dashoffset: 0; } }
        @keyframes acGlow  { 0%, 100% { opacity: .22; transform: scale(.95); } 50% { opacity: .42; transform: scale(1.08); } }
        @keyframes acShake { 20% { transform: translateX(-6px); } 40% { transform: translateX(6px); } 60% { transform: translateX(-4px); } 80% { transform: translateX(4px); } }

        .ac-title {
            margin: 0; font-family: 'Sora', sans-serif; font-weight: 800;
            font-size: 1.45rem; letter-spacing: -.03em; color: #fff; line-height: 1.2;
        }
        .ac-sub { margin: .45rem 0 0; font-size: .88rem; color: #94a3b8; line-height: 1.55; }

        /* Chips */
        .ac-chips { display: flex; justify-content: center; flex-wrap: wrap; gap: .5rem; margin: 1rem 0 .2rem; }
        .ac-chip {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .3rem .75rem; border-radius: 999px;
            font-size: .7rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase;
            color: #cbd5e1; background: rgba(2, 6, 23, .5); border: 1px solid rgba(148, 163, 184, .15);
        }
        .ac-chip i { width: 7px; height: 7px; border-radius: 50%; background: var(--ac); box-shadow: 0 0 8px var(--ac); }
        .ac-chip.is-state { color: var(--ac-text); border-color: var(--ac-line); background: var(--ac-soft); }

        /* Mensaje */
        .ac-msg {
            margin: 1.1rem 0 0; padding: .95rem 1.1rem; text-align: center;
            font-size: .92rem; line-height: 1.65; color: #e2e8f0;
            background: rgba(2, 6, 23, .45); border: 1px solid rgba(148, 163, 184, .12); border-radius: 12px;
        }

        /* Cajas informativas */
        .ac-info, .ac-error {
            margin-top: .9rem; padding: .85rem 1rem; border-radius: 12px;
            font-size: .8rem; line-height: 1.65; border: 1px solid rgba(148, 163, 184, .12);
            border-left: 4px solid var(--ac);
        }
        .ac-info  { background: rgba(2, 6, 23, .45); color: #94a3b8; }
        .ac-info strong { color: #e2e8f0; }
        .ac-error { background: rgba(239, 68, 68, .08); border-color: rgba(239, 68, 68, .25); border-left-color: #ef4444; color: #fca5a5; word-break: break-word; }
        .ac-error strong { color: #fecaca; }

        /* Acciones */
        .ac-actions { display: flex; flex-direction: column; gap: .6rem; margin-top: 1.2rem; }
        .ac-actions .pe-login-submit { margin-top: 0; text-decoration: none; }

        /* Detalle técnico */
        .ac-details { margin-top: 1.1rem; }
        .ac-details summary {
            cursor: pointer; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            color: #64748b; user-select: none; outline: none; list-style: none;
        }
        .ac-details summary::-webkit-details-marker { display: none; }
        .ac-details summary::before { content: "▸ "; }
        .ac-details[open] summary::before { content: "▾ "; }
        .ac-details summary:hover { color: #a5b4fc; }
        .ac-pre {
            margin: .6rem 0 0; padding: .9rem; max-height: 220px; overflow: auto;
            background: #020617; color: #cbd5e1; border: 1px solid rgba(148, 163, 184, .12); border-radius: 10px;
            font-family: 'JetBrains Mono', 'Courier New', monospace; font-size: .68rem; line-height: 1.6;
            white-space: pre-wrap; word-break: break-word;
        }
        .ac-copy {
            margin-top: .5rem; padding: .35rem .8rem; border-radius: 8px; cursor: pointer;
            font-family: inherit; font-size: .72rem; font-weight: 700; color: #a5b4fc;
            background: rgba(99, 102, 241, .12); border: 1px solid rgba(129, 140, 248, .3);
            transition: background .2s ease;
        }
        .ac-copy:hover { background: rgba(99, 102, 241, .22); }

        .ac-foot { text-align: center; margin: 1.1rem 0 0; font-size: .7rem; line-height: 1.7; color: #475569; }
        .ac-foot a { color: #818cf8; font-weight: 700; text-decoration: none; }
        .ac-foot a:hover { color: #a5b4fc; }

        /* Puntitos animados */
        .ac-dots::after { content: ''; animation: acDots 1.4s steps(4, end) infinite; }
        @keyframes acDots {
            0%   { content: ''; }
            25%  { content: '.'; }
            50%  { content: '..'; }
            75%  { content: '...'; }
            100% { content: ''; }
        }

        /* Resumen de la factura */
        .ac-doc {
            margin-top: 1rem; padding: .2rem 1rem; border-radius: 12px;
            background: rgba(2, 6, 23, .45); border: 1px solid rgba(148, 163, 184, .12);
        }
        .ac-doc-row {
            display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
            padding: .55rem 0; font-size: .82rem; border-bottom: 1px solid rgba(148, 163, 184, .08);
        }
        .ac-doc-row:last-child { border-bottom: 0; }
        .ac-doc-row span {
            font-size: .66rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
            color: #64748b; white-space: nowrap;
        }
        .ac-doc-row strong { color: #e2e8f0; text-align: right; overflow-wrap: anywhere; }

        /* Botón WhatsApp */
        .ac-wa {
            display: inline-flex; align-items: center; justify-content: center; gap: .55rem;
            padding: .75rem 1rem; border-radius: 12px; text-decoration: none;
            font-family: inherit; font-size: .85rem; font-weight: 700; color: #4ade80;
            background: rgba(37, 211, 102, .12); border: 1px solid rgba(37, 211, 102, .35);
            transition: background .2s ease, transform .2s ease;
        }
        .ac-wa:hover { background: rgba(37, 211, 102, .22); transform: translateY(-1px); }
        .ac-wa svg { width: 18px; height: 18px; fill: currentColor; }

        @media (max-width: 480px) { .ac-title { font-size: 1.25rem; } }
        @media (prefers-reduced-motion: reduce) {
            .ac-ring, .ac-mark { animation: none; stroke-dashoffset: 0; }
            .ac-icon, .ac-icon::before { animation: none; }
            .ac-ring-spin { animation: none; }
            .ac-dots::after { animation: none; content: '...'; }
        }
    </style>
</head>
<body>

<div class="pe-login" id="peLoginRoot">

    <div class="pe-login-bg" aria-hidden="true">
        <div class="pe-login-grid"></div>
        <div class="pe-orb pe-orb-1"></div>
        <div class="pe-orb pe-orb-2"></div>
        <div class="pe-orb pe-orb-3"></div>
        <div class="pe-login-stars" id="peStars"></div>
    </div>

    <div class="ac-wrap">
        <div class="pe-login-card ac-card {{ $cardClass }}" id="acCard">

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
                <p class="pe-login-card-subtitle">Acuse de recibo · DIAN</p>
            </div>

            <div class="pe-login-card-body" id="acBody" role="{{ $ok && !$estadoProcesando ? 'status' : ($estadoProcesando ? 'status' : 'alert') }}" aria-live="polite">

                {{-- Estado --}}
                <div class="ac-status">
                    <div class="ac-icon" aria-hidden="true">
                        <svg viewBox="0 0 52 52" id="acSvg">
                            @if ($estadoProcesando)
                                <circle class="ac-track" cx="26" cy="26" r="23" />
                                <circle class="ac-ring-spin" cx="26" cy="26" r="23" />
                            @else
                                <circle class="ac-ring" cx="26" cy="26" r="23" />
                                @if ($ok)
                                    <path class="ac-mark" d="M15 27l8 8 14-16" />
                                @else
                                    <path class="ac-mark" d="M18 18l16 16M34 18L18 34" />
                                @endif
                            @endif
                        </svg>
                    </div>

                    <h2 class="ac-title" id="acTitle">
                        @if ($estadoProcesando)
                            Registrando tu acuse<span class="ac-dots"></span>
                        @else
                            {{ $titulo }}
                        @endif
                    </h2>
                    <p class="ac-sub" id="acSub">{{ $subtitulo }}</p>

                    <div class="ac-chips">
                        <span class="ac-chip is-state" id="acChip"><i></i>{{ $estadoProcesando ? 'Procesando' : ($ok ? 'Registrado' : 'Con error') }}</span>
                        <span class="ac-chip">{{ now()->format('d/m/Y · H:i') }}</span>
                    </div>

                    {{-- Resumen de la factura (solo si llega desde el controlador) --}}
                    @if (!empty($factura))
                        <div class="ac-doc">
                            <div class="ac-doc-row"><span>Empresa</span><strong>{{ $factura['empresa'] }}</strong></div>
                            <div class="ac-doc-row"><span>NIT</span><strong>{{ $factura['nit'] }}</strong></div>
                            <div class="ac-doc-row"><span>Factura</span><strong>{{ $factura['documento'] }}</strong></div>
                            @if (!empty($factura['fecha']))
                                <div class="ac-doc-row"><span>Fecha</span><strong>{{ $factura['fecha'] }}</strong></div>
                            @endif
                            <div class="ac-doc-row"><span>Total</span><strong>{{ $factura['total'] }}</strong></div>
                        </div>
                    @endif
                </div>

                {{-- Resultado (el JS lo rellena cuando termina el POST) --}}
                <div id="acResult">
                    @unless ($estadoProcesando)
                        @if (!empty($mensaje))
                            <p class="ac-msg">{{ $mensaje }}</p>
                        @endif

                        @unless ($ok)
                            <div class="ac-error">
                                @if (!empty($errorTecnico))
                                    <strong>Detalle:</strong> {{ $errorTecnico }}<br>
                                @endif
                                <strong>¿Qué hacer?</strong> Verifica NIT del adquiriente y CUFE e intenta de nuevo.
                            </div>

                            <div class="ac-actions">
                                <button type="button" class="pe-login-submit" data-retry>
                                    Reintentar
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 4 23 10 17 10" />
                                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                                    </svg>
                                </button>
                                <a href="{{ $wa }}" target="_blank" rel="noopener" class="ac-wa">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    Soporte por WhatsApp
                                </a>
                            </div>

                            @if ($detalleJson)
                                <details class="ac-details">
                                    <summary>Ver detalle técnico</summary>
                                    <pre class="ac-pre" id="acDetalle">{{ $detalleJson }}</pre>
                                    <button type="button" class="ac-copy" id="acCopy">Copiar detalle</button>
                                </details>
                            @endif
                        @endunless
                    @endunless
                </div>

                <p class="ac-foot">
                    &copy; {{ date('Y') }} Portafolio ERP · Medellín<br>
                    <a href="https://portafolioerp.com">portafolioerp.com</a>
                    &nbsp;·&nbsp;
                    <a href="{{ $wa }}" target="_blank" rel="noopener">Soporte</a>
                </p>

            </div>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    var $  = function (s, c) { return (c || document).querySelector(s); };
    var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
    var esc = function (t) {
        var d = document.createElement('div'); d.textContent = (t == null ? '' : String(t)); return d.innerHTML;
    };

    var A = window.anime;
    var animate = A && A.animate ? A.animate : null;
    var stagger = A && A.stagger ? A.stagger : null;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var card   = $('#acCard');
    var body   = $('#acBody');
    var result = $('#acResult');

    var ICON = {
        load: '<circle class="ac-track" cx="26" cy="26" r="23"/><circle class="ac-ring-spin" cx="26" cy="26" r="23"/>',
        ok:   '<circle class="ac-ring" cx="26" cy="26" r="23"/><path class="ac-mark" d="M15 27l8 8 14-16"/>',
        bad:  '<circle class="ac-ring" cx="26" cy="26" r="23"/><path class="ac-mark" d="M18 18l16 16M34 18L18 34"/>'
    };

    var RETRY_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>';

    var WA_URL  = @json($wa);
    var WA_ICON = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
    var WA_BTN  = '<a href="' + WA_URL + '" target="_blank" rel="noopener" class="ac-wa">' + WA_ICON + 'Soporte por WhatsApp</a>';

    // Pinta un estado (load | ok | bad) sin recargar la página
    function setState(state, o) {
        o = o || {};
        card.classList.remove('ac-load', 'ac-ok', 'ac-bad');
        card.classList.add('ac-' + state);
        body.setAttribute('role', state === 'bad' ? 'alert' : 'status');

        // Reinsertar el icono reinicia sus animaciones
        var oldIcon = $('.ac-icon');
        var newIcon = oldIcon.cloneNode(false);
        newIcon.innerHTML = '<svg viewBox="0 0 52 52" id="acSvg">' + ICON[state] + '</svg>';
        oldIcon.parentNode.replaceChild(newIcon, oldIcon);

        $('#acTitle').innerHTML = state === 'load'
            ? 'Registrando tu acuse<span class="ac-dots"></span>'
            : esc(o.titulo);
        $('#acSub').textContent  = o.subtitulo || '';
        $('#acChip').innerHTML   = '<i></i>' + esc(o.chip);
        result.innerHTML         = o.html || '';

        if (animate && !reduceMotion && result.children.length) {
            animate(result.children, {
                opacity: [0, 1], translateY: [10, 0], duration: 500,
                delay: stagger ? stagger(70) : 0, ease: 'outExpo'
            });
        }
    }

    function resultadoHTML(res) {
        var html = '';
        if (res.mensaje) html += '<p class="ac-msg">' + esc(res.mensaje) + '</p>';
        if (res.ok) return html;

        var raw = res.detalle && res.detalle.raw ? res.detalle.raw : null;
        var err = raw && raw.message ? raw.message : null;

        html += '<div class="ac-error">' +
                (err ? '<strong>Detalle:</strong> ' + esc(err) + '<br>' : '') +
                '<strong>¿Qué hacer?</strong> Verifica NIT del adquiriente y CUFE e intenta de nuevo.</div>';

        html += '<div class="ac-actions"><button type="button" class="pe-login-submit" data-retry>Reintentar ' + RETRY_ICON + '</button>' + WA_BTN + '</div>';

        if (res.detalle) {
            html += '<details class="ac-details"><summary>Ver detalle técnico</summary>' +
                    '<pre class="ac-pre" id="acDetalle">' + esc(JSON.stringify(res.detalle, null, 2)) + '</pre>' +
                    '<button type="button" class="ac-copy" id="acCopy">Copiar detalle</button></details>';
        }
        return html;
    }

    // ============================================================
    // POST único (sin recargar la página)
    // ============================================================
    var token    = @json($token ?? null);
    var endpoint = @json(route('venta-acuse-recibo.procesar'));
    var running  = false;

    function procesar() {
        if (running || !token) return;
        running = true;

        setState('load', {
            subtitulo: 'Estamos enviando tu acuse a la DIAN. Esto puede tardar unos segundos.',
            chip: 'Procesando'
        });

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
            },
            body: JSON.stringify({ token: token }),
            credentials: 'same-origin'
        })
        .then(function (r) {
            return r.json().catch(function () { return { ok: false, mensaje: 'Respuesta inválida del servidor.' }; });
        })
        .then(function (res) {
            var ok = !!res.ok;
            setState(ok ? 'ok' : 'bad', {
                titulo:    ok ? 'Acuse registrado' : 'No se pudo registrar el acuse',
                subtitulo: ok ? 'Tu acuse de recibo fue enviado correctamente a la DIAN.'
                              : 'Ocurrió un problema al procesar tu acuse.',
                chip:      ok ? 'Registrado' : 'Con error',
                html:      resultadoHTML(res)
            });
        })
        .catch(function (err) {
            console.error('[acuse] error', err);
            setState('bad', {
                titulo:    'Error de conexión',
                subtitulo: 'No pudimos contactar al servidor.',
                chip:      'Error',
                html:      '<div class="ac-actions"><button type="button" class="pe-login-submit" data-retry>Reintentar ' + RETRY_ICON + '</button>' + WA_BTN + '</div>'
            });
        })
        .then(function () { running = false; });
    }

    // Reintentar y copiar (delegación, funcionan también con el HTML pintado por JS)
    document.addEventListener('click', function (e) {
        var retry = e.target.closest('[data-retry]');
        if (retry) {
            if (token) { procesar(); } else { window.location.reload(); }
            return;
        }

        var copy = e.target.closest('#acCopy');
        var pre  = $('#acDetalle');
        if (copy && pre) {
            var txt  = pre.textContent;
            var done = function () {
                copy.textContent = '¡Copiado!';
                setTimeout(function () { copy.textContent = 'Copiar detalle'; }, 1800);
            };
            if (navigator.clipboard) { navigator.clipboard.writeText(txt).then(done); }
            else {
                var ta = document.createElement('textarea'); ta.value = txt; document.body.appendChild(ta);
                ta.select(); document.execCommand('copy'); document.body.removeChild(ta); done();
            }
        }
    });

    // ============================================================
    // Animaciones de entrada (igual que el login)
    // ============================================================
    if (animate && !reduceMotion) {
        var box = $('#peStars');
        if (box) {
            for (var i = 0; i < 40; i++) {
                var s = document.createElement('span');
                s.style.left = (Math.random() * 100) + '%';
                s.style.top = (Math.random() * 100) + '%';
                s.style.animationDelay = (Math.random() * 6) + 's';
                s.style.animationDuration = (6 + Math.random() * 8) + 's';
                box.appendChild(s);
            }
        }

        $$('.pe-orb').forEach(function (orb, i) {
            animate(orb, {
                translateX: [0, (i % 2 ? 1 : -1) * 40], translateY: [0, -30], scale: [1, 1.12, 1],
                duration: 12000 + i * 3000, loop: true, ease: 'inOutSine'
            });
        });

        animate('#acCard', { opacity: [0, 1], translateY: [36, 0], scale: [0.97, 1], duration: 850, ease: 'outExpo' });
        animate('.pe-login-logo', { scale: [0.4, 1], rotate: [-180, 0], opacity: [0, 1], duration: 950, delay: 200, ease: 'outBack' });
        animate('.pe-login-card-title', { opacity: [0, 1], translateY: [12, 0], duration: 650, delay: 420, ease: 'outExpo' });
        animate('.pe-login-card-subtitle', { opacity: [0, 1], translateY: [8, 0], duration: 600, delay: 540, ease: 'outExpo' });

        animate('.ac-title, .ac-sub, .ac-chips, .ac-doc, #acResult > *', {
            opacity: [0, 1], translateY: [14, 0], duration: 650,
            delay: stagger ? stagger(90, { start: 700 }) : 700, ease: 'outExpo'
        });
    }

    // Arranca el proceso una sola vez si la vista llegó en modo "procesando"
    @if ($estadoProcesando && !empty($token))
        procesar();
    @endif
})();
</script>

</body>
</html>