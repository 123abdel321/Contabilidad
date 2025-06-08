var contrato_table = null;
var $comboNitContrato = null;
var $comboPeriodoContrato = null;
var $comboConceptoBasicoContrato = null;
var $comboCecosContrato = null;
var $comboFondoSaludContrato = null;
var $comboFondoPensionContrato = null;
var $comboFondoCesantiasContrato = null;
var $comboCajaCompensacionContrato = null;
var $comboAdministradoraRiesgoContrato = null;

function contratosInit() {

    cargarTablasContratos();
    cargarSelect2Contratos();

    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        html: true,
        placement: 'top',
        container: 'body',
        customClass: 'popover-formas-pagos'
    });

    $('.water').hide();
}

function cargarTablasContratos() {
    contrato_table = $('#contratosTable').DataTable({
        pageLength: 15,
        dom: 'Brtip',
        paging: true,
        responsive: false,
        processing: true,
        serverSide: true,
        fixedHeader: true,
        deferLoading: 0,
        initialLoad: false,
        language: lenguajeDatatable,
        sScrollX: "100%",
        fixedColumns : {
            left: 0,
            right : 1,
        },
        ajax:  {
            type: "GET",
            headers: headers,
            url: base_url + 'contratos',
        },
        columns: [
            {"data": function (row, type, set){  
                if (row.nit) {
                    return `${row.nit.numero_documento} - ${row.nit.nombre_completo}`
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.periodo) {
                    return `${row.periodo.nombre}`
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.concepto_basico) {
                    return `${row.concepto_basico.codigo} - ${row.concepto_basico.nombre}`
                }
                return '';
            }},
            {"data":'fecha_inicio_contrato'},
            {"data": function (row, type, set){  
                if (row.fecha_fin_contrato) {
                    return row.fecha_fin_contrato;
                }
                return 'Indefinido';
            }},
            {"data": function (row, type, set){  
                // FECHA INICIO PERIODO
                return '';
            }},
            {"data": function (row, type, set){  
                // FECHA FIN PERIODO
                return '';
            }},
            {"data": function (row, type, set){  
                let estadoTexto = 'Inactivo';
                if (row.estado == 1) estadoTexto = 'Activo';
                if (row.estado == 2) estadoTexto = 'Finalizado';
                return estadoTexto;
            }},
            {"data": function (row, type, set){  
                let terminoTexto = 'Indefinido';
                if (row.termino == 1) terminoTexto = 'Fijo';
                if (row.termino == 2) terminoTexto = 'Obra-Labor';
                if (row.termino == 3) terminoTexto = 'Transitorio';
                return terminoTexto;
            }},
            {"data": function (row, type, set){  
                let tipoSalarioTexto = 'Normal';
                if (row.termino == 1) tipoSalarioTexto = 'Honorarios';
                if (row.termino == 2) tipoSalarioTexto = 'Integral';
                if (row.termino == 3) tipoSalarioTexto = 'Servicios';
                if (row.termino == 4) tipoSalarioTexto = 'Practicante';
                return tipoSalarioTexto;
            }},
            {"data": function (row, type, set){  
                if (row.cecos) {
                    return `${row.cecos.codigo} - ${row.cecos.nombre}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                // OFICIO
                return '';
            }},
            {"data":'salario', render: $.fn.dataTable.render.number(',', '.', 0, ''), className: 'dt-body-right'},
            {"data": function (row, type, set){  
                let tipoCotizanteTexto = 'Ninguno';
                if (row.tipo_cotizante == 1) tipoCotizanteTexto = 'Dependiente';
                if (row.tipo_cotizante == 12) tipoCotizanteTexto = 'Aprendices en etapa lectiva';
                if (row.tipo_cotizante == 19) tipoCotizanteTexto = 'Aprendices en etapa productiva';
                return tipoCotizanteTexto;
            }},
            {"data": function (row, type, set){  
                let subTipoCotizanteTexto = 'Ninguno';
                if (row.subtipo_cotizante == 1) subTipoCotizanteTexto = 'Dependiente pensionado por vejez activo';
                if (row.subtipo_cotizante == 12) subTipoCotizanteTexto = 'Cotizante no obligado a cotización a pensiones por edad';
                if (row.subtipo_cotizante == 19) subTipoCotizanteTexto = 'Cotizante pensionado con mesada superior a 25 SMLMV';
                return subTipoCotizanteTexto;
            }},
            {"data": function (row, type, set){  
                if (row.fondo_salud) {
                    return `${getTipoAdministradoraContrato(row.fondo_salud.tipo)} - ${row.fondo_salud.descripcion}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.fondo_pension) {
                    return `${getTipoAdministradoraContrato(row.fondo_pension.tipo)} - ${row.fondo_pension.descripcion}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.fondo_cesantias) {
                    return `${getTipoAdministradoraContrato(row.fondo_cesantias.tipo)} - ${row.fondo_cesantias.descripcion}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.fondo_caja_compensacion) {
                    return `${getTipoAdministradoraContrato(row.fondo_caja_compensacion.tipo)} - ${row.fondo_caja_compensacion.descripcion}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                if (row.fondo_arl) {
                    return `${getTipoAdministradoraContrato(row.fondo_arl.tipo)} - ${row.fondo_arl.descripcion}`;
                }
                return '';
            }},
            {"data": function (row, type, set){  
                let nivelRiesgoTexto = '0 - 0%';
                if (row.nivel_riesgo_arl == 1) nivelRiesgoTexto = 'I (Bajo) - 0.522%';
                if (row.nivel_riesgo_arl == 2) nivelRiesgoTexto = 'II (Medio) - 1.044%';
                if (row.nivel_riesgo_arl == 3) nivelRiesgoTexto = 'III (Alto) - 2.436';
                if (row.nivel_riesgo_arl == 4) nivelRiesgoTexto = 'IV (Muy Alto) - 4.35%';
                if (row.nivel_riesgo_arl == 5) nivelRiesgoTexto = 'V (Extremo) - 6.96%';
                return nivelRiesgoTexto;
            }},
            {"data": function (row, type, set){  
                let metodoRetencionTexto = 'Ninguno';
                if (row.metodo_retencion == 1) metodoRetencionTexto = 'Mensual';
                if (row.metodo_retencion == 2) metodoRetencionTexto = 'Anual';
                return metodoRetencionTexto;
            }},
            {"data":'disminucion_defecto_retencion'},
            {"data": function (row, type, set){
                if (row.auxilio_transporte) {
                    return 'Si';
                }
                return 'No';

            }},
            {"data":'talla_camisa'},
            {"data":'talla_pantalon'},
            {"data":'talla_zapatos'},
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
                    var html = '';
                    if (editarContratos) html+= `<span id="editcontratos_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-success edit-contrato" style="margin-bottom: 0rem !important; min-width: 50px;">Editar</span>&nbsp;`;
                    if (eliminarContratos) html+= `<span id="deletecontratos_${row.id}" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-contrato" style="margin-bottom: 0rem !important; min-width: 50px;">Eliminar</span>`;
                    return html;
                }
            },
        ]
    });

    if (contrato_table) {
        contrato_table.on('click', '.drop-contrato', function() {
            var id = this.id.split('_')[1];
            var data = getDataById(id, contrato_table);

            Swal.fire({
                title: `Eliminar contrato de: ${data.nit.nombre_completo}?`,
                text: "No se podrá revertir!",
                type: 'warning',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true,
            }).then((result) => {
                if (result.value){
                    $.ajax({
                        url: base_url + 'contratos',
                        method: 'DELETE',
                        data: JSON.stringify({id: id}),
                        headers: headers,
                        dataType: 'json',
                    }).done((res) => {
                        if(res.success){
                            contrato_table.ajax.reload();
                            agregarToast('exito', 'Eliminación exitosa', 'Contrato eliminado con exito!', true );
                        } else {
                            agregarToast('error', 'Eliminación errada', res.message);
                        }
                    }).fail((res) => {
                        agregarToast('error', 'Eliminación errada', res.message);
                    });
                }
            })
        });
        contrato_table.on('click', '.edit-contrato', function() {

            clearFormContratos();

            $("#textContratosCreate").hide();
            $("#textContratosUpdate").show();
            $("#saveContratosLoading").hide();
            $("#updateContratos").show();
            $("#saveContratos").hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, contrato_table);

            $("#id_contratos_up").val(data.id);

            // $("#id_oficio_contrato_nomina").val('').trigger('change');

            if(data.nit){
                var dataEmpleado = {
                    id: data.nit.id,
                    text: data.nit.numero_documento + ' - ' + data.nit.nombre_completo
                };
                var newOption = new Option(dataEmpleado.text, dataEmpleado.id, false, false);
                $comboNitContrato.append(newOption).val(dataEmpleado.id).trigger('change');
            }

            if(data.periodo){
                var dataPeriodo = {
                    id: data.periodo.id,
                    text: data.periodo.nombre
                };
                var newOption = new Option(dataPeriodo.text, dataPeriodo.id, false, false);
                $comboPeriodoContrato.append(newOption).val(dataPeriodo.id).trigger('change');
            }

            if(data.concepto_basico){
                var dataConceptoBasico = {
                    id: data.concepto_basico.id,
                    text: data.concepto_basico.codigo + ' - ' + data.concepto_basico.nombre
                };
                var newOption = new Option(dataConceptoBasico.text, dataConceptoBasico.id, false, false);
                $comboConceptoBasicoContrato.append(newOption).val(dataConceptoBasico.id).trigger('change');
            }

            if(data.cecos){
                var dataCecos = {
                    id: data.cecos.id,
                    text: data.cecos.codigo + ' - ' + data.cecos.nombre
                };
                var newOption = new Option(dataCecos.text, dataCecos.id, false, false);
                $comboCecosContrato.append(newOption).val(dataCecos.id).trigger('change');
            }

            if(data.fondo_salud){
                var dataFondo = {
                    id: data.fondo_salud.id,
                    text: data.fondo_salud.codigo + ' - ' + data.fondo_salud.descripcion
                };
                var newOption = new Option(dataFondo.text, dataFondo.id, false, false);
                $comboFondoSaludContrato.append(newOption).val(dataFondo.id).trigger('change');
            }

            if(data.fondo_pension){
                var dataFondo = {
                    id: data.fondo_pension.id,
                    text: data.fondo_pension.codigo + ' - ' + data.fondo_pension.descripcion
                };
                var newOption = new Option(dataFondo.text, dataFondo.id, false, false);
                $comboFondoPensionContrato.append(newOption).val(dataFondo.id).trigger('change');
            }
            
            if(data.fondo_cesantias){
                var dataFondo = {
                    id: data.fondo_cesantias.id,
                    text: data.fondo_cesantias.codigo + ' - ' + data.fondo_cesantias.descripcion
                };
                var newOption = new Option(dataFondo.text, dataFondo.id, false, false);
                $comboFondoCesantiasContrato.append(newOption).val(dataFondo.id).trigger('change');
            }
            
            if(data.fondo_caja_compensacion){
                var dataFondo = {
                    id: data.fondo_caja_compensacion.id,
                    text: data.fondo_caja_compensacion.codigo + ' - ' + data.fondo_caja_compensacion.descripcion
                };
                var newOption = new Option(dataFondo.text, dataFondo.id, false, false);
                $comboCajaCompensacionContrato.append(newOption).val(dataFondo.id).trigger('change');
            }

            if(data.fondo_arl){
                var dataFondo = {
                    id: data.fondo_arl.id,
                    text: data.fondo_arl.codigo + ' - ' + data.fondo_arl.descripcion
                };
                var newOption = new Option(dataFondo.text, dataFondo.id, false, false);
                $comboAdministradoraRiesgoContrato.append(newOption).val(dataFondo.id).trigger('change');
            }

            $("#fecha_inicio_contrato_nomina").val(data.fecha_inicio_contrato);
            $("#fecha_fin_contrato_nomina").val(data.fecha_fin_contrato);
            $("#fecha_inicio_periodo_nomina").val(data.fecha_inicio_periodo);
            $("#fecha_fin_periodo_nomina").val(data.fecha_fin_periodo);
            $("#estado_contrato_nomina").val(data.estado).trigger('change');
            $("#termino_contrato_nomina").val(data.termino).trigger('change');
            $("#salario_contrato_nomina").val(new Intl.NumberFormat("ja-JP").format(data.salario));
            $("#tipo_salario_contrato_nomina").val(data.tipo_salario).trigger('change');
            $("#tipo_empleado_contrato_nomina").val(data.tipo_empleado).trigger('change');
            $("#tipo_cotizante_contrato_nomina").val(data.tipo_cotizante).trigger('change');
            $("#subtipo_cotizante_contrato_nomina").val(data.subtipo_cotizante).trigger('change');
            $("#nivel_riesgo_arl_compensacion_contrato_nomina").val(data.nivel_riesgo_arl).trigger('change');
            $("#metodo_retencion_compensacion_contrato_nomina").val(data.metodo_retencion).trigger('change');
            $("#porcentaje_fijo_contrato_nomina").val(data.porcentaje_fijo);
            $("#disminucion_defecto_retencion_contrato_nomina").val(data.disminucion_defecto_retencion);
            $("#auxilio_transporte_contrato_nomina").val(data.auxilio_transporte).trigger('change');
            $("#talla_camisa_contrato_nomina").val(data.talla_camisa);
            $("#talla_pantalon_contrato_nomina").val(data.talla_pantalon);
            $("#talla_zapatos_contrato_nomina").val(data.talla_zapatos);

            $("#contratosFormModal").modal('show');
        });
    }
    contrato_table.ajax.reload();
}

