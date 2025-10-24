var idDocumento = 0;
var rowExtracto = '';
var cambiarNit = true;
var editandoCaptura = 0;
var tipo_consecutivo = 0;
var tipo_comprobante = '';
var validarFactura = null;
var calcularCabeza = true;
var nuevaFacturaDG = false; 
var cuentaExtractoDg = null
var askSaveDocumentos = true;
var $comboComprobante = null;
var documento_extracto = null;
var consecutivoUltimo = false;
var consecutivoEditado = false;
var guardarDocumentoDG = false;
var documento_general_table = null;
var guardarDocumentoGeneral = false;
var ignoreChangeEventGeneral = false;


function documentogeneralInit() {

    initConfigDocumentoGeneral();
    initTablaDocumentoGeneral();
    initTablaDocumentoGeneralExtracto();
    initCombosActionDocumentoGeneral();

    //ABRIR COMBO COMPROBANTE
    setTimeout(function(){
        $comboComprobante.select2("open");
    },10);
}

function initConfigDocumentoGeneral() {
    const dateNow = new Date();
    const fechaHoraDG = dateNow.getFullYear() + '-' + 
        ("0" + (dateNow.getMonth() + 1)).slice(-2) + '-' + 
        ("0" + dateNow.getDate()).slice(-2) + 'T' + 
        ("0" + dateNow.getHours()).slice(-2) + ':' + 
        ("0" + dateNow.getMinutes()).slice(-2);
    $('#fecha_manual_documento').val(fechaHoraDG);
}

