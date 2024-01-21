var fecha = null;
var idDocumento = 0;
var editandoCaptura = 0;
var rowExtracto = '';
var tipo_comprobante = '';
var validarFactura = null;
var cambiarNit = true;
var calcularCabeza = true;
var askSaveDocumentos = true;
var documento_general_table = null;
var documento_extracto = null;
var $comboComprobante = null;
var guardarDocumentoGeneral = false;

function documentogeneralInit() {

    fecha = dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-'+("0" + (dateNow.getDate())).slice(-2);

    $('#fecha_manual_documento').val(fecha);

    documento_general_table = $('#documentoReferenciaTable').DataTable({
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
                    return '<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteRow('+idDocumento+')" id="droprow_'+idDocumento+'"><i class="fas fa-trash-alt"></i></span>';
                }
            },
            {//CUENTA
                "data": function (row, type, set, col){
                    return '<select class="form-control form-control-sm combo_cuenta combo-grid" id="combo_cuenta_'+idDocumento+'"></select>';
                },
            },
            {//NIT
                "data": function (row, type, set, col){
                    return '<select class="form-control form-control-sm combo_nits combo-grid" id="combo_nits_'+idDocumento+'" onchange="changeNitRow('+idDocumento+')" disabled></select>';
                }
            },
            {//CECOS
                "data": function (row, type, set, col){
                    return '<select class="form-control form-control-sm combo_cecos combo-grid" id="combo_cecos_'+idDocumento+'" onchange="changeCecosRow('+idDocumento+')" onkeypress="onkeypressCecosRow('+idDocumento+', event)" disabled></select>';
                }
            },
            {//DCTO REFE
                "data": function (row, type, set, col){
                    var html = '';
                    html+= '';
                    html+= '    <div class="input-group" style="width: 180px; height: 30px;">';
                    html+= '        <input type="text" class="form-control form-control-sm documento_referencia_row" id="documento_referencia_'+idDocumento+'" onkeydown="buscarFactura('+idDocumento+', event)" onkeypress="changeDctoRow('+idDocumento+', event)" keyup style="height: 33px;" value="" disabled readonly>';
                    html+= '        <i class="fa fa-spinner fa-spin fa-fw documento-load" id="documento_load_'+idDocumento+'" style="display: none;"></i>';
                    html+= '        <div class="valid-feedback info-factura">Nueva factura</div>';
                    html+= '        <div class="invalid-feedback info-factura">Factura existente</div>';
                    html+= '        <div class="input-group-append button-group" id="conten_button_'+idDocumento+'"><span href="javascript:void(0)" class="btn badge bg-gradient-secondary btn-group btn-documento-extracto" style="min-width: 40px; margin-right: 3px; border-radius: 0px 7px 7px 0px; height: 33px;"><i class="fas fa-search" style="font-size: 17px; margin-top: 3px;"></i><b style="vertical-align: text-top;"></b></span></div></div>';
                    html+= '    </div>';
                    html+= '';
    
                    return html;
                }
            },
            {//DEBITO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm input_number debito_input" id="debito_${idDocumento}" onkeypress="changeDebitoRow(${idDocumento}, event)" onfocusout="mostrarValores()" onfocus="this.select();" style="width: 130px !important; text-align: right;" min="0" value="0" disabled>`;
                }
            },
            {//CREDITO
                "data": function (row, type, set, col){
                    return `<input type="text" data-type="currency" class="form-control form-control-sm input_number credito_input" id="credito_${idDocumento}" onkeypress="changeCreditoRow(${idDocumento}, event)" onfocusout="mostrarValores()" onfocus="this.select();" style="width: 130px !important; text-align: right;" min="0" value="0" disabled>`;
                }
            },
            {//CONCEPTO
                "data": function (row, type, set, col){
                    return '<input type="text" class="form-control form-control-sm" id="concepto_'+idDocumento+'" onkeypress="changeConceptoRow('+idDocumento+', event)" placeholder="SIN OBSERVACIÓN" style="width: 300px !important;" onfocus="this.select();" onfocus="setValueConcepto('+idDocumento+')" disabled>';
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
                    dropdownCssClass: 'custom-documentogeneral_cuenta',
                    delay: 250,
                    minimumInputLength: 2,
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
                    dropdownCssClass: 'custom-documentogeneral_nit',
                    ajax: {
                        url: 'api/nit/combo-nit',
                        headers: headers,
                        dataType: 'json',
                        data: function (params) {
                            var query = {
                                q: params.term,
                                totalRows: 3,
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
                $('.combo_cecos').select2({
                    theme: 'bootstrap-5',
                    delay: 250,
                    // minimumInputLength: 1,
                    ajax: {
                        url: 'api/centro-costos/combo-centro-costo',
                        headers: headers,
                        dataType: 'json',
                        data: function (params) {
                            var query = {
                                q: params.term,
                                totalRows: 3,
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
                                id_tipo_cuenta: 3,
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
                $('.combo_cuenta').on('select2:close', function(event) {
                    var id = this.id.split('_')[2];
                    changeCuentaRow(id);
                });
                $('.combo_cecos').on('select2:close', function(event) {
                    var id = this.id.split('_')[2];
                    changeCecosRow(id);
                });
                $('.combo_nits').on('select2:close', function(event) {
                    var id = this.id.split('_')[2];
                    changeNitRow(id);
                });
                $("input[data-type='currency']").on({
                    keyup: function(event) {
                        if (event.keyCode >= 96 && event.keyCode <= 105 || event.keyCode == 110 || event.keyCode == 8 || event.keyCode == 46) {
                            formatCurrency($(this));
                        }
                    },
                    blur: function() {
                        formatCurrency($(this), "blur");
                    }
                });
            });
        }
    });

    if(documento_general_table) {
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
    }

    $comboComprobante = $('#id_comprobante').select2({
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

    $('#id_comprobante').on('select2:close', function(event) {
        var data = $(this).select2('data');
        if(data.length){
            setTimeout(function(){
                $('#fecha_manual_documento').focus();
                $('#fecha_manual_documento').select();
            },10);
            tipo_comprobante = data[0].tipo_comprobante;
            consecutivoSiguiente();
        }
    });

    $('#fecha_manual_documento').on( "focusout", function() {
        validarFechaManualDocumentos();
    });

    documento_extracto = $('#documentoExtractoTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
        deferLoading: 0,
        initialLoad: false,
        autoWidth: true,
        language: lenguajeDatatable,
        ordering: false,
        sScrollX: "100%",
        scrollX: true,
        fixedColumns : {
            left: 0,
            right : 1,
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
                    if (!row.capturando) html+= '<span href="javascript:void(0)" id="documentoextracto_'+row.documento_referencia+'_'+row.saldo+'" class="btn badge bg-gradient-primary select-documento" style="margin-bottom: 0rem !important">Seleccionar</span>&nbsp;';
                    else html+= '<span href="javascript:void(0)" class="btn badge bg-gradient-secondary disabled" style="margin-bottom: 0rem !important">Capturando</span>&nbsp;'
                    return html;
                }
            }
        ]
    });

    setTimeout(function(){
        $comboComprobante.select2("open");
    },10);
}

function initDatatableExtracto() {
    if (!documento_extracto) {
        documento_extracto = $('#documentoExtractoTable').DataTable({
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
                        if (!row.capturando) html+= '<span href="javascript:void(0)" id="documentoextracto_'+row.documento_referencia+'_'+row.saldo+'" class="btn badge bg-gradient-primary select-documento" style="margin-bottom: 0rem !important">Seleccionar</span>&nbsp;';
                        else html+= '<span href="javascript:void(0)" class="btn badge bg-gradient-secondary disabled" style="margin-bottom: 0rem !important">Capturando</span>&nbsp;'
                        return html;
                    }
                }
            ]
        });
    }
}

