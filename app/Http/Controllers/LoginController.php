<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;

class LoginController extends Controller
{
    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('auth.login');
    }

    public function validateSession (Request $request)
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales1 = ['email' => $request->email, 'password' => $request->password];
        $credenciales2 = ['username' => $request->email, 'password' => $request->password];

        if (Auth::attempt($credenciales1) || Auth::attempt($credenciales2)) {
            $request->session()->regenerate();
            $user =  User::find(Auth::user()->id);

            if($user->tokens()->where('tokenable_id', $user->id)
                ->where('name', 'web_token')
                ->exists()) {
                // $user->tokens()->delete();
            }

            $plainTextToken = '';
            if ($user->remember_token) {
                $token = $user->createToken("web_token");
                $plainTextToken = $token->plainTextToken;
                $user->remember_token = $plainTextToken;
            } else {
                $token = $user->createToken("web_token");
                $plainTextToken = $token->plainTextToken;
                $user->remember_token = $plainTextToken;
            }

            $empresa = UsuarioEmpresa::where('id_usuario', $user->id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            $empresaSelect = Empresa::where('id', $empresa->id_empresa)->first();
            $notificacionCode =  null;

            if ($empresaSelect) {
                $notificacionCode = $empresaSelect->token_db.'_'.$user->id;
                $user->id_empresa = $empresa->id_empresa;
                $user->has_empresa = $empresaSelect->token_db;
                $user->save();
            }

            return response()->json([
                'success'=>	true,
                'access_token' => $plainTextToken,
                'token_type' => 'Bearer',
                'empresa' => $empresaSelect,
                'notificacion_code' => $notificacionCode,
                'message'=> 'Usuario logeado con exito!'
            ], 200);
        }

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'The provided credentials do not match our records.'
    	], 422);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'logout true'
    	], 200);
    }
}
