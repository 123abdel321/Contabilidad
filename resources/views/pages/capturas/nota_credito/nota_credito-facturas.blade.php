<!-- MODAL USUARIO ACCIÓN-->
<div class="modal fade" id="modalFacturasDevolucion" tabindex="-1" role="dialog" aria-labelledby="modal-default" aria-hidden="true">
    <div class="modal-dialog modal- modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h6 class="modal-title" id="modal-title-documento-extracto">Facturas</h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="card mb-4" style="content-visibility: auto; overflow: auto;">
            <table id="facturaDevolucionTable" class="table nowrap table-bordered display responsive" width="100%">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Consecutivo</th>
                        <th>Total</th>
                        <th>Cecos</th>
                        <th>Bodega</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-danger ml-auto" data-bs-dismiss="modal">Cerrar</button>
        </div>
        </div>
    </div>
</div>