var formaspago_table = null;
var $comboCuenta = null;
var $comboTiposFormasPago = null;

function formapagoInit() {
    formaspago_table = $('#formasPagoTable').DataTable({
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
            url: base_url + 'forma-pago',
        },
        columns: [
            {"data":'nombre'},
            {
                "data": function (row, type, set){
                    if(row.cuenta){
                        return row.cuenta.cuenta + ' - ' + row.cuenta.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.tipo_forma_pago){
                        return row.tipo_forma_pago.codigo + ' - ' + row.tipo_forma_pago.nombre;
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
                    if (editarFormaPago) html+= '<span id="editformaspago_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-formaspago" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarFormaPago) html+= '<span id="deleteformaspago_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-formaspago" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
    
        ]
    });

    if(formaspago_table) {
        formaspago_table.on('click', '.edit-formaspago', function() {
            $("#textFormasPagoCreate").hide();
            $("#textFormasPagoUpdate").show();
            $("#saveFormasPagoLoading").hide();
            $("#updateFormasPago").show();
            $("#saveFormasPago").hide();
        
            var id = this.id.split('_')[1];
            var data = getDataById(id, formaspago_table);

            if(data.cuenta){
                var dataCuenta = {
                    id: data.cuenta.id,
                    text: data.cuenta.cuenta + ' - ' + data.cuenta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuenta.append(newOption).trigger('change');
                $comboCuenta.val(dataCuenta.id).trigger('change');
            }

            if(data.tipo_forma_pago){
                var dataTipoFormaPago = {
                    id: data.tipo_forma_pago.id,
                    text: data.tipo_forma_pago.codigo + ' - ' + data.tipo_forma_pago.nombre
                };
                var newOption = new Option(dataTipoFormaPago.text, dataTipoFormaPago.id, false, false);
                $comboTiposFormasPago.append(newOption).trigger('change');
                $comboTiposFormasPago.val(dataTipoFormaPago.id).trigger('change');
            }
            
            $("#id_formas_pago_up").val(id);
            $("#nombre_forma_pago").val(data.nombre);
        
            $("#formasPagoFormModal").modal('show');
        });
        
        formaspago_table.on('click', '.drop-formaspago', function() {

            var id = this.id.split('_')[1];
            var data = getDataById(id, formaspago_table);
            Swal.fire({
                title: 'Eliminar forma de pago: '+data.nombre+'?',
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
                        url: base_url + 'forma-pago',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            formaspago_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Forma de pago eliminada con exito!', true );
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

    $comboCuenta = $('#id_cuenta_forma_pago').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#formasPagoFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            },
            processResults: function (data) {
                var data_modified = $.map(data.data, function (obj) {
                    console.log(obj);
                    obj.disabled = obj.auxiliar ? false : true;
                    return obj;
                });
                return {
                    results: data_modified
                };
            }
        }
    });

    $comboTiposFormasPago = $('#id_tipo_formas_pago').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#formasPagoFormModal'),
        delay: 250,
        ajax: {
            url: 'api/forma-pago/combo-tipo-formas-pago',
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

    let column = formaspago_table.column(5);

    if (!editarFormaPago && !eliminarFormaPago) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    formaspago_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputFormaPago").on("input", function (e) {
    formaspago_table.context[0].jqXHR.abort();
    $('#formasPagoTable').DataTable().search($("#searchInputFormaPago").val()).draw();
});

$(document).on('click', '#createFormaPago', function () {
    clearFormFormaPago();
    $("#updateFormasPago").hide();
    $("#saveFormasPago").show();
    $("#formasPagoFormModal").modal('show');
});

function clearFormFormaPago(){
    $("#textFormasPagoCreate").show();
    $("#textFormasPagoUpdate").hide();
    $("#saveFormasPagoLoading").hide();

    $("#nombre_forma_pago").val('');
    $comboCuenta.val('').trigger('change');
    $comboTiposFormasPago.val('').trigger('change');
}

$(document).on('click', '#saveFormasPago', function () {

    var form = document.querySelector('#formasPagoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveFormasPagoLoading").show();
    $("#updateFormasPago").hide();
    $("#saveFormasPago").hide();

    let data = {
        nombre: $("#nombre_forma_pago").val(),
        id_cuenta: $("#id_cuenta_forma_pago").val(), 
        id_tipo_formas_pago: $("#id_tipo_formas_pago").val() 
    }

    $.ajax({
        url: base_url + 'forma-pago',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormFormaPago();
            $("#saveFormasPago").show();
            $("#updateFormasPago").hide();
            $("#saveFormasPagoLoading").hide();
            $("#formasPagoFormModal").modal('hide');
            formaspago_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Forma de pago creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveFormasPago').show();
        $('#updateFormasPago').hide();
        $('#saveFormasPagoLoading').hide();
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

$(document).on('click', '#updateFormasPago', function () {

    var form = document.querySelector('#formasPagoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveFormasPagoLoading").show();
    $("#updateFormasPago").hide();
    $("#saveFormasPago").hide();

    let data = {
        id: $("#id_formas_pago_up").val(),
        id_cuenta: $("#id_cuenta_forma_pago").val(),
        id_tipo_formas_pago: $("#id_tipo_formas_pago").val(),
        nombre: $("#nombre_forma_pago").val(),
    }

    $.ajax({
        url: base_url + 'forma-pago',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
    if(res.success){
        
        clearFormFormaPago();
        $("#saveFormasPago").hide();
        $("#updateFormasPago").show();
        $("#saveFormasPagoLoading").hide();
        $("#formasPagoFormModal").modal('hide');
        formaspago_table.ajax.reload();
        agregarToast('exito', 'Actualización exitosa', 'Centro de costos actualizado con exito!', true );
    }
    }).fail((err) => {
        $('#updateFormasPago').show();
        $('#saveFormasPagoLoading').hide();
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
        agregarToast('error', 'Error al actualizar formas de pago!', errorsMsg);
    });
});