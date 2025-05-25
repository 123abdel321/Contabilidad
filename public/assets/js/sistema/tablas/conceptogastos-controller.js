var conceptos_gastos_table = null;
//CUENTAS
var $comboCuentaGasto = null;
var $comboCuentaGastoRetencion = null;
var $comboCuentaGastoRetencionDeclarante = null;
var $comboCuentaGastoIva = null;
var $comboCuentaGastoReteIca = null;

function conceptogastosInit() {

    conceptos_gastos_table = $('#conceptoGastosTable').DataTable({
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
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'concepto-gasto',

        },
        columns: [
            {"data":'codigo'},
            {"data":'nombre'},
            {
                "data": function (row, type, set){
                    return getCuentaDataFormatConceptoGastos(row.cuenta_gasto);
                }
            },
            {
                "data": function (row, type, set){
                    return getCuentaDataFormatConceptoGastos(row.cuenta_iva);
                }
            },
            {
                "data": function (row, type, set){
                    return getCuentaDataFormatConceptoGastos(row.cuenta_retencion);
                }
            },
            {
                "data": function (row, type, set){
                    return getCuentaDataFormatConceptoGastos(row.cuenta_retencion_declarante);
                }
            },
            {
                "data": function (row, type, set){
                    return getCuentaDataFormatConceptoGastos(row.cuenta_reteica);
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
                    if (editarConceptoGastos) html+= '<span id="editgastos_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-gastos" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                    if (eliminarConceptoGastos) html+= '<span id="deletegastos_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-gastos" style="margin-bottom: 0rem !important">Eliminar</span>&nbsp';
                    if (crearConceptoGastos) html+= '<span id="duplicategastos_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info duplicar-gastos" style="margin-bottom: 0rem !important">Duplicar</span>';
                    return html;
                }
            }
        ]
    });

    $comboCuentaGasto = $('#id_cuenta_concepto_gasto_gasto').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoGastosFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [1]
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

    $comboCuentaGastoRetencion = $('#id_cuenta_concepto_gasto_retencion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoGastosFormModal'),
        delay: 250,
        allowClear: true,
        placeholder: "Seleccione una Retención",
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [12]
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

    $comboCuentaGastoRetencionDeclarante = $('#id_cuenta_concepto_gasto_retencion_declarante').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoGastosFormModal'),
        delay: 250,
        allowClear: true,
        placeholder: "Seleccione una Retención",
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [12]
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

    $comboCuentaGastoIva = $('#id_cuenta_concepto_gasto_iva').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoGastosFormModal'),
        delay: 250,
        allowClear: true,
        placeholder: "Seleccione una Cuenta",
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            }
        },
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [9]
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

    $comboCuentaGastoReteIca = $('#id_cuenta_concepto_gasto_reteica').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptoGastosFormModal'),
        delay: 250,
        allowClear: true,
        placeholder: "Seleccione una Cuenta",
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            }
        },
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [17]

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

    let column = conceptos_gastos_table.column(7);
    
    if (!editarConceptoGastos && !eliminarConceptoGastos) column.visible(false);
    else column.visible(true);

    if(conceptos_gastos_table) {
        //EDITAR CONCEPTO GASTOS
        conceptos_gastos_table.on('click', '.edit-gastos', function() {

            clearFormConceptoGastos();
            $("#textConceptoGastoCreate").hide();
            $("#textConceptoGastoUpdate").show();
            $("#textConceptoGastoDuplicate").hide();
            $("#saveConceptoGastoLoading").hide();
            $("#updateConceptoGasto").show();
            $("#saveConceptoGasto").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, conceptos_gastos_table);
    
            if(data.cuenta_gasto){
                var dataCuenta = {
                    id: data.cuenta_gasto.id,
                    text: data.cuenta_gasto.cuenta + ' - ' + data.cuenta_gasto.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGasto.append(newOption).trigger('change');
                $comboCuentaGasto.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_retencion){
                var dataCuenta = {
                    id: data.cuenta_retencion.id,
                    text: data.cuenta_retencion.cuenta + ' - ' + data.cuenta_retencion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGastoRetencion.append(newOption).trigger('change');
                $comboCuentaGastoRetencion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_retencion_declarante){
                var dataCuenta = {
                    id: data.cuenta_retencion_declarante.id,
                    text: data.cuenta_retencion_declarante.cuenta + ' - ' + data.cuenta_retencion_declarante.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGastoRetencionDeclarante.append(newOption).trigger('change');
                $comboCuentaGastoRetencionDeclarante.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_iva){
                var dataCuenta = {
                    id: data.cuenta_iva.id,
                    text: data.cuenta_iva.cuenta + ' - ' + data.cuenta_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGastoIva.append(newOption).trigger('change');
                $comboCuentaGastoIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_reteica){
                var dataCuenta = {
                    id: data.cuenta_reteica.id,
                    text: data.cuenta_reteica.cuenta + ' - ' + data.cuenta_reteica.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGastoReteIca.append(newOption).trigger('change');
                $comboCuentaGastoReteIca.val(dataCuenta.id).trigger('change');
            }

            $("#codigo_concepto_gasto").val(data.codigo);
            $("#nombre_concepto_gasto").val(data.nombre);
            $("#id_concepto_gasto_up").val(data.id);

            $("#conceptoGastosFormModal").modal('show');

            setTimeout(function(){
                $('#codigo_concepto_gasto').focus();
                $('#codigo_concepto_gasto').select();
            },50);
        });
        //DOBLE CLICK EDITAR
        conceptos_gastos_table.on('dblclick', 'tr', function () {
            var data = conceptos_gastos_table.row(this).data();
            if (data) {
                document.getElementById("editgastos_"+data.id).click();
            }
        });
        //ELIMIAR CONCEPTOS GASTOS
        conceptos_gastos_table.on('click', '.drop-gastos', function() {

            var trPlanCuenta = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, conceptos_gastos_table);

            Swal.fire({
                title: 'Eliminar concepto de gasto: '+data.nombre+'?',
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
                        url: base_url + 'concepto-gasto',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            conceptos_gastos_table.row(trPlanCuenta).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Concepto gasto eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        //DUPLICAR CONCEPTOS
        conceptos_gastos_table.on('click', '.duplicar-gastos', function() {

            clearFormConceptoGastos();
            $("#textConceptoGastoCreate").hide();
            $("#textConceptoGastoUpdate").hide();
            $("#textConceptoGastoDuplicate").show();
            $("#saveConceptoGastoLoading").hide();
            $("#updateConceptoGasto").hide();
            $("#saveConceptoGasto").show();

            var id = this.id.split('_')[1];
            var data = getDataById(id, conceptos_gastos_table);
    
            if(data.cuenta_gasto){
                var dataCuenta = {
                    id: data.cuenta_gasto.id,
                    text: data.cuenta_gasto.cuenta + ' - ' + data.cuenta_gasto.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGasto.append(newOption).trigger('change');
                $comboCuentaGasto.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_retencion){
                var dataCuenta = {
                    id: data.cuenta_retencion.id,
                    text: data.cuenta_retencion.cuenta + ' - ' + data.cuenta_retencion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGastoRetencion.append(newOption).trigger('change');
                $comboCuentaGastoRetencion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_retencion_declarante){
                var dataCuenta = {
                    id: data.cuenta_retencion_declarante.id,
                    text: data.cuenta_retencion_declarante.cuenta + ' - ' + data.cuenta_retencion_declarante.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGastoRetencionDeclarante.append(newOption).trigger('change');
                $comboCuentaGastoRetencionDeclarante.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_iva){
                var dataCuenta = {
                    id: data.cuenta_iva.id,
                    text: data.cuenta_iva.cuenta + ' - ' + data.cuenta_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaGastoIva.append(newOption).trigger('change');
                $comboCuentaGastoIva.val(dataCuenta.id).trigger('change');
            }

            $("#codigo_concepto_gasto").val('');
            $("#nombre_concepto_gasto").val('');
            $("#id_concepto_gasto_up").val('');

            $("#conceptoGastosFormModal").modal('show');

            document.getElementById('conceptoGastosFormModal').click();
            
            setTimeout(function(){
                $('#codigo_concepto_gasto').focus();
                $('#codigo_concepto_gasto').select();
            },20);
        });
    }

    $("#codigo_concepto_gasto").on('keydown', function(event) {
        if(event.keyCode == 13){
            event.preventDefault();
            setTimeout(function(){
                $('#nombre_concepto_gasto').focus();
                $('#nombre_concepto_gasto').select();
            },10);
        }
    });

    $('.water').hide();
    conceptos_gastos_table.ajax.reload();
}

function getCuentaDataFormatConceptoGastos(cuenta) {
    let cuentaFormat = ``;
    const cuentaData = cuenta;

    if (!cuentaData) {
        return cuentaFormat;
    }

    cuentaFormat = `<b style="font-weight: 600;">${cuentaData.cuenta}</b> - ${cuentaData.nombre}`;
    
    if (cuentaData.impuesto) {
        const porcentaje = cuentaData.impuesto.porcentaje;
        const base = new Intl.NumberFormat('ja-JP').format(cuentaData.impuesto.base);
        cuentaFormat+= ` - <b style="font-weight: 600;">PORCENTAJE:</b> ${porcentaje} - <b style="font-weight: 600;">BASE:</b> ${base}`;
    }

    return cuentaFormat;
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputConceptoGastos").on("input", function (e) {
    conceptos_gastos_table.context[0].jqXHR.abort();
    $('#conceptoGastosTable').DataTable().search($("#searchInputConceptoGastos").val()).draw();
});

$(document).on('click', '#createConceptoGasto', function () {
    clearFormConceptoGastos();
    $("#updateConceptoGasto").hide();
    $("#saveConceptoGasto").show();
    $("#conceptoGastosFormModal").modal('show');

    setTimeout(function(){
        $('#codigo_concepto_gasto').focus();
        $('#codigo_concepto_gasto').select();
    },10);
});

function clearFormConceptoGastos(){
    $("#textConceptoGastoCreate").show();
    $("#textConceptoGastoUpdate").hide();
    $("#textConceptoGastoDuplicate").hide();

    $("#saveConceptoGasto").hide();
    $("#id_concepto_gasto_up").val('');
    $("#codigo_concepto_gasto").val('');
    $("#nombre_concepto_gasto").val('');

    $comboCuentaGasto.val('').trigger('change');
    $comboCuentaGastoRetencion.val('').trigger('change');
    $comboCuentaGastoRetencionDeclarante.val('').trigger('change');
    $comboCuentaGastoIva.val('').trigger('change');
}

$(document).on('click', '#saveConceptoGasto', function () {
    var form = document.querySelector('#conceptoGastosForm');

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $("#saveConceptoGastoLoading").show();
    $("#updateConceptoGasto").hide();
    $("#saveConceptoGasto").hide();

    let data = {
        codigo: $('#codigo_concepto_gasto').val(),
        nombre: $('#nombre_concepto_gasto').val(),
        id_cuenta_gasto: $('#id_cuenta_concepto_gasto_gasto').val(),
        id_cuenta_retencion: $('#id_cuenta_concepto_gasto_retencion').val(),
        id_cuenta_retencion_declarante: $('#id_cuenta_concepto_gasto_retencion_declarante').val(),
        id_cuenta_reteica: $('#id_cuenta_concepto_gasto_reteica').val(),
        id_cuenta_iva: $('#id_cuenta_concepto_gasto_iva').val(),
    };

    $.ajax({
        url: base_url + 'concepto-gasto',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormConceptoGastos();
            $("#saveConceptoGasto").show();
            $("#updateConceptoGasto").hide();
            $("#saveConceptoGastoLoading").hide();
            $("#conceptoGastosFormModal").modal('hide');
            conceptos_gastos_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Concepto de gasto creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveConceptoGasto').show();
        $('#saveConceptoGastoLoading').hide();
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

$(document).on('click', '#updateConceptoGasto', function () {
    var form = document.querySelector('#conceptoGastosForm');

    $("#saveConceptoGastoLoading").show();
    $("#updateConceptoGasto").hide();
    $("#saveConceptoGasto").hide();

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id: $("#id_concepto_gasto_up").val(),
        codigo: $('#codigo_concepto_gasto').val(),
        nombre: $('#nombre_concepto_gasto').val(),
        id_cuenta_gasto: $('#id_cuenta_concepto_gasto_gasto').val(),
        id_cuenta_retencion: $('#id_cuenta_concepto_gasto_retencion').val(),
        id_cuenta_retencion_declarante: $('#id_cuenta_concepto_gasto_retencion_declarante').val(),
        id_cuenta_reteica: $('#id_cuenta_concepto_gasto_reteica').val(),
        id_cuenta_iva: $('#id_cuenta_concepto_gasto_iva').val(),
    };

    $.ajax({
        url: base_url + 'concepto-gasto',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormConceptoGastos();
            $("#saveConceptoGasto").show();
            $("#updateConceptoGasto").hide();
            $("#saveConceptoGastoLoading").hide();
            $("#conceptoGastosFormModal").modal('hide');
            conceptos_gastos_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Concepto de gasto actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#updateConceptoGasto').show();
        $('#saveConceptoGastoLoading').hide();
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
        agregarToast('error', 'Error al actualizar concepto de gastos', errorsMsg);
    });
});