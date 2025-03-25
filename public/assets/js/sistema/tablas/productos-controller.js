let searchTimeoutProductos;
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

var ivaIncluidoProductos = true;

var nuevoProducto = {
    imagen: '',
    nombre: '',
    codigo: '',
    id_familia: null,
    precio: 0,
    tipo_producto: 0,
    precio_minimo: 0,
    precio_inicial: 0,
    porcentaje_utilidad: 0,
    valor_utilidad: 0,
    inventarios: [],
    variante: false,
    variantes: [],
    productos_variantes: []
}

function productosInit() {

    $('#cantidad_bodega_producto').val(0);

    productos_table = $('#productoTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'producto',
            data: function ( d ) {
                d.search = $("#searchInputProductos").val()
            }
        },
        columns: [
            // {"data":'id'},
            {"data":'codigo'},
            {"data": function (row, type, set){
                if (row.imagen) {
                    return `<img
                    style="height: 35px; border-radius: 10%;"
                    src="${bucketUrl}${row.imagen}"
                    alt="${row.nombre}" />`;
                }
                return '';
            }, className: 'dt-body-center'},
            {"data": function (row, type, set){
                return row.nombre;
            }},
            {"data": function (row, type, set){  
                if (row.tipo_producto == 0) {
                    if (row.id_padre) return '<span class="badge rounded-pill bg-light text-dark">VARIANTE</span>';
                    return '<span class="badge rounded-pill bg-dark">PRODUCTO</span>';
                }
                if (row.tipo_producto == 1) {
                    return '<span class="badge rounded-pill bg-success">SERVICIO</span>';
                }
                if (row.tipo_producto == 2) {
                    return '<span class="badge rounded-pill bg-primary">COMBO</span>';
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.familia) {
                    return row.familia.nombre
                }
                return '<span class="badge rounded-pill bg-danger">SIN FAMILIA!</span>';
            }},
            {"data": function (row, type, set){  
                var inventarios = row.inventarios;
                var totalUnidades = 0
                if (!row.id_familia) {
                    return '<span class="badge rounded-pill bg-danger">SIN FAMILIA!</span>';
                }
                if (row.id_familia && row.familia.inventario && inventarios.length > 0 && row.tipo_producto != 1) {
                    inventarios.forEach(inventario => {
                        totalUnidades+= parseInt(inventario.cantidad);
                    });
                    if (totalUnidades > 0) {
                        return totalUnidades;
                    } else {
                        return '<span class="badge rounded-pill bg-danger">Sin unidades</span>';
                    }
                }
                return '';
            }, className: 'dt-body-right'},
            {"data": "precio_inicial", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "precio", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                return  parseFloat(row.porcentaje_utilidad).toFixed(2)+ '%';
            }, className: 'dt-body-right'},
            {"data": "valor_utilidad", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                var inventarios = row.inventarios
                if (!row.id_familia) {
                    return '<span class="badge rounded-pill bg-danger">SIN FAMILIA!</span>';
                }
                if (row.familia.inventario && inventarios.length > 0) {
                    var html = ``;
                    inventarios.forEach(inventario => {
                        html+= `<span class="badge bg-light text-dark">${inventario.bodega.nombre} / ${inventario.cantidad} </span>&nbsp;`;
                    });
                    return html;
                }

                return '';
            }},
            {"data": function (row, type, set){  
                var inventarios = row.inventarios;
                var totalUnidades = 0
                if (!row.id_familia) {
                    return '';
                }
                if (row.familia.inventario && inventarios.length > 0 && row.tipo_producto != 1) {
                    inventarios.forEach(inventario => {
                        totalUnidades+= parseInt(inventario.cantidad);
                    });
                    return totalUnidades * row.precio_inicial;
                    
                }
                return '';
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                var inventarios = row.inventarios;
                var totalUnidades = 0
                if (!row.id_familia) {
                    return '';
                }
                if (row.familia.inventario && inventarios.length > 0 && row.tipo_producto != 1) {
                    inventarios.forEach(inventario => {
                        totalUnidades+= parseInt(inventario.cantidad);
                    });
                    return totalUnidades * row.precio;
                    
                }
                return '';
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    if (editarProductos) html+= '<span id="editproducto_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-producto" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (eliminarProductos) html+= '<span id="deleteproducto_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-producto" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
    
        ]
    });

    let column = productos_table.column(9);
    
    if (!editarProductos && !eliminarProductos) column.visible(false);
    else column.visible(true);

    if (productos_table) {
        productos_table.on('click', '.edit-producto', function() {

            var id = this.id.split('_')[1];
            var dataProducto = getDataById(id, productos_table);
            
            nuevoProducto = {
                imagen: '',
                nombre: dataProducto.nombre,
                codigo: dataProducto.codigo,
                id_familia: dataProducto.familia.id,
                precio: stringToNumberFloat(dataProducto.precio),
                tipo_producto: dataProducto.tipo_producto,
                precio_minimo: stringToNumberFloat(dataProducto.precio_minimo),
                precio_inicial: stringToNumberFloat(dataProducto.precio_inicial),
                porcentaje_utilidad: stringToNumberFloat(dataProducto.porcentaje_utilidad),
                valor_utilidad: stringToNumberFloat(dataProducto.valor_utilidad),
                inventarios: asignarDatosInventario(dataProducto),
                variante: isVariante(dataProducto),
                variantes: asignarDatosVariantes(dataProducto),
                productos_variantes: asingnarDatosProductosVariantes(dataProducto)
            }

            if(dataProducto.imagen) {
                $('#new_produc_img').attr('src', bucketUrl+dataProducto.imagen);
                $('#new_produc_img').show();
                $('#default_produc_img').hide();
            } else {
                $('#new_produc_img').attr('src', '');
                $('#new_produc_img').hide();
                $('#default_produc_img').show();
            }

            $("#botton-agregar-bodega").hide();
            $("#searchInputProductos").hide();
            $('#producto-inventario').hide();
            $("#table-products-view").hide();
            $("#totales-products-view").hide();
            $('#btn-modal-variantes').show();
            $("#add-products-view").show();
            $("#saveEditProducto").show();
            $("#saveNewProducto").hide();
            $("#cancelProducto").show();
            $("#createProducto").hide();
            $("#reloadProducto").hide();

            $('#id_producto_edit').val(dataProducto.id);
            $("#nombre_producto").val(dataProducto.nombre);
            $("#codigo_producto").val(dataProducto.codigo);
            $("#precio_producto").val(stringToNumberFloat(dataProducto.precio));
            $("#precio_minimo").val(stringToNumberFloat(dataProducto.precio_minimo));
            $("#precio_inicial").val(stringToNumberFloat(dataProducto.precio_inicial));
            $("#porcentaje_utilidad").val(stringToNumberFloat(dataProducto.porcentaje_utilidad));
            $("#valor_utilidad").val(stringToNumberFloat(dataProducto.valor_utilidad));

            document.getElementById("id_bodega_producto").disabled = true;
            document.getElementById("producto_variantes1").disabled = false;
            document.getElementById("producto_variantes2").disabled = false;
            document.getElementById("tipo_producto_producto").disabled = false;
            document.getElementById("tipo_producto_servicio").disabled = false;

            if (dataProducto.id_padre && tipo_producto == 0) {
                document.getElementById('producto_variantes1').click();
            }

            if (dataProducto.hijos.length > 0) {
                document.getElementById('producto_variantes2').click();
                $('#contenedor-variantes-generales').show();
                addProductosVarianteItems(false);
            }

            if (dataProducto.id_padre || dataProducto.hijos.length > 0) {
                document.getElementById("producto_variantes2").disabled = true;
                document.getElementById("producto_variantes1").disabled = true;
            }

            var dataFamilia = {
                id: dataProducto.familia.id,
                text: dataProducto.familia.codigo+' - '+dataProducto.familia.nombre
            };
            var newOption = new Option(dataFamilia.text, dataFamilia.id, false, false);
            $comboFamilia.append(newOption).trigger('change');
            $comboFamilia.val(dataFamilia.id).trigger('change');

            if (dataProducto.tipo_producto == 0) {
                document.getElementById('tipo_producto_producto').click();
            }
            else if (dataProducto.tipo_producto == 1) {
                document.getElementById('tipo_producto_servicio').click();
            }
            else if (dataProducto.tipo_producto == 2) {
                document.getElementById('tipo_producto_combo').click();
            }

            if (dataProducto.variantes.length > 0) {
                $('#btn-modal-variantes').hide();
                addVarianteItems();
            }

            if (dataProducto.id_padre || dataProducto.hijos.length > 0 || dataProducto.utilizado_captura) {
                document.getElementById("tipo_producto_producto").disabled = true;
                document.getElementById("tipo_producto_servicio").disabled = true;
            }

            if (dataProducto.familia && dataProducto.familia.cuenta_venta_iva && dataProducto.familia.cuenta_venta_iva.impuesto) {
                var porcentajeIva = dataProducto.familia.cuenta_venta_iva.impuesto.porcentaje;
                $('#input-iva-porcentaje').show();
                $('#input-iva-valor').show();
                $('#porcentaje_iva').val(porcentajeIva);
                var valorIva = stringToNumberFloat(dataProducto.precio) * (stringToNumberFloat(porcentajeIva) / 100);
                if(ivaIncluidoProductos) {//CALCULAR IVA INCLUIDO
                    valorIva = stringToNumberFloat(dataProducto.precio) - (stringToNumberFloat(dataProducto.precio) / (1 + (stringToNumberFloat(porcentajeIva) / 100)));
                }
                $('#valor_iva').val(valorIva);
            } else {
                $('#input-iva-porcentaje').hide();
                $('#input-iva-valor').hide();
            }

            if (dataProducto.familia && dataProducto.familia.cuenta_venta_impuestos && dataProducto.familia.cuenta_venta_impuestos.impuesto) {
                var porcentajeImpuestos = dataProducto.familia.cuenta_venta_impuestos.impuesto.porcentaje;
                $('#input-impuestos-porcentaje').show();
                $('#input-impuestos-valor').show();
                $('#text_otros_impuestos_valor').text('Valor '+dataProducto.familia.cuenta_venta_impuestos.nombre);
                $('#text_otros_impuestos_porcentaje').text('Porcentaje '+dataProducto.familia.cuenta_venta_impuestos.nombre);

                $('#porcentaje_otros_impuestos').val(porcentajeIva);
                var valorImpuestos = stringToNumberFloat(dataProducto.precio) * (stringToNumberFloat(porcentajeImpuestos) / 100);
                if(ivaIncluidoProductos) {//CALCULAR IVA INCLUIDO
                    valorImpuestos = stringToNumberFloat(dataProducto.precio) - (stringToNumberFloat(dataProducto.precio) / (1 + (stringToNumberFloat(porcentajeImpuestos) / 100)));
                }
                $('#valor_otros_impuestos').val(valorImpuestos);
            } else {
                $('#input-impuestos-porcentaje').hide();
                $('#input-impuestos-valor').hide();
            }            

            if (dataProducto.familia && dataProducto.familia.inventario && dataProducto.utilizado_captura == 0) {
                $('#producto-inventario').show();
                generarBodegas();
            }

            $('.dtfh-floatingparent').hide();
        });

        productos_table.on('dblclick', 'tr', function () {
            var data = productos_table.row(this).data();
            if (data) {
                document.getElementById("editproducto_"+data.id).click();
            }
        });

        productos_table.on('click', '.drop-producto', function() {
            var id = this.id.split('_')[1];
            var trProducto = $(this).closest('tr');
            var dataProducto = getDataById(id, productos_table);
            
            Swal.fire({
                title: 'Eliminar Producto: '+dataProducto.nombre+'?',
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
                    $.ajax({
                        url: base_url + 'producto',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            productos_table.row(trProducto).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Producto eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
    }

    productos_varaibles_table = $('#productosVariantesTable').DataTable({
        dom: 'Brtip',
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
            {//CODIGO
                "data": function (row, type, set, col){
                    return `<input type="text" class="form-control form-control-sm" onfocusout="actualizarCodigo(${row.id})" id="prodvari-codigo_${row.id}" value="${row.codigo}">`
                }
            },
            {//COSTO COMPRA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" onfocusout="actualizarPrecioInicial(${row.id})" id="prodvari-precioinicial_${row.id}" value="${row.precio_inicial}">`
                }
            },
            {//VALOR VENTA
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" onfocusout="actualizarPrecio(${row.id})" id="prodvari-precio_${row.id}" value="${row.precio}">`
                }
            },
            {//PRECIO MINIMO
                "data": function (row, type, set, col){
                    return `<input type="number" class="form-control form-control-sm" onfocusout="actualizarPrecioMaximo(${row.id})" id="prodvari-preciomaximo_${row.id}" value="${row.precio_minimo}">`
                }
            },
            {
                "data": function (row, type, set){
                    var html = '<span class="badge bg-light text-dark">Ninguna</span>';
                    var bodegas = row.inventarios;
                    
                    if (bodegas.length == 1) {
                        html = `<span class="badge bg-light text-dark">${bodegas[0].nombre} / ${bodegas[0].cantidad} </span>`;
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
                    var idProductoPadre = $('#id_producto_edit').val();
                    html+= '<span id="bodegaproducvari_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info bodega-productovariante" style="margin-bottom: 0rem !important; min-width: 50px;">Bodegas</span>&nbsp;';
                    if (!idProductoPadre || row.edit) {
                        html+= '<span id="deleteproducvari_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-productovariante" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    }
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

    $("#table-products-view").show();
    $("#totales-products-view").show();
    $("#add-products-view").hide();

    $('.water').hide();
    productos_table.ajax.reload(function(res) {
        showTotalsProductos(res);
    })

    $("#searchInputProductos").on("input", function () {
        clearTimeout(searchTimeoutProductos);
        searchTimeoutProductos = setTimeout(function () {
            productos_table.ajax.reload();
        }, 300);
    });
}

function asignarDatosInventario (dataProducto) {
    var data = [];

    if (dataProducto.inventarios.length > 0) {
        dataProducto.inventarios.forEach(inventario => {
            data.push({
                id: inventario.bodega.id,
                codigo: inventario.bodega.codigo,
                nombre: inventario.bodega.nombre,
                ubicacion: inventario.bodega.ubicacion,
                cantidad: parseInt(inventario.cantidad)
            });
        });
    }
    return data;
}

function asignarDatosVariantes (dataProducto) {
    var dataProductoVariante = [];
    var variantes = dataProducto.variantes;
    if (dataProducto.variante == 1 && !dataProducto.id_padre) {
        variantes.forEach(variante => {
            if (dataProductoVariante.length > 0) {
                var crear = true;
                for (let index = 0; index < dataProductoVariante.length; index++) {
                    const dataVariante = dataProductoVariante[index];
                    if (dataVariante.id == variante.variante.id) {
                        crear = false;
                        dataProductoVariante[index].opciones.push({
                            id: variante.opcion.id,
                            estado: true,
                            nombre: variante.opcion.nombre
                        });
                    }
                }
                if (crear) {
                    dataProductoVariante.push({
                        id: variante.variante.id,
                        estado: true,
                        nombre: variante.variante.nombre,
                        opciones: [{
                            id: variante.opcion.id,
                            estado: true,
                            nombre: variante.opcion.nombre
                        }]
                    });
                }
            } else {
                dataProductoVariante.push({
                    id: variante.variante.id,
                    estado: true,
                    nombre: variante.variante.nombre,
                    opciones: [{
                        id: variante.opcion.id,
                        estado: true,
                        nombre: variante.opcion.nombre
                    }]
                });
            }
        });
    }

    return dataProductoVariante;
}

function asingnarDatosProductosVariantes (dataProducto) {
    var data = [];

    if (dataProducto.hijos.length > 0) {
        dataProducto.hijos.forEach(productoHijo => {
            data.push({
                id: productoHijo.id,
                codigo: productoHijo.codigo,
                estado: true,
                inventario: productoHijo.inventarios.length > 0 ? true : false,
                inventarios: asignarDatosInventario(productoHijo),
                precio: stringToNumberFloat(productoHijo.precio),
                precio_inicial: stringToNumberFloat(productoHijo.precio_inicial),
                precio_minimo: stringToNumberFloat(productoHijo.precio_minimo),
                variantes: asignarOpciones(productoHijo),
            });
        });
    }

    return data;
}

function asignarOpciones (productoHijo) {
    var data = [];
    var variantes = productoHijo.variantes;
    
    variantes.forEach(variante => {
        data.push({
            id: variante.opcion.id,
            estado: true,
            nombre: variante.opcion.nombre
        });
    });

    return data;
}

function isVariante (dataProducto) {
    if (dataProducto.variante == 1 && !dataProducto.id_padre) return true;
    return false;
}

function showBodebasVariantes (idProducto) {
    $('#productos_bodegas_contenedor').empty();
    var producto = nuevoProducto.productos_variantes[idProducto];

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
    
    if (primeraBodegas.length == 1) addBodegaToProduct(primeraBodegas[0], false);
    else addBodegaToProduct(primeraBodegas[0]);
    
    $("#botton-agregar-bodega").show();
    $('#input-iva-porcentaje').hide();
    $("#searchInputProductos").hide();
    $("#table-products-view").hide();
    $("#totales-products-view").hide();
    $('#default_produc_img').show();
    $("#add-products-view").show();
    $('#input-iva-valor').hide();
    $("#saveNewProducto").show();
    $("#cancelProducto").show();
    $("#createProducto").hide();
    $("#reloadProducto").hide();

    $('#new_produc_img').attr('src', '');
    $('#new_produc_img').hide();

    document.getElementById("id_bodega_producto").disabled = false;

    $("#titulo-view").text('Agregar producto');
});

$(document).on('click', '#reloadProducto', function () {
    productos_table.ajax.reload(function(res) {
        showTotalsProductos(res);
    });
});

$(document).on('click', '#saveNewProducto', function () {
    var form = document.querySelector('#newProductoForm');

    $('#valor_utilidad').removeClass("is-invalid");
    $('#precio_producto').removeClass("is-invalid");

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    if (!$('#valor_utilidad').val()) {
        $('#valor_utilidad').addClass("is-invalid");
        return;
    }

    if (!$('#precio_producto').val()) {
        $('#precio_producto').addClass("is-invalid");
        return;
    }

    $('#saveNewProducto').hide();
    $('#cancelProducto').hide();
    $('#saveNewProductoLoading').show();
    
    nuevoProducto.variantes = getVariantesActivas();
    nuevoProducto.id_familia = parseInt($('#id_familia_producto').val());

    $.ajax({
        url: base_url + 'producto',
        method: 'POST',
        data: JSON.stringify(nuevoProducto),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $('#saveNewProducto').show();
            $('#cancelProducto').show();
            $('#saveNewProductoLoading').hide();
            productos_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Producto creado con exito!', true);
            document.getElementById('cancelProducto').click();
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
        $('#saveNewProducto').show();
        $('#cancelProducto').show();
        $('#saveNewProductoLoading').hide();
        agregarToast('error', 'Carga errada', errorsMsg);
    });

    
});

$(document).on('click', '#saveEditProducto', function () {
    var form = document.querySelector('#newProductoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $('#saveEditProducto').hide();
    $('#cancelProducto').hide();
    $('#saveNewProductoLoading').show();

    nuevoProducto.id = parseInt($('#id_producto_edit').val());
    nuevoProducto.id_familia = parseInt($('#id_familia_producto').val());
    
    $.ajax({
        url: base_url + 'producto',
        method: 'PUT',
        data: JSON.stringify(nuevoProducto),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $('#saveEditProducto').show();
            $('#cancelProducto').show();
            $('#saveNewProductoLoading').hide();
            var currentPage = productos_table.page(); // Guarda la página actual

            productos_table.ajax.reload(function(res) {
                showTotalsProductos(res);
                productos_table.page(currentPage).draw(false); // Restaura la página actual
            }, false);
            agregarToast('exito', 'Actualización exitosa', 'Producto actualizado con exito!', true);
            document.getElementById('cancelProducto').click();
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
        $('#saveEditProducto').show();
        $('#cancelProducto').show();
        $('#saveNewProductoLoading').hide();
        agregarToast('error', 'Error al actualizar producto', errorsMsg);
    });
});


$(document).on('click', '#cancelProducto', function () {
    clearFormProductos();

    $("#table-products-view").show();
    $("#totales-products-view").show();
    $("#add-products-view").hide();
    $("#cancelProducto").hide();
    $("#saveEditProducto").hide();
    $("#saveNewProducto").hide();
    $("#createProducto").show();
    $("#reloadProducto").show();
    $('.dtfh-floatingparent').show();
    $("#searchInputProductos").show();
    $("#titulo-view").text('Productos');
});

function clearFormProductos() {
    
    $('#bodegas-contenedor').empty();
    $('#variantes-contenedor').empty();
    $('#productos_bodegas_contenedor').empty();
    $('#productos_bodegas_contenedor').empty();
    $('#variante_producto_contenedor').empty();
    $('#variante_opcion_contenedor').empty();
    $('#button-new-opcion').hide();
    
    $("#text_tipo_combo").hide();
    $("#text_tipo_producto").hide();
    $("#text_tipo_servicio").hide();
    $('#contenedor-variantes-generales').hide();

    $('#id_familia_producto').val(0).change();
    $('#porcentaje_utilidad').val(0);
    $('#id_producto_edit').val('');
    $('#nombre_producto').val('');
    $('#codigo_producto').val('');
    $('#precio_producto').val(0);
    $('#valor_utilidad').val(0);
    $('#porcentaje_iva').val(0);
    $('#precio_inicial').val(0);
    $('#precio_minimo').val(0);
    $('#valor_iva').val(0);

    document.getElementById("producto_variantes1").disabled = false;
    document.getElementById("producto_variantes2").disabled = false;
    document.getElementById("tipo_producto_producto").disabled = false;
    document.getElementById("tipo_producto_servicio").disabled = false;

    setCrearProducto();
    
    nuevoProducto = {
        imagen: '',
        tipo_producto: 0,
        nombre: '',
        codigo: '',
        id_familia: null,
        precio: 0,
        tipo_producto: 0,
        precio_minimo: 0,
        precio_inicial: 0,
        inventarios: [],
        variante: false,
        variantes: [],
        productos_variantes: []
    }
    productos_varaibles_table.clear([]).draw();
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
    $('#producto-inventario').hide();
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
    var dataFamilia = $("#id_familia_producto").select2('data');
    $('#producto-inventario').hide();
    if(!$("input[type='radio']#producto_variantes1").is(':checked')){
        nuevoProducto.variante = true;
        dataFamilia = [];
        $('#producto-variantes').show();
    } else {

        nuevoProducto.variante = false;
        $('#producto-variantes').hide();
    }
    if (dataFamilia.length > 0) {
        if (dataFamilia[0].inventario) {
            $('#producto-inventario').show();
            productos_varaibles_table.column(5).visible(true);
        }
        else $('#producto-inventario').hide();
    } else {
        productos_varaibles_table.column(5).visible(false);
    }
});

function addBodegaToProduct (bodega, deleteButton = true) {
    var cantidad = parseInt($('#cantidad_bodega_producto').val());
    var idProductoPadre = $('#id_producto_edit').val();

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
        edit: idProductoPadre ? true : false
    });
}

function generarBodegas () {

    var bodegas = nuevoProducto.inventarios;

    if (bodegas.length > 0) {
        bodegas.forEach(bodega => {
            var html = `
                <div class="item-bodega" style="cursor: pointer;">
                    <i class="fas fa-box-open" style="font-size: 20px; color: #596cff;"></i>
                </div>
        
                <div style="text-align: -webkit-center; cursor: pointer;">
                    <h6>${bodega.codigo} - ${bodega.nombre}</h6>
                    <label id="bodega-candidad_${bodega.id}">Cantidad: ${bodega.cantidad}</label>
                </div>
        
                <div>
                    <div style="padding: 10px; cursor: pointer" onclick="editarBodega(${bodega.id})">
                        <i class="fas fa-edit" style="font-size: 15px; color: lawngreen;"></i>
                    </div>
                </div>
            `;
        
            var item = document.createElement('li');
            item.setAttribute("id", "bodega-producto_"+bodega.id);
            item.setAttribute("class", "list-group-item d-flex justify-content-between align-items-center animate__animated animate__fadeIn");
            item.innerHTML = [
                html
            ].join('');
            document.getElementById('bodegas-contenedor').insertBefore(item, null);
        });
    }
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
    if ($('#id_producto_edit').val()) generarVariantesProductos();
    addProductosVarianteItems();
});

function generarVariantesProductos () {
    
    var variantes = getVariantesActivas();

    var cacheNuevosProductos = [];

    for (let indexVariante = 0; indexVariante < variantes.length; indexVariante++) {

        let variante = variantes[indexVariante];
        let idProductoPadre = $('#id_producto_edit').val();

        if (!variante.estado) continue;

        if (indexVariante > 1) {
            cacheNuevosProductos = [];
            nuevoProducto.productos_variantes.forEach(productosActuales => {
                cacheNuevosProductos = cacheNuevosProductos.concat({
                    precio: productosActuales.precio,
                    codigo: productosActuales.codigo,
                    precio_minimo: productosActuales.precio_minimo,
                    precio_inicial: productosActuales.precio_inicial,
                    inventario: productosActuales.inventario,
                    estado: true,
                    inventarios: inventarioVariantes(),
                    variantesc: productosActuales.variantes,
                    edit: idProductoPadre ? true : false
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
                    precio_minimo: nuevoProducto.precio_minimo,
                    precio_inicial: nuevoProducto.precio_inicial,
                    inventario: false,
                    estado: true,
                    inventarios: inventarioVariantes(),
                    variantesc: [opcion],
                    edit: idProductoPadre ? true : false
                });

                nuevoProducto.productos_variantes = nuevoProducto.productos_variantes.concat({
                    precio: nuevoProducto.precio,
                    codigo: nuevoProducto.codigo,
                    precio_minimo: nuevoProducto.precio_minimo,
                    precio_inicial: nuevoProducto.precio_inicial,
                    inventario: false,
                    estado: true,
                    inventarios: inventarioVariantes(),
                    variantes: [opcion],
                    edit: idProductoPadre ? true : false
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
                        precio_minimo: nuevoProducto.precio_minimo,
                        precio_inicial: nuevoProducto.precio_inicial,
                        inventario: false,
                        estado: true,
                        inventarios: nuevoProducto.inventarios,
                        variantes: varianteCache,
                        edit: idProductoPadre ? true : false
                    };
                    newData.variantes = newData.variantes.concat(opcion);
                    nuevoProducto.productos_variantes = nuevoProducto.productos_variantes.concat(newData);
                });
            }
        }
    }
}

function inventarioVariantes() {
    return [{
        id: primeraBodegas[0].id,
        codigo: primeraBodegas[0].codigo,
        nombre: primeraBodegas[0].nombre,
        ubicacion: primeraBodegas[0].ubicacion,
        cantidad: 0,    
        edit: false
    }];
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
            inventario.edit = true;
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
    var idProductoPadre = $('#id_producto_edit').val();
    var disabled = idProductoPadre ? 'disabled' : '';
    if (bodega.edit == true) disabled = '';

    if (addProducto) {
        disabled = '';
        nuevoProducto.productos_variantes[idProductoBodegaSelected].inventarios.push({
            id: parseInt(bodega.id),
            nombre: bodega.nombre,
            codigo: bodega.codigo,
            ubicacion: bodega.ubicacion,
            cantidad: bodega.cantidad ? bodega.cantidad : 0,
            edit: idProductoPadre ? true : false
        });
    }

    var cantidad = bodega.cantidad ? bodega.cantidad : 0;

    var html = `
        <div style="padding: 5px; padding: 5px; border-top: solid 1px #dfdfdf; margin-left: 10px;"></div>
                                    
        <div class="form-group col-12 col-sm-6 col-md-6" >
            <label for="example-text-input" class="form-control-label">Bodega</label>
            <input type="text" class="form-control form-control-sm" id="bodega-producto-variante_${bodega.id}" value="${bodega.nombre}" disabled>
        </div>

        <div class="form-group col-12 col-sm-6 col-md-6" >
            <label for="example-text-input" class="form-control-label">Cantidad</label>
            <input type="number" class="form-control form-control-sm" id="cantidad-producto-variante_${bodega.id}" value="${cantidad}" onfocusout="actualizarCantidadBodega(${bodega.id})" ${disabled}>
        </div>
    `;
    var btnEliminar = `
        <div class="col-12 col-sm-12 col-md-12">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBodegaProductoVariente(${bodega.id})" style="width: 100%; margin-top: 5px; box-shadow: none; ">
                Eliminar
            </button>
        </div>
    `;

    if (bodega.edit == true) {
        html+= btnEliminar;
    } else if (!idProductoPadre){
        html+= btnEliminar;
    }

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

    actualizarRowBodega();
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
    var idProductoPadre = $('#id_producto_edit').val();
    var disabled = idProductoPadre ? 'disabled' : '';
    var variantes = getVariantesActivas();

    Object.values(variantes).forEach(variante => {
        var html = `
        <button type="button" class="btn btn btn-outline-dark" ${disabled}>
            ${variante.nombre} <span class="badge bg-info">&nbsp;${variante.opciones.length}</span>
        </button>
        `;

        var item = document.createElement('div');
        item.setAttribute("id", "variante-item-id_"+variante.id);
        item.setAttribute("class", "col");
        item.setAttribute("style", "text-align-last: center;");
        item.onclick = function(){
            if (disabled == 'disabled') agregarVarianteProducto();
        };
        item.innerHTML = [
            html
        ].join('');
        document.getElementById('variantes-contenedor').insertBefore(item, null);
    });

}

function addProductosVarianteItems (tomarPadre = true) {
    if (!$('#id_producto_edit').val()) {
        generarVariantesProductos();
    } 
    var idProducto = 0;
    var codigo = '';
    var productosVariantes = nuevoProducto.productos_variantes;
    var idProductoPadre = tomarPadre ? $('#id_producto_edit').val() : false;

    productosVariantes.forEach(producto => {
        codigo = idProducto < 10 ? '0'+(idProducto+1) : idProducto+1;
        producto.codigo = producto.codigo +'-'+ codigo;
        productos_varaibles_table.row.add({
            id: idProducto,
            codigo: producto.codigo,
            inventario: producto.inventario,
            inventarios: producto.inventarios,
            precio: producto.precio,
            precio_inicial: producto.precio_inicial,
            precio_minimo: producto.precio_minimo,
            variantes: producto.variantes,
            edit: idProductoPadre ? true : false 
        }).draw(false);
        idProducto++;
    });
    
}

function changeCostoCompra(event) {
    if(event.keyCode == 13) {
        calcularCostoCompra();
        setTimeout(function(){
            $('#porcentaje_utilidad').focus();
            $('#porcentaje_utilidad').select();
        },100);
    }
}

function calcularCostoCompra(focus = true) {
    var costoCompra = stringToNumberFloat($('#precio_inicial').val());
    var valorVenta = stringToNumberFloat($('#precio_producto').val());
    var valorUtilidad = stringToNumberFloat($('#valor_utilidad').val());
    
    if (valorVenta) {
        
        var porcentajeUtilidad = (valorUtilidad / costoCompra) * 100;
        if (!valorVenta && !costoCompra) porcentajeUtilidad = 0;
        else if (porcentajeUtilidad < 0) porcentajeUtilidad = 0;

        $('#precio_minimo').val(formatCurrencyValue(costoCompra));
        $('#porcentaje_utilidad').val(formatCurrencyValue(porcentajeUtilidad));
        $('#valor_utilidad').val(formatCurrencyValue(costoCompra * (porcentajeUtilidad / 100)));
        $('#precio_producto').val(formatCurrencyValue(valorVenta));
    } else {
        $('#porcentaje_utilidad').val(0);
        $('#precio_minimo').val(formatCurrencyValue(costoCompra));
        $('#precio_producto').val(formatCurrencyValue(costoCompra));
    }
}

function changeValorVenta(event) {
    if(event.keyCode == 13) {
        calcularPrecioProducto();
        setTimeout(function(){
            $('#precio_minimo').focus();
            $('#precio_minimo').select();
        },100);
    }
}

function calcularPrecioProducto() {
    var costoCompra = stringToNumberFloat($('#precio_inicial').val());
    var valorVenta = stringToNumberFloat($('#precio_producto').val());
    var porcentajeIva = stringToNumberFloat($('#porcentaje_iva').val());
    var valorUtilidad = stringToNumberFloat($('#valor_utilidad').val());
    var porcentajeImpuesto = stringToNumberFloat($('#porcentaje_otros_impuestos').val());

    if (valorVenta < costoCompra) {// VALOR VENTA NO PUEDE SER MENOR A COSTO DEL PRODUCTO
        $('#precio_producto').val(formatCurrencyValue(costoCompra));
        return;
    }

    if (costoCompra == 0) {//SI EL COSTO ES 0 LA UTILIDAD ES 100%
        $('#porcentaje_utilidad').val(100);
        $('#valor_utilidad').val(formatCurrencyValue(valorVenta));
    } else {//CALCULAR % DE UTILIDAD
        var porcentajeUtilidad = parseFloat(valorVenta - costoCompra) / costoCompra;
        if (valorUtilidad) porcentajeUtilidad = parseFloat(valorUtilidad / costoCompra);
        if (!valorUtilidad) valorUtilidad = costoCompra * porcentajeUtilidad;

        $('#porcentaje_utilidad').val(formatCurrencyValue(porcentajeUtilidad * 100));
        $('#valor_utilidad').val(formatCurrencyValue(valorUtilidad));
    }

    if (porcentajeIva) {
        var totalIva = valorVenta * (porcentajeIva / 100);
        if(ivaIncluidoProductos) {//CALCULAR IVA INCLUIDO
            totalIva = valorVenta - (valorVenta / (1 + (porcentajeIva / 100)));
        }
        $('#valor_iva').val(formatCurrencyValue(totalIva));
    }

    if (porcentajeImpuesto) {
        var totalImpuesto = valorVenta * (porcentajeImpuesto / 100);
        $('#valor_otros_impuestos').val(formatCurrencyValue(totalImpuesto));
    }

}

function changePrecioMinimo(event) {
    if(event.keyCode == 13) {
    }
}

function changePorcentajeUtilidad(event) {
    if(event.keyCode == 13) {
        calcularPorcentajeUtilidad();
        setTimeout(function(){
            $('#valor_utilidad').focus();
            $('#valor_utilidad').select();
        },100);
    }
}

function calcularPorcentajeUtilidad() {
    var costoCompra = stringToNumberFloat($('#precio_inicial').val());
    var valorVenta = stringToNumberFloat($('#precio_producto').val());
    var porcentajeIva = stringToNumberFloat($('#porcentaje_iva').val());
    var porcentajeUtilidad = stringToNumberFloat($('#porcentaje_utilidad').val());
    var porcentajeImpuesto = stringToNumberFloat($('#porcentaje_otros_impuestos').val());

    if (costoCompra == 0 && valorVenta > 0) {
        $('#porcentaje_utilidad').val(formatCurrencyValue(100));
        $('#valor_utilidad').val(formatCurrencyValue(valorVenta));
    } else {
        var valorUtilidad = costoCompra * (porcentajeUtilidad / 100);
        var precioProducto = costoCompra * ((porcentajeUtilidad / 100) + 1);
        var valorIva = precioProducto * (porcentajeIva / 100);
        var valorImpuesto = precioProducto * (porcentajeImpuesto / 100);
        // if(ivaIncluidoProductos) {//CALCULAR IVA INCLUIDO
        //     valorIva = valorVenta - (valorVenta / (1 + (porcentajeIva / 100)));
        // }
        $('#valor_iva').val(formatCurrencyValue(valorIva));
        $('#valor_utilidad').val(formatCurrencyValue(valorUtilidad));
        $('#precio_producto').val(formatCurrencyValue(precioProducto));
        $('#valor_otros_impuestos').val(formatCurrencyValue(valorImpuesto));
    }
    calcularValorUtilidad();
}

function changeValorUtilidad(event) {
    if(event.keyCode == 13) {
        calcularValorUtilidad();
        setTimeout(function(){
            $('#precio_producto').focus();
            $('#precio_producto').select();
        },100);
    }
}

function calcularValorUtilidad() {
    var costoCompra = stringToNumberFloat($('#precio_inicial').val());
    var valorProducto = stringToNumberFloat($('#precio_producto').val());
    var valorUtilidad = stringToNumberFloat($('#valor_utilidad').val());

    if (costoCompra == 0 && valorProducto > 0) {
        $('#porcentaje_utilidad').val(100);
        $('#valor_utilidad').val(valorProducto);

    } else if (valorUtilidad > 0) {
        var porcentajeIva = stringToNumberFloat($('#porcentaje_iva').val());
        var precioProducto = costoCompra + valorUtilidad;
        var valorIva = precioProducto * (porcentajeIva / 100);
        if(ivaIncluidoProductos) {//CALCULAR IVA INCLUIDO
            precioProducto+= valorIva;
        }
        $('#porcentaje_utilidad').val(formatCurrencyValue((valorUtilidad / costoCompra) * 100));
        $('#precio_producto').val(formatCurrencyValue(precioProducto));
        $('#valor_iva').val(formatCurrencyValue(valorIva));

    } else {
        var porcentajeIva = stringToNumberFloat($('#porcentaje_iva').val());
        var precioProducto = costoCompra + valorUtilidad;
        var valorIva = precioProducto * (porcentajeIva / 100);
        if(ivaIncluidoProductos) {//CALCULAR IVA INCLUIDO
            costoCompra+= valorIva;
        }

        $('#porcentaje_utilidad').val(formatCurrencyValue(0));
        $('#precio_producto').val(formatCurrencyValue(costoCompra));
        $('#valor_iva').val(formatCurrencyValue(valorIva));
    }
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            nuevoProducto.imagen = e.target.result;
            $('#new_produc_img').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_produc_img').hide();
        $('#new_produc_img').show();
    }
}

$('#productoTable').on('search.dt', function (res, data) {
    if (data.json) {
        var datos = data.json.totalesProductos;
        var total_utilidad = datos.total_precio - datos.total_costo;
        var porcentaje_utilidad = (total_utilidad / datos.total_costo) * 100;

        var countA = new CountUp('total_bodegas_producto', 0, datos.cantidad_productos);
            countA.start();

        var countB = new CountUp('total_productos_producto', 0, datos.total_productos);
            countB.start();

        var countC = new CountUp('total_costo_producto', 0, datos.total_costo);
            countC.start();

        var countD = new CountUp('total_precio_producto', 0, datos.total_precio);
            countD.start();

        var countE = new CountUp('total_utilidad_producto', 0, total_utilidad);
            countE.start();

        var countF = new CountUp('total_porcentaje_producto', 0, porcentaje_utilidad ? porcentaje_utilidad : 0);
            countF.start();            
    }
});

function actualizarPrecio (id) {
    var value = stringToNumberFloat($('#prodvari-precio_'+id).val());
    nuevoProducto.productos_variantes[id].precio = value;
}

function actualizarPrecioMaximo (id) {
    var value = stringToNumberFloat($('#prodvari-preciomaximo_'+id).val());
    nuevoProducto.productos_variantes[id].precio_minimo = value;
}

function actualizarPrecioInicial (id) {
    var value = stringToNumberFloat($('#prodvari-precioinicial_'+id).val());
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

function keyPressCodigoProducto(event) {
    if(event.keyCode == 13){
        setTimeout(function(){
            $('#nombre_producto').focus();
        },10);
    }
}

function keyPressNombreProducto(event) {
    if(event.keyCode == 13){
        setTimeout(function(){
            $('#id_familia_producto').focus();
            $('#id_familia_producto').select2('open');
        },10);
    }
}

function focusCodigoProducto() {
    $('#codigo_producto').select();
}

function focusNombreProducto() {
    $('#nombre_producto').select();
}

$('#id_familia_producto').on('select2:close', function(event) {
    var data = $(this).select2('data');

    if (data.length > 0) {
        var familia = data[0];
        if (familia.cuenta_venta_iva && familia.cuenta_venta_iva.impuesto) {
            $('#input-iva-porcentaje').show();
            $('#input-iva-valor').show();
            $('#porcentaje_iva').val(familia.cuenta_venta_iva.impuesto.porcentaje);
        } else {
            $('#input-iva-porcentaje').hide();
            $('#input-iva-valor').hide();
        }
        console.log('familia: ',familia);
        if (familia.cuenta_venta_impuestos && familia.cuenta_venta_impuestos.impuesto) {
            $('#input-impuestos-porcentaje').show();
            $('#input-impuestos-valor').show();
            $('#porcentaje_otros_impuestos').val(familia.cuenta_venta_impuestos.impuesto.porcentaje);
            $('#text_otros_impuestos_valor').text('Valor '+familia.cuenta_venta_impuestos.nombre);
            $('#text_otros_impuestos_porcentaje').text('Porcentaje '+familia.cuenta_venta_impuestos.nombre);
        } else {
            $('#input-impuestos-porcentaje').hide();
            $('#input-impuestos-valor').hide();
        }

        nuevoProducto.id_familia = parseInt(familia.id);
        if (familia.inventario) $('#producto-inventario').show();
        else $('#producto-inventario').hide();

        setTimeout(function(){
            $('#precio_inicial').focus();
        },10);
    }
});

function showTotalsProductos(res) {
    if (!res.success) return;

    $('#totales-products-view').show();
    var totales = res.totalesProductos;
    var total_utilidad = totales.total_precio - totales.total_costo;
    var porcentaje_utilidad = (total_utilidad / totales.total_costo) * 100;

    var countA = new CountUp('total_bodegas_producto', 0, totales.cantidad_productos);
        countA.start();

    var countB = new CountUp('total_productos_producto', 0, totales.total_productos);
        countB.start();

    var countC = new CountUp('total_costo_producto', 0, totales.total_costo);
        countC.start();

    var countD = new CountUp('total_precio_producto', 0, totales.total_precio);
        countD.start();

    var countE = new CountUp('total_utilidad_producto', 0, total_utilidad);
        countE.start();

    var countF = new CountUp('total_porcentaje_producto', 0, porcentaje_utilidad ? porcentaje_utilidad : 0);
        countF.start(); 
}

function addPrecioMinimoProducto () {
    actualizarDatosProducto();
}

function addPrecioProducto () {
    calcularPrecioProducto();
    actualizarDatosProducto();
}

function addPrecioInicialProducto () {
    actualizarDatosProducto();
    calcularCostoCompra();
}

function addPorcentajeUtilidadProducto () {
    calcularPorcentajeUtilidad();
    actualizarDatosProducto();
}

function addValorUtilidadProducto () {
    calcularValorUtilidad();
    actualizarDatosProducto();
}

function actualizarDatosProducto () {
    nuevoProducto.precio = stringToNumberFloat($('#precio_producto').val());
    nuevoProducto.precio_minimo = stringToNumberFloat($('#precio_minimo').val());
    nuevoProducto.precio_inicial = stringToNumberFloat($('#precio_inicial').val());
    nuevoProducto.porcentaje_utilidad = stringToNumberFloat($('#porcentaje_utilidad').val());
    nuevoProducto.valor_utilidad = stringToNumberFloat($('#valor_utilidad').val());
}