var import_documentos_table = null;
var btnImportDocumento = document.getElementById('actualizarPlantillaDocumentos');

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
        'rowCallback': function(row, data, index){
            if (parseInt(data.total_errores)) {
                $('td', row).css('background-color', '#ff00005e');
                return;
            }
        },
        columns: [
            {"data":'id'},
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
            $('#actualizarPlantillaDocumentos').show();
        }
    });
}

$("#form-importador-documentos").submit(function(event) {
    event.preventDefault();

    $('#cargarPlantillaDocumentos').hide();
    $('#actualizarPlantillaDocumentos').hide();
    $('#cargarPlantillaDocumentosLoagind').show();

    import_documentos_table.rows().remove().draw();

    var ajxForm = document.getElementById("form-importador-documentos");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "importdocumentos-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';
        $('#cargarPlantillaDocumentos').show();
        $('#cargarPlantillaDocumentosLoagind').hide();

        if (responseData.success) {
            import_documentos_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    $('#actualizarPlantillaDocumentos').show();
                }
            });
            agregarToast('exito', 'Datos cargados', 'Precio de productos cargados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }
    };
    xhr.onerror = function (res) {
        $('#cargarPlantillaDocumentos').hide();
        $('#cargarPlantillaDocumentosLoagind').show();
    };
    return false;
});

$(document).on('click', '#reloadImportadorDocumentos', function () {
    $("#reloadImportadorDocumentosIconNormal").hide();
    $("#reloadImportadorDocumentosIconLoading").show();
    $.ajax({
        url: base_url + 'documentos-validar-import',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $("#reloadImportadorDocumentosIconNormal").show();
        $("#reloadImportadorDocumentosIconLoading").hide();
        import_documentos_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                $('#actualizarPlantillaDocumentos').show();
            }
        });
    }).fail((err) => {
        $("#reloadImportadorDocumentosIconNormal").show();
        $("#reloadImportadorDocumentosIconLoading").hide();
    });
    
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

btnImportDocumento.addEventListener('click', event => {
    event.preventDefault();

    $('#cargarPlantillaDocumentos').hide();
    $('#actualizarPlantillaDocumentos').hide();
    $('#cargarPlantillaDocumentosLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'documentos-actualizar-import',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#cargarPlantillaDocumentos').show();
        $('#actualizarPlantillaDocumentos').hide();
        $('#cargarPlantillaDocumentosLoagind').hide();
        import_documentos_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                $('#actualizarPlantillaDocumentos').show();
            }
        });
        agregarToast('exito', 'Documentos importadas', 'Documentos importadas con exito!', true);
    }).fail((err) => {
        $('#cargarPlantillaDocumentos').show();
        $('#actualizarPlantillaDocumentos').show();
        $('#cargarPlantillaDocumentosLoagind').hide();
        import_documentos_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                $('#actualizarPlantillaDocumentos').show();
            }
        });
        agregarToast('error', 'Importación de documentos errado', '');
    });
});