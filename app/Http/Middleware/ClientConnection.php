<?php

namespace App\Http\Middleware;

use Closure;
use Config;
use DB;

class ClientConnection
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

        if (!$user || !$user->has_empresa) {
            return response()->json([
                "success" => false,
                "message" => "Para acceder a esta opción debes seleccionar una empresa o iniciar sesión.",
            ], 401);
        }

        $desiredDatabase = $user->has_empresa;
        $currentConfigDatabase = Config::get('database.connections.sam.database');

        if ($currentConfigDatabase !== $desiredDatabase) {
            if (DB::getConnections() && array_key_exists('sam', DB::getConnections())) {
                DB::purge('sam');
            }

            Config::set('database.connections.sam.database', $desiredDatabase);
        }

        return $next($request);
    }

}
