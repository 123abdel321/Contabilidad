
var calendarioReuniones = null;
var reuniones_table = null;
var reuniones_nit_table = null;
var participantes_reuniones_table = null;
var reunion_id = null;

function reunionesInit() {

    initCalendarReuniones();
    initSelect2Reuniones();
    initTablesReuniones();

    $('.water').hide();
}

function initCalendarReuniones() {
    const calendarReuniones = document.getElementById('reuniones-fullcalender');
    calendarioReuniones = new FullCalendar.Calendar(calendarReuniones, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        editable: true,
        droppable: true,
        expandRows: true,
        selectable: true,
        locale: 'es',
        events: {
            url: 'reuniones-event',
            method: 'GET',
            extraParams: function() {
                return {
                    estado: $("#estado_filter_reunion").val() ?? '',
                };
            },
            failure: function() {
                agregarToast('error', 'Actualización errada', 'Error al cargar los eventos!');
            }
        },
        eventDrop: function(info) {
            cambiarRangoDeReunion(info.event);
        },
        eventReceive: function(info) {
            cambiarRangoDeReunion(info.event);
        },
        eventResize: function(info) {
            cambiarRangoDeReunion(info.event);
        },
        select: function(info) {
            seleccionarRangoDeReuniones(info);
        },
        eventClick: function(info) {
            mostrarModalReunion(info.event);
        },
        height: 'auto',
        contentHeight: 'auto',
        expandRows: true,
        buttonText: {
            prev: 'Ant',
            next: 'Sig',
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día',
            list: 'Agenda',
        },
        buttonHints: {
            prev: '$0 antes',
            next: '$0 siguiente',
            today(buttonText) {
                return (buttonText === 'Día') ? 'Hoy' :
                    ((buttonText === 'Semana') ? 'Esta' : 'Este') + ' ' + buttonText.toLocaleLowerCase();
            },
        },
        viewHint(buttonText) {
            return 'Vista ' + (buttonText === 'Semana' ? 'de la' : 'del') + ' ' + buttonText.toLocaleLowerCase();
        },
        weekText: 'Sm',
        weekTextLong: 'Semana',
        allDayText: 'Todo el día',
        moreLinkText: 'más',
        moreLinkHint(eventCnt) {
            return `Mostrar ${eventCnt} eventos más`;
        },
        noEventsText: 'No hay eventos para mostrar',
        navLinkHint: 'Ir al $0',
        closeHint: 'Cerrar',
        timeHint: 'La hora',
        eventHint: 'Evento',
        views: {
            dayGridMonth: {
                titleFormat: { year: 'numeric', month: 'long' }
            },
            timeGridWeek: {
                titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
            },
            timeGridDay: {
                titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
            },
            listWeek: {
                titleFormat: { year: 'numeric', month: 'short', day: 'numeric' }
            }
        },
        datesSet: function(view) {
            setTimeout(function() {
                applyPerfectScrollbar();
            }, 0);
        }
    });
    calendarioReuniones.render();
}

function initSelect2Reuniones() {
    $('#id_nit_reunion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#reunionNitsFormModal'),
        placeholder: "Buscar usuarios...",
        allowClear: true,
        language: {
            noResults: function() {
                return "No se encontraron resultados";
            },
            searching: function() {
                return "Buscando...";
            },
            inputTooShort: function () {
                return "Por favor ingresa al menos 1 carácter";
            }
        },
        ajax: {
            url: 'api/nit/combo-nit',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data.data
                };
            },
            cache: true
        }
    });
}

function cambiarRangoDeTurno(info) {
    var [fechaInicio, horaInicio] = armarFechaReuniones(info.start);
    var [fechaFin, horaFin] = armarFechaReuniones(info.end);

    let data = {
        id: info.id,
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
    }

    $.ajax({
        url: base_url + 'turnos',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            agregarToast('exito', 'Actualización exitosa', 'Evento actualizado con exito!', true);
        }
    }).fail((err) => {

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });

}

