// Pusher.logToConsole = true;
const pusher = new Pusher('9ea234cc370d308638af', {cluster: 'us2'});
// const base_url = 'http://localhost:8000/api/';
// const base_web = 'http://localhost:8000/';
const bucketUrl = 'https://bucketlistardatos.nyc3.digitaloceanspaces.com/';
const btnLogout = document.getElementById('sessionLogout');
const base_url = 'https://listardatos.com/api/';
const base_web = 'https://listardatos.com/';
const dateNow = new Date();
const auth_token = localStorage.getItem("auth_token");
const iconNavbarSidenavMaximo = document.getElementById('iconNavbarSidenavMaximo');
const headers = {
    "Authorization": auth_token,
    "Content-Type": "application/json",
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
};

let body = document.getElementsByTagName('body')[0];
let className = 'g-sidenav-pinned';
let sidenav = document.getElementById('sidenav-main');
let sidenav2 = document.getElementById('sidenav-main-2');
let buttonMostrarLateral = document.getElementById('button-mostrar-lateral');
let buttonocultarLateral = document.getElementById('button-ocultar-lateral');
let iconSidenav = document.getElementById('iconSidenav');

var moduloCreado = {
    'nit': false,
    'comprobante': false,
    'plancuenta': false,
    'cecos': false,
    'familias': false,
    'resoluciones': false,
    'formapago': false,
    'bodegas': false,
    'productos': false,
    'documentogeneral': false,
    'documentosgenerales': false,
    'auxiliar': false,
    'balance': false,
    'cartera': false,
    'documentos': false,
    'compra': false,
    'venta': false,
    'compras': false,
    'ventas': false,
    'usuarios': false,
    'empresa': false,
    'productoprecios': false,
    'carguedescargue': false,
};

var moduloRoute = {
    'nit': 'tablas',
    'comprobante': 'tablas',
    'plancuenta': 'tablas',
    'cecos': 'tablas',
    'familias': 'tablas',
    'resolucion': 'tablas',
    'formapago': 'tablas',
    'bodegas': 'tablas',
    'productos': 'tablas',
    'carguedescargue': 'tablas',
    'documentogeneral': 'capturas',
    'auxiliar': 'informes',
    'balance': 'informes',
    'cartera': 'informes',
    'documentos': 'informes',
    'documentosgenerales': 'informes',
    'compras': 'informes',
    'compra': 'capturas',
    'ventas': 'informes',
    'venta': 'capturas',
    'usuarios': 'configuracion',
    'empresa': 'configuracion',
    'productoprecios': 'importador',
}

$('.water').show();
$('#containner-dashboard').load('/dashboard', function() {
    $('.water').hide();
});
$("#titulo-view").text('Inicio');

$(document).ajaxError(function myErrorHandler(event, xhr, ajaxOptions, thrownError) {
    // console.log('xhr: ',xhr);
    if(xhr.status == 401) {
        document.getElementById('logout-form').submit();
    }
});

$("#nombre-empresa").text(localStorage.getItem("empresa_nombre"));
// $("#nombre-usuario-topnav").text(localStorage.getItem("empresa_nombre"));
$("#titulo-empresa").text(localStorage.getItem("empresa_nombre"));

