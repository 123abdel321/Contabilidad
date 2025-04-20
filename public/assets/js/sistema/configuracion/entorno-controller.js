
function entornoInit() {

    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        var checksEntorno = [
            'iva_incluido',
            'capturar_documento_descuadrado',
            'vendedores_ventas',
            'ubicacion_maximoph',
            'fecha_ultimo_cierre',
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
    }
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
