var vacaciones_table = null;
var $comboEmpleadoVacaciones = null;
var $comboEmpleadoVacacionesFilter = null;
var $comboPeriodoVacacionesFilter = null


function vacacionesInit() {

    cargarTablasVacaciones();
    cargarCombosVacaciones();
    cargarPopoverVacaciones();

    $('.water').hide();
}

function cargarTablasVacaciones() {
    vacaciones_table = $('#vacacionesTable').DataTable({
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
            url: base_url + 'vacaciones',
        },
        columns: [
            { data: function (row, type, set){  
                if (row.empleado) {
                    return `${row.empleado.numero_documento} - ${row.empleado.nombre_completo}`
                }
                return '';
            }},
            { data: function (row, type, set){  
                if (row.metodo) {
                    return 'VARIABLE'
                }
                return 'FIJO';
            }},
            { data:'fecha_inicio' },
            { data:'fecha_fin' },
            { data: "dias_habiles" },
            { data: "dias_no_habiles" },
            { data: "dias_compensados" },
            { data: "promedio_otros", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: "salario_dia", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: "valor_dia_vacaciones", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: "total_disfrutado", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: "total_compensado", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
            { data: function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            { data: function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            { data: function (row, type, set){
                    var html = '';
                    // if (editarVacaciones) html+= `<span id="editvacaciones_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-vacaciones" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    if (eliminarVacaciones) html+= `<span id="deletevacaciones_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-vacaciones" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    if (vacaciones_table) {
        vacaciones_table.on('click', '.drop-vacaciones', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, vacaciones_table);

            Swal.fire({
                title: `Eliminar vacaciones de: ${data.empleado.numero_documento} - ${data.empleado.nombre_completo}?`,
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
                        url: base_url + 'vacaciones',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            vacaciones_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', res.message, true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        var mensaje = err.responseJSON.message;
                        var errorsMsg = arreglarMensajeError(mensaje);
                        agregarToast('error', 'Eliminación errada', errorsMsg);
                    });
                }
            })
        });
        
        vacaciones_table.on('click', '.edit-vacaciones', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, vacaciones_table);

            $("#saveVacaciones").hide();
            $("#updateVacaciones").show();
            $("#saveVacacionesLoading").hide();
            $("#textVacacionesCreate").hide();
            $("#textVacacionesUpdate").show();

            if (data.empleado) {
                var dataEmpleado = {
                    id: data.empleado.id,
                    text: data.empleado.numero_documento + ' - ' + data.empleado.nombre_completo
                };
                var newOption = new Option(dataEmpleado.text, dataEmpleado.id, false, false);
                $comboEmpleadoVacaciones.append(newOption).val(dataEmpleado.id).trigger('change');
            }

            $("#metodo_vacaciones").val(data.metodo).trigger('change');
            $("#dias_habiles_vacaciones").val(data.dias_habiles);
            $("#dias_no_habiles_vacaciones").val(data.dias_no_habiles);
            $("#dias_compensados_vacaciones").val(data.dias_compensados);
            $("#fecha_inicio_vacaciones").val(data.fecha_inicio);
            $("#fecha_fin_vacaciones").val(data.fecha_fin);
            $("#observacion_vacaciones").val(data.observacion);

            $("#salario_dia_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.salario_dia));
            $("#promedio_otros_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.promedio_otros));
            // $("#valor_dia_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.));

            console.log('data: ',data);
        });
    }

    vacaciones_table.ajax.reload();
}

function cargarCombosVacaciones() {

    $comboEmpleadoVacaciones = $('#id_empleado_vacaciones').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#vacacionesFormModal'),
        delay: 250,
        placeholder: "Seleccione un empleado a calcular",
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

    $comboEmpleadoVacacionesFilter = $('#id_empleado_vacaciones_filter').select2({
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

    $comboPeriodoVacacionesFilter = $('#id_periodo_pago_vacaciones_filter').select2({
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
}

function cargarPopoverVacaciones() {
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });
}

function clearFormVacaciones() {
    $("#saveVacaciones").hide();
    $("#textVacacionesCreate").show();
    $("#textVacacionesUpdate").hide();
    $("#saveVacaciones").show();
    $("#updateVacaciones").hide();
    $("#saveVacacionesLoading").hide();

    const dateVacacion = new Date();
    const fecha = dateVacacion.getFullYear()+'-'+("0" + (dateVacacion.getMonth() + 1)).slice(-2)+'-'+("0" + (dateVacacion.getDate())).slice(-2);

    $("#id_vacaciones_up").val("");
    $("#json_detalle_vacaciones").val("");
    $("#id_empleado_vacaciones").val('').trigger('change');
    $("#metodo_vacaciones").val('').trigger('change');
    $("#dias_habiles_vacaciones").val('0');
    $("#dias_no_habiles_vacaciones").val('0');
    $("#dias_compensados_vacaciones").val('0');
    $("#fecha_inicio_vacaciones").val(fecha);
    $("#fecha_fin_vacaciones").val(fecha);
    $("#observacion_vacaciones").val('');
    $("#salario_dia_vacaciones").val(0);
    $("#promedio_otros_vacaciones").val(0);
    $("#valor_dia_vacaciones").val(0);
    $("#total_disfrutado_vacaciones").val(0);
    $("#total_compensado_vacaciones").val(0);
}

function focusOutCalcularFechaFin() {
    actualizarFechaFin();
    calcularVacaciones();
}

function enterPressCalcularFechaFin(event) {
    if (event.keyCode != 13) return;
    actualizarFechaFin();
    calcularVacaciones();
}

function actualizarFechaFin() {
    let fecha_inicio = $("#fecha_inicio_vacaciones").val();
    let dias_habiles = parseInt($("#dias_habiles_vacaciones").val()) || 0;
    let dias_no_habiles = parseInt($("#dias_no_habiles_vacaciones").val()) || 0;
    let dias_compensados = parseInt($("#dias_compensados_vacaciones").val()) || 0;

    // Convertir fecha_inicio a objeto Date
    let fechaInicioDate = new Date(fecha_inicio);
    if (isNaN(fechaInicioDate.getTime())) {
        alert("La fecha de inicio no es válida");
        return;
    }

    let totalDias = dias_habiles + dias_no_habiles + dias_compensados - 1;
    let fechaFinDate = new Date(fechaInicioDate);
    fechaFinDate.setDate(fechaFinDate.getDate() + totalDias);

    // Formatear fecha_fin en formato yyyy-mm-dd
    let yyyy = fechaFinDate.getFullYear();
    let mm = (fechaFinDate.getMonth() + 1).toString().padStart(2, '0');
    let dd = fechaFinDate.getDate().toString().padStart(2, '0');
    let fechaFinString = `${yyyy}-${mm}-${dd}`;

    $("#fecha_fin_vacaciones").val(fechaFinString);
}

function calcularVacaciones() {
    var form = document.querySelector('#vacacionesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveVacaciones").hide();
    $("#json_detalle_vacaciones").val("");
    $("#reloadCalculoVacacionesNormal").hide();
    $("#reloadCalculoVacacionesLoading").show();
    
    $.ajax({
        url: base_url + 'vacaciones-calcular',
        method: 'GET',
        data: {
            id_empleado: $("#id_empleado_vacaciones").val(),
            metodo: $("#metodo_vacaciones").val(),
        },
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#reloadCalculoVacacionesNormal").show();
        $("#reloadCalculoVacacionesLoading").hide();

        $("#total_disfrutado_vacaciones").val(0);
        $("#total_compensado_vacaciones").val(0);

        const valorDiaVacacion = res.data.salario_dia + res.data.promedio_otros;
        const diaCompensado = parseInt($("#dias_compensados_vacaciones").val());
        const sumaDias = parseInt($("#dias_habiles_vacaciones").val()) + parseInt($("#dias_no_habiles_vacaciones").val());

        $("#json_detalle_vacaciones").val(res.data.json_detalle);
        $("#salario_dia_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(res.data.salario_dia));
        $("#promedio_otros_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(res.data.promedio_otros));
        $("#valor_dia_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valorDiaVacacion));

        if(sumaDias > 0) $("#total_disfrutado_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valorDiaVacacion * sumaDias));
        if(diaCompensado > 0) $("#total_compensado_vacaciones").val(new Intl.NumberFormat('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valorDiaVacacion * diaCompensado));

        $("#saveVacaciones").show();

    }).fail((err) => {
        $("#reloadCalculoVacacionesNormal").show();
        $("#reloadCalculoVacacionesLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

$(document).on('click', '#createVacaciones', function () {
    clearFormVacaciones();
    $("#vacacionesFormModal").modal('show');
});

$(document).on('click', '#saveVacaciones', function () {

    const jsonDetalleVacaciones = $("#json_detalle_vacaciones").val();
    if (!jsonDetalleVacaciones) {
        agregarToast('error', 'Sin calculo', 'Es necesario calcular las vacaciones');
    }

    $("#saveVacaciones").hide();
    $("#saveVacacionesLoading").show();

    let data = {
        metodo: $("#metodo_vacaciones").val(),
        id_empleado: $("#id_empleado_vacaciones").val(),
        fecha_inicio: $("#fecha_inicio_vacaciones").val(),
        fecha_fin: $("#fecha_fin_vacaciones").val(),
        dias_habiles: $("#dias_habiles_vacaciones").val(),
        dias_compensados: $("#dias_no_habiles_vacaciones").val(),
        dias_no_habiles: $("#dias_compensados_vacaciones").val(),
        promedio_otros: stringToNumberFloat($("#promedio_otros_vacaciones").val()),
        salario_dia: stringToNumberFloat($("#salario_dia_vacaciones").val()),
        valor_dia_vacaciones: stringToNumberFloat($("#valor_dia_vacaciones").val()),
        total_disfrutado: stringToNumberFloat($("#total_disfrutado_vacaciones").val()),
        total_compensado: stringToNumberFloat($("#total_compensado_vacaciones").val()),
        observacion: $("#observacion_vacaciones").val(),
        json_detalle: $("#json_detalle_vacaciones").val(),
    };

    $.ajax({
        url: base_url + 'vacaciones',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#saveVacaciones").show();
        $("#saveVacacionesLoading").hide();

        vacaciones_table.ajax.reload();
        $("#vacacionesFormModal").modal('hide');
    }).fail((err) => {
        $("#saveVacaciones").show();
        $("#saveVacacionesLoading").hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});