if (localStorage.getItem("empresa_logo") == 'null') {
    $("#side_main_logo").attr('src', '/img/logo_contabilidad.png');
} else{ 
    $("#side_main_logo").attr('src', bucketUrl+localStorage.getItem("empresa_logo"));
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

function openNewItem(id, nombre, icon) {
    if($('#containner-'+id).length == 0) {
        generatView(id, nombre, icon);
    }
    seleccionarView(id, nombre);
    document.getElementById('sidenav-main-2').click();
}    

function generatView(id, nombre, icon){
    $('.water').show();
    $('#contenerdores-views').append('<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-'+id+'"></main>');
    $('#footer-navigation').append(generateNewTabButton(id, nombre, icon));
    $('#containner-'+id).load('/'+id, function() {

        if(!moduloCreado[id]) includeJs(id);
        else callInitFuntion(id);

        $('.water').hide();
    });
}

function callInitFuntion(id) {
    var functionInit = id+'Init';
    window[functionInit]();
}

function includeJs(id){
    let scriptEle = document.createElement("script");

    let urlFile = base_web + "assets/js/sistema/"+moduloRoute[id]+"/"+id+"-controller.js";
    scriptEle.setAttribute("src", urlFile);
    scriptEle.onload = function () {
        callInitFuntion(id);
    };
    document.body.appendChild(scriptEle);
    moduloCreado[id] = true;
}

function seleccionarView(id, nombre = 'Inicio'){

    $(".dtfh-floatingparent").remove();
    $('.change-view').removeClass("active");
    $('.seleccionar-view').removeClass("active");
    $('.button-side-nav').removeClass("active");
    $('#containner-'+id).addClass("active");
    $('#tab-'+id).addClass("active");
    $('#sidenav_'+id).addClass("active");
    
    $("#titulo-view").text(nombre);
}

function generateNewTabView(id){
    var html = '<main class="tab-pane main-content border-radius-lg change-view" style="margin-left: 5px;" id="containner-'+id+'"></main>';
}

function generateNewTabButton(id, nombre, icon){
    
    var html = `
        <li class="nav-item" id="lista_view_${id}">
            <div class="nav-link col seleccionar-view" onclick="seleccionarView('${id}', '${nombre}')" id="tab-${id}">
                <i class="${icon}"></i>&nbsp;
                ${nombre}&nbsp;&nbsp;
                <i class="fas fa-times-circle close_item_navigation" id="closetab_${id}" onclick="closeView(this)"></i>&nbsp;
            </div>
        </li>
    `;
    return html;
}

function closeView(nameView) {
    var id = nameView.id.split('_')[1];
    
    $("#lista_view_"+id).remove();
    $("#containner-"+id).empty();
    $("#containner-"+id).remove();

    setTimeout(() => {
        seleccionarView('dashboard');
    }, 10)
}

function toggleSidenavMaximo() {
    if (body.classList.contains(className)) {

        body.classList.remove(className);
        sidenav.classList.remove('bg-transparent');
        sidenav.classList.add('side-nav-maximo-close');
        sidenav.classList.remove('side-nav-maximo-open');
        buttonMostrarLateral.classList.remove('ocultar');
        buttonocultarLateral.classList.add('ocultar');
        setTimeout(function() {
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
    setTimeout(function() {
    sidenav.classList.remove('bg-white');
    }, 100);
    sidenav.classList.remove('bg-transparent');
    sidenav.classList.add('side-nav-maximo-close');
    sidenav.classList.remove('side-nav-maximo-open');
}

//PERSONAL TABLE LENGUAJE
const lenguajeDatatable = {
    "sProcessing":     "Cargando <span><i style='font-size: 15px' class='fa fa-spinner fa-spin'></i></span>",
    "sLengthMenu":     "Mostrar _MENU_ registros",
    "sZeroRecords":    "No se encontraron resultados",
    "sEmptyTable":     "Ningún registro disponible",
    "sInfo":           "Registros del _START_ al _END_ de un total de _TOTAL_ ",
    "sInfoEmpty":      "Registros del 0 al 0 de un total de 0 ",
    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
    "sInfoPostFix":    "",
    "sSearch":         "Buscar:",
    "sUrl":            "",
    "sInfoThousands":  ",",
    "oPaginate": {
        "sFirst":    "Primero",
        "sLast":     "Último",
        "sNext":     ">",
        "sPrevious": "<"
    },
    "oAria": {
        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
    }
}

// $("#button-login").click(function(event){
    
//     $("#button-login-loading").show();
//     $("#button-login").hide();

//     $.ajax({
//         url: base_web + 'login',
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         method: 'POST',
//         data: {
//             "email": $('#email_login').val(),
//             "password": $('#password_login').val(),
//             "_token": $('meta[name="csrf-token"]').attr('content'),
//         },
//         dataType: 'json',
//     }).done((res) => {
//         $("#button-login-loading").hide();
//         $("#button-login").show();
//         if(res.success){
//             localStorage.setItem("auth_token", res.token_type+' '+res.access_token);
//             localStorage.setItem("empresa_nombre", res.empresa.razon_social);
//             localStorage.setItem("empresa_logo", res.empresa.logo);
//             window.location.href = '/home';
//         }
//     }).fail((err) => {
//         $("#button-login-loading").hide();
//         $("#button-login").show();
//     });
// });

$(".btn-cerrar").click(function(event){
    console.log('asdasd');
});

function swalFire(titulo, mensaje, estado = true){
    var status = estado ? 'success' : 'error';
    Swal.fire(
        titulo,
        mensaje,
        status
    )
}

function getRowById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if(element.id == idData){
            return index;
        }
    }
    return false;
}

function getDataById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if(element.id == idData){
            return element;
        }
    }
    return false;
}

