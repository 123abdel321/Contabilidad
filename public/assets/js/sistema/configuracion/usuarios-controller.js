var id_usuario_filter = null;
var usuarios_table = null;
var usuarios_empresa_table = null;
var permisosUsuarios = [];
var searchTimeoutUsuarios;
var $comboBodegaUsuario = null;
var $comboResolucionUsuario = null;
var $comboUsuarioFilterEmpresa = null;

function usuariosInit() {

    cargarCombosUsuarios();
    cargarTablasUsuarios();
    cargarChangesUsuarios();
    cargarPermisosUsuarios();

    $('.water').hide();
}

function cargarTablasUsuarios() {
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
            data: function(d) {
                d.search = $("#searchInputUsuarios").val(),
                d.id_empresa_filter = $("#id_empresa_filter_usuario").val()
            }
        },
        columns: [
            {"data":'username'},
            {"data": function (row, type, set){
                if (row.roles.length > 0) {
                    return row.roles[0].name;
                }
                return '';
            }},
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
                    if (esDios) html+= `<span id="asociarempresa_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-primary asociar-empresa-usuarios" style="margin-bottom: 0rem !important; min-width: 50px;">Empresas</span>`;
                    // if (eliminarUsuarios) html+= '<span id="deleteusuarios_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-usuarios" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    usuarios_empresa_table = $('#usuariosEmpresaTable').DataTable({
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
            url: base_url + 'generate-empresa',
            data: function(d) {
                d.id_usuario = id_usuario_filter
            }
        },
        columns: [
            {"data":'empresa.nit'},
            {"data":'empresa.razon_social'},
            {"data":'rol.name'},
            {
                "data": function (row, type, set){
                    var html = '';
                    // html+= '<span id="editusuarioempresa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-usuario-empresa" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="deleteusuarioempresa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-usuario-empresa" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    let column = usuarios_table.column(8);
    
    if (!editarUsuarios && !eliminarUsuarios) column.visible(false);
    else column.visible(true);

    if (usuarios_table) {
        usuarios_table.on('click', '.edit-usuarios', function() {
            $("#textUsuariosCreate").hide();
            $("#textUsuariosUpdate").show();
            $("#saveUsuariosLoading").hide();
            $("#updateUsuarios").show();
            $("#saveUsuarios").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, usuarios_table);
            const permisos = data.permisos ? data.permisos : [];
            const bodegas = permisos.length > 0 && permisos[0].ids_bodegas_responsable ? permisos[0].ids_bodegas_responsable.split(',') : [];
            const resoluciones = permisos.length > 0 && permisos[0].ids_resolucion_responsable ? permisos[0].ids_resolucion_responsable.split(',') : [];

            console.log(permisos);

            $('#password_usuario').val('');
            $('#password_confirm').val('');
            $("#id_usuarios_up").val(data.id);
            $("#usuario").val(data.username);
            $("#email_usuario").val(data.email);
            $("#firstname_usuario").val(data.firstname);
            $("#lastname_usuario").val(data.lastname);
            $("#address_usuario").val(data.address);
            $("#id_bodega_usuario").val(bodegas).change();
            $("#id_resolucion_usuario").val(resoluciones).change();

            clearPermisos();

            data.permissions.forEach(permiso => {
                var nombrePermisoSplit = permiso.name.split(' ');
                var nombrePermiso = nombrePermisoSplit[0]+'_'+nombrePermisoSplit[1];
                $('#permiso_'+nombrePermiso).prop('checked', true);
            });
    
            $('.permiso-item').trigger('change');
            $("#usuariosFormModal").modal('show');
        });

        usuarios_table.on('click', '.asociar-empresa-usuarios', function() {
            var trInmueble = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, usuarios_table);

            id_usuario_filter = data.id;

            $('#volverUsuarios').show();
            $('#asociarEmpresaUsuarios').show();
            $('#nombre_usuario_empresa').show();
            $('#tablas_usuarios_empresas_view').show();

            $('#reloadUsuarios').hide();
            $('#createUsuarios').hide();
            $('#tablas_usuarios_view').hide();
            $('#searchInputUsuarios').hide();

            $('#div-searchInputUsuarios').hide();
            $('#div-id_empresa_filter_usuario').hide();

            $("#nombre_usuario_empresa").html(data.nombre_completo);

            usuarios_empresa_table.ajax.reload();
        });
    }

    if (usuarios_empresa_table) {
        usuarios_empresa_table.on('click', '.drop-usuario-empresa', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, usuarios_empresa_table);

            Swal.fire({
                title: `Desasociar empresa ${data.empresa.razon_social}?`,
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Desasociar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url + 'usuario-empresa',
                        method: 'DELETE',
                        data: JSON.stringify({
                            id_usuario: data.id_usuario,
                            id_empresa: data.id_empresa,
                        }),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            usuarios_empresa_table.ajax.reload();
                            agregarToast('exito', 'Desasociación exitosa', 'Empresa desasociar con exito!', true );
                        } else {
                            agregarToast('error', 'Desasociación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Desasociación errada', res.message);
                    });
                }
            })
        });
    }

    usuarios_table.ajax.reload();
}

