<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Empresas\UsuarioEmpresa;
use App\Models\Empresas\ComponentesMenu;

class HomeController extends Controller
{
        /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $hasEmpresa = $request->user()->has_empresa;

        $empresa = Empresa::where('token_db', $hasEmpresa)
            ->with('componentes.componente')
            ->first();

        $suscripciones = [];
        foreach ($empresa->componentes as $componentes) {
            $suscripciones[] = $componentes->id_componente;
        }

        $estadoRestringido = in_array($empresa->estado, [
            Empresa::ESTADO_INACTIVO,
            Empresa::ESTADO_RETIRADO,
            Empresa::ESTADO_INSTALANDO
        ]);

        $menus = [];
        $usuarioEmpresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
            ->where('id_usuario', $request->user()['id'])
            ->first();

        if ($request->user()->rol_portafolio == 1) {
            $menus = ComponentesMenu::whereIn('id_componente', $suscripciones)
                ->where('estado', 1)
                ->with('padre')
                ->get();

        } else if (in_array($empresa->estado, [Empresa::ESTADO_INACTIVO, Empresa::ESTADO_RETIRADO])) {
            $menus = ComponentesMenu::whereIn('nombre', ['Empresa', 'Suscripciónes'])
                ->with('padre')
                ->get();
        } else {
            $menus = ComponentesMenu::whereIn('id_componente', $suscripciones)
                ->where('estado', 1)
                ->with('padre')
                ->get();
        }

        if (in_array($empresa->estado, [Empresa::ESTADO_INACTIVO, Empresa::ESTADO_RETIRADO]) && $request->user()->rol_portafolio == 0) {

            $menus = ComponentesMenu::whereIn('nombre', ['Empresa', 'Suscripciónes'])
                ->with('padre')
                ->get();

        } else {
            $menus = ComponentesMenu::whereIn('id_componente', $suscripciones)
                ->where('estado', 1)
                ->with('padre')
                ->get();

            foreach ($menus as $key => $menu) {
                if ($usuarioEmpresa->id_rol == 2 && ($menu->code_name == 'suscripcion' || $menu->code_name == 'empresa' || $menu->code_name == 'usuarios')) {
                    continue;
                }
                if ($usuarioEmpresa->id_rol != 2 && ($menu->code_name == 'empresa' || $menu->code_name == 'usuarios')) {
                    continue;
                }
                if ($menu->code_name && !$request->user()->hasPermissionTo($menu->code_name.' read')) {
                    unset($menus[$key]);
                }
            }
        }

        // Buscamos el menú de suscripción para pasarlo a la vista (id, nombre, icon)
        $menuSuscripcion = $menus->firstWhere('url', 'suscripcion');
        $menuEmpresa     = $menus->firstWhere('url', 'empresa');

        $user = $request->user();

        $data = [
            'menus'             => $menus->groupBy('id_padre'),
            'esDios'            => $user->rol_portafolio,
            'empresa'           => $empresa,
            'estadoRestringido' => $estadoRestringido,
            'menuSuscripcion'   => $menuSuscripcion,
            'menuEmpresa'       => $menuEmpresa,
            'usuario_empresa'   => $usuarioEmpresa
        ];

        return view('layouts.app', $data);
    }

    public function dashboard()
    {
        return view('pages.dashboard');
    }
    
}
