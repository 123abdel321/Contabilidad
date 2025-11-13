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
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

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

        $menus = [];
        if (in_array($empresa->estado, [Empresa::ESTADO_INACTIVO, Empresa::ESTADO_RETIRADO]) && $request->user()->rol_portafolio == 0) {
            $menus = ComponentesMenu::where('nombre', 'Empresa')
                ->with('padre')
                ->get();

            foreach ($menus as $key => $menu) {
                if ($menu->code_name && !$request->user()->hasPermissionTo($menu->code_name.' read')) {
                    unset($menus[$key]);
                }
            }
        } else {
            $menus = ComponentesMenu::whereIn('id_componente', $suscripciones)
                ->where('estado', 1)
                ->with('padre')
                ->get();

            foreach ($menus as $key => $menu) {
                if ($menu->code_name && !$request->user()->hasPermissionTo($menu->code_name.' read')) {
                    unset($menus[$key]);
                }
            }
        }


        
        
        $data = [
            'menus' => $menus->groupBy('id_padre'),
            'empresa' => $empresa,
            'usuario_empresa' => UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first()
        ];

        return view('layouts.app', $data);
    }

    public function dashboard()
    {
        return view('pages.dashboard');
    }
    
}
