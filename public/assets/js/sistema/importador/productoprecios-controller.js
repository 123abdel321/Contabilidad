var productos_precios_table = null;
const btn = document.getElementById('actualizarPrecios');

function productopreciosInit() {
    productos_precios_table = $('#importProductoPrecios').DataTable({
        pageLength: 15,
        dom: '',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'producto-precio-cache-import',
        },
        columns: [
            {"data":'row'},
            {"data": function (row, type, set){  
                if (row.estado == 2) {
                    return `<span class="badge badge-primary">
                        <i class="fas fa-check-circle fa-lg" style="color: lightgreen;"></i>
                        <label>Para actualizar</label>
                    </span>`
                } else if (row.estado == 1) {
                    return `<span class="badge badge-primary">
                        <i class="fas fa-equals fa-lg" style="color: #ffd600;"></i>
                        <label>Sin cambios</label>
                    </span>`
                }

                return `<span class="badge badge-primary">
                        <i class="fas fa-exclamation-circle fa-lg" style="color: red;"></i>
                        <label>Con errores</label>
                    </span>`
            }},
            {"data":'observacion'},
            {"data":'codigo'},
            {"data":'nombre'},
            {"data": function (row, type, set){
                if (row.estado == 2) {
                    if (row.precio_inicial > row.producto.precio_inicial) {
                        return `<label style="margin-top: -5px; position: fixed;">${row.precio_inicial}&nbsp;<i class="fas fas fa-sort-up" style="color: lightgreen; vertical-align: sub;"></i></label><br>
                                <label style="margin-top: -10px; font-size: 11px; position: fixed; margin-left: 10px; font-weight: 500;">
                                    ${row.producto.precio_inicial}
                                </label>`;
                    } else {
                        return `<label style="margin-top: -5px; position: fixed;">${row.precio_inicial}&nbsp;<i class="fas fa-sort-down" style="color: red; -webkit-writing-mode: horizontal-tb;"></i></label><br>
                                <label style="margin-top: -10px; font-size: 11px; position: fixed; margin-left: 10px; font-weight: 500;">
                                    ${row.producto.precio_inicial}
                                </label>`;
                    }
                }
                return row.precio;
            }},
            {"data": function (row, type, set){
                if (row.estado == 2) {
                    if (row.precio > row.producto.precio) {
                        return `<label style="margin-top: -5px; position: fixed;">${row.precio}&nbsp;<i class="fas fas fa-sort-up" style="color: lightgreen; vertical-align: sub;"></i></label><br>
                                <label style="margin-top: -10px; font-size: 11px; position: fixed; margin-left: 10px; font-weight: 500;">
                                    ${row.producto.precio}
                                </label>`;
                    } else {
                        return `<label style="margin-top: -5px; position: fixed;">${row.precio}&nbsp;<i class="fas fa-sort-down" style="color: red; -webkit-writing-mode: horizontal-tb;"></i></label><br>
                                <label style="margin-top: -10px; font-size: 11px; position: fixed; margin-left: 10px; font-weight: 500;">
                                    ${row.producto.precio}
                                </label>`;
                    }
                }
                return '';
            }}
        ]
    });
}

$("#form-producto-precios").submit(function(e) {
    e.preventDefault();

    $('#cargarPlantilla').hide();
    $('#actualizarPrecios').hide();
    $('#cargarPlantillaLoagind').show();

    productos_precios_table.rows().remove().draw();

    var ajxForm = document.getElementById("form-producto-precios");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "productoprecios-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';
        console.log('responseData: ',responseData);
        $('#cargarPlantilla').show();
        $('#cargarPlantillaLoagind').hide();

        if (responseData.success) {
            productos_precios_table.ajax.reload();
            $('#actualizarPrecios').show();
            agregarToast('exito', 'Datos cargados', 'Precio de productos cargados con exito!', true);
        } else {
            if (responseData.data.length > 0) {
                for (let index = 0; index < responseData.data.length; index++) {
                    var element = responseData.data[index];
                    for (campo in element.errors) {
                        errorsMsg += "Fila "+element.row+"- "+element.errors[campo]+" <br>";
                    }
                }
                agregarToast('error', 'Carga errada', errorsMsg);
                return;
            } else if (typeof responseData.message === 'object'){
                for (field in responseData.message) {
                    var errores = responseData.message[field];
                    for (campo in errores) {
                        errorsMsg += "- "+errores[campo]+" <br>";
                    }
                };
                agregarToast('error', 'Carga errada', errorsMsg);
                return;
            }
        }
    };
    xhr.onerror = function (res) {
        console.log('res erres: ',res);
        $('#cargarPlantilla').hide();
        $('#cargarPlantillaLoagind').show();
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
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

btn.addEventListener('click', event => {

    $('#cargarPlantilla').hide();
    $('#actualizarPrecios').hide();
    $('#cargarPlantillaLoagind').show();

    event.preventDefault();
    $.ajax({
        method: 'POST',
        url: base_url + 'producto-precio-actualizar',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#cargarPlantilla').show();
        $('#actualizarPrecios').hide();
        $('#cargarPlantillaLoagind').hide();
        productos_precios_table.ajax.reload();
        agregarToast('exito', 'Productos actualizados', 'Precio de productos actualizados con exito!', true);
    }).fail((err) => {
        $('#cargarPlantilla').show();
        $('#actualizarPrecios').show();
        $('#cargarPlantillaLoagind').hide();
        agregarToast('error', 'Actualización de precios errada', '');
    });
});