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

        .combo-grid {
            width: 200px !important;
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

    </style>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="card mb-4">
                <div class="card-body" style="padding: 0 !important;">

                    @include('pages.capturas.documento_general.documento_general-filter')

                </div>
            </div>
            <div class="card" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
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

            <div class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
                <div class="card-body">

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
                        // if(row.cuenta){
                        //     return row.cuenta.text;
                        // }
                        return '<select class="form-control form-control-sm combo_cuenta combo-grid" id="combo_cuenta_'+col.row+'" onchange="changeCuentaRow('+col.row+')"></select>';
                    },
                },
                {
                    "data": function (row, type, set, col){
                        // if(row.nit){
                        //     return row.nit.text;
                        // }
                        return '<select class="form-control form-control-sm combo_nits combo-grid" id="combo_nits_'+col.row+'" onchange="changeNitRow('+col.row+')" disabled></select>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        // if(row.centro_costos){
                        //     return row.centro_costos.text;
                        // }
                        return '<select class="form-control form-control-sm combo_cecos combo-grid" id="combo_cecos_'+col.row+'" onchange="changeCecosRow('+col.row+')" disabled></select>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        // if(row.documento_referencia){
                        //     return documento_referencia
                        // }
                        return '<input type="text" class="form-control form-control-sm" id="documento_referencia_'+col.row+'" onkeypress="changeDctoRow('+col.row+', event)" style="width: 100px !important;" disabled>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        // if(row.debito){
                        //     return '$'+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(row.debito);
                        // }
                        return '<input type="text" class="form-control form-control-sm input_number debito_input" id="debito_'+col.row+'" onkeypress="changeDebitoRow('+col.row+', event)" onfocusout="mostrarValores()" style="width: 100px !important;" disabled>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        // if(row.credito){
                        //     return new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(row.credito);
                        // }
                        return '<input type="text" class="form-control form-control-sm input_number credito_input" id="credito_'+col.row+'" onkeypress="changeCreditoRow('+col.row+', event)" onfocusout="mostrarValores()" style="width: 100px !important;" disabled>';
                    }
                },
                {
                    "data": function (row, type, set, col){
                        // if(row.concepto){
                        //     return concepto
                        // }
                        return '<input type="text" class="form-control form-control-sm" id="concepto_'+col.row+'" onkeypress="changeConceptoRow('+col.row+', event)" placeholder="SIN OBSERVACIÓN" style="width: 150px !important;" disabled>';
                    }
                }
            ],
            columnDefs: [{
                // 'targets': [1,3],
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
                    $(".input_number").inputmask({
                        alias: 'decimal',
                        rightAlign: true,
                        groupSeparator: '.',
                        autoGroup: true
                    });
                });
            }
        });

        function addRow() {
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

            rows = rows-1;
            $('#combo_cuenta_'+rows).select2('open');
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
            mostrarValores();
        }

        function changeCreditoRow(idRow, event) {
            if(event.keyCode == 13){
                focusNextRow(5, idRow);
            }
            mostrarValores();
        }

        function changeConceptoRow(idRow, event) {
            if(event.keyCode == 13){
                addRow();
                var idRow = idRow+1;
                setTimeout(function(){
                    $('#documentoReferenciaTable tr').find('#combo_cuenta_'+idRow).focus();
                    $('#combo_cuenta_'+idRow).select2('open');
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
            if(data.naturaleza_cuenta){
                $("#credito_"+idRow).val('');
            } else {
                $("#debito_"+idRow).val('');
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
            if(data && data.naturaleza_cuenta) {
                $("#debito_"+idRow).prop('disabled', false);
                $("#credito_"+idRow).prop('disabled', true);
            } else {
                $("#debito_"+idRow).prop('disabled', true);
                $("#credito_"+idRow).prop('disabled', false);
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
                        var data = res.data
                        $("#agregarDocumentos").show();
                        $("#cancelarCapturaDocumentos").show();
                        $("#iniciarCapturaDocumentosLoading").hide();
                        $("#crearCapturaDocumentos").show();
                        if(data.length > 0){
                            idDocumento = 1;
                            for (let index = 0; index < data.length; index++) {
                                var documento = data[index];
                                
                                let dataTable = {
                                    "id": idDocumento, 
                                    "cuenta": documento.cuenta ? documento.cuenta : "",
                                    "nit": documento.nit ? documento.nit : "",
                                    "centro_costos": documento.centro_costos ? documento.centro_costos : "",
                                    "documento_referencia": documento.documento_referencia,
                                    "debito": documento.debito,
                                    "credito": documento.credito,
                                    "concepto": documento.concepto,
                                };
                                if(documento.cuenta){
                                    dataTable.cuenta.text = documento.cuenta.cuenta + ' - ' + documento.cuenta.nombre;
                                }
                                if(documento.nit){
                                    dataTable.nit.text = documento.nit.numero_documento + ' - ' + documento.nit.primer_nombre;
                                }
                                if(documento.centro_costos){
                                    dataTable.centro_costos.text = documento.centro_costos.codigo + ' - ' + documento.centro_costos.nombre;
                                }
    
                                documento_table.row.add(dataTable).draw(false);
                                idDocumento++;
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
        });

        $(document).on('click', '#cancelarCapturaDocumentos', function () {
            cancelarFacturas();
        });

        function cancelarFacturas(){

            idDocumento = 1;
            if(documento_table.rows().data().length){
                documento_table.clear([]).draw();
                documento_table.rows().destroy();
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

            $('#consecutivo').val('');
            $("#id_comprobante").val('').change();
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
                    var deb = document.getElementById('debito_'+index).inputmask.unmaskedvalue();
                    var cre = document.getElementById('credito_'+index).inputmask.unmaskedvalue();
                    debito+= parseInt(deb ? deb : 0);
                    credito+= parseInt(cre ? cre : 0);
                }
            } else {
                $("#crearCapturaDocumentos").hide();
                $("#crearCapturaDocumentosDisabled").show();
            }


            if(debito > 0){
                $("#general_debito").css("color", "green");
            } else if (debito < 0){
                $("#general_debito").css("color", "red");
            } else {
                $("#general_debito").css("color", "#344767");
            }

            if(credito > 0){
                $("#general_credito").css("color", "red");
            } else if (credito < 0){
                $("#general_credito").css("color", "red");
            } else {
                $("#general_credito").css("color", "#344767");
            }

            if(debito-credito > 0){
                $("#general_diferencia").css("color", "green");
            } else if (debito-credito < 0){
                $("#general_diferencia").css("color", "red");
            } else {
                $("#general_diferencia").css("color", "#344767");
            }

            $("#general_debito").text('$'+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(debito));
            $("#general_credito").text('$'+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(credito <= 0 ? credito : credito * -1));
            $("#general_diferencia").text('$'+ new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(debito-credito));
        }

        $(document).on('click', '#crearCapturaDocumentos', function () {
            Swal.fire({
                title: 'Guardar documentos?',
                text: "Desea guardar documentos en la tabla",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonText: 'Guardar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
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
                        } else {
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
                        $('#savePlanCuenta').show();
                        $('#savePlanCuentaLoading').hide();
                        swalFire('Creación herrada', 'Error crear Documentos!', false);
                    });
                }
            })
        });

        function getDocumentos(){
            var data = [];

            var dataDocumento = documento_table.rows().data();
            if(dataDocumento.length > 0){
                for (let index = 0; index < dataDocumento.length; index++) {
                    
                    var debito = document.getElementById('debito_'+index).inputmask.unmaskedvalue();
                    var credito = document.getElementById('credito_'+index).inputmask.unmaskedvalue();

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
