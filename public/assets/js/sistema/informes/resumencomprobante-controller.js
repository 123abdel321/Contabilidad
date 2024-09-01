var $comboNitResumen = null;
var $comboCuentaResumen = null;
var initResumenComprobante = false;
var $comboComprobanteResumen = null;
var resumen_comprobante_table = null;
var generarResumenComprobante = false;

function resumencomprobanteInit() {

    $('#fecha_desde_comprobantes').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_comprobantes').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2));

    resumen_comprobante_table = $('#comprobantesInformeTable').DataTable({
        pageLength: 100,
        dom: 'Brtip',
        paging: true,
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
        colReorder: true,
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
            url: base_url + 'resumen-comprobante',
            headers: headers,
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_comprobantes').val();
                d.fecha_hasta = $('#fecha_hasta_comprobantes').val();
                d.id_comprobante = $('#id_comprobante_comprobantes').val();
                d.id_cuenta = $('#id_cuenta_comprobantes').val();
                d.id_nit = $('#id_nit_comprobantes').val();
                d.generar = generarResumenComprobante;
                d.agrupado = $('#agrupar_comprobantes').val();
                d.detallar = getDetallarResumen();
                d.init = initResumenComprobante;
            }
        },
        columns: [
            { data: 'cuenta'},
            { data: 'nombre_cuenta'},
            { data: 'numero_documento'},
            { data: 'nombre_nit'},
            { data: 'apartamento_nit'},
            { data: 'consecutivo'},
            { data: 'fecha_manual'},
            { data: 'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'diferencia', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            { data: 'concepto'},
            { data: 'registros'}
        ],
        'rowCallback': function(row, data, index){
            if(data.nivel == 4){
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.nivel == 3){
                $('td', row).css('background-color', 'rgb(64 164 209 / 70%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.nivel == 2){
                $('td', row).css('background-color', 'rgb(64 164 209 / 40%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.nivel == 1){
                $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
        },
        columnDefs: [{
            'orderable': false
        }]
    });

    $comboNitResumen = $('#id_nit_comprobantes').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
        ajax: {
            url: 'api/nit/combo-nit',
            dataType: 'json',
            headers: headers,
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboCuentaResumen = $('#id_cuenta_comprobantes').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            data: function (params) {
                var query = {
                    search: params.term,
                    auxiliar: true,
                }
                return query;
            },
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboComprobanteResumen = $('#id_comprobante_comprobantes').select2({
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
}

var channelResumenComprobante = pusher.subscribe('informe-resumen-comprobantes-'+localStorage.getItem("notificacion_code"));

channelResumenComprobante.bind('notificaciones', function(data) {
    if(data.url_file){
        // loadExcel(data);
        return;
    }
    if(data.id_resumen_comprobante){
        $('#id_resumen_comprobante').val(data.id_resumen_comprobante);
        loadResumenComprobanteById(data.id_resumen_comprobante);
        return;
    }
});

function loadResumenComprobanteById(id_resumen_comprobante) {
    resumen_comprobante_table.ajax.url(base_url + 'resumen-comprobante-show?id='+id_resumen_comprobante).load(function(res) {
        if(res.success){
            agregarToast('exito', 'Resumen de comprobantes cargado', res.mensaje, true);

            var agrupado = $('#agrupar_comprobantes').val();
            var tabla = resumen_comprobante_table;

            if (getDetallarResumen()) {
                tabla.column(0).visible(true);// CODIGO CUENTA
                tabla.column(1).visible(true);// NOMBRE CUENTA
                tabla.column(2).visible(true);// DOCUMENTO NIT
                tabla.column(3).visible(true);// NOMBRE  NIT
                tabla.column(4).visible(true);// UBICACION
                tabla.column(5).visible(true);// CONSECUTIVO
                tabla.column(6).visible(true);// FECHA MANUAL
                tabla.column(10).visible(true);// CONCEPTO
            } else {
                tabla.column(0).visible(false);// CODIGO CUENTA
                tabla.column(1).visible(false);// NOMBRE CUENTA
                tabla.column(2).visible(false);// DOCUMENTO NIT
                tabla.column(3).visible(false);// NOMBRE  NIT
                tabla.column(4).visible(true);// UBICACION
                tabla.column(5).visible(false);// CONSECUTIVO
                tabla.column(6).visible(false);// FECHA MANUAL
                tabla.column(10).visible(false);// CONCEPTO
                
                switch (agrupado) {
                    case 'id_cuenta':
                        tabla.column(0).visible(true);
                        tabla.column(1).visible(true);
                        break;
                    case 'id_nit':
                        tabla.column(2).visible(true);
                        tabla.column(3).visible(true);
                        var columnUbicacionMaximoPH = tabla.column(4);
                        if (ubicacion_maximoph_resumen) columnUbicacionMaximoPH.visible(true);
                        else columnUbicacionMaximoPH.visible(false);
                        break;
                    case 'consecutivo':
                        tabla.column(2).visible(true);
                        tabla.column(3).visible(true);
                        var columnUbicacionMaximoPH = tabla.column(4);
                        if (ubicacion_maximoph_resumen) columnUbicacionMaximoPH.visible(true);
                        else columnUbicacionMaximoPH.visible(false);
                        tabla.column(5).visible(true);
                        tabla.column(6).visible(false);
                        tabla.column(10).visible(false);
                        break;
                    default:
                        var columnUbicacionMaximoPH = tabla.column(4);
                        if (ubicacion_maximoph_resumen) columnUbicacionMaximoPH.visible(true);
                        else columnUbicacionMaximoPH.visible(false);
                        tabla.column(5).visible(true);
                        tabla.column(6).visible(false);
                        tabla.column(10).visible(false);
                        break;
                }
            }
            
            $('#generarResumenComprobantes').show();
            $('#generarResumenComprobantesLoading').hide();
        }
    });
}

$(document).on('click', '#generarResumenComprobantes', function () {
    $('#generarResumenComprobantes').hide();
    $('#generarResumenComprobantesLoading').show();

    generarEstadoActual = false;
    initResumenComprobante = true;

    var url = base_url + 'resumen-comprobante';

    url+= '?fecha_desde='+$('#fecha_desde_comprobantes').val();
    url+= '?fecha_hasta='+$('#fecha_hasta_comprobantes').val();
    url+= '?id_comprobante='+$('#id_comprobante_comprobantes').val();
    url+= '?id_cuenta='+$('#id_cuenta_comprobantes').val();
    url+= '?id_nit='+$('#id_nit_comprobantes').val();
    url+= '?agrupado='+$('#agrupar_comprobantes').val();
    url+= '?detallar='+ getDetallarResumen();
    url+= '?init='+initResumenComprobante;
    url+= '&generar='+generarResumenComprobante;

    resumen_comprobante_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando resumen de comprobante', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});

function getDetallarResumen() {
    if($("input[type='radio']#detalle_comprobantes0").is(':checked')) return '';
    if($("input[type='radio']#detalle_comprobantes1").is(':checked')) return 1;

    return '';
}


function formatNitResumen (nit) {
    
    if (nit.loading) return nit.text;

    if (ubicacion_maximoph) {
        if (nit.apartamentos) return nit.text+' - '+nit.apartamentos;
        else return nit.text;
    }
    else return nit.text;
}

function formatRepoSelectionResumen (nit) {
    return nit.full_name || nit.text;
}
