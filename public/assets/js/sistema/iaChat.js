var aiChatSessionId = null;
var aiChatEnviando = false;
var aiChatWidget = null;
var aiChatMensajes = null;
var aiChatLista = null;

// ---------------------------------------------------------------------------
// ABRIR / CERRAR
// ---------------------------------------------------------------------------
function abrirAiChat() {
    if (!aiChatWidget) {
        aiChatWidget = document.getElementById('ai-chat-widget');
        aiChatMensajes = document.getElementById('ai-chat-mensajes');
        aiChatLista = document.getElementById('ai-chat-sidebar-lista');
    }
    aiChatWidget.classList.add('abierto');
    document.getElementById('ai-chat-abrir').classList.add('oculto');
    document.getElementById('ai-chat-input').focus();

    aiChatSessionId = localStorage.getItem('ai_chat_session_id');

    if (aiChatSessionId) {
        cargarConversacionAiChat(aiChatSessionId);
    } else if (!aiChatMensajes.children.length) {
        agregarMensajeAiChat('Hola 👋 ¿Qué venta u operación quieres hacer?', 'sistema');
    }

    cargarConversacionesAiChat();
}

function cerrarAiChat() {
    document.getElementById('ai-chat-widget').classList.remove('abierto');
    document.getElementById('ai-chat-abrir').classList.remove('oculto');
}

function toggleSidebarAiChat() {
    var sidebar = document.querySelector('.ai-chat-sidebar');
    sidebar.style.display = (sidebar.style.display === 'none') ? 'flex' : 'none';
}

// ---------------------------------------------------------------------------
// ENVIAR MENSAJE
// ---------------------------------------------------------------------------
function enviarMensajeAiChat() {
    if (aiChatEnviando) return;

    var input = document.getElementById('ai-chat-input');
    var texto = input.value.trim();
    if (!texto) return;

    agregarMensajeAiChat(texto, 'user');
    input.value = '';
    bloquearInputAiChat(true);

    var data = {
        mensaje: texto,
        session_id: aiChatSessionId,
    };

    $.ajax({
        url: base_url + 'ia/chat',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.session_id) {
            aiChatSessionId = res.session_id;
            localStorage.setItem('ai_chat_session_id', aiChatSessionId);
        }

        agregarMensajeAiChat(res.respuesta || 'Sin respuesta.', 'asistente');

        if (res.cerrada) {
            localStorage.removeItem('ai_chat_session_id');
            aiChatSessionId = null;
            agregarMensajeAiChat('Conversación finalizada. Usa "+" para iniciar una nueva.', 'sistema');
            cargarConversacionesAiChat();
        }

        if (res.pdf_url) {
            mostrarPdfAiChat(res.pdf_url);
        }

    }).fail((err) => {
        var mensaje = 'Error de conexión.';
        if (err.responseJSON && err.responseJSON.message) {
            mensaje = err.responseJSON.message;
            if (typeof mensaje === 'object') {
                mensaje = Object.values(mensaje).flat().join(' ');
            }
        }
        agregarToast('error', 'Error IA', mensaje);
        agregarMensajeAiChat('⚠️ ' + mensaje, 'sistema');
    }).always(() => {
        bloquearInputAiChat(false);
        document.getElementById('ai-chat-input').focus();
    });
}

// ---------------------------------------------------------------------------
// NUEVA CONVERSACIÓN
// ---------------------------------------------------------------------------
function nuevaConversacionAiChat() {
    if (aiChatSessionId) {
        $.ajax({
            url: base_url + 'ia/reset',
            method: 'POST',
            data: JSON.stringify({ session_id: aiChatSessionId }),
            headers: headers,
            dataType: 'json',
        });
    }

    localStorage.removeItem('ai_chat_session_id');
    aiChatSessionId = null;
    aiChatMensajes.innerHTML = '';
    setTituloAiChat('Asistente IA');
    agregarMensajeAiChat('Nueva conversación iniciada. ¿En qué te ayudo?', 'sistema');
    document.getElementById('ai-chat-input').focus();
    cargarConversacionesAiChat();
}

