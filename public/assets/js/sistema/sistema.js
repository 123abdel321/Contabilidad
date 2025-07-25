const host = window.location.host;

let base_url, base_web;

if (host.includes("app.portafolioerp.com")) {
    base_url = "https://app.portafolioerp.com/api/";
    base_web = "https://app.portafolioerp.com/";
} else if (host.includes("test.portafolioerp.com")) {
    base_url = "https://test.portafolioerp.com/api/";
    base_web = "https://test.portafolioerp.com/";
} else if (host.includes("localhost:8000")) {
    base_url = 'http://localhost:8000/api/';
    base_web = 'http://localhost:8000/';
}

const pusher = new Pusher('9ea234cc370d308638af', {cluster: 'us2'});
// Pusher.logToConsole = true;
const bucketUrl = 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/';
const btnLogout = document.getElementById('sessionLogout');
const itemMenuActive = localStorage.getItem("item_active_menu");

var dateNow = new Date();
const auth_token = localStorage.getItem("auth_token");
const iconNavbarSidenavMaximo = document.getElementById('iconNavbarSidenavMaximo');

$.ajaxSetup({
    'headers':{
        "Authorization": auth_token,
        "Content-Type": "application/json"
    }
});
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

iniciarCanalesDeNotificacion();

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
    'vendedores': false,
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
    'entorno': false,
    'productoprecios': false,
    'carguedescargue': false,
    'movimientoinventario': false,
    'notacredito': false,
    'ventasgenestales': false,
    'estadoactual': false,
    'estadocomprobante': false,
    'importnits': false,
    'importdocumentos': false,
    'eliminardocumentos': false,
    'recibo': false,
    'pago': false,
    'recibos': false,
    'conceptogastos': false,
    'gasto': false,
    'resumencomprobante': false,
    'presupuesto': false,
    'impuestos': false,
    'resultados': false,
    'ubicaciones': false,
    'pedido': false,
    'parqueadero': false,
    'reserva': false,
    'exogena': false,
    'resumencartera': false,
    'administradoras': false,
    'periodos': false,
    'conceptosnomina': false,
    'contratos': false,
    'configuracionprovisiones': false,
    'novedadesgenerales': false,
    'extracto': false,
    'causar': false,
    'liquidaciondefinitiva': false,
    'vacaciones': false,
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
    'vendedores': 'tablas',
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
    'entorno': 'configuracion',
    'productoprecios': 'importador',
    'movimientoinventario': 'capturas',
    'notacredito': 'capturas',
    'ventasgenerales': 'informes',
    'estadoactual': 'informes',
    'estadocomprobante': 'informes',
    'importnits': 'importador',
    'importdocumentos': 'importador',
    'eliminardocumentos': 'capturas',
    'recibo': 'capturas',
    'pago': 'capturas',
    'recibos': 'informes',
    'conceptogastos': 'tablas',
    'gasto': 'capturas',
    'resumencomprobante': 'informes',
    'presupuesto': 'tablas',
    'impuestos': 'informes',
    'resultados': 'informes',
    'ubicaciones': 'tablas',
    'pedido': 'capturas',
    'parqueadero': 'capturas',
    'reserva': 'capturas',
    'exogena': 'informes',
    'resumencartera': 'informes',
    'administradoras': 'tablas',
    'periodos': 'tablas',
    'conceptosnomina': 'tablas',
    'contratos': 'tablas',
    'configuracionprovisiones': 'tablas',
    'novedadesgenerales': 'capturas',
    'extracto': 'informes',
    'causar': 'capturas',
    'liquidaciondefinitiva': 'capturas',
    'vacaciones': 'capturas',
}

function iniciarCanalesDeNotificacion () {
    channelAbdelCastro = pusher.subscribe('canal-general-abdel-castro');
    channelFe = pusher.subscribe('notificacion-fe-'+localStorage.getItem("notificacion_code"));
}

$imagenes = [
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
];

const meses = [
    "enero", "febrero", "marzo", "abril", "mayo", "junio",
    "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
];

var urlImgFondo = $imagenes[getRandomInt($imagenes.length)];

