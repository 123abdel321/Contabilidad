<div class="modal fade" id="periodoPagoDetalleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-lg-down modal-dialog-scrollable">
        <div class="modal-content pp-modal">

            {{-- HEADER --}}
            <div class="pp-header">
                <button type="button" class="btn-close btn-close-white pp-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>

                <div class="pp-header-top">
                    <div class="pp-avatar" id="pp_avatar">
                        <span class="pp-avatar-initials" id="pp_avatar_initials">--</span>
                        <img id="pp_avatar_img" class="pp-avatar-img" alt="" style="display:none">
                    </div>
                    <div class="pp-emp">
                        <span class="pp-eyebrow">Desprendible de nómina</span>
                        <h4 class="pp-emp-name" id="pp_nombre">—</h4>
                        <div class="pp-emp-meta">
                            <span><i class="fas fa-id-card"></i> <span id="pp_documento">—</span></span>
                            <span><i class="fas fa-envelope"></i> <span id="pp_email">—</span></span>
                            <span><i class="fas fa-phone"></i> <span id="pp_telefono">—</span></span>
                        </div>
                    </div>
                    <div class="pp-header-right">
                        <span class="pp-badge" id="pp_estado">—</span>
                        <small class="pp-periodo" id="pp_periodo"></small>
                    </div>
                </div>

                {{-- CONTRATO --}}
                <div class="pp-contract">
                    <div class="pp-chip">
                        <i class="fas fa-file-signature"></i>
                        <div><span>Contrato</span><b id="pp_tipo_contrato">—</b></div>
                    </div>
                    <div class="pp-chip">
                        <i class="fas fa-layer-group"></i>
                        <div><span>Tipo de salario</span><b id="pp_tipo_salario">—</b></div>
                    </div>
                    <div class="pp-chip">
                        <i class="fas fa-coins"></i>
                        <div><span>Salario base</span><b id="pp_salario_base">—</b></div>
                    </div>
                    <div class="pp-chip">
                        <i class="fas fa-calendar-day"></i>
                        <div><span>Inicio contrato</span><b id="pp_inicio_contrato">—</b></div>
                    </div>
                </div>

                {{-- KPIs --}}
                <div class="pp-kpis row g-3">
                    <div class="col-12 col-md-4">
                        <div class="pp-kpi pp-kpi-in">
                            <div class="pp-kpi-icon"><i class="fas fa-arrow-trend-up"></i></div>
                            <div>
                                <span class="pp-kpi-label">Devengado</span>
                                <strong class="pp-kpi-value" id="devengado_detalle_total">$ 0</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="pp-kpi pp-kpi-out">
                            <div class="pp-kpi-icon"><i class="fas fa-arrow-trend-down"></i></div>
                            <div>
                                <span class="pp-kpi-label">Deducciones</span>
                                <strong class="pp-kpi-value" id="deduccion_detalle_total">$ 0</strong>
                                <small class="pp-kpi-sub" id="pp_pct_deduccion">0% del devengado</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="pp-kpi pp-kpi-net">
                            <div class="pp-kpi-icon"><i class="fas fa-wallet"></i></div>
                            <div>
                                <span class="pp-kpi-label">Neto a pagar</span>
                                <strong class="pp-kpi-value" id="neto_detalle_total">$ 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pp-bar" title="Distribución del devengado">
                    <div class="pp-bar-net" id="pp_bar_net" style="width:100%"></div>
                    <div class="pp-bar-ded" id="pp_bar_ded" style="width:0%"></div>
                </div>
            </div>

            {{-- BODY --}}
            <div class="modal-body pp-body">

                <table id="periodoPagoDetalleTable" class="table pp-table display responsive" width="100%">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th class="text-end">Devengado</th>
                            <th class="text-end">Deducción</th>
                            <th>Horas</th>
                            <th>Días</th>
                            <th>Observación</th>
                            <th>%</th>
                            <th>Base %</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Hora inicio</th>
                            <th>Hora fin</th>
                        </tr>
                    </thead>
                </table>
            </div>

            {{-- FOOTER --}}
            <div class="modal-footer pp-footer">
                <div class="pp-footer-net">
                    <span>Total neto a pagar</span>
                    <strong id="pp_footer_neto">$ 0</strong>
                </div>
                <div class="pp-footer-actions">
                    <!-- <button type="button" class="btn btn-light" id="pp_btn_print"><i class="fas fa-print me-1"></i> Imprimir</button> -->
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>

        </div>
    </div>
</div>