$(document).on('click', '#volverUsuarios', function() {
    $('#volverUsuarios').hide();
    $('#asociarEmpresaUsuarios').hide();
    $('#nombre_usuario_empresa').hide();
    $('#tablas_usuarios_empresas_view').hide();

    $('#reloadUsuarios').show();
    $('#createUsuarios').show();
    $('#tablas_usuarios_view').show();
    $('#searchInputUsuarios').show();
    $('#div-searchInputUsuarios').show();
    $('#div-id_empresa_filter_usuario').show();
});

$(document).on('click', '#asociarEmpresaUsuarios', function() {
    clearFormUsuariosEmpresa();

    $("#saveUsuarios").show();
    $("#usuariosEmpresaFormModal").modal('show');
});

function cargarCombosUsuarios() {
    $comboResolucionUsuario = $('#id_resolucion_usuario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#usuariosFormModal'),
    });

    $comboBodegaUsuario = $('#id_bodega_usuario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#usuariosFormModal'),
    });

    $comboFilterEmpresaUsuario  = $('#id_empresa_filter_usuario').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Filtrar por empresas",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            }
        },
        ajax: {
            url: 'api/empresas-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function(data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_empresa_usuario_create').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/empresas-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function(data) {
                return {
                    results: data.data
                };
            }
        }
    });
    
    if (esDios && dataEmpresa) {
        const dataComboEmpresa = {
            id: dataEmpresa.id,
            text: dataEmpresa.nit + ' - ' + dataEmpresa.razon_social
        };
        var newOption = new Option(dataComboEmpresa.text, dataComboEmpresa.id, false, false);
        $comboFilterEmpresaUsuario.append(newOption).val(dataComboEmpresa.id).trigger('change');
    }
}

function cargarChangesUsuarios() {
    $("#id_empresa_filter_usuario").on('change', function(event) {
        usuarios_table.ajax.reload();
    });

    $("#searchInputUsuarios").on("input", function() {
        clearTimeout(searchTimeoutUsuarios);
        searchTimeoutUsuarios = setTimeout(function() {
            usuarios_table.ajax.reload();
        }, 300);
    });
}

function cargarPermisosUsuarios() {
    if (componentesMenu && componentesMenu.length > 0) {
        for (let i = 0; i < componentesMenu.length; i++) {
            const componente = componentesMenu[i];
            for (let j = 0; j < componente.menus.length; j++) {
                const menu = componente.menus[j];
                if (menu.permisos.length > 0) {
                    for (let k = 0; k < menu.permisos.length; k++) {
                        const permiso = menu.permisos[k];
                        var permisoNombre = permiso.name.split(' ');
                        permisosUsuarios.push({
                            name: permisoNombre[0]+'_'+permisoNombre[1],
                            id_permiso: permiso.id,
                            value: false
                        });
                    }
                }
            }
        }
    }
}

$("#searchInputUsuarios").on("input", function (e) {
    usuarios_table.context[0].jqXHR.abort();
    $('#usuariosTable').DataTable().search($("#searchInputUsuarios").val()).draw();
});

$(document).on('click', '#reloadUsuarios', function() {
    $("#reloadUsuariosIconNormal").hide();
    $("#reloadUsuariosIconLoading").show();

    setTimeout(function(){
        $("#reloadUsuariosIconNormal").show();
        $("#reloadUsuariosIconLoading").hide();
    },1000);

    usuarios_table.ajax.reload();
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
    $("#email_usuario").val('');
    $("#firstname_usuario").val('');
    $("#lastname_usuario").val('');
    $("#address_usuario").val('');
    $("#password_usuario").val('');
    $("#id_bodega_usuario").val('').change();
    $("#id_resolucion_usuario").val('').change();
    $("#password_confirm").val('');
    $("#telefono_usuario").val('');
}

function clearFormUsuariosEmpresa() {
    $("#id_empresa_usuario_create").val('').change();
    $("#id_empresa_rol_create").val('').change();
}

function usuarioNombre(event){
    if (event.keyCode == 8) {
        return true;
    }

    patron = /[A-Za-z0-9]/;
    tecla_final = String.fromCharCode(event.keyCode);

    return patron.test(tecla_final);
}

$(document).on('click', '#saveUsuarios', function () {
    var form = document.querySelector('#usuariosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    if (!validateUserPassword()) {
        return;
    }

    $("#saveUsuariosLoading").show();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").hide();

    let data = {
        usuario: $("#usuario").val(),
        email: $("#email_usuario").val(),
        firstname: $("#firstname_usuario").val(),
        lastname: $("#lastname_usuario").val(),
        address: $("#address_usuario").val(),
        password: $("#password_usuario").val(),
        telefono: $("#telefono_usuario").val(),
        id_bodega: $("#id_bodega_usuario").val(),
        id_resolucion: $("#id_resolucion_usuario").val(),
        rol_usuario: $("#rol_usuario").val(),
        permisos: getPermisos(permisosUsuarios)
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
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Asosiación errada', errorsMsg);
    });

});