// if (localStorage.getItem("fondo_sistema") != 'null' && localStorage.getItem("fondo_sistema") != '') {
//     urlImgFondo = bucketUrl + localStorage.getItem("fondo_sistema");
// }
setTimeout(function(){
    $(".fondo-sistema").css('background-image', 'url(' +urlImgFondo+ ')');
},300);

function getRandomInt(max) {
    return Math.floor(Math.random() * max);
}

channelAbdelCastro.bind('notificaciones', function(data) {
    let timerInterval;
    Swal.fire({
        title: "Actualizando nueva version!",
        html: "Se recargará la pagina para aplicar la version: "+version_app,
        timer: 4000,
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading();
            const timer = Swal.getPopup().querySelector("b");
            timerInterval = setInterval(() => {
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        }
        }).then((result) => {
        /* Read more about handling dismissals below */
        if (result.dismiss === Swal.DismissReason.timer) {
        }
    });
    setTimeout(function(){
        closeSessionProfile();
    },4000);
});

channelFe.bind('notificaciones', function(data) {
    agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
});

function closeSessionProfile() {
    $.ajax({
        url: base_web + 'logout-api',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        localStorage.setItem("token_db_portafolio", '');
        localStorage.setItem("auth_token", '');
        localStorage.setItem("auth_token_erp", '');
        localStorage.setItem("empresa_nombre", '');
        localStorage.setItem("notificacion_code", '');
        localStorage.setItem("notificacion_code_general", '');
        localStorage.setItem("fondo_sistema", '');
        localStorage.setItem("empresa_logo", '');

        window.location.href = '/login';
    }).fail((res) => {
        window.location.href = '/login';
    });
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
$("#titulo-empresa").text(localStorage.getItem("empresa_nombre"));
$("#titulo-empresa").text(localStorage.getItem("empresa_nombre"));
setTimeout(function(){
    $(".fondo-sistema").css('background-image', 'url(' +bucketUrl + localStorage.getItem("fondo_sistema")+ ')');
},200);

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

selectMenu(itemMenuActive);

function openNewItem(id, nombre, icon) {
    if($('#containner-'+id).length == 0) {
        generateView(id, nombre, icon);
    }
    seleccionarView(id, nombre);
    document.getElementById('sidenav-main-2').click();
}

function closeAnotherItems(id) {
    let items = document.getElementsByClassName("nav-padre");
    for (let index = 0; index < items.length; index++) {
        const element = items[index];
        if (element.id != 'nav_'+id) {
            element.classList.add("collapsed");
            document.getElementById('collapse'+element.id.split('_')[1]).classList.remove("show"); 
        }
    }
}

function closeMenu() {
    if (body.classList.contains(className)) {
        toggleSidenavMaximo();
    }
}

function generateView(id, nombre, icon){
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

    let urlFile = base_web + "assets/js/sistema/"+moduloRoute[id]+"/"+id+"-controller.js?v="+version_app;
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

$("#tab-dashboard").click(function(event){
    seleccionarView('dashboard');
});

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
    "sProcessing":     "",
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

function getIndexById(idData, tabla) {
    var data = tabla.rows().data();
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        if(element.id == idData){
            return index;
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
                $('#avatar_nit').attr('src', 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/'+data.logo_nit);
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
    var fecha = Date.now();
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
        <div class="contenido toast-general">
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
    setTimeout(function(){
        console.log('setTimeout');
        window.open('https://'+data.url_file, "_blank");
        agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
    },5000);
}

$(document).on('click', '#descargarPlantilla', function () {
    
});

btnLogout.addEventListener('click', event => {

    event.preventDefault();
    $.ajax({
        url: base_web + 'logout-api',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        localStorage.setItem("token_db_portafolio", '');
        localStorage.setItem("auth_token", '');
        localStorage.setItem("auth_token_erp", '');
        localStorage.setItem("empresa_nombre", '');
        localStorage.setItem("notificacion_code", '');
        localStorage.setItem("notificacion_code_general", '');
        localStorage.setItem("fondo_sistema", '');
        localStorage.setItem("empresa_logo", '');

        window.location.href = '/login';
    }).fail((err) => {
        window.location.href = '/login';
    });
});

function numberWithCommas(x) {
    x = x.toString();
    var pattern = /(-?\d+)(\d{3})/;
    while (pattern.test(x))
        x = x.replace(pattern, "$1,$2");
    return x;
}

function selectMenu(menu) {
    var nombre = '';
    var tipoMenu = '';

    if (menu == 'facturacion') {
        nombre = 'Facturación';
        
        tipoMenu = 2;
    } else if (menu == 'contabilidad') {
        nombre = 'Contabilidad';
        
        tipoMenu = 1;
    } else if (menu == 'nomina') {
        nombre = 'Nomina';

        tipoMenu = 3;
    }

    hideAllMenus();

    var elements = document.getElementsByClassName('tipo_menu_'+tipoMenu);

    if (elements.length) { //SHOW ELEMENTS
        for (let index = 0; index < elements.length; index++) {
            const element = elements[index];
            element.style.display = 'block';
        }
    }
    localStorage.setItem("item_active_menu", menu);
    $('#dropdownTiposMenu').text(nombre);
}

function showAllMenus() {
    var menu1 = document.getElementsByClassName('tipo_menu_1');
    if (menu1.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu1.length; index++) {
            const element = menu1[index];
            element.style.display = 'block';
        }
    }

    var menu2 = document.getElementsByClassName('tipo_menu_2');
    if (menu2.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu2.length; index++) {
            const element = menu2[index];
            element.style.display = 'block';
        }
    }

    var menu3 = document.getElementsByClassName('tipo_menu_3');
    if (menu3.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu3.length; index++) {
            const element = menu3[index];
            element.style.display = 'block';
        }
    }
}

function hideAllMenus() {
    var menu1 = document.getElementsByClassName('tipo_menu_1');
    if (menu1.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu1.length; index++) {
            const element = menu1[index];
            element.style.display = 'none';
        }
    }

    var menu2 = document.getElementsByClassName('tipo_menu_2');
    if (menu2.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu2.length; index++) {
            const element = menu2[index];
            element.style.display = 'none';
        }
    }

    var menu3 = document.getElementsByClassName('tipo_menu_3');
    if (menu3.length) { //HIDE ELEMENTS
        for (let index = 0; index < menu3.length; index++) {
            const element = menu3[index];
            element.style.display = 'none';
        }
    }
}

