<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Exception;
use Smalot\PdfParser\Parser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\User;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;
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
        // $user = $request->user();
        // $user->permisos = [];
		// $empresasExternas = $user->empresasExternas->pluck("id_empresa");
		// $empresasExternas = $empresasExternas->toArray();

        // $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
        // $capturarDocumentosDescuadrados = VariablesEntorno::where('nombre', 'capturar_documento_descuadrado')->first();
        // $responsabilidades = ResponsabilidadesTributarias::get();

        // if (!$capturarDocumentosDescuadrados) {
        //     $capturarDocumentosDescuadrados = new VariablesEntorno;
        //     $capturarDocumentosDescuadrados->nombre = 'capturar_documento_descuadrado';
        //     $capturarDocumentosDescuadrados->valor = false;
        //     $capturarDocumentosDescuadrados->save();
        // }

        // $query = Empresa::whereNotNull("id");

        // $query->where(function($q) use($empresasExternas,$user){
        //     $q->whereIn("id",$empresasExternas);
        //     $q->orWhere("id_usuario_owner",$user->id);
        // });

        // $data = [
        //     'empresa' => $empresa,
        //     'empresas' => $query->get(),
        //     'responsabilidades' => $responsabilidades,
        //     'capturarDocumentosDescuadrados' => $capturarDocumentosDescuadrados,
        // ];
        
        return view('pages.configuracion.empresa.empresa-view');
    }

    public function generate (Request $request)
    {
        try {
            
            $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', request()->user()->id)
                ->get();
                
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc
            $searchValue = $search_arr['value']; // Search value

            $empresas = Empresa::orderBy($columnName,$columnSortOrder)
                ->with('usuario');

            if (!request()->user()->rol_portafolio) {
                $idEmpresas = [];
                
                foreach ($usuarioEmpresa as $key => $empresa) {
                    array_push($idEmpresas, $empresa->id_empresa);
                }
                
                $empresas->whereIn('id', $idEmpresas);
            }

            $empresasTotals = $empresas->get();

            $empresasPaginate = $empresas->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $empresasTotals->count(),
                'iTotalDisplayRecords' => $empresasTotals->count(),
                'data' => $empresasPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Empresas generadas con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function rut (Request $request)
    {
        $rules = [
            'file_rut_empresa' => 'required|mimes:pdf|max:1024'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {

            $parser = new Parser;
            $pdf = $parser->parseFile($request->file('file_rut_empresa'));
            $pages = $pdf->getPages();

            $data = [
                'dv' => null,
                'nit' => null,
                'email' => null,
                'telefono' => null,
                'direccion' => null,
                'razon_social' => null,
                'nombre_completo' => null,
            ];

            foreach ($pages as $page) {
                $text = nl2br($page->getText());
                $text = str_replace(["\n","\t"], " ", $text);
                $dataPage = explode('<br />', $text);
                $nitCompleto = $this->getNitCompleto($dataPage);
                
                $data['dv'] = substr($nitCompleto, -1);
                $data['nit'] = substr($nitCompleto, 0, -1);
                $data['email'] = $this->getEmail($dataPage);
                $data['telefono'] = $this->getTelefono($dataPage);
                $data['direccion'] = $this->getDireccion($dataPage);
                $data['razon_social'] = $this->getRazonSocial($dataPage);
                $data['nombre_completo'] = $this->getNombreCompleto($dataPage);

                return response()->json([
                    "success" => true,
                    "data" => $data,
                ]);
            }            

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
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
                "message"=>$validator->errors()
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
                    'fecha_ultimo_cierre' => $request->get('fecha_ultimo_cierre'),
                    'dv' => $request->get('dv'),
                    'telefono' => $request->get('telefono'),
                ]);

            $user = User::where('id', $request->user()->id)->first();
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            if($request->fondo_imagen) {
                $image = $request->fondo_imagen;
                $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
                $image = str_replace('data:image/'.$ext.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'fondo_imagen_'.$request->get('codigo').'_'.uniqid().'.'. $ext;
                
                Storage::disk('do_spaces')->put('imagen/empresa/'.$imageName, base64_decode($image), 'public');
                
                $user->fondo_sistema = 'imagen/empresa/'.$imageName;
                $user->save();
            }

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
                'fondo_sistema' => $user->fondo_sistema,
                'message'=> 'Empresa actualizada con exito!'
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

    private function getNitCompleto($dataPage)
    {
        if (array_key_exists(85, $dataPage)) {
            return str_replace(" ","",$dataPage[85]);
        }
        if (array_key_exists(86, $dataPage)) {
            return str_replace(" ","",$dataPage[86]);
        }
        return null;
    }

    private function getDireccion($dataPage)
    {
        if (array_key_exists(94, $dataPage)) {
            return str_replace("  ","",substr($dataPage[94], 1));
        }
        return null;
    }

    private function getEmail($dataPage)
    {
        if (array_key_exists(95, $dataPage)) {
            return str_replace(" ","",$dataPage[95]);
        }
        return null;
    }

    private function getTelefono($dataPage)
    {
        if (array_key_exists(96, $dataPage)) {
            return str_replace(" ","",$dataPage[96]);
        }
        return null;
    }

    private function getRazonSocial($dataPage)
    {
        if (array_key_exists(92, $dataPage)) {
            return str_replace("  ","",substr($dataPage[92], 1));
        }
        return null;
    }

    private function getNombreCompleto($dataPage)
    {
        if (array_key_exists(90, $dataPage)) {
            return str_replace("  ","",substr($dataPage[90], 1));
        }
        return null;
    }
}