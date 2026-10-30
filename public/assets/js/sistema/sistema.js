/* ============================================================
   CONFIG BASE
   ============================================================ */
const host = window.location.host;

let base_url, base_web, base_url_eco;

if (host.includes("app.portafolioerp.com")) {
    base_url = "https://app.portafolioerp.com/api/";
    base_web = "https://app.portafolioerp.com/";
    base_url_eco = "https://eco.portafolioerp.com/api/";
} else if (host.includes("test.portafolioerp.com")) {
    base_url = "https://test.portafolioerp.com/api/";
    base_web = "https://test.portafolioerp.com/";
    base_url_eco = "https://eco.portafolioerp.com/api/";
} else if (host.includes("localhost:8000")) {
    base_url = 'http://localhost:8000/api/';
    base_web = 'http://localhost:8000/';
    base_url_eco = "https://eco.portafolioerp.com/api/";
}

const pusher = new Pusher('95252a904d7ad78656ac', { cluster: 'us2' });
const bucketUrl = 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/';
const btnLogout = document.getElementById('sessionLogout');
const itemMenuActive = localStorage.getItem("item_active_menu");

var dateNow = new Date();
const auth_token = localStorage.getItem("auth_token");
const iconNavbarSidenavMaximo = document.getElementById('iconNavbarSidenavMaximo');

/* ============================================================
   HELPERS FECHAS
   ============================================================ */
const startOfDay = (date) => new Date(date.setHours(0, 0, 0, 0));
const endOfDay = (date) => new Date(date.setHours(23, 59, 59, 999));

const rangoFechas = {
    "Hoy": [startOfDay(new Date(dateNow)), endOfDay(new Date(dateNow))],
    "Ayer": [startOfDay(new Date(dateNow.getTime() - 86400000)), endOfDay(new Date(dateNow.getTime() - 86400000))],
    "Este mes": [startOfDay(new Date(dateNow.getFullYear(), dateNow.getMonth(), 1)), endOfDay(new Date(dateNow.getFullYear(), dateNow.getMonth() + 1, 0))],
    "Mes anterior": [startOfDay(new Date(dateNow.getFullYear(), dateNow.getMonth() - 1, 1)), endOfDay(new Date(dateNow.getFullYear(), dateNow.getMonth(), 0))],
    "Este año": [startOfDay(new Date(dateNow.getFullYear(), 0, 1)), endOfDay(new Date(dateNow.getFullYear(), 11, 31))]
};

/* ============================================================
   AJAX SETUP
   ============================================================ */
$.ajaxSetup({
    headers: {
        "Authorization": auth_token,
        "Content-Type": "application/json"
    }
});

const headers = {
    "Authorization": auth_token,
    "Content-Type": "application/json",
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
};

/* ============================================================
   SIDENAV ELEMENTOS
   ============================================================ */
let body = document.getElementsByTagName('body')[0];
let className = 'g-sidenav-pinned';
let sidenav = document.getElementById('sidenav-main');
let sidenav2 = document.getElementById('sidenav-main-2');
let buttonMostrarLateral = document.getElementById('button-mostrar-lateral');
let buttonocultarLateral = document.getElementById('button-ocultar-lateral');
let iconSidenav = document.getElementById('iconSidenav');

iniciarCanalesDeNotificacion();

/* ============================================================
   MAPA DE MÓDULOS (reemplaza moduloCreado + moduloRoute)
   ============================================================ */
