<?php

namespace App\Http\Controllers\Capturas;

use DB;
use DateTimeImmutable;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
                "message"=>$validator->messages()
            ], 422);
        }

		$empresa = Empresa::where('id', request()->user()->id_empresa)->first();
		$fechaCierre= DateTimeImmutable::createFromFormat('Y-m-d', $empresa->fecha_ultimo_cierre);
        $fechaManual = DateTimeImmutable::createFromFormat('Y-m-d', $request->get('fecha_manual'));

        if ($fechaManual <= $fechaCierre) {
			return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>['fecha_manual' => ['mensaje' => 'Se esta grabando en un año cerrado']]
            ], 422);
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
					], 422);
				}
			}

			DocumentosGeneral::where('id_comprobante', $request->get('id_comprobante'))
				->where('consecutivo', $request->get('consecutivo'))
				->where('fecha_manual', $request->get('fecha_manual'))
				->delete();

			$facDocumento = null;

			if($request->get('editing_documento')) {
				$facDocumento = FacDocumentos::where('id_comprobante', $request->get('id_comprobante'))
					->where('consecutivo', $request->get('consecutivo'))
					->first();
			} else {
				$facDocumento = FacDocumentos::create([
					'id_comprobante' => $request->get('id_comprobante'),
					'fecha_manual' => $request->get('fecha_manual'),
					'consecutivo' => $request->get('consecutivo'),
					'debito' => $debito,
					'credito' => $credito,
					'saldo_final' => $debito - $credito,
					'created_by' => request()->user()->id,
					'updated_by' => request()->user()->id,
				]);
			}

			$documentoGeneral = new Documento($request->get('id_comprobante'), $facDocumento, $request->get('fecha_manual'), $request->get('consecutivo'));

			if ($request->get('editing_documento')) $documentoGeneral->setCreatedAt($facDocumento->created_at);

			foreach ($documento as $doc) {
				
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
					unset($doc['numero_documento']);
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
				], 422);
			}

			if(!$request->get('editing_documento')) {
				$this->updateConsecutivo($request->get('id_comprobante'), $request->get('consecutivo'));
			} else {
				$facDocumento->fecha_manual = $request->get('fecha_manual');
				$facDocumento->consecutivo = $request->get('consecutivo');
				$facDocumento->debito = $debito;
				$facDocumento->credito = $credito;
				$facDocumento->saldo_final = $debito - $credito;
				$facDocumento->updated_by = request()->user()->id;
				$facDocumento->save();
			}

			DB::connection('sam')->commit();
			
			return response()->json([
				'success'=>	true,
				'data' => $documentoGeneral->getRows(),
				'impresion' => $comprobante->imprimir_en_capturas ? $facDocumento->id : '',
				'message'=> 'Documentos creados con exito!'
			], 200);

		} catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function generate(Request $request)
    {
		$documento = DocumentosGeneral::with(['centro_costos', 'cuenta', 'nit'])
			->where('id_comprobante', $request->get('id_comprobante'))
			->where('consecutivo', $request->get('consecutivo'))
			->where('fecha_manual', $request->get('fecha_manual'))
            ->get();

		return response()->json([
			'success'=>	true,
			'data' => $documento,
			'message'=> 'Documentos creados con exito!'
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
                "message"=>$validator->messages()
            ], 422);
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
            ], 422);
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
}
