var familias_table = null;

// CUENTAS (globales por compatibilidad con el resto del código)
var $comboCuentaVenta = null;
var $comboCuentaVentaRetencion = null;
var $comboCuentaVentaDevolucion = null;
var $comboCuentaVentaIva = null;
var $comboCuentaVentaImpuestos = null;
var $comboCuentaVentaDescuento = null;
var $comboCuentaVentaDevolucionIva = null;

var $comboCuentaCompra = null;
var $comboCuentaCompraRetencion = null;
var $comboCuentaCompraRetencionNoDeclarante = null;   // NUEVO
var $comboCuentaCompraDevolucion = null;
var $comboCuentaCompraIva = null;
var $comboCuentaCompraImpuestos = null;
var $comboCuentaCompraDescuento = null;
var $comboCuentaCompraDevolucionIva = null;

var $comboCuentaInventario = null;
var $comboCuentaCostos = null;

// === CONFIG CENTRAL DE COMBOS ===
// selector : input HTML
// field    : campo del JSON
// clear    : si se limpia al resetear el form (respeta comportamiento original)
var combosFamiliasConfig = {
    cuentaVenta:              { selector: '#id_cuenta_venta',                 field: 'cuenta_venta',                clear: true  },
    cuentaVentaRetencion:     { selector: '#id_cuenta_venta_retencion',       field: 'cuenta_venta_retencion',      clear: true  },
    cuentaVentaDevolucion:    { selector: '#id_cuenta_venta_devolucion',      field: 'cuenta_venta_devolucion',     clear: true  },
    cuentaVentaIva:           { selector: '#id_cuenta_venta_iva',             field: 'cuenta_venta_iva',            clear: true  },
    cuentaVentaImpuestos:     { selector: '#id_cuenta_impuestos_venta',       field: 'cuenta_venta_impuestos',      clear: false },
    cuentaVentaDescuento:     { selector: '#id_cuenta_venta_descuento',       field: 'cuenta_venta_descuento',      clear: true  },
    cuentaVentaDevolucionIva: { selector: '#id_cuenta_venta_devolucion_iva',  field: 'cuenta_venta_devolucion_iva', clear: true  },

    cuentaCompra:                    { selector: '#id_cuenta_compra',                        field: 'cuenta_compra',                        clear: true  },
    cuentaCompraRetencion:           { selector: '#id_cuenta_compra_retencion',              field: 'cuenta_compra_retencion',              clear: true  },
    // NUEVO: retención no declarante (mismo comportamiento que retención)
    cuentaCompraRetencionNoDeclarante: { selector: '#id_cuenta_compra_retencion_nodeclarante', field: 'cuenta_compra_retencion_nodeclarante', clear: true },
    cuentaCompraDevolucion:          { selector: '#id_cuenta_compra_devolucion',             field: 'cuenta_compra_devolucion',             clear: true  },
    cuentaCompraIva:                 { selector: '#id_cuenta_compra_iva',                    field: 'cuenta_compra_iva',                    clear: true  },
    cuentaCompraImpuestos:           { selector: '#id_cuenta_impuestos_compra',              field: 'cuenta_compra_impuestos',              clear: false },
    // NOTA: los selectores de estos dos están cruzados igual que en el código original.
    cuentaCompraDescuento:           { selector: '#id_cuenta_compra_devolucion_iva',        field: 'cuenta_compra_descuento',              clear: true  },
    cuentaCompraDevolucionIva:       { selector: '#id_cuenta_compra_descuento',             field: 'cuenta_compra_devolucion_iva',         clear: true  },

    cuentaInventario: { selector: '#id_cuenta_inventario', field: 'cuenta_inventario', clear: true },
    cuentaCostos:     { selector: '#id_cuenta_costos',     field: 'cuenta_costos',     clear: true }
};

