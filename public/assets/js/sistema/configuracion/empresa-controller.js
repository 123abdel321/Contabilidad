var fondoSistema = null;
var empresas_table = null;

function empresaInit() {

    empresas_table = $('#empresasTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'empresas',
        },
        columns: [
            {"data": 'id',
            render: function (row, type, data){
                var urlImg = `logos_empresas/no-photo.jpg`;
                var nameImg = 'none-img'
                if (data.logo) {
                    urlImg = data.logo;
                    nameImg = data.logo;
                }
                return `<img
                    style="height: 40px; border-radius: 10%; cursor: pointer;"
                    onclick="mostrarEventoPorteria(${data.id})"
                    src="${bucketUrl}${urlImg}"
                    alt="${nameImg}"
                />`;
            }, className: 'dt-body-center'},
            {"data":'razon_social'},
            {"data":'nit'},
            {"data":'dv'},
            {"data":'telefono'},
            {"data":'direccion'},
            {"data": function (row, type, set){  
                if (row.usuario) {
                    return row.usuario.firstname;
                }
                return '';
            }},
            // {"data":'primer_nombre'},
            // {"data":'otros_nombres'},
            // {"data":'primer_apellido'},
            // {"data":'segundo_apellido'},
            {
                "data": function (row, type, set){
                    var html = '<span id="editempresa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-empresa" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="selectempresa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info select-empresa" style="margin-bottom: 0rem !important; min-width: 50px;">Seleccionar</span>&nbsp;';
                    return html;
                }
            },
        ]
    });

    if (empresas_table) {
        empresas_table.on('click', '.select-empresa', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, empresas_table);

            seleccionarEmpresa(data.hash);
        });
    }

    empresas_table.ajax.reload();

    // $('#id_responsabilidades').select2({
    //     theme: 'bootstrap-5',
    // });
    
    // $("#id_responsabilidades").val(
    //     datosEmpresa.codigos_responsabilidades.split(',')
    // ).change();
}

$(document).on('click', '#generateNuevaEmpresa', function () {
    $("#form-empresa-rut").show();
    $("#form-empresa-create").hide();
    $("#empresaFormModal").modal('show');
});

$(document).on('click', '#omitirEmpresa', function () {
    $("#form-empresa-rut").hide();
    $("#form-empresa-create").show();
});

$(document).on('change', '#file_rut_empresa', function () {
    
    $("#omitirEmpresa").hide();
    $("#omitirEmpresaLoading").show();

    var ajxForm = document.getElementById("form-empresa-rut");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "loadrut");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);

        
        dataNit = responseData.data;

        if (dataNit.razon_social && dataNit.razon_social != " ") $("#razon_social_empresa_nueva").val(dataNit.razon_social);
        if (dataNit.nombre_completo && dataNit.nombre_completo != " ") $("#nombre_completo_empresa_nueva").val(dataNit.nombre_completo);
        if (dataNit.dv) $("#dv_empresa_nueva").val(dataNit.dv);
        if (dataNit.nit) $("#nit_empresa_nueva").val(dataNit.nit);
        if (dataNit.email) $("#email_empresa_nueva").val(dataNit.email);
        if (dataNit.telefono) $("#telefono_empresa_nueva").val(dataNit.telefono);
        if (dataNit.direccion) $("#direccion_empresa_nueva").val(dataNit.direccion);

        $("#form-empresa-rut").hide();
        $("#form-empresa-create").show();
        $('#omitirEmpresa').show();
        $('#omitirEmpresaLoading').hide();
    };
    xhr.onerror = function (res) {
        var res = JSON.parse(res.currentTarget.response);

        agregarToast('error', 'Carga errada', res.message);
    };
});

$("#tipo_contribuyente_empresa").on('change', function(e) {
    var tipoContribuyente = $("#tipo_contribuyente_empresa").val();

    if (tipoContribuyente == 1) {
        $("#primer_nombre_empresa").prop('required',false);
        $("#otros_nombres_empresa").prop('required',false);
        $("#primer_apellido_empresa").prop('required',false);
        $("#segundo_apellido_empresa").prop('required',false);
        $("#razon_social_empresa").prop('required',true);
    } else {
        $("#primer_nombre_empresa").prop('required',true);
        $("#otros_nombres_empresa").prop('required',false);
        $("#primer_apellido_empresa").prop('required',true);
        $("#segundo_apellido_empresa").prop('required',false);
        $("#razon_social_empresa").prop('required',false);
    }

    $("#numero_documento").prop('required',false);
    $("#direccion_empresa").prop('required',false);
    $("#telefono_empresa").prop('required',false);

    var form = document.querySelector('#empresaForm');

    form.checkValidity();
});