const modulos = {
    nit: 'tablas', comprobante: 'tablas', plancuenta: 'tablas', cecos: 'tablas',
    familias: 'tablas', resolucion: 'tablas', formapago: 'tablas', bodegas: 'tablas',
    productos: 'tablas', carguedescargue: 'tablas', vendedores: 'tablas',
    documentogeneral: 'capturas', auxiliar: 'informes', balance: 'informes',
    cartera: 'informes', documentos: 'informes', documentosgenerales: 'informes',
    compras: 'informes', compra: 'capturas', ventas: 'informes', venta: 'capturas',
    usuarios: 'configuracion', empresa: 'configuracion', entorno: 'configuracion',
    productoprecios: 'importador', movimientoinventario: 'capturas',
    notacredito: 'capturas', ventasgenerales: 'informes', estadoactual: 'informes',
    estadocomprobante: 'informes', importnits: 'importador', importproductos: 'importador',
    importdocumentos: 'importador', eliminardocumentos: 'capturas', recibo: 'capturas',
    pago: 'capturas', recibos: 'informes', conceptogastos: 'tablas', gasto: 'capturas',
    resumencomprobante: 'informes', presupuesto: 'tablas', impuestos: 'informes',
    resultados: 'informes', ubicaciones: 'tablas', pedido: 'capturas',
    parqueadero: 'capturas', reserva: 'capturas', exogena: 'informes',
    resumencartera: 'informes', administradoras: 'tablas', periodos: 'tablas',
    conceptosnomina: 'tablas', contratos: 'tablas', configuracionprovisiones: 'tablas',
    novedadesgenerales: 'capturas', extracto: 'informes', causar: 'capturas',
    liquidaciondefinitiva: 'capturas', vacaciones: 'capturas',
    ventasacumuladas: 'informes', cesantiasintereses: 'capturas',
    reuniones: 'configuracion', impuesto: 'tablas', cambiodatos: 'capturas',
    primas: 'capturas', pagos: 'capturas', nominaelectronica: 'capturas',
    notificaciones: 'configuracion', movimientoproducto: 'informes',
    suscripcion: 'configuracion',
};

const modulosCargados = new Set();
const modulosCargando = new Map();

function includeJs(id) {
    if (modulosCargados.has(id)) return Promise.resolve();
    if (modulosCargando.has(id)) return modulosCargando.get(id);

    const promesa = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `${base_web}assets/js/sistema/${modulos[id]}/${id}-controller.js?v=${version_app}`;
        script.onload = () => {
            modulosCargados.add(id);
            modulosCargando.delete(id);
            resolve();
        };
        script.onerror = () => {
            modulosCargando.delete(id);
            reject(new Error('No se pudo cargar el controller: ' + id));
        };
        document.body.appendChild(script);
    });

    modulosCargando.set(id, promesa);
    return promesa;
}

/* ============================================================
   PUSHER
   ============================================================ */
function iniciarCanalesDeNotificacion() {
    const code = localStorage.getItem("notificacion_code");
    window.channelAbdelCastro = pusher.subscribe('canal-general-abdel-castro');
    // Solo suscribirse si hay code válido
    if (code && code !== 'null' && code !== '') {
        window.channelFe = pusher.subscribe('notificacion-fe-' + code);
        channelFe.bind('notificaciones', function (data) {
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
        });
    }
}

/* ============================================================
   FONDOS DE PANTALLA
   ============================================================ */
const $imagenes = [
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_1.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_2.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_3.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_4.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_5.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_6.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_7.jpeg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_8.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_9.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_10.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_11.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_12.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_13.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_14.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_15.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_16.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_17.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_18.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_19.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_20.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_21.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_22.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_23.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_24.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_25.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_26.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_27.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_28.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_29.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_30.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_31.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_32.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_33.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_34.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_35.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_36.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_37.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_38.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_39.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_40.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_41.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_42.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_43.jpg',
    'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_49.jpg'
];

const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
];

function getRandomInt(max) {
    return Math.floor(Math.random() * max);
}

/* Fondo único: preferencia del usuario, sino aleatoria. Sin pedir .../null */
(function aplicarFondo() {
    const fondoGuardado = localStorage.getItem('fondo_sistema');
    const urlImgFondo = (fondoGuardado && fondoGuardado !== 'null' && fondoGuardado !== '')
        ? bucketUrl + fondoGuardado
        : $imagenes[getRandomInt($imagenes.length)];

    const img = new Image();
    img.onload = () => {
        $('.fondo-sistema').css('background-image', 'url(' + urlImgFondo + ')');
    };
    img.src = urlImgFondo;
})();

/* ============================================================
   NOTIFICACIONES CANAL GENERAL (recarga por nueva versión)
   ============================================================ */
