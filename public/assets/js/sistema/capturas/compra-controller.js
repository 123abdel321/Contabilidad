var fecha = null;
var compra_table = null;
var validarFacturaCompra = null;
var idCompraProducto = 0;
var $comboBodega = null;
var $comboProveedor = null;
var porcentajeRetencion = 0;
var topeRetencion = 0;

function compraInit () {
    
    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_manual_compra').val(fecha);

    compra_table = $('#compraTable').DataTable({
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
                    console.log('col: ',col);
                    console.log('idCompraProducto: ',idCompraProducto);
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteProductoCompra(${idCompraProducto})" id="delete-producto-compra_${idCompraProducto}"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            {//PRODUCTO
                "data": function (row, type, set, col){
                    return `<select class="form-control form-control-sm combo_producto combo-grid" id="combo_producto_${idCompraProducto}" onchange="changeProductoCompra(${idCompraProducto})" onfocusout="calcularProducto(${idCompraProducto})"></select>`;
                },
            },
            {//EXISTENCIAS
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_existencia_${idCompraProducto}" value="1" disabled>`;
                }
            },
            {//CANTIDAD
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_cantidad_${idCompraProducto}" min="1" value="1" onkeydown="CantidadkeyDown(${idCompraProducto}, event)" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//COSTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_costo_${idCompraProducto}" value="0" onkeydown="CostokeyDown(${idCompraProducto}, event)" style="min-width: 100px;" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//% DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_descuento_porcentaje_${idCompraProducto}" value="0"  onkeydown="DescuentokeyDown(${idCompraProducto}, event)" maxlength="2" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//VALOR DESCUENTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_descuento_valor_${idCompraProducto}" value="0" onkeydown="DescuentoTotalkeyDown(${idCompraProducto}, event)" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//% IVA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_iva_porcentaje_${idCompraProducto}" value="0" onkeydown="IvakeyDown(${idCompraProducto}, event)" maxlength="2" onfocusout="calcularProducto(${idCompraProducto})" disabled>`;
                }
            },
            {//VALOR IVA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_iva_valor_${idCompraProducto}" value="0" onkeydown="IvaTotalkeyDown(${idCompraProducto}, event)" disabled>`;
                }
            },
            {//TOTAL
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 110px;" id="compra_total_${idCompraProducto}" value="0" disabled>`;
                }
            },
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#compraTable').on('draw.dt', function() {
                $('.combo_producto').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    minimumInputLength: 1,
                    ajax: {
                        url: 'api/producto/combo-producto',
                        headers: headers,
                        dataType: 'json',
                        processResults: function (data) {
                            return {
                                results: data.data
                            };
                        }
                    },
                    // templateResult: formatProducto,
                    // templateSelection: formatRepoSelection
                });
            });

            function formatProducto (repo) {
                console.log(repo);
                if (repo.loading) {
                  return repo.text;
                }

                var $container = $(`<div>
                </div>`);
              
                // var $container = $(
                //   "<div class='select2-result-repository clearfix'>" +
                //     "<div class='select2-result-repository__avatar'><img src='https://listardatos.com/img/logo_contabilidad.png' /></div>" +
                //     "<div class='select2-result-repository__meta'>" +
                //       "<div class='select2-result-repository__title'></div>" +
                //       "<div class='select2-result-repository__description'></div>" +
                //       "<div class='select2-result-repository__statistics'>" +
                //         "<div class='select2-result-repository__forks'><i class='fa fa-flash'></i> </div>" +
                //         "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> </div>" +
                //         "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> </div>" +
                //       "</div>" +
                //     "</div>" +
                //   "</div>"
                // );
              
                // $container.find(".select2-result-repository__title").text(repo.full_name);
                // $container.find(".select2-result-repository__description").text(repo.description);
                // $container.find(".select2-result-repository__forks").append(repo.forks_count + " Forks");
                // $container.find(".select2-result-repository__stargazers").append(repo.stargazers_count + " Stars");
                // $container.find(".select2-result-repository__watchers").append(repo.watchers_count + " Watchers");
              
                return $container;
                
            }

            function formatRepoSelection (repo) {
                return repo.full_name || repo.text;
            }
        }
    });

    $comboProveedor = $('#id_proveedor_compra').select2({
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

    $comboBodega = $('#id_bodega_compra').select2({
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

    if(primeraBodegaCompra){
        var dataBodega = {
            id: primeraBodegaCompra.id,
            text: primeraBodegaCompra.codigo + ' - ' + primeraBodegaCompra.nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodega.append(newOption).trigger('change');
        $comboBodega.val(dataBodega.id).trigger('change');
    }

    setTimeout(function(){
        $comboProveedor.select2("open");
    },10);
}

function addRowProducto () {
    console.log('idCompraProducto: ',idCompraProducto);
    compra_table.row.add({
        "id": idCompraProducto,
        "cantidad": 1,
        "costo": 0,
        "porcentaje_descuento": 0,
        "valor_descuento": 0,
        "porcentaje_iva": 0,
        "valor_iva": 0,
        "valor_total": 0,
    }).draw(false)

    $('#card-compra').focus();
    document.getElementById("card-compra").scrollLeft = 0;

    $('#combo_producto_'+idCompraProducto).focus();
    $('#combo_producto_'+idCompraProducto).select2('open');
    idCompraProducto++;
}

function changeProductoCompra (idRow) {
    var data = $('#combo_producto_'+idRow).select2('data')[0];

    if (data.length == 0) {
        return
    }

    if (data.familia.cuenta_compra_iva && data.familia.cuenta_compra_iva.impuesto) {
        $('#compra_iva_porcentaje_'+idRow).prop('disabled', false);
        $('#compra_iva_porcentaje_'+idRow).val(data.familia.cuenta_compra_iva.impuesto.porcentaje);
    }

    if (data.familia.cuenta_compra_retencion && data.familia.cuenta_compra_retencion.impuesto) {
        var impuestoPorcentaje = parseFloat(data.familia.cuenta_compra_retencion.impuesto.porcentaje);
        var topeValor = parseFloat(data.familia.cuenta_compra_retencion.impuesto.base);
        if (impuestoPorcentaje > porcentajeRetencion) {
            porcentajeRetencion = impuestoPorcentaje;
            topeRetencion = topeValor;
        }
    }

    $('#compra_costo_'+idRow).val(parseFloat(data.precio_inicial));

    $('#combo_producto_'+idRow).select2('open');
    $('#compra_cantidad_'+idRow).prop('disabled', false);
    $('#compra_costo_'+idRow).prop('disabled', false);
    $('#compra_descuento_porcentaje_'+idRow).prop('disabled', false);
    $('#compra_descuento_valor_'+idRow).prop('disabled', false);
    $('#compra_iva_porcentaje_'+idRow).prop('disabled', false);
    // $('#compra_iva_valor_'+idRow).prop('disabled', false);
    // $('#compra_total_'+idRow).prop('disabled', false);
    
    document.getElementById('compra_texto_retencion').innerHTML = 'RETENCIÓN '+ porcentajeRetencion+'%';
        
    calcularProducto(idRow);
    
    setTimeout(function(){
        $('#compra_cantidad_'+idRow).focus();
        $('#compra_cantidad_'+idRow).select();
    },10);
}

$("#id_proveedor_compra").on('change', function(event) {
    var data = $(this).select2('data');
    if(data.length){
        setTimeout(function(){
            $('#fecha_manual_compra').focus();
            $('#fecha_manual_compra').select();
        },10);
    }
});

$("#fecha_manual_compra").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();
        setTimeout(function(){
            $('#documento_referencia_compra').focus();
            $('#documento_referencia_compra').select();
        },10);
    }
});

$("#documento_referencia_compra").on('keydown', function(event) {
    if(event.keyCode == 13){
        event.preventDefault();

        document.getElementById('iniciarCapturaCompra').click();
    }
});

function buscarFacturaCompra(event) {

    if (validarFacturaCompra) {
        validarFacturaCompra.abort();
    }
    
    
    $('#documento_referencia_compra_loading').show();

    var botonPrecionado = event.key.length == 1 ? event.key : '';
    var documento_referencia = $('#documento_referencia_compra').val()+''+botonPrecionado;
    
    $('#documento_referencia_compra').removeClass("is-invalid");
    $('#documento_referencia_compra').removeClass("is-valid");

    if(event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
    setTimeout(function(){
        validarFacturaCompra = $.ajax({
            url: base_url + 'existe-factura',
            method: 'GET',
            data: {documento_referencia: documento_referencia},
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
                $("#error_documento_referencia_compra").text('La factura ');
            }
        }).fail((err) => {
            $('#documento_referencia_compra_loading').hide();
        });
    },100);
}

function CantidadkeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProducto(idRow);
        setTimeout(function(){
            $('#compra_costo_'+idRow).focus();
            $('#compra_costo_'+idRow).select();
        },10);
    }
}

function CostokeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProducto (idRow);
        setTimeout(function(){
            $('#compra_descuento_porcentaje_'+idRow).focus();
            $('#compra_descuento_porcentaje_'+idRow).select();
        },10);
    }
}

function DescuentokeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProducto(idRow);
        setTimeout(function(){
            $('#compra_descuento_valor_'+idRow).focus();
            $('#compra_descuento_valor_'+idRow).select();
        },10);
    }
}

function DescuentoTotalkeyDown (idRow, event) {
    if(event.keyCode == 13){
        var descuentoProductoValor = $('#compra_descuento_valor_'+idRow).val();
        var costoProducto = $('#compra_costo_'+idRow).val();
        var cantidadProducto = $('#compra_cantidad_'+idRow).val();
        var porcentajeDescuento = descuentoProductoValor * 100 / (costoProducto * cantidadProducto);

        $('#compra_descuento_porcentaje_'+idRow).val(porcentajeDescuento);
        calcularProducto(idRow);
        setTimeout(function(){
            $('#compra_iva_porcentaje_'+idRow).focus();
            $('#compra_iva_porcentaje_'+idRow).select();
        },10);
    }
}

function IvakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProducto(idRow);
        addRowProducto();
    }
}

$(document).on('click', '#iniciarCapturaCompra', function () {
    var form = document.querySelector('#compraFilterForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        $("#error_documento_referencia_compra").text('El No. factura requerido');
        return;
    }
    
    $("#iniciarCapturaCompra").hide();
    $("#agregarCompra").show();
    $("#cancelarCapturaCompra").show();
    $("#crearCapturaCompraDisabled").show();

    addRowProducto();
});

