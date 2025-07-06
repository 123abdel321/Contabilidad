var configuracion_provisiones_table = null;
var $cuentaAdministrativosCP = null;
var $cuentaOperativosCP = null;
var $cuentaVentasCP = null;
var $cuentaOtrasCP = null;
var $cuentaXPagarCP = null;

function configuracionprovisionesInit() {

    cargarTablasConfiguracionProvisiones();
    cargarSelect2ConfiguracionProvisiones();
    cargarPopoverGeneral();

    $('.water').hide();
}

function cargarTablasConfiguracionProvisiones() {
    configuracion_provisiones_table = $('#configuracionProvisionesTable').DataTable({
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
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'configuracion-provisiones',
        },
        columns: [
            {"data": function (row, type, set){
                let tipoConfiguracionProvisiones = 'Parafiscal';
                if (row.tipo == 1) tipoConfiguracionProvisiones = 'Seguridad Social';
                if (row.tipo == 2) tipoConfiguracionProvisiones = 'Prestaciones Sociales';
                return tipoConfiguracionProvisiones;
            }},
            {"data":'nombre'},
            {"data":'porcentaje', className: 'dt-body-right'},
            {"data": function (row, type, set){
                if (row.cuenta_administrativos) {
                    return `${row.cuenta_administrativos.cuenta} - ${row.cuenta_administrativos.nombre}`;
                }
                return ``;
            }},
            {"data": function (row, type, set){
                if (row.cuenta_operativos) {
                    return `${row.cuenta_operativos.cuenta} - ${row.cuenta_operativos.nombre}`;
                }
                return ``;
            }},
            {"data": function (row, type, set){
                if (row.cuenta_ventas) {
                    return `${row.cuenta_ventas.cuenta} - ${row.cuenta_ventas.nombre}`;
                }
                return ``;
            }},
            {"data": function (row, type, set){
                if (row.cuenta_otros) {
                    return `${row.cuenta_otros.cuenta} - ${row.cuenta_otros.nombre}`;
                }
                return ``;
            }},
            {"data": function (row, type, set){
                if (row.cuenta_pagar) {
                    return `${row.cuenta_pagar.cuenta} - ${row.cuenta_pagar.nombre}`;
                }
                return ``;
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
                    if (editarConfiguracionProvisiones) html+= `<span id="editconceptosNomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-configuracion_provisiones" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    return html;
                }
            },
        ]
    });

    if (configuracion_provisiones_table) {
        configuracion_provisiones_table.on('click', '.edit-configuracion_provisiones', function() {

            clearFormConfiguracionProvisiones();

            var id = this.id.split('_')[1];
            var data = getDataById(id, configuracion_provisiones_table);
            
            $("#id_configuracion_provisiones_up").val(data.id);
            $("#porcentaje_configuracion_provisiones").val(parseFloat(data.porcentaje));
            $("#textEditarConfiguracionProvisiones").html(data.nombre);

            if(data.cuenta_administrativos){
                var dataCuenta = {
                    id: data.cuenta_administrativos.id,
                    text: data.cuenta_administrativos.cuenta + ' - ' + data.cuenta_administrativos.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaAdministrativosCP.append(newOption).val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_operativos){
                var dataCuenta = {
                    id: data.cuenta_operativos.id,
                    text: data.cuenta_operativos.cuenta + ' - ' + data.cuenta_operativos.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaOperativosCP.append(newOption).val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_ventas){
                var dataCuenta = {
                    id: data.cuenta_ventas.id,
                    text: data.cuenta_ventas.cuenta + ' - ' + data.cuenta_ventas.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaVentasCP.append(newOption).val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_otros){
                var dataCuenta = {
                    id: data.cuenta_otros.id,
                    text: data.cuenta_otros.cuenta + ' - ' + data.cuenta_otros.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaOtrasCP.append(newOption).val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_pagar){
                var dataCuenta = {
                    id: data.cuenta_pagar.id,
                    text: data.cuenta_pagar.cuenta + ' - ' + data.cuenta_pagar.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaXPagarCP.append(newOption).val(dataCuenta.id).trigger('change');
            }

            $("#updateConfiguracionProvisiones").show();
            $("#configuracionProvisionesFormModal").modal('show');
        });
    }

    configuracion_provisiones_table.ajax.reload();
}

function cargarSelect2ConfiguracionProvisiones() {
    $cuentaAdministrativosCP = $('#id_cuenta_administrativos_configuracion_provisiones').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#configuracionProvisionesFormModal'),
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    // id_tipo_cuenta: [16]
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

    $cuentaOperativosCP = $('#id_cuenta_operativos_configuracion_provisiones').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#configuracionProvisionesFormModal'),
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    // id_tipo_cuenta: [16]
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

    $cuentaVentasCP = $('#id_cuenta_venta_configuracion_provisiones').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#configuracionProvisionesFormModal'),
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    // id_tipo_cuenta: [16]
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

    $cuentaOtrasCP = $('#id_cuenta_otros_configuracion_provisiones').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#configuracionProvisionesFormModal'),
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    // id_tipo_cuenta: [16]
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

    $cuentaXPagarCP = $('#id_cuenta_por_pagar_configuracion_provisiones').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#configuracionProvisionesFormModal'),
        delay: 250,
        placeholder: "Seleccione una cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    // id_tipo_cuenta: [16]
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

function clearFormConfiguracionProvisiones() {
    $("#updateConfiguracionProvisiones").hide();
    $("#saveConfiguracionProvisionesLoading").hide();
    $("#textEditarConfiguracionProvisiones").text("");

    $("#porcentaje_configuracion_provisiones").val("0");
    $("#id_cuenta_administrativos_configuracion_provisiones").val('').trigger('change');
    $("#id_cuenta_operativos_configuracion_provisiones").val('').trigger('change');
    $("#id_cuenta_venta_configuracion_provisiones").val('').trigger('change');
    $("#id_cuenta_otros_configuracion_provisiones").val('').trigger('change');
    $("#id_cuenta_por_pagar_configuracion_provisiones").val('').trigger('change');
}

$(document).on('click', '#updateConfiguracionProvisiones', function () {
    const form = document.querySelector('#configuracionProvisionesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        const firstInvalidInput = form.querySelector(':invalid');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
        }
        return;
    }

    $("#updateConfiguracionProvisiones").hide();
    $("#saveConfiguracionProvisionesLoading").show();

    let data = {
        id: $("#id_configuracion_provisiones_up").val(),
        porcentaje: $("#porcentaje_configuracion_provisiones").val(),
        id_cuenta_administrativos: $("#id_cuenta_administrativos_configuracion_provisiones").val(),
        id_cuenta_operativos: $("#id_cuenta_operativos_configuracion_provisiones").val(),
        id_cuenta_venta: $("#id_cuenta_venta_configuracion_provisiones").val(),
        id_cuenta_otros: $("#id_cuenta_otros_configuracion_provisiones").val(),
        id_cuenta_por_pagar: $("#id_cuenta_por_pagar_configuracion_provisiones").val()
    }

    $.ajax({
        url: base_url + 'configuracion-provisiones',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormConfiguracionProvisiones();
            $("#updateConfiguracionProvisiones").show();
            $("#saveConfiguracionProvisionesLoading").hide();
            $("#configuracionProvisionesFormModal").modal('hide');
            configuracion_provisiones_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Configuracion provisionada actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updateConfiguracionProvisiones').show();
        $('#saveConfiguracionProvisionesLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
});