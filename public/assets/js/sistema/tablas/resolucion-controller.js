var fechaResolucion = null;
var resoluciones_table = null;
var $comboComprobanteResolucion = null;

function resolucionInit() {
    
    resoluciones_table = $('#resolucionesTable').DataTable({
        pageLength: 15,
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
            url: base_url + 'resoluciones',
        },
        columns: [
            {"data":'nombre'},
            {"data":'prefijo'},
            {"data": function (row, type, set){
                if(!row.comprobante){
                    return '';
                }
                return row.comprobante.codigo + ' - ' +row.comprobante.nombre;
            }},
            {"data":'consecutivo'},
            {"data":'numero_resolucion'},
            {"data": function (row, type, set){
                if (row.tipo_resolucion == 1) return 'Facturacion electronica';
                if (row.tipo_resolucion == 2) return 'Nota debito';
                if (row.tipo_resolucion == 3) return 'Nota credito';
                if (row.tipo_resolucion == 4) return 'Documento Equivalente/Soporte';
                return 'POS';
            }},
            {"data":'fecha'},
            {"data":'vigencia'},
            {"data":'consecutivo_desde'},
            {"data":'consecutivo_hasta'},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_creacion+'</div>';
                if(!row.created_by && !row.fecha_creacion) return '';
                if(!row.created_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_creacion+'</div>';
                return html;
            }},
            {"data": function (row, type, set){
                var html = '<div class="button-user" onclick="showUser('+row.updated_by+',`'+row.fecha_edicion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
                if(!row.updated_by && !row.fecha_edicion) return '';
                if(!row.updated_by) html = '<div class=""><i class="fas fa-user-times icon-user-none"></i>'+row.fecha_edicion+'</div>';
                return html;
            }},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span id="editresolucion_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-success edit-resolucion" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="deleteresolucion_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-resolucion" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },
        ]
    });

    if(resoluciones_table) {
        resoluciones_table.on('click', '.edit-resolucion', function() {
            $("#textResolucionesCreate").hide();
            $("#textResolucionesUpdate").show();
            $("#saveResolucionesLoading").hide();
            $("#updateResoluciones").show();
            $("#saveResoluciones").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, resoluciones_table);

            if(data.comprobante){
                var dataComprobante = {
                    id: data.comprobante.id,
                    text: data.comprobante.codigo + ' - ' + data.comprobante.nombre
                };
                var newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
                $comboComprobanteResolucion.append(newOption).trigger('change');
                $comboComprobanteResolucion.val(dataComprobante.id).trigger('change');
            }

            $("#id_resoluciones_up").val(data.id);
            $("#nombre_resolucion").val(data.nombre);
            $("#prefijo_resolucion").val(data.prefijo);
            $("#consecutivo_resolucion").val(data.consecutivo);
            $("#numero_resolucion_resolucion").val(data.numero_resolucion);
            $("#tipo_impresion_resolucion").val(data.tipo_impresion);
            $("#tipo_resolucion_resolucion").val(data.tipo_resolucion);
            $("#fecha_resolucion").val(data.fecha);
            $("#vigencia_resolucion").val(data.vigencia);
            $("#consecutivo_desde_resolucion").val(data.consecutivo_desde);
            $("#consecutivo_hasta_resolucion").val(data.consecutivo_hasta);

            $("#resolucionesFormModal").modal('show');
        });

        resoluciones_table.on('click', '.drop-resolucion', function() {
            var trNit = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, resoluciones_table);

            Swal.fire({
                title: 'Eliminar resolucion: '+data.nombre+'?',
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
                        url: base_url + 'resoluciones',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            resoluciones_table.row(trNit).remove().draw();
                            agregarToast('exito', 'Eliminación exitosa', 'Resolución eliminada con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
    }

    if(!$comboComprobanteResolucion) {
        $comboComprobanteResolucion = $('#id_comprobante_resolucion').select2({
            theme: 'bootstrap-5',
            delay: 250,
            ajax: {
                url: 'api/comprobantes/combo-comprobante',
                headers: headers,
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: data.data
                    };
                }
            }
        });
    }

    $('.water').hide();
    resoluciones_table.ajax.reload();
}

$('.form-control').keyup(function() {
    $(this).val($(this).val().toUpperCase());
});

