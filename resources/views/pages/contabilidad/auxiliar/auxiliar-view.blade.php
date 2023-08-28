<style>
    .error {
        color: red;
    }
    .column-number {
        text-align: -webkit-right;
    }
    .content-export-btn {
        padding: 10px;
        margin-top: -20px;
    }
    .button-export-excel {
        width: 40px;
        background-color: #006d37;
        padding: 5px;
        height: 30px;
        text-align-last: center;
        color: white;
        border-radius: 5px;
        cursor: pointer;
        float: right;
    }
    
</style>

<div class="container-fluid py-2">
    <div class="row">

        <div class="card mb-4">
            <div class="card-body" style="padding: 0 !important;">
                @include('pages.contabilidad.auxiliar.auxiliar-filter')
            </div>
        </div>

        <div class="card cardTotalAuxiliar" style="content-visibility: auto; overflow: auto; border-radius: 20px 20px 0px 0px;">
            <div class="row" style="text-align: -webkit-center;">
                <div class="col-6 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">SALDO ANTERIOR</p>
                    <h6 id="auxiliar_anterior" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col-6 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">DEBITO</p>
                    <h6 id="auxiliar_debito" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col-6 col-md-3 col-sm-3" style="border-right: solid 1px #787878;">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">CREDITO</p>
                    <h6 id="auxiliar_credito" style="margin-top: -15px;">$0</h6>
                </div>
                <div class="col-6 col-md-3 col-sm-3">
                    <p style="font-size: 13px; margin-top: 5px; color: black;">SALDO FINAL</p>
                    <h6 id="auxiliar_diferencia" style="margin-top: -15px;">$0</h6>
                </div>
            </div>
        </div>
        <div class="card mb-4" style="content-visibility: auto; overflow: auto; border-radius: 0px 0px 20px 20px;">
            @include('pages.contabilidad.auxiliar.auxiliar-table')
        </div>
    </div>
    
</div>