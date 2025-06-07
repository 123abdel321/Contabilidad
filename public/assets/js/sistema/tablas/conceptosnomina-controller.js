var conceptos_nomina_table = null;
var $cuentaAdministrativos = null;
var $cuentaOperativos = null;
var $cuentaVentas = null;
var $cuentaOtros = null;
var $conceptoPorcentaje = null;

function conceptosnominaInit() {

    cargarTablasConceptosNomina();
    cargarSelect2ConceptosNomina();

    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });

    $('.water').hide();
}

function cargarTablasConceptosNomina() {
    conceptos_nomina_table = $('#conceptosNominaTable').DataTable({
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
            url: base_url + 'conceptos-nomina',
        },
        columns: [
            {"data": function (row, type, set){  
                if (row.tipo_concepto) {
                    return row.tipo_concepto_nombre
                }
                return '';
            }},
            {"data":'codigo'},
            {"data":'nombre'},
            {"data": function (row, type, set){  
                if (row.cuenta_administrativos) {
                    return `${row.cuenta_administrativos.cuenta} - ${row.cuenta_administrativos.nombre}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.cuenta_operativos) {
                    return `${row.cuenta_operativos.cuenta} - ${row.cuenta_operativos.nombre}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.cuenta_ventas) {
                    return `${row.cuenta_ventas.cuenta} - ${row.cuenta_ventas.nombre}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.cuenta_otros) {
                    return `${row.cuenta_otros.cuenta} - ${row.cuenta_otros.nombre}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                return parseInt(row.porcentaje);
            }},
            {"data":'id_concepto_porcentaje'},
            {"data": function (row, type, set){
                if (row.unidad == '0') return 'Horas';
                if (row.unidad == '1') return 'Días';
                if (row.unidad == '2') return 'Valor';
            }},
            { data: "valor_mensual", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                if (row.concepto_fijo) return 'Fijo';
                return 'Manual';
            }},
            {"data": function (row, type, set){
                if (row.base_retencion) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_sena) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_icbf) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_caja_compensacion) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_salud) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_pension) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_arl) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_vacacion) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_prima) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_cesantia) return 'Si';
                return 'No';
            }},
            {"data": function (row, type, set){
                if (row.base_interes_cesantia) return 'Si';
                return 'No';
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
                    if (editarConceptosNomina) html+= `<span id="editconceptosNomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-conceptos_nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    if (eliminarConceptosNomina) html+= `<span id="deleteconceptosNomina_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-conceptos_nomina" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    if (conceptos_nomina_table) {
        conceptos_nomina_table.on('click', '.drop-conceptos_nomina', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, conceptos_nomina_table);

            Swal.fire({
                title: `Eliminar concepto nomina: ${data.nombre}?`,
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
                        url: base_url + 'conceptos-nomina',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            conceptos_nomina_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Conceptos de nomina eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        conceptos_nomina_table.on('click', '.edit-conceptos_nomina', function() {

            $("#textConceptosNominaCreate").hide();
            $("#textConceptosNominaUpdate").show();
            $("#saveConceptosNominaLoading").hide();
            $("#updateConceptosNomina").show();
            $("#saveConceptosNomina").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, conceptos_nomina_table);

            $("#id_conceptos_nomina_up").val(data.id);
            $("#tipo_concepto_nomina").val(data.tipo_concepto).trigger('change');
            $("#codigo_concepto_nomina").val(data.codigo);
            $("#nombre_concepto_nomina").val(data.nombre);

            $("#id_cuenta_administrativos_concepto_nomina").val(data.id_cuenta_administrativos).trigger('change');
            $("#id_cuenta_operativos_concepto_nomina").val(data.id_cuenta_operativos).trigger('change');
            $("#id_cuenta_ventas_concepto_nomina").val(data.id_cuenta_ventas).trigger('change');
            $("#id_cuenta_otros_concepto_nomina").val(data.id_cuenta_otros).trigger('change');

            if(data.cuenta_administrativos){
                var dataCuenta = {
                    id: data.cuenta_administrativos.id,
                    text: data.cuenta_administrativos.cuenta + ' - ' + data.cuenta_administrativos.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaAdministrativos.append(newOption).trigger('change');
                $cuentaAdministrativos.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_operativos){
                var dataCuenta = {
                    id: data.cuenta_operativos.id,
                    text: data.cuenta_operativos.cuenta + ' - ' + data.cuenta_operativos.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaOperativos.append(newOption).trigger('change');
                $cuentaOperativos.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_ventas){
                var dataCuenta = {
                    id: data.cuenta_ventas.id,
                    text: data.cuenta_ventas.cuenta + ' - ' + data.cuenta_ventas.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaVentas.append(newOption).trigger('change');
                $cuentaVentas.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_otros){
                var dataCuenta = {
                    id: data.cuenta_otros.id,
                    text: data.cuenta_otros.cuenta + ' - ' + data.cuenta_otros.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $cuentaOtros.append(newOption).trigger('change');
                $cuentaOtros.val(dataCuenta.id).trigger('change');
            }

            // $("#id_concepto_porcentaje_concepto_nomina").val(data.id_cuenta_otros).trigger('change');
            $("#unidad_concepto_nomina").val(data.unidad).trigger('change');
            $("#porcentaje_concepto_nomina").val(parseInt(data.porcentaje));
            $("#valor_mensual_concepto_nomina").val(data.valor_mensual);
            $("#concepto_fijo_concepto_nomina").val(data.concepto_fijo).trigger('change');

            const basesNomina = [
                'base_retencion',
                'base_sena',
                'base_icbf',
                'base_caja_compensacion',
                'base_salud',
                'base_pension',
                'base_arl',
                'base_vacacion',
                'base_prima',
                'base_cesantia',
                'base_interes_cesantia',
            ];

            basesNomina.forEach(base => {
                const checkboxId = `${base}_conceptos_nomina`;
                const isChecked = data[base] ? true : false;
                $(`#${checkboxId}`).prop('checked', isChecked);
            });
            
        
            $("#conceptosNominaFormModal").modal('show');

            setTimeout(function(){
                document.getElementById('nombre_periodo').focus();
            },500);
        });
    }
    conceptos_nomina_table.ajax.reload();
}

function cargarSelect2ConceptosNomina() {
    $cuentaAdministrativos = $('#id_cuenta_administrativos_concepto_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptosNominaForm'),
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
    $cuentaOperativos = $('#id_cuenta_operativos_concepto_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptosNominaForm'),
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
    $cuentaVentas = $('#id_cuenta_ventas_concepto_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptosNominaForm'),
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
    $cuentaOtros = $('#id_cuenta_otros_concepto_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#conceptosNominaForm'),
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

function clearFormConceptosNomina() {
    $("#textConceptosNominaCreate").show();
    $("#textConceptosNominaUpdate").hide();
    $("#saveConceptosNomina").show();
    $("#updateConceptosNomina").hide();
    $("#saveConceptosNominaLoading").hide();

    $("#id_conceptos_nomina_up").val('');
    $("#tipo_concepto_nomina").val('').trigger('change');
    $("#codigo_concepto_nomina").val('');
    $("#nombre_concepto_nomina").val('');
    $("#id_cuenta_administrativos_concepto_nomina").val('').trigger('change');
    $("#id_cuenta_operativos_concepto_nomina").val('').trigger('change');
    $("#id_cuenta_ventas_concepto_nomina").val('').trigger('change');
    $("#id_cuenta_otros_concepto_nomina").val('').trigger('change');
    $("#porcentaje_concepto_nomina").val(0);
    $("#id_concepto_porcentaje_concepto_nomina").val('').trigger('change');
    $("#unidad_concepto_nomina").val('').trigger('change');
    $("#valor_mensual_concepto_nomina").val(0);
    $("#concepto_fijo_concepto_nomina").val('').trigger('change');
    
    $("#base_retencion_conceptos_nomina").prop('checked', false);
    $("#base_sena_conceptos_nomina").prop('checked', false);
    $("#base_icbf_conceptos_nomina").prop('checked', false);
    $("#base_caja_compensacion_conceptos_nomina").prop('checked', false);
    $("#base_salud_conceptos_nomina").prop('checked', false);
    $("#base_pension_conceptos_nomina").prop('checked', false);
    $("#base_arl_conceptos_nomina").prop('checked', false);
    $("#base_vacacion_conceptos_nomina").prop('checked', false);
    $("#base_prima_conceptos_nomina").prop('checked', false);
    $("#base_cesantia_conceptos_nomina").prop('checked', false);
    $("#base_interes_cesantia_conceptos_nomina").prop('checked', false);
}

function enterPeriodo(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        const id_periodo = $("#id_periodos_up").val();
        if (id_periodo) actualizarConceptosNomina();
        else  guardarConceptosNomina();
    }
}

function guardarConceptosNomina() {
    var form = document.querySelector('#conceptosNominaForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveConceptosNomina").hide();
    $("#updateConceptosNomina").hide();
    $("#saveConceptosNominaLoading").show();
    
    let data = getDataConceptosNomina();

    $.ajax({
        url: base_url + 'conceptos-nomina',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormConceptosNomina();
            $("#saveConceptosNomina").show();
            $("#saveConceptosNominaLoading").hide();
            $("#conceptosNominaFormModal").modal('hide');
            conceptos_nomina_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Concepto nomina creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveConceptosNomina').show();
        $('#saveConceptosNominaLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getDataConceptosNomina() {
    return {
        id: $("#id_conceptos_nomina_up").val(),
        tipo_concepto: $("#tipo_concepto_nomina").val(),
        codigo: $("#codigo_concepto_nomina").val(),
        nombre: $("#nombre_concepto_nomina").val(),
        id_cuenta_administrativos: $("#id_cuenta_administrativos_concepto_nomina").val(),
        id_cuenta_operativos: $("#id_cuenta_operativos_concepto_nomina").val(),
        id_cuenta_ventas: $("#id_cuenta_ventas_concepto_nomina").val(),
        id_cuenta_otros: $("#id_cuenta_otros_concepto_nomina").val(),
        porcentaje: $("#porcentaje_concepto_nomina").val(),
        id_concepto_porcentaje: $("#id_concepto_porcentaje_concepto_nomina").val(),
        unidad: $("#unidad_concepto_nomina").val(),
        valor_mensual: $("#valor_mensual_concepto_nomina").val(),
        concepto_fijo: $("#concepto_fijo_concepto_nomina").val(),
        base_retencion: $("input[type='checkbox']#base_retencion_conceptos_nomina").is(':checked') ? 1 : 0,
        base_sena: $("input[type='checkbox']#base_sena_conceptos_nomina").is(':checked') ? 1 : 0,
        base_icbf: $("input[type='checkbox']#base_icbf_conceptos_nomina").is(':checked') ? 1 : 0,
        base_caja_compensacion: $("input[type='checkbox']#base_caja_compensacion_conceptos_nomina").is(':checked') ? 1 : 0,
        base_salud: $("input[type='checkbox']#base_salud_conceptos_nomina").is(':checked') ? 1 : 0,
        base_pension: $("input[type='checkbox']#base_pension_conceptos_nomina").is(':checked') ? 1 : 0,
        base_arl: $("input[type='checkbox']#base_arl_conceptos_nomina").is(':checked') ? 1 : 0,
        base_vacacion: $("input[type='checkbox']#base_vacacion_conceptos_nomina").is(':checked') ? 1 : 0,
        base_prima: $("input[type='checkbox']#base_prima_conceptos_nomina").is(':checked') ? 1 : 0,
        base_cesantia: $("input[type='checkbox']#base_cesantia_conceptos_nomina").is(':checked') ? 1 : 0,
        base_interes_cesantia: $("input[type='checkbox']#base_interes_cesantia_conceptos_nomina").is(':checked') ? 1 : 0,
    }
}

function actualizarConceptosNomina() {
    var form = document.querySelector('#conceptosNominaForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveConceptosNomina").hide();
    $("#updateConceptosNomina").hide();
    $("#saveConceptosNominaLoading").show();

    let data = getDataConceptosNomina();

    $.ajax({
        url: base_url + 'conceptos-nomina',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormConceptosNomina();
            $("#saveConceptosNomina").show();
            $("#saveConceptosNominaLoading").hide();
            $("#conceptosNominaFormModal").modal('hide');
            conceptos_nomina_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Periodo actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#updateConceptosNomina').show();
        $('#saveConceptosNominaLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
}

$(document).on('click', '#createConceptosNomina', function () {
    clearFormConceptosNomina();
    $("#conceptosNominaFormModal").modal('show');
    setTimeout(function(){
        document.getElementById('nombre_periodo').focus();
    },500);
});

$(document).on('click', '#saveConceptosNomina', function () {
    guardarConceptosNomina();
});

$(document).on('click', '#updateConceptosNomina', function () {
    actualizarConceptosNomina();
});0