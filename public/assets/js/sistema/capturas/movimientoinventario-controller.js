
var $comboNitMI = null;
var idComprobante = null;
var key13PressNewRowMI = false;
var $comboBodegaOrigenMI = null;
var $comboBodegaDestinoMI = null;
var $comboCargueDescargueMi = null;
var fechaMovimientoInventario = null;
var movimiento_inventario_table = null;
var idMovimientoInventarioProducto = 0;
var validarExistenciasProductoMI = null;
var guardandoMovimientoContable = false;

function movimientoinventarioInit() {

    fechaMovimientoInventario = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $('#fecha_manual_movimiento_inventario').val(fechaMovimientoInventario);

    movimiento_inventario_table = $('#movimientoInventarioTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
        deferLoading: 0,
        initialLoad: false,
        autoWidth: true,
        language: lenguajeDatatable,
        ordering: false,
        columnDefs: [{
            'orderable': false
        }],
        columns: [
            {//BORRAR
                "data": function (row, type, set, col){
                    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteProductoMovimientoInventario(${idMovimientoInventarioProducto})" id="delete-producto-movimiento-inventario_${idMovimientoInventarioProducto}"><i class="fas fa-trash-alt"></i></span>`;
                }
            },
            {//PRODUCTO
                "data": function (row, type, set, col){
                    return `<select class="form-control form-control-sm movimiento-inventario_producto combo-grid" id="movimiento-inventario_producto_${idMovimientoInventarioProducto}" onchange="changeProductoMovimientoInventario(${idMovimientoInventarioProducto})" onfocusout="calcularProductoMovimientoInventario(${idMovimientoInventarioProducto})"></select>`;
                },
            },
            {//EXISTENCIAS
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="movimiento-inventario_existencia_${idMovimientoInventarioProducto}" disabled>`;
                }
            },
            {//CANTIDAD
                "data": function (row, type, set, col){
                    return `
                        <div class="input-group" style="height: 30px;">
                            <input type="number" class="form-control form-control-sm" style="min-width: 80px; border-right: solid 1px #b3b3b3; border-top-right-radius: 10px; border-bottom-right-radius: 10px; text-align: right;" id="movimiento-inventario_cantidad_${idMovimientoInventarioProducto}" min="1" value="0" onkeydown="cantidadMIkeyDown(${idMovimientoInventarioProducto}, event)" onfocusout="calcularProductoMovimientoInventario(${idMovimientoInventarioProducto})" disabled>
                            <i class="fa fa-spinner fa-spin fa-fw movimiento_inventario_producto_load" id="movimiento-inventario_producto_load_${idMovimientoInventarioProducto}" style="display: none;"></i>
                            <div id="movimiento-inventario_cantidad_text_${idMovimientoInventarioProducto}" style="position: absolute; margin-top: 30px;" class="invalid-feedback">
                            </div>
                        </div>
                    `;
                }
            },
            {//COSTO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 80px; text-align: right;" id="movimiento-inventario_costo_${idMovimientoInventarioProducto}" value="0" onkeydown="CostoVentakeyDown(${idMovimientoInventarioProducto}, event)" style="min-width: 100px;" onfocusout="calcularProductoMovimientoInventario(${idMovimientoInventarioProducto})" disabled>`;
                }
            },
            {//TOTAL
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" style="min-width: 90px; text-align: right;" id="movimiento-inventario_total_${idMovimientoInventarioProducto}" value="0" disabled>`;
                }
            },
        ],
        initComplete: function () {
            $('#movimientoInventarioTable').on('draw.dt', function() {
                $('.movimiento-inventario_producto').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    minimumInputLength: 1,
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
                                id_bodega: $("#id_bodega_origen_movimiento_inventario").val(),
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
                    },
                    templateResult: formatProducto,
                    templateSelection: formatRepoSelection
                });
            });

            function formatProducto (producto) {
                if (producto.loading) return producto.text;

                var urlImagen = producto.imagen ?
                    bucketUrl+producto.imagen :
                    '/img/sin_imagen.png';

                var inventario = producto.inventarios.length > 0 ? 
                    producto.inventarios[0].cantidad+' Existencias' :
                    'Sin inventario';

                var color = producto.inventarios.length > 0 ?
                    producto.inventarios[0].cantidad <= 0 ? 
                    '#a30000' : '#1c4587' :
                    '#838383';

                var $container = '';

                if (producto.familia.inventario) {
                    var $container = $(`
                    <div class="row">
                        <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                            <img
                                style="width: 40px; border-radius: 10%;"
                                    src="${urlImagen}" />
                            </div>
                            <div class="col-9" style="padding-left: 0px !important">
                                <div class="row" style="margin-left: 5px;">
                                    <div class="col-12" style="padding-left: 0px !important">
                                        <h6 style="font-size: 12px; margin-bottom: 0px; color: black;">${producto.text}</h6>
                                    </div>
                                    <div class="col-12" style="padding-left: 0px !important">
                                        <i class="fas fa-box-open" style="font-size: 11px; color: ${color};"></i>
                                        ${inventario}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    var $container = $(`
                        <div class="row">
                            <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                                <img
                                    style="width: 40px; border-radius: 10%;"
                                    src="${urlImagen}" />
                            </div>
                            <div class="col-9">
                                <div class="row">
                                    <div class="col-12" style="padding-left: 0px !important">
                                        <h6 style="font-size: 12px; margin-bottom: 0px; color: black; margin-left: 10px;">${producto.text}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                }


                return $container;
            }

            function formatRepoSelection (producto) {
                return producto.full_name || producto.text;
            }
        }
    });

}