function cargarSelect2Contratos() {
    $comboNitContrato = $('#id_empleado_contrato_nomina').select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
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

    $comboPeriodoContrato = $('#id_periodo_contrato_nomina').select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/periodos-combo',
            headers: headers,
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboConceptoBasicoContrato = $('#id_concepto_basico_contrato_nomina').select2({
        theme: 'bootstrap-5',
        delay: 250,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/conceptos-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo_concepto: 'basico'
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

    $comboCecosContrato = $('#id_centro_costo_contrato_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#contratosFormModal'),
        delay: 250,
        placeholder: "Seleccione un centro de costos",
        allowClear: true,
        language: {
            noResults: function() {
                return "No hay resultado";        
            },
            searching: function() {
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/centro-costos/combo-centro-costo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
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

    $comboFondoSaludContrato = $('#id_fondo_salud_contrato_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#contratosFormModal'),
        delay: 250,
        placeholder: "Seleccione un fondo de salud",
        allowClear: true,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/administradoras-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo: 0
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

    $comboFondoPensionContrato = $('#id_fondo_pension_contrato_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#contratosFormModal'),
        delay: 250,
        placeholder: "Seleccione un fondo de pensión",
        allowClear: true,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/administradoras-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo: 1
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

    $comboFondoCesantiasContrato = $('#id_fondo_cesantias_contrato_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#contratosFormModal'),
        delay: 250,
        placeholder: "Seleccione un fondo de cesantias",
        allowClear: true,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/administradoras-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo: 1
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

    $comboCajaCompensacionContrato = $('#id_fondo_caja_compensacion_contrato_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#contratosFormModal'),
        delay: 250,
        placeholder: "Seleccione una caja de compensaciones",
        allowClear: true,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/administradoras-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo: 3
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

    $comboAdministradoraRiesgoContrato = $('#id_fondo_arl_compensacion_contrato_nomina').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#contratosFormModal'),
        delay: 250,
        placeholder: "Seleccione una administradora de riesgos",
        allowClear: true,
        language: {
            noResults: function() {
                createNewNit = true;
                return "No hay resultado";        
            },
            searching: function() {
                createNewNit = false;
                return "Buscando..";
            },
            inputTooShort: function () {
                return "Por favor introduce 1 o más caracteres";
            }
        },
        ajax: {
            url: 'api/administradoras-combo',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    tipo: 2
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

function clearFormContratos() {
    $("#textContratosCreate").show();
    $("#textContratosUpdate").hide();
    $("#saveContratos").show();
    $("#updateContratos").hide();
    $("#saveContratosLoading").hide();

    $("#div-porcentaje_fijo_contrato_nomina").hide();


    $("#id_empleado_contrato_nomina").val('').trigger('change')
    $("#id_periodo_contrato_nomina").val('').trigger('change')
    $("#id_concepto_basico_contrato_nomina").val('').trigger('change')
    $("#fecha_inicio_contrato_nomina").val(dateNow.getFullYear()+'-'+("0" + (dateNow.getMonth() + 1)).slice(-2)+'-01');
    $("#fecha_fin_contrato_nomina").val('');
    $("#fecha_inicio_periodo_nomina").val('');
    $("#fecha_fin_periodo_nomina").val('');
    $("#estado_contrato_nomina").val('1').trigger('change');
    $("#termino_contrato_nomina").val('1').trigger('change');
    $("#salario_contrato_nomina").val('0');
    $("#tipo_salario_contrato_nomina").val('0').trigger('change');
    $("#tipo_empleado_contrato_nomina").val('0').trigger('change');
    $("#tipo_cotizante_contrato_nomina").val('1').trigger('change');
    $("#subtipo_cotizante_contrato_nomina").val('').trigger('change');
    $("#id_centro_costo_contrato_nomina").val('').trigger('change');
    $("#id_oficio_contrato_nomina").val('').trigger('change');
    $("#id_fondo_salud_contrato_nomina").val('').trigger('change');
    $("#id_fondo_pension_contrato_nomina").val('').trigger('change');
    $("#id_fondo_cesantias_contrato_nomina").val('').trigger('change');
    $("#id_fondo_caja_compensacion_contrato_nomina").val('').trigger('change');
    $("#id_fondo_arl_compensacion_contrato_nomina").val('').trigger('change');
    $("#nivel_riesgo_arl_compensacion_contrato_nomina").val('1').trigger('change');
    $("#metodo_retencion_compensacion_contrato_nomina").val('0').trigger('change');
    $("#porcentaje_fijo_contrato_nomina").val(0);
    $("#disminucion_defecto_retencion_contrato_nomina").val(0);
    $("#auxilio_transporte_contrato_nomina").val("1").trigger('change');
    $("#talla_camisa_contrato_nomina").val("");
    $("#talla_pantalon_contrato_nomina").val("");
    $("#talla_zapatos_contrato_nomina").val("");
}

function enterContrato(e) {
    if (e.key === 'Enter' || e.keyCode === 13) {
        const id_contrato = $("#id_contratos_up").val();
        if (id_contrato) actualizarContrato();
        else  guardarContrato();
    }
}

function guardarContrato() {
    const form = document.querySelector('#contratosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        const firstInvalidInput = form.querySelector(':invalid');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
        }
        return;
    }

    $("#saveContratos").hide();
    $("#updateContratos").hide();
    $("#saveContratosLoading").show();
    
    let data = getDataContratos();

    $.ajax({
        url: base_url + 'contratos',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormContratos();
            $("#saveContratos").show();
            $("#saveContratosLoading").hide();
            $("#contratosFormModal").modal('hide');
            contrato_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Contrato creado con exito!', true);
        }
    }).fail((err) => {
        $('#saveContratos').show();
        $('#saveContratosLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Creación errada', errorsMsg);
    });
}

function actualizarContrato() {
    const form = document.querySelector('#contratosForm');

    if(!form.checkValidity()){
        form.classList.add('was-validated');
        const firstInvalidInput = form.querySelector(':invalid');
        if (firstInvalidInput) {
            firstInvalidInput.focus();
        }
        return;
    }

    $("#saveContratos").hide();
    $("#updateContratos").hide();
    $("#saveContratosLoading").show();

    let data = getDataContratos();

    $.ajax({
        url: base_url + 'contratos',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            clearFormContratos();
            $("#saveContratos").show();
            $("#saveContratosLoading").hide();
            $("#contratosFormModal").modal('hide');
            contrato_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Contrato actualizado con exito!', true);
        }
    }).fail((err) => {
        $('#updateContratos').show();
        $('#saveContratosLoading').hide();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
}

function getDataContratos() {
    return {
        id: $("#id_contratos_up").val(),
        id_empleado: $("#id_empleado_contrato_nomina").val(),
        id_periodo: $("#id_periodo_contrato_nomina").val(),
        id_concepto_basico: $("#id_concepto_basico_contrato_nomina").val(),
        fecha_inicio_contrato: $("#fecha_inicio_contrato_nomina").val(),
        fecha_fin_contrato: $("#fecha_fin_contrato_nomina").val(),
        fecha_inicio_periodo: $("#fecha_inicio_periodo_nomina").val(),
        fecha_fin_periodo: $("#fecha_fin_periodo_nomina").val(),
        estado: $("#estado_contrato_nomina").val(),
        termino: $("#termino_contrato_nomina").val(),
        salario: stringToNumberFloat($("#salario_contrato_nomina").val()),
        tipo_salario: $("#tipo_salario_contrato_nomina").val(),
        tipo_empleado: $("#tipo_empleado_contrato_nomina").val(),
        tipo_cotizante: $("#tipo_cotizante_contrato_nomina").val(),
        porcentaje_arl: $("#porcentaje_arl_contrato_nomina").val(),
        subtipo_cotizante: $("#subtipo_cotizante_contrato_nomina").val(),
        id_centro_costo: $("#id_centro_costo_contrato_nomina").val(),
        id_oficio: $("#id_oficio_contrato_nomina").val(),
        id_fondo_salud: $("#id_fondo_salud_contrato_nomina").val(),
        id_fondo_pension: $("#id_fondo_pension_contrato_nomina").val(),
        id_fondo_cesantias: $("#id_fondo_cesantias_contrato_nomina").val(),
        id_fondo_caja_compensacion: $("#id_fondo_caja_compensacion_contrato_nomina").val(),
        id_fondo_arl: $("#id_fondo_arl_compensacion_contrato_nomina").val(),
        nivel_riesgo_arl_compensacion: $("#nivel_riesgo_arl_compensacion_contrato_nomina").val(),
        metodo_retencion_compensacion: $("#metodo_retencion_compensacion_contrato_nomina").val(),
        porcentaje_fijo: $("#porcentaje_fijo_contrato_nomina").val(),
        disminucion_defecto_retencion: $("#disminucion_defecto_retencion_contrato_nomina").val(),
        auxilio_transporte: $("#auxilio_transporte_contrato_nomina").val(),
        talla_camisa: $("#talla_camisa_contrato_nomina").val(),
        talla_pantalon: $("#talla_pantalon_contrato_nomina").val(),
        talla_zapatos: $("#talla_zapatos_contrato_nomina").val(),
    }
}

function getTipoAdministradoraContrato(tipo) {
    let nombreTipo = 'EPS';
    if (tipo == 1) nombreTipo = 'AFP';
    if (tipo == 2) nombreTipo = 'ARL';
    if (tipo == 3) nombreTipo = 'CCF';
    return nombreTipo;
}

$(document).on('click', '#createContratos', function () {
    clearFormContratos();
    $("#contratosFormModal").modal('show');
});

$(document).on('change', '#nivel_riesgo_arl_compensacion_contrato_nomina', function () {
    const nivel_riesgo = parseInt($("#nivel_riesgo_arl_compensacion_contrato_nomina").val());

    let porcentaje = 0;
    switch (nivel_riesgo) {
        case 1:
            porcentaje = 0.522;
            break;
        case 2:
            porcentaje = 1.044;
            break;
        case 3:
            porcentaje = 2.436;
            break;
        case 4:
            porcentaje = 4.35;
            break;
        case 5:
            porcentaje = 6.96;
            break;
        default:
            break;
    }
    $("#porcentaje_arl_contrato_nomina").val(porcentaje);
});

$(document).on('change', '#metodo_retencion_compensacion_contrato_nomina', function () {
    const metodo_retencion = parseInt($("#metodo_retencion_compensacion_contrato_nomina").val());

    if (metodo_retencion) {
        $("#porcentaje_fijo_contrato_nomina").prop("required", true);
        $("#div-porcentaje_fijo_contrato_nomina").show();
    } else {
        $("#porcentaje_fijo_contrato_nomina").prop("required", false);
        $("#div-porcentaje_fijo_contrato_nomina").hide();
        $("#porcentaje_fijo_contrato_nomina").val("");
    }
});

$(document).on('click', '#saveContratos', function () {
    guardarContrato();
});

$(document).on('click', '#updateContratos', function () {
    actualizarContrato();
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