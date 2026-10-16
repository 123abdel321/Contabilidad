<?php

namespace App\Http\Middleware;

use DB;
use Config;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class ClientConnectionWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        // dd( $user);
        // 🔑 Si no está autenticado, mandar al login
        if (!$user) {
            return redirect('/login');
        }

        // 🔑 Si no tiene empresa asignada → limpiar sesión y redirigir al login
        if (!$user->has_empresa) {
            // Cerrar todos los tokens del usuario
            try {
                $user->tokens()->delete();
            } catch (\Exception $e) {
                // Silencioso
            }

            // Limpiar sesión (sin usar Auth::logout porque el guard es sanctum)
            try {
                $request->session()->flush();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Exception $e) {
                // Silencioso
            }

            // Cerrar conexión de BD
            try {
                DB::purge('sam');
            } catch (\Exception $e) {
                // Silencioso
            }

            return redirect('/login');
        }

        // Cerrar la conexión actual si existe
        if (DB::connection('sam')->getDatabaseName() !== $user->has_empresa) {
            DB::purge('sam'); // Cierra la conexión actual
        }

        Config::set('database.connections.sam.database', $user->has_empresa);

        return $next($request);
    }
}