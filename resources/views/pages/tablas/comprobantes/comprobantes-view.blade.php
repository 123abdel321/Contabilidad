@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Comprobantes'])

    <style>
        .error {
            color: red;
        }
        .fa-comprobante {
            margin-left: -5px;
        }
    </style>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="row" style="z-index: 9;">
                <div class="">
                    <button type="button" class="btn btn-success btn-sm" id="createComprobante">Agregar comprobante</button>
                </div>
                <!-- <div class="col-8 col-md-8 col-sm-8">
                    <input type="text" id="searchInput" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div> -->
            </div>
            

            <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
                <div class="card-body">

                    @include('pages.tablas.comprobantes.comprobantes-table')

                </div>
            </div>
        </div>

        @include('pages.tablas.comprobantes.comprobantes-form')
        
    </div>
@endsection

@push('js')
    <script>

        var comprobante_table = $('#comprobantesTable').DataTable({
            dom: '',
            responsive: true,
            processing: true,
            serverSide: true,
            initialLoad: true,
            language: lenguajeDatatable,
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
                {
                    "data": function (row, type, set){
                        var html = '';
                        html+= '<span id="editcomprobante_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-info edit-comprobante" style="margin-bottom: 0rem !important;">Editar</span>&nbsp;';
                        html+= '<span id="deletecomprobante_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-comprobante" style="margin-bottom: 0rem !important">Eliminar</span>';
                        return html;
                    }
                },

            ]
        });

        // $("#searchInput").on("input", function (e) {
        //     comprobante_table.context[0].jqXHR.abort();
        //     $('#comprobantesTable').DataTable().search($("#searchInput").val()).draw();
        // });
        
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
                    swalFire('Creación exitosa', 'Comprobante creado con exito!');
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
                swalFire('Creación herrada', errorsMsg, false);
            });
        });

        function clearFormComprobante(){
            $("#textComprobanteCreate").show();
            $("#textComprobanteUpdate").hide();
            $("#saveComprobanteLoading").hide();

            $("#id_comprobante").val('');
            $("#codigo").val('');
            $("#nombre").val('');
            $("#tipo_comprobante").val(0).change();
            $("#tipo_consecutivo").val(0).change();
            $("#consecutivo_siguiente").val(1);
        }

        comprobante_table.on('click', '.edit-comprobante', function() {
            $("#textComprobanteCreate").hide();
            $("#textComprobanteUpdate").show();
            $("#saveComprobanteLoading").hide();
            $("#updateComprobante").show();
            $("#saveComprobante").hide();

            var trComprobante = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, comprobante_table);
            console.log(data);
            $("#id_comprobante").val(id);
            $("#codigo").val(data.codigo);
            $("#nombre").val(data.nombre);
            $("#tipo_comprobante").val(data.tipo_comprobante).change();
            $("#tipo_consecutivo").val(data.tipo_consecutivo).change();
            $("#consecutivo_siguiente").val(data.consecutivo_siguiente);

            $("#comprobanteFormModal").modal('show');
        });

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
                id: $("#id_comprobante").val(),
                codigo: $("#codigo").val(),
                nombre: $("#nombre").val(),
                tipo_comprobante: $("#tipo_comprobante").val(),
                tipo_consecutivo: $("#tipo_consecutivo").val(),
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
                swalFire('Actualización exitosa', 'Comprobante creado con exito!');
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
                swalFire('Actualización herrada', errorsMsg, false);
            });
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
                            swalFire('Eliminación exitosa', 'Comprobante eliminado con exito!');
                        } else {
                            swalFire('Eliminación herrada', res.message, false);
                        }
                    }).fail((res) => {
                        swalFire('Eliminación herrada', res.message, false);
                    });
                }
            })
        });


    </script>
@endpush
