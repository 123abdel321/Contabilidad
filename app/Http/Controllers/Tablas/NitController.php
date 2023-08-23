<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\TipoDocumentos;
use App\Models\Sistema\DocumentosGeneral;

class NitController extends Controller
{
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
        return view('pages.tablas.nits.nits-view');
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 15; // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $nits = Nits::skip($start)
            ->with('tipo_documento', 'ciudad')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(nits.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nits.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nits.created_by',
                'nits.updated_by'
            )
            ->take($rowperpage);

        if($columnName){
            $nits->orderBy($columnName,$columnSortOrder);
        }

        if($searchValue) {
            $nits->where('primer_apellido', 'like', '%' .$searchValue . '%')
                ->orWhere('segundo_apellido', 'like', '%' .$searchValue . '%')
                ->orWhere('primer_nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('otros_nombres', 'like', '%' .$searchValue . '%')
                ->orWhere('email', 'like', '%' .$searchValue . '%')
                ->orWhere('telefono_1', 'like', '%' .$searchValue . '%')
                ->orWhere('razon_social', 'like', '%' .$searchValue . '%');
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $nits->count(),
            'iTotalDisplayRecords' => $nits->count(),
            'data' => $nits->get(),
            'perPage' => $rowperpage,
            'message'=> 'Comprobante generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $tipoDocumentoNit = TipoDocumentos::where('nombre', 'NIT')->first(['id']);
		$idTipoDocumentoNit = $tipoDocumentoNit ? $tipoDocumentoNit->id : 0;

        $rules = [
			'id_tipo_documento' => 'required|exists:sam.tipos_documentos,id',
			'id_ciudad' => 'nullable|exists:clientes.ciudades,id',
            'observaciones' => 'nullable|string',
			'id_actividad_econo' => 'nullable|exists:sam.actividades_economicas,id',
			'numero_documento' => 'required|unique:sam.nits,numero_documento|max:30',
			'digito_verificacion' => "nullable|between:0,9|numeric|required_if:id_tipo_documento,$idTipoDocumentoNit", // Campo requerido si el tipo de documento es nit (codigo: 31)
			'tipo_contribuyente' => 'required|in:1,2',
			'primer_apellido' => 'nullable|string|max:60|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_NATURAL, // Campo requerido si el tipo contribuyente es persona natural (id: 2)
			'segundo_apellido' => 'nullable|string|max:60|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_NATURAL, // Campo requerido si el tipo contribuyente es persona natural (id: 2)
			'primer_nombre' => 'nullable|string|max:60|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_NATURAL, // Campo requerido si el tipo contribuyente es persona natural (id: 2)
			'otros_nombres' => 'nullable|string|max:60',
			'razon_social' => 'nullable|string|max:120|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_JURIDICA, // Campo requerido si el tipo contribuyente es persona jurídica (id: 1)
			'nombre_comercial' => 'nullable|string|max:120',
			'direccion' => 'required|min:3|max:100',
			'email' => 'required|email|max:250',
			'email_recepcion_factura_electronica' => 'nullable|email|max:60',
			'telefono_1' => 'nullable|numeric|digits_between:1,30',
			'telefono_2' => 'nullable|numeric|digits_between:1,30',
			'tipo_cuenta_banco' => 'nullable|in:0,1',
			'cuenta_bancaria' => 'nullable|string|max:20',
			'plazo' => 'nullable|numeric|min:0|digits_between:1,3',
			'cupo' => 'nullable|numeric|min:0|digits_between:1,13',
			'descuento' => 'nullable|numeric|min:0|max:100',
			'no_calcular_iva' => 'nullable|boolean',
			'inactivar' => 'nullable',
		];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }

        DB::connection('sam')->beginTransaction();
        
        try {
            $nit = Nits::create([
                'id_tipo_documento' => $request->get('id_tipo_documento'),
                'numero_documento' => $request->get('numero_documento'),
                'tipo_contribuyente' => $request->get('tipo_contribuyente'),
                'primer_apellido' => $request->get('primer_apellido'),
                'segundo_apellido' => $request->get('segundo_apellido'),
                'primer_nombre' => $request->get('primer_nombre'),
                'otros_nombres' => $request->get('otros_nombres'),
                'razon_social' => $request->get('razon_social'),
                'direccion' => $request->get('direccion'),
                'email' => $request->get('email'),
                'telefono_1' => $request->get('telefono_1'),
                'id_ciudad' => $request->get('id_ciudad'),
                'observaciones' => $request->get('observaciones'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            if($request->avatar) {
                $image = $request->avatar;
                $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
                $image = str_replace('data:image/'.$ext.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'profile_'.$nit->id.'_'.uniqid().'.'. $ext;
                Storage::disk('do_spaces')->put('imagen/profile/'.$imageName, base64_decode($image), 'public');
                $nit->logo_nit = 'imagen/profile/'.$imageName;
                $nit->save();
            }

			DB::connection('sam')->commit();
    
            return response()->json([
                'success'=>	true,
                'data' => $nit,
                'message'=> 'Nit creado con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function update (Request $request)
    {
        $nitActual = Nits::find($request->get('id'));
        if($nitActual->numero_documento != $request->get('numero_documento')){
            $nitsExist = Nits::where('numero_documento', $request->get('numero_documento'));
            if($nitsExist->count() > 0){
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'El numero de documento ya esta siendo usado!'
                ]);
            }
        }

        Nits::where('id', $request->get('id'))
            ->update([
                'id_tipo_documento' => $request->get('id_tipo_documento'),
                'numero_documento' => $request->get('numero_documento'),
                'tipo_contribuyente' => $request->get('tipo_contribuyente'),
                'primer_apellido' => $request->get('primer_apellido'),
                'segundo_apellido' => $request->get('segundo_apellido'),
                'primer_nombre' => $request->get('primer_nombre'),
                'otros_nombres' => $request->get('otros_nombres'),
                'razon_social' => $request->get('razon_social'),
                'direccion' => $request->get('direccion'),
                'email' => $request->get('email'),
                'telefono_1' => $request->get('telefono_1'),
                'id_ciudad' => $request->get('id_ciudad'),
                'observaciones' => $request->get('observaciones'),
                'updated_by' => request()->user()->id,
            ]);

        $nit = Nits::where('id', $request->get('id'))->with('tipo_documento')->first();

        if($request->avatar) {
            $image = $request->avatar;
            $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
            $image = str_replace('data:image/'.$ext.';base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = 'profile_'.$nit->id.'_'.uniqid().'.'. $ext;
            Storage::disk('do_spaces')->put('imagen/profile/'.$imageName, base64_decode($image), 'public');
            $nit->logo_nit = 'imagen/profile/'.$imageName;
            $nit->save();
        }

        

        return response()->json([
            'success'=>	true,
            'data' => $nit,
            'message'=> 'Nit actualizado con exito!'
        ]);
    }

    public function delete (Request $request)
    {
        $documentos = DocumentosGeneral::where('id_nit', $request->get('id'));
        if($documentos->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'Este nit tiene transacciones contables, no puede ser eliminado!'
            ]);
        }

        Nits::where('id', $request->get('id'))->delete();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Nit eliminado con exito!'
        ]);
    }

    public function comboTipoDocumento(Request $request)
    {
        $tipoDocumento = TipoDocumentos::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $tipoDocumento->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $tipoDocumento->paginate(40);
    }

    public function comboNit(Request $request)
    {

        $nits = Nits::select(
            'id',
            'id_tipo_documento',
            'id_ciudad',
            'primer_nombre',
            \DB::raw('numero_documento AS segundo_nombre'),
            'primer_apellido',
            'segundo_apellido',
            'email',
            \DB::raw('telefono_1 AS telefono'),
            \DB::raw("(CASE
					WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN CONCAT(numero_documento, ' - ', razon_social)
					WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT( numero_documento, ' - ', CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido))
					ELSE NULL
				END) AS text")
        );

        if ($request->get("q")) {
            $nits->where('primer_apellido', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('razon_social', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('email', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('numero_documento', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $nits->paginate(40);
    }

    public function getNitInfo (Request $request)
    {
        $nit = Nits::where('id', $request->get('id_nit'))
            ->select(
                '*',
                DB::raw("CASE
                    WHEN id IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END AS nombre_nit")
            )
            ->with('ciudad');

        // $nit = Nits::whereNumeroDocumento($request->get("numero_documento"));

        return response()->json([
            "success"=>true,
            "data"=>$nit->first()
        ], 200);
    }

}
