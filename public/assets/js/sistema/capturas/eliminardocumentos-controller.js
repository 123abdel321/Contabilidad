var eliminar_documentos_table = null;
var generarEliminarDocumentos = false;

function eliminardocumentosInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_desde_eliminar_documentos').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_eliminar_documentos').val(fechaDesde);

    eliminar_documentos_table = $('#eliminarDocumentosInformeTable').DataTable({
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
            if (data.nivel != 99) {
                $("#eliminarDocumentos").show();
                $("#eliminarDocumentosDisabled").hide();
            }
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
                if (row.id_cuenta) return row.cuenta + ' - ' +row.nombre_cuenta;
                return '';
            }},
            {"data": function (row, type, set){ //NIT
                if(!row.numero_documento){
                    return '';
                }
                var nombre = row.numero_documento + ' - ' +row.nombre_nit;
                if(row.razon_social){
                    nombre = row.numero_documento +' - '+ row.razon_social;
                }
                
                var html = '<div class="button-user" onclick="showNit('+row.id_nit+')"><i class="far fa-id-card icon-user"></i>&nbsp;'+nombre+'</div>';
                return html;
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

    $('#id_nit_eliminar_documentos').select2({
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

    $('#id_comprobante_eliminar_documentos').select2({
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

    $('#id_cecos_eliminar_documentos').select2({
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

    $('#id_cuenta_eliminar_documentos').select2({
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

    $('#id_usuario_eliminar_documentos').select2({
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

    $('#agrupar_eliminar_documentos').select2({
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

    $("#agrupar_eliminar_documentos").on("select2:select", function(evt) {
        var element = evt.params.data.element;
        var $element = $(element);
      
        if ($("#agrupar_eliminar_documentos").find(":selected").length > 1) {
          var $second = $("#agrupar_eliminar_documentos").find(":selected").eq(-2);
          $element.detach();
          $second.after($element);
        } else {
          $element.detach();
          $("#agrupar_eliminar_documentos").prepend($element);
        }
      
        $("#agrupar_eliminar_documentos").trigger("change");
        var orderedArray= $("#agrupar_eliminar_documentos").serializeArray;
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
        loadEliminarDocumentosById(data.id_documento_general);
        return;
    }
});

function loadEliminarDocumentosById(id_documento_general) {
    $("#eliminarDocumentos").hide();
    $("#eliminarDocumentosDisabled").show();
    console.log('id_documento_general: ',id_documento_general);
    eliminar_documentos_table.ajax.url(base_url + 'documentos-generales-show?id='+id_documento_general).load(function(res) {
        console.log('respuesta: ',res);
        if(res.success){
            $("#generarEliminarDocumentos").show();
            $("#generarEliminarDocumentosLoading").hide();
            agregarToast('exito', 'Documentos generales cargados', 'Informe cargado con exito!', true);
        }
    });
}

function getNivelAgrupado() {
    if($("input[type='radio']#agrupado_eliminar_documentos0").is(':checked')) return 0;
    if($("input[type='radio']#agrupado_eliminar_documentos1").is(':checked')) return 1;

    return false;
}

$(document).on('click', '#generarEliminarDocumentos', function () {
    generarEliminarDocumentos = false;

    $("#generarEliminarDocumentos").hide();
    $("#generarEliminarDocumentosLoading").show();

    var agruparDocumentos = $("#agrupar_eliminar_documentos").val();
    var agruparDocumentosText = '';

    if (agruparDocumentos.length) {
        for (let index = 0; index < agruparDocumentos.length; index++) {
            const element = agruparDocumentos[index];
            agruparDocumentosText+= element+',';
        }
        agruparDocumentosText = agruparDocumentosText.slice(0, -1);
    }

    var id_nit = $('#id_nit_eliminar_documentos').val();
    var id_comprobante= $('#id_comprobante_eliminar_documentos').val();
    var id_centro_costos= $('#id_cecos_eliminar_documentos').val();
    var id_cuenta= $('#id_cuenta_eliminar_documentos').val();
    var id_usuario= $('#id_usuario_eliminar_documentos').val();

    var url = base_url + 'documentos-generales';
    url+= '?fecha_desde='+$('#fecha_desde_eliminar_documentos').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_eliminar_documentos').val();
    url+= '&precio_desde='+$('#precio_desde_eliminar_documentos').val();
    url+= '&precio_hasta='+$('#precio_hasta_eliminar_documentos').val();
    url+= '&id_nit='+ id_nit;
    url+= '&id_comprobante='+ id_comprobante;
    url+= '&id_centro_costos='+ id_centro_costos;
    url+= '&id_cuenta='+ id_cuenta;
    url+= '&documento_referencia='+$('#factura_eliminar_documentos').val();
    url+= '&consecutivo='+$('#consecutivo_eliminar_documentos').val();
    url+= '&concepto='+$('#concepto_eliminar_documentos').val();
    url+= '&id_usuario='+ id_usuario;
    url+= '&agrupar='+agruparDocumentosText;
    url+= '&agrupado='+getNivelAgrupado();
    url+= '&generar='+generarEliminarDocumentos;
    
    eliminar_documentos_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando documentos generales', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});

$(document).on('click', '#eliminarDocumentos', function () {

    Swal.fire({
        title: "Esta seguro?",
        text: "¡No podrás revertir esto!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, borar!"
    }).then((result) => {
        if (result.isConfirmed) {
            var data = {
                fecha_desde: $('#fecha_desde_eliminar_documentos').val(),
                fecha_hasta: $('#fecha_hasta_eliminar_documentos').val(),
                precio_desde: $('#precio_desde_eliminar_documentos').val(),
                precio_hasta: $('#precio_hasta_eliminar_documentos').val(),
                id_nit: $('#id_nit_eliminar_documentos').val(),
                id_comprobante: $('#id_comprobante_eliminar_documentos').val(),
                id_centro_costos: $('#id_cecos_eliminar_documentos').val(),
                id_cuenta: $('#id_cuenta_eliminar_documentos').val(),
                id_usuario: $('#id_usuario_eliminar_documentos').val(),
                documento_referencia: $('#factura_eliminar_documentos').val(),
                consecutivo: $('#consecutivo_eliminar_documentos').val(),
                concepto: $('#concepto_eliminar_documentos').val()
            };
        
            $.ajax({
                url: base_url + 'documentos-generales-delete',
                method: 'POST',
                data: JSON.stringify(data),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    document.getElementById('generarEliminarDocumentos').click();
                    agregarToast('exito', 'Documentos eliminados!', 'Documentos eliminados con exito!', true);
                }
            }).fail((err) => {
            });
        }
    });

});
