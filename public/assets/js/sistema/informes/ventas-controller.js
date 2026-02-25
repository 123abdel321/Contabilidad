var fechaDesde = null;
var ventas_table = null;

function ventasInit() {

    cargarFechasVentas();
    cargarTablasVentas();
    cargarCombosVentas();

    $('.water').hide();
}

function cargarTablasVentas() {
    var newLenguaje = lenguajeDatatable;
    newLenguaje.sInfo = "Ventas del _START_ al _END_ de un total de _TOTAL_ ";
    
    ventas_table = $('#VentasInformeTable').DataTable({
        pageLength: 20,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        ordering: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        'rowCallback': function(row, data, index){
            if (data.detalle == '') {
                $('td', row).css('background-color', 'rgb(51, 132, 158)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'ventas',
            data: function ( d ) {
                d.id_cliente = $('#id_cliente_ventas').val();
                d.fecha_desde = $('#fecha_manual_ventas').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm');
                d.fecha_hasta = $('#fecha_manual_ventas').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm');
                d.factura = $('#factura_ventas').val();
                d.id_resolucion = $('#id_resolucion_ventas').val();
                d.id_bodega = $('#id_bodega_ventas').val();
                d.id_producto = $('#id_producto_ventas').val();
                d.id_usuario = $('#id_usuario_ventas').val();
                d.id_forma_pago = $('#id_forma_pago_ventas').val();
                d.detallar_venta = $("input[type='radio']#detallar_venta1").is(':checked') ? 'si' : 'no';
            }
        },
        columns: [
            {"data": "documento_referencia"},
            {"data": "nombre_completo"},
            {"data": "nombre_bodega"},
            {"data": "fecha_manual"},
            {"data": "descripcion"},
            {"data": "cantidad", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "costo", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "valor", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "subtotal", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "iva_porcentaje", className: 'dt-body-right'},
            {"data": "total_iva", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "descuento_porcentaje", className: 'dt-body-right'},
            {"data": "total_descuento", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "total_rete_fuente", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "total_factura", render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": "nombre_vendedor"},
            {"data": function (row, type, set){
                if (row.detalle) {
                    return row.valor - row.costo;
                }
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                if (row.valor) {
                    if (!parseInt(row.costo)) {
                        return 100;
                    }
                    return ((row.valor - row.costo) / row.costo) * 100;
                }
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                if (row.valor) {
                    if (!parseInt(row.costo)) {
                        return 100;
                    }
                    return ((row.valor - row.costo) / row.valor) * 100;
                }
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
                    if (row.id) {
                        html+= '<span id="imprimirventa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info imprimir-venta" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px;">PDF &nbsp;<i class="fas fa-print"></i></span>';
                        if (eliminarVentas && !row.fe_codigo_identificador) {
                            html+= '<span id="eliminarventa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger eliminar-venta" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px;">ELIMINAR &nbsp;<i class="fa-solid fa-trash-can"></i></span>';
                            html+= '<span id="eliminarventaloading_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px; display: none;" disabled>ELIMINANDO &nbsp;<i class="fa fa-spinner fa-spin"></i></span>';
                        }
                    }
                    
                    if (row.resolucion && !row.fe_codigo_identificador){
                        html+= '<span id="enviarfeventa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-primary enviar-fe-venta" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px;">Enviar FE &nbsp;<i class="fas fa-share"></i></span>';
                        html+= '<span id="enviarfeventaloading_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px; display: none;" disabled>Enviando &nbsp;<i class="fa fa-spinner fa-spin"></i></span>';
                    } else if (row.factura && row.factura.resolucion && !row.fe_codigo_identificador) {
                        html+= '<span id="enviarnceventa_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-primary enviar-nce-venta" style="margin-bottom: 0rem !important; color: white; background-color: white !important;">Enviar NCE &nbsp;<i class="fas fa-share"></i></span>';
                        html+= '<span id="enviarnceventaloading_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-primary" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px; display: none;" disabled>Enviando &nbsp;<i class="fa fa-spinner fa-spin"></i></span>';
                    }

                    if (row.resolucion && row.fe_codigo_identificador) {
                        html+= '<span id="reenviaremail_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success reenviar-email-venta" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px;">REENVIAR &nbsp;<i class="fas fa-envelope"></i></span>&nbsp;';
                        html+= '<span id="reenviaremailloading_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px; display: none;" disabled>Enviando &nbsp;<i class="fa fa-spinner fa-spin"></i></span>&nbsp;';
                    } else if (row.id && enviarEmailVentas){
                        html+= '<span id="enviaremail_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success enviar-email-venta" style="margin-bottom: 0rem !important; color: white; background-color: white !important;">ENVIAR &nbsp;<i class="fas fa-envelope"></i></span>&nbsp;';
                        html+= '<span id="enviaremailloading_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success" style="margin-bottom: 0rem !important; color: white; background-color: white !important; margin-right: 3px; display: none;" disabled>Enviando &nbsp;<i class="fa fa-spinner fa-spin"></i></span>&nbsp;';
                    }
                    return html;
                }
            }
    
        ]
    });

    if (ventas_table) {
        ventas_table.on('click', '.enviar-email-venta', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, ventas_table);

            Swal.fire({
                title: "Enviar factura al email : ",
                input: "text",
                inputValue: data.email_cliente ? data.email_cliente : '',
                inputAttributes: {
                    autocapitalize: "off",
                },
                showCancelButton: true,
                confirmButtonText: "Enviar",
                showLoaderOnConfirm: true,
                preConfirm: async (email) => {
                    $("#enviaremail_"+id).hide();
                    $("#enviaremailloading_"+id).show();
                    $.ajax({
                        url: base_url + 'ventas-email',
                        method: 'POST',
                        data: JSON.stringify({
                            id_venta: id,
                            email: email
                        }),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        $("#enviaremail_"+id).show();
                        $("#enviaremailloading_"+id).hide();

                        agregarToast('exito', 'Envio de email exito', 'el email se ha enviado exitosamente!');

                    }).fail((err) => {
                        $("#enviaremail_"+id).show();
                        $("#enviaremailloading_"+id).hide();
                        
                        const mensajes = err.responseJSON.message;
                        let errorsMsg = '';
                        let countError = 0;

                        if (typeof mensajes === 'string') {
                            agregarToast('error', 'Envio de email errado', mensajes);
                            return;
                        }

                        mensajes.forEach(mensaje => {
                            countError++;
                            errorsMsg+='<b>'+countError+'-</b> '+mensaje+'<br/>';
                        });

                        agregarToast('error', 'Envio de email errado', errorsMsg);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                if (result.isConfirmed) {
                    console.log('result: ',result);
                }
            });
        });
    }
    
    ventas_table.ajax.reload(function (res) {
        showTotalsVentas(res);
    });
}

function cargarCombosVentas() {
    $('#id_resolucion_ventas').select2({
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

    $('#id_cliente_ventas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Cliente",
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

    $('#id_bodega_ventas').select2({
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

    $('#id_usuario_ventas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Usuario",
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
            url: 'api/usuarios/combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $('#id_producto_ventas').select2({
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
            url: 'api/producto/combo-producto',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
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
        }
    });

    $('#id_forma_pago_ventas').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccionar forma de pago",
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
            url: 'api/forma-pago/combo-forma-pago',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
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

function cargarFechasVentas() {
    const start = moment().startOf("month");
    const end = moment().endOf("month");
    
    $("#fecha_manual_ventas").daterangepicker({
        startDate: start,
        endDate: end,
        timePicker: true,
        timePicker24Hour: true,
        timePickerSeconds: true,
        locale: {
            format: "YYYY-MM-DD",
            separator: " - ",
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: moment.weekdaysMin(),
            monthNames: moment.months(),
            firstDay: 1
        },
        ranges: rangoFechas
    }, formatoFecha);

    formatoFecha(start, end, "fecha_manual_ventas");
}

//IMPRIMIR VENTAS PDF
$(document).on('click', '.imprimir-venta', function () {
    var id = this.id.split('_')[1];
    window.open("/ventas-print/"+id, "_blank");
});
$(document).on('click', '.eliminar-venta', function () {

    var id = this.id.split('_')[1];
    var data = getDataById(id, ventas_table);

    Swal.fire({
        title: 'Eliminar factura',
        text: "Desea eliminar la factura a "+data.documento_referencia+"?",
        type: 'warning',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Eliminar facturas!',
        reverseButtons: true,
    }).then((result) => {
        if (result.value){
            
            $("#eliminarventa_"+id).hide();
            $("#eliminarventaloading_"+id).show();
            $.ajax({
                url: base_url + 'ventas',
                method: 'DELETE',
                data: JSON.stringify({
                    id_venta: id
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                ventas_table.ajax.reload(function (res) {
                    showTotalsVentas(res);
                });
            }).fail((err) => {
                $("#eliminarventa_"+id).show();
                $("#eliminarventaloading_"+id).hide();

                $("#crearCapturaVenta").show();
                $("#crearCapturaVentaLoading").hide();
                
                const mensajes = err.responseJSON.message;
                let errorsMsg = '';
                let countError = 0;

                if (typeof mensajes === 'string') {
                    agregarToast('error', 'Envio a la dian errado', mensajes);
                    return;
                }

                mensajes.forEach(mensaje => {
                    countError++;
                    errorsMsg+='<b>'+countError+'-</b> '+mensaje+'<br/>';
                });

                agregarToast('error', 'Envio a la dian errado', errorsMsg);
            });
        }
    })
});

//REENVIAR FACTURACIÓN ELECTRONICA
$(document).on('click', '.enviar-fe-venta', function () {
    var id = this.id.split('_')[1];
    $("#enviarfeventa_"+id).hide();
    $("#enviarfeventaloading_"+id).show();
    $.ajax({
        url: base_url + 'ventas-fe',
        method: 'POST',
        data: JSON.stringify({
            id_venta: id
        }),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        ventas_table.ajax.reload(function (res) {
            showTotalsVentas(res);
        });
    }).fail((err) => {
        $("#enviarfeventa_"+id).show();
        $("#enviarfeventaloading_"+id).hide();

        $("#crearCapturaVenta").show();
        $("#crearCapturaVentaLoading").hide();
        
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);

        agregarToast('error', 'Envio a la dian errado', errorsMsg);
    });
});

$(document).on('click', '.enviar-nce-venta', function () {
    var id = this.id.split('_')[1];
    $("#enviarnceventa_"+id).hide();
    $("#enviarnceventaloading_"+id).show();
    $.ajax({
        url: base_url + 'nota-credito-fe',
        method: 'POST',
        data: JSON.stringify({
            id_nota_credito: id
        }),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        ventas_table.ajax.reload(function (res) {
            showTotalsVentas(res);
        });
    }).fail((err) => {
        $("#enviarnceventa_"+id).show();
        $("#enviarnceventaloading_"+id).hide();

        $("#crearCapturaVenta").show();
        $("#crearCapturaVentaLoading").hide();

        const mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Envio a la dian errado', errorsMsg);
    });
});

//RENVIAR EMAIL FACTURA
$(document).on('click', '.reenviar-email-venta', function () {

    var id = this.id.split('_')[1];

    Swal.fire({
        title: "Agregar email de reenvio",
        input: "text",
        inputAttributes: {
            autocapitalize: "off"
        },
        showCancelButton: true,
        confirmButtonText: "Reenviar",
        showLoaderOnConfirm: true,
        preConfirm: async (email) => {
            $("#reenviaremail_"+id).hide();
            $("#reenviaremailloading_"+id).show();
            $.ajax({
                url: base_url + 'ventas-notificar',
                method: 'POST',
                data: JSON.stringify({
                    id_venta: id,
                    email: email
                }),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#reenviaremail_"+id).show();
                $("#reenviaremailloading_"+id).hide();

                agregarToast('exito', 'Reenvio de email exito', 'el email se ha enviado exitosamente!');

            }).fail((err) => {
                $("#reenviaremail_"+id).show();
                $("#reenviaremailloading_"+id).hide();
                
                const mensajes = err.responseJSON.message;
                let errorsMsg = '';
                let countError = 0;

                if (typeof mensajes === 'string') {
                    agregarToast('error', 'Reenvio de email errado', mensajes);
                    return;
                }

                mensajes.forEach(mensaje => {
                    countError++;
                    errorsMsg+='<b>'+countError+'-</b> '+mensaje+'<br/>';
                });

                agregarToast('error', 'Reenvio de email errado', errorsMsg);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
        if (result.isConfirmed) {
            console.log('result: ',result);
        }
    });
});

$(document).on('click', '#generarVentas', function () {
    $("#generarVentas").hide();
    $("#generarVentasLoading").show();
    ventas_table.ajax.reload(function (res) {
        showTotalsVentas(res);
        $("#generarVentas").show();
        $("#generarVentasLoading").hide();
    });
});

$('input[type=radio][name=detallar_venta]').change(function() {
    $("#generarVentas").hide();
    $("#generarVentasLoading").show();
    if($("input[type='radio']#detallar_venta1").is(':checked')){
        ventas_table.column(4).visible(true);
        ventas_table.column(5).visible(true);
        ventas_table.column(6).visible(true);
        ventas_table.column(8).visible(true);
        ventas_table.column(10).visible(true);
    } else {
        ventas_table.column(4).visible(false);
        ventas_table.column(5).visible(false);
        ventas_table.column(6).visible(false);
        ventas_table.column(8).visible(false);
        ventas_table.column(10).visible(false);
    }
    ventas_table.ajax.reload(function (res) {
        showTotalsVentas(res);
        $("#generarVentas").show();
        $("#generarVentasLoading").hide();
    });
});

$(document).on('click', '#generarInformeZ', function () {
    var mensajeError = '';
    var errorCount = 0

    $("#generarInformeZ").hide();
    $("#generarInformeZLoading").show();
    $("#generarInformeZDisabled").hide();
    
    if (!$('#id_bodega_ventas').val()) {
        errorCount++;
        mensajeError+= errorCount+': La bogeda es requerida <br/>';
    }

    if (!$('#id_resolucion_ventas').val()) {
        errorCount++;
        mensajeError+= errorCount+': La resolución es requerida <br/>';
    }

    // if ($('#fecha_manual_desde_ventas').val() != $('#fecha_manual_hasta_ventas').val()) {
    //     errorCount++;
    //     mensajeError+= errorCount+': La fecha debe estar en el rango de un día <br/>';
    // }

    if (errorCount) {
        
        $("#generarInformeZ").show();
        $("#generarInformeZLoading").hide();
        $("#generarInformeZDisabled").hide();
        agregarToast('warning', 'Informe Z', mensajeError, false);
        return;
    }

    var url = base_web + 'ventas-print-informez';
    $('#fecha_manual_ventas').val() ? url+= '?fecha_desde='+$('#fecha_manual_ventas').data('daterangepicker').startDate.format('YYYY-MM-DD HH:mm') : null;
    $('#fecha_manual_ventas').val() ? url+= '&fecha_hasta='+$('#fecha_manual_ventas').data('daterangepicker').endDate.format('YYYY-MM-DD HH:mm') : null;
    $('#factura_ventas').val() ? url+= '&factura='+$('#factura_ventas').val() : null;
    $('#factura_ventas').val() ? url+= '&id_resolucion='+$('#factura_ventas').val() : null;
    $('#id_bodega_ventas').val() ? url+= '&id_bodega='+$('#id_bodega_ventas').val() : null;
    $('#id_resolucion_ventas').val() ? url+= '&id_resolucion='+$('#id_resolucion_ventas').val() : null;
    $('#id_producto').val() ? url+= '&id_producto='+$('#id_producto').val() : null;
    $('#id_forma_pago_ventas').val() ? url+= '&id_forma_pago='+$('#id_forma_pago_ventas').val() : null;
    $('#id_usuario').val() ? url+= '&id_usuario='+$('#id_usuario').val() : null;
    url+= $("input[type='radio']#detallar_venta1").is(':checked') ? '&detallar_venta=1' : '&detallar_venta=0';

    window.open(url,'_blank');

    setTimeout(function(){
        $("#generarInformeZ").show();
        $("#generarInformeZLoading").hide();
        $("#generarInformeZDisabled").hide();
    },100);
});

function showTotalsVentas(res) {
    if (!res.success) return;

    $("#generarInformeZ").show();
    $("#generarInformeZLoading").hide();
    $("#generarInformeZDisabled").hide();

    var totales = res.totalesVenta[0];
    var totalesNotas = res.totalesNotas[0];
    var total_venta = totales.total_venta - totalesNotas.total_venta;
    var total_costo = totales.total_costo - totalesNotas.total_costo;
    var total_cantidad = totales.total_productos_cantidad - totalesNotas.total_productos_cantidad;
    var total_utilidad = total_venta - total_costo;
    var porcentaje_utilidad = (total_utilidad / totales.total_costo) * 100;

    var countA = new CountUp('total_productos_vendidos', 0, total_cantidad);
        countA.start();

    var countB = new CountUp('total_costo_ventas', 0, total_costo);
        countB.start();

    var countC = new CountUp('total_precio_ventas', 0, total_venta);
        countC.start();

    var countD = new CountUp('total_utilidad_ventas', 0, total_utilidad);
        countD.start();

    var countE = new CountUp('total_porcentaje_ventas', 0, porcentaje_utilidad ? parseFloat(porcentaje_utilidad).toFixed(2) : 100);
        countE.start();
}



