var administradora_table = null;
var $comboNitAdministradoras = null;
var $comboTipoAdministradora = null;

function administradorasInit() {

    cargarTablasAdministradoras();
    loadSelect2Administradoras();
    loadSelect2TipoAdministradora();

    $('.water').hide();
}

function cargarTablasAdministradoras() {

    // Si ya existe la tabla, la destruimos para evitar duplicados
    if ($.fn.DataTable.isDataTable('#administradorasTable')) {
        $('#administradorasTable').DataTable().destroy();
    }

    administradora_table = $('#administradorasTable').DataTable({
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
        fixedColumns: {
            left: 0,
            right: 1,
        },
        ajax: {
            type: "GET",
            headers: headers,
            url: base_url + 'administradoras',
            data: function (d) {
                d.search_value = $('#searchInputAdministradoras').val();
                d.tipo = $('#filtroTipoAdministradora').val();
            }
        },
        columns: [
            {"data": function (row, type, set){
                const tipo = parseInt(row.tipo);

                if (tipo == 0) return 'EPS';
                if (tipo == 1) return 'AFP';
                if (tipo == 2) return 'ARL';
                if (tipo == 3) return 'CCF';
                return '';
            }},
            {"data":'codigo'},
            {"data": function (row, type, set){
                if (row.nit) {
                    return row.nit.numero_documento;
                }
                return "";
            }},
            {"data":'descripcion'},
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
                    if (editarAdministradoras) html += `<span id="editadministradoras_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-administradora" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    if (eliminarAdministradoras) html += `<span id="deleteadministradoras_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-administradora" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    if (administradora_table) {
        administradora_table.on('click', '.drop-administradora', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, administradora_table);

            Swal.fire({
                title: `Eliminar administradora: ${data.codigo} - ${data.descripcion}?`,
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url + 'administradoras',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            administradora_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Administradora eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });

        administradora_table.on('click', '.edit-administradora', function() {

            $("#textAdministradorasCreate").hide();
            $("#textAdministradorasUpdate").show();
            $("#saveAdministradorasLoading").hide();
            $("#updateAdministradoras").show();
            $("#saveAdministradoras").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, administradora_table);

            $("#id_administradoras_up").val(id);

            $("#codigo_administradora").val(data.codigo);
            $("#nombre_administradora").val(data.descripcion);
            $("#tipo_administradora").val(data.tipo).trigger('change');

            if(data.nit){
                var dataNit = {
                    id: data.nit.id,
                    text: data.nit.numero_documento + ' - ' + data.nit.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboNitAdministradoras.append(newOption).val(dataNit.id).trigger('change');
            }

            $("#administradorasFormModal").modal('show');
        });
    }
    administradora_table.ajax.reload();
}

function loadSelect2Administradoras() {

    // 🔥 CLAVE: destruir el select2 si ya estaba inicializado
    if ($('#id_nit_administradora').data('select2')) {
        $('#id_nit_administradora').select2('destroy');
    }

    $comboNitAdministradoras = $('#id_nit_administradora').select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/nit/combo-nit',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

function loadSelect2TipoAdministradora() {

    // 🔥 CLAVE: destruir el select2 si ya estaba inicializado
    if ($('#filtroTipoAdministradora').data('select2')) {
        $('#filtroTipoAdministradora').select2('destroy');
    }

    $comboTipoAdministradora = $('#filtroTipoAdministradora').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        placeholder: 'Tipo administradora',
        minimumResultsForSearch: Infinity,
        language: {
            noResults: function () { return "No hay resultado"; }
        }
    });

    // 🔥 CLAVE: .off() antes de .on() para no duplicar eventos
    $comboTipoAdministradora.off('change.filtro').on('change.filtro', function () {
        if (administradora_table) {
            administradora_table.ajax.reload();
        }
    });
}

// Búsqueda con debounce (se engancha UNA sola vez fuera de la función de init)
var searchTimerAdministradoras = null;
$(document).off('keyup', '#searchInputAdministradoras').on('keyup', '#searchInputAdministradoras', function () {
    clearTimeout(searchTimerAdministradoras);
    searchTimerAdministradoras = setTimeout(function () {
        if (administradora_table) {
            administradora_table.ajax.reload();
        }
    }, 400);
});

function clearFormAdministradoras(){
    $("#textAdministradorasCreate").show();
    $("#textAdministradorasUpdate").hide();
    $("#saveAdministradoras").show();
    $("#updateAdministradoras").hide();
    $("#saveAdministradorasLoading").hide();

    $("#id_administradoras_up").val('');
    $("#codigo_administradora").val('');
    $("#nombre_administradora").val('');
    $("#tipo_administradora").val('').trigger('change');
    $("#id_nit_administradora").val('').trigger('change');
}

$(document).on('click', '#createAdministradoras', function () {
    clearFormAdministradoras();
    $("#administradorasFormModal").modal('show');
});

$(document).on('click', '#saveAdministradoras', function () {
    var form = document.querySelector('#administradorasForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveAdministradoras").hide();
    $("#updateAdministradoras").hide();
    $("#saveAdministradorasLoading").show();

    let data = {
        tipo: $("#tipo_administradora").val(),
        codigo: $("#codigo_administradora").val(),
        descripcion: $("#nombre_administradora").val(),
        id_nit: $("#id_nit_administradora").val()
    }

    $.ajax({
        url: base_url + 'administradoras',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormAdministradoras();
            $("#saveAdministradoras").show();
            $("#saveAdministradorasLoading").hide();
            $("#administradorasFormModal").modal('hide');
            administradora_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Administradora creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveAdministradoras').show();
        $('#saveAdministradorasLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#updateAdministradoras', function () {
    var form = document.querySelector('#administradorasForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveAdministradoras").hide();
    $("#updateAdministradoras").hide();
    $("#saveAdministradorasLoading").show();

    let data = {
        id: $("#id_administradoras_up").val(),
        tipo: $("#tipo_administradora").val(),
        codigo: $("#codigo_administradora").val(),
        descripcion: $("#nombre_administradora").val(),
        id_nit: $("#id_nit_administradora").val()
    }

    $.ajax({
        url: base_url + 'administradoras',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormAdministradoras();
            $("#saveAdministradoras").show();
            $("#saveAdministradorasLoading").hide();
            $("#administradorasFormModal").modal('hide');
            administradora_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Administradora actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updateAdministradoras').show();
        $('#saveAdministradorasLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
});

$(document).on('click', '#sincronizarAdministradoras', function () {
    $("#sincronizarAdministradoras").hide();
    $("#sincronizarAdministradorasLoading").show();

    $.ajax({
        url: base_url + 'administradoras-sincronizar',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormAdministradoras();
            $("#sincronizarAdministradoras").show();
            $("#sincronizarAdministradorasLoading").hide();
            administradora_table.ajax.reload();
            agregarToast('exito', 'Sincronización exitosa', `Total ${res.count} administradora sincronizadas con exito!`, true);
        }
    }).fail((err) => {
        $("#sincronizarAdministradoras").show();
        $("#sincronizarAdministradorasLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
});