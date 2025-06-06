var periodo_table = null;

function periodosInit() {

    cargarTablasPeriodos();

    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });

    $('.water').hide();
}

function cargarTablasPeriodos() {
    periodo_table = $('#periodosTable').DataTable({
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
            url: base_url + 'periodos',
        },
        columns: [
            {"data":'nombre'},
            {"data":'dias_salario'},
            {"data":'horas_dia'},
            {"data": function (row, type, set){  
                if (row.tipo_dia_pago) {
                    return 'CALENDARIO';
                }
                return 'ORDINAL';
            }},
            {"data":'periodo_dias_ordinales'},
            {"data":'periodo_dias_calendario'},
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
                    if (editarPeriodos) html+= `<span id="editperiodos_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-periodo" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    if (eliminarPeriodos) html+= `<span id="deleteperiodos_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-periodo" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    if (periodo_table) {
        periodo_table.on('click', '.drop-periodo', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, periodo_table);

            Swal.fire({
                title: `Eliminar periodo: ${data.nombre}?`,
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
                        url: base_url + 'periodos',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            periodo_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Periodo eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        periodo_table.on('click', '.edit-periodo', function() {

            $("#textPeriodosCreate").hide();
            $("#textPeriodosUpdate").show();
            $("#savePeriodosLoading").hide();
            $("#updatePeriodos").show();
            $("#savePeriodos").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, periodo_table);

            $("#id_periodos_up").val(data.id);
            $("#nombre_periodo").val(data.nombre);
            $("#dias_salario").val(data.dias_salario);
            $("#horas_dia").val(data.horas_dia);
            $("#horas_periodo").val(data.horas_periodo);
            $("#tipo_dia_pago").val(data.tipo_dia_pago).trigger('change');
            $("#periodo_dias_ordinales").val(data.periodo_dias_ordinales);
            $("#periodo_dias_calendario").val(data.periodo_dias_calendario);

            changeTipoDiaPago();
            actualizaHorasPeriodo();
        
            $("#periodosFormModal").modal('show');

            setTimeout(function(){
                document.getElementById('nombre_periodo').focus();
            },500);
        });
    }
    periodo_table.ajax.reload();
}

function clearFormPeriodos() {
    $("#textPeriodosCreate").show();
    $("#textPeriodosUpdate").hide();
    $("#savePeriodos").show();
    $("#updatePeriodos").hide();
    $("#savePeriodosLoading").hide();

    $("#id_periodos_up").val('');
    $("#nombre_periodo").val('');
    $("#dias_salario").val('');
    $("#horas_dia").val('');
    $("#horas_periodo").val('');
    $("#tipo_dia_pago").val('').trigger('change');
    $("#periodo_dias_calendario").val('');
}

function actualizaHorasPeriodo() {
    const horasDia = $("#horas_dia").val();
    const diasSalario = $("#dias_salario").val();
    const horasPeriodo = diasSalario && horasDia ? diasSalario * horasDia : 0;

    $("#horas_periodo").val(horasPeriodo);
}

function enterPeriodo(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        const id_periodo = $("#id_periodos_up").val();
        if (id_periodo) actualizarPeriodo();
        else  guardarPeriodo();
    }
}

function guardarPeriodo() {
    var form = document.querySelector('#periodosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#savePeriodos").hide();
    $("#updatePeriodos").hide();
    $("#savePeriodosLoading").show();
    
    let data = {
        nombre: $("#nombre_periodo").val(),
        dias_salario: $("#dias_salario").val(),
        horas_dia: $("#horas_dia").val(),
        horas_periodo: $("#horas_periodo").val(),
        tipo_dia_pago: $("#tipo_dia_pago").val(),
        periodo_dias_ordinales: $("#periodo_dias_ordinales").val(),
        periodo_dias_calendario: $("#periodo_dias_calendario").val(),
    }

    $.ajax({
        url: base_url + 'periodos',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPeriodos();
            $("#savePeriodos").show();
            $("#savePeriodosLoading").hide();
            $("#periodosFormModal").modal('hide');
            periodo_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Periodo creado con exito!', true);
        }
    }).fail((err) => {
        $('#savePeriodos').show();
        $('#savePeriodosLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function actualizarPeriodo() {
    var form = document.querySelector('#periodosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        return;
    }

    $("#savePeriodos").hide();
    $("#updatePeriodos").hide();
    $("#savePeriodosLoading").show();

    let data = {
        id: $("#id_periodos_up").val(),
        nombre: $("#nombre_periodo").val(),
        dias_salario: $("#dias_salario").val(),
        horas_dia: parseInt($("#horas_dia").val() ?? 0),
        horas_periodo: $("#horas_periodo").val(),
        tipo_dia_pago: parseInt($("#tipo_dia_pago").val() ?? 0 ),
        periodo_dias_ordinales: $("#periodo_dias_ordinales").val(),
        periodo_dias_calendario: $("#periodo_dias_calendario").val(),
    }

    $.ajax({
        url: base_url + 'periodos',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormPeriodos();
            $("#savePeriodos").show();
            $("#savePeriodosLoading").hide();
            $("#periodosFormModal").modal('hide');
            periodo_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Periodo actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#updatePeriodos').show();
        $('#savePeriodosLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
}

function changeTipoDiaPago() {
    const tipo_dia_pago = $("#tipo_dia_pago").val();

    $("#input-periodo_dias_calendario").hide();
    $("#input-periodo_dias_ordinales").hide();

    if (tipo_dia_pago == "0") {
        $("#input-periodo_dias_ordinales").show();
        document.getElementById('periodo_dias_ordinales').focus();
    } else if (tipo_dia_pago == "1") {
        $("#input-periodo_dias_calendario").show();
        document.getElementById('periodo_dias_calendario').focus();
    }
}

$(document).on('click', '#createPeriodos', function () {
    clearFormPeriodos();
    $("#periodosFormModal").modal('show');
    setTimeout(function(){
        document.getElementById('nombre_periodo').focus();
    },500);
});

$(document).on('click', '#savePeriodos', function () {
    guardarPeriodo();
});

$(document).on('click', '#updatePeriodos', function () {
    actualizarPeriodo();
});
