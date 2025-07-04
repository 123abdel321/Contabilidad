var newImgProfile = '';
var nits_table = null;
var $comboCiudad = null;
var $comboVendedores = null;
var $comboTipoDocumento = null;
var $comboResponsabilidares = null;
var $comboActividadEconomica = null;

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
            {"data":'numero_documento'},
            {
                "data": function (row, type, set){
                    if (row.razon_social) {
                        return row.razon_social
                    }
                    var primer_nombre = row.primer_nombre ? row.primer_nombre+' ' : '';
                    var otros_nombres = row.otros_nombres ? row.otros_nombres+' ' : '';
                    var primer_apellido = row.primer_apellido ? row.primer_apellido+' ' : '';
                    var segundo_apellido = row.segundo_apellido ? row.segundo_apellido+' ' : '';
                    
                    return primer_nombre+otros_nombres+primer_apellido+segundo_apellido;
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
            {
                "data": function (row, type, set){
                    if(row.declarante){
                        return 'SI';
                    }
                    return 'NO';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.actividad_economica){
                        return row.actividad_economica.nombre;
                    }
                    return '';
                }
            },
            {"data":'porcentaje_reteica', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'porcentaje_aiu', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {
                "data": function (row, type, set){
                    if(row.sumar_aiu){
                        return 'SI';
                    }
                    return 'NO';
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
                    if (editarUsuario) html+= '<span id="editnits_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-nits" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                    if (eliminarUsuario) html+= '<span id="deletenits_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-nits" style="margin-bottom: 0rem !important">Eliminar</span>';
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
            
            if(data.vendedor){
                var dataVendedor = {
                    id: data.vendedor.id,
                    text: data.vendedor.nit.nombre_completo
                };
                var newOption = new Option(dataVendedor.text, dataVendedor.id, false, false);
                $comboVendedores.append(newOption).trigger('change');
                $comboVendedores.val(dataVendedor.id).trigger('change');
            }
        
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
        
            // if (data.declarante) {
            //     $('#declarante_nit').prop('checked', true);
            // } else {
            //     $('#declarante_nit').prop('checked', false);
            // }

            if (data.sumar_aiu) {
                $('#sumar_aiu_nits').prop('checked', true);
            } else {
                $('#sumar_aiu_nits').prop('checked', false);
            }

            if (data.id_responsabilidades) {
                var id_responsabilidades = data.id_responsabilidades.split(",");
                $("#id_responsabilidades").val(id_responsabilidades).change();
            }

            if(data.actividad_economica){
                var dataActividadEconomica = {
                    id: data.actividad_economica.id,
                    text: data.actividad_economica.nombre
                };
                var newOption = new Option(dataActividadEconomica.text, dataActividadEconomica.id, false, false);
                $comboActividadEconomica.append(newOption).trigger('change');
                $comboActividadEconomica.val(dataActividadEconomica.id).trigger('change');
            }

            hideFormNits();
        
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
            $("#porcentaje_aiu").val(data.porcentaje_aiu);
            $("#porcentaje_reteica").val(data.porcentaje_reteica);
            $("#div_id_actividad_economica_nit").show();
            if (data.actividad_economica) {
                $("#div_porcentaje_reteica").show();
            } else {
                $("#div_porcentaje_reteica").hide();
            }
            
            if(data.logo_nit) {
                $('#new_avatar').attr('src', 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/'+data.logo_nit);
                $('#new_avatar').show();
                $('#default_avatar').hide();
            } else {
                $('#new_avatar').attr('src', '');
                $('#new_avatar').hide();
                $('#default_avatar').show();
            }
        
            $("#nitFormModal").modal('show');
        });

        nits_table.on('dblclick', 'tr', function () {
            var data = nits_table.row(this).data();
            if (data) {
                document.getElementById("editnits_"+data.id).click();
            }
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

    $comboVendedores = $('#id_vendedor_nit').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitFormModal'),
        delay: 250,
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
            url: 'api/vendedores/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                var data_modified = $.map(data.data, function (obj) {
                    obj.text = obj.nit.nombre_completo;
                    return obj;
                });
                return {
                    results: data_modified
                };
            },
        }
    });

    $comboResponsabilidares = $('#id_responsabilidades').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#nitFormModal'),
    });

    $comboActividadEconomica = $('#id_actividad_economica_nit').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#nitFormModal'),
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/actividad-economica-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $("#searchInputNits").on("input", function (e) {
        nits_table.context[0].jqXHR.abort();
        $('#nitTable').DataTable().search($("#searchInputNits").val()).draw();
    });

    let column = nits_table.column(12);
    
    if (!editarUsuario && !eliminarUsuario) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    nits_table.ajax.reload();
}

$('.only-numbers').keypress(function (e) {
    var txt = String.fromCharCode(e.which);
    if (!txt.match(/[0-9&. ]/)) {
        return false;
    }
});
  
