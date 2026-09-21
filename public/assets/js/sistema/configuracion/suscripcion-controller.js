var componentes_table = null;
var $comboComponenteSuscripcion = null;

function suscripcionInit() {
    cargarTablasComponentes();
    cargarCombosComponentes();
}

function cargarTablasComponentes() {
    componentes_table = $('#componentesSuscripcionTable').DataTable({
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
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'suscripcion-componentes',
            data: function (d) {
                delete d.columns;
            }
        },
        rowCallback: function(row, data, index){
            if (data.id == 'TOTAL') {
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
            }
        },
        columns: [
            {"data": function (row, type, set){  
                if (row.id == 'TOTAL') {
                    return 'TOTAL';
                }
                if (row.componente) {
                    return `${row.componente.nombre}`
                }
                return '';
            }},
            {"data":'precio', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                if (row.cantidad) {
                    return row.cantidad
                }
                return 1;
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'fecha_inicio_suscripcion'},
            {"data":'fecha_siguiente_cobro'},
            {"data": function (row, type, set){  
                if (row.descuento) {
                    return row.descuento
                }
                return 0;
            }, render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){
                if (row.id == 'TOTAL') {
                    return;
                }
                var html = '';
                if (true) html+= `<span id="editcomponentesuscripcion_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-componente-suscripcion" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                if (true) html+= `<span id="deletecomponentesuscripcion_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-componente-suscripcion" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                return html;
            }},
        ]
    });

    if (componentes_table) {
        componentes_table.on('click', '.edit-componente-suscripcion', function() {
            $("#textComponentesCreate").hide();
            $("#textComponentesUpdate").show();
            $("#saveComponentesLoading").hide();
            $("#updateComponentes").show();
            $("#saveComponentes").hide();
        
            var id = this.id.split('_')[1];
            var data = getDataById(id, componentes_table);
            console.log('data: ',data);
            $("#id_componentes_up").val(data.id);
            $("#precio_componente_suscripcion").val(data.precio);
            $("#fecha_componente_suscripcion").val(data.fecha_siguiente_cobro);
            $("#descuento_componente_suscripcion").val(0);

            if(data.componente){
                var dataComponente = {
                    id: data.componente.id,
                    text: data.componente.nombre + ' - ' + data.componente.precio
                };
                var newOption = new Option(dataComponente.text, dataComponente.id, false, false);
                $comboComponenteSuscripcion.append(newOption).val(dataComponente.id).trigger('change');
            }
        
            $("#componentesSuscripcionFormModal").modal('show');
        });
    }

    componentes_table.ajax.reload();
}

function cargarCombosComponentes() {
    $comboComponenteSuscripcion = $('#id_componente_suscripcion').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownParent: $('#componentesSuscripcionFormModal'),
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/suscripcion-combos',
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

function clearFormComponentesSuscripcion() {
    const dateNowSuscripcion = new Date();
    $("#saveComponentes").show();
    $("#updateComponentes").hide();
    $("#saveComponentesLoading").hide();

    $("#textComponentesUpdate").hide();
    $("#textComponentesCreate").show();

    $("#id_componente_suscripcion").val("").trigger('change');
    $("#precio_componente_suscripcion").val(0);
    $("#fecha_componente_suscripcion").val(dateNowSuscripcion.getFullYear()+'-'+("0" + (dateNowSuscripcion.getMonth() + 1)).slice(-2)+'-'+("0" + dateNow.getDate()).slice(-2));
    $("#descuento_componente_suscripcion").val(0);

    if (esDios) {
        $("#precio_componente_suscripcion").prop('disabled', false);
        $("#descuento_componente_suscripcion").prop('disabled', false);
    } else {
        $("#precio_componente_suscripcion").prop('disabled', true);
        $("#descuento_componente_suscripcion").prop('disabled', true);
    }
}

function guardarComponenteSuscripcion() {
    const form = document.querySelector('#componentesForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        const firstInvalidInput = form.querySelector(':invalid');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
        }
        return;
    }

    $("#saveComponentes").hide();
    $("#updateComponentes").hide();
    $("#saveComponentesLoading").show();
    
    let data = getDataComponentes();

    $.ajax({
        url: base_url + 'suscripcion-componentes',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormComponentesSuscripcion();
            $("#saveComponentes").show();
            $("#saveComponentesLoading").hide();
            $("#componentesSuscripcionFormModal").modal('hide');
            componentes_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Componente agregado con exito!', true);
        }
    }).fail((err) => {
        $('#saveComponentes').show();
        $('#saveComponentesLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getDataComponentes() {
    return {
        id: $("#id_componentes_up").val(),
        id_componente: $("#id_componente_suscripcion").val(),
        precio: $("#precio_componente_suscripcion").val(),
        fecha_inicio: $("#fecha_componente_suscripcion").val(),
        descuento: $("#descuento_componente_suscripcion").val(),
    }
}

$(document).on('click', '#componentesSuscripcionCreate', function () {
    clearFormComponentesSuscripcion();
    $("#componentesSuscripcionFormModal").modal('show');
});

$(document).on('click', '#saveComponentes', function () {
    guardarComponenteSuscripcion();
});

$("#id_componente_suscripcion").on('change', function(event) {
    let data = $(this).select2('data');
    if(data.length <= 0) return;
    
    data = data[0];
    $("#precio_componente_suscripcion").val(data.precio);
});