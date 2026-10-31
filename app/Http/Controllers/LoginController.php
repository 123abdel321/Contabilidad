<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Traits\BegReCaptchaValidate2Trait;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;
use App\Models\Empresas\UsuarioPermisos;

class LoginController extends Controller
{
    use BegReCaptchaValidate2Trait;
    
    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        // 🔑 Si ya está logueado con empresa, mandarlo al home
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->has_empresa && $user->id_empresa) {
                return redirect('/home');
            }
        }

        return view('auth.login', [
            'mode' => 'login',
            'step' => 1,
        ]);
    }

    public function validateSession (Request $request)
    {
        return view('auth.login');
    }

    

    public function loginDirectoGet(Request $request)
    {
        return view('auth.login', [
            'mode'       => 'login-direct',
            'email'      => $request->get('email'),
            'code_login' => $request->get('code_login'),
        ]);
    }

    public function login(Request $request)
    {
        // ═══════════════════════════════════════════════════════
        // 🔑 VALIDAR reCAPTCHA (antes que nada)
        // ═══════════════════════════════════════════════════════
        $captcha_token = $request->get('g-recaptcha-response');

        if (!$captcha_token) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos',
            ], 422);
        }

        $captcha = $this->validateReCaptcha($captcha_token, 'login', 0.5);

        if (!$captcha['success']) {
            \Log::warning('Login reCAPTCHA inválido', [
                'ip'     => $request->ip(),
                'email'  => $request->email,
                'reason' => $captcha['error'],
                'score'  => $captcha['score'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos',
            ], 422);
        }

        // ═══════════════════════════════════════════════════════
        // 🔑 A partir de aquí, tu código actual SIN CAMBIOS
        // ═══════════════════════════════════════════════════════
        $credenciales1 = ['email' => $request->email, 'password' => $request->password];
        $credenciales2 = ['username' => $request->email, 'password' => $request->password];

        if (Auth::attempt($credenciales1) || Auth::attempt($credenciales2)) {
            $request->session()->regenerate();
            $user = User::find(Auth::user()->id);

            // Crear token
            $token = $user->createToken("web_token");
            $plainTextToken = $token->plainTextToken;
            $user->remember_token = $plainTextToken;
            $user->save();

            // ═══════════════════════════════════════════════════════
            // 🔑 CASO 1: Email no verificado → paso 2 del registro
            // ═══════════════════════════════════════════════════════
            if (empty($user->email_verified_at)) {
                return response()->json([
                    'success'      => true,
                    'needs'        => 'verify',
                    'access_token' => $plainTextToken,
                    'token_type'   => 'Bearer',
                    'user_id'      => $user->id,
                    'message'      => 'Debes verificar tu correo.',
                ], 200);
            }

            // ═══════════════════════════════════════════════════════
            // 🔑 CASO 2: Empresa en instalación (estado 5) → paso 4
            // ═══════════════════════════════════════════════════════
            $empresaEnInstalacion = Empresa::where('id_usuario_owner', $user->id)
                ->where('estado', 5)
                ->first();

            // ═══════════════════════════════════════════════════════
            // 🔑 CASO 3: Sin empresa asignada → paso 3
            // ═══════════════════════════════════════════════════════
            $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $user->id)->first();

            if (!$usuarioEmpresa && !$empresaEnInstalacion && !$user->id_empresa) {
                return response()->json([
                    'success'      => true,
                    'needs'        => 'empresa',
                    'access_token' => $plainTextToken,
                    'token_type'   => 'Bearer',
                    'user_id'      => $user->id,
                    'message'      => 'Completa los datos de tu empresa.',
                ], 200);
            }

            if ($empresaEnInstalacion && !$usuarioEmpresa) {
                return response()->json([
                    'success'      => true,
                    'needs'        => 'pago',
                    'access_token' => $plainTextToken,
                    'token_type'   => 'Bearer',
                    'user_id'      => $user->id,
                    'message'      => 'Completa tu pago.',
                ], 200);
            }

            // ═══════════════════════════════════════════════════════
            // CASO 4: Login normal
            // ═══════════════════════════════════════════════════════
            $idEmpresa = $user->id_empresa;

            if (!$idEmpresa) {
                $empresa = UsuarioEmpresa::where('id_usuario', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$empresa) {
                    return response()->json([
                        'success'           => true,
                        'access_token'      => $plainTextToken,
                        'token_type'        => 'Bearer',
                        'empresa'           => '',
                        'notificacion_code' => '',
                        'message'           => 'Usuario logeado con exito!'
                    ], 200);
                }

                $idEmpresa = $empresa->id_empresa;
            }

            $empresaSelect = Empresa::where('id', $idEmpresa)->first();

            if (!$empresaSelect) {
                return response()->json([
                    'success'           => true,
                    'access_token'      => $plainTextToken,
                    'token_type'        => 'Bearer',
                    'empresa'           => '',
                    'notificacion_code' => '',
                    'message'           => 'Usuario logeado con exito!'
                ], 200);
            }

            $notificacionCode = $empresaSelect->token_db . '_' . $user->id;
            $user->id_empresa = $empresaSelect->id;
            $user->has_empresa = $empresaSelect->token_db;
            $user->save();

            $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', $user->id)
                ->where('id_empresa', $empresaSelect->id)
                ->first();

            if ($usuarioPermisosEmpresa && !empty($usuarioPermisosEmpresa->ids_permission)) {
                $permisosIds = explode(',', $usuarioPermisosEmpresa->ids_permission);
                $permisosIds = array_unique(array_map('intval', $permisosIds));
                $user->syncPermissions($permisosIds);
            }

            return response()->json([
                'success'           => true,
                'access_token'      => $plainTextToken,
                'token_type'        => 'Bearer',
                'empresa'           => $empresaSelect,
                'notificacion_code' => $notificacionCode,
                'fondo_sistema'     => $user->fondo_sistema,
                'nombre_usuario'    => $this->nombreUsuario($user),
                'avatar_usuario'    => $user->avatar,
                'message'           => 'Usuario logeado con exito!'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'data'    => '',
            'message' => 'The provided credentials do not match our records.'
        ], 422);
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