$comboNitMI = $('#id_nit_movimiento_inventario').select2({
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

$comboBodegaOrigenMI = $('#id_bodega_origen_movimiento_inventario').select2({
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

$comboBodegaDestinoMI = $('#id_bodega_destino_movimiento_inventario').select2({
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
        data: function (params) {
            var query = {
                q: params.term,
                with_out: $('#id_bodega_origen_movimiento_inventario').val(),
                _type: 'query'
            }
            return query;
        },
        processResults: function (data) {
            return {
                results: data.data
            };
        }
    }
});

$comboCargueDescargueMi = $('#id_cargue_descargue_movimiento_inventario').select2({
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
        url: 'api/cargue-descargue/combo',
        headers: headers,
        dataType: 'json',
        data: function (params) {
            var query = {
                q: params.term,
                tipo: $('#tipo_movimiento_inventario').val(),
                _type: 'query'
            }
            return query;
        },
        processResults: function (data) {
            return {
                results: data.data
            };
        }
    }
});

$(document).on('click', '#iniciarCapturaMovimientoInventario', function () {
    var form = document.querySelector('#movimientoInventarioFilterForm');

    if(!form.checkValidity()){
        console.log('maloo');
        form.classList.add('was-validated');
        return;
    }

    $("#tipo_movimiento_inventario").prop('disabled', true);
    $("#id_cargue_descargue_movimiento_inventario").prop('disabled', true);
    $("#id_bodega_origen_movimiento_inventario").prop('disabled', true);
    $("#id_bodega_destino_movimiento_inventario").prop('disabled', true);
    
    $("#iniciarCapturaMovimientoInventario").hide();
    $("#agregarMovimientoInventario").show();
    $("#cancelarCapturaMovimientoInventario").show();
    $("#crearCapturaMovimientoInventarioDisabled").show();

    addRowMovimientoInventario();
});

$(document).on('click', '#cancelarCapturaMovimientoInventario', function () {
    cancelarCaptura();
});

function cancelarCaptura () {
    idMovimientoInventarioProducto = 0;
    var totalRows = movimiento_inventario_table.rows().data().length;

    if(movimiento_inventario_table.rows().data().length){
        movimiento_inventario_table.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            movimiento_inventario_table.row(0).remove().draw();
        }
        
        mostrarValoresMovimientoInventario();
    }

    clearFiltersMovimientoInventario();
}

function changeProductoMovimientoInventario (idRow) {

    var data = $('#movimiento-inventario_producto_'+idRow).select2('data');
    var tipoMovimiento = $('#tipo_movimiento_inventario').val();
    if (data.length == 0) return;
    data = data[0];

    if (data.inventarios.length > 0 && data.familia.inventario) {
        var totalInventario = parseFloat(data.inventarios[0].cantidad);

        if (tipoMovimiento != "1") {
            $("#movimiento-inventario_cantidad_"+idRow).attr({"max" : totalInventario});
        }
        $("#movimiento-inventario_existencia_"+idRow).val(totalInventario);
    
    }

    $('#movimiento-inventario_costo_'+idRow).val(parseFloat(data.precio));
    $('#movimiento-inventario_cantidad_'+idRow).prop('disabled', false);
    $('#movimiento-inventario_costo_'+idRow).prop('disabled', false);

    calcularProductoMovimientoInventario(idRow);

    setTimeout(function(){
        $('#movimiento-inventario_cantidad_'+idRow).focus();
        $('#movimiento-inventario_cantidad_'+idRow).select();
    },10);
}

function cantidadMIkeyDown (idRow, event) {
    if(event.keyCode == 13){
        key13PressNewRowMI = true;
        if (!validarExistenciasMovimientoInventario(idRow)) return;
    }
}

function CostoVentakeyDown (idRow, event) {
    if(event.keyCode == 13){
        calcularProductoMovimientoInventario (idRow);
        addRowMovimientoInventario();
    }
}

function clearFiltersMovimientoInventario() {
    $('#div_bodega_destino_movimiento_inventario').hide();
    $("#consecutivo_movimiento_inventario").val('');
    
    $('#id_nit_movimiento_inventario').val('').trigger('change');
    $('#id_bodega_origen_movimiento_inventario').val('').trigger('change');
    $('#id_cargue_descargue_movimiento_inventario').val('').trigger('change');

    $("#tipo_movimiento_inventario").prop('disabled', false);
    $("#id_cargue_descargue_movimiento_inventario").prop('disabled', false);
    $("#id_bodega_origen_movimiento_inventario").prop('disabled', false);
    $("#id_bodega_destino_movimiento_inventario").prop('disabled', false);

    $("#agregarMovimientoInventario").hide();
    $("#crearCapturaMovimientoInventario").hide();
    $("#iniciarCapturaMovimientoInventario").show();
    $("#cancelarCapturaMovimientoInventario").hide();
    $("#crearCapturaMovimientoInventarioDisabled").hide();
    $("#iniciarCapturaMovimientoInventarioLoading").hide();

    var form = document.querySelector('#movimientoInventarioFilterForm');
    form.classList.remove('was-validated');
}

function changeTipoMovimientoInventario() {
    var tipoMovimiento = $('#tipo_movimiento_inventario').val();

    if (tipoMovimiento == '2') {
        $('#div_bodega_destino_movimiento_inventario').show();
        $("#id_bodega_destino_movimiento_inventario").prop("required", true);
    } else {
        $('#div_bodega_destino_movimiento_inventario').hide();
        $("#id_bodega_destino_movimiento_inventario").prop("required", false);
    }

    $('#id_cargue_descargue_movimiento_inventario').val('').trigger('change');
}

function changeCargueDescargue() {
    var tipoMovimiento = $('#tipo_movimiento_inventario').val();
    var data = $('#id_cargue_descargue_movimiento_inventario').select2('data');

    if (tipoMovimiento == '2') {
        $('#div_bodega_destino_movimiento_inventario').show();
        $("#id_bodega_destino_movimiento_inventario").prop("required", true);
    } else {
        $('#div_bodega_destino_movimiento_inventario').hide();
        $("#id_bodega_destino_movimiento_inventario").prop("required", false);
    }

    if (data.length) {
        data = data[0];

        if(data.nit){
            var dataNit = {
                id: data.nit.id,
                text: data.nit.numero_documento + ' - ' + data.nit.nombre_completo
            };
            var newOption = new Option(dataNit.text, dataNit.id, false, false);
            $comboNitMI.append(newOption).trigger('change');
            $comboNitMI.val(dataNit.id).trigger('change');
        }

        if (data.id_comprobante) {
            idComprobante = data.id_comprobante;
        }

        consecutivoSiguienteMovimientoInventario();
    } else {
        $comboNitMI.val('').trigger('change');
        $comboBodegaDestinoMI.val('').trigger('change');
    }
}

function consecutivoSiguienteMovimientoInventario() {

    var fecha_manual = $('#fecha_manual_movimiento_inventario').val();

    if(idComprobante && fecha_manual) {

        let data = {
            id_comprobante: idComprobante,
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
                $("#consecutivo_movimiento_inventario").val(res.data);
            }
        }).fail((err) => {
        });
    }
}

