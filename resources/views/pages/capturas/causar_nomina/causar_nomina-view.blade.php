<style>

    .dtrg-group {
        font-weight: bold;
        background-color: #f0f0f0;
        padding: 10px;
        text-transform: uppercase;
    }

    .pp-modal{border:0;border-radius:18px;overflow:hidden;box-shadow:0 25px 60px rgba(16,24,40,.25)}

    /* Header */
    .pp-header{position:relative;padding:24px 28px 20px;color:#fff;
    background:linear-gradient(135deg,#1e3a8a 0%,#3b5bdb 55%,#5c7cfa 100%)}
    .pp-close{position:absolute;top:16px;right:18px;opacity:.8}
    .pp-header-top{display:flex;align-items:center;gap:18px;flex-wrap:wrap}
    .pp-avatar{position:relative;width:64px;height:64px;border-radius:18px;display:grid;place-items:center;
    font-size:24px;font-weight:700;background:rgba(255,255,255,.18);overflow:hidden;
    border:1px solid rgba(255,255,255,.35);backdrop-filter:blur(6px);flex-shrink:0}
    .pp-avatar-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
    .pp-avatar-initials{position:relative;z-index:0}
    .pp-emp{flex:1;min-width:220px}
    .pp-eyebrow{font-size:11px;letter-spacing:.12em;text-transform:uppercase;opacity:.75}
    .pp-emp-name{margin:2px 0 6px;font-weight:700;color:#fff}
    .pp-emp-meta{display:flex;flex-wrap:wrap;gap:6px 18px;font-size:13px;opacity:.9}
    .pp-emp-meta i{opacity:.7;margin-right:4px}
    .pp-header-right{text-align:right;margin-right:28px}
    .pp-periodo{display:block;margin-top:6px;opacity:.8}
    .pp-badge{display:inline-block;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:600;
    background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.4)}
    .pp-badge.ok{background:#12b76a;border-color:#12b76a}
    .pp-badge.pend{background:#f79009;border-color:#f79009}

    /* KPIs compactos */
    .pp-kpis{margin-top:4px}
    .pp-kpi{height:100%;display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:12px;
    background:#fff;color:#1d2939;box-shadow:0 4px 12px rgba(0,0,0,.10)}
    .pp-kpi-icon{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;font-size:14px;flex-shrink:0}
    .pp-kpi-label{display:block;font-size:10.5px;color:#667085;text-transform:uppercase;letter-spacing:.05em}
    .pp-kpi-value{display:block;font-size:17px;line-height:1.15;font-variant-numeric:tabular-nums}
    .pp-kpi-sub{font-size:10.5px;color:#98a2b3}

    /* Colores de KPIs */
    .pp-kpi-in .pp-kpi-icon{background:#ecfdf3;color:#12b76a}
    .pp-kpi-in .pp-kpi-value{color:#067647}

    .pp-kpi-out .pp-kpi-icon{background:#fef3f2;color:#f04438}
    .pp-kpi-out .pp-kpi-value{color:#b42318}

    .pp-kpi-net{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff}
    .pp-kpi-net .pp-kpi-label{color:#94a3b8}
    .pp-kpi-net .pp-kpi-icon{background:rgba(255,255,255,.1);color:#fbbf24}
    .pp-kpi-net .pp-kpi-value{color:#fff;font-size:18px}

    /* Franja de contrato */
    .pp-contract{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:16px}
    .pp-chip{display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;
    background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.22)}
    .pp-chip i{font-size:14px;opacity:.8;width:16px;text-align:center}
    .pp-chip span{display:block;font-size:10px;letter-spacing:.08em;text-transform:uppercase;opacity:.7}
    .pp-chip b{display:block;font-size:13px;font-weight:600;font-variant-numeric:tabular-nums}

    @media (max-width:768px){
    .pp-contract{grid-template-columns:repeat(2,1fr)}
    }

    /* Footer */
    .pp-footer{display:flex;justify-content:space-between;align-items:center;padding:14px 24px;background:#fff;border-top:1px solid #eaecf0}
    .pp-footer-net span{display:block;font-size:12px;color:#667085;text-transform:uppercase}
    .pp-footer-net strong{font-size:22px;color:#067647;font-variant-numeric:tabular-nums}

    @media (max-width:768px){
    .pp-header-right{text-align:left;margin-right:0}
    }

    /* Impresión */
    @media print{
    body *{visibility:hidden}
    #periodoPagoDetalleModal,#periodoPagoDetalleModal *{visibility:visible}
    #periodoPagoDetalleModal{position:absolute;inset:0}
    .pp-close,.pp-footer-actions{display:none !important}
    .pp-header,.pp-kpi-net{-webkit-print-color-adjust:exact;print-color-adjust:exact}
    }

    .causar-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    padding: 2px 10px;
    background-color: #ffffff;
    color: #003883;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
    border: 1px solid #003883;
    border-radius: 12px;
    cursor: pointer;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}

.causar-chip .check-causar-periodo {
    width: 15px;
    height: 15px;
    margin: 0;
    cursor: pointer;
    accent-color: #003883;
}

.causar-chip:hover {
    background-color: #eef5ff;
    border-color: #0056c7;
}

/* Marcado */
.causar-chip.checked {
    background-color: #e3f6e8;
    border-color: #28a745;
    color: #1e7e34;
}

.causar-chip.checked .check-causar-periodo {
    accent-color: #28a745;
}

.group-toggle {
    padding: 2px 8px 2px 4px;
    border-radius: 6px;
    transition: background-color 0.15s ease;
}

.group-toggle:hover {
    background-color: rgba(0, 56, 131, 0.12);
}

.group-toggle .toggle-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    margin-right: 6px;
    border-radius: 4px;
    background-color: #ffffff;
    border: 1px solid #003883;
}

.group-toggle .toggle-icon {
    margin: 0 !important;
    font-size: 13px;
    color: #003883;
}

.group-toggle:hover .toggle-btn {
    background-color: #003883;
}

.group-toggle:hover .toggle-icon {
    color: #ffffff;
}

.group-toggle .group-hint {
    margin-left: 10px;
    font-size: 11px;
    font-weight: 400;
    font-style: italic;
    color: #5a7ba8;
    opacity: 0;
    transition: opacity 0.15s ease;
}

/* El texto de ayuda aparece solo al pasar el mouse */
.group-toggle:hover .group-hint {
    opacity: 1;
}

.group-header-causar {
    -webkit-user-select: none; /* Safari */
    -ms-user-select: none;     /* IE/Edge viejo */
    user-select: none;
}

</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4" style="content-visibility: auto; overflow: auto; background-color: transparent; box-shadow: none;">

            <div class="card-body row">
                <ul class="nav nav-tabs" role="tablist" style="border-bottom: none;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="causar-nomina-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#causar_nomina" type="button" role="tab" aria-controls="causar_nomina" aria-selected="true">
                            Causar nomina
                        </button>
                    </li>
                    <!-- <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="causar-nomina-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#causar_nomina" type="button" role="tab" aria-controls="causar_nomina" aria-selected="true">
                            Detalle
                        </button>
                    </li> -->
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="parafiscales-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#parafiscales" type="button" role="tab" aria-controls="parafiscales" aria-selected="false">
                            Parafiscales
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="seguridad_social-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#seguridad_social" type="button" role="tab" aria-controls="seguridad_social" aria-selected="false">
                            Seguridad social
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prestaciones-sociales-tab" style="font-size: 15px !important; font-weight: bold; margin-right: 2px; color: black;" data-bs-toggle="tab" data-bs-target="#prestaciones_sociales" type="button" role="tab" aria-controls="prestaciones_sociales" aria-selected="false">
                            Prestaciones sociales
                        </button>
                    </li>
                </ul>
    
                <div class="tab-content" style="background-color: white; border-top-right-radius: 10px;">
                    <div class="tab-pane fade show active" id="causar_nomina" role="tabpanel" aria-labelledby="causar_nomina_tab">
                        @include('pages.capturas.causar_nomina.causar_nomina-table')
                    </div>
                    <div class="tab-pane fade" id="parafiscales" role="tabpanel" aria-labelledby="parafiscales_tab">
                        @include('pages.capturas.causar_nomina.parafiscales-table')
                    </div>
                    <div class="tab-pane fade" id="seguridad_social" role="tabpanel" aria-labelledby="seguridad_social_tab">
                        @include('pages.capturas.causar_nomina.seguridad_social-table')
                    </div>
                    <div class="tab-pane fade" id="prestaciones_sociales" role="tabpanel" aria-labelledby="prestaciones_sociales_tab">
                        @include('pages.capturas.causar_nomina.prestaciones_sociales-table')
                    </div>
                </div>
            </div>

        </div>

        @include('pages.capturas.causar_nomina.causar_nomina-detalle')
        @include('pages.capturas.causar_nomina.causar_provisiones-form')

    </div>
    
</div>