function applyPerfectScrollbar() {
    if ($(".fc-scroller-liquid-absolute").length >= 1) new PerfectScrollbar($(".fc-scroller-liquid-absolute")[0]);
    if ($(".fc-scroller").length >= 1) new PerfectScrollbar($(".fc-scroller")[0]);
    if ($(".fc-scroller").length >= 2) new PerfectScrollbar($(".fc-scroller")[1]);
    if ($(".fc-scroller").length >= 3) new PerfectScrollbar($(".fc-scroller")[2]);
}

function initTablesReuniones() {

    reuniones_table = $('#reunionesTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns: {
            left: 0,
            right: 1,
        },
        ajax: {
            type: "GET",
            headers: headers,
            url: base_url + 'reuniones-table',
            data: function(d) {
                d.fecha_desde = $('#fecha_desde_reuniones_filter').val();
                d.fecha_hasta = $('#fecha_hasta_reuniones_filter').val();
                d.estado = $('#estado_reuniones_filter_table').val();
            }
        },
        columns: [
            {"data": 'id'},
            {"data": function(row, type, set) {
                if (row.estado == '0') return `<span class="badge bg-info">PROGRAMADA</span>`;
                if (row.estado == '1') return `<span class="badge bg-primary">EN CURSO</span>`;
                if (row.estado == '2') return `<span class="badge bg-success">FINALIZADA</span>`;
                if (row.estado == '3') return `<span class="badge bg-danger">CANCELADA</span>`;
                return '';
            }},
            {"data": 'titulo'},
            {"data": function(row, type, set) {
                return `<div class="text-wrap width-200">${row.lugar}</div>`;
            }},
            {"data": function(row, type, set) {
                return `<div class="text-wrap width-500">${row.descripcion}</div>`;
            }},
            {"data": 'fecha_hora_inicio'},
            {"data": 'fecha_hora_fin'},
             {"data": function(row, type, set) {
                return row.participantes.length + ' participantes';
            }},
            {"data": 'fecha_creacion'},
            {
                "data": function(row, type, set) {
                    var html = '';
                    if (editarReuniones) html += `<span id="editreuniones_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-info edit-reuniones" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    html += `<span id="participantesreuniones_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success participantes-reuniones" style="margin-bottom: 0rem !important; min-width: 50px;">Participantes</span>`;
                    if (eliminarReuniones) html += `<span id="deletereuniones_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-reuniones" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    participantes_reuniones_table = $('#reunionesNitTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns: {
            left: 0,
            right: 1,
        },
        ajax: {
            
            type: "GET",
            headers: headers,
            url: base_url + 'reuniones-participantes',
            data: function(d) {
                d.id_reunion = reunion_id;
            }
        },
        columns: [
            {"data": function(row, type, set) {
                const nit = row.nit
                if (nit) return nit.numero_documento;
                return '';
            }},
            {"data": function(row, type, set) {
                const nit = row.nit
                if (nit) return nit.nombre_completo;
                return '';
            }},
            {"data": function(row, type, set) {
                const nit = row.nit
                if (nit) return nit.apartamentos;
                return '';
            }},
            {"data": function(row, type, set) {
                const nit = row.nit
                if (nit) return nit.telefono_1;
                return '';
            }},
            {"data": function(row, type, set) {
                const nit = row.nit
                if (nit) return nit.email;
                return '';
            }},
            {
                "data": function(row, type, set) {
                    var html = '';
                    html += `<span id="deletereunionesnit_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-reuniones-nit" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    if (reuniones_table) {
        // EDITAR REUNIONES
        reuniones_table.on('click', '.edit-reuniones', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, reuniones_table);

            cargarReunionParaEdicion(data);
        });
        
        // BORRAR REUNIONES
        reuniones_table.on('click', '.drop-reuniones', function() {
            var trReunion = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, reuniones_table);
            
            Swal.fire({
                title: 'Eliminar reunión: ' + data.titulo + '?',
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
                        url: base_url + 'reuniones',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            reuniones_table.row(trReunion).remove().draw();
                            calendarioReuniones.refetchEvents();
                            agregarToast('exito', 'Eliminación exitosa', 'Reunión eliminada con exito!', true);
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            });
        });

        reuniones_table.on('click', '.participantes-reuniones', function() {
            var id = this.id.split('_')[1];
            mostrarParticipantes(id);
        });
    }

    if (participantes_reuniones_table) {
        participantes_reuniones_table.on('click', '.drop-reuniones-nit', function() {
            var trReunion = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, participantes_reuniones_table);
            
            Swal.fire({
                title: 'Eliminar participante: ' + data.nit.nombre_completo + '?',
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
                        url: base_url + 'reuniones-participantes',
                        method: 'DELETE',
                        data: JSON.stringify({
                            id_nit: id,
                            id_reunion: reunion_id
                        }),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            participantes_reuniones_table.row(trReunion).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Reunión eliminada con exito!', true);
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            });
        });
    }
}

function reloadReuniones() {
    $("#reloadReunionesIconNormal").hide();
    $("#reloadReunionesIconLoading").show();

    setTimeout(function(){
        $("#reloadReunionesIconNormal").show();
        $("#reloadReunionesIconLoading").hide();
    },500);

    calendarioReuniones.refetchEvents();
    reuniones_table.ajax.reload();
}

function clearFormReunion() {
    $("#textReunionCreate").html('Agregar reunión');
    $("#id_reunion_up").val("");
    $("#titulo_reunion").val("");
    $("#descripcion_reunion").val("");
    $("#lugar_reunion").val("");
    $("#fecha_inicio_reunion").val("");
    $("#fecha_fin_reunion").val("");
    $("#hora_inicio_reunion").val("08:00");
    $("#hora_fin_reunion").val("09:00");
    $("#id_nit_reunion").val(null).trigger('change');
}

function cargarReunionParaEdicion(reunion) {
    console.log('reunion: ',reunion);
    $("#id_reunion_up").val(reunion.id);
    $("#titulo_reunion").val(reunion.titulo);
    $("#descripcion_reunion").val(reunion.descripcion);
    $("#lugar_reunion").val(reunion.lugar);
    
    // Formatear fechas
    const fechaInicio = new Date(reunion.fecha_inicio);
    const fechaFin = new Date(reunion.fecha_fin);
    
    $("#fecha_inicio_reunion").val(fechaInicio.toISOString().split('T')[0]);
    $("#fecha_fin_reunion").val(fechaFin.toISOString().split('T')[0]);
    $("#hora_inicio_reunion").val(fechaInicio.toTimeString().substring(0,5));
    $("#hora_fin_reunion").val(fechaFin.toTimeString().substring(0,5));
    
    // Cargar participantes
    for (let index = 0; index < reunion.participantes.length; index++) {
        const participante = reunion.participantes[index];
        participantesSeleccionados.set(participante.id, participante);
    }
    
    $("#textReunionCreate").hide();
    $("#textReunionUpdate").show();
    $("#reunionFormModal").modal('show');
}

function seleccionarRangoDeReuniones(info) {
    clearFormReunion();

    var [fechaInicio, horaInicio] = armarFechaReuniones(info.start);
    var [fechaFin, horaFin] = armarFechaReuniones(info.end);

    $('#fecha_inicio_reunion').val(fechaInicio);
    $('#fecha_fin_reunion').val(fechaFin);

    $('#hora_inicio_reunion').val(horaInicio);
    $('#hora_fin_reunion').val(horaFin);

    $("#reunionFormModal").modal('show');
}

function mostrarModalReunion(info) {

    $.ajax({
        url: base_url + 'reuniones',
        method: 'GET',
        data: {id: info.id},
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        console.log('res: ',res);
        const dataReunion = res.data;
        clearFormReunion();

        const fechaInicioISO = dataReunion.fecha_inicio;
        const fechaInicio = new Date(fechaInicioISO);
        const fechaFinISO = dataReunion.fecha_fin;
        const fechaFin = new Date(fechaFinISO);

        $("#id_reunion_up").val(dataReunion.id);
        $("#titulo_reunion").val(dataReunion.titulo);
        $("#descripcion_reunion").val(dataReunion.descripcion);
        $("#lugar_reunion").val(dataReunion.lugar);
        $("#fecha_inicio_reunion").val(fechaInicio.toLocaleDateString('en-CA'));
        $("#hora_inicio_reunion").val(
            `${fechaInicio.getHours().toString().padStart(2, '0')}:${fechaInicio.getMinutes().toString().padStart(2, '0')}`
        );
        $("#fecha_fin_reunion").val(fechaFin.toLocaleDateString('en-CA'));
        $("#hora_fin_reunion").val(
            `${fechaFin.getHours().toString().padStart(2, '0')}:${fechaFin.getMinutes().toString().padStart(2, '0')}`
        );

        for (let index = 0; index < dataReunion.participantes.length; index++) {
            const participante = dataReunion.participantes[index];
            participantesSeleccionados.set(participante.id, participante);
        }
        
        $("#textReunionCreate").html('Actualizar reunión');
        $("#reunionFormModal").modal('show');
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
}

function guardarReunion() {
    const formData = {
        id: $('#id_reunion_up').val(),
        titulo: $('#titulo_reunion').val(),
        descripcion: $('#descripcion_reunion').val(),
        fecha_inicio: $('#fecha_inicio_reunion').val() + ' ' + $('#hora_inicio_reunion').val(),
        fecha_fin: $('#fecha_fin_reunion').val() + ' ' + $('#hora_fin_reunion').val(),
        lugar: $('#lugar_reunion').val()
    };

    // Validar que haya al menos un participante
    if (formData.participantes.length === 0) {
        agregarToast('error', 'Error', 'Debe seleccionar al menos un participante');
        return;
    }

    const reunionId = $('#id_reunion_up').val();
    const method = reunionId ? 'PUT' : 'POST';
    const url = base_url + 'reuniones';

    $('#saveReunion').hide();
    $('#saveReunionLoading').show();

    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(formData),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $('#reunionFormModal').modal('hide');
            clearFormReunion();
            reloadReuniones();
            agregarToast('exito', 'Éxito', res.message, true);
        } else {
            agregarToast('error', 'Error', res.message);
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Error', errorsMsg);
    }).always(() => {
        $('#saveReunion').show();
        $('#saveReunionLoading').hide();
    });
}

function armarFechaReuniones (fecha) {

    fecha = new Date(fecha);

    const fechaFormateada = fecha.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    }).split('/').reverse().join('-');
    
    const horaFormateada = fecha.toLocaleTimeString('es-CO', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });

    return [fechaFormateada, horaFormateada];

}

function quitarParticipante(nitId) {
    participantesSeleccionados.delete(nitId);
    
    // Actualizar botones en la tabla
    if (reuniones_nit_table) {
        reuniones_nit_table.rows().every(function() {
            const data = this.data();
            if (data.id === nitId) {
                const button = $(this.node()).find('.btn-seleccionar');
                button.removeClass('btn-danger').addClass('btn-success').text('Seleccionar');
                $(this.node()).removeClass('table-success');
            }
        });
    }
}

function mostrarParticipantes(id) {
    if (!id) return;
    reunion_id = id;
    participantes_reuniones_table.ajax.reload();

    $('#createReunion').hide();
    $('#volverReuniones').hide();
    $('#createReunionNit').show();
    $('#verReunionDetalle').hide();
    $('#volverReunionesNits').show();

    $('#tabla_reuniones').hide();
    $('#calendar_reuniones').hide();
    $('#tabla_reuniones_nits').show();
}




$(document).on('click', '#createReunionNit', function() {
    $("#id_nit_reunion").val(null).trigger('change');
    $("#reunionNitsFormModal").modal('show');
});

$(document).on('click', '#createReunion', function() {
    clearFormReunion();
    $("#reunionFormModal").modal('show');
});

$(document).on('click', '#reloadReuniones', function() {
    reloadReuniones();
});

$(document).on('click', '#saveReunion', function() {
    var form = document.querySelector('#form-reunion');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }
    
    const reunionData = {
        id: $("#id_reunion_up").val(),
        titulo: $('#titulo_reunion').val(),
        descripcion: $('#descripcion_reunion').val(),
        lugar: $('#lugar_reunion').val(),
        fecha_inicio: $('#fecha_inicio_reunion').val() + ' ' + $('#hora_inicio_reunion').val(),
        fecha_fin: $('#fecha_fin_reunion').val() + ' ' + $('#hora_fin_reunion').val(),
    };

    $("#saveReunion").hide();
    $("#saveReunionLoading").show();

    const method = reunionData.id ? 'PUT' : 'POST';
    const url = base_url + 'reuniones';

    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(reunionData),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormReunion();
            $("#saveReunion").show();
            $("#saveReunionLoading").hide();
            $("#reunionFormModal").modal('hide');
            reloadReuniones();
            agregarToast('exito', 'Exitoso', res.message, true);
        } else {
            agregarToast('error', 'Error', res.message);
        }
    }).fail((err) => {
        $("#saveReunion").show();
        $("#saveReunionLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Error', errorsMsg);
    });
});

$(document).on('click', '#saveReunionNit', function() {

    var form = document.querySelector('#form-reunion-nit');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    const id = $("#id_nit_reunion").val();

    $("#saveReunionNit").hide();
    $("#saveReunionNitLoading").show();
    
    $.ajax({
        url: base_url + 'reuniones-participantes',
        method: 'POST',
        data: JSON.stringify({
            id_nit: id,
            id_reunion: reunion_id
        }),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){

            $("#saveReunionNit").show();
            $("#saveReunionNitLoading").hide();
            $("#reunionNitsFormModal").modal('hide');
            participantes_reuniones_table.ajax.reload();
            agregarToast('exito', 'Exitoso', res.message, true);
        } else {
            agregarToast('error', 'Error', res.message);
        }
    }).fail((err) => {
        $("#saveReunionNit").show();
        $("#saveReunionNitLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Error', errorsMsg);
    });
});

$(document).on('click', '#verReunionDetalle', function () {
    reuniones_table.ajax.reload();
    $('#createReunion').show();
    $('#volverReuniones').show();
    $('#createReunionNit').hide();
    $('#verReunionDetalle').hide();
    $('#volverReunionesNits').hide();

    $('#tabla_reuniones').show();
    $('#calendar_reuniones').hide();
    $('#tabla_reuniones_nits').hide();
});

$(document).on('click', '#volverReunionesNits', function () {
    reuniones_table.ajax.reload();
    
    $('#createReunion').show();
    $('#volverReuniones').show();
    $('#createReunionNit').hide();
    $('#verReunionDetalle').hide();
    $('#volverReunionesNits').hide();

    $('#tabla_reuniones').show();
    $('#calendar_reuniones').hide();
    $('#tabla_reuniones_nits').hide();
});

$(document).on('click', '#volverReuniones', function () {
    $('#createReunion').show();
    $('#volverReuniones').hide();
    $('#createReunionNit').hide();
    $('#verReunionDetalle').show();
    $('#volverReunionesNits').hide();

    $('#tabla_reuniones').hide();
    $('#calendar_reuniones').show();
    $('#tabla_reuniones_nits').hide();
});