var documentos_generales_table = null;
var generarDocumentosGenerales = false;

function documentosgeneralesInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_desde_documentos_generales').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_documentos_generales').val(fechaDesde);

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
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 1){
                $('td', row).css('background-color', 'rgb(64 164 209 / 90%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.nivel == 2){
                $('td', row).css('background-color', 'rgb(64 164 209 / 70%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.nivel == 3){
                $('td', row).css('background-color', 'rgb(64 164 209 / 50%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.nivel == 4){
                $('td', row).css('background-color', 'rgb(64 164 209 / 30%)');
                $('td', row).css('font-weight', 'bold');
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
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
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
        },
        templateResult: formatNitDocumentosGenerales,
        templateSelection: formatRepoSelectionDocumentosGenerales
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

var channel = pusher.subscribe('informe-documentos-generales-'+localStorage.getItem("notificacion_code"));

channel.bind('notificaciones', function(data) {
    console.log('notificaciones', data);
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_documento_general){
        $('#id_auxiliar_cargado').val(data.id_documento_general);
        loadDocumentosGeneralesById(data.id_documento_general);
        return;
    }
});

function loadDocumentosGeneralesById(id_documento_general) {
    documentos_generales_table.ajax.url(base_url + 'documentos-generales-show?id='+id_documento_general).load(function(res) {
        if(res.success){
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

$(document).on('click', '#generarDocumentosGenerales', function () {
    generarDocumentosGenerales = false;

    $("#generarDocumentosGenerales").hide();
    $("#generarDocumentosGeneralesLoading").show();

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
    url+= '?fecha_desde='+$('#fecha_desde_documentos_generales').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_documentos_generales').val();
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
    url+= '&generar='+generarDocumentosGenerales;
    
    documentos_generales_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando documentos generales', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
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