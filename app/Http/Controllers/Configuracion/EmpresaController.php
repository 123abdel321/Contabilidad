<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\VariablesEntorno;
use App\Models\Empresas\ResponsabilidadesTributarias;

class EmpresaController extends Controller
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

    public function index(Request $request)
    {
        $user = $request->user();
        $user->permisos = [];
		$empresasExternas = $user->empresasExternas->pluck("id_empresa");
		$empresasExternas = $empresasExternas->toArray();

        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        $capturarDocumentosDescuadrados = VariablesEntorno::where('nombre', 'capturar_documento_descuadrado')->first();
        $responsabilidades = ResponsabilidadesTributarias::get();

        if (!$capturarDocumentosDescuadrados) {
            $capturarDocumentosDescuadrados = new VariablesEntorno;
            $capturarDocumentosDescuadrados->nombre = 'capturar_documento_descuadrado';
            $capturarDocumentosDescuadrados->valor = false;
            $capturarDocumentosDescuadrados->save();
        }

        $query = Empresa::whereNotNull("id");

        $query->where(function($q) use($empresasExternas,$user){
            $q->whereIn("id",$empresasExternas);
            $q->orWhere("id_usuario_owner",$user->id);
        });

        $data = [
            'empresa' => $empresa,
            'empresas' => $query->get(),
            'responsabilidades' => $responsabilidades,
            'capturarDocumentosDescuadrados' => $capturarDocumentosDescuadrados,
        ];
        
        return view('pages.configuracion.empresa.empresa-view', $data);
    }

    public function updateEmpresa(Request $request)
    {
        $rules = [
            'nombre' => 'max:200',
            // 'codigos_responsabilidades' => 'max:200',
            'actividad_economica' => 'nullable',
            // 'pais' => 'max:100',
            // 'departamento' => 'max:100',
            // 'ciudad' => 'max:100',
			'nit' => 'required|max:200',
			'dv' => "between:0,9|numeric|required",
			'tipo_contribuyente' => 'required|in:1,2',
			'primer_apellido' => 'nullable|string|max:60|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_NATURAL, // Campo requerido si el tipo contribuyente es persona natural (id: 2)
			'segundo_apellido' => 'nullable|string|max:60|',
			'primer_nombre' => 'nullable|string|max:60|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_NATURAL, // Campo requerido si el tipo contribuyente es persona natural (id: 2)
			'otros_nombres' => 'nullable|string|max:60',
			'razon_social' => 'nullable|string|max:120|required_if:tipo_contribuyente,'.Nits::TIPO_CONTRIBUYENTE_PERSONA_JURIDICA, // Campo requerido si el tipo contribuyente es persona jurídica (id: 1)
			'direccion' => 'nullable|min:3|max:100',
			'telefono' => 'nullable|numeric|digits_between:1,30',
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
            Empresa::where('token_db', $request->user()['has_empresa'])
                ->update([
                    'nombre' => $request->get('nombre'),
                    'primer_apellido' => $request->get('primer_apellido'),
                    'segundo_apellido' => $request->get('segundo_apellido'),
                    'primer_nombre' => $request->get('primer_nombre'),
                    'otros_nombres' => $request->get('otros_nombres'),
                    'tipo_contribuyente' => $request->get('tipo_contribuyente'),
                    'razon_social' => $request->get('razon_social'),
                    'codigos_responsabilidades' => implode(',', $request->get('id_responsabilidades')),
                    'nit' => $request->get('nit'),
                    'dv' => $request->get('dv'),
                    'telefono' => $request->get('telefono'),
                ]);
            
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            copyDBConnection($empresa->servidor ?: 'sam', 'sam');
            setDBInConnection('sam', $empresa->token_db);

            VariablesEntorno::where('nombre', 'capturar_documento_descuadrado')
                ->update([
                    'valor' => $request->get('capturar_documento_descuadrado') ? $request->get('capturar_documento_descuadrado') : 0,
                ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Empresa actualizada con exito!'
            ]);

        }  catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function comboResponsabilidades(Request $request)
    {
        $responsabilidades = ResponsabilidadesTributarias::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $responsabilidades->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $responsabilidades->paginate(40);
    }
}