channelAbdelCastro.bind('notificaciones', function (data) {
    let timerInterval;
    Swal.fire({
        title: "Actualizando nueva version!",
        html: "Se recargará la pagina para aplicar la version: " + version_app,
        timer: 4000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
            timerInterval = setInterval(() => {}, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        }
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer) {}
    });
    setTimeout(function () {
        closeSessionProfile();
    }, 4000);
});

/* ============================================================
   LOGOUT
   ============================================================ */
function closeSessionProfile() {
    $.ajax({
        url: base_web + 'logout-api',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done(() => {
        limpiarLocalStorage();
        window.location.href = '/login';
    }).fail(() => {
        limpiarLocalStorage();
        window.location.href = '/login';
    });
}

function limpiarLocalStorage() {
    ['token_db_portafolio', 'auth_token', 'auth_token_erp', 'empresa_nombre',
     'notificacion_code', 'notificacion_code_general', 'fondo_sistema', 'empresa_logo']
        .forEach(k => localStorage.setItem(k, ''));
}

/* ============================================================
   CARGA INICIAL
   ============================================================ */
$('.water').show();
$('#containner-dashboard').load('/dashboard', function () {
    $('.water').hide();
});
$("#titulo-view").text('Inicio');

$(document).ajaxError(function myErrorHandler(event, xhr) {
    if (xhr.status == 401) {
        closeSessionProfile();
    }
});

$("#nombre-empresa").text(localStorage.getItem("empresa_nombre"));
$("#titulo-empresa").text(localStorage.getItem("empresa_nombre"));

if (localStorage.getItem("empresa_logo") == 'null' || !localStorage.getItem("empresa_logo")) {
    $("#side_main_logo").attr('src', '/img/logo_contabilidad.png');
} else {
    $("#side_main_logo").attr('src', bucketUrl + localStorage.getItem("empresa_logo"));
}

if (iconNavbarSidenavMaximo) {
    iconNavbarSidenavMaximo.addEventListener("click", toggleSidenavMaximo);
}

if (iconSidenav) {
    iconSidenav.addEventListener("click", toggleSidenavMaximoClose);
}

if (sidenav2) {
    sidenav2.addEventListener("click", toggleSidenavMaximo);
}

selectMenu(itemMenuActive);

/* ============================================================
   GESTIÓN DE VISTAS / TABS
   ============================================================ */
function openNewItem(id, nombre, icon, idPadre) {
    if ($('#containner-' + id).length == 0) {
        generateView(id, nombre, icon);
    }
    seleccionarView(id, nombre, idPadre);
    document.getElementById('sidenav-main-2').click();
}

function closeAnotherItems(id) {
    let items = document.getElementsByClassName("nav-padre");
    for (let index = 0; index < items.length; index++) {
        const element = items[index];
        if (element.id != 'nav_' + id) {
            element.classList.add("collapsed");
            document.getElementById('collapse' + element.id.split('_')[1]).classList.remove("show");
        }
    }
}

function closeMenu() {
    if (body.classList.contains(className)) {
        toggleSidenavMaximo();
    }
}

function generateView(id, nombre, icon) {
    $('.water').show();
    $('#contenerdores-views').append('<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-' + id + '"></main>');
    $('#footer-navigation').append(generateNewTabButton(id, nombre, icon));

    Promise.all([
        new Promise((resolve, reject) => {
            $('#containner-' + id).load('/' + id, (response, status, xhr) => {
                if (xhr.status === 401) { window.location.href = '/login'; return reject(new Error('401')); }
                resolve();
            });
        }),
        includeJs(id).catch((err) => {
            console.error(err);
            agregarToast('error', 'Error', 'No se pudo cargar el módulo: ' + id, true);
        }),
    ]).then(() => {
        callInitFuntion(id);
        $('.water').hide();
    }).catch(() => {
        $('.water').hide();
    });
}

function callInitFuntion(id) {
    const fn = window[id + 'Init'];
    if (typeof fn === 'function') {
        try { fn(); } catch (e) { console.error('Init falló para ' + id, e); }
    }
}

function seleccionarView(id, nombre = 'Inicio', idPadre) {
    $(".dtfh-floatingparent").remove();
    $('.change-view').removeClass("active");
    $('.seleccionar-view').removeClass("active");
    $('.nav-link.nav-padre').removeClass("active");
    $('.button-side-nav').removeClass("active");

    $('#containner-' + id).addClass("active");
    $('#tab-' + id).addClass("active");

    const $submenu = $('#sidenav_' + id);
    $submenu.addClass("active");

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

function generateNewTabButton(id, nombre, icon) {
    // Escapamos comillas simples para no romper onclick
    const safeNombre = String(nombre).replace(/'/g, "\\'");
    return `
        <li class="nav-item" id="lista_view_${id}">
            <div class="nav-link col seleccionar-view" onclick="seleccionarView('${id}', '${safeNombre}')" id="tab-${id}">
                <i class="${icon}"></i>&nbsp;
                ${nombre}&nbsp;&nbsp;
                <i class="fas fa-times-circle close_item_navigation"
                   id="closetab_${id}"
                   onclick="event.stopPropagation(); closeView(this)"></i>&nbsp;
            </div>
        </li>
    `;
}

function closeView(el) {
    const id = el.id.split('_')[1];
    const $tab = $('#lista_view_' + id);
    const eraActivo = $tab.find('.seleccionar-view').hasClass('active');

    // Destruir DataTables dentro del contenedor
    const cont = document.getElementById('containner-' + id);
    if (cont && $.fn.dataTable) {
        $(cont).find('table').each(function () {
            if ($.fn.dataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }
        });
    }

    // Destroyer opcional del controller
    if (typeof window[id + 'Destroy'] === 'function') {
        try { window[id + 'Destroy'](); } catch (e) { console.error(e); }
    }

    $tab.remove();
    $('#containner-' + id).empty().remove();

    if (eraActivo) {
        const $next = $('#footer-navigation .nav-item').last();
        const nextId = $next.attr('id')?.replace('lista_view_', '') || 'dashboard';
        const nextNombre = $next.find('.seleccionar-view').text().trim() || 'Inicio';
        seleccionarView(nextId, nextNombre);
    }
}

$("#tab-dashboard").click(function () {
    seleccionarView('dashboard');
});

/* ============================================================
   TOGGLE SIDENAV
   ============================================================ */
function toggleSidenavMaximo() {
    if (body.classList.contains(className)) {
        body.classList.remove(className);
        sidenav.classList.remove('bg-transparent');
        sidenav.classList.add('side-nav-maximo-close');
        sidenav.classList.remove('side-nav-maximo-open');
        buttonMostrarLateral.classList.remove('ocultar');
        buttonocultarLateral.classList.add('ocultar');
        setTimeout(function () {
            sidenav.classList.remove('bg-white');
        }, 100);
    } else {
        body.classList.add(className);
        sidenav.classList.add('bg-white');
        sidenav.classList.remove('bg-transparent');
        iconSidenav.classList.remove('d-none');
        sidenav.classList.add('side-nav-maximo-open');
        sidenav.classList.remove('side-nav-maximo-close');
        buttonMostrarLateral.classList.add('ocultar');
        buttonocultarLateral.classList.remove('ocultar');
    }
}

function toggleSidenavMaximoOpen() {
    body.classList.add(className);
    sidenav.classList.add('bg-white');
    sidenav.classList.remove('bg-transparent');
    iconSidenav.classList.remove('d-none');
    sidenav.classList.add('side-nav-maximo-open');
    sidenav.classList.remove('side-nav-maximo-close');
}

function toggleSidenavMaximoClose() {
    body.classList.remove(className);
    setTimeout(function () {
        sidenav.classList.remove('bg-white');
    }, 100);
    sidenav.classList.remove('bg-transparent');
    sidenav.classList.add('side-nav-maximo-close');
    sidenav.classList.remove('side-nav-maximo-open');
}

/* ============================================================
   LENGUAJE DATATABLE
   ============================================================ */
const lenguajeDatatable = {
    "sProcessing": "",
    "sLengthMenu": "Mostrar _MENU_ registros",
    "sZeroRecords": `<div style="text-align: center; padding: 40px 20px;">
        <div style="background: #f8f9fa; border-radius: 50%; width: 80px; height: 80px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
            <i class="fas fa-search" style="font-size: 32px; color: #6c757d;"></i>
        </div>
        <h4 style="color: #495057; margin: 0 0 8px 0; font-weight: 500;">Sin resultados</h4>
        <p style="color: #6c757d; margin: 0; font-size: 14px;">Intenta con otros términos de búsqueda</p>
    </div>`,
    "sEmptyTable": `<div style="text-align: center; padding: 40px 20px;">
        <div style="background: #e7f3ff; border-radius: 50%; width: 80px; height: 80px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
            <i class="fas fa-table" style="font-size: 32px; color: #007bff;"></i>
        </div>
        <h4 style="color: #495057; margin: 0 0 8px 0; font-weight: 500;">Tabla vacía</h4>
        <p style="color: #6c757d; margin: 0; font-size: 14px;">No hay registros para mostrar en este momento</p>
    </div>`,
    "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ registros",
    "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
    "sInfoFiltered": "(filtrado de _MAX_ registros totales)",
    "sInfoPostFix": "",
    "sSearch": "Buscar:",
    "sUrl": "",
    "sInfoThousands": ",",
    "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "›",
        "sPrevious": "‹"
    },
    "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    }
};

const animateDatatables = function (selector) {
    if (!window.anime) return;
    anime.animate(selector + ' tbody tr', {
        opacity: [0, 1],
        translateY: [14, 0],
        duration: 600,
        delay: anime.stagger(35),
        ease: 'outExpo'
    });
};

/* ============================================================
   UTILIDADES GENERALES
   ============================================================ */
function swalFire(titulo, mensaje, estado = true) {
    var status = estado ? 'success' : 'error';
    Swal.fire(titulo, mensaje, status);
}

function getRowById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if (element.id == idData) return index;
    }
    return false;
}

function getDataById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if (element.id == idData) return element;
    }
    return false;
}

