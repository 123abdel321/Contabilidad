var fecha = null;
var venta_table = null;
var validarFacturaVenta = null;
var idVentaProducto = 0;
var $comboResolucion = null;
var $comboCliente = null;
var porcentajeRetencionVenta = 0;
var topeRetencionVenta = 0;

function ventaInit () {

    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_venta').val(fecha);

    venta_table = $('#ventaTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
        deferLoading: 0,
        initialLoad: false,
        autoWidth: true,
        language: lenguajeDatatable,
        ordering: false,
        columns: [
            {//BORRAR
                "data": function (row, type, set, col){
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteProductoVenta(${idVentaProducto})" id="delete-producto-venta_${idVentaProducto}"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            {//PRODUCTO
                "data": function (row, type, set, col){
                    return `<select class="form-control form-control-sm venta_producto combo-grid" id="venta_producto_${idVentaProducto}" onchange="changeProductoVenta(${idVentaProducto})" onfocusout="calcularProductoVenta(${idVentaProducto})"></select>`;
                },
            },
            {//EXISTENCIAS
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_existencia_${idVentaProducto}" disabled>`;
                }
            },
            {//CANTIDAD
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_cantidad_${idVentaProducto}" min="1" value="1" onkeydown="CantidadkeyDown(${idVentaProducto}, event)" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//COSTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_costo_${idVentaProducto}" value="0" onkeydown="CostokeyDown(${idVentaProducto}, event)" style="min-width: 100px;" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//% DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_descuento_porcentaje_${idVentaProducto}" value="0"  onkeydown="DescuentokeyDown(${idVentaProducto}, event)" maxlength="2" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_descuento_valor_${idVentaProducto}" value="0" onkeydown="DescuentoTotalkeyDown(${idVentaProducto}, event)" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//% IVA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_iva_porcentaje_${idVentaProducto}" value="0" onkeydown="IvakeyDown(${idVentaProducto}, event)" maxlength="2" onfocusout="calcularProductoVenta(${idVentaProducto})" disabled>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_iva_valor_${idVentaProducto}" value="0" onkeydown="IvaTotalkeyDown(${idVentaProducto}, event)" disabled>`;
                }
            },
            {//TOTAL
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="venta_total_${idVentaProducto}" value="0" disabled>`;
                }
            },
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#ventaTable').on('draw.dt', function() {
                $('.venta_producto').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    minimumInputLength: 1,
                    ajax: {
                        url: 'api/producto/combo-producto',
                        headers: headers,
                        data: function (params) {
                            var query = {
                                q: params.term,
                                id_bodega: $("#id_bodega_venta").val(),
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
                    },
                    templateResult: formatProducto,
                    templateSelection: formatRepoSelection
                });
            });

            function formatProducto (repo) {

                if (repo.loading) return repo.text;

                var urlImagen = repo.imagen ?
                    bucketUrl+repo.imagen :
                    '/img/sin_imagen.png';

                var inventario = repo.inventarios.length > 0 ? 
                    repo.inventarios[0].cantidad+' Existencias' :
                    'Sin inventario';

                var color = repo.inventarios.length > 0 ?
                    repo.inventarios[0].cantidad <= 0 ? 
                    '#a30000' : '#1c4587' :
                    '#838383';

                var $container = $(`
                <div class="row">
                    <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                        <img style="width: 50px;" src="${urlImagen}" />
                    </div>
                    <div class="col-9">
                        <div class="row">
                            <div class="col-12">
                                <h6 style="font-size: 14px; margin-bottom: 0px; color: black;">${repo.text}</h6>
                            </div>
                            <div class="col-12">
                                <i class="fas fa-box-open" style="font-size: 11px; color: ${color};"></i>
                                ${inventario}
                            </div>
                        </div>
                    </div>
                </div>
                `);

                return $container;
            }

            function formatRepoSelection (repo) {
                return repo.full_name || repo.text;
            }
        }
    });

    $comboCliente = $('#id_cliente_venta').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    $comboResolucion = $('#id_resolucion_venta').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/resoluciones/combo-resoluciones',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboBodega = $('#id_bodega_venta').select2({
        theme: 'bootstrap-5',
        delay: 250,
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

    if(primeraResolucionVenta){
        var dataResolucion = {
            id: primeraResolucionVenta.id,
            text: primeraResolucionVenta.prefijo + ' - ' + primeraResolucionVenta.nombre
        };
        var newOption = new Option(dataResolucion.text, dataResolucion.id, false, false);
        $comboResolucion.append(newOption).trigger('change');
        $comboResolucion.val(dataResolucion.id).trigger('change');
    }

    if(primeraBodegaVenta){
        var dataBodega = {
            id: primeraBodegaVenta.id,
            text: primeraBodegaVenta.codigo + ' - ' + primeraBodegaVenta.nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodega.append(newOption).trigger('change');
        $comboBodega.val(dataBodega.id).trigger('change');
    }

    setTimeout(function(){
        $comboCliente.select2("open");
    },10);
}

$('#id_cliente_venta').on('select2:close', function(event) {
    var data = $(this).select2('data');
    if(data.length){
        setTimeout(function(){
            $('#id_resolucion_venta').focus();
            $comboResolucion.select2("open");
        },10);
    }
});

$('#id_resolucion_venta').on('select2:close', function(event) {
    var data = $(this).select2('data');
    if(data.length){
        setTimeout(function(){
            $('#fecha_manual_venta').focus();
            $('#fecha_manual_venta').select();
        },10);
    }
});

$("#fecha_manual_venta").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        setTimeout(function(){
            $('#documento_referencia_venta').focus();
            $('#documento_referencia_venta').select();
        },10);
    }
});

$("#documento_referencia_venta").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        document.getElementById('iniciarCapturaVenta').click();
    }
});

function buscarFacturaVenta(event) {

    if (validarFacturaVenta) {
        validarFacturaVenta.abort();
    }
    
    $('#documento_referencia_venta_loading').show();

    var botonPrecionado = event.key.length == 1 ? event.key : '';
    var documento_referencia = $('#documento_referencia_venta').val()+''+botonPrecionado;
    
    $('#documento_referencia_venta').removeClass("is-invalid");
    $('#documento_referencia_venta').removeClass("is-valid");

    if(event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
    setTimeout(function(){
        validarFacturaVenta = $.ajax({
            url: base_url + 'existe-factura',
            method: 'GET',
            data: {documento_referencia: documento_referencia},
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarFacturaVenta = null;
            $('#documento_referencia_venta_loading').hide();
            if(res.data == 0){
                $('#documento_referencia_venta').removeClass("is-invalid");
                $('#documento_referencia_venta').addClass("is-valid");
            }else {
                $('#documento_referencia_venta').removeClass("is-valid");
                $('#documento_referencia_venta').addClass("is-invalid");
                $("#error_documento_referencia_venta").text('La factura ');
            }
        }).fail((err) => {
            $('#documento_referencia_venta_loading').hide();
        });
    },100);
}

$(document).on('click', '#iniciarCapturaVenta', function () {
    var form = document.querySelector('#ventaFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        $("#error_documento_referencia_venta").text('El No. factura requerido');
        return;
    }
    
    $("#iniciarCapturaVenta").hide();
    $("#agregarVenta").show();
    $("#cancelarCapturaVenta").show();
    $("#crearCapturaVentaDisabled").show();

    addRowProductoVenta();
});

function addRowProductoVenta () {
    
    venta_table.row.add({
        "id": idVentaProducto,
        "cantidad": 1,
        "costo": 0,
        "porcentaje_descuento": 0,
        "valor_descuento": 0,
        "porcentaje_iva": 0,
        "valor_iva": 0,
        "valor_total": 0,
    }).draw(false)

    $('#card-venta').focus();
    document.getElementById("card-venta").scrollLeft = 0;

    $('#venta_producto_'+idVentaProducto).focus();
    $('#venta_producto_'+idVentaProducto).select2('open');
    idVentaProducto++;
}

function calcularProductoVenta (idRow) {
    var costoProducto = $('#venta_costo_'+idRow).val();
    var cantidadProducto = $('#venta_cantidad_'+idRow).val();
    var ivaProducto = $('#venta_iva_porcentaje_'+idRow).val();
    var descuentoProducto = $('#venta_descuento_porcentaje_'+idRow).val();
    var totalPorCantidad = 0;
    var totalIva = 0;
    var totalDescuento = 0;
    var totalProducto = 0;
    
    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    if (descuentoProducto > 0) {
        totalDescuento = totalPorCantidad * descuentoProducto / 100;
        $('#venta_descuento_valor_'+idRow).val(totalDescuento);
    }

    if (ivaProducto > 0) {
        totalIva = (totalPorCantidad - totalDescuento) * ivaProducto / 100;
        $('#venta_iva_valor_'+idRow).val(totalIva);
    }

    totalProducto = totalPorCantidad - totalDescuento + totalIva;
    $('#venta_total_'+idRow).val(totalProducto);

    mostrarValoresVentas ();
}

function mostrarValoresVentas () {
    var [iva, retencion, descuento, total] = totalValoresVentas();

    $("#venta_total_iva").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(iva));
    $("#venta_total_descuento").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(descuento));
    $("#venta_total_retencion").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(retencion));
    $("#venta_total_valor").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total));
}

function totalValoresVentas() {
    var iva = retencion = descuento = total = 0;
    var valorBruto = 0;
    var dataVenta = venta_table.rows().data();

    if(dataVenta.length > 0) {
        $("#crearCapturaVenta").show();
        $("#crearCapturaVentaDisabled").hide();
        
        for (let index = 0; index < dataVenta.length; index++) {
            var producto = $('#venta_producto_'+dataVenta[index].id).val();
             
            if (producto) {
                var cantidad = $('#venta_cantidad_'+dataVenta[index].id).val();
                var costo = $('#venta_costo_'+dataVenta[index].id).val();
                var ivaSum = $('#venta_iva_valor_'+dataVenta[index].id).val();
                var totaSum = $('#venta_total_'+dataVenta[index].id).val();
                var descSum = $('#venta_descuento_valor_'+dataVenta[index].id).val();
    
                iva+= parseInt(ivaSum ? ivaSum : 0);
                descuento+= parseInt(descSum ? descSum : 0);
                total+= parseInt(totaSum ? totaSum : 0);
                valorBruto+= (cantidad*costo) - descSum;
            }
        }
        if (total >= topeRetencionVenta) {
            retencion = porcentajeRetencionVenta ? (valorBruto * porcentajeRetencionVenta) / 100 : 0;
            total = total - retencion;
        }
    } else {
        $("#crearCapturaVenta").hide();
        $("#crearCapturaVentaDisabled").show();
    }

    return [iva, retencion, descuento, total];
}

function changeProductoVenta (idRow) {
    var data = $('#venta_producto_'+idRow).select2('data')[0];
    console.log('data: ',data);
    return;
    if (data.length == 0) {
        return
    }
    
    if (data.inventarios) {
        var totalInventario = parseInt(data.inventarios[0].cantidad);
        $("#venta_existencia_"+idRow).val(totalInventario);
        $("#venta_cantidad_"+idRow).attr({"max" : totalInventario});
    }

    if (data.familia.cuenta_venta_iva && data.familia.cuenta_venta_iva.impuesto) {
        $('#venta_iva_porcentaje_'+idRow).prop('disabled', false);
        $('#venta_iva_porcentaje_'+idRow).val(data.familia.cuenta_venta_iva.impuesto.porcentaje);
    }

    if (data.familia.cuenta_venta_retencion && data.familia.cuenta_venta_retencion.impuesto) {
        var impuestoPorcentaje = parseFloat(data.familia.cuenta_venta_retencion.impuesto.porcentaje);
        var topeValor = parseFloat(data.familia.cuenta_venta_retencion.impuesto.base);
        if (impuestoPorcentaje > porcentajeRetencion) {
            porcentajeRetencion = impuestoPorcentaje;
            topeRetencion = topeValor;
        }
    }

    $('#venta_costo_'+idRow).val(parseFloat(data.precio_inicial));
    $('#venta_producto_'+idRow).select2('open');
    $('#venta_cantidad_'+idRow).prop('disabled', false);
    $('#venta_costo_'+idRow).prop('disabled', false);
    $('#venta_descuento_porcentaje_'+idRow).prop('disabled', false);
    $('#venta_descuento_valor_'+idRow).prop('disabled', false);
    $('#venta_iva_porcentaje_'+idRow).prop('disabled', false);
    
    document.getElementById('venta_texto_retencion').innerHTML = 'RETENCIÓN '+ porcentajeRetencion+'%';
        
    calcularProducto(idRow);
    
    setTimeout(function(){
        $('#venta_cantidad_'+idRow).focus();
        $('#venta_cantidad_'+idRow).select();
    },10);
}