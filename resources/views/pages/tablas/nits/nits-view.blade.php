@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Nits'])

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
                <button type="button" class="btn btn-success btn-sm" id="createNits">Agregar nit</button>
            </div>

            <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body">
                    
                    @include('pages.tablas.nits.nits-table')

                </div>
            </div>
        </div>

        @include('pages.tablas.nits.nits-form')
        
    </div>
@endsection

@push('js')
    <script>

        var $validator = $('#nitsForm').validate({
            rules: {
                id_tipo_documento: {
                    required: true
                },
                numero_documento: {
                    required: true,
                    minlength: 2,
                    maxlength: 100,
                },
                direccion: {
                    required: true,
                    minlength: 2,
                    maxlength: 100,
                },
                numero_documento: {
                    required: true,
                    minlength: 2,
                    maxlength: 100,
                },
            },
            messages: {
                id_tipo_cuenta: {
                    required: "El campo Tipo documento es requerido",
                },
                numero_documento: {
                    required: "El campo Numero documento es requerido",
                    minlength: "El campo Numero documento debe tener minimo 2 caracteres",
                    maxlength: "El campo Numero documento debe tener maximo 100 caracteres",
                },
                tipo_contribuyente: {
                    required: "El campo Tipo contribuyente es requerido",
                },
                direccion: {
                    required: "El campo Dirección documento es requerido",
                    minlength: "El campo Dirección documento debe tener minimo 2 caracteres",
                    maxlength: "El campo Dirección documento debe tener maximo 100 caracteres",
                },
            },

            highlight: function(element) {
                $(element).closest('.form-control').removeClass('is-valid').addClass('is-invalid');
            },
            success: function(element) {
                $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
            }
        });

        var nits_table = $('#nitTable').DataTable({
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
                url: base_url + 'nit',
            },
            columns: [
                {"data":'numero_documento'},
                {
                    "data": function (row, type, set){
                        if(row.tipo_documento){
                            return row.tipo_documento.nombre;
                        }
                        return '';
                    }
                },
                {
                    "data": function (row, type, set){
                        if(row.tipo_contribuyente == 1){
                            return 'Persona jurídica';
                        } else if(row.tipo_contribuyente == 2) {
                            return 'Persona natural';
                        }
                        return '';
                    }
                },
                {"data":'primer_apellido'},
                {"data":'segundo_apellido'},
                {"data":'primer_nombre'},
                {"data":'otros_nombres'},
                {"data":'razon_social'},
                {"data":'direccion'},
                {"data":'email'},
                {
                    "data": function (row, type, set){
                        var html = '';
                        html+= '<span id="editplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info edit-nits" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                        html+= '<span id="deleteplancuentas_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-nits" style="margin-bottom: 0rem !important">Eliminar</span>';
                        return html;
                    }
                }
            ]
        });

        $(document).on('click', '#createNits', function () {
            clearFormNits();

            $("#updateNit").hide();
            $("#saveNit").show();
            $("#nitFormModal").modal('show');
        });

        $(document).on('click', '#updateNit', function () {
            var $valid = $('#nitsForm').valid();
            if (!$valid) {
                $validator.focusInvalid();
                return false;
            }

            $("#saveNitLoading").show();
            $("#updateNit").hide();
            $("#saveNit").hide();

            let data = {
                id: $("#id_nit").val(),
                id_tipo_documento: $("#id_tipo_documento").val(),
                numero_documento: $("#numero_documento").val(),
                tipo_contribuyente: $("#tipo_contribuyente").val(),
                primer_apellido: $("#primer_apellido").val(),
                segundo_apellido: $("#segundo_apellido").val(),
                primer_nombre: $("#primer_nombre").val(),
                otros_nombres: $("#otros_nombres").val(),
                razon_social: $("#razon_social").val(),
                direccion: $("#direccion").val(),
                email: $("#email").val(),
            }

            $.ajax({
                url: base_url + 'nit',
                method: 'PUT',
                data: data,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    clearFormNits();
                    $("#saveNit").show();
                    $("#updateNit").hide();
                    $("#saveNitLoading").hide();
                    $("#nitFormModal").modal('hide');
                    nits_table.row.add(res.data).draw();
                    swalFire('Edición exitosa', 'Nit actualizado con exito!');
                }
            }).fail((err) => {
                $('#saveNit').show();
                $('#updateNit').hide();
                $("#saveNitLoading").hide();
                swalFire('Edición herrada', 'Error al actualizar Nit!', false);
            });
        });

        $(document).on('click', '#saveNit', function () {

            var $valid = $('#nitsForm').valid();
            if (!$valid) {
                $validator.focusInvalid();
                return false;
            }

            $("#saveNitLoading").show();
            $("#updateNit").hide();
            $("#saveNit").hide();

            let data = {
                id_tipo_documento: $("#id_tipo_documento").val(),
                numero_documento: $("#numero_documento").val(),
                tipo_contribuyente: $("#tipo_contribuyente").val(),
                primer_apellido: $("#primer_apellido").val(),
                segundo_apellido: $("#segundo_apellido").val(),
                primer_nombre: $("#primer_nombre").val(),
                otros_nombres: $("#otros_nombres").val(),
                razon_social: $("#razon_social").val(),
                direccion: $("#direccion").val(),
                email: $("#email").val(),
            }

            $.ajax({
                url: base_url + 'nit',
                method: 'POST',
                data: JSON.stringify(data),
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                if(res.success){
                    clearFormNits();
                    $("#saveNit").show();
                    $("#updateNit").hide();
                    $("#saveNitLoading").hide();
                    $("#nitFormModal").modal('hide');
                    nits_table.row.add(res.data).draw();
                    swalFire('Creación exitosa', 'Nit creado con exito!');
                }
            }).fail((err) => {
                $('#saveNit').show();
                $('#saveNitLoading').hide();
                swalFire('Creación herrada', 'Error al crear Nit!', false);
            });
        });

        nits_table.on('click', '.edit-nits', function() {
            
            $("#textNitCreate").hide();
            $("#textNitUpdate").show();
            $("#saveNitLoading").hide();
            $("#updateNit").show();
            $("#saveNit").hide();

            var trNit = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, nits_table);

            if(data.tipo_documento){
                var dataCuenta = {
                    id: data.tipo_documento.id,
                    text: data.tipo_documento.codigo + ' - ' + data.tipo_documento.nombre
                };
                var newOption = new Option(dataCuenta.text, dataCuenta.id, false, false);
                $comboTipoDocumento.append(newOption).trigger('change');
                $comboTipoDocumento.val(dataCuenta.id).trigger('change');
            }

            $("#id_nit").val(data.id);
            $("#numero_documento").val(data.numero_documento);
            $("#tipo_contribuyente").val(data.tipo_contribuyente).change();
            $("#primer_apellido").val(data.primer_apellido);
            $("#segundo_apellido").val(data.segundo_apellido);
            $("#primer_nombre").val(data.primer_nombre);
            $("#otros_nombres").val(data.otros_nombres);
            $("#razon_social").val(data.razon_social);
            $("#direccion").val(data.direccion);
            $("#email").val(data.email);

            $("#nitFormModal").modal('show');
        });

        nits_table.on('click', '.drop-nits', function() {
            var trNit = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, nits_table);
            Swal.fire({
                title: 'Eliminar documento: '+data.numero_documento+'?',
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
                        url: base_url + 'nit',
                        method: 'DELETE',
                        data: {id: id},
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            nits_table.row(trNit).remove().draw();
                            swalFire('Eliminación exitosa', 'Nit eliminado con exito!');
                        } else {
                            swalFire('Eliminación herrada', res.message, false);
                        }
                    }).fail((res) => {
                        swalFire('Eliminación herrada', res.message, false);
                    });
                }
            })
        });

        function clearFormNits(){
            $("#textNitCreate").show();
            $("#textNitUpdate").hide();
            $("#saveNitLoading").hide();

            $("#id_tipo_documento").val('').change();
            $("#numero_documento").val('');
            $("#tipo_contribuyente").val('').change();
            $("#primer_apellido").val('');
            $("#segundo_apellido").val('');
            $("#primer_nombre").val('');
            $("#otros_nombres").val('');
            $("#razon_social").val('');
            $("#direccion").val('');
            $("#email").val('');
        }

        var $comboTipoDocumento = $('#id_tipo_documento').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#nitFormModal'),
            delay: 250,
            ajax: {
                url: 'api/nit/combo-tipo-documento',
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
