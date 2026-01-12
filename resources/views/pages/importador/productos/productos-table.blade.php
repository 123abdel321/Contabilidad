<div class="mt-2" id="uploadStatus" style="display: none;">
    <div class="d-flex align-items-center mb-2">
        <div class="progress flex-grow-1 me-2" style="height: 6px;">
            <div id="uploadProgress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                 role="progressbar" style="width: 0%"></div>
        </div>
        <small id="progressText" class="text-muted small fw-bold">0%</small>
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span id="statusText" class="badge bg-info text-dark bg-opacity-10 border border-info border-opacity-25">
                <i class="fas fa-spinner fa-spin me-1"></i>
                <span id="statusMessage">Preparando carga...</span>
            </span>
        </div>
        <div>
            <small id="statsText" class="text-muted small">
                <span id="processedRows">0</span> / <span id="totalRows">0</span> registros
                <i class="fas fa-file-excel ms-1 text-success"></i>
            </small>
        </div>
    </div>
</div>

<table id="importProductos" class="table table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Row</th>
            <th>Codigo</th>
            <th>Nombre</th>
            <th>Costo</th>
            <th>Venta</th>
            <th>Familia</th>
            <th>Bodega</th>
            <th>Existencias</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Observacion</th>
        </tr>
    </thead>
</table>