$('.only-numbers').bind('paste', function() {
    setTimeout(function() { 
        var value = $(this).val();
        var updated = value.replace(/[^0-9&. ]/g, '');
        $(this).val(updated);
    });
});

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

$(document).on('click', '#createNits', function () {
    clearFormNits();
    hideFormNits();
    $("#updateNit").hide();
    $("#saveNit").show();
    $("#div_declarante").hide();
    $("#div_id_actividad_economica_nit").hide();
    $("#nitFormModal").modal('show');
});

$("#id_tipo_documento").on('change', function(e) {
    hideFormNits();
    $("#div_sumar_aiu").show();
    $("#div_declarante").show();
    $("#div_id_actividad_economica_nit").show();
});

$('#id_actividad_economica_nit').on('change', function (e) {
    var data = $(this).select2('data');
    if (data.length) {
        var actividadEconomica = data[0];
        $("#div_porcentaje_reteica").show();
        $("#porcentaje_reteica").val(actividadEconomica.porcentaje);
    } else {
        $("#div_porcentaje_reteica").hide();
        $("#porcentaje_reteica").val(0);
    }
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
            id_vendedor: $('#id_vendedor_nit').val(),
            porcentaje_aiu: $('#porcentaje_aiu').val(),
            porcentaje_reteica: $('#porcentaje_reteica').val(),
            id_responsabilidades: $("#id_responsabilidades").val(),
            id_actividad_economica: $("#id_actividad_economica_nit").val(),
            // declarante: $("input[type='checkbox']#declarante_nit").is(':checked') ? '1' : '',
            sumar_aiu: $("input[type='checkbox']#sumar_aiu_nits").is(':checked') ? '1' : '',
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
            id_vendedor: $('#id_vendedor_nit').val(),
            porcentaje_aiu: $('#porcentaje_aiu').val(),
            porcentaje_reteica: $('#porcentaje_reteica').val(),
            id_responsabilidades: $("#id_responsabilidades").val(),
            id_actividad_economica: $("#id_actividad_economica_nit").val(),
            // declarante: $("input[type='checkbox']#declarante_nit").is(':checked') ? '1' : '',
            sumar_aiu: $("input[type='checkbox']#sumar_aiu_nits").is(':checked') ? '1' : '',
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
    $("#id_vendedor_nit").val('').change();
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
    $("#porcentaje_aiu").val('');
    $("#porcentaje_reteica").val('');
    $('#default_avatar').show();
    $('#new_avatar').hide();
    $("#id_actividad_economica_nit").val('').change();
    $("#id_responsabilidades").val([5,7]).change();
    
    $("#div_sumar_aiu").hide();
    $("#div_declarante").hide();
    $("#div_id_actividad_economica_nit").hide();

    newImgProfile = '';
}

function hideFormNits(){
    var tipoDocumento = $("#id_tipo_documento").val();

    var divsForm = [
        'id_vendedor_nit',
        'id_ciudad',
        'observaciones',
        'numero_documento',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'otros_nombres',
        'razon_social',
        'telefono_1',
        'porcentaje_aiu',
        'id_responsabilidades',
        'id_actividad_economica_nit',
        'direccion',
        'email',
        'declarante'
    ];

    var nitsForm = [
        'id_vendedor_nit',
        'id_ciudad',
        'observaciones',
        'numero_documento',
        'razon_social',
        'telefono_1',
        'porcentaje_aiu',
        'id_responsabilidades',
        'direccion',
        'email',
        'declarante'
    ];

    var noNitsForm = [
        'id_vendedor_nit',
        'id_ciudad',
        'observaciones',
        'numero_documento',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'otros_nombres',
        'telefono_1',
        'porcentaje_aiu',
        'id_responsabilidades',
        'direccion',
        'email',
        'declarante'
    ];

    divsForm.forEach(form => {
        $("#div_"+form).hide();
        $("#"+form).prop('required',false);
    });

    if (tipoDocumento && tipoDocumento == '6') {
        nitsForm.forEach(form => {
            $("#div_"+form).show();
            if (form == 'otros_nombres' || form == 'segundo_apellido' || form == 'porcentaje_aiu' || form == 'id_responsabilidades') {
            } else {
                $("#"+form).prop('required',true);
            }
        });
    } else if (tipoDocumento) {
        noNitsForm.forEach(form => {
            $("#div_"+form).show();
            if (form == 'otros_nombres' || form == 'segundo_apellido' || form == 'porcentaje_aiu' || form == 'id_responsabilidades') {
            } else {
                $("#"+form).prop('required',true);
            }
        });
    }

    $("#div_porcentaje_reteica").hide();
    $('#id_vendedor_nit').prop('required',false);
    $('#observaciones').prop('required',false);
}