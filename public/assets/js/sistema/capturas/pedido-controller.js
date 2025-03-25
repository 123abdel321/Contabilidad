let timerBusquedaPedidos;
let pagePedidos = 0;
let familiaFilterPD;
let searchFilterPD;
let lastPagePedidos = 1;
let pedidoEditando = null;
let porcentajeRetencionPedidos = 0
let topeRetencionPedidos = 0
let id_ubicacion_select = null;
let $comboBodegaPedidos;
let $comboClientePedidos;
let pedidoFinalizado = false;
let pedidos_table_pagos;
let bodegaEventoActivo = false;
var guardandoPedido = false;
let $comboResolucionPedidos;
let $comboVendedorPedidos;
let loadingPedidos = false;
let productosPedidos = [];
let consecutivoPedidos = 0;
let pedidoXHR = null;
var totalAnticiposDisponiblesPedidos = 0;
let contenedorPedidos = $("#contenedor-productos-pedidos");

function pedidoInit () {

    cargarTablasPedido();
    cargarCombosPedido();
    cargarUbicacionPedido();
    cargarProductosPedido();
    loadFormasPagoPedidos();
    cargarChangesFunction();

    if(primeraBodegaPedido && primeraBodegaPedido.length > 0){
        var dataBodega = {
            id: primeraBodegaPedido[0].id,
            text: primeraBodegaPedido[0].codigo + ' - ' + primeraBodegaPedido[0].nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodegaPedidos.append(newOption).trigger('change');
        $comboBodegaPedidos.val(dataBodega.id).trigger('change');
    }

    if (primerNitPedido) {
        
        var dataCliente = {
            id: primerNitPedido.id,
            text: primerNitPedido.numero_documento + ' - ' + primerNitPedido.nombre_completo
        };
        var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
        $comboCliente.append(newOption).trigger('change');
        $comboCliente.val(dataCliente.id).trigger('change');

        loadAnticiposClientePedido();

        // if (primerNitPedido.vendedor) {
        //     var dataVendedor = {
        //         id: primerNitPedido.vendedor.nit.id,
        //         text: primerNitPedido.vendedor.nit.numero_documento + ' - ' + primerNitPedido.vendedor.nit.nombre_completo
        //     };
        //     var newOption = new Option(dataVendedor.text, dataVendedor.id, false, false);
        //     $comboVendedor.append(newOption).trigger('change');
        //     $comboVendedor.val(dataVendedor.id).trigger('change');
        // }

    } else {
        setTimeout(function(){
            $comboClientePedidos.select2("open");
        },10);
    }

    $("#searchInputPedidos").on("input", function (e) {
        $('#nitTable').DataTable().search($("#searchInputPedidos").val()).draw();
    });
}

function cargarUbicacionPedido() {
    $.ajax({
        url: base_url + 'ubicaciones-combo-general',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        $("#div-item-ubicacion").empty();
        id_ubicacion_select = null;

        let dataUbicaciones = res.data;
        for (let index = 0; index < dataUbicaciones.length; index++) {
            const ubicaciones = dataUbicaciones[index];
            const cassUbicacion = ubicaciones.pedido ? 'with' : 'selected';
            
            $("#div-item-ubicacion").append(`
                <div class="item-ubicacion">
                    <div id="ubicacion-pedido_${ubicaciones.id}" class="ubicaciones-datos">
                        <div class="${cassUbicacion}"></div>
                        <div class="nombre">${ubicaciones.nombre}</div>
                        <div id="ubicacion-total_${ubicaciones.id}" class="total">${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format( ubicaciones.pedido ? ubicaciones.pedido.total_factura : 0 )}</div>
                    </div>
                </div>
            `);
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function filtrarProductosPedidos(id_familia) {
    clearTimeout(timerBusquedaPedidos);
    
    pagePedidos = 0;
    lastPagePedidos = 1;

    $(".familia-filter-pedidos").removeClass("bg-gradient-dark");
    $(".familia-filter-pedidos").removeClass("bg-gradient-light");
    $(".familia-filter-pedidos").addClass("bg-gradient-light");

    if (id_familia) {
        familiaFilterPD = id_familia;
        $("#filter-familias-pedido-"+id_familia).removeClass("bg-gradient-light");
        $("#filter-familias-pedido-"+id_familia).addClass("bg-gradient-dark");
    } else {
        familiaFilterPD = null;
        $("#filter-familias-pedido").removeClass("bg-gradient-light");
        $("#filter-familias-pedido").addClass("bg-gradient-dark");
    }

    timerBusquedaPedidos = setTimeout(() => {
        searchFilterPD = $("#searchInputPedidos").val().trim();
        cargarProductosPedido();
    }, 200);
}

function buscarProductosPedidos() {
    clearTimeout(timerBusquedaPedidos);

    pagePedidos = 0;
    lastPagePedidos = 1;

    timerBusquedaPedidos = setTimeout(() => {
        searchFilterPD = $("#searchInputPedidos").val().trim();
        cargarProductosPedido();
    }, 200);
}

function cargarChangesFunction() {
    $("#id_resolucion_pedido").on('change', function(event) {
        consecutivoSiguientePedido();
    });
    
    $("#id_bodega_pedido").on('change', function(event) {
        if (bodegaEventoActivo) return;
        consecutivoSiguienteBodegaPedido();
    });
}

function cargarTablasPedido() {
    pedidos_table_pagos = $('#pedidoFormaPago').DataTable({
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
                type: 'ventas'
            },
            url: base_url + 'forma-pago/combo-forma-pago',
        },
        columns: [
            {"data":'nombre'},
            {"data": function (row, type, set){
                var anticipos = false;
                var className = '';
                if (row.cuenta.tipos_cuenta.length > 0) {
                    var tiposCuentas = row.cuenta.tipos_cuenta;
                    for (let index = 0; index < tiposCuentas.length; index++) {
                        const tipoCuenta = tiposCuentas[index];
                        if (tipoCuenta.id_tipo_cuenta == 8) {
                            anticipos = true;
                            className = 'anticipos'
                        }
                    }
                }
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoPedido(${row.id}, ${anticipos})" onfocusout="calcularVentaPedido(${row.id}, ${anticipos})" onkeypress="changeFormaPagoPedidos(${row.id}, ${anticipos}, event)" id="pedido_forma_pago_${row.id}">`;
            }},
        ],
        initComplete: function () {
            $('#pedidoFormaPago').on('draw.dt', function() {
                $("input[data-type='currency']").on({
                    keyup: function(event) {
                        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
                            formatCurrency($(this));
                        }
                    },
                    blur: function() {
                        formatCurrency($(this), "blur");
                    }
                });
            });
        }
    });
}

function cargarCombosPedido() {
    $comboClientePedidos = $('#id_cliente_pedido').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownCssClass: 'custom-id_cliente_pedido',
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

    $comboResolucionPedidos = $('#id_resolucion_pedido').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#pedidosFormModal'),
        delay: 250,
        placeholder: "Seleccione una resolución",
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

    $comboBodegaPedidos = $('#id_bodega_pedido').select2({
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

    $comboVendedorPedidos = $('#id_vendedor_pedido').select2({
        theme: 'bootstrap-5',
        delay: 250,
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
            url: 'api/vendedores/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                var data_modified = $.map(data.data, function (obj) {
                    obj.text = obj.nit.nombre_completo;
                    return obj;
                });
                return {
                    results: data_modified
                };
            },
        }
    });
}

