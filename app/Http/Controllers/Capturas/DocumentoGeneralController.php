<?php

namespace App\Http\Controllers\Capturas;

use DB;
use DateTimeImmutable;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessBorrarDocumentos;
use App\Jobs\ProcessGenerarDocumentos;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\Nits;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\FacDocumentos;
use App\Models\Sistema\FacResoluciones;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Sistema\DocumentosGeneral;

class DocumentoGeneralController extends Controller
{
    use BegConsecutiveTrait;

	protected $messages = null;
	protected $cuentasDocumentos = [
		'id_cuenta_por_cobrar' => 'total',
		'id_cuenta_por_pagar' => 'total',
		'id_cuenta_rete_fuente' => 'valor_total_retencion',
		'id_cuenta_ingreso' => 'total',
		'id_cuenta_gasto' => 'total',
		'id_cuenta_iva' => 'valor_total_iva',
	];

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index ()
    {
		$capturarDocumentosDescuadrados = VariablesEntorno::where('nombre', 'capturar_documento_descuadrado')->first();

		if (!$capturarDocumentosDescuadrados) $capturarDocumentosDescuadrados = false;
		else $capturarDocumentosDescuadrados = $capturarDocumentosDescuadrados->valor;

		$data = [
            'cecos' => CentroCostos::get(),
			'capturarDocumentosDescuadrados' => $capturarDocumentosDescuadrados
        ];

        return view('pages.capturas.documento_general.documento_general-view', $data);
    }

