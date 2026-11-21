/**
 * notacredito.js  (versión refactorizada - CORREGIDA)
 *
 * Convive con ventas.js en la misma página (sistema de pestañas).
 * - IIFE con 'use strict': NO expone globales accidentales.
 * - Namespace de eventos: .notacredito (no pisa .venta).
 * - Estado en memoria (state.lineas) como fuente de verdad.
 * - Cálculos en funciones puras (calcularTotalesNotaCredito).
 * - Eventos delegados, registrados una sola vez.
 * - Datos escapados antes de insertar en HTML.
 *
 * IMPORTANTE: el payload de getProductosNotaCredito() y el ajax de
 * nota-credito-movimiento conservan EXACTAMENTE el formato original
 * que espera el backend (total_devolucion, arrays crudos).
 */
// (function (window, $) {
//     'use strict';

    // ------------------------------------------------------------------
    // Constantes
    // ------------------------------------------------------------------
    const LOCALE_NUM = 'en-US';

    const fmt2 = new Intl.NumberFormat(LOCALE_NUM, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const fmt0 = new Intl.NumberFormat(LOCALE_NUM, { maximumFractionDigits: 0 });

    // ------------------------------------------------------------------
    // Estado (encapsulado; NO contamina window)
    // ------------------------------------------------------------------
    const state = {
        inited: false,
        guardando: false,
        idNotaCreditoFactura: null,
        totalReteFuente: 0,
        totalFacturaNotaCredito: 0,
        idempotencyKey: null,
        lineas: new Map(),          // id_factura_detalle -> línea
        nextOrden: 0,
        tabla: null,
        tablaPagos: null,
        tablaFacturas: null,
        tablaMovimiento: null,
        $comboBodega: null,
        $comboCliente: null,
        $comboResolucion: null,
        ultimosTotales: null
    };

    // ------------------------------------------------------------------
    // Utilidades
    // ------------------------------------------------------------------
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    const num = (v) => {
        const n = parseFloat(String(v ?? '').replace(/,/g, ''));
        return Number.isFinite(n) ? n : 0;
    };

    const round2 = (n) => Math.round((n + Number.EPSILON) * 100) / 100;

    function debounce(fn, ms) {
        let t = null;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), ms);
        };
    }

    function mostrarError(err, titulo = 'Error') {
        if (err && err.statusText === 'abort') return;
        const mensaje = (err && err.responseJSON && err.responseJSON.message) ||
            (err && err.message) ||
            (err && err.statusText) ||
            'Error de conexión';
        agregarToast('error', titulo, arreglarMensajeError(mensaje));
    }

    function api(url, method = 'GET', data = null) {
        return $.ajax({ url: base_url + url, method, data, headers, dataType: 'json' });
    }

    // ------------------------------------------------------------------
    // Cálculo (funciones puras)
    // ------------------------------------------------------------------
    function nuevaLinea(detalle) {
        return {
            id_factura_detalle: detalle.id,
            id_producto: detalle.id_producto,
            producto: detalle.producto,
            descripcion: detalle.descripcion,
            costo: num(detalle.costo),
            // --- originales de la factura (inmutables) ---
            cantidad_original: num(detalle.cantidad),
            cantidad_devuelta_previa: num(detalle.cantidad_devuelta),
            subtotal_original: num(detalle.subtotal),
            total_original: num(detalle.total),
            total_devuelto_previo: num(detalle.total_devuelto),
            descuento_valor_original: num(detalle.descuento_valor),
            descuento_porcentaje: num(detalle.descuento_porcentaje),
            iva_porcentaje: num(detalle.iva_porcentaje),
            iva_valor_original: num(detalle.iva_valor),
            // --- valores de la devolución actual ---
            cantidad_devuelta: 0,
            total_devolucion: 0,
            devolucion_total: 0,
            total_disponible: 0,
            observacion: ''
        };
    }

    function calcularLineaPorCantidad(l) {
        const cantOrig = l.cantidad_original;
        if (!cantOrig) { l.cantidad_devuelta = 0; l.total_devolucion = 0; return l; }

        const dispCant = Math.max(0, cantOrig - l.cantidad_devuelta_previa);
        l.cantidad_devuelta = Math.min(Math.max(l.cantidad_devuelta, 0), dispCant);

        const proporcion = dispCant > 0 ? l.cantidad_devuelta / dispCant : 0;

        const descuentoDisp = l.descuento_valor_original * (1 - l.total_devuelto_previo / (l.total_original || 1));
        const ivaDisp = l.iva_valor_original * (1 - l.total_devuelto_previo / (l.total_original || 1));
        const totalDisp = Math.max(0, l.total_original - l.total_devuelto_previo);

        const descuento = descuentoDisp * proporcion;
        const iva = ivaDisp * proporcion;
        const subtotalBase = totalDisp * proporcion;

        l.total_devolucion = round2(subtotalBase);
        l.devolucion_total = l.total_devolucion;
        l.total_disponible = round2(totalDisp - subtotalBase);

        l._descuento_calculado = round2(descuento);
        l._iva_calculado = round2(iva);

        return l;
    }

    function calcularLineaPorTotal(l) {
        const totalDisp = Math.max(0, l.total_original - l.total_devuelto_previo);
        l.total_devolucion = Math.min(Math.max(l.total_devolucion, 0), totalDisp);
        l.devolucion_total = l.total_devolucion;
        l.total_disponible = round2(totalDisp - l.total_devolucion);

        const proporcion = l.total_original > 0 ? l.total_devolucion / l.total_original : 0;
        l._descuento_calculado = round2(l.descuento_valor_original * proporcion);
        l._iva_calculado = round2(l.iva_valor_original * proporcion);

        if (l.cantidad_original > 0) {
            l.cantidad_devuelta = Math.min(
                Math.round(l.cantidad_original * proporcion),
                Math.max(0, l.cantidad_original - l.cantidad_devuelta_previa)
            );
        }
        return l;
    }

    function calcularTotalesNotaCredito() {
        let iva = 0, descuento = 0, total = 0, productos = 0, valorBruto = 0, retencion = 0;

        state.lineas.forEach((l) => {
            if (!l.total_devolucion) return;
            const proporcion = l.total_original > 0 ? l.total_devolucion / l.total_original : 0;

            productos += l.cantidad_devuelta;
            total += l.total_devolucion;
            valorBruto += l.total_devolucion;

            if (l.descuento_porcentaje > 0) {
                descuento += l.descuento_valor_original * proporcion;
                valorBruto -= l.descuento_valor_original * proporcion;
            }
            if (l.iva_porcentaje > 0) {
                iva += l.iva_valor_original * proporcion;
                if (ivaIncluidoNotaCredito) {
                    valorBruto -= l.iva_valor_original * proporcion;
                }
            }
            if (state.totalReteFuente) {
                retencion += state.totalReteFuente * proporcion;
                total -= state.totalReteFuente * proporcion;
            }
        });

        return {
            iva: round2(iva),
            retencion: round2(retencion),
            descuento: round2(descuento),
            total: round2(total),
            valorBruto: round2(valorBruto),
            productos: round2(productos)
        };
    }

    // ------------------------------------------------------------------
    // Pagos (lectura)
    // ------------------------------------------------------------------
    function filasPagos() {
        return state.tablaPagos ? state.tablaPagos.rows().data().toArray() : [];
    }

    function totalFormasPagoNotasCredito(exceptoId = null) {
        let total = 0;
        filasPagos().forEach((fp) => {
            if (exceptoId && exceptoId == fp.id) return;
            total += num($('#nota_credito_forma_pago_' + fp.id).val());
        });
        return total;
    }

    // ------------------------------------------------------------------
    // Render
    // ------------------------------------------------------------------
    let countUpTotalNC = null;
    let ultimoTotalAnimadoNC = null;

    function animarTotalNotaCredito(valor) {
        valor = round2(valor);
        if (valor === ultimoTotalAnimadoNC) return;
        ultimoTotalAnimadoNC = valor;

        if (typeof CountUp === 'undefined') {
            const el = document.getElementById('nota_credito_total_valor');
            if (el) el.textContent = fmt2.format(valor);
            return;
        }
        if (!countUpTotalNC) {
            countUpTotalNC = new CountUp('nota_credito_total_valor', 0, valor, 2, 0.2);
            countUpTotalNC.start();
        } else {
            countUpTotalNC.update(valor);
        }
    }

    function mostrarValoresNotaCredito() {
        const t = calcularTotalesNotaCredito();
        state.ultimosTotales = t;

        $('#totales_descuento_nota_credito').toggle(!!t.descuento);
        $('#totales_retencion_nota_credito').toggle(!!t.retencion);
        $('#totales_productos_nota_credito').toggle(!!t.productos);
        $('#movimientoContableNotaCredito').toggle(t.total > 0);

        $('#nota_credito_total_iva').text(fmt2.format(t.iva));
        $('#nota_credito_total_descuento').text(fmt2.format(t.descuento));
        $('#nota_credito_total_productos').text(fmt2.format(t.productos));
        $('#nota_credito_total_retencion').text(fmt2.format(t.retencion));
        $('#nota_credito_sub_total').text(fmt2.format(t.valorBruto));

        animarTotalNotaCredito(t.total);

        // Botón guardar
        const totalPagos = totalFormasPagoNotasCredito();
        const listo = t.total > 0 && round2(totalPagos) >= round2(t.total);

        if (state.guardando) {
            $('#crearCapturaNotaCredito').hide();
            $('#crearCapturaNotaCreditoDisabled').show();
        } else if (listo) {
            $('#crearCapturaNotaCredito').show();
            $('#crearCapturaNotaCreditoDisabled').hide();
        } else {
            $('#crearCapturaNotaCredito').hide();
            $('#crearCapturaNotaCreditoDisabled').show();
        }

        // Faltante de pagos (ÚNICO punto que escribe este campo)
        const faltante = Math.max(0, t.total - totalPagos);
        $('#total_pagado_nota_credito').text(fmt2.format(totalPagos));
        $('#total_faltante_nota_credito').text(fmt2.format(faltante));
        $('#total_faltante_nota_credito').removeClass('is-invalid');
    }

    // ------------------------------------------------------------------
    // Tabla de productos (notaCreditoTable)
    // ------------------------------------------------------------------
    function renderCantidad(row) {
        const l = state.lineas.get(row.id_factura_detalle);
        if (!l) return '';
        const disp = l.cantidad_original - l.cantidad_devuelta_previa;
        if (disp <= 0) {
            return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_cantidad_${l.id_factura_detalle}" value="0" disabled>`;
        }
        return `<input type="number" class="form-control form-control-sm v-nc-cantidad" data-id="${l.id_factura_detalle}" style="min-width: 30px; text-align: right;" id="nota_credito_cantidad_${l.id_factura_detalle}" value="${l.cantidad_devuelta}" min="0" step="any">`;
    }

    function renderTotal(row) {
        const l = state.lineas.get(row.id_factura_detalle);
        if (!l) return '';
        const disp = l.total_original - l.total_devuelto_previo;
        if (disp <= 0) {
            return `<input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="nota_credito_total_${l.id_factura_detalle}" value="0" disabled>`;
        }
        return `<input type="number" class="form-control form-control-sm v-nc-total" data-id="${l.id_factura_detalle}" style="min-width: 30px; text-align: right;" id="nota_credito_total_${l.id_factura_detalle}" value="${l.total_devolucion}" min="0" step="any">`;
    }

    function renderObservacion(row) {
        const l = state.lineas.get(row.id_factura_detalle);
        if (!l) return '';
        const disp = (l.cantidad_original - l.cantidad_devuelta_previa) || (l.total_original - l.total_devuelto_previo);
        const disabled = disp > 0 ? '' : 'disabled';
        return `<input type="text" class="form-control form-control-sm v-nc-observacion" data-id="${l.id_factura_detalle}" style="min-width: 200px; text-align: right;" id="nota_credito_observacion_${l.id_factura_detalle}" value="${esc(l.observacion)}" ${disabled}>`;
    }

    function initTablaProductosNotaCredito() {
        state.tabla = $('#notaCreditoTable').DataTable({
            dom: '',
            paging: false,
            responsive: false,
            processing: false,
            serverSide: false,
            deferLoading: 0,
            initialLoad: false,
            autoWidth: true,
            language: lenguajeDatatable,
            ordering: false,
            columns: [
                { data: 'descripcion' },
                { data: 'cantidad_original', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
                { data: null, render: (row) => renderCantidad(row) },
                { data: 'costo', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
                {
                    data: null,
                    render: (row) => `
                        <div class="form-group mb-3" style="min-width: 80px;">
                            <div class="input-group input-group-sm" style="height: 18px; min-width: 100px;">
                                <span class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${parseInt(row.descuento_porcentaje)}%</span>
                                <input style="height: 30px; text-align: right;" type="text" class="form-control form-control-sm" value="${parseInt(row.descuento_valor_original)}" disabled>
                            </div>
                        </div>`
                },
                {
                    data: null,
                    render: (row) => `
                        <div class="form-group mb-3" style="min-width: 80px;">
                            <div class="input-group input-group-sm" style="height: 18px; min-width: 100px;">
                                <span class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">${parseInt(row.iva_porcentaje)}%</span>
                                <input style="height: 30px; text-align: right;" type="text" class="form-control form-control-sm" value="${parseInt(row.iva_valor_original)}" disabled>
                            </div>
                        </div>`
                },
                { data: 'total_disponible', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
                { data: null, render: (row) => renderObservacion(row) },
                { data: null, render: (row) => renderTotal(row) }
            ],
            fixedColumns: { left: 0, right: 1 },
            columnDefs: [{ orderable: false, targets: '_all' }]
        });
    }

    function refrescarFilaNotaCredito(idDetalle) {
        const l = state.lineas.get(idDetalle);
        if (!l || !state.tabla) return;

        const idx = state.tabla.rows().data().toArray()
            .findIndex((r) => r.id_factura_detalle == idDetalle);
        if (idx >= 0) {
            state.tabla.row(idx).data(l).invalidate().draw(false);
        }
        mostrarValoresNotaCredito();
    }

    // ------------------------------------------------------------------
    // Tabla de pagos
    // ------------------------------------------------------------------
    function initTablaPagosNotaCredito() {
        state.tablaPagos = $('#notaCreditoFormaPago').DataTable({
            dom: '',
            paging: false,
            responsive: false,
            processing: true,
            serverSide: true,
            deferLoading: 0,
            initialLoad: false,
            language: lenguajeDatatable,
            sScrollX: '100%',
            scrollX: true,
            ordering: false,
            ajax: {
                type: 'GET',
                headers,
                data: { type: 'ventas' },
                url: base_url + 'forma-pago/combo-forma-pago'
            },
            columns: [
                { data: 'nombre' },
                {
                    data: null,
                    render: (row) => `<input type="number" class="form-control form-control-sm v-nc-pago" style="text-align: right; font-size: larger;" data-id="${row.id}" id="nota_credito_forma_pago_${row.id}" value="0" min="0" step="any">`
                }
            ]
        });
    }

    function loadFormasPagoNotasCredito() {
        if (!state.tablaPagos) return;
        state.tablaPagos.ajax.reload(() => mostrarValoresNotaCredito());
    }

    function clearFormasPagoNotaCredito() {
        $('#notaCreditoFormaPago .v-nc-pago').val(0);
        mostrarValoresNotaCredito();
    }

    // ------------------------------------------------------------------
    // Tabla de facturas (devoluciones)
    // ------------------------------------------------------------------
    function initTablaFacturasNotaCredito() {
        state.tablaFacturas = $('#facturaDevolucionTable').DataTable({
            dom: '',
            pageLength: 200,
            responsive: false,
            processing: true,
            serverSide: false,
            deferLoading: 0,
            initialLoad: false,
            autoWidth: true,
            language: lenguajeDatatable,
            ordering: false,
            ajax: {
                type: 'GET',
                url: base_url + 'facturas',
                headers,
                data: (d) => {
                    d.id_cliente = $('#id_cliente_nota_credito').val();
                    d.id_bodega = $('#id_bodega_nota_credito').val();
                    d.consecutivo = $('#searchConsecutiveNC').val();
                }
            },
            columns: [
                { data: 'cliente.nombre_completo', defaultContent: '' },
                { data: 'consecutivo' },
                { data: 'total_factura', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
                { data: 'centro_costo', defaultContent: '', render: (row) => row ? (row.codigo + ' - ' + row.nombre) : '' },
                { data: 'bodega', defaultContent: '', render: (row) => row ? (row.codigo + ' - ' + row.nombre) : '' },
                { data: 'fecha_manual' },
                {
                    data: null,
                    render: (row) => `<span class="btn badge bg-gradient-primary v-nc-select-factura" data-id="${row.id}" style="margin-bottom: 0rem !important">Seleccionar</span>`
                }
            ]
        });
    }

    // ------------------------------------------------------------------
    // Payload para backend (FORMATO ORIGINAL — NO TOCAR)
    // ------------------------------------------------------------------
    function getProductosNotaCredito() {
        const data = [];
        state.lineas.forEach((l) => {
            if (!l.cantidad_devuelta && !l.total_devolucion) return;
            data.push({
                id_producto: l.id_producto,
                id_factura_detalle: l.id_factura_detalle,
                cantidad: l.cantidad_devuelta,
                total_devolucion: l.total_devolucion
            });
        });
        return data;
    }

    function getNotasCreditoPagos() {
        const data = [];
        filasPagos().forEach((fp) => {
            const valor = num($('#nota_credito_forma_pago_' + fp.id).val());
            if (valor > 0) data.push({ id: fp.id, valor });
        });
        return data;
    }

    // ------------------------------------------------------------------
    // Tabla de movimiento contable
    // ------------------------------------------------------------------
    function initTablaMovimientoNotaCredito() {
        state.tablaMovimiento = $('#notasCreditoMovimientoTable').DataTable({
            pageLength: -1,
            deferRender: true,
            deferLoading: true,
            dom: 'Brtip',
            paging: false,
            responsive: false,
            processing: true,
            serverSide: false,
            fixedHeader: true,
            language: Object.assign({}, lenguajeDatatable, { info: '', infoEmpty: '', infoFiltered: '' }),
            ordering: false,
            scrollX: true,
            scrollCollapse: true,
            sScrollX: '100%',
            autoWidth: false,
            ajax: {
                type: 'GET',
                headers,
                url: base_url + 'nota-credito-movimiento',
                data: (d) => {
                    d.id_factura = state.idNotaCreditoFactura;
                    d.id_cliente = $('#id_cliente_nota_credito').val();
                    d.documento_referencia = $('#consecutivo_nota_credito').val();
                    // FORMATO ORIGINAL: arrays crudos (NO JSON.stringify)
                    d.pagos = getNotasCreditoPagos();
                    d.productos = getProductosNotaCredito();
                }
            },
            rowCallback: (row, data) => {
                if (data.id_cuenta === 'TOTALES') {
                    $('td', row).css({ 'background-color': '#000', 'font-weight': 'bold', color: 'white' });
                }
            },
            columns: [
                { data: null, render: (row) => row.id_cuenta === 'TOTALES' ? 'TOTALES' : (row.cuenta ? row.cuenta.cuenta : '') },
                { data: null, render: (row) => row.cuenta ? row.cuenta.nombre : '' },
                { data: 'debito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
                { data: 'credito', render: $.fn.dataTable.render.number(',', '.', 2, ''), className: 'dt-body-right' },
                {
                    data: 'concepto',
                    render: (row) => {
                        if (row.id_cuenta === 'TOTALES') {
                            return num(row.concepto).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                        }
                        return row.concepto;
                    }
                }
            ]
        });

        state.tablaMovimiento.on('error.dt', (e, settings, techNote, message) => {
            const xhr = settings.jqXHR;
            if (xhr && xhr.responseJSON) {
                agregarToast('error', 'Error al cargar movimiento', arreglarMensajeError(xhr.responseJSON.message));
            }
        });
    }

    // ------------------------------------------------------------------
    // Seleccionar factura para devolución
    // ------------------------------------------------------------------
    function seleccionarFacturaNotaCredito(idFactura) {
        const dataFactura = getDataById(idFactura, state.tablaFacturas);
        if (!dataFactura) return;

        state.idNotaCreditoFactura = dataFactura.id;
        state.totalReteFuente = num(dataFactura.total_rete_fuente);
        state.totalFacturaNotaCredito = num(dataFactura.total_factura);

        $('#iniciarCapturaNotaCredito').hide();
        $('#iniciarCapturaNotaCreditoLoading').show();

        $('#modalFacturasDevolucion').modal('hide');
        $('#texto-nota-credito').html(`Datos de factura devoluciones <span class="text-success">${esc(dataFactura.documento_referencia)}</span>`);

        if (dataFactura.cliente) {
            setComboValue(state.$comboCliente, dataFactura.cliente.id, dataFactura.cliente.nombre_completo);
        }
        if (dataFactura.bodega) {
            setComboValue(state.$comboBodega, dataFactura.bodega.id, dataFactura.bodega.nombre);
        }

        state.$comboCliente.prop('disabled', true);
        state.$comboBodega.prop('disabled', true);

        state.lineas.clear();
        state.tabla.rows().remove().draw();

        const form = document.querySelector('#notaCreditoFilterForm');
        if (form && !form.checkValidity()) form.classList.add('was-validated');

        api('nota-credito/factura-detalle', 'GET', { id: dataFactura.id })
            .done((res) => {
                if (!res.success) return;
                $('#iniciarCapturaNotaCreditoLoading').hide();
                $('#cancelarCapturaNotaCredito').show();

                (res.data || []).forEach((detalle) => {
                    const l = nuevaLinea(detalle);
                    state.lineas.set(l.id_factura_detalle, l);
                    state.tabla.row.add(l).draw(false);
                });
                mostrarValoresNotaCredito();
            })
            .fail((err) => {
                $('#iniciarCapturaNotaCreditoLoading').hide();
                $('#crearCapturaNotaCreditoDisabled').hide();
                $('#iniciarCapturaNotaCredito').show();
                mostrarError(err, 'Creación errada');
            });
    }

    // ------------------------------------------------------------------
    // Combos
    // ------------------------------------------------------------------
    function crearComboNotaCredito(selector, url, ajaxExtra = {}) {
        return $(selector).select2({
            theme: 'bootstrap-5',
            delay: 250,
            language: {
                noResults: () => 'No hay resultado',
                searching: () => 'Buscando..',
                inputTooShort: () => 'Por favor introduce 1 o más caracteres'
            },
            ajax: Object.assign({
                url,
                headers,
                dataType: 'json',
                processResults: (data) => ({ results: data.data })
            }, ajaxExtra)
        });
    }

    function setComboValue($combo, id, text) {
        const option = new Option(text, id, false, false);
        $combo.append(option).val(id).trigger('change');
    }

    function cargarCombosNotasCredito() {
        state.$comboCliente = crearComboNotaCredito('#id_cliente_nota_credito', 'api/nit/combo-nit');
        state.$comboResolucion = crearComboNotaCredito('#id_resolucion_nota_credito', 'api/resoluciones/combo-resoluciones', {
            data: (params) => ({ q: params.term, tipo_resoluciones: [3], _type: 'query' })
        });
        state.$comboBodega = crearComboNotaCredito('#id_bodega_nota_credito', 'api/bodega/combo-bodega');

        if (primeraResolucionNotaCredito && primeraResolucionNotaCredito.length > 0) {
            const r = primeraResolucionNotaCredito[0];
            setComboValue(state.$comboResolucion, r.id, r.prefijo + ' - ' + r.nombre);
            consecutivoSiguienteNotaCredito();
        }
    }

    // ------------------------------------------------------------------
    // Consecutivo
    // ------------------------------------------------------------------
    function consecutivoSiguienteNotaCredito() {
        const idResolucion = $('#id_resolucion_nota_credito').val();
        const fechaManual = $('#fecha_manual_nota_credito').val();
        if (!idResolucion || !fechaManual) return;

        $('#consecutivo_nota_credito_loading').show();
        api('consecutivo', 'GET', { id_resolucion: idResolucion, fecha_manual: fechaManual })
            .done((res) => {
                $('#consecutivo_nota_credito_loading').hide();
                if (res.success) $('#consecutivo_nota_credito').val(res.data);
            })
            .fail((err) => {
                $('#consecutivo_nota_credito_loading').hide();
                mostrarError(err, 'Creación errada');
            });
    }

    // ------------------------------------------------------------------
    // Cálculos desde el DOM
    // ------------------------------------------------------------------
    function recalcularPorCantidad(idDetalle) {
        const l = state.lineas.get(idDetalle);
        if (!l) return;
        l.cantidad_devuelta = num($('#nota_credito_cantidad_' + idDetalle).val());
        calcularLineaPorCantidad(l);
        refrescarFilaNotaCredito(idDetalle);
        clearFormasPagoNotaCredito();
    }

    function recalcularPorTotal(idDetalle) {
        const l = state.lineas.get(idDetalle);
        if (!l) return;
        l.total_devolucion = num($('#nota_credito_total_' + idDetalle).val());
        calcularLineaPorTotal(l);
        refrescarFilaNotaCredito(idDetalle);
        clearFormasPagoNotaCredito();
    }

    // ------------------------------------------------------------------
    // Formas de pago: foco / commit
    // ------------------------------------------------------------------
    function focusFormaPagoNotaCredito(idFormaPago) {
        const t = state.ultimosTotales || calcularTotalesNotaCredito();
        const totalPagos = totalFormasPagoNotasCredito(idFormaPago);
        const totalFactura = Math.max(0, t.total - totalPagos);
        const $in = $('#nota_credito_forma_pago_' + idFormaPago);
        $in.val(totalFactura);
        $in.select();
    }

    function calcularNotaCreditoPagos(idFormaPago = null) {
        const $in = idFormaPago ? $('#nota_credito_forma_pago_' + idFormaPago) : null;
        if ($in && (num($in.val()) < 0 || $in.val() === '')) $in.val(0);

        const t = state.ultimosTotales || calcularTotalesNotaCredito();
        let totalPagos = totalFormasPagoNotasCredito();

        if (idFormaPago && totalPagos > t.total) {
            const sinActual = totalFormasPagoNotasCredito(idFormaPago);
            $('#nota_credito_forma_pago_' + idFormaPago).val(Math.max(0, t.total - sinActual));
            totalPagos = totalFormasPagoNotasCredito();
        }

        const faltante = Math.max(0, t.total - totalPagos);
        $('#total_pagado_nota_credito').text(fmt2.format(totalPagos));
        $('#total_faltante_nota_credito').text(fmt2.format(faltante));
        $('#total_faltante_nota_credito').removeClass('is-invalid');

        mostrarValoresNotaCredito();
    }

    function focusNextFormasPagoNotasCredito(idActual) {
        const filas = filasPagos();
        if (!filas.length) return;

        let idFocus = filas[0].id;
        let tomarSiguiente = false;
        for (const fp of filas) {
            if (tomarSiguiente) { idFocus = fp.id; tomarSiguiente = false; }
            else if (fp.id == idActual) { tomarSiguiente = true; }
        }
        focusFormaPagoNotaCredito(idFocus);
    }

    // ------------------------------------------------------------------
    // Guardar
    // ------------------------------------------------------------------
    function validateSaveNotaCredito() {
        $('#total_faltante_nota_credito_text, #total_faltante_nota_credito').css('color', '#484848');
        if (state.guardando) return;

        const t = calcularTotalesNotaCredito();
        const totalPagos = totalFormasPagoNotasCredito();

        if (t.total > 0 && round2(totalPagos) >= round2(t.total)) {
            state.guardando = true;
            saveNotaCredito();
        } else {
            $('#total_faltante_nota_credito_text, #total_faltante_nota_credito').css('color', 'red');
        }
    }

    function saveNotaCredito() {
        $('#iniciarCapturaNotaCredito').hide();
        $('#iniciarCapturaNotaCreditoLoading').hide();
        $('#crearCapturaNotaCreditoDisabled').hide();
        $('#crearCapturaNotaCredito').hide();
        $('#cancelarCapturaNotaCredito').hide();
        $('#movimientoContableNotaCredito').hide();
        $('#crearCapturaNotaCreditoLoading').show();

        if (!state.idempotencyKey) {
            state.idempotencyKey = (window.crypto && crypto.randomUUID)
                ? crypto.randomUUID()
                : String(Date.now()) + Math.random().toString(16).slice(2);
        }

        const data = {
            id_factura: state.idNotaCreditoFactura,
            pagos: getNotasCreditoPagos(),
            productos: getProductosNotaCredito(),
            id_resolucion: $('#id_resolucion_nota_credito').val(),
            fecha_manual: $('#fecha_manual_nota_credito').val(),
            documento_referencia: $('#consecutivo_nota_credito').val(),
            idempotency_key: state.idempotencyKey
        };

        $('#notaCreditoFormaPago .v-nc-pago').prop('disabled', true);

        $.ajax({
            url: base_url + 'nota-credito',
            method: 'POST',
            data: JSON.stringify(data),
            headers,
            dataType: 'json'
        }).done((res) => {
            state.guardando = false;
            $('#crearCapturaNotaCreditoLoading').hide();

            if (!res.success) {
                mostrarError({ responseJSON: { message: res.message } }, 'Creación errada');
                $('#notaCreditoFormaPago .v-nc-pago').prop('disabled', false);
                mostrarValoresNotaCredito();
                return;
            }

            state.idempotencyKey = null;
            agregarToast('exito', 'Creación exitosa', 'Nota Crédito creada con éxito!', true);
            cancelarNotaCredito();
        }).fail((err) => {
            state.guardando = false;
            $('#crearCapturaNotaCreditoLoading').hide();
            $('#notaCreditoFormaPago .v-nc-pago').prop('disabled', false);
            mostrarValoresNotaCredito();
            mostrarError(err, 'Creación errada');
        });
    }

    // ------------------------------------------------------------------
    // Cancelar
    // ------------------------------------------------------------------
    function cancelarNotaCredito() {
        state.idNotaCreditoFactura = null;
        state.idempotencyKey = null;
        state.totalReteFuente = 0;
        state.totalFacturaNotaCredito = 0;
        state.lineas.clear();

        if (state.tabla) state.tabla.clear().draw();
        clearFormasPagoNotaCredito();

        if (state.$comboBodega) state.$comboBodega.val('').trigger('change');
        if (state.$comboCliente) state.$comboCliente.val('').trigger('change');
        state.$comboBodega.prop('disabled', false);
        state.$comboCliente.prop('disabled', false);

        $('#consecutivo_nota_credito').val('');
        $('#texto-nota-credito').html('Datos de factura devoluciones');

        $('#iniciarCapturaNotaCredito').show();
        $('#crearCapturaNotaCredito').hide();
        $('#cancelarCapturaNotaCredito').hide();
        $('#crearCapturaNotaCreditoLoading').hide();
        $('#crearCapturaNotaCreditoDisabled').hide();
        $('#iniciarCapturaNotaCreditoLoading').hide();

        mostrarValoresNotaCredito();
        consecutivoSiguienteNotaCredito();
    }

    // ------------------------------------------------------------------
    // Eventos (delegados, namespace .notacredito)
    // ------------------------------------------------------------------
    function registrarEventosNotaCredito() {
        if (state.inited) return;
        state.inited = true;

        const $doc = $(document);

        // Cantidad
        $doc.on('focusout.notacredito', '#notaCreditoTable .v-nc-cantidad', function () {
            recalcularPorCantidad(Number(this.dataset.id));
        });
        $doc.on('keydown.notacredito', '#notaCreditoTable .v-nc-cantidad', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            recalcularPorCantidad(Number(this.dataset.id));
        });

        // Total
        $doc.on('keydown.notacredito', '#notaCreditoTable .v-nc-total', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            recalcularPorTotal(Number(this.dataset.id));
        });

        // Observación
        $doc.on('input.notacredito', '#notaCreditoTable .v-nc-observacion', function () {
            const l = state.lineas.get(Number(this.dataset.id));
            if (l) l.observacion = $(this).val() || '';
        });

        // Formas de pago
        $doc.on('focusin.notacredito', '#notaCreditoFormaPago .v-nc-pago', function () {
            focusFormaPagoNotaCredito(Number(this.dataset.id));
        });
        $doc.on('focusout.notacredito', '#notaCreditoFormaPago .v-nc-pago', function () {
            calcularNotaCreditoPagos(Number(this.dataset.id));
        });
        $doc.on('keydown.notacredito', '#notaCreditoFormaPago .v-nc-pago', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const id = Number(this.dataset.id);
            calcularNotaCreditoPagos(id);

            const t = state.ultimosTotales || calcularTotalesNotaCredito();
            if (!t.total) return;
            if (totalFormasPagoNotasCredito() >= t.total) {
                validateSaveNotaCredito();
                return;
            }
            focusNextFormasPagoNotasCredito(id);
        });

        // Seleccionar factura
        $doc.on('click.notacredito', '.v-nc-select-factura', function () {
            seleccionarFacturaNotaCredito(Number(this.dataset.id));
        });

        // Consecutivo al cambiar resolución
        $doc.on('change.notacredito', '#id_resolucion_nota_credito', consecutivoSiguienteNotaCredito);

        // Enter en buscador de facturas
        $doc.on('keydown.notacredito', '#searchConsecutiveNC', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            if (state.tablaFacturas) state.tablaFacturas.ajax.reload();
            $('#modalFacturasDevolucion').modal('show');
        });

        // Botones
        $doc.on('click.notacredito', '#iniciarCapturaNotaCredito', iniciarNotaCredito);
        $doc.on('click.notacredito', '#crearCapturaNotaCredito', validateSaveNotaCredito);
        $doc.on('click.notacredito', '#cancelarCapturaNotaCredito', cancelarNotaCredito);
    }

    function iniciarNotaCredito() {
        if (state.tablaFacturas) state.tablaFacturas.ajax.reload();
        $('#modalFacturasDevolucion').modal('show');
    }

    // ------------------------------------------------------------------
    // Init
    // ------------------------------------------------------------------
    function resetStateNotaCredito() {
        try { $(document).off('.notacredito'); } catch (e) { /* noop */ }

        [state.tabla, state.tablaPagos, state.tablaFacturas, state.tablaMovimiento].forEach((dt) => {
            if (dt) { try { dt.destroy(); } catch (e) { /* noop */ } }
        });
        state.tabla = null;
        state.tablaPagos = null;
        state.tablaFacturas = null;
        state.tablaMovimiento = null;

        ['#id_cliente_nota_credito', '#id_resolucion_nota_credito', '#id_bodega_nota_credito'].forEach((sel) => {
            const $el = $(sel);
            if ($el.data('select2')) { try { $el.select2('destroy'); } catch (e) { /* noop */ } }
            try { $el.empty(); } catch (e) { /* noop */ }
        });

        state.guardando = false;
        state.idNotaCreditoFactura = null;
        state.totalReteFuente = 0;
        state.totalFacturaNotaCredito = 0;
        state.idempotencyKey = null;
        state.lineas = new Map();
        state.ultimosTotales = null;
        state.inited = false;

        countUpTotalNC = null;
        ultimoTotalAnimadoNC = null;

        $('#iniciarCapturaNotaCredito').show();
        $('#crearCapturaNotaCredito').hide();
        $('#cancelarCapturaNotaCredito').hide();
        $('#crearCapturaNotaCreditoLoading').hide();
        $('#crearCapturaNotaCreditoDisabled').hide();
        $('#iniciarCapturaNotaCreditoLoading').hide();
    }

    function notacreditoInit() {
        resetStateNotaCredito();

        const hoy = new Date();
        const p = (n) => String(n).padStart(2, '0');
        $('#fecha_manual_nota_credito').val(`${hoy.getFullYear()}-${p(hoy.getMonth() + 1)}-${p(hoy.getDate())}`);

        initTablaProductosNotaCredito();
        initTablaPagosNotaCredito();
        initTablaFacturasNotaCredito();
        initTablaMovimientoNotaCredito();

        cargarCombosNotasCredito();
        loadFormasPagoNotasCredito();
        registrarEventosNotaCredito();

        mostrarValoresNotaCredito();
    }

    // ------------------------------------------------------------------
    // API pública
    // ------------------------------------------------------------------
    Object.assign(window, {
        notacreditoInit,
        cancelarNotaCredito,
        consecutivoSiguienteNotaCredito,
        loadFormasPagoNotasCredito,
        clearFormasPagoNotaCredito
    });

// })(window, jQuery);