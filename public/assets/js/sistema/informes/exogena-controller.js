var exogena_table = null;
var $comboFormatoExogena = null;
var $comboConceptoExogena = null;
var $comboYearExogena = null;
var $comboNitExogena = null;
var channelExogena = pusher.subscribe('informe-exogena-'+localStorage.getItem("notificacion_code"));

function exogenaInit() {
    $('.water').hide();

    initTablesExogena();
    initCombosExogena();
    initPusherExogena();
}

function onLoadGridInforme(formatoData) {
    
    var table = $('#ExogenaInformeTable').DataTable();
    if (!table) return;

    // 1. PRIMERO hacer reset: mostrar solo columnas básicas
    for (var i = 0; i < table.columns().count(); i++) {
        table.column(i).visible(i <= 2); // Solo mostrar primeras 3 columnas
    }

    // 2. LUEGO mostrar columnas según el formato
    if (formatoData) {
        // Columnas básicas de información
        if (formatoData.tipo_documento) table.column(3).visible(true);
        if (formatoData.numero_documento) table.column(4).visible(true);
        if (formatoData.digito_verificacion) table.column(5).visible(true);
        if (formatoData.primer_apellido || formatoData.segundo_apellido || 
            formatoData.primer_nombre || formatoData.otros_nombres) {
            table.column(6).visible(true);
        }
        if (formatoData.direccion) table.column(7).visible(true);
        if (formatoData.departamento) table.column(8).visible(true);
        if (formatoData.municipio) table.column(9).visible(true);
        if (formatoData.pais) table.column(10).visible(true);

        // Columnas específicas con datos
        if (formatoData.columnas && formatoData.columnas.length > 0) {
            formatoData.columnas.forEach(function(columnaFormato) {
                var columnIndex = getColumnIndexByName(columnaFormato.columna, table);
                if (columnIndex !== -1) {
                    table.column(columnIndex).visible(true);
                }
            });
        }
    }
    
    table.draw();
}
function initCombosExogena() {
    $comboFormatoExogena = $('#id_formato_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un formato",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/exogena/formato',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboConceptoExogena = $('#id_concepto_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un concepto",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/exogena/concepto',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboYearExogena = $('#id_year_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un año",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
            }
        },
        ajax: {
            url: 'api/year-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboNitExogena = $('#id_nit_exogena').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione una Cédula/nit",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Debes ingresar más caracteres...";
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
}

function initTablesExogena() {
    exogena_table = $('#ExogenaInformeTable').DataTable({
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
            url: base_url + 'exogena',
            headers: headers,
            data: function ( d ) {
                d.year = $('#id_year_exogena').val();
                d.id_formato = $('#id_formato_exogena').val();
                d.id_concepto = $('#id_concepto_exogena').val();
                d.id_nit = $('#id_nit_exogena').val();
            }
        },
        "columns": [
            {data: 'concepto', name: 'concepto'},
            {data: 'cuenta', name: 'cuenta'},
            {
                data: null,
                name: 'nombre',
                render: function(row, type, set, col) {
                    if (row.primer_nombre) {
                        return `${row.primer_nombre}`;
                    }
                    return '';
                }
            },
            {data: 'tipo_documento', name: 'tipo_documento'},
            {data: 'numero_documento', name: 'numero_documento'},
            {data: 'digito_verificacion', name: 'digito_verificacion'},
            {
                data: null,
                name: 'nombre_completo',
                render: function(row, type, set, col) {
                    if (row.primer_nombre) {
                        return `${row.primer_nombre} - ${row.primer_apellido}`;
                    }
                    return '';
                }
            },
            {data: 'direccion', name: 'direccion'},
            {data: 'departamento', name: 'departamento'},
            {data: 'municipio', name: 'municipio'},
            {data: 'pais', name: 'pais'},
            {data: 'pago_cuenta_deducible', name: 'pago_cuenta_deducible', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pago_cuenta_no_deducible', name: 'pago_cuenta_no_deducible', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'iva_mayor_deducible', name: 'iva_mayor_deducible', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'iva_mayor_no_deducible', name: 'iva_mayor_no_deducible', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'retencion_practicada_renta', name: 'retencion_practicada_renta', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'retencion_asumida_renta', name: 'retencion_asumida_renta', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'retencion_iva_practicado_comun', name: 'retencion_iva_practicado_comun', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'retencion_practicada_iva_no_domiciliado', name: 'retencion_practicada_iva_no_domiciliado', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'valor_acumulado_del_pago', name: 'valor_acumulado_del_pago', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'retencion_en_la_fuente_que_le_practicaron', name: 'retencion_en_la_fuente_que_le_practicaron', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'impuesto_descontable', name: 'impuesto_descontable', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'iva_descontable_por_devoluciones_en_ventas', name: 'iva_descontable_por_devoluciones_en_ventas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'impuesto_generado', name: 'impuesto_generado', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'iva_generado_por_devoluciones_en_compras', name: 'iva_generado_por_devoluciones_en_compras', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'impuesto_al_consumo', name: 'impuesto_al_consumo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'ingresos_brutos_recibidos', name: 'ingresos_brutos_recibidos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'devoluciones_rebajas_y_descuentos', name: 'devoluciones_rebajas_y_descuentos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'saldo_cuentas_por_cobrar', name: 'saldo_cuentas_por_cobrar', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'saldo_cuentas_por_pagar', name: 'saldo_cuentas_por_pagar', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'saldo', name: 'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'valor', name: 'valor', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_salario', name: 'pagos_por_salario', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_emolumentos_eclesiasticos', name: 'pagos_por_emolumentos_eclesiasticos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_honorarios', name: 'pagos_por_honorarios', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_servicios', name: 'pagos_por_servicios', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_comisiones', name: 'pagos_por_comisiones', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_prestaciones_sociales', name: 'pagos_por_prestaciones_sociales', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_viaticos', name: 'pagos_por_viaticos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_gastos_de_representacion', name: 'pagos_por_gastos_de_representacion', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_por_compensaciones', name: 'pagos_por_compensaciones', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'otros_pagos', name: 'otros_pagos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pagos_realizados_con_bonos_electronicos', name: 'pagos_realizados_con_bonos_electronicos', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'cesantias_e_intereses_de_cesantias', name: 'cesantias_e_intereses_de_cesantias', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'pensiones_de_jubilacion_vejez_o_invalidez', name: 'pensiones_de_jubilacion_vejez_o_invalidez', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'aportes_obligatorios_por_salud', name: 'aportes_obligatorios_por_salud', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'aportes_obligatorios_a_fondos_de_pensiones', name: 'aportes_obligatorios_a_fondos_de_pensiones', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'aportes_voluntarios_a_fondos_de_pensiones_voluntarias', name: 'aportes_voluntarios_a_fondos_de_pensiones_voluntarias', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'aportes_a_cuentas_afc', name: 'aportes_a_cuentas_afc', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {data: 'valor_de_las_retenciones_en_la_fuente', name: 'valor_de_las_retenciones_en_la_fuente', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'}
        ],
        createdRow: function(row, data, dataIndex) {
            // Agregar atributos data-name a las celdas para facilitar la búsqueda
            $(row).find('td').each(function(i) {
                var column = exogena_table.settings().init().columns[i];
                if (column && column.name) {
                    $(this).attr('data-name', column.name);
                }
            });
        }
    });
}

function initPusherExogena() {
    channelExogena.bind('notificaciones', function(data) {
        if(data.url_file){
            loadExcel(data);
            return;
        }
        if(data.id_exogena){
            $('#id_exogena_cargado').val(data.id_exogena);
            loadExogenaById(data.id_exogena);
            return;
        }
        if(data.tipo == 'error'){
            console.log('data: ',data);
        }
    });
}

function loadExogenaById(id_exogena) {
    $('#id_exogena_cargado').val(id_exogena);
    exogena_table.ajax.url(base_url + 'exogena-show?id='+id_exogena).load(function(res) {
        if(res.success){
            $("#generarExogena").show();
            $("#generarExogenaLoading").hide();

            // OCULTAR/MOSTRAR COLUMNAS BASADO EN EL FORMATO DEL BACKEND
            if (res.formato_data) {
                aplicarConfiguracionColumnas(res.formato_data);
            }

            if ($("#tipo_informe_exogena").val() != '3') {
                if(res.descuadre) {
                    Swal.fire('Exogena descuadrado', '', 'warning');
                } else {
                    agregarToast('exito', 'Exogena cargada', 'Informe cargado con exito!', true);
                }
            }
        }
    });
}

// Función para aplicar la configuración de columnas
function aplicarConfiguracionColumnas(formatoData) {
    var table = $('#ExogenaInformeTable').DataTable();
    if (!table) return;

    // 1. Ocultar todas las columnas excepto las básicas (0-2)
    for (var i = 3; i < table.columns().count(); i++) {
        table.column(i).visible(false);
    }

    // 2. Mostrar columnas básicas según el formato
    if (formatoData.tipo_documento) table.column(3).visible(true);
    if (formatoData.numero_documento) table.column(4).visible(true);
    if (formatoData.digito_verificacion) table.column(5).visible(true);
    
    // Columna "nombre_completo" (índice 6) - Mostrar si hay cualquier dato de nombre
    if (formatoData.primer_apellido || formatoData.segundo_apellido || 
        formatoData.primer_nombre || formatoData.otros_nombres) {
        table.column(6).visible(true);
    }
    
    if (formatoData.direccion) table.column(7).visible(true);
    if (formatoData.departamento) table.column(8).visible(true);
    if (formatoData.municipio) table.column(9).visible(true);
    if (formatoData.pais) table.column(10).visible(true);

    // 3. Mostrar columnas específicas del formato
    if (formatoData.columnas && formatoData.columnas.length > 0) {
        formatoData.columnas.forEach(function(columnaFormato) {
            var columnIndex = getColumnIndexByName(columnaFormato.columna, table);
            if (columnIndex !== -1) {
                table.column(columnIndex).visible(true);
            }
        });
    }

    table.draw();
}

function generateExogena() {
    var form = document.querySelector('#exogenaFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#generarExogena").hide();
    $("#generarExogenaLoading").show();

    var url = base_url + 'exogena';
    url+= '?year='+$('#id_year_exogena').val();
    url+= '&id_formato='+$('#id_formato_exogena').val();
    url+= '&id_concepto='+$('#id_concepto_exogena').val();
    url+= '&id_nit='+$('#id_nit_exogena').val();

    exogena_table.ajax.url(url).load(function(res) {
        if(res.success) {
            agregarToast('info', 'Generando informe', 'En un momento se le notificará cuando el informe esté generado...', true );
        }
    });
}
// Modifica también la función getColumnIndexByName
function getColumnIndexByName(columnName, table) {
    if (!table) {
        table = $('#ExogenaInformeTable').DataTable();
    }
    
    for (var i = 0; i < table.columns().count(); i++) {
        var columnHeader = $(table.column(i).header());
        if (columnHeader.data('name') === columnName) {
            return i;
        }
    }
    return -1;
}

$(document).on('click', '#generarExogena', function () {
    generateExogena();
});
