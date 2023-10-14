var usuarios_table = null;
var $comboBodegaUsuario = null;
var $comboResolucionUsuario = null;

function usuariosInit() {

    usuarios_table =  $('#usuariosTable').DataTable({
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
            url: base_url + 'usuarios',
        },
        columns: [
            {"data":'username'},
            {"data":'firstname'},
            {"data":'email'},
            {"data":'telefono'},
            {"data":'address'},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="editusuarios_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-usuarios" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    // html+= '<span id="deleteusuarios_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-usuarios" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (usuarios_table) {
        usuarios_table.on('click', '.edit-usuarios', function() {
            $("#textUsuariosCreate").hide();
            $("#textUsuariosUpdate").show();
            $("#saveUsuariosLoading").hide();
            $("#updateUsuarios").show();
            $("#saveUsuarios").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, usuarios_table);

            console.log(data.ids_bodegas_responsable);
            console.log(data.ids_bodegas_responsable.split(','));
    
            $("#id_usuarios_up").val(data.id);
            $("#usuario").val(data.username);
            $("#email").val(data.email);
            $("#firstname").val(data.firstname);
            $("#lastname").val(data.lastname);
            $("#address").val(data.address);
            $("#id_bodega_usuario").val(data.ids_bodegas_responsable.split(',')).change();
            $("#id_resolucion_usuario").val(data.ids_resolucion_responsable.split(',')).change();
    
            $("#usuariosFormModal").modal('show');
        });
    }

    $comboResolucionUsuario = $('#id_resolucion_usuario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#usuariosFormModal'),
    });

    $comboBodegaUsuario = $('#id_bodega_usuario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#usuariosFormModal'),
    });

    $('.water').hide();
    usuarios_table.ajax.reload();
}

$("#searchInputUsuarios").on("input", function (e) {
    usuarios_table.context[0].jqXHR.abort();
    $('#usuariosTable').DataTable().search($("#searchInputUsuarios").val()).draw();
});

$(document).on('click', '#createUsuarios', function () {
    clearFormUsuarios();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").show();
    $("#usuariosFormModal").modal('show');
});

function clearFormUsuarios(){
    $("#textUsuariosCreate").show();
    $("#textUsuariosUpdate").hide();
    $("#saveUsuariosLoading").hide();

    $("#id_usuarios_up").val('');
    $("#usuario").val('');
    $("#email").val('');
    $("#firstname").val('');
    $("#lastname").val('');
    $("#address").val('');
    $("#password").val('');
    $("#id_bodega_usuario").val('').change();
    $("#id_resolucion_usuario").val('').change();
    $("#password_confirm").val('');
}

$(document).on('click', '#saveUsuarios', function () {
    var form = document.querySelector('#usuariosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveUsuariosLoading").show();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").hide();

    let data = {
        usuario: $("#usuario").val(),
        email: $("#email").val(),
        firstname: $("#firstname").val(),
        lastname: $("#lastname").val(),
        address: $("#address").val(),
        password: $("#password").val(),
        id_bodega: $("#id_bodega_usuario").val(),
        id_resolucion: $("#id_resolucion_usuario").val(),
        facturacion_rapida: $("input[type='checkbox']#facturacion_rapida").is(':checked') ? '1' : ''
    }

    $.ajax({
        url: base_url + 'usuarios',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormUsuarios();
            $("#saveUsuarios").show();
            $("#updateUsuarios").hide();
            $("#saveUsuariosLoading").hide();
            $("#usuariosFormModal").modal('hide');
            usuarios_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Usuario creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveUsuarios').show();
        $('#saveUsuariosLoading').hide();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });

});

$(document).on('click', '#updateUsuarios', function () {
    var form = document.querySelector('#usuariosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveUsuariosLoading").show();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").hide();

    let data = {
        id: $("#id_usuarios_up").val(),
        usuario: $("#usuario").val(),
        email: $("#email").val(),
        firstname: $("#firstname").val(),
        lastname: $("#lastname").val(),
        address: $("#address").val(),
        password: $("#password").val(),
        id_bodega: $("#id_bodega_usuario").val(),
        id_resolucion: $("#id_resolucion_usuario").val(),
        facturacion_rapida: $("input[type='checkbox']#facturacion_rapida").is(':checked') ? '1' : ''
    }

    $.ajax({
        url: base_url + 'usuarios',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormUsuarios();
            $("#saveUsuarios").show();
            $("#updateUsuarios").hide();
            $("#saveUsuariosLoading").hide();
            $("#usuariosFormModal").modal('hide');
            usuarios_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Usuario creado con exito!', true);
        }
    }).fail((err) => {
        $('#updateUsuarios').show();
        $('#saveUsuariosLoading').hide();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });

});