function showUser (id_usuario, fecha, creado) {

    if(!id_usuario) {
        return;
    }

    $('#usuario_accion').val('');
    $('#correo_accion').val('');
    $('#fecha_accion').val('');
    $("#modal-title-usuario-accion").html("Buscando usuario ...");

    $('#modal-usuario-accion').modal('show');
    $('.water').show();
    $.ajax({
        url: base_url + 'usuario-accion',
        method: 'GET',
        data: {id: id_usuario},
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            var data = res.data;
            $('#usuario_accion').val(data.username);
            $('#correo_accion').val(data.email);
            $('#fecha_accion').val(fecha);

            if (creado) {
                $("#modal-title-usuario-accion").html("Creado por: "+ data.username);
            } else {
                $("#modal-title-usuario-accion").html("Actualizado por: "+ data.username);
            }
        }
        $('.water').hide();
    }).fail((err) => {
        swalFire('Error al cargar usuario', '', false);
        $('.water').hide();
    });
}

function showNit (id_nit) {

    if(!id_nit) {
        return;
    }

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
        data: {id_nit: id_nit},
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            var data = res.data;
            if(data.logo_nit) {
                $('#avatar_nit').attr('src', 'bucketlistardatos.nyc3.digitaloceanspaces.com/'+data.logo_nit);
            } else {
                $('#avatar_nit').attr('src', '/img/theme/tim.png');
            }
            $('#numero_documento_nit').val(data.numero_documento);
            $('#nombre_completo_nit').val(data.nombre_nit);
            $('#direccion_nit').val(data.direccion);
            $('#telefono_1_nit').val(data.telefono_1);
            $('#email_nit').val(data.email);
            $('#observaciones_nit').val(data.observaciones);
            $('#ciudad_nit').val(data.ciudad.nombre_completo);
        }
        $('.water').hide();
    }).fail((err) => {
        swalFire('Error al cargar nit', '', false);
        $('.water').hide();
    });
}


// const contenedorBotones = document.getElementById('contenedor-botones');
const contenedorToast = document.getElementById('contenedor-toast');

// Event listener para detectar click en los botones
// contenedorBotones.addEventListener('click', (e) => {
//     e.preventDefault();

//     const tipo = e.target.dataset.tipo;

//     if (tipo === 'exito') {
//         agregarToast({ tipo: 'exito', titulo: 'Exito!', descripcion: 'La operación fue exitosa.', autoCierre: true });
//     }
//     if (tipo === 'error') {
//         agregarToast({ tipo: 'error', titulo: 'Error', descripcion: 'Hubo un error', autoCierre: true });
//     }
//     if (tipo === 'info') {
//         agregarToast({ tipo: 'info', titulo: 'Info', descripcion: 'Esta es una notificación de información.' });
//     }
//     if (tipo === 'warning') {
//         agregarToast({ tipo: 'warning', titulo: 'Warning', descripcion: 'Ten cuidado' });
//     }
// });

