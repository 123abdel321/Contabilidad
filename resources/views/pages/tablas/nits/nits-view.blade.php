@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Cedulas Nits'])

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
            <div class="row" style="z-index: 9;">
                <div class="col-4 col-md-4 col-sm-4">
                    <button type="button" class="btn btn-success btn-sm" id="createNits">Agregar nit</button>
                </div>
                <div class="col-8 col-md-8 col-sm-8">
                    <input type="text" id="searchInput" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
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

        var nits_table = $('#nitTable').DataTable({
            pageLength: 15,
            dom: 'tip',
            paging: true,
            responsive: true,
            processing: true,
            serverSide: true,
            initialLoad: true,
            bFilter: true,
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
                        var primer_nombre = row.primer_nombre ? row.primer_nombre+' ' : '';
                        var otros_nombres = row.otros_nombres ? row.otros_nombres+' ' : '';
                        var primer_apellido = row.primer_apellido ? row.primer_apellido+' ' : '';
                        var segundo_apellido = row.segundo_apellido ? row.segundo_apellido+' ' : '';
                        return primer_nombre+otros_nombres+primer_apellido+segundo_apellido;
                    }
                },
                {"data":'razon_social'},
                {"data":'direccion'},
                {"data":'email'},
                {"data":'telefono_1'},
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

        $("#searchInput").on("input", function (e) {
            nits_table.context[0].jqXHR.abort();
            $('#nitTable').DataTable().search($("#searchInput").val()).draw();
        });

        $(document).on('click', '#createNits', function () {
            clearFormNits();

            $("#updateNit").hide();
            $("#saveNit").show();
            $("#nitFormModal").modal('show');
        });

        $("#tipo_contribuyente").on('change', function(e) {
            var tipoContribuyente = $("#tipo_contribuyente").val();

            if (tipoContribuyente == 1) {
                $("#primer_nombre").prop('required',false);
                $("#otros_nombres").prop('required',false);
                $("#primer_apellido").prop('required',false);
                $("#segundo_apellido").prop('required',false);
                $("#razon_social").prop('required',true);
            } else {
                $("#primer_nombre").prop('required',true);
                $("#otros_nombres").prop('required',false);
                $("#primer_apellido").prop('required',true);
                $("#segundo_apellido").prop('required',false);
                $("#razon_social").prop('required',false);
            }
            $("#numero_documento").prop('required',false);
            $("#direccion").prop('required',true);
            $("#email").prop('required',true);
            $("#telefono_1").prop('required',false);
            
        });

        $(document).on('click', '#updateNit', function () {

            var form = document.querySelector('#nitsForm');

            if(form.checkValidity()){
                $("#saveNitLoading").show();
                $("#updateNit").hide();
                $("#saveNit").hide();
                
                let data = {
                    id: $("#id_nit").val(),
                    id_tipo_documento: $("#id_tipo_documento").val(),
                    numero_documento: document.getElementById('numero_documento').inputmask.unmaskedvalue(),
                    tipo_contribuyente: $("#tipo_contribuyente").val(),
                    primer_apellido: $("#primer_apellido").val(),
                    segundo_apellido: $("#segundo_apellido").val(),
                    primer_nombre: $("#primer_nombre").val(),
                    otros_nombres: $("#otros_nombres").val(),
                    razon_social: $("#razon_social").val(),
                    direccion: $("#direccion").val(),
                    email: $("#email").val(),
                    telefono_1: $("#telefono_1").val(),
                }

                $.ajax({
                    url: base_url + 'nit',
                    method: 'PUT',
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
                        swalFire('Edición exitosa', 'Nit actualizado con exito!');
                    }
                }).fail((res) => {
                    $('#saveNit').hide();
                    $('#updateNit').show();
                    $("#saveNitLoading").hide();
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
                    swalFire('Edición herrada', errorsMsg, false);
                });
            } else {
                form.classList.add('was-validated');
            }
        });



        $(document).on('click', '#saveNit', function () {
            var form = document.querySelector('#nitsForm');

            if(form.checkValidity()){
                $("#saveNitLoading").show();
                $("#updateNit").hide();
                $("#saveNit").hide();
    
                let data = {
                    id_tipo_documento: $("#id_tipo_documento").val(),
                    numero_documento: document.getElementById('numero_documento').inputmask.unmaskedvalue(),
                    tipo_contribuyente: $("#tipo_contribuyente").val(),
                    primer_apellido: $("#primer_apellido").val(),
                    segundo_apellido: $("#segundo_apellido").val(),
                    primer_nombre: $("#primer_nombre").val(),
                    otros_nombres: $("#otros_nombres").val(),
                    razon_social: $("#razon_social").val(),
                    direccion: $("#direccion").val(),
                    email: $("#email").val(),
                    telefono_1: $("#telefono_1").val(),
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
                    swalFire('Creación herrada', errorsMsg, false);
                });
            } else {
                form.classList.add('was-validated');
            }
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
            $("#telefono_1").val(data.telefono_1);

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
