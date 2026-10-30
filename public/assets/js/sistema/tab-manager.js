// ============================================================
// TAB MANAGER — Sistema de pestañas SPA con cache de HTML
// Requiere que el archivo principal defina:
//   modulos, modulosCargados, modulosCargando, includeJs,
//   base_web, version_app, $ (jQuery)
// ============================================================

var TAB_CONFIG = {
    LIBERAR_HTML_AL_CERRAR: false,
    PRECARGAR_VISTAS: ['dashboard'],
    PRECARGA_DELAY_MS: 3000,
};

window.__viewCacheHTML = {};   // { id: htmlString }
window.__viewInited    = {};   // { id: true }  → init ya ejecutado

// ============================================================
// ABRIR VISTA
// ============================================================
function generateView(id, nombre, icon) {

    // ¿Ya existe el contenedor y tab? Entonces solo montamos el HTML
    const yaExiste = $('#containner-' + id).length > 0;

    if (!yaExiste) {
        $('#contenerdores-views').append(
            '<main class="tab-pane main-content border-radius-lg change-view" ' +
            'style="margin-left:5px;" id="containner-' + id + '"></main>'
        );
        $('#footer-navigation').append(generateNewTabButton(id, nombre, icon));
    }

    $('.water').show();

    // Si hay cache de HTML y el contenedor sigue en el DOM, lo re-montamos
    if (yaExiste && window.__viewCacheHTML[id]) {
        $('#containner-' + id).html(window.__viewCacheHTML[id]);
        afterViewMounted(id);
        return;
    }

    // 1) Traer HTML + cargar controller en paralelo
    Promise.all([
        new Promise(function (resolve, reject) {
            $.ajax({
                url: '/' + id,
                method: 'GET',
                dataType: 'html',
            }).done(function (html) {
                window.__viewCacheHTML[id] = html;
                resolve(html);
            }).fail(function (xhr) {
                if (xhr.status === 401) window.location.href = '/login';
                reject(new Error('HTML ' + id + ' falló: ' + xhr.status));
            });
        }),
        includeJs(id).catch(function (err) {
            console.error('[tab-manager]', err);
            if (typeof agregarToast === 'function') {
                agregarToast('error', 'Error', 'No se pudo cargar el módulo: ' + id, true);
            }
        }),
    ]).then(function (result) {
        $('#containner-' + id).html(result[0]);
        afterViewMounted(id);
    }).catch(function (err) {
        console.error('[tab-manager] generateView:', err);
        $('.water').hide();
    });
}

/**
 * Se ejecuta cada vez que el HTML de una vista se monta en el DOM.
 * Solo inicializa la PRIMERA vez; si se re-monta, avisa con xxOnReopen().
 */
function afterViewMounted(id) {
    try {
        callInitFuntion(id);
    } catch (e) {
        console.error('[tab-manager] afterViewMounted:', e);
    }
    $('.water').hide();
}

