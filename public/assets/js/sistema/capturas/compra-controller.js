/**
 * compras.js  (versión refactorizada + aislamiento de ventas)
 *
 * Ajustes para convivencia con ventas.js:
 *  - Selectores de grilla scopeados a #compraTable (evita que .v-borrar de ventas dispare borrado de compras).
 *  - Popovers scopeados a contenedores de compras (#compraFormaPago, #icon_info_retencion_compra).
 *  - IDs compartidos renombrados en compras:
 *      #cambio-totals       -> #cambio-totals-compra
 *      #totales_descuento   -> #totales_descuento-compra
 *    (Debes actualizar esos 2 IDs en el HTML de compras).
 *  - El resto de IDs se mantienen igual que antes.
 */
(function (window, $) {
    'use strict';

    // ------------------------------------------------------------------
    // Constantes
    // ------------------------------------------------------------------
    const ID_FORMA_PAGO_EFECTIVO = 1;
    const ID_TIPO_CUENTA_ANTICIPO = 8;
    const LOCALE_NUM = 'en-US';

    const fmt2 = new Intl.NumberFormat(LOCALE_NUM, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const fmt0 = new Intl.NumberFormat(LOCALE_NUM, { maximumFractionDigits: 0 });

    const LANG = {
        noResults: () => 'No hay resultado',
        searching: () => 'Buscando..',
        inputTooShort: () => 'Por favor introduce 1 o más caracteres'
    };

    const LANG_PRODUCTO = {
        noResults: () => 'No hay resultado',
        searching: () => 'Buscando..',
        inputTooShort: () => 'Por favor introduce 2 o más caracteres'
    };

    // ------------------------------------------------------------------
    // Estado
    // ------------------------------------------------------------------
    const state = {
        nextId: 0,
        lineas: new Map(),
        capturando: false,
        guardando: false,
        abrirFormasPago: false,
        createNewNit: false,
        calculaRetencion: false,
        responsabilidades: [],
        anticiposTotal: 0,
        anticiposPorCuenta: {},
        ultimosTotales: null,
        modoCaptura: 'compra',
        inited: false,
        $proveedor: null,
        $vendedor: null,
        $comprobante: null,
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

    function animarTotal(valor) {
        valor = round2(valor);
        if (valor === ultimoTotalAnimado) return;
        ultimoTotalAnimado = valor;

        if (typeof CountUp === 'undefined') {
            setText('compra_total_valor', valor);
            return;
        }

        if (!countUpTotal) {
            countUpTotal = new CountUp('compra_total_valor', 0, valor, 2, 0.2);
            countUpTotal.start();
        } else {
            countUpTotal.update(valor);
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
        const card = document.getElementById('card-compra');
        if (card) card.scrollLeft = 0;
    }

    function inyectarEstilosCompras() {
        if (document.getElementById('compra-estilos')) return;
        const style = document.createElement('style');
        style.id = 'compra-estilos';
        style.textContent = `
            #compraTable.v-sin-existencias th:nth-child(3), #compraTable.v-sin-existencias td:nth-child(3),
            #compraTable.v-sin-descuento th:nth-child(6), #compraTable.v-sin-descuento td:nth-child(6),
            #compraTable.v-sin-descuento th:nth-child(7), #compraTable.v-sin-descuento td:nth-child(7) { display: none; }
        `;
        document.head.appendChild(style);
    }

    function aplicarVisibilidadColumnasCompras() {
        const $t = $('#compraTable');
        $t.toggleClass('v-sin-existencias', !compraExistencias);
        $t.toggleClass('v-sin-descuento', !compraDescuento);
    }

    function getTbody() {
        let $tb = $('#compraTable tbody');
        if (!$tb.length) $tb = $('<tbody></tbody>').appendTo('#compraTable');
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
            iva = ivaIncluidoCompras
                ? base * (l.ivaPct / (l.ivaPct + 100))
                : base * l.ivaPct / 100;
        }
        l.ivaVal = iva;
        l.total = round2(ivaIncluidoCompras ? base : base + iva);
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

        const neto = ivaIncluidoCompras ? bruto - iva : bruto;
        let total = neto + iva;
        const retencion = calcularRetencion(neto, total);
        total -= retencion;

        let redondeo = 0;
        if (window.redondearFactura) {
            const redondeado = Math.round(total / 1000) * 1000;
            redondeo = redondeado - total;
            total = redondeado;
        }

        if (ivaIncluidoCompras) total = total + iva;

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
            const $in = $('#compra_forma_pago_' + fp.id);
            const valor = num($in.val());
            if (fp.id == ID_FORMA_PAGO_EFECTIVO) efectivo += valor;
            else if ($in.hasClass('anticipos')) anticipos += valor;
            else otros += valor;
        });
        return [efectivo, otros, anticipos];
    }

    // ------------------------------------------------------------------
    // Render
    // ------------------------------------------------------------------
    function refrescar() {
        if (state.guardando) return;

        const t = calcularTotales();
        state.ultimosTotales = t;

        // IDs de compras (aislados de ventas)
        $('#totales_descuento-compra').toggle(!!t.descuento);
        setText('compra_total_iva', t.iva);
        setText('compra_total_descuento', t.descuento);
        setText('compra_total_retencion', t.retencion);
        animarTotal(t.total);
        setText('compra_sub_total', t.valorBruto);

        // Pagos
        const [ef, otros, ant] = totalFormasPago();
        const pagado = ef + otros + ant;
        const faltante = Math.max(0, t.total - pagado);
        setText('total_pagado_compra', Math.min(pagado, t.total));
        setText('total_faltante_compra', faltante);
        $('#total_faltante_compra').removeClass('is-invalid');

        const cambio = pagado - t.total;
        if (cambio >= 1) {
            $('#cambio-totals-compra').show();
            setText('total_cambio_compra', cambio);
        } else {
            $('#cambio-totals-compra').hide();
        }

        habilitarFormasPago(t.total > 0);

        // Botón guardar
        if (state.capturando) {
            const listo = t.total > 0 && round2(pagado) >= round2(t.total);
            if (listo) {
                $('#crearCapturaCompra').show();
                $('#crearCapturaCompraDisabled').hide();
            } else {
                $('#crearCapturaCompra').hide();
                $('#crearCapturaCompraDisabled').show();
            }
        }

        refrescarPopoverRetencion();
    }

    function habilitarFormasPago(hayTotal) {
        filasPagos().forEach((fp) => {
            const $in = $('#compra_forma_pago_' + fp.id);
            if (esAnticipoCuenta(fp)) {
                const saldo = state.anticiposPorCuenta[fp.cuenta.id] || 0;
                $in.prop('disabled', !(hayTotal && saldo > 0));
            } else {
                $in.prop('disabled', !hayTotal);
            }
        });
    }

    function actualizarInfoRetencionCompras() {
        const iconInfo = document.getElementById('icon_info_retencion_compra');
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

        $('#nombre_info_retencion_compra').html(`RETENCIÓN %${porcentaje}:`);

        const uvt = typeof valor_uvt !== 'undefined' ? valor_uvt : 0;
        const responsable = state.calculaRetencion
            ? `<b class='titulo-popover'>Con permiso:</b> Calcula retención en la fuente`
            : `<b class='titulo-popover'>Sin responsablidad:</b> 07 => No calcula retención en la fuente`;

        const titulo = `
            <b class='titulo-popover'>Cuenta:</b> ${esc(nombre)}<br/>
            <b class='titulo-popover'>UVT:</b> ${totalUvt} X ${fmt0.format(uvt)} = ${fmt0.format(base)}<br/>
            <b class='titulo-popover'>Subtotal:</b> ${fmt0.format(t.valorBruto)}<br/>
            ${responsable}
        `;
        recrearPopover(iconInfo, titulo);
    }

    const refrescarPopoverRetencion = debounce(actualizarInfoRetencionCompras, 250);

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
        return `
        <tr id="compra_row_${id}" data-id="${id}">
            <td><span class="btn badge bg-gradient-danger drop-row-grid v-borrar" data-id="${id}" id="delete-producto-compra_${id}"><i class="fas fa-trash-alt"></i></span></td>
            <td><select class="form-control form-control-sm compra_producto combo-grid" data-id="${id}" id="compra_producto_${id}" inert></select></td>
            <td><input type="number" class="form-control form-control-sm" style="min-width: 30px; text-align: right;" id="compra_existencia_${id}" disabled></td>
            <td>
                <div class="input-group" style="height: 30px;">
                    <input type="number" class="form-control form-control-sm v-cantidad" data-id="${id}" style="min-width: 80px; border-right: solid 1px #b3b3b3; border-top-right-radius: 10px; border-bottom-right-radius: 10px; text-align: right;" id="compra_cantidad_${id}" min="0" step="any" value="1" disabled>
                    <i class="fa fa-spinner fa-spin fa-fw compra_producto_load" id="compra_producto_load_${id}" style="display: none;"></i>
                    <div id="compra_cantidad_text_${id}" style="position: absolute; margin-top: 30px; z-index: 9;" class="invalid-feedback"></div>
                </div>
            </td>
            <td><input type="text" data-type="currency" class="form-control form-control-sm v-costo" data-id="${id}" style="min-width: 100px; text-align: right;" id="compra_costo_${id}" value="0" disabled></td>
            <td><input type="text" data-type="currency" class="form-control form-control-sm v-dpct" data-id="${id}" style="min-width: 80px; text-align: right;" id="compra_descuento_porcentaje_${id}" value="0" disabled></td>
            <td><input type="text" data-type="currency" class="form-control form-control-sm v-dval" data-id="${id}" style="min-width: 100px; text-align: right;" id="compra_descuento_valor_${id}" value="0" disabled></td>
            <td>
                <div class="form-group mb-3" style="min-width: 85px;">
                    <div class="input-group input-group-sm" style="height: 18px; min-width: 112px;">
                        <span id="compra_iva_porcentaje_text_${id}" class="input-group-text" style="height: 30px; background-color: #e9ecef; font-size: 11px; width: 33px; border-right: solid 2px #c9c9c9 !important; padding: 5px;">0%</span>
                        <input style="height: 30px; text-align: right; min-width: 80px;" type="text" class="form-control form-control-sm" value="0" id="compra_iva_valor_${id}" disabled>
                    </div>
                </div>
                <input type="number" class="form-control form-control-sm" style="min-width: 110px; display: none;" id="compra_iva_porcentaje_${id}" value="0">
            </td>
            <td><input type="text" data-type="currency" class="form-control form-control-sm" style="min-width: 100px; text-align: right;" id="compra_total_${id}" value="0" disabled></td>
            <td><input type="text" class="form-control form-control-sm v-concepto" data-id="${id}" id="compra_concepto_${id}" onfocus="this.select();" style="width: 180px !important;" value="" disabled></td>
        </tr>`;
    }

    function formatProducto(producto) {
        if (producto.loading) return producto.text;

        const urlImagen = producto.imagen ? bucketUrl + producto.imagen : '/img/sin_imagen.png';
        const inventarios = producto.inventarios || [];
        const conInventario = !!(producto.familia && producto.familia.inventario) && compraExistencias;

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
            dropdownCssClass: 'custom-compra_producto',
            minimumInputLength: 2,
            language: LANG_PRODUCTO,
            ajax: {
                url: 'api/producto/combo-producto',
                headers,
                dataType: 'json',
                delay: 250,
                data: (params) => ({
                    q: params.term,
                    id_bodega: $('#id_bodega_compra').val(),
                    id_cliente: $('#id_proveedor_compra').val(),
                    captura: 'compra',
                    tipo_producto: state.modoCaptura === 'documento_soporte' ? [4] : [0, 1, 3],
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

    function addRowProductoCompra() {
        const last = ultimaLinea();
        if (last && !last.idProducto) {
            $('#compra_producto_' + last.id).select2('open');
            scrollGrillaIzquierda();
            return;
        }

        const id = state.nextId++;
        state.lineas.set(id, nuevaLinea(id));
        getTbody().append(rowHtml(id));

        const $sel = $('#compra_producto_' + id);
        initProductoSelect($sel);
        scrollGrillaIzquierda();
        $sel.select2('open');
    }

    function deleteProductoCompra(id) {
        const $sel = $('#compra_producto_' + id);
        if ($sel.data('select2')) $sel.select2('destroy');
        $('#compra_row_' + id).remove();
        state.lineas.delete(id);
        refrescar();
    }

    function vaciarGrilla() {
        $('#compraTable .compra_producto').each(function () {
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
        l.cantidad = parseFloat($('#compra_cantidad_' + id).val()) || 0;
        l.costo = num($('#compra_costo_' + id).val());
        l.descPct = num($('#compra_descuento_porcentaje_' + id).val());
        l.descVal = num($('#compra_descuento_valor_' + id).val());
        l.concepto = $('#compra_concepto_' + id).val() || '';
    }

    function pintarLinea(l) {
        const id = l.id;
        $('#compra_descuento_porcentaje_' + id).val(formatCurrencyValue(l.descPct));
        $('#compra_descuento_valor_' + id).val(formatCurrencyValue(l.descVal));
        $('#compra_iva_porcentaje_' + id).val(l.ivaPct);
        $('#compra_iva_porcentaje_text_' + id).text(l.ivaPct + '%');
        $('#compra_iva_valor_' + id).val(formatCurrencyValue(l.ivaVal));
        $('#compra_total_' + id).val(formatCurrencyValue(l.total));
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

        const sel = $('#compra_producto_' + id).select2('data');
        const data = sel && sel[0];
        if (!data || !data.id) return;

        const familia = data.familia || {};
        const inv = (data.inventarios || [])[0];

        l.idProducto = data.id;
        l.producto = data;

        if (inv && familia.inventario) {
            const existencias = parseFloat(inv.cantidad);
            $('#compra_existencia_' + id).val(existencias);
            $('#compra_cantidad_' + id).attr('max', existencias);
        }

        const imp = familia.cuenta_compra_iva && familia.cuenta_compra_iva.impuesto;
        l.ivaPct = imp ? (parseFloat(imp.porcentaje) || 0) : 0;

        const crNodec = familia.cuenta_compra_retencion_nodeclarante;
        const cr = familia.cuenta_compra_retencion;
        if (!state.responsabilidades.includes('5') && crNodec && crNodec.impuesto) {
            l.retencion = {
                cuenta: crNodec.cuenta,
                nombre: crNodec.nombre,
                id_retencion: crNodec.impuesto.id,
                porcentaje: parseFloat(crNodec.impuesto.porcentaje),
                base: parseFloat(crNodec.impuesto.base),
                total_uvt: parseFloat(crNodec.impuesto.total_uvt)
            };
        } else if (cr && cr.impuesto) {
            l.retencion = {
                cuenta: cr.cuenta,
                nombre: cr.nombre,
                id_retencion: cr.impuesto.id,
                porcentaje: parseFloat(cr.impuesto.porcentaje),
                base: parseFloat(cr.impuesto.base),
                total_uvt: parseFloat(cr.impuesto.total_uvt)
            };
        } else {
            l.retencion = null;
        }

        const permiteDescuento = !!(familia.id_cuenta_compra_descuento && compraDescuento);
        $('#compra_descuento_valor_' + id).prop('disabled', !permiteDescuento);
        $('#compra_descuento_porcentaje_' + id).prop('disabled', !permiteDescuento);

        $('#compra_costo_' + id).val(formatCurrencyValue(data.precio_inicial));
        $('#compra_cantidad_' + id).prop('disabled', false);
        $('#compra_costo_' + id).prop('disabled', false);
        $('#compra_concepto_' + id).prop('disabled', false);

        vaciarPagos();
        recalcularLinea(id);

        setTimeout(() => {
            const $c = $('#compra_cantidad_' + id);
            $c.focus();
            $c.select();
        }, 10);
    }

    function confirmarCantidad(id) {
        const l = recalcularLinea(id);
        if (!l || !l.idProducto) return;
        addRowProductoCompra();
    }

    // ------------------------------------------------------------------
    // Formas de pago
    // ------------------------------------------------------------------
    function iniciarTablaPagosCompras() {
        state.tablaPagos = $('#compraFormaPago').DataTable({
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
                data: { type: 'compras' },
                url: base_url + 'forma-pago/combo-forma-pago'
            },
            columns: [
                {
                    data: function (row) {
                        let styles = 'margin-bottom: 0px; font-size: 13px;';
                        let stylesInfo = '';
                        let naturaleza = 'Credito - Compras';
                        if (row.cuenta.naturaleza_compras == 0) {
                            naturaleza = 'Error de naturaleza en comprar';
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
                            id="compra_forma_pago_${row.id}">`;
                    }
                }
            ],
            initComplete: function () {
                $('#compraFormaPago').on('draw.dt', function () {
                    $('#compraFormaPago [data-toggle="popover"]').popover({
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

    function loadFormasPagoCompra() {
        if (!state.tablaPagos) return;
        state.tablaPagos.ajax.reload(function () {
            refrescar();
        });
    }

    function vaciarPagos() {
        $('#compraFormaPago .v-pago').val(0);
    }

    function clearFormasPagoCompra() {
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

    function maxAnticipoPermitido(id, idCuenta) {
        const t = calcularTotales();
        const [ef, otros, ant] = totalFormasPago(id);
        const saldo = state.anticiposPorCuenta[idCuenta] || 0;
        const faltante = Math.max(0, t.total - (ef + otros + ant));
        return Math.max(0, Math.min(saldo - ant, faltante));
    }

    function focusFormaPagoCompra(id, anticipo = false, idCuenta = null) {
        if (state.guardando) return;

        const t = calcularTotales();
        const [ef, otros, ant] = totalFormasPago(id);
        const faltante = Math.max(0, t.total - (ef + otros + ant));

        const $in = $('#compra_forma_pago_' + id);

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
            if (num($('#compra_forma_pago_' + id).val()) > max) {
                $('#compra_forma_pago_' + id).val(formatCurrencyValue(max));
            }
        }

        const $in = $('#compra_forma_pago_' + id);
        if ($in.val() === '' || num($in.val()) < 0) $in.val(0);

        refrescar();
        if (!esEnter) return;

        const t = state.ultimosTotales;
        if (!t || !t.total) return;

        const [ef, otros, ant] = totalFormasPago();
        if ((ef + otros + ant) >= t.total) {
            validateSaveCompra();
            return;
        }
        focusNextFormasPagoCompras(id);
    }

    function focusNextFormasPagoCompras(idActual) {
        const filas = filasPagos();
        if (!filas.length) return;

        let idFocus = filas[0].id;
        let tomarSiguiente = false;

        for (const fp of filas) {
            const input = document.getElementById('compra_forma_pago_' + fp.id);
            if (!input || input.disabled) continue;
            if (tomarSiguiente) {
                idFocus = fp.id;
                tomarSiguiente = false;
            } else if (fp.id == idActual) {
                tomarSiguiente = true;
            }
        }
        focusFormaPagoCompra(idFocus);
    }

    // ------------------------------------------------------------------
    // Anticipos / proveedor
    // ------------------------------------------------------------------
    function loadAnticiposProveedorCompras() {
        state.anticiposTotal = 0;
        state.anticiposPorCuenta = {};
        $('#input-anticipos-compra').hide();
        $('#compra_anticipo_disp_view').hide();
        $('#saldo_anticipo_compra').val(0);
        $('#compra_anticipo_disp').text('0.00');

        const idNit = $('#id_proveedor_compra').val();
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
                    $('#compra_anticipo_disp_view').show();
                    $('#saldo_anticipo_compra').val(fmt0.format(state.anticiposTotal));
                    $('#compra_anticipo_disp').text(fmt0.format(state.anticiposTotal));
                }
                refrescar();
            })
            .fail((err) => mostrarError(err, 'Error cargando anticipos'));
    }

    function procesarCambioProveedorCompra(data) {
        if (!data) {
            $('#btn_ver_cliente_compra').hide();
            return;
        }

        state.calculaRetencion = false;
        $('#btn_ver_cliente_compra').show();
        $('#checkRetencionCompras').prop('checked', false);

        if (data.retencion) {
            state.calculaRetencion = true;
            $('#checkRetencionCompras').prop('checked', true);
        }

        state.responsabilidades = getResponsabilidades(data.id_responsabilidades);
        vaciarPagos();
        changeRetencionCompras();
        loadAnticiposProveedorCompras();
    }

    function changeRetencionCompras() {
        state.calculaRetencion = true;
        $('#totales_retencion_compra').removeClass('totales_retencion_disable');
        if (!$('#checkRetencionCompras').is(':checked')) {
            state.calculaRetencion = false;
            $('#totales_retencion_compra').addClass('totales_retencion_disable');
        }
        refrescar();
        actualizarInfoRetencionCompras();
    }

    // ------------------------------------------------------------------
    // Modo captura (compra / documento soporte)
    // ------------------------------------------------------------------
    function toggleModoCapturaCompra() {
        if (state.modoCaptura === 'compra') {
            state.modoCaptura = 'documento_soporte';

            $('#id_comprobante_compra').prop('disabled', true).prop('required', false);
            $('#id_comprobante_compra').next('.select2-container').hide();
            $('#id_comprobante_compra').hide();

            if ($('#id_resolucion_compra').data('select2')) {
                $('#id_resolucion_compra').select2('destroy');
            }
            $('#id_resolucion_compra').show().prop('disabled', false).prop('required', true);
            initComboResolucionCompra();
            $('#id_resolucion_compra').next('.select2-container').show();

            $('#label_comprobante_resolucion_compra').html('Resolución<span style="color:red">*</span>');
            $('#toggleDocumentoSoporteCompraTexto').text('COMPRA');
            $('#titulo-view').text('Documento Soporte');
            $('#textiniciarCapturaCompra').text('INICIAR DOCUMENTO SOPORTE');
            $('#textoCrearCapturaCompraDisabled').text('GRABAR DOCUMENTO SOPORTE');
            $('#crearCapturaCompra').text('GRABAR DOCUMENTO SOPORTE');
            $('#toggleDocumentoSoporteCompra').attr('title', 'Cambiar a Compra');
            $('#toggleDocumentoSoporteCompra').removeClass('bg-gradient-warning').addClass('bg-gradient-primary');
        } else {
            state.modoCaptura = 'compra';

            $('#id_resolucion_compra').prop('disabled', true).prop('required', false);
            $('#id_resolucion_compra').next('.select2-container').hide();
            $('#id_resolucion_compra').hide();

            if ($('#id_comprobante_compra').data('select2')) {
                $('#id_comprobante_compra').select2('destroy');
            }
            $('#id_comprobante_compra').show().prop('disabled', false).prop('required', true);
            state.$comprobante = $('#id_comprobante_compra').select2({
                theme: 'bootstrap-5',
                delay: 250,
                ajax: {
                    url: 'api/comprobantes/combo-comprobante',
                    headers: headers,
                    data: (params) => ({ q: params.term, tipo_comprobante: 2, _type: 'query' }),
                    dataType: 'json',
                    processResults: (data) => ({ results: data.data })
                }
            });
            if (primeraComprobanteCompra) {
                const text = primeraComprobanteCompra.codigo + ' - ' + primeraComprobanteCompra.nombre;
                const opt = new Option(text, primeraComprobanteCompra.id, false, false);
                state.$comprobante.append(opt).trigger('change');
                state.$comprobante.val(primeraComprobanteCompra.id).trigger('change');
            }

            $('#label_comprobante_resolucion_compra').html('Comprobante<span style="color:red">*</span>');
            $('#toggleDocumentoSoporteCompraTexto').text('DOC. SOPORTE');
            $('#titulo-view').text('Compras');
            $('#textiniciarCapturaCompra').text('INICIAR COMPRA');
            $('#textoCrearCapturaCompraDisabled').text('GRABAR COMPRA');
            $('#crearCapturaCompra').text('GRABAR COMPRA');
            $('#toggleDocumentoSoporteCompra').attr('title', 'Cambiar a Documento Soporte');
            $('#toggleDocumentoSoporteCompra').removeClass('bg-gradient-primary').addClass('bg-gradient-warning');
        }

        $('#documento_referencia_compra').val('');
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

    function cargarCombosCompra() {
        state.$proveedor = crearCombo('#id_proveedor_compra', 'api/nit/combo-nit', {
            dropdownCssClass: 'custom-id_proveedor_compra',
            language: Object.assign({}, LANG, {
                noResults: () => { state.createNewNit = true; return 'No hay resultado'; },
                searching: () => { state.createNewNit = false; return 'Buscando..'; }
            })
        });

        state.$vendedor = crearCombo('#id_vendedor_compra', 'api/vendedores/combo', { allowClear: true }, {
            processResults: (data) => ({
                results: data.data.map((o) => Object.assign({}, o, { text: o.nit.nombre_completo }))
            })
        });

        state.$comprobante = crearCombo('#id_comprobante_compra', 'api/comprobantes/combo-comprobante', {}, {
            data: (params) => ({ q: params.term, tipo_comprobante: 2, _type: 'query' })
        });

        state.$bodega = crearCombo('#id_bodega_compra', 'api/bodega/combo-bodega');

        if (primeraComprobanteCompra) {
            const text = primeraComprobanteCompra.codigo + ' - ' + primeraComprobanteCompra.nombre;
            setComboValue(state.$comprobante, primeraComprobanteCompra.id, text);
        }

        if (primeraBodegaCompra && primeraBodegaCompra.length > 0) {
            const b = primeraBodegaCompra[0];
            setComboValue(state.$bodega, b.id, b.codigo + ' - ' + b.nombre);
        }

        if (!primeraBodegaCompra || !primeraBodegaCompra.length) {
            agregarToast('warning', 'Sin bodegas asignadas', '', true);
        }

        initComboResolucionCompra();
        $('#id_resolucion_compra').next('.select2-container').hide();
        $('#id_resolucion_compra').hide();

        setTimeout(() => {
            state.$proveedor.select2('open');
        }, 10);
    }

    function initComboResolucionCompra() {
        state.$resolucion = $('#id_resolucion_compra').select2({
            theme: 'bootstrap-5',
            delay: 250,
            language: LANG,
            ajax: {
                url: 'api/resoluciones/combo-resoluciones',
                headers,
                data: (params) => ({
                    q: params.term,
                    tipo_resoluciones: [4],
                    _type: 'query'
                }),
                dataType: 'json',
                processResults: (data) => ({ results: data.data })
            }
        });
        $('#id_resolucion_compra').next('.select2-container').hide();
        $('#id_resolucion_compra').hide();
    }

    function cargarFechasCompras() {
        const d = new Date();
        const p = (n) => String(n).padStart(2, '0');
        const valor = `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
        $('#fecha_manual_compra').val(valor);
    }

    function cargarPopoverCompra() {
        // Scoped a compras para no pisar popovers de ventas
        $('#compraFormaPago [data-toggle="popover"], #icon_info_retencion_compra').popover({
            trigger: 'hover',
            html: true,
            placement: 'top',
            container: 'body',
            customClass: 'popover-formas-pagos'
        });
    }

    // ------------------------------------------------------------------
    // Guardar
    // ------------------------------------------------------------------
    function getComprasPagos() {
        const data = [];
        filasPagos().forEach((fp) => {
            const valor = num($('#compra_forma_pago_' + fp.id).val());
            if (valor > 0) data.push({ id: fp.id, valor });
        });
        return data;
    }

    function getProductosCompra() {
        const data = [];
        state.lineas.forEach((l) => {
            if (!l.idProducto) return;
            leerLineaDom(l);
            if (!l.cantidad) return;

            let subtotal = l.cantidad * l.costo;
            if (ivaIncluidoCompras) subtotal -= l.ivaVal;

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

    function validateSaveCompra() {
        $('#total_faltante_compra_text, #total_faltante_compra').css('color', '#484848');
        if (state.guardando) return;

        const t = calcularTotales();
        const [ef, otros, ant] = totalFormasPago();

        if (t.total > 0 && round2(ef + otros + ant) >= round2(t.total)) {
            state.guardando = true;
            saveCompra();
        } else {
            $('#total_faltante_compra_text, #total_faltante_compra').css('color', 'red');
        }
    }

    function saveCompra() {
        $('#crearCapturaCompra').hide();
        $('#crearCapturaCompraDisabled').hide();
        $('#crearCapturaCompraLoading').show();

        const data = {
            pagos: getComprasPagos(),
            productos: getProductosCompra(),
            id_bodega: $('#id_bodega_compra').val(),
            id_proveedor: $('#id_proveedor_compra').val(),
            fecha_manual: $('#fecha_manual_compra').val(),
            documento_referencia: $('#documento_referencia_compra').val(),
            observacion: $('#observacion_compra').val(),
            modo: state.modoCaptura
        };

        if (state.modoCaptura === 'documento_soporte') {
            data.id_resolucion = $('#id_resolucion_compra').val();
        } else {
            data.id_comprobante = $('#id_comprobante_compra').val();
        }

        $('#compraFormaPago .v-pago').prop('disabled', true);

        $.ajax({
            url: base_url + 'compras',
            method: 'POST',
            data: JSON.stringify(data),
            headers,
            dataType: 'json'
        }).done((res) => {
            state.guardando = false;
            $('#crearCapturaCompraLoading').hide();

            if (!res.success) {
                mostrarError({ responseJSON: { message: res.message } }, 'Creación errada');
                refrescar();
                return;
            }

            agregarToast('exito', 'Creación exitosa', 'Compra creada con exito!', true);

            if (res.impresion) window.open('/compras-print/' + res.impresion, '_blank');

            $('#iniciarCapturaCompra').hide();
            $('#iniciarCapturaCompraLoading').hide();
            $('#toggleDocumentoSoporteCompra').hide();
            $('#documento_referencia_compra').val('');

            vaciarGrilla();
            vaciarPagos();

            setTimeout(() => {
                $('#id_proveedor_compra').focus();
                state.$proveedor.select2('open');
            }, 10);

            loadAnticiposProveedorCompras();
            cargarFechasCompras();
            refrescar();
        }).fail((err) => {
            state.guardando = false;
            $('#crearCapturaCompraLoading').hide();
            refrescar();
            mostrarError(err, 'Creación errada');
        });
    }

    // ------------------------------------------------------------------
    // Cancelar / iniciar
    // ------------------------------------------------------------------
    function cancelarCompra() {
        vaciarGrilla();
        vaciarPagos();
        state.capturando = false;

        $('#id_bodega_compra').prop('disabled', false);
        $('#id_comprobante_compra').prop('disabled', false);

        $('#iniciarCapturaCompra').show();
        $('#agregarCompraProducto').hide();
        $('#crearCapturaCompra').hide();
        $('#cancelarCapturaCompra').hide();
        $('#crearCapturaCompraDisabled').hide();
        $('#input-anticipos-compra').hide();
        $('#toggleDocumentoSoporteCompra').show();

        refrescar();
        cargarFechasCompras();

        setTimeout(() => state.$proveedor.select2('open'), 10);
    }

    function iniciarCaptura() {
        const form = document.querySelector('#compraFilterForm');

        $('#id_comprobante_compra').removeClass('is-invalid');
        $('#id_resolucion_compra').removeClass('is-invalid').next('.select2-container').find('.select2-selection').removeClass('is-invalid');

        if (state.modoCaptura === 'documento_soporte') {
            if (!$('#id_resolucion_compra').val()) {
                $('#id_resolucion_compra').addClass('is-invalid');
                $('#id_resolucion_compra').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                agregarToast('warning', 'Resolución requerida', 'Debes seleccionar una resolución para continuar', true);
                $('#id_resolucion_compra').select2('open');
                return;
            }
        } else {
            if (!$('#id_comprobante_compra').val()) {
                $('#id_comprobante_compra').addClass('is-invalid');
                $('#id_comprobante_compra').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                agregarToast('warning', 'Comprobante requerido', 'Debes seleccionar un comprobante para continuar', true);
                $('#id_comprobante_compra').select2('open');
                return;
            }
        }

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        state.capturando = true;
        $('#iniciarCapturaCompra').hide();
        $('#agregarCompraProducto').show();
        $('#crearCapturaCompra').hide();
        $('#cancelarCapturaCompra').show();
        $('#cambio-totals-compra').hide();
        $('#crearCapturaCompraDisabled').show();
        $('#toggleDocumentoSoporteCompra').hide();

        addRowProductoCompra();
    }

    // ------------------------------------------------------------------
    // Modales de NIT
    // ------------------------------------------------------------------
    function openModalNewNitCompra() {
        abrirNitGeneral({
            modo: 'create',
            captura: 'compra',
            onSave: function (dataNit) {
                setComboValue(state.$proveedor, dataNit.id, dataNit.numero_documento + ' - ' + dataNit.nombre_completo);
                procesarCambioProveedorCompra(dataNit);
                agregarToast('exito', 'Creación exitosa', 'Cliente creado con éxito!', true);
                document.getElementById('iniciarCapturaCompra').click();
            }
        });
    }

    function openModalViewNitCompra() {
        const data = $('#id_proveedor_compra').select2('data');
        if (!data.length) {
            agregarToast('warning', 'Sin cliente', 'Selecciona un cliente primero');
            return;
        }
        abrirNitDetalle(data[0].id);
    }

    function openModalEditNitCompra() {
        const data = $('#id_proveedor_compra').select2('data');
        if (!data.length) {
            agregarToast('warning', 'Sin cliente', 'Selecciona un cliente primero');
            return;
        }
        abrirNitGeneral({
            modo: 'edit',
            id: data[0].id,
            onUpdate: function (dataNit) {
                const $sel = $('#id_proveedor_compra');
                const texto = dataNit.numero_documento + ' - ' + dataNit.nombre_completo;
                $sel.find("option[value='" + dataNit.id + "']").remove();
                $sel.append(new Option(texto, dataNit.id, true, true));
                $sel.val(dataNit.id).trigger('change');

                procesarCambioProveedorCompra(dataNit);
                agregarToast('exito', 'Edición exitosa', 'Cliente actualizado con éxito!', true);
            }
        });
    }

    // ------------------------------------------------------------------
    // Validar factura
    // ------------------------------------------------------------------
    let validarFacturaReq = null;

    function buscarFacturaCompra(event) {
        const botonPresionado = event.key && event.key.length === 1 ? event.key : '';
        let documento = $('#documento_referencia_compra').val() + botonPresionado;

        if (event.keyCode === 13 && documento) {
            document.getElementById('iniciarCapturaCompra').click();
            return;
        }

        if (validarFacturaReq) validarFacturaReq.abort();
        if (event.key === 'Backspace') documento = documento.slice(0, -1);
        if (!documento) return;

        $('#documento_referencia_compra_loading').show();
        $('#documento_referencia_compra').removeClass('is-invalid is-valid');

        setTimeout(() => {
            validarFacturaReq = api('existe-factura', 'GET', {
                id_comprobante: $('#id_comprobante_compra').val(),
                documento_referencia: documento
            }).done((res) => {
                validarFacturaReq = null;
                $('#documento_referencia_compra_loading').hide();
                if (!res.data) {
                    $('#documento_referencia_compra').removeClass('is-invalid').addClass('is-valid');
                } else {
                    $('#documento_referencia_compra').removeClass('is-valid').addClass('is-invalid');
                    $('#error_documento_referencia_compra').text('La factura No ' + documento + ' ya existe!');
                }
            }).fail(() => {
                validarFacturaReq = null;
                $('#documento_referencia_compra_loading').hide();
            });
        }, 100);
    }

    // ------------------------------------------------------------------
    // Eventos (se registran una sola vez, todos delegados)
    // ------------------------------------------------------------------
    function registrarEventosCompras() {
        if (state.inited) return;
        state.inited = true;

        const $doc = $(document);

        // Formato de moneda: un solo handler para toda la grilla y los pagos (scopeado a compras)
        $('#compraTable, #compraFormaPago')
            .on('keyup.compra', "input[data-type='currency']", function (e) {
                const k = e.key;
                if ((k >= '0' && k <= '9') || k === 'Backspace' || k === 'Delete' || k === ',' || k === '.') {
                    formatCurrency($(this));
                }
            })
            .on('blur.compra', "input[data-type='currency']", function () {
                formatCurrency($(this), 'blur');
            });

        // --- Grilla de productos (SCOPEADO a #compraTable) ---
        $doc.on('click.compra', '#compraTable .v-borrar', function () {
            deleteProductoCompra(Number(this.dataset.id));
        });

        $doc.on('change.compra', '#compraTable .compra_producto', function () {
            onProductoChange(Number(this.dataset.id));
        });

        $doc.on('focusin.compra', '#compraTable .v-cantidad, #compraTable .v-costo, #compraTable .v-dpct, #compraTable .v-dval',
            function () { seleccionarConDelay(this); });

        $doc.on('focusout.compra', '#compraTable .v-cantidad, #compraTable .v-costo, #compraTable .v-dval, #compraTable .v-concepto',
            function () { recalcularLinea(Number(this.dataset.id), 'val'); });

        $doc.on('focusout.compra', '#compraTable .v-dpct', function () {
            recalcularLinea(Number(this.dataset.id), 'pct');
        });

        $doc.on('keydown.compra', '#compraTable .v-cantidad', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); confirmarCantidad(Number(this.dataset.id)); }
        });

        $doc.on('keydown.compra', '#compraTable .v-costo', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                recalcularLinea(Number(this.dataset.id), 'val');
                addRowProductoCompra();
            }
        });

        $doc.on('keydown.compra', '#compraTable .v-dpct', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const id = Number(this.dataset.id);
                recalcularLinea(id, 'pct');
                setTimeout(() => {
                    const $v = $('#compra_descuento_valor_' + id);
                    $v.focus();
                    $v.select();
                }, 10);
            }
        });

        $doc.on('keydown.compra', '#compraTable .v-dval', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                recalcularLinea(Number(this.dataset.id), 'val');
                addRowProductoCompra();
            }
        });

        // --- Buscador de productos: Numpad0 + Enter abre formas de pago ---
        $doc.on('keydown.compra', '.custom-compra_producto .select2-search__field', function (event) {
            if (state.guardando) return;

            if (event.code === 'Numpad0' && !this.value.length) {
                state.abrirFormasPago = true;
            } else if (event.key === 'Enter') {
                const t = calcularTotales();
                if (t.total > 0 && state.abrirFormasPago) {
                    state.abrirFormasPago = false;
                    $('.compra_producto').select2('close');
                    focusFormaPagoCompra(ID_FORMA_PAGO_EFECTIVO);
                }
            } else {
                state.abrirFormasPago = false;
            }
        });

        // --- Buscador de proveedores: Enter => crear NIT ---
        $doc.on('keydown.compra', '.custom-id_proveedor_compra .select2-search__field', function (event) {
            if (event.key === 'Enter') {
                const documento = this.value;
                openModalNewNitCompra();
                state.$proveedor.select2('close');
                $('#numero_documento_compra_nit').val(documento);
            }
        });

        // --- Formas de pago (SCOPEADO a #compraFormaPago) ---
        $doc.on('focusin.compra', '#compraFormaPago .v-pago', function () {
            const m = metaPago($(this));
            focusFormaPagoCompra(m.id, m.anticipo, m.idCuenta);
        });

        $doc.on('focusout.compra', '#compraFormaPago .v-pago', function () {
            const m = metaPago($(this));
            onPagoCommit(m.id, m.anticipo, m.idCuenta, false);
        });

        $doc.on('keydown.compra', '#compraFormaPago .v-pago', function (e) {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            const m = metaPago($(this));
            onPagoCommit(m.id, m.anticipo, m.idCuenta, true);
        });

        // --- Encabezado ---
        $doc.on('change.compra', '#id_vendedor_compra', refrescar);

        $doc.on('select2:close.compra', '#id_proveedor_compra', function () {
            const data = $(this).select2('data');
            if (data.length) {
                procesarCambioProveedorCompra(data[0]);
                setTimeout(() => {
                    $('#documento_referencia_compra').focus().select();
                }, 10);
                loadAnticiposProveedorCompras();
            }
        });

        $doc.on('keydown.compra', '#fecha_manual_compra', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                setTimeout(() => {
                    $('#documento_referencia_compra').focus().select();
                }, 10);
            }
        });

        $doc.on('keydown.compra', '#documento_referencia_compra', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('iniciarCapturaCompra').click();
            }
        });

        $doc.on('keydown.compra', '#documento_referencia_compra', function (e) {
            // Validación en vivo del número de factura (reemplaza el onkeydown inline)
            buscarFacturaCompra(e);

            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('iniciarCapturaCompra').click();
            }
        });

        // --- Botones ---
        $doc.on('click.compra', '#toggleDocumentoSoporteCompra', toggleModoCapturaCompra);
        $doc.on('click.compra', '#iniciarCapturaCompra', iniciarCaptura);
        $doc.on('click.compra', '#agregarCompraProducto', addRowProductoCompra);
        $doc.on('click.compra', '#cancelarCapturaCompra', cancelarCompra);
        $doc.on('click.compra', '#crearCapturaCompra', validateSaveCompra);
    }

    function resetComprasState() {
        try { $(document).off('.compra'); } catch (e) { /* noop */ }
        try { $('#compraTable, #compraFormaPago').off('.compra'); } catch (e) { /* noop */ }

        if (state.tablaPagos) {
            try { state.tablaPagos.destroy(); } catch (e) { /* noop */ }
            state.tablaPagos = null;
        }
        $('#compraFormaPago').empty();

        ['#id_proveedor_compra', '#id_vendedor_compra', '#id_comprobante_compra', '#id_resolucion_compra', '#id_bodega_compra']
            .forEach((sel) => {
                const $el = $(sel);
                if ($el.data('select2')) {
                    try { $el.select2('destroy'); } catch (e) { /* noop */ }
                }
                try { $el.empty(); } catch (e) { /* noop */ }
            });

        $('#compraTable .compra_producto').each(function () {
            if ($(this).data('select2')) {
                try { $(this).select2('destroy'); } catch (e) { /* noop */ }
            }
        });
        $('#compraTable tbody').empty();

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
        state.modoCaptura = 'compra';
        state.inited = false;

        countUpTotal = null;
        ultimoTotalAnimado = null;

        $('#iniciarCapturaCompra').show();
        $('#agregarCompraProducto').hide();
        $('#crearCapturaCompra').hide();
        $('#cancelarCapturaCompra').hide();
        $('#crearCapturaCompraDisabled').hide();
        $('#crearCapturaCompraLoading').hide();
        $('#iniciarCapturaCompraLoading').hide();
        $('#toggleDocumentoSoporteCompra').show();
        $('#input-anticipos-compra').hide();

        $('#id_bodega_compra').prop('disabled', false);
        $('#id_comprobante_compra').prop('disabled', false);
    }

    // ------------------------------------------------------------------
    // Init
    // ------------------------------------------------------------------
    function compraInit() {
        resetComprasState();

        inyectarEstilosCompras();
        registrarEventosCompras();
        aplicarVisibilidadColumnasCompras();
        iniciarTablaPagosCompras();
        cargarFechasCompras();
        cargarCombosCompra();
        cargarPopoverCompra();
        loadFormasPagoCompra();

        $('.water').hide();
    }

    // ------------------------------------------------------------------
    // API pública
    // ------------------------------------------------------------------
    Object.assign(window, {
        compraInit,
        cancelarCompra,
        changeRetencionCompras,
        openModalNewNitCompra,
        openModalViewNitCompra,
        openModalEditNitCompra,
        loadFormasPagoCompra,
        clearFormasPagoCompra,
        loadAnticiposProveedorCompras,
        toggleModoCapturaCompra
    });

})(window, jQuery);