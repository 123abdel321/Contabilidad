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
    font-size:24px;font-weight:700;background:rgba(255,255,255,.18);
    border:1px solid rgba(255,255,255,.35);backdrop-filter:blur(6px)}
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

    /* KPIs */
    .pp-kpis{margin-top:10px}
    .pp-kpi{height:100%;
        display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:14px;
        background:#fff;color:#1d2939;box-shadow:0 6px 18px rgba(0,0,0,.12)}
    .pp-kpi-icon{width:44px;height:44px;border-radius:12px;display:grid;place-items:center;font-size:18px}
    .pp-kpi-label{display:block;font-size:12px;color:#667085;text-transform:uppercase;letter-spacing:.06em}
    .pp-kpi-value{display:block;font-size:22px;line-height:1.2;font-variant-numeric:tabular-nums}
    .pp-kpi-sub{color:#98a2b3}
    .pp-kpi-in .pp-kpi-icon{background:#ecfdf3;color:#12b76a}
    .pp-kpi-in .pp-kpi-value{color:#067647}
    .pp-kpi-out .pp-kpi-icon{background:#fef3f2;color:#f04438}
    .pp-kpi-out .pp-kpi-value{color:#b42318}
    .pp-kpi-net{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff}
    .pp-kpi-net .pp-kpi-label{color:#94a3b8}
    .pp-kpi-net .pp-kpi-icon{background:rgba(255,255,255,.1);color:#fbbf24}
    .pp-kpi-net .pp-kpi-value{color:#fff;font-size:26px}

    .pp-bar{display:flex;height:8px;margin-top:18px;border-radius:99px;overflow:hidden;background:rgba(255,255,255,.2)}
    .pp-bar-net{background:#32d583;transition:width .8s ease}
    .pp-bar-ded{background:#fda29b;transition:width .8s ease}

    /* Body */
    .pp-body{background:#f8fafc;padding:20px 24px}
    .pp-table-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;color:#344054}
    .pp-count{margin-left:8px;padding:2px 10px;border-radius:99px;background:#e0e7ff;color:#3730a3;font-size:12px}
    .pp-table{background:#fff;border-radius:12px;overflow:hidden;border:1px solid #eaecf0 !important}
    .pp-table thead th{background:#f2f4f7;color:#475467;font-size:12px;text-transform:uppercase;
    letter-spacing:.04em;border-bottom:1px solid #eaecf0 !important;white-space:nowrap}
    .pp-table tbody td{vertical-align:middle;font-size:13.5px;border-color:#f2f4f7}
    .pp-table tbody tr:hover{background:#f5f8ff}
    .pp-table .td-dev{color:#067647;font-weight:600;text-align:right;font-variant-numeric:tabular-nums}
    .pp-table .td-ded{color:#b42318;font-weight:600;text-align:right;font-variant-numeric:tabular-nums}
    .pp-table .td-zero{color:#cbd5e1;font-weight:400}

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