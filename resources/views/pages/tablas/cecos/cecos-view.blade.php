<div class="container-fluid py-2">
    <div class="row">
        <div class="row" style="z-index: 9;">
            <div class="row" style="z-index: 9;">
                <div class="col-12 col-md-6 col-sm-6">
                    <button type="button" class="btn btn-primary btn-sm" id="createCecos">Agregar centro costos</button>
                </div>
                <div class="col-12 col-md-6 col-sm-6">
                    <input type="text" id="searchInput" class="form-control form-control-sm search-table" placeholder="Buscar">
                </div>
            </div>
        </div>
        

        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">

                @include('pages.tablas.cecos.cecos-table')

            </div>
        </div>
    </div>

    @include('pages.tablas.cecos.cecos-form')
    
</div>

<script>

    $('.form-control').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });

    var cecos_table = $('#cecosTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: true,
        initialLoad: true,
        language: lenguajeDatatable,
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'cecos',
        },
        columns: [
            {"data":'codigo'},
            {"data":'nombre'},
            {"data": function (row, type, set){  
                var html = '<div class="button-user" onclick="showUser('+row.created_by+',`'+row.fecha_creacion+'`,0)"><i class="fas fa-user icon-user"></i>&nbsp;'+row.fecha_edicion+'</div>';
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
                    html+= '<span id="editcecos_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-secondary edit-cecos" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;';
                    html+= '<span id="deletececos_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-cecos" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>';
                    return html;
                }
            },

        ]
    });

    $("#searchInput").on("input", function (e) {
        cecos_table.context[0].jqXHR.abort();
        $('#cecosTable').DataTable().search($("#searchInput").val()).draw();
    });
    
    $(document).on('click', '#createCecos', function () {
        clearFormCecos();
        $("#updateCecos").hide();
        $("#saveCecos").show();
        $("#cecosFormModal").modal('show');
    });

    function clearFormCecos(){
        $("#textCecosCreate").show();
        $("#textCecosUpdate").hide();
        $("#saveCecosLoading").hide();

        $("#id_cecos_up").val('');
        $("#codigo").val('');
        $("#nombre").val('');
    }

    $(document).on('click', '#saveCecos', function () {

        var form = document.querySelector('#cecosForm');

        if(!form.checkValidity()){
            form.classList.add('was-validated');
            return;
        }

        $("#saveCecosLoading").show();
        $("#updateCecos").hide();
        $("#saveCecos").hide();

        let data = {
            codigo: $("#codigo").val(),
            nombre: $("#nombre").val()
        }

        $.ajax({
            url: base_url + 'cecos',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                clearFormCecos();
                $("#saveCecos").show();
                $("#saveCecosLoading").hide();
                $("#cecosFormModal").modal('hide');
                cecos_table.row.add(res.data).draw();
                agregarToast('exito', 'Creación exitosa', 'Centro de costos creado con exito!', true);
            }
        }).fail((err) => {
            $('#saveCecos').show();
            $('#saveCecosLoading').hide();
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

    cecos_table.on('click', '.edit-cecos', function() {
        $("#textCecosCreate").hide();
        $("#textCecosUpdate").show();
        $("#saveCecosLoading").hide();
        $("#updateCecos").show();
        $("#saveCecos").hide();

        var trCecos = $(this).closest('tr');
        var id = this.id.split('_')[1];
        var data = getDataById(id, cecos_table);
        
        $("#id_cecos_up").val(id);
        $("#codigo").val(data.codigo);
        $("#nombre").val(data.nombre);

        $("#cecosFormModal").modal('show');
    });

    cecos_table.on('click', '.drop-cecos', function() {
        var trCecos = $(this).closest('tr');
        var id = this.id.split('_')[1];
        var data = getDataById(id, cecos_table);
        Swal.fire({
            title: 'Eliminar centro de costos: '+data.nombre+'?',
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
                    url: base_url + 'cecos',
                    method: 'DELETE',
                    data: JSON.stringify({id: id}),
                    headers: headers,
                    dataType: 'json',
                }).done((res) => {
                    if(res.success){
                        cecos_table.ajax.reload();
                        agregarToast('exito', 'Eliminación exitosa', 'Centro de costos eliminado con exito!', true );
                    } else {
                        agregarToast('error', 'Eliminación herrada', res.message);
                    }
                }).fail((res) => {
                    agregarToast('error', 'Eliminación herrada', res.message);
                });
            }
        })
    });

    $(document).on('click', '#updateCecos', function () {

        var form = document.querySelector('#cecosForm');

        if(!form.checkValidity()){
            form.classList.add('was-validated');
            return;
        }

        $("#saveCecosLoading").show();
        $("#updateCecos").hide();
        $("#saveCecos").hide();

        let data = {
            id: $("#id_cecos_up").val(),
            codigo: $("#codigo").val(),
            nombre: $("#nombre").val()
        }

        $.ajax({
            url: base_url + 'cecos',
            method: 'PUT',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
        if(res.success){
            console.log(res.data);
            clearFormCecos();
            $("#saveCecos").show();
            $("#saveCecosLoading").hide();
            $("#cecosFormModal").modal('hide');
            cecos_table.ajax.reload();
            agregarToast('exito', 'Actualización exitosa', 'Centro de costos actualizado con exito!', true );
        }
        }).fail((err) => {
            $('#updateCecos').show();
            $('#saveCecosLoading').hide();
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
            agregarToast('error', 'Error al actualizar Centro de costos!', errorsMsg);
        });
    });

</script>