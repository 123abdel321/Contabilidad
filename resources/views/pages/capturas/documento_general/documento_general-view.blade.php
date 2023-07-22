@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Documentos generales'])

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

        .odd {
            text-align: -webkit-center !important;
        }

        .even {
            text-align: -webkit-center !important;
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
                <div class="row">
                    <div class="col-12 col-md-4 col-sm-4" style="margin-top: 5px;">
                        <h6 style="float: left;">DEBITO:&nbsp; </h6><h6 id="general_debito">$0</h6>
                    </div>
                    <div class="col-12 col-md-4 col-sm-4" style="margin-top: 5px;">
                        <h6 style="float: left;">CREDITO:&nbsp; </h6><h6 id="general_credito">$0</h6>
                    </div>
                    <div class="col-12 col-md-4 col-sm-4" style="margin-top: 5px;">
                        <h6 style="float: left;">DIFERENCIA:&nbsp; </h6><h6 id="general_diferencia">$0</h6>
                    </div>
                </div>
            </div>

            <div id="card-documento-general" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
                <div class="card-body" >

                    @include('pages.capturas.documento_general.documento_general-table')
                    
                </div>
            </div>
            
            @include('pages.capturas.documento_general.documento_general-form')
        </div>
    </div>
@endsection

@push('js')

    <script>
        var fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);
        var idDocumento = 1;
        var editandoCaptura = 0;
        $('#fecha_manual').val(fecha);

        var documento_table = $('#documentoReferenciaTable').DataTable({
            dom: '',
            fixedHeader: true,
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
                {
                    "data": function (row, type, set, col){
                        return '<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteRow('+col.row+')"><i class="fas fa-trash-alt"></i></span>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        return '<select class="form-control form-control-sm combo_cuenta combo-grid" id="combo_cuenta_'+col.row+'" onchange="changeCuentaRow('+col.row+')"></select>';
                    },
                },
                {
                    "data": function (row, type, set, col){
                        return '<select class="form-control form-control-sm combo_nits combo-grid" id="combo_nits_'+col.row+'" onchange="changeNitRow('+col.row+')" disabled></select>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        return '<select class="form-control form-control-sm combo_cecos combo-grid" id="combo_cecos_'+col.row+'" onchange="changeCecosRow('+col.row+')" disabled></select>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        return '<input type="text" class="form-control form-control-sm" id="documento_referencia_'+col.row+'" onkeypress="changeDctoRow('+col.row+', event)" style="width: 100% !important;" disabled>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        return '<input type="text" class="form-control form-control-sm input_number debito_input" id="debito_'+col.row+'" onkeypress="changeDebitoRow('+col.row+', event)" onfocusout="mostrarValores()" style="width: 130px !important;" disabled>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        return '<input type="text" class="form-control form-control-sm input_number credito_input" id="credito_'+col.row+'" onkeypress="changeCreditoRow('+col.row+', event)" onfocusout="mostrarValores()" style="width: 130px !important;" disabled>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        return '<input type="text" class="form-control form-control-sm" id="concepto_'+col.row+'" onkeypress="changeConceptoRow('+col.row+', event)" placeholder="SIN OBSERVACIÓN" style="width: 250px !important;" disabled>';
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
                    $('.combo_nits').select2({
                        theme: 'bootstrap-5',
                        delay: 250,
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
                        ajax: {
                            url: 'api/centro-costos/combo-centro-costo',
                            headers: headers,
                            dataType: 'json',
                            processResults: function (data) {
                                return {
                                    results: data.data
                                };
                            }
                        }
                    });
                    
                });
            }
        });

        function addRow(openCuenta = true) {
            documento_table.row.add({
                "id": '',
                "cuenta": '',
                "nit": '',
                "centro_costos": '',
                "documento_referencia": '',
                "debito": '',
                "credito": '',
                "concepto": '',
            }).draw(false);
            var rows = documento_table.rows().data().length;
            $('#card-documento-general').focus();
            document.getElementById("card-documento-general").scrollLeft = 0;
            rows = rows-1;
            // console.log('#row: ',rows);
            if(openCuenta){
                $('#combo_cuenta_'+rows).select2('open');
            }
        }

        function deleteRow(idRow) {
            documento_table.row(idRow).remove().draw();
            if(!documento_table.rows().data().length){
                $("#crearCapturaDocumentos").prop('disabled', true);
            }
            mostrarValores();
        }

        function changeCuentaRow(idRow) {
            let data = $('#combo_cuenta_'+idRow).select2('data')[0];
            setDisabledRows(data, idRow);
            focusNextRow(0, idRow);
            clearRows(data, idRow);
            mostrarValores();
        }

        function changeNitRow(idRow) {
            focusNextRow(1, idRow);
        }

        function changeCecosRow(idRow) {
            focusNextRow(2, idRow);
        }

        function changeDctoRow(idRow, event) {
            if(event.keyCode == 13){
                focusNextRow(3, idRow);
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
                document.getElementById('agregarDocumentos').click();
                // addRow();
                // console.log('add concepto')
                // var idRow = idRow+1;
                // setTimeout(function(){
                //     $('#combo_cuenta_'+idRow).select2('open');
                    // $('#documentoReferenciaTable tr').find('#select2-search__field').focus();
                // },10);
            }
        }

        function changeConcecutivo(event) {
            if(event.keyCode == 13){
                searchCaptura();
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
            
            if(idRow >= 6) {
                console.log('agregar nueva columna');
                return;
            }

            var idNextColumn = Idcolumn;

            while (buscar) {
                if(idNextColumn >= 6) buscar = false;

                var idInput = inputsId[idNextColumn]+'_'+idRow;
                var isDisabled = $(idInput).is(":disabled");
                if(!isDisabled) {
                    if(inputsId[idNextColumn] == '#combo_nits' || inputsId[idNextColumn] == '#combo_cecos') {
                        setTimeout(function(){
                            $(idInput).select2('open');
                        },10);
                    } else {
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find(idInput).focus();
                        },10);
                    }
                    buscar = false;
                }
                idNextColumn++;
            }
        }

        $(document).on('click', '#iniciarCapturaDocumentos', function () {
            searchCaptura();
        });

        function searchCaptura() {
            var form = document.querySelector('#documentoFilterForm');

            if(form.checkValidity()){

                $("#id_comprobante").prop('disabled', true);
                $("#fecha_manual").prop('disabled', true);
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
                            idDocumento = 1;
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
                                        text: documento.nit.documento+ ' - ' +nombre
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

                                $('#concepto_'+index).val(documento.concepto);

                                setDisabledRows(documento.cuenta, index);
                            }
                            $("#editing_documento").val("1");
                            swalFire('Documentos encontrados', 'Documentos cargados con exito!');
                            mostrarValores();
                        } else {
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

        function cancelarFacturas(){
            var totalRows = documento_table.rows().data().length;
            idDocumento = 1;
            if(documento_table.rows().data().length){
                documento_table.clear([]).draw();
                for (let index = 0; index < totalRows; index++) {
                    documento_table.row(0).remove().draw();
                }
                mostrarValores();
            }

            $("#id_comprobante").prop('disabled', false);
            $("#fecha_manual").prop('disabled', false);
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

        var $comboCuenta = $('#id_cuenta').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#documentoGeneralFormModal'),
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

        var $comboNit = $('#id_nit').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#documentoGeneralFormModal'),
            delay: 250,
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

        var $comboCentroCostos = $('.combo_cecos').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#documentoGeneralFormModal'),
            delay: 250,
            ajax: {
                url: 'api/centro-costos/combo-centro-costo',
                headers: headers,
                dataType: 'json',
                processResults: function (data) {
                    return {
                        results: data.data
                    };
                }
            }
        });

        var $comboCentroCostos = $('#id_centro_costos').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#documentoGeneralFormModal'),
            delay: 250,
            ajax: {
                url: 'api/centro-costos/combo-centro-costo',
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
                consecutivoSiguiente();
            }
        });

        $("#fecha_manual").on('change', function(e) {
            var data = $('#fecha_manual').val();
            if(data){
                consecutivoSiguiente();
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

            documento_table.row.add(dataTable).draw(false);
            idDocumento++;
            $("#documentoGeneralFormModal").modal('hide');

            $("#crearCapturaDocumentos").prop('disabled', false);

            mostrarValores();
        });
        
        documento_table.on('click', '.edit-documento-general', function() {

            var trPlanCuenta = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, documento_table);

            $("#id_nit").val('').change();
            $("#id_cuenta").val('').change();
            $("#id_centro_costos").val('').change();
            $("#documento_referencia").val('').change();
            $("#debito").val('');
            $("#credito").val('');
            $("#concepto").val('');

            if(data.cuenta){
                var newOption = new Option(data.cuenta.text, data.cuenta.id, false, false);
                $comboCuenta.append(newOption).trigger('change');
                $comboCuenta.val(data.cuenta.id).trigger('change');
                
            }

            if(data.nit){
                var newOption = new Option(data.nit.text, data.nit.id, false, false);
                $comboNit.append(newOption).trigger('change');
                $comboNit.val(data.nit.id).trigger('change');
            }

            if(data.centro_costos){
                var newOption = new Option(data.centro_costos.text, data.centro_costos.id, false, false);
                $comboCentroCostos.append(newOption).trigger('change');
                $comboCentroCostos.val(data.centro_costos.id).trigger('change');
            }

            if(data.cuenta.exige_nit){
                $("#id_nit").prop('disabled', false);
            } else {
                $("#id_nit").prop('disabled', true);
                $("#id_nit").val('').change();
            }

            if(data.cuenta.exige_centro_costos){
                $("#id_centro_costos").prop('disabled', false);
            } else {
                $("#id_centro_costos").prop('disabled', true);
                $("#id_centro_costos").val('').change();
            }

            if(data.cuenta.exige_concepto){
                $("#concepto").prop('disabled', false);
            } else {
                $("#concepto").prop('disabled', true);
                $("#concepto").val('');
            }

            if(data.cuenta.exige_documento_referencia){
                $("#documento_referencia").prop('disabled', false);
            } else {
                $("#documento_referencia").prop('disabled', true);
                $("#documento_referencia").val('');
            }

            if(data.cuenta.naturaleza_cuenta){
                $("#credito").prop('disabled', false);
                $("#debito").prop('disabled', false);
                $("#debito").val('');
            }else{
                $("#debito").prop('disabled', false);
                $("#credito").prop('disabled', false);
                $("#credito").val('');
            };

            $("#documento_referencia").val(data.documento_referencia);
            $("#debito").val(data.debito);
            $("#credito").val(data.credito);
            $("#concepto").val(data.concepto);
            $("#id_documento").val(data.id);

            $("#textDocumentoFormCreate").hide();
            $("#textDocumentoFormUpdate").show();
            $("#saveDocumentoGeneral").hide();
            $("#updateDocumentoGeneral").show();
            $("#saveDocumentoGeneralLoading").hide();

            $("#documentoGeneralFormModal").modal('show');
        });

        documento_table.on('click', '.drop-documento-general    ', function() {
            var trPlanCuenta = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var index = getRowById(id, documento_table);
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
                    documento_table.row(index).remove().draw();
                    if(!documento_table.rows().data().length){
                        $("#crearCapturaDocumentos").prop('disabled', true);
                    }
                    mostrarValores();
                    swalFire('Eliminación exitosa', 'Documento eliminado con exito!');
                }
            })
        });

        $(document).on('click', '#updateDocumentoGeneral', function () {
            var id = $('#id_documento').val();
            var data = getDataById(id, documento_table);
            var row = getRowById(id, documento_table);

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
            documento_table.row(row).data(dataTable).draw(false);
            
            $("#documentoGeneralFormModal").modal('hide');

            mostrarValores();
        });

        function mostrarValores(){
            var debito = 0;
            var credito = 0;

            var dataDocumento = documento_table.rows().data();
            
            if(dataDocumento.length > 0) {
                $("#crearCapturaDocumentos").show();
                $("#crearCapturaDocumentosDisabled").hide();
                
                for (let index = 0; index < dataDocumento.length; index++) {
                    // $('#debito_'+index).val();
                    var deb = $('#debito_'+index).val();
                    var cre = $('#credito_'+index).val();
                    debito+= parseInt(deb ? deb : 0);
                    credito+= parseInt(cre ? cre : 0);
                }
            } else {
                $("#crearCapturaDocumentos").hide();
                $("#crearCapturaDocumentosDisabled").show();
            }

            if(debito-credito > 0){
                $(".cardTotal").css("background-color", "lightpink");
            } else if (debito-credito < 0){
                $(".cardTotal").css("background-color", "lightpink");
            } else if (debito-credito == 0 && debito > 0 || credito > 0){
                $(".cardTotal").css("background-color", "lightgreen");
            } else {
                $(".cardTotal").css("background-color", "white");
            }

            $("#general_debito").text('$'+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(debito));
            $("#general_credito").text('$'+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(credito));
            $("#general_diferencia").text('$'+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(debito-credito));
        }

        $(document).on('click', '#crearCapturaDocumentos', function () {

            var debito = 0;
            var credito = 0;
            var diferencia = 0;

            var dataDocumento = documento_table.rows().data();
            
            if(dataDocumento.length > 0) {
                for (let index = 0; index < dataDocumento.length; index++) {
                    var deb = $('#debito_'+index).val();
                    var cre = $('#credito_'+index).val();
                    debito+= parseInt(deb ? deb : 0);
                    credito+= parseInt(cre ? cre : 0);
                }
            }

            diferencia = debito - credito;

            var texto = 'Desea guardar documentos en la tabla';
            var type = 'question';

            if (diferencia != 0) {
                texto = "Documentos descuadrados, desea guardarlos en la tabla?";
                type = "warning";
            } else if (debito == 0 && credito == 0) {
                swalFire('Creación herrada', 'Sin datos para guardar en la tabla', false);
                return;
            }

            Swal.fire({
                title: 'Guardar documentos?',
                text: texto,
                icon: type,
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Guardar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
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
                            cancelarFacturas();
                            mostrarValores();
                            $('#crearCapturaDocumentosDisabled').hide();
                            swalFire('Creación exitosa', 'Documentos creados con exito!');
                            $(".cardTotal").css("background-color", "white");
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
                            swalFire('Creación herrada', errorsMsg, false);
                        }
                    }).fail((err) => {

                        $("#agregarDocumentos").show();
                        $("#crearCapturaDocumentos").show();
                        $("#iniciarCapturaDocumentos").hide();
                        $("#cancelarCapturaDocumentos").show();
                        $("#crearCapturaDocumentosDisabled").hide();
                        $("#iniciarCapturaDocumentosLoading").hide();

                        var mensaje = err.mensages;
                        var errorsMsg = "";
                        for (field in mensaje) {
                            var errores = mensaje[field];
                            for (campo in errores) {
                                errorsMsg += "- "+errores[campo]+" <br>";
                            }
                            
                        };
                        swalFire('Creación herrada', errorsMsg, false);
                    });
                }
            })
        });

        function getDocumentos(){
            var data = [];

            var dataDocumento = documento_table.rows().data();
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

@endpush