function addRowMovimientoInventario() {
    var rows = movimiento_inventario_table.rows().data();
    var totalRows = rows.length;
    var dataLast = rows[totalRows - 1];

    if (dataLast) {
        var cuentaLast = $('#movimiento-inventario_producto_'+dataLast.id).val();
        if (!cuentaLast) {
            $('#movimiento-inventario_producto_'+dataLast.id).select2('open');

            document.getElementById("card-movimiento-inventario").scrollLeft = 0;
            return;
        }
    }

    movimiento_inventario_table.row.add({
        "id": idMovimientoInventarioProducto,
        "cantidad": 1,
        "costo": 0,
        "existencias": 0,
        "valor_total": 0,
    }).draw(false);

    $('#card-movimiento-inventario').focus();
    document.getElementById("card-movimiento-inventario").scrollLeft = 0;

    $('#movimiento-inventario_producto_'+idMovimientoInventarioProducto).focus();
    $('#movimiento-inventario_producto_'+idMovimientoInventarioProducto).select2('open');

    idMovimientoInventarioProducto++;
}

function calcularProductoMovimientoInventario(idRow, validarCantidad = false) {

    var totalProducto = 0;
    var totalPorCantidad = 0;
    var costoProducto = $('#movimiento-inventario_costo_'+idRow).val();
    var cantidadProducto = $('#movimiento-inventario_cantidad_'+idRow).val();

    if (validarCantidad && !validarExistenciasMovimientoInventario(idRow)) return;

    if (cantidadProducto > 0) {
        totalPorCantidad = cantidadProducto * costoProducto;
    }

    totalProducto = totalPorCantidad;
    $('#movimiento-inventario_total_'+idRow).val(totalProducto);
    
    mostrarValoresMovimientoInventario ();
}

