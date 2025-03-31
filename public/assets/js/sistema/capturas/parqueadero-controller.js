let parqueadero_table;
let guardandoParqueadero = false;
let parqueadero_table_pagos;
let $comboBodegaParqueadero = null;
let $comboResolucionParqueadero = null;
let $comboClienteParqueadero = null;
let $comboClienteParqueaderoFilter = null;
let $comboProductoParqueadero = null;
let parqueaderoActivo = null;
var totalAnticiposDisponiblesParqueadero = 0;
let bodegaEventoParqueadero = false;

function parqueaderoInit () {
    cargarTablasParqueadero();
    cargarCombosParqueadero();
    cargarChangesParqueadero();
    loadFormasPagoParqueadero();

    $("#buscarPlacaParqueadero").focus();
}

function cargarTablasParqueadero() {
    //PARQUEADERO
    parqueadero_table = $('#parqueaderoTable').DataTable({
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
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'parqueadero',
            data: function ( d ) {
                d.id_nit = $("#id_nit_parqueadro_filter").val(),
                d.tipo_vehiculo = $("#tipo_vehiculo_parqueadero_filter").val();
                d.placa = $("#placa_parqueadero_filter").val();
            }
        },
        'rowCallback': function(row, data, index){
            if (data.id_venta) {
                // $('td', row).css('background-color', 'background-color: #05ff0026;');
                $('td', row).css('background-color', 'rgb(186 231 176)');
                return;
            }
        },
        columns: [
            {"data":function (row, type, set){  
                if (row.tipo == 1) return 'CARRO';
                if (row.tipo == 2) return 'MOTO';
                if (row.tipo == 3) return 'OTROS';
                return '';
            }},
            {"data":'placa'},
            {"data":function (row, type, set){  
                if (row.cliente) {
                    return row.cliente.numero_documento+' - '+row.cliente.nombre_completo
                }
                return '';
            }},
            {"data":function (row, type, set){  
                if (row.producto) {
                    return row.producto.nombre;
                }
                return '';
            }},
            {"data":'fecha_inicio'},
            {"data":'fecha_fin'},
            {"data":'consecutivo'},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    if (!row.id_venta) html+= '<span id="pagarparqueadero_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info pagar-parqueadero" style="margin-bottom: 0rem !important; min-width: 50px;">Pagar</span>&nbsp;';
                    if (!row.id_venta && editarParqueadero) html+= '<span id="editparqueadero_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-parqueadero" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    if (!row.id_venta && eliminarParqueadero) html+= '<span id="deleteparqueadero_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-parqueadero" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    if (row.id_venta) html+= '<span id="deleteparqueadero_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-primary drop-parqueadero" style="margin-bottom: 0rem !important; min-width: 50px;">Imprimir</span>';
                    return html;
                }
            },
        ]
    });

    //FORMAS PAGO
    parqueadero_table_pagos = $('#parqueaderoFormaPago').DataTable({
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
                return `<input type="text" data-type="currency" class="form-control form-control-sm ${className}" style="text-align: right; font-size: larger;" value="0" onfocus="focusFormaPagoParqueadero(${row.id}, ${anticipos})" onfocusout="calcularVentaParqueadero(${row.id}, ${anticipos})" onkeypress="changeFormaPagoParqueadero(${row.id}, ${anticipos}, event)" id="parqueadero_forma_pago_${row.id}">`;
            }},
        ],
        initComplete: function () {
            $('#parqueaderoFormaPago').on('draw.dt', function() {
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

    if (parqueadero_table) {
        //PAGAR PARQUEADERO
        parqueadero_table.on('click', '.pagar-parqueadero', function() {

            var id = this.id.split('_')[1];
            var data = getDataById(id, parqueadero_table);
            parqueaderoActivo = data;
            $("#parqueaderoTexto").text('PLACA: '+data.placa);

            clearFormParqueaderoVenta();

            if(primeraResolucionParqueadero && primeraResolucionParqueadero.length > 0){
                var dataResolucion = {
                    id: primeraResolucionParqueadero[0].id,
                    text: primeraResolucionParqueadero[0].prefijo + ' - ' + primeraResolucionParqueadero[0].nombre
                };
                var newOption = new Option(dataResolucion.text, dataResolucion.id, false, false);
                $comboResolucionParqueadero.append(newOption).trigger('change');
                $comboResolucionParqueadero.val(dataResolucion.id).trigger('change');
            }

            $("#parqueaderoVentaFormModal").modal('show');
        });
        //EDITAR PARQUEADERO
        parqueadero_table.on('click', '.edit-parqueadero', function() {

            var id = this.id.split('_')[1];
            var data = getDataById(id, parqueadero_table);
            parqueaderoActivo = data;

            clearFormParqueaderoVenta();

            $("#textParqueaderoCreate").hide();
            $("#textParqueaderoUpdate").show();
            $("#id_parqueadero_up").val(data.id);

            if(data.bodega){
                var dataBodega = {
                    id: data.bodega.id,
                    text: data.bodega.codigo + ' - ' + data.bodega.nombre
                };
                var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
                $comboBodegaParqueadero.append(newOption).trigger('change');
                $comboBodegaParqueadero.val(dataBodega.id).trigger('change');
            }

            if(data.cliente){
                var dataCliente = {
                    id: data.bodega.id,
                    text: data.bodega.codigo + ' - ' + data.bodega.nombre
                };
                var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
                $comboClienteParqueadero.append(newOption).trigger('change');
                $comboClienteParqueadero.val(dataCliente.id).trigger('change');
            }

            if(data.producto){
                var dataProducto = {
                    id: data.producto.id,
                    text: data.producto.codigo + ' - ' + data.producto.nombre
                };
                var newOption = new Option(dataProducto.text, dataProducto.id, false, false);
                $comboProductoParqueadero.append(newOption).trigger('change');
                $comboProductoParqueadero.val(dataProducto.id).trigger('change');
            }
            
            $("#saveParqueadero").hide();
            $("#updateParqueadero").show();
            $("#saveParqueaderoLoading").hide();

            $("#fecha_inicio_parqueadero").prop('disabled',false);
            $("#fecha_inicio_parqueadero").val(data.fecha_inicio);
            $("#tipo_vehiculo_parqueadero").val(data.tipo).change();
            $("#placa_vehiculo_parqueadero").val(data.placa).change();
            $("#consecutivo_bodegas_parqueadero").val(data.consecutivo);

            $("#parqueaderoFormModal").modal('show');
        });
    }

    parqueadero_table.ajax.reload();
}

function clearFormParqueaderoVenta() {
    const dateParqueadero = new Date();
    const formattedDate = dateParqueadero.toISOString().split('T')[0]; // Obtiene yyyy-mm-dd

    $("#textParqueaderoCreate").show();
    $("#textParqueaderoUpdate").hide();

    $("#fecha_inicio_parqueadero").prop('disabled',true);
    $("#id_resolucion_parqueadero").val(null).change();
    $("#fecha_manual_parqueadero").val(formattedDate);
    $("#consecutivo_parqueadero").val('');
    $("#observacion_venta").val('');

    clearFormasPagoParqueadero();
    disabledFormasPagoParqueadero(false);
}

function clearFormParqueadero(){

    const dateParqueadero = new Date();
    const offset = dateParqueadero.getTimezoneOffset();
    dateParqueadero.setMinutes(dateParqueadero.getMinutes() - offset);

    const formattedDate = dateParqueadero.toISOString().slice(0, 16);

    $("#textParqueaderoCreate").show();
    $("#textParqueaderoUpdate").hide();
    $("#saveParqueaderoLoading").hide();

    $("#tipo_vehiculo_parqueadero").val(1);
    $("#fecha_inicio_parqueadero").val(formattedDate);
    $("#placa_vehiculo_parqueadero").val('');
    $("#id_nit_parqueadero").val(null).change();
    $("#id_producto_parqueadero").val(null).change();

    if(primeraBodegaParqueadero && primeraBodegaParqueadero.length > 0){
        var dataBodega = {
            id: primeraBodegaParqueadero[0].id,
            text: primeraBodegaParqueadero[0].codigo + ' - ' + primeraBodegaParqueadero[0].nombre
        };
        var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
        $comboBodegaParqueadero.append(newOption).trigger('change');
        $comboBodegaParqueadero.val(dataBodega.id).trigger('change');
    }

    if(primerNitParqueadero){
        var dataCliente = {
            id: primerNitParqueadero.id,
            text: primerNitParqueadero.numero_documento + ' - ' + primerNitParqueadero.nombre_completo
        };
        var newOption = new Option(dataCliente.text, dataCliente.id, false, false);
        $comboClienteParqueadero.append(newOption).trigger('change');
        $comboClienteParqueadero.val(dataCliente.id).trigger('change');
    }
}

function disabledFormasPagoParqueadero(estado = true) {
    var dataFormasPago = parqueadero_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#parqueadero_forma_pago_'+formaPago.id).prop('disabled', estado);
        }
    }

    if (totalAnticiposDisponiblesParqueadero <= 0) {
        var pagosAnticipos = document.getElementsByClassName('anticipos');
        if (pagosAnticipos) { //HIDE ELEMENTS
            for (let index = 0; index < pagosAnticipos.length; index++) {
                const element = pagosAnticipos[index];
                element.disabled = true;
            }
        }
    }
}