function addRow(openCuenta = true) {

    var rows = documento_general_table.rows().data();
    var totalRows = rows.length;
    var dataLast = rows[totalRows - 1];
    if (dataLast) {
        var cuentaLast = $('#combo_cuenta_'+dataLast.id).val();
        if (!cuentaLast) {
            $('#combo_cuenta_'+dataLast.id).select2('open');
            document.getElementById("card-documento-general").scrollLeft = 0;
            return;
        }
    }

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

    if (!data) return;

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
                    var [debito, credito] = totalValores();
                    var rowBack = idRow-1;
                    var resta = credito - debito;
                    if (tipo_comprobante != 4) $("#debito_"+idRow).val(resta < 0 ? resta * -1 : resta);
                    else $("#debito_"+idRow).val(0);
                    
                    $("#concepto_"+idRow).val($("#concepto_"+rowBack).val());
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
    document.getElementById("card-documento-general").scrollLeft = 310;
    focusNextRow(0, idRow);
}

function changeNitRow(idRow) {
    if($('#combo_nits_'+idRow).val()){
        document.getElementById("card-documento-general").scrollLeft = 550;
        if (cambiarNit) {
            if($('#combo_cecos_'+idRow).val()) focusNextRow(2, idRow);
            else focusNextRow(1, idRow);
            return;
        }

        cambiarNit = true;
        setTimeout(function(){
            $('#combo_nits_'+idRow).select2('open');
        },10);
    }
}

