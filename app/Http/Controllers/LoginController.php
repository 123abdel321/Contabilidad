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

    public function login(Request $request)
    {

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            $user =  User::find(Auth::user()->id);

            if($user->tokens()->where('tokenable_id', $user->id)
                ->where('name', 'web_token')
                ->exists()) {
                // $user->tokens()->delete();
            }

            $token = $user->createToken("web_token");

            $empresa = UsuarioEmpresa::where('id_usuario', $user->id)->first();
            $empresaSelect = Empresa::where('id', $empresa->id_empresa)->first();
            
            $user->has_empresa = $empresaSelect->token_db;
            $user->save();

            return response()->json([
                'success'=>	true,
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'empresa' => $empresaSelect->razon_social,
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

        return redirect('/login');
    }
}
