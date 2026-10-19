var import_documentos_table = null;
var inputImportadorDocumentos = document.getElementById('importador_documentos');
var channelImportDocumentos = pusher.subscribe('importador-documentos-'+localStorage.getItem("notificacion_code"));

function importdocumentosInit() {
    import_documentos_table = $('#importDocumentos').DataTable({
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
            url: base_url + 'documentos-cache-import',
        },
        columns: [
            {"data": function (row, type, set){
                if (row.errores) {
                    return `<span class="badge bg-danger" style="font-size: 11px; padding: 4px 8px;">
                                <i class="fas fa-exclamation-circle"></i> Fila ${row.total_errores}: Errores
                            </span>`;
                }
                
                return `<span class="badge bg-success" style="font-size: 11px; padding: 4px 8px;">
                            <i class="fas fa-check"></i> Fila ${row.total_errores}: Listo
                        </span>`;
            }},
            {"data":'nombre_nit'},
            {"data":'nombre_cuenta'},
            {"data":'nombre_cecos'},
            {"data":'nombre_comprobante'},
            {"data":'consecutivo'},
            {"data":'documento_referencia'},
            {"data":'fecha_manual'},
            {"data":'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'concepto'},
            {"data":'errores'},
            {"data":'total_errores'}
        ]
    });

    import_documentos_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#importarDocumentos').prop('disabled', false);
        } else {
            $('#importarDocumentos').prop('disabled', true);
        }
    });

    $("#importador_documentos").on('change', function(event) {
        if ($("#importador_documentos").val()) {
            $('#cargarPlantillaDocumentos').prop('disabled', false);
        } else {
            $('#cargarPlantillaDocumentos').prop('disabled', true);
        }
    });
}

$(document).on('click', '#cargarPlantillaDocumentos', function () {
    $('#cargarPlantillaDocumentos').hide();
    $('#cargarPlantillaDocumentosLoading').show();
    
    // Mostrar la barra de progreso
    $('#uploadStatusDocumentos').show();
    
    var ajxForm = document.getElementById("form-importador-documentos");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    
    xhr.open("POST", "importdocumentos-importar");
    xhr.send(data);
    
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        if (responseData.success) {
            // La barra ya se mostrará con los eventos de progreso
        } else {
            $('#cargarPlantillaDocumentos').show();
            $('#cargarPlantillaDocumentosLoading').hide();
            $('#uploadStatusDocumentos').hide();
            var mensaje = responseData.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Carga errada', errorsMsg);
        }
    };
    
    xhr.onerror = function (res) {
        $('#cargarPlantillaDocumentos').show();
        $('#cargarPlantillaDocumentosLoading').hide();
        $('#uploadStatusDocumentos').hide();
        var responseData = JSON.parse(res.currentTarget.response);
        var mensaje = responseData.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Carga errada', errorsMsg);
    };
    
    return false;
});

