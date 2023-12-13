
function entornoInit() {

    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        if (variable.nombre == 'iva_incluido') {
            if (variable.valor == '1') $('#iva_incluido').prop('checked', true);
            else $('#iva_incluido').prop('checked', false);
            continue;
        }

        if (variable.nombre == 'capturar_documento_descuadrado') {
            if (variable.valor == '1') $('#capturar_documento_descuadrado').prop('checked', true);
            else $('#capturar_documento_descuadrado').prop('checked', false);
            continue;
        }

        if (variable.nombre == 'valor_uvt') {
            $('#valor_uvt').val(variable.valor);
            continue;
        }
    }
}

$(document).on('click', '#updateEntorno', function () {
    $("#updateEntornoLoading").show();
    $("#updateEntorno").hide();

    let data = {
        valor_uvt: $('#valor_uvt').val(),
        iva_incluido: $("input[type='checkbox']#iva_incluido").is(':checked') ? '1' : '',
        capturar_documento_descuadrado: $("input[type='checkbox']#capturar_documento_descuadrado_empresa").is(':checked') ? '1' : '',
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
        $("#updateEmpresaLoading").hide();
            $("#updateEmpresa").show();
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
