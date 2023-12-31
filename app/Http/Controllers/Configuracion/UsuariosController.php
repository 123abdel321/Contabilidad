<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacBodegas;
use App\Models\Empresas\UsuarioEmpresa;
use App\Models\Sistema\FacResoluciones;
use App\Models\Empresas\UsuarioPermisos;



class UsuariosController extends Controller
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

    public function index (Request $request)
    {
        $empresa = Empresa::where('token_db', $request->user()['has_empresa'])
            ->with(
                'suscripcionActiva.componentes.menus.permisos',
                'suscripcionActiva.componentes.menus.padre',
                'suscripcionActiva.componentes.componente',
            )
            ->first();

        $data = [
            'roles' => Role::where('id', '!=', 1)->get(),
            'bodegas' => FacBodegas::all(),
            'resoluciones' => FacResoluciones::all(),
            'componentes' => $empresa->suscripcionActiva->componentes
        ];
        
        return view('pages.configuracion.usuarios.usuarios-view', $data);
    }

    public function generate (Request $request)
    {
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

        $usuarios = User::orderBy($columnName,$columnSortOrder)
            ->with('roles', 'permissions')
            ->where('id_empresa', $request->user()['id_empresa'])
            ->withWhereHas('permisos', function ($query) use ($request){
                $query->where('id_empresa', $request->user()['id_empresa']);
            })
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        if ($searchValue) {
            $usuarios = $usuarios->where('username', 'like', '%' .$searchValue. '%')
                ->orWhere('firstname', 'like', '%' .$searchValue. '%')
                ->orWhere('lastname', 'like', '%' .$searchValue. '%')
                ->orWhere('email', 'like', '%' .$searchValue. '%');
        }

        $usuarios = $usuarios->where('id_empresa', $request->user()['id_empresa']);
        $usuariosTotals = $usuarios->get();

        $usuariosPaginate = $usuarios->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $usuariosTotals->count(),
            'iTotalDisplayRecords' => $usuariosTotals->count(),
            'data' => $usuariosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Usuarios cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {        
        $rules = [
            'usuario' => 'required|string|min:1|unique:App\Models\User,username',
            'email' => 'required|email|string|max:255|unique:App\Models\User,email',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'permisos' => 'array|required',
            'permisos.*.id_permiso' => 'required|exists:clientes.permissions,id',
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
            
            DB::connection('sam')->beginTransaction();

            $rol = Role::where('id', $request->get('rol_usuario'))->first();

            $usuario = User::create([
                'username' => $request->get('usuario'),
                'id_empresa' => $request->user()['id_empresa'],
                'has_empresa' => $request->user()['has_empresa'],
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('email'),
                'address' => $request->get('address'),
                'telefono' => $request->get('telefono'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            UsuarioPermisos::updateOrCreate([
                'id_user' => $usuario->id,
                'id_empresa' => $request->user()['id_empresa']
            ],[
                'ids_bodegas_responsable' => implode(",",$request->get('id_bodega')),
                'ids_resolucion_responsable' => implode(",",$request->get('id_resolucion')),
            ]);

            $permisos = [];

            if (count($request->get('permisos')) > 0) {
                foreach ($request->get('permisos') as $permiso) {
                    if ($permiso['value'] == "1") {
                        $permisos[] = $permiso['id_permiso'];
                    }
                }
            }

            if ($request->get('password')) {
                $usuario->update([
                    'password' => $request->get('password')
                ]);
            }

            UsuarioEmpresa::create([
                'id_usuario' => $usuario->id,
                'id_empresa' => $request->user()['id_empresa'],
                'id_rol' => $request->get('rol_usuario'), 
                'estado' => 1,
            ]);

            $usuario->syncRoles($rol);
            $usuario->syncPermissions($permisos);

            UsuarioPermisos::updateOrCreate([
                'id_user' => $usuario->id,
                'id_empresa' => $request->user()['id_empresa']
            ],[
                'ids_permission' => implode(',', $permisos)
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $usuario,
                'message'=> 'Usuario creado con exito!'
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
        $rules = [
            'id' => 'required|exists:App\Models\User,id',
            'usuario' => 'required|string|min:1|unique:App\Models\User,username',
            "usuario" => [
                "required","string",
				function ($attribute, $value, $fail) use ($request) {
                    $existeUsuario = User::where('username', $request->get('usuario'))->where('id', '!=', $request->get('id'));
					if ($existeUsuario->count()) {
                        $fail("El usuario (".$value.") ya se encuentra en uso.");
                    }
				},
            ],
            "email" => [
                "required","email","string","max:255",
				function ($attribute, $value, $fail) use ($request) {
                    $existeCorreo = User::where('email', $request->get('email'))->where('id', '!=', $request->get('id'));
					if ($existeCorreo->count()) {
                        $fail("El correo (".$value.") ya se encuentra en uso.");
                    }
				},
            ],
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255'
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

            DB::connection('sam')->beginTransaction();

            $rol = Role::where('id', $request->get('rol_usuario'))->first();

            $usuario = User::where('id', $request->get('id'))->first();
            $usuario->username = $request->get('usuario');
            $usuario->id_empresa = $request->user()['id_empresa'];
            $usuario->has_empresa = $request->user()['has_empresa'];
            $usuario->firstname = $request->get('firstname');
            $usuario->lastname = $request->get('lastname');
            $usuario->email = $request->get('email');
            $usuario->address = $request->get('address');
            $usuario->telefono = $request->get('telefono');
            $usuario->facturacion_rapida = $request->get('facturacion_rapida');
            $usuario->updated_by = request()->user()->id;
            $usuario->save();

            if ($request->get('password')) {
                $usuario->update([
                    'password' => $request->get('password')
                ]);
            }

            $permisos = [];

            if (count($request->get('permisos')) > 0) {
                foreach ($request->get('permisos') as $permiso) {
                    if ($permiso['value'] == "1") {
                        $permisos[] = $permiso['id_permiso'];
                    }
                }
            }
            $usuario->syncRoles($rol);
            $usuario->syncPermissions($permisos);

            UsuarioPermisos::updateOrCreate([
                'id_user' => $usuario->id,
                'id_empresa' => $request->user()['id_empresa']
            ],[
                'ids_permission' => implode(',', $permisos),
                'ids_bodegas_responsable' => implode(",", $request->get('id_bodega')),
                'ids_resolucion_responsable' => implode(",", $request->get('id_resolucion')),
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,   
                'data' => $usuario,
                'message'=> 'Usuario actualizado con exito!'
            ]);

            return response()->json([
                'success'=>	true,
                'data' => $usuario,
                'message'=> 'Usuario actualizado con exito!'
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

    public function comboUsuario (Request $request)
    {
        $usuario = User::where('id_empresa', $request->user()['id_empresa'])
            ->select(
                \DB::raw('*'),
                \DB::raw("CONCAT(firstname, ' ', lastname) as text")
            );

        if ($request->get("q")) {
            $usuario->where('firstname', 'LIKE', '%' . $request->get("q") . '%');
            $usuario->Orwhere('lastname', 'LIKE', '%' . $request->get("q") . '%');
            $usuario->Orwhere('email', 'LIKE', '%' . $request->get("q") . '%');
            $usuario->Orwhere('username', 'LIKE', '%' . $request->get("q") . '%');
            $usuario->Orwhere('telefono', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $usuario->paginate(40);
    }

}