function cargarProductosPedido(clear = true) {
    if (loadingPedidos) return;
    loadingPedidos = true;

    if (pagePedidos >= lastPagePedidos) return;

    if (clear) {
        let contenedor = $("#contenedor-productos-pedidos");
        contenedor.html("");
        agregarProductosLoaging(7);
    }

    $.ajax({
        url: base_url + 'producto/combo-producto',
        method: 'GET',
        headers: headers,
        dataType: 'json',
        data: {
            query: searchFilterPD,
            id_familia: familiaFilterPD
        }
    }).done((res) => {
        if (res.data.length > 0) {
            console.log('res: ',res);
            pagePedidos++;
            lastPagePedidos = res.last_page;
            mostrarProductos(res.data, clear);
            $("#count-productos-pedidos").html('Productos: '+res.total);
        } else {
            pagePedidos = 0;
            lastPagePedidos = 1;
            let contenedor = $("#contenedor-productos-pedidos");
            contenedor.html(`
                <div style=" font-size: 20px; padding: 60px; text-align: -webkit-center;">
                    SIN PRODUCTOS ENCONTRADOS
                    <br/>
                    <button type="button" class="btn btn-sm btn-outline-info limpiar-filtros-pedidos">Limpiar filtros</button>
                </div>
            `);
        }
        loadingPedidos = false;

    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function agregarProductosLoaging(total) {
    for (let index = 0; index < total; index++) {
        $("#contenedor-productos-pedidos").append(`
            <div class="item-producto" onclick="seleccionarProducto(this)">
                <div class="producto-datos" style="">
                    <div class="imagen">
                        <div class="placeholder" style="width: 100%; height: 100px; border-radius: 10px;"></div>
                    </div>
                    <p class="card-text placeholder-glow" style="height: 75px; border-radius: 2px;">
                        <span class="placeholder" style="font-size: 10px; width: 90%; margin-top: -10px; margin-left: 5px; border-radius: 2px;"></span>
                        <span class="placeholder" style="font-size: 10px; width: 90%; margin-top: -25px; margin-left: 5px;border-radius: 2px;"></span>
                        <span class="placeholder" style="font-size: 10px; width: 60%; margin-top: -40px; margin-left: 19px; border-radius: 5px;"></span>
                        <span class="placeholder" style="font-size: 13px; width: 80%; margin-top: -50px; margin-left: 9px; border-radius: 5px;"></span>
                    </p>
                </div>
            </div>
        `);
    }
}

function mostrarProductos (listaProductos, clear) {

    if (clear) {
        let contenedor = $("#contenedor-productos-pedidos");
        contenedor.html("");
    }

    listaProductos.forEach(producto => {
        let cantidadInv = producto.inventarios.length ? parseInt(producto.inventarios[0].cantidad) : 0
        let imagenSrc = producto.imagen ? bucketUrl+producto.imagen : 'https://kzmlujhyhk8xlnk9h654.lite.vusercontent.net/placeholder.svg?height=80&width=80';
        let classInventario = cantidadInv > 0 ? 'bg-gradient-info' : 'bg-gradient-warning';
        let textoInventario = `INV: ${cantidadInv}`;

        let productoJson = encodeURIComponent(JSON.stringify(producto));
        
        $("#contenedor-productos-pedidos").append(`
            <div class="item-producto" onclick="seleccionarProducto(this)" data-producto="${productoJson}">
                <div class="producto-datos" style="">
                    <div class="imagen">
                        <img src="${imagenSrc}">
                    </div>
                    <div class="nombre">${producto.nombre}</div>
                    <div class="precio">${producto.precio}</div>
                    <div class="inventario">
                        <span class="badge ${classInventario}">${textoInventario}</span>
                    </div>
                </div>
            </div>
        `);
    });
}

function loadFormasPagoPedidos() {
    var totalRows = pedidos_table_pagos .rows().data().length;
    if(pedidos_table_pagos .rows().data().length){
        pedidos_table_pagos .clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            pedidos_table_pagos .row(0).remove().draw();
        }
    }
    pedidos_table_pagos .ajax.reload();
}

function seleccionarProducto(element) {

    if (pedidoFinalizado) return;

    let productoJson = element.getAttribute("data-producto"); // Obtener el JSON desde el atributo
    let producto = JSON.parse(decodeURIComponent(productoJson)); // Convertirlo en un objeto

    consecutivoPedidos++;

    let precioProducto = parseFloat(producto.precio);
    let totalProducto = 0;
    let descuentoValor = 0;
    let ivaPorcentaje = 0;
    let subtotal = precioProducto * 1;
    let ivaValor = 0;

    //OBTENER IVA DEL PRODUCTO
    if (producto.familia && producto.familia.cuenta_venta_iva && producto.familia.cuenta_venta_iva.impuesto) {
        ivaPorcentaje = parseFloat(producto.familia.cuenta_venta_iva.impuesto.porcentaje);
    }
    //OBTENER RETE-FUENTE DEL PRODUCTO
    if (producto.familia && producto.familia.cuenta_venta_retencion && producto.familia.cuenta_venta_retencion.impuesto) {
        var impuestoPorcentaje = parseFloat(producto.familia.cuenta_venta_retencion.impuesto.porcentaje);
        var topeValor = parseFloat(producto.familia.cuenta_venta_retencion.impuesto.base);
        if (impuestoPorcentaje > porcentajeRetencionPedidos) {
            porcentajeRetencionPedidos = impuestoPorcentaje;
            topeRetencionPedidos = topeValor;
        }
    }

    if (ivaPorcentaje > 0) {
        ivaValor = (precioProducto - descuentoValor) * (ivaPorcentaje / 100);
        if (ivaIncluidoPedido) {
            ivaValor = (precioProducto - descuentoValor) - ((precioProducto - descuentoValor) / (1 + (ivaPorcentaje / 100)));
        } else {
            ivaValor + ivaValor;
        }
    }

    totalProducto = precioProducto - descuentoValor;
    if (!ivaIncluidoPedido) {
        totalProducto+= ivaValor;
    } else {
        subtotal-= ivaValor;
    }

    productosPedidos.push({
        consecutivo: consecutivoPedidos,
        id_producto: producto.id,
        cantidad: 1,
        costo: precioProducto,
        subtotal: subtotal,
        descuento_porcentaje: 0,
        descuento_valor: 0,
        iva_porcentaje: ivaPorcentaje,
        iva_valor: ivaValor,
        total: totalProducto,
        concepto: '',
    })

    $("#lista_productos_seleccionados").append(`
        <div id="list_group_item_${consecutivoPedidos}" class="list-group-item">
            <div class="row" style="width: 100%; margin: 0px;">
                <div class="col-12 nombre">
                    ${producto.nombre}
                </div>
            </div>
            <div class="row" style="width: 100%; margin: 0px;">
                <div class="col-7 precio">
                    <div id="precio_producto_${consecutivoPedidos}" style="margin-bottom: 0px; font-weight: 600; font-size: 12px; color: #939393;">Precio: ${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(precioProducto)}</div>
                    <div id="total_producto_${consecutivoPedidos}" style="margin-bottom: 0px; font-weight: bold; font-size: 13px;">Total: ${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalProducto)}</div>
                </div>
                <div class="col-3 cantidad">
                    <div id="quitar_producto_${consecutivoPedidos}" class="quitar" onclick="restarCantidadPedido(${consecutivoPedidos})"><i class="fas fa-minus"></i></div>
                    <input id="cantidad_producto_${consecutivoPedidos}" class="button-cantidad-producto" type="text" value="1" onfocus="this.select();" onkeypress="changeCantidadPedido(${consecutivoPedidos}, event)">
                    <div id="agregar_producto_${consecutivoPedidos}" class="agregar" onclick="sumarCantidadPedido(${consecutivoPedidos})"><i class="fas fa-plus"></i></div>
                </div>
                <div id="eliminar_producto_${consecutivoPedidos}" class="col-2 eliminar" onclick="eliminarProductoPedido(${consecutivoPedidos})"><i class="fas fa-trash-alt"></i></div>
            </div>
        </div>`
    );

    mostrarValoresPedidos();
    guardarPedido();
}

function mostrarValoresPedidos() {
    var [iva, retencion, descuento, total, valorBruto] = totalValoresPedidos();

    if (descuento) $('#totales_descuento-pedidos').show();
    else $('#totales_descuento-pedidos').hide();

    if (retencion) $('#totales_retencion-pedidos').show();
    else $('#totales_retencion-pedidos').hide();

    $("#crearCapturaVentaPedidos").hide();
    $("#crearCapturaVentaPedidosDisabled").hide();

    $("#eliminarPedidosDisabled").hide();
    $("#imprimirPedidosDisabled").hide();
    $("#eliminarPedidos").hide();
    $("#imprimirPedidos").hide();

    if (total) {
        $("#crearCapturaVentaPedidos").show();
        $("#eliminarPedidos").show();
        $("#imprimirPedidos").show();
    } else {
        $("#eliminarPedidosDisabled").show();
        $("#imprimirPedidosDisabled").show();
        $("#crearCapturaVentaPedidosDisabled").show();
    }

    // const totalFactura = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(total);
    //     $("#ubicacion-total_"+id_ubicacion_select).html(totalFactura);
    if (id_ubicacion_select) {
        var count0 = new CountUp('ubicacion-total_'+id_ubicacion_select, 0, total, 2, 0.5);
        count0.start();
    }

    var countA = new CountUp('pedido_total_iva', 0, iva, 2, 0.5);
        countA.start();

    var countB = new CountUp('pedido_total_descuento', 0, descuento, 2, 0.5);
        countB.start();

    var countC = new CountUp('pedido_total_retencion', 0, retencion, 2, 0.5);
        countC.start();

    var countD = new CountUp('pedido_total_valor', 0, total, 2, 0.5);
        countD.start();

    var countD = new CountUp('total_faltante_pedidos', 0, total, 2, 0.5);
        countD.start();
        
    var countE = new CountUp('pedido_sub_total', 0, valorBruto, 2, 0.5);
        countE.start();
}

function totalValoresPedidos() {
    var iva = retencion = descuento = total = redondeo = valorBruto = 0;

    productosPedidos.forEach(productos => {
        iva+= productos.iva_valor;
        descuento+= productos.descuento_valor;
        valorBruto+= (productos.cantidad * productos.costo) - productos.descuento_valor;
    });

    if (ivaIncluidoPedido) valorBruto-= iva;

    total = ivaIncluidoPedido ? valorBruto : valorBruto + iva;

    if (total >= topeRetencionPedidos) {
        retencion = porcentajeRetencionPedidos ? (valorBruto * porcentajeRetencionPedidos) / 100 : 0;
        retencion = retencion;
    }

    if (ivaIncluidoPedido) total = total+= iva;

    return [iva, retencion, descuento, total, valorBruto, redondeo];
}

function sumarCantidadPedido(consecutivo) {
    let index = productosPedidos.findIndex(producto => producto.consecutivo === consecutivo);

    if (index === -1) {
        return;
    }
    
    productosPedidos[index].cantidad++;
    calcularCantidadPedido(consecutivo);
    mostrarValoresPedidos();
    guardarPedido();
}

function restarCantidadPedido(consecutivo) {
    let index = productosPedidos.findIndex(producto => producto.consecutivo === consecutivo);

    if (index === -1) {
        return;
    }

    if (productosPedidos[index].cantidad == 0) {
        return;
    }
    
    productosPedidos[index].cantidad--;
    calcularCantidadPedido(consecutivo);
    mostrarValoresPedidos();
    guardarPedido();
}

function eliminarProductoPedido(consecutivo) {
    let index = productosPedidos.findIndex(producto => producto.consecutivo === consecutivo);

    if (index === -1) {
        return;
    }

    document.getElementById("list_group_item_"+consecutivo).remove();
    productosPedidos.splice(index, 1);
    mostrarValoresPedidos();
    guardarPedido();
}

function calcularCantidadPedido(consecutivo) {
    let index = productosPedidos.findIndex(producto => producto.consecutivo === consecutivo);
    
    if (index === -1) {
        return;
    }

    var totalPorCantidad = productosPedidos[index].costo * productosPedidos[index].cantidad;
    var totalIva = 0;
    var totalDescuento = 0;
    var totalProducto = 0;

    if (productosPedidos[index].descuento_porcentaje) {
        totalDescuento = totalPorCantidad * (productosPedidos[index].descuento_porcentaje / 100);
    }

    totalProducto = totalPorCantidad - totalDescuento;

    if (productosPedidos[index].iva_porcentaje) {
        totalIva = (totalPorCantidad - totalDescuento) * (productosPedidos[index].iva_porcentaje / 100);
        if (ivaIncluidoPedido) {
            totalIva = (totalPorCantidad - totalDescuento) - ((totalPorCantidad - totalDescuento) / (1 + (productosPedidos[index].iva_porcentaje / 100)));
        }
    }

    if (!ivaIncluidoPedido) {
        totalProducto+= totalIva;
    }

    productosPedidos[index].subtotal = totalPorCantidad - totalDescuento;
    productosPedidos[index].descuento_valor = totalDescuento;
    productosPedidos[index].iva_valor = totalIva;
    productosPedidos[index].total = totalProducto;

    $("#cantidad_producto_"+consecutivo).val(productosPedidos[index].cantidad);
    $("#total_producto_"+consecutivo).text(`Total: ${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalProducto)}`);
}

function clearFormPedidosVenta() {
    $("#id_resolucion_pedido").val(null).change();
    $("#consecutivo_pedido").val('');
    $("#observacion_venta").val('');

    clearFormasPagoPedido();
    disabledFormasPagoPedido(false);
}

function clearFormasPagoPedido() {
    var dataFormasPago = pedidos_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#pedido_forma_pago_'+formaPago.id).val(0);
        }
    }

    calcularVentaPedido();
}

