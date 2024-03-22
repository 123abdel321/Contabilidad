<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\ProvisionadaSeeder;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Database\Seeders\PropiedadesHorizontalesSeeder;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\User;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\DocumentosGeneral;

class ProcessGenerarDocumentos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	use BegConsecutiveTrait;

    public $timeout = 300;
	public $id_empresa;
	public $id_usuario;
	public $request;

	public $dbName = '';
	public $connectionName = '';
	public $tokenUsuario;

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

			$cuentasContables = [
				'id_cuenta_por_cobrar',
				'id_cuenta_ingreso'
			];
			
			$documento = $this->request['documento'];
			$documentosGroup = [];

			foreach($documento as $document) {
				$document = (object)$document;
				$documentosGroup[$document->token_factura][] = $document;
			}

			foreach($documentosGroup as $docGroup) {

				$consecutivo = $this->getNextConsecutive($docGroup[0]->id_comprobante, $docGroup[0]->fecha_manual);

				$facDocumento = FacDocumentos::create([
					'id_nit' => $docGroup[0]->id_nit,
					'id_comprobante' => $docGroup[0]->id_comprobante,
					'fecha_manual' => $docGroup[0]->fecha_manual,
					'consecutivo' => $consecutivo,
					'token_factura' => $docGroup[0]->token_factura,
					'debito' => 0,
					'credito' => 0,
					'saldo_final' => 0,
					'created_by' => $this->id_usuario,
					'updated_by' => $this->id_usuario,
				]);

				$documentoGeneral = new Documento(
					$docGroup[0]->id_comprobante,
					$facDocumento,
					$docGroup[0]->fecha_manual,
					$consecutivo
				);

				foreach ($docGroup as $doc) {
					foreach ($cuentasContables as $cuentaContableI) {
						$naturaleza = null;
						$docGeneral = $this->newDocGeneral();
						$cuentaContable = PlanCuentas::where('id', $doc->{$cuentaContableI})
							->with('tipos_cuenta')
							->first();
	
						$naturaleza = null;
						$documentoReferencia = $doc->documento_referencia;

						if ($doc->naturaleza_opuesta) {

							$documentoReferencia = $this->generarDocumentoReferenciaAnticipos($cuentaContable, $doc);

							if ($cuentaContable->naturaleza_cuenta == PlanCuentas::DEBITO) {
								$naturaleza = PlanCuentas::CREDITO;
								$docGeneral['credito'] = $doc->valor;
							} else {
								$naturaleza = PlanCuentas::DEBITO;
								$docGeneral['debito'] = $doc->valor;
							}
						} else {
							if ($cuentaContable->naturaleza_cuenta == PlanCuentas::DEBITO) {
								$naturaleza = PlanCuentas::DEBITO;
								$docGeneral['debito'] = $doc->valor;
							} else {
								$naturaleza = PlanCuentas::CREDITO;
								$docGeneral['credito'] = $doc->valor;
							}
						}

						$docGeneral['id_nit'] = $doc->id_nit;
						$docGeneral['id_cuenta'] = $cuentaContable->id;
						$docGeneral['id_centro_costos'] = $doc->id_centro_costos;
						$docGeneral['documento_referencia'] = $documentoReferencia;
						$docGeneral['concepto'] = $doc->concepto;
						$docGeneral['consecutivo'] = $consecutivo;
						$docGeneral['created_by'] = $this->id_usuario;
						$docGeneral['updated_by'] = $this->id_usuario;
		
						$docGeneral = new DocumentosGeneral($docGeneral);
						$documentoGeneral->addRow($docGeneral, $naturaleza);
					}
				}
				if (!$documentoGeneral->save()) {
	
					DB::connection('sam')->rollback();
					return response()->json([
						'success'=>	false,
						'data' => [],
						'message'=> $documentoGeneral->getErrors()
					], 401);
				}
	
				$this->updateConsecutivo($docGroup[0]->id_comprobante, $consecutivo);
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

	private function generarDocumentoReferenciaAnticipos($cuenta, $doc)
	{
		$tiposCuenta = $cuenta->tipos_cuenta;
        foreach ($tiposCuenta as $tipoCuenta) {
            if ($tipoCuenta->id_tipo_cuenta == 4 || $tipoCuenta->id_tipo_cuenta == 8) {
                return $doc->documento_referencia_anticipo;
            }
        }
		return $doc->documento_referencia;
	}

	private function newDocGeneral()
	{
		return [
			'id_nit' => '',
			'id_cuenta' => '',
			'id_centro_costos' => '',
			'created_by' => '',
			'updated_by' => '',
			'consecutivo' => '',
			'concepto' => '',
			'credito' => 0,
			'debito' => 0,
			'saldo' => 0,
			'documento_referencia' => ''
		];
	}
}
