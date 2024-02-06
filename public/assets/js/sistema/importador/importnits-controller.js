importNits
var import_nits_table = null;
var btn = document.getElementById('actualizarPlantillaNits');

function importnitsInit() {
    import_nits_table = $('#importNits').DataTable({
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
            url: base_url + 'nits-cache-import',
        },
        'rowCallback': function(row, data, index){
            if (data.total_erroes) {
                $('td', row).css('background-color', '#ff00005e');
                return;
            }
        },
        columns: [
            {"data":'id'},
            {"data":'tipo_documento'},
            {"data":'numero_documento'},
            {"data":'digito_verificacion'},
            {"data":'primer_nombre'},
            {"data":'otros_nombres'},
            {"data":'primer_apellido'},
            {"data":'segundo_apellido'},
            {"data":'razon_social'},
            {"data":'direccion'},
            {"data":'email'},
            {"data":'telefono_1'},
            {"data":'plazo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'cupo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'observaciones'},
            {"data":'erroes'},
            {"data":'total_erroes'}
        ]
    });

    import_nits_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#actualizarPlantillaNits').show();
        }
    });
}

$("#form-importador-nits").submit(function(e) {
    e.preventDefault();

    $('#cargarPlantillaNits').hide();
    $('#actualizarPlantillaNits').hide();
    $('#cargarPlantillaNitsLoagind').show();

    import_nits_table.rows().remove().draw();

    var ajxForm = document.getElementById("form-importador-nits");
    var data = new FormData(ajxForm);
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "importnits-importar");
    xhr.send(data);
    xhr.onload = function(res) {
        var responseData = JSON.parse(res.currentTarget.response);
        var errorsMsg = '';
        $('#cargarPlantillaNits').show();
        $('#cargarPlantillaNitsLoagind').hide();

        if (responseData.success) {
            import_nits_table.ajax.reload(function(res) {
                if (res.success && res.data.length) {
                    $('#actualizarPlantillaNits').show();
                }
            });
            agregarToast('exito', 'Datos cargados', 'Precio de productos cargados con exito!', true);
        } else {
            agregarToast('error', 'Carga errada', 'errorsMsg');
        }
    };
    xhr.onerror = function (res) {
        $('#cargarPlantillaNits').hide();
        $('#cargarPlantillaNitsLoagind').show();
    };
    return false;
});

$(document).on('click', '#reloadImportadorNits', function () {
    
    import_nits_table.ajax.reload(function(res) {
        if (res.success && res.data.length) {
            $('#actualizarPlantillaNits').show();
        }
    });
});

$(document).on('click', '#descargarPlantillaNits', function () {
    $.ajax({
        url: 'importnits-exportar',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        window.open(res.url, "_blank");
    }).fail((err) => {
    });
});

btn.addEventListener('click', event => {
    event.preventDefault();

    $('#cargarPlantillaNits').hide();
    $('#actualizarPlantillaNits').hide();
    $('#cargarPlantillaNitsLoagind').show();

    $.ajax({
        method: 'POST',
        url: base_url + 'nits-actualizar-import',
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        $('#cargarPlantillaNits').show();
        $('#actualizarPlantillaNits').hide();
        $('#cargarPlantillaNitsLoagind').hide();
        import_nits_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                $('#actualizarPlantillaNits').show();
            }
        });
        agregarToast('exito', 'Cédulas/Nits importadas', 'Cédulas nits importadas con exito!', true);
    }).fail((err) => {
        $('#cargarPlantillaNits').show();
        $('#actualizarPlantillaNits').show();
        $('#cargarPlantillaNitsLoagind').hide();
        import_nits_table.ajax.reload(function(res) {
            if (res.success && res.data.length) {
                $('#actualizarPlantillaNits').show();
            }
        });
        agregarToast('error', 'Importación de nits errado', '');
    });
});