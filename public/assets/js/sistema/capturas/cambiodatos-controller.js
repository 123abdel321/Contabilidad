var cambio_datos_table = null;
var channel_cambios_datos = pusher.subscribe('cambio_datos-'+localStorage.getItem("notificacion_code"));

function cambiodatosInit() {
    initTablasCambioDatos();
    initCombosCambioDatos();
    initFechasCambioDatos();
}

function initTablasCambioDatos () {
    cambio_datos_table = $('#cambioDatosTable').DataTable({
        pageLength: 100,
        dom: 'Brtip',
        paging: true,
        colReorder: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
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
        ajax:  {
            type: "GET",
            url: base_url + 'documentos-generales-show',
            headers: headers
        },
        rowCallback: function(row, data, index){

            if(parseInt(data.diferencia) && data.nivel == 1) {
                $(row).addClass('highlight-error');
                return;
            }
            if(data.nivel == 99){
                $('td', row).css('background-color', '#000');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 1){
                $('td', row).css('background-color', '#000');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 2){
                $('td', row).css('background-color', 'rgb(0 0 0 / 85%)');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 3){
                $('td', row).css('background-color', 'rgb(0 0 0 / 70%)');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 4){
                $('td', row).css('background-color', 'rgb(0 0 0 / 55%)');
                $('td', row).css('color', 'white');
                return;
            }
        },
        columns: [
            {"data": function (row, type, set){ //CUENTA
                if (row.nivel == 99) return 'TOTALES'
                if (row.id_cuenta) return row.cuenta;
                return '';
            }},
            {"data": function (row, type, set){ //CUENTA
                if (row.nivel == 99) return 'TOTALES'
                if (row.id_cuenta) return row.nombre_cuenta;
                return '';
            }},
            {"data": function (row, type, set){ //NIT
                if(!row.numero_documento){
                    return '';
                }
                var nombre = row.numero_documento;
                if(row.razon_social){
                    nombre = row.numero_documento;
                }
                
                var html = '<div class="button-user" onclick="showNit('+row.id_nit+')"><i class="far fa-id-card icon-user"></i>&nbsp;'+nombre+'</div>';
                return html;
            }},
            {"data": function (row, type, set){ //NIT
                if(!row.numero_documento){
                    return '';
                }
                var nombre = row.nombre_nit;
                if(row.razon_social){
                    nombre = row.razon_social;
                }
                
                return nombre;
            }},
            {"data": function (row, type, set){ //UBICACION
                return row.apartamento_nit;
            }},
            {"data": function (row, type, set){ //COMPROBANTE
                if(!row.codigo_comprobante){
                    return '';
                }
                return row.codigo_comprobante + ' - ' +row.nombre_comprobante;
            }},
            { data: 'consecutivo'}, //CONSECUTIVO
            {"data": function (row, type, set){ //CECOS
                if(!row.codigo_cecos){
                    return '';
                }
                return row.codigo_cecos + ' - ' +row.nombre_cecos;
            }},
            { data: 'documento_referencia'}, //FACTURA
            { data: "debito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "credito", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){ //DIFERENCIA
                const diferencia = parseFloat(row.diferencia).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                if(parseInt(row.diferencia) && row.nivel == 1) {
                    return `<div class="error-container">
                        <i class="fas fa-exclamation-triangle error-triangle" aria-hidden="true"></i>&nbsp;
                        <span class="error-text">${diferencia}</span>
                    </div>`;
                }
                return diferencia;
            }, className: 'dt-body-right'},
            { data: 'fecha_manual'},
            { data: 'concepto'},
            { data: "base_cuenta", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: "porcentaje_cuenta", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'total_columnas'},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by ) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
        ]
    });

    var columnUbicacionMaximoPH = cambio_datos_table.column(4);

    if (ubicacion_maximoph_cambio_datos) columnUbicacionMaximoPH.visible(true);
    else columnUbicacionMaximoPH.visible(false);
}