// ---------------------------------------------------------------------------
// CARGAR LISTA DE CONVERSACIONES
// ---------------------------------------------------------------------------
function cargarConversacionesAiChat() {
    $.ajax({
        url: base_url + 'ia/conversaciones',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (!aiChatLista) {
            aiChatLista = document.getElementById('ai-chat-sidebar-lista');
        }
        aiChatLista.innerHTML = '';

        if (!res.success || !res.data.length) {
            aiChatLista.innerHTML = '<div style="padding: 14px; font-size: 12px; color: #9ca3af;">Sin conversaciones aún.</div>';
            return;
        }

        for (var i = 0; i < res.data.length; i++) {
            var conv = res.data[i];
            var item = document.createElement('div');
            item.className = 'ai-chat-sidebar-item';
            if (conv.session_id === aiChatSessionId) item.classList.add('activa');

            var cliente = conv.borrador && conv.borrador.cliente
                ? conv.borrador.cliente.nombre
                : (conv.flujo ? conv.flujo : 'Conversación');

            var fecha = conv.updated_at
                ? new Date(conv.updated_at).toLocaleString('es-CO', {
                    day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit'
                })
                : '';

            var badge = conv.cerrada ? '<span class="badge-cerrada">Cerrada</span>' : '';

            item.innerHTML = `
                <span class="titulo">${escapeHtmlAiChat(cliente)} ${badge}</span>
                <span class="meta">${fecha}</span>
            `;

            item.setAttribute('data-session', conv.session_id);
            aiChatLista.appendChild(item);
        }
    }).fail((err) => {
        var mensaje = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error al cargar conversaciones.';
        agregarToast('error', 'Error IA', mensaje);
    });
}

// ---------------------------------------------------------------------------
// CARGAR UNA CONVERSACIÓN
// ---------------------------------------------------------------------------
function cargarConversacionAiChat(sessionId) {
    $.ajax({
        url: base_url + 'ia/conversaciones/' + sessionId + '/mensajes',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (!res.success) return;

        aiChatSessionId = sessionId;
        localStorage.setItem('ai_chat_session_id', aiChatSessionId);

        if (!aiChatMensajes) aiChatMensajes = document.getElementById('ai-chat-mensajes');
        aiChatMensajes.innerHTML = '';

        var historial = res.data.historial || [];

        for (var i = 0; i < historial.length; i++) {
            var item = historial[i];
            if (item.role === 'user' && item.content) {
                agregarMensajeAiChat(item.content, 'user');
            } else if (item.role === 'assistant' && item.content) {
                agregarMensajeAiChat(item.content, 'asistente');
            }
        }

        var cliente = res.data.borrador && res.data.borrador.cliente
            ? res.data.borrador.cliente.nombre
            : 'Conversación';

        setTituloAiChat(cliente);

        var items = document.querySelectorAll('.ai-chat-sidebar-item');
        for (var j = 0; j < items.length; j++) {
            items[j].classList.remove('activa');
            if (items[j].getAttribute('data-session') === sessionId) {
                items[j].classList.add('activa');
            }
        }

        if (res.data.resultado && res.data.resultado.id) {
            mostrarPdfAiChat(base_web + 'ventas-print/' + res.data.resultado.id);
        } else {
            cerrarPdfAiChat();
        }

    }).fail((err) => {
        var mensaje = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Error al cargar conversación.';
        agregarToast('error', 'Error IA', mensaje);
    });
}

// ---------------------------------------------------------------------------
// HELPERS
// ---------------------------------------------------------------------------
function agregarMensajeAiChat(texto, tipo) {
    if (!texto) return;
    if (!aiChatMensajes) aiChatMensajes = document.getElementById('ai-chat-mensajes');

    var div = document.createElement('div');
    div.className = 'ai-chat-msg ' + tipo;
    div.textContent = texto;
    aiChatMensajes.appendChild(div);
    aiChatMensajes.scrollTop = aiChatMensajes.scrollHeight;
}

