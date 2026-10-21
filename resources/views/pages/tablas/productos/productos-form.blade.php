<style>
/* =====================================================================
   PRODUCTOS · FORMULARIO PRO (claro)
   ===================================================================== */
.pf-wrap {
    --pf-primary: #596cff;
    --pf-primary-soft: #eef0ff;
    --pf-success: #2dce89;
    --pf-danger: #f5365c;
    --pf-ink: #1f2937;
    --pf-muted: #6b7280;
    --pf-line: #e5e7eb;
    --pf-bg: #f6f7fb;
    --pf-radius: 14px;
    --pf-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 4px 14px rgba(16, 24, 40, .06);
    color: var(--pf-ink);
}

/* ---------- Encabezado ---------- */
.pf-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
    padding: 18px 22px;
    border-radius: var(--pf-radius);
    background: linear-gradient(135deg, #596cff 0%, #7a5cff 55%, #9b6bff 100%);
    color: #fff;
    box-shadow: 0 10px 28px rgba(89, 108, 255, .28);
    transition: background .3s ease;
}
.pf-hero h4 { margin: 0; color: #fff; font-size: 18px; font-weight: 700; letter-spacing: .2px; }
.pf-hero p { margin: 2px 0 0; font-size: 12.5px; opacity: .85; }
.pf-hero .pf-mode {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 7px 14px; border-radius: 30px;
    background: rgba(255, 255, 255, .18);
    backdrop-filter: blur(6px);
    font-size: 12.5px; font-weight: 600;
}
.pf-hero .pf-mode i { font-size: 11px; }

/* ---------- Tarjetas ---------- */
.pf-card {
    margin-bottom: 16px;
    border: 1px solid var(--pf-line);
    border-radius: var(--pf-radius);
    background: #fff;
    box-shadow: var(--pf-shadow);
    overflow: hidden;
}
.pf-card-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--pf-line);
    background: #fff;
    font-size: 13.5px; font-weight: 700; color: var(--pf-ink);
}
.pf-card-header .pf-ttl { display: inline-flex; align-items: center; gap: 10px; }
.pf-card-header .pf-ico {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 9px;
    background: var(--pf-primary-soft); color: var(--pf-primary); font-size: 13px;
}
.pf-card-header small { font-weight: 500; color: var(--pf-muted); }
.pf-card-body { padding: 20px; }

