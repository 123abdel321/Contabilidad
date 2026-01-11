importProductos
var import_productos_table = null;
var channelImportProductos = pusher.subscribe('importador-productos-'+localStorage.getItem("notificacion_code"));

function importproductosInit() {
    console.log('importproductosInit');
    import_productos_table = $('#importProductos').DataTable({
        pageLength: 20,
        dom: 'Brtip',
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
            url: base_url + 'productos-cache-import',
        },
        'rowCallback': function(row, data, index){
        },
        columns: [
            {"data": function (row, type, set){
                if (row.estado == 1) {
                    return `<i class="fas fa-minus-circle" style="color: red; font-size: 14px;"></i>&nbsp;${row.row}`;
                }
                return `<i class="fas fa-check-circle" style="color: #03b403; font-size: 14px;"></i>&nbsp;${row.row}`;
            }},
            {"data":'codigo'},
            {"data":'nombre'},
            {
                "data": 'costo',
                "render": function(data, type, row) {
                    // parseFloat elimina los ceros innecesarios a la derecha
                    return data ? parseFloat(data) : 0;
                }
            },
            {
                "data": 'venta',
                "render": function(data, type, row) {
                    return data ? parseFloat(data) : 0;
                }
            },
            {"data": function (row, type, set){
                if (row.familia) {
                    return `${row.familia.codigo} - ${row.familia.nombre}`;
                }
                return '';
            }},
            {"data": function (row, type, set){
                if (row.bodega) {
                    return `${row.bodega.codigo} - ${row.bodega.nombre}`;
                }
                return '';
            }},
            {
                "data": 'existencias',
                "render": function(data, type, row) {
                    // parseFloat elimina los ceros innecesarios a la derecha
                    return data ? parseFloat(data) : 0;
                }
            },
            {"data":'observacion'}
        ]
    });

    import_productos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#importarProductos').show();
        }
    });
}

$("#form-importador-productos").submit(function(e) {
    e.preventDefault();

    $('#cargarPlantillaProductos').hide();
    $('#importarProductos').hide();
    $('#importarProductosLoading').show();

    import_productos_table.rows().remove().draw();

    var ajxForm = document.getElementById("form-importador-productos");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "productos-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';
        $('#cargarPlantillaProductos').show();
        $('#importarProductosLoading').hide();

        agregarToast('info', 'Cargando plantilla', 'La plantilla de productos se esta cargando, se le notificará cuando haya terminado.', true);
    };
    xhr.onerror = function (res) {
        $('#cargarPlantillaProductos').hide();
        $('#importarProductosLoading').show();
    };
    return false;
});

$(document).on('click', '#reloadImportadorProductos', function () {
    
    import_productos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#importarProductos').show();
        }
    });
});

$(document).on('click', '#descargarPlantillaProductos', function () {
    $.ajax({
        url: 'importproductos-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

$(document).on('click', '#importarProductos', function () {

    $('#cargarPlantillaProductos').hide();
    $('#importarProductos').hide();
    $('#importarProductosLoading').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'productos-cache-actualizar',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        agregarToast('info', 'Importando productos', 'Se le notificará cuando la importación haya terminado!', true);
    }).fail((err) => {
        $('#cargarPlantillaProductos').show();
        $('#importarProductos').show();
        $('#importarProductosLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

channelImportProductos.bind('notificaciones', function(data) {
    import_productos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#importarProductos').show();
        }
    });
    agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
});