function bloquearInputAiChat(bloquear) {
    aiChatEnviando = bloquear;
    var btn = document.getElementById('ai-chat-enviar');
    btn.disabled = bloquear;
    btn.textContent = bloquear ? '...' : 'Enviar';
}

function setTituloAiChat(titulo) {
    document.getElementById('ai-chat-titulo').textContent = titulo || 'Asistente IA';
}

function escapeHtmlAiChat(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ---------------------------------------------------------------------------
// EVENTOS
// ---------------------------------------------------------------------------
$(document).on('click', '#ai-chat-abrir', function () {
    abrirAiChat();
});

$(document).on('click', '#ai-chat-cerrar', function () {
    cerrarAiChat();
});

$(document).on('click', '#ai-chat-toggle-sidebar', function () {
    toggleSidebarAiChat();
});

$(document).on('click', '#ai-chat-enviar', function () {
    enviarMensajeAiChat();
});

$(document).on('keypress', '#ai-chat-input', function (e) {
    if (e.key === 'Enter') {
        enviarMensajeAiChat();
    }
});

$(document).on('click', '#ai-chat-nueva', function () {
    nuevaConversacionAiChat();
});

$(document).on('click', '.ai-chat-sidebar-item', function () {
    var sessionId = $(this).attr('data-session');
    if (sessionId) {
        cargarConversacionAiChat(sessionId);
    }
});

function mostrarPdfAiChat(url) {
    var viewer  = document.getElementById('ai-chat-pdf-viewer');
    var iframe  = document.getElementById('ai-chat-pdf-iframe');
    var abrir   = document.getElementById('ai-chat-pdf-abrir');
    var loading = document.getElementById('ai-chat-pdf-loading');

    // Reset
    iframe.src = 'about:blank';
    loading.classList.remove('oculto');
    viewer.style.display = 'flex';
    abrir.href = url;

    // Precargar el PDF
    fetch(url, {
        method: 'GET',
        headers: headers,   // tus credenciales
        credentials: 'include',
    })
    .then(function (response) {
        if (!response.ok) throw new Error('No se pudo generar la factura.');
        return response.blob();
    })
    .then(function (blob) {
        var blobUrl = URL.createObjectURL(blob);

        iframe.src = blobUrl;

        iframe.onload = function () {
            setTimeout(function () {
                loading.classList.add('oculto');
            }, 200);
        };

        // Libera memoria cuando se cierre el visor
        iframe.setAttribute('data-blob-url', blobUrl);
    })
    .catch(function (error) {
        loading.innerHTML = '<div style="padding:20px;text-align:center;color:#b91c1c;font-size:13px;">⚠️ ' + error.message + '</div>';
    });
}

function cerrarPdfAiChat() {
    var viewer  = document.getElementById('ai-chat-pdf-viewer');
    var iframe  = document.getElementById('ai-chat-pdf-iframe');
    var loading = document.getElementById('ai-chat-pdf-loading');

    // Limpia el blob si existe
    var blobUrl = iframe.getAttribute('data-blob-url');
    if (blobUrl) {
        URL.revokeObjectURL(blobUrl);
        iframe.removeAttribute('data-blob-url');
    }

    iframe.src = 'about:blank';
    loading.classList.remove('oculto');
    loading.innerHTML = `
        <div class="ai-chat-pdf-spinner"></div>
        <div class="ai-chat-pdf-loading-texto">Generando factura...</div>
        <div class="ai-chat-pdf-loading-subtexto">Esto puede tardar unos segundos</div>
    `;
    viewer.style.display = 'none';
}

$(document).on('click', '#ai-chat-pdf-cerrar', function () {
    cerrarPdfAiChat();
});