// === INICIALIZACIÓN ===
function familiasInit() {

    familias_table = $('#famimliaTable').DataTable({
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
        sScrollX: '100%',
        scroller: {
            displayBuffer: 20,
            rowHeight: 50,
            loadingIndicator: true
        },
        deferRender: true,
        fixedHeader: {
            header: true,
            footer: true,
            headerOffset: 45
        },
        fixedColumns: {
            left: 0,
            right: 1
        },
        ajax: {
            type: 'GET',
            headers: headers,
            url: base_url + 'familia'
        },
        columns: [
            { data: 'codigo' },
            { data: 'nombre' },
            cuentaCol('cuenta_venta'),
            cuentaCol('cuenta_venta_retencion'),
            cuentaCol('cuenta_venta_devolucion'),
            cuentaCol('cuenta_venta_iva'),
            cuentaCol('cuenta_venta_impuestos'),
            cuentaCol('cuenta_venta_descuento'),
            cuentaCol('cuenta_venta_devolucion_iva'),
            cuentaCol('cuenta_inventario'),
            cuentaCol('cuenta_costos'),
            cuentaCol('cuenta_compra'),
            cuentaCol('cuenta_compra_retencion'),
            cuentaCol('cuenta_compra_retencion_nodeclarante'),   // NUEVO
            cuentaCol('cuenta_compra_devolucion'),
            cuentaCol('cuenta_compra_iva'),
            cuentaCol('cuenta_compra_impuestos'),
            cuentaCol('cuenta_compra_descuento'),
            cuentaCol('cuenta_compra_devolucion_iva'),
            userCol('created_by', 'fecha_creacion'),
            userCol('updated_by', 'fecha_edicion'),
            {
                data: function (row) {
                    var html = '';
                    if (editarFamilias)   html += '<span id="editfamilias_' + row.id + '" href="javascript:void(0)" class="btn badge bg-gradient-success edit-familias" style="margin-bottom: 0rem !important">Editar</span>&nbsp;';
                    if (eliminarFamilias) html += '<span id="deletefamilias_' + row.id + '" href="javascript:void(0)" class="btn badge bg-gradient-danger drop-familias" style="margin-bottom: 0rem !important">Eliminar</span>&nbsp';
                    if (crearFamilias)    html += '<span id="duplicatefamilias_' + row.id + '" href="javascript:void(0)" class="btn badge bg-gradient-info duplicar-familias" style="margin-bottom: 0rem !important">Duplicar</span>';
                    return html;
                }
            }
        ]
    });

    // FIX: antes era column(18) que apuntaba a created_by (bug).
    // Con -1 apuntamos a la ÚLTIMA columna (Acciones), que es lo que se pretendía.
    familias_table.column(-1).visible(editarFamilias || eliminarFamilias);

    if (familias_table) {
        // EDITAR
        familias_table.on('click', '.edit-familias', function () {
            clearFormFamilias();
            $('#textFamiliaCreate').hide();
            $('#textFamiliaUpdate').show();
            $('#textFamiliaDuplicate').hide();
            $('#saveFamiliaLoading').hide();
            $('#updateFamilia').show();
            $('#saveFamilia').hide();

            var id = this.id.split('_')[1];
            var data = getDataById(id, familias_table);

            fillCombosFromData(data);
            $('#inventario_familia').prop('checked', !!data.inventario);
            showInventario(!!data.inventario);

            $('#codigo_familia').val(data.codigo);
            $('#nombre_familia').val(data.nombre);
            $('#id_familia_up').val(data.id);
            $('#familiaFormModal').modal('show');

            setTimeout(function () {
                $('#codigo_familia').focus().select();
            }, 50);
        });

        familias_table.on('dblclick', 'tr', function () {
            var data = familias_table.row(this).data();
            if (data) document.getElementById('editfamilias_' + data.id).click();
        });

        // ELIMINAR
        familias_table.on('click', '.drop-familias', function () {
            var trPlanCuenta = $(this).closest('tr');
            var id = this.id.split('_')[1];
            var data = getDataById(id, familias_table);

            Swal.fire({
                title: 'Eliminar familia: ' + data.nombre + '?',
                text: 'No se podrá revertir!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Borrar!',
                reverseButtons: true
            }).then(function (result) {
                if (!result.value) return;
                $.ajax({
                    url: base_url + 'familia',
                    method: 'DELETE',
                    data: JSON.stringify({ id: id }),
                    headers: headers,
                    dataType: 'json'
                }).done(function (res) {
                    if (res.success) {
                        familias_table.row(trPlanCuenta).remove().draw();
                        agregarToast('exito', 'Eliminación exitosa', 'Familia eliminada con exito!', true);
                    } else {
                        agregarToast('error', 'Eliminación errada', res.message);
                    }
                }).fail(function (res) {
                    agregarToast('error', 'Eliminación errada', res.message);
                });
            });
        });

        // DUPLICAR
        familias_table.on('click', '.duplicar-familias', function () {
            clearFormFamilias();
            $('#textFamiliaCreate').hide();
            $('#textFamiliaUpdate').hide();
            $('#textFamiliaDuplicate').show();
            $('#saveFamiliaLoading').hide();
            $('#updateFamilia').hide();
            $('#saveFamilia').show();

            var id = this.id.split('_')[1];
            var data = getDataById(id, familias_table);

            fillCombosFromData(data);
            $('#inventario_familia').prop('checked', !!data.inventario);
            showInventario(!!data.inventario);

            $('#codigo_familia').val('');
            $('#nombre_familia').val('');
            $('#id_familia_up').val('');
            $('#familiaFormModal').modal('show');

            document.getElementById('familiaFormModal').click();

            setTimeout(function () {
                $('#codigo_familia').focus().select();
            }, 20);
        });
    }

    $('#codigo_familia').on('keydown', function (event) {
        if (event.keyCode === 13) {
            event.preventDefault();
            setTimeout(function () {
                $('#nombre_familia').focus().select();
            }, 10);
        }
    });

    initCombosFamilias();
    showInventario(false);

    $('.water').hide();
    familias_table.ajax.reload();
}

