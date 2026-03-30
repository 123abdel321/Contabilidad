var fechaDesde = null;
var resultados_table = null;

function resumencarteraInit() {
    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarResultados = false;
    resultadosExistente = false;

    $('#fecha_hasta_resumen_cartera').val(fechaDesde);
    $('#id_nit_resumen_cartera').val('')

    const columnas = [
        { data: 'numero_documento' },
        { data: 'nombre_nit' },
        { data: 'ubicacion' } // si tienes esta columna
    ];

    // Generar dinámicamente las columnas cuenta_1 a cuenta_30
    for (let i = 1; i <= 30; i++) {
        columnas.push({
            data: `cuenta_${i}`,
            render: $.fn.dataTable.render.number(',', '.', 2, ''),
            className: 'dt-body-right'
        });
    }

    // Agregar columnas finales
    columnas.push(
        { 
            data: 'total_abono',
            render: $.fn.dataTable.render.number(',', '.', 2, ''),
            className: 'dt-body-right'
        },
        { data: 'fecha_manual' },
        { 
            data: 'saldo_anterior',
            render: $.fn.dataTable.render.number(',', '.', 2, ''),
            className: 'dt-body-right'
        },
        { 
            data: 'saldo_final',
            render: $.fn.dataTable.render.number(',', '.', 2, ''),
            className: 'dt-body-right'
        },
        { data: 'dias_mora' },

    );

    resultados_table = $('#resumenCarteraInformeTable').DataTable({
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
        ajax:  {
            type: "GET",
            url: base_url + 'resumen-cartera',
            headers: headers,
            data: function ( d ) {
                var data = {

                    draw: d.draw,
                    start: d.start,
                    length: d.length,
            // Solo estos parámetros, NADA de d.columns
                    fecha_desde: $('#fecha_desde_resumen_cartera').val(),
                    fecha_hasta: $('#fecha_hasta_resumen_cartera').val(),
                    ubicaciones: getUbicacionesResumenCartera(),
                    proveedor: getProveedoresResumenCartera(),
                    dias_mora: $('#mora_resumen_cartera').val(),
                    id_nit: $('#id_nit_resumen_cartera').val()
                };
                return data;
            }
        },
        
        "rowCallback": function(row, data, index){
            // if (parseFloat(data.saldo_final) < 0) {
            //     $('td', row).css('background-color', '#ff00004d');
            //     $('td', row).css('color', 'black');
            //     return;
            // }
            if(data.nombre_nit == "TOTAL"){
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
            }
        },
        "columns": columnas
    });

    if (!ubicacion_maximoph_resumen_cartera) {
        resultados_table.column(2).visible(false);
    }

    for (let index = 0; index < 30; index++) {
        const newIndex = index + 3;
        resultados_table.column(newIndex).visible(false);
    }    

    $('#id_nit_resumen_cartera').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un nit",
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

    $("#tipo_informe_resumen_cartera").on('change', function(){
        const tipoInforme = parseInt($("#tipo_informe_resumen_cartera").val());

        $('#id_nit_resumen_cartera').val('').trigger('change');

        if(tipoInforme == 1){
            $('#nitAuxiliarDiv').hide();
            $('#fechaDesdeDiv').hide();        
            $('#fecha_desde_resumen_cartera').val('');
        } else if(tipoInforme == 2){
            var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("01").slice(-2);
            
            $('#fechaDesdeDiv').show();
            $('#nitAuxiliarDiv').show();
            $('#fecha_desde_resumen_cartera').val(fechaDesde);
        }
    });

    $('#id_nit_resumen_cartera').val('2').trigger('change');
}

function mostrarCuentas(cuentas) {
    for (let index = 0; index < cuentas.length; index++) {
        const cuenta = cuentas[index];
        const columnaId = index + 1;
        const columnaTable = index + 3;

        resultados_table.column(columnaTable).visible(true);
        $(`#cuenta_${columnaId}`).attr('title', `${cuenta.cuenta} - ${cuenta.nombre_cuenta}`);
        $(`#cuenta_${columnaId}`).html(`${cuenta.nombre_cuenta}`);
    }

    resultados_table.columns.adjust().draw();

    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
    });
}

