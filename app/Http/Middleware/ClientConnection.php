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
        if(!$user->has_empresa){
            return response()->json([
                "success" => false,
				"message" => "Para acceder a esta opción debes seleccionar una empresa",
            ], 401);
        }

        // Cerrar la conexión actual si existe
        if (DB::connection('sam')->getDatabaseName() !== $user->has_empresa) {
            DB::purge('sam'); // Cierra la conexión actual
        }

		Config::set('database.connections.sam.database', $user->has_empresa);
		
        return $next($request);
    }
}