function mostrarValoresMovimientoInventario () {
    var [cantidad, total] = totalValoresMovimientoInventario();

    $("#movimiento_inventario_cantidad").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(cantidad));
    $("#movimiento_inventario_total_valor").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total));
}

function totalValoresMovimientoInventario() {
    var cantidadTotal = total = 0;
    var dataMovimientoInventario = movimiento_inventario_table.rows().data();

    if(dataMovimientoInventario.length > 0) {
        
        $("#crearCapturaMovimientoInventarioDisabled").hide();
        
        for (let index = 0; index < dataMovimientoInventario.length; index++) {
            var producto = $('#movimiento-inventario_producto_'+dataMovimientoInventario[index].id).val();
             
            if (producto) {
                var cantidad = $('#movimiento-inventario_cantidad_'+dataMovimientoInventario[index].id).val();
                var costo = $('#movimiento-inventario_costo_'+dataMovimientoInventario[index].id).val();

                if (cantidad && costo) {
                    cantidadTotal+= parseInt(cantidad);
                    total+= parseFloat(cantidad * costo);
                }
            }
        }

    } else {
        $("#crearCapturaMovimientoInventario").hide();
        $("#crearCapturaMovimientoInventarioDisabled").show();
    }

    if (total > 0) {
        $("#crearCapturaMovimientoInventario").show();
        $("#crearCapturaMovimientoInventarioDisabled").hide();
    } else {
        $("#crearCapturaMovimientoInventario").hide();
        $("#crearCapturaMovimientoInventarioDisabled").show();
    }

    return [cantidadTotal, total];
}

