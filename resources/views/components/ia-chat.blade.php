<style>
    .ai-chat-widget {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 780px;
        height: 620px;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.18);
        z-index: 9999;
        overflow: hidden;
        font-family: system-ui, -apple-system, sans-serif;
        flex-direction: row;
    }

    .ai-chat-widget.abierto {
        display: flex;
    }

    .ai-chat-sidebar {
        width: 240px;
        background: #f7f8fa;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
    }

    .ai-chat-sidebar-header {
        padding: 12px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 13px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }

    .ai-chat-sidebar-header button {
        background: #4f46e5;
        color: white;
        border: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
        line-height: 1;
    }

    .ai-chat-sidebar-lista {
        flex: 1;
        overflow-y: auto;
        padding: 6px;
    }

    .ai-chat-sidebar-item {
        padding: 8px 10px;
        border-radius: 8px;
        font-size: 12px;
        color: #374151;
        cursor: pointer;
        margin-bottom: 4px;
        transition: background 0.15s;
        border: 1px solid transparent;
    }

    .ai-chat-sidebar-item:hover {
        background: #eef2ff;
    }

    .ai-chat-sidebar-item.activa {
        background: #e0e7ff;
        border-color: #c7d2fe;
        font-weight: 600;
    }

    .ai-chat-sidebar-item .titulo {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .ai-chat-sidebar-item .meta {
        display: block;
        font-size: 10px;
        color: #9ca3af;
        margin-top: 2px;
    }

    .ai-chat-sidebar-item .badge-cerrada {
        display: inline-block;
        font-size: 9px;
        background: #dcfce7;
        color: #166534;
        padding: 1px 6px;
        border-radius: 8px;
        margin-left: 4px;
        font-weight: 600;
    }

    .ai-chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .ai-chat-header {
        padding: 12px 16px;
        background: #4f46e5;
        color: white;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
    }

    .ai-chat-header-acciones button {
        background: transparent;
        border: none;
        color: white;
        cursor: pointer;
        font-size: 18px;
        margin-left: 6px;
        line-height: 1;
    }

    .ai-chat-mensajes {
        flex: 1;
        overflow-y: auto;
        padding: 14px;
        background: #f9fafb;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .ai-chat-msg {
        max-width: 82%;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 13.5px;
        line-height: 1.45;
        word-wrap: break-word;
        white-space: pre-wrap;
    }

    .ai-chat-msg.user {
        align-self: flex-end;
        background: #4f46e5;
        color: white;
        border-radius: 12px 12px 0 12px;
    }

    .ai-chat-msg.asistente {
        align-self: flex-start;
        background: white;
        color: #1f2937;
        border: 1px solid #e5e7eb;
        border-radius: 12px 12px 12px 0;
    }

    .ai-chat-msg.sistema {
        align-self: center;
        background: #fef3c7;
        color: #92400e;
        font-size: 12px;
        border-radius: 10px;
        padding: 6px 10px;
        text-align: center;
    }

    .ai-chat-input-wrap {
        padding: 10px 12px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 8px;
        background: white;
    }

    .ai-chat-input-wrap input {
        flex: 1;
        padding: 9px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 13.5px;
        outline: none;
    }

    .ai-chat-input-wrap input:focus {
        border-color: #4f46e5;
    }

    .ai-chat-input-wrap button {
        padding: 9px 18px;
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
    }

    .ai-chat-input-wrap button:disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    .ai-chat-abrir {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #4f46e5;
        color: white;
        border: none;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .ai-chat-abrir.oculto {
        display: none;
    }

    @media (max-width: 820px) {
        .ai-chat-widget {
            width: 100%;
            height: 100%;
            bottom: 0;
            right: 0;
            border-radius: 0;
        }

        .ai-chat-sidebar {
            display: none;
        }
    }
</style>

<div id="ai-chat-widget" class="ai-chat-widget">
    <div class="ai-chat-sidebar">
        <div class="ai-chat-sidebar-header">
            <span>Conversaciones</span>
            <button type="button" id="ai-chat-nueva" title="Nueva conversación">+</button>
        </div>
        <div id="ai-chat-sidebar-lista" class="ai-chat-sidebar-lista"></div>
    </div>

    <div class="ai-chat-main">
        <div class="ai-chat-header">
            <span id="ai-chat-titulo">Asistente IA</span>
            <div class="ai-chat-header-acciones">
                <button type="button" id="ai-chat-toggle-sidebar" title="Ver conversaciones">☰</button>
                <button type="button" id="ai-chat-cerrar" title="Cerrar">×</button>
            </div>
        </div>

        <div id="ai-chat-mensajes" class="ai-chat-mensajes"></div>

        <div class="ai-chat-input-wrap">
            <input type="text" id="ai-chat-input" placeholder="Escribe tu mensaje..." autocomplete="off">
            <button type="button" id="ai-chat-enviar">Enviar</button>
        </div>
    </div>
</div>

<button type="button" id="ai-chat-abrir" class="ai-chat-abrir">💬</button>