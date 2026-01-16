
var tokenEco = "";
var $comboClienteVentas = null;
var $comboComprobanteNomina = null;
var $comboComprobanteParafiscales = null;
var $comboComprobanteSeguridadSocial = null;
var $comboComprobantePrestacionesSociales = null;

function entornoInit() {

    cargarPopoverGeneral();
    cargarVariablesDeEntorno();
    cargarSelect2VariablesDeEntorno();
}

function cargarVariablesDeEntorno() {
    for (let index = 0; index < variablesEntorno.length; index++) {
        const variable = variablesEntorno[index];

        const checksEntorno = [
            'iva_incluido',
            'capturar_documento_descuadrado',
            'validar_salto_consecutivos',
            'vendedores_ventas',
            'ubicacion_maximoph',
            'fecha_ultimo_cierre',
            'no_exonerado_parafiscales',
            'recordar_ultimo_precio_venta',
            'precio_ponderado',
        ];

        const select2Comprobantes = [
            'id_comprobante_nomina',
            'id_comprobante_parafiscales',
            'id_comprobante_seguridad_social',
            'id_comprobante_prestaciones_sociales',
        ];

        checksEntorno.forEach(entorno => {
            if (variable.nombre == entorno) {
                if (variable.valor == '1') $('#'+entorno).prop('checked', true);
                else $('#'+entorno).prop('checked', false);
            }
        });

        select2Comprobantes.forEach(entorno => {
            if (variable.nombre == entorno) {
                if (variable.comprobante) {
                    const dataComprobante = {
                        id: variable.comprobante.id,
                        text: variable.comprobante.codigo + ' - ' + variable.comprobante.nombre
                    };
                    const newOption = new Option(dataComprobante.text, dataComprobante.id, false, false);
                    $('#'+entorno).append(newOption).val(dataComprobante.id).trigger('change');
                }
            }
        });

        if (variable.nombre == 'id_cliente_venta_defecto') {
            if (variable.nit) {
                const dataCliente = {
                    id: variable.nit.id,
                    text: variable.nit.numero_documento + ' - ' + variable.nit.nombre_completo
                };
                const newOption = new Option(dataCliente.text, dataCliente.id, false, false);
                $('#id_cliente_venta_defecto').append(newOption).val(dataCliente.id).trigger('change');
            }
        }

        if (variable.nombre == 'eco_login') {
            tokenEco = variable.valor
        }

        if (variable.nombre == 'valor_uvt') {
            $('#valor_uvt').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'porcentaje_iva_aiu') {
            $('#porcentaje_iva_aiu').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'fecha_ultimo_cierre') {
            $('#fecha_ultimo_cierre').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'redondeo_gastos') {
            $('#redondeo_gastos').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_utilidad') {
            $('#cuenta_utilidad').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_perdida') {
            $('#cuenta_perdida').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'observacion_venta') {
            quill.root.innerHTML = variable.valor;
            continue;
        }

        if (variable.nombre == 'salario_minimo') {
            $('#salario_minimo').val(new Intl.NumberFormat('ja-JP').format(variable.valor));
            continue;
        }

        if (variable.nombre == 'subsidio_transporte') {
            $('#subsidio_transporte').val(new Intl.NumberFormat('ja-JP').format(variable.valor));
            continue;
        }

        if (variable.nombre == 'cuenta_x_pagar_empleados') {
            $('#cuenta_x_pagar_empleados').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_contable_pago_nomina') {
            $('#cuenta_contable_pago_nomina').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'cuenta_bancaria_nomina') {
            $('#cuenta_bancaria_nomina').val(variable.valor);
            continue;
        }

        if (variable.nombre == 'tipo_cuenta_banco') {
            $('#tipo_cuenta_banco').val(variable.valor).trigger('change');
            continue;
        }
    }
}

