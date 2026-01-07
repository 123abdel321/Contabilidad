<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//HELPERS
use App\Helpers\Eco\RegisterEco;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\VariablesEntorno;

class EcoController extends Controller
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
        $ecoToken = VariablesEntorno::where('nombre', 'eco_login')->first();
        $ecoToken = $ecoToken->valor ?? null;

        $data = [
            'tokenEco' => $ecoToken
        ];
        
        return view('pages.configuracion.notificaciones.notificaciones-view', $data);
    }

    public function register(Request $request)
    {
        try {
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            if (!$empresa) {
                return response()->json([
                    "success" => false,
                    "message" => "Empresa no encontrada para el usuario autenticado."
                ], 404);
            }

            if (!$empresa->email) {
                return response()->json([
                    "success" => false,
                    "message" => "La empresa no tiene un correo electrónico registrado."
                ], 404);
            }

            $securePassword = Str::random(16);

            $data = [
                "name" => $empresa->nombre,
                "email" => $empresa->email,
                "password" => $securePassword,
                "password_confirmation" => $securePassword
            ];

            $register = (new RegisterEco($data))->send();

            if ($register->status == 200) {

                $token = "{$register->response->token_type} {$register->response->access_token}";
                VariablesEntorno::updateOrCreate(
                    [ 'nombre' => 'eco_login' ],
                    [ 'valor' =>  $token ]
                );

                return response()->json([
                    "success" => true,
                    'data' => $register->response,
                    'token' => $token,
                    "message" => "Registro en servicio Eco exitoso."
                ], 200);

            } elseif ($register->status == 422) {
                $externalErrors = $register->response->errors ?? [];

                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $externalErrors,
                ], 422);

            } else {
                $errorMessage = $register->response->message ?? 'Error desconocido en el servicio Eco.';
                
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $errorMessage
                ], $register->status);
            }

        } catch (Exception $e) {
            return response()->json([
                "success"=> false,
                'data' => [],
                "message"=> $e->getMessage()
            ], 422);
        } 
    }


}