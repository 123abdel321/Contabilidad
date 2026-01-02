var $comboNit = null;
var $comboComprobante = null;
var $comboCuentaDebito = null;
var $comboCuentaCredito = null;
var cargue_descargue_table = null

function carguedescargueInit() {
    cargue_descargue_table = $('#cargueDescargueTable').DataTable({
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
            url: base_url + 'cargue-descargue',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){  
                if (row.tipo == 1) return 'CARGUE';
                if (row.tipo == 2) return 'TRASLADO';
                return 'DESCARGUE';
            }},
            {"data": function (row, type, set){
                if (row.id_comprobante && row.comprobante) {
                    return row.comprobante.codigo +' - '+ row.comprobante.nombre;
                }
                return;
            }},
            {"data": function (row, type, set){  
                if (row.id_cuenta_debito) {
                    return row.cuenta_debito.cuenta +' - '+ row.cuenta_debito.nombre;
                }
                return;
            }},
            {"data": function (row, type, set){  
                if (row.id_cuenta_credito) {
                    return row.cuenta_credito.cuenta +' - '+ row.cuenta_credito.nombre;
                }
                return;
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
                    if (editarCargueDescargue) html+= '<span id="editcarguedescargue_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-carguedescargue" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarCargueDescargue) html+= '<span id="deletecarguedescargue_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-carguedescargue" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if(cargue_descargue_table) {
        cargue_descargue_table.on('click', '.edit-carguedescargue', function() {
            clearFormCargueDescargue();

            $("#textCargueDescargueCreate").hide();
            $("#textCargueDescargueUpdate").show();
            $("#saveCargueDescargueLoading").hide();
            $("#updateCargueDescargue").show();
            $("#saveCargueDescargue").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, cargue_descargue_table);
    
            if(data.nit){
                var dataNit = {
                    id: data.nit.id,
                    text: data.nit.numero_documento + ' - ' + data.nit.nombre_completo
                };
                var newOption = new Option(dataNit.text, dataNit.id, false, false);
                $comboNit.append(newOption).trigger('change');
                $comboNit.val(dataNit.id).trigger('change');
            }

            if(data.comprobante){
                var dataComprobante = {
                    id: data.comprobante.id,
                    text: data.comprobante.codigo + ' - ' + data.comprobante.nombre
                };
                var newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
                $comboComprobante.append(newOption).trigger('change');
                $comboComprobante.val(dataComprobante.id).trigger('change');
            }

            if(data.cuenta_debito){
                var dataCuentaDebito = {
                    id: data.cuenta_debito.id,
                    text: data.cuenta_debito.cuenta + ' - ' + data.cuenta_debito.nombre
                };
                var newOption = new Option(dataCuentaDebito.text, dataCuentaDebito.id, false, false);
                $comboCuentaDebito.append(newOption).trigger('change');
                $comboCuentaDebito.val(dataCuentaDebito.id).trigger('change');
            }

            if(data.cuenta_credito){
                var dataCuentaCredito = {
                    id: data.cuenta_credito.id,
                    text: data.cuenta_credito.cuenta + ' - ' + data.cuenta_credito.nombre
                };
                var newOption = new Option(dataCuentaCredito.text, dataCuentaCredito.id, false, false);
                $comboCuentaCredito.append(newOption).trigger('change');
                $comboCuentaCredito.val(dataCuentaCredito.id).trigger('change');
            }
            
            $("#id_cargue_descargue_up").val(data.id);
            $("#nombre_cargue_descargue").val(data.nombre);
            $("#tipo_cargue_descargue").val(data.tipo);

            $("#cargueDescargueFormModal").modal('show');
        });

        cargue_descargue_table.on('dblclick', 'tr', function () {
            var data = cargue_descargue_table.row(this).data();
            if (data) {
                document.getElementById("editcarguedescargue_"+data.id).click();
            }
        });

        cargue_descargue_table.on('click', '.drop-carguedescargue', function() {

            var id = this.id.split('_')[1];
            var data = getDataById(id, cargue_descargue_table);

            Swal.fire({
                title: 'Eliminar Cargue / Descargue: '+data.nombre+'?',
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
                        url: base_url + 'cargue-descargue',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            cargue_descargue_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Cargue / Descargue eliminado con exito!', true );
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

    $comboNit = $('#id_nit_cargue_descargue').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#cargueDescargueFormModal'),
        placeholder: "Seleccione una Cédula/nit",
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

    $comboComprobante = $('#id_comprobante_cargue_descargue').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cargueDescargueFormModal'),
        delay: 250,
        placeholder: "Seleccione un Comprobante",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaDebito = $('#id_cuenta_debito_cargue_descargue').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cargueDescargueFormModal'),
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                var data_modified = $.map(data.data, function (obj) {
                    obj.disabled = obj.auxiliar ? false : true;
                    return obj;
                });
                return {
                    results: data_modified
                };
            }
        }
    });

    $comboCuentaCredito = $('#id_cuenta_credito_cargue_descargue').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#cargueDescargueFormModal'),
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                var data_modified = $.map(data.data, function (obj) {
                    obj.disabled = obj.auxiliar ? false : true;
                    return obj;
                });
                return {
                    results: data_modified
                };
            }
        }
    });

    let column = cargue_descargue_table.column(7);

    if (!editarCargueDescargue && !eliminarCargueDescargue) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    cargue_descargue_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputCargueDescargue").on("input", function (e) {
    cargue_descargue_table.context[0].jqXHR.abort();
    $('#cargueDescargueTable').DataTable().search($("#searchInputCargueDescargue").val()).draw();
});