// Event listener para detectar click en los toasts
contenedorToast.addEventListener('click', (e) => {
    const toastId = e.target.closest('div.toast').id;

    if (e.target.closest('button.btn-cerrar')) {
        cerrarToast(toastId);
    }
});

// Función para cerrar el toast
function cerrarToast(id){
    document.getElementById(id)?.classList.add('cerrando');
}

// Función para agregar la clase de cerrando al toast.
function agregarToast (tipo, titulo, descripcion, autoCierre = false) {
    // Crear el nuevo toast
    const nuevoToast = document.createElement('div');

    // Agregar clases correspondientes
    nuevoToast.classList.add('toast');
    nuevoToast.classList.add(tipo);
    if (autoCierre) nuevoToast.classList.add('autoCierre');

    // Agregar id del toast
    const numeroAlAzar = Math.floor(Math.random() * 100);
    const fecha = Date.now();
    const toastId = fecha + numeroAlAzar;
    nuevoToast.id = toastId;

    // Iconos
    const iconos = {
        exito: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm10.03 4.97a.75.75 0 0 1 .011 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.75.75 0 0 1 1.08-.022z"
                    />
                </svg>`,
        error: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                    />
                </svg>`,
        info: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"
                    />
                </svg>`,
        warning: `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
                    />
                </svg>`,
    };

    // Plantilla del toast
    var toast = `
        <div class="contenido">
            <div class="icono">
                ${iconos[tipo]}
            </div>
            <div class="texto">
                <p class="titulo">${titulo}</p>
                <p class="descripcion">${descripcion}</p>
            </div>
        </div>
        <button class="btn-cerrar"  onclick="cerrarToast('${toastId}')" href="javascript:void(0)">
            <div class="icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                    <path
                        d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
                    />
                </svg>
            </div>
        </button>
    `;

    // Agregar la plantilla al nuevo toast
    nuevoToast.innerHTML = toast;

    // Agregamos el nuevo toast al contenedor
    contenedorToast.appendChild(nuevoToast);

    // Función para menajera el cierre del toast
    const handleAnimacionCierre = (e) => {
        if (e.animationName === 'cierre') {
            nuevoToast.removeEventListener('animationend', handleAnimacionCierre);
            nuevoToast.remove();
        }
    };

    if (autoCierre) {
        setTimeout(() => cerrarToast(toastId), 3000);
    }

    // Agregamos event listener para detectar cuando termine la animación
    nuevoToast.addEventListener('animationend', handleAnimacionCierre);
};

function removejscssfile(filename, filetype){
    var targetelement=(filetype=="js")? "script" : (filetype=="css")? "link" : "none" //determine element type to create nodelist from
    var targetattr=(filetype=="js")? "src" : (filetype=="css")? "href" : "none" //determine corresponding attribute to test for
    var allsuspects=document.getElementsByTagName(targetelement)
    for (var i=allsuspects.length; i>=0; i--){ //search backwards within nodelist for matching elements to remove
        if (allsuspects[i] && allsuspects[i].getAttribute(targetattr)!=null && allsuspects[i].getAttribute(targetattr).indexOf(filename)!=-1)
            allsuspects[i].parentNode.removeChild(allsuspects[i]) //remove element by calling parentNode.removeChild()
    }
}

function loadExcel(data) {
    window.open('https://'+data.url_file, "_blank");
    agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
}

$(document).on('click', '#descargarPlantilla', function () {
    
});

btnLogout.addEventListener('click', event => {

    event.preventDefault();
    $.ajax({
        url: base_web + 'logout',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.location.href = '/';
    }).fail((err) => {
    });
});

function numberWithCommas(x) {
    x = x.toString();
    var pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}