.pf-wrap .form-control-label {
    margin-bottom: 4px;
    color: var(--pf-muted);
    font-size: 11.5px; font-weight: 700;
    letter-spacing: .4px; text-transform: uppercase;
}
.pf-wrap .form-control,
.pf-wrap .form-select {
    border-color: #d9dde5;
    border-radius: 10px;
    min-height: 38px;
    transition: border-color .15s, box-shadow .15s;
}
.pf-wrap .form-control:focus,
.pf-wrap .form-select:focus {
    border-color: var(--pf-primary);
    box-shadow: 0 0 0 4px rgba(89, 108, 255, .14);
}
.pf-wrap .form-control:disabled { background: #f3f4f6; }

/* ---------- Selector de tipo ---------- */
.pf-types { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; }
.pf-type {
    position: relative;
    display: flex; align-items: center; gap: 12px;
    margin: 0; padding: 14px 16px;
    border: 1.5px solid var(--pf-line);
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
    transition: all .18s ease;
}
.pf-type:hover { border-color: #b9c1ff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(89, 108, 255, .12); }
.pf-type.active {
    border-color: var(--pf-primary);
    background: var(--pf-primary-soft);
    box-shadow: 0 0 0 3px rgba(89, 108, 255, .12);
}
.pf-type input[type="radio"] { position: absolute; opacity: 0; pointer-events: none; }
.pf-type i {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 10px;
    background: #f1f3f9; color: var(--pf-muted); font-size: 15px;
    transition: all .18s;
}
.pf-type.active i { background: var(--pf-primary); color: #fff; }
.pf-type .tn { font-size: 13px; font-weight: 700; color: var(--pf-ink); line-height: 1.2; }

/* ---------- Imagen ---------- */
.pf-img {
    position: relative;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 170px; padding: 14px;
    border: 2px dashed #cfd5ff; border-radius: 12px;
    background: var(--pf-bg);
    cursor: pointer; transition: all .18s;
}
.pf-img:hover { border-color: var(--pf-primary); background: var(--pf-primary-soft); }
.pf-img img { max-width: 100%; max-height: 140px; border-radius: 10px; object-fit: contain; }
.pf-img .pf-img-hint { margin-top: 8px; font-size: 11.5px; color: var(--pf-muted); }

/* ---------- Estado (switch) ---------- */
.pf-status {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 14px; border: 1px solid var(--pf-line); border-radius: 12px; background: #fff;
}
.pf-status .st-t { font-size: 13px; font-weight: 700; }
.pf-status .st-s { font-size: 11.5px; color: var(--pf-muted); }
.pf-status .form-check-input { width: 2.6em; height: 1.4em; cursor: pointer; }
.pf-status .form-check-input:checked { background-color: var(--pf-success); border-color: var(--pf-success); }

/* ---------- Precios: flujo ---------- */
.pf-flow { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
.pf-flow .fld { flex: 1 1 140px; min-width: 0; }
.pf-flow .arr {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; margin-bottom: 4px; border-radius: 50%;
    background: var(--pf-primary-soft); color: var(--pf-primary); font-weight: 800;
}
.pf-input-addon { position: relative; }
.pf-input-addon .pf-pre {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: var(--pf-muted); font-size: 12px; font-weight: 700; pointer-events: none;
}
.pf-input-addon .form-control { padding-left: 26px; }
.pf-divider { height: 1px; margin: 18px 0; background: var(--pf-line); }
.pf-sub { margin: 0 0 10px; font-size: 11.5px; font-weight: 800; letter-spacing: .6px; text-transform: uppercase; color: var(--pf-muted); }

/* ---------- Resumen en vivo (CLARO) ---------- */
.pf-aside { position: sticky; top: 70px; }
.pf-summary {
    padding: 18px; border-radius: var(--pf-radius);
    background: #ffffff;
    border: 1px solid var(--pf-line);
    box-shadow: var(--pf-shadow);
    color: var(--pf-ink);
}
.pf-summary h6 {
    margin: 0 0 12px;
    color: var(--pf-muted);
    font-size: 11px; font-weight: 800;
    letter-spacing: .8px; text-transform: uppercase;
}
.pf-summary h6 i { color: var(--pf-primary); }
.pf-sum-row {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: 7px 0;
    border-bottom: 1px dashed var(--pf-line);
    font-size: 13px;
}
.pf-sum-row span:first-child { color: var(--pf-muted); }
.pf-sum-row b { font-variant-numeric: tabular-nums; color: var(--pf-ink); }
.pf-sum-total {
    display: flex; justify-content: space-between; align-items: baseline;
    margin-top: 12px;
}
.pf-sum-total .lbl { color: var(--pf-muted); font-size: 12px; }
.pf-sum-total .val {
    font-size: 24px; font-weight: 800; letter-spacing: -.5px;
    color: var(--pf-primary);
}
.pf-margin {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 10px; padding: 5px 11px; border-radius: 20px;
    background: rgba(45, 206, 137, .14);
    color: #12805c;
    font-size: 12px; font-weight: 700;
}
.pf-margin.neg { background: rgba(245, 54, 92, .12); color: #b91c3c; }
.pf-bar {
    height: 6px; margin-top: 12px; border-radius: 6px;
    background: #eef0ff; overflow: hidden;
}
.pf-bar > span {
    display: block; height: 100%; width: 0; border-radius: 6px;
    background: linear-gradient(90deg, #2dce89, #4ade80);
    transition: width .35s ease;
}

/* ---------- Variantes ---------- */
.pf-seg { display: inline-flex; padding: 3px; border-radius: 10px; background: #f1f3f9; }
.pf-seg .form-check { margin: 0; padding: 0; min-height: 0; }
.pf-seg .form-check-input { position: absolute; opacity: 0; }
.pf-seg .form-check-label {
    display: block; margin: 0; padding: 6px 14px; border-radius: 8px;
    font-size: 12px; font-weight: 700; color: var(--pf-muted); cursor: pointer; transition: all .15s;
}
.pf-seg .form-check-input:checked + .form-check-label { background: #fff; color: var(--pf-primary); box-shadow: 0 1px 4px rgba(16, 24, 40, .12); }
.pf-seg .form-check-input:disabled + .form-check-label { opacity: .55; cursor: not-allowed; }

#variantes-contenedor { display: flex; flex-wrap: wrap; gap: 8px; }
#variantes-contenedor > .col { flex: 0 0 auto; width: auto; padding: 0; text-align: left !important; }
#variantes-contenedor .btn {
    border-radius: 30px; border: 1px solid #d9dde5; background: var(--pf-bg);
    padding: 6px 14px; font-size: 12.5px; font-weight: 600; color: var(--pf-ink); box-shadow: none;
}
#variantes-contenedor .btn:hover { border-color: var(--pf-primary); background: var(--pf-primary-soft); }

.pf-var-chip {
    display: inline-flex; align-items: center; gap: 5px; margin: 2px; padding: 4px 10px;
    border: 1px solid #dee2e6; border-radius: 20px; background: #f8f9fa; color: #495057; font-size: 12px; cursor: pointer;
}
.pf-var-chip.off { color: #6c757d; text-decoration: line-through; }

/* ---------- Tablas ---------- */
.pf-wrap table.table { margin-bottom: 0; font-size: 13px; }
.pf-wrap table.table thead th {
    padding: 11px 12px; border-bottom: 1px solid var(--pf-line) !important;
    background: var(--pf-bg) !important;
    color: var(--pf-muted); font-size: 11px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase;
}
.pf-wrap table.table tbody td { vertical-align: middle; padding: 8px 12px; border-color: #f0f1f5; }
.pf-wrap .dataTables_wrapper .dataTables_empty { padding: 26px; color: var(--pf-muted); }

/* ---------- Bodegas ---------- */
.pf-bodega-row {
    display: flex; align-items: center; gap: 10px;
    margin-bottom: 10px; padding: 10px 12px;
    border: 1px solid var(--pf-line); border-radius: 12px; background: var(--pf-bg);
}
.pf-bodega-row select { flex: 2; min-width: 0; }
.pf-bodega-row input { flex: 1; min-width: 0; }
.pf-bodega-row .btn-del { flex: 0 0 auto; border-radius: 9px; }

/* ---------- Combo total ---------- */
.pf-total-box {
    display: inline-flex; align-items: center; gap: 12px;
    padding: 10px 18px; border-radius: 12px;
    background: var(--pf-primary-soft); color: var(--pf-primary); font-weight: 700;
}
.pf-total-box #total_combo { font-size: 20px; font-weight: 800; }

/* ---------- Botones ---------- */
.pf-wrap .btn { border-radius: 10px; font-weight: 600; }
.pf-wrap .btn-primary { background: var(--pf-primary); border-color: var(--pf-primary); box-shadow: 0 6px 14px rgba(89, 108, 255, .3); }
.pf-wrap .btn-primary:hover { filter: brightness(1.06); }

/* ---------- Barra de acciones (NO sticky) ---------- */
.pf-actions {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    margin-top: 8px; padding: 14px 18px;
    border: 1px solid var(--pf-line); border-radius: var(--pf-radius);
    background: #fff;
    box-shadow: var(--pf-shadow);
}
.pf-actions .pf-hint { font-size: 12.5px; color: var(--pf-muted); }
.pf-actions .pf-hint i { color: var(--pf-primary); margin-right: 6px; }

/* ---------- Validación ---------- */
.pf-wrap .was-validated .form-control:invalid,
.pf-wrap .form-control.is-invalid { border-color: var(--pf-danger); }

/* ---------- Responsive ---------- */
@media (max-width: 991.98px) {
    .pf-aside { position: static; }
}
@media (max-width: 575.98px) {
    .pf-hero { padding: 14px 16px; }
    .pf-card-body { padding: 14px; }
    .pf-types { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .pf-type { padding: 10px; gap: 8px; }
    .pf-type i { width: 32px; height: 32px; }
    .pf-flow .arr { display: none; }
    .pf-flow .fld { flex-basis: calc(50% - 10px); }
    .pf-bodega-row { flex-wrap: wrap; }
    .pf-bodega-row select { flex-basis: 100%; }
}
</style>

<div class="pf-wrap">
    <form id="newProductoForm" class="needs-invalidation" novalidate>
        <input type="hidden" name="id_producto_edit" id="id_producto_edit">

        {{-- ===== HERO ===== --}}
        <div class="pf-hero" id="pf-hero">
            <div>
                <h4><i class="fas fa-cube"></i>&nbsp; Ficha de producto</h4>
                <p>Define el tipo, los precios y el inventario en un solo lugar.</p>
            </div>
            <span class="pf-mode"><i class="fas fa-circle"></i>&nbsp;<span id="form-mode-label">Nuevo producto</span></span>
        </div>

        {{-- ===== TIPO ===== --}}
        <div class="pf-card">
            <div class="pf-card-header">
                <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-shapes"></i></span> Tipo de ítem</span>
                <small>Selecciona cómo se comportará en facturación e inventario</small>
            </div>
            <div class="pf-card-body">
                <div class="pf-types">
                    <label class="pf-type active" for="tipo_producto_producto">
                        <input type="radio" name="options-outlined" id="tipo_producto_producto" onchange="changeProducType()" checked>
                        <i class="fas fa-box"></i><span class="tn">Producto</span>
                    </label>
                    <label class="pf-type" for="tipo_producto_servicio">
                        <input type="radio" name="options-outlined" id="tipo_producto_servicio" onchange="changeProducType()">
                        <i class="fas fa-concierge-bell"></i><span class="tn">Servicio</span>
                    </label>
                    <label class="pf-type" for="tipo_producto_combo">
                        <input type="radio" name="options-outlined" id="tipo_producto_combo" onchange="changeProducType()">
                        <i class="fas fa-layer-group"></i><span class="tn">Combo</span>
                    </label>
                    @can('parqueadero productos')
                    <label class="pf-type" for="tipo_producto_parqueadero">
                        <input type="radio" name="options-outlined" id="tipo_producto_parqueadero" onchange="changeProducType()">
                        <i class="fas fa-parking"></i><span class="tn">Parqueadero</span>
                    </label>
                    @endcan
                    <label class="pf-type" for="tipo_producto_documento">
                        <input type="radio" name="options-outlined" id="tipo_producto_documento" onchange="changeProducType()">
                        <i class="fas fa-file-invoice"></i><span class="tn">Doc. Soporte</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="row g-3">
            {{-- ===================== COLUMNA PRINCIPAL ===================== --}}
            <div class="col-12 col-lg-8">

                {{-- ===== INFO BÁSICA ===== --}}
                <div class="pf-card">
                    <div class="pf-card-header">
                        <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-info-circle"></i></span> Información básica</span>
                    </div>
                    <div class="pf-card-body">
                        <div class="row g-3">
                            <div class="col-12 col-sm-4">
                                <label class="form-control-label">Código</label>
                                <input type="text" class="form-control form-control-sm" name="codigo_producto" id="codigo_producto" onfocusout="addCodigoProducto()" onkeypress="keyPressCodigoProducto(event)" onfocus="focusCodigoProducto()" placeholder="Ej: PRD-001" required>
                                <div class="invalid-feedback">Requerido</div>
                            </div>
                            <div class="col-12 col-sm-8">
                                <label class="form-control-label">Nombre</label>
                                <input type="text" class="form-control form-control-sm" name="nombre_producto" id="nombre_producto" onfocusout="addNombreProducto()" onkeypress="keyPressNombreProducto(event)" onfocus="focusNombreProducto()" placeholder="Nombre del producto o servicio" required>
                                <div class="invalid-feedback">Requerido</div>
                            </div>
                            <div class="col-12">
                                <label class="form-control-label">Familia</label>
                                <select name="id_familia_producto" id="id_familia_producto" class="form-control form-control-sm" required></select>
                                <div class="invalid-feedback">Requerido</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== PRECIOS ===== --}}
                <div class="pf-card">
                    <div class="pf-card-header">
                        <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-dollar-sign"></i></span> Precios y rentabilidad</span>
                        <small>Costo + Utilidad = Venta</small>
                    </div>
                    <div class="pf-card-body">
                        <p class="pf-sub">Cálculo de utilidad</p>
                        <div class="pf-flow">
                            <div class="fld" id="div-precio_inicial">
                                <label class="form-control-label">Costo compra</label>
                                <div class="pf-input-addon"><span class="pf-pre">$</span>
                                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="precio_inicial" id="precio_inicial" onfocus="this.select();" onfocusout="addPrecioInicialProducto()" onkeypress="changeCostoCompra(event)" value="0" required>
                                </div>
                            </div>
                            <div class="arr">+</div>
                            <div class="fld" id="div-porcentaje_utilidad">
                                <label class="form-control-label">% Utilidad</label>
                                <div class="pf-input-addon"><span class="pf-pre">%</span>
                                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="porcentaje_utilidad" id="porcentaje_utilidad" onfocusout="addPorcentajeUtilidadProducto()" onkeypress="changePorcentajeUtilidad(event)" value="0" required>
                                </div>
                            </div>
                            <div class="arr">=</div>
                            <div class="fld" id="div-valor_utilidad">
                                <label class="form-control-label">$ Utilidad</label>
                                <div class="pf-input-addon"><span class="pf-pre">$</span>
                                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="valor_utilidad" id="valor_utilidad" onfocusout="addValorUtilidadProducto()" onkeypress="changeValorUtilidad(event)" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="pf-divider"></div>

                        <p class="pf-sub">Precios de venta</p>
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <label class="form-control-label">Precio venta</label>
                                <div class="pf-input-addon"><span class="pf-pre">$</span>
                                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="precio_producto" id="precio_producto" onfocusout="addPrecioProducto()" onkeypress="changeValorVenta(event)" value="0">
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-control-label">Precio venta 2</label>
                                <div class="pf-input-addon"><span class="pf-pre">$</span>
                                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" name="precio_producto_2" id="precio_producto_2" value="0">
                                </div>
                            </div>
                            <div class="col-6 col-md-4" id="div-precio_minimo">
                                <label class="form-control-label">Precio mínimo</label>
                                <div class="pf-input-addon"><span class="pf-pre">$</span>
                                    <input type="text" data-type="currency" class="form-control form-control-sm text-align-right" onfocusout="addPrecioMinimoProducto()" name="precio_minimo" id="precio_minimo" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-6 col-md-3" id="input-iva-porcentaje" style="display: none;">
                                <label class="form-control-label">% IVA</label>
                                <input type="text" class="form-control form-control-sm text-align-right" name="porcentaje_iva" id="porcentaje_iva" value="0" disabled>
                            </div>
                            <div class="col-6 col-md-3" id="input-iva-valor" style="display: none;">
                                <label class="form-control-label">$ IVA</label>
                                <input type="text" class="form-control form-control-sm text-align-right" name="valor_iva" id="valor_iva" value="0" disabled>
                            </div>
                            <div class="col-6 col-md-3" id="input-impuestos-porcentaje" style="display: none;">
                                <label class="form-control-label" id="text_otros_impuestos_porcentaje"></label>
                                <input type="text" class="form-control form-control-sm text-align-right" name="porcentaje_otros_impuestos" id="porcentaje_otros_impuestos" value="0" disabled>
                            </div>
                            <div class="col-6 col-md-3" id="input-impuestos-valor" style="display: none;">
                                <label class="form-control-label" id="text_otros_impuestos_valor"></label>
                                <input type="text" class="form-control form-control-sm text-align-right" name="valor_otros_impuestos" id="valor_otros_impuestos" value="0" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== CONFIG PRODUCTO / DOC SOPORTE ===== --}}
                <div id="config-producto-block" style="display: none;">

                    {{-- Variantes --}}
                    <div class="pf-card">
                        <div class="pf-card-header">
                            <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-random"></i></span> Variantes</span>
                            <div class="pf-seg" id="item-maneja-variante">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="producto_variantes" id="producto_variantes1" checked>
                                    <label class="form-check-label" for="producto_variantes1">Sin variantes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="producto_variantes" id="producto_variantes2">
                                    <label class="form-check-label" for="producto_variantes2">Con variantes</label>
                                </div>
                            </div>
                        </div>
                        <div class="pf-card-body" id="producto-variantes" style="display: none;">
                            <div id="variantes-contenedor" class="mb-3"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-modal-variantes" onclick="agregarVarianteProducto()">
                                <i class="fas fa-sliders-h"></i>&nbsp; Configurar variantes
                            </button>

                            <div id="contenedor-variantes-generales" style="display: none; margin-top: 16px;">
                                <div class="table-responsive" style="border: 1px solid var(--pf-line); border-radius: 12px;">
                                    <table id="productosVariantesTable" class="table table-sm" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Variantes</th>
                                                <th>Código</th>
                                                <th>Costo</th>
                                                <th>Precio</th>
                                                <th>Mínimo</th>
                                                <th>Bodegas</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Inventario --}}
                    <div class="pf-card" id="producto-inventario" style="display: none;">
                        <div class="pf-card-header">
                            <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-warehouse"></i></span> Inventario por bodega</span>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="agregarFilaBodegaInline()">
                                <i class="fas fa-plus-circle"></i>&nbsp; Añadir bodega
                            </button>
                        </div>
                        <div class="pf-card-body">
                            <div id="bodegas-contenedor"></div>
                        </div>
                    </div>
                </div>

                {{-- ===== COMBO ===== --}}
                <div class="pf-card" id="seccion-combo" style="display: none;">
                    <div class="pf-card-header">
                        <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-layer-group"></i></span> Productos del combo</span>
                    </div>
                    <div class="pf-card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-5">
                                <label class="form-control-label">Producto</label>
                                <select id="id_producto_combo" class="form-control form-control-sm"></select>
                            </div>
                            <div class="col-4 col-md-2">
                                <label class="form-control-label">Cantidad</label>
                                <input type="number" class="form-control form-control-sm" id="cantidad_item_combo" value="1" min="1">
                            </div>
                            <div class="col-4 col-md-2">
                                <label class="form-control-label">Costo</label>
                                <input type="number" class="form-control form-control-sm" id="precio_item_combo" value="0" step="0.01">
                            </div>
                            <div class="col-4 col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-sm btn-success w-100" id="btn-agregar-item-combo">
                                    <i class="fas fa-plus-circle"></i>&nbsp; Agregar
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive mt-3" style="border: 1px solid var(--pf-line); border-radius: 12px;">
                            <table id="itemsComboTable" class="table table-sm" width="100%">
                                <thead>
                                    <tr><th>Producto</th><th>Código</th><th>Cant.</th><th>Costo</th><th>Subtotal</th><th>—</th></tr>
                                </thead>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <div class="pf-total-box">Total combo <span id="total_combo">$ 0.00</span></div>
                        </div>
                    </div>
                </div>

                {{-- ===== PARQUEADERO ===== --}}
                <div class="pf-card" id="seccion-parqueadero" style="display: none;">
                    <div class="pf-card-header">
                        <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-parking"></i></span> Configuración parqueadero</span>
                    </div>
                    <div class="pf-card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-control-label">Formato tiempo</label>
                                <select id="tipo_tiempo_producto" class="form-control form-control-sm">
                                    <option value="1">Hora</option>
                                    <option value="2">Día</option>
                                    <option value="3">Mes</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-control-label">Tipo vehículo</label>
                                <select id="tipo_vehiculo_producto" class="form-control form-control-sm">
                                    <option value="1">CARRO</option>
                                    <option value="2">MOTO</option>
                                    <option value="3">OTROS</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end" id="div-fraccion_hora">
                                <div class="pf-status w-100">
                                    <div>
                                        <div class="st-t">Fracción de tiempo</div>
                                        <div class="st-s">Cobrar por fracción</div>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="fraccion_hora">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== COLUMNA LATERAL ===================== --}}
            <div class="col-12 col-lg-4">
                <div class="pf-aside">

                    {{-- Imagen + Estado --}}
                    <div class="pf-card">
                        <div class="pf-card-header">
                            <span class="pf-ttl"><span class="pf-ico"><i class="fas fa-image"></i></span> Imagen</span>
                        </div>
                        <div class="pf-card-body">
                            <div class="pf-img" onclick="document.getElementById('newImagenProducto').click();">
                                <img id="default_produc_img" src="/img/add_product_img.png" style="max-height: 90px;" alt="Subir">
                                <img id="new_produc_img" src="" style="display: none;" alt="Prod">
                                <span class="pf-img-hint"><i class="fas fa-cloud-upload-alt"></i> Clic para subir</span>
                            </div>
                            <input type="file" name="newImagenProducto" id="newImagenProducto" onchange="readURL(this);" style="display: none;">

                            <div class="pf-status mt-3">
                                <div>
                                    <div class="st-t">Estado</div>
                                    <div class="st-s">Disponible para la venta</div>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="producto_estado">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Resumen en vivo (CLARO) --}}
                    <div class="pf-summary" id="pf-summary">
                        <h6><i class="fas fa-chart-line"></i>&nbsp; Resumen en vivo</h6>
                        <div class="pf-sum-row"><span>Costo</span><b id="pfs-costo">$ 0</b></div>
                        <div class="pf-sum-row"><span>Utilidad</span><b id="pfs-utilidad">$ 0</b></div>
                        <div class="pf-sum-total">
                            <span class="lbl">Precio de venta</span>
                            <span class="val" id="pfs-venta">$ 0</span>
                        </div>
                        <div class="pf-bar"><span id="pfs-bar"></span></div>
                        <span class="pf-margin" id="pfs-margin"><i class="fas fa-percentage"></i> Margen 0%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== ACCIONES (normales, al final) ===== --}}
        <div class="pf-actions">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger" id="cancelProducto" style="display: none;">
                    <i class="fas fa-times"></i>&nbsp; Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="saveNewProducto" style="display: none;">
                    <i class="fas fa-save"></i>&nbsp; Guardar
                </button>
                <button type="button" class="btn btn-sm btn-primary" id="saveEditProducto" style="display: none;">
                    <i class="fas fa-save"></i>&nbsp; Actualizar
                </button>
                <button id="saveNewProductoLoading" class="btn btn-sm btn-success" style="display: none;" disabled>
                    <i class="fas fa-spinner fa-spin"></i>&nbsp; Guardando...
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// ===== Resumen en vivo (solo visual; no toca la lógica del formulario) =====
window.refreshPfSummary = function () {
    var form = document.getElementById('newProductoForm');
    if (!form || form.offsetParent === null) return;

    function num(v) {
        if (typeof stringToNumberFloat === 'function') return parseFloat(stringToNumberFloat(v)) || 0;
        return parseFloat(String(v || '0').replace(/,/g, '')) || 0;
    }
    function money(n) {
        return '$ ' + n.toLocaleString('es-CO', { maximumFractionDigits: 2 });
    }

    var costo = num($('#precio_inicial').val());
    var venta = num($('#precio_producto').val());
    var util  = venta - costo;
    var margen = venta > 0 ? (util / venta) * 100 : 0;

    $('#pfs-costo').text(money(costo));
    $('#pfs-utilidad').text(money(util));
    $('#pfs-venta').text(money(venta));
    $('#pfs-bar').css('width', Math.max(0, Math.min(100, margen)) + '%');
    $('#pfs-margin')
        .toggleClass('neg', margen < 0)
        .html('<i class="fas fa-percentage"></i> Margen ' + margen.toFixed(1) + '%');
};

// Refresca en cada input (reemplaza el setInterval)
$(document).on('input', '#precio_inicial, #porcentaje_utilidad, #valor_utilidad, #precio_producto', function () {
    refreshPfSummary();
});
$(document).on('change', '#precio_minimo, #precio_producto_2', function () {
    refreshPfSummary();
});

// También refresca cuando el form se hace visible o cambia tipo
$(document).on('click', '#createProducto, .edit-producto, #tipo_producto_producto, #tipo_producto_servicio, #tipo_producto_combo, #tipo_producto_parqueadero, #tipo_producto_documento', function () {
    setTimeout(refreshPfSummary, 120);
});
</script>