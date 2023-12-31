var fondoSistema = null;

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

function readURLFonsoSistema(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            fondoSistema = e.target.result;
            $('#empresa_fondo_sistema').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);

        $('#default_fondo_sistema').hide();
        $('#empresa_fondo_sistema').show();
    }
}

$(document).on('click', '#updateEmpresa', function () {

    var form = document.querySelector('#empresaForm');

    if(!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $("#updateEmpresaLoading").show();
    $("#updateEmpresa").hide();

    let data = {
        nit: $('#nit_empresa').val(),
        dv: $('#dv_empresa').val(),
        direccion: $('#direccion_empresa').val(),
        telefono: $('#telefono_empresa').val(),
        tipo_contribuyente: $('#tipo_contribuyente_empresa').val(),
        razon_social: $('#razon_social_empresa').val(),
        primer_nombre: $('#primer_nombre_empresa').val(),
        otros_nombres: $('#otros_nombres_empresa').val(),
        primer_apellido: $('#primer_apellido_empresa').val(),
        segundo_apellido: $('#segundo_apellido_empresa').val(),
        fecha_ultimo_cierre: $('#fecha_ultimo_cierre').val(),
        id_responsabilidades: $('#id_responsabilidades').val(),
        fondo_imagen : fondoSistema,
    }

    $.ajax({
        url: base_url + 'empresa',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            localStorage.setItem("fondo_sistema", res.fondo_sistema);
            $("#updateEmpresaLoading").hide();
            $("#updateEmpresa").show();

            setTimeout(function(){
                $(".fondo-sistema").css('background-image', 'url(' +bucketUrl + res.fondo_sistema+ ')');
            },300);
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

function seleccionarEmpresa(hash) {

    $.ajax({
        url: base_url + 'seleccionar-empresa',
        method: 'POST',
        data: JSON.stringify({empresa: hash}),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            localStorage.setItem("empresa_nombre", res.empresa.razon_social);
            localStorage.setItem("empresa_logo", res.empresa.logo);
            localStorage.setItem("notificacion_code", res.notificacion_code);
            location.reload();
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
}