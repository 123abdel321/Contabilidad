var import_productos_table = null;
var inputImportadorProductos = document.getElementById('importador_productos');
var channelImportProductos = pusher.subscribe('importador-productos-'+localStorage.getItem("notificacion_code"));

function importproductosInit() {
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
                    return `<span class="badge bg-danger" style="font-size: 11px; padding: 4px 8px;">
                                <i class="fas fa-exclamation-circle"></i> Fila ${row.row}: Errores
                            </span>`;
                }
                
                return `<span class="badge bg-success" style="font-size: 11px; padding: 4px 8px;">
                            <i class="fas fa-check"></i> Fila ${row.row}: Listo
                        </span>`;
            }},
            {"data":'codigo'},
            {"data":'nombre'},
            {
                "data": 'costo',
                "className": 'dt-body-right',
                "render": function(data, type, row) {
                    if (data == null || data === '') return '0';
                    let numero = parseFloat(data);

                    return numero.toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 5 
                    });
                }
            },
            {
                "data": 'venta',
                "className": 'dt-body-right',
                "render": function(data, type, row) {
                    if (data == null || data === '') return '0';
                    let numero = parseFloat(data);

                    return numero.toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 5 
                    });
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
                "className": 'dt-body-right',
                "render": function(data, type, row) {
                    if (data == null || data === '') return '0';
                    let numero = parseFloat(data);

                    return numero.toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 5 
                    });
                }
            },
            {"data":'observacion'}
        ]
    });

    import_productos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#importarProductos').prop('disabled', false);
        } else {
            $('#importarProductos').prop('disabled', true);
        }
    });
}

inputImportadorProductos.addEventListener('change', function () {
    if ($("#importador_productos").val()) {
        $('#cargarPlantillaProductos').prop('disabled', false);
    } else {
        $('#cargarPlantillaProductos').prop('disabled', true);
    }
});

$(document).on('click', '#cargarPlantillaProductos', function () {
    $('#cargarPlantillaProductos').hide();
    $('#cargarPlantillaProductosLoading').show();
    
    // Mostrar la barra de progreso
    $('#uploadStatus').show();
    
    // Resto del código que ya tienes...
    var ajxForm = document.getElementById("form-importador-productos");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    
    xhr.open("POST", "productos-importar");
    xhr.send(data);
    
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        if (responseData.success) {
            // La barra ya se mostrará con los eventos de progreso
        } else {
            $('#cargarPlantillaProductos').show();
            $('#cargarPlantillaProductosLoading').hide();
            $('#uploadStatus').hide();
            var mensaje = responseData.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Carga errada', errorsMsg);
        }
    };
    
    xhr.onerror = function (res) {
        $('#cargarPlantillaProductos').show();
        $('#cargarPlantillaProductosLoading').hide();
        $('#uploadStatus').hide();
        var responseData = JSON.parse(res.currentTarget.response);
        var mensaje = responseData.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Carga errada', errorsMsg);
    };
    
    return false;
});

$(document).on('click', '#descargarPlantillaProductos', function () {
    $.ajax({
        url: 'productos-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

$(document).on('click', '#importarProductos', function () {
    $('#importarProductos').hide();
    $('#importarProductosLoading').show();

    // Mostrar la barra de progreso
    $('#uploadStatus').show();
    // Resetear la barra a 0% y cambiar el mensaje
    $('#uploadProgress').css('width', '0%').removeClass('bg-success').addClass('progress-bar-striped progress-bar-animated bg-primary');
    $('#progressText').text('0%');
    $('#statusMessage').text('Iniciando carga de productos al sistema...');
    $('#processedRows').text('0');
    // No tenemos el total aún, pero podemos poner un placeholder
    // En el evento de progreso, el backend nos enviará el total.

    $.ajax({
        method: 'POST',
        url: base_url + 'productos-cache-actualizar',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        // No mostramos toast, porque la barra de progreso ya está mostrando el estado
        // El evento de progreso se encargará de actualizar la barra
    }).fail((err) => {
        $('#importarProductos').show();
        $('#importarPlantillaProductosLoading').hide();
        // Ocultar la barra de progreso en caso de error
        $('#uploadStatus').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

channelImportProductos.bind('notificaciones', function(data) {
    
    // Si es un evento de progreso
    if (data.name === 'progress') {
        // Actualizar la barra de progreso y el mensaje
        $('#uploadProgress').css('width', data.progress + '%');
        $('#progressText').text(data.progress + '%');
        $('#statusMessage').text(data.mensaje);
        $('#processedRows').text(data.processed);
        $('#totalRows').text(data.total);
        
        // Cambiar el color de la barra según el stage
        if (data.stage === 'completed') {
            $('#uploadProgress').removeClass('progress-bar-striped progress-bar-animated').addClass('bg-success');
            
            // Ocultar la barra después de 5 segundos
            setTimeout(() => {
                $('#uploadStatus').slideUp();
            }, 5000);

            $("#cargarPlantillaProductos").show();
            $("#cargarPlantillaProductosLoading").hide();
            $("#importarProductos").show();
            $("#importarProductosLoading").hide();
            
            // Recargar la tabla de productos importados
            if (import_productos_table) {
                import_productos_table.ajax.reload(function(res) {
                    if (res.success && res.data.length) {
                        $('#importarProductos').prop('disabled', false);
                    } else {
                        $('#importarProductos').prop('disabled', true);
                    }
                });
            }
        }
    } 
    // Si es el evento final de importación (el antiguo 'carga' o el nuevo 'import')
    else if (data.name === 'carga' || data.name === 'import') {
        // Recargar la tabla
        if (import_productos_table) {
            import_productos_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    $('#importarProductos').prop('disabled', false);
                } else {
                    $('#importarProductos').prop('disabled', true);
                }
            });
        }
        
        // Mostrar notificación (toast) solo si es el evento 'carga' (para mantener compatibilidad)
        if (data.name === 'carga') {
            agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
        }
        
        // Si es el evento 'import', no mostramos toast porque ya se mostró en el progreso
        // Pero si quieres mostrar un toast final, descomenta la siguiente línea:
        // agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
        
        // Ocultar el loading del botón de importar
        $('#importarProductosLoading').hide();
        $('#importarProductos').show();
    }
});