function changeCecosRow(idRow) {
    if($('#combo_cecos_'+idRow).val()){
        focusNextRow(2, idRow);
    }
}

function changeConsecutivo(event) {
    if(event.keyCode == 13){
        document.getElementById('iniciarCapturaDocumentos').click();
    }
}

function changeDctoRow(idRow, event, eso) {

    if(event.keyCode == 13){
        if(!$('#concepto_'+idRow).val() && tipo_comprobante != 4){
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
            },300);
        }
    }
}

function changeDebitoRow(idRow, event) {
    if(event.keyCode == 13){
        if (tipo_comprobante == 4) {
            var valorDebito = $('#debito_'+idRow).val();
            if (valorDebito) $('#credito_'+idRow).val(0);
        }
        focusNextRow(4, idRow);
    }
}

function changeCreditoRow(idRow, event) {
    if(event.keyCode == 13){
        var inputCreditoMax = parseInt($("#credito_"+idRow)[0].max);
        var inputCreditoValue = parseInt($("#credito_"+idRow).val());
        if (inputCreditoValue > inputCreditoMax) {
            setTimeout(function(){
                if (tipo_comprobante != 4) $("#credito_"+idRow).val(inputCreditoMax);
                $("#credito_"+idRow).focus();
                $("#credito_"+idRow).select();
            },10);
            return;
        }
        if (tipo_comprobante == 4) {
            var valorCredito = $('#credito_'+idRow).val();
            if (valorCredito) $('#debito_'+idRow).val(0);
        }
        focusNextRow(5, idRow);
    }
}

function changeConceptoRow(idRow, event) {
    if(event.keyCode == 13){

        let dataComprobante = $('#id_comprobante').select2('data')[0];

        if(dataComprobante.tipo_comprobante != 4) {

            var dataDocumento = documento_general_table.rows().data();

            if(dataDocumento.length > 0) {

                var debito = 0;
                var credito = 0;
                
                $("#crearCapturaDocumentos").show();
                $("#crearCapturaDocumentosDisabled").hide();
                
                for (let index = 0; index < dataDocumento.length; index++) {
                    var deb = stringToNumberFloat($('#debito_'+index).val());
                    var cre = stringToNumberFloat($('#credito_'+index).val());
                    debito+= deb ? deb : 0;
                    credito+= cre ? cre : 0;
                }

                if(debito > 0 && credito > 0 && debito - credito == 0) {
                    askSaveDocumentos = false;
                    document.getElementById('crearCapturaDocumentos').click();
                    return;
                } else {
                    document.getElementById('agregarDocumentos').click();
                }
            }
        }
        
        var dataCuenta = $('#combo_cuenta_'+idRow).select2('data');
        if(!dataCuenta.length > 0) {
            $('#combo_cuenta_'+idRow).select2('open');
        } else {
            document.getElementById('agregarDocumentos').click();
        }
    }
}

function changeConcecutivo(event) {
    if(event.keyCode == 13){
        searchCaptura();
    }
}

function changeFecha(event) {

    if(event.keyCode == 13){
        validarFechaManualDocumentos();
        setTimeout(function(){
            $('#consecutivo').focus();
            $('#consecutivo').select();
        },10);
    }
}

function clearRows(data, idRow) {

    if (!data) {
        $("#combo_cecos_"+idRow).val('').change();
        $("#combo_nits_"+idRow).val('').change();
        $("#documento_referencia_"+idRow).val('');
        $("#combo_cecos_"+idRow).val('').change();
        $("#debito_"+idRow).val(0);
        $("#credito_"+idRow).val(0);
        return;
    }
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
}