$(document).on('click', '#agregarCompra', function () {
    addRowProducto();
});

$(document).on('click', '#crearCapturaCompra', function () {


    Swal.fire({
        title: 'Guardar compra?',
        text: 'Desea guardar compra en la tabla?',
        icon: 'question',
        showCancelButton: true,
        cancelButtonColor: '#d33',
        confirmButtonText: 'Guardar!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){
            saveCompra();
        }
    })
});

function saveCompra() {
    
    ocultarBotonesCabezaCompra();

    $('#iniciarCapturaCompraLoading').show();

    let data = {
        productos: getProductosCompra(),
        id_proveedor: $("#id_proveedor_compra").val(),
        id_bodega: $("#id_bodega_compra").val(),
        fecha_manual: $("#fecha_manual_compra").val(),
        documento_referencia: $("#documento_referencia_compra").val(),
    }
    $.ajax({
        url: base_url + 'compras',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            if(res.impresion) {
                window.open("/compras-print/"+res.impresion, "", "_blank");
            }
            idCompraProducto = 0;
            $('#iniciarCapturaCompra').hide();
            $('#iniciarCapturaCompraLoading').hide();
            $('#documento_referencia_compra').val('');

            var totalRows = compra_table.rows().data().length;
            for (let index = 0; index < totalRows; index++) {
                compra_table.row(0).remove().draw();
            }

            mostrarValoresCompras();
            agregarToast('exito', 'Creación exitosa', 'Compra creada con exito!', true);
            setTimeout(function(){
                $comboProveedor.select2("open");
            },10);
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
        $("#agregarDocumentos").show();
        $("#crearCapturaDocumentos").show();
        $("#iniciarCapturaDocumentos").hide();
        $("#cancelarCapturaDocumentos").show();
        $("#crearCapturaDocumentosDisabled").hide();
        $("#iniciarCapturaDocumentosLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        for (field in mensaje) {
            var errores = mensaje[field];
            for (campo in errores) {
                errorsMsg += field+": "+errores[campo]+" <br>";
            }
            
        };
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
    console.log('getProductosCompra');

    var dataDocumento = compra_table.rows().data();
    if(dataDocumento.length > 0){
        for (let index = 0; index < dataDocumento.length; index++) {

            const id_row = dataDocumento[index].id;
            var id_producto = $('#combo_producto_'+id_row).val();
            var cantidad = $('#compra_cantidad_'+id_row).val();
            
            if (id_producto && cantidad) {
                var costo = $('#compra_costo_'+id_row).val();
                var descuento_porcentaje = $('#compra_descuento_porcentaje_'+id_row).val();
                var descuento_valor = $('#compra_descuento_valor_'+id_row).val();
                var iva_porcentaje = $('#compra_iva_porcentaje_'+id_row).val();
                var iva_valor = $('#compra_iva_valor_'+id_row).val();
                var total = $('#compra_total_'+id_row).val();

                data.push({
                    id_producto: parseInt(id_producto),
                    cantidad: parseInt(cantidad),
                    costo: costo ? parseFloat(costo) : 0,
                    subtotal: parseInt(cantidad) * parseFloat(costo),
                    descuento_porcentaje: descuento_porcentaje ? parseFloat(descuento_porcentaje) : 0,
                    descuento_valor: descuento_valor ? parseFloat(descuento_valor) : 0,
                    iva_porcentaje: iva_porcentaje ? parseFloat(iva_porcentaje) : 0,
                    iva_valor: iva_valor ? parseFloat(iva_valor) : 0,
                    total: total ? parseFloat(total) : 0,
                });
            }
        }
    }
    return data;
}

function calcularProducto (idRow) {
    var costoProducto = $('#compra_costo_'+idRow).val();
    var cantidadProducto = $('#compra_cantidad_'+idRow).val();
    var ivaProducto = $('#compra_iva_porcentaje_'+idRow).val();
    var descuentoProducto = $('#compra_descuento_porcentaje_'+idRow).val();
    var totalPorCantidad = 0;
    var totalIva = 0;
    var totalDescuento = 0;
    var totalProducto = 0;
    
    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    if (descuentoProducto > 0) {
        totalDescuento = totalPorCantidad * descuentoProducto / 100;
        $('#compra_descuento_valor_'+idRow).val(totalDescuento);
    }

    if (ivaProducto > 0) {
        totalIva = (totalPorCantidad - totalDescuento) * ivaProducto / 100;
        $('#compra_iva_valor_'+idRow).val(totalIva);
    }

    totalProducto = totalPorCantidad - totalDescuento + totalIva;
    $('#compra_total_'+idRow).val(totalProducto);

    mostrarValoresCompras ();
}

function mostrarValoresCompras () {
    var [iva, retencion, descuento, total] = totalValoresCompras();

    $("#compra_total_iva").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(iva));
    $("#compra_total_descuento").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(descuento));
    $("#compra_total_retencion").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(retencion));
    $("#compra_total_valor").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total));
}

