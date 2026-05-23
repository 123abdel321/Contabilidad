<div class="mt-2" id="uploadStatusDocumentos" style="display: none;">
    <div class="d-flex align-items-center mb-2">
        <div class="progress flex-grow-1 me-2" style="height: 6px;">
            <div id="uploadProgressDocumentos" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                 role="progressbar" style="width: 0%"></div>
        </div>
        <small id="progressTextDocumentos" class="text-muted small fw-bold">0%</small>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span id="statusTextDocumentos" class="badge bg-info text-dark bg-opacity-10 border border-info border-opacity-25">
                <i class="fas fa-spinner fa-spin me-1"></i>
                <span id="statusMessageDocumentos">Preparando carga...</span>
            </span>
        </div>
        <div>
            <small id="statsTextDocumentos" class="text-muted small">
                <span id="processedRowsDocumentos">0</span> / <span id="totalRowsDocumentos">0</span> registros
                <i class="fas fa-file-excel ms-1 text-success"></i>
            </small>
        </div>
    </div>
</div>

<table id="importDocumentos" class="table-import-documentos table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Row</th>
            <th>Documento nit</th>
            <th>Cuenta contable</th>
            <th>Codigo cecos</th>
            <th>Codigo comprobante</th>
            <th>Consecutivo</th>
            <th>Dcto Refe</th>
            <th>Fecha manual</th>
            <th>Debito</th>
            <th>Credito</th>
            <th>Concepto</th>
            <th>Errores</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Total errores</th>
        </tr>
    </thead>
</table>