function validarFechaManualDocumentos() {
    var fechaManual = $("#fecha_manual_documento").val();

    $('#fecha_manual_documento').removeClass("is-valid");
    $('#fecha_manual_documento').removeClass("is-invalid");

    if (!fechaManual) {
        $('#fecha_manual_documento').removeClass("is-valid");
        $('#fecha_manual_documento').addClass("is-invalid");
        $('#fecha_manual_documento-feedback').text('La Fecha manual es requerida')
        return;
    }

    $.ajax({
        url: base_url + 'anio-cerrado',
        method: 'GET',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        var fechaCierre = new Date(res.data).getTime();
        var fechaManual = new Date($("#fecha_manual_documento").val()).getTime();

        if (fechaManual <= fechaCierre) {
            $('#fecha_manual_documento').removeClass("is-valid");
            $('#fecha_manual_documento').addClass("is-invalid");
            $('#fecha_manual_documento-feedback').text('La Fecha se encuentra en un año cerrado');
        }
    }).fail((err) => {
    });

}

function setDisabledRows(data, idRow) {
    if(data && data.exige_nit) {
        $("#combo_nits_"+idRow).prop('disabled', false);
    } else {
        $("#combo_nits_"+idRow).prop('disabled', true);
    }
    if(data && data.exige_centro_costos) {
        if(primerCecosGeneral.length == 1) {
            cecosGeneral = primerCecosGeneral[0];
            var dataCecos = {
                id: cecosGeneral.id,
                text: cecosGeneral.codigo+ ' - ' +cecosGeneral.nombre,
            };
            var newOptionCecos = new Option(dataCecos.text, dataCecos.id, false, false);
            $('#combo_cecos_'+idRow).append(newOptionCecos).trigger('change');
        }
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

    if (tipo_comprobante != 4) {
        if(data && data.naturaleza_cuenta == 1) {
            $("#debito_"+idRow).prop('disabled', true);
            $("#credito_"+idRow).prop('disabled', false);
        } else {
            $("#debito_"+idRow).prop('disabled', false);
            $("#credito_"+idRow).prop('disabled', true);
        }
    } else {
        $("#debito_"+idRow).prop('disabled', false);
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
    
    var idNextColumn = Idcolumn;

    while (buscar) {
        if(idNextColumn >= 6) buscar = false;

        var idInput = inputsId[idNextColumn]+'_'+idRow;
        var isDisabled = $(idInput).is(":disabled");
        var rowAnterior = idRow-1;

        if(!isDisabled) {
            //COMBOS
            if(inputsId[idNextColumn] == '#combo_nits' || inputsId[idNextColumn] == '#combo_cecos') {
                //AGREGAR DATOS COMBOS NIT
                if(inputsId[idNextColumn] == '#combo_nits' && !$(idInput).val() && idRow > 0 ) {
                    
                    var dataNit = $('#combo_nits_'+rowAnterior).select2('data');
                    //SI LA COLUMNA ANTERIOR TIENE DATOS
                    if(dataNit && dataNit.length > 0) {
                        cambiarNit = false;
                        var optionNit = {
                            id: dataNit[0].id,
                            text: dataNit[0].text
                        };
                        var newOptionNit = new Option(optionNit.text, optionNit.id, false, false);
                        $('#combo_nits_'+idRow).append(newOptionNit).trigger('change');
                        $('#concepto_'+idRow).val($('#concepto_'+rowAnterior).val());
                        document.getElementById("card-documento-general").scrollLeft = 250;
                    } else {
                    }
                    setTimeout(function(){
                        $(idInput).select2('open');
                    },10);
                }else {
                    setTimeout(function(){
                        $(idInput).select2('open');
                    },10);
                }
            } else {
                //BUSCAR EXTRACTO
                var dataCuentaRow = $('#combo_cuenta_'+idRow).select2('data')[0];
                if(inputsId[idNextColumn] == '#documento_referencia' && idRow === rowExtracto){
                    
                    if (dataCuentaRow.naturaleza_cuenta != dataCuentaRow.naturaleza_origen) {
                        buscarExtracto();
                    };
                }
                if (tipo_comprobante == 4) {
                    
                    var valorCredito =stringToNumberFloat($('#credito_'+idRow).val());
                    var valorDebito = stringToNumberFloat($('#debito_'+idRow).val());
                    var campoCredito = inputsId[idNextColumn] == '#credito' ? true : false;
                    var campoDebito = inputsId[idNextColumn] == '#debito' ? true : false;
                    var campoDctoRefe = inputsId[idNextColumn] == '#documento_referencia' ? true : false;
                    var campoConcepto = inputsId[idNextColumn] == '#concepto' ? true : false;
                    var naturalezaCuenta = dataCuentaRow.naturaleza_cuenta == 1 ? 'credito' : 'debito';

                    if (campoDebito && !valorDebito && naturalezaCuenta == 'credito') {//FOCUS CREDITO SI LA NATURALEZA ES CREDITO Y EL VALOR DEBITO ES 0
                        buscar = false;
                        document.getElementById("card-documento-general").scrollLeft = 910;
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find('#credito_'+idRow).focus();
                            $('#documentoReferenciaTable tr').find('#credito_'+idRow).select();
                        },10);
                    } else if (campoDebito && valorDebito && naturalezaCuenta == 'credito') {//FOCUS DEBITO SI LA NATURALEZA ES CREDITO Y EL VALOR DEBITO NO > 0
                        buscar = false;
                        document.getElementById("card-documento-general").scrollLeft = 800;
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find('#debito_'+idRow).focus();
                            $('#documentoReferenciaTable tr').find('#debito_'+idRow).select();
                        },10);
                    } else if (campoDebito && !campoCredito && naturalezaCuenta == 'debito') {
                        buscar = false;
                        document.getElementById("card-documento-general").scrollLeft = 800;
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find('#debito_'+idRow).focus();
                            $('#documentoReferenciaTable tr').find('#debito_'+idRow).select();
                        },10);
                    }else if (campoCredito && valorDebito) {//FOCUS CONCEPTO SI EL CAMPO ES CREDITO Y DEBITO ES > 0
                        buscar = false;
                        document.getElementById("card-documento-general").scrollLeft = 1200;
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find('#concepto_'+idRow).focus();
                            $('#documentoReferenciaTable tr').find('#concepto_'+idRow).select();
                        },10);
                    } else if (campoCredito && !valorDebito) {//FOCUS CREDITO SI EL CAMPO ES CREDITO Y DEBITO ES = 0
                        buscar = false;
                        document.getElementById("card-documento-general").scrollLeft = 910;
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find('#credito_'+idRow).focus();
                            $('#documentoReferenciaTable tr').find('#credito_'+idRow).select();
                        },10);
                    } else if (campoDctoRefe) {
                        buscar = false;
                        document.getElementById("card-documento-general").scrollLeft = 604;
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find('#documento_referencia_'+idRow).focus();
                        },10);
                    } else if (campoConcepto) {
                        buscar = false;
                        document.getElementById("card-documento-general").scrollLeft = 1200;
                        setTimeout(function(){
                            $('#documentoReferenciaTable tr').find('#concepto_'+idRow).focus();
                        },10);
                    }
                } else {
                    //COMPLETAR VALORES
                    if(inputsId[idNextColumn] == '#debito') {
                        var [debito, credito] = totalValores();
                        var total = credito - debito;
                        if(total > 0) {
                            calcularCabeza = false;
                            if (tipo_comprobante != 4) $(idInput).val(total);
                        }
                        document.getElementById("card-documento-general").scrollLeft = 900;
                    }
                    if(inputsId[idNextColumn] == '#credito') {
                        var [debito, credito] = totalValores();
                        var total = debito - credito;
                        if(total > 0) {
                            calcularCabeza = false;
                            if (tipo_comprobante != 4) $(idInput).val(total);
                        }
                        document.getElementById("card-documento-general").scrollLeft = 1000;
                    }
                    setTimeout(function(){
                        $('#documentoReferenciaTable tr').find(idInput).focus();
                    },10);
                }
            }
            buscar = false;
        }
        idNextColumn++;
    }
}