function initCombosCambioDatos () {
    //FILTROS
    $('#id_nit_cambio_datos').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        
        allowClear: true,
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

    $('#id_comprobante_cambio_datos').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    $('#id_cecos_cambio_datos').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Centro de costos",
        allowClear: true,
        ajax: {
            url: 'api/centro-costos/combo-centro-costo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_cuenta_cambio_datos').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
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

    $('#agrupar_cambio_datos').select2({
        theme: 'bootstrap-5',
        sorter: function(data) {
            var enabled = data.filter(function(d) {
                return !d.disabled;
            });
            var disabled = data.filter(function(d) {
                return d.disabled;
            });
            return enabled.concat(disabled);
        }
    });

    //CHANGE DATA
    $('#id_nit_destino').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
        dropdownParent: $('#cambioDatosFormModal'),
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

    $('#id_comprobante_destino').select2({
        theme: 'bootstrap-5',
        delay: 250,
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
        placeholder: "Seleccione un Comprobante",
        allowClear: true,
        dropdownParent: $('#cambioDatosFormModal'),
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

    $('#id_centro_costos_destino').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Centro de costos",
        allowClear: true,
        dropdownParent: $('#cambioDatosFormModal'),
        ajax: {
            url: 'api/centro-costos/combo-centro-costo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_cuenta_destino').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
        dropdownParent: $('#cambioDatosFormModal'),
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

}

function initFechasCambioDatos () {
    const start = moment().startOf("month");
    const end = moment().endOf("month");
    
    $("#fecha_manual_cambio_datos").daterangepicker({
        startDate: start,
        endDate: end,
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
        locale: {
            format: "YYYY-MM-DD",
            separator: " - ",
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        },
        ranges: rangoFechas
    }, formatoFecha);

    formatoFecha(start, end, "fecha_manual_cambio_datos");
}

function loadDocumentosGeneralesById(id_documento_general) {
    cambio_datos_table.ajax.url(base_url + 'documentos-generales-show?id='+id_documento_general).load(function(res) {
        if(res.success){
            $("#generarCambioDatos").show();
            $("#generarCambioDatosLoading").hide();

            $("#descargarCambioDatos").show();
            $("#descargarCambioDatosLoading").hide();
            $("#descargarCambioDatosDisabled").hide();

            agregarToast('exito', 'Documentos generales cargados', 'Informe cargado con exito!', true);
        }
    });
}

function clearFormChangeDatos() {
    $("#id_nit_destino").val("").trigger('change');
    $("#id_cuenta_destino").val("").trigger('change');
    $("#id_comprobante_destino").val("").trigger('change');
    $("#id_centro_costos_destino").val("").trigger('change');

    $("#fecha_manual_destino").val("");
    $("#consecutivo_desde_destino").val("");
    $("#consecutivo_hasta_destino").val("");
}

function getDatosCambio() {
    // -----------------------------------------------------------------
    // 1. Recolección de Filtros (Define qué documentos se van a modificar)
    // -----------------------------------------------------------------
    const id_nit_filtro = $('#id_nit_cambio_datos').val();
    const id_comprobante_filtro = $('#id_comprobante_cambio_datos').val();
    const id_centro_costos_filtro = $('#id_cecos_cambio_datos').val();
    const id_cuenta_filtro = $('#id_cuenta_cambio_datos').val();
    const documento_referencia_filtro = $('#documento_referencia_cambio_datos').val();
    const consecutivo_desde_filtro = $('#consecutivo_desde_cambio_datos').val();
    const consecutivo_hasta_filtro = $('#consecutivo_hasta_cambio_datos').val();
    const concepto_filtro = $('#concepto_cambio_datos').val();
    const agrupar_filtro = $("#agrupar_cambio_datos").val();
    const precio_desde_filtro = $('#precio_desde_cambio_datos').val();
    const precio_hasta_filtro = $('#precio_hasta_cambio_datos').val();

    // Fechas desde el daterangepicker
    const fecha_desde_filtro = $('#fecha_manual_cambio_datos').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm:ss');
    const fecha_hasta_filtro = $('#fecha_manual_cambio_datos').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm:ss');

    const filtros = {
        fecha_desde: fecha_desde_filtro,
        fecha_hasta: fecha_hasta_filtro,
        precio_desde: precio_desde_filtro,
        precio_hasta: precio_hasta_filtro,
        id_nit: id_nit_filtro,
        id_comprobante: id_comprobante_filtro,
        id_centro_costos: id_centro_costos_filtro,
        id_cuenta: id_cuenta_filtro,
        documento_referencia: documento_referencia_filtro,
        consecutivo_desde: consecutivo_desde_filtro,
        consecutivo_hasta: consecutivo_hasta_filtro,
        concepto: concepto_filtro,
        agrupar: agrupar_filtro,
        agrupado: 0, // Asumo valores fijos
        anulado: 0,  // Asumo valores fijos
        cambio_datos: true, // Asumo valor fijo
    };

    // -----------------------------------------------------------------
    // 2. Recolección de Datos de Destino (El cambio a aplicar)
    // -----------------------------------------------------------------
    const nit_destino = $("#id_nit_destino").val();
    const comprobante_destino = $("#id_comprobante_destino").val();
    const centro_costos_destino = $("#id_centro_costos_destino").val();
    const cuenta_destino = $("#id_cuenta_destino").val();
    const fecha_manual_destino = $("#fecha_manual_destino").val();
    const consecutivo_desde_destino = $("#consecutivo_desde_destino").val();
    const consecutivo_hasta_destino = $("#consecutivo_hasta_destino").val();
    
    // Obtener la pestaña activa
    const active_tab_id = $('#cambioDatosTabs .nav-link.active').attr('id');
    
    let tipo_cambio = '';
    let valor_cambio = {};
    let is_valid = true;

    if (active_tab_id === 'tab-nits' && nit_destino) {
        tipo_cambio = 'nit';
        valor_cambio = { id_nit_destino: nit_destino };
    } else if (active_tab_id === 'tab-comprobantes' && comprobante_destino) {
        tipo_cambio = 'comprobante';
        valor_cambio = { id_comprobante_destino: comprobante_destino };
    } else if (active_tab_id === 'tab-centros' && centro_costos_destino) {
        tipo_cambio = 'centro_costos';
        valor_cambio = { id_centro_costos_destino: centro_costos_destino };
    } else if (active_tab_id === 'tab-cuentas' && cuenta_destino) {
        tipo_cambio = 'cuenta';
        valor_cambio = { id_cuenta_destino: cuenta_destino };
    } else if (active_tab_id === 'tab-fechas' && fecha_manual_destino) {
        tipo_cambio = 'fecha';
        valor_cambio = { fecha_manual_destino: fecha_manual_destino };
    } else if (active_tab_id === 'tab-consecutivos' && (consecutivo_desde_destino || consecutivo_hasta_destino)) { // Corregido el ID del tab
        
        // Se valida que al menos un campo consecutivo esté lleno si la pestaña está activa
        if (consecutivo_desde_destino.length > 0 || consecutivo_hasta_destino.length > 0) {
            tipo_cambio = 'consecutivos';
            valor_cambio = { 
                consecutivo_desde_destino: consecutivo_desde_destino,
                consecutivo_hasta_destino: consecutivo_hasta_destino 
            };
        } else {
             is_valid = false;
        }

    } else {
        // Si la pestaña está activa, pero el campo de destino está vacío
        is_valid = false;
    }
    
    if (!is_valid) {
        return null; // No hay un cambio válido seleccionado
    }

    return {
        filtros: filtros,
        tipo_cambio: tipo_cambio,
        ...valor_cambio
    };
}

$(document).on('click', '#generarCambioDatos', function () {

    $("#generarCambioDatos").hide();
    $("#generarCambioDatosLoading").show();

    $("#descargarCambioDatos").hide();
    $("#descargarCambioDatosLoading").hide();
    $("#descargarCambioDatosDisabled").show();

    var id_nit = $('#id_nit_cambio_datos').val();
    var id_comprobante= $('#id_comprobante_cambio_datos').val();
    var id_centro_costos= $('#id_cecos_cambio_datos').val();
    var id_cuenta= $('#id_cuenta_cambio_datos').val();

    var url = base_url + 'documentos-generales';
    url+= '?fecha_desde='+$('#fecha_manual_cambio_datos').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
    url+= '&fecha_hasta='+$('#fecha_manual_cambio_datos').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
    url+= '&precio_desde='+$('#precio_desde_cambio_datos').val();
    url+= '&precio_hasta='+$('#precio_hasta_cambio_datos').val();
    url+= '&id_nit='+ id_nit ?? '';
    url+= '&id_comprobante='+ id_comprobante ?? '';
    url+= '&id_centro_costos='+ id_centro_costos ?? '';
    url+= '&id_cuenta='+ id_cuenta ?? '';
    url+= '&documento_referencia='+$('#documento_referencia_cambio_datos').val();
    url+= '&consecutivo_desde='+$('#consecutivo_desde_cambio_datos').val();
    url+= '&consecutivo_hasta='+$('#consecutivo_hasta_cambio_datos').val();
    url+= '&concepto='+$('#concepto_cambio_datos').val();
    url+= '&agrupar='+$("#agrupar_cambio_datos").val();
    url+= '&agrupado='+0;
    url+= '&anulado='+0;
    url+= '&cambio_datos='+true;
    url+= '&generar='+true;
    
    cambio_datos_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando documentos generales', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});

$(document).on('click', '#descargarCambioDatos', function () {
    clearFormChangeDatos();
    $("#cambioDatosFormModal").modal('show');
});

$('#cambioDatosTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    clearFormChangeDatos();
});

$(document).on('click', '#confirmarCambioDatos', function (e) {

    var datos_cambio = getDatosCambio();

    if (!datos_cambio) {
        agregarToast('error', 'Error de Datos', 'Debe seleccionar al menos un campo de destino para el cambio.', true);
        return;
    }

    $('#confirmarCambioDatos').hide();
    $('#confirmarCambioLoading').show();

    // Estructura AJAX para el POST
    $.ajax({
        type: "POST",
        url: base_url + 'cambio-datos', // Endpoint que debes crear/usar
        headers: headers,
        data: JSON.stringify(datos_cambio),
        contentType: "application/json",
        dataType: 'json',
        success: function(response) {
            $('#confirmarCambioDatos').show();
            $('#confirmarCambioLoading').hide();
        },
        error: function(err) {

            $('#confirmarCambioDatos').show();
            $('#confirmarCambioLoading').hide();

            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    });
});

channel_cambios_datos.bind('notificaciones', function(data) {
    console.log('data: ',data);
    if (data.id_documento_general) {
        loadDocumentosGeneralesById(data.id_documento_general);
        return;
    }
});