function focusFormaPagoPedido(idFormaPago, anticipo = false) {

    if (guardandoPedido) {
        return;
    }

    var [iva, retencion, descuento, total, subtotal] = totalValoresPedidos();

    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoPedidos(idFormaPago);
    
    var totalFactura = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    totalFactura = totalFactura < 0 ? 0 : totalFactura;

    if (anticipo) {
        if ((totalAnticiposDisponiblesPedidos - totalAnticipos) < totalFactura) {
            $('#pedido_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalAnticiposDisponiblesPedidos - totalAnticipos));
            $('#pedido_forma_pago_'+idFormaPago).select();
            return;
        }
    }

    $('#pedido_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalFactura));
    $('#pedido_forma_pago_'+idFormaPago).select();
}

function changeFormaPagoPedidos(idFormaPago, anticipo, event) {

    if (guardandoPedido) {
        return;
    }

    if(event.keyCode == 13){

        calcularVentaPedido(idFormaPago);

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoPedidos();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresPedidos();

        if (!total) {
            return;
        }

        if (anticipo) {
            if (totalAnticipos > totalAnticiposDisponiblesPedidos || totalAnticipos > total) {

                var [efectivo, pagos, totalOtrosAnticipos] = totalFormasPagoPedidos(idFormaPago);

                $('#pedido_forma_pago_'+idFormaPago).val(totalAnticiposDisponiblesPedidos - totalOtrosAnticipos);
                $('#pedido_forma_pago_'+idFormaPago).select();
                return;
            }
        }

        if (vendedoresPedido && !$("#id_vendedor_pedido").val()) {
            return;
        }

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
            validateSavePedido();
            return;
        }
        focusNextFormasPagoPedidos(idFormaPago);
    }
}

function calcularVentaPedido(idFormaPago) {

    if (guardandoPedido) {
        return;
    }

    if (
        $('#pedido_forma_pago_'+idFormaPago).val() == '' ||
        $('#pedido_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#pedido_forma_pago_'+idFormaPago).val(0);
    }

    $('#total_faltante_pedidos').removeClass("is-invalid");

    var [iva, retencion, descuento, total, subtotal] = totalValoresPedidos();
    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoPedidos();
    var totalFaltante = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    
    if ((totalOtrosPagos + totalEfectivo + totalAnticipos) >= total) {
        var totalCambio = (totalEfectivo + totalOtrosPagos + totalAnticipos) - total;
        if(parseInt(totalCambio) > 0)$('#cambio-totals-pedidos').show();
        document.getElementById('total_cambio_pedidos').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalCambio);
    } else {
        $('#cambio-totals-pedidos').hide();
        if (totalFaltante < 0) {
            $('#pedido_forma_pago_'+idFormaPago).val(totalFaltante * -1);
            $('#pedido_forma_pago_'+idFormaPago).select();
        }
    }
    var totalPagado = totalFaltante < 0 ? total : totalEfectivo + totalOtrosPagos + totalAnticipos;
    var totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    var countA = new CountUp('total_pagado_pedidos', 0, totalPagado, 2, 0.5);
        countA.start();

    var countB = new CountUp('total_faltante_pedidos', 0, totalFaltante, 2, 0.5);
        countB.start();
}

