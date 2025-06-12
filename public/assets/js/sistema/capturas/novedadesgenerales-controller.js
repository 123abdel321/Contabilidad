var novedades_generales_table = null;
var $comboNitNovedades = null;
var $comboConceptoNovedades = null;

var $comboPeriodoFilter = null;
var $comboNitNovedadesFilter = null;
var $comboConceptoNovedadesFilter = null;

function novedadesgeneralesInit () {

    cargarTablasNovedades();
    cargarSelect2Novedades();
}

function cargarTablasNovedades() {
    novedades_generales_table = $('#novedadesGeneralesTable').DataTable({
        pageLength: 30,
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
            url: base_url + 'novedades-generales',
            data: function ( d ) {
                d.id_empleado = $('#id_empleado_novedades_generales_filter').val(),
                d.id_periodo_pago = $('#id_periodo_pago_novedades_generales_filter').val(),
                d.id_concepto = $('#id_concepto_novedades_generales_filter').val()
            }
        },
        columns: [
            {"data": function (row, type, set){
                if (row.periodo_pago) {
                    return `${row.periodo_pago.fecha_inicio_periodo} al ${row.periodo_pago.fecha_fin_periodo}`;
                }
                return 'SIN PERIODO';
            }},
            {"data": function (row, type, set){
                if (row.empleado) {
                    return `${row.empleado.numero_documento} - ${row.empleado.nombre_completo}`;
                }
                return 'SIN EMPLEADO';
            }},
            {"data": function (row, type, set){
                if (row.concepto) {
                    return `${row.concepto.codigo} - ${row.concepto.nombre}`;
                }
                return 'SIN CONCEPTO';
            }},
            {"data": function (row, type, set){
                if (row.tipo_unidad == 0) {
                    return 'HORAS';
                }
                if (row.tipo_unidad == 1) {
                    return 'DÍAS';
                }
                if (row.tipo_unidad == 3) {
                    return 'VALOR FIJO';
                }
                return '';
            }},
            {"data":'unidades'},
            {"data":'valor', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                return parseFloat(row.porcentaje);
            }},
            {"data":'base', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observacion'},
            {"data":'fecha_inicio'},
            {"data":'fecha_fin'},
            {"data":'hora_inicio'},
            {"data":'hora_fin'},
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
                    if (editarNovedadesGenerales) html+= `<span id="editnovedadesgenerales_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-novedadesgenerales" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    if (eliminarNovedadesGenerales) html+= `<span id="deletenovedadesgenerales_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-novedadesgenerales" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    if (novedades_generales_table) {
        novedades_generales_table.on('click', '.edit-novedadesgenerales', function() {

            clearFormNovedadesGenerales();

            $("#textCreateNovedadesGenerales").hide();
            $("#textEditarNovedadesGenerales").show();
            $("#saveNovedadesGeneralesLoading").hide();
            $("#updateNovedadesGenerales").show();
            $("#saveNovedadesGenerales").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, novedades_generales_table);

            $("#id_novedades_generales_up").val(data.id);

            if(data.empleado){
                var dataEmpleado = {
                    id: data.empleado.id,
                    text: data.empleado.numero_documento + ' - ' + data.empleado.nombre_completo
                };
                var newOption = new Option(dataEmpleado.text, dataEmpleado.id, false, false);
                $comboNitNovedades.append(newOption).val(dataEmpleado.id).trigger('change');
            }

            if(data.concepto){
                var dataConcepto = {
                    id: data.concepto.id,
                    text: data.concepto.codigo + ' - ' + data.concepto.nombre,
                };
                var newOption = new Option(dataConcepto.text, dataConcepto.id, false, false);
                $comboConceptoNovedades.append(newOption).val(dataConcepto.id).trigger('change');
            }

            $("#unidades_novedades_generales").val(data.unidades);
            $("#valor_novedades_generales").val(data.valor);
            $("#porcentaje_novedades_generales").val(data.porcentaje);
            $("#fecha_desde_novedades_generales").val(data.fecha_inicio);
            $("#fecha_hasta_novedades_generales").val(data.fecha_fin);
            $("#hora_desde_novedades_generales").val(data.hora_inicio);
            $("#hora_hasta_novedades_generales").val(data.hora_fin);

            mostrarDatosNovedadesGenerales(data.concepto);

            $("#novedadesGeneralesFormModal").modal('show');
        });

        novedades_generales_table.on('click', '.drop-novedadesgenerales', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, novedades_generales_table);

            Swal.fire({
                title: `Eliminar novedad: ${data.concepto.nombre} <br> Empleado: ${data.empleado.nombre_completo}?`,
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
                        url: base_url + 'novedades-generales',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            novedades_generales_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Contrato eliminado con exito!', true );
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

    novedades_generales_table.ajax.reload();
}

function cargarSelect2Novedades() {
    $comboNitNovedades = $('#id_empleado_novedades_generales').select2({
        theme: 'bootstrap-5',
        delay: 250,
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
            url: 'api/nit/empleado-activo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboConceptoNovedades = $('#id_concepto_novedades_generales').select2({
        theme: 'bootstrap-5',
        delay: 250,
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
            url: 'api/conceptos-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboNitNovedadesFilter = $('#id_empleado_novedades_generales_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un empleado a filtrar",
        allowClear: true,
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
            url: 'api/nit/empleado-activo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboPeriodoFilter = $('#id_periodo_pago_novedades_generales_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un periodo a filtrar",
        allowClear: true,
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
            url: 'api/periodos-pagos-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboConceptoNovedadesFilter = $('#id_concepto_novedades_generales_filter').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un concepto a filtrar",
        allowClear: true,
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
            url: 'api/conceptos-combo',
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

function clearFormNovedadesGenerales() {
    $("#textCreateNovedadesGenerales").show();
    $("#textEditarNovedadesGenerales").hide();
    $("#saveNovedadesGenerales").show();
    $("#updateNovedadesGenerales").hide();
    $("#saveNovedadesGeneralesLoading").hide();

    dateNow = new Date();
    const fechaActual = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    //DIVS
    const unidadesDiv = $("#div-unidades_novedades_generales");
    const valorDiv = $("#div-valor_novedades_generales");
    const porcentajeDiv = $("#div-porcentaje_novedades_generales");
    const horaDesdeDiv = $("#div-hora_desde_novedades_generales");
    const horaHastaDiv = $("#div-hora_hasta_novedades_generales");
    //INPUTS
    const fechaDesdeInput = $("#fecha_desde_novedades_generales");
    const fechaHastaInput = $("#fecha_hasta_novedades_generales");
    const horaDesdeInput = $("#hora_desde_novedades_generales");
    const horaHastaInput = $("#hora_hasta_novedades_generales");

    $("#id_empleado_novedades_generales").val("").trigger('change');
    $("#id_concepto_novedades_generales").val("").trigger('change');  
    $("#valor_novedades_generales").val("0");
    $("#porcentaje_novedades_generales").val("0");
    $("#unidades_novedades_generales").val("0");
    fechaDesdeInput.val(fechaActual);
    fechaHastaInput.val(fechaActual);
    horaDesdeInput.val("");
    horaHastaInput.val("");

    unidadesDiv.hide();
    valorDiv.hide();
    porcentajeDiv.hide();
    horaDesdeDiv.hide();
    horaHastaDiv.hide();
    fechaDesdeInput.prop("required", false);
    fechaHastaInput.prop("required", false);
    horaDesdeInput.prop("required", false);
    horaHastaInput.prop("required", false);
}

function mostrarDatosNovedadesGenerales(novedadGeneral) {
    const fechaHora = ['heds','hens','hrns','heddfs','hrddfs','hendfs','hrndfs'];
    const fecha = ['vacaciones_comunes','vacaciones_compensadas','incapacidades','licencia_mp','licencia_r','licencia_nr','huelgas_legales'];

    //DIVS
    const unidadesDiv = $("#div-unidades_novedades_generales");
    const valorDiv = $("#div-valor_novedades_generales");
    const porcentajeDiv = $("#div-porcentaje_novedades_generales");
    const fechaDesdeDiv = $("#div-fecha_desde_novedades_generales");
    const fechaHastaDiv = $("#div-fecha_hasta_novedades_generales");
    const horaDesdeDiv = $("#div-hora_desde_novedades_generales");
    const horaHastaDiv = $("#div-hora_hasta_novedades_generales");
    const unidadesLabel = $("#text-unidades_novedades_generales");
    //INPUTS
    const fechaDesdeInput = $("#fecha_desde_novedades_generales");
    const fechaHastaInput = $("#fecha_hasta_novedades_generales");
    const horaDesdeInput = $("#hora_desde_novedades_generales");
    const horaHastaInput = $("#hora_hasta_novedades_generales");

    if (novedadGeneral.unidad == 0) {
        unidadesDiv.show();
        unidadesLabel.html("Horas <span style='color: red'>*</span>");
        $("#valor_novedades_generales").prop("required", true);
    }

    if (novedadGeneral.unidad == 1) {
        unidadesDiv.show();
        unidadesLabel.html("Días <span style='color: red'>*</span>");
        $("#valor_novedades_generales").prop("required", true);
    }

    if (novedadGeneral.unidad == 2) {
        valorDiv.show();
        $("#unidades_novedades_generales").prop("required", true);
    }

    if (novedadGeneral.porcentaje) {
        porcentajeDiv.show();
        $("#porcentaje_novedades_generales").val(parseFloat(novedadGeneral.porcentaje));
    }

    if (fechaHora.includes(novedadGeneral.tipo_concepto)) {
        fechaDesdeDiv.show();
        fechaHastaDiv.show();
        horaDesdeDiv.show();
        horaHastaDiv.show();
        fechaDesdeInput.prop("required", true);
        fechaHastaInput.prop("required", true);
        horaDesdeInput.prop("required", true);
        horaHastaInput.prop("required", true);
    } else if (fecha.includes(novedadGeneral.tipo_concepto)) {
        fechaDesdeDiv.show();
        fechaHastaDiv.show();
        horaDesdeDiv.hide();
        horaHastaDiv.hide();
        fechaDesdeInput.prop("required", true);
        fechaHastaInput.prop("required", true);
    } else {
        horaDesdeDiv.hide();
        horaHastaDiv.hide();
    }
}

$(document).on('click', '#createNovedadGeneral', function () {
    clearFormNovedadesGenerales();
    $("#novedadesGeneralesFormModal").modal('show');
});

$(document).on('change', '#id_concepto_novedades_generales', function () {
    const novedadGeneral = producto = $('#id_concepto_novedades_generales').select2('data')[0];
    if (!novedadGeneral) return;

    mostrarDatosNovedadesGenerales(novedadGeneral);
});

$(document).on('click', '#saveNovedadesGenerales', function () {
    const form = document.querySelector('#NovedadesGeneralesForm');
    const formData = new FormData(form);

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        const firstInvalidInput = form.querySelector(':invalid');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
        }
        return;
    }

    const formDataObj = Object.fromEntries(formData.entries());

    $("#saveNovedadesGenerales").hide();
    $("#updateNovedadesGenerales").hide();
    $("#saveNovedadesGeneralesLoading").show();

    $.ajax({
        url: base_url + 'novedades-generales',
        method: 'POST',
        data: JSON.stringify(formDataObj),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormNovedadesGenerales();
            $("#saveNovedadesGenerales").show();
            $("#saveNovedadesGeneralesLoading").hide();
            $("#novedadesGeneralesFormModal").modal('hide');
            novedades_generales_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Novedad general creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveNovedadesGenerales').show();
        $('#saveNovedadesGeneralesLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#updateNovedadesGenerales', function () {
    const form = document.querySelector('#NovedadesGeneralesForm');
    const formData = new FormData(form);

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        const firstInvalidInput = form.querySelector(':invalid');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
        }
        return;
    }

    const formDataObj = Object.fromEntries(formData.entries());

    $("#saveNovedadesGenerales").hide();
    $("#updateNovedadesGenerales").hide();
    $("#saveNovedadesGeneralesLoading").show();

    $.ajax({
        url: base_url + 'novedades-generales',
        method: 'PUT',
        data: JSON.stringify(formDataObj),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormNovedadesGenerales();
            $("#saveNovedadesGenerales").show();
            $("#saveNovedadesGeneralesLoading").hide();
            $("#novedadesGeneralesFormModal").modal('hide');
            novedades_generales_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Novedad general creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveNovedadesGenerales').show();
        $('#saveNovedadesGeneralesLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('change', '#id_empleado_novedades_generales_filter', function () {
    novedades_generales_table.ajax.reload();
});

$(document).on('change', '#id_periodo_pago_novedades_generales_filter', function () {
    novedades_generales_table.ajax.reload();
});

$(document).on('change', '#id_concepto_novedades_generales_filter', function () {
    novedades_generales_table.ajax.reload();
});