function getIndexById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if (element.id == idData) return index;
    }
    return false;
}

/* ============================================================
   MODALES USUARIO / NIT
   ============================================================ */
function showUser(id_usuario, fecha, creado) {
    if (!id_usuario) return;

    $('#usuario_accion').val('');
    $('#correo_accion').val('');
    $('#fecha_accion').val('');
    $("#modal-title-usuario-accion").html("Buscando usuario ...");

    $('#modal-usuario-accion').modal('show');
    $('.water').show();
    $.ajax({
        url: base_url + 'usuario-accion',
        method: 'GET',
        data: { id: id_usuario },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            var data = res.data;
            $('#usuario_accion').val(data.username);
            $('#correo_accion').val(data.email);
            $('#fecha_accion').val(fecha);
            $("#modal-title-usuario-accion").html((creado ? "Creado por: " : "Actualizado por: ") + data.username);
        }
        $('.water').hide();
    }).fail(() => {
        swalFire('Error al cargar usuario', '', false);
        $('.water').hide();
    });
}

function showNit(id_nit) {
    if (!id_nit) return;

    $('#numero_documento').val('');
    $('#nombre_completo').val('');
    $('#direccion').val('');
    $('#telefono_1').val('');
    $('#email').val('');
    $('#observaciones').val('');
    $('#ciudad').val('');

    $('#modal-nit-informacion').modal('show');
    $('.water').show();

    $.ajax({
        url: base_url + 'nit/informacion',
        method: 'GET',
        data: { id_nit: id_nit },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if (res.success) {
            var data = res.data;
            if (data.logo_nit) {
                $('#avatar_nit').attr('src', 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/' + data.logo_nit);
            } else {
                $('#avatar_nit').attr('src', '/img/theme/tim.png');
            }
            $('#numero_documento_nit').val(data.numero_documento);
            $('#nombre_completo_nit').val(data.nombre_nit);
            $('#direccion_nit').val(data.direccion);
            $('#telefono_1_nit').val(data.telefono_1);
            $('#email_nit').val(data.email);
            $('#observaciones_nit').val(data.observaciones);
            $('#ciudad_nit').val(data.ciudad ? data.ciudad.nombre_completo : '');
        }
        $('.water').hide();
    }).fail(() => {
        swalFire('Error al cargar nit', '', false);
        $('.water').hide();
    });
}

/* ============================================================
   TOASTS
   ============================================================ */
const contenedorToast = document.getElementById('contenedor-toast');
let toastCounter = 0;

contenedorToast.addEventListener('click', (e) => {
    const toast = e.target.closest('div.toast');
    if (toast && e.target.closest('button.btn-cerrar')) {
        cerrarToast(toast.id);
    }
});

function cerrarToast(id) {
    document.getElementById(id)?.classList.add('cerrando');
}

function agregarToast(tipo, titulo, descripcion, autoCierre = false, tiempoCierre = 3000) {
    if (descripcion === null || descripcion === undefined || !descripcion) return;

    const nuevoToast = document.createElement('div');
    nuevoToast.classList.add('toast');
    nuevoToast.classList.add(tipo);
    if (autoCierre) nuevoToast.classList.add('autoCierre');

    toastCounter = (toastCounter + 1) % 1000000;
    const toastId = 'toast_' + Date.now() + '_' + toastCounter;
    nuevoToast.id = toastId;

    const iconos = {
        exito: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm10.03 4.97a.75.75 0 0 1 .011 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.75.75 0 0 1 1.08-.022z"/></svg>`,
        error: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>`,
    };

    const tieneTabla = descripcion.includes('<table');

    var toast = `
        <div class="contenido ${tieneTabla ? 'con-tabla' : ''}">
            <div class="icono">
                ${iconos[tipo]}
            </div>
            <div class="texto">
                <p class="titulo">${titulo}</p>
                <div class="descripcion ${tieneTabla ? 'contenido-tabla' : ''}">
                    ${descripcion}
                </div>
            </div>
        </div>
        <button class="btn-cerrar" onclick="event.stopPropagation(); cerrarToast('${toastId}')" href="javascript:void(0)">
            <div class="icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
            </div>
        </button>
    `;

    nuevoToast.innerHTML = toast;
    contenedorToast.appendChild(nuevoToast);

    if (tieneTabla) {
        setTimeout(() => {
            const descripcionDiv = nuevoToast.querySelector('.descripcion');
            const tablas = descripcionDiv.querySelectorAll('table');
            tablas.forEach(tabla => {
                tabla.style.width = '100%';
                tabla.style.maxWidth = '300px';
                tabla.style.margin = '8px 0';
                tabla.style.borderCollapse = 'collapse';
                tabla.style.overflow = 'visible';
                if (typeof bootstrap !== 'undefined') {
                    tabla.classList.add('table', 'table-sm', 'table-bordered');
                }
            });
            const texto = nuevoToast.querySelector('.texto');
            texto.style.overflowY = 'visible';
            texto.style.maxHeight = 'none';
            nuevoToast.style.maxWidth = '500px';
            nuevoToast.style.minWidth = '350px';
            const contenido = nuevoToast.querySelector('.contenido');
            contenido.style.maxHeight = 'none';
        }, 10);
    }

    const handleAnimacionCierre = (e) => {
        if (e.animationName === 'cierre') {
            nuevoToast.removeEventListener('animationend', handleAnimacionCierre);
            nuevoToast.remove();
        }
    };

    if (autoCierre) {
        setTimeout(() => cerrarToast(toastId), tiempoCierre);
    }

    nuevoToast.addEventListener('animationend', handleAnimacionCierre);
}

/* ============================================================
   DESCARGAS
   ============================================================ */
function loadExcel(data) {
    setTimeout(function () {
        window.open('https://' + data.url_file, "_blank");
        agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
    }, 1000);
}

function loadPdf(data) {
    setTimeout(function () {
        window.open('https://' + data.url_file_pdf, "_blank");
        agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
    }, 1000);
}

/* ============================================================
   LOGOUT BOTÓN
   ============================================================ */
if (btnLogout) {
    btnLogout.addEventListener('click', event => {
        event.preventDefault();
        $.ajax({
            url: base_web + 'logout-api',
            method: 'POST',
            headers: headers,
            dataType: 'json',
        }).done(() => {
            limpiarLocalStorage();
            window.location.href = '/login';
        }).fail(() => {
            limpiarLocalStorage();
            window.location.href = '/login';
        });
    });
}

/* ============================================================
   FORMATEO NÚMEROS / FECHAS
   ============================================================ */
function numberWithCommas(x) {
    x = x.toString();
    var pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}

function formatNumber(n) {
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function formatCurrencyValue(value) {
    if (value) {
        value = value + '';
        if (value.indexOf(".") >= 0) {
            var decimal_pos = value.indexOf(".");
            var left_side = value.substring(0, decimal_pos);
            var right_side = value.substring(decimal_pos);
            left_side = formatNumber(left_side);
            right_side = formatNumber(parseFloat(right_side).toFixed(2).slice(1));
            right_side = right_side.substring(0, 2);
            valorFormato = left_side + "." + right_side;
            return valorFormato;
        } else {
            return formatNumber(value) + ".00";
        }
    } else {
        return '0.00';
    }
}

function formatCurrency(input, blur) {
    var input_val = input.val();
    input_val = input_val.replace(',', '');
    if (input_val === "") return;

    var original_len = input_val.length;
    var caret_pos = input.prop("selectionStart");

    if (input_val.indexOf(".") >= 0) {
        var decimal_pos = input_val.indexOf(".");
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);
        left_side = formatNumber(left_side);
        right_side = formatNumber(right_side);
        if (blur === "blur") right_side += "00";
        right_side = right_side.substring(0, 2);
        input_val = left_side + "." + right_side;
    } else {
        input_val = formatNumber(input_val);
        if (blur === "blur") input_val += ".00";
    }

    input.val(input_val);
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
}

function stringToNumberFloat(value) {
    if (!value && value !== 0) return 0;
    value = value.toString();
    const cleaned = value.replaceAll(',', '');
    const num = parseFloat(cleaned);
    if (isNaN(num)) return 0;
    return num;
}

function arreglarMensajeError(mensaje) {
    var errorsMsg = '';
    if (typeof mensaje === 'object') {
        for (field in mensaje) {
            var errores = mensaje[field];
            for (campo in errores) {
                errorsMsg += `<b>${field}:</b> ${errores[campo]} <br>`;
            }
        }
    } else if (typeof mensaje === 'string') {
        errorsMsg = mensaje;
    }
    return errorsMsg;
}

function cargarPopoverGeneral() {
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
}

function redondear(valor, redondeo_valor = null) {
    if (redondeo_valor == '') redondeo_valor = null;
    if (!redondeo_valor && redondeo_valor != 0) return valor;
    redondeo_valor = parseFloat(redondeo_valor);
    if (!valor) return valor;
    if (redondeo_valor === null) return valor;
    if (redondeo_valor === 0) return Math.floor(valor);
    return Math.round(valor / redondeo_valor) * redondeo_valor;
}

function primeraFormaPago(formasPagos, nombreInput) {
    for (let index = 0; index < formasPagos.length; index++) {
        const formaPago = formasPagos[index];
        const input = document.getElementById(`${nombreInput}_${formaPago.id}`);
        if (input && input.disabled) continue;
        return formaPago.id;
    }
}

function getResponsabilidades(id_responsabilidades) {
    if (id_responsabilidades) return id_responsabilidades.split(',');
    return [];
}

/* Normalizar fecha ÚNICA (la que agrega T00:00) */
function normalizarFecha(fecha) {
    if (fecha && fecha.length === 10) return fecha + 'T00:00';
    return fecha;
}

function focusNexInput(e, inputId, type = null) {
    if (!e.keyCode) {
        if (type == 'select') {
            document.getElementById(inputId).focus();
        }
    }
    if (e.key === 'Enter' || e.keyCode === 13) {
        document.getElementById(inputId).focus();
    }
}

function formatoFecha(start, end, input) {
    var isStartTimeDefault = start.hours() === 0 && start.minutes() === 0 && start.seconds() === 0;
    var isEndTimeDefault = end.hours() === 23 && end.minutes() === 59 && end.seconds() === 59;

    if (isStartTimeDefault && isEndTimeDefault) {
        start.startOf('day');
        end.endOf('day');
    }

    $("#" + input).html(start.format("MMMM D, YYYY HH:mm:ss") + " - " + end.format("MMMM D, YYYY HH:mm:ss"));
}

function parseManualInput(value, input) {
    var parts = value.split(' - ');
    if (parts.length === 2) {
        var start = moment(parts[0], "YYYY-MM-DD HH:mm:ss");
        var end = moment(parts[1], "YYYY-MM-DD HH:mm:ss");
        if (start.isValid() && end.isValid()) {
            if (parts[0].length <= 10) start.startOf('day');
            if (parts[1].length <= 10) end.endOf('day');
            $("#" + input).data('daterangepicker').setStartDate(start);
            $("#" + input).data('daterangepicker').setEndDate(end);
            formatoFecha(start, end, input);
        }
    }
}

function formatNumberWithSmallDecimals(number) {
    const formatted = new Intl.NumberFormat('ja-JP').format(number);
    const parts = formatted.split('.');
    if (parts.length > 1) {
        return `<span class="integer-part">${parts[0]}</span><span class="decimal-part">.${parts[1]}</span>`;
    }
    return formatted;
}

$(document).on('shown.bs.popover', function () {
    $('.popover b.titulo-popover').css({ 'color': '#72ffff', 'font-weight': 'bold' });
    $('.popover b.titulo-popover-error').css({ 'color': 'red', 'font-weight': 'bold' });
    $('.popover b.mensaje-blanco').css({ 'color': '#FFF', 'font-weight': 'bold' });
});

function cargasFechasGenerales(input, start = moment().startOf("month"), end = moment().endOf("month"), dates = rangoFechas) {
    $(`#${input}`).daterangepicker({
        startDate: start,
        endDate: end,
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
        locale: {
            format: "YYYY-MM-DD",
            separator: " - ",
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        },
        ranges: dates
    }, formatoFecha);

    formatoFecha(start, end, input);
}

