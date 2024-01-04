
var vendedores_table = null;
$comboNitVendedor = null;

function vendedoresInit() {

    vendedores_table = $('#vendedoresTable').DataTable({
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
            url: base_url + 'vendedores',
        },
        columns: [
            {"data":'id', visible: false},
            {"data": function (row, type, set){
                console.log('row: ',row);
                if (row.nit) {
                    return row.nit.numero_documento;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.nit) {
                    return row.nit.nombre_completo;
                }
                return '';
            }},
            {"data":'porcentaje_comision', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 2, targets: -1},
            {"data":'plazo_dias', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 2, targets: -1},
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
                    if (editarVendedores) html+= '<span id="editvendedores_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-vendedores" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarVendedores) html+= '<span id="deletevendedores_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-vendedores" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if(vendedores_table) {
        vendedores_table.on('click', '.edit-vendedores', function() {
            $("#textVendedoresCreate").hide();
            $("#textVendedoresUpdate").show();
            $("#saveVendedoresLoading").hide();
            $("#updateVendedores").show();
            $("#saveVendedores").hide();
        
            var trVendedores = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, vendedores_table);

            if(data.nit){
                var dataNit = {
                    id: data.nit.id,
                    text: data.nit.numero_documento + ' - ' + data.nit.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboNitVendedor.append(newOption).trigger('change');
                $comboNitVendedor.val(dataNit.id).trigger('change');
            }
            
            $("#id_vendedor_up").val(id);
            $("#plazo_dias_vendedor").val(data.plazo_dias);
            $("#porcentaje_comision_vendedor").val(data.porcentaje_comision);
        
            $("#vendedoresFormModal").modal('show');
        });
        
        vendedores_table.on('click', '.drop-vendedores', function() {
            var trVendedores = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, vendedores_table);

            Swal.fire({
                title: 'Eliminar vendedor: '+data.nit.nombre_completo+'?',
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
                        url: base_url + 'vendedores',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            vendedores_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Vendedor eliminado con exito!', true );
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

    $comboNitVendedor = $('#id_nit_vendedor').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    let column = vendedores_table.column(6);

    if (!editarVendedores && !eliminarVendedores) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    vendedores_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputVendedores").on("input", function (e) {
    vendedores_table.context[0].jqXHR.abort();
    $('#vendedoresTable').DataTable().search($("#searchInputVendedores").val()).draw();
});

$(document).on('click', '#createVendedores', function () {
    clearFormVendedores();
    $("#updateVendedores").hide();
    $("#saveVendedores").show();
    $("#vendedoresFormModal").modal('show');
});

function clearFormVendedores(){
    $("#textVendedoresCreate").show();
    $("#textVendedoresUpdate").hide();
    $("#saveVendedoresLoading").hide();

    $("#id_vendedor_up").val('');
    $("#id_nit_vendedor").val(0).change();
    $("#plazo_dias_vendedor").val(0);
    $("#porcentaje_comision_vendedor").val(0);
}

$(document).on('click', '#saveVendedores', function () {

    var form = document.querySelector('#vendedoresForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveVendedoresLoading").show();
    $("#updateVendedores").hide();
    $("#saveVendedores").hide();

    let data = {
        id_nit: $("#id_nit_vendedor").val(),
        plazo_dias: $("#plazo_dias_vendedor").val(),
        porcentaje_comision: $("#porcentaje_comision_vendedor").val()
    }

    $.ajax({
        url: base_url + 'vendedores',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormVendedores();
            $("#saveVendedores").show();
            $("#saveVendedoresLoading").hide();
            $("#vendedoresFormModal").modal('hide');
            vendedores_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Vendedor creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveVendedores').show();
        $('#saveVendedoresLoading').hide();
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

$(document).on('click', '#updateVendedores', function () {

    var form = document.querySelector('#vendedoresForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveVendedoresLoading").show();
    $("#updateVendedores").hide();
    $("#saveVendedores").hide();

    let data = {
        id: $("#id_vendedor_up").val(),
        id_nit: $("#id_nit_vendedor").val(),
        plazo_dias: $("#plazo_dias_vendedor").val(),
        porcentaje_comision: $("#porcentaje_comision_vendedor").val()
    }

    $.ajax({
        url: base_url + 'vendedores',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
    if(res.success){
        console.log(res.data);
        clearFormVendedores();
        $("#saveVendedores").show();
        $("#saveVendedoresLoading").hide();
        $("#vendedoresFormModal").modal('hide');
        vendedores_table.ajax.reload();
        agregarToast('exito', 'Actualización exitosa', 'Vendedor actualizado con exito!', true );
    }
    }).fail((err) => {
        $('#updateVendedores').show();
        $('#saveVendedoresLoading').hide();
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
        agregarToast('error', 'Error al actualizar Vendedor!', errorsMsg);
    });
});