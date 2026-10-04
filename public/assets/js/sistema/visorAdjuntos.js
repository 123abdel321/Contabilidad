function abrirVisorArchivosDocumento(relationId, relationType) {

    $('#documentoArchivosLoading').show();
    $('#documentoArchivosEmpty').hide();
    $('#documentoArchivosContent').hide();
    $('#documentoArchivosLista').html('');
    $('#documentoArchivoViewer').hide();
    $('#documentoArchivoViewerContent').html('');

    $('#documentoArchivosModal').modal('show');

    $.ajax({
        url: base_url + 'archivos',
        method: 'GET',
        headers: headers,
        data: {
            relation_id: relationId,
            relation_type: relationType
        },
        dataType: 'json'

    }).done(function (res) {

        $('#documentoArchivosLoading').hide();
        if (!res.archivos || res.archivos.length === 0) {
            $('#documentoArchivosEmpty').show();
            return;
        }

        $('#documentoArchivosContent').show();
        $('#documentoArchivosLista').data('archivos', res.archivos);
        res.archivos.forEach(function (archivo, index) {
            agregarArchivoAlVisor(archivo, index);
        });

        mostrarArchivoDocumento(res.archivos[0]);
        $('.archivo-item-card[data-index="0"]').addClass('border-primary shadow');

    }).fail(function (xhr) {
        $('#documentoArchivosLoading').hide();
        $('#documentoArchivosEmpty').show();
        console.error(xhr);
    });

}

function urlCompletaArchivo(url) {
    if (!url) return '';
    if (/^https?:\/\//i.test(url)) return url;

    const path = url.replace(/^\/+/, '');
    return bucketUrl.replace(/\/+$/, '') + '/' + path;
}

function agregarArchivoAlVisor(archivo, index) {
    const icon = iconoArchivoVisor(archivo.tipo_archivo);
    const nombre = (archivo.url_archivo || '').split('/').pop();
    const url = urlCompletaArchivo(archivo.url_archivo);

    // Detectar si es imagen para poner miniatura
    const esImagen = /^image\//i.test(archivo.tipo_archivo || '') ||
                     /\.(jpg|jpeg|png|webp|gif)$/i.test(nombre);

    const html = `
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border rounded-3 shadow-sm h-100 archivo-item-card"
                 data-index="${index}"
                 style="cursor: pointer; transition: all .15s ease;">

                ${esImagen
                    ? `<img src="${url}"
                            class="card-img-top"
                            style="height: 90px; object-fit: cover; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">`
                    : `<div class="d-flex align-items-center justify-content-center"
                            style="height: 90px; background: #f8f9fa; border-top-left-radius: .5rem; border-top-right-radius: .5rem;">
                           <i class="${icon} fa-2x"></i>
                       </div>`
                }

                <div class="card-body p-2">
                    <div class="text-truncate fw-semibold" style="font-size: 11px;" title="${nombre}">
                        ${nombre}
                    </div>
                    <small class="text-muted" style="font-size: 10px;">
                        ${tamanoLegible(archivo.tamano ?? archivo.size ?? 0)}
                    </small>
                </div>
            </div>
        </div>
    `;

    $('#documentoArchivosLista').append(html);
}

function mostrarArchivoDocumento(archivo) {
    if (!archivo) return;

    const nombre = (archivo.url_archivo || '').split('/').pop();
    const tipo = (archivo.tipo_archivo || '').toLowerCase();
    const icon = iconoArchivoVisor(archivo.tipo_archivo);
    const url = urlCompletaArchivo(archivo.url_archivo);

    // Marcar activo en la lista
    $('.archivo-item-card').removeClass('border-primary shadow');
    $(`.archivo-item-card[data-index="${obtenerIndexArchivo(archivo)}"]`).addClass('border-primary shadow');

    // Header del visor
    $('#documentoArchivoViewerIcon').attr('class', icon);
    $('#documentoArchivoViewerNombre').text(nombre);
    $('#documentoArchivoViewerAbrir').attr('href', url);

    // Contenido según tipo
    let contenido = '';

    if (tipo.includes('pdf') || /\.pdf$/i.test(nombre)) {
        contenido = `<iframe src="${url}"
                             style="width: 100%; height: 100%; border: 0;">
                     </iframe>`;
    } else if (tipo.startsWith('image/') || /\.(jpg|jpeg|png|webp|gif)$/i.test(nombre)) {
        contenido = `<div class="d-flex align-items-center justify-content-center h-100"
                          style="background: #1e1e1e;">
                        <img src="${url}"
                             style="max-width: 100%; max-height: 100%; object-fit: contain;">
                     </div>`;
    } else {
        contenido = `
            <div class="d-flex flex-column justify-content-center align-items-center h-100 text-white">
                <i class="${icon} fa-4x mb-3"></i>
                <div class="mb-3">Vista previa no disponible</div>
                <a href="${url}" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> Abrir en nueva pestaña
                </a>
            </div>`;
    }

    $('#documentoArchivoViewerContent').html(contenido);
    $('#documentoArchivoViewer').show();
}

$(document).on('click', '.archivo-item-card', function () {
    const index = $(this).data('index');
    const archivos = $('#documentoArchivosLista').data('archivos') || [];

    if (archivos[index]) {
        mostrarArchivoDocumento(archivos[index]);
    }
});

function iconoArchivoVisor(tipo) {
    tipo = (tipo || '').toLowerCase();

    if (tipo.includes('pdf')) return 'fas fa-file-pdf text-danger';
    if (tipo.startsWith('image/')) return 'fas fa-file-image text-primary';
    if (tipo.includes('excel') || tipo.includes('sheet') || tipo.includes('spreadsheet')) return 'fas fa-file-excel text-success';
    if (tipo.includes('word') || tipo.includes('document')) return 'fas fa-file-word text-info';
    if (tipo.includes('zip') || tipo.includes('compressed')) return 'fas fa-file-archive text-warning';
    return 'fas fa-file text-secondary';
}

function tamanoLegible(bytes) {
    bytes = parseInt(bytes) || 0;
    if (bytes === 0) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function obtenerIndexArchivo(archivo) {
    const archivos = $('#documentoArchivosLista').data('archivos') || [];
    return archivos.findIndex(a =>
        a.id === archivo.id ||
        a.url_archivo === archivo.url_archivo
    );
}