$(document).on('click', '#descargarPlantillaDocumentos', function () {
    $.ajax({
        url: 'importdocumentos-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

$(document).on('click', '#importarDocumentos', function () {
    $('#importarDocumentos').hide();
    $('#importarDocumentosLoading').show();

    // Mostrar la barra de progreso
    $('#uploadStatusDocumentos').show();
    // Resetear la barra a 0% y cambiar el mensaje
    $('#uploadProgressDocumentos').css('width', '0%').removeClass('bg-success').addClass('progress-bar-striped progress-bar-animated bg-primary');
    $('#progressTextDocumentos').text('0%');
    $('#statusMessageDocumentos').text('Iniciando carga de productos al sistema...');
    $('#processedRowsDocumentos').text('0');

    $.ajax({
        method: 'POST',
        url: base_url + 'documentos-cache-actualizar',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
    }).fail((err) => {
        $('#importarDocumentos').show();
        $('#uploadStatusDocumentos').hide();
        $('#importarDocumentosLoading').hide();        

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

channelImportDocumentos.bind('notificaciones', function(data) {
    // Si es un evento de progreso
    if (data.name === 'progress') {
        // Actualizar la barra de progreso y el mensaje
        $('#uploadProgressDocumentos').css('width', data.progress + '%');
        $('#progressTextDocumentos').text(data.progress + '%');
        $('#statusMessageDocumentos').text(data.mensaje);
        $('#processedRowsDocumentos').text(data.processed);
        $('#totalRowsDocumentos').text(data.total);
        
        // Cambiar el color de la barra según el stage
        if (data.stage === 'completed') {
            $('#uploadProgressDocumentos').removeClass('progress-bar-striped progress-bar-animated').addClass('bg-success');
            
            // Ocultar la barra después de 5 segundos
            setTimeout(() => {
                $('#uploadStatusDocumentos').slideUp();
            }, 5000);

            $("#cargarPlantillaDocumentos").show();
            $("#cargarPlantillaDocumentosLoading").hide();
            $("#importarDocumentos").show();
            $("#importarDocumentosLoading").hide();
            
            // Recargar la tabla de productos importados
            if (import_documentos_table) {
                import_documentos_table.ajax.reload(function(res) {
                    if (res.success && res.data.length) {
                        $('#importarDocumentos').prop('disabled', false);
                    } else {
                        $('#importarDocumentos').prop('disabled', true);
                    }
                });
            }
        }

        if (data.stage === 'completed_with_errors') {
            $('#uploadProgressDocumentos')
                .removeClass('progress-bar-striped progress-bar-animated bg-primary')
                .addClass('bg-danger');

            // Renderizar errores
            mostrarErroresImportacionDocumentos(data.errores || [], data.mensaje);

            // Restaurar botones
            $('#cargarPlantillaDocumentos').show();
            $('#cargarPlantillaDocumentosLoading').hide();
            $('#importarDocumentos').show();
            $('#importarDocumentosLoading').hide();

            // Recargar tabla para que las filas fallidas sigan visibles
            if (import_documentos_table) {
                import_documentos_table.ajax.reload();
            }
        }

        if (data.stage === 'error') {
            $('#uploadProgressDocumentos')
                .removeClass('progress-bar-striped progress-bar-animated bg-primary')
                .addClass('bg-danger');

            // Restaurar botones para que el usuario pueda corregir y reintentar
            $('#cargarPlantillaDocumentos').show();
            $('#cargarPlantillaDocumentosLoading').hide();
            $('#importarDocumentos').show();
            $('#importarDocumentosLoading').hide();

            // Recargar tabla para no perder las filas
            if (import_documentos_table) {
                import_documentos_table.ajax.reload();
            }

            // Ocultar la barra después de 8 segundos
            setTimeout(() => {
                $('#uploadStatusDocumentos').slideUp();
            }, 8000);
        }
    } 
    // Si es el evento final de importación (el antiguo 'carga' o el nuevo 'import')
    else if (data.name === 'carga' || data.name === 'import') {
        // Recargar la tabla
        if (import_documentos_table) {
            import_documentos_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    $('#importarDocumentos').prop('disabled', false);
                } else {
                    $('#importarDocumentos').prop('disabled', true);
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
        $('#importarDocumentosLoading').hide();
        $('#importarDocumentos').show();
    }
});

function mostrarErroresImportacionDocumentos(errores, mensajeGeneral) {
    if (!errores || !errores.length) {
        agregarToast('error', 'Carga con errores', mensajeGeneral || 'Se encontraron errores.', true);
        return;
    }

    // Construir HTML de detalle
    let html = `<div style="font-size: 13px; text-align:left;">
                  <p style="margin-bottom: 10px;"><strong>${mensajeGeneral || ''}</strong></p>
                  <p style="margin-bottom: 10px;">Documentos con errores: <b>${errores.length}</b></p>`;

    errores.forEach((doc, idx) => {
        html += `<div style="background:#fff; border-radius:6px; padding:8px 10px; margin-bottom:8px; border-left:4px solid #dc3545;">
                    <div style="font-weight:bold; color:#b02a37;">
                        Documento #${idx + 1} — ${doc.comprobante || ''} · cons. ${doc.consecutivo || ''} · ${doc.fecha || ''}
                    </div>
                    <div style="margin-left:8px; color:#555;">${doc.detalle || ''}</div>
                </div>`;
    });

    if (data.truncado) {
        html += `<p style="color:#666; font-size:12px;"><em>Se muestran solo ${errores.length} de ${data.total_errores} documentos con error.</em></p>`;
    }
    html += `</div>`;

    // Si tienes SweetAlert, mostrar modal; si no, fallback al toast
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Errores en la importación',
            html: html,
            icon: 'error',
            width: 700,
            showCancelButton: false,
            confirmButtonText: 'Entendido',
            didOpen: () => {
                const htmlContainer = document.querySelector('.swal2-html-container');
                if (htmlContainer) {
                    htmlContainer.style.maxHeight = '60vh';
                    htmlContainer.style.overflowY = 'auto';
                    htmlContainer.style.textAlign = 'left';
                }
            }
        });
    } else {
        // Fallback: al menos avisar al usuario
        console.error('Errores de importación:', errores);
        agregarToast(
            'error',
            'Carga con errores',
            `${errores.length} documento(s) con errores. Revisa la consola para el detalle.`,
            true
        );
    }
}