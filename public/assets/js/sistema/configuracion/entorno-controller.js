
function entornoInit() {

    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        var checksEntorno = [
            'iva_incluido',
            'capturar_documento_descuadrado',
            'vendedores_ventas',
            'ubicacion_maximoph',
            'fecha_ultimo_cierre',
            'no_exonerado_parafiscales',
        ];

        checksEntorno.forEach(entorno => {
            if (variable.nombre == entorno) {
                if (variable.valor == '1') $('#'+entorno).prop('checked', true);
                else $('#'+entorno).prop('checked', false);
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

    cargarPopoverGeneral();
}

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
        vendedores_ventas: $("input[type='checkbox']#vendedores_ventas").is(':checked') ? '1' : '',
        ubicacion_maximoph: $("input[type='checkbox']#ubicacion_maximoph").is(':checked') ? '1' : '',
        salario_minimo: stringToNumberFloat($("#salario_minimo").val()),
        subsidio_transporte: stringToNumberFloat($("#subsidio_transporte").val()),
        cuenta_x_pagar_empleados: $("#cuenta_x_pagar_empleados").val(),
        no_exonerado_parafiscales: $("input[type='checkbox']#no_exonerado_parafiscales").is(':checked') ? '1' : '',
        cuenta_contable_pago_nomina: $("#cuenta_contable_pago_nomina").val(),
        cuenta_bancaria_nomina: $("#cuenta_bancaria_nomina").val(),
        tipo_cuenta_banco: $("#tipo_cuenta_banco").val(),
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

var quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Escribe algo aquí...',
    modules: {
        toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link']]
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
