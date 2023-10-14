<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//MODELS
use App\Models\Empresas\Empresa;
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

        $menus = ComponentesMenu::whereIn('id_componente', $suscripciones)
            ->where('estado', 1)
            ->with('padre')
            ->get();
        
        $data = [
            'menus' => $menus->groupBy('id_padre')
        ];

        return view('layouts.app', $data);
    }

    public function dashboard()
    {
        return view('pages.dashboard');
    }
    
}