$(document).on('click', '#createCargueDescargue', function () {
    clearFormCargueDescargue();

    $("#updateCargueDescargue").hide();
    $("#saveCargueDescargue").show();

    $("#cargueDescargueFormModal").modal('show');

    setTimeout(function(){
        $("#nombre_cargue_descargue").focus();
    },10);
});

function clearFormCargueDescargue () {
    $("#textCargueDescargueCreate").show();
    $("#textCargueDescargueUpdate").hide();

    $("#id_cargue_descargue_up").val('');
    $("#nombre_cargue_descargue").val('');
    $("#tipo_cargue_descargue").val(0).change();
    $("#id_nit_cargue_descargue").val(0).change();
    $("#id_comprobante_cargue_descargue").val(0).change();
    $("#id_cuenta_debito_cargue_descargue").val(0).change();
    $("#id_cuenta_credito_cargue_descargue").val(0).change();
}

$(document).on('click', '#saveCargueDescargue', function () {
    var form = document.querySelector('#cargueDescargueForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveCargueDescargueLoading").show();
    $("#updateCargueDescargue").hide();
    $("#saveCargueDescargue").hide();

    let data = {
        nombre: $("#nombre_cargue_descargue").val(),
        tipo: $("#tipo_cargue_descargue").val(),
        id_nit: $("#id_nit_cargue_descargue").val(),
        id_comprobante: $("#id_comprobante_cargue_descargue").val(),
        id_cuenta_debito: $("#id_cuenta_debito_cargue_descargue").val(),
        id_cuenta_credito: $("#id_cuenta_credito_cargue_descargue").val(),
    };

    $.ajax({
        url: base_url + 'cargue-descargue',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormCargueDescargue();
            $("#saveCargueDescargue").show();
            $("#saveCargueDescargueLoading").hide();
            $("#cargueDescargueFormModal").modal('hide');

            cargue_descargue_table.row.add(res.data).draw();

            agregarToast('exito', 'Creación exitosa', 'Cargue / Descargue creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveCargueDescargue').show();
        $('#saveCargueDescargueLoading').hide();
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

$(document).on('click', '#updateCargueDescargue', function () {

    var form = document.querySelector('#cargueDescargueForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveCargueDescargueLoading").show();
    $("#updateCargueDescargue").hide();
    $("#saveCargueDescargue").hide();

    let data = {
        id: $("#id_cargue_descargue_up").val(),
        nombre: $("#nombre_cargue_descargue").val(),
        tipo: $("#tipo_cargue_descargue").val(),
        id_nit: $("#id_nit_cargue_descargue").val(),
        id_comprobante: $("#id_comprobante_cargue_descargue").val(),
        id_cuenta_debito: $("#id_cuenta_debito_cargue_descargue").val(),
        id_cuenta_credito: $("#id_cuenta_credito_cargue_descargue").val(),
    };

    $.ajax({
        url: base_url + 'cargue-descargue',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
    if(res.success){

        clearFormCargueDescargue();
        $("#saveCargueDescargue").show();
        $("#saveCargueDescargueLoading").hide();
        $("#cargueDescargueFormModal").modal('hide');
        
        cargue_descargue_table.row.add(res.data).draw();

        agregarToast('exito', 'Actualización exitosa', 'Cargue / Descargue actualizado con exito!', true );
    }
    }).fail((err) => {
        $('#saveCargueDescargue').show();
        $('#saveCargueDescargueLoading').hide();
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
        agregarToast('error', 'Error al actualizar Cargue / Descargue!', errorsMsg);
    });
});

