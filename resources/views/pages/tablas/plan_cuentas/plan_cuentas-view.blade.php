<style>
    .error {
        color: red;
    }
    .edit-comprobante {
        width: 10px;
    }
    .drop-comprobante {
        width: 10px;
    }
    .fa-comprobante {
        margin-left: -5px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="col-12 col-md-4 col-sm-4">
                <button type="button" class="btn btn-primary btn-sm" id="createPlanCuenta">Agregar cuenta</button>
            </div>
            <div class="col-12 col-md-8 col-sm-8">
                <input type="text" id="searchInput" class="form-control form-control-sm search-table" placeholder="Buscar">
            </div>
        </div>

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.plan_cuentas.plan_cuentas-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.plan_cuentas.plan_cuentas-form', ['tipoCuenta' => $tipoCuenta])
    
</div>

<script>

    $('.form-control').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });
    
    var $validator = $('#planCuentaForm').validate({
        rules: {
            nombre: {
                required: true,
                minlength: 2,
                maxlength: 100,
            },
            naturaleza_cuenta: {
                required: true
            }
        },
        messages: {
            nombre: {
                required: "El campo nombre es requerido",
                minlength: "El campo nombre debe tener minimo 2 caracteres",
                maxlength: "El campo nombre debe tener maximo 100 caracteres",
            },
            naturaleza_cuenta: {
                required: "El campo Naturaleza es requerido",
            }
        },

        highlight: function(element) {
            $(element).closest('.form-control').removeClass('is-valid').addClass('is-invalid');
        },
        success: function(element) {
            $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
        }
    });

    var plan_cuentas_table = $('#planCuentaTable').DataTable({
        pageLength: 30,
        dom: 'ti',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
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
        'rowCallback': function(row, data, index){
            if(data.cuenta.auxiliar){
                return;
            }
            if(data.cuenta.length == 1){
                $('td', row).css('background-color', 'rgb(64 164 209 / 80%)');
                return;
            }
            if(data.cuenta.length == 2){
                $('td', row).css('background-color', 'rgb(64 164 209 / 50%)');
                return;
            }
            if(data.cuenta.length == 4){
                $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
                return;
            }
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'plan-cuenta',
        },
        columns: [
            {"data":'cuenta'},
            {"data":'nombre'},
            {
                "data": function (row, type, set){
                    if(row.naturaleza_cuenta){
                        return 'Credito';
                    }
                    return 'Dedito';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_ingresos == 1){
                        return 'Credito';
                    } else if (row.naturaleza_ingresos == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_egresos == 1){
                        return 'Credito';
                    } else if (row.naturaleza_egresos == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_compras == 1){
                        return 'Credito';
                    } else if (row.naturaleza_compras == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.naturaleza_ventas == 1){
                        return 'Credito';
                    } else if (row.naturaleza_ventas == 0) {
                        return 'Dedito';
                    }
                    return '';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_nit){
                        return 'Si';
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_documento_referencia){
                        return 'Si';
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_concepto){
                        return 'Si';
                    }
                    return 'No';
                }
            },
            {
                "data": function (row, type, set){
                    if(row.exige_centro_costos){
                        return 'Si';
                    }
                    return 'No';
                }
            },
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
                    html+= '<span id="editplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-secondary edit-plan-cuentas" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                    html+= '<span id="deleteplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-plan-cuentas" style="margin-bottom: 0rem !important">Eliminar</span>';
                    return html;
                }
            }
        ]
    });
    
    $(document).on('click', '#createPlanCuenta', function () {
        clearFormPlanCuenta();
        $("#updatePlanCuenta").hide();
        $("#savePlanCuenta").show();
        $("#planCuentaFormModal").modal('show');
    });

    $("#searchInput").on("input", function (e) {
        plan_cuentas_table.context[0].jqXHR.abort();
        $('#planCuentaTable').DataTable().search($("#searchInput").val()).draw();
    });

    $(document).on('click', '#savePlanCuenta', function () {

        var $valid = $('#planCuentaForm').valid();
        if (!$valid) {
            $validator.focusInvalid();
            return false;
        }

        $("#savePlanCuentaLoading").show();
        $("#updatePlanCuenta").hide();
        $("#savePlanCuenta").hide();

        let data = {
            id_padre: $("#id_padre").val(),
            cuenta: $("#cuenta").val(),
            nombre: $("#nombre").val(),
            id_tipo_cuenta: $("#id_tipo_cuenta").val(),
            naturaleza_cuenta: $("#naturaleza_cuenta").val(),
            naturaleza_ingresos: $("#naturaleza_ingresos").val(),
            naturaleza_egresos: $("#naturaleza_egresos").val(),
            naturaleza_ventas: $("#naturaleza_ventas").val(),
            naturaleza_compras: $("#naturaleza_compras").val(),
            exige_nit: $("#exige_nit:checked").val() ? 1 : 0,
            exige_documento_referencia: $("#exige_documento_referencia:checked").val() ? 1 : 0,
            exige_concepto: $("#exige_concepto:checked").val() ? 1 : 0,
            exige_centro_costos: $("#exige_centro_costos:checked").val() ? 1 : 0,
        }

        $.ajax({
            url: base_url + 'plan-cuenta',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                clearFormPlanCuenta();
                $("#savePlanCuenta").show();
                $("#updatePlanCuenta").hide();
                $("#savePlanCuentaLoading").hide();
                $("#planCuentaFormModal").modal('hide');
                plan_cuentas_table.row.add(res.data).draw();
                agregarToast('exito', 'Creación exitosa', 'Cuenta creado con exito!', true);
            }
        }).fail((err) => {
            $('#savePlanCuenta').show();
            $('#savePlanCuentaLoading').hide();
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

    function clearFormPlanCuenta(){
        $("#textPlanCuentaCreate").show();
        $("#textPlanCuentaUpdate").hide();
        $("#savePlanCuentaLoading").hide();

        $("#text_cuenta_padre").val('');
        $("#id_cuenta").val('');
        $("#id_padre").val(0).change();
        $("#cuenta").val('');
        $("#nombre").val('');
        $("#naturaleza_cuenta").val('').change();
        $("#naturaleza_ingresos").val('').change();
        $("#naturaleza_egresos").val('').change();
        $("#naturaleza_compras").val('').change();
        $("#naturaleza_ventas").val('').change();
        $("#id_tipo_cuenta").val('').change();
        $("#exige_nit").prop( "checked", false );
        $("#exige_documento_referencia").prop( "checked", false );
        $("#exige_concepto").prop( "checked", false );
        $("#exige_centro_costos").prop( "checked", false );

    }

    plan_cuentas_table.on('click', '.edit-plan-cuentas', function() {
        $("#textPlanCuentaCreate").hide();
        $("#textPlanCuentaUpdate").show();
        $("#savePlanCuentaLoading").hide();
        $("#updatePlanCuenta").show();
        $("#savePlanCuenta").hide();

        var trPlanCuenta = $(this).closest('tr');
        var id = this.id.split('_')[1];
        var data = getDataById(id, plan_cuentas_table);
        if(data.padre){
            var dataCuenta = {
                id: data.padre.id,
                text: data.padre.cuenta + ' - ' + data.padre.nombre
            };
            var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
            $comboPadre.append(newOption).trigger('change');
            $comboPadre.val(dataCuenta.id).trigger('change');
            $("#text_cuenta_padre").val(data.padre.cuenta);
            $("#cuenta").val(data.cuenta.slice(data.padre.cuenta.length));
        }else{ 
            $("#text_cuenta_padre").val('');
            $comboPadre.val('').trigger('change');
            $("#cuenta").val(data.cuenta);
        }
        $("#id_cuenta").val(data.id);
        $("#nombre").val(data.nombre);
        $("#naturaleza_cuenta").val(data.naturaleza_cuenta).change();
        $("#naturaleza_ingresos").val(data.naturaleza_ingresos).change();
        $("#naturaleza_egresos").val(data.naturaleza_egresos).change();
        $("#naturaleza_compras").val(data.naturaleza_compras).change();
        $("#naturaleza_ventas").val(data.naturaleza_ventas).change();
        $("#id_tipo_cuenta").val(data.id_tipo_cuenta).change();
        
        $("#exige_nit").prop( "checked", data.exige_nit == 1 ? true : false );
        $("#exige_documento_referencia").prop( "checked", data.exige_documento_referencia == 1 ? true : false );
        $("#exige_concepto").prop( "checked", data.exige_concepto == 1 ? true : false );
        $("#exige_centro_costos").prop( "checked", data.exige_centro_costos == 1 ? true : false );

        $("#planCuentaFormModal").modal('show');
    });

    $(document).on('click', '#updatePlanCuenta', function () {

        var $valid = $('#planCuentaForm').valid();
        if (!$valid) {
            $validator.focusInvalid();
            return false;
        }
        
        $("#savePlanCuentaLoading").show();
        $("#updatePlanCuenta").hide();
        $("#savePlanCuenta").hide();

        let data = {
            id_cuenta: $("#id_cuenta").val(),
            id_padre: $("#id_padre").val(),
            cuenta: $("#cuenta").val(),
            nombre: $("#nombre").val(),
            naturaleza_cuenta: $("#naturaleza_cuenta").val(),
            id_tipo_cuenta: $("#id_tipo_cuenta").val(),
            naturaleza_cuenta: $("#naturaleza_cuenta").val(),
            naturaleza_ingresos: $("#naturaleza_ingresos").val(),
            naturaleza_egresos: $("#naturaleza_egresos").val(),
            naturaleza_ventas: $("#naturaleza_ventas").val(),
            naturaleza_compras: $("#naturaleza_compras").val(),
            exige_nit: $("#exige_nit:checked").val() ? 1 : 0,
            exige_documento_referencia: $("#exige_documento_referencia:checked").val() ? 1 : 0,
            exige_concepto: $("#exige_concepto:checked").val() ? 1 : 0,
            exige_centro_costos: $("#exige_centro_costos:checked").val() ? 1 : 0,
        }

        $.ajax({
            url: base_url + 'plan-cuenta',
            method: 'PUT',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                clearFormPlanCuenta();
                $("#savePlanCuenta").show();
                $("#updatePlanCuenta").hide();
                $("#savePlanCuentaLoading").hide();
                $("#planCuentaFormModal").modal('hide');
                plan_cuentas_table.row.add(res.data).draw();
                agregarToast('exito', 'Actualización exitosa', 'Cuenta actualizada con exito!', true );
            }
        }).fail((err) => {
            $('#savePlanCuenta').show();
            $('#savePlanCuentaLoading').hide();
            agregarToast('error', 'Error al actualizar Cuenta!', errorsMsg);
        });
    });

    plan_cuentas_table.on('click', '.drop-plan-cuentas', function() {
        var trPlanCuenta = $(this).closest('tr');
        var id = this.id.split('_')[1];
        var data = getDataById(id, plan_cuentas_table);
        Swal.fire({
            title: 'Eliminar cuenta: '+data.nombre+'?',
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
                    url: base_url + 'plan-cuenta',
                    method: 'DELETE',
                    data: JSON.stringify({id: id}),
                    headers: headers,
                    dataType: 'json',
                }).done((res) => {
                    if(res.success){
                        plan_cuentas_table.row(trPlanCuenta).remove().draw();
                        agregarToast('exito', 'Eliminación exitosa', 'Plan cuenta eliminado con exito!', true );
                    } else {
                        agregarToast('error', 'Eliminación herrada', res.message);
                    }
                }).fail((res) => {
                    agregarToast('error', 'Eliminación herrada', res.message);
                });
            }
        })
    });

    $("#id_padre").on('change', function(e) {
        var data = $(this).select2('data');
        if(data.length){
            $("#text_cuenta_padre").val(data[0].cuenta);
        }
    });

    var $comboPadre = $('#id_padre').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#planCuentaFormModal'),
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

</script>
