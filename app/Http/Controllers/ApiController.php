<?php

namespace App\Http\Controllers;

use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
//MODELS
use App\Models\User;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;
use App\Models\Empresas\UsuarioPermisos;

class ApiController extends Controller
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
        $data = [
            'empresas' => $this->dataEmpresas($request)->items()
        ];
        
        return view('seleccionar-empresa', $data);
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        $data = json_decode($request->getContent());
        $user = User::where('email', $data->email)->first();

        try {
            if($user){
                if(Hash::check($data->password, $user->password)){
                    $tokenExist = null;

                    if($tokenExist) {
                        $token = $user->remember_token;
                    } else {
                        $token = $user->createToken("api_token")->plainTextToken;
                        $user->remember_token = $token;
                        $user->save();
                    }
                    
                    $empresaSelect = UsuarioEmpresa::where('id_usuario', $user->id)
                        ->where('id_empresa', $user->id_empresa)
                        ->first();
                    
                    if (!$empresaSelect) {
                        return response()->json([
                            'success'=>	true,
                            'access_token' => $token,
                            'empresa' => '',
                            'token_type' => 'Bearer',
                            'message'=> 'Usuario logeado con exito!'
                        ], 200);
                    }

                    $user->has_empresa = $empresaSelect->token_db;
                    $user->save();

                    $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', $user->id)
                        ->where('id_empresa', $empresaSelect->id_empresa)
                        ->first();
        
                    $user->syncPermissions(explode(',', $usuarioPermisosEmpresa->ids_permission));

                    return response()->json([
                        'success'=>	true,
                        'access_token' => $token,
                        'empresa' => $empresaSelect ? $empresaSelect->razon_social : '',
                        'token_type' => 'Bearer',
                        'message'=> 'Usuario logeado con exito!'
                    ], 200);
                }
            }

            return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Credenciales incorrectas o el usuario no existe!'
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function register(Request $request)
    {
        if($request->get('es_carta') == 'soy yo'){
            $rules = [
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'email' => 'required|string|email|max:255|unique:users',
                'telefono' => 'required|string',
                'documento' => 'required|string',
                'tipo_documento' => 'required|string',
                'password' => 'required|string'
            ];
    
            $validator = Validator::make($request->all(), $rules, $this->messages);
    
            if ($validator->fails()){
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], 422);
            }
            // dd(Hash::make($request->password), bcrypt($request->password));
            $user = User::create($request->all());
    
            // $user = User::create([
            //     'name' => $request->name,
            //     'email' => $request->email,
            //     'telefono' => $request->telefono,
            //     'documento' => $request->documento,
            //     'tipo_documento' => $request->tipo_documento,
            //     'password' => bcrypt($request->password)
            // ]);
    
            if($user){
                // $this->sendWelcomeEmail($user);
    
                // if($request->get("empresa")){
                //     $this->associateUserToCompany($user, $request->get("empresa"));
                // }
    
                return response()->json([
                    "success" => true,
                    "data" => [],
                    "message" => 'Usuario registrado con exito!'
                ], 200);
            }
        }

        return response()->json([
            "success" => true,
            "data" => [],
            "message" => 'Usuario registrado con exito!'
        ], 200);
    }

    public function setEmpresa(Request $request)
    {
        $empresaSelect = Empresa::where('hash', $request->get("empresa"))->first();
		$check = $request->user()->checkRelacionEmpresa($request->get("empresa"));

        if($request->user()->rol_portafolio && !$check){
            $usuarioPermisosEmpresa = UsuarioPermisos::updateOrCreate([
                'id_user' => $request->user()->id,
                'id_empresa' => $empresaSelect->id
            ],[
                'ids_permission' => UsuarioPermisos::where('id_empresa', $empresaSelect->id)->first()->ids_permission,
                'ids_bodegas_responsable' => '1',
                'ids_resolucion_responsable' => '1'
            ]);

            $check = true;
        }
        
		if($check){
            
            $user = $request->user();
            $user->id_empresa = $empresaSelect->id;
            $user->has_empresa = $empresaSelect->token_db;
            $user->save();

            $notificacionCode = $empresaSelect->token_db.'_'.$user->id;

            $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', request()->user()->id)
                ->where('id_empresa', $empresaSelect->id)
                ->first();

            $user->syncPermissions(explode(',', $usuarioPermisosEmpresa->ids_permission));

			return response()->json([
				"success"=>true,
				"empresa"=>$empresaSelect,
                "notificacion_code"=>$notificacionCode
			]);
		}else{
			return response()->json([
				"success"=>false,
				"message"=>"Intenta acceder a una empresa a la que no tiene acceso"
			],422);
		}
	}

    public function getEmpresas(Request $request){
		$user = $request->user();
		$user->permisos = [];
		$empresasExternas = $user->empresasExternas->pluck("id_empresa");
		$empresasExternas = $empresasExternas->toArray();

		$query = Empresa::whereNotNull("id");

		if($request->has("q")){
			$query->where("nombre",'LIKE',"%".$request->get("q")."%");
		}

		if((int)$user->tipo == 0){
			$query->where(function($q) use($empresasExternas,$user){
				$q->whereIn("id",$empresasExternas);
				$q->orWhere("id_usuario_owner",$user->id);
			});
		}

		if ($request->get('estado')) {
			$estado = $request->get('estado');
			$estado = is_array($estado) ? $estado : explode(',', trim($estado));

			$query->whereIn('estado', $estado);
		}

		$res = $query->paginate($request->get("limit",15),[
			"nombre",
			"nit",
			"dv",
			"id",
			"logo",
			"hash",
			"estado",
			DB::raw("IF(id_usuario_owner=".$user->id.",'1','0') as propio")
		]);

        $res = $this->dataEmpresas($request);

		return response()->json([
			"total"=>$res->total(),
			"items"=>$res->items(),
			"isQuery"=>$request->has("q")
		]);
	}

    private function dataEmpresas($request)
    {
        $user = $request->user();
		$user->permisos = [];
		$empresasExternas = $user->empresasExternas->pluck("id_empresa");
		$empresasExternas = $empresasExternas->toArray();

		$query = Empresa::whereNotNull("id");

		if($request->has("q")){
			$query->where("nombre",'LIKE',"%".$request->get("q")."%");
		}

		if((int)$user->tipo == 0){
			$query->where(function($q) use($empresasExternas,$user){
				$q->whereIn("id",$empresasExternas);
				$q->orWhere("id_usuario_owner",$user->id);
			});
		}

		if ($request->get('estado')) {
			$estado = $request->get('estado');
			$estado = is_array($estado) ? $estado : explode(',', trim($estado));

			$query->whereIn('estado', $estado);
		}

		$res = $query->paginate($request->get("limit",15),[
			"nombre",
			"nit",
			"dv",
			"id",
			"logo",
			"hash",
			"estado",
			DB::raw("IF(id_usuario_owner=".$user->id.",'1','0') as propio")
		]);

        return $res;
    }

    public function getUsuario (Request $request)
    {
        $usuario = User::where('id', $request->get('id'))->first();

        return response()->json([
            "success"=>true,
            "data"=>$usuario
        ], 200);
    }

}
