
<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

    
        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.documento.documento-filter')
            </div>
        </div>
        <div class="card cardTotalDocumentosCapturados" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">DEBITO</p>
                    <h6 id="documentos_capturados_debito" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">CREDITO</p>
                    <h6 id="documentos_capturados_credito" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold;">DIFERENCIA</p>
                    <h6 id="documentos_capturados_diferencia" style="margin-top: -15px;">$0</h6>
                </div>
            </div>
        </div>
        <div class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
            @include('pages.contabilidad.documento.documento-table')
        </div>
    </div>

    <script>
        
        var ubicacion_maximoph_documentos = JSON.parse('<?php echo $ubicacion_maximoph; ?>');

    </script>

</div>