function loadResumenCarteraById(id_resumen_cartera) {
    
    $('#id_documento_general_cargado').val(id_resumen_cartera);

    var url = base_url + 'resumen-cartera-show?id='+id_resumen_cartera;
    resultados_table.ajax.url(url).load(function(res) {
        if(res.success){
            const cuentas = res.cuentas;
            if (cuentas && cuentas.length) {
                mostrarCuentas(cuentas);
            }

            $('#descargarExcelResumenCartera').show();
            $('#descargarExcelResumenCarteraDisabled').hide();
            agregarToast('exito', 'Resumen cartera cargado', 'Informe cargado con exito!', true);
        }
    });
}

$(document).on('click', '#descargarExcelResumenCartera', function () {

    for (let index = 0; index < 30; index++) {
        const newIndex = index + 3;
        resultados_table.column(newIndex).visible(false);
    }

    $.ajax({
        url: base_url + 'resumen-cartera-excel',
        method: 'POST',
        data: JSON.stringify({
            id: $('#id_documento_general_cargado').val(),
            tipo_informe: $("#tipo_informe_resumen_cartera").val(),
            id_nit: $('#id_nit_resumen_cartera').val(),
            fecha_desde: $('#fecha_desde_resumen_cartera').val(),
            fecha_hasta: $('#fecha_hasta_resumen_cartera').val()
        }),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            if(res.url_file){
                window.open('https://'+res.url_file, "_blank");
                return; 
            }
            agregarToast('info', 'Generando excel', res.message, true);
        }
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
        agregarToast('error', 'Error al generar excel', errorsMsg);
    });
});

$(document).on('click', '#resumenCarteraGenerales', function () {
    $('#resumenCarteraGenerales').hide();
    $('#resumenCarteraGeneralesLoading').show();

    $("#descargarExcelResumenCartera").hide();
    $("#descargarExcelResumenCarteraDisabled").show();

    var url = base_url + 'resumen-cartera';
    url+= '?fecha_hasta='+$('#fecha_hasta_resumen_cartera').val();

    marcarFilasNoVisibles();
    actualizarTipoInformeResumenCartera();

    resultados_table.ajax.url(url).load(function(res) {
        $('#resumenCarteraGenerales').show();
        $('#resumenCarteraGeneralesLoading').hide();
        if(res.success) {
            agregarToast('info', 'Generando resumen de cartera', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
});

var channelResumenComprobante = pusher.subscribe('informe-resumen-cartera-'+localStorage.getItem("notificacion_code"));

channelResumenComprobante.bind('notificaciones', function(data) {
    if(data.url_file){
        loadExcel(data);
        return;
    }
    if(data.id_resumen_cartera){
        $('#id_documento_general_cargado').val(data.id_resumen_cartera);
        loadResumenCarteraById(data.id_resumen_cartera);
        return;
    }
});

function getUbicacionesResumenCartera() {
    if($("input[type='radio']#ubicaciones_resumen_cartera0").is(':checked')) return '';
    if($("input[type='radio']#ubicaciones_resumen_cartera1").is(':checked')) return 1;

    return '';
}

function getProveedoresResumenCartera() {
    if($("input[type='radio']#proveedores_resumen_cartera0").is(':checked')) return '';
    if($("input[type='radio']#proveedores_resumen_cartera1").is(':checked')) return 1;

    return '';
}

function marcarFilasNoVisibles() {
    for (let index = 0; index < 30; index++) {
        const newIndex = index + 3;
        resultados_table.column(newIndex).visible(false);
    }
}

function actualizarTipoInformeResumenCartera() {
    const tipoInforme = $("#tipo_informe_resumen_cartera").val();

    const columnaNombreNit = resultados_table.column(1);
    const columnaUbicacion = resultados_table.column(2);
    const columnaTotalAbono = resultados_table.column(33);
    const columnaFechaManual = resultados_table.column(34);
    const columnaSaldoAnterior = resultados_table.column(35);
    const columnaMora = resultados_table.column(37);

    if(tipoInforme == 1){

        $("#one_colum_resumen_cartera").text("Documento");

        columnaMora.visible(true);
        columnaNombreNit.visible(true);
        columnaTotalAbono.visible(false);
        columnaFechaManual.visible(false);
        columnaSaldoAnterior.visible(false);
        if (ubicacion_maximoph_resumen_cartera) {
            columnaUbicacion.visible(true);
        }
    } else if(tipoInforme == 2){

        $("#one_colum_resumen_cartera").text("Mes");

        columnaMora.visible(false);
        columnaTotalAbono.visible(true);
        columnaFechaManual.visible(true);
        columnaSaldoAnterior.visible(true);
        columnaNombreNit.visible(false);
        columnaUbicacion.visible(false);
    }
}