$(document).on('click', '#usuariosEmpresaCreate', function () {
    var form = document.querySelector('#usuariosEmpresaForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#usuariosEmpresaCreateLoading").show();
    $("#usuariosEmpresaCreate").hide();

    let data = {
        id_usuario: id_usuario_filter,
        id_empresa: $("#id_empresa_usuario_create").val(),
        id_rol: $("#id_empresa_rol_create").val()
    }

    $.ajax({
        url: base_url + 'usuario-empresa',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormUsuarios();
            $("#usuariosEmpresaCreate").show();
            $("#usuariosEmpresaCreateLoading").hide();

            $("#usuariosEmpresaFormModal").modal('hide');
            usuarios_empresa_table.row.add(res.data).draw();
            agregarToast('exito', 'Asosiación exitosa', 'Empresa asociada con exito!', true);
        }
    }).fail((err) => {
        $('#usuariosEmpresaCreate').show();
        $('#usuariosEmpresaCreateLoading').hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Asosiación errada', errorsMsg);
    });

});

function validateUserPassword(newPassword = true) {
    var contrasena = $("#password_usuario").val();
    var confirmarContrasena = $("#password_confirm").val();

    if (newPassword) {
        if (!contrasena || !confirmarContrasena) {
            $('#password_confirm').removeClass("is-valid");
            $('#password_confirm').addClass("is-invalid");
            $('#collapseDatosUsuario').addClass('show');
            $('#collapsePermisosUsuarios').removeClass('show');
            $('#password-error-username').text('La contraseña es obligatoria');
            return false;
        }
    }

    if (contrasena != confirmarContrasena) {
        $('#password_confirm').removeClass("is-valid");
        $('#password_confirm').addClass("is-invalid");
        $('#collapseDatosUsuario').addClass('show');
        $('#collapsePermisosUsuarios').removeClass('show');
        $('#password-error-username').text('Las contraseñas no coinciden');
        return false;
    }

    $('#password_confirm').addClass("is-valid");
    $('#password_confirm').removeClass("is-invalid");

    return true;
}

$(document).on('click', '#updateUsuarios', function () {
    var form = document.querySelector('#usuariosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    if (!validateUserPassword(false)) {
        return;
    }

    $("#saveUsuariosLoading").show();
    $("#updateUsuarios").hide();
    $("#saveUsuarios").hide();

    let data = {
        id: $("#id_usuarios_up").val(),
        usuario: $("#usuario").val(),
        email: $("#email_usuario").val(),
        firstname: $("#firstname_usuario").val(),
        lastname: $("#lastname_usuario").val(),
        address: $("#address_usuario").val(),
        password: $("#password_usuario").val(),
        id_bodega: $("#id_bodega_usuario").val(),
        id_resolucion: $("#id_resolucion_usuario").val(),
        telefono: $("#telefono_usuario").val(),
        rol_usuario: $("#rol_usuario").val(),
        permisos: getPermisos(permisosUsuarios)
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

function getPermisos() {
    for (let index = 0; index < permisosUsuarios.length; index++) {
        const permiso = permisosUsuarios[index];
        permiso.value = $("input[type='checkbox']#permiso_"+permiso.name).is(':checked') ? '1' : '';
    }

    return permisosUsuarios;
}

function clearPermisos() {
    for (let index = 0; index < permisosUsuarios.length; index++) {
        const permiso = permisosUsuarios[index];
        permiso.value = ''
    }

    permisosUsuarios.forEach(permiso => {
        $('#permiso_'+permiso.name).prop('checked', false);
    });
}

$('#usuariosFormModal').on('change', '.select-all-permisos', function() {
    const isChecked = $(this).prop('checked');
    const menuId = $(this).data('menu');
    
    // Buscar todos los permisos hijos que pertenecen a este mismo menú
    $(this).closest('.card').find('.permiso-item').prop('checked', isChecked);
});

$('#usuariosFormModal').on('change', '.permiso-item', function() {
    const card = $(this).closest('.card');
    const total = card.find('.permiso-item').length;
    const checked = card.find('.permiso-item:checked').length;
    const selectAllCheckbox = card.find('.select-all-permisos');
    
    if (checked === 0) {
        selectAllCheckbox.prop('checked', false).prop('indeterminate', false);
    } else if (checked === total) {
        selectAllCheckbox.prop('checked', true).prop('indeterminate', false);
    } else {
        selectAllCheckbox.prop('indeterminate', true); // estado intermedio visual
    }
});