function totalFormasPagoPedidos(idFormaPago = null) {
    var totalEfectivo = 0;
    var totalAnticipos = 0;
    var totalOtrosPagos = 0;

    var dataPagoPedido = pedidos_table_pagos.rows().data();

    if(dataPagoPedido.length > 0) {
        for (let index = 0; index < dataPagoPedido.length; index++) {
            
            var ventaPago = stringToNumberFloat($('#pedido_forma_pago_'+dataPagoPedido[index].id).val());
            
            if (idFormaPago && idFormaPago == dataPagoPedido[index].id) continue;

            if (dataPagoPedido[index].id == 1) totalEfectivo+= ventaPago;
            else if ($('#pedido_forma_pago_'+dataPagoPedido[index].id).hasClass("anticipos")) totalAnticipos+= ventaPago;
            else totalOtrosPagos+= ventaPago;
        }
    }

    return [totalEfectivo, totalOtrosPagos, totalAnticipos];
}

function focusNextFormasPagoPedidos(idFormaPago) {
    var dataPedidosPagos = pedidos_table_pagos.rows().data();
    var idFormaPagoFocus = dataPedidosPagos[0].id;
    var obtenerFormaPago = false;

    if(!dataPedidosPagos.length > 0) return;

    for (let index = 0; index < dataPedidosPagos.length; index++) {
        const dataPagoCompra = dataPedidosPagos[index];
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoCompra.id;
            obtenerFormaPago = false;
        } else if (dataPagoCompra.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }
    focusFormaPagoPedido(idFormaPagoFocus);
}

