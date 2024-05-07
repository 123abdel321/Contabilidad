
function entornoInit() {

    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        var checksEntorno = [
            'iva_incluido',
            'capturar_documento_descuadrado',
            'vendedores_ventas'
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
    }
}

$(document).on('click', '#updateEntorno', function () {
    $("#updateEntornoLoading").show();
    $("#updateEntorno").hide();

    let data = {
        valor_uvt: $('#valor_uvt').val(),
        porcentaje_iva_aiu: $('#porcentaje_iva_aiu').val(),
        iva_incluido: $("input[type='checkbox']#iva_incluido").is(':checked') ? '1' : '',
        capturar_documento_descuadrado: $("input[type='checkbox']#capturar_documento_descuadrado_empresa").is(':checked') ? '1' : '',
        vendedores_ventas: $("input[type='checkbox']#vendedores_ventas").is(':checked') ? '1' : '',
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
