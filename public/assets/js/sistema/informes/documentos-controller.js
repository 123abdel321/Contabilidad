var fechaDesde = null;
var documento_table = null;

function documentosInit() {

    fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_desde_documento').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_documento').val(fechaDesde);

    documento_table = $('#DocumentosInformeTable').DataTable({
        dom: 't',
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        ordering: false,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        scrollX: true,
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
            url: base_url + 'documento',
            headers: headers,
            data: function ( d ) {
                d.id_comprobante = $('#id_comprobante_documento').val();
                d.fecha_desde = $('#fecha_desde_documento').val();
                d.fecha_hasta = $('#fecha_hasta_documento').val();
                d.consecutivo = $('#consecutivo_documento').val();
                d.tipo_factura = getEstadoDocumentos();
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
                    if(row.anulado == 1) {
                        return ''
                    }
                    var html = '';
                    html+= '<span id="anulardocumento_'+row.id+'" href="javascript:void(0)" class="btn badge bg-gradient-danger anular-documento" style="margin-bottom: 0rem !important">Anular</span>&nbsp;';
                    html+= '<span id="imprimirdocumento_'+row.id+'" href="javascript:void(0)" class="btn badge btn-outline-dark imprimir-documento" style="margin-bottom: 0rem !important; color: black;">PDF</span>';
                    return html;
    
                }
            }
        ]
    });

    $('#id_comprobante_documento').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un Comprobante",
        allowClear: true,
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

    $('.water').hide();
    documento_table.ajax.reload();
}

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
    var trDocumento = $(this).closest('tr');
    var id = this.id.split('_')[1];

    var data = getDataById(id, documento_table);

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
                    documento_table.row(trDocumento).remove().draw();
                    swalFire('Anulación exitosa', 'Documentos anulados con exito!');
                } else {
                    swalFire('Anulación errada', res.message, false);
                }
            }).fail((res) => {
                swalFire('Anulación errada', res.message, false);
            });
        }
    })

});

$(document).on('click', '.imprimir-documento', function () {
    var id = this.id.split('_')[1];
    window.open("/documentos-print/"+id, "_blank");
});

function getEstadoDocumentos() {
    if($("input[type='radio']#nivel_documento1").is(':checked')) return '';
    if($("input[type='radio']#nivel_documento2").is(':checked')) return 1;

    return '';
}