$("#searchInputResolucion").on("input", function (e) {
    resoluciones_table.context[0].jqXHR.abort();
    $('#resolucionesTable').DataTable().search($("#searchInputResolucion").val()).draw();
});

$(document).on('click', '#createResoluciones', function () {
    clearFormResoluciones();
    $("#updateResoluciones").hide();
    $("#saveResoluciones").show();
    $("#resolucionesFormModal").modal('show');
});

function clearFormResoluciones(){
    fechaResolucion = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    $("#textResolucionesCreate").show();
    $("#textResolucionesUpdate").hide();
    $("#saveResolucionesLoading").hide();

    $("#id_resolucion_up").val('');
    $("#id_comprobante_resolucion").val(0).change();
    $("#nombre_resolucion").val('');
    $("#prefijo_resolucion").val('');
    $("#consecutivo_resolucion").val('');
    $("#numero_resolucion_resolucion").val('');
    $("#tipo_resolucion_resolucion").val('');
    $("#fecha_resolucion").val(fechaResolucion);
    $("#vigencia_resolucion").val('');
    $("#consecutivo_desde_resolucion").val('');
    $("#consecutivo_hasta_resolucion").val('');
}

$(document).on('click', '#saveResoluciones', function () {
    var form = document.querySelector('#resolucionesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveResolucionesLoading").show();
    $("#updateResoluciones").hide();
    $("#saveResoluciones").hide();

    let data = {
        id_comprobante: $("#id_comprobante_resolucion").val(),
        nombre: $("#nombre_resolucion").val(),
        prefijo: $("#prefijo_resolucion").val(),
        numero_resolucion: $("#numero_resolucion_resolucion").val(),
        tipo_resolucion: $("#tipo_resolucion_resolucion").val(),
        tipo_impresion: $("#tipo_impresion_resolucion").val(),
        fecha: $("#fecha_resolucion").val(),
        vigencia: parseInt($("#vigencia_resolucion").val()),
        consecutivo: parseInt($("#consecutivo_resolucion").val()),
        consecutivo_desde: parseInt($("#consecutivo_desde_resolucion").val()),
        consecutivo_hasta: parseInt($("#consecutivo_hasta_resolucion").val()),
    };

    $.ajax({
        url: base_url + 'resoluciones',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormResoluciones();
            $("#saveResoluciones").show();
            $("#updateResoluciones").hide();
            $("#saveResolucionesLoading").hide();
            $("#resolucionesFormModal").modal('hide');
            resoluciones_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Resolución creada con exito!', true);
        }
    }).fail((err) => {
        $('#saveResoluciones').show();
        $('#saveResolucionesLoading').hide();
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });
});

$(document).on('click', '#updateResoluciones', function () {
    var form = document.querySelector('#resolucionesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#saveResolucionesLoading").show();
    $("#updateResoluciones").hide();
    $("#saveResoluciones").hide();

    let data = {
        id: $("#id_resoluciones_up").val(),
        id_comprobante: $("#id_comprobante_resolucion").val(),
        nombre: $("#nombre_resolucion").val(),
        prefijo: $("#prefijo_resolucion").val(),
        numero_resolucion: $("#numero_resolucion_resolucion").val(),
        tipo_resolucion: $("#tipo_resolucion_resolucion").val(),
        tipo_impresion: $("#tipo_impresion_resolucion").val(),
        fecha: $("#fecha_resolucion").val(),
        vigencia: parseInt($("#vigencia_resolucion").val()),
        consecutivo: parseInt($("#consecutivo_resolucion").val()),
        consecutivo_desde: parseInt($("#consecutivo_desde_resolucion").val()),
        consecutivo_hasta: parseInt($("#consecutivo_hasta_resolucion").val()),
    };

    $.ajax({
        url: base_url + 'resoluciones',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormResoluciones();
            $("#saveResoluciones").hide();
            $("#updateResoluciones").show();
            $("#saveResolucionesLoading").hide();
            $("#resolucionesFormModal").modal('hide');
            resoluciones_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Resolución actualizada con exito!', true);
        }
    }).fail((err) => {
        $('#updateResoluciones').show();
        $('#saveResolucionesLoading').hide();
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
        agregarToast('error', 'Error al actualizar resolución!', errorsMsg);
    });
});