function totalValoresCompras() {
    var iva = retencion = descuento = total = 0;
    var valorBruto = 0;
    var dataCompra = compra_table.rows().data();

    if(dataCompra.length > 0) {
        $("#crearCapturaCompra").show();
        $("#crearCapturaCompraDisabled").hide();
        
        for (let index = 0; index < dataCompra.length; index++) {
            var producto = $('#combo_producto_'+dataCompra[index].id).val();
             
            if (producto) {
                var cantidad = $('#compra_cantidad_'+dataCompra[index].id).val();
                var costo = $('#compra_costo_'+dataCompra[index].id).val();
                var ivaSum = $('#compra_iva_valor_'+dataCompra[index].id).val();
                var totaSum = $('#compra_total_'+dataCompra[index].id).val();
                var descSum = $('#compra_descuento_valor_'+dataCompra[index].id).val();
    
                iva+= parseInt(ivaSum ? ivaSum : 0);
                descuento+= parseInt(descSum ? descSum : 0);
                total+= parseInt(totaSum ? totaSum : 0);
                valorBruto+= (cantidad*costo) - descSum;
            }
        }
        if (total >= topeRetencion) {
            retencion = porcentajeRetencion ? (valorBruto * porcentajeRetencion) / 100 : 0;
            total = total - retencion;
        }
    } else {
        $("#crearCapturaCompra").hide();
        $("#crearCapturaCompraDisabled").show();
    }

    return [iva, retencion, descuento, total];
}

function deleteProductoCompra (idRow) {
    let dataCompra = compra_table.rows().data();

    for (let row = 0; row < dataCompra.length; row++) {
        let element = dataCompra[row];
        if(element.id == idRow) {
            compra_table.row(row).remove().draw();
            if(!compra_table.rows().data().lengt){
                $("#crearCapturaCompraDisabled").show();
                $("#crearCapturaCompra").hide();
            }
        }
    }
    mostrarValoresCompras();
}