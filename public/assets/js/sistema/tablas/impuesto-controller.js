var impuesto_table = null;
var $comboTipoImpuesto = null;

function impuestoInit() {

    cargarTablasImpuestos();
    cargarCombosImpuestos();

    $('.water').hide();
}

function cargarTablasImpuestos() {
    impuesto_table = $('#impuestoTable').DataTable({
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
            url: base_url + 'impuesto',
        },
        columns: [
            { "data":'nombre' },
            {
                "data": function (row, type, set){
                    if (row.tipo_impuesto) {
                        return row.tipo_impuesto.codigo+' - '+row.tipo_impuesto.nombre;
                    }
                    return '';
                }
            },
            { "data":'base', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { "data":'total_uvt', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { "data":'porcentaje', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            {
                "data": function (row, type, set){
                    var html = '';
                    if (editarImpuesto) html+= '<span id="editimpuesto_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-impuesto" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    // if (eliminarImpuesto) html+= '<span id="deleteimpuesto_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-impuesto" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if(impuesto_table) {
        impuesto_table.on('click', '.edit-impuesto', function() {
            $("#textImpuestoCreate").hide();
            $("#textImpuestoUpdate").show();
            $("#saveImpuestoLoading").hide();
            $("#updateImpuesto").show();
            $("#saveImpuesto").hide();
        
            var trImpuesto = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, impuesto_table);
            
            $("#id_impuesto_up").val(id);
            $("#nombre_impuesto").val(data.nombre);
            $("#base_impuesto").val(data.base);
            $("#total_uvt_impuesto").val(data.total_uvt);
            $("#porcentaje_impuesto").val(data.porcentaje);

            if (data.tipo_impuesto) {
                var dataTipoImpuesto = {
                    id: data.tipo_impuesto.id,
                    text: data.tipo_impuesto.codigo + ' ' + data.tipo_impuesto.nombre
                };
                var newOption = new Option(dataTipoImpuesto.text, dataTipoImpuesto.id, false, false);
                $comboTipoImpuesto.append(newOption).trigger('change');
                $comboTipoImpuesto.val(dataTipoImpuesto.id).trigger('change');
            } else {
                $("#id_impuesto_cuenta").val('').change();
            }
        
            $("#impuestoFormModal").modal('show');
        });

        impuesto_table.on('dblclick', 'tr', function () {
            var data = impuesto_table.row(this).data();
            if (data) {
                document.getElementById("editimpuesto_"+data.id).click();
            }
        });
        
        impuesto_table.on('click', '.drop-impuesto', function() {
            var trImpuesto = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, impuesto_table);
            Swal.fire({
                title: 'Eliminar centro de costos: '+data.nombre+'?',
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
                        url: base_url + 'impuesto',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            impuesto_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Centro de costos eliminado con exito!', true );
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

    impuesto_table.ajax.reload();
}

function cargarCombosImpuestos() {
    $comboTipoImpuesto = $('#id_tipo_impuesto_impuesto').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#impuestoFormModal'),
        delay: 250,
        placeholder: "Seleccione un tipo de impuesto",
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
            url: 'api/impuesto/combo-tipo-impuesto',
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

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputImpuesto").on("input", function (e) {
    impuesto_table.context[0].jqXHR.abort();
    $('#impuestoTable').DataTable().search($("#searchInputImpuesto").val()).draw();
});

$(document).on('click', '#createImpuesto', function () {
    clearFormImpuesto();
    $("#updateImpuesto").hide();
    $("#saveImpuesto").show();
    $("#impuestoFormModal").modal('show');
});

function clearFormImpuesto(){
    $("#textImpuestoCreate").show();
    $("#textImpuestoUpdate").hide();
    $("#saveImpuestoLoading").hide();

    $("#id_impuesto_up").val('');
    $("#id_tipo_impuesto_impuesto").val('').trigger('change');
    $("#nombre_impuesto").val('');
    $("#base_impuesto").val('');
    $("#total_uvt_impuesto").val('');
    $("#porcentaje_impuesto").val('');
}

$(document).on('click', '#saveImpuesto', function () {

    var form = document.querySelector('#impuestoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveImpuestoLoading").show();
    $("#updateImpuesto").hide();
    $("#saveImpuesto").hide();

    let data = {
        id_tipo_impuesto: $("#id_tipo_impuesto_impuesto").val(),
        nombre: $("#nombre_impuesto").val(),
        base: $("#base_impuesto").val(),
        total_uvt: $("#total_uvt_impuesto").val(),
        porcentaje: $("#porcentaje_impuesto").val(),
    }

    $.ajax({
        url: base_url + 'impuesto',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormImpuesto();
            $("#saveImpuesto").show();
            $("#saveImpuestoLoading").hide();
            $("#impuestoFormModal").modal('hide');
            impuesto_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Impuesto creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveImpuesto').show();
        $('#saveImpuestoLoading').hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#updateImpuesto', function () {

    var form = document.querySelector('#impuestoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveImpuestoLoading").show();
    $("#updateImpuesto").hide();
    $("#saveImpuesto").hide();

    let data = {
        id: $("#id_impuesto_up").val(),
        id_tipo_impuesto: $("#id_tipo_impuesto_impuesto").val(),
        nombre: $("#nombre_impuesto").val(),
        base: $("#base_impuesto").val(),
        total_uvt: $("#total_uvt_impuesto").val(),
        porcentaje: $("#porcentaje_impuesto").val(),
    }

    $.ajax({
        url: base_url + 'impuesto',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
    if(res.success){
        console.log(res.data);
        clearFormImpuesto();
        $("#saveImpuesto").show();
        $("#saveImpuestoLoading").hide();
        $("#impuestoFormModal").modal('hide');
        impuesto_table.ajax.reload();
        agregarToast('exito', 'Actualización exitosa', 'Impuesto actualizado con exito!', true );
    }
    }).fail((err) => {
        $('#updateImpuesto').show();
        $('#saveImpuestoLoading').hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

function actualizarBase() {
    
    const total_uvt = parseFloat($("#total_uvt_impuesto").val());
    $("#base_impuesto").val(total_uvt * parseFloat(valor_uvt));
}