	public function generarDocumentos(Request $request)
	{
		$rules = [
			'documento' => 'array|required',
			'documento.*.id_nit' => 'nullable|sometimes|exists:sam.nits,id',
			'documento.*.id_cuenta_ingreso' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.id_cuenta_por_cobrar' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.id_comprobante' => 'sometimes|exists:sam.comprobantes,id',
			'documento.*.id_centro_costos' => 'sometimes|exists:sam.centro_costos,id',
			'documento.*.fecha_manual' => 'date|required',
			'documento.*.documento_referencia' => 'nullable|string',
			'documento.*.valor' => 'required',
			'documento.*.concepto' => 'required',
			'documento.*.naturaleza_opuesta' => 'nullable',
			'documento.*.token_factura' => 'nullable'
        ];

		$validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 401);
        }

		try {

			$empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
			ProcessGenerarDocumentos::dispatch($request->all(), $request->user()->id, $empresa->id);
				
			return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Documentos creados con exito!'
			], 200);
			
		} catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 401);
        }
	}

	public function bulkDocumentos(Request $request)
	{
		$rules = [
			'documento' => 'array|required',
			'documento.*.id_comprobante' => 'sometimes|required_if:cod_comprobante,=,null|exists:sam.comprobantes,id',
			'documento.*.cod_comprobante' => 'sometimes|required_if:id_comprobante,=,null|exists:sam.comprobantes,codigo',
			'documento.*.id_centro_costos' => 'sometimes|required_if:cod_centro_costos,=,null|exists:sam.centro_costos,id',
			'documento.*.cod_centro_costos' => 'sometimes|required_if:id_centro_costos,=,null|exists:sam.centro_costos,codigo',
			'documento.*.id_tercero_erp' => 'nullable|sometimes|exists:sam.nits,id',
			'documento.*.fecha_factura' => 'date|required',
			'documento.*.consecutivo_factura' => 'sometimes|required_if:id_comprobante,!=,null',
			'documento.*.descripcion' => 'nullable|string',
			'documento.*.total' => 'nullable|numeric',
			'documento.*.valor_total_iva' => 'nullable|numeric',
			'documento.*.valor_total_retencion' => 'nullable|numeric',
			'documento.*.documento_referencia_custom' => 'nullable|string',
			'documento.*.id_cuenta_por_cobrar' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.id_cuenta_por_pagar' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.id_cuenta_rete_fuente' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.id_cuenta_ingreso' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.id_cuenta_gasto' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.id_cuenta_iva' => 'nullable|exists:sam.plan_cuentas,id',
			'documento.*.nombre_concepto' => 'nullable|string',
        ];
		
		$validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 401);
        }

		try {
			DB::connection('sam')->beginTransaction();

			$documento = $request->get('documento');
			$documentosGroup = [];

			foreach($documento as $document) {
				$document = (object)$document;
				$documentosGroup[$document->token_factura][] = $document;
			}

			foreach($documentosGroup as $docGroup) {

				DocumentosGeneral::where('id_comprobante', $docGroup[0]->id_comprobante)
					->where('consecutivo', $docGroup[0]->consecutivo_factura)
					->where('fecha_manual', $docGroup[0]->fecha_factura)
					->delete();

				$tokenFactura = $docGroup[0]->token_factura ? $docGroup[0]->token_factura : $this->generateTokenDocumento();

				$facDocumento = FacDocumentos::create([
					'id_nit' => $docGroup[0]->id_tercero_erp,
					'id_comprobante' => $docGroup[0]->id_comprobante,
					'fecha_manual' => $docGroup[0]->fecha_factura,
					'consecutivo' => $docGroup[0]->consecutivo_factura,
					'token_factura' => $tokenFactura,
					'debito' => 0,
					'credito' => 0,
					'saldo_final' => 0,
					'created_by' => request()->user()->id,
					'updated_by' => request()->user()->id,
				]);

				$documentoGeneral = new Documento(
					$docGroup[0]->id_comprobante,
					$facDocumento,
					$docGroup[0]->fecha_factura,
					$docGroup[0]->consecutivo_factura
				);

				foreach ($docGroup as $doc) {

					foreach ($this->cuentasDocumentos as $nombreCuenta => $nombreTotal) {
						if (property_exists($doc, $nombreCuenta) && $doc->{$nombreCuenta} && property_exists($doc, $nombreTotal) && $doc->{$nombreTotal}) {
							
							$naturaleza = null;
							$docGeneral = $this->newDocGeneral();
							$cuentaContable = PlanCuentas::where('id', $doc->{$nombreCuenta})->first();
		
							$naturaleza = null;
		
							if ($cuentaContable->naturaleza_cuenta == PlanCuentas::DEBITO) {
								$naturaleza = PlanCuentas::DEBITO;
								$docGeneral['debito'] = $doc->{$nombreTotal};
							} else {
								$naturaleza = PlanCuentas::CREDITO;
								$docGeneral['credito'] = $doc->{$nombreTotal};
							}
		
							$docGeneral['id_nit'] = $doc->id_tercero_erp;
							$docGeneral['id_cuenta'] = $cuentaContable->id;
							$docGeneral['id_centro_costos'] = $doc->id_centro_costos;
							$docGeneral['documento_referencia'] = property_exists($doc, "documento_referencia") ? $doc->documento_referencia : '';
							$docGeneral['concepto'] = $doc->nombre_concepto.' '.$doc->descripcion;
							$docGeneral['consecutivo'] = property_exists($doc, "consecutivo_factura") ? $doc->consecutivo_factura : '';
							$docGeneral['created_by'] = request()->user()->id;
							$docGeneral['updated_by'] = request()->user()->id;
							
		
							$docGeneral = new DocumentosGeneral($docGeneral);
							$documentoGeneral->addRow($docGeneral, $naturaleza);
						}
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

				$this->updateConsecutivo($docGroup[0]->id_comprobante, $docGroup[0]->consecutivo_factura);
			}

			DB::connection('sam')->commit();
				
			return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Documentos creados con exito!'
			], 200);
		} catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 401);
        }
	}

	public function bulkDocumentosDelete(Request $request)
	{
		$rules = [
			'documento' => 'array|required',
			'documento.*.token' => 'required',
        ];
		
		$validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 401);
        }
		try {
			
			DB::connection('sam')->beginTransaction();

			$documento = $request->get('documento');

			foreach ($documento as $token) {

				$factura = FacDocumentos::where('token_factura', $token)->first();

				if ($factura) {
					
					$documento = DocumentosGeneral::where('relation_id', $factura->id)
						->where('relation_type', 2)
						->delete();
						
					$factura->delete();
				}	
			}

			DB::connection('sam')->commit();

			return response()->json([
				'success'=>	true,
				'data' => [],
				'message'=> 'Documentos eliminados con exito!'
			], 200);

		} catch (Exception $e) {

			DB::connection('sam')->rollback();
			return response()->json([
				"success"=>false,
				'data' => [],
				"message"=>$e->getMessage()
			], 401);
		}
	}

    public function create(Request $request)
    {
		$rules = [
            'id_comprobante' => 'sometimes|required_if:cod_comprobante,=,null|exists:sam.comprobantes,id',
			'cod_comprobante' => 'sometimes|required_if:id_comprobante,=,null|exists:sam.comprobantes,codigo',
			'fecha_manual' => 'date|required',
			'consecutivo' => 'sometimes|required_if:id_comprobante,!=,null',
			'documento' => 'array|required',
			'documento.*.concepto' => 'nullable|string',
			'documento.*.credito' => 'numeric',
			'documento.*.debito' => 'numeric',
			'documento.*.documento_referencia' => 'nullable|string',
			'documento.*.token_documento' => 'nullable|string',
			'documento.*.id_centro_costos' => 'nullable|sometimes|exists:sam.centro_costos,id',
			'documento.*.codigo_centro_costos' => 'nullable|sometimes|exists:sam.centro_costos,codigo',
			'documento.*.id_cuenta' => 'exists:sam.plan_cuentas,id',
			"documento.*.cuenta" => "required_without:documento.*.id_cuenta|sometimes|nullable|exists:sam.con_plan_cuentas,cuenta",
			'documento.*.numero_documento' => 'nullable|sometimes|exists:sam.nits,numero_documento',
        ];

		$validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 200);
        }

		$empresa = Empresa::where('id', request()->user()->id_empresa)->first();
		$fechaCierre= DateTimeImmutable::createFromFormat('Y-m-d', $empresa->fecha_ultimo_cierre);
        $fechaManual = DateTimeImmutable::createFromFormat('Y-m-d', $request->get('fecha_manual'));

        if ($fechaManual < $fechaCierre) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['fecha_manual' => ['mensaje' => 'Se esta grabando en un año cerrado']]
            ], 200);
		}
		
		try {

			DB::connection('sam')->beginTransaction();

			$documento = $request->get('documento');
			
			$debito = 0;
			$credito = 0;

			foreach ($documento as $doc) {
				$debito+= $doc['debito'];
				$credito+= $doc['credito'];
			}

			$comprobante = Comprobantes::whereId($request->get('id_comprobante'))->first();

			if(!$request->has('consecutivo')){
				$consecutivo = $this->getNextConsecutive($comprobante->id, $request->get('fecha_manual'));

				$request->merge([
					'consecutivo' => $consecutivo
				]);
			} 

			if(!$request->get('editing_documento')) {
				$consecutivoUsado = DocumentosGeneral::where('id_comprobante', $request->get('id_comprobante'))
					->where('consecutivo', $request->get('consecutivo'))
					->where('fecha_manual', $request->get('fecha_manual'))
					->count();

				if ($consecutivoUsado) {
					return response()->json([
						"success"=>false,
						'data' => [],
						"message"=> "El consecutivo {$request->get('consecutivo')} ya está en uso."
					], 200);
				}
			}

			$facDocumento = null;

			$tokenFactura = $request->get('token_factura') ? $request->get('token_factura') : $this->generateTokenDocumento();
			
			if($request->get('editing_documento')) {
				$facDocumento = DocumentosGeneral::with('relation')
					->where('id_comprobante', $request->get('id_comprobante'))
					->where('consecutivo', $request->get('consecutivo'))
					->where('fecha_manual', $request->get('fecha_manual'))
					->first();
					
				$facDocumento = $facDocumento->relation;
				
				DocumentosGeneral::where('id_comprobante', $request->get('id_comprobante'))
					->where('consecutivo', $request->get('consecutivo'))
					->where('fecha_manual', $request->get('fecha_manual'))
					->delete();
			} else {
				DocumentosGeneral::where('id_comprobante', $request->get('id_comprobante'))
					->where('consecutivo', $request->get('consecutivo'))
					->where('fecha_manual', $request->get('fecha_manual'))
					->delete();

				$facDocumento = FacDocumentos::create([
					'id_comprobante' => $request->get('id_comprobante'),
					'id_nit' => $request->get('id_nit'),
					'fecha_manual' => $request->get('fecha_manual'),
					'consecutivo' => $request->get('consecutivo'),
					'token_factura' => $tokenFactura,
					'debito' => $debito,
					'credito' => $credito,
					'saldo_final' => $debito - $credito,
					'created_by' => request()->user()->id,
					'updated_by' => request()->user()->id,
				]);
			}
			$primerIdNit = null;
			$documentoGeneral = new Documento($facDocumento->id_comprobante, $facDocumento, $request->get('fecha_manual'), $request->get('consecutivo'));

			// if ($request->get('editing_documento')) $documentoGeneral->setCreatedAt($facDocumento->created_at);

			foreach ($documento as $doc) {
				// dd($doc);
				$naturaleza = null;

				if (array_key_exists('debito', $doc) && $doc['debito']) {
					$naturaleza = PlanCuentas::DEBITO;
				}
				
				if (array_key_exists('credito', $doc) && $doc['credito']) {
					$naturaleza = PlanCuentas::CREDITO;
				}

				if (array_key_exists('cuenta', $doc)) {
					$doc['id_cuenta'] = PlanCuentas::whereCuenta($doc['cuenta'])->value('id');
					unset($doc['cuenta']);
				}

				if (array_key_exists('codigo_centro_costos', $doc)) {
					$doc['id_centro_costos'] = CentroCostos::whereCodigo($doc['codigo_centro_costos'])->value('id');
					unset($doc['codigo_centro_costos']);
				}

				if (array_key_exists('numero_documento', $doc)) {
					$doc['id_nit'] = Nits::whereNumeroDocumento($doc['numero_documento'])->value('id');
					if(!$primerIdNit) $primerIdNit = $doc['id_nit'];
					unset($doc['numero_documento']);
				} else {
					if(!$primerIdNit) $primerIdNit = $doc['id_nit'];
				}

				$doc['created_by'] = request()->user()->id;
				$doc['updated_by'] = request()->user()->id;
				
				$doc['consecutivo'] = $request->get('consecutivo');
				$doc = new DocumentosGeneral($doc);
				$documentoGeneral->addRow($doc, $naturaleza);
			}
			
			if (!$documentoGeneral->save()) {

				DB::connection('sam')->rollback();
				return response()->json([
					'success'=>	false,
					'data' => [],
					'message'=> $documentoGeneral->getErrors()
				], 200);
			}

			if(!$request->get('editing_documento')) {
				$this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));
			} else {
				$facDocumento->debito = $debito;
				$facDocumento->credito = $credito;
				$facDocumento->saldo_final = $debito - $credito;
				$facDocumento->updated_by = request()->user()->id;
			}

			DB::connection('sam')->commit();
			
			return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => $comprobante->imprimir_en_capturas ? $request->get('consecutivo') : '',
				'id_comprobante' => $comprobante->id,
				'fecha_manual' => $request->get('fecha_manual'),
				'message'=> 'Documentos creados con exito!'
			], 200);

		} catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 200);
        }
    }

    public function generate(Request $request)
    {
		$documento = DocumentosGeneral::with(['centro_costos', 'cuenta', 'nit'])
			->where('id_comprobante', $request->get('id_comprobante'))
			->where('consecutivo', $request->get('consecutivo'))
            ->get();

		return response()->json([
			'success'=>	true,
			'data' => $documento,
			'message'=> 'Documentos cargados con exito!'
		]);
    }

	public function anular (Request $request)
	{
		$rules = [
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
			'consecutivo' => 'required|numeric',
			'motivo_anulacion' => 'required|string',
			'fecha_manual' => 'required',
        ];

		$validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 200);
        }

		try {

			DB::connection('sam')->beginTransaction();
			
			$documento = DocumentosGeneral::where('id_comprobante', $request->get('id_comprobante'))
				->where('consecutivo', $request->get('consecutivo'))
				->where('fecha_manual', $request->get('fecha_manual'))
				->with('relation')->get();
				
			$documento[0]->relation->anulado = 1;
			$documento[0]->relation->save();

			foreach ($documento as $doc) {
				$doc->anulado = 1;
				$doc->concepto .= ' - motivo anulación: ' . $request->get('motivo_anulacion');
				$doc->save();
			}

			DB::connection('sam')->commit();

			return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Documentos anulados con exito!'
            ]);

		} catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 200);
        }

		DB::connection('sam')->beginTransaction();

	}

    public function vacio(Request $request)
    {
        return response()->json([
    		'success'=>	true,
    		'data' => [],
    		'message'=> 'Consecutivo siguiente generado con exito!'
    	]);
    }

    public function getConsecutivo(Request $request)
    {
		$consecutivo = null;

		if ($request->get('id_comprobante')) {
			$consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $request->get('fecha_manual'));
		}

		if ($request->get('id_resolucion')) {
			$resolucion = FacResoluciones::where('id', $request->get('id_resolucion'))
				->with('comprobante')
				->first();

			$consecutivo = $this->getNextConsecutive($resolucion->comprobante->id, $request->get('fecha_manual'));
		}

        return response()->json([
    		'success'=>	true,
    		'data' => $consecutivo,
    		'message'=> 'Consecutivo siguiente generado con exito!'
    	]);
    }

	public function getAnioCerrado(Request $request)
	{
		$empresa = Empresa::where('id', request()->user()->id_empresa)->first();

		return response()->json([
			'success'=>	true,
			'data' =>  $empresa->fecha_ultimo_cierre,
			'message'=> 'Año cerrado consultado con exito!'
		], 200);
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

	private function generateTokenDocumento()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

	public function comboYear()
	{
		$years = DocumentosGeneral::where('anulado', 0)
			->select(
				DB::raw("DATE_FORMAT(fecha_manual, '%Y') id"),
				DB::raw("DATE_FORMAT(fecha_manual, '%Y') text")
			)
			->groupBy(DB::raw("DATE_FORMAT(fecha_manual, '%Y')"));

		return $years->paginate(40);
	}
}