function abrirVistaDesdeBanner(id, nombre, icon) {
    if ($('#containner-' + id).length === 0) {
        generateView(id, nombre, icon);
    }
    seleccionarView(id, nombre);
}

/* ============================================================
   SELECT MENU (facturación / contabilidad / nómina)
   ============================================================ */
function selectMenu(menu) {
    const mapa = {
        facturacion:  { nombre: 'Facturación',  icono: 'fas fa-file-invoice-dollar', tipo: 2 },
        contabilidad: { nombre: 'Contabilidad', icono: 'fas fa-calculator',          tipo: 1 },
        nomina:       { nombre: 'Nomina',       icono: 'fas fa-users',               tipo: 3 },
    };
    const cfg = mapa[menu];
    if (!cfg) return;

    // Estado restringido: no aplica selector de tipos
    if (typeof estadoRestringido !== 'undefined' && estadoRestringido) {
        document.getElementById('sidenav-collapse-main')?.removeAttribute('data-tipo');
        $('#dropdownTiposMenu').closest('.dropdown').hide();
        return;
    }

    const cont = document.getElementById('sidenav-collapse-main');
    if (cont) cont.setAttribute('data-tipo', cfg.tipo);

    localStorage.setItem('item_active_menu', menu);
    $('#dropdownTiposMenu').html(`<i class="${cfg.icono} me-1"></i>${cfg.nombre}`);
}

/* ============================================================
   LISTENER GLOBAL: banner estado restringido
   ============================================================ */
document.addEventListener('click', function (e) {
    if (typeof estadoRestringido === 'undefined' || !estadoRestringido) return;
    const banner = document.getElementById('banner-estado-restringido');
    if (!banner) return;
    if (e.target.closest('#tab-dashboard')) {
        banner.style.display = '';
    }
});