function cargarSelect2VariablesDeEntorno() {
    $comboClienteVentas = $('#id_cliente_venta_defecto').select2({
        theme: 'bootstrap-5',
        delay: 250,
        dropdownCssClass: 'custom-id_cliente_venta_defecto',
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

    $comboComprobanteNomina = $('#id_comprobante_nomina').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboComprobanteParafiscales= $('#id_comprobante_parafiscales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboComprobanteSeguridadSocial= $('#id_comprobante_seguridad_social').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });

    $comboComprobantePrestacionesSociales= $('#id_comprobante_prestaciones_sociales').select2({
        theme: 'bootstrap-5',
        delay: 250,
        placeholder: "Seleccione un comprobante para nomina",
        allowClear: true,
        ajax: {
            url: 'api/comprobantes/combo-comprobante',
            headers: headers,
            data: function (params) {
                var query = {
                    q: params.term,
                    tipo_comprobante: 4,
                    _type: 'query'
                }
                return query;
            },
            dataType: 'json',
            processResults: function (data) {
                return {
                    results: data.data
                };
            }
        }
    });
}

function validarNotificaciones() {
    $.ajax({
        url: base_url_eco + 'credenciales',
        method: 'GET',
        headers: {
            "Authorization": tokenEco,
            "Content-Type": "application/json",
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            let htmlContent = '';

            // Recorremos los datos
            res.data.forEach(item => {
                // Lógica visual según el tipo de canal
                let icono = '';
                let colorIcono = '';
                let titulo = '';

                // Definir iconos y colores según el tipo
                if(item.tipo === 'whatsapp'){
                    icono = 'fab fa-whatsapp';
                    titulo = 'WhatsApp';
                    // Si está activo verde, si no gris
                    colorIcono = item.activo ? 'text-success' : 'text-secondary'; 
                } else if(item.tipo === 'email'){
                    icono = 'fas fa-envelope';
                    titulo = 'Correo Electrónico';
                    // Si está activo warning (típico de email en argon) o info, si no gris
                    colorIcono = item.activo ? 'text-warning' : 'text-secondary';
                } else {
                    icono = 'fab fa-bell';
                    titulo = item.tipo.charAt(0).toUpperCase() + item.tipo.slice(1);
                    colorIcono = 'text-info';
                }

                // Lógica para el estado (Badge)
                let badgeClass = item.activo ? 'bg-gradient-success' : 'bg-gradient-secondary';
                let textoEstado = item.activo ? 'Activo' : 'Inactivo';
                
                // Lógica para el estado de verificación
                let verificacionHtml = '';
                if(item.estado_verificacion === 'verificado'){
                    verificacionHtml = `<span class="text-xs text-success font-weight-bold"><i class="fas fa-check-circle me-1"></i>Verificado</span>`;
                } else {
                    verificacionHtml = `<span class="text-xs text-danger font-weight-bold"><i class="fas fa-exclamation-circle me-1"></i>No verificado</span>`;
                }

                // Construcción de la tarjeta (Card de Argon)
                htmlContent += `
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-frame shadow-sm h-100">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-lg icon-shape bg-white shadow text-center border-radius-xl me-3">
                                    <i class="${icono} ${colorIcono} opacity-10" aria-hidden="true" style="font-size: 1.5rem; line-height: 1.5;"></i>
                                </div>
                                
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-sm font-weight-bolder">${titulo}</h6>
                                        <span class="badge badge-sm ${badgeClass}">${textoEstado}</span>
                                    </div>
                                    <p class="text-xs text-secondary mb-0">
                                        Proveedor: <span class="text-dark font-weight-bold text-capitalize">${item.proveedor}</span>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        ${verificacionHtml}
                                        <small class="text-xxs text-secondary">${new Date(item.ultima_verificacion).toLocaleDateString()}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });

            // Si no hay datos
            if(res.data.length === 0){
                htmlContent = `
                    <div class="col-12 text-center text-muted">
                        <p>No se encontraron canales configurados.</p>
                    </div>`;
            }

            // Inyectar el HTML
            $('#contenedor-canales').html(htmlContent);
        }
    }).fail((err) => {
        // Manejo de error visual en el contenedor
        $('#contenedor-canales').html(`
            <div class="col-12 text-center text-danger py-3">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <p class="text-sm">Error al cargar las notificaciones.</p>
            </div>
        `);

        $('#updateCecos').show();
        $('#saveCecosLoading').hide();
        
        var mensaje = err.responseJSON?.message || "Error desconocido";
        // Tu función existente de manejo de errores
        if(typeof arreglarMensajeError === 'function') {
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Creación errada', errorsMsg);
        }
    });
}

function generarTokenEco() {
    
    // Cambio visual: Ocultar botón, mostrar cargando
    $('#btn-container-token').hide();
    $('#spinner-token').show();

    $.ajax({
        url: base_url + 'eco-register',
        method: 'POST',
        headers: headers,
        dataType: 'json',
    }).done((res) => {

        $('#btn-container-token').show();
        $('#spinner-token').hide();
        
        if(res.success){
            // 1. Asignar el token a la variable global
            tokenEco = res.token; 
            // 2. Feedback visual rápido (Opcional, un toast de éxito)
            agregarToast('exito', 'Conexión Exitosa', 'Notificaciones configuradas correctamente!', true);
            // 3. Cambiar vistas automáticamente
            $("#div-token-eco").fadeOut(300, function() {
                $("#div-canales-eco").fadeIn(300);
                validarNotificaciones();
            });

        }

    }).fail((err) => {
        // Restaurar estado visual
        $('#btn-container-token').show();
        $('#spinner-token').hide();

        var mensaje = err.responseJSON?.message || "Error de conexión";
        if(typeof arreglarMensajeError === 'function') {
            var errorsMsg = arreglarMensajeError(mensaje);
            agregarToast('error', 'Error', errorsMsg);
        }
    });
}

var quill = new Quill('#editor-container', {
    theme: 'snow',
    placeholder: 'Escribe algo aquí...',
    modules: {
        toolbar: [['bold', 'italic', 'underline'], [{ 'list': 'ordered' }, { 'list': 'bullet' }], ['link']]
    }
});

$(document).on('click', '#updateEntorno', function () {
    $("#updateEntornoLoading").show();
    $("#updateEntorno").hide();

    let data = {
        valor_uvt: $('#valor_uvt').val(),
        porcentaje_iva_aiu: $('#porcentaje_iva_aiu').val(),
        redondeo_gastos: $('#redondeo_gastos').val(),
        cuenta_utilidad: $('#cuenta_utilidad').val(),
        cuenta_perdida: $('#cuenta_perdida').val(),
        fecha_ultimo_cierre: $('#fecha_ultimo_cierre').val(),
        observacion_venta: quill.root.innerHTML,
        iva_incluido: $("input[type='checkbox']#iva_incluido").is(':checked') ? '1' : '',
        capturar_documento_descuadrado: $("input[type='checkbox']#capturar_documento_descuadrado").is(':checked') ? '1' : '0',
        validar_salto_consecutivos: $("input[type='checkbox']#validar_salto_consecutivos").is(':checked') ? '1' : '0',
        vendedores_ventas: $("input[type='checkbox']#vendedores_ventas").is(':checked') ? '1' : '',
        recordar_ultimo_precio_venta: $("input[type='checkbox']#recordar_ultimo_precio_venta").is(':checked') ? '1' : '',
        precio_ponderado: $("input[type='checkbox']#precio_ponderado").is(':checked') ? '1' : '',
        ubicacion_maximoph: $("input[type='checkbox']#ubicacion_maximoph").is(':checked') ? '1' : '',
        salario_minimo: stringToNumberFloat($("#salario_minimo").val()),
        subsidio_transporte: stringToNumberFloat($("#subsidio_transporte").val()),
        cuenta_x_pagar_empleados: $("#cuenta_x_pagar_empleados").val(),
        no_exonerado_parafiscales: $("input[type='checkbox']#no_exonerado_parafiscales").is(':checked') ? '1' : '',
        cuenta_contable_pago_nomina: $("#cuenta_contable_pago_nomina").val(),
        cuenta_bancaria_nomina: $("#cuenta_bancaria_nomina").val(),
        tipo_cuenta_banco: $("#tipo_cuenta_banco").val(),
        id_comprobante_nomina: $("#id_comprobante_nomina").val(),
        id_comprobante_parafiscales: $("#id_comprobante_parafiscales").val(),
        id_comprobante_seguridad_social: $("#id_comprobante_seguridad_social").val(),
        id_comprobante_prestaciones_sociales: $("#id_comprobante_prestaciones_sociales").val(),
        encabezado_ventas_regimen: $('#encabezado_ventas_regimen').val(),
        id_cliente_venta_defecto: $('#id_cliente_venta_defecto').val(),
    };

    $.ajax({
        url: base_url + 'entorno',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json',
    }).done((res) => {
        if(res.success){
            $("#updateEntornoLoading").hide();
            $("#updateEntorno").show();

            agregarToast('exito', 'Actualización exitosa', 'Datos de entorno actualizados con exito!', true);
        }
    }).fail((err) => {
        $("#updateEntornoLoading").hide();
        $("#updateEntorno").show();

        var mensaje = err.responseJSON.message;
        var errorsMsg = arreglarMensajeError(mensaje);
        agregarToast('error', 'Actualización errada', errorsMsg);
    });
});

$(document).on('click', '#notificaciones-tab', function () {
    if (tokenEco) {
        $("#div-canales-eco").show();
        $("#div-token-eco").hide();
        validarNotificaciones();
    } else {
        $("#div-canales-eco").hide();
        $("#div-token-eco").show();
    }
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
