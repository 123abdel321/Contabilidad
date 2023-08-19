<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }

    .combo-grid-nit {
        width: 300px !important;
    }

    .combo-grid {
        width: 300px !important;
    }

    .drop-row-grid {
        margin-bottom: 0rem !important;
        font-size: 12px;
        margin-top: 4px;
        border-radius: 50px;
        width: 26px;
    }

    .fa-trash-alt {
        margin-left: -3px;
        margin-top: 1px;
    }
    #documentoReferenciaTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #documentoReferenciaTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .btn-group {
        box-shadow: 0 0px 0px rgba(50, 50, 93, 0.1), 0 0px 0px rgba(0, 0, 0, 0.08);
    }

    .normal_input {
        border-radius: 9px !important;
    }

    .documento-load {
        position: absolute;
        margin-left: 160px !important;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
    }

    .info-factura {
        margin-top: -16px;
        z-index: 999;
        margin-left: 80px !important;
    }

</style>

<div class="container-fluid py-2">
    <div class="row">
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                @include('pages.capturas.documento_general.documento_general-filter')

            </div>
        </div>

        <div class="card cardTotal" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-4 col-md-4 col-sm-4" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px;">DEBITO</p>
                    <h6 id="general_debito" style="margin-top: -15px;">0.00</h6>
                </div>
                <div class="col-4 col-md-4 col-sm-4" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px;">CREDITO</p>
                    <h6 id="general_credito" style="margin-top: -15px;">0.00</h6>
                </div>
                <div class="col-4 col-md-4 col-sm-4">
                    <p style="font-size: 13px; margin-top: 5px;">DIFERENCIA</p>
                    <h6 id="general_diferencia" style="margin-top: -15px;">0.00</h6>
                </div>
            </div>
        </div>
        <div id="card-documento-general" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
            @include('pages.capturas.documento_general.documento_general-table')
        </div>
        
        @include('pages.capturas.documento_general.documento_general-form')
        @include('pages.capturas.documento_general.documento_general-extracto')

    </div>
</div>


