var bodegas_table = null;
var $comboCuentaCartera = null;
var $comboBodegaCecos = null;

function bodegasInit() {
    bodegas_table = $('#bodegasTable').DataTable({
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
            url: base_url + 'bodega',
        },
        columns: [
            {"data":'codigo'},
            {"data":'nombre'},
            {"data":'ubicacion'},
            {
                "data": function (row, type, set){
                    if(row.id_centro_costos){
                        return row.cecos.codigo + ' - ' + row.cecos.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.cuenta_cartera){
                        return row.cuenta_cartera.cuenta + ' - ' + row.cuenta_cartera.nombre;
                    }
                    return '';
                }
            },
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
                    html+= '<span id="editbodegas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-bodegas" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="deletebodegas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-bodegas" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
    
        ]
    });

    let column = bodegas_table.column(6);
    
    if (!editarBodegas && !eliminarBodegas) column.visible(false);
    else column.visible(true);

    $comboBodegaCecos = $('#id_centro_costos_bodega').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#bodegasFormModal'),
        delay: 250,
        placeholder: "Seleccione un centro de costos",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/centro-costos/combo-centro-costo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
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

    $comboCuentaCartera = $('#id_cuenta_cartera_bodega').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#bodegasFormModal'),
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [2]
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

    if(bodegas_table) {
        //EDITAR BODEGAS
        bodegas_table.on('click', '.edit-bodegas', function() {

            clearFormBodegas();
            $("#textBodegasCreate").hide();
            $("#textBodegasUpdate").show();
            $("#saveBodegasLoading").hide();
            $("#updateBodegas").show();
            $("#saveBodegas").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, bodegas_table);

            if(data.cecos){
                var dataCecos = {
                    id: data.cecos.id,
                    text: data.cecos.codigo + ' - ' + data.cecos.nombre
                };
                var newOption = new Option(dataCecos.text, dataCecos.id, false, false);
                $comboBodegaCecos.append(newOption).trigger('change');
                $comboBodegaCecos.val(dataCecos.id).trigger('change');
            }

            if(data.cuenta_cartera){
                var dataCuenta = {
                    id: data.cuenta_cartera.id,
                    text: data.cuenta_cartera.cuenta + ' - ' + data.cuenta_cartera.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCartera.append(newOption).trigger('change');
                $comboCuentaCartera.val(dataCuenta.id).trigger('change');
            }

            $("#consecutivo_bodegas").val(data.consecutivo);
            $("#ubicacion_bodega").val(data.ubicacion);
            $("#codigo_bodega").val(data.codigo);
            $("#nombre_bodega").val(data.nombre);
            $("#id_bodega").val(data.id);

            $("#bodegasFormModal").modal('show');

        });

        bodegas_table.on('dblclick', 'tr', function () {
            var data = bodegas_table.row(this).data();
            if (data) {
                document.getElementById("editbodegas_"+data.id).click();
            }
        });

        //BORRAR BODEGAS
        bodegas_table.on('click', '.drop-bodegas', function() {

            var trBodega = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, bodegas_table);

            Swal.fire({
                title: 'Eliminar bodega: '+data.nombre+'?',
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
                        url: base_url + 'bodega',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            bodegas_table.row(trBodega).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Bodega eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
    }

    $('.water').hide();
    bodegas_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInput").on("input", function (e) {
    bodegas_table.context[0].jqXHR.abort();
    $('#cecosTable').DataTable().search($("#searchInput").val()).draw();
});

$(document).on('click', '#createBodega', function () {
    clearFormBodegas();
    $("#updateBodegas").hide();
    $("#saveBodegas").show();
    $("#bodegasFormModal").modal('show');
});

function clearFormBodegas(){
    $("#textBodegasCreate").show();
    $("#textBodegasUpdate").hide();
    $("#saveBodegasLoading").hide();

    $("#id_bodega").val('');
    $("#nombre_bodega").val('');
    $("#codigo_bodega").val('');
    $("#id_centro_costos_bodega").val('');
    $("#id_cuenta_cartera_bodega").val('');
}

$(document).on('click', '#saveBodegas', function () {
    var form = document.querySelector('#bodegasForm');

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $("#saveBodegasLoading").show();
    $("#updateBodegas").hide();
    $("#saveBodegas").hide();

    let data = {
        codigo: $('#codigo_bodega').val(),
        nombre: $('#nombre_bodega').val(),
        ubicacion: $('#ubicacion_bodega').val(),
        consecutivo: $('#consecutivo_bodegas').val(),
        id_centro_costos: $('#id_centro_costos_bodega').val(),
        id_cuenta_cartera: $("#id_cuenta_cartera_bodega").val(),
    };

    $.ajax({
        url: base_url + 'bodega',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormBodegas();
            $("#saveBodegas").show();
            $("#updateBodegas").hide();
            $("#saveBodegasLoading").hide();
            $("#bodegasFormModal").modal('hide');
            bodegas_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Bodega creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveBodegas').show();
        $('#saveBodegasLoading').hide();
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

$(document).on('click', '#updateBodegas', function () {
    var form = document.querySelector('#bodegasForm');

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id: $("#id_bodega").val(),
        codigo: $('#codigo_bodega').val(),
        nombre: $('#nombre_bodega').val(),
        ubicacion: $('#ubicacion_bodega').val(),
        consecutivo: $('#consecutivo_bodegas').val(),
        id_centro_costos: $('#id_centro_costos_bodega').val(),
        id_cuenta_cartera: $("#id_cuenta_cartera_bodega").val(),
    };

    $.ajax({
        url: base_url + 'bodega',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormBodegas();
            $("#saveBodegas").show();
            $("#updateBodegas").hide();
            $("#saveBodegasLoading").hide();
            $("#bodegasFormModal").modal('hide');
            bodegas_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Bodega actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updateBodegas').show();
        $('#saveBodegasLoading').hide();
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
        agregarToast('error', 'Error al actualizar bodega', errorsMsg);
    });
});

function clearFormBodegas(){
    $("#textBodegasCreate").show();
    $("#textBodegasUpdate").hide();
    $("#saveBodegasLoading").hide();

    $("#id_bodega").val('');
    $("#codigo_bodega").val('');
    $("#nombre_bodega").val('');
    $("#ubicacion_bodega").val('');
    $("#consecutivo_bodegas").val('');

    $comboBodegaCecos.val('').trigger('change');
}