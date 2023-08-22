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
        
        <div class="card cardTotalBalance" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-6 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">SALDO ANTERIOR</p>
                    <h6 id="balance_anterior" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col-6 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">DEBITO</p>
                    <h6 id="balance_debito" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col-6 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">CREDITO</p>
                    <h6 id="balance_credito" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col-6 col-md-3 col-sm-3">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">SALDO FINAL</p>
                    <h6 id="balance_diferencia" style="margin-top: -15px;">$0</h6>
                </div>
            </div>
        </div>
        <div class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px; margin-top: -1px;">
            @include('pages.contabilidad.balance.balance-table')
        </div>
    </div>
</div>

<script>
    var fechaDesde = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    var generarBalance = false;
    var balanceExistente = false;

    $('#fecha_desde_balance').val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $('#fecha_hasta_balance').val(fechaDesde);

    findBalance();

    var balance_table = $('#balanceInformeTable').DataTable({
        dom: 'ti',
        responsive: true,
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
                $('td', row).css('background-color', 'rgb(28 69 135)');
                $('td', row).css('font-weight', 'bold');
                $('td', row).css('color', 'white');
                return;
            }
            if(data.balance){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 10%)');
                return;
            }
            if(data.cuenta.length == 1){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 70%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 2){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 50%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 4){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 30%)');
                $('td', row).css('font-weight', 'bold');
                return;
            }
            if(data.cuenta.length == 6){//
                $('td', row).css('background-color', 'rgb(64 164 209 / 20%)');
                $('td', row).css('font-weight', 'bold');
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
    
    $(document).on('click', '#generarBalance', function () {
        generarBalance = false;
        $("#generarBalance").hide();
        $("#generarBalanceLoading").show();
        $('#descargarExcelBalance').prop('disabled', true);
        $("#descargarExcelBalance").hide();

        $(".cardTotalBalance").css("background-color", "white");

        $("#balance_anterior").text('$0');
        $("#balance_debito").text('$0');
        $("#balance_credito").text('$0');
        $("#balance_diferencia").text('$0');

        var url = base_url + 'balances';
        url+= '?fecha_desde='+$('#fecha_desde_balance').val();
        url+= '&fecha_hasta='+$('#fecha_hasta_balance').val();
        url+= '&id_cuenta='+$('#id_cuenta_balance').val();
        url+= '&nivel='+getNivel();
        url+= '&generar='+generarBalance;

        balance_table.ajax.url(url).load(function(res) {
            if(res.success) {
                if(res.data){
                    Swal.fire({
                        title: '¿Cargar Balance?',
                        text: "Balance generado anteriormente ¿Desea cargarlo?",
                        type: 'info',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Cargar',
                        cancelButtonText: 'Generar nuevo',
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.value){
                            console.log('cargando: ',res.data)
                            $('#id_balance_cargado').val(res.data);
                            loadBalanceById(res.data);
                        } else {
                            generarBalance = true;
                            GenerateBalance();
                        }
                    })
                } else {
                    agregarToast('info', 'Generando balance', 'En un momento se le notificará cuando el informe esté generado...', true );
                }
            }
        });
    });

    var channel = pusher.subscribe('informe-balance-'+localStorage.getItem("notificacion_code"));

    channel.bind('notificaciones', function(data) {
        if(data.url_file){
            loadExcel(data);
            return;
        }
        if(data.id_balance){
            $('#id_balance_cargado').val(data.id_balance);
            loadBalanceById(data.id_balance);
            return;
        }
    });

    function loadExcel(data) {
        window.open('https://'+data.url_file, "_blank");
        agregarToast(data.tipo, data.titulo, data.mensaje, data.autoclose);
    }

    function loadBalanceById(id_balance) {
        balance_table.ajax.url(base_url + 'balances-show?id='+id_balance).load(function(res) {
            if(res.success){
                $("#generarBalance").show();
                $("#generarBalanceLoading").hide();
                $('#descargarExcelBalance').prop('disabled', false);
                $("#descargarExcelBalance").show();
                $("#descargarExcelBalanceDisabled").hide();

                if(res.descuadre) {
                    Swal.fire(
                        'Balance descuadrado',
                        '',
                        'warning'
                    );
                } else {
                    agregarToast('exito', 'Balance cargado', 'Informe cargado con exito!', true);
                }
                console.log(res);
                mostrarTotalesBalance(res.totales, res.filtros);
            }
        });
    }

    function mostrarTotalesBalance(data, filtros = false) {
        if (!data) {
            return;
        }
        if(!filtros && parseInt(data.saldo_anterior)){
            $(".cardTotalBalance").css("background-color", "lightpink");
        } else if (!filtros && !parseInt(data.saldo_anterior)){
            $(".cardTotalBalance").css("background-color", "lightgreen");
        } else if (!filtros && parseInt(data.saldo_final)){
            $(".cardTotalBalance").css("background-color", "lightpink");
        } else if (!filtros && !parseInt(data.saldo_final)) {
            $(".cardTotalBalance").css("background-color", "lightgreen");
        } else {
            $(".cardTotalBalance").css("background-color", "white");
        }

        $("#balance_anterior").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_anterior));
        $("#balance_debito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.debito));
        $("#balance_credito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.credito));
        $("#balance_diferencia").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.saldo_final));
    }

    function getNivel() {
        if($("input[type='radio']#nivel_balance1").is(':checked')) return 1;
        if($("input[type='radio']#nivel_balance2").is(':checked')) return 2;
        if($("input[type='radio']#nivel_balance3").is(':checked')) return 3;

        return false;
    }

    function GenerateBalance() {
        var url = base_url + 'balances';
        url+= '?fecha_desde='+$('#fecha_desde_balance').val();
        url+= '&fecha_hasta='+$('#fecha_hasta_balance').val();
        url+= '&id_cuenta='+$('#id_cuenta_balance').val();
        url+= '&nivel='+getNivel();
        url+= '&generar='+generarBalance;

        balance_table.ajax.url(url).load(function(res) {
            if(res.success) {

                agregarToast('info', 'Generando balance', 'En un momento se le notificará cuando el informe esté generado...', true );
            }
        });
    }

    $(document).on('click', '#descargarExcelBalance', function () {
        $.ajax({
            url: base_url + 'balances-excel',
            method: 'POST',
            data: JSON.stringify({id: $('#id_balance_cargado').val()}),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                if(res.url_file){
                    window.open('https://'+res.url_file, "_blank");
                    return; 
                }
                agregarToast('info', 'Generando excel', res.message, true);
            }
        }).fail((err) => {
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
            agregarToast('error', 'Error al generar excel', errorsMsg);
        });
    });

    $(document).on('click', '#generarBalanceUltimo', function () {
        $('#generarBalanceUltimo').hide();
        $('#generarBalanceUltimoLoading').show();
        loadBalanceById(auxiliarExistente);
    });

    function findBalance() {
        balanceExistente = false;
        $('#generarBalanceUltimo').hide();
        $('#generarBalanceUltimoLoading').show();

        var url = 'balances-find';
        url+= '?fecha_desde='+$('#fecha_desde_balance').val();
        url+= '&fecha_hasta='+$('#fecha_hasta_balance').val();
        url+= '&id_cuenta='+$('#id_cuenta_balance').val();
        url+= '&nivel='+getNivel();
        console.log('url: ',url)
        $.ajax({
            url: base_url + url,
            method: 'GET',
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            $('#generarBalanceUltimoLoading').hide();
            if(res.data){
                balanceExistente = res.data;
                $('#generarBalanceUltimo').show();
            }
        }).fail((err) => {
            $('#generarBalanceUltimoLoading').hide();
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
            agregarToast('error', 'Error consultar balancees', errorsMsg, true);
        });
    }

    $("#fecha_desde_balance").on('change', function(){
        clearBalance();
        findBalance();
    });

    $("#fecha_hasta_balance").on('change', function(){
        clearBalance();
        findBalance();
    });

    $("#id_cuenta_balance").on('change', function(){
        clearBalance();
        findBalance();
    });

    $(".nivel_balance").on('change', function(){
        clearBalance();
        findBalance();
    });

    function clearBalance() {
        $("#descargarExcelBalance").hide();
        $("#descargarExcelBalanceDisabled").show();
    }

</script>