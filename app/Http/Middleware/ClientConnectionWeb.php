<?php

namespace App\Http\Middleware;

use DB;
use Config;
use Closure;
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
        if(!$user->has_empresa){
            return redirect(RouteServiceProvider::SELECT_EMPRESA);
        }

        // Cerrar la conexión actual si existe
        if (DB::connection('sam')->getDatabaseName() !== $user->has_empresa) {
            DB::purge('sam'); // Cierra la conexión actual
        }

		Config::set('database.connections.sam.database', $user->has_empresa);
		
        return $next($request);
    }
}
