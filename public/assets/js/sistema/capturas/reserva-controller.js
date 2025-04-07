var $comboNitReserva;
var $comboNitReservaFilter;
var $comboNitReservaFilterCalender;
var $comboUbicacionReserva;
var $comboUbicacionReservaFilter;
var $comboUbicacionReservaFilterCalender;
var reserva_table = null;

function reservaInit() {
    initComboReserva();
    initTableReserva();
    initFilterReserva();
    initCalendarReserva();
}

function initComboReserva() {
    $comboNitReserva = $('#id_nit_reserva').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#reservaFormModal'),
        delay: 250,
        placeholder: "Seleccione un usuario",
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
            url: base_url + 'nit/combo-nit',
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

    $comboNitReservaFilter = $('#id_nit_filter_reserva_table').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un usuario",
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
            url: base_url + 'nit/combo-nit',
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

    $comboNitReservaFilterCalender = $('#id_nit_filter_reserva').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un usuario",
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
            url: base_url + 'nit/combo-nit',
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

    $comboUbicacionReserva = $('#id_ubicacion_reserva').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#reservaFormModal'),
        delay: 250,
        placeholder: "Seleccione una ubicacion",
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
            url: base_url + 'ubicaciones-combo-general',
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

    $comboUbicacionReservaFilter = $('#id_ubicacion_filter_reserva_table').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una ubicacion",
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
            url: base_url + 'ubicaciones-combo-general',
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

    $comboUbicacionReservaFilterCalender  = $('#id_ubicacion_filter_reserva').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una ubicacion",
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
            url: base_url + 'ubicaciones-combo-general',
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
}

