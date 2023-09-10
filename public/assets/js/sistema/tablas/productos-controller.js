var productos_table = null;
var productos_varaibles_table = null;
var tipo_producto = 0;
var bodegasProductos = [];
var idVarianteSelected = false;
var idProductoBodegaSelected = false;
var trProductoBodegaSelected = false;
var $comboFamilia = null;
var $comboBodega = null;
var $comboBodegaVariante = null;
var cacheProducto = null;

var nuevoProducto = {
    nombre: '',
    codigo: '',
    id_familia: null,
    precio: 0,
    precio_maximo: 0,
    precio_inicial: 0,
    inventarios: [],
    variante: false,
    variantes: [],
    productos_variantes: []
}

function productosInit() {

    productos_varaibles_table = $('#productosVariantesTable').DataTable({
        dom: 'tip',
        paging: false,
        responsive: false,
        processing: true,
        serverSide: false,
        autoWidth: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        ordering: false,
        sScrollX: "100%",
        scroller: {
            displayBuffer: 20,
            rowHeight: 50,
            loadingIndicator: true
        },
        deferRender: true,
        fixedHeader: {
            header: true,
            footer: true,
            headerOffset: 45
        },
        fixedColumns: {
            left: 0,
            right: 1,
        },
        columns: [
            {
                "data": function (row, type, set){
                    var variantesNombre = '';
                    var variantesLength = row.variantes.length;
                    if (variantesLength > 0){
                        for (let index = 0; index < variantesLength; index++) {
                            const variante = row.variantes[index];
                            if (index == 0) variantesNombre = variante.nombre;
                            if (index > 0) variantesNombre+= ' / ' + variante.nombre
                        }
                        return variantesNombre;
                    }
                    return '';
                }
            },
            {//PRECIO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" onfocusout="actualizarPrecio(${row.id})" id="prodvari-precio_${row.id}" value="${row.precio}">`
                }
            },
            {//PRECIO MAXIMO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" onfocusout="actualizarPrecioMaximo(${row.id})" id="prodvari-preciomaximo_${row.id}" value="${row.precio_maximo}">`
                }
            },
            {//PRECIO INICIAL
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" onfocusout="actualizarPrecioInicial(${row.id})" id="prodvari-precioinicial_${row.id}" value="${row.precio_inicial}">`
                }
            },
            {//CODIGO
                "data": function (row, type, set, col){
                    return `<input type="text" class="form-control form-control-sm" onfocusout="actualizarCodigo(${row.id})" id="prodvari-codigo_${row.id}" value="${row.codigo}">`
                }
            },
            {
                "data": function (row, type, set){
                    var html = '<span class="badge bg-light text-dark">Ninguna</span>';
                    var bodegas = row.inventarios;
                    if (bodegas.length == 1) {
                        html = `<span class="badge bg-light text-dark">${bodegas[0].nombre} / ${bodegas[0].cantidad} ud</span>`;
                    } else if (bodegas.length > 1) {
                        var totalBodegas = 0;
                        var totalUnidades = 0;
                        bodegas.forEach(bodega => {
                            totalBodegas++;
                            totalUnidades+= bodega.cantidad;
                        });
                        html = `
                            <span class="badge bg-light text-dark">Total bodegas: ${totalBodegas}</span><br/>
                            <span class="badge bg-light text-dark">Total unidades: ${totalUnidades}</span>
                        `;
                    }
                    return html;
                }
            },
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="bodegaproducvari_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info bodega-productovariante" style="margin-bottom: 0rem !important; min-width: 50px;">Bodegas</span>&nbsp;';
                    html+= '<span id="deleteproducvari_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-productovariante" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ],
        columnDefs: [{
            'orderable': false
        }],
    });

    if (productos_varaibles_table) {
        
        productos_varaibles_table.on('click', '.drop-productovariante', function() {

            var id = this.id.split('_')[1];
            var trProductoVariante = $(this).closest('tr');

            Swal.fire({
                title: 'Eliminar variante ?',
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
                    productos_varaibles_table.row(trProductoVariante).remove().draw();
                    nuevoProducto.productos_variantes[id].estado = false;
                }
            })
        });

        productos_varaibles_table.on('click', '.bodega-productovariante', function() {

            var id = this.id.split('_')[1];
            var trProductoVariante = $(this).closest('tr');

            idProductoBodegaSelected = id;
            trProductoBodegaSelected = trProductoVariante;

            $comboBodegaVariante.val('');
            $('#productos_bodegas_contenedor').empty();

            showBodebasVariantes(id);
        });
        
    }

    $comboFamilia = $('#id_familia_producto').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/familia/combo-familia',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboBodega = $('#id_bodega_producto').select2({
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

    $comboBodegaVariante = $('#id_bodega_producto_variante').select2({
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

    $('#id_variante_producto').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/variante/combo-variante',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $("#table-products-view").show();
    $("#add-products-view").hide();
}

function showBodebasVariantes (id) {

    $('#productos_bodegas_contenedor').empty();
    var producto = nuevoProducto.productos_variantes[id];

    if (producto.inventarios.length > 0) {
        Object.values(producto.inventarios).forEach(inventario => {
            newItemBodega(inventario, false);
        });
    }

    $('#bodegasProductoVarianteFormModal').modal('show');
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$(document).on('click', '#createProducto', function () {
    clearFormProductos();
    // addBodegaToProduct(primeraBodegas, false);
    addVarianteItems();
    addProductosVarianteItems();
    $("#table-products-view").hide();
    $("#add-products-view").show();
    $("#cancelProducto").show();
    $("#saveNewProducto").show();
    $("#createProducto").hide();
    $("#searchInputProductos").hide();
    $("#titulo-view").text('Agregar producto');
});


$(document).on('click', '#saveNewProducto', function () {
    var form = document.querySelector('#newProductoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }
});

$(document).on('click', '#cancelProducto', function () {
    clearFormProductos();
    $("#table-products-view").show();
    $("#add-products-view").hide();
    $("#cancelProducto").hide();
    $("#saveNewProducto").hide();
    $("#createProducto").show();
    $("#searchInputProductos").show();
    $("#titulo-view").text('Productos');
});

function clearFormProductos() {
    $('#bodegas-contenedor').empty();
    nuevoProducto = {
        tipo_producto: 0,
        nombre: '',
        codigo: '',
        id_familia: null,
        precio: 0,
        precio_maximo: 0,
        precio_inicial: 0,
        inventarios: [],
        variante: false,
        variantes: [],
        productos_variantes: []
    }
}

function changeProducType() {
    var checkProducto = $("input[type='radio']#tipo_producto_producto").is(':checked');
    var checkServicio = $("input[type='radio']#tipo_producto_servicio").is(':checked');
    var checkCombo = $("input[type='radio']#tipo_producto_combo").is(':checked');

    $("#text_tipo_producto").hide();
    $("#text_tipo_servicio").hide();
    $("#text_tipo_combo").hide();

    if(checkProducto) setCrearProducto();
    else if (checkServicio) setCrearServicio();
    else if (checkCombo) setCrearCombo();
}

function setCrearProducto() {
    nuevoProducto.tipo_producto = 0;
    $("#text_tipo_producto").show();
    $("#item-maneja-variante").show();
    $('#producto-inventario').show();
    $('#producto-variantes').hide();
    document.getElementById("producto_variantes1").checked = true;
}

function setCrearServicio() {
    nuevoProducto.tipo_producto = 1;
    $("#text_tipo_servicio").show();
    $("#item-maneja-variante").hide();
    $('#producto-inventario').hide();
    $('#producto-variantes').hide();
}

function setCrearCombo() {
    nuevoProducto.tipo_producto = 2;
    $("#text_tipo_combo").show();
    $("#item-maneja-variante").hide();
    $('#producto-inventario').hide();
    $('#producto-variantes').hide();
}

$('input[type=radio][name=producto_variantes]').change(function() {
    if(!$("input[type='radio']#producto_variantes1").is(':checked')){
        nuevoProducto.variante = true;
        $('#producto-inventario').hide();
        $('#producto-variantes').show();
    } else {
        nuevoProducto.variante = false;
        $('#producto-inventario').show();
        $('#producto-variantes').hide();
    }
});

function addBodegaToProduct (bodega, deleteButton = true) {
    var cantidad = parseInt($('#cantidad_bodega_producto').val());
    cantidad = cantidad ? cantidad : 0;
    var html = '';
    html+=  '<div class="item-bodega" style="cursor: pointer;" onclick="editarBodega('+bodega.id+')">';
    html+=      '<i class="fas fa-box-open" style="font-size: 20px; color: #596cff;"></i>';
    html+=  '</div>';

    html+=  '<div style="text-align: -webkit-center; cursor: pointer;" onclick="editarBodega('+bodega.id+')">';
    html+=      '<h6>'+bodega.codigo+ ' - '+bodega.nombre+'</h6>';
    html+=      '<label id="bodega-candidad_'+bodega.id+'">Cantidad: '+cantidad+'</label>';
    html+=  '</div>';

    html+=  '<div>';
    html+=      '<div style="padding: 10px; cursor: pointer" onclick="editarBodega('+bodega.id+')">';
    html+=          '<i class="fas fa-edit" style="font-size: 15px; color: lawngreen;"></i>';
    html+=      '</div>';
    if(deleteButton) {
        html+=  '<div style="padding: 10px; cursor: pointer" onclick="deleteBodega('+bodega.id+')">';
        html+=      '<i class="fas fa-trash-alt" style="font-size: 15px; color: red;"></i>';
        html+=  '</div>';
    }
    html+=  '</div>';

    var item = document.createElement('li');
    item.setAttribute("id", "bodega-producto_"+bodega.id);
    item.setAttribute("class", "list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn");
    item.innerHTML = [
        html
    ].join('');
    document.getElementById('bodegas-contenedor').insertBefore(item, null);

    nuevoProducto.inventarios.push({
        id: parseInt(bodega.id),
        nombre: bodega.nombre,
        codigo: bodega.codigo,
        ubicacion: bodega.ubicacion,
        cantidad: cantidad ? cantidad : 0,
    });
}

function agregarBodegaProducto () {

    var form = document.querySelector('#newProductoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $('#textBodegaProductoCreate').show();
    $('#textBodegaProductoUpdate').hide();
    $("#id_bodega_producto").val(0).change();
    $("#cantidad_bodega_producto").val(0);
    $('#saveBodegaProducto').show();
    $('#updateBodegaProducto').hide();
    $('#bodegasProductoFormModal').modal('show');
}

function editarBodega (idBodega) {
    $('#id_bodega_producto_up').val(idBodega);
    $('#textBodegaProductoCreate').hide();
    $('#textBodegaProductoUpdate').show();
    $('#saveBodegaProducto').hide();
    $('#updateBodegaProducto').show();

    Object.values(nuevoProducto.inventarios).forEach(inventario => {
        if (inventario.id == idBodega) {
            var bodegaProducto = {
                id: inventario.id,
                text: inventario.codigo + ' - ' + inventario.nombre
            }
            var newOption = new Option(bodegaProducto.text, bodegaProducto.id, false, false);
            $comboBodega.append(newOption).trigger('change');
            $comboBodega.val(bodegaProducto.id).trigger('change');
            $('#cantidad_bodega_producto').val(inventario.cantidad);
        }
    });

    $('#bodegasProductoFormModal').modal('show');

    setTimeout(function(){
        $('#cantidad_bodega_producto').focus();
        $('#cantidad_bodega_producto').select();
    },500);
}

function deleteBodega (idBodega) {
    var inventarios = nuevoProducto.inventarios;

    for (let index = 0; index < inventarios.length; index++) {
        const inventario = inventarios[index];
        if(inventario.id == idBodega) {
            nuevoProducto.inventarios.splice(index, 1);
        }
    }
    document.getElementById("bodega-producto_"+idBodega).remove();
}

$(document).on('click', '#saveBodegaProducto', function () {
    
    var form = document.querySelector('#productoBodegaForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    let bodega = $('#id_bodega_producto').select2('data')[0];
    var existeBodega = getBodegaProducto(bodega.id);
    var cantidad = parseInt($('#cantidad_bodega_producto').val());

    if (!existeBodega) {
        addBodegaToProduct(bodega);
    } else {
        document.getElementById('bodega-candidad_'+bodega.id).innerHTML = 'Cantidad: '+cantidad;
        Object.values(nuevoProducto.inventarios).forEach(inventario => {
            if (inventario.id == bodega.id) {
                inventario.cantidad = cantidad;
            }
        });
    }
    
    $('#bodegasProductoFormModal').modal('hide');
});

$(document).on('click', '#saveVariantesProducto', function () {
    nuevoProducto.productos_variantes = [];
    productos_varaibles_table.clear([]).draw();
    $('#variantes-contenedor').empty();
    $('#variantesProductoFormModal').modal('hide');
    $('#contenedor-variantes-generales').show();
    addVarianteItems();
    addProductosVarianteItems();
});

function generarVariantesProductos () {
    
    var variantes = getVariantesActivas();

    var cacheNuevosProductos = [];

    for (let indexVariante = 0; indexVariante < variantes.length; indexVariante++) {

        let variante = variantes[indexVariante];

        if (!variante.estado) continue;

        if (indexVariante > 1) {
            cacheNuevosProductos = [];
            nuevoProducto.productos_variantes.forEach(productosActuales => {
                cacheNuevosProductos = cacheNuevosProductos.concat({
                    precio: productosActuales.precio,
                    codigo: productosActuales.codigo,
                    precio_maximo: productosActuales.precio_maximo,
                    precio_inicial: productosActuales.precio_inicial,
                    inventario: productosActuales.inventario,
                    estado: true,
                    inventarios: [],
                    variantesc: productosActuales.variantes
                });
            });
        }

        for (let indexOpcion = 0; indexOpcion < variante.opciones.length; indexOpcion++) {
            const opcion = variante.opciones[indexOpcion];

            if (!opcion.estado) continue;

            if (indexVariante == 0) {

                cacheNuevosProductos = cacheNuevosProductos.concat({
                    precio: nuevoProducto.precio,
                    codigo: nuevoProducto.codigo,
                    precio_maximo: nuevoProducto.precio_maximo,
                    precio_inicial: nuevoProducto.precio_inicial,
                    inventario: false,
                    estado: true,
                    inventarios: [],
                    variantesc: [opcion]
                });

                nuevoProducto.productos_variantes = nuevoProducto.productos_variantes.concat({
                    precio: nuevoProducto.precio,
                    codigo: nuevoProducto.codigo,
                    precio_maximo: nuevoProducto.precio_maximo,
                    precio_inicial: nuevoProducto.precio_inicial,
                    inventario: false,
                    estado: true,
                    inventarios: [],
                    variantes: [opcion]
                });
            }

            if (indexVariante != 0 && indexOpcion == 0) {
                Object.values(nuevoProducto.productos_variantes).forEach(productoVariantes => {
                    productoVariantes.variantes = productoVariantes.variantes.concat(opcion);
                });
            }

            if (indexVariante != 0 && indexOpcion != 0) {
                Object.values(cacheNuevosProductos).forEach(productoCache => {
                    var varianteCache = productoCache.variantesc;
                    var newData = {
                        precio: nuevoProducto.precio,
                        codigo: nuevoProducto.codigo,
                        precio_maximo: nuevoProducto.precio_maximo,
                        precio_inicial: nuevoProducto.precio_inicial,
                        inventario: false,
                        estado: true,
                        inventarios: nuevoProducto.inventarios,
                        variantes: varianteCache
                    };
                    newData.variantes = newData.variantes.concat(opcion);
                    nuevoProducto.productos_variantes = nuevoProducto.productos_variantes.concat(newData);
                });
            }
        }
    }
}

function getVariantesActivas() {
    var variantes = [];

    Object.values(nuevoProducto.variantes).forEach(variante => {
        if (variante.estado) {
            var opciones = [];
            Object.values(variante.opciones).forEach(opcion => {
                if (opcion.estado) {
                    opciones.push(opcion);
                }
            });
            if(opciones.length > 0) {
                variantes.push({
                    id: variante.id,
                    estado: variante.estado,
                    nombre: variante.nombre,
                    opciones: opciones
                });
            }
        }
    });

    return variantes;
}

$(document).on('click', '#updateBodegaProducto', function () {
    var idBodega = $('#id_bodega_producto_up').val();
    var totalBodega = $('#cantidad_bodega_producto').val();
    $('#bodega-candidad_'+idBodega).html('Cantidad: '+totalBodega);

    Object.values(nuevoProducto.inventarios).forEach(inventario => {
        if (inventario.id == idBodega) {
            inventario.cantidad = totalBodega;
        }
    });

    $('#bodegasProductoFormModal').modal('hide');
});

function agregarVarianteProducto () {

    var form = document.querySelector('#newProductoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $('#variantesProductoFormModal').modal('show');
}

function keyPressNombreOpcion (event) {

    var nombreOpcion = $('#nombre-opcion').val();

    if(event.keyCode == 13) {

        $('#nombre-opcion-loaging').show();

        var data = {
            id_variante: idVarianteSelected,
            nombre: nombreOpcion
        };

        $.ajax({
            url: base_url + 'variante/opcion',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                if(res.message != 'Opción existente!') {
                    crearItemOpcion(res.data);
                }
                $('#nombre-opcion-loaging').hide();
                $('#nombre-opcion').val('');
                $('#form-new-opcion').hide();
                $('#button-new-opcion').show();
            }
        }).fail((err) => {
            $('#nombre-opcion-loaging').hide();
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
            agregarToast('error', 'Error al actualizar familia', errorsMsg);
        });

    }

    if(event.keyCode == 8 && !nombreOpcion) {
        $('#form-new-opcion').hide();
        $('#text-new-opcion').hide();
        $('#button-new-opcion').show();
    }
}

function keyPressNombreInventario (event) {
    var nombreInventario = $('#nombre-variante').val();
    if(event.keyCode == 13) {

        $('#nombre-variante-loaging').show();

        var data = {
            nombre: nombreInventario
        };

        $.ajax({
            url: base_url + 'variante',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                crearItemVariable(res.data);
                $('#nombre-variante-loaging').hide();
                $('#nombre-variante').val('');
                $('#form-new-variante').addClass("hide-new-variante");
                $('#button-new-variable').show();
            }
        }).fail((err) => {
            $('#updateFamilia').show();
            $('#saveFamiliaLoading').hide();
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
            agregarToast('error', 'Error al actualizar familia', errorsMsg);
        });
    }

    if(event.keyCode == 8 && !nombreInventario) {
        $('#form-new-variante').addClass("hide-new-variante");
        $('#button-new-variable').show();
    }
}

function agregarVarianteNombre () {
    $('#form-new-variante').removeClass("hide-new-variante");
    $('#button-new-variable').hide();
    setTimeout(function(){
        $('#nombre-variante').select();
    },10);
}

function agregarOpcionNombre () {
    $('#form-new-opcion').show();
    $('#text-new-opcion').show();
    $('#button-new-opcion').hide();
    setTimeout(function(){
        $('#nombre-opcion').select();
    },10);
}

function removeVariante () {
    var nombreVariante = $('#nombre-opcion').val();
    if(!nombreVariante) {
        $('#form-new-variante').addClass("hide-new-variante");
        $('#button-new-variable').show();
    }
}

function removeOpcion () {
    var nombreOpcion = $('#nombre-opcion').val();
    if(!nombreOpcion) {
        $('#form-new-opcion').hide();
        $('#text-new-opcion').hide();
        $('#button-new-opcion').show();
    }
}

$("#id_variante_producto").on('change', function(e) {
    var data = $(this).select2('data');

    if (!data.length) {
        agregarToast('error', 'Error al seleccionar variante, por favor vuelta a intentarlo', errorsMsg, true);
        return;
    }

    crearItemVariable(data[0]);
});

$("#id_bodega_producto_variante").on('change', function(e) {
    var data = $(this).select2('data');

    if (!data.length) {
        // agregarToast('error', 'Error al seleccionar variante, por favor vuelta a intentarlo', errorsMsg, true);
        return;
    }
    crearItemBodegaProducto(data[0]);
    actualizarRowBodega();

    setTimeout(function(){
        $('#cantidad_bodega_producto').focus();
        $('#cantidad_bodega_producto').select();
    },300);
});

function actualizarRowBodega () {
    var data = getDataById(idProductoBodegaSelected, productos_varaibles_table);
    productos_varaibles_table.row(trProductoBodegaSelected).data(data).draw();
}

function crearItemOpcion (opcion) {
    var html = `
    <label>${opcion.nombre}</label>
    <i class="fas fa-check" style="float: right; margin-top: 5px; margin-right: 5px;"></i>`;

    var item = document.createElement('div');
    item.setAttribute("id", "item-variante-opcion_"+opcion.id);
    item.setAttribute("class", "item-variante-opcion");
    item.setAttribute("style", "margin-top: 10px;");
    item.onclick = function(){
        setStatusCheckOpcion(opcion, opcion.variante.id);
    };
    item.innerHTML = [
        html
    ].join('');
    document.getElementById('contenedor-opciones_'+opcion.variante.id).insertBefore(item, null);

}

function crearItemVariable (variante) {
    var idActiveVariante = false;
    nuevoProducto.variante = true;
    var existeVariante = getVarianteById(variante.id);

    if(!existeVariante){
        idActiveVariante = variante.id;
        newItemVariante(variante);
        newItemVarianteOpciones(variante);
    } else {
        idActiveVariante = existeVariante.id;
    }

    $("#button-new-opcion").show();
    $("#id_variante_producto").val('');

    activeVarianteContenedor(idActiveVariante);
}

function crearItemBodegaProducto (bodega) {
    var existeBodegaProducto = getBodegaProductoVarianteById(bodega.id);

    if (!existeBodegaProducto) {
        newItemBodega(bodega);
    } else {
        setTimeout(function(){
            $('#cantidad-producto-variante_'+bodega.id).focus();
            $('#cantidad-producto-variante_'+bodega.id).select();
        },100);
    }
}

function newItemBodega (bodega, addProducto = true) {
    if (addProducto) {
        nuevoProducto.productos_variantes[idProductoBodegaSelected].inventarios.push({
            id: parseInt(bodega.id),
            nombre: bodega.nombre,
            codigo: bodega.codigo,
            ubicacion: bodega.ubicacion,
            cantidad: bodega.cantidad ? bodega.cantidad : 0,
        });
    }

    var html = `
        <div style="padding: 5px; padding: 5px; border-top: solid 1px #dfdfdf; margin-left: 10px;"></div>
                                    
        <div class="form-group col-12 col-sm-6 col-md-6" >
            <label for="example-text-input" class="form-control-label">Bodega</label>
            <input type="text" class="form-control form-control-sm" id="bodega-producto-variante_${bodega.id}" value="${bodega.nombre}" disabled>
        </div>

        <div class="form-group col-12 col-sm-6 col-md-6" >
            <label for="example-text-input" class="form-control-label">Cantidad</label>
            <input type="number" class="form-control form-control-sm" id="cantidad-producto-variante_${bodega.id}" value="${bodega.cantidad}" onfocusout="actualizarCantidadBodega(${bodega.id})">
        </div>

        <div class="col-12 col-sm-12 col-md-12">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBodegaProductoVariente(${bodega.id})" style="width: 100%; margin-top: 5px; box-shadow: none; ">
                Eliminar
            </button>
        </div>
    `;

    var item = document.createElement('div');
    item.setAttribute("id", "contenedor-variante-bodegas_"+bodega.id);
    item.setAttribute("class", "col-12 col-sm-12 col-md-12 row");
    item.innerHTML = [
        html
    ].join('');
    document.getElementById('productos_bodegas_contenedor').insertBefore(item, null);

    setTimeout(function(){
        $('#cantidad-producto-variante_'+bodega.id).focus();
        $('#cantidad-producto-variante_'+bodega.id).select();
    },100);
}

function actualizarCantidadBodega (idBodega) {
    var cantidad = $('#cantidad-producto-variante_'+idBodega).val();
    var producto = nuevoProducto.productos_variantes[idProductoBodegaSelected];

    Object.values(producto.inventarios).forEach(inventario => {
        if (inventario.id == idBodega) {
            inventario.cantidad = parseInt(cantidad);
        }
    });
}

function deleteBodegaProductoVariente (idBodega) {
    var producto = nuevoProducto.productos_variantes[idProductoBodegaSelected];
    
    for (let index = 0; index < producto.inventarios.length; index++) {
        const inventario = producto.inventarios[index];
        if (inventario.id == idBodega) {
            producto.inventarios.splice(index, 1);
        }
    }

    document.getElementById("contenedor-variante-bodegas_"+idBodega).remove();
}

function newItemVariante (variante) {

    nuevoProducto.variantes.push({
        id: parseInt(variante.id),
        estado: true,
        nombre: variante.nombre,
        opciones: []
    });

    var html = `${variante.nombre}
        <div class="form-check form-switch" style="margin-left: 12px;" id="lista-variante-producto-content_${variante.id}">
        </div>`;

    var item = document.createElement('li');
    item.setAttribute("id", "lista-variante-producto_"+variante.id);
    item.setAttribute("class", "list-group-item d-flex justify-content-between align-items-center lista-variante-producto active");
    item.onclick = function(){
        activeVarianteContenedor(variante.id);
    };
    item.innerHTML = [
        html
    ].join('');
    document.getElementById('variante_producto_contenedor').insertBefore(item, null);

    setTimeout(function(){
        var checkbox = document.createElement('input');
        checkbox.setAttribute("id", "inventariofamilia_"+variante.id);
        checkbox.setAttribute("class", "form-check-input inventario_familia");
        checkbox.setAttribute("type", "checkbox");
        checkbox.setAttribute("name", "inventario_familia");
        checkbox.setAttribute("style", "height: 20px;");
        checkbox.setAttribute("checked", "true");
        checkbox.onclick = function(){
            setStatusCheckVariante(variante.id);
        };
        checkbox.innerHTML = [
            html
        ].join('');

        document.getElementById('lista-variante-producto-content_'+variante.id).insertBefore(checkbox, null);
    },100);
}

function newItemVarianteOpciones (variante) {

    var item = document.createElement('div');
    item.setAttribute("id", "contenedor-opciones_"+variante.id);

    document.getElementById('variante_opcion_contenedor').insertBefore(item, null);

    if(variante.opciones.length > 0) {
        variante.opciones.forEach(opcion => {

            var html = `
            <label>${opcion.nombre}</label>
            <i class="fas fa-check" style="float: right; margin-top: 5px; margin-right: 5px;"></i>`;

            var item = document.createElement('div');
            item.setAttribute("id", "item-variante-opcion_"+opcion.id);
            item.setAttribute("class", "item-variante-opcion");
            item.setAttribute("style", "margin-top: 10px;");
            item.onclick = function(){
                setStatusCheckOpcion(opcion, variante.id);
            };
            item.innerHTML = [
                html
            ].join('');
            document.getElementById('contenedor-opciones_'+variante.id).insertBefore(item, null);
        });
    }
}

function getVarianteById (idVariante) {
    var data = false;

    if(nuevoProducto.variantes.length > 0) {
        Object.values(nuevoProducto.variantes).forEach(variante => {
            if (variante.id == idVariante) {
                data = variante;
            }
        });
    }
    return data;
}

function getBodegaProducto (idBodega) {
    var data = false;

    if (nuevoProducto.inventarios.length > 0) {
        Object.values(nuevoProducto.inventarios).forEach(inventario => {
            if (inventario.id == idBodega) {
                data = inventario;
            }
        });
    }

    return data;
}

function getBodegaProductoVarianteById (idBodega) {
    var data = false;
    var producto = nuevoProducto.productos_variantes[idProductoBodegaSelected];

    if (producto.inventarios.length > 0) {
        Object.values(producto.inventarios).forEach(inventario => {
            if (inventario.id == idBodega) {
                data = inventario;
            }
        });
    }
    return data;
}

function activeVarianteContenedor (idActiveVariante) {
    $('#nombre-variante').select();
    idVarianteSelected = idActiveVariante;

    Object.values(nuevoProducto.variantes).forEach(variante => {
        $('#lista-variante-producto_'+variante.id).removeClass("active");
        $('#contenedor-opciones_'+variante.id).hide();
    });

    $('#contenedor-opciones_'+idActiveVariante).show();
    $('#lista-variante-producto_'+idActiveVariante).addClass("active");
}

function setStatusCheckVariante (idVariante) {
    var variante = nuevoProducto.variantes;
    for (let index = 0; index < variante.length; index++) {
        let dataVariante = variante[index];
        if(dataVariante.id == idVariante) {
            var nuevoEstado = !nuevoProducto.variantes[index].estado;
            nuevoProducto.variantes[index].estado = nuevoEstado;
            var opciones = nuevoProducto.variantes[index].opciones;
            for (let ind = 0; ind < opciones.length; ind++) {
                let dataOpcion = opciones[ind];
                dataOpcion.estado = nuevoEstado;
                if(nuevoEstado) {
                    $('#item-variante-opcion_'+dataOpcion.id).addClass('item-variante-opcion-active');
                } else {
                    $('#item-variante-opcion_'+dataOpcion.id).removeClass('item-variante-opcion-active');
                }
            }
        }
    }
}

function setStatusCheckOpcion (opcion, idVariante) {
    var variante = nuevoProducto.variantes;
    for (let index = 0; index < variante.length; index++) {
        let dataVariante = variante[index];
        if(dataVariante.id == idVariante) {
            if(dataVariante.opciones.length > 0) {
                var opciones = dataVariante.opciones;
                var create = true;
                for (let ind = 0; ind < opciones.length; ind++) {
                    let dataOpcion = opciones[ind];
                    if(dataOpcion.id == opcion.id) {
                        dataOpcion.estado = !dataOpcion.estado;
                        create = false;
                        if(dataOpcion.estado) {
                            $('#item-variante-opcion_'+opcion.id).addClass('item-variante-opcion-active');
                        } else {
                            $('#item-variante-opcion_'+opcion.id).removeClass('item-variante-opcion-active');
                        }
                    }
                }
                if(create) {
                    $('#item-variante-opcion_'+opcion.id).addClass('item-variante-opcion-active');
                    nuevoProducto.variantes[index].opciones.push({
                        id: opcion.id,
                        nombre: opcion.nombre,
                        estado: true
                    });
                }
            } else {
                $('#item-variante-opcion_'+opcion.id).addClass('item-variante-opcion-active');
                nuevoProducto.variantes[index].opciones.push({
                    id: opcion.id,
                    nombre: opcion.nombre,
                    estado: true
                });
            }
        }
    }
}

function addVarianteItems () {
    var variantes = getVariantesActivas();

    Object.values(variantes).forEach(variante => {
        var html = `
        <button type="button" class="btn btn btn-outline-dark">
            ${variante.nombre} <span class="badge bg-info">&nbsp;${variante.opciones.length}</span>
        </button>
        `;

        var item = document.createElement('div');
        item.setAttribute("id", "variante-item-id_"+variante.id);
        item.setAttribute("class", "col");
        item.setAttribute("style", "text-align-last: center;");
        item.onclick = function(){
            agregarVarianteProducto();
        };
        item.innerHTML = [
            html
        ].join('');
        document.getElementById('variantes-contenedor').insertBefore(item, null);
    });

}

function addProductosVarianteItems () {
    generarVariantesProductos();
    var idProducto = 0;
    var productosVariantes = nuevoProducto.productos_variantes;

    productosVariantes.forEach(producto => {
        productos_varaibles_table.row.add({
            id: idProducto,
            codigo: producto.codigo,
            inventario: producto.inventario,
            inventarios: producto.inventarios,
            precio: producto.precio,
            precio_inicial: producto.precio_inicial,
            precio_maximo: producto.precio_maximo,
            variantes: producto.variantes,
        }).draw(false);
        idProducto++;
    });
    
}

function actualizarPrecio (id) {
    var value = $('#prodvari-precio_'+id).val();
    nuevoProducto.productos_variantes[id].precio = value;
}

function actualizarPrecioMaximo (id) {
    var value = $('#prodvari-preciomaximo_'+id).val();
    nuevoProducto.productos_variantes[id].precio_maximo = value;
}

function actualizarPrecioInicial (id) {
    var value = $('#prodvari-precioinicial_'+id).val();
    nuevoProducto.productos_variantes[id].precio_inicial = value;
}

function actualizarCodigo (id) {
    var value = $('#prodvari-codigo_'+id).val();
    nuevoProducto.productos_variantes[id].codigo = value;
}

function addNombreProducto () {
    var value = $('#nombre_producto').val();
    nuevoProducto.nombre = value;
}

function addCodigoProducto () {
    var value = $('#codigo_producto').val();
    nuevoProducto.codigo = value;
}

$("#id_familia_producto").on('change', function(e) {
    var data = $(this).select2('data')[0];
    nuevoProducto.id_familia = parseInt(data.id);
});

function addPrecioProducto () {
    var value = $('#precio_producto').val();
    nuevoProducto.precio = value;
}

function addPrecioMaximoProducto () {
    var value = $('#precio_maximo').val();
    nuevoProducto.precio_maximo = value;
}

function addPrecioInicialProducto () {
    var value = $('#precio_inicial').val();
    nuevoProducto.precio_inicial = value;
}