function clearFormasPagoParqueadero() {
    var dataFormasPago = parqueadero_table_pagos.rows().data();

    if (dataFormasPago.length) {
        for (let index = 0; index < dataFormasPago.length; index++) {
            var formaPago = dataFormasPago[index];
            $('#parqueadero_forma_pago_'+formaPago.id).val(0);
        }
    }

    calcularVentaParqueadero();
}

function cargarCombosParqueadero() {
    $comboClienteParqueadero = $('#id_nit_parqueadero').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#parqueaderoFormModal'),
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

    $comboClienteParqueaderoFilter = $('#id_nit_parqueadero_filter').select2({
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

    $comboProductoParqueadero = $('#id_producto_parqueadero').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#parqueaderoFormModal'),
        delay: 250,
        dropdownCssClass: 'custom-id_producto_parqueadero',
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
            url: 'api/producto/combo-parqueadero',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboBodegaParqueadero = $('#id_bodega_parqueadero').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#parqueaderoFormModal'),
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

    $comboResolucionParqueadero = $('#id_resolucion_parqueadero').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#parqueaderoVentaFormModal'),
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
}

function cargarChangesParqueadero() {
    $("#id_bodega_parqueadero").on('change', function(event) {
        if (bodegaEventoParqueadero) return;
        consecutivoSiguienteParqueadero();
    });

    $("#id_resolucion_parqueadero").on('change', function(event) {
        consecutivoSiguienteResolucionParqueadero();
    });

    $("#id_nit_parqueadero_filter").on('change', function(event) {
        parqueadero_table.ajax.reload();
    });

    $("#tipo_vehiculo_parqueadero_filter").on('change', function(event) {
        parqueadero_table.ajax.reload();
    });
}

function consecutivoSiguienteResolucionParqueadero() {
    var id_resolucion = $('#id_resolucion_parqueadero').val();
    var fecha_manual = $('#fecha_manual_parqueadero').val();

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
                $("#consecutivo_parqueadero").val(res.data);
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function calcularVentaParqueadero(idFormaPago) {
    if (guardandoParqueadero) {
        return;
    }

    if (
        $('#parqueadero_forma_pago_'+idFormaPago).val() == '' ||
        $('#parqueadero_forma_pago_'+idFormaPago).val() < 0
    ) {
        $('#parqueadero_forma_pago_'+idFormaPago).val(0);
    }

    $('#total_faltante_parqueadero').removeClass("is-invalid");

    var [iva, retencion, descuento, total, subtotal] = totalValoresParqueadero();
    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoParqueadero();
    var totalFaltante = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);

    if ((totalOtrosPagos + totalEfectivo + totalAnticipos) >= total) {
        var totalCambio = (totalEfectivo + totalOtrosPagos + totalAnticipos) - total;
        if(parseInt(totalCambio) > 0)$('#cambio-totals-parqueadero').show();
        document.getElementById('total_cambio_parqueadero').innerText = new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(totalCambio);
    } else {
        $('#cambio-totals-parqueadero').hide();
        if (totalFaltante < 0) {
            $('#parqueadero_forma_pago_'+idFormaPago).val(totalFaltante * -1);
            $('#parqueadero_forma_pago_'+idFormaPago).select();
        }
    }
    var totalPagado = totalFaltante < 0 ? total : totalEfectivo + totalOtrosPagos + totalAnticipos;
    var totalFaltante = totalFaltante < 0 ? 0 : totalFaltante;

    var countA = new CountUp('total_pagado_parqueadero', 0, totalPagado, 2, 0.5);
        countA.start();

    var countB = new CountUp('total_faltante_parqueadero', 0, totalFaltante, 2, 0.5);
        countB.start();
}

function totalFormasPagoParqueadero(idFormaPago = null) {
    var totalEfectivo = 0;
    var totalAnticipos = 0;
    var totalOtrosPagos = 0;

    var dataPagoParqueadero = parqueadero_table_pagos.rows().data();

    if(dataPagoParqueadero.length > 0) {
        for (let index = 0; index < dataPagoParqueadero.length; index++) {
            
            var ventaPago = stringToNumberFloat($('#parqueadero_forma_pago_'+dataPagoParqueadero[index].id).val());
            
            if (idFormaPago && idFormaPago == dataPagoParqueadero[index].id) continue;

            if (dataPagoParqueadero[index].id == 1) totalEfectivo+= ventaPago;
            else if ($('#parqueadero_forma_pago_'+dataPagoParqueadero[index].id).hasClass("anticipos")) totalAnticipos+= ventaPago;
            else totalOtrosPagos+= ventaPago;
        }
    }

    return [totalEfectivo, totalOtrosPagos, totalAnticipos];
}

function focusNextFormasPagoParqueadero(idFormaPago) {
    var dataParqueaderoPagos = parqueadero_table_pagos.rows().data();
    var idFormaPagoFocus = dataParqueaderoPagos[0].id;
    var obtenerFormaPago = false;

    if(!dataParqueaderoPagos.length > 0) return;

    for (let index = 0; index < dataParqueaderoPagos.length; index++) {
        const dataPagoCompra = dataParqueaderoPagos[index];
        if (obtenerFormaPago) {
            idFormaPagoFocus = dataPagoCompra.id;
            obtenerFormaPago = false;
        } else if (dataPagoCompra.id == idFormaPago) {
            obtenerFormaPago = true;
        }
    }

    focusFormaPagoParqueadero(idFormaPagoFocus);
}

function changeFormaPagoParqueadero(idFormaPago, anticipo, event) {

    if (guardandoParqueadero) {
        return;
    }

    if(event.keyCode == 13){

        calcularVentaParqueadero(idFormaPago);

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoParqueadero();
        var [iva, retencion, descuento, total, subtotal] = totalValoresParqueadero();

        if (!total) {
            return;
        }

        if (anticipo) {
            if (totalAnticipos > totalAnticiposDisponiblesParqueadero || totalAnticipos > total) {

                var [efectivo, pagos, totalOtrosAnticipos] = totalFormasPagoParqueadero(idFormaPago);

                $('#parqueadero_forma_pago_'+idFormaPago).val(totalAnticiposDisponiblesParqueadero - totalOtrosAnticipos);
                $('#parqueadero_forma_pago_'+idFormaPago).select();
                return;
            }
        }

        if (vendedoresParqueadero && !$("#id_vendedor_parqueadero").val()) {
            return;
        }

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
            validateSaveParqueadero();
            return;
        }
        focusNextFormasPagoParqueadero(idFormaPago);
    }
}

function validateSaveParqueadero() {
    $('#total_faltante_parqueadero_text').css("color","#484848");
    $('#total_faltante_parqueadero').css("color","#484848");

    if (!guardandoParqueadero) {

        var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoPedidos();
        var total = totalValoresPedidos();

        if ((totalEfectivo + totalOtrosPagos + totalAnticipos) >= total) {
            
            guardandoParqueadero = true;
            saveParqueaderoVenta();
        } else {
            $('#total_faltante_parqueadero_text').css("color","red");
            $('#total_faltante_parqueadero').css("color","red");
        }
    }
}

function saveParqueaderoVenta() {

    var form = document.querySelector('#parqueaderoVentasForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveParqueaderoVenta").hide();
    $("#saveParqueaderoVentaLoading").show();

    let data = {
        id_parqueadero: parqueaderoActivo.id,
        pagos: getPedidosParqueadero(),
        producto: parqueaderoActivo.id_producto,
        id_bodega: parqueaderoActivo.id_bodega,
        consecutivo: $("#consecutivo_parqueadero").val(),
        id_cliente: parqueaderoActivo.id_cliente,
        fecha_manual: $("#fecha_manual_parqueadero").val(),
        id_resolucion: $("#id_resolucion_parqueadero").val(),
        observacion: $("#observacion_parqueadero").val(),
    };

    disabledFormasPagoParqueadero();

    $.ajax({
        url: base_url + 'parqueadero-ventas',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardandoParqueadero = false;
        if (res.success) {
            agregarToast('exito', 'Creación exitosa', 'Venta creada con exito!', true);

            $("#saveParqueaderoVenta").show();
            $("#saveParqueaderoVentaLoading").hide();
            $("#parqueaderoFormModal").modal('hide');

            if(res.impresion) {
                window.open("/ventas-print/"+res.impresion, '_blank');
            }
            
            arqueadero_table.ajax.reload();
        }
    }).fail((err) => {
        guardandoParqueadero = false;
        $("#saveParqueaderoVenta").show();
        $("#saveParqueaderoVentaLoading").hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getPedidosParqueadero() {
    var data = [];

    var dataPedidoPagos = parqueadero_table_pagos.rows().data();

    if(!dataPedidoPagos.length > 0) return data;

    for (let index = 0; index < dataPedidoPagos.length; index++) {
        const dataPagoPedido = dataPedidoPagos[index];
        var pagoVentaPedido = stringToNumberFloat($('#parqueadero_forma_pago_'+dataPagoPedido.id).val());
        if (pagoVentaPedido > 0) {
            data.push({
                id: dataPagoPedido.id,
                valor: pagoVentaPedido
            });
        }
    }

    return data;
}

function focusFormaPagoParqueadero(idFormaPago, anticipo = false) {

    if (guardandoParqueadero) {
        return;
    }

    var [iva, retencion, descuento, total, subtotal] = totalValoresParqueadero();
    var [totalEfectivo, totalOtrosPagos, totalAnticipos] = totalFormasPagoParqueadero(idFormaPago);
    
    var totalFactura = total - (totalEfectivo + totalOtrosPagos + totalAnticipos);
    totalFactura = totalFactura < 0 ? 0 : totalFactura;

    if (anticipo) {
        if ((totalAnticiposDisponiblesParqueadero - totalAnticipos) < totalFactura) {
            $('#parqueadero_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalAnticiposDisponiblesParqueadero - totalAnticipos));
            $('#parqueadero_forma_pago_'+idFormaPago).select();
            return;
        }
    }

    $('#parqueadero_forma_pago_'+idFormaPago).val(formatCurrencyValue(totalFactura));
    $('#parqueadero_forma_pago_'+idFormaPago).select();
}

function totalValoresParqueadero() {
    var iva = retencion = descuento = total = redondeo = valorBruto = 0;

    //PRODUCTO
    let producto = parqueaderoActivo.producto;
    let diferenciaFechas = 0;
    let sumarCuartoHora = false;

    let fechaInicio = new Date(parqueaderoActivo.fecha_inicio); // UTC-5 para Colombia

    // CALCULAR HORAS
    if (parseInt(producto.tipo_tiempo) == 1) {
        let fechaActual = new Date();
        let diferenciaMs = fechaActual - fechaInicio;
        diferenciaFechas = Math.floor(diferenciaMs / (1000 * 60 * 60));
        let diferenciaMinutos = Math.floor((diferenciaMs % (1000 * 60 * 60)) / (1000 * 60));
        let excedeCuartoHora = diferenciaMinutos > 15;

        $("#total_tiempo_parqueadero").text(`${diferenciaFechas} Horas + ${diferenciaMinutos} Minutos`);

        if (excedeCuartoHora) diferenciaFechas+= 1;
        else if (!excedeCuartoHora && producto.fraccion_hora) sumarCuartoHora = true;
        else if (!excedeCuartoHora && !producto.fraccion_hora) diferenciaFechas+= 1;
    }

    //CALCULAR DÍA
    if (parseInt(producto.tipo_tiempo) == 2) {
        let [anio, mes, dia, horas, minutos] = parqueaderoActivo.fecha_inicio.split(/[- :]/);
        let fechaInicioDate = new Date(anio, mes - 1, dia, horas, minutos);
        let fechaActual = new Date();
        let diferenciaMs = fechaActual - fechaInicioDate;
        diferenciaFechas = parseInt(Math.floor(diferenciaMs / (1000 * 60 * 60 * 24)));

        $("#total_tiempo_parqueadero").text(`${diferenciaFechas} Días`);
    }

    //CALCULAR MES
    if (parseInt(producto.tipo_tiempo) == 3) {
        let [anio, mes, dia, horas, minutos] = parqueaderoActivo.fecha_inicio.split(/[- :]/);
        let fechaInicioDate = new Date(anio, mes - 1, dia, horas, minutos);
        let fechaActual = new Date();

        let diferenciaAnios = fechaActual.getFullYear() - fechaInicioDate.getFullYear();
        let diferenciaMeses = fechaActual.getMonth() - fechaInicioDate.getMonth();

        if (diferenciaMeses < 0) {
            diferenciaAnios--;
            diferenciaMeses += 12;
        }

        diferenciaFechas = parseInt(diferenciaAnios * 12 + diferenciaMeses);

        $("#total_tiempo_parqueadero").text(`${diferenciaFechas} Meses`);
    }

    if (!diferenciaFechas) diferenciaFechas = 1;
    
    let cuentaRetencion = producto.familia.cuenta_venta_retencion;
    let porcentaje_rete_fuente = cuentaRetencion ? parseFloat(cuentaRetencion.porcentaje) : 0;
    let tope_retencion = cuentaRetencion ? parseFloat(cuentaRetencion.base) : 0;
    let ivaProducto = 0;
    let costo = producto.precio;
    let totalPorCantidad = diferenciaFechas * costo;
    let cuentaIva = producto.familia.cuenta_venta_iva;
    let porcentaje_iva = 0;
    let descuentoProducto = 0;

    if (cuentaIva && cuentaIva.impuesto) {
        porcentaje_iva = parseFloat(cuentaIva.impuesto.porcentaje);
        ivaProducto = ((totalPorCantidad - descuentoProducto) * (porcentaje_iva / 100)).toFixed(2);
        if (ivaIncluidoParqueadero) {
            ivaProducto = ((totalPorCantidad - descuentoProducto) - ((totalPorCantidad - descuentoProducto) / (1 + porcentaje_iva / 100))).toFixed(2);
        }
    }

    if (ivaIncluidoParqueadero && porcentaje_iva) {
        costo = (producto.precio / (1 + porcentaje_iva / 100)).toFixed(2);
    }

    valorBruto = (diferenciaFechas * costo) - descuentoProducto;
    iva = parseFloat(ivaProducto);
    descuento = descuentoProducto;
    total = (valorBruto + iva).toFixed(1);
    
    if (sumarCuartoHora) {
        total = parseFloat(total) + parseFloat(producto.precio / 4);
    }


    return [iva, retencion, descuento, total, valorBruto, redondeo];
}

function loadFormasPagoParqueadero() {
    var totalRows = parqueadero_table_pagos.rows().data().length;
    if(parqueadero_table_pagos.rows().data().length){
        parqueadero_table_pagos.clear([]).draw();
        for (let index = 0; index < totalRows; index++) {
            parqueadero_table_pagos.row(0).remove().draw();
        }
    }
    parqueadero_table_pagos.ajax.reload();
}

function consecutivoSiguienteParqueadero() {
    var id_bodega = $('#id_bodega_parqueadero').val();

    if(id_bodega) {

        let data = {
            id_bodega: id_bodega,
        }

        $.ajax({
            url: base_url + 'bodega-parqueadero-consecutivo',
            method: 'GET',
            data: data,
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                $("#consecutivo_bodegas_parqueadero").val(res.data);
            }
        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function buscarPlacaParqueadero(event) {
    if (event.keyCode == 13) {
        parqueadero_table.ajax.reload(function (res) {
            if (!res.data.length) {
                let plata = $("#placa_parqueadero_filter").val();
                let ultimoCarater = plata.slice(-1);

                clearFormParqueadero();

                $("#placa_vehiculo_parqueadero").val(plata);
                if (parseInt(ultimoCarater)) $("#tipo_vehiculo_parqueadero").val(1).change();
                else $("#tipo_vehiculo_parqueadero").val(2).change();

                if(primeraBodegaParqueadero && primeraBodegaParqueadero.length > 0){
                    var dataBodega = {
                        id: primeraBodegaParqueadero[0].id,
                        text: primeraBodegaParqueadero[0].codigo + ' - ' + primeraBodegaParqueadero[0].nombre
                    };
                    var newOption = new Option(dataBodega.text, dataBodega.id, false, false);
                    $comboBodegaParqueadero.append(newOption).trigger('change');
                    $comboBodegaParqueadero.val(dataBodega.id).trigger('change');
                }

                $("#saveParqueadero").show();
                $("#updateParqueadero").hide();
                $("#saveParqueaderoLoading").hide();
                $("#parqueaderoFormModal").modal('show');
            }
        });
    }
}

$(document).on('click', '#createParqueadero', function () {
    clearFormParqueadero();
    $("#updateParqueadero").hide();
    $("#saveParqueadero").show();
    $("#parqueaderoFormModal").modal('show');
});

$(document).on('click', '#saveParqueaderoVenta', function () {
    saveParqueaderoVenta();
});

$(document).on('click', '#saveParqueadero', function () {

    var form = document.querySelector('#parqueaderoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveParqueaderoLoading").show();
    $("#updateParqueadero").hide();
    $("#saveParqueadero").hide();

    let data = {
        id_nit: $("#id_nit_parqueadero").val(),
        tipo: $("#tipo_vehiculo_parqueadero").val(),
        placa: $("#placa_vehiculo_parqueadero").val(),
        fecha_inicio: $("#fecha_inicio_parqueadero").val(),
        id_producto: $("#id_producto_parqueadero").val(),
        id_bodega: $("#id_bodega_parqueadero").val(),
        consecutivo: $("#consecutivo_bodegas_parqueadero").val(),
    }

    $.ajax({
        url: base_url + 'parqueadero',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormParqueadero();
            $("#saveParqueadero").show();
            $("#saveParqueaderoLoading").hide();
            $("#parqueaderoFormModal").modal('hide');
            parqueadero_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Item parqueadero creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveParqueadero').show();
        $('#saveParqueaderoLoading').hide();
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

$(document).on('click', '#updateParqueadero', function () {

    var form = document.querySelector('#parqueaderoForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveParqueaderoLoading").show();
    $("#updateParqueadero").hide();
    $("#saveParqueadero").hide();

    let data = {
        id: $("#id_parqueadero_up").val(),
        id_nit: $("#id_nit_parqueadero").val(),
        tipo: $("#tipo_vehiculo_parqueadero").val(),
        placa: $("#placa_vehiculo_parqueadero").val(),
        fecha_inicio: $("#fecha_inicio_parqueadero").val(),
        id_producto: $("#id_producto_parqueadero").val(),
        id_bodega: $("#id_bodega_parqueadero").val(),
        consecutivo: $("#consecutivo_bodegas_parqueadero").val(),
    }

    $.ajax({
        url: base_url + 'parqueadero',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormParqueadero();
            $("#saveParqueadero").show();
            $("#saveParqueaderoLoading").hide();
            $("#parqueaderoFormModal").modal('hide');
            parqueadero_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Item parqueadero actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#updateParqueadero').show();
        $('#saveParqueaderoLoading').hide();
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
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
});