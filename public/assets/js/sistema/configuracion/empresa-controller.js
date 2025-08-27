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
            data: function ( d ) {
                d.search = $("#searchInputEmpresa").val()
            }
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
                    var html = ``;
                    html += `
                        <span
                            id="editempresa_${row.id}"
                            href="javascript:void(0)"
                            class="edit-empresa btn badge bg-gradient-success btn-bg-excel"
                            style="margin: 0px; !important; min-width: 50px; margin-bottom: 0px !important;"
                        >
                            <i class="fa-solid fa-file-pen" style="font-size: 12px;"></i>&nbsp;
                            <b style="font-size: 11px;">Editar</b>
                        </span>
                    `;
                    html += `
                        <span
                            id="selectempresa_${row.id}"
                            href="javascript:void(0)"
                            class="select-empresa btn badge bg-gradient-info btn-bg-gold"
                            style="margin: 0px; !important; min-width: 50px; margin-bottom: 0px !important;"
                        >
                            <i class="fa-solid fa-plug" style="font-size: 12px;"></i>&nbsp;
                            <b style="font-size: 11px;">Seleccionar</b>
                        </span>
                        <span
                            id="selectingempresa_${row.id}"
                            class="badge bg-gradient-info btn-bg-gold-loading"
                            style="margin: 0px; !important; min-width: 50px; margin-bottom: 0px !important; display: none;"
                        >
                            <i class="fas fa-spinner fa-spin" style="font-size: 12px;" aria-hidden="true"></i>
                            <b style="text-transform: math-auto;">Seleccionando</b>
                        </span>
                    `;
                    return html;
                }
            },
        ]
    });

    if (empresas_table) {

        empresas_table.on('click', '.edit-empresa', function() {

            var id = this.id.split('_')[1];
            var data = getDataById(id, empresas_table);

            clearFormularioEditEmpresa();

            if (data.logo) {
                $('#new_avatar_empresa_edit').attr('src', bucketUrl + data.logo);
                $('#default_avatar_empresa_edit').hide();
                $('#new_avatar_empresa_edit').show();
            } else {
                $('#default_avatar_empresa_edit').show();
                $('#new_avatar_empresa_edit').hide();
            }
            
            $("#id_empresa_up").val(data.id);
            $("#razon_social_empresa_edit").val(data.razon_social);
            $("#nit_empresa_edit").val(data.nit);
            $("#dv_empresa_edit").val(data.dv);
            $("#email_empresa_edit").val(data.email);
            $("#telefono_empresa_edit").val(data.telefono);
            $("#direccion_empresa_edit").val(data.direccion);
            $("#correo_empresa_edit").val(data.correo);
            $("#empresaEditFormModal").modal('show');
        });

        empresas_table.on('click', '.select-empresa', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, empresas_table);

            seleccionarEmpresa(data.hash, id);
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

    clearFormularioEmpresa();
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

$("#form-empresa-update").submit(function(e) {
    e.preventDefault();

    var form = document.querySelector('#form-empresa-update');

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $('#updateEmpresa').hide();
    $('#updateEmpresaLoading').show();

    var ajxForm = document.getElementById("form-empresa-update");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "actualizarempresa");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);

        $('#updateEmpresa').show();
        $('#updateEmpresaLoading').hide();

        agregarToast('exito', 'Actualización completada', 'Actualización completada con exito!');
        
        $("#empresaEditFormModal").modal('hide');

        empresas_table.ajax.reload();
    };
    xhr.onerror = function (res) {
        var responseData = JSON.parse(res.currentTarget.response);
        agregarToast('error', 'Carga errada', responseData.message);
    };
});

function seleccionarEmpresa(hash, id) {

    $("#selectempresa_" + id).hide();
    $("#selectingempresa_" + id).show();

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

        $("#selectempresa_" + id).show();
        $("#selectingempresa_" + id).hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Selección de empresa errada', errorsMsg);
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

function clearFormularioEmpresa() {
    $("#razon_social_empresa_nueva").val("");
    $("#nit_empresa_nueva").val("");
    $("#email_empresa_nueva").val("");
    $("#telefono_empresa_nueva").val("");
    $("#direccion_empresa_nueva").val("");

    $('#new_avatar_empresa_nueva').hide();
    $('#default_avatar_empresa_nueva').show();
}

function clearFormularioEditEmpresa() {
    $("#id_empresa_up").val("");
    $("#razon_social_empresa_edit").val("");
    $("#nit_empresa_edit").val("");
    $("#telefono_empresa_edit").val("");
    $("#direccion_empresa_edit").val("");
    $("#correo_empresa_edit").val("");

    $('#new_avatar_empresa_edit').hide();
    $('#default_avatar_empresa_edit').show();
}

function searchEmpresas (event) {
    if (event.keyCode == 20 || event.keyCode == 16 || event.keyCode == 17 || event.keyCode == 18) {
        return;
    }

    empresas_table.context[0].jqXHR.abort();
    empresas_table.ajax.reload();
}