function formatNumber(n) {
    // format number 1000000 to 1,234,567
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",")
} 

function formatCurrencyValue (value) {
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
            return formatNumber(value)+".00";
        }

    } else {
        return '0.00';
    }
}
 
function formatCurrency(input, blur) {
    // appends $ to value, validates decimal side
    // and puts cursor back in right position.
    
    // get input value
    var input_val = input.val();
    input_val = input_val.replace(',', '');
    // don't validate empty input
    if (input_val === "") { return; }
    
    // original length
    var original_len = input_val.length;
  
    // initial caret position 
    var caret_pos = input.prop("selectionStart");
      
    // check for decimal
    if (input_val.indexOf(".") >= 0) {
        // get position of first decimal
        // this prevents multiple decimals from
        // being entered
        var decimal_pos = input_val.indexOf(".");
        // split number by decimal point
        var left_side = input_val.substring(0, decimal_pos);
        var right_side = input_val.substring(decimal_pos);
        // add commas to left side of number
        left_side = formatNumber(left_side);
        // validate right side
        right_side = formatNumber(right_side);
        // On blur make sure 2 numbers after decimal
        if (blur === "blur") {
            right_side += "00";
        }
        // Limit decimal to only 2 digits
        right_side = right_side.substring(0, 2);

        input_val = left_side + "." + right_side;
    } else {
        input_val = formatNumber(input_val);
        if (blur === "blur") {
            input_val += ".00";
        }
        input_val = input_val;
    }
    
    // send updated string to input
    input.val(input_val);
    
    // put caret back in the right position
    var updated_len = input_val.length;
    caret_pos = updated_len - original_len + caret_pos;
    input[0].setSelectionRange(caret_pos, caret_pos);
}

function stringToNumberFloat (value) {
    value = value+'';
    if (value) value = parseFloat(parseFloat(value.replaceAll(',', '')).toFixed(2));
    return value ? value : 0;
}

