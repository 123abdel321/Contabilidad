
var $comboPadreCuenta = null;
var $comboImpuesto = null;
var $comboFormatoCuenta = null;
var $comboConceptoCuenta = null;
var $comboColumnaCuenta = null;
var plan_cuentas_table = null;

function plancuentaInit() {
    plan_cuentas_table = $('#planCuentaTable').DataTable({
        pageLength: 50,
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
        scroller: {
            displayBuffer: 20,
            rowHeight: 50,
            loadingIndicator: true
        },
        deferRender: true,
        fixedHeader : {
            header : true,
            footer : true,
            headerOffset: 45
        },
        fixedColumns : {
            left: 0,
            right : 1,
        },
        'rowCallback': function(row, data, index){
            // console.log('data.cuenta: ',data);
            if(data.auxiliar == 1){
                return;
            }
            if(data.cuenta.length == 1){
                $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 2){
                $('td', row).css('background-color', 'rgb(64 164 209 / 45%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length >= 4){
                $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            return;
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'plan-cuenta'
        },
        columns: [
            {"data":'cuenta'},
            {"data":'nombre'},
            {
                "data": function (row, type, set){
                    if(row.tipos_cuenta.length){
                        return row.tipos_cuenta[0].tipo.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_cuenta){
                        return 'Credito';
                    }
                    return 'Dedito';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_ingresos == 1){
                        return 'Credito';
                    } else if (row.naturaleza_ingresos == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_egresos == 1){
                        return 'Credito';
                    } else if (row.naturaleza_egresos == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_compras == 1){
                        return 'Credito';
                    } else if (row.naturaleza_compras == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_ventas == 1){
                        return 'Credito';
                    } else if (row.naturaleza_ventas == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_nit){
                        return 'Si';
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_documento_referencia){
                        return 'Si';
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_concepto){
                        return 'Si';
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_centro_costos){
                        return 'Si';
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exogena_formato){
                        return row.exogena_formato.formato;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exogena_concepto){
                        return row.exogena_concepto.concepto;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exogena_columna){
                        return row.exogena_columna.nombre;
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
                    if (editarPlanCuenta) html+= '<span id="editplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-plan-cuentas" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                    if (eliminarPlanCuenta) html+= '<span id="deleteplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-plan-cuentas" style="margin-bottom: 0rem !important">Eliminar</span>';
                    return html;
                }
            }
        ]
    });

    if(plan_cuentas_table) {
        plan_cuentas_table.on('click', '.edit-plan-cuentas', function() {
            $("#textPlanCuentaCreate").hide();
            $("#textPlanCuentaUpdate").show();
            $("#savePlanCuentaLoading").hide();
            $("#updatePlanCuenta").show();
            $("#savePlanCuenta").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, plan_cuentas_table);

            if(data.padre){
                var dataCuenta = {
                    id: data.padre.id,
                    text: data.padre.cuenta + ' - ' + data.padre.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboPadreCuenta.append(newOption).trigger('change');
                $comboPadreCuenta.val(dataCuenta.id).trigger('change');
                $("#text_cuenta_padre").val(data.padre.cuenta);
                $("#cuenta").val(data.cuenta.slice(data.padre.cuenta.length));
            }else{ 
                $("#text_cuenta_padre").val('');
                $comboPadreCuenta.val('').trigger('change');
                $("#cuenta").val(data.cuenta);
            }

            if (data.impuesto) {
                var dataImpuesto = {
                    id: data.impuesto.id,
                    text: data.impuesto.nombre + ' %' + data.impuesto.porcentaje
                };
                var newOption = new Option(dataImpuesto.text, dataImpuesto.id, false, false);
                $comboImpuesto.append(newOption).trigger('change');
                $comboImpuesto.val(dataImpuesto.id).trigger('change');
            } else {
                $("#id_impuesto_cuenta").val('').change();
            }

            if (data.exogena_formato) {
                var dataFormato = {
                    id: data.exogena_formato.id,
                    text: data.exogena_formato.formato
                };
                var newOption = new Option(dataFormato.text, dataFormato.id, false, false);
                $comboFormatoCuenta.append(newOption).trigger('change');
                $comboFormatoCuenta.val(dataFormato.id).trigger('change');
            }

            if (data.exogena_concepto) {
                var dataConcepto = {
                    id: data.exogena_concepto.id,
                    text: data.exogena_concepto.concepto
                };
                var newOption = new Option(dataConcepto.text, dataConcepto.id, false, false);
                $comboConceptoCuenta.append(newOption).trigger('change');
                $comboConceptoCuenta.val(dataConcepto.id).trigger('change');
            }

            if (data.exogena_columna) {
                var dataColumna = {
                    id: data.exogena_columna.id,
                    text: data.exogena_columna.nombre
                };
                var newOption = new Option(dataColumna.text, dataColumna.id, false, false);
                $comboColumnaCuenta.append(newOption).trigger('change');
                $comboColumnaCuenta.val(dataColumna.id).trigger('change');
            }

            var tipoCuenta = [];
            data.tipos_cuenta.forEach(tipo_cuenta => {
                tipoCuenta.push(tipo_cuenta.id_tipo_cuenta);
            });

            $("#id_plan_cuenta").val(data.id);
            $("#id_tipo_cuenta").val(tipoCuenta).change();
            $("#nombre").val(data.nombre);
            $("#naturaleza_cuenta").val(data.naturaleza_cuenta).change();
            $("#naturaleza_ingresos").val(data.naturaleza_ingresos).change();
            $("#naturaleza_egresos").val(data.naturaleza_egresos).change();
            $("#naturaleza_compras").val(data.naturaleza_compras).change();
            $("#naturaleza_ventas").val(data.naturaleza_ventas).change();
            
            $("#exige_nit").prop( "checked", data.exige_nit == 1 ? true : false );
            $("#exige_documento_referencia").prop( "checked", data.exige_documento_referencia == 1 ? true : false );
            $("#exige_concepto").prop( "checked", data.exige_concepto == 1 ? true : false );
            $("#exige_centro_costos").prop( "checked", data.exige_centro_costos == 1 ? true : false );
        
            $("#planCuentaFormModal").modal('show');
        });

        plan_cuentas_table.on('dblclick', 'tr', function () {
            var data = plan_cuentas_table.row(this).data();
            if (data) {
                document.getElementById("editplancuentas_"+data.id).click();
            }
        });
    
        plan_cuentas_table.on('click', '.drop-plan-cuentas', function() {
            var trPlanCuenta = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, plan_cuentas_table);
            Swal.fire({
                title: 'Eliminar cuenta: '+data.nombre+'?',
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
                        url: base_url + 'plan-cuenta',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            plan_cuentas_table.row(trPlanCuenta).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Plan cuenta eliminado con exito!', true );
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

    $comboPadreCuenta = $('#id_padre').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#planCuentaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboImpuesto = $('#id_impuesto_cuenta').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#planCuentaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/impuesto/combo-impuesto',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboFormatoCuenta = $('#id_exogena_formato_nit').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#planCuentaFormModal'),
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
            url: 'api/exogena/formato',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboConceptoCuenta = $('#id_exogena_formato_concepto_nit').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#planCuentaFormModal'),
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
            url: 'api/exogena/concepto',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboColumnaCuenta = $('#id_exogena_formato_columna_nit').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#planCuentaFormModal'),
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
            url: 'api/exogena/columna',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_tipo_cuenta').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#planCuentaFormModal'),
    });

    let column = plan_cuentas_table.column(13);

    if (!editarPlanCuenta && !eliminarPlanCuenta) column.visible(false);
    else column.visible(true);

    $('.water').hide();
    plan_cuentas_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$(document).on('click', '#createPlanCuenta', function () {
    clearFormPlanCuenta();
    $("#exige_nit").prop( "checked", true );
    $("#exige_concepto").prop( "checked", true );
    $("#updatePlanCuenta").hide();
    $("#savePlanCuenta").show();
    $("#planCuentaFormModal").modal('show');
});

$("#searchInputCuenta").on("input", function (e) {
    plan_cuentas_table.context[0].jqXHR.abort();
    $('#planCuentaTable').DataTable().search($("#searchInputCuenta").val()).draw();
});

$(document).on('click', '#savePlanCuenta', function () {

    // var $valid = $('#planCuentaForm').valid();
    // if (!$valid) {
    //     // $validator.focusInvalid();
    //     return false;
    // }

    $("#savePlanCuentaLoading").show();
    $("#updatePlanCuenta").hide();
    $("#savePlanCuenta").hide();

    let data = {
        id_padre: $("#id_padre").val(),
        cuenta: $("#cuenta").val(),
        nombre: $("#nombre").val(),
        id_tipo_cuenta: $("#id_tipo_cuenta").val(),
        id_impuesto: $("#id_impuesto_cuenta").val(),
        id_exogena_formato: $('#id_exogena_formato_nit').val(),
        id_exogena_formato_concepto: $('#id_exogena_formato_concepto_nit').val(),
        id_exogena_formato_columna: $('#id_exogena_formato_columna_nit').val(),
        naturaleza_cuenta: $("#naturaleza_cuenta").val(),
        naturaleza_ingresos: $("#naturaleza_ingresos").val(),
        naturaleza_egresos: $("#naturaleza_egresos").val(),
        naturaleza_ventas: $("#naturaleza_ventas").val(),
        naturaleza_compras: $("#naturaleza_compras").val(),
        exige_nit: $("#exige_nit:checked").val() ? 1 : 0,
        exige_documento_referencia: $("#exige_documento_referencia:checked").val() ? 1 : 0,
        exige_concepto: $("#exige_concepto:checked").val() ? 1 : 0,
        exige_centro_costos: $("#exige_centro_costos:checked").val() ? 1 : 0,
    }

    $.ajax({
        url: base_url + 'plan-cuenta',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPlanCuenta();
            $("#savePlanCuenta").show();
            $("#updatePlanCuenta").hide();
            $("#savePlanCuentaLoading").hide();
            $("#planCuentaFormModal").modal('hide');
            plan_cuentas_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Cuenta creado con exito!', true);
        }
    }).fail((err) => {
        $('#savePlanCuenta').show();
        $('#savePlanCuentaLoading').hide();
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

function clearFormPlanCuenta(){
    $("#textPlanCuentaCreate").show();
    $("#textPlanCuentaUpdate").hide();
    $("#savePlanCuentaLoading").hide();

    $("#text_cuenta_padre").val('');
    $("#id_plan_cuenta").val('');
    $("#id_padre").val(0).change();
    $("#cuenta").val('');
    $("#nombre").val('');

    $("#naturaleza_cuenta").val('').change();
    $("#naturaleza_ingresos").val('').change();
    $("#naturaleza_egresos").val('').change();
    $("#naturaleza_compras").val('').change();
    $("#naturaleza_ventas").val('').change();
    $("#id_tipo_cuenta").val('').change();
    $("#id_impuesto_cuenta").val('').change();

    $('#id_exogena_formato_nit').val('').change();
    $('#id_exogena_formato_concepto_nit').val('').change();
    $('#id_exogena_formato_columna_nit').val('').change();

    $("#exige_nit").prop( "checked", false );
    $("#exige_documento_referencia").prop( "checked", false );
    $("#exige_concepto").prop( "checked", false );
    $("#exige_centro_costos").prop( "checked", false );
}

$(document).on('click', '#updatePlanCuenta', function () {

    // var $valid = $('#planCuentaForm').valid();
    // if (!$valid) {
    //     $validator.focusInvalid();
    //     return false;
    // }
    
    $("#savePlanCuentaLoading").show();
    $("#updatePlanCuenta").hide();
    $("#savePlanCuenta").hide();

    let data = {
        id: $("#id_plan_cuenta").val(),
        id_padre: $("#id_padre").val(),
        cuenta: $("#cuenta").val(),
        nombre: $("#nombre").val(),
        id_tipo_cuenta: $("#id_tipo_cuenta").val(),
        id_impuesto: $("#id_impuesto_cuenta").val(),
        id_exogena_formato: $('#id_exogena_formato_nit').val(),
        id_exogena_formato_concepto: $('#id_exogena_formato_concepto_nit').val(),
        id_exogena_formato_columna: $('#id_exogena_formato_columna_nit').val(),
        naturaleza_cuenta: $("#naturaleza_cuenta").val(),
        naturaleza_ingresos: $("#naturaleza_ingresos").val(),
        naturaleza_egresos: $("#naturaleza_egresos").val(),
        naturaleza_ventas: $("#naturaleza_ventas").val(),
        naturaleza_compras: $("#naturaleza_compras").val(),
        exige_nit: $("#exige_nit:checked").val() ? 1 : 0,
        exige_documento_referencia: $("#exige_documento_referencia:checked").val() ? 1 : 0,
        exige_concepto: $("#exige_concepto:checked").val() ? 1 : 0,
        exige_centro_costos: $("#exige_centro_costos:checked").val() ? 1 : 0,
    }

    $.ajax({
        url: base_url + 'plan-cuenta',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPlanCuenta();
            $("#savePlanCuenta").show();
            $("#updatePlanCuenta").hide();
            $("#savePlanCuentaLoading").hide();
            $("#planCuentaFormModal").modal('hide');
            plan_cuentas_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Cuenta actualizada con exito!', true );
        }
    }).fail((err) => {
        $('#savePlanCuenta').hide();
        $('#updatePlanCuenta').show();
        $('#savePlanCuentaLoading').hide();
        agregarToast('error', 'Error al actualizar Cuenta!', errorsMsg);
    });
});

$("#id_padre").on('change', function(e) {
    var data = $(this).select2('data');
    if(data.length){
        $("#text_cuenta_padre").val(data[0].cuenta);
    }
});

function selectItem (idItem) {
    $('#'+idItem).select();
}