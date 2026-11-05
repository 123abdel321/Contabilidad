/**
 * ventas.js  (versión refactorizada)
 *
 * Cambios principales respecto a la versión anterior:
 *  - Todo encapsulado en un IIFE ('use strict'); solo se exponen las funciones que el HTML necesita.
 *  - Estado en memoria (state.lineas) como fuente de verdad: el DOM solo muestra.
 *  - Cálculos en funciones puras (calcularLinea / calcularTotales); un único refrescar() pinta.
 *  - Grilla de productos sin DataTables (tbody simple): agregar filas es instantáneo.
 *  - Eventos delegados (sin onclick/onchange inline) y registrados una sola vez.
 *  - Datos escapados antes de insertarlos en HTML.
 *  - Sin CountUp en cada cálculo, sin popovers recreados en cada tecla.
 *  - Bugs corregidos: .lengt, descuento por %, retenciones duplicadas, pagos bloqueados tras error,
 *    responseJSON undefined, globales accidentales, HTML mal formado, etc.
 *
 * Dependencias globales que se asumen (las mismas del archivo original):
 *  headers, base_url, bucketUrl, lenguajeDatatable, multiplesPrecios, ventaExistencias, ventaDescuento,
 *  ventaFecha, ventaNegativa, ventaRapida, vendedoresVentas, ivaIncluidoVentas, primeraResolucionVenta,
 *  primeraBodegaVenta, primeraNit, cuentaPropina, porcentajePropina, valor_uvt, getResponsabilidades,
 *  formatCurrency, formatCurrencyValue, stringToNumberFloat, arreglarMensajeError, agregarToast,
 *  abrirNitGeneral, abrirNitDetalle, Swal, select2, DataTables (solo para formas de pago), popover.
 *
 *  Opcional: window.redondearFactura (si otro script lo activa).
 */