function validarExistenciasMovimientoInventario (idRow) {
    var producto = $('#movimiento-inventario_producto_'+idRow).select2('data')[0];
    var cantidad = $('#movimiento-inventario_cantidad_'+idRow).val();
    var rowProductos = movimiento_inventario_table.rows().data();
    var tipoMovimiento = $('#tipo_movimiento_inventario').val();

    if (producto !== undefined && producto.familia && producto.familia.inventario && tipoMovimiento != "1") {

        if (rowProductos.length > 1) {
            consultarExistenciasMovimientoInventario(idRow);
            return false;
        } else {
            if (cantidad > parseInt(producto.inventarios[0].cantidad)) {
                $('#movimiento-inventario_cantidad_text_'+idRow).text("Se ha superado las existencias");
                $('#movimiento-inventario_cantidad_'+idRow).addClass("is-invalid");
                $('#movimiento-inventario_cantidad_'+idRow).removeClass("is-valid");
                $('#movimiento-inventario_cantidad_'+idRow).val(0);
                setTimeout(function(){
                    $('#movimiento-inventario_cantidad_'+idRow).focus();
                    $('#movimiento-inventario_cantidad_'+idRow).select();
                },10);
                return false;
            } else {
                if (key13PressNewRowMI) {
                    key13PressNewRowMI = false;
                    calcularProductoMovimientoInventario(idRow);
                    addRowMovimientoInventario();
                }
                $('#movimiento-inventario_cantidad_'+idRow).removeClass("is-invalid");
            }
        }

    } else if (producto !== undefined && !producto.familia && tipoMovimiento != "1") {
        consultarExistenciasMovimientoInventario(idRow);
        return false;
    }

    addRowMovimientoInventario();
    calcularProductoMovimientoInventario(idRow);
    return true;
}

