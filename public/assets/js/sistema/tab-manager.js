// ============================================================
// TAB MANAGER — Sistema de pestañas SPA con cache de HTML
// ============================================================

// Configuración
var TAB_CONFIG = {
    LIBERAR_HTML_AL_CERRAR: false,   // Si true, al cerrar se borra el HTML cacheado
    PRECARGAR_VISTAS: ['dashboard'], // Vistas a precargar en idle (opcional)
    PRECARGA_DELAY_MS: 3000,         // Espera antes de empezar a precargar
};

// Estado interno
window.__viewCacheHTML   = {};   // { id: htmlString }
window.__viewInited      = {};   // { id: true } — módulo ya inicializado alguna vez
window.__tabCacheVersion = version_app;

/**
 * Abre (o enfoca) una vista en una pestaña.
 */
function generateView(id, nombre, icon) {

    if ($('#containner-' + id).length === 0) {
        $('#contenerdores-views').append(
            '<main class="tab-pane main-content border-radius-lg change-view" ' +
            'style="margin-left:5px;" id="containner-' + id + '"></main>'
        );
        $('#footer-navigation').append(generateNewTabButton(id, nombre, icon));
    }

    $('.water').show();

    if (window.__viewCacheHTML[id]) {
        $('#containner-' + id).html(window.__viewCacheHTML[id]);
        afterViewMounted(id);
        return;
    }

    $.ajax({
        url: '/' + id,
        method: 'GET',
        dataType: 'html',
    }).done(function (html) {
        window.__viewCacheHTML[id] = html;
        $('#containner-' + id).html(html);
        afterViewMounted(id);
    }).fail(function (xhr) {
        if (xhr.status === 401) window.location.href = '/login';
    }).always(function () {
        $('.water').hide();
    });
}

/**
 * Se ejecuta cada vez que el HTML de una vista se monta en el DOM.
 */
function afterViewMounted(id) {
    if (!moduloCreado[id]) {
        includeJs(id);
    } else {
        callInitFuntion(id);
    }
    $('.water').hide();
}

/**
 * Carga el JS del módulo solo una vez.
 */
function includeJs(id) {
    var scriptId = 'js-module-' + id;

    if (document.getElementById(scriptId)) {
        callInitFuntion(id);
        return;
    }

    var urlFile = base_web + 'assets/js/sistema/' + moduloRoute[id] + '/' + id + '-controller.js?v=' + version_app;

    var scriptEle = document.createElement('script');
    scriptEle.id  = scriptId;
    scriptEle.src = urlFile;

    scriptEle.onload = function () {
        moduloCreado[id] = true;
        callInitFuntion(id);
    };

    scriptEle.onerror = function () {
        console.error('[tab-manager] Error cargando JS del módulo:', id, urlFile);
    };

    document.body.appendChild(scriptEle);
}

/**
 * Llama a xxInit() con guard automático.
 */
function callInitFuntion(id) {
    var fnName = id + 'Init';

    if (typeof window[fnName] !== 'function') {
        console.warn('[tab-manager] No existe la función ' + fnName);
        return;
    }

    if (window.__viewInited[id]) {
        var onReopen = window[id + 'OnReopen'];
        if (typeof onReopen === 'function') {
            try { onReopen(); } catch (e) { console.error(e); }
        }
        return;
    }

    try {
        window[fnName]();
        window.__viewInited[id] = true;
    } catch (e) {
        console.error('[tab-manager] Error en ' + fnName, e);
    }
}

/**
 * Destroy genérico.
 */
function callDestroyFunction(id) {
    var $cont = $('#containner-' + id);

    $cont.find('table.dataTable').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
            try { $(this).DataTable().destroy(true); } catch (e) {}
        }
    });

    $cont.find('.select2-hidden-accessible').each(function () {
        try { $(this).select2('destroy'); } catch (e) {}
    });

    $cont.find('[data-toggle="popover"], [data-bs-toggle="popover"]').each(function () {
        try { $(this).popover('dispose'); } catch (e) {}
    });
    $cont.find('[data-toggle="tooltip"], [data-bs-toggle="tooltip"]').each(function () {
        try { $(this).tooltip('dispose'); } catch (e) {}
    });

    $cont.find('input[data-daterangepicker], .daterangepicker').each(function () {
        try {
            var drp = $(this).data('daterangepicker');
            if (drp) drp.remove();
        } catch (e) {}
    });

    var destroyFn = window[id + 'Destroy'];
    if (typeof destroyFn === 'function') {
        try { destroyFn(); } catch (e) { console.error(e); }
    }

    $cont.off();
    $cont.find('*').off();

    window.__viewInited[id] = false;
}