<script>
    var fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
    var idDocumento = 0;
    var editandoCaptura = 0;
    var rowExtracto = '';
    var tipo_comprobante = '';
    var validarFactura = null;
    var calcularCabeza = true;
    var askSaveDocumentos = true;

    $('#fecha_manual').val(fecha);

    $('.form-control').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });

    $('.form-control').keyup(function() {
        $(this).val($(this).val().toUpperCase());
    });

    var documento_general_table = $('#documentoReferenciaTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
        deferLoading: 0,
        initialLoad: false,
        autoWidth: true,
        language: lenguajeDatatable,
        ordering: false,
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'documento-vacio',
        },
        columns: [
            {//BORRAR
                "data": function (row, type, set, col){
                    return '<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteRow('+col.row+')" id="droprow_'+col.row+'"><i class="fas fa-trash-alt"></i></span>';
                }
            },
            {//CUENTA
                "data": function (row, type, set, col){
                    return '<select class="form-control form-control-sm combo_cuenta combo-grid" id="combo_cuenta_'+col.row+'" onchange="changeCuentaRow('+col.row+')"></select>';
                },
            },
            {//NIT
                "data": function (row, type, set, col){
                    return '<select class="form-control form-control-sm combo_nits combo-grid" id="combo_nits_'+col.row+'" onchange="changeNitRow('+col.row+')" disabled></select>';
                }
            },
            {//CECOS
                "data": function (row, type, set, col){
                    return '<select class="form-control form-control-sm combo_cecos combo-grid" id="combo_cecos_'+col.row+'" onchange="changeCecosRow('+col.row+')" disabled></select>';
                }
            },
            {//DCTO REFE
                "data": function (row, type, set, col){
                    var html = '';
                    html+= '';
                    html+= '    <div class="input-group" style="width: 180px; height: 30px;">';
                    html+= '        <input type="text" class="form-control form-control-sm documento_referencia_row" id="documento_referencia_'+col.row+'" onkeydown="buscarFactura('+col.row+', event)" onkeypress="changeDctoRow('+col.row+', event)" keyup style="height: 33px;" value="" disabled readonly>';
                    html+= '        <i class="fa fa-spinner fa-spin fa-fw documento-load" id="documento_load_'+col.row+'" style="display: none;"></i>';
                    html+= '        <div class="valid-feedback info-factura">Nueva factura</div>';
                    html+= '        <div class="invalid-feedback info-factura">Factura existente</div>';
                    html+= '        <div class="input-group-append button-group" id="conten_button_'+col.row+'"><span href="javascript:void(0)" class="btn badge bg-gradient-secondary btn-group btn-documento-extracto" style="min-width: 40px; margin-right: 3px; border-radius: 0px 7px 7px 0px; height: 33px;"><i class="fas fa-search" style="font-size: 17px; margin-top: 3px;"></i><b style="vertical-align: text-top;"></b></span></div></div>';
                    html+= '    </div>';
                    html+= '';

                    return html;
                }
            },
            {//DEBITO
                "data": function (row, type, set, col){
                    return '<input type="number" class="form-control form-control-sm input_number debito_input" id="debito_'+col.row+'" onkeypress="changeDebitoRow('+col.row+', event)" onfocusout="mostrarValores()" style="width: 130px !important;" disabled>';
                }
            },
            {//CREDITO
                "data": function (row, type, set, col){
                    return '<input type="number" class="form-control form-control-sm input_number credito_input" id="credito_'+col.row+'" onkeypress="changeCreditoRow('+col.row+', event)" onfocusout="mostrarValores()" style="width: 130px !important;" disabled>';
                }
            },
            {//CONCEPTO
                "data": function (row, type, set, col){
                    return '<input type="text" class="form-control form-control-sm" id="concepto_'+col.row+'" onkeypress="changeConceptoRow('+col.row+', event)" placeholder="SIN OBSERVACIÓN" style="width: 300px !important;" disabled>';
                }
            }
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#documentoReferenciaTable').on('draw.dt', function() {
                $('.combo_cuenta').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    minimumInputLength: 1,
                    ajax: {
                        url: 'api/plan-cuenta/combo-cuenta',
                        headers: headers,
                        data: function (params) {
                            var query = {
                                q: params.term,
                                id_comprobante: $("#id_comprobante").val(),
                                _type: 'query'
                            }
                            return query;
                        },
                        dataType: 'json',
                        processResults: function (data) {
                            var data_modified = $.map(data.data, function (obj) {
                                obj.disabled = obj.auxiliar ? false : true;
                                return obj;
                            });
                            return {
                                results: data_modified
                            };
                        }
                    }
                });
                $('.combo_nits').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    minimumInputLength: 1,
                    ajax: {
                        url: 'api/nit/combo-nit',
                        headers: headers,
                        dataType: 'json',
                        processResults: function (data) {
                            return {
                                results: data.data
                            };
                        }
                    }
                });
                $('.combo_cecos').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    minimumInputLength: 1,
                    ajax: {
                        url: 'api/centro-costos/combo-centro-costo',
                        headers: headers,
                        dataType: 'json',
                        data: function (params) {
                            var query = {
                                q: params.term,
                                _type: 'query'
                            }
                            return query;
                        },
                        processResults: function (data) {
                            if (data.total.length == 1) {
                                $("#product_id").append($("<option />")
                                    .attr("value", data.results[0].id)
                                    .html(data.results[0].text)
                                ).val(data.results[0].id).trigger("change").select2("close");
                            }

                            return {
                                results: data.data
                            };
                        }
                    }
                });
                $('.combo_extracto').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    ajax: {
                        url: 'api/extracto',
                        headers: headers,
                        dataType: 'json',
                        data: function (params) {
                            var query = {
                                q: params.term,
                                id_cuenta: $("#combo_cuenta_"+rowExtracto).val(),
                                id_nit: $("#combo_nits_"+rowExtracto).val(),
                                _type: 'query'
                            }
                            return query;
                        },
                        processResults: function (data) {
                            return {
                                results: data.data
                            };
                        }
                    }
                });
                $('.form-control').keyup(function() {
                    $(this).val($(this).val().toUpperCase());
                });
            });
        }
    });

    var documento_extracto = $('#documentoExtractoTable').DataTable({
        dom: '',
        autoWidth: true,
        responsive: false,
        processing: true,
        serverSide: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'extracto',
            data: function ( d ) {
                d.id_cuenta = $("#combo_cuenta_"+rowExtracto).val();
                d.id_nit = $("#combo_nits_"+rowExtracto).val();
            }
        },
        columns: [
            {"data":'documento_referencia'},
            {"data":'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'fecha_manual'},
            {"data":'dias_cumplidos'},
            {
                "data": function (row, type, set){
                    var html = '';
                    html+= '<span href="javascript:void(0)" id="documentoextracto_'+row.documento_referencia+'_'+row.saldo+'" class="btn badge bg-gradient-primary select-documento" style="margin-bottom: 0rem !important">Seleccionar</span>&nbsp;';
                    return html;
                }
            }
        ]
    });

    function addRow(openCuenta = true) {

        var concepto = $('#concepto_'+idDocumento-1).val();

        documento_general_table.row.add({
            "id": idDocumento,
            "cuenta": '',
            "nit": '',
            "centro_costos": '',
            "documento_referencia": '',
            "debito": '',
            "credito": '',
            "concepto": '',
        }).draw(false);

        $('#card-documento-general').focus();
        document.getElementById("card-documento-general").scrollLeft = 0;

        if(concepto) $('#concepto_'+idDocumento).val(concepto);
        if(openCuenta) $('#combo_cuenta_'+idDocumento).select2('open');

        idDocumento++;
    }

    function deleteRow(idRow) {
        let dataDocumento = documento_general_table.rows().data();

        for (let row = 0; row < dataDocumento.length; row++) {
            let element = dataDocumento[row];
            if(element.id == idRow) {
                documento_general_table.row(row).remove().draw();
                if(!documento_general_table.rows().data().lengt){
                    $("#crearCapturaDocumentos").prop('disabled', true);
                }
            }
        }
        mostrarValores();
    }
    
    function changeCuentaRow(idRow) {
        let data = $('#combo_cuenta_'+idRow).select2('data')[0];
        setDisabledRows(data, idRow);
        clearRows(data, idRow);
        mostrarValores();

        if(data.cuenta) {
            if(data.cuenta.slice(0, 1) == '2' || data.cuenta.slice(0, 2) == '13') {
                if(data.naturaleza_cuenta != data.naturaleza_origen) {
                    rowExtracto = idRow;
                    $("#conten_button_"+idRow).show();
                    $("#documento_referencia_"+idRow).removeClass("normal_input");
                    $("#documento_referencia_"+idRow).prop("readonly", true)
                } else {
                    $("#conten_button_"+idRow).hide();
                    $("#documento_referencia_"+idRow).addClass("normal_input");
                    $("#documento_referencia_"+idRow).prop("readonly", false);
                }
            } else {
                $("#conten_button_"+idRow).hide();
                $("#documento_referencia_"+idRow).addClass("normal_input");
                $("#documento_referencia_"+idRow).prop("readonly", false);
                if(data.cuenta.slice(0, 2) == '11' && idRow > 0 && data.naturaleza_cuenta == data.naturaleza_origen) {
                    var dataDocumento = documento_general_table.rows().data();
                    var credito = 0;
                    if(dataDocumento.length > 0) {
                        for (let index = 0; index < dataDocumento.length; index++) {
                            var cre = $('#credito_'+index).val();
                            credito+= parseInt(cre ? cre : 0);
                        }
                        var rowBack = idRow-1;
                        $("#debito_"+idRow).val(credito);
                        $("#concepto_"+idRow).val($("#concepto_"+rowBack).val());
                        // console.log('here');
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find("#debito_"+idRow).focus();
                            $('#documentoReferenciaTable tr').find("#debito_"+idRow).select();
                        },100);
                        return;
                    }
                }
            }
        } else {
            $("#conten_button_"+idRow).hide();
            $("#documento_referencia_"+idRow).addClass("normal_input");
            $("#documento_referencia_"+idRow).prop("readonly", false);
        }
        focusNextRow(0, idRow);
    }

    function changeNitRow(idRow) {
        if($('#combo_nits_'+idRow).val()){
            focusNextRow(1, idRow);
        }
    }

    function changeCecosRow(idRow) {
        if($('#combo_cecos_'+idRow).val()){
            focusNextRow(2, idRow);
        }
    }

    function changeConsecutivo() {
        if(event.keyCode == 13){
            document.getElementById('iniciarCapturaDocumentos').click();
        }
    }

    function changeDctoRow(idRow, event, eso) {

        if(event.keyCode == 13){
            if(!$('#concepto_'+idRow).val()){
                var nit = $('#combo_nits_'+idRow).select2('data');
                if(nit.length > 0) {
                    var factura = $('#documento_referencia_'+idRow).val();
                    var fac = factura ? ' - FACTURA: ' + factura : '';
                    $('#concepto_'+idRow).val(nit[0].text + fac);
                }
            }
            focusNextRow(3, idRow);
        }
    }

    function buscarFactura(idRow, event) {

        var dataCuenta = $('#combo_cuenta_'+idRow).select2('data')[0];
        if(dataCuenta.cuenta.slice(0, 1) == '2' || dataCuenta.cuenta.slice(0, 2) == '13') {
            if(dataCuenta.naturaleza_cuenta == dataCuenta.naturaleza_origen) {
                $('#documento_load_'+idRow).show();
                if (validarFactura) {
                    validarFactura.abort();
                }
                var botonPrecionado = event.key.length == 1 ? event.key : '';
                var documento_referencia = $('#documento_referencia_'+idRow).val()+''+botonPrecionado;
                if(event.key == 'Backspace') documento_referencia = documento_referencia.slice(0, -1);
                setTimeout(function(){
                    validarFactura = $.ajax({
                        url: base_url + 'existe-factura',
                        method: 'GET',
                        data: {documento_referencia: documento_referencia},
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        validarFactura = null;
                        $('#documento_load_'+idRow).hide();
                        if(res.data == 0){
                            $('#documento_referencia_'+idRow).removeClass("is-invalid");
                            $('#documento_referencia_'+idRow).addClass("is-valid");
                        }else {
                            $('#documento_referencia_'+idRow).removeClass("is-valid");
                            $('#documento_referencia_'+idRow).addClass("is-invalid");
                        }
                    }).fail((err) => {
                        validarFactura = null;
                        if(err.statusText != "abort") {
                            $('#documento_load_'+idRow).hide();
                        }
                    });
                },100);
            }
        }
    }

    function changeDebitoRow(idRow, event) {
        if(event.keyCode == 13){
            focusNextRow(4, idRow);
        }
    }

    function changeCreditoRow(idRow, event) {
        if(event.keyCode == 13){
            focusNextRow(5, idRow);
        }
    }

    function changeConceptoRow(idRow, event) {
        if(event.keyCode == 13){

            let dataComprobante = $('#id_comprobante').select2('data')[0];
            let dataCuenta = $('#combo_cuenta_'+idRow).select2('data')[0];

            if(dataComprobante.tipo_comprobante != 4) {
                var dataDocumento = documento_general_table.rows().data();

                if(dataDocumento.length > 0) {
                    var debito = 0;
                    var credito = 0;
                    $("#crearCapturaDocumentos").show();
                    $("#crearCapturaDocumentosDisabled").hide();
                    
                    for (let index = 0; index < dataDocumento.length; index++) {
                        var deb = $('#debito_'+index).val();
                        var cre = $('#credito_'+index).val();
                        debito+= parseInt(deb ? deb : 0);
                        credito+= parseInt(cre ? cre : 0);
                    }

                    if(debito > 0 && credito > 0 && debito - credito == 0) {
                        askSaveDocumentos = false;
                        document.getElementById('crearCapturaDocumentos').click();
                        return;
                    }
                }

            }
            document.getElementById('agregarDocumentos').click();
        }
    }

    function changeConcecutivo(event) {
        if(event.keyCode == 13){
            searchCaptura();
        }
    }

    function changeFecha(event) {
        if(event.keyCode == 13){
            setTimeout(function(){
                $('#consecutivo').focus();
                $('#consecutivo').select();
            },10);
        }
    }

    function clearRows(data, idRow) {

        if(!data.exige_centro_costos) {
            $("#combo_cecos_"+idRow).val('').change();
        }

        if(!data.exige_nit) {
            $("#combo_nits_"+idRow).val('').change();
        }

        if(!data.exige_documento_referencia) {
            $("#documento_referencia_"+idRow).val('');
        }

        if(!data.exige_centro_costos) {
            $("#combo_cecos_"+idRow).val('').change();
        }

        if(data && data.naturaleza_cuenta == 1) {
            $("#debito_"+idRow).val('');
        } else {
            $("#credito_"+idRow).val('');
        }
    }

    function setDisabledRows(data, idRow) {
        if(data && data.exige_nit) {
            $("#combo_nits_"+idRow).prop('disabled', false);
        } else {
            $("#combo_nits_"+idRow).prop('disabled', true);
        }
        if(data && data.exige_centro_costos) {
            $("#combo_cecos_"+idRow).prop('disabled', false);
        } else {
            $("#combo_cecos_"+idRow).prop('disabled', true);
        }
        if(data && data.exige_documento_referencia) {
            $("#documento_referencia_"+idRow).prop('disabled', false);
        } else {
            $("#documento_referencia_"+idRow).prop('disabled', true);
        }
        if(data && data.exige_concepto) {
            $("#concepto_"+idRow).prop('disabled', false);
        } else {
            $("#concepto_"+idRow).prop('disabled', true);
        }

        if(data && data.naturaleza_cuenta == 1) {
            $("#debito_"+idRow).prop('disabled', true);
            $("#credito_"+idRow).prop('disabled', false);
        } else {
            $("#debito_"+idRow).prop('disabled', false);
            $("#credito_"+idRow).prop('disabled', true);
        }
    }

    function focusNextRow(Idcolumn, idRow) {
        var buscar = true;
        var inputsId = [
            "#combo_nits",
            "#combo_cecos",
            "#documento_referencia",
            "#debito",
            "#credito",
            "#concepto",
        ];
        
        var idNextColumn = Idcolumn;

        while (buscar) {
            if(idNextColumn >= 6) buscar = false;

            var idInput = inputsId[idNextColumn]+'_'+idRow;
            var isDisabled = $(idInput).is(":disabled");

            if(!isDisabled) {
                //COMBOS
                if(inputsId[idNextColumn] == '#combo_nits' || inputsId[idNextColumn] == '#combo_cecos') {
                    //AGREGAR DATOS COMBOS NIT
                    if(inputsId[idNextColumn] == '#combo_nits' && !$(idInput).val() && idRow > 0 ) {
                        var rowAnterior = idRow-1;
                        var dataNit = $('#combo_nits_'+rowAnterior).select2('data');
                        
                        //SI LA COLUMNA ANTERIOR TIENE DATOS
                        if(dataNit && dataNit.length > 0) {
                            var optionNit = {
                                id: dataNit[0].id,
                                text: dataNit[0].text
                            };
                            var newOptionNit = new Option(optionNit.text, optionNit.id, false, false);
                            $('#combo_nits_'+idRow).append(newOptionNit).trigger('change');
                            $('#concepto_'+idRow).val($('#concepto_'+rowAnterior).val());
                            document.getElementById("card-documento-general").scrollLeft = 250;
                        } else {
                            setTimeout(function(){
                                $(idInput).select2('open');
                            },10);
                        }
                    }else {
                        setTimeout(function(){
                            $(idInput).select2('open');
                        },10);
                    }
                } else {
                    //BUSCAR EXTRACTO
                    if(inputsId[idNextColumn] == '#documento_referencia' && idRow === rowExtracto){
                        var dataCuentaRow = $('#combo_cuenta_'+idRow).select2('data')[0];
                        
                        if (dataCuentaRow.naturaleza_cuenta != dataCuentaRow.naturaleza_origen) {
                            buscarExtracto();
                        };
                    }
                    
                    setTimeout(function(){
                        $('#documentoReferenciaTable tr').find(idInput).focus();
                    },10);

                    //COMPLETAR VALORES
                    if(inputsId[idNextColumn] == '#debito') {
                        var [debito, credito] = totalValores();
                        var total = credito - debito;
                        if(total > 0) {
                            calcularCabeza = false;
                            $(idInput).val(total);
                            setTimeout(function(){
                                console.log('seleccionando debito');
                                $('#documentoReferenciaTable tr').find(idInput).select();
                            },10);
                        }
                    }
                    if(inputsId[idNextColumn] == '#credito') {
                        var [debito, credito] = totalValores();
                        var total = debito - credito;
                        if(total > 0) {
                            calcularCabeza = false;
                            $(idInput).val(total);
                            setTimeout(function(){
                                console.log('seleccionando credito');
                                $('#documentoReferenciaTable tr').find(idInput).select();
                            },10);
                        }
                    }
                }
                buscar = false;
            }
            idNextColumn++;
        }
    }

    $(document).on('click', '#iniciarCapturaDocumentos', function () {
        searchCaptura();
    });

    $(document).on('click', '.btn-documento-extracto', function () {
        buscarExtracto();
    });

    $(document).on('click', '.select-documento', function () {
        var saldo = parseInt(this.id.split('_')[2]);
        var documentoReferencia = this.id.split('_')[1];
        let dataNit = $('#combo_nits_'+rowExtracto).select2('data')[0];
          
        $('#documento_referencia_'+rowExtracto).val(documentoReferencia);
        $("#modalDocumentoExtracto").modal('hide');  
        $('#concepto_'+rowExtracto).val(dataNit.text + ' - FACTURA: ' + documentoReferencia);

        if($('#debito_'+rowExtracto).is(":disabled")){
            $('#credito_'+rowExtracto).val(saldo);
            setTimeout(function(){
                $('#documentoReferenciaTable tr').find('#credito_'+rowExtracto).select();
            },10);
        } else {
            $('#debito_'+rowExtracto).val(saldo);
            setTimeout(function(){
                $('#documentoReferenciaTable tr').find('#debito_'+rowExtracto).select();
            },10);
        }
        // focusNextRow(3, rowExtracto);
    });

    function buscarExtracto() {
        let dataNit = $('#combo_nits_'+rowExtracto).select2('data')[0];
        $('#modal-title-documento-extracto').html(dataNit.text);
        $("#modalDocumentoExtracto").modal('show');
        documento_extracto.ajax.reload(function() {},false);
    }
    

    function searchCaptura() {
        var form = document.querySelector('#documentoFilterForm');

        if(form.checkValidity()){

            $("#id_comprobante").prop('disabled', true);
            // $("#fecha_manual").prop('disabled', true);
            $("#consecutivo").prop('disabled', true);
            
            $("#iniciarCapturaDocumentos").hide();
            $("#iniciarCapturaDocumentosLoading").show();

            let data = {
                id_comprobante: $("#id_comprobante").val(),
                fecha_manual: $("#fecha_manual").val(),
                consecutivo: $("#consecutivo").val()
            }

            $.ajax({
                url: base_url + 'documentos',
                method: 'GET',
                data: data,
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#agregarDocumentos").show();
                if(res.success){
                    var data = res.data;

                    $("#agregarDocumentos").show();
                    $("#cancelarCapturaDocumentos").show();
                    $("#iniciarCapturaDocumentosLoading").hide();
                    $("#crearCapturaDocumentos").show();
                    if(data.length > 0){
                        editandoCaptura = true;
                        idDocumento = 0;
                        for (let index = 0; index < data.length; index++) {
                            addRow(false);
                            let documento = data[index];

                            var dataCuenta = {
                                id: documento.cuenta.id,
                                text: documento.cuenta.cuenta + ' - ' + documento.cuenta.nombre
                            };
                            var newOptionCuenta = new Option(dataCuenta.text, dataCuenta.id, false, false);
                            $('#combo_cuenta_'+index).append(newOptionCuenta).trigger('change');

                            if(documento.nit) {
                                var nombre = documento.nit.tipo_contribuyente == 1 ? documento.nit.razon_social : documento.nit.primer_nombre+' '+documento.nit.primer_apellido;
                                var dataNit = {
                                    id: documento.nit.id,
                                    text: documento.nit.numero_documento+ ' - ' +nombre
                                };
                                var newOptionNit = new Option(dataNit.text, dataNit.id, false, false);
                                $('#combo_nits_'+index).append(newOptionNit).trigger('change');
                            }

                            if(documento.centro_costos) {
                                var dataCecos = {
                                    id: documento.centro_costos.id,
                                    text: documento.centro_costos.codigo+ ' - ' +documento.centro_costos.nombre,
                                };
                                var newOptionCecos = new Option(dataCecos.text, dataCecos.id, false, false);
                                $('#combo_cecos_'+index).append(newOptionCecos).trigger('change');
                            }

                            if(documento.documento_referencia) {
                                $('#documento_referencia_'+index).val(documento.documento_referencia);
                            }

                            if(parseInt(documento.debito)) {
                                $('#debito_'+index).val(parseInt(documento.debito));
                            }

                            if(parseInt(documento.credito)) {
                                $('#credito_'+index).val(parseInt(documento.credito));
                            }

                            var comprobante = $("#id_comprobante").select2('data')[0];
                            
                            documento.cuenta.naturaleza_origen = documento.cuenta.naturaleza_cuenta;

                            if(tipo_comprobante == 0) {
                                documento.cuenta.naturaleza_cuenta = documento.cuenta.naturaleza_ingresos;
                            }

                            if(tipo_comprobante == 1) {
                                documento.cuenta.naturaleza_cuenta = documento.cuenta.naturaleza_egresos;
                            }

                            if(tipo_comprobante == 2) {
                                documento.cuenta.naturaleza_cuenta = documento.cuenta.naturaleza_compras;
                            }

                            if(tipo_comprobante == 3) {
                                documento.cuenta.naturaleza_cuenta = documento.cuenta.naturaleza_ventas;
                            }

                            $('#concepto_'+index).val(documento.concepto);

                            
                            setDisabledRows(documento.cuenta, index);
                        }
                        $("#editing_documento").val("1");
                        agregarToast('exito', 'Documentos encontrados', 'Documentos cargados con exito!', true );
                        mostrarValores();
                    } else {
                        editandoCaptura = false;
                        $("#crearCapturaDocumentos").hide();
                        $("#crearCapturaDocumentosDisabled").show();
                        $("#editing_documento").val("0");
                        addRow();
                    }
                }
            }).fail((res) => {
                $("#iniciarCapturaDocumentosLoading").hide();
            });
        } else {
            form.classList.add('was-validated');
        }
    }

    $(document).on('click', '#cancelarCapturaDocumentos', function () {
        cancelarFacturas();
    });

    function cancelarFacturas() {
        var totalRows = documento_general_table.rows().data().length;
        idDocumento = 0;
        if(documento_general_table.rows().data().length){
            documento_general_table.clear([]).draw();
            for (let index = 0; index < totalRows; index++) {
                documento_general_table.row(0).remove().draw();
            }
            mostrarValores();
        }

        $("#id_comprobante").prop('disabled', false);
        // $("#fecha_manual").prop('disabled', false);
        $("#consecutivo").prop('disabled', false);

        $("#iniciarCapturaDocumentos").show();
        $("#agregarDocumentos").hide();
        $("#cancelarCapturaDocumentos").hide();
        $("#crearCapturaDocumentos").hide();
        $("#crearCapturaDocumentosDisabled").hide();
        $("#iniciarCapturaDocumentosLoading").hide();

        $('#consecutivo').val('');
        $("#id_comprobante").val('').change();
        $(".cardTotal").css("background-color", "white");
    }

    function cargarNuevoConsecutivo() {
        var totalRows = documento_general_table.rows().data().length;
        idDocumento = 0;
        if(documento_general_table.rows().data().length){
            documento_general_table.clear([]).draw();
            for (let index = 0; index < totalRows; index++) {
                documento_general_table.row(0).remove().draw();
            }
            mostrarValores();
        }

        $("#iniciarCapturaDocumentos").show();
        $("#agregarDocumentos").hide();
        $("#cancelarCapturaDocumentos").hide();
        $("#crearCapturaDocumentos").hide();
        $("#crearCapturaDocumentosDisabled").hide();
        $("#iniciarCapturaDocumentosLoading").hide();

        $('#consecutivo').val('');
        $(".cardTotal").css("background-color", "white");
        consecutivoSiguiente();
    }

    $(document).on('click', '#agregarDocumentos', function () {
        $('#agregarDocumentos').hide();
        $('#iniciarCapturaDocumentosLoading').show();
        setTimeout(function(){
            addRow();
            $('#agregarDocumentos').show();
            $('#iniciarCapturaDocumentosLoading').hide();
        },100);
    });

    function mostrarModalFormDocumentos() {
        $("#textDocumentoFormCreate").show();
        $("#textDocumentoFormUpdate").hide();
        $("#saveDocumentoGeneralLoading").hide();
        $("#saveDocumentoGeneral").show();
        $("#updateDocumentoGeneral").hide();

        $("#id_nit").val('').change();
        $("#id_cuenta").val('').change();
        $("#id_centro_costos").val('').change();
        $("#documento_referencia").val('').change();
        $("#debito").val('');
        $("#credito").val('');
        // $("#concepto").val('');

        $("#id_nit").prop('disabled', true);
        $("#id_centro_costos").prop('disabled', true);
        $("#documento_referencia").prop('disabled', true);
        $("#debito").prop('disabled', true);
        $("#credito").prop('disabled', true);
        $("#concepto").prop('disabled', true);
        
        $("#documentoGeneralFormModal").modal('show');
    }

    var $comboTipoDocumento = $('#id_comprobante').select2({
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
    
    $("#id_comprobante").on('change', function(e) {
        var data = $(this).select2('data');
        if(data.length){
            setTimeout(function(){
                $('#fecha_manual').focus();
                $('#fecha_manual').select();
            },10);
            tipo_comprobante = data[0].tipo_comprobante;
            consecutivoSiguiente();
        }
    });

    $("#fecha_manual").on('change', function(e) {
        var data = $('#fecha_manual').val();
        if(event.keyCode == 13){
            setTimeout(function(){
                $('#consecutivo').focus();
                $('#consecutivo').select();
            },10);
        }
    });

    function consecutivoSiguiente() {
        var id_comprobante = $('#id_comprobante').val();
        var fecha_manual = $('#fecha_manual').val();
        if(id_comprobante && fecha_manual) {
            $("#consecutivo").prop('disabled', true);
            $("#iniciarCapturaDocumentos").hide();
            $("#iniciarCapturaDocumentosLoading").show();
            let data = {
                id_comprobante: id_comprobante,
                fecha_manual: fecha_manual
            }
            $.ajax({
                url: base_url + 'consecutivo',
                method: 'GET',
                data: data,
                headers: headers,
                dataType: 'json',
            }).done((res) => {
                $("#iniciarCapturaDocumentos").show();
                $("#iniciarCapturaDocumentosLoading").hide();
                // setTimeout(function(){
                //     $('#consecutivo').focus();
                //     $('#consecutivo').select();
                // },100);
                if(res.success){
                    $("#consecutivo").val(res.data);
                    $("#consecutivo").prop('disabled', false);
                }
            }).fail((err) => {
                $("#iniciarCapturaDocumentos").show();
                $("#iniciarCapturaDocumentosLoading").hide();
                $("#consecutivo").prop('disabled', false);
            });
        }
    }

    $("#id_cuenta").on('change', function(e) {
        var data = $(this).select2('data');
        
        if(data.length <= 0){
            return;
        }
        data = data[0];
        if(data.exige_nit){
            $("#id_nit").prop('disabled', false);
        } else {
            $("#id_nit").prop('disabled', true);
            $("#id_nit").val('').change();
        }

        if(data.exige_centro_costos){
            $("#id_centro_costos").prop('disabled', false);
        } else {
            $("#id_centro_costos").prop('disabled', true);
            $("#id_centro_costos").val('').change();
        }

        if(data.exige_concepto){
            $("#concepto").prop('disabled', false);
        } else {
            $("#concepto").prop('disabled', true);
            $("#concepto").val('');
        }

        if(data.exige_documento_referencia){
            $("#documento_referencia").prop('disabled', false);
        } else {
            $("#documento_referencia").prop('disabled', true);
            $("#documento_referencia").val('');
        }

        $(".hide").show();

        if(data.naturaleza_cuenta){
            $("#credito").prop('disabled', false);
            $("#debito").prop('disabled', true);
            $("#debito").val('');
        }else{
            $("#debito").prop('disabled', false);
            $("#credito").prop('disabled', true);
            $("#credito").val('');
        };
    });

    $(document).on('click', '#saveDocumentoGeneral', function () {

        var nit = $('#id_nit').select2('data');
        var cuenta = $('#id_cuenta').select2('data');
        var centroCostos = $('#id_centro_costos').select2('data');

        let dataTable = {
            "id": idDocumento, 
            "cuenta": cuenta.length > 0 ? cuenta[0] : "",
            "nit": nit.length > 0 ? nit[0] : "",
            "centro_costos": centroCostos.length > 0 ? centroCostos[0] : "",
            "documento_referencia": $("#documento_referencia").val(),
            "debito": $("#debito").val(),
            "credito": $("#credito").val(),
            "concepto": $("#concepto").val()
        };

        documento_general_table.row.add(dataTable).draw(false);
        idDocumento++;
        $("#documentoGeneralFormModal").modal('hide');

        $("#crearCapturaDocumentos").prop('disabled', false);

        mostrarValores();
    });

    documento_general_table.on('click', '.drop-documento-general    ', function() {
        var trPlanCuenta = $(this).closest('tr');
        var id = this.id.split('_')[1];
        var index = getRowById(id, documento_general_table);
        Swal.fire({
            title: 'Eliminar documento?',
            text: "Desea eliminar documento de la tabla",
            type: 'warning',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Borrar!',
            reverseButtons: true,
        }).then((result) => {
            if (result.value){
                documento_general_table.row(index).remove().draw();
                if(!documento_general_table.rows().data().length){
                    $("#crearCapturaDocumentos").prop('disabled', true);
                }
                mostrarValores();
                agregarToast('error', 'Eliminación exitosa', 'Documento eliminado con exito!', true );
            }
        })
    });

    $(document).on('click', '#updateDocumentoGeneral', function () {
        var id = $('#id_documento').val();
        var data = getDataById(id, documento_general_table);
        var row = getRowById(id, documento_general_table);

        var nit = $('#id_nit').select2('data');
        var cuenta = $('#id_cuenta').select2('data');
        var centroCostos = $('#id_centro_costos').select2('data');

        let dataTable = {
            "id": idDocumento, 
            "cuenta": cuenta.length > 0 ? cuenta[0] : "",
            "nit": nit.length > 0 ? nit[0] : "",
            "centro_costos": centroCostos.length > 0 ? centroCostos[0] : "",
            "documento_referencia": $("#documento_referencia").val(),
            "debito": $("#debito").val(),
            "credito": $("#credito").val(),
            "concepto": $("#concepto").val()
        };
        documento_general_table.row(row).data(dataTable).draw(false);
        
        $("#documentoGeneralFormModal").modal('hide');

        mostrarValores();
    });

    function mostrarValores(){

        if(calcularCabeza) {
            var [debito, credito] = totalValores();
            general_debito
            general_credito
            general_diferencia
            if(debito-credito > 0){
                $(".cardTotal").css("background-color", "lightpink");
                $(".general_diferencia").css("background-color", "lightpink");
            } else if (debito-credito < 0){
                $(".cardTotal").css("background-color", "lightpink");
            } else if (debito-credito == 0 && debito > 0 || credito > 0){
                $(".cardTotal").css("background-color", "lightgreen");
            } else {
                $(".cardTotal").css("background-color", "white");
            }
    
            $("#general_debito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(debito));
            $("#general_credito").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(credito));
            $("#general_diferencia").text(new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(debito-credito));
        } else {
            calcularCabeza = true;
        }
    }

    function totalValores() {

        var debito = credito = 0;
        var dataDocumento = documento_general_table.rows().data();
        
        if(dataDocumento.length > 0) {
            $("#crearCapturaDocumentos").show();
            $("#crearCapturaDocumentosDisabled").hide();
            
            for (let index = 0; index < dataDocumento.length; index++) {

                var deb = $('#debito_'+index).val();
                var cre = $('#credito_'+index).val();

                debito+= parseInt(deb ? deb : 0);
                credito+= parseInt(cre ? cre : 0);
            }
        } else {
            $("#crearCapturaDocumentos").hide();
            $("#crearCapturaDocumentosDisabled").show();
        }
        
        return [debito, credito];
    }

    $(document).on('click', '#crearCapturaDocumentos', function () {

        var debito = 0;
        var credito = 0;
        var diferencia = 0;

        var dataDocumento = documento_general_table.rows().data();
        
        if(dataDocumento.length > 0) {
            for (let index = 0; index < dataDocumento.length; index++) {
                var deb = $('#debito_'+index).val();
                var cre = $('#credito_'+index).val();
                debito+= parseInt(deb ? deb : 0);
                credito+= parseInt(cre ? cre : 0);
            }
        }

        diferencia = debito - credito;

        var texto = 'Desea guardar documento en la tabla?';
        var type = 'question';

        if (diferencia != 0) {
            texto = "Documento descuadrado, desea guardarlo en la tabla?";
            type = "warning";
        } else if (debito == 0 && credito == 0) {
            agregarToast('warning', 'Creación herrada', 'Sin datos para guardar en la tabla', true);
            return;
        }

        if(askSaveDocumentos) {
            Swal.fire({
                title: 'Guardar documento?',
                text: texto,
                icon: type,
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Guardar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    saveDocumentos();
                }
            })
        } else {
            askSaveDocumentos = true;
            saveDocumentos();
        }

    });

    function saveDocumentos() {
        $("#agregarDocumentos").hide();
        $("#crearCapturaDocumentos").hide();
        $("#iniciarCapturaDocumentos").hide();
        $("#cancelarCapturaDocumentos").hide();
        $("#crearCapturaDocumentosDisabled").hide();
        $("#iniciarCapturaDocumentosLoading").show();
        let data = {
            documento: getDocumentos(),
            id_comprobante: $("#id_comprobante").val(),
            consecutivo: $("#consecutivo").val(),
            fecha_manual: $("#fecha_manual").val(),
            editing_documento: $("#editing_documento").val(),
        }
        $.ajax({
            url: base_url + 'documentos',
            method: 'POST',
            data: JSON.stringify(data),
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            if(res.success){
                cargarNuevoConsecutivo();
                agregarToast('exito', 'Creación exitosa', 'Documentos creados con exito!', true);
            } else {
                $("#agregarDocumentos").show();
                $("#crearCapturaDocumentos").show();
                $("#iniciarCapturaDocumentos").hide();
                $("#cancelarCapturaDocumentos").show();
                $("#crearCapturaDocumentosDisabled").hide();
                $("#iniciarCapturaDocumentosLoading").hide();

                var mensaje = res.mensages;
                var errorsMsg = "";
                for (field in mensaje) {
                    var errores = mensaje[field];
                    for (campo in errores) {
                        errorsMsg += "- "+errores[campo]+" <br>";
                    }
                };
                agregarToast('error', 'Creación herrada', errorsMsg);
            }
        }).fail((err) => {
            $("#agregarDocumentos").show();
            $("#crearCapturaDocumentos").show();
            $("#iniciarCapturaDocumentos").hide();
            $("#cancelarCapturaDocumentos").show();
            $("#crearCapturaDocumentosDisabled").hide();
            $("#iniciarCapturaDocumentosLoading").hide();

            var mensaje = err.responseJSON.message;
            var errorsMsg = "";
            for (field in mensaje) {
                var errores = mensaje[field];
                for (campo in errores) {
                    errorsMsg += field+": "+errores[campo]+" <br>";
                }
                
            };
            agregarToast('error', 'Creación herrada', errorsMsg);
        });
    }

    function getDocumentos(){
        var data = [];

        var dataDocumento = documento_general_table.rows().data();
        if(dataDocumento.length > 0){
            for (let index = 0; index < dataDocumento.length; index++) {
                
                var debito = $('#debito_'+index).val();
                var credito = $('#credito_'+index).val();

                if(debito || credito) {

                    var dctrf = $('#documento_referencia_'+index).val();
                    var concepto = $('#concepto_'+index).val();
                    var cuenta = $('#combo_cuenta_'+index).val();
                    var nit = $('#combo_nits_'+index).val();
                    var cecos = $('#combo_cecos_'+index).val();

                    data.push({
                        id_cuenta: cuenta ? parseInt(cuenta) : '',
                        id_nit: nit ? parseInt(nit) : '',
                        id_centro_costos: cecos ? parseInt(cecos) : '',
                        documento_referencia: dctrf ? dctrf : '',
                        debito: debito ? parseFloat(debito) : 0,
                        credito: credito ? parseFloat(credito) : 0,
                        concepto: concepto ? concepto : '',
                    });
                }
            }
        }
        return data;
    }

    

</script>