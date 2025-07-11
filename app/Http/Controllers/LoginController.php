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
use App\Models\Empresas\UsuarioPermisos;

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

            $idEmpresa = $user->id_empresa;

            if (!$idEmpresa) {
                $empresa = UsuarioEmpresa::where('id_usuario', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$empresa){
                    return response()->json([
                        'success'=>	true,
                        'access_token' => $plainTextToken,
                        'token_type' => 'Bearer',
                        'empresa' => '',
                        'notificacion_code' => '',
                        'message'=> 'Usuario logeado con exito!'
                    ], 200);
                }

                $idEmpresa = $empresa->id;
            }
                
            $notificacionCode =  null;
            $empresaSelect = Empresa::where('id', $idEmpresa)->first();

            $notificacionCode = $empresaSelect->token_db.'_'.$user->id;
            $user->id_empresa = $empresaSelect->id;
            $user->has_empresa = $empresaSelect->token_db;
            $user->save();

            $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', $user->id)
                ->where('id_empresa', $empresaSelect->id)
                ->first();

            $user->syncPermissions(explode(',', $usuarioPermisosEmpresa->ids_permission));

            return response()->json([
                'success'=>	true,
                'access_token' => $plainTextToken,
                'token_type' => 'Bearer',
                'empresa' => $empresaSelect,
                'notificacion_code' => $notificacionCode,
                'fondo_sistema' => $user->fondo_sistema,
                'nombre_usuario' => $this->nombreUsuario($user),
                'avatar_usuario' => $user->avatar,
                'message'=> 'Usuario logeado con exito!'
            ], 200);
        }

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'The provided credentials do not match our records.'
    	], 422);
    }

    public function loginDirectoGet(Request $request)
    {
        return view('auth.login', [
            'email' => $request->get('email'),
            'about' => $request->get('code_login')
        ]);
    }

    public function loginDirectoPost(Request $request)
    {
        $user =  User::where('email', $request->get('email'))
            ->where('about', base64_decode($request->get('code_login')))
            ->first();
        
        if ($user) {

            $user->tokens()->delete();
            Auth::login($user);

            if (Auth::user()) {

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

                $idEmpresa = $user->id_empresa;

                if (!$idEmpresa) {
                    $empresa = UsuarioEmpresa::where('id_usuario', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (!$empresa){
                        return response()->json([
                            'success'=>	true,
                            'access_token' => $plainTextToken,
                            'token_type' => 'Bearer',
                            'empresa' => '',
                            'notificacion_code' => '',
                            'message'=> 'Usuario logeado con exito!'
                        ], 200);
                    }

                    $idEmpresa = $empresa->id;
                }

                $notificacionCode =  null;
                $empresaSelect = Empresa::where('id', $idEmpresa)->first();

                $notificacionCode = $empresaSelect->token_db.'_'.$user->id;
                $user->id_empresa = $empresaSelect->id;
                $user->has_empresa = $empresaSelect->token_db;
                $user->about = '';
                $user->save();

                $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', $user->id)
                    ->where('id_empresa', $empresaSelect->id)
                    ->first();

                if (!$usuarioPermisosEmpresa) {
                    $usuarioPermisosEmpresa = UsuarioPermisos::updateOrCreate([
                        'id_user' => $user->id,
                        'id_empresa' => $empresaSelect->id
                    ],[
                        'ids_permission' => UsuarioPermisos::where('id_empresa', $empresaSelect->id)->first()->ids_permission,
                        'ids_bodegas_responsable' => '1',
                        'ids_resolucion_responsable' => '1'
                    ]);
                }

                $user->syncPermissions(explode(',', $usuarioPermisosEmpresa->ids_permission));

                return response()->json([
                    'success'=>	true,
                    'access_token' => $plainTextToken,
                    'token_type' => 'Bearer',
                    'empresa' => $empresaSelect,
                    'notificacion_code' => $notificacionCode,
                    'fondo_sistema' => $user->fondo_sistema,
                    'nombre_usuario' => $this->nombreUsuario($user),
                    'avatar_usuario' => $user->avatar,
                    'message'=> 'Usuario logeado con exito!'
                ], 200);
            }
        }

        return response()->json([
    		'success'=>	false,
    		'data' => $request->all(),
    		'message'=> 'Datos incorrectos'
    	], 200);
    }

    public function logout(Request $request)
    {
        $user =  User::find(Auth::user()->id);
        $user->tokens()->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return view('auth.login');
    }

    public function logoutApi(Request $request)
    {
        $user =  User::find(Auth::user()->id);
        $user->tokens()->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'logout true'
    	], 200);
    }

    private function nombreUsuario ($user)
    {
        if ($user->firstname && $user->lastname) {
            return $user->firstname.' '.$user->lastname;
        }
        return $user->firstname;
    }


}