function initTableReserva() {
    reserva_table = $('#reservaTable').DataTable({
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
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'reserva',
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_reserva_filter').val();
                d.fecha_hasta = $('#fecha_hasta_reserva_filter').val();
                d.id_ubicacion = $('#id_ubicacion_filter_reserva_table').val();
                d.id_nit = $('#id_nit_filter_reserva_table').val();
            }
        },
        columns: [
            {"data":'id'},
            {"data": function (row, type, set){
                return `<div  class="text-wrap">${row.ubicacion.codigo} - ${row.ubicacion.nombre}</div >`;
            }},
            {"data": function (row, type, set){
                return `<div  class="text-wrap">${row.nit.numero_documento} - ${row.nit.nombre_completo}</div >`;
            }},
            {"data": function (row, type, set){
                return `<div  class="text-wrap">${row.observacion}</div >`;
            }},
            {"data":'fecha_inicio'},
            {"data":'fecha_fin'},
            {"data":'fecha_creacion'},
            {
                "data": function (row, type, set){
                    var html = '';
                    // if (editarReservas) html+= '<span id="editreserva_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-reserva" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarReservas) html+= '<span id="deletereserva_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-reserva" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if (reserva_table) {
        //BORRAR TURNOS
        reserva_table.on('click', '.drop-reserva', function() {
            var trTurno = $(this).closest('tr');
            var id = this.id.split('_')[1];
            
            Swal.fire({
                title: 'Eliminar reserva?',
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
                        url: base_url + 'reserva',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            reserva_table.row(trTurno).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Reserva eliminada con exito!', true );
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

function initCalendarReserva() {
    const calendarReservas = document.getElementById('reserva-fullcalender');
    calendarioReservas = new FullCalendar.Calendar(calendarReservas, {
        initialView: 'dayGridMonth',  // Vista inicial (mes)
        // timeZone: 'UTC',
        headerToolbar: {
            left: 'prev,next today',     // Botones de navegación
            center: 'title',             // Título del calendario
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'  // Vistas disponibles
        },
        editable: true,
        droppable: true,
        expandRows: true,
        selectable: true,
        locale: 'es',
        events: {
            url: 'reserva-evento',
            method: 'GET',
            extraParams: function() {
                return {
                    id_nit: $("#id_nit_filter_reserva").val() ?? '',
                    id_ubicacion: $("#id_ubicacion_filter_reserva").val() ?? '',
                };
            },
            failure: function() {
                agregarToast('error', 'Actualización errada', 'Error al cargar los eventos!');
            }
        },
        eventDrop: function(info) {
            cambiarRangoDeReserva(info.event);
        },
        eventReceive: function(info) {
            cambiarRangoDeReserva(info.event);
        },
        eventResize: function(info) {
            cambiarRangoDeReserva(info.event);
        },
        // select: function(info) {
        //     seleccionarRangoDeReservas(info);
        // },
        // eventClick: function(info) {
        //     mostrarModalEvento(info.event.id);
        // },
        height: 'auto',
        contentHeight: 'auto',
        expandRows: true, // Hacer que las filas de eventos ocupen todo el espacio vertical disponible
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
    calendarioReservas.render();
}

function applyPerfectScrollbar() {
    if ($(".fc-scroller-liquid-absolute").length >= 1) new PerfectScrollbar($(".fc-scroller-liquid-absolute")[0]);
    if ($(".fc-scroller").length >= 1) new PerfectScrollbar($(".fc-scroller")[0]);
    if ($(".fc-scroller").length >= 2) new PerfectScrollbar($(".fc-scroller")[1]);
    if ($(".fc-scroller").length >= 3) new PerfectScrollbar($(".fc-scroller")[2]);
}

function clearFormReserva () {
    const today = new Date().toLocaleDateString('en-CA');

    $("#id_nit_reserva").val('').change();
    $("#id_ubicacion_reserva").val('').change();

    $("#observacion_reserva").val(null);

    $("#fecha_inicio_reserva").val(today);
    $("#fecha_fin_reserva").val(today);
    $("#hora_inicio_reserva").val("08:00");
    $("#hora_fin_reserva").val("20:00");
}

function cambiarRangoDeReserva(info) {
    var [fechaInicio, horaInicio] = armarFechaReserva(info.start);
    var [fechaFin, horaFin] = armarFechaReserva(info.end);

    $('#reloadReservasIconNormal').hide();
    $('#reloadReservasIconLoading').show();

    let data = {
        id: info.id,
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
    }

    $.ajax({
        url: base_url + 'reserva',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#reloadReservasIconNormal').show();
        $('#reloadReservasIconLoading').hide();
        if(res.success){
            agregarToast('exito', 'Actualización exitosa', 'Reserva actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#reloadReservasIconNormal').show();
        $('#reloadReservasIconLoading').hide();
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
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
}

function armarFechaReserva (fecha) {

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

function initFilterReserva () {

    $("#id_nit_filter_reserva").on('change', function(event) {
        reloadReserva();
    });

    $("#id_ubicacion_filter_reserva").on('change', function(event) {
        reloadReserva();
    });

    $("#id_nit_filter_reserva_table").on('change', function(event) {
        reserva_table.ajax.reload();
    });

    $("#id_ubicacion_filter_reserva_table").on('change', function(event) {
        reserva_table.ajax.reload();
    });

    $("#fecha_desde_reserva_filter").on('change', function(event) {
        reserva_table.ajax.reload();
    });

    $("#fecha_hasta_reserva_filter").on('change', function(event) {
        reserva_table.ajax.reload();
    });
}

function reloadReserva() {
    $("#reloadReservasIconNormal").hide();
    $("#reloadReservasIconLoading").show();

    setTimeout(function(){
        $("#reloadReservasIconNormal").show();
        $("#reloadReservasIconLoading").hide();
    },500);

    calendarioReservas.removeAllEvents();
    calendarioReservas.refetchEvents();
    reserva_table.ajax.reload();
}

$(document).on('click', '#createReserva', function () {
    clearFormReserva();
    $("#reservaFormModal").modal('show');
});

$(document).on('click', '#reloadReservas', function () {
    reloadReserva();
});

$(document).on('click', '#saveReserva', function () {
    var form = document.querySelector('#form-reserva');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id_nit: $("#id_nit_reserva").val(),
        id_ubicacion: $("#id_ubicacion_reserva").val(),
        fecha_inicio: $("#fecha_inicio_reserva").val(),
        fecha_fin: $("#fecha_fin_reserva").val(),
        hora_inicio: $("#hora_inicio_reserva").val(),
        hora_fin: $("#hora_fin_reserva").val(),
        observacion: $("#observacion_reserva").val(),
    }

    $("#saveReserva").hide();
    $("#saveReservaLoading").show();

    $.ajax({
        url: base_url + 'reserva',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#saveReserva").show();
            $("#saveReservaLoading").hide();
            $("#reservaFormModal").modal('hide');
            
            // reloadTurnos();
            agregarToast('exito', 'Creación exitosa', 'Reserva creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveReserva').show();
        $('#saveReservaLoading').hide();
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

$(document).on('click', '#detalleReserva', function () {
    reserva_table.ajax.reload();
    $('#tabla_reserva').show();
    $('#volverReserva').show();
    $('#detalleReserva').hide();
    $('#calendar_reserva').hide();
});

$(document).on('click', '#volverReserva', function () {
    $('#tabla_reserva').hide();
    $('#volverReserva').hide();
    $('#detalleReserva').show();
    $('#calendar_reserva').show();
});