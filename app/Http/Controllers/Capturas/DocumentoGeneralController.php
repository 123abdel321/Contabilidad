<?php

namespace App\Http\Controllers\Capturas;

use DB;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\CentroCostos;
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
        return view('pages.capturas.documento_general.documento_general-view');
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
		
		try {

			DB::connection('sam')->beginTransaction();

			$documento = $request->get('documento');
			$documentoGeneral = new Documento($request->get('id_comprobante'), null, $request->get('fecha_manual'), $request->get('consecutivo'));
			
			DocumentosGeneral::whereDocumento(
				$request->get('id_comprobante'),
				$request->get('consecutivo'),
				$request->get('fecha')
			)->delete();

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
			}

			DB::connection('sam')->commit();
			return response()->json([
				'success'=>	true,
				'data' => '',
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
			->whereDocumento($request->get('id_comprobante'), $request->get('consecutivo'), $request->get('fecha_manual'))
            ->get();

		return response()->json([
			'success'=>	true,
			'data' => $documento,
			'message'=> 'Documentos creados con exito!'
		]);
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
        $consecutivo = $this->getNextConsecutive($request->get('id_comprobante'), $request->get('fecha_manual'));

        return response()->json([
    		'success'=>	true,
    		'data' => $consecutivo,
    		'message'=> 'Consecutivo siguiente generado con exito!'
    	]);
    }
}