// === HELPERS ===

function initCuentaSelect2(selector) {
    return $(selector).select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#familiaFormModal'),
        delay: 250,
        placeholder: 'Seleccione una cuenta',
        allowClear: true,
        ajax: {
            url: 'api/plan-cuenta/combo-cuenta',
            headers: headers,
            dataType: 'json',
            data: function (params) {
                return { search: params.term };
            },
            processResults: function (data) {
                return { results: data.data };
            }
        }
    });
}

function initCombosFamilias() {
    Object.values(combosFamiliasConfig).forEach(function (c) {
        c.$ = initCuentaSelect2(c.selector);
    });

    // Asignación a globales (retro-compatibilidad)
    $comboCuentaVenta                       = combosFamiliasConfig.cuentaVenta.$;
    $comboCuentaVentaRetencion              = combosFamiliasConfig.cuentaVentaRetencion.$;
    $comboCuentaVentaDevolucion             = combosFamiliasConfig.cuentaVentaDevolucion.$;
    $comboCuentaVentaIva                    = combosFamiliasConfig.cuentaVentaIva.$;
    $comboCuentaVentaImpuestos              = combosFamiliasConfig.cuentaVentaImpuestos.$;
    $comboCuentaVentaDescuento              = combosFamiliasConfig.cuentaVentaDescuento.$;
    $comboCuentaVentaDevolucionIva          = combosFamiliasConfig.cuentaVentaDevolucionIva.$;
    $comboCuentaCompra                      = combosFamiliasConfig.cuentaCompra.$;
    $comboCuentaCompraRetencion             = combosFamiliasConfig.cuentaCompraRetencion.$;
    $comboCuentaCompraRetencionNoDeclarante = combosFamiliasConfig.cuentaCompraRetencionNoDeclarante.$;
    $comboCuentaCompraDevolucion            = combosFamiliasConfig.cuentaCompraDevolucion.$;
    $comboCuentaCompraIva                   = combosFamiliasConfig.cuentaCompraIva.$;
    $comboCuentaCompraImpuestos             = combosFamiliasConfig.cuentaCompraImpuestos.$;
    $comboCuentaCompraDescuento             = combosFamiliasConfig.cuentaCompraDescuento.$;
    $comboCuentaCompraDevolucionIva         = combosFamiliasConfig.cuentaCompraDevolucionIva.$;
    $comboCuentaInventario                  = combosFamiliasConfig.cuentaInventario.$;
    $comboCuentaCostos                      = combosFamiliasConfig.cuentaCostos.$;
}

