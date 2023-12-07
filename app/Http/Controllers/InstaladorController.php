<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Jobs\ProcessProvisionedDatabase;
use Illuminate\Support\Facades\Validator;
// MODELS
use App\Models\User;
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Empresas\EmpresaSuscripcion;
use App\Models\Empresas\BaseDatosProvisionada;
use App\Models\Empresas\ComponentesSuscripcion;
use App\Models\Empresas\EmpresaComponentesSuscripcion;


class InstaladorController extends Controller
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
    
    public function createEmpresa(Request $request)
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

			$valid_exist_cliente = Empresa::where('nit',$request->get('nit'))->get();

			foreach($valid_exist_cliente as $empresa){
				if($empresa->estado == 5) {
					// $transaccion = CliTransacciones::where('id_empresa',$empresa->id)->where('estado',0)->first();

					// if($transaccion){
						return response()->json([
							"success"=>false,
							"errors"=>["La empresa ".$empresa->nombre." con nit ".$empresa->nit." tiene un proceso de pago pendiente. Por favor intenta más tarde"]
						], Response::HTTP_UNPROCESSABLE_ENTITY);
					// }
				}else{
					return response()->json([
						"success"=>false,
						"errors"=>["La empresa ".$empresa->nombre." con nit ".$empresa->nit." ya está registrada."]
					], Response::HTTP_UNPROCESSABLE_ENTITY);
				}
			}

			$user = $request->user();

			$empresa = Empresa::create([
				'servidor' => 'sam',
				'nombre' => $request->razon_social ?? $request->primer_nombre .' '. $request->primer_apellido,
				'primer_apellido' => $request->primer_apellido,
				'segundo_apellido' => $request->segundo_apellido,
				'primer_nombre' => $request->primer_nombre,
				'otros_nombres' => $request->otros_nombres,
				'tipo_contribuyente' => $request->tipo_contribuyente,
				'razon_social' => $request->razon_social,
				'nit' => $request->nit,
				'dv' => $request->dv,
				'telefono' => $request->telefono,
				'id_usuario_owner' => $user->id,
				'estado' => 0
			]);

			$this->associateComponentsToCompany($empresa); 

			$empresa->hash = Hash::make($empresa->id);
			$empresa->save();

			$this->associateUserToCompany($user, $empresa);

			$dbProvisionada = BaseDatosProvisionada::available()->first();

			if (!$dbProvisionada) {
				ProcessProvisionedDatabase::dispatch($empresa->id);

				return response()->json([
					"success" => true,
					'data' => $empresa,
					"message" => 'La instalación se está procesando, verifique en 5 minutos.'
				], 200);
			}

			$dbProvisionada->ocupar();

			$empresa->token_db = $dbProvisionada->hash;

			$empresa->estado = 1;
			$empresa->save();

			copyDBConnection($empresa->servidor ?: 'sam', 'sam');
			setDBInConnection('sam', $empresa->token_db);

			Nits::create([
                'id_tipo_documento' => 3,
                'numero_documento' => $request->nit,
				'digito_verificacion' => $request->dv,
                'tipo_contribuyente' => $request->tipo_contribuyente,
                'primer_apellido' => $request->primer_apellido,
                'segundo_apellido' => $request->segundo_apellido,
                'primer_nombre' => $request->primer_nombre,
                'otros_nombres' => $request->otros_nombres,
                'razon_social' => $request->razon_social,
                // 'direccion' => $request->get('direccion'),
                // 'email' => $request->get('email'),
                'telefono_1' => $request->telefono,
                // 'id_ciudad' => $request->get('id_ciudad'),
                // 'observaciones' => $request->get('observaciones'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

			DB::connection('sam')->commit();

			return response()->json([
				"success" => true,
				"data" => 'Instalación Finalizada. Hemos enviado a tu email el instructivo para continuar con el acceso.'
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

	private function associateComponentsToCompany($empresa)
	{
		$totalPrecio = 0;
		$totalDescuento = 0;

		$suscripcionSuscripcion = [];
		$componentesSeleccionados = ComponentesSuscripcion::all();

		$suscripcion = EmpresaSuscripcion::create([
			'id_empresa' => $empresa->id,
			'id_suscripcion' => 2,
			'id_forma_pago' => 1,
			'dias_para_pagar' => 0,
			'dias_de_gracia' => 0,
			'fecha_inicio_suscripcion' => '',
			'fecha_inicio_facturacion' => '',
			'fecha_siguiente_pago' => '',
			'estado' => 1,
			'duracion' => '',
			'precio' => '',
			'descuento' => '',
		]);

		foreach ($componentesSeleccionados as $componentesSeleccionado) {
			$suscripcionSuscripcion[] = [
				'id_empresa' => $empresa->id,
				'id_empresa_suscripcion' => $suscripcion->id,
				'id_componente' => $componentesSeleccionado->id,
				'precio' => 0
			];
		}

		EmpresaComponentesSuscripcion::insert($suscripcionSuscripcion);

		return;
	}

    private function associateUserToCompany($user, $empresa)
	{
		$usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $user->id)
			->where('id_empresa', $empresa->id)
			->first();

			User::where('id', $user->id)->update([
				'id_empresa' => $empresa->id,
				'has_empresa' => $empresa->hash
			]);

		if(!$usuarioEmpresa){
			UsuarioEmpresa::create([
				'id_usuario' => $user->id,
				'id_empresa' => $empresa->id,
				'id_rol' => 2, // default: 2
				'estado' => 1, // default: 1 activo
			]);
		}

		$permisos = [];
		$permissions = Permission::all();
		$rol = Role::where('id', 2)->first();

		foreach ($permissions as $permissions) {

			$permisos[] = $permissions->id;
		}
		
		$user->syncRoles($rol);
		$user->syncPermissions($permisos);
		UsuarioPermisos::updateOrCreate([
			'id_user' => $user->id,
			'id_empresa' => $empresa->id
		],[
			'ids_permission' => implode(',', $permisos)
		]);

		return;
	}

}
