<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Http\Controllers\Traits\BegFacturacionElectronica;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacVentas;
use App\Models\Sistema\VariablesEntorno;

class ProcessConsultarFE implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use BegFacturacionElectronica;

    public $trackId;
    public $id_venta;
    public $id_usuario;
	public $id_empresa;

    public function __construct($id_venta, $trackId, $id_usuario, $id_empresa)
    {
        $this->trackId = $trackId;
        $this->id_venta = $id_venta;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
    }

    public function handle()
    {
        info('Validando Batch.');

        $empresa = Empresa::find($this->id_empresa);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

        DB::connection('sam')->beginTransaction();

        try {

            $url = "http://localhost:6666/api/ubl2.1/status/zip/{$this->trackId}";

            $bearerToken = VariablesEntorno::where('nombre', 'token_key_fe')->first();
            $bearerToken = $bearerToken ? $bearerToken->valor	: '';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Requested-With' => 'XMLHttpRequest',
                'Authorization' => 'Bearer ' . $bearerToken
            ])->post($url);

            $data = (object) $response->json();

            info(json_encode($data));

            $dianResponse = $data->ResponseDian['Envelope']['Body']['GetStatusZipResponse']['GetStatusZipResult']['DianResponse'];
            $isValid = $dianResponse['IsValid'];

            $venta = FacVentas::find($this->id_venta);

            if ($isValid == 'true') {
                $venta = $this->SetFeFields($venta, $dianResponse['XmlDocumentKey'], $empresa->nit);
                $venta->save();

                DB::connection('sam')->commit();

                event(new PrivateMessageEvent('notificacion-fe-'.$empresa->token_db.'_'.$this->id_usuario, [
                    'tipo' => 'exito',
                    'mensaje' => "La Factura electrónica $venta->documento_referencia_fe se encuentra aprobada!",
                    'titulo' => 'Factura electronica consultada',
                    'autoclose' => false
                ]));

                return;
            }

            if ($dianResponse['StatusDescription'] == 'Batch en proceso de validación.') {
                
                ProcessConsultarFE::dispatch($this->id_venta, $this->trackId, $this->id_usuario, $this->id_empresa)->delay(now()->addSeconds(15));

                return;
            }

            event(new PrivateMessageEvent('notificacion-fe-'.$empresa->token_db.'_'.$this->id_usuario, [
                'tipo' => 'error',
                'mensaje' => $dianResponse['StatusDescription'],
                'titulo' => 'Factura electronica consultada',
                'autoclose' => false
            ]));

        } catch (Exception $exception) {
            DB::connection('sam')->rollback();

			throw $exception;
        }
    }

}