// Carga las cuentas de un row (editar/duplicar) en sus combos
function fillCombosFromData(data) {
    Object.values(combosFamiliasConfig).forEach(function (c) {
        var cuenta = data[c.field];
        if (!cuenta) return;
        var option = new Option(cuenta.cuenta + ' - ' + cuenta.nombre, cuenta.id, false, false);
        c.$.append(option).val(cuenta.id).trigger('change');
    });
}

// Columna DataTable para un campo de cuenta
function cuentaCol(field) {
    return {
        data: function (row) {
            return getCuentaDataFormatFamilias(row[field]);
        }
    };
}

// Columna DataTable para usuario + fecha
function userCol(userField, dateField) {
    return {
        data: function (row) {
            if (!row[userField] && !row[dateField]) return '';
            if (!row[userField]) return '<div class=""><i class="fas fa-user-times icon-user-none"></i>' + row[dateField] + '</div>';
            return '<div class="button-user" onclick="showUser(' + row[userField] + ',`' + row[dateField] + '`,0)"><i class="fas fa-user icon-user"></i>&nbsp;' + row[dateField] + '</div>';
        }
    };
}

// Payload del formulario
function getFamiliaFormData() {
    return {
        codigo: $('#codigo_familia').val(),
        nombre: $('#nombre_familia').val(),
        inventario: $('#inventario_familia').is(':checked') ? '1' : '',
        id_cuenta_venta: $('#id_cuenta_venta').val(),
        id_cuenta_venta_retencion: $('#id_cuenta_venta_retencion').val(),
        id_cuenta_venta_devolucion: $('#id_cuenta_venta_devolucion').val(),
        id_cuenta_venta_iva: $('#id_cuenta_venta_iva').val(),
        id_cuenta_venta_impuestos: $('#id_cuenta_impuestos_venta').val(),
        id_cuenta_venta_descuento: $('#id_cuenta_venta_descuento').val(),
        id_cuenta_venta_devolucion_iva: $('#id_cuenta_venta_devolucion_iva').val(),
        id_cuenta_inventario: $('#id_cuenta_inventario').val(),
        id_cuenta_costos: $('#id_cuenta_costos').val(),
        id_cuenta_compra: $('#id_cuenta_compra').val(),
        id_cuenta_compra_retencion: $('#id_cuenta_compra_retencion').val(),
        id_cuenta_compra_retencion_nodeclarante: $('#id_cuenta_compra_retencion_nodeclarante').val(),   // NUEVO
        id_cuenta_compra_devolucion: $('#id_cuenta_compra_devolucion').val(),
        id_cuenta_compra_iva: $('#id_cuenta_compra_iva').val(),
        id_cuenta_compra_impuestos: $('#id_cuenta_impuestos_compra').val(),
        id_cuenta_compra_descuento: $('#id_cuenta_compra_descuento').val(),
        id_cuenta_compra_devolucion_iva: $('#id_cuenta_compra_devolucion_iva').val()
    };
}

// Manejo centralizado de errores AJAX
function showFamiliaAjaxError(err, btnToShow, title) {
    $(btnToShow).show();
    $('#saveFamiliaLoading').hide();

    var errorsMsg = '';
    var mensaje = err.responseJSON.message;

    if (typeof mensaje === 'object' || Array.isArray(mensaje)) {
        for (var field in mensaje) {
            var errores = mensaje[field];
            for (var campo in errores) {
                errorsMsg += '- ' + errores[campo] + ' <br>';
            }
        }
    } else {
        errorsMsg = mensaje;
    }

    agregarToast('error', title, errorsMsg);
}

function getCuentaDataFormatFamilias(cuenta) {
    if (!cuenta) return '';
    var result = '<b style="font-weight: 600;">' + cuenta.cuenta + '</b> - ' + cuenta.nombre;
    if (cuenta.impuesto) {
        var base = new Intl.NumberFormat('ja-JP').format(cuenta.impuesto.base);
        result += ' - <b style="font-weight: 600;">PORCENTAJE:</b> ' + cuenta.impuesto.porcentaje +
                  ' - <b style="font-weight: 600;">BASE:</b> ' + base;
    }
    return result;
}

