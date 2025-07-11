var fechaDesde = null;
var impuestos_table = null;
var generarImpuestos = false;
var impuestosExistente = false;

function impuestosInit() {
    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarImpuestos = false;
    impuestosExistente = false;

    $('#fecha_desde_impuestos').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_impuestos').val(fechaDesde);

    impuestos_table = $('#ImpuestosInformeTable').DataTable({
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
        'rowCallback': function(row, data, index){
            var nivel = getNivelImpuestos();
            if (nivel == 1) {
                if (data.nivel == 0) {
                    $('td', row).css('background-color', 'rgb(28 69 135)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if (data.errores) $('td', row).css('background-color', 'rgb(209 64 64 / 40%)');
            } else if (nivel == 2) {
                if (data.nivel == 0) {
                    $('td', row).css('background-color', 'rgb(28 69 135)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.nivel == 1){
                    if (data.errores) $('td', row).css('background-color', 'rgb(209 64 64 / 40%)');
                    else $('td', row).css('background-color', 'rgb(64 164 209 / 40%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
            } else if (nivel == 3) {
                if (data.nivel == 0) {
                    $('td', row).css('background-color', 'rgb(28 69 135)');
                    $('td', row).css('font-weight', 'bold');
                    $('td', row).css('color', 'white');
                    return;
                }
                if(data.nivel == 1){
                    if (data.errores) $('td', row).css('background-color', 'rgb(209 64 64 / 40%)');
                    else $('td', row).css('background-color', 'rgb(64 164 209 / 70%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
                if(data.nivel == 2){
                    $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
                    $('td', row).css('font-weight', 'bold');
                    return;
                }
            }
        },
        ajax:  {
            type: "GET",
            url: base_url + 'impuestos',
            headers: headers,
            data: function( result ) {
                return result;
            }
        },
        "columns": [
            {"data": function (row, type, set){
                var agrupado = $('#agrupar_impuestos').val();
                if (agrupado == 'id_cuenta') {
                    if (row.nivel == 1) {
                        return row.cuenta;
                    } else {
                        return row.numero_documento;
                    }
                }
                if (agrupado == 'id_nit') {
                    if (row.nivel == 1) {
                        return row.numero_documento;
                    } else {
                        return row.cuenta;
                    }
                }
                return '';
            }},
            {"data": function (row, type, set){
                var agrupado = $('#agrupar_impuestos').val();
                if (agrupado == 'id_cuenta') {
                    if (row.nivel == 1) {
                        return row.nombre_cuenta;
                    } else {
                        return row.nombre_nit;
                    }
                }
                if (agrupado == 'id_nit') {
                    if (row.nivel == 1) {
                        return row.nombre_nit;
                    } else {
                        return row.nombre_cuenta;
                    }
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.nit) {
                    if (row.nit.actividad_economica) {
                        return row.nit.actividad_economica.nombre;
                    }
                }
                return '';
            }},
            {data: 'saldo_anterior', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 5, targets: -4},
            {data: 'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 5, targets: -4},
            {data: 'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 5, targets: -4},
            {data: 'valor_base', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 5, targets: -4},
            {data: 'porcentaje_base', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 5, targets: -4},
            {data: 'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 5, targets: -4},
            {data: 'fecha_manual'},
            {"data": function (row, type, set){
                if (row.nivel == 3) {
                    return row.consecutivo;
                }
            }},
            {"data": function (row, type, set){
                if (row.nivel == 3) {
                    if (row.codigo_comprobante) {
                        return row.codigo_comprobante+' - '+row.nombre_comprobante;
                    }
                }
            }},
            {"data": function (row, type, set){
                if (row.nivel == 3) {
                    return row.concepto
                }
                return '';
            }}
        ]
    });

    $('#id_cuenta_impuestos').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cuenta",
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            data: function (params) {
                var query = {
                    search: params.term,
                    total_cuentas: true,
                    id_tipo_cuenta: tipoCuentaInforme()
                }
                return query;
            },
            dataType: 'json',
            headers: headers,
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_nit_impuestos').select2({
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

    $("#nivel_impuestos1").on('change', function(){
        actualizarColumnas();
        document.getElementById("generarImpuestos").click();
    });
    
    $("#nivel_impuestos2").on('change', function(){
        actualizarColumnas();
        document.getElementById("generarImpuestos").click();
    });

    $("#tipo_informe_impuestos").on('change', function(){
        actualizarColumnas();
    });

    $("#nivel_impuestos3").on('change', function(){
        actualizarColumnas();
        document.getElementById("generarImpuestos").click();
    });

    $(".agrupar_impuestos").on('change', function(){
        var agrupado = $("#agrupar_impuestos").val();
        if (agrupado == 'id_cuenta') {
            $("#nombre_item_impuestos").text("Cuenta");
        }
        if (agrupado == 'id_nit') {
            $("#nombre_item_impuestos").text("Documento");
        }
        actualizarColumnas();
        document.getElementById("generarImpuestos").click();
    });

    $("#id_cuenta_impuestos").on('change', function(){
        clearImpuestos();
        findImpuestos();
    });
    
    $("#id_nit_impuestos").on('change', function(){
        clearImpuestos();
        findImpuestos();
    });    
    
    $(".detallar_impuestos").on('change', function(){
        clearImpuestos();
        findImpuestos();
    });

    findImpuestos();
    actualizarColumnas();
}

function tipoCuentaInforme() {
    let tipoInforme = $("#tipo_informe_impuestos").val();
    if (tipoInforme == 'iva') return [9,16];
    if (tipoInforme == 'retencion') return [12,13];
    if (tipoInforme == 'reteica') return [17];
}

function actualizarColumnas() {
    var nivel = getNivelImpuestos();
    let tipoInforme = $("#tipo_informe_impuestos").val();

    var columnActividadEconomica = impuestos_table.column(2);
    var columnComprobante = impuestos_table.column(9);
    var columnFechaManul = impuestos_table.column(10);
    var columnConcecutivo = impuestos_table.column(11);
    var columnConcepto = impuestos_table.column(12);
    
    if (tipoInforme == 'reteica') columnActividadEconomica.visible(true);
    else columnActividadEconomica.visible(false);

    if (nivel == 1 || nivel == 2) {
        columnComprobante.visible(false);
        columnFechaManul.visible(false);
        columnConcecutivo.visible(false);
        columnConcepto.visible(false);
    }
    if (nivel == 3) {
        columnComprobante.visible(true);
        columnFechaManul.visible(true);
        columnConcecutivo.visible(true);
        columnConcepto.visible(true);
    }
}

function loadImpuestosById(id_impuesto) {
    var url = base_url + 'impuestos-show?id='+id_impuesto;

    impuestos_table.ajax.url(url).load(function(res) {
        
        if(res.success){
            $("#generarImpuestos").show();
            $("#generarImpuestosLoading").hide();
            $("#generarImpuestosUltimoLoading").hide();
            $('#descargarExcelImpuestos').prop('disabled', false);
            $("#descargarExcelImpuestos").show();
            $("#descargarExcelImpuestosDisabled").hide();
            $('#generarImpuestosUltimo').hide();
            $('#generarImpuestosUltimoLoading').hide();

            agregarToast('exito', 'Impuestos cargado', 'Informe cargado con exito!', true);
            
            mostrarTotalesImpuestos(res.totales);
        }
    });
}

function mostrarTotalesImpuestos(data) {
    if(!data) {
        return;
    }

    $("#impuestos_anterior").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_anterior));
    $("#impuestos_debito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.debito));
    $("#impuestos_credito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.credito));
    $("#impuestos_diferencia").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo));
}

$(document).on('click', '#generarImpuestos', function () {
    generarImpuestos = false;
    $("#generarImpuestos").hide();
    $("#generarImpuestosLoading").show();
    $('#descargarExcelImpuestos').prop('disabled', true);
    $("#descargarExcelImpuestos").hide();
    
    $("#impuestos_anterior").text('$0');
    $("#impuestos_facturas").text('$0');
    $("#impuestos_abonos").text('$0');
    $("#impuestos_diferencia").text('$0');

    var url = base_url + 'impuestos';
    url+= '?id_nit='+$('#id_nit_impuestos').val();
    url+= '&id_cuenta='+$('#id_cuenta_impuestos').val();
    url+= '&fecha_desde='+$('#fecha_desde_impuestos').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_impuestos').val();
    url+= '&agrupar_impuestos='+$('#agrupar_impuestos').val();
    url+= '&tipo_informe='+$("#tipo_informe_impuestos").val();
    url+= '&nivel='+getNivelImpuestos();

    impuestos_table.ajax.url(url).load(function(res) {
        if(res.success) {
            if(res.data){
                Swal.fire({
                    title: '¿Cargar Impuestos?',
                    text: "Impuestos generado anteriormente ¿Desea cargarlo?",
                    type: 'info',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Cargar',
                    cancelButtonText: 'Generar nuevo',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.value){
                        $('#id_impuesto_cargado').val(res.data);
                        loadImpuestosById(res.data);
                    } else {
                        generarImpuestos = true;
                        GenerateImpuestos();
                    }
                })
            } else {
                agregarToast('info', 'Generando impuestos', 'En un momento se le notificará cuando el informe esté generado...', true );
            }
        }
    });

});

function findImpuestos() {
    impuestosExistente = false;
    $('#generarImpuestosUltimo').hide();
    $('#generarImpuestosUltimoLoading').show();

    var url = base_url + 'impuestos-find';
    url+= '?id_nit='+$('#id_nit_impuestos').val();
    url+= '&id_cuenta='+$('#id_cuenta_impuestos').val();
    url+= '&fecha_desde_impuestos='+$('#fecha_desde_impuestos').val();
    url+= '&agrupar_impuestos='+$('#agrupar_impuestos').val();
    url+= '&tipo_informe='+$("#tipo_informe_impuestos").val();
    url+= '&nivel='+getNivelImpuestos();
    
    $.ajax({
        url: url,
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#generarImpuestosUltimoLoading').hide();
        if(res.data){
            impuestosExistente = res.data;
            $('#generarImpuestosUltimo').show();
        }
    }).fail((err) => {
        $('#generarImpuestosUltimoLoading').hide();
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
        agregarToast('error', 'Error al consultar impuestos', errorsMsg, true);
    });
}

function GenerateImpuestos() {
    var url = base_url + 'impuestos-find';
    url+= '?id_nit='+$('#id_nit_impuestos').val();
    url+= '&id_cuenta='+$('#id_cuenta_impuestos').val();
    url+= '&fecha_desde='+$('#fecha_desde_impuestos').val();
    url+= '&fecha_hasta='+$('#fecha_hasta_impuestos').val();
    url+= '&agrupar='+$('#agrupar_impuestos').val();
    url+= '&tipo_informe='+$("#tipo_informe_impuestos").val();
    url+= '&nivel='+getNivelImpuestos();
    url+= '&generar='+generarImpuestos;

    impuestos_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando impuestos', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
}

$(document).on('click', '#generarImpuestosUltimo', function () {
    $('#generarImpuestosUltimo').hide();
    $('#generarImpuestosUltimoLoading').show();
    loadImpuestosById(impuestosExistente);
});

var channelImpuestos = pusher.subscribe('informe-impuestos-'+localStorage.getItem("notificacion_code"));

channelImpuestos.bind('notificaciones', function(data) {
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_impuestos){
        $('#id_impuestos_cargado').val(data.id_impuestos);
        loadImpuestosById(data.id_impuestos);
        return;
    }
});

function clearImpuestos() {
    $("#descargarExcelImpuestos").hide();
    $("#descargarExcelImpuestosDisabled").show();
}

function getNivelImpuestos() {
    if($("input[type='radio']#nivel_impuestos1").is(':checked')) return 1;
    if($("input[type='radio']#nivel_impuestos2").is(':checked')) return 2;
    if($("input[type='radio']#nivel_impuestos3").is(':checked')) return 3;

    return false;
}

function formatNitImpuestos (nit) {
    
    if (nit.loading) return nit.text;

    if (ubicacion_maximoph_impuestos) {
        if (nit.apartamentos) return nit.text+' - '+nit.apartamentos;
        else return nit.text;
    }
    else return nit.text;
}

function formatRepoImpuestos (nit) {
    return nit.full_name || nit.text;
}