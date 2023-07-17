@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Plan de cuentas'])

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
    
    <div class="container-fluid py-4">
        <div class="row">
            <div style="z-index: 9;">
                <button type="button" class="btn btn-success btn-sm" id="createPlanCuenta">Agregar cuenta</button>
            </div>

            <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body">

                    @include('pages.tablas.plan_cuentas.plan_cuentas-table')

                </div>
            </div>
        </div>

        @include('pages.tablas.plan_cuentas.plan_cuentas-form', ['tipoCuenta' => $tipoCuenta])
        
    </div>
@endsection

@push('js')
    <script>
        
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
            pageLength: 15,
            dom: 'ftip',
            paging: true,
            responsive: true,
            processing: true,
            serverSide: true,
            initialLoad: true,
            language: lenguajeDatatable,
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
                        if(row.naturaleza_ingresos){
                            return 'Credito';
                        }
                        return 'Dedito';
                    }
                },
                {
                    "data": function (row, type, set){
                        if(row.naturaleza_egresos){
                            return 'Credito';
                        }
                        return 'Dedito';
                    }
                },
                {
                    "data": function (row, type, set){
                        if(row.naturaleza_compras){
                            return 'Credito';
                        }
                        return 'Dedito';
                    }
                },
                {
                    "data": function (row, type, set){
                        if(row.naturaleza_ventas){
                            return 'Credito';
                        }
                        return 'Dedito';
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
                {
                    "data": function (row, type, set){
                        var html = '';
                        html+= '<span id="editplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info edit-plan-cuentas" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
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
                    swalFire('Creación exitosa', 'Cuenta creado con exito!');
                }
            }).fail((err) => {
                $('#savePlanCuenta').show();
                $('#savePlanCuentaLoading').hide();
                swalFire('Creación herrada', 'Error al crear Cuenta!', false);
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
            $("#naturaleza_cuenta").val(0).change();
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
                    swalFire('Edición exitosa', 'Cuenta actualizada con exito!');
                }
            }).fail((err) => {
                $('#savePlanCuenta').show();
                $('#savePlanCuentaLoading').hide();
                swalFire('Edición herrada', 'Error al actualizat Cuenta!', false);
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
                            swalFire('Eliminación exitosa', 'Plan cuenta eliminado con exito!');
                        } else {
                            swalFire('Eliminación herrada', res.message, false);
                        }
                    }).fail((res) => {
                        swalFire('Eliminación herrada', res.message, false);
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
@endpush