$(document).on('keydown', '.custom-documentogeneral_nit .select2-search__field', function (event) {
    var dataInputSearch = $('.select2-search__field').val();
    var datainputSelect = $(this).attr( "aria-controls" );
    
    if (datainputSelect && datainputSelect.split('-').length == 3) {
        datainputSelect = datainputSelect.split('-');

        var inputNit = datainputSelect[1].split('_');
        if (inputNit.length == 3) {
            var dataNit = $('#combo_nits_'+inputNit[2]).select2('data');
            if (event.keyCode == 13 && !dataInputSearch && dataNit && dataNit.length > 0) {
                $('#combo_nits_'+inputNit[2]).select2('close');
            }
        }
    }
    
});

$(document).on('keydown', '.custom-documentogeneral_cuenta .select2-search__field', function (event) {
    var dataInputSearch = $('.select2-search__field').val();
    var [debito, credito] = totalValores();

    if (event.keyCode == 96 && dataInputSearch.length == 1) {
        guardarDocumentoGeneral = true;
    } else if (event.keyCode == 13){
        if ((debito + credito) > 0) {
            if (guardarDocumentoGeneral) {
                guardarDocumentoGeneral = false;
                $(".combo_cuenta").select2('close');
                document.getElementById('crearCapturaDocumentos').click();
            }
        }
    } else {
        guardarDocumentoGeneral = false;
    }
});



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
    if (tipo_comprobante != 4) $('#concepto_'+rowExtracto).val(dataNit.text + ' - FACTURA: ' + documentoReferencia);

    if($('#debito_'+rowExtracto).is(":disabled")){
        $('#credito_'+rowExtracto).val(saldo);
        document.getElementById("credito_"+rowExtracto).max = saldo;
        setTimeout(function(){
            $('#documentoReferenciaTable tr').find('#credito_'+rowExtracto).select();
        },10);
    } else {
        $('#debito_'+rowExtracto).val(saldo);
        document.getElementById("debito_"+rowExtracto).max = saldo;
        setTimeout(function(){
            $('#documentoReferenciaTable tr').find('#debito_'+rowExtracto).select();
        },10);
    }
    // focusNextRow(3, rowExtracto);
});