$('.form-control').keyup(function () {
    $(this).val($(this).val().toUpperCase());
});

$('#searchInput').on('input', function () {
    familias_table.context[0].jqXHR.abort();
    $('#cecosTable').DataTable().search($('#searchInput').val()).draw();
});

$(document).on('click', '#createFamilia', function () {
    clearFormFamilias();
    showInventario(false);
    $('#updateFamilia').hide();
    $('#saveFamilia').show();
    $('#inventario_familia').prop('checked', false);
    $('#familiaFormModal').modal('show');

    setTimeout(function () {
        $('#codigo_familia').focus().select();
    }, 10);
});

function clearFormFamilias() {
    $('#textFamiliaCreate').show();
    $('#textFamiliaUpdate').hide();
    $('#textFamiliaDuplicate').hide();

    $('#saveFamiliaLoading').hide();
    $('#id_familia_up').val('');
    $('#codigo_familia').val('');
    $('#nombre_familia').val('');

    // Limpieza centralizada (respeta flag 'clear' para no alterar comportamiento original)
    Object.values(combosFamiliasConfig).forEach(function (c) {
        if (c.clear) c.$.val('').trigger('change');
    });
}

$(document).on('click', '#saveFamilia', function () {
    var form = document.querySelector('#familiasForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    $('#saveFamiliaLoading').show();
    $('#updateFamilia').hide();
    $('#saveFamilia').hide();

    var data = getFamiliaFormData();

    $.ajax({
        url: base_url + 'familia',
        method: 'POST',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json'
    }).done(function (res) {
        if (res.success) {
            clearFormFamilias();
            $('#saveFamilia').show();
            $('#updateFamilia').hide();
            $('#saveFamiliaLoading').hide();
            $('#familiaFormModal').modal('hide');
            familias_table.row.add(res.data).draw();
            agregarToast('exito', 'Creación exitosa', 'Familia creada con exito!', true);
        }
    }).fail(function (err) {
        showFamiliaAjaxError(err, '#saveFamilia', 'Creación errada');
    });
});

$(document).on('click', '#updateFamilia', function () {
    var form = document.querySelector('#familiasForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }

    var data = getFamiliaFormData();
    data.id = $('#id_familia_up').val();

    $.ajax({
        url: base_url + 'familia',
        method: 'PUT',
        data: JSON.stringify(data),
        headers: headers,
        dataType: 'json'
    }).done(function (res) {
        if (res.success) {
            clearFormFamilias();
            $('#saveFamilia').show();
            $('#updateFamilia').hide();
            $('#saveFamiliaLoading').hide();
            $('#familiaFormModal').modal('hide');
            familias_table.row.add(res.data).draw();
            agregarToast('exito', 'Actualización exitosa', 'Familia actualizada con exito!', true);
        }
    }).fail(function (err) {
        showFamiliaAjaxError(err, '#updateFamilia', 'Error al actualizar familia');
    });
});

$('input[type=checkbox][name=inventario_familia]').change(function () {
    showInventario($('#inventario_familia').is(':checked'));
});

function showInventario(show) {
    show = !!show;
    if (show) {
        $('#input-familia-inventario').show();
        $('#input-familia-costos').show();
        $('#inputs-familias-compras').show();
    } else {
        $('#input-familia-inventario').hide();
        $('#input-familia-costos').hide();
        $('#inputs-familias-compras').hide();
        $('#id_cuenta_inventario').val('').trigger('change');
        $('#id_cuenta_costos').val('').trigger('change');
        $('#id_cuenta_compra').val('').trigger('change');
        $('#id_cuenta_compra_retencion').val('').trigger('change');
        $('#id_cuenta_compra_retencion_nodeclarante').val('').trigger('change');   // NUEVO
        $('#id_cuenta_compra_devolucion').val('').trigger('change');
        $('#id_cuenta_compra_iva').val('').trigger('change');
        $('#id_cuenta_compra_descuento').val('').trigger('change');
        $('#id_cuenta_compra_devolucion_iva').val('').trigger('change');
    }
}