/**
 * Init con guard: primera vez → xxInit(); siguientes → xxOnReopen() si existe.
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

// ============================================================
// DESTROY
// ============================================================
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

// ============================================================
// SELECCIONAR VISTA
// ============================================================
function seleccionarView(id, nombre, idPadre) {
    nombre = nombre || 'Inicio';

    $(".dtfh-floatingparent").remove();
    $('.change-view').removeClass("active");
    $('.seleccionar-view').removeClass("active");
    $('.nav-link.nav-padre').removeClass("active");
    $('.button-side-nav').removeClass("active");

    $('#containner-' + id).addClass("active");
    $('#tab-' + id).addClass("active");

    // Submenú activo
    const $submenu = $('#sidenav_' + id);
    $submenu.addClass("active");

    // Padre activo
    const idPadreFinal = idPadre || $submenu.closest('li.nav-item').find('.nav-padre').attr('id')?.replace('nav_', '');
    if (idPadreFinal) {
        const $padre = $('#nav_' + idPadreFinal);
        $padre.addClass("active").removeClass("collapsed");
        $padre.attr('aria-expanded', 'true');

        const $collapse = $('#collapse' + idPadreFinal);
        if ($collapse.length) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
                const instance = bootstrap.Collapse.getOrCreateInstance($collapse[0], { toggle: false });
                instance.show();
            } else {
                $collapse.addClass('show');
            }
        }
    }

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

// ============================================================
// CERRAR VISTA
// ============================================================
function closeView(el) {
    var id = el.id.split('_')[1];
    var $tab = $('#lista_view_' + id);
    var eraActivo = $tab.find('.seleccionar-view').hasClass('active');

    callDestroyFunction(id);

    $tab.remove();
    $('#containner-' + id).empty().remove();

    if (TAB_CONFIG.LIBERAR_HTML_AL_CERRAR) {
        delete window.__viewCacheHTML[id];
        delete window.__viewInited[id];
    }

    // Si era la activa, ir a la vecina (o al dashboard si no queda ninguna)
    if (eraActivo) {
        var $next = $('#footer-navigation .nav-item').last();
        var nextId = $next.attr('id')?.replace('lista_view_', '') || 'dashboard';
        var nextNombre = $next.find('.seleccionar-view').text().trim() || 'Inicio';
        seleccionarView(nextId, nextNombre);
    }
}

// ============================================================
// TAB BUTTON
// ============================================================
function generateNewTabButton(id, nombre, icon) {
    var safeNombre = String(nombre).replace(/'/g, "\\'");
    return `
        <li class="nav-item" id="lista_view_${id}">
            <div class="nav-link col seleccionar-view"
                 onclick="seleccionarView('${id}', '${safeNombre}')"
                 id="tab-${id}">
                <i class="${icon}"></i>&nbsp;
                ${nombre}&nbsp;&nbsp;
                <i class="fas fa-times-circle close_item_navigation"
                   id="closetab_${id}"
                   onclick="event.stopPropagation(); closeView(this)"></i>&nbsp;
            </div>
        </li>
    `;
}

// ============================================================
// PRECARGA EN IDLE
// ============================================================
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

// ============================================================
// APERTURA DESDE MENÚ
// ============================================================
function openNewItem(id, nombre, icon, idPadre) {
    if ($('#containner-' + id).length === 0) {
        generateView(id, nombre, icon);
    }
    seleccionarView(id, nombre, idPadre);
    var side2 = document.getElementById('sidenav-main-2');
    if (side2) side2.click();
}

function closeAnotherItems(id) {
    var items = document.getElementsByClassName("nav-padre");
    for (var index = 0; index < items.length; index++) {
        var element = items[index];
        if (element.id != 'nav_' + id) {
            element.classList.add("collapsed");
            var c = document.getElementById('collapse' + element.id.split('_')[1]);
            if (c) c.classList.remove("show");
        }
    }
}

function closeMenu() {
    if (typeof className !== 'undefined' && document.body.classList.contains(className)) {
        toggleSidenavMaximo();
    }
}

function abrirVistaDesdeBanner(id, nombre, icon) {
    if ($('#containner-' + id).length === 0) {
        generateView(id, nombre, icon);
    }
    seleccionarView(id, nombre);
}

// ============================================================
// INICIALIZACIÓN (eventos globales)
// ============================================================
$(document).ready(function () {

    // Wheel horizontal sobre la barra de tabs
    $('#footer-navigation').on('wheel', function (e) {
        var delta = e.originalEvent.deltaY;
        if (delta !== 0) {
            e.preventDefault();
            this.scrollLeft += delta;
        }
    });

    // Cerrar tab con click medio (rueda)
    $(document).on('auxclick', '.nav-link.seleccionar-view', function (e) {
        if (e.which === 2) {
            e.preventDefault();
            var id = this.id.replace('tab-', '');
            if (id === 'dashboard') return;
            var $btn = document.getElementById('closetab_' + id);
            if ($btn) closeView($btn);
        }
    });

    // Precarga de vistas frecuentes
    precargarVistasFrecuentes();
});