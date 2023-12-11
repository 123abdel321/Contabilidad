var ventas_generales_table = null;
var generarDocumentosGenerales = false;

function ventasgeneralesInit() {
    fechaDesdeVentasGenerales = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_desde_ventas_generales').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_ventas_generales').val(fechaDesdeVentasGenerales);

    ventas_generales_table = $('#ventasGeneralesInformeTable').DataTable({
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
            url: base_url + 'ventas-generales-show',
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
            { data: "total", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'fecha_manual'},
            { data: 'concepto'},
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

    $('#id_nit_ventas_generales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un cliente",
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
    
    $('#id_resolucion_ventas_generales').select2({
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
        ajax: {
            url: 'api/resoluciones/combo-resoluciones',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_resoluciones: [0, 1],
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
    
    $('#id_bodega_ventas_generales').select2({
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
        ajax: {
            url: 'api/bodega/combo-bodega',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
    
    $('#id_usuario_ventas_generales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Usuario",
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

    $('#id_producto_ventas_generales').select2({
        theme: 'bootstrap-5',
        dropdownCssClass: 'custom-venta_producto',
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
        ajax: {
            url: 'api/producto/combo-producto',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    captura: 'venta',
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

var channel = pusher.subscribe('informe-ventas-generales-'+localStorage.getItem("notificacion_code"));

channel.bind('notificaciones', function(data) {
    console.log('notificaciones', data);
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_venta_general){
        loadVentasGeneralesById(data.id_venta_general);
        return;
    }
});

function loadVentasGeneralesById(id_venta_general) {
    $("#generarVentasGenerales").hide();
    $("#generarVentasGeneralesLoading").show();
    ventas_generales_table.ajax.url(base_url + 'ventas-generales-show?id='+id_venta_general).load(function(res) {
        if(res.success){
            $("#generarVentasGenerales").show();
            $("#generarVentasGeneralesLoading").hide();
            agregarToast('exito', 'Informe ventas generales', res.message, true);
        }
    });
}


$(document).on('click', '#generarVentasGenerales', function () {
    generarVentasGenerales = false;

    $("#generarVentasGenerales").hide();
    $("#generarVentasGeneralesLoading").show();

    var id_nit = $('#id_nit_ventas_generales').val();
    var id_resolucion= $('#id_resolucion_ventas_generales').val();
    var id_bodega= $('#id_bodega_ventas_generales').val();
    var id_usuario= $('#id_usuario_ventas_generales').val();
    var id_producto = $('#id_producto_ventas_generales').val();

    var url = base_url + 'ventas-generales';
    url+= '?fecha_desde='+$('#fecha_desde_ventas_generales').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_ventas_generales').val();
    url+= '&precio_desde='+$('#precio_desde_ventas_generales').val();
    url+= '&precio_hasta='+$('#precio_hasta_ventas_generales').val();
    url+= '&id_nit='+ id_nit;
    url+= '&id_resolucion='+ id_resolucion;
    url+= '&id_bodega='+ id_bodega;
    url+= '&id_usuario='+ id_usuario;
    url+= '&id_producto='+ id_producto;
    url+= '&consecutivo='+$('#consecutivo_ventas_generales').val();
    url+= '&generar='+generarVentasGenerales;
    
    ventas_generales_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando informe de ventas generales', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});


