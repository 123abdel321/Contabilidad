

var $comboComprobanteNomina = null;
var $comboComprobanteParafiscales = null;
var $comboComprobanteSeguridadSocial = null;
var $comboComprobantePrestacionesSociales = null;

function entornoInit() {

    cargarPopoverGeneral();
    cargarVariablesDeEntorno();
    cargarSelect2VariablesDeEntorno();

}

function cargarVariablesDeEntorno() {
    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        const checksEntorno = [
            'iva_incluido',
            'capturar_documento_descuadrado',
            'validar_salto_consecutivos',
            'vendedores_ventas',
            'ubicacion_maximoph',
            'fecha_ultimo_cierre',
            'no_exonerado_parafiscales',
            'recordar_ultimo_precio_venta',
        ];

        const select2Comprobantes = [
            'id_comprobante_nomina',
            'id_comprobante_parafiscales',
            'id_comprobante_seguridad_social',
            'id_comprobante_prestaciones_sociales',
        ];

        checksEntorno.forEach(entorno => {
            if (variable.nombre == entorno) {
                if (variable.valor == '1') $('#'+entorno).prop('checked', true);
                else $('#'+entorno).prop('checked', false);
            }
        });

        select2Comprobantes.forEach(entorno => {
            if (variable.nombre == entorno) {
                if (variable.comprobante) {
                    const dataComprobante = {
                        id: variable.comprobante.id,
                        text: variable.comprobante.codigo + ' - ' + variable.comprobante.nombre
                    };
                    const newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
                    $('#'+entorno).append(newOption).val(dataComprobante.id).trigger('change');
                }
            }
        });

        if (variable.nombre == 'valor_uvt') {
            $('#valor_uvt').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'porcentaje_iva_aiu') {
            $('#porcentaje_iva_aiu').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'fecha_ultimo_cierre') {
            $('#fecha_ultimo_cierre').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'redondeo_gastos') {
            $('#redondeo_gastos').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_utilidad') {
            $('#cuenta_utilidad').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_perdida') {
            $('#cuenta_perdida').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'observacion_venta') {
            quill.root.innerHTML = variable.valor;
            continue;
        }

        if (variable.nombre == 'salario_minimo') {
            $('#salario_minimo').val(new Intl.NumberFormat('ja-JP').format(variable.valor));
            continue;
        }

        if (variable.nombre == 'subsidio_transporte') {
            $('#subsidio_transporte').val(new Intl.NumberFormat('ja-JP').format(variable.valor));
            continue;
        }

        if (variable.nombre == 'cuenta_x_pagar_empleados') {
            $('#cuenta_x_pagar_empleados').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_contable_pago_nomina') {
            $('#cuenta_contable_pago_nomina').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_bancaria_nomina') {
            $('#cuenta_bancaria_nomina').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'tipo_cuenta_banco') {
            $('#tipo_cuenta_banco').val(variable.valor).trigger('change');
            continue;
        }
    }
}

function cargarSelect2VariablesDeEntorno() {
    $comboComprobanteNomina = $('#id_comprobante_nomina').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
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

    $comboComprobanteParafiscales= $('#id_comprobante_parafiscales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
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

    $comboComprobanteSeguridadSocial= $('#id_comprobante_seguridad_social').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
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

    $comboComprobantePrestacionesSociales= $('#id_comprobante_prestaciones_sociales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
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

var quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Escribe algo aquí...',
    modules: {
        toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link']]
    }
});

$(document).on('click', '#updateEntorno', function () {
    $("#updateEntornoLoading").show();
    $("#updateEntorno").hide();

    let data = {
        valor_uvt: $('#valor_uvt').val(),
        porcentaje_iva_aiu: $('#porcentaje_iva_aiu').val(),
        redondeo_gastos: $('#redondeo_gastos').val(),
        cuenta_utilidad: $('#cuenta_utilidad').val(),
        cuenta_perdida: $('#cuenta_perdida').val(),
        fecha_ultimo_cierre: $('#fecha_ultimo_cierre').val(),
        observacion_venta: quill.root.innerHTML,
        iva_incluido: $("input[type='checkbox']#iva_incluido").is(':checked') ? '1' : '',
        capturar_documento_descuadrado: $("input[type='checkbox']#capturar_documento_descuadrado").is(':checked') ? '1' : '0',
        validar_salto_consecutivos: $("input[type='checkbox']#validar_salto_consecutivos").is(':checked') ? '1' : '0',
        vendedores_ventas: $("input[type='checkbox']#vendedores_ventas").is(':checked') ? '1' : '',
        recordar_ultimo_precio_venta: $("input[type='checkbox']#recordar_ultimo_precio_venta").is(':checked') ? '1' : '',
        ubicacion_maximoph: $("input[type='checkbox']#ubicacion_maximoph").is(':checked') ? '1' : '',
        salario_minimo: stringToNumberFloat($("#salario_minimo").val()),
        subsidio_transporte: stringToNumberFloat($("#subsidio_transporte").val()),
        cuenta_x_pagar_empleados: $("#cuenta_x_pagar_empleados").val(),
        no_exonerado_parafiscales: $("input[type='checkbox']#no_exonerado_parafiscales").is(':checked') ? '1' : '',
        cuenta_contable_pago_nomina: $("#cuenta_contable_pago_nomina").val(),
        cuenta_bancaria_nomina: $("#cuenta_bancaria_nomina").val(),
        tipo_cuenta_banco: $("#tipo_cuenta_banco").val(),
        id_comprobante_nomina: $("#id_comprobante_nomina").val(),
        id_comprobante_parafiscales: $("#id_comprobante_parafiscales").val(),
        id_comprobante_seguridad_social: $("#id_comprobante_seguridad_social").val(),
        id_comprobante_prestaciones_sociales: $("#id_comprobante_prestaciones_sociales").val(),
        encabezado_ventas_regimen: $('#encabezado_ventas_regimen').val(),
    };

    $.ajax({
        url: base_url + 'entorno',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#updateEntornoLoading").hide();
            $("#updateEntorno").show();

            agregarToast('exito', 'Actualización exitosa', 'Datos de entorno actualizados con exito!', true);
        }
    }).fail((err) => {
        $("#updateEntornoLoading").hide();
        $("#updateEntorno").show();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
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
