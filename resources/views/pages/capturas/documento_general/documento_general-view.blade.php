<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }

    .combo-grid-nit {
        width: 200px !important;
    }

    .combo-grid {
        width: 230px !important;
    }

    .drop-row-grid {
        margin-bottom: 0rem !important;
        font-size: 12px;
        margin-top: 4px;
        border-radius: 50px;
        width: 26px;
    }

    .fa-trash-alt {
        margin-left: -3px;
        margin-top: 1px;
    }
    #documentoReferenciaTable>tbody>tr.odd {
        text-align: -webkit-center !important;
    }

    #documentoReferenciaTable tbody>tr.even {
        text-align: -webkit-center !important;
    }

    .btn-group {
        box-shadow: 0 0px 0px rgba(50, 50, 93, 0.1), 0 0px 0px rgba(0, 0, 0, 0.08);
    }

    .normal_input {
        border-radius: 9px !important;
    }

    .documento-load {
        position: absolute;
        margin-left: 109px !important;
        margin-top: 9px;
        z-index: 99;
        font-size: 12px;
        color: #004cb3;
    }

    .info-factura {
        position: absolute;
        margin-top: 23px;
        margin-left: 6px !important;
        background-color: white;
        width: 115px;
        border-radius: 15px;
        z-index: 3;
    }
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">

                @include('pages.capturas.documento_general.documento_general-filter')

            </div>
        </div>

        <div class="card cardTotal" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-4 col-md-4 col-sm-4" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">DEBITO</p>
                    <h6 id="general_debito" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>
                <div class="col-4 col-md-4 col-sm-4" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">CREDITO</p>
                    <h6 id="general_credito" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>
                <div class="col-4 col-md-4 col-sm-4">
                    <p style="font-size: 13px; margin-top: 5px; color: black; font-weight: bold; margin-bottom: 0px;">DIFERENCIA</p>
                    <h6 id="general_diferencia" style="color: #0002ff; margin-bottom: 0px;">0</h6>
                </div>
            </div>
        </div>
        <div id="card-documento-general" class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
            @include('pages.capturas.documento_general.documento_general-table')
            <div class="space_documento_general" style="padding: 2px;"></div>
        </div>
        
        @include('pages.capturas.documento_general.documento_general-form')
        @include('pages.capturas.documento_general.documento_general-extracto')

    </div>

    <script>
        var primerCecosGeneral = JSON.parse('<?php echo $cecos; ?>');
        var capturarDocumentosDescuadrados = JSON.parse('<?php echo $capturarDocumentosDescuadrados; ?>');
    </script>

</div>