var fechaDesde = null;
var resultados_table = null;

function resumencarteraInit() {
    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    generarResultados = false;
    resultadosExistente = false;

    $('#fecha_hasta_resumen_cartera').val(fechaDesde);

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
            data: 'saldo_final',
            render: $.fn.dataTable.render.number(',', '.', 2, ''),
            className: 'dt-body-right'
        },
        { data: 'dias_mora' }
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
                d.fecha_hasta = $('#fecha_hasta_resumen_cartera').val();
                d.ubicaciones = getUbicacionesResumenCartera();
                d.dias_mora = $('#mora_resumen_cartera').val();
            }
        },
        
        "rowCallback": function(row, data, index){
            if (parseFloat(data.saldo_final) < 0) {
                $('td', row).css('background-color', '#ff00004d');
                $('td', row).css('color', 'black');
                return;
            }
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
    var url = base_url + 'resumen-cartera-show?id='+id_resumen_cartera;

    resultados_table.ajax.url(url).load(function(res) {
        if(res.success){
            const cuentas = res.cuentas;
            if (cuentas && cuentas.length) {
                mostrarCuentas(cuentas);
            }

            // $('#descargarExcelResumenCartera').hide();
            // $('#descargarExcelResumenCarteraDisabled').hide();
            agregarToast('exito', 'Resumen cartera cargado', 'Informe cargado con exito!', true);
        }
    });
}

$(document).on('click', '#resumenCarteraGenerales', function () {
    $('#resumenCarteraGenerales').hide();
    $('#resumenCarteraGeneralesLoading').show();

    var url = base_url + 'resumen-cartera';

    url+= '?fecha_hasta='+$('#fecha_hasta_resumen_cartera').val();

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