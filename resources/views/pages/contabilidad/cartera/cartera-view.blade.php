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

                @include('pages.contabilidad.cartera.cartera-filter')

            </div>
        </div>
        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <div class="card-body">
                @include('pages.contabilidad.cartera.cartera-table')
            </div>
        </div>
    </div>
</div>

<script>
    var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    
    $('#fecha').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha').val(fechaDesde);

    $('input[type=radio][name=detallar_cartera]').change(function() {
        console.log('change cnartera');
        if(!$("input[type='radio']#detallar_cartera1").is(':checked')){
            cartera_table.column( 6 ).visible( false );
        } else {
            cartera_table.column( 6 ).visible( true );
        }
        document.getElementById("generarCartera").click();
    });

    var $validator = $('#carteraInformeForm').validate({
        rules: {
            id_tipo_cuenta: {
                required: false,
            },
            fecha_desde: {
                required: true,
                minlength: 5,
                maxlength: 20,
            },
            fecha_hasta: {
                required: true,
                minlength: 3,
                maxlength: 20,
            }
        },
        messages: {
            id_tipo_cuenta: {
                required: "El campo tipo cuenta es requerido"
            },
            fecha_desde: {
                required: "El campo Fecha desde es requerido"
            },
            fecha_hasta: {
                required: "El campo Fecha hasta es requerido"
            }
        },

        highlight: function(element) {
            $(element).closest('.form-control').removeClass('is-valid').addClass('is-invalid');
        },
        success: function(element) {
            $(element).closest('.form-control').removeClass('is-invalid').addClass('is-valid');
        }
    });

    var cartera_table = $('#CarteraInformeTable').DataTable({
        dom: '',
        autoWidth: true,
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: false,
        headers: headers,
        language: lenguajeDatatable,
        ordering: false,
        'rowCallback': function(row, data, index){
            if(data.detalle_group == 'nits'){
                if(!$("input[type='radio']#detallar_cartera1").is(':checked')) {
                    return;
                }
                $('td', row).css('background-color', 'rgb(128 207 120 / 40%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta == "TOTALES"){
                $('td', row).css('background-color', 'rgb(0 215 64 / 60%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 1){
                $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 2){
                $('td', row).css('background-color', 'rgb(64 164 209 / 45%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 4){
                $('td', row).css('background-color', 'rgb(64 164 209 / 30%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.detalle_group && !data.detalle){
                $('td', row).css('background-color', 'rgb(64 164 209 / 15%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.detalle){
                $('td', row).css('background-color', 'rgb(197 228 241 / 56%)');
                $('td', row).css('font-weight', 'bold');
            }
        },
        ajax:  {
            type: "GET",
            url: base_url + 'extracto',
            headers: headers,
            data: function ( d ) {
                d.id_tipo_cuenta = $('#id_tipo_cuenta').val();
                d.id_nit = $('#id_nit_cartera').val();
                d.fecha = $('#fecha_cartera').val();
                d.detallar_cartera = $("input[type='radio']#detallar_cartera1").is(':checked') ? '1' : '';
            }
        },
        "columns": [
            {"data": function (row, type, set){
                if(!row.cuenta) {
                    return '';
                }
                return row.cuenta + ' - ' +row.nombre_cuenta;
            }},
            {"data": function (row, type, set){
                if(!row.numero_documento){
                    return '';
                }
                if(row.razon_social){
                    return row.numero_documento +' - '+ row.razon_social;
                }
                return row.numero_documento + ' - ' +row.nombre_nit;
            }, responsivePriority: 1, targets: 0},
            
            {data: 'documento_referencia'},
            {data: 'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 4, targets: -3},
            {data: 'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 3, targets: -2},
            {data: 'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right', responsivePriority: 2, targets: -1},
            {"data": function (row, type, set){
                if(!row.codigo_comprobante){
                    return '';
                }
                return row.codigo_comprobante + ' - ' +row.nombre_comprobante;
            }, visible: false},
            {data: 'fecha_manual'},
            {data: 'dias_cumplidos', responsivePriority: 5, targets: -4},
            {data: 'plazo'},
            {"data": function (row, type, set){
                if(row.plazo > 0){
                    var mora = row.dias_cumplidos - row.plazo;
                    if(mora <= 0) {
                        return 0
                    }
                    return mora;
                }
                return row.dias_cumplidos;
            }},
            {data: 'concepto'},
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
        ]
    });

    var $comboCuenta = $('#id_cuenta').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            dataType: 'json',
            headers: headers,
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    var $comboNit = $('#id_nit_cartera').select2({
        theme: 'bootstrap-5',
        delay: 250,
        ajax: {
            url: 'api/nit/combo-nit',
            dataType: 'json',
            headers: headers,
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $(document).on('click', '#generarCartera', function () {
        $("#generarCartera").hide();
        $("#generarCarteraLoading").show();
        // $('#descargarExcelCartera').prop('disabled', true);
        // $("#descargarExcelCartera").hide();
        // $("#descargarExcelCarteraDisabled").show();
        var $valid = $('#carteraInformeForm').valid();
        $('.error').hide();
        if (!$valid) {
            $(".error").show();
            $("#generarCartera").show();
            $("#generarCarteraLoading").hide();
            $validator.focusInvalid();
            return false;
        }else{
            $('.error').hide();
            cartera_table.ajax.reload(function() {
                $("#generarCartera").show();
                $("#generarCarteraLoading").hide();
                // $('#descargarExcelCartera').prop('disabled', false);
                // $("#descargarExcelCartera").show();
                // $("#descargarExcelCarteraDisabled").hide();
                $('.error').hide();
            },false);
        }
    });

</script>