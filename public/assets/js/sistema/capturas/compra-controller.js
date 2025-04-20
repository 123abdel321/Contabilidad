var fecha = null;
var compra_table = null;
var compra_table_pagos = null;
var validarFacturaCompra = null;
var $comboBodegaCompra = null;
var $comboProveedor = null;
var $comboComprobante  = null;
var guardarCompra = false
var porcentajeRetencionCompras = 0;
var topeRetencionCompras = 0;
var abrirFormasPagoCompras = false;
var guardandoCompra = false;
let productosData = [];
let programmaticChange = false;
let hotCompras = null;

function compraInit () {
    
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_compra').val(fecha);

    const container = document.getElementById('compraTable');
    hotCompras = new Handsontable(container, {
        className: 'ht-theme-horizon',
        colHeaders: ['', 'Producto', 'Invt.', 'Cant.', 'Costo', 'Dscto', '% Dscto', '% Iva', 'Iva', 'Total', 'id'],
        rowHeaders: true,
        startRows: 0,
        width: '100%',
        height: '350px',
        stretchH: 'all',
        contextMenu: false,
        filters: false,
        dropdownMenu: false,
        minSpareRows: 1,
        autoWrapRow: true,
        autoWrapCol: true,
        manualRowResize: true,
        manualColumnResize: true,
        navigableHeaders: true,
        enterMoves: { row: 0, col: 0 },
        licenseKey: 'non-commercial-and-evaluation',
        columns: [
            {
                data: 'eliminar',
                renderer: eliminarCompraRenderer,
                width: 45,
                readOnly: true
            },
            {
                data: 'producto',
                type: 'dropdown',
                strict: true,
                allowInvalid: false,
                copyable: false,
                width: 230,
                source: obtenerComprasProductos,
                renderer: comprasProductosRenderer,
            },
            { data: 'inventario', type: 'numeric', numericFormat: { pattern: '0,0' }, readOnly: true },
            { data: 'cantidad', type: 'numeric', numericFormat: { pattern: '0,0' } },
            { data: 'costo', type: 'numeric', numericFormat: { pattern: '0,0' }, width: 80 },
            { data: 'descuento', type: 'numeric', numericFormat: { pattern: '0,0' }, width: 80 },
            { data: 'porcentaje_descuento', type: 'numeric', numericFormat: { pattern: '0,0' }, width: 80 },
            { data: 'porcentaje_iva', type: 'numeric', numericFormat: { pattern: '0,0' }, readOnly: true },
            { data: 'valor_iva', type: 'numeric', numericFormat: { pattern: '0,0' }, readOnly: true, width: 80 },
            { data: 'total', type: 'numeric', numericFormat: { pattern: '0,0' }, readOnly: true, width: 100 },
            { data: 'id_producto' }
        ],
        afterChange: function(changes, source) {
            if (programmaticChange || source !== 'edit') return;
            programmaticChange = true;
            if (source === 'edit') {
                changes.forEach(function(change) {

                    const row = change[0];
                    const col = change[1];
                    const newValue = change[3];

                    if (col === 'producto') {
                        const productoSeleccionado = productosData.find(function(producto) {
                            return producto.text === newValue;
                        });

                        editarCeldaProductoCompras(productoSeleccionado, newValue, row);
                    }
                    if (col === 'cantidad') {
                        const productoText = hotCompras.getDataAtCell(row, 1);
                        const productoSeleccionado = productosData.find(function(producto) {
                            return producto.text === productoText;
                        });

                        editarCeldaCantidadCompras(productoSeleccionado, newValue, row);
                    }
                    if (col === 'costo') {
                        const productoText = hotCompras.getDataAtCell(row, 1);
                        const productoSeleccionado = productosData.find(function(producto) {
                            return producto.text === productoText;
                        });

                        editarCeldaCostoCompras(productoSeleccionado, newValue, row);
                    }

                    if (col === 'descuento') {
                        const productoText = hotCompras.getDataAtCell(row, 1);
                        const productoSeleccionado = productosData.find(function(producto) {
                            return producto.text === productoText;
                        });

                        editarCeldaDescuentoCompras(productoSeleccionado, newValue, row);
                    }

                    if (col === 'porcentaje_descuento') {
                        const productoText = hotCompras.getDataAtCell(row, 1);
                        const productoSeleccionado = productosData.find(function(producto) {
                            return producto.text === productoText;
                        });

                        editarCeldaDescuentoPorcentajeCompras(productoSeleccionado, newValue, row);
                    }
                });
            }
            setTimeout(() => mostrarValoresCompras(), 20);
            setTimeout(() => programmaticChange = false, 10);
        },
    });

    hotCompras.updateSettings({
        readOnly: true,
    });

    const hotComprasCount = hotCompras.countRows();

    setTimeout(function(){
        hotCompras.alter('remove_row', 0, hotComprasCount);

        const cardCompra = document.querySelector('.wtHolder');
        new PerfectScrollbar(container);
        new PerfectScrollbar(cardCompra);
    },10);

    compra_table_pagos = $('#compraFormaPago').DataTable({
        dom: '',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scrollX: true,
        ordering: false,
        ajax:  {
            type: "GET",
            headers: headers,
            data: {
                type: 'compras'
            },
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){
                return `<input type="number" class="form-control form-control-sm" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoCompra(${row.id})" onfocusout="calcularCompraPagos()" onkeypress="changeFormaPagoCompra(${row.id}, event)" id="compra_forma_pago_${row.id}">`;
            }},
        ],
    });

    $comboComprobante = $('#id_comprobante_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 2,
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

    $comboProveedor = $('#id_proveedor_compra').select2({
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

    $comboBodegaCompra = $('#id_bodega_compra').select2({
        theme: 'bootstrap-5',
        delay: 250,
        minimumInputLength: 2,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 2 o más caracteres";
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

    $("#fecha_manual_compra").on('keydown', function(event) {
        if(event.keyCode == 13){
            event.preventDefault();
            validarFechaManualCompras();
            setTimeout(function(){
                $('#documento_referencia_compra').focus();
                $('#documento_referencia_compra').select();
            },10);
        }
    });

    function validarFechaManualCompras() {
        var fechaManual = $("#fecha_manual_compra").val();
    
        $('#fecha_manual_compra').removeClass("is-valid");
        $('#fecha_manual_compra').removeClass("is-invalid");
    
        if (!fechaManual) {
            $('#fecha_manual_compra').removeClass("is-valid");
            $('#fecha_manual_compra').addClass("is-invalid");
            $('#fecha_manual_compra-feedback').text('La Fecha manual es requerida')
            return;
        }
    
        $.ajax({
            url: base_url + 'anio-cerrado',
            method: 'GET',
            headers: headers,
            dataType: 'json',
        }).done((res) => {
    
            var fechaCierre = new Date(res.data).getTime();
            var fechaManual = new Date($("#fecha_manual_compra").val()).getTime();
    
            if (fechaManual <= fechaCierre) {
                $('#fecha_manual_compra').removeClass("is-valid");
                $('#fecha_manual_compra').addClass("is-invalid");
                $('#fecha_manual_compra-feedback').text('La Fecha se encuentra en un año cerrado');
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    
    }

    $("#documento_referencia_compra").on('keydown', function(event) {
        if(event.keyCode == 13){
            event.preventDefault();

            calcularCompraPagos();
            clearFormasPagoCompras();
            document.getElementById('iniciarCapturaCompra').click();
        }
    });

    $("#compraFilterForm").submit(function(event) {
        event.preventDefault();
    });

    if (!primeraBodegaCompra.length) {
        agregarToast('warning', 'Sin bodegas asignadas', '', true);
    }

    if(primeraBodegaCompra.length > 0){
        var dataBodega = {
            id: primeraBodegaCompra[0].id,
            text: primeraBodegaCompra[0].codigo + ' - ' + primeraBodegaCompra[0].nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodegaCompra.append(newOption).trigger('change');
        $comboBodegaCompra.val(dataBodega.id).trigger('change');
    }

    if(primerComprobanteCompra){
        var dataComprobante = {
            id: primerComprobanteCompra.id,
            text: primerComprobanteCompra.codigo + ' - ' + primerComprobanteCompra.nombre
        };
        var newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
        $comboComprobante.append(newOption).trigger('change');
        $comboComprobante.val(dataComprobante.id).trigger('change');
    }

    if (!agregarDescuentoCompra && !ventaExistenciasCompra) ocultarColumnas([2,5,6,10]);
    else if (!agregarDescuentoCompra) ocultarColumnas([5,6,10]);
    else if (!ventaExistenciasCompra) ocultarColumnas([2,10]);
    else ocultarColumnas([10]);

    loadFormasPagoCompra()

    setTimeout(function(){
        $comboProveedor.select2("open");
    },10);
}

function editarCeldaProductoCompras(productoSeleccionado, newValue, row) {

    if (productoSeleccionado) {
        const inventario = productoSeleccionado.inventarios.length ? parseFloat(productoSeleccionado.inventarios[0].cantidad) : 0;
        const costo = parseFloat(productoSeleccionado.precio_inicial);
        let porcentajeiva = productoSeleccionado.familia.cuenta_compra_iva;
        porcentajeiva = porcentajeiva ? porcentajeiva.impuesto : 0;
        porcentajeiva = porcentajeiva ? porcentajeiva.porcentaje : 0;
        let valorIva = porcentajeiva ? costo * (parseFloat(porcentajeiva) / 100) : 0;
        const total = parseFloat(costo) + parseFloat(valorIva);
        
        hotCompras.setDataAtCell(row, 2, inventario);//INVENTARIO
        hotCompras.setDataAtCell(row, 3, 1);//CANTIDAD
        hotCompras.setDataAtCell(row, 4, costo);//COSTO
        hotCompras.setDataAtCell(row, 5, 0);//DESCUENTO
        hotCompras.setDataAtCell(row, 6, 0);//DESCUENTO %
        hotCompras.setDataAtCell(row, 7, porcentajeiva);//IVA %
        hotCompras.setDataAtCell(row, 8, valorIva);//IVA VALOR
        hotCompras.setDataAtCell(row, 9, total);//TOTAL
        hotCompras.setDataAtCell(row, 10, productoSeleccionado.id);//ID PRODUCTO

        calcularRetencionCompras(productoSeleccionado);

        setTimeout(() => hotCompras.selectCell(row, 3), 10);
    } else celdaVaciaCompras(row);
}

function editarCeldaCantidadCompras(productoSeleccionado, newValue, row) {
    if (productoSeleccionado) {
        const costo = parseFloat(productoSeleccionado.precio_inicial);
        const cantidad = parseFloat(newValue);
        const inventario = parseFloat(hotCompras.getDataAtCell(row, 2));
        const porcentajeiva = parseFloat(hotCompras.getDataAtCell(row, 7));
        const valorDescuento = parseFloat(hotCompras.getDataAtCell(row, 5));
        const porcentajeDescuento = valorDescuento ? valorDescuento * 100 / (costo * cantidad) : 0;
        const valorIva = porcentajeiva ? ((costo - valorDescuento) * cantidad) * (porcentajeiva / 100) : 0;
        const total = ((costo - valorDescuento) * cantidad) + valorIva;
        
        hotCompras.setDataAtCell(row, 2, inventario);//INVENTARIO
        hotCompras.setDataAtCell(row, 3, cantidad);//CANTIDAD
        hotCompras.setDataAtCell(row, 4, costo);//COSTO
        hotCompras.setDataAtCell(row, 5, valorDescuento);//DESCUENTO
        hotCompras.setDataAtCell(row, 6, porcentajeDescuento.toFixed(2));//DESCUENTO %
        hotCompras.setDataAtCell(row, 7, porcentajeiva);//IVA %
        hotCompras.setDataAtCell(row, 8, valorIva);//IVA VALOR
        hotCompras.setDataAtCell(row, 9, total);//TOTAL

        setTimeout(() => hotCompras.selectCell(row, 4), 10);
    } else celdaVaciaCompras(row);
}

function editarCeldaCostoCompras(productoSeleccionado, newValue, row) {
    if (productoSeleccionado) {
        const costo = parseFloat(newValue);
        const cantidad = parseFloat(hotCompras.getDataAtCell(row, 3));
        const inventario = parseFloat(hotCompras.getDataAtCell(row, 2));
        const valorDescuento = parseFloat(hotCompras.getDataAtCell(row, 5));
        const porcentajeDescuento = valorDescuento ? valorDescuento * 100 / (costo * cantidad) : 0;
        const porcentajeiva = parseFloat(hotCompras.getDataAtCell(row, 7));
        const valorIva = porcentajeiva ? ((costo - valorDescuento) * cantidad) * (porcentajeiva / 100) : 0;
        const total = ((costo - valorDescuento) * cantidad) + valorIva;
        
        hotCompras.setDataAtCell(row, 2, inventario);//INVENTARIO
        hotCompras.setDataAtCell(row, 3, cantidad);//CANTIDAD
        hotCompras.setDataAtCell(row, 4, costo);//COSTO
        hotCompras.setDataAtCell(row, 5, valorDescuento);//DESCUENTO
        hotCompras.setDataAtCell(row, 6, porcentajeDescuento.toFixed(2));//DESCUENTO %
        hotCompras.setDataAtCell(row, 7, porcentajeiva);//IVA %
        hotCompras.setDataAtCell(row, 8, valorIva);//IVA VALOR
        hotCompras.setDataAtCell(row, 9, total);//TOTAL

        setTimeout(() => hotCompras.selectCell(row+1, 1), 10);
    } else celdaVaciaCompras(row);
}

function editarCeldaDescuentoCompras(productoSeleccionado, newValue, row) {
    if (productoSeleccionado) {
        const costo = parseFloat(hotCompras.getDataAtCell(row, 4));
        const cantidad = parseFloat(hotCompras.getDataAtCell(row, 3));
        const inventario = parseFloat(hotCompras.getDataAtCell(row, 2));
        const valorDescuento = parseFloat(newValue);
        const porcentajeDescuento = valorDescuento ? valorDescuento * 100 / (costo * cantidad) : 0;
        const porcentajeiva = parseFloat(hotCompras.getDataAtCell(row, 7));
        const valorIva = porcentajeiva ? ((costo - valorDescuento) * cantidad) * (porcentajeiva / 100) : 0;
        const total = ((costo - valorDescuento) * cantidad) + valorIva;
        
        hotCompras.setDataAtCell(row, 2, inventario);//INVENTARIO
        hotCompras.setDataAtCell(row, 3, cantidad);//CANTIDAD
        hotCompras.setDataAtCell(row, 4, costo);//COSTO
        hotCompras.setDataAtCell(row, 5, valorDescuento);//DESCUENTO
        hotCompras.setDataAtCell(row, 6, porcentajeDescuento.toFixed(2));//DESCUENTO %
        hotCompras.setDataAtCell(row, 7, porcentajeiva);//IVA %
        hotCompras.setDataAtCell(row, 8, valorIva);//IVA VALOR
        hotCompras.setDataAtCell(row, 9, total);//TOTAL

        setTimeout(() => hotCompras.selectCell(row+1, 1), 10);
    } else celdaVaciaCompras(row);
}

function editarCeldaDescuentoPorcentajeCompras(productoSeleccionado, newValue, row) {
    if (productoSeleccionado) {
        const costo = parseFloat(hotCompras.getDataAtCell(row, 4));
        const cantidad = parseFloat(hotCompras.getDataAtCell(row, 3));
        const inventario = parseFloat(hotCompras.getDataAtCell(row, 2));
        const porcentajeDescuento = parseFloat(hotCompras.getDataAtCell(row, 6));
        const valorDescuento = (porcentajeDescuento / 100) * costo;
        const porcentajeiva = parseFloat(hotCompras.getDataAtCell(row, 7));
        const valorIva = porcentajeiva ? ((costo - valorDescuento) * cantidad) * (porcentajeiva / 100) : 0;
        const total = ((costo - valorDescuento) * cantidad) + valorIva;
        
        hotCompras.setDataAtCell(row, 2, inventario);//INVENTARIO
        hotCompras.setDataAtCell(row, 3, cantidad);//CANTIDAD
        hotCompras.setDataAtCell(row, 4, costo);//COSTO
        hotCompras.setDataAtCell(row, 5, valorDescuento);//DESCUENTO
        hotCompras.setDataAtCell(row, 6, porcentajeDescuento);//DESCUENTO %
        hotCompras.setDataAtCell(row, 7, porcentajeiva);//IVA %
        hotCompras.setDataAtCell(row, 8, valorIva);//IVA VALOR
        hotCompras.setDataAtCell(row, 9, total);//TOTAL

        setTimeout(() => hotCompras.selectCell(row+1, 1), 10);
    } else celdaVaciaCompras(row);
}

function celdaVaciaCompras(row) {
    hotCompras.setDataAtCell(row, 2, 0);//INVENTARIO
    hotCompras.setDataAtCell(row, 3, 0);//CANTIDAD
    hotCompras.setDataAtCell(row, 4, 0);//COSTO
    hotCompras.setDataAtCell(row, 5, 0);//DESCUENTO
    hotCompras.setDataAtCell(row, 6, 0);//DESCUENTO %
    hotCompras.setDataAtCell(row, 7, 0);//IVA %
    hotCompras.setDataAtCell(row, 8, 0);//IVA VALOR
    hotCompras.setDataAtCell(row, 9, 0);//TOTAL
    setTimeout(() => hotCompras.selectCell(row, 1), 10);
}

function eliminarCompraRenderer(instance, td, row, col, prop, value, cellProperties) {

    const btnEliminar = document.createElement('span');
    
    btnEliminar.setAttribute('class', 'btn badge bg-gradient-danger drop-row-grid');
    btnEliminar.innerHTML = '<i class="fas fa-trash-alt"></i>';
    btnEliminar.addEventListener('click', function () {
        instance.alter('remove_row', row);
        const hotComprasCount = hotCompras.countRows();
        mostrarValoresCompras();
        setTimeout(function(){
            if (hotCompras.countRows() == 1) {
                hotCompras.render();
                hotCompras.selectCell(0, 1);
            }
        },10);
    });

    td.innerHTML = '';
    td.appendChild(btnEliminar);

    return td;
}

function obtenerComprasProductos(query, process) {
    if (productosData.length === 0 || !productosData.some(producto => producto.text === query)) {
        $.ajax({
            url: '/api/productos',
            headers: headers,
            data: { query: query, page: 1 },
            method: 'GET',
            success: function(response) {
                productosData = response.data;
                process(response.data.map(function(producto) {
                    return producto.text.trim();
                }));
            }
        });
    } else {
        const results = productosData.filter(function(producto) {
            return producto.text.includes(query);
        });
        process(results.map(function(producto) {
            return producto.text.trim();
        }));
    }
}

function comprasProductosRenderer(instance, td, row, col, prop, value, cellProperties) {
    const producto = productosData.find(producto => producto.text === value);
    
    // Limpia la celda
    Handsontable.dom.empty(td);

    if (producto) {
        const text = document.createTextNode(producto.text);
        td.appendChild(text);
    } else {
        td.innerHTML = value || ''; // Por si no hay valor, muestra vacío
    }

    return td;
}

function ocultarColumnas(indicesColumnas) {
    hotCompras.updateSettings({
        hiddenColumns: {
            columns: indicesColumnas, // Índices de las columnas que deseas ocultar
            indicators: true,
        },
    });
}

$('#id_proveedor_compra').on('select2:close', function(event) {
    var data = $(this).select2('data');
    if(data.length){
        $('#fecha_manual_compra').focus();
        $('#fecha_manual_compra').select();
    }
});

$("#fecha_manual_compra").on('keydown', function(event) {
    if(event.keyCode == 13){
        $('#documento_referencia_compra').focus();
        $('#documento_referencia_compra').select();
    }
});

function loadFormasPagoCompra() {
    var totalRows = compra_table_pagos.rows().data().length;
    if(compra_table_pagos.rows().data().length){
        compra_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            compra_table_pagos.row(0).remove().draw();
        }
    }
    compra_table_pagos.ajax.reload(function(res) {
        disabledFormasPagoCompras();
    });
}

function focusFormaPagoCompra(idFormaPago) {
    var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
    var totalPagos = totalFormasPagoCompras(idFormaPago);
    var totalFactura = total - totalPagos;

    $('#compra_forma_pago_'+idFormaPago).val(totalFactura < 0 ? 0 : totalFactura);
    $('#compra_forma_pago_'+idFormaPago).select();
}

function calcularCompraPagos(idFormaPago = null) {

    if (
        $('#compra_forma_pago_'+idFormaPago).val() == '' ||
        $('#compra_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#compra_forma_pago_'+idFormaPago).val(0);
    }

    $('#total_faltante_compra').removeClass("is-invalid");

    var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
    var totalPagos = totalFormasPagoCompras();
    var totalFaltante = total - totalPagos;

    if (idFormaPago && totalFaltante < 0) {
        var totalPagoSinActual = totalFormasPagoCompras(idFormaPago);
        $('#compra_forma_pago_'+idFormaPago).val(total - totalPagoSinActual);
        $('#compra_forma_pago_'+idFormaPago).select();
        return;
    }

    var totalPagado = totalFaltante < 0 ? total : totalPagos;
    var totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    document.getElementById('total_pagado_compra').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalPagado);
    document.getElementById('total_faltante_compra').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalFaltante);
}


function totalFormasPagoCompras(idFormaPago = null) {

    var totalPagos = 0;
    var dataPagoCompra = compra_table_pagos.rows().data();

    if(dataPagoCompra.length > 0) {
        for (let index = 0; index < dataPagoCompra.length; index++) {
            
            var ventaPago = parseFloat($('#compra_forma_pago_'+dataPagoCompra[index].id).val());

            if (idFormaPago && idFormaPago == dataPagoCompra[index].id) continue;
            totalPagos+= ventaPago;
        }
    }

    return totalPagos;
}

function iniciarCapturaCompra () {
    hotCompras.selectCell(0, 1);
    clearFormasPagoCompras();
    $('#card-compra').focus();
    document.getElementById("card-compra").scrollLeft = 0;
    hotCompras.updateSettings({
        readOnly: false,
    });
    hotCompras.render();
}

function calcularRetencionCompras (producto) {
    if (producto.familia.cuenta_compra_retencion && producto.familia.cuenta_compra_retencion.impuesto) {
        var impuestoPorcentaje = parseFloat(producto.familia.cuenta_compra_retencion.impuesto.porcentaje);
        var topeValor = parseFloat(producto.familia.cuenta_compra_retencion.impuesto.base);
        if (impuestoPorcentaje > porcentajeRetencionCompras) {
            porcentajeRetencionCompras = impuestoPorcentaje;
            topeRetencionCompras = topeValor;
        }
    }

    document.getElementById('compra_texto_retencion').innerHTML = 'RETENCIÓN '+ porcentajeRetencionCompras+'%'+'<br> BASE '+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(topeRetencionCompras);
    mostrarValoresCompras ();
}

function buscarFacturaCompra(event) {

    if (validarFacturaCompra) {
        validarFacturaCompra.abort();
    }

    var botonPrecionado = event.key.length == 1 ? event.key : '';
    var documento_referencia = $('#documento_referencia_compra').val()+''+botonPrecionado;

    if (event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
    if (!documento_referencia) return;
    
    $('#documento_referencia_compra_loading').show();
    
    $('#documento_referencia_compra').removeClass("is-invalid");
    $('#documento_referencia_compra').removeClass("is-valid");

    setTimeout(function(){
        validarFacturaCompra = $.ajax({
            url: base_url + 'existe-factura',
            method: 'GET',
            data: {
                id_comprobante: $("#id_comprobante_compra").val(),
                documento_referencia: documento_referencia
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarFacturaCompra = null;
            $('#documento_referencia_compra_loading').hide();
            if(res.data == 0){
                $('#documento_referencia_compra').removeClass("is-invalid");
                $('#documento_referencia_compra').addClass("is-valid");
            }else {
                $('#documento_referencia_compra').removeClass("is-valid");
                $('#documento_referencia_compra').addClass("is-invalid");
                $("#error_documento_referencia_compra").text('La factura No '+documento_referencia+' ya existe!');
            }
        }).fail((err) => {
            $('#documento_referencia_compra_loading').hide();
        });
    },100);
}

$(document).on('click', '#cancelarCapturaCompra', function () {
    cancelarCompra();
});

function cancelarCompra() {
    $("#id_bodega_compra").prop('disabled', false);

    $('#agregarCompra').hide();
    $("#iniciarCapturaCompra").show();
    $('#agregarCompraProducto').hide();
    $("#crearCapturaCompra").hide();
    $("#cancelarCapturaCompra").hide();
    $("#crearCapturaCompraDisabled").hide();
    $("#documento_referencia_compra").val(null);

    const hotComprasCount = hotCompras.countRows();
    hotCompras.alter('remove_row', 0, hotComprasCount);
    
    setTimeout(function(){
        mostrarValoresCompras();
        $comboProveedor.select2("open");
    },10);
}

$(document).on('click', '#iniciarCapturaCompra', function () {
    var form = document.querySelector('#compraFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        $("#error_documento_referencia_compra").text('El No. factura requerido');
        return;
    }

    $("#id_bodega_compra").prop('disabled', true);
    
    $("#iniciarCapturaCompra").hide();
    $("#agregarCompra").show();
    $("#cancelarCapturaCompra").show();
    $("#crearCapturaCompraDisabled").show();

    iniciarCapturaCompra();
});

$(document).on('click', '#agregarCompra', function () {
    iniciarCapturaCompra();
});

$(document).on('click', '#crearCapturaCompra', function () {
    validateSaveCompra();
});

function saveCompra() {
    
    ocultarBotonesCabezaCompra();

    $('#iniciarCapturaCompraLoading').show();

    let data = {
        pagos: getComprasPagos(),
        productos: getProductosCompra(),
        id_proveedor: $("#id_proveedor_compra").val(),
        id_bodega: $("#id_bodega_compra").val(),
        id_comprobante: $("#id_comprobante_compra").val(),
        fecha_manual: $("#fecha_manual_compra").val(),
        documento_referencia: $("#documento_referencia_compra").val(),
    }

    disabledFormasPagoCompras();

    $.ajax({
        url: base_url + 'compras',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoCompra = false;
        if(res.success){
            if(res.impresion) {
                window.open("/compras-print/"+res.impresion, "", "_blank");
            }

            $('#iniciarCapturaCompra').hide();
            $('#iniciarCapturaCompraLoading').hide();
            $('#documento_referencia_compra').val('');

            agregarToast('exito', 'Creación exitosa', 'Compra creada con exito!', true);
            disabledFormasPagoCompras();
            cancelarCompra();

        } else {
            $("#agregarCompra").show();
            $("#crearCapturaCompra").show();
            $("#iniciarCapturaCompra").hide();
            $("#cancelarCapturaCompra").show();
            $("#crearCapturaCompraDisabled").hide();
            $("#iniciarCapturaCompraLoading").hide();
            
            var mensaje = res.mensages;
            var errorsMsg = "";
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += "- "+errores[campo]+" <br>";
                }
            };
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    }).fail((err) => {
        guardandoCompra = false;
        $("#agregarCompra").show();
        $("#crearCapturaCompra").show();
        $("#iniciarCapturaCompra").hide();
        $("#cancelarCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
        $("#iniciarCapturaCompraLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function ocultarBotonesCabezaCompra () {
    $("#iniciarCapturaCompra").hide();
    $("#iniciarCapturaCompraLoading").hide();
    $("#agregarCompra").hide();
    $("#crearCapturaCompraDisabled").hide();
    $("#crearCapturaCompra").hide();
}

function getProductosCompra(){
    var data = [];

    const hotComprasCount = hotCompras.countRows();
    for (let index = 0; index < hotComprasCount; index++) {
        const id_producto = hotCompras.getDataAtCell(index, 10);
        if (id_producto) {
            const cantidad = hotCompras.getDataAtCell(index, 3);
            const costo = hotCompras.getDataAtCell(index, 4);
            data.push({
                id_producto: id_producto,
                cantidad: cantidad,
                costo: costo,
                subtotal: cantidad * costo,
                descuento_valor: hotCompras.getDataAtCell(index, 5),
                descuento_porcentaje: hotCompras.getDataAtCell(index, 6),
                iva_porcentaje: hotCompras.getDataAtCell(index, 7),
                iva_valor: hotCompras.getDataAtCell(index, 8),
                total: hotCompras.getDataAtCell(index, 9),
            });
        }
    }
    
    return data;
}

function mostrarValoresCompras () {
    var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

    if (descuento) $('#totales_descuento_compra').show();
    else $('#totales_descuento_compra').hide();

    if (retencion) $('#totales_retencion_compra').show();
    else $('#totales_retencion_compra').hide();

    if (total) disabledFormasPagoCompras(false);
    else disabledFormasPagoCompras();

    var countA = new CountUp('compra_total_iva', 0, iva, 2, 0.5);
        countA.start();

    var countB = new CountUp('compra_total_descuento', 0, descuento, 2, 0.5);
        countB.start();

    var countC = new CountUp('compra_total_retencion', 0, retencion, 2, 0.5);
        countC.start();

    var countD = new CountUp('compra_total_valor', 0, total, 2, 0.5);
        countD.start();

    var countE = new CountUp('compra_sub_total', 0, valorBruto, 2, 0.5);
        countE.start();

    var countF = new CountUp('total_faltante_compra', 0, total, 2, 0.5);
        countF.start();
}

function disabledFormasPagoCompras(estado = true) {
    var dataFormasPago = compra_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#compra_forma_pago_'+formaPago.id).prop('disabled', estado);
        }
    }
}

function totalValoresCompras() {
    var iva = retencion = descuento = total = valorBruto = 0;

    const columnCosto = hotCompras.getDataAtCol(4);
    columnCosto.forEach(function(value) {
        valorBruto += value || 0;
    });
    const columnDescuento = hotCompras.getDataAtCol(5);
    columnDescuento.forEach(function(value) {
        descuento += value || 0;
    });
    const columnIva = hotCompras.getDataAtCol(8);
    columnIva.forEach(function(value) {
        iva += value || 0;
    });
    const columnTotal = hotCompras.getDataAtCol(9);
    columnTotal.forEach(function(value) {
        total += value || 0;
    });

    if (total) {
        if (total >= topeRetencionCompras) {
            retencion = porcentajeRetencionCompras ? (valorBruto * porcentajeRetencionCompras) / 100 : 0;
            total = total - retencion;
        }
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }

    var totalPagos = totalFormasPagoCompras();

    if (total > 0 && totalPagos >= total) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }

    return [iva, retencion, descuento, total, valorBruto];
}

function changeFormaPagoCompra(idFormaPago, event) {
    if(event.keyCode == 13){

        calcularCompraPagos(idFormaPago);

        var totalPagos = totalFormasPagoCompras();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresCompras();

        if (!total) {
            return;
        }

        if (totalPagos >= total) {
            validateSaveCompra();
            return;
        }
        
        focusNextFormasPagoCompras(idFormaPago);
    }
}

function focusNextFormasPagoCompras(idFormaPago) {
    var dataCompraPagos = compra_table_pagos.rows().data();
    var idFormaPagoFocus = dataCompraPagos[0].id;
    var obtenerFormaPago = false;

    if(!dataCompraPagos.length > 0) return;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoCompra = dataCompraPagos[index];
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoCompra.id;
            obtenerFormaPago = false;
        } else if (dataPagoCompra.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoCompra(idFormaPagoFocus);
}

function clearFormasPagoCompras() {
    var dataCompraPagos = compra_table_pagos.rows().data();

    if(!dataCompraPagos.length > 0) return;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoCompra = dataCompraPagos[index];
        $('#compra_forma_pago_'+dataPagoCompra.id).val(0);
    }

    calcularCompraPagos();
}

function getComprasPagos() {
    var data = [];

    var dataCompraPagos = compra_table_pagos.rows().data();

    if(!dataCompraPagos.length > 0) return data;

    for (let index = 0; index < dataCompraPagos.length; index++) {
        const dataPagoCompra = dataCompraPagos[index];
        var pagoCompra = $('#compra_forma_pago_'+dataPagoCompra.id).val();
        if (pagoCompra > 0) {
            data.push({
                id: dataPagoCompra.id,
                valor: pagoCompra
            });
        }
    }

    return data;
}

function validateSaveCompra() {
    $('#total_faltante_compra_text').css("color","#484848");
    $('#total_faltante_compra').css("color","#484848");

    if (!guardandoCompra) {

        var [iva, retencion, descuento, total, subtotal] = totalValoresCompras();
        var totalPagos = totalFormasPagoCompras();

        if (totalPagos >= total) {
            guardandoCompra = true;
            saveCompra(); 
        } else {
            $('#total_faltante_compra_text').css("color","red");
            $('#total_faltante_compra').css("color","red");
            return;
        }
    }
}