$("#form-empresa-create").submit(function(e) {
    e.preventDefault();

    var form = document.querySelector('#form-empresa-create');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $('#saveEmpresa').hide();
    $('#saveEmpresaLoading').show();

    var ajxForm = document.getElementById("form-empresa-create");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "instalacionempresa");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);

        $('#saveEmpresa').show();
        $('#saveEmpresaLoading').hide();

        agregarToast('exito', 'Instalacion completada', 'Instalacion completada con exito!');
        
        $("#empresaFormModal").modal('hide');

        empresas_table.ajax.reload();
    };
    xhr.onerror = function (res) {
        var responseData = JSON.parse(res.currentTarget.response);
        agregarToast('error', 'Carga errada', responseData.message);
    };
});

$(document).on('click', '#updateEmpresa', function () {

    var form = document.querySelector('#empresaForm');

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $("#updateEmpresaLoading").show();
    $("#updateEmpresa").hide();

    let data = {
        nit: $('#nit_empresa').val(),
        dv: $('#dv_empresa').val(),
        direccion: $('#direccion_empresa').val(),
        telefono: $('#telefono_empresa').val(),
        tipo_contribuyente: $('#tipo_contribuyente_empresa').val(),
        razon_social: $('#razon_social_empresa').val(),
        primer_nombre: $('#primer_nombre_empresa').val(),
        otros_nombres: $('#otros_nombres_empresa').val(),
        primer_apellido: $('#primer_apellido_empresa').val(),
        segundo_apellido: $('#segundo_apellido_empresa').val(),
        fecha_ultimo_cierre: $('#fecha_ultimo_cierre').val(),
        id_responsabilidades: $('#id_responsabilidades').val(),
        fondo_imagen : fondoSistema,
    }

    $.ajax({
        url: base_url + 'empresa',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            localStorage.setItem("fondo_sistema", res.fondo_sistema);
            $("#updateEmpresaLoading").hide();
            $("#updateEmpresa").show();

            setTimeout(function(){
                $(".fondo-sistema").css('background-image', 'url(' +bucketUrl + res.fondo_sistema+ ')');
            },300);
            agregarToast('exito', 'Actualización exitosa', 'Datos de empresa actualizados con exito!', true);
        }
    }).fail((err) => {
        $("#updateEmpresaLoading").hide();
            $("#updateEmpresa").show();
        var errorsMsg = "";
        var mensaje = err.responseJSON.message;
        if(typeof mensaje  === 'object' || Array.isArray(mensaje)){
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += "- "+errores[campo]+" <br>";
                }
            };
        } else {
            errorsMsg = mensaje
        }
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
    
});

function seleccionarEmpresa(hash) {

    $.ajax({
        url: base_url + 'seleccionar-empresa',
        method: 'POST',
        data: JSON.stringify({empresa: hash}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            localStorage.setItem("empresa_nombre", res.empresa.razon_social);
            localStorage.setItem("empresa_logo", res.empresa.logo);
            localStorage.setItem("notificacion_code", res.notificacion_code);
            location.reload();
        }
    }).fail((err) => {
        $("#updateEmpresaLoading").hide();
        $("#updateEmpresa").show();
        var errorsMsg = "";
        var mensaje = err.responseJSON.message;
        if(typeof mensaje  === 'object' || Array.isArray(mensaje)){
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += "- "+errores[campo]+" <br>";
                }
            };
        } else {
            errorsMsg = mensaje
        }
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
}

function readURLEmpresaNueva(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_empresa_nueva').attr('src', e.target.result);
            $('#new_avatar_empresa').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_empresa').hide();
        $('#new_avatar_empresa').show();
    }
}

function readURLEmpresaEdit(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#imagen_empresa_edit').attr('src', e.target.result);
            $('#new_avatar_empresa_edit').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar_empresa_edit').hide();
        $('#new_avatar_empresa_edit').show();
    }
}

function readURLFonsoSistema(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            fondoSistema = e.target.result;
            $('#empresa_fondo_sistema').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_fondo_sistema').hide();
        $('#empresa_fondo_sistema').show();
    }
}