/**
 * Marca una vista como activa.
 */
function seleccionarView(id, nombre = 'Inicio', idPadre) {
    $(".dtfh-floatingparent").remove();
    $('.change-view').removeClass("active");
    $('.seleccionar-view').removeClass("active");
    $('.nav-link.nav-padre').removeClass("active");
    $('.button-side-nav').removeClass("active");

    $('#containner-' + id).addClass("active");
    $('#tab-' + id).addClass("active");
    $('#sidenav_' + id).addClass("active");

    $("#titulo-view").text(nombre);

    // Auto-scroll al tab activo
    var $tab = $('#lista_view_' + id);
    if ($tab.length) {
        var $nav = $('#footer-navigation');
        var tabLeft = $tab.position().left;
        var tabWidth = $tab.outerWidth();
        var navScroll = $nav.scrollLeft();
        var navWidth = $nav.width();

        if (tabLeft < navScroll || (tabLeft + tabWidth) > (navScroll + navWidth)) {
            $nav.animate({
                scrollLeft: tabLeft - (navWidth / 2) + (tabWidth / 2)
            }, 200);
        }
    }
}

/**
 * Cierra una vista.
 */
function closeView(nameView) {
    var id = nameView.id.split('_')[1];

    callDestroyFunction(id);

    $('#lista_view_' + id).remove();
    $('#containner-' + id).empty().remove();

    if (TAB_CONFIG.LIBERAR_HTML_AL_CERRAR) {
        delete window.__viewCacheHTML[id];
    }

    setTimeout(function () {
        var abiertas = $('.change-view').length;
        if (abiertas === 0) seleccionarView('dashboard');
    }, 10);
}

/**
 * Botón de la pestaña.
 */
function generateNewTabButton(id, nombre, icon) {
    return `
        <li class="nav-item" id="lista_view_${id}">
            <div class="nav-link col seleccionar-view"
                 onclick="seleccionarView('${id}', '${nombre}')"
                 id="tab-${id}">
                <i class="${icon}"></i>&nbsp;
                ${nombre}&nbsp;&nbsp;
                <i class="fas fa-times-circle close_item_navigation"
                   id="closetab_${id}"
                   onclick="closeView(this)"></i>&nbsp;
            </div>
        </li>
    `;
}

/**
 * Precarga en idle las vistas más usadas.
 */
function precargarVistasFrecuentes() {
    var vistas = TAB_CONFIG.PRECARGAR_VISTAS || [];
    var i = 0;

    function siguiente() {
        if (i >= vistas.length) return;
        var id = vistas[i++];

        if (window.__viewCacheHTML[id]) {
            setTimeout(siguiente, 100);
            return;
        }

        $.get('/' + id, function (html) {
            window.__viewCacheHTML[id] = html;
        }).always(function () {
            setTimeout(siguiente, 500);
        });
    }

    setTimeout(siguiente, TAB_CONFIG.PRECARGA_DELAY_MS);
}

/**
 * Apertura desde el menú.
 */
function openNewItem(id, nombre, icon, idPadre) {
    if ($('#containner-' + id).length === 0) {
        generateView(id, nombre, icon);
    }
    seleccionarView(id, nombre, idPadre);
    document.getElementById('sidenav-main-2').click();
}

// ============================================================
// INICIALIZACIÓN (eventos globales)
// ============================================================
$(document).ready(function () {

    // -------- Wheel horizontal sobre la barra de tabs --------
    $('#footer-navigation').on('wheel', function (e) {
        var delta = e.originalEvent.deltaY;
        if (delta !== 0) {
            e.preventDefault();
            this.scrollLeft += delta;
        }
    });

    // -------- Cerrar tab con click medio (rueda) --------
    $(document).on('auxclick', '.nav-link.seleccionar-view', function (e) {
        if (e.which === 2) {   // 2 = botón medio del mouse
            e.preventDefault();
            var id = this.id.replace('tab-', '');

            // No cerrar el dashboard con click medio
            if (id === 'dashboard') return;

            var $btn = document.getElementById('closetab_' + id);
            if ($btn) closeView($btn);
        }
    });

    // -------- Precarga de vistas frecuentes --------
    precargarVistasFrecuentes();
});