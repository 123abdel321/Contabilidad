
<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.documento.documento-filter')
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.documento.documento-table')
            </div>
        </div>
    </div>
</div>

<script>
    var documento_table = $('#DocumentosInformeTable').DataTable({
        dom: 'tip',
        autoWidth: true,
        responsive: false,
        processing: true,
        serverSide: true,
        initialLoad: true,
        bFilter: true,
        language: lenguajeDatatable,
        ajax:  {
            type: "GET",
            url: base_url + 'documento',
            headers: headers,
            data: function ( d ) {
                d.id_comprobante = $('#id_comprobante_documento').val();
                d.fecha_manual = $('#fecha_manual_documento').val();
                d.consecutivo = $('#consecutivo_documento').val();
                d.tipo_factura = $("input[type='radio']#tipo_factura1").is(':checked') ? 'todas' : 'anuladas';
            }
        },
        "columns": [
            {"data": function (row, type, set){
                if(!row.comprobante){
                    return '';
                }
                return row.comprobante.codigo + ' - ' +row.comprobante.nombre;
            }},
            {"data": function (row, type, set){
                if(!row.consecutivo){
                    return '';
                }
                return row.consecutivo;
            }},
            {"data": function (row, type, set){
                if(!row.fecha_manual){
                    return '';
                }
                return row.fecha_manual;
            }},
            {
                data: 'debito',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
            {
                data: 'credito',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
            {
                data: 'saldo_final',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
            {"data": function (row, type, set){
                if(row.anulado){
                    return 'Si';
                }
                return 'No';
            }},
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
                    if(row.anulado == 1) {
                        return ''
                    }
                    var html = '';
                    html+= '<span id="anulardocumento_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger anular-documento" style="margin-bottom: 0rem !important">Anular</span>';
                    return html;
                }
            }
        ]
    });
    
    $('input[type=radio][name=tipo_factura]').change(function() {
        document.getElementById("generarDocumento").click();
    });

    $(document).on('click', '#generarDocumento', function () {
        $("#generarDocumento").hide();
        $("#generarDocumentoLoading").show();
        documento_table.ajax.reload(function() {
            $("#generarDocumento").show();
            $("#generarDocumentoLoading").hide();
        },false);
    });

    $(document).on('click', '.anular-documento', function () {
        var trNit = $(this).closest('tr');
        var id = this.id.split('_')[1];
        console.log('id: ',id);
        var data = getDataById(id, documento_table);
        console.log('data: ',data);

        Swal.fire({
            title: 'Anular documento ?',
            type: 'warning',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Anular!',
            input: 'text',
            inputLabel: 'Motivo de anulación',
            reverseButtons: true,
            inputValidator: (value) => {
                if (!value) {
                    return 'El motivo de anulación es requerido'
                }
            },
        }).then((result) => {
            if (result.isConfirmed){

                $.ajax({
                    url: base_url + 'documentos',
                    method: 'PUT',
                    data: JSON.stringify({
                        id_comprobante: data.id_comprobante,
                        consecutivo: data.consecutivo,
                        fecha_manual: data.fecha_manual,
                        motivo_anulacion: result.value
                    }),
                    headers: headers,
                    dataType: 'json',
                }).done((res) => {
                    if(res.success){
                        documento_table.row(trPlanCuenta).remove().draw();
                        swalFire('Anulación exitosa', 'Documentos anulados con exito!');
                    } else {
                        swalFire('Anulación herrada', res.message, false);
                    }
                }).fail((res) => {
                    swalFire('Anulación herrada', res.message, false);
                });
            }
        })

    });

    

    var $comboComprobante = $('#id_comprobante_documento').select2({
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
</script>
