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

                @include('pages.contabilidad.balance.balance-filter')

            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body" style="content-visibility: auto; overflow: auto;">
                @include('pages.contabilidad.balance.balance-table')
            </div>
        </div>
    </div>
</div>

<script>
    var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    var $comboPadre = $('#id_cuenta_balance').select2({
        theme: 'bootstrap-5',
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
    
    $('#fecha_desde_balance').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_balance').val(fechaDesde);
    var $validator = $('#balanceInformeForm').validate({
        rules: {
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

    var balance_table = $('#balanceInformeTable').DataTable({
        dom: 'ti',
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: false,
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
        'rowCallback': function(row, data, index){
            if(data.cuenta == "TOTALES"){
                $('td', row).css('background-color', 'rgb(0 255 76 / 56%)');
                $('td', row).css('font-weight', 'bold');
            }
            if(data.cuenta.length == 1){
                $('td', row).css('background-color', 'rgb(64 164 209 / 60%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 2){
                $('td', row).css('background-color', 'rgb(64 164 209 / 45%)');
                return;
            }
            if(data.cuenta.length == 4){
                $('td', row).css('background-color', 'rgb(64 164 209 / 30%)');
                return;
            }
            
        },
        ajax:  {
            type: "GET",
            url: base_url + 'balances',
            headers: headers,
            data: function ( d ) {
                d.fecha_desde = $('#fecha_desde_balance').val();
                d.fecha_hasta = $('#fecha_hasta_balance').val();
                d.id_cuenta = $('#id_cuenta_balance').val();
            }
        },
        "columns": [
            {"data": function (row, type, set){
                if(row.cuenta){
                    return row.cuenta +' - '+ row.nombre_cuenta;
                }
                return '';
            }},
            {
                data: 'saldo_anterior',
                render: $.fn.dataTable.render.number(',', '.', 2, ''),
                className: "column-number", className: 'dt-body-right'
            },
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
        ]
    });
    
    $(document).on('click', '#generarBalance', function () {
        $("#generarBalance").hide();
        $("#generarBalanceLoading").show();
        $('#descargarExcelBalance').prop('disabled', true);
        $('.error').hide();
        var $valid = $('#balanceInformeForm').valid();
        if (!$valid) {
            $(".error").show();
            $("#generarBalance").show();
            $("#generarBalanceLoading").hide();
            $validator.focusInvalid();
            return false;
        }else{
            balance_table.ajax.reload(function() {
                $("#generarBalance").show();
                $("#generarBalanceLoading").hide();
                $('#descargarExcelBalance').prop('disabled', false);
                $('.error').hide();
            },false);
        }
    });

    $(document).on('click', '#descargarExcelBalance', function () {
        var fecha_desde = $('#fecha_desde_balance').val();
        var fecha_hasta = $('#fecha_hasta_balance').val();
        var id_cuenta = $('#id_cuenta_balance').val();
        window.open("/balance-excel?fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"$id_cuenta="+id_cuenta, "_blank");
    });

</script>