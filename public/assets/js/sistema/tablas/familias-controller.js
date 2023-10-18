var familias_table = null;
//CUENTAS
var $comboCuentaVenta = null;
var $comboCuentaVentaRetencion = null;
var $comboCuentaVentaDevolucion = null;
var $comboCuentaVentaIva = null;
var $comboCuentaVentaDescuento = null;
var $comboCuentaVentaDevolucionIva = null;

var $comboCuentaCompra = null;
var $comboCuentaCompraRetencion = null;
var $comboCuentaCompraDevolucion = null;
var $comboCuentaCompraIva = null;
var $comboCuentaCompraDescuento = null;
var $comboCuentaCompraDevolucionIva = null;

var $comboCuentaInventario = null;
var $comboCuentaCostos = null;

function familiasInit() {

    familias_table = $('#famimliaTable').DataTable({
        pageLength: 15,
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
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'familia',
        },
        columns: [
            {"data":'codigo'},
            {"data":'nombre'},
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_venta){
                        return row.cuenta_venta.cuenta + ' - ' + row.cuenta_venta.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_venta_retencion){
                        return row.cuenta_venta_retencion.cuenta + ' - ' + row.cuenta_venta_retencion.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_venta_devolucion){
                        return row.cuenta_venta_devolucion.cuenta + ' - ' + row.cuenta_venta_devolucion.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_venta_iva){
                        return row.cuenta_venta_iva.cuenta + ' - ' + row.cuenta_venta_iva.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_venta_descuento){
                        return row.cuenta_venta_descuento.cuenta + ' - ' + row.cuenta_venta_descuento.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_venta_devolucion_iva){
                        return row.cuenta_venta_devolucion_iva.cuenta + ' - ' + row.cuenta_venta_devolucion_iva.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_inventario){
                        return row.cuenta_inventario.cuenta + ' - ' + row.cuenta_inventario.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_costos){
                        return row.cuenta_costos.cuenta + ' - ' + row.cuenta_costos.nombre;
                    }
                    return '';
                }
            },


            {
                "data": function (row, type, set){
                    if(row.id_cuenta_compra){
                        return row.cuenta_compra.cuenta + ' - ' + row.cuenta_compra.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_compra_retencion){
                        return row.cuenta_compra_retencion.cuenta + ' - ' + row.cuenta_compra_retencion.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_compra_devolucion){
                        return row.cuenta_compra_devolucion.cuenta + ' - ' + row.cuenta_compra_devolucion.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_compra_iva){
                        return row.cuenta_compra_iva.cuenta + ' - ' + row.cuenta_compra_iva.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_compra_descuento){
                        return row.cuenta_compra_descuento.cuenta + ' - ' + row.cuenta_compra_descuento.nombre;
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.id_cuenta_compra_devolucion_iva){
                        return row.cuenta_compra_devolucion_iva.cuenta + ' - ' + row.cuenta_compra_devolucion_iva.nombre;
                    }
                    return '';
                }
            },
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
                    if (editarFamilias) html+= '<span id="editfamilias_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-familias" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                    if (eliminarFamilias) html+= '<span id="deletefamilias_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-familias" style="margin-bottom: 0rem !important">Eliminar</span>&nbsp';
                    if (crearFamilias) html+= '<span id="duplicatefamilias_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info duplicar-familias" style="margin-bottom: 0rem !important">Duplicar</span>';
                    return html;
                }
            }
        ]
    });

    let column = familias_table.column(18);
    
    if (!editarFamilias && !eliminarFamilias) column.visible(false);
    else column.visible(true);

    if(familias_table) {
        //EDITAR FAMILIAS
        familias_table.on('click', '.edit-familias', function() {

            clearFormFamilias();
            $("#textFamiliaCreate").hide();
            $("#textFamiliaUpdate").show();
            $("#textFamiliaDuplicate").hide();
            $("#saveFamiliaLoading").hide();
            $("#updateFamilia").show();
            $("#saveFamilia").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, familias_table);
    
            if(data.cuenta_venta){
                var dataCuenta = {
                    id: data.cuenta_venta.id,
                    text: data.cuenta_venta.cuenta + ' - ' + data.cuenta_venta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVenta.append(newOption).trigger('change');
                $comboCuentaVenta.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_retencion){
                var dataCuenta = {
                    id: data.cuenta_venta_retencion.id,
                    text: data.cuenta_venta_retencion.cuenta + ' - ' + data.cuenta_venta_retencion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaRetencion.append(newOption).trigger('change');
                $comboCuentaVentaRetencion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_devolucion){
                var dataCuenta = {
                    id: data.cuenta_venta_devolucion.id,
                    text: data.cuenta_venta_devolucion.cuenta + ' - ' + data.cuenta_venta_devolucion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaDevolucion.append(newOption).trigger('change');
                $comboCuentaVentaDevolucion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_iva){
                var dataCuenta = {
                    id: data.cuenta_venta_iva.id,
                    text: data.cuenta_venta_iva.cuenta + ' - ' + data.cuenta_venta_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaIva.append(newOption).trigger('change');
                $comboCuentaVentaIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_descuento){
                var dataCuenta = {
                    id: data.cuenta_venta_descuento.id,
                    text: data.cuenta_venta_descuento.cuenta + ' - ' + data.cuenta_venta_descuento.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaDescuento.append(newOption).trigger('change');
                $comboCuentaVentaDescuento.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_devolucion_iva){
                var dataCuenta = {
                    id: data.cuenta_venta_devolucion_iva.id,
                    text: data.cuenta_venta_devolucion_iva.cuenta + ' - ' + data.cuenta_venta_devolucion_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaDevolucionIva.append(newOption).trigger('change');
                $comboCuentaVentaDevolucionIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra){
                var dataCuenta = {
                    id: data.cuenta_compra.id,
                    text: data.cuenta_compra.cuenta + ' - ' + data.cuenta_compra.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompra.append(newOption).trigger('change');
                $comboCuentaCompra.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_retencion){
                var dataCuenta = {
                    id: data.cuenta_compra_retencion.id,
                    text: data.cuenta_compra_retencion.cuenta + ' - ' + data.cuenta_compra_retencion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraRetencion.append(newOption).trigger('change');
                $comboCuentaCompraRetencion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_devolucion){
                var dataCuenta = {
                    id: data.cuenta_compra_devolucion.id,
                    text: data.cuenta_compra_devolucion.cuenta + ' - ' + data.cuenta_compra_devolucion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraDevolucion.append(newOption).trigger('change');
                $comboCuentaCompraDevolucion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_iva){
                var dataCuenta = {
                    id: data.cuenta_compra_iva.id,
                    text: data.cuenta_compra_iva.cuenta + ' - ' + data.cuenta_compra_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraIva.append(newOption).trigger('change');
                $comboCuentaCompraIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_descuento){
                var dataCuenta = {
                    id: data.cuenta_compra_descuento.id,
                    text: data.cuenta_compra_descuento.cuenta + ' - ' + data.cuenta_compra_descuento.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraDescuento.append(newOption).trigger('change');
                $comboCuentaCompraDescuento.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_devolucion_iva){
                var dataCuenta = {
                    id: data.cuenta_compra_devolucion_iva.id,
                    text: data.cuenta_compra_devolucion_iva.cuenta + ' - ' + data.cuenta_compra_devolucion_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraDevolucionIva.append(newOption).trigger('change');
                $comboCuentaCompraDevolucionIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_inventario){
                var dataCuenta = {
                    id: data.cuenta_inventario.id,
                    text: data.cuenta_inventario.cuenta + ' - ' + data.cuenta_inventario.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaInventario.append(newOption).trigger('change');
                $comboCuentaInventario.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_costos){
                var dataCuenta = {
                    id: data.cuenta_costos.id,
                    text: data.cuenta_costos.cuenta + ' - ' + data.cuenta_costos.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCostos.append(newOption).trigger('change');
                $comboCuentaCostos.val(dataCuenta.id).trigger('change');
            }

            if (data.inventario) {
                $('#inventario_familia').prop('checked', true);
                showInventario(true);
            } else {
                $('#inventario_familia').prop('checked', false);
                showInventario(false);
            }

            $("#codigo_familia").val(data.codigo);
            $("#nombre_familia").val(data.nombre);
            $("#id_familia_up").val(data.id);

            $("#familiaFormModal").modal('show');

            setTimeout(function(){
                $('#codigo_familia').focus();
                $('#codigo_familia').select();
            },50);
        });
        //ELIMIAR FAMILIAS
        familias_table.on('click', '.drop-familias', function() {

            var trPlanCuenta = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, familias_table);

            Swal.fire({
                title: 'Eliminar familia: '+data.nombre+'?',
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
                        url: base_url + 'familia',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            familias_table.row(trPlanCuenta).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Familia eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        //DUPLICAR FAMILIAS
        familias_table.on('click', '.duplicar-familias', function() {

            clearFormFamilias();
            $("#textFamiliaCreate").hide();
            $("#textFamiliaUpdate").hide();
            $("#textFamiliaDuplicate").show();
            $("#saveFamiliaLoading").hide();
            $("#updateFamilia").hide();
            $("#saveFamilia").show();

            var id = this.id.split('_')[1];
            var data = getDataById(id, familias_table);
    
            if(data.cuenta_venta){
                var dataCuenta = {
                    id: data.cuenta_venta.id,
                    text: data.cuenta_venta.cuenta + ' - ' + data.cuenta_venta.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVenta.append(newOption).trigger('change');
                $comboCuentaVenta.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_retencion){
                var dataCuenta = {
                    id: data.cuenta_venta_retencion.id,
                    text: data.cuenta_venta_retencion.cuenta + ' - ' + data.cuenta_venta_retencion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaRetencion.append(newOption).trigger('change');
                $comboCuentaVentaRetencion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_devolucion){
                var dataCuenta = {
                    id: data.cuenta_venta_devolucion.id,
                    text: data.cuenta_venta_devolucion.cuenta + ' - ' + data.cuenta_venta_devolucion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaDevolucion.append(newOption).trigger('change');
                $comboCuentaVentaDevolucion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_iva){
                var dataCuenta = {
                    id: data.cuenta_venta_iva.id,
                    text: data.cuenta_venta_iva.cuenta + ' - ' + data.cuenta_venta_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaIva.append(newOption).trigger('change');
                $comboCuentaVentaIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_descuento){
                var dataCuenta = {
                    id: data.cuenta_venta_descuento.id,
                    text: data.cuenta_venta_descuento.cuenta + ' - ' + data.cuenta_venta_descuento.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaDescuento.append(newOption).trigger('change');
                $comboCuentaVentaDescuento.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_venta_devolucion_iva){
                var dataCuenta = {
                    id: data.cuenta_venta_devolucion_iva.id,
                    text: data.cuenta_venta_devolucion_iva.cuenta + ' - ' + data.cuenta_venta_devolucion_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaVentaDevolucionIva.append(newOption).trigger('change');
                $comboCuentaVentaDevolucionIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra){
                var dataCuenta = {
                    id: data.cuenta_compra.id,
                    text: data.cuenta_compra.cuenta + ' - ' + data.cuenta_compra.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompra.append(newOption).trigger('change');
                $comboCuentaCompra.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_retencion){
                var dataCuenta = {
                    id: data.cuenta_compra_retencion.id,
                    text: data.cuenta_compra_retencion.cuenta + ' - ' + data.cuenta_compra_retencion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraRetencion.append(newOption).trigger('change');
                $comboCuentaCompraRetencion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_devolucion){
                var dataCuenta = {
                    id: data.cuenta_compra_devolucion.id,
                    text: data.cuenta_compra_devolucion.cuenta + ' - ' + data.cuenta_compra_devolucion.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraDevolucion.append(newOption).trigger('change');
                $comboCuentaCompraDevolucion.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_iva){
                var dataCuenta = {
                    id: data.cuenta_compra_iva.id,
                    text: data.cuenta_compra_iva.cuenta + ' - ' + data.cuenta_compra_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraIva.append(newOption).trigger('change');
                $comboCuentaCompraIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_descuento){
                var dataCuenta = {
                    id: data.cuenta_compra_descuento.id,
                    text: data.cuenta_compra_descuento.cuenta + ' - ' + data.cuenta_compra_descuento.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraDescuento.append(newOption).trigger('change');
                $comboCuentaCompraDescuento.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_compra_devolucion_iva){
                var dataCuenta = {
                    id: data.cuenta_compra_devolucion_iva.id,
                    text: data.cuenta_compra_devolucion_iva.cuenta + ' - ' + data.cuenta_compra_devolucion_iva.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCompraDevolucionIva.append(newOption).trigger('change');
                $comboCuentaCompraDevolucionIva.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_inventario){
                var dataCuenta = {
                    id: data.cuenta_inventario.id,
                    text: data.cuenta_inventario.cuenta + ' - ' + data.cuenta_inventario.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaInventario.append(newOption).trigger('change');
                $comboCuentaInventario.val(dataCuenta.id).trigger('change');
            }

            if(data.cuenta_costos){
                var dataCuenta = {
                    id: data.cuenta_costos.id,
                    text: data.cuenta_costos.cuenta + ' - ' + data.cuenta_costos.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboCuentaCostos.append(newOption).trigger('change');
                $comboCuentaCostos.val(dataCuenta.id).trigger('change');
            }

            if (data.inventario) {
                $('#inventario_familia').prop('checked', true);
                showInventario(true);
            } else {
                $('#inventario_familia').prop('checked', false);
                showInventario(false);
            }

            $("#codigo_familia").val(data.codigo);
            $("#nombre_familia").val(data.nombre);
            $("#id_familia_up").val('');

            $("#familiaFormModal").modal('show');

            document.getElementById('familiaFormModal').click();
            
            setTimeout(function(){
                $('#codigo_familia').focus();
                $('#codigo_familia').select();
            },20);
        });
    }

    $("#codigo_familia").on('keydown', function(event) {
        if(event.keyCode == 13){
            event.preventDefault();
            setTimeout(function(){
                $('#nombre_familia').focus();
                $('#nombre_familia').select();
            },10);
        }
    });

    $comboCuentaVenta = $('#id_cuenta_venta').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [6,9]
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

    $comboCuentaVentaRetencion = $('#id_cuenta_venta_retencion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [13]
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

    $comboCuentaVentaDevolucion = $('#id_cuenta_venta_devolucion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [6]
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

    $comboCuentaVentaIva = $('#id_cuenta_venta_iva').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [16]
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

    $comboCuentaVentaDescuento = $('#id_cuenta_venta_descuento').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [11]
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

    $comboCuentaVentaDevolucionIva = $('#id_cuenta_venta_devolucion_iva').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [6]
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

    $comboCuentaCompra = $('#id_cuenta_compra').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [17]
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
    
    $comboCuentaCompraRetencion = $('#id_cuenta_compra_retencion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [12]
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
    
    $comboCuentaCompraDevolucion = $('#id_cuenta_compra_devolucion').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [17]
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
    
    $comboCuentaCompraIva = $('#id_cuenta_compra_iva').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [9]
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
    
    $comboCuentaCompraDescuento = $('#id_cuenta_compra_devolucion_iva').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [17]
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
    
    $comboCuentaCompraDevolucionIva = $('#id_cuenta_compra_descuento').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [10]
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

    $comboCuentaInventario = $('#id_cuenta_inventario').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [10]
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

    $comboCuentaCostos = $('#id_cuenta_costos').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    id_tipo_cuenta: [10]
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

    showInventario(false);

    $('.water').hide();
    familias_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInput").on("input", function (e) {
    familias_table.context[0].jqXHR.abort();
    $('#cecosTable').DataTable().search($("#searchInput").val()).draw();
});

$(document).on('click', '#createFamilia', function () {
    clearFormFamilias();
    showInventario(false);
    $("#updateFamilia").hide();
    $("#saveFamilia").show();
    $('#inventario_familia').prop('checked', false);
    $("#familiaFormModal").modal('show');

    setTimeout(function(){
        $('#codigo_familia').focus();
        $('#codigo_familia').select();
    },10);
});

function clearFormFamilias(){
    $("#textFamiliaCreate").show();
    $("#textFamiliaUpdate").hide();
    $("#textFamiliaDuplicate").hide();

    $("#saveFamiliaLoading").hide();
    $("#id_familia_up").val('');
    $("#codigo_familia").val('');
    $("#nombre_familia").val('');

    $comboCuentaVenta.val('').trigger('change');
    $comboCuentaVentaRetencion.val('').trigger('change');
    $comboCuentaVentaDevolucion.val('').trigger('change');
    $comboCuentaVentaIva.val('').trigger('change');
    $comboCuentaVentaDescuento.val('').trigger('change');
    $comboCuentaVentaDevolucionIva.val('').trigger('change');

    $comboCuentaInventario.val('').trigger('change');
    $comboCuentaCostos.val('').trigger('change');

    $comboCuentaCompra.val('').trigger('change');
    $comboCuentaCompraRetencion.val('').trigger('change');
    $comboCuentaCompraDevolucion.val('').trigger('change');
    $comboCuentaCompraIva.val('').trigger('change');
    $comboCuentaCompraDescuento.val('').trigger('change');
    $comboCuentaCompraDevolucionIva.val('').trigger('change');
}

$(document).on('click', '#saveFamilia', function () {
    var form = document.querySelector('#familiasForm');

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $("#saveFamiliaLoading").show();
    $("#updateFamilia").hide();
    $("#saveFamilia").hide();

    let data = {
        codigo: $('#codigo_familia').val(),
        nombre: $('#nombre_familia').val(),
        inventario: $("input[type='checkbox']#inventario_familia").is(':checked') ? '1' : '',
        id_cuenta_venta: $('#id_cuenta_venta').val(),
        id_cuenta_venta_retencion: $('#id_cuenta_venta_retencion').val(),
        id_cuenta_venta_devolucion: $('#id_cuenta_venta_devolucion').val(),
        id_cuenta_venta_iva: $('#id_cuenta_venta_iva').val(),
        id_cuenta_venta_descuento: $('#id_cuenta_venta_descuento').val(),
        id_cuenta_venta_devolucion_iva: $('#id_cuenta_venta_devolucion_iva').val(),
        id_cuenta_inventario: $('#id_cuenta_inventario').val(),
        id_cuenta_costos: $('#id_cuenta_costos').val(),
        id_cuenta_compra: $('#id_cuenta_compra').val(),
        id_cuenta_compra_retencion: $('#id_cuenta_compra_retencion').val(),
        id_cuenta_compra_devolucion: $('#id_cuenta_compra_devolucion').val(),
        id_cuenta_compra_iva: $('#id_cuenta_compra_iva').val(),
        id_cuenta_compra_descuento: $('#id_cuenta_compra_descuento').val(),
        id_cuenta_compra_devolucion_iva: $('#id_cuenta_compra_devolucion_iva').val(),
    };

    $.ajax({
        url: base_url + 'familia',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormFamilias();
            $("#saveFamilia").show();
            $("#updateFamilia").hide();
            $("#saveFamiliaLoading").hide();
            $("#familiaFormModal").modal('hide');
            familias_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Familia creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveFamilia').show();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#updateFamilia', function () {
    var form = document.querySelector('#familiasForm');

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    let data = {
        id: $("#id_familia_up").val(),
        codigo: $('#codigo_familia').val(),
        nombre: $('#nombre_familia').val(),
        inventario: $("input[type='checkbox']#inventario_familia").is(':checked') ? '1' : '',
        id_cuenta_venta: $('#id_cuenta_venta').val(),
        id_cuenta_venta_retencion: $('#id_cuenta_venta_retencion').val(),
        id_cuenta_venta_devolucion: $('#id_cuenta_venta_devolucion').val(),
        id_cuenta_venta_iva: $('#id_cuenta_venta_iva').val(),
        id_cuenta_venta_descuento: $('#id_cuenta_venta_descuento').val(),
        id_cuenta_venta_devolucion_iva: $('#id_cuenta_venta_devolucion_iva').val(),
        id_cuenta_inventario: $('#id_cuenta_inventario').val(),
        id_cuenta_costos: $('#id_cuenta_costos').val(),
        id_cuenta_compra: $('#id_cuenta_compra').val(),
        id_cuenta_compra_retencion: $('#id_cuenta_compra_retencion').val(),
        id_cuenta_compra_devolucion: $('#id_cuenta_compra_devolucion').val(),
        id_cuenta_compra_iva: $('#id_cuenta_compra_iva').val(),
        id_cuenta_compra_descuento: $('#id_cuenta_compra_descuento').val(),
        id_cuenta_compra_devolucion_iva: $('#id_cuenta_compra_devolucion_iva').val(),
    };

    $.ajax({
        url: base_url + 'familia',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormFamilias();
            $("#saveFamilia").show();
            $("#updateFamilia").hide();
            $("#saveFamiliaLoading").hide();
            $("#familiaFormModal").modal('hide');
            familias_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Familia actualizada con exito!', true);
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
});



$('input[type=checkbox][name=inventario_familia]').change(function () {
    if(!$("input[type='checkbox']#inventario_familia").is(':checked')){
        showInventario(false);
    } else {
        showInventario(true);
    }
});

function showInventario(show = false) {
    if (show) {
        $('#input-familia-inventario').show();
        $('#input-familia-costos').show();
        $('#inputs-familias-compras').show();
    } else {
        $('#input-familia-inventario').hide();
        $('#input-familia-costos').hide();
        $('#inputs-familias-compras').hide();
    }
}