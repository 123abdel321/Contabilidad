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

    public function generate(Request $request)
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

        $idEmpresa = $request->user()['id_empresa'];
        
        // Consulta base: usuarios que pertenecen a la empresa actual
        $usuarios = User::orderBy($columnName, $columnSortOrder)
            ->with(['roles', 'permissions'])
            ->whereHas('empresasExternas', function ($query) use ($idEmpresa) {
                $query->where('id_empresa', $idEmpresa);
            })
            ->withWhereHas('permisos', function ($query) use ($idEmpresa) {
                $query->where('id_empresa', $idEmpresa);
            })
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        // Aplicar búsqueda si existe
        if ($searchValue) {
            $usuarios->where(function($query) use ($searchValue) {
                $query->where('username', 'like', '%' . $searchValue . '%')
                    ->orWhere('firstname', 'like', '%' . $searchValue . '%')
                    ->orWhere('lastname', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            });
        }

        // Obtener el total de registros
        $usuariosTotals = $usuarios->get();

        // Obtener datos paginados
        $usuariosPaginate = $usuarios->skip($start)
            ->take($rowperpage)
            ->get();

        return response()->json([
            'success' => true,
            'draw' => $draw,
            'iTotalRecords' => $usuariosTotals->count(),
            'iTotalDisplayRecords' => $usuariosTotals->count(),
            'data' => $usuariosPaginate,
            'perPage' => $rowperpage,
            'message' => 'Usuarios cargados con éxito!'
        ]);
    }

    public function create(Request $request)
    {        
        $rules = [
            'usuario' => 'required|string|min:1',
            'email' => 'required|email|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'permisos' => 'array|required',
            'permisos.*.id_permiso' => 'required|exists:clientes.permissions,id',
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('sam')->beginTransaction();

            // Verificar si el usuario ya existe por username o email
            $usuarioExistente = User::where('username', $request->get('usuario'))
                ->orWhere('email', $request->get('email'))
                ->first();

            $empresaActualId = $request->user()['id_empresa'];
            $empresaActualHash = $request->user()['has_empresa'];
            $usuarioCreadorId = request()->user()->id;

            // Verificar si el usuario ya está asociado a esta empresa
            $yaAsociadoEmpresa = false;
            if ($usuarioExistente) {
                $yaAsociadoEmpresa = UsuarioEmpresa::where('id_usuario', $usuarioExistente->id)
                    ->where('id_empresa', $empresaActualId)
                    ->exists();
            }

            // Si el usuario existe y ya está asociado a esta empresa, retornar error
            if ($usuarioExistente && $yaAsociadoEmpresa) {
                DB::connection('sam')->rollback();
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => "El usuario ya existe y está asociado a esta empresa"
                ], 422);
            }

            $usuario = null;

            if ($usuarioExistente && !$yaAsociadoEmpresa) {
                // CASO 1: Usuario existe pero no está asociado a esta empresa
                $usuario = $usuarioExistente;
                
                // Actualizar datos del usuario si se proporcionaron
                $actualizacionUsuario = [
                    'updated_by' => $usuarioCreadorId,
                ];
                
                // Solo actualizar campos si se proporcionan y son diferentes
                if ($request->has('firstname')) {
                    $actualizacionUsuario['firstname'] = $request->get('firstname');
                }
                if ($request->has('lastname')) {
                    $actualizacionUsuario['lastname'] = $request->get('lastname');
                }
                if ($request->has('address')) {
                    $actualizacionUsuario['address'] = $request->get('address');
                }
                if ($request->has('telefono')) {
                    $actualizacionUsuario['telefono'] = $request->get('telefono');
                }
                
                $usuario->update($actualizacionUsuario);
                
            } else {
                // CASO 2: Usuario no existe, crearlo nuevo
                // Validar unicidad para nuevo usuario
                $uniqueRules = [
                    'usuario' => 'unique:App\Models\User,username',
                    'email' => 'unique:App\Models\User,email',
                ];
                
                $uniqueValidator = Validator::make($request->all(), $uniqueRules);
                
                if ($uniqueValidator->fails()) {
                    DB::connection('sam')->rollback();
                    return response()->json([
                        "success" => false,
                        'data' => [],
                        "message" => $uniqueValidator->errors()
                    ], 422);
                }

                $usuario = User::create([
                    'username' => $request->get('usuario'),
                    'id_empresa' => $empresaActualId,
                    'has_empresa' => $empresaActualHash,
                    'firstname' => $request->get('firstname'),
                    'lastname' => $request->get('lastname'),
                    'email' => $request->get('email'),
                    'address' => $request->get('address'),
                    'telefono' => $request->get('telefono'),
                    'created_by' => $usuarioCreadorId,
                    'updated_by' => $usuarioCreadorId,
                ]);

                // Actualizar password si se proporciona
                if ($request->get('password')) {
                    $usuario->update([
                        'password' => $request->get('password')
                    ]);
                }
            }

            // Obtener el rol
            $rol = Role::where('id', $request->get('rol_usuario'))->first();
            
            if (!$rol) {
                throw new Exception("El rol especificado no existe");
            }

            // Procesar permisos
            $permisos = [];
            if (count($request->get('permisos')) > 0) {
                foreach ($request->get('permisos') as $permiso) {
                    if ($permiso['value'] == "1") {
                        $permisos[] = $permiso['id_permiso'];
                    }
                }
            }

            // Asociar usuario a la empresa (crear o actualizar)
            UsuarioEmpresa::updateOrCreate(
                [
                    'id_usuario' => $usuario->id,
                    'id_empresa' => $empresaActualId
                ],
                [
                    'id_rol' => $request->get('rol_usuario'), 
                    'estado' => 1,
                ]
            );

            // Sincronizar roles y permisos
            $usuario->syncRoles($rol);
            $usuario->syncPermissions($permisos);

            // Crear o actualizar permisos específicos del usuario
            UsuarioPermisos::updateOrCreate(
                [
                    'id_user' => $usuario->id,
                    'id_empresa' => $empresaActualId
                ],
                [
                    'ids_bodegas_responsable' => implode(",", $request->get('id_bodega', [])),
                    'ids_resolucion_responsable' => implode(",", $request->get('id_resolucion', [])),
                    'ids_permission' => implode(',', $permisos)
                ]
            );

            DB::connection('sam')->commit();

            $mensaje = $usuarioExistente 
                ? "Usuario asociado a la empresa con éxito!" 
                : "Usuario creado con éxito!";

            return response()->json([
                'success' => true,
                'data' => $usuario,
                'message' => $mensaje,
                'nuevo_usuario' => !$usuarioExistente
            ]);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
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