(function (window, $) {
    'use strict';

    // ------------------------------------------------------------------
    // Constantes
    // ------------------------------------------------------------------
    const ID_FORMA_PAGO_EFECTIVO = 1;
    const ID_TIPO_CUENTA_ANTICIPO = 8;
    // El código original redondeaba a 10 pesos (toFixed(2) * 1000 sobre total/1000).
    // Cambia a 100 o 1000 si tu regla real es otra.
    const REDONDEO_PASO = 10;
    const LOCALE_NUM = 'en-US'; // mismo estilo de miles/decimales que CountUp y ja-JP

    const fmt2 = new Intl.NumberFormat(LOCALE_NUM, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const fmt0 = new Intl.NumberFormat(LOCALE_NUM, { maximumFractionDigits: 0 });

    const LANG = {
        noResults: () => 'No hay resultado',
        searching: () => 'Buscando..',
        inputTooShort: () => 'Por favor introduce 1 o más caracteres'
    };

    // ------------------------------------------------------------------
    // Estado
    // ------------------------------------------------------------------
    const state = {
        nextId: 0,
        lineas: new Map(),            // id -> línea
        capturando: false,
        guardando: false,
        abrirFormasPago: false,
        createNewNit: false,
        calculaRetencion: false,
        responsabilidades: [],
        anticiposTotal: 0,
        anticiposPorCuenta: {},       // idCuenta -> saldo
        ultimosTotales: null,
        idempotencyKey: null,
        reqExistencias: null,
        inited: false,
        $cliente: null,
        $vendedor: null,
        $resolucion: null,
        $bodega: null,
        tablaPagos: null
    };

    // ------------------------------------------------------------------
    // Utilidades
    // ------------------------------------------------------------------
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    const num = (v) => {
        const n = stringToNumberFloat(v);
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

    function setText(id, valor) {
        const el = document.getElementById(id);
        if (!el) return;
        if (Math.abs(valor) < 0.005) valor = 0;
        el.textContent = fmt2.format(valor);
    }

    let countUpTotal = null;
    let ultimoTotalAnimado = null;

    /** Anima solo el total grande. La instancia se crea una vez y luego se actualiza desde el valor actual. */
    function animarTotal(valor) {
        valor = round2(valor);
        if (valor === ultimoTotalAnimado) return; // sin cambios, no relanza animación
        ultimoTotalAnimado = valor;

        // Si CountUp no está cargado, escribe directo
        if (typeof CountUp === 'undefined') {
            setText('venta_total_valor', valor);
            return;
        }

        if (!countUpTotal) {
            countUpTotal = new CountUp('venta_total_valor', 0, valor, 2, 0.2);
            countUpTotal.start();
        } else {
            countUpTotal.update(valor); // anima desde el valor actual, no desde 0
        }
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

    function seleccionarConDelay(el) {
        setTimeout(() => { try { el.select(); } catch (e) { /* noop */ } }, 80);
    }

    function scrollGrillaIzquierda() {
        const card = document.getElementById('card-venta');
        if (card) card.scrollLeft = 0;
    }

    function inyectarEstilos() {
        if (document.getElementById('venta-estilos')) return;
        const style = document.createElement('style');
        style.id = 'venta-estilos';
        style.textContent = `
            #ventaTable.v-sin-existencias th:nth-child(3), #ventaTable.v-sin-existencias td:nth-child(3),
            #ventaTable.v-sin-descuento th:nth-child(6), #ventaTable.v-sin-descuento td:nth-child(6),
            #ventaTable.v-sin-descuento th:nth-child(7), #ventaTable.v-sin-descuento td:nth-child(7) { display: none; }
        `;
        document.head.appendChild(style);
    }

    function aplicarVisibilidadColumnas() {
        const $t = $('#ventaTable');
        $t.toggleClass('v-sin-existencias', !ventaExistencias);
        $t.toggleClass('v-sin-descuento', !ventaDescuento);
    }

    function getTbody() {
        let $tb = $('#ventaTable tbody');
        if (!$tb.length) $tb = $('<tbody></tbody>').appendTo('#ventaTable');
        return $tb;
    }

    // ------------------------------------------------------------------
    // Cálculo (funciones puras)
    // ------------------------------------------------------------------
    function nuevaLinea(id) {
        return {
            id, idProducto: null, producto: null,
            cantidad: 1, costo: 0, descPct: 0, descVal: 0,
            ivaPct: 0, ivaVal: 0, total: 0, concepto: '',
            retencion: null
        };
    }

    /** origen: 'pct' si el usuario editó el %, cualquier otro valor => manda el valor. */
    function calcularLinea(l, origen) {
        const bruto = l.cantidad > 0 ? l.cantidad * l.costo : 0;

        if (bruto > 0) {
            if (origen === 'pct') {
                l.descPct = Math.min(Math.max(l.descPct, 0), 100);
                l.descVal = round2(bruto * l.descPct / 100);
            } else {
                l.descVal = Math.min(Math.max(l.descVal, 0), bruto);
                l.descPct = l.descVal > 0 ? round2(l.descVal / bruto * 100) : 0;
            }
        } else {
            l.descPct = 0;
            l.descVal = 0;
        }

        const base = bruto - l.descVal;
        let iva = 0;
        if (l.ivaPct > 0) {
            iva = ivaIncluidoVentas
                ? base * (l.ivaPct / (l.ivaPct + 100))
                : base * l.ivaPct / 100;
        }
        l.ivaVal = iva;
        l.total = round2(ivaIncluidoVentas ? base : base + iva);
        return l;
    }

    function retencionesActivas() {
        const mapa = new Map();
        state.lineas.forEach((l) => {
            if (l.idProducto && l.retencion && !mapa.has(l.retencion.id_retencion)) {
                mapa.set(l.retencion.id_retencion, l.retencion);
            }
        });
        return Array.from(mapa.values());
    }

    function obtenerDatosRetencion(valorSubtotal) {
        let porcentaje = 0;
        let base = 0;
        retencionesActivas().forEach((r) => {
            if (r.base <= valorSubtotal && r.base > base && r.porcentaje > porcentaje) {
                porcentaje = r.porcentaje;
                base = r.base;
            }
        });
        return [base, porcentaje];
    }

    function calcularRetencion(neto, total) {
        if (!state.calculaRetencion) return 0;
        const [base, porcentaje] = obtenerDatosRetencion(neto);
        if (porcentaje && total >= base) return neto * (porcentaje / 100);
        return 0;
    }

    function calcularTotales() {
        let bruto = 0, iva = 0, descuento = 0;

        state.lineas.forEach((l) => {
            if (!l.idProducto) return;
            bruto += l.cantidad * l.costo - l.descVal;
            iva += l.ivaVal;
            descuento += l.descVal;
        });

        const neto = ivaIncluidoVentas ? bruto - iva : bruto;
        let total = neto + iva;
        const retencion = calcularRetencion(neto, total);
        total -= retencion;

        let redondeo = 0;
        if (window.redondearFactura) {
            const redondeado = Math.round(total / REDONDEO_PASO) * REDONDEO_PASO;
            redondeo = redondeado - total;
            total = redondeado;
        }

        return { iva, retencion, descuento, total: round2(total), valorBruto: neto, redondeo };
    }

    // ------------------------------------------------------------------
    // Pagos (lectura)
    // ------------------------------------------------------------------
    function esAnticipoCuenta(row) {
        const tipos = (row && row.cuenta && row.cuenta.tipos_cuenta) || [];
        return tipos.some((t) => t.id_tipo_cuenta == ID_TIPO_CUENTA_ANTICIPO);
    }

    function filasPagos() {
        return state.tablaPagos ? state.tablaPagos.rows().data().toArray() : [];
    }

    function totalFormasPago(exceptoId = null) {
        let efectivo = 0, otros = 0, anticipos = 0;
        filasPagos().forEach((fp) => {
            if (exceptoId && exceptoId == fp.id) return;
            const $in = $('#venta_forma_pago_' + fp.id);
            const valor = num($in.val());
            if (fp.id == ID_FORMA_PAGO_EFECTIVO) efectivo += valor;
            else if ($in.hasClass('anticipos')) anticipos += valor;
            else otros += valor;
        });
        return [efectivo, otros, anticipos];
    }

    function propinaActual() {
        return num($('#input_venta_propina').val());
    }

    // ------------------------------------------------------------------
    // Render
    // ------------------------------------------------------------------
    function refrescar() {
        if (state.guardando) return;

        const t = calcularTotales();
        state.ultimosTotales = t;
        const propina = propinaActual();
        const aPagar = t.total + propina;

        // Totales
        $('#totales_descuento').toggle(!!t.descuento);
        setText('venta_total_iva', t.iva);
        setText('venta_total_descuento', t.descuento);
        setText('venta_total_retencion', t.retencion);
        animarTotal(t.total);
        setText('venta_sub_total', t.valorBruto);

        // Propina
        if (cuentaPropina && porcentajePropina) {
            $('#total_con_propina').show();
            const sugerida = t.total * (porcentajePropina / 100);
            const inputProp = document.getElementById('input_venta_propina');
            if (inputProp) inputProp.placeholder = String(Math.round(sugerida));
            setText('venta_total_propina', t.total + (propina || sugerida));
        } else {
            $('#total_con_propina').hide();
        }

        // Pagos
        const [ef, otros, ant] = totalFormasPago();
        const pagado = ef + otros + ant;
        const faltante = Math.max(0, aPagar - pagado);
        setText('total_pagado_venta', Math.min(pagado, aPagar));
        setText('total_faltante_venta', faltante);
        $('#total_faltante_venta').removeClass('is-invalid');

        const cambio = pagado - aPagar;
        if (cambio >= 1) {
            $('#cambio-totals').show();
            setText('total_cambio_venta', cambio);
        } else {
            $('#cambio-totals').hide();
        }

        habilitarFormasPago(t.total > 0);

        // Botón guardar
        if (state.capturando) {
            const listo = t.total > 0 && round2(pagado) >= round2(aPagar);
            const faltaVendedor = vendedoresVentas && !$('#id_vendedor_venta').val();

            if (listo && !faltaVendedor) {
                $('#crearCapturaVenta').show();
                $('#crearCapturaVentaDisabled').hide();
                $('#id_vendedor_venta').removeClass('is-invalid').addClass('is-valid');
            } else {
                $('#crearCapturaVenta').hide();
                $('#crearCapturaVentaDisabled').show();
                if (listo && faltaVendedor) {
                    $('#id_vendedor_venta').addClass('is-invalid').removeClass('is-valid');
                }
            }
        }

        refrescarPopoverRetencion();
    }

    function habilitarFormasPago(hayTotal) {
        filasPagos().forEach((fp) => {
            const $in = $('#venta_forma_pago_' + fp.id);
            if (esAnticipoCuenta(fp)) {
                const saldo = state.anticiposPorCuenta[fp.cuenta.id] || 0;
                $in.prop('disabled', !(hayTotal && saldo > 0));
            } else {
                $in.prop('disabled', !hayTotal);
            }
        });
    }

    function actualizarInfoRetencionVentas() {
        const iconInfo = document.getElementById('icon_info_retencion_venta');
        if (!iconInfo) return;

        const t = state.ultimosTotales || calcularTotales();
        let porcentaje = 0, base = 0, totalUvt = 0;
        let nombre = 'Sin cuenta con retención';

        retencionesActivas().forEach((r) => {
            if (r.base > base && r.porcentaje > porcentaje) {
                porcentaje = r.porcentaje;
                base = r.base;
                totalUvt = r.total_uvt;
                nombre = `${r.cuenta} - ${r.nombre}`;
            }
        });

        $('#nombre_info_retencion_venta').html(`RETENCIÓN %${porcentaje}:`);

        const uvt = typeof valor_uvt !== 'undefined' ? valor_uvt : 0;
        const responsable = state.calculaRetencion
            ? `<b class='titulo-popover'>Con permiso:</b> Calcula retención en la fuente`
            : `<b class='titulo-popover'>Sin permiso:</b> 07 => No calcula retención en la fuente`;

        const titulo = `
            <b class='titulo-popover'>Cuenta:</b> ${esc(nombre)}<br/>
            <b class='titulo-popover'>UVT:</b> ${totalUvt} X ${fmt0.format(uvt)} = ${fmt0.format(base)}<br/>
            <b class='titulo-popover'>Subtotal:</b> ${fmt0.format(t.valorBruto)}<br/>
            ${responsable}
        `;
        recrearPopover(iconInfo, titulo);
    }

    const refrescarPopoverRetencion = debounce(actualizarInfoRetencionVentas, 250);

    function actualizarInfoPropinaVentas() {
        if (!cuentaPropina || !porcentajePropina) {
            $('#totales_propina').hide();
            $('#total_con_propina').hide();
            return;
        }
        $('#totales_propina').show();
        $('#total_con_propina').show();

        const iconInfo = document.getElementById('icon_info_propina_venta');
        $('#nombre_info_propina_venta').html(`PROPINA %${porcentajePropina}:`);
        if (!iconInfo) return;

        recrearPopover(iconInfo, `
            <b class='titulo-popover'>Porcentaje:</b> ${porcentajePropina}% <br/>
            <b class='titulo-popover'>Cuenta:</b> ${esc(cuentaPropina.cuenta)} - ${esc(cuentaPropina.nombre)}<br/>
        `);
    }

    function recrearPopover(el, titulo) {
        el.setAttribute('title', titulo);
        $(el).popover('dispose');
        $(el).popover({
            trigger: 'hover',
            html: true,
            placement: 'top',
            container: 'body',
            customClass: 'popover-formas-pagos'
        });
    }

    // ------------------------------------------------------------------
    // Grilla de productos
    // ------------------------------------------------------------------
    function rowHtml(id) {
        const celdaCosto = multiplesPrecios
            ? `
                <div class="input-group" style="width: 180px; height: 30px;">
                    <input type="text" data-type="currency" class="form-control form-control-sm v-costo" data-id="${id}" style="min-width: 100px; text-align: right; height: 30px;" id="venta_costo_${id}" value="0" disabled>
                    <div id="texto_extracto_${id}" class="valid-feedback info-factura">Nueva factura</div>
                    <div class="input-group-append button-group">
                        <span id="button_preciosventas_${id}" data-id="${id}" class="btn badge bg-gradient-light btn-group btn-precios-ventas" style="min-width: 30px; margin-right: 3px; border-radius: 0px 7px 7px 0px; height: 30px;">
                            <i class="fa-solid fa-money-bill" style="font-size: 17px; margin-top: 1px;"></i>
                        </span>
                    </div>
                </div>`
            : `<input type="text" data-type="currency" class="form-control form-control-sm v-costo" data-id="${id}" style="min-width: 100px; text-align: right;" id="venta_costo_${id}" value="0" disabled>`;

        return `
        <tr id="venta_row_${id}" data-id="${id}">
            <td><span class="btn badge bg-gradient-danger drop-row-grid v-borrar" data-id="${id}" id="delete-producto-venta_${id}"><i class="fas fa-trash-alt"></i></span></td>
            <td><select class="form-control form-control-sm venta_producto combo-grid" data-id="${id}" id="venta_producto_${id}" inert></select></td>
            <td><input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="venta_existencia_${id}" disabled></td>
            <td>
                <div class="input-group" style="height: 30px;">
                    <input type="number" class="form-control form-control-sm v-cantidad" data-id="${id}" style="min-width: 80px; border-right: solid 1px #b3b3b3; border-top-right-radius: 10px; border-bottom-right-radius: 10px; text-align: right;" id="venta_cantidad_${id}" min="0" step="any" value="1" disabled>
                    <i class="fa fa-spinner fa-spin fa-fw venta_producto_load" id="venta_producto_load_${id}" style="display: none;"></i>
                    <div id="venta_cantidad_text_${id}" style="position: absolute; margin-top: 30px; z-index: 9;" class="invalid-feedback"></div>
                </div>
            </td>
            <td>${celdaCosto}</td>
            <td><input type="text" data-type="currency" class="form-control form-control-sm v-dpct" data-id="${id}" style="min-width: 80px; text-align: right;" id="venta_descuento_porcentaje_${id}" value="0" disabled></td>
            <td><input type="text" data-type="currency" class="form-control form-control-sm v-dval" data-id="${id}" style="min-width: 100px; text-align: right;" id="venta_descuento_valor_${id}" value="0" disabled></td>
            <td>
                <div class="form-group mb-3" style="min-width: 85px;">
                    <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                        <span id="venta_iva_porcentaje_text_${id}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">0%</span>
                        <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" value="0" id="venta_iva_valor_${id}" disabled>
                    </div>
                </div>
                <input type="number" class="form-control form-control-sm" style="min-width: 110px; display: none;" id="venta_iva_porcentaje_${id}" value="0">
            </td>
            <td><input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="venta_total_${id}" value="0" disabled></td>
            <td><input type="text" class="form-control form-control-sm v-concepto" data-id="${id}" id="venta_concepto_${id}" onfocus="this.select();" style="width: 180px !important;" value="" disabled></td>
        </tr>`;
    }

    function formatProducto(producto) {
        if (producto.loading) return producto.text;

        const urlImagen = producto.imagen ? bucketUrl + producto.imagen : '/img/sin_imagen.png';
        const inventarios = producto.inventarios || [];
        const conInventario = !!(producto.familia && producto.familia.inventario) && ventaExistencias;

        let infoInventario = '';
        if (conInventario) {
            const hay = inventarios.length > 0;
            const cant = hay ? parseFloat(inventarios[0].cantidad) : 0;
            const texto = hay ? `${cant} Existencias` : 'Sin inventario';
            const color = hay ? (cant <= 0 ? '#a30000' : '#1c4587') : '#838383';
            infoInventario = `
                <div class="col-12" style="padding-left: 0px !important">
                    <i class="fas fa-box-open" style="font-size: 11px; color: ${color};"></i> ${texto}
                </div>`;
        }

        return $(`
            <div class="row">
                <div class="col-3" style="display: flex; justify-content: center; align-items: center;">
                    <img style="width: 40px; border-radius: 10%;" src="${esc(urlImagen)}" />
                </div>
                <div class="col-9" style="padding-left: 0px !important">
                    <div class="row" style="margin-left: 5px;">
                        <div class="col-12" style="padding-left: 0px !important">
                            <h6 style="font-size: 12px; margin-bottom: 0px; color: black;">${esc(producto.text)}</h6>
                        </div>
                        ${infoInventario}
                    </div>
                </div>
            </div>`);
    }

    function initProductoSelect($el) {
        $el.select2({
            theme: 'bootstrap-5',
            dropdownCssClass: 'custom-venta_producto',
            minimumInputLength: 2,
            language: Object.assign({}, LANG, {
                inputTooShort: () => 'Por favor introduce 2 o más caracteres'
            }),
            ajax: {
                url: 'api/producto/combo-producto',
                headers,
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    q: params.term,
                    id_bodega: $('#id_bodega_venta').val(),
                    id_cliente: $('#id_cliente_venta').val(),
                    captura: 'venta',
                    _type: 'query'
                }),
                processResults: (data) => ({ results: data.data })
            },
            templateResult: formatProducto,
            templateSelection: (p) => p.full_name || p.text
        });
    }

    function ultimaLinea() {
        let ultima = null;
        state.lineas.forEach((l) => { ultima = l; });
        return ultima;
    }

    function addRowProductoVenta() {
        const last = ultimaLinea();
        if (last && !last.idProducto) {
            $('#venta_producto_' + last.id).select2('open');
            scrollGrillaIzquierda();
            return;
        }

        const id = state.nextId++;
        state.lineas.set(id, nuevaLinea(id));
        getTbody().append(rowHtml(id));

        const $sel = $('#venta_producto_' + id);
        initProductoSelect($sel);
        scrollGrillaIzquierda();
        $sel.select2('open');
    }

    function deleteProductoVenta(id) {
        const $sel = $('#venta_producto_' + id);
        if ($sel.data('select2')) $sel.select2('destroy');
        $('#venta_row_' + id).remove();
        state.lineas.delete(id);
        refrescar();
    }

    function vaciarGrilla() {
        $('#ventaTable .venta_producto').each(function () {
            if ($(this).data('select2')) $(this).select2('destroy');
        });
        getTbody().empty();
        state.lineas.clear();
        state.nextId = 0;
    }

    // ------------------------------------------------------------------
    // Líneas: lectura / pintado / eventos
    // ------------------------------------------------------------------
    function leerLineaDom(l) {
        const id = l.id;
        l.cantidad = parseFloat($('#venta_cantidad_' + id).val()) || 0;
        l.costo = num($('#venta_costo_' + id).val());
        l.descPct = num($('#venta_descuento_porcentaje_' + id).val());
        l.descVal = num($('#venta_descuento_valor_' + id).val());
        l.concepto = $('#venta_concepto_' + id).val() || '';
    }

    function pintarLinea(l) {
        const id = l.id;
        $('#venta_descuento_porcentaje_' + id).val(formatCurrencyValue(l.descPct));
        $('#venta_descuento_valor_' + id).val(formatCurrencyValue(l.descVal));
        $('#venta_iva_porcentaje_' + id).val(l.ivaPct);
        $('#venta_iva_porcentaje_text_' + id).text(l.ivaPct + '%');
        $('#venta_iva_valor_' + id).val(formatCurrencyValue(l.ivaVal));
        $('#venta_total_' + id).val(formatCurrencyValue(l.total));
    }

    function recalcularLinea(id, origen) {
        const l = state.lineas.get(id);
        if (!l) return null;
        leerLineaDom(l);
        calcularLinea(l, origen);
        pintarLinea(l);
        refrescar();
        return l;
    }

    function onProductoChange(id) {
        const l = state.lineas.get(id);
        if (!l) return;

        const sel = $('#venta_producto_' + id).select2('data');
        const data = sel && sel[0];
        if (!data || !data.id) return;

        const familia = data.familia || {};
        const inv = (data.inventarios || [])[0];

        l.idProducto = data.id;
        l.producto = data;

        if (inv && familia.inventario) {
            const existencias = parseFloat(inv.cantidad);
            $('#venta_existencia_' + id).val(existencias);
            $('#venta_cantidad_' + id).attr('max', existencias);
        }

        const imp = familia.cuenta_venta_iva && familia.cuenta_venta_iva.impuesto;
        l.ivaPct = imp ? (parseFloat(imp.porcentaje) || 0) : 0;

        const cr = familia.cuenta_venta_retencion;
        l.retencion = (cr && cr.impuesto) ? {
            cuenta: cr.cuenta,
            nombre: cr.nombre,
            id_retencion: cr.impuesto.id,
            porcentaje: parseFloat(cr.impuesto.porcentaje),
            base: parseFloat(cr.impuesto.base),
            total_uvt: parseFloat(cr.impuesto.total_uvt)
        } : null;

        const permiteDescuento = !!(familia.id_cuenta_venta_descuento && ventaDescuento);
        $('#venta_descuento_valor_' + id).prop('disabled', !permiteDescuento);
        $('#venta_descuento_porcentaje_' + id).prop('disabled', !permiteDescuento);

        $('#venta_costo_' + id).val(formatCurrencyValue(data.precio));
        $('#venta_cantidad_' + id).prop('disabled', false);
        $('#venta_costo_' + id).prop('disabled', false);
        $('#venta_concepto_' + id).prop('disabled', false);

        vaciarPagos();
        recalcularLinea(id);

        setTimeout(() => {
            const $c = $('#venta_cantidad_' + id);
            $c.focus();
            $c.select();
        }, 10);
    }

    // ---- Existencias ----
    function stockProducto(l) {
        if (ventaNegativa) return Infinity;
        const p = l.producto;
        if (!p || !p.familia || !p.familia.inventario) return Infinity;
        const inv = (p.inventarios || [])[0];
        return inv ? parseFloat(inv.cantidad) : 0;
    }

    function cantidadEnOtrasLineas(l) {
        let total = 0;
        state.lineas.forEach((o) => {
            if (o.id !== l.id && o.idProducto && o.idProducto == l.idProducto) {
                total += parseFloat($('#venta_cantidad_' + o.id).val()) || 0;
            }
        });
        return total;
    }

    function marcarCantidadInvalida(l, disponible) {
        const id = l.id;
        $('#venta_cantidad_text_' + id).text(`Se ha superado las existencias (disponible: ${disponible})`);
        $('#venta_cantidad_' + id).addClass('is-invalid').removeClass('is-valid').val(disponible);
        recalcularLinea(id);
        setTimeout(() => {
            const $c = $('#venta_cantidad_' + id);
            $c.focus();
            $c.select();
        }, 10);
    }

    function validarExistenciaLocal(l) {
        const stock = stockProducto(l);
        if (!Number.isFinite(stock)) return true;

        const cantidad = parseFloat($('#venta_cantidad_' + l.id).val()) || 0;
        const disponible = Math.max(0, stock - cantidadEnOtrasLineas(l));

        if (cantidad > disponible) {
            marcarCantidadInvalida(l, disponible);
            return false;
        }
        $('#venta_cantidad_' + l.id).removeClass('is-invalid');
        return true;
    }

    /** Fallback: producto sin "familia" => se consulta al servidor. Devuelve Promise<boolean>. */
    function validarExistenciaServidor(l) {
        if (state.reqExistencias) state.reqExistencias.abort();
        const $load = $('#venta_producto_load_' + l.id);
        $load.show();

        const cantidad = (parseFloat($('#venta_cantidad_' + l.id).val()) || 0) + cantidadEnOtrasLineas(l);

        return new Promise((resolve) => {
            state.reqExistencias = api('existencias-producto', 'GET', {
                id_producto: l.idProducto,
                id_bodega: $('#id_bodega_venta').val()
            }).done((res) => {
                state.reqExistencias = null;
                $load.hide();
                if (res.data && cantidad > parseInt(res.data.cantidad, 10)) {
                    marcarCantidadInvalida(l, Math.max(0, parseInt(res.data.cantidad, 10) - cantidadEnOtrasLineas(l)));
                    resolve(false);
                } else {
                    $('#venta_cantidad_' + l.id).removeClass('is-invalid');
                    resolve(true);
                }
            }).fail((err) => {
                $load.hide();
                state.reqExistencias = null;
                mostrarError(err, 'Error consultando existencias');
                resolve(false);
            });
        });
    }

    function confirmarCantidad(id) {
        const l = recalcularLinea(id);
        if (!l || !l.idProducto) return;

        if (ventaNegativa) { addRowProductoVenta(); return; }

        if (l.producto && !l.producto.familia) {
            validarExistenciaServidor(l).then((ok) => { if (ok) addRowProductoVenta(); });
            return;
        }
        if (validarExistenciaLocal(l)) addRowProductoVenta();
    }

    function validarTodasLasExistencias() {
        let ok = true;
        state.lineas.forEach((l) => {
            if (ok && l.idProducto && !validarExistenciaLocal(l)) ok = false;
        });
        return ok;
    }

    // ------------------------------------------------------------------
    // Precios múltiples
    // ------------------------------------------------------------------
    function mostrarPreciosVentas(id) {
        const l = state.lineas.get(id);
        if (!l || !l.producto) return;

        const precios = [
            { nombre: 'Precio 1', valor: parseFloat(l.producto.precio || 0) },
            { nombre: 'Precio 2', valor: parseFloat(l.producto.precio_2 || 0) }
        ].filter((p) => p.valor > 0);

        if (!precios.length) {
            Swal.fire({
                title: 'Precios de venta',
                text: 'El producto no tiene precios de venta configurados.',
                icon: 'warning',
                confirmButtonText: 'Aceptar'
            });
            return;
        }

        const opciones = precios.map((p) => `
            <button type="button" class="btn btn-outline-primary btn-lg btn-precio"
                data-precio="${p.valor}" style="margin: 5px; min-width: 180px;">
                <strong>${p.nombre}</strong><br>$${formatCurrencyValue(p.valor)}
            </button>`).join('');

        Swal.fire({
            title: 'Seleccione el precio de venta',
            html: `<div style="display: flex; justify-content: center; flex-wrap: wrap;">${opciones}</div>`,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            didOpen: (popup) => {
                popup.querySelectorAll('.btn-precio').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        $('#venta_costo_' + id).val(formatCurrencyValue(parseFloat(btn.dataset.precio)));
                        Swal.close();
                        recalcularLinea(id);
                    });
                });
            }
        });
    }

    // ------------------------------------------------------------------
    // Formas de pago
    // ------------------------------------------------------------------
    function iniciarTablaPagos() {
        state.tablaPagos = $('#ventaFormaPago').DataTable({
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
                {
                    data: function (row) {
                        let styles = 'margin-bottom: 0px; font-size: 13px;';
                        let stylesInfo = '';
                        let naturaleza = 'Débito - Ventas';
                        if (row.cuenta.naturaleza_ventas) {
                            naturaleza = 'Error de naturaleza en ventas';
                            stylesInfo = 'border: solid 1px red !important; color: red !important;';
                        }
                        let titulo = 'Cuenta:';
                        if (esAnticipoCuenta(row)) {
                            styles += ' color: #0bb19e; font-weight: 600;';
                            titulo = 'Anticipos cuenta:';
                        }
                        const dataContent = `<b class='titulo-popover'>${titulo}</b> ${esc(naturaleza)}<br/> ${esc(row.cuenta.cuenta)} - ${esc(row.cuenta.nombre)}`;
                        return `<p style="${styles}">
                            <i class="fas fa-info icon-info" style="${stylesInfo}"
                               title="${esc(dataContent)}" data-toggle="popover" data-html="true"></i>
                            ${esc(row.nombre)}
                        </p>`;
                    }
                },
                {
                    data: function (row) {
                        const anticipo = esAnticipoCuenta(row);
                        return `<input type="text" data-type="currency"
                            class="form-control form-control-sm v-pago ${anticipo ? 'anticipos' : ''}"
                            style="text-align: right; font-size: larger;" value="0"
                            data-id="${row.id}" data-anticipo="${anticipo}" data-cuenta="${row.cuenta.id}"
                            id="venta_forma_pago_${row.id}">`;
                    }
                }
            ],
            initComplete: function () {
                $('#ventaFormaPago').on('draw.dt', function () {
                    $('#ventaFormaPago [data-toggle="popover"]').popover({
                        trigger: 'hover',
                        html: true,
                        placement: 'top',
                        container: 'body',
                        customClass: 'popover-formas-pagos'
                    });
                });
            }
        });
    }

    function loadFormasPagoVenta() {
        if (!state.tablaPagos) return;
        state.tablaPagos.ajax.reload(function () {
            refrescar();
        });
    }

    function vaciarPagos() {
        $('#ventaFormaPago .v-pago').val(0);
    }

    function clearFormasPagoVenta() {
        vaciarPagos();
        refrescar();
    }

    function metaPago($input) {
        return {
            id: Number($input.data('id')),
            anticipo: String($input.data('anticipo')) === 'true',
            idCuenta: Number($input.data('cuenta'))
        };
    }

    /** Máximo permitido para un anticipo: saldo de la cuenta menos otros anticipos, y no más de lo que falta. */
    function maxAnticipoPermitido(id, idCuenta) {
        const t = calcularTotales();
        const aPagar = t.total + propinaActual();
        const [ef, otros, ant] = totalFormasPago(id);
        const saldo = state.anticiposPorCuenta[idCuenta] || 0;
        const faltante = Math.max(0, aPagar - (ef + otros + ant));
        return Math.max(0, Math.min(saldo - ant, faltante));
    }

    function focusFormaPagoVenta(id, anticipo = false, idCuenta = null) {
        if (state.guardando) return;

        const t = calcularTotales();
        const aPagar = t.total + propinaActual();
        const [ef, otros, ant] = totalFormasPago(id);
        const faltante = Math.max(0, aPagar - (ef + otros + ant));
        
        const $in = $('#venta_forma_pago_' + id);

        if (anticipo) {
            $in.val(formatCurrencyValue(maxAnticipoPermitido(id, idCuenta)));
            $in.select();
            return;
        }

        if (!num($in.val())) $in.val(faltante);
        $in.select();
    }

    function onPagoCommit(id, anticipo, idCuenta, esEnter) {
        if (state.guardando) return;

        if (anticipo) {
            const max = maxAnticipoPermitido(id, idCuenta);
            if (num($('#venta_forma_pago_' + id).val()) > max) {
                $('#venta_forma_pago_' + id).val(formatCurrencyValue(max));
            }
        }

        const $in = $('#venta_forma_pago_' + id);
        if ($in.val() === '' || num($in.val()) < 0) $in.val(0);

        refrescar();
        if (!esEnter) return;

        const t = state.ultimosTotales;
        if (!t || !t.total) return;
        if (vendedoresVentas && !$('#id_vendedor_venta').val()) return;

        const [ef, otros, ant] = totalFormasPago();
        if ((ef + otros + ant) >= t.total + propinaActual()) {
            validateSaveVenta();
            return;
        }
        focusNextFormasPagoVentas(id);
    }

    function focusNextFormasPagoVentas(idActual) {
        const filas = filasPagos();
        if (!filas.length) return;

        let idFocus = filas[0].id;
        let tomarSiguiente = false;

        for (const fp of filas) {
            const input = document.getElementById('venta_forma_pago_' + fp.id);
            if (!input || input.disabled) continue;
            if (tomarSiguiente) {
                idFocus = fp.id;
                tomarSiguiente = false;
            } else if (fp.id == idActual) {
                tomarSiguiente = true;
            }
        }
        focusFormaPagoVenta(idFocus);
    }

    // ------------------------------------------------------------------
    // Anticipos / cliente / vendedor
    // ------------------------------------------------------------------
    function loadAnticiposClienteVentas() {
        state.anticiposTotal = 0;
        state.anticiposPorCuenta = {};
        $('#input-anticipos-venta').hide();
        $('#venta_anticipo_disp_view').hide();
        $('#saldo_anticipo_venta').val(0);
        $('#venta_anticipo_disp').text('0.00');

        const idNit = $('#id_cliente_venta').val();
        if (!idNit) return;

        api('extracto-anticipos', 'GET', { id_nit: idNit, id_tipo_cuenta: [ID_TIPO_CUENTA_ANTICIPO] })
            .done((res) => {
                if (!res.success || !res.data || !res.data.length) return;

                res.data.forEach((a) => {
                    const saldo = Math.abs(parseFloat(a.saldo)) || 0;
                    state.anticiposTotal += saldo;
                    state.anticiposPorCuenta[a.id_cuenta] = (state.anticiposPorCuenta[a.id_cuenta] || 0) + saldo;
                });

                if (state.anticiposTotal) {
                    $('#venta_anticipo_disp_view').show();
                    $('#saldo_anticipo_venta').val(fmt0.format(state.anticiposTotal));
                    $('#venta_anticipo_disp').text(fmt0.format(state.anticiposTotal));
                }
                refrescar();
            })
            .fail((err) => mostrarError(err, 'Error cargando anticipos'));
    }

    function loadVendedorCliente() {
        api('nit', 'GET', { id: $('#id_cliente_venta').val() })
            .done((res) => {
                if (!res.success || !res.data[0] || !res.data[0].vendedor) return;
                const v = res.data[0].vendedor.nit;
                setComboValue(state.$vendedor, v.id, v.numero_documento + ' - ' + v.nombre_completo);
            })
            .fail((err) => mostrarError(err, 'Error cargando vendedor'));
    }

    function procesarCambioClienteVenta(data) {
        if (!data) {
            $('#btn_ver_cliente_venta').hide();
            return;
        }

        state.calculaRetencion = false;
        $('#btn_ver_cliente_venta').show();
        $('#checkRetencionVentas').prop('checked', false);

        if (data.retencion) {
            state.calculaRetencion = true;
            $('#checkRetencionVentas').prop('checked', true);
        }

        state.responsabilidades = getResponsabilidades(data.id_responsabilidades);
        vaciarPagos();
        changeRetencionVentas();
        loadAnticiposClienteVentas();
        if (vendedoresVentas) loadVendedorCliente();
    }

    function changeRetencionVentas() {
        state.calculaRetencion = true;
        $('#totales_retencion').removeClass('totales_retencion_disable');
        if (!$('#checkRetencionVentas').is(':checked')) {
            state.calculaRetencion = false;
            $('#totales_retencion').addClass('totales_retencion_disable');
        }
        refrescar();
        actualizarInfoRetencionVentas();
    }

    // ------------------------------------------------------------------
    // Propina
    // ------------------------------------------------------------------
    function calcularPropinaVenta() {
        if (propinaActual()) return;
        const t = calcularTotales();
        const propina = t.total * (porcentajePropina / 100);
        $('#input_venta_propina').val(propina);
        $('#input_venta_propina').select();
    }

    function keyPresPropinaVenta(event) {
        if (event.key === 'Enter' || event.keyCode === 13) refrescar();
    }

    // ------------------------------------------------------------------
    // Combos / fechas
    // ------------------------------------------------------------------
    function crearCombo(selector, url, extra = {}, ajaxExtra = {}) {
        return $(selector).select2(Object.assign({
            theme: 'bootstrap-5',
            language: LANG,
            ajax: Object.assign({
                url,
                headers,
                dataType: 'json',
                delay: 250,
                processResults: (data) => ({ results: data.data })
            }, ajaxExtra)
        }, extra));
    }

    function setComboValue($combo, id, text) {
        const option = new Option(text, id, false, false);
        $combo.append(option).val(id).trigger('change');
    }

    function cargarCombosVenta() {
        state.$cliente = crearCombo('#id_cliente_venta', 'api/nit/combo-nit', {
            dropdownCssClass: 'custom-id_cliente_venta',
            language: Object.assign({}, LANG, {
                noResults: () => { state.createNewNit = true; return 'No hay resultado'; },
                searching: () => { state.createNewNit = false; return 'Buscando..'; }
            })
        });

        state.$vendedor = crearCombo('#id_vendedor_venta', 'api/vendedores/combo', { allowClear: true }, {
            processResults: (data) => ({
                results: data.data.map((o) => Object.assign({}, o, { text: o.nit.nombre_completo }))
            })
        });

        state.$resolucion = crearCombo('#id_resolucion_venta', 'api/resoluciones/combo-resoluciones', {}, {
            data: (params) => ({ q: params.term, tipo_resoluciones: [0, 1], _type: 'query' })
        });

        state.$bodega = crearCombo('#id_bodega_venta', 'api/bodega/combo-bodega');

        crearCombo('#id_tipo_documento_venta_nit', 'api/nit/combo-tipo-documento',
            { dropdownParent: $('#nitVentaFormModal') });
        crearCombo('#id_ciudad_venta_nit', 'api/ciudades',
            { dropdownParent: $('#nitVentaFormModal') });

        if (primeraResolucionVenta && primeraResolucionVenta.length > 0) {
            const r = primeraResolucionVenta[0];
            setComboValue(state.$resolucion, r.id, r.prefijo + ' - ' + r.nombre);
        }

        if (primeraBodegaVenta && primeraBodegaVenta.length > 0) {
            const b = primeraBodegaVenta[0];
            setComboValue(state.$bodega, b.id, b.codigo + ' - ' + b.nombre);
        }

        if (primeraNit) {
            setComboValue(state.$cliente, primeraNit.id, primeraNit.numero_documento + ' - ' + primeraNit.nombre_completo);
            procesarCambioClienteVenta(primeraNit);
        }
    }

    function cargarFechasVentas() {
        const d = new Date();
        const p = (n) => String(n).padStart(2, '0');
        const valor = `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
        $('#fecha_manual_venta').val(valor).prop('disabled', !ventaFecha);
    }

    function cargarPopoverVenta() {
        $('[data-toggle="popover"]').popover({
            trigger: 'hover',
            html: true,
            placement: 'top',
            container: 'body',
            customClass: 'popover-formas-pagos'
        });
    }

    function consecutivoSiguienteVenta() {
        const idResolucion = $('#id_resolucion_venta').val();
        const fechaManual = $('#fecha_manual_venta').val();
        if (!idResolucion || !fechaManual) return;

        api('consecutivo', 'GET', { id_resolucion: idResolucion, fecha_manual: fechaManual })
            .done((res) => { if (res.success) $('#documento_referencia_venta').val(res.data); })
            .fail((err) => mostrarError(err, 'Error consultando consecutivo'));
    }

    // ------------------------------------------------------------------
    // Guardar
    // ------------------------------------------------------------------
    function getVentasPagos() {
        const data = [];
        filasPagos().forEach((fp) => {
            const valor = num($('#venta_forma_pago_' + fp.id).val());
            if (valor > 0) data.push({ id: fp.id, valor });
        });
        return data;
    }

    function getProductosVenta() {
        const data = [];
        state.lineas.forEach((l) => {
            if (!l.idProducto) return;
            leerLineaDom(l);
            if (!l.cantidad) return;

            let subtotal = l.cantidad * l.costo;
            if (ivaIncluidoVentas) subtotal -= l.ivaVal;

            data.push({
                id_producto: parseInt(l.idProducto, 10),
                cantidad: l.cantidad,
                costo: l.costo || 0,
                subtotal,
                descuento_porcentaje: l.descPct || 0,
                descuento_valor: l.descVal || 0,
                iva_porcentaje: l.ivaPct || 0,
                iva_valor: l.ivaVal || 0,
                total: l.total || 0,
                concepto: l.concepto || null
            });
        });
        return data;
    }

    function validateSaveVenta() {
        $('#total_faltante_venta_text, #total_faltante_venta').css('color', '#484848');
        if (state.guardando) return;

        if (vendedoresVentas && !$('#id_vendedor_venta').val()) {
            refrescar();
            return;
        }
        if (!validarTodasLasExistencias()) return;

        const t = calcularTotales();
        const aPagar = t.total + propinaActual();
        const [ef, otros, ant] = totalFormasPago();

        if (aPagar > 0 && round2(ef + otros + ant) >= round2(aPagar)) {
            state.guardando = true;
            saveVenta();
        } else {
            $('#total_faltante_venta_text, #total_faltante_venta').css('color', 'red');
        }
    }

    function saveVenta() {
        $('#crearCapturaVenta').hide();
        $('#crearCapturaVentaDisabled').hide();
        $('#crearCapturaVentaLoading').show();

        // Llave de idempotencia: el backend puede usarla para ignorar reenvíos duplicados.
        if (!state.idempotencyKey) {
            state.idempotencyKey = (window.crypto && crypto.randomUUID)
                ? crypto.randomUUID()
                : String(Date.now()) + Math.random().toString(16).slice(2);
        }

        const data = {
            pagos: getVentasPagos(),
            productos: getProductosVenta(),
            propina: propinaActual(),
            id_bodega: $('#id_bodega_venta').val(),
            id_cliente: $('#id_cliente_venta').val(),
            fecha_manual: $('#fecha_manual_venta').val(),
            id_resolucion: $('#id_resolucion_venta').val(),
            id_vendedor: $('#id_vendedor_venta').val(),
            documento_referencia: $('#documento_referencia_venta').val(),
            observacion: $('#observacion_venta').val(),
            calcula_retencion: state.calculaRetencion ? 1 : 0,
            idempotency_key: state.idempotencyKey
        };

        // Bloquea los campos de pago mientras se guarda
        $('#ventaFormaPago .v-pago').prop('disabled', true);

        $.ajax({
            url: base_url + 'ventas',
            method: 'POST',
            data: JSON.stringify(data),
            headers,
            dataType: 'json'
        }).done((res) => {
            state.guardando = false;
            $('#crearCapturaVentaLoading').hide();

            if (!res.success) {
                mostrarError({ responseJSON: { message: res.message } }, 'Creación errada');
                refrescar();
                return;
            }

            state.idempotencyKey = null;
            consecutivoSiguienteVenta();
            agregarToast('exito', 'Creación exitosa', 'Venta creada con exito!', true);

            if (res.impresion) window.open('/ventas-print/' + res.impresion, '_blank');

            $('#iniciarCapturaVenta').hide();
            $('#iniciarCapturaVentaLoading').hide();
            $('#documento_referencia_venta').val('');
            $('#input_venta_propina').val('');

            vaciarGrilla();
            vaciarPagos();

            if (ventaRapida) {
                addRowProductoVenta();
            } else {
                setTimeout(() => {
                    $('#id_cliente_venta').focus();
                    state.$cliente.select2('open');
                }, 10);
            }

            loadAnticiposClienteVentas();
            cargarFechasVentas();
            refrescar();
        }).fail((err) => {
            // Reactiva todo para que el cajero pueda corregir y reintentar
            state.guardando = false;
            $('#crearCapturaVentaLoading').hide();
            refrescar();
            mostrarError(err, 'Creación errada');
        });
    }

    // ------------------------------------------------------------------
    // Cancelar / iniciar
    // ------------------------------------------------------------------
    function cancelarVenta() {
        vaciarGrilla();
        vaciarPagos();
        state.capturando = false;
        state.idempotencyKey = null;

        $('#id_bodega_venta').prop('disabled', false);
        $('#id_resolucion_venta').prop('disabled', false);

        $('#iniciarCapturaVenta').show();
        $('#agregarVentaProducto').hide();
        $('#crearCapturaVenta').hide();
        $('#cancelarCapturaVenta').hide();
        $('#crearCapturaVentaDisabled').hide();
        $('#input-anticipos-venta').hide();

        refrescar();
        cargarFechasVentas();

        setTimeout(() => state.$cliente.select2('open'), 10);
    }

    function iniciarCaptura() {
        const form = document.querySelector('#ventaFilterForm');
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            $('#error_documento_referencia_venta').text('El No. factura requerido');
            return;
        }

        state.capturando = true;
        $('#iniciarCapturaVenta').hide();
        $('#agregarVentaProducto').show();
        $('#crearCapturaVenta').hide();
        $('#cancelarCapturaVenta').show();
        $('#cambio-totals').hide();
        $('#crearCapturaVentaDisabled').show();

        addRowProductoVenta();
    }

    // ------------------------------------------------------------------
    // Modales de NIT
    // ------------------------------------------------------------------
    function openModalNewNitVenta() {
        abrirNitGeneral({
            modo: 'create',
            captura: 'venta',
            onSave: function (dataNit) {
                setComboValue(state.$cliente, dataNit.id, dataNit.numero_documento + ' - ' + dataNit.nombre_completo);
                procesarCambioClienteVenta(dataNit);
                agregarToast('exito', 'Creación exitosa', 'Cliente creado con éxito!', true);
                document.getElementById('iniciarCapturaVenta').click();
            }
        });
    }

    function openModalViewNitVenta() {
        const data = $('#id_cliente_venta').select2('data');
        if (!data.length) {
            agregarToast('warning', 'Sin cliente', 'Selecciona un cliente primero');
            return;
        }
        abrirNitDetalle(data[0].id);
    }

    function openModalEditNitVenta() {
        const data = $('#id_cliente_venta').select2('data');
        if (!data.length) {
            agregarToast('warning', 'Sin cliente', 'Selecciona un cliente primero');
            return;
        }
        abrirNitGeneral({
            modo: 'edit',
            id: data[0].id,
            onUpdate: function (dataNit) {
                const $sel = $('#id_cliente_venta');
                const texto = dataNit.numero_documento + ' - ' + dataNit.nombre_completo;
                $sel.find("option[value='" + dataNit.id + "']").remove();
                $sel.append(new Option(texto, dataNit.id, true, true));
                $sel.val(dataNit.id).trigger('change');

                procesarCambioClienteVenta(dataNit);
                agregarToast('exito', 'Edición exitosa', 'Cliente actualizado con éxito!', true);
            }
        });
    }

    // ------------------------------------------------------------------
    // Eventos (se registran una sola vez, todos delegados)
    // ------------------------------------------------------------------
    function registrarEventos() {
        if (state.inited) return;
        state.inited = true;

        const $doc = $(document);

        // Formato de moneda: un solo handler para toda la grilla y los pagos
        $('#ventaTable, #ventaFormaPago')
            .on('keyup.venta', "input[data-type='currency']", function (e) {
                const k = e.key;
                if ((k >= '0' && k <= '9') || k === 'Backspace' || k === 'Delete' || k === ',' || k === '.') {
                    formatCurrency($(this));
                }
            })
            .on('blur.venta', "input[data-type='currency']", function () {
                formatCurrency($(this), 'blur');
            });

        // --- Grilla de productos ---
        $doc.on('click.venta', '.v-borrar', function () {
            deleteProductoVenta(Number(this.dataset.id));
        });

        $doc.on('change.venta', '#ventaTable .venta_producto', function () {
            onProductoChange(Number(this.dataset.id));
        });

        $doc.on('click.venta', '.btn-precios-ventas', function () {
            mostrarPreciosVentas(Number(this.dataset.id));
        });

        $doc.on('focusin.venta', '#ventaTable .v-cantidad, #ventaTable .v-costo, #ventaTable .v-dpct, #ventaTable .v-dval',
            function () { seleccionarConDelay(this); });

        $doc.on('focusout.venta', '#ventaTable .v-cantidad, #ventaTable .v-costo, #ventaTable .v-dval, #ventaTable .v-concepto',
            function () { recalcularLinea(Number(this.dataset.id), 'val'); });

        $doc.on('focusout.venta', '#ventaTable .v-dpct', function () {
            recalcularLinea(Number(this.dataset.id), 'pct');
        });

        $doc.on('keydown.venta', '#ventaTable .v-cantidad', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); confirmarCantidad(Number(this.dataset.id)); }
        });

        $doc.on('keydown.venta', '#ventaTable .v-costo', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                recalcularLinea(Number(this.dataset.id), 'val');
                addRowProductoVenta();
            }
        });

        $doc.on('keydown.venta', '#ventaTable .v-dpct', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const id = Number(this.dataset.id);
                recalcularLinea(id, 'pct');
                setTimeout(() => {
                    const $v = $('#venta_descuento_valor_' + id);
                    $v.focus();
                    $v.select();
                }, 10);
            }
        });

        $doc.on('keydown.venta', '#ventaTable .v-dval', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                recalcularLinea(Number(this.dataset.id), 'val');
                addRowProductoVenta();
            }
        });

        // --- Buscador de productos: Numpad0 + Enter abre formas de pago ---
        $doc.on('keydown.venta', '.custom-venta_producto .select2-search__field', function (event) {
            if (state.guardando) return;

            if (event.code === 'Numpad0' && !this.value.length) {
                state.abrirFormasPago = true;
            } else if (event.key === 'Enter') {
                const t = calcularTotales();
                if (t.total > 0 && state.abrirFormasPago) {
                    state.abrirFormasPago = false;
                    $('.venta_producto').select2('close');
                    focusFormaPagoVenta(ID_FORMA_PAGO_EFECTIVO);
                }
            } else {
                state.abrirFormasPago = false;
            }
        });

        // --- Buscador de clientes: Enter sin resultados => crear NIT ---
        $doc.on('keydown.venta', '.custom-id_cliente_venta .select2-search__field', function (event) {
            if (event.key === 'Enter' && state.createNewNit) {
                const documentoBuscado = this.value;
                openModalNewNitVenta();
                state.$cliente.select2('close');
                $('#numero_documento_venta_nit').val(documentoBuscado);
            }
        });

        // --- Formas de pago ---
        $doc.on('focusin.venta', '#ventaFormaPago .v-pago', function () {
            const m = metaPago($(this));
            focusFormaPagoVenta(m.id, m.anticipo, m.idCuenta);
        });

        $doc.on('focusout.venta', '#ventaFormaPago .v-pago', function () {
            const m = metaPago($(this));
            onPagoCommit(m.id, m.anticipo, m.idCuenta, false);
        });

        $doc.on('keydown.venta', '#ventaFormaPago .v-pago', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const m = metaPago($(this));
            onPagoCommit(m.id, m.anticipo, m.idCuenta, true);
        });

        // --- Encabezado ---
        $doc.on('change.venta', '#id_vendedor_venta', refrescar);
        $doc.on('change.venta', '#id_resolucion_venta', consecutivoSiguienteVenta);

        $doc.on('select2:close.venta', '#id_cliente_venta', function () {
            const data = $(this).select2('data');
            if (data.length) procesarCambioClienteVenta(data[0]);
        });

        $doc.on('select2:close.venta', '#id_resolucion_venta', function () {
            const data = $(this).select2('data');
            if (data.length) document.getElementById('iniciarCapturaVenta').click();
        });

        $doc.on('keydown.venta', '#fecha_manual_venta', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                setTimeout(() => {
                    $('#documento_referencia_venta').focus().select();
                }, 10);
            }
        });

        $doc.on('keydown.venta', '#documento_referencia_venta', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('iniciarCapturaVenta').click();
            }
        });

        // --- Botones ---
        $doc.on('click.venta', '#iniciarCapturaVenta', iniciarCaptura);
        $doc.on('click.venta', '#agregarVentaProducto', addRowProductoVenta);
        $doc.on('click.venta', '#cancelarCapturaVenta', cancelarVenta);
        $doc.on('click.venta', '#crearCapturaVenta', validateSaveVenta);
    }

    function resetState() {
        // 1. Quitar handlers viejos (namespace .venta)
        try { $(document).off('.venta'); } catch (e) { /* noop */ }
        try { $('#ventaTable, #ventaFormaPago').off('.venta'); } catch (e) { /* noop */ }

        // 2. Destruir DataTable de pagos
        if (state.tablaPagos) {
            try { state.tablaPagos.destroy(); } catch (e) { /* noop */ }
            state.tablaPagos = null;
        }
        $('#ventaFormaPago').empty();

        // 3. Destruir select2 de combos principales
        ['#id_cliente_venta', '#id_vendedor_venta', '#id_resolucion_venta', '#id_bodega_venta']
            .forEach((sel) => {
                const $el = $(sel);
                if ($el.data('select2')) {
                    try { $el.select2('destroy'); } catch (e) { /* noop */ }
                }
                // Quitar opciones viejas para que no queden pegadas
                try { $el.empty(); } catch (e) { /* noop */ }
            });

        // 4. Destruir select2 de productos y vaciar tbody
        $('#ventaTable .venta_producto').each(function () {
            if ($(this).data('select2')) {
                try { $(this).select2('destroy'); } catch (e) { /* noop */ }
            }
        });
        $('#ventaTable tbody').empty();

        // 5. Resetear estado en memoria
        state.nextId = 0;
        state.lineas = new Map();
        state.capturando = false;
        state.guardando = false;
        state.abrirFormasPago = false;
        state.createNewNit = false;
        state.calculaRetencion = false;
        state.responsabilidades = [];
        state.anticiposTotal = 0;
        state.anticiposPorCuenta = {};
        state.ultimosTotales = null;
        state.idempotencyKey = null;
        state.reqExistencias = null;
        state.inited = false;

        // 6. Resetear animación
        countUpTotal = null;
        ultimoTotalAnimado = null;

        // 7. Resetear UI de botones al estado inicial
        $('#iniciarCapturaVenta').show();
        $('#agregarVentaProducto').hide();
        $('#crearCapturaVenta').hide();
        $('#cancelarCapturaVenta').hide();
        $('#crearCapturaVentaDisabled').hide();
        $('#crearCapturaVentaLoading').hide();
        $('#iniciarCapturaVentaLoading').hide();
        $('#input-anticipos-venta').hide();

        $('#id_bodega_venta').prop('disabled', false);
        $('#id_resolucion_venta').prop('disabled', false);
    }

    // ------------------------------------------------------------------
    // Init
    // ------------------------------------------------------------------
    function ventaInit() {
        resetState();

        inyectarEstilos();
        registrarEventos();
        aplicarVisibilidadColumnas();
        iniciarTablaPagos();
        cargarFechasVentas();
        cargarCombosVenta();
        cargarPopoverVenta();
        loadFormasPagoVenta();
        actualizarInfoPropinaVentas();

        $('.water').hide();
    }

    // ------------------------------------------------------------------
    // API pública (lo que el HTML u otros scripts pueden invocar)
    // ------------------------------------------------------------------
    Object.assign(window, {
        ventaInit,
        cancelarVenta,
        changeRetencionVentas,
        calcularPropinaVenta,
        keyPresPropinaVenta,
        openModalNewNitVenta,
        openModalViewNitVenta,
        openModalEditNitVenta,
        loadFormasPagoVenta,
        clearFormasPagoVenta,
        consecutivoSiguienteVenta,
        loadAnticiposClienteVentas
    });

})(window, jQuery);