<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Jobs\ProcessProvisionedDatabase;
use Illuminate\Support\Facades\Validator;
// MODELS
use App\Models\Sistema\Nits;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;
use App\Models\Empresas\BaseDatosProvisionada;


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
		// $dbProvisionada = BaseDatosProvisionada::available()->first();
		// dd($dbProvisionada, !$dbProvisionada);
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

        $empresa->hash = Hash::make($empresa->id);
        $empresa->save();

        $this->associateUserToCompany($user, $empresa->id);

        $dbProvisionada = BaseDatosProvisionada::available()->first();
		// dd($dbProvisionada);
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

        return response()->json([
            "success" => true,
            "data" => 'Instalación Finalizada. Hemos enviado a tu email el instructivo para continuar con el acceso.'
        ], 200);
    }

    private function associateUserToCompany($user, $company_id)
	{

		$existe = UsuarioEmpresa::where('id_usuario', $user->id)->where('id_empresa', $company_id)->first();
		if($existe){
			$usuarioEmpresa = $existe;
		} else {
			$usuarioEmpresa = UsuarioEmpresa::create([
				'id_usuario' => $user->id,
				'id_empresa' => $company_id,
				'id_rol' => 4, // default: 1
				'estado' => 1, // default: 1 activo
			]);
		}

		// $suscripciones = CliEmpresaSuscripciones::where('id_empresa', $company_id)->with('componentes.componente.hijos')->first();

		// foreach ($suscripciones->componentes as $suscripcion) {
		// 	foreach ($suscripcion->componente as $componente) {

		// 		$permiso = CliPermiso::where([
		// 			['id_componente_suscripcion', $componente->id]
		// 		])->first();

		// 		if($permiso){
		// 			CliUsuarioPermiso::create([
		// 				'id_usuario_empresa' => $usuarioEmpresa->id,
		// 				'id_permiso' => $permiso->id,
		// 			]);
		// 		}
		// 		//AGREGAR TODOS LOS PERMISOS PADRE
		// 		$permisos = CliPermiso::where([
		// 			['id_padre', $componente->id]
		// 		])->get();
				
		// 		if(count($permisos) > 0){
		// 			foreach ($permisos as $permiso) {
		// 				CliUsuarioPermiso::create([
		// 					'id_usuario_empresa' => $usuarioEmpresa->id,
		// 					'id_permiso' => $permiso->id,
		// 				]);
		// 			}
		// 		}
		// 	}
		// }

		return;
	}

}
