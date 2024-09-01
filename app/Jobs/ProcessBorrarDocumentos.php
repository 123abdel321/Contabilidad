<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ProvisionadaSeeder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Database\Seeders\PropiedadesHorizontalesSeeder;
//MODELS
use App\Models\User;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\DocumentosGeneral;

class ProcessBorrarDocumentos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
	public $id_empresa;
	public $id_usuario;
	public $request;

    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
		$this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
    }

    public function handle()
    {
		$empresa = Empresa::find($this->id_empresa);
        
        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db);

		try {
			DB::connection('sam')->beginTransaction();

			$documento = $this->request['documento'];

			foreach ($documento as $token) {

				$factura = FacDocumentos::where('token_factura', $token)->first();

				if ($factura) {
					
					$documento = DocumentosGeneral::where('relation_id', $factura->id)
						->where('relation_type', 2)
						->with('relation')
						->delete();
						
					$factura->delete();
				}	
			}

			DB::connection('sam')->commit();
				
			return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Documentos creados con exito!'
			], 200);
			
		} catch (Exception $exception) {
            DB::connection('sam')->rollback();

			throw $exception;
        }
    }
}
