var comprobante_table = null;

function comprobanteInit() {
    comprobante_table = $('#comprobantesTable').DataTable({
        pageLength: 30,
        dom: 'ti',
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: true,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scroller: {
            displayBuffer: 20,
            rowHeight: 50,
            loadingIndicator: true
        },
        deferRender: true,
        fixedHeader : {
            header : true,
            footer : true,
            headerOffset: 45
        },
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'comprobantes',
        },
        columns: [
            {"data":'codigo'},
            {"data":'nombre'},
            {"data": function (row, type, set){
                switch (row.tipo_comprobante) {
                    case 0:
                        return 'INGRESOS'
                        break;
                    case 1:
                        return 'EGRESOS'
                        break;
                    case 2:
                        return 'COMPRAS'
                        break;
                    case 3:
                        return 'VENTAS'
                        break;
                    case 4:
                        return 'OTROS'
                        break;
                    case 5:
                        return 'CIERRE'
                        break;
                    default:
                        break;
                }
                return '';
            }},
            {"data": function (row, type, set){
                switch (row.tipo_consecutivo) {
                    case 0:
                        return 'ACUMULADO'
                        break;
                    case 1:
                        return 'MENSUAL'
                        break;
                }
                return '';
            }},
            {"data":'consecutivo_siguiente'},
            {"data": function (row, type, set){
                switch (row.imprimir_en_capturas) {
                    case 0:
                        return 'NO'
                        break;
                    case 1:
                        return 'SI'
                        break;
                }
                return '';
            }},
            {"data": function (row, type, set){
                switch (row.tipo_impresion) {
                    case 0:
                        return 'POS'
                        break;
                    case 1:
                        return 'MEDIA CARTA'
                        break;
                    case 2:
                        return 'CARTA'
                        break;
                }
                return '';
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="editcomprobante_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-secondary edit-comprobante" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="deletecomprobante_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-comprobante" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
    
        ]
    });

    if(comprobante_table) {
        comprobante_table.on('click', '.edit-comprobante', function() {
            $("#textComprobanteCreate").hide();
            $("#textComprobanteUpdate").show();
            $("#saveComprobanteLoading").hide();
            $("#updateComprobante").show();
            $("#saveComprobante").hide();
        
            var trComprobante = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, comprobante_table);
            
            $("#id_comprobante_up").val(id);
            $("#codigo").val(data.codigo);
            $("#nombre").val(data.nombre);
            $("#tipo_comprobante").val(data.tipo_comprobante).change();
            $("#tipo_consecutivo").val(data.tipo_consecutivo).change();
            $("#imprimir_en_capturas").val(data.imprimir_en_capturas).change();
            $("#tipo_impresion").val(data.tipo_impresion).change();
            $("#consecutivo_siguiente").val(data.consecutivo_siguiente);
        
            $("#comprobanteFormModal").modal('show');
        });
        
        comprobante_table.on('click', '.drop-comprobante', function() {
            var trComprobante = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, comprobante_table);
            Swal.fire({
                title: 'Eliminar comprobante: '+data.nombre+'?',
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url + 'comprobantes',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            comprobante_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Comprobante eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación herrada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación herrada', res.message);
                    });
                }
            })
        });
    }

    $('.water').hide();
    comprobante_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInput").on("input", function (e) {
    comprobante_table.context[0].jqXHR.abort();
    $('#comprobantesTable').DataTable().search($("#searchInput").val()).draw();
});

$(document).on('click', '#createComprobante', function () {
    clearFormComprobante();
    $("#updateComprobante").hide();
    $("#saveComprobante").show();
    $("#comprobanteFormModal").modal('show');
});

$(document).on('click', '#saveComprobante', function () {

    var form = document.querySelector('#comprobanteForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveComprobanteLoading").show();
    $("#updateComprobante").hide();
    $("#saveComprobante").hide();

    let data = {
        codigo: $("#codigo").val(),
        nombre: $("#nombre").val(),
        tipo_comprobante: $("#tipo_comprobante").val(),
        tipo_consecutivo: $("#tipo_consecutivo").val(),
        imprimir_en_capturas: $("#imprimir_en_capturas").val(),
        tipo_impresion: $("#tipo_impresion").val(),
        consecutivo_siguiente: $("#consecutivo_siguiente").val(),
    }

    $.ajax({
        url: base_url + 'comprobantes',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormComprobante();
            $("#saveComprobante").show();
            $("#saveComprobanteLoading").hide();
            $("#comprobanteFormModal").modal('hide');
            comprobante_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Comprobante creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveComprobante').show();
        $('#saveComprobanteLoading').hide();
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
        agregarToast('error', 'Creación herrada', errorsMsg);
    });
});

function clearFormComprobante(){
    $("#textComprobanteCreate").show();
    $("#textComprobanteUpdate").hide();
    $("#saveComprobanteLoading").hide();

    $("#id_comprobante_up").val('');
    $("#codigo").val('');
    $("#nombre").val('');
    $("#tipo_comprobante").val(0).change();
    $("#tipo_consecutivo").val(0).change();
    $("#imprimir_en_capturas").val(0).change();
    $("#tipo_impresion").val('').change();
    $("#consecutivo_siguiente").val(1);
}

$(document).on('click', '#updateComprobante', function () {

    var form = document.querySelector('#comprobanteForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveComprobanteLoading").show();
    $("#updateComprobante").hide();
    $("#saveComprobante").hide();

    let data = {
        id: $("#id_comprobante_up").val(),
        codigo: $("#codigo").val(),
        nombre: $("#nombre").val(),
        tipo_comprobante: $("#tipo_comprobante").val(),
        tipo_consecutivo: $("#tipo_consecutivo").val(),
        imprimir_en_capturas: $("#imprimir_en_capturas").val(),
        tipo_impresion: $("#tipo_impresion").val(),
        consecutivo_siguiente: $("#consecutivo_siguiente").val(),
    }

    $.ajax({
        url: base_url + 'comprobantes',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
    if(res.success){
        console.log(res.data);
        clearFormComprobante();
        $("#saveComprobante").show();
        $("#saveComprobanteLoading").hide();
        $("#comprobanteFormModal").modal('hide');
        comprobante_table.ajax.reload();
        agregarToast('exito', 'Actualización exitosa', 'Comprobante actualizado con exito!', true );
    }
    }).fail((err) => {
        $('#updateComprobante').show();
        $('#saveComprobanteLoading').hide();
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
        agregarToast('error', 'Error al actualizar Comprobante!', errorsMsg);
    });
});