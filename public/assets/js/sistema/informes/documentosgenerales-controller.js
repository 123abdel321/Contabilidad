var documentos_generales_table = null;
var generarDocumentosGenerales = false;
var agrupadoPorDocumento = false;
var channel_informe_documentos_generales = pusher.subscribe('informe-documentos-generales-'+localStorage.getItem("notificacion_code"));

function documentosgeneralesInit() {

    const start = moment().startOf("month");
    const end = moment().endOf("month");
    
    $("#fecha_manual_documentos_generales").daterangepicker({
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
        ranges: {
            "Hoy": [moment().startOf('day'), moment().endOf('day')],
            "Ayer": [
                moment().subtract(1, "days").startOf("day"),
                moment().subtract(1, "days").endOf("day")
            ],
            "Últimos 7 días": [
                moment().subtract(6, "days").startOf("day"),
                moment().endOf("day")
            ],
            "Últimos 30 días": [
                moment().subtract(29, "days").startOf("day"),
                moment().endOf("day")
            ],
            "Este mes": [
                moment().startOf("month").startOf("day"),
                moment().endOf("month").endOf("day")
            ],
            "Mes anterior": [
                moment().subtract(1, "month").startOf("month").startOf("day"),
                moment().subtract(1, "month").endOf("month").endOf("day")
            ]
        }
    }, function(start, end) {
        formatoFecha(start, end, "fecha_manual_documentos_generales");
    });

    formatoFecha(start, end, "fecha_manual_documentos_generales");

    $("#fecha_manual_documentos_generales").on('change blur', function() {
        parseManualInput($(this).val(), "fecha_manual_documentos_generales");
    });

    documentos_generales_table = $('#documentosGeneralesInformeTable').DataTable({
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
                if (row.nivel == 1 && agrupadoPorDocumento) {
                    var html = ``;
                    if (row.anulado == 1) return '';
                    html+= `<span id="imprimirdocumentogeneral_${row.id}" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-documentogeneral" style="margin-bottom: 0rem !important; color: black; background-color: white !important;">Imprimir</span>&nbsp;`;
                    html+= `<span id="anulardocumentogeneral_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger anular-documentogeneral" style="margin-bottom: 0rem !important;">Anular</span>`;

                    return html;
                }
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
            { data: "diferencia", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
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
                var html = '';
                if (row.nivel == 1 && agrupadoPorDocumento) {
                    html+= `<span id="imprimirdocumentogeneral_${row.id}" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-documentogeneral" style="margin-bottom: 0rem !important; color: black; background-color: white !important;">Imprimir</span>&nbsp;`;
                    html+= `<span id="anulardocumentogeneral_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger anular-documentogeneral" style="margin-bottom: 0rem !important;">Anular</span>`;

                    return html;
                }
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by ) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
        ]
    });

    var columnUbicacionMaximoPH = documentos_generales_table.column(4);

    if (ubicacion_maximoph_documentos_generales) columnUbicacionMaximoPH.visible(true);
    else columnUbicacionMaximoPH.visible(false);

    $('#id_nit_documentos_generales').select2({
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

    $('#id_comprobante_documentos_generales').select2({
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

    $('#id_cecos_documentos_generales').select2({
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

    $('#id_cuenta_documentos_generales').select2({
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

    $('#id_usuario_documentos_generales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Usuario",
        allowClear: true,
        ajax: {
            url: 'api/usuarios/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#agrupar_documentos_generales').select2({
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

    $("#agrupar_documentos_generales").on("select2:select", function(evt) {
        var element = evt.params.data.element;
        var $element = $(element);
      
        if ($("#agrupar_documentos_generales").find(":selected").length > 1) {
          var $second = $("#agrupar_documentos_generales").find(":selected").eq(-2);
          $element.detach();
          $second.after($element);
        } else {
          $element.detach();
          $("#agrupar_documentos_generales").prepend($element);
        }
      
        $("#agrupar_documentos_generales").trigger("change");
        var orderedArray= $("#agrupar_documentos_generales").serializeArray;
    });
}

channel_informe_documentos_generales.bind('notificaciones', function(data) {
    if (data.id_documento_general) {
        $('#id_documento_general_cargado').val(data.id_documento_general);
    }
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_documento_general){
        loadDocumentosGeneralesById(data.id_documento_general);
        return;
    }
});

function loadDocumentosGeneralesById(id_documento_general) {
    documentos_generales_table.ajax.url(base_url + 'documentos-generales-show?id='+id_documento_general).load(function(res) {
        if(res.success){
            const comprobante = $('#id_comprobante_documentos_generales').select2('data')[0];
            if (comprobante && comprobante.tipo_comprobante == 0) {
                $("#descargarPdfDocumento").show();
            } else {
                $("#descargarPdfDocumento").hide();
            }
            $("#generarDocumentosGenerales").show();
            $("#generarDocumentosGeneralesLoading").hide();
            // $('#descargarExcelAuxiliar').prop('disabled', false);
            // $("#descargarExcelAuxiliar").show();
            // $("#descargarExcelAuxiliarDisabled").hide();
            // $('#generarAuxiliarUltimo').hide();
            // $('#generarAuxiliarUltimoLoading').hide();
            agregarToast('exito', 'Documentos generales cargados', 'Informe cargado con exito!', true);
        }
    });
}

function getNivelAgrupado() {
    if($("input[type='radio']#agrupado_documentos_generales0").is(':checked')) return 0;
    if($("input[type='radio']#agrupado_documentos_generales1").is(':checked')) return 1;

    return false;
}

function getDocumentoAnulado() {
    if($("input[type='radio']#anulado_documentos_generales0").is(':checked')) return 0;
    if($("input[type='radio']#anulado_documentos_generales1").is(':checked')) return 1;

    return false;
}

$(document).on('click', '.imprimir-documentogeneral', function () {
    var id = this.id.split('_')[1];
    var data = getDataById(id, documentos_generales_table);
    
    window.open(`/documentos-generales-print/${data.id_comprobante}/${data.consecutivo}/${data.fecha_manual}`, "_blank");
});

$(document).on('click', '.anular-documentogeneral', function () {
    var id = this.id.split('_')[1];
    var data = getDataById(id, documentos_generales_table);

    Swal.fire({
        title: "Anular documento",
        text: "Agregar concepto",
        input: "text",
        inputAttributes: {
            autocapitalize: "off"
        },
        showCancelButton: true,
        confirmButtonText: "Anular",
        showLoaderOnConfirm: true,
        preConfirm: async (concepto) => {
            if (!concepto) {
                agregarToast('warning', 'Error al anular documento', 'El concepto anulación es requerido');
            }
            $.ajax({
                url: base_url + 'documentos-anular',
                method: 'POST',
                data: JSON.stringify({
                    id_comprobante: data.id_comprobante,
                    consecutivo: data.consecutivo,
                    fecha_manual: data.fecha_manual,
                    concepto: concepto
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                agregarToast('exito', 'Documento anulado', 'El documento se ha sido anulado exitosamente!');
            }).fail((err) => {
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
        },
        allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
        if (result.isConfirmed) {
            
        }
    });
});

$(document).on('click', '#generarDocumentosGenerales', function () {
    generarDocumentosGenerales = false;

    $("#generarDocumentosGenerales").hide();
    $("#generarDocumentosGeneralesLoading").show();

    $("#descargarExcelDocumento").hide();
    $("#descargarExcelDocumentoLoading").hide();
    $("#descargarExcelDocumentoDisabled").show();

    $("#descargarPdfDocumento").hide();
    $("#descargarPdfDocumentoLoading").hide();
    $("#descargarPdfDocumentoDisabled").show();

    var agruparDocumentos = $("#agrupar_documentos_generales").val();
    var agruparDocumentosText = '';

    if (agruparDocumentos.length) {
        for (let index = 0; index < agruparDocumentos.length; index++) {
            const element = agruparDocumentos[index];
            agruparDocumentosText+= element+',';
        }
        agruparDocumentosText = agruparDocumentosText.slice(0, -1);
    }

    var id_nit = $('#id_nit_documentos_generales').val();
    var id_comprobante= $('#id_comprobante_documentos_generales').val();
    var id_centro_costos= $('#id_cecos_documentos_generales').val();
    var id_cuenta= $('#id_cuenta_documentos_generales').val();
    var id_usuario= $('#id_usuario_documentos_generales').val();

    var url = base_url + 'documentos-generales';
    url+= '?fecha_desde='+$('#fecha_manual_documentos_generales').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
    url+= '&fecha_hasta='+$('#fecha_manual_documentos_generales').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
    url+= '&precio_desde='+$('#precio_desde_documentos_generales').val();
    url+= '&precio_hasta='+$('#precio_hasta_documentos_generales').val();
    url+= '&id_nit='+ id_nit;
    url+= '&id_comprobante='+ id_comprobante;
    url+= '&id_centro_costos='+ id_centro_costos;
    url+= '&id_cuenta='+ id_cuenta;
    url+= '&documento_referencia='+$('#factura_documentos_generales').val();
    url+= '&consecutivo='+$('#consecutivo_documentos_generales').val();
    url+= '&concepto='+$('#concepto_documentos_generales').val();
    url+= '&id_usuario='+ id_usuario;
    url+= '&agrupar='+agruparDocumentosText;
    url+= '&agrupado='+getNivelAgrupado();
    url+= '&anulado='+getDocumentoAnulado();
    url+= '&generar='+generarDocumentosGenerales;

    if (agruparDocumentosText == 'consecutivo') agrupadoPorDocumento = true;
    else agrupadoPorDocumento = false;
    
    documentos_generales_table.ajax.url(url).load(function(res) {
        if(res.success) {
            $("#descargarExcelDocumento").show();
            $("#descargarExcelDocumentoDisabled").hide();
            
            const comprobante = $('#id_comprobante_documentos_generales').select2('data')[0];
            if (comprobante && comprobante.tipo_comprobante == 0) {
                $("#descargarPdfDocumento").show();
                $("#descargarPdfDocumentoDisabled").hide();
            }

            agregarToast('info', 'Generando documentos generales', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});

$(document).on('click', '#descargarExcelDocumento', function () {

    $("#descargarExcelDocumento").hide();
    $("#descargarExcelDocumentoLoading").show();
    $("#descargarExcelDocumentoDisabled").hide();

    $.ajax({
        url: base_url + 'documentos-generales-excel',
        method: 'POST',
        data: JSON.stringify({id: $('#id_documento_general_cargado').val()}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        $("#descargarExcelDocumento").show();
        $("#descargarExcelDocumentoLoading").hide();
        $("#descargarExcelDocumentoDisabled").hide();

        if(res.success){
            if(res.url_file){
                window.open('https://'+res.url_file, "_blank");
                return; 
            }
            agregarToast('info', 'Generando excel', res.message, true);
        }
    }).fail((err) => {

        $("#descargarExcelDocumento").show();
        $("#descargarExcelDocumentoLoading").hide();
        $("#descargarExcelDocumentoDisabled").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Generación de excel errada', errorsMsg);
    });
});

$(document).on('click', '#descargarPdfDocumento', function () {

    $("#descargarPdfDocumento").hide();
    $("#descargarPdfDocumentoLoading").show();
    $("#descargarPdfDocumentoDisabled").hide();

    let data = {
        fecha_desde: $('#fecha_manual_documentos_generales').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm'),
        fecha_hasta: $('#fecha_manual_documentos_generales').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm'),
        precio_desde: $("#precio_desde_documentos_generales").val(),
        precio_hasta: $("#precio_hasta_documentos_generales").val(),
        documento_referencia: $("#factura_documentos_generales").val(),
        consecutivo: $("#consecutivo_documentos_generales").val(),
        concepto: $("#concepto_documentos_generales").val(),
        id_nit: $("#id_nit_documentos_generales").val(),
        id_comprobante: $("#id_comprobante_documentos_generales").val(),
        id_cecos: $("#id_cecos_documentos_generales").val(),
        id_cuenta: $("#id_cuenta_documentos_generales").val()
    };

    $.ajax({
        url: base_url + 'documentos-generales-pdf',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        
        $("#descargarPdfDocumento").show();
        $("#descargarPdfDocumentoLoading").hide();
        $("#descargarPdfDocumentoDisabled").hide();

        if(res.success){
            if(res.url_file){
                window.open('https://'+res.url_file, "_blank");
                return; 
            }
            agregarToast('info', 'Generando pdf', res.message, true);
        }
    }).fail((err) => {
        $("#descargarPdfDocumento").show();
        $("#descargarPdfDocumentoLoading").hide();
        $("#descargarPdfDocumentoDisabled").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Generación de pdf errada', errorsMsg);;
    });
});

function formatNitDocumentosGenerales (nit) {
    
    if (nit.loading) return nit.text;

    if (ubicacion_maximoph_documentos_generales) {
        if (nit.apartamentos) return nit.text+' - '+nit.apartamentos;
        else return nit.text;
    }
    else return nit.text;
}

function formatRepoSelectionDocumentosGenerales (nit) {
    return nit.full_name || nit.text;
}