function consecutivoSiguientePedido() {
    var id_resolucion = $('#id_resolucion_pedido').val();
    var fecha_manual = $('#fecha_manual_pedido').val();

    if(id_resolucion && fecha_manual) {

        let data = {
            id_resolucion: id_resolucion,
            fecha_manual: fecha_manual
        }

        $.ajax({
            url: base_url + 'consecutivo',
            method: 'GET',
            data: data,
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                $("#consecutivo_pedido").val(res.data);
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function consecutivoSiguienteBodegaPedido() {
    var id_bodega = $('#id_bodega_pedido').val();

    if(id_bodega) {

        $("#consecutivo_bodegas_pedidos").prop('disabled', true);

        let data = {
            id_bodega: id_bodega,
        }

        $.ajax({
            url: base_url + 'bodega-consecutivo',
            method: 'GET',
            data: data,
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            $("#consecutivo_bodegas_pedidos").prop('disabled', false);
            if(res.success){
                $("#consecutivo_bodegas_pedidos").val(res.data);
            }
        }).fail((err) => {
            $("#consecutivo_bodegas_pedidos").prop('disabled', false);
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function savePedidoVenta() {

    var form = document.querySelector('#pedidosVentasForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#savePedidos").hide();
    $("#savePedidosLoading").show();

    let data = {
        pagos: getPedidosPagos(),
        productos: productosPedidos,
        id_ubicacion: id_ubicacion_select,
        id_bodega: $("#id_bodega_pedido").val(),
        consecutivo_bodegas: $("#consecutivo_bodegas_pedidos").val(),
        id_cliente: $("#id_cliente_pedido").val(),
        fecha_manual: $("#fecha_manual_pedido").val(),
        id_resolucion: $("#id_resolucion_pedido").val(),
        id_vendedor: null,
        id_pedido: pedidoEditando,
        consecutivo: $("#consecutivo_pedido").val(),
        observacion: $("#observacion_pedido").val(),
    };

    disabledFormasPagoPedido();

    $.ajax({
        url: base_url + 'pedido-ventas',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoPedido = false;
        if (res.success) {

            agregarToast('exito', 'Creación exitosa', 'Venta creada con exito!', true);

            $("#savePedidos").show();
            $("#savePedidosLoading").hide();
            $("#pedidosFormModal").modal('hide');

            if(res.impresion) {
                window.open("/ventas-print/"+res.impresion, '_blank');
            }

            pedidoEditando = null;
            consecutivoPedidos = 0;

            setTimeout(function(){
                $('#id_cliente_pedido').focus();
                $comboClientePedidos.select2("open");
            },10);
            
            $("#lista_productos_seleccionados").empty();
            consecutivoSiguienteBodegaPedido();
            loadAnticiposClientePedido();
            disabledFormasPagoPedido();
            cargarUbicacionPedido();
            productosPedidos = [];

        } else {
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
        guardandoPedido = false;
        $("#savePedidos").show();
        $("#savePedidosLoading").hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function loadAnticiposClientePedido() {

    totalAnticiposDisponiblesPedidos = 0;
    $('#input-anticipos-pedido').hide();
    $('#id_saldo_anticipo_pedido').val(0);

    let data = {
        id_nit: $('#id_cliente_pedido').val(),
        id_tipo_cuenta: 8
    }

    $.ajax({
        url: base_url + 'extracto-anticipos',
        method: 'GET',
        data: data,
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            var disabled = true;
            if (res.data) {
                var saldo = parseFloat(res.data.saldo);
                if (saldo > 0) {
                    disabled = false;
                    $('#input-anticipos-pedido').show();
                    totalAnticiposDisponiblesPedidos = saldo;
                    $('#id_saldo_anticipo_pedido').val(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(saldo));
                }
            }
            var pagosAnticipos = document.getElementsByClassName('anticipos');
            if (pagosAnticipos) { //HIDE ELEMENTS
                for (let index = 0; index < pagosAnticipos.length; index++) {
                    const element = pagosAnticipos[index];
                    element.disabled = disabled;
                }
            }
        }
    }).fail((err) => {
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function disabledFormasPagoPedido(estado = true) {
    var dataFormasPago = pedidos_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#pedido_forma_pago_'+formaPago.id).prop('disabled', estado);
        }
    }

    if (totalAnticiposDisponiblesPedidos <= 0) {
        var pagosAnticipos = document.getElementsByClassName('anticipos');
        if (pagosAnticipos) { //HIDE ELEMENTS
            for (let index = 0; index < pagosAnticipos.length; index++) {
                const element = pagosAnticipos[index];
                element.disabled = true;
            }
        }
    }
}

function getPedidosPagos() {
    var data = [];

    var dataPedidoPagos = pedidos_table_pagos.rows().data();

    if(!dataPedidoPagos.length > 0) return data;

    for (let index = 0; index < dataPedidoPagos.length; index++) {
        const dataPagoPedido = dataPedidoPagos[index];
        var pagoVentaPedido = stringToNumberFloat($('#pedido_forma_pago_'+dataPagoPedido.id).val());
        if (pagoVentaPedido > 0) {
            data.push({
                id: dataPagoPedido.id,
                valor: pagoVentaPedido
            });
        }
    }

    return data;
}

function validateSavePedido() {
    $('#total_faltante_pedidos_text').css("color","#484848");
    $('#total_faltante_pedidos').css("color","#484848");

    if (!guardandoPedido) {

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoPedidos();
        var [iva, retencion, descuento, total, valorBruto] = totalValoresPedidos();

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
            
            guardandoPedido = true;
            savePedidoVenta();
        } else {
            $('#total_faltante_pedidos_text').css("color","red");
            $('#total_faltante_pedidos').css("color","red");
        }
    }
}

function pressConcecutivoPedidos(event) {
    if(event.keyCode != 13) return;
    buscarPedidos();
}

function buscarPedidos() {
    $("#consecutivo_bodegas_pedidos").prop('disabled', true);

    let data = {
        id_bodega: $("#id_bodega_pedido").val(),
        consecutivo: $("#consecutivo_bodegas_pedidos").val(),
        id_ubicacion: id_ubicacion_select
    }

    $.ajax({
        url: base_url + 'pedido',
        method: 'GET',
        data: data,
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        $("#consecutivo_bodegas_pedidos").prop('disabled', false);

        productosPedidos = [];
        pedidoFinalizado = false;
        $("#lista_productos_seleccionados").empty();

        if (!res.data) {
            pedidoEditando = null;
            mostrarValoresPedidos();
            return;
        }

        const pedido = res.data
        const detalles = pedido.detalles;

        if(pedido.bodega) {
            bodegaEventoActivo = true;
            var dataBodega = {
                id: pedido.bodega.id,
                text: pedido.bodega.codigo+' - '+pedido.bodega.nombre
            };
            var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
            $comboBodegaPedidos.append(newOption).trigger('change');
            $comboBodegaPedidos.val(dataBodega.id).trigger('change');
        }

        if(pedido.cliente) {
            var dataCliente = {
                id: pedido.cliente.id,
                text: pedido.cliente.numero_documento+' - '+pedido.cliente.nombre_completo
            };
            var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
            $comboClientePedidos.append(newOption).trigger('change');
            $comboClientePedidos.val(dataCliente.id).trigger('change');
        }

        pedidoEditando = pedido.id;

        for (let index = 0; index < detalles.length; index++) {
            const producto = detalles[index];
            
            consecutivoPedidos++;

            var impuestoPorcentaje = 0;
            var topeValor = 0;

            if (producto.cuenta_retencion && producto.cuenta_retencion.impuesto) {
                var impuestoPorcentaje = parseFloat(producto.cuenta_retencion.impuesto.porcentaje);
                var topeValor = parseFloat(producto.cuenta_retencion.impuesto.base);
            }

            if (impuestoPorcentaje > porcentajeRetencionPedidos) {
                porcentajeRetencionPedidos = impuestoPorcentaje;
                topeRetencionPedidos = topeValor;
            }
            
            productosPedidos.push({
                consecutivo: consecutivoPedidos,
                id_producto: producto.id_producto,
                cantidad: parseFloat(producto.cantidad),
                costo: parseFloat(producto.costo),
                subtotal: parseFloat(producto.subtotal),
                descuento_porcentaje: parseFloat(producto.descuento_porcentaje),
                descuento_valor: parseFloat(producto.descuento_valor),
                iva_porcentaje: parseFloat(producto.iva_porcentaje),
                iva_valor: parseFloat(producto.iva_valor),
                total: parseFloat(producto.total),
                concepto: producto.observacion,
            });

            letProductoCantidad = `
                <div class="col-3 cantidad">
                    <div id="quitar_producto_${consecutivoPedidos}" class="quitar" onclick="restarCantidadPedido(${consecutivoPedidos})"><i class="fas fa-minus"></i></div>
                        <input id="cantidad_producto_${consecutivoPedidos}" class="button-cantidad-producto" type="text" value="${parseInt(producto.cantidad)}" onfocus="this.select();" onkeypress="changeCantidadPedido(${consecutivoPedidos}, event)">
                    <div id="agregar_producto_${consecutivoPedidos}" class="agregar" onclick="sumarCantidadPedido(${consecutivoPedidos})"><i class="fas fa-plus"></i></div>
                </div>
                <div id="eliminar_producto_${consecutivoPedidos}" class="col-2 eliminar" onclick="eliminarProductoPedido(${consecutivoPedidos})"><i class="fas fa-trash-alt"></i></div>
                `;

            if (pedido.id_venta) {
                letProductoCantidad = `<div class="col-5" style="place-content: center;">
                    <div style="text-align: center; font-weight: bold; font-size: 20px; color: lightseagreen;">
                        Cant: ${parseInt(producto.cantidad)}
                    </div>
                </div>`
            }
            // pedido.venta

            $("#lista_productos_seleccionados").append(`
                <div id="list_group_item_${consecutivoPedidos}" class="list-group-item">
                    <div class="row" style="width: 100%; margin: 0px;">
                        <div class="col-12 nombre">
                            ${producto.producto.nombre}
                        </div>
                    </div>
                    <div class="row" style="width: 100%; margin: 0px;">
                        <div class="col-7 precio">
                            <div id="precio_producto_${consecutivoPedidos}" style="margin-bottom: 0px; font-weight: 600; font-size: 12px; color: #939393;">Precio: ${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(producto.costo)}</div>
                            <div id="total_producto_${consecutivoPedidos}" style="margin-bottom: 0px; font-weight: bold; font-size: 13px;">Total: ${new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(producto.total)}</div>
                        </div>
                        ${letProductoCantidad}
                    </div>
                </div>`
            );
        }

        bodegaEventoActivo = false;
        mostrarValoresPedidos();
        
        if (pedido.id_venta) {
            pedidoFinalizado = true;
            $("#crearCapturaVentaPedidosDisabled").show();
            $("#crearCapturaPedidosLoading").hide();
            $("#crearCapturaVentaPedidos").hide();

            $("#eliminarPedidosDisabled").show();
            $("#imprimirPedidosDisabled").show();
            $("#eliminarPedidos").hide();
            $("#imprimirPedidos").hide();
        }

    }).fail((res) => {
        $("#consecutivo_bodegas_pedidos").prop('disabled', false);
    });
}

$(document).on('click', '#crearCapturaVentaPedidos', function () {

    $('#id_cliente_pedido').removeClass("is-invalid");
    $('#id_bodega_pedido').removeClass("is-invalid");
    $('#consecutivo_bodegas_pedidos').removeClass("is-invalid");

    let isValit = true;
    const id_bodega = $("#id_bodega_pedido").val();
    const consecutivo = $("#consecutivo_bodegas_pedidos").val();
    const id_cliente = $("#id_cliente_pedido").val();

    if (!id_cliente) {
        isValit = false;
        $('#id_cliente_pedido').addClass("is-invalid");
    }

    if (!id_bodega) {
        isValit = false;
        $('#id_bodega_pedido').addClass("is-invalid");
    }

    if (!consecutivo) {
        isValit = false;
        $('#consecutivo_bodegas_pedidos').addClass("is-invalid");
    }

    if (!isValit) {
        return;
    }

    clearFormPedidosVenta();
    loadAnticiposClientePedido();

    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_pedido').val(fecha);

    $("#pedidosFormModal").modal('show');
});

function guardarPedido() {
    let = isValit = true;

    $('#id_bodega_pedido').removeClass("is-invalid");
    $('#id_cliente_pedido').removeClass("is-invalid");
    $('#consecutivo_bodegas_pedidos').removeClass("is-invalid");

    let data = {
        productos: productosPedidos,
        id_ubicacion: id_ubicacion_select,
        id_bodega: $("#id_bodega_pedido").val(),
        consecutivo: $("#consecutivo_bodegas_pedidos").val(),
        id_cliente: $("#id_cliente_pedido").val(),
        fecha_manual: $("#fecha_manual_pedido").val(),
        id_resolucion: $("#id_resolucion_pedido").val(),
        id_vendedor: null,
        id_pedido: pedidoEditando,
        observacion: $("#observacion_pedido").val(),
    };

    if (!data.id_cliente) {
        isValit = false;
        $('#id_cliente_pedido').addClass("is-invalid");
    }

    if (!data.id_bodega) {
        isValit = false;
        $('#id_bodega_pedido').addClass("is-invalid");
    }

    if (!data.consecutivo) {
        isValit = false;
        $('#consecutivo_bodegas_pedidos').addClass("is-invalid");
    }

    if (!isValit) {
        return;
    }

    if (pedidoXHR !== null) {
        pedidoXHR.abort();
    }

    $("#crearCapturaVentaPedidosDisabled").hide();
    $("#crearCapturaPedidosLoading").show();
    $("#crearCapturaVentaPedidos").hide();

    $("#eliminarPedidosDisabled").hide();
    $("#imprimirPedidosDisabled").hide();
    $("#eliminarPedidos").hide();
    $("#imprimirPedidos").hide();

    pedidoXHR = $.ajax({
        url: base_url + 'pedido',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json'
    }).done((res) => {
        guardandoPedido = false;
        if (res.success) {
            $("#crearCapturaVentaPedidosDisabled").hide();
            $("#crearCapturaPedidosLoading").hide();
            $("#crearCapturaVentaPedidos").show();

            $("#eliminarPedidosDisabled").hide();
            $("#imprimirPedidosDisabled").hide();
            $("#eliminarPedidos").show();
            $("#imprimirPedidos").show();

            pedidoEditando = res.data.id;

        } else {
            $("#crearCapturaVentaPedidosDisabled").hide();
            $("#crearCapturaPedidosLoading").hide();
            $("#crearCapturaVentaPedidos").show();

            $("#eliminarPedidosDisabled").hide();
            $("#imprimirPedidosDisabled").hide();
            $("#eliminarPedidos").show();
            $("#imprimirPedidos").show();

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
        console.log('err: ',err);

        if (err.statusText == "abort") return;

        guardandoPedido = false;
        $("#savePedidos").show();
        $("#savePedidosLoading").hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

$(document).on('click', '#savePedidos', function () {
    validateSavePedido();
});

$(document).on('click', '.limpiar-filtros-pedidos', function () {
    $("#searchInputPedidos").val('');
    filtrarProductosPedidos();
});

$(document).on('click', '#eliminarPedidos', function () {

    
    Swal.fire({
        title: '¿Borrar pedido?',
        text: "No se podrá revertir!",
        type: 'warning',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Borrar!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){

            $("#crearCapturaVentaPedidos").hide();
            $("#crearCapturaVentaPedidosDisabled").hide();
            $("#eliminarPedidosDisabled").hide();
            $("#imprimirPedidosDisabled").hide();
            $("#eliminarPedidos").hide();
            $("#imprimirPedidos").hide();

            $("#crearCapturaPedidosLoading").show();

            $.ajax({
                url: base_url + 'pedido',
                method: 'DELETE',
                data: JSON.stringify({id: pedidoEditando}),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                agregarToast('exito', 'Eliminación exitosa', 'Pedido eliminado con exito!', true );

                $("#crearCapturaVentaPedidosDisabled").show();
                $("#eliminarPedidosDisabled").show();
                $("#imprimirPedidosDisabled").show();

                $("#crearCapturaPedidosLoading").hide();
                $("#crearCapturaVentaPedidos").hide();
                $("#eliminarPedidos").hide();
                $("#imprimirPedidos").hide();

                pedidoEditando = null;
                productosPedidos = [];
                consecutivoPedidos = 0;

                $('#id_cliente_pedido').val(null).change();
                setTimeout(function(){
                    $('#id_cliente_pedido').focus();
                    $comboClientePedidos.select2("open");
                },10);
                
                $("#lista_productos_seleccionados").empty();
                consecutivoSiguienteBodegaPedido();
                loadAnticiposClientePedido();
                disabledFormasPagoPedido();
                cargarUbicacionPedido();
                mostrarValoresPedidos();
                
            }).fail((res) => {
                agregarToast('error', 'Eliminación errada', res.message);

                $("#crearCapturaVentaPedidosDisabled").hide();
                $("#eliminarPedidosDisabled").hide();
                $("#imprimirPedidosDisabled").hide();

                $("#crearCapturaPedidosLoading").hide();
                $("#crearCapturaVentaPedidos").show();
                $("#eliminarPedidos").show();
                $("#imprimirPedidos").show();
            });
        }
    })
});

$(document).on('click', '.ubicaciones-datos', function () {
    var id = this.id.split('_')[1];

    if (id_ubicacion_select == id) id_ubicacion_select = null;//LIMPIAR SI DA CLICK AL MISMO ITEM
    else id_ubicacion_select = id; //SELECCIONAR ITEM

    $(".ubicaciones-datos").removeClass('active');
    if (id_ubicacion_select) $("#ubicacion-pedido_"+id_ubicacion_select).addClass('active');

    buscarPedidos();
});

function changeCantidadPedido(consecutivo, event) {
    if(event.keyCode != 13) return;

    let index = productosPedidos.findIndex(producto => producto.consecutivo === consecutivo);

    if (index === -1) {
        return;
    }

    const cantidadProducto = parseInt($("#cantidad_producto_"+consecutivo).val());
    productosPedidos[index].cantidad = cantidadProducto;
    calcularCantidadPedido(consecutivo);
    mostrarValoresPedidos();
    guardarPedido();
} 

contenedorPedidos.on("scroll", function () {

    if (contenedorPedidos.scrollTop() + contenedorPedidos.innerHeight() >= contenedorPedidos[0].scrollHeight - 50) {
        cargarProductosPedido(false);
    }
});