function consultarExistenciasMovimientoInventario(idRow) {

    var idproducto = $('#movimiento-inventario_producto_'+idRow).val();
    var bodega = $('#id_bodega_origen_movimiento_inventario').val();

    var [cantidadActualRow, cantidadTotal] = totalCantidadProductoMovimientoInventario(idRow)

    if (validarExistenciasProductoMI) {
        validarExistenciasProductoMI.abort();
    }
    
    $('#movimiento-inventario_producto_load_'+idRow).show();

    setTimeout(function(){
        validarExistenciasProductoMI = $.ajax({
            url: base_url + 'existencias-producto',
            method: 'GET',
            data: {
                id_producto: idproducto,
                id_bodega: bodega
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarExistenciasProductoMI = null;
            $('#movimiento-inventario_producto_load_'+idRow).hide();
            if (res.data) {
                if (cantidadActualRow + cantidadTotal > parseInt(res.data.cantidad)) {
                    $('#movimiento-inventario_cantidad_text_'+idRow).text("Se ha superado las existencias");
                    $('#movimiento-inventario_cantidad_'+idRow).addClass("is-invalid");
                    $('#movimiento-inventario_cantidad_'+idRow).removeClass("is-valid");

                    if (1 + cantidadTotal > parseInt(res.data.cantidad)) $('#movimiento-inventario_'+idRow).val(0);
                    else $('#movimiento-inventario_cantidad_'+idRow).val(1);
                    
                    setTimeout(function(){
                        $('#movimiento-inventario_cantidad_'+idRow).focus();
                        $('#movimiento-inventario_cantidad_'+idRow).select();
                    },10);

                    return false;
                } else {
                    $('#movimiento-inventario_cantidad_'+idRow).removeClass("is-invalid");
                    if (key13PressNewRowMI) {
                        key13PressNewRowMI = false;
                        calcularProductoMovimientoInventario(idRow);
                        addRowMovimientoInventario();
                    }
                }
            }
            
        }).fail((err) => {
            $('#movimiento-inventario_producto_load_'+idRow).hide();
            validarExistenciasProductoMI = null;
            if(err.statusText != "abort") {
            }
        });
    },300);
}

function totalCantidadProductoMovimientoInventario(idRow) {
    
    var idProducto = $('#movimiento-inventario_producto_'+idRow).val();
    var rowProductos = movimiento_inventario_table.rows().data();
    var cantidadActualRow = parseInt($('#movimiento-inventario_cantidad_'+idRow).val());
    var cantidadTotal = 0;

    for (let index = 0; index < rowProductos.length; index++) {
        var producto = $('#movimiento-inventario_producto_'+rowProductos[index].id).val();
         
        if (producto && rowProductos[index].id != idRow && producto == idProducto) {
            var cantidad = parseInt($('#movimiento-inventario_cantidad_'+rowProductos[index].id).val());
            cantidadTotal+= cantidad;
        }
    }

    return [cantidadActualRow, cantidadTotal];
}

$(document).on('click', '#crearCapturaMovimientoInventario', function () {
    validateSaveVenta();
});


function validateSaveVenta() {
    if (!guardandoMovimientoContable) {
        guardandoMovimientoContable = true;
        saveMovimientoInventario();
    }
}

function saveMovimientoInventario() {

    $("#agregarMovimientoInventario").hide();
    $("#crearCapturaMovimientoInventario").hide();
    $("#cancelarCapturaMovimientoInventario").hide();
    $("#iniciarCapturaMovimientoInventarioLoading").show();

    let data = {
        productos: getProductosMovimientoInventario(),
        tipo: $("#tipo_movimiento_inventario").val(),
        id_nit: $("#id_nit_movimiento_inventario").val(),
        fecha_manual: $("#fecha_manual_movimiento_inventario").val(),
        id_bodega_origen: $("#id_bodega_origen_movimiento_inventario").val(),
        id_bodega_destino: $("#id_bodega_destino_movimiento_inventario").val(),
        id_cargue_descargue: $("#id_cargue_descargue_movimiento_inventario").val(),
    }

    $.ajax({
        url: base_url + 'movimiento-inventario',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoMovimientoContable = false;
        if(res.success){
            cancelarCaptura();
            agregarToast('exito', 'Creación exitosa', 'Movimiento creado con exito!', true);
        } else {
            guardandoMovimientoContable = false;
            $("#agregarMovimientoInventario").show();
            $("#crearCapturaMovimientoInventario").show();
            $("#cancelarCapturaMovimientoInventario").show();
            $("#crearCapturaMovimientoInventarioDisabled").hide();
            $("#iniciarCapturaMovimientoInventarioLoading").hide();
            
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
        guardandoMovimientoContable = false;
        $("#agregarMovimientoInventario").show();
        $("#crearCapturaMovimientoInventario").show();
        $("#cancelarCapturaMovimientoInventario").show();
        $("#iniciarCapturaMovimientoInventarioLoading").hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = "";
        if (typeof mensaje === 'object') {
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += field+": "+errores[campo]+" <br>";
                }
                agregarToast('error', 'Creación errada', errorsMsg);
            };
        } else {
            agregarToast('error', 'Creación errada', mensaje);
        }
    });

}

function getProductosMovimientoInventario() {
    var data = [];

    var dataProductosMovimientoInventario = movimiento_inventario_table.rows().data();

    if(!dataProductosMovimientoInventario.length > 0) return data;

    for (let index = 0; index < dataProductosMovimientoInventario.length; index++) {

        const id_row = dataProductosMovimientoInventario[index].id;
        var id_producto = $('#movimiento-inventario_producto_'+id_row).val();
        var cantidad = $('#movimiento-inventario_cantidad_'+id_row).val();
        
        if (id_producto && cantidad) {
            var costo = $('#movimiento-inventario_costo_'+id_row).val();
            var total = $('#movimiento-inventario_total_'+id_row).val();

            data.push({
                id_producto: parseInt(id_producto),
                cantidad: parseInt(cantidad),
                costo: costo ? parseFloat(costo) : 0,
                total: total ? parseFloat(total) : 0,
            });
        }
    }

    return data;
}




