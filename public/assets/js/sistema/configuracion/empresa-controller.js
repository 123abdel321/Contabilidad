
function empresaInit() {
    $('#id_responsabilidades').select2({
        theme: 'bootstrap-5',
    });
    
    $("#id_responsabilidades").val(
        datosEmpresa.codigos_responsabilidades.split(',')
    ).change();
}

$("#tipo_contribuyente_empresa").on('change', function(e) {
    var tipoContribuyente = $("#tipo_contribuyente_empresa").val();

    if (tipoContribuyente == 1) {
        $("#primer_nombre_empresa").prop('required',false);
        $("#otros_nombres_empresa").prop('required',false);
        $("#primer_apellido_empresa").prop('required',false);
        $("#segundo_apellido_empresa").prop('required',false);
        $("#razon_social_empresa").prop('required',true);
    } else {
        $("#primer_nombre_empresa").prop('required',true);
        $("#otros_nombres_empresa").prop('required',false);
        $("#primer_apellido_empresa").prop('required',true);
        $("#segundo_apellido_empresa").prop('required',false);
        $("#razon_social_empresa").prop('required',false);
    }

    $("#numero_documento").prop('required',false);
    $("#direccion_empresa").prop('required',false);
    $("#telefono_empresa").prop('required',false);

    var form = document.querySelector('#empresaForm');

    form.checkValidity();
});

$(document).on('click', '#updateEmpresa', function () {

    var form = document.querySelector('#empresaForm');

    if(!form.checkValidity()) form.classList.add('was-validated');

    $("#updateEmpresaLoading").show();
    $("#updateEmpresa").hide();

    let data = {
        'razon_social': $('#razon_social_empresa').val(),
        'nit': $('#nit_empresa').val(),
        'dv': $('#dv_empresa').val(),
        'direccion': $('#direccion_empresa').val(),
        'telefono': $('#telefono_empresa').val(),
        'tipo_contribuyente': $('#tipo_contribuyente_empresa').val(),
        'razon_social': $('#razon_social_empresa').val(),
        'primer_nombre': $('#primer_nombre_empresa').val(),
        'otros_nombres': $('#otros_nombres_empresa').val(),
        'primer_apellido': $('#primer_apellido_empresa').val(),
        'segundo_apellido': $('#segundo_apellido_empresa').val(),
        'id_responsabilidades': $('#id_responsabilidades').val(),
        'capturar_documento_descuadrado': $("input[type='checkbox']#capturar_documento_descuadrado_empresa").is(':checked') ? '1' : '',
    }

    $.ajax({
        url: base_url + 'empresa',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#updateEmpresaLoading").hide();
            $("#updateEmpresa").show();
            agregarToast('exito', 'Actualización exitosa', 'Datos de empresa actualizados con exito!', true);
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