function arreglarMensajeError(mensaje) {
    var errorsMsg = '';
    if (typeof mensaje === 'object') {
        for (field in mensaje) {
            var errores = mensaje[field];
            for (campo in errores) {
                errorsMsg += field+": "+errores[campo]+" <br>";
            }
        };
    }
    else if (typeof mensaje === 'string') {
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
    if (!valor) return valor; //Sin valor a redondear
    if (redondeo_valor === null) return valor; // No redondear
    if (redondeo_valor === 0) return Math.floor(valor); // Quitar decimales (redondear hacia abajo)
    return Math.round(valor / redondeo_valor) * redondeo_valor; // Redondear al múltiplo más cercano
}

function primeraFormaPago(formasPagos, nombreInput) {
    for (let index = 0; index < formasPagos.length; index++) {

        const formaPago = formasPagos[index];
        const input = document.getElementById(`${nombreInput}_${formaPago.id}`);

        if (input.disabled) {
            continue;
        }

        return formaPago.id
    }
}

function getResponsabilidades(id_responsabilidades) {
    if (id_responsabilidades) {
        return id_responsabilidades.split(',');
    }
    return [];
}

function normalizarFecha(fecha) {
    // Si ya tiene hora (es decir, espacio y algo más), no tocamos nada
    if (fecha.includes(' ')) {
        return fecha;
    }

    // Si no tiene hora, le agregamos la hora actual
    const ahora = new Date();
    const hora = ahora.getHours().toString().padStart(2, '0');
    const minutos = ahora.getMinutes().toString().padStart(2, '0');
    const segundos = ahora.getSeconds().toString().padStart(2, '0');

    return `${fecha} ${hora}:${minutos}:${segundos}`;
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
    // Verificar si las horas no han sido modificadas manualmente (00:00:00 y 23:59:59)
    var isStartTimeDefault = start.hours() === 0 && start.minutes() === 0 && start.seconds() === 0;
    var isEndTimeDefault = end.hours() === 23 && end.minutes() === 59 && end.seconds() === 59;
    
    // Si es una selección nueva (no edición manual de horas), ajustamos las horas
    if (isStartTimeDefault && isEndTimeDefault) {
        start.startOf('day');    // 00:00:00
        end.endOf('day');        // 23:59:59
    }
    
    // Formatear y mostrar las fechas
    $("#"+input).html(start.format("MMMM D, YYYY HH:mm:ss") + " - " + end.format("MMMM D, YYYY HH:mm:ss"));
    
    // Aquí puedes agregar cualquier otra lógica que necesites con las fechas
    // Por ejemplo, actualizar campos ocultos o hacer una llamada AJAX
}

function parseManualInput(value, input) {
    var parts = value.split(' - ');
    if (parts.length === 2) {
        var start = moment(parts[0], "YYYY-MM-DD HH:mm:ss");
        var end = moment(parts[1], "YYYY-MM-DD HH:mm:ss");
        
        if (start.isValid() && end.isValid()) {
            // Si no se especificó hora, establecer valores por defecto
            if (parts[0].length <= 10) start.startOf('day');
            if (parts[1].length <= 10) end.endOf('day');
            
            // Actualizar el datepicker
            $("#"+input).data('daterangepicker').setStartDate(start);
            $("#"+input).data('daterangepicker').setEndDate(end);
            
            // Llamar a formatoFecha con los nuevos valores
            formatoFecha(start, end, input);
        }
    }
}

function normalizarFecha(fecha) {
    // Si la fecha no tiene parte de hora, agregamos 'T00:00'
    if (fecha && fecha.length === 10) {
        return fecha + 'T00:00';
    }
    // Si ya tiene hora, la dejamos tal cual
    return fecha;
}

function formatNumberWithSmallDecimals(number) {
    const formatted = new Intl.NumberFormat('ja-JP').format(number);
    const parts = formatted.split('.');
    if (parts.length > 1) {
        return `<span class="integer-part">${parts[0]}</span><span class="decimal-part">.${parts[1]}</span>`;
    }
    return formatted;
}

$(document).on('shown.bs.popover', function() {
    $('.popover b.titulo-popover').css({
        'color': '#72ffff',
        'font-weight': 'bold'
    });

    $('.popover b.titulo-popover-error').css({
        'color': 'red',
        'font-weight': 'bold'
    });

    $('.popover b.mensaje-blanco').css({
        'color': '#FFF',
        'font-weight': 'bold'
    });
});