var newImgProfile = '';
var nits_table = null;
var $comboCiudad = null;
var $comboTipoDocumento = null;

function nitInit() {
    nits_table = $('#nitTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        bFilter: true,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scrollX: true,
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'nit',
        },
        columns: [
            {"data":'numero_documento', visible: false},
            {
                "data": function (row, type, set){
                    var texto = row.numero_documento + ' - ';
                    if (row.razon_social) {
                        return texto + row.razon_social
                    }
                    var primer_nombre = row.primer_nombre ? row.primer_nombre+' ' : '';
                    var otros_nombres = row.otros_nombres ? row.otros_nombres+' ' : '';
                    var primer_apellido = row.primer_apellido ? row.primer_apellido+' ' : '';
                    var segundo_apellido = row.segundo_apellido ? row.segundo_apellido+' ' : '';
                    
                    return texto + primer_nombre+otros_nombres+primer_apellido+segundo_apellido;
                }
            },
            {"data":'razon_social', visible: false},
            {"data":'direccion'},
            {"data":'email'},
            {"data":'telefono_1'},
            {
                "data": function (row, type, set){
                    if(row.ciudad){
                        return row.ciudad.nombre_completo;
                    }
                    return '';
                }
            },
            {"data":'observaciones'},
            {
                "data": function (row, type, set){
                    if(row.tipo_documento){
                        return row.tipo_documento.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.tipo_contribuyente == 1){
                        return 'Persona jurídica';
                    } else if(row.tipo_contribuyente == 2) {
                        return 'Persona natural';
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
                    html+= '<span id="editplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-nits" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                    html+= '<span id="deleteplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-nits" style="margin-bottom: 0rem !important">Eliminar</span>';
                    return html;
                }
            }
        ]
    });

    if(nits_table){
        nits_table.on('click', '.edit-nits', function() {
            newImgProfile = '';
            $("#textNitCreate").hide();
            $("#textNitUpdate").show();
            $("#saveNitLoading").hide();
            $("#updateNit").show();
            $("#saveNit").hide();
        
            var trNit = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, nits_table);
        
            if(data.tipo_documento){
                var dataCuenta = {
                    id: data.tipo_documento.id,
                    text: data.tipo_documento.codigo + ' - ' + data.tipo_documento.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboTipoDocumento.append(newOption).trigger('change');
                $comboTipoDocumento.val(dataCuenta.id).trigger('change');
            }
        
        
            if(data.ciudad){
                var dataCiudad = {
                    id: data.ciudad.id,
                    text: data.ciudad.nombre_completo
                };
                var newOption = new Option(dataCiudad.text, dataCiudad.id, false, false);
                $comboCiudad.append(newOption).trigger('change');
                $comboCiudad.val(dataCiudad.id).trigger('change');
            }
        
            $("#id_nit_up").val(data.id);
            $("#numero_documento").val(data.numero_documento);
            $("#tipo_contribuyente").val(data.tipo_contribuyente).change();
            $("#primer_apellido").val(data.primer_apellido);
            $("#segundo_apellido").val(data.segundo_apellido);
            $("#primer_nombre").val(data.primer_nombre);
            $("#otros_nombres").val(data.otros_nombres);
            $("#razon_social").val(data.razon_social);
            $("#direccion").val(data.direccion);
            $("#email").val(data.email);
            $("#telefono_1").val(data.telefono_1);
            $("#observaciones").val(data.observaciones);
            
            if(data.logo_nit) {
                $('#new_avatar').attr('src', 'https://bucketlistardatos.nyc3.digitaloceanspaces.com/'+data.logo_nit);
                $('#new_avatar').show();
                $('#default_avatar').hide();
            } else {
                $('#new_avatar').attr('src', '');
                $('#new_avatar').hide();
                $('#default_avatar').show();
            }
        
            $("#nitFormModal").modal('show');
        });
        
        nits_table.on('click', '.drop-nits', function() {
            var trNit = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, nits_table);
            Swal.fire({
                title: 'Eliminar documento: '+data.numero_documento+'?',
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
                        url: base_url + 'nit',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            nits_table.row(trNit).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Cedula nit eliminado con exito!', true );
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

    $comboCiudad = $('#id_ciudad').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitFormModal'),
        delay: 250,
        ajax: {
            url: 'api/ciudades',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
    
    $comboTipoDocumento = $('#id_tipo_documento').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitFormModal'),
        delay: 250,
        ajax: {
            url: 'api/nit/combo-tipo-documento',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('.water').hide();
    nits_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            newImgProfile = e.target.result;
            $('#new_avatar').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_avatar').hide();
        $('#new_avatar').show();
    }
}

$("#searchInputNits").on("input", function (e) {
    nits_table.context[0].jqXHR.abort();
    $('#nitTable').DataTable().search($("#searchInputNits").val()).draw();
});

$(document).on('click', '#createNits', function () {
    clearFormNits();

    $("#updateNit").hide();
    $("#saveNit").show();
    $("#nitFormModal").modal('show');
});

$("#tipo_contribuyente").on('change', function(e) {
    var tipoContribuyente = $("#tipo_contribuyente").val();

    if (tipoContribuyente == 1) {
        $("#primer_nombre").prop('required',false);
        $("#otros_nombres").prop('required',false);
        $("#primer_apellido").prop('required',false);
        $("#segundo_apellido").prop('required',false);
        $("#razon_social").prop('required',true);
    } else {
        $("#primer_nombre").prop('required',true);
        $("#otros_nombres").prop('required',false);
        $("#primer_apellido").prop('required',true);
        $("#segundo_apellido").prop('required',false);
        $("#razon_social").prop('required',false);
    }
    $("#numero_documento").prop('required',false);
    $("#direccion").prop('required',true);
    $("#email").prop('required',true);
    $("#telefono_1").prop('required',false);
    
});

$(document).on('click', '#updateNit', function () {

    var form = document.querySelector('#nitsForm');

    if(form.checkValidity()){
        $("#saveNitLoading").show();
        $("#updateNit").hide();
        $("#saveNit").hide();
        
        let data = {
            id: $("#id_nit_up").val(),
            id_tipo_documento: $("#id_tipo_documento").val(),
            numero_documento: $("#numero_documento").val(),
            tipo_contribuyente: $("#tipo_contribuyente").val(),
            primer_apellido: $("#primer_apellido").val(),
            segundo_apellido: $("#segundo_apellido").val(),
            primer_nombre: $("#primer_nombre").val(),
            otros_nombres: $("#otros_nombres").val(),
            razon_social: $("#razon_social").val(),
            direccion: $("#direccion").val(),
            email: $("#email").val(),
            telefono_1: $("#telefono_1").val(),
            id_ciudad: $("#id_ciudad").val(),
            observaciones: $("#observaciones").val(),
            avatar: newImgProfile
        }

        $.ajax({
            url: base_url + 'nit',
            method: 'PUT',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                clearFormNits();
                $("#saveNit").show();
                $("#updateNit").hide();
                $("#saveNitLoading").hide();
                $("#nitFormModal").modal('hide');
                nits_table.row.add(res.data).draw();
                agregarToast('exito', 'Edición exitosa', 'Cedula nit actualizada con exito!', true );
            }
        }).fail((res) => {
            $('#saveNit').hide();
            $('#updateNit').show();
            $("#saveNitLoading").hide();
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
            agregarToast('error', 'Edición errada', errorsMsg);
        });
    } else {
        form.classList.add('was-validated');
    }
});

$(document).on('click', '#saveNit', function () {
    var form = document.querySelector('#nitsForm');

    if(form.checkValidity()){

        $("#saveNitLoading").show();
        $("#updateNit").hide();
        $("#saveNit").hide();

        let data = {
            id_tipo_documento: $("#id_tipo_documento").val(),
            numero_documento: $("#numero_documento").val(),
            tipo_contribuyente: $("#tipo_contribuyente").val(),
            primer_apellido: $("#primer_apellido").val(),
            segundo_apellido: $("#segundo_apellido").val(),
            primer_nombre: $("#primer_nombre").val(),
            otros_nombres: $("#otros_nombres").val(),
            razon_social: $("#razon_social").val(),
            direccion: $("#direccion").val(),
            email: $("#email").val(),
            telefono_1: $("#telefono_1").val(),
            id_ciudad: $("#id_ciudad").val(),
            observaciones: $("#observaciones").val(),
            avatar: newImgProfile
        }

        $.ajax({
            url: base_url + 'nit',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                clearFormNits();
                $("#saveNit").show();
                $("#updateNit").hide();
                $("#saveNitLoading").hide();
                $("#nitFormModal").modal('hide');
                nits_table.row.add(res.data).draw();
                agregarToast('exito', 'Creación exitosa', 'Cedula nit creada con exito!', true);
            }
        }).fail((err) => {
            $('#saveNit').show();
            $('#saveNitLoading').hide();
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
    } else {
        form.classList.add('was-validated');
    }
});

function clearFormNits(){
    $("#textNitCreate").show();
    $("#textNitUpdate").hide();
    $("#saveNitLoading").hide();

    $("#id_tipo_documento").val('').change();
    $("#id_ciudad").val('').change();
    $("#observaciones").val('');
    $("#numero_documento").val('');
    $("#tipo_contribuyente").val('').change();
    $("#primer_apellido").val('');
    $("#segundo_apellido").val('');
    $("#primer_nombre").val('');
    $("#otros_nombres").val('');
    $("#razon_social").val('');
    $("#telefono_1").val('');
    $("#direccion").val('');
    $("#email").val('');
    $('#default_avatar').show();
    $('#new_avatar').hide();
    newImgProfile = '';
}