function initTablaDocumentoGeneral() {
    documento_general_table = $('#documentoReferenciaTable').DataTable({
        pageLength: 300,
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
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
            { "data": (row) => renderBotonEliminarDG() }, //BORRAR
            { "data": (row) => renderComboCuentaDG() }, //CUENTA
            { "data": (row) => renderComboNitDG() }, //NIT
            { "data": (row) => renderComboCecosDG() }, //CECOS
            { "data": (row) => renderDocRefeDG() }, //DCTO REFE
            { "data": (row) => renderDebitoDG() }, //DEBITO
            { "data": (row) => renderCreditoDG() }, //CREDITO
            { "data": (row) => renderConceptoDG() } //CONCEPTO
        ],
        columnDefs: [{
            'orderable': false
        }],
        initComplete: function () {
            $('#documentoReferenciaTable').on('draw.dt', function() {
                initSelect2CombosDG();
                initInputFormattingDG();
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
}

function initSelect2CombosDG() {
    initComboCuentaTableDG(0);
    // initComboCuentaTableDG();
    // initComboNitsTableDG();
    // initComboCecosTableDG();
    // initComboExtractoTableDG();
}

function initComboCuentaTableDG(rowId = null) {
    const selector = rowId !== null ? `#combo_cuenta_${rowId}` : '.combo_cuenta';
    
    $(selector).not('.select2-hidden-accessible').each(function() {
        const $combo = $(this);
        if (!$combo.hasClass('select2-hidden-accessible')) {
            $combo.select2({
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
        }
    });
}


function initComboNitsTableDG(rowId = null) {
    const selector = rowId !== null ? `#combo_nits_${rowId}` : '.combo_nits';
    
    $(selector).not('.select2-hidden-accessible').each(function() {
        const $combo = $(this);
        if (!$combo.hasClass('select2-hidden-accessible')) {
            $combo.select2({
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
        }
    });
}

function initComboCecosTableDG(rowId = null) {
    const selector = rowId !== null ? `#combo_cecos_${rowId}` : '.combo_cecos';
    
    $(selector).not('.select2-hidden-accessible').each(function() {
        const $combo = $(this);
        if (!$combo.hasClass('select2-hidden-accessible')) {
            $combo.select2({
                theme: 'bootstrap-5',
                delay: 250,
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
        }
    });
}

function initComboExtractoTableDG(rowId = null) {
    const selector = rowId !== null ? `#combo_extracto_${rowId}` : '.combo_extracto';
    
    $(selector).not('.select2-hidden-accessible').each(function() {
        const $combo = $(this);
        if (!$combo.hasClass('select2-hidden-accessible')) {
            $combo.select2({
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
        }
    });
}

function initInputFormattingDG() {
    $('.form-control').keyup(function () {
        $(this).val($(this).val().toUpperCase());
    });

    $("input[data-type='currency']").off().on({
        keyup: function (event) {
            const validKeys = [96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 110, 8, 46];
            if (validKeys.includes(event.keyCode)) {
                formatCurrency($(this));
            }
        },
        blur: function () {
            formatCurrency($(this), "blur");
        }
    });
}

function initTablaDocumentoGeneralExtracto() {
    documento_extracto = $('#documentoExtractoTable').DataTable({
        dom: '',
        responsive: false,
        processing: true,
        serverSide: false,
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
            {"data":'cuenta'}, 
            {"data":'nombre_cuenta'},
            {"data":'documento_referencia'},
            {"data":'total_facturas', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'total_abono', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'saldo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right'},
            {"data":'fecha_manual'},
            {"data":'dias_cumplidos'},
            {
                "data": function (row, type, set){
                    var html = ``;
                    if (row.cuenta != cuentaExtractoDg) {
                        return `<span href="javascript:void(0)" class="btn badge bg-gradient-primary disabled" style="margin-bottom: 0rem !important; box-shadow: 0px 0px 0px rgba(50, 50, 93, 0.1), 0px 0px 0px rgb(0 0 0 / 57%);">Seleccionar</span>&nbsp;`;
                    } else if (!row.capturando) html+= `<span href="javascript:void(0)" id="documentoextracto@${row.documento_referencia}@${row.saldo}" class="btn badge bg-gradient-primary select-documento" style="margin-bottom: 0rem !important">Seleccionar</span>&nbsp;`;
                    else html+= `<span href="javascript:void(0)" class="btn badge bg-gradient-secondary disabled" style="margin-bottom: 0rem !important">Capturando</span>&nbsp;`
                    return html;
                }
            }
        ]
    });
}

function initCombosActionDocumentoGeneral() {

    $('#documentoReferenciaTable').on('select2:close', '.combo_cuenta', function() {
        var id = this.id.split('_')[2];
        changeCuentaRow(id);
    });
    
    $('#documentoReferenciaTable').on('select2:close', '.combo_cecos', function() {
        var id = this.id.split('_')[2];
        changeCecosRow(id);
    });
    
    $('#documentoReferenciaTable').on('select2:close', '.combo_nits', function() {
        var id = this.id.split('_')[2];
        changeNitRow(id);
    });

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
            console.log(data[0]);
            tipo_consecutivo = parseInt(data[0].tipo_consecutivo);
            if (tipo_comprobante == 5) tipo_comprobante = 2;
            consecutivoSiguiente();
        }
    });

    $('#fecha_manual_documento').on( "focusout", function() {
        validarFechaManualDocumentos();
        if (tipo_consecutivo == 1) {
            consecutivoSiguiente();
        }
    });
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

function renderBotonEliminarDG() {
    return `<span class="btn badge bg-gradient-danger drop-row-grid" onclick="deleteRow(${idDocumento})" id="droprow_${idDocumento}">
                <i class="fas fa-trash-alt"></i>
            </span>`;
}

function renderComboCuentaDG() {
    return `<select class="form-control form-control-sm combo_cuenta combo-grid" id="combo_cuenta_${idDocumento}">
            </select>`;
}

function renderComboNitDG() {
    return `<select class="form-control form-control-sm combo_nits combo-grid" id="combo_nits_${idDocumento}" onchange="changeNitRow(${idDocumento})" disabled>
            </select>`;
}

function renderComboCecosDG() {
    return `<select class="form-control form-control-sm combo_cecos combo-grid" id="combo_cecos_${idDocumento}" onchange="changeCecosRow(${idDocumento})" onkeypress="onkeypressCecosRow(${idDocumento}, event)" disabled>
            </select>`;
}

function renderDocRefeDG() {
    return `
        <div class="input-group" style="width: 180px; height: 30px;">
            <input type="text" class="form-control form-control-sm documento_referencia_row" id="documento_referencia_${idDocumento}" onkeydown="buscarFactura(${idDocumento}, event)" onkeypress="changeDctoRow(${idDocumento}, event)" keyup style="height: 30px;" value="" disabled readonly>
            <i class="fa fa-spinner fa-spin fa-fw documento-load" id="documento_load_${idDocumento}" style="display: none;"></i>
            <div id="texto_extracto_${idDocumento}" class="valid-feedback info-factura">Nueva factura</div>
            <div class="invalid-feedback info-factura">Factura existente</div>
                <div class="input-group-append button-group" id="conten_button_${idDocumento}">
                    <span href="javascript:void(0)" id="button_extracto_${idDocumento}" class="btn badge bg-gradient-secondary btn-group btn-documento-extracto" style="min-width: 40px; margin-right: 3px; border-radius: 0px 7px 7px 0px; height: 30px;">
                        <i class="fas fa-search" style="font-size: 17px; margin-top: 1px;"></i>
                        <b style="vertical-align: text-top;"></b>
                    </span>
                </div>
            </div>
        </div>
    `;
}

function renderDebitoDG() {
    return `<input type="text" data-type="currency" class="form-control form-control-sm input_number debito_input" id="debito_${idDocumento}" onkeypress="changeDebitoRow(${idDocumento}, event)" onfocusout="validarMax(${idDocumento}, 'debito')" onfocus="this.select();" style="width: 130px !important; text-align: right;" min="0" value="0" disabled>`;
}

function renderCreditoDG() {
    return `<input type="text" data-type="currency" class="form-control form-control-sm input_number credito_input" id="credito_${idDocumento}" onkeypress="changeCreditoRow(${idDocumento}, event)" onfocusout="validarMax(${idDocumento}, 'credito')" onfocus="this.select();" style="width: 130px !important; text-align: right;" min="0" value="0" disabled>`;
}

function renderConceptoDG() {
    return `<input type="text" class="form-control form-control-sm" id="concepto_${idDocumento}" onkeypress="changeConceptoRow(${idDocumento}, event)" placeholder="SIN OBSERVACIÓN" style="width: 300px !important;" onfocus="this.select();" onfocus="setValueConcepto(${idDocumento})" disabled>`;
}

function addRow(openCuenta = true) {

    var rows = documento_general_table.rows().data();
    var totalRows = rows.length;
    var dataLast = rows[totalRows - 1];
    if (dataLast) {
        var cuentaLast = $('#combo_cuenta_'+dataLast.id).val();
        if (!cuentaLast) {
            // Asegurar que el Select2 de cuenta esté inicializado
            if (!$('#combo_cuenta_'+dataLast.id).hasClass('select2-hidden-accessible')) {
                initComboCuentaTableDG(dataLast.id);
            }
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

    // Inicializar solo el combo de cuenta para la nueva fila
    initComboCuentaTableDG(idDocumento);

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
            if(!documento_general_table.rows().data().length){
                $("#crearCapturaDocumentos").prop('disabled', true);
            }
        }
    }
    mostrarValores();
}

function changeCuentaRow(idRow) {
    // 1. Obtener datos y ejecutar acciones iniciales
    const data = $('#combo_cuenta_' + idRow).select2('data')[0];

    if (!data) {
        return;
    }

    setDisabledRows(data, idRow);
    clearRows(data, idRow);
    mostrarValores();

    $('#combo_cuenta_' + idRow).attr('data-cuenta', JSON.stringify(data));

    if (!data?.cuenta) { // Salir si no hay datos o no hay propiedad 'cuenta'
        focusNextRow(0, idRow); // Mover foco si se deselecciona
        return;
    }

    // 2. Lógica principal basada en la cuenta seleccionada
    const tieneTipoCuentaEspecial = checkTipoCuentaEspecial(data.tipos_cuenta);
    const naturalezasDiferentes = data.naturaleza_cuenta !== data.naturaleza_origen;

    // 3. Manejar visibilidad y estado de Documento Referencia
    handleDocumentoReferenciaUI(idRow, naturalezasDiferentes, tieneTipoCuentaEspecial);

    // 4. Lógica específica si las naturalezas coinciden
    if (!naturalezasDiferentes) {
        handleLogicaNaturalezasIguales(data, idRow);
    }

    // 5. Ajustar scroll y mover foco
    scrollToDG(310);
    focusNextRow(0, idRow);
}

function checkTipoCuentaEspecial(tiposCuenta) {
    if (!tiposCuenta || !Array.isArray(tiposCuenta)) {
        return [];
    }
    return tiposCuenta
        .filter(
            tc => tc.id_tipo_cuenta === 3
            ||tc.id_tipo_cuenta === 4
            || tc.id_tipo_cuenta === 7
            || tc.id_tipo_cuenta === 8
        )
        .map(tc => tc.id_tipo_cuenta);
}

function handleDocumentoReferenciaUI(idRow, naturalezasDiferentes, tieneTipoCuentaEspecial) {
    const contenButton = $(`#conten_button_${idRow}`);
    const inputDocRef = $(`#documento_referencia_${idRow}`);

    if (tieneTipoCuentaEspecial?.length) {
        contenButton.show();
        inputDocRef.removeClass("normal_input").prop("readonly", false);
        inputDocRef.removeClass("normal_input").prop("disabled", false);
    } else {
        contenButton.hide();
        inputDocRef.addClass("normal_input").prop("readonly", false);
    }
}

function handleLogicaNaturalezasIguales(data, idRow) {
    // Verificar si es cuenta tipo '11', no es la primera fila y naturalezas coinciden (ya verificado)
    if (data.cuenta.startsWith('11') && idRow > 0) {
        handleAutoFillBanco(data, idRow);
    }
}

function handleAutoFillBanco(data, idRow) {
    const dataDocumento = documento_general_table?.rows().data(); // Usar optional chaining si puede no existir
    if (!dataDocumento || dataDocumento.length === 0) {
        return; // No hay datos en la tabla para calcular
    }

    const [debitoTotal, creditoTotal] = totalValores(); // Asumiendo que esta función devuelve los totales actuales
    const diferencia = Math.abs(creditoTotal - debitoTotal); // Siempre positivo

    const rowBack = idRow - 1;
    const conceptoAnterior = $(`#concepto_${rowBack}`).val();
    const inputDebito = $(`#debito_${idRow}`);
    const inputCredito = $(`#credito_${idRow}`);
    const inputConcepto = $(`#concepto_${idRow}`);

    if (tipo_comprobante !== 4) {
        if (data.naturaleza_cuenta === 1) {
            // Si la cuenta es Débito, la contrapartida usualmente es Crédito
            inputCredito.val(formatCurrencyValue(diferencia));
            inputDebito.val(formatCurrencyValue(0)); // Asegurarse que el otro campo quede en 0 formateado
        } else {
            // Si la cuenta es Crédito, la contrapartida usualmente es Débito
            inputDebito.val(formatCurrencyValue(diferencia));
            inputCredito.val(formatCurrencyValue(0)); // Asegurarse que el otro campo quede en 0 formateado
        }
        inputConcepto.val(conceptoAnterior);
        focusNextRow(0, idRow); // Mover el foco como antes
    } else {
        // Lógica específica para Nota Crédito (Tipo 4)
        inputDebito.val(formatCurrencyValue(0)); // Iniciar débito en 0
        // inputCredito.val(formatCurrency(0)); // Quizás también el crédito? Depende del flujo deseado
        inputConcepto.val(conceptoAnterior);
        // Foco específico en débito para tipo 4
        setTimeout(() => {
            const debitoField = $(`#documentoReferenciaTable tr #debito_${idRow}`); // Selector más específico si es necesario
            debitoField.focus().select();
        }, 100); // El timeout puede ser necesario para esperar renderizado/cambios
    }
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
    if (ignoreChangeEventGeneral) return;

    if($('#combo_cecos_'+idRow).val()){
        focusNextRow(2, idRow);
    }
}

function changeConsecutivo(event) {
    if(event.keyCode == 13){
        console.log('changeConsecutivo: ',consecutivoEditado);
        document.getElementById('iniciarCapturaDocumentos').click();
        if (!consecutivoEditado) {
            var consecutivoActual = parseInt($("#consecutivo").val());
            if (consecutivoUltimo !== undefined && consecutivoUltimo !== null) {
                var diferenciaConsecutivos = Math.abs(consecutivoUltimo - consecutivoActual);
                if (diferenciaConsecutivos >= 2 && consecutivoActual > 2) {

                    var mensajeDetallado = 
                        'El consecutivo actual (' + consecutivoActual + 
                        ') está ' + diferenciaConsecutivos + 
                        ' números alejado del último consecutivo utilizado (' + numConsecutivoUltimo + 
                        '). Confirme si desea saltar esta numeración.';

                    agregarToast('warning', 'Salto de Consecutivo', mensajeDetallado);
                }
            }
        }
        consecutivoEditado = true;
    }
}

function changeDctoRow(idRow, event) {
    if(event.keyCode == 13){
        if(!$('#concepto_'+idRow).val() && tipo_comprobante != 4){
            var nit = $('#combo_nits_'+idRow).select2('data');
            if(nit.length > 0) {
                var factura = $('#documento_referencia_'+idRow).val();
                var fac = factura ? ' - FACTURA: ' + factura : '';
                $('#concepto_'+idRow).val(nit[0].text + fac);
            }
        }
    }
}

function buscarFactura(idRow, event) {
    // 1. Verificar si la tecla presionada es "Enter" (código 13)
    if (event.key !== "Enter") {
        return; // Salir si no es Enter
    }

    // 2. Evitar el comportamiento por defecto del Enter (como enviar formularios)
    event.preventDefault();

    // 3. Declarar validarFactura en el ámbito adecuado
    let validarFactura = window.validarFactura || null;

    var dataCuenta = $('#combo_cuenta_' + idRow).select2('data')[0];
    const tipoCuenta = checkTipoCuentaEspecial(dataCuenta.tipos_cuenta);
    if (!tipoCuenta.length) {
        return;
    }

    if (dataCuenta.naturaleza_cuenta == dataCuenta.naturaleza_origen) {
        $('#documento_load_' + idRow).show();

        // 4. Cancelar petición anterior si existe
        if (validarFactura && validarFactura.readyState !== 4) {
            validarFactura.abort();
        }

        $('#documento_referencia_' + idRow).removeClass("is-valid is-invalid");

        let documento_referencia = $('#documento_referencia_' + idRow).val();

        // 5. Validación mínima antes de hacer la petición
        if (!documento_referencia || documento_referencia.trim().length < 1) {
            $('#documento_load_' + idRow).hide();
            return;
        }

        let id_nit = $('#combo_nits_' + idRow).val();
        let id_cuenta = $('#combo_cuenta_' + idRow).val();
        let fecha_manual = $('#fecha_manual_documento').val();

        // 6. Hacer la petición AJAX
        validarFactura = $.ajax({
            url: base_url + 'existe-factura',
            method: 'GET',
            data: {
                documento_referencia: documento_referencia,
                fecha_manual: fecha_manual,
                id_cuenta: id_cuenta,
                id_nit: id_nit
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            validarFactura = null;
            $('#documento_load_' + idRow).hide();
            if (res.data) {
                $('#documento_referencia_' + idRow).addClass("is-invalid");
            } else {
                $('#documento_referencia_' + idRow).addClass("is-valid");
                $('#texto_extracto_' + idRow).html("Nueva factura");

                let focusValorTexto = 'debito';
                const dataCuenta = $('#combo_cuenta_' + idRow).select2('data')[0];
                const tiposCuenta = checkTipoCuentaEspecial(dataCuenta.tipos_cuenta);

                if (tiposCuenta.includes(3) || tiposCuenta.includes(7)) {
                    if (dataCuenta.naturaleza_cuenta == 1) {
                        focusValorTexto = 'credito';
                    }
                } else if (tiposCuenta.includes(4) || tiposCuenta.includes(8)) {
                    if (dataCuenta.naturaleza_cuenta == 1) {
                        focusValorTexto = 'credito';
                    }
                }

                document.getElementById(`debito_${idRow}`).max = 0;
                document.getElementById(`credito_${idRow}`).max = 0;

                // 7. Enfocar el campo correspondiente después de validar
                setTimeout(function() {
                    $(`#${focusValorTexto}_${idRow}`).select();
                }, 10);
            }
        }).fail((err) => {
            validarFactura = null;
            $('#documento_load_' + idRow).hide();
            if (err.statusText !== "abort") {
                console.error("Error al buscar factura:", err);
            }
        });
    }
}

function changeDebitoRow(idRow, event) {
    if(event.keyCode == 13){
        if (tipo_comprobante == 4) {
            var valorDebito = $('#debito_'+idRow).val();
            if (valorDebito) $('#credito_'+idRow).val(0);
        }
        focusNextRow(4, idRow);
        mostrarValores()
    }
}

function changeCreditoRow(idRow, event) {
    if(event.keyCode == 13){
        var inputCreditoMax = parseInt($("#credito_"+idRow)[0].max);
        var inputCreditoValue = parseInt($("#credito_"+idRow).val());
        
        if (inputCreditoMax > 0 && inputCreditoValue > inputCreditoMax) {
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
        mostrarValores()
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
        console.log('consecutivoEditado: ',consecutivoEditado);
        if (!consecutivoEditado) {
            var consecutivoActual = parseInt($("#consecutivo").val());
            console.log('consecutivoActual: ',consecutivoActual);
            
            if (consecutivoUltimo !== undefined && consecutivoUltimo !== null) {
                
                // 👈 AQUI DEBE IR LA DEFINICIÓN DE numConsecutivoUltimo
                var numConsecutivoUltimo = parseInt(consecutivoUltimo); 
                console.log('numConsecutivoUltimo: ',numConsecutivoUltimo);
                // Verifica si la conversión fue exitosa (no es NaN)
                if (!isNaN(numConsecutivoUltimo)) {
                    
                    var diferenciaConsecutivos = Math.abs(numConsecutivoUltimo - consecutivoActual);
                    
                    if (diferenciaConsecutivos >= 2 && consecutivoActual > 2) {
                        
                        var mensajeDetallado = 
                            'El consecutivo actual (' + consecutivoActual + 
                            ') está ' + diferenciaConsecutivos + 
                            ' números alejado del último consecutivo utilizado (' + numConsecutivoUltimo + 
                            '). Confirme si desea saltar esta numeración.';

                        agregarToast('warning', 'Salto de Consecutivo', mensajeDetallado);
                    }
                }
            }
        }
        consecutivoEditado = true;
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

    const comboNits = $(`#combo_nits_${idRow}`);
    const comboCecos = $(`#combo_cecos_${idRow}`);
    const inputDocRef = $(`#documento_referencia_${idRow}`);
    const inputDebito = $(`#debito_${idRow}`);
    const inputCredito = $(`#credito_${idRow}`);

    const exigeNit = data?.exige_nit ?? false;
    const exigeCecos = data?.exige_centro_costos ?? false;
    const exigeDocRef = data?.exige_documento_referencia ?? false;

    // Si no hay datos (se deseleccionó la cuenta), limpiar todo lo relevante
    if (!data) {
        comboNits.val(null).trigger('change');
        comboCecos.val(null).trigger('change');
        inputDocRef.val('');
        inputDebito.val(0);
        inputCredito.val(0);
        return;
    }

    // Limpiar campos específicos si la cuenta NO los exige
    if (!exigeNit) {
        comboNits.val(null).trigger('change');
    }
    if (!exigeCecos) {
        comboCecos.val(null).trigger('change');
    }
    if (!exigeDocRef) {
        inputDocRef.val('');
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
        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });

}

function setDisabledRows(data, idRow) {

    const exigeNit = data?.exige_nit ?? false;
    const exigeCecos = data?.exige_centro_costos ?? false;
    const exigeDocRef = data?.exige_documento_referencia ?? false;
    const exigeConcepto = data?.exige_concepto ?? false;
    const naturalezaCuenta = data?.naturaleza_cuenta;

    const comboNits = $(`#combo_nits_${idRow}`);
    const comboCecos = $(`#combo_cecos_${idRow}`);
    const inputDocRef = $(`#documento_referencia_${idRow}`);
    const inputConcepto = $(`#concepto_${idRow}`);
    const inputDebito = $(`#debito_${idRow}`);
    const inputCredito = $(`#credito_${idRow}`);

    comboNits.prop('disabled', !exigeNit);
    comboCecos.prop('disabled', !exigeCecos);
    inputDocRef.prop('disabled', !exigeDocRef);
    inputConcepto.prop('disabled', !exigeConcepto);

    // Lógica específica para Centro de Costos (si aplica)
    if (exigeCecos && primerCecosGeneral?.length === 1) {
        const cecosGeneral = primerCecosGeneral[0];
        const dataCecos = {
            id: cecosGeneral.id,
            text: `${cecosGeneral.codigo} - ${cecosGeneral.nombre}`,
        };

        if (comboCecos.find(`option[value='${dataCecos.id}']`).length === 0) {
            ignoreChangeEventGeneral = true;
            const newOptionCecos = new Option(dataCecos.text, dataCecos.id, true, true); // Marcar como seleccionado
            comboCecos.append(newOptionCecos).trigger('change');
            ignoreChangeEventGeneral = false;
        }
    }

    // Lógica para Débito/Crédito
    if (tipo_comprobante !== 4) {
        const esNaturalezaDebito = naturalezaCuenta == 1;
        inputDebito.prop('disabled', esNaturalezaDebito);
        inputCredito.prop('disabled', !esNaturalezaDebito);
    } else {
        // Para Nota Crédito (o tipo 4), ambos habilitados
        inputDebito.prop('disabled', false);
        inputCredito.prop('disabled', false);
    }    
}

function focusNextRow(columnIndex, rowId) {
    var inputIds = [
        "#combo_nits",
        "#combo_cecos",
        "#documento_referencia",
        "#debito",
        "#credito",
        "#concepto",
    ];

    let nextColumn = columnIndex;
    let found = false;

    while (nextColumn < inputIds.length && !found) {
        const inputSelector = `${inputIds[nextColumn]}_${rowId}`;
        const $input = $(inputSelector);

        if ($input.is(":disabled")) {
            nextColumn++;
            continue;
        }

        if (["#combo_nits", "#combo_cecos"].includes(inputIds[nextColumn])) {
            // Inicializar Select2 on-demand si no está inicializado
            if (!$input.hasClass('select2-hidden-accessible')) {
                if (inputIds[nextColumn] === "#combo_nits") {
                    initComboNitsTableDG(rowId);
                } else if (inputIds[nextColumn] === "#combo_cecos") {
                    initComboCecosTableDG(rowId);
                }
            }

            // Autocompletar NIT desde fila anterior si está vacío
            if (inputIds[nextColumn] === "#combo_nits" && !$input.val() && rowId > 0) {
                
                let prevRow = rowId;
                for (let index = 0; index < rowId; index++) {
                    prevRow--;
                    const nitData = $(`#combo_nits_${prevRow}`).select2('data');
                    if (nitData && nitData.length > 0) {
                        const option = new Option(nitData[0].text, nitData[0].id, false, false);
                        $input.append(option).trigger('change');
                        $(`#concepto_${rowId}`).val($(`#concepto_${prevRow}`).val());
                        scrollToDG(250);
                        break;
                    }
                }
                
            } else {
                setTimeout(() => $input.select2('open'), 10);
            }
            found = true; // Detener el bucle después de procesar el combo
        } else {
            const cuentaData = $(`#combo_cuenta_${rowId}`).select2('data')[0];

            if (cuentaData) {
                //BUSCAR EXTRACTOS
                if (inputIds[nextColumn] === "#documento_referencia") {
                    const tipoCuenta = checkTipoCuentaEspecial(cuentaData.tipos_cuenta);
                    if (tipoCuenta.length) {
                        buscarExtractoDg(rowId);
                    }
                }
                //COMPROBANTE OTROS
                if (tipo_comprobante == 4) {
    
                    const valorCredito = stringToNumberFloat($(`#credito_${rowId}`).val());
                    const valorDebito = stringToNumberFloat($(`#debito_${rowId}`).val());
                    const naturaleza = cuentaData.naturaleza_cuenta === 1 ? 'credito' : 'debito';
    
                    const isCampo = campo => inputIds[nextColumn] === `#${campo}`;
                    const scrollAndFocus = (id, scrollX = 0) => {
                        scrollToDG(scrollX);
                        setTimeout(() => {
                            $(`#documentoReferenciaTable tr`).find(`#${id}_${rowId}`).focus().select?.();
                        }, 10);
                    };
    
                    let debito = false;
                    let credito = false;
    
                    if (isCampo('debito') && !valorDebito && naturaleza === 'credito') {
                        scrollAndFocus('credito', 910);
                        credito = true;
                        found = true;
                    } else if (isCampo('debito') && valorDebito && naturaleza === 'credito') {
                        scrollAndFocus('debito', 800);
                        debito = true;
                        found = true;
                    } else if (isCampo('debito') && naturaleza === 'debito') {
                        scrollAndFocus('debito', 800);
                        debito = true;
                        found = true;
                    } else if (isCampo('credito') && valorDebito) {
                        scrollAndFocus('concepto', 1200);
                        found = true;
                    } else if (isCampo('credito') && !valorDebito) {
                        scrollAndFocus('credito', 910);
                        credito = true;
                        found = true;
                    } else if (isCampo('documento_referencia')) {
                        scrollAndFocus('documento_referencia', 604);
                        found = true;
                    } else if (isCampo('concepto')) {
                        scrollAndFocus('concepto', 1200);
                        found = true;
                    }
                }
            }

        }

        nextColumn++;
    }
}

function scrollToDG(x) {
    document.getElementById("card-documento-general").scrollLeft = x;
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

$(document).on('click', '#iniciarCapturaDocumentos', function () {
    searchCaptura();
});

$(document).on('click', '.btn-documento-extracto', function () {
    var idRow = this.id.split('_')[2];
    buscarExtractoDg(idRow);
});

$(document).on('click', '.select-documento', function () {

    const documentoReferencia = this.id.split('@')[1];
    const dataSplit = this.id.split('@');

    const dataNit = $('#combo_nits_'+rowExtracto).select2('data')[0];
    const dataCuenta = $('#combo_cuenta_'+rowExtracto).select2('data')[0];
    const tiposCuenta = checkTipoCuentaEspecial(dataCuenta.tipos_cuenta);

    $('#texto_extracto_'+rowExtracto).html("Factura seleccionada");
    $('#documento_referencia_'+rowExtracto).removeClass("is-invalid");
    $('#documento_referencia_'+rowExtracto).addClass("is-valid");

    $('#documento_referencia_'+rowExtracto).val(dataSplit[1]);
    $("#modalDocumentoExtracto").modal('hide');
    if (tipo_comprobante != 4) $('#concepto_'+rowExtracto).val(dataNit.text + ' - FACTURA: ' + documentoReferencia);

    const saldo = dataSplit[2] ? parseFloat(dataSplit[2]) : 0;
    let focusValorTexto = 'debito';
    
    if (tiposCuenta.includes(3) || tiposCuenta.includes(7)) {
        if (dataCuenta.naturaleza_cuenta == 0) {
            focusValorTexto = 'credito';
        }
    } else if (tiposCuenta.includes(4) || tiposCuenta.includes(8)) {
        if (dataCuenta.naturaleza_cuenta == 0) {
            focusValorTexto = 'credito';
        }
    }

    document.getElementById(`${focusValorTexto}_${rowExtracto}`).max = saldo;
    $(`#${focusValorTexto}_${rowExtracto}`).val(saldo);
    setTimeout(function(){
        $('#documentoReferenciaTable tr').find(`#${focusValorTexto}_${rowExtracto}`).select();
    },10);
    // focusNextRow(3, rowExtracto);
});

function buscarExtractoDg(idRow) {
    
    if (!documento_extracto) initDatatableExtracto();
    if ($('#combo_nits_'+idRow).val()) {
        let dataNit = $('#combo_nits_'+idRow).select2('data')[0];

        const dataCuenta = JSON.parse($('#combo_cuenta_'+idRow).attr('data-cuenta'));
        rowExtracto = idRow;

        var id_tipo_cuenta = checkTipoCuentaEspecial(dataCuenta.tipos_cuenta);
        cuentaExtractoDg = dataCuenta.cuenta;

        $('#modal-title-documento-extracto').html(dataNit.text);
        $("#modalDocumentoExtracto").modal('show');

        documento_extracto.rows().remove().draw();

        const fechaHora = $("#fecha_manual_documento").val();
        const momento = moment(fechaHora, "YYYY-MM-DD hh:mm:ss A");
        const fechaHoraManual = momento.format("YYYY-MM-DD HH:mm:ss"); 

        $.ajax({
            url: base_url + 'extracto',
            method: 'GET',
            data: {
                editando: editandoCaptura,
                id_tipo_cuenta: id_tipo_cuenta,
                id_nit: $('#combo_nits_'+idRow).val(),
                fecha_manual: fechaHoraManual,
            },
            headers: headers,
            dataType: 'json',
        }).done((res) => {
            var documentos = res.data;
            documento_extracto.rows().remove().draw();
            var dataRowDocumentos = documento_general_table.rows().data();
            for (let index = 0; index < documentos.length; index++) {
                let documento = documentos[index];
                if (dataRowDocumentos.length > 1 ) documento = calcularPagosEnCaptura(documento);
                documento_extracto.row.add(documento).draw();
            }


        }).fail((err) => {
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        });
    }
}

function validarMax(id, type) {
    // Obtener el input mediante su id
    const input = document.getElementById(`${type}_${id}`);
    
    // Obtener el valor máximo permitido
    const max = input.getAttribute('max');
    const inputValue = stringToNumberFloat(input.value);

    if (parseFloat(max) == 0) {
        return;
    }

    // Verificar si el valor del campo es mayor que el máximo
    if (parseFloat(inputValue) > parseFloat(max)) {
        input.value = max;  // Establecer el valor al máximo permitido
        mostrarValores();
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
                    const fechaManual = data[0].fecha_manual;
                    editandoCaptura = true;
                    idDocumento = 0;
                    for (let index = 0; index < data.length; index++) {
                        addRow(false);
                        const documento = data[index];
                        const tipoCuenta = checkTipoCuentaEspecial(documento.cuenta.tipos_cuenta);

                        $('#combo_cuenta_' + index).attr('data-cuenta', JSON.stringify(documento.cuenta));

                        var dataCuenta = {
                            id: documento.cuenta.id,
                            text: documento.cuenta.cuenta + ' - ' + documento.cuenta.nombre
                        };
                        var newOptionCuenta = new Option(dataCuenta.text, dataCuenta.id, false, false);
                        $('#combo_cuenta_'+index).append(newOptionCuenta).trigger('change');

                        // Inicializar Select2 para NIT si hay datos
                        if(documento.nit) {
                            if (!$('#combo_nits_'+index).hasClass('select2-hidden-accessible')) {
                                initComboNitsTableDG(index);
                            }
                            var nombre = documento.nit.tipo_contribuyente == 1 ? documento.nit.razon_social : documento.nit.primer_nombre+' '+documento.nit.primer_apellido;
                            var dataNit = {
                                id: documento.nit.id,
                                text: documento.nit.numero_documento+ ' - ' +nombre
                            };
                            var newOptionNit = new Option(dataNit.text, dataNit.id, false, false);
                            $('#combo_nits_'+index).append(newOptionNit).trigger('change');
                        }

                        // Inicializar Select2 para CECOS si hay datos
                        if(documento.centro_costos) {
                            if (!$('#combo_cecos_'+index).hasClass('select2-hidden-accessible')) {
                                initComboCecosTableDG(index);
                            }
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

                        if(tipo_comprobante == 2 || tipo_comprobante == 5) {
                            documento.cuenta.naturaleza_cuenta = documento.cuenta.naturaleza_compras;
                        }

                        if(tipo_comprobante == 3) {
                            documento.cuenta.naturaleza_cuenta = documento.cuenta.naturaleza_ventas;
                        }

                        $('#concepto_'+index).val(documento.concepto);
                        
                        setDisabledRows(documento.cuenta, index);

                        const inputDocRef = $(`#documento_referencia_${index}`);
                        if (tipoCuenta.length) {
                            inputDocRef.removeClass("normal_input").prop("readonly", false);
                            inputDocRef.removeClass("normal_input").prop("disabled", false);
                        } else {
                            $(`#conten_button_${index}`).hide();
                            inputDocRef.addClass("normal_input").prop("readonly", false);
                        }
                    }
                    
                    $('#fecha_manual_documento').val(normalizarFecha(fechaManual));

                    $("#editing_documento").val("1");
                    agregarToast('exito', 'Documentos encontrados', 'Documentos cargados con exito!', true );
                    mostrarValores();
                    addRow();
                    
                } else {
                    editandoCaptura = false;
                    $("#crearCapturaDocumentos").hide();
                    $("#crearCapturaDocumentosDisabled").show();
                    $("#editing_documento").val("0");
                    addRow();
                }
            } else {
                $("#agregarDocumentos").hide();
                $("#iniciarCapturaDocumentosLoading").hide();                

                $("#iniciarCapturaDocumentos").show();
                $("#id_comprobante").prop('disabled', false);
                $("#consecutivo").prop('disabled', false);
                agregarToast('warning', 'Comprobante en uso', res.message, false);
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

    const dateNow = new Date();
    const fechaHoraDG = dateNow.getFullYear() + '-' + 
        ("0" + (dateNow.getMonth() + 1)).slice(-2) + '-' + 
        ("0" + dateNow.getDate()).slice(-2) + 'T' + 
        ("0" + dateNow.getHours()).slice(-2) + ':' + 
        ("0" + dateNow.getMinutes()).slice(-2);

    $('#fecha_manual_documento').val(fechaHoraDG);
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
                consecutivoUltimo = parseInt(res.data);
                $("#consecutivo").val(res.data);
                $("#consecutivo").prop('disabled', false);
            }
        }).fail((err) => {
            $("#iniciarCapturaDocumentos").show();
            $("#iniciarCapturaDocumentosLoading").hide();
            $("#consecutivo").prop('disabled', false);
            var mensaje = err.responseJSON.message;
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
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

$(document).on('keydown', '.custom-documentogeneral_cuenta .select2-search__field', function (event) {

    const dataSearch = $('.select2-search__field').val();

    if (event.keyCode == 96 && !dataSearch.length) {
        guardarDocumentoDG = true;
    } else if (event.keyCode == 13){
        if (guardarDocumentoDG) {
            guardarDocumentoDG = false;
            askSaveDocumentos = false;
            $('.combo_cuenta').select2('close');
            capturarDcoumentosGenerales();
        }
    } else {
        guardarDocumentoDG = false;
    }
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
    var titulo = 'Guardar documento?';

    if (!capturarDocumentosDescuadrados && diferencia != 0) {
        agregarToast('warning', 'Documentos descuadrados', 'Para guardarlos debe activar la opción en Configuración > Empresa "Capturar documentos descuadrados".', false);
        return;
    }

    if (diferencia != 0) {
        titulo = "Documento descuadrado";
        texto = "Desea guardar un documento descuadrado?";
        type = "warning";
    } else if (debito == 0 && credito == 0) {
        agregarToast('warning', 'Creación errada', 'Sin datos para guardar en la tabla', true);
        return;
    }
    
    if(askSaveDocumentos) {

        Swal.fire({
            title: titulo,
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

    const fechaHora = $("#fecha_manual_documento").val();
    const momento = moment(fechaHora, "YYYY-MM-DD hh:mm:ss A");
    const fechaHoraManual = momento.format("YYYY-MM-DD HH:mm:ss"); 
    
    let data = {
        documento: getDocumentos(),
        fecha_manual: fechaHoraManual,
        id_comprobante: $("#id_comprobante").val(),
        consecutivo: $("#consecutivo").val(),
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
            if(res.impresion && res.id_comprobante) {
                window.open(`/documentos-generales-print/${res.id_comprobante}/${res.impresion}/${res.fecha_manual}`, "", "_blank");
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
        var errorsMsg = arreglarMensajeError(mensaje);
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
                var crearDocumento = $('#texto_extracto_'+idDocumento).text() == "Nueva factura" ? true : false;

                if (id_cuenta && (debito + credito) > 0) {
                    data.push({
                        id_cuenta: cuenta ? parseInt(cuenta) : '',
                        id_nit: nit ? parseInt(nit) : '',
                        id_centro_costos: cecos ? parseInt(cecos) : '',
                        documento_referencia: dctrf ? dctrf : '',
                        debito: debito ? parseFloat(debito) : 0,
                        credito: credito ? parseFloat(credito) : 0,
                        concepto: concepto ? concepto : '',
                        crear_documento: crearDocumento
                    });
                }

            }
        }
    }
    return data;
}