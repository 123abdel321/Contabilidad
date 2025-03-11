let ubicaciones_table = null;
let $comboUbicacionesTipo = null;

function ubicacionesInit() {

    $('.water').hide();

    initTablesUbicacion();
    initCombosUbicacion();
}

function initTablesUbicacion() {
    ubicaciones_table = $('#ubicacionesTable').DataTable({
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
            url: base_url + 'ubicaciones',
        },
        columns: [
            {"data":'codigo'},
            {"data":'nombre'},
            {"data": function (row, type, set){  
                return row.tipo.nombre;
            }},
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
                    if (editarUbicacion) html+= '<span id="editubicacion_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-ubicacion" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarUbicacion) html+= '<span id="deleteubicacion_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-ubicacion" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (ubicaciones_table) {

        //EDITAR PORTERIA
        ubicaciones_table.on('click', '.edit-ubicacion', function() {
            clearFormUbiaciones();

            $("#saveUbicaciones").hide();
            $("#updateUbicaciones").show();
            $("#textUbicacionesCreate").hide();
            $("#textUbicacionesUpdate").show();
            $("#saveUbicacionesLoading").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, ubicaciones_table);
            //LENAR FORMULARIO
            $("#id_ubicaciones_up").val(id);
            $("#codigo_ubicaciones").val(data.codigo);
            $("#nombre_ubicaciones").val(data.nombre);
            if(data.tipo) {
                var dataTipoUbicacion = {
                    id: data.tipo.id,
                    text: data.tipo.nombre
                };
                var newOption = new Option(dataTipoUbicacion.text, dataTipoUbicacion.id, false, false);
                $comboUbicacionesTipo.append(newOption).trigger('change');
                $comboUbicacionesTipo.val(dataTipoUbicacion.id).trigger('change');
            }

            $("#ubicacionesFormModal").modal('show');
        });
    }

    let columnAcciones = ubicaciones_table.column(5);

    if (!editarUbicacion && !eliminarUbicacion) columnAcciones.visible(false);
    else columnAcciones.visible(true);

    ubicaciones_table.ajax.reload();
}

function initCombosUbicacion() {
    $comboUbicacionesTipo = $('#id_ubicacion_tipos_ubicaciones').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#ubicacionesFormModal'),
        delay: 250,
        placeholder: "Seleccione un tipo de ubicación",
        allowClear: true,
        ajax: {
            url: 'api/ubicaciones-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                }
                return query;
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputUbicaciones").on("input", function (e) {
    ubicacion_table.context[0].jqXHR.abort();
    $('#ubicacionTable').DataTable().search($("#searchInputUbicaciones").val()).draw();
});

$(document).on('click', '#createUbicaciones', function () {
    clearFormUbiaciones();
    $("#saveUbicaciones").show();
    $("#updateUbicaciones").hide();

    $("#ubicacionesFormModal").modal('show');
});

function clearFormUbiaciones(){
    $("#textUbicacionesCreate").show();
    $("#textUbicacionesUpdate").hide();
    $("#saveUbicacionesLoading").hide();

    $("#id_ubicaciones_up").val('');
    $("#codigo_ubicaciones").val('');
    $("#nombre_ubicaciones").val('');
    $("#id_ubicacion_tipos_ubicaciones").val('').change();
}

$(document).on('click', '#saveUbicaciones', function () {

    var form = document.querySelector('#ubicacionesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveUbicacionesLoading").show();
    $("#updateUbicaciones").hide();
    $("#saveUbicaciones").hide();

    let data = {
        codigo: $("#codigo_ubicaciones").val(),
        nombre: $("#nombre_ubicaciones").val(),
        id_ubicacion_tipos: $("#id_ubicacion_tipos_ubicaciones").val(),
    }

    $.ajax({
        url: base_url + 'ubicaciones',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormUbiaciones();
            $("#saveUbicaciones").show();
            $("#saveUbicacionesLoading").hide();
            $("#ubicacionesFormModal").modal('hide');
            ubicaciones_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Ubicacion creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveUbicaciones').show();
        $('#saveUbicacionesLoading').hide();
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

$(document).on('click', '#updateUbicaciones', function () {

    var form = document.querySelector('#ubicacionesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveUbicacionesLoading").show();
    $("#updateUbicaciones").hide();
    $("#saveUbicaciones").hide();

    let data = {
        id: $("#id_ubicaciones_up").val(),
        codigo: $("#codigo_ubicaciones").val(),
        nombre: $("#nombre_ubicaciones").val(),
        id_ubicacion_tipos: $("#id_ubicacion_tipos_ubicaciones").val(),
    }

    $.ajax({
        url: base_url + 'ubicaciones',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormUbiaciones();
            $("#saveUbicaciones").show();
            $("#saveUbicacionesLoading").hide();
            $("#ubicacionesFormModal").modal('hide');
            ubicaciones_table.ajax.reload();
            agregarToast('exito', 'Creación exitosa', 'Ubicacion creada con exito!', true);
        }
    }).fail((err) => {
        $('#updateUbicaciones').show();
        $('#saveUbicacionesLoading').hide();
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
