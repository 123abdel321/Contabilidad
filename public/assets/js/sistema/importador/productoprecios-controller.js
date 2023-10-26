
function productopreciosInit() {
    // console.log('productoPreciosInit');
}

$("#form-producto-precios").submit(function(e) {
    e.preventDefault();

    $('#cargarPlantilla').hide();
    $('#cargarPlantillaLoagind').show();

    var ajxForm = document.getElementById("form-producto-precios");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "productoprecios-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';

        $('#cargarPlantilla').show();
        $('#cargarPlantillaLoagind').hide();

        if (responseData.success) {
            agregarToast('exito', 'Actualizacion exitosa', 'Precio de productos actualizados con exito!', true);
        } else {
            if (responseData.data.length > 0) {
                for (let index = 0; index < responseData.data.length; index++) {
                    var element = responseData.data[index];
                    for (campo in element.errors) {
                        errorsMsg += "Fila "+element.row+"- "+element.errors[campo]+" <br>";
                    }
                }
                agregarToast('error', 'Creación errada', errorsMsg);
                return;
            } else if (typeof responseData.message === 'object'){
                for (field in responseData.message) {
                    var errores = responseData.message[field];
                    for (campo in errores) {
                        errorsMsg += "- "+errores[campo]+" <br>";
                    }
                };
                agregarToast('error', 'Creación errada', errorsMsg);
                return;
            }
        }
    };
    return false;
});

$(document).on('click', '#descargarPlantilla', function () {
    $.ajax({
        url: 'productoprecios-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        // console.log('res: ',res);
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});