function buscarExtracto() {
    if (!documento_extracto) initDatatableExtracto();
    if ($('#combo_nits_'+rowExtracto).val()) {
        let dataNit = $('#combo_nits_'+rowExtracto).select2('data')[0];
        $('#modal-title-documento-extracto').html(dataNit.text);
        $("#modalDocumentoExtracto").modal('show');

        documento_extracto.rows().remove().draw();

        $.ajax({
            url: base_url + 'extracto',
            method: 'GET',
            data: {
                id_tipo_cuenta: 3,
                id_nit: $('#combo_nits_'+rowExtracto).val(),
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            var documentos = res.data;
            var dataRowDocumentos = documento_general_table.rows().data();

            for (let index = 0; index < documentos.length; index++) {
                let documento = documentos[index];
                if (dataRowDocumentos.length > 1 ) documento = calcularPagosEnCaptura(documento);
                documento_extracto.row.add(documento).draw();
            }


        }).fail((err) => {
        });
    }
}

function calcularPagosEnCaptura (documento) {
    var dataRowDocumentos = documento_general_table.rows().data();

    for (let index = 0; index < dataRowDocumentos.length; index++) {
        const dataRow = dataRowDocumentos[index];
        
        if (rowExtracto != dataRow.id) {
    
            var id_nit = $('#combo_nits_'+dataRow.id).val();
            var id_cuenta = $('#combo_cuenta_'+dataRow.id).val();
            var documento_referencia = $('#documento_referencia_'+dataRow.id).val();

            if (id_nit == documento.id_nit && id_cuenta == $('#combo_cuenta_'+rowExtracto).val() && documento_referencia == documento.documento_referencia ) {
                
                var totalDebito = stringToNumberFloat($('#debito_'+dataRow.id).val());
                var totalCredito = stringToNumberFloat($('#credito_'+dataRow.id).val());
                totalDebito = totalDebito ? totalDebito : 0;
                totalCredito = totalCredito ? totalCredito : 0;
                documento.capturando = true;

                documento.total_abono = parseFloat(documento.total_abono) + totalDebito + totalCredito;
                documento.saldo = parseFloat(documento.saldo) - totalDebito - totalCredito;
            }
        }
    }

    return documento;
}

function setValueConcepto(Idcolumn) {
    let dataNit = $('#combo_nits_'+Idcolumn).select2('data')[0];
    let dataCuenta = $('#combo_cuenta_'+Idcolumn).select2('data')[0];
    
    if (tipo_comprobante == 4) {
        var rows = documento_general_table.rows().data();
        var totalRows = rows.length;
        var dataLast = rows[totalRows - 2];
        if (dataLast) {
            var conceptoLast = $('#concepto_'+dataLast.id).val();
            $('#concepto_'+Idcolumn).val(conceptoLast);
        }
    } else if (dataCuenta && dataCuenta.cuenta && dataCuenta.cuenta.slice(0, 2) == '11') {
        var rows = documento_general_table.rows().data();
        var totalRows = rows.length;
        var dataLast = rows[totalRows - 2];
        if (dataLast) {
            var conceptoLast = $('#concepto_'+dataLast.id).val();
            $('#concepto_'+Idcolumn).val(conceptoLast);
        }
    } else if (!$('#concepto_'+Idcolumn).val() && dataNit) {
        $('#concepto_'+Idcolumn).val(dataNit.text);
    }

    $('#concepto_'+Idcolumn).select();
}

function searchCaptura() {
    var form = document.querySelector('#documentoFilterForm');

    if(form.checkValidity()){

        $("#id_comprobante").prop('disabled', true);
        $("#consecutivo").prop('disabled', true);
        
        $("#iniciarCapturaDocumentos").hide();
        $("#iniciarCapturaDocumentosLoading").show();

        let data = {
            id_comprobante: $("#id_comprobante").val(),
            fecha_manual: $("#fecha_manual_documento").val(),
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
                            $('#debito_'+index).val(documento.debito);
                        }

                        if(parseInt(documento.credito)) {
                            $('#credito_'+index).val(documento.credito);
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

function consecutivoSiguiente() {
    var id_comprobante = $('#id_comprobante').val();
    var fecha_manual = $('#fecha_manual_documento').val();
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
        if(debito-credito > 0){
            $("#general_diferencia").css("color", "red");
            $('#general_diferencia').css({'color' : 'red'});
        } else if (debito-credito < 0){
            $("#general_diferencia").css("color", "red");
        } else if (debito-credito == 0 && debito > 0 || credito > 0){
            $("#general_diferencia").css("color", "#0002ff");
        } else {
            $("#general_diferencia").css("color", "#0002ff");
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
            var id = dataDocumento[index].id;
            var deb = stringToNumberFloat($('#debito_'+id).val());
            var cre = stringToNumberFloat($('#credito_'+id).val());

            debito+= deb ? deb : 0;
            credito+= cre ? cre : 0;
        }
    } else {
        $("#crearCapturaDocumentos").hide();
        $("#crearCapturaDocumentosDisabled").show();
    }
    
    return [debito.toFixed(2), credito.toFixed(2)];
}

// $(document).on('click', '#crearCapturaDocumentos', function () {
    
// });

function capturarDcoumentosGenerales() {
    
    var [debito, credito] = totalValores();
    var diferencia = debito - credito;

    var texto = 'Desea guardar documento en la tabla?';
    var type = 'question';

    if (!capturarDocumentosDescuadrados && diferencia != 0) {
        agregarToast('warning', 'Documentos descuadrados', 'Para guardarlos debe activar la opción en Configuración > Empresa "Capturar documentos descuadrados".', false);
        return;
    }

    if (diferencia != 0) {
        texto = "Documento descuadrado, desea guardarlo en la tabla?";
        type = "warning";
    } else if (debito == 0 && credito == 0) {
        agregarToast('warning', 'Creación errada', 'Sin datos para guardar en la tabla', true);
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
}

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
        fecha_manual: $("#fecha_manual_documento").val(),
        editing_documento: $("#editing_documento").val(),
    }
    $.ajax({
        url: base_url + 'documentos',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        guardarDocumentoGeneral = false;
        if(res.success){
            cargarNuevoConsecutivo();
            agregarToast('exito', 'Creación exitosa', 'Documentos creados con exito!', true);
            $("#id_comprobante").prop('disabled', false);
            setTimeout(function(){
                $comboComprobante.select2("open");
            },10);
            if(res.impresion) {
                window.open("/documentos-print/"+res.impresion, "", "_blank");
            }
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
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    }).fail((err) => {
        guardarDocumentoGeneral = false;
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
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function getDocumentos(){
    
    var data = [];

    var dataDocumento = documento_general_table.rows().data();
    if(dataDocumento.length > 0){
        for (let index = 0; index < dataDocumento.length; index++) {
            var idDocumento = dataDocumento[index].id;
            
            var debito = stringToNumberFloat($('#debito_'+idDocumento).val());
            var credito = stringToNumberFloat($('#credito_'+idDocumento).val());

            if(debito || credito) {

                var dctrf = $('#documento_referencia_'+idDocumento).val();
                var concepto = $('#concepto_'+idDocumento).val();
                var cuenta = $('#combo_cuenta_'+idDocumento).val();
                var nit = $('#combo_nits_'+idDocumento).val();
                var cecos = $('#combo_cecos_'+idDocumento).val();

                if (id_cuenta && (debito + credito) > 0) {
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
    }
    return data;
}