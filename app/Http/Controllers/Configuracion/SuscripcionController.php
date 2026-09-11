<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
// MODELS
use App\Models\User;
use App\Models\Empresas\EmpresaSuscripcion;
use App\Models\Empresas\ComponentesSuscripcion;
use App\Models\Empresas\EmpresaComponentesSuscripcion;

class SuscripcionController extends Controller
{
    protected $messages = [
        'required' => 'El campo :attribute es requerido.',
        'exists' => 'El :attribute es inválido.',
        'numeric' => 'El campo :attribute debe ser un valor numérico.',
        'string' => 'El campo :attribute debe ser texto',
        'array' => 'El campo :attribute debe ser un arreglo.',
        'date' => 'El campo :attribute debe ser una fecha válida.',
    ];

    public function __construct()
	{
	}

    public function index(Request $request)
    {
        $user = $request->user();
        $esDios = $user->rol_portafolio;

        $data = [
            'esDios' => $esDios
        ];

        return view('pages.configuracion.suscripcion.suscripcion-view', $data);
    }

    public function componentesGet(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 20;

        $order_arr = $request->get('order');
        $searchValue = $request->get('search');
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $user = $request->user();

        $empresaSuscripcion = EmpresaComponentesSuscripcion::with(
                'componente',
            )
        ->where('id_empresa', $user->id_empresa)
        ->has('componente')
        ->orderBy('id', 'desc');

        $totalEmpresaSuscripcion = $empresaSuscripcion->count();
        $empresaSuscripcion = $empresaSuscripcion->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalEmpresaSuscripcion,
            'iTotalDisplayRecords' => $totalEmpresaSuscripcion,
            'data' => $empresaSuscripcion->get(),
            'perPage' => $rowperpage,
            'message'=> 'Suscripciones de empresa cargados con exito!'
        ]);
    }

    public function combosGet(Request $request)
    {
        $componentesSuscripcion = ComponentesSuscripcion::select(
            DB::raw('*'),
            DB::raw("CONCAT(nombre, ' - ', FORMAT(precio, 2)) as text")
        );

        if ($request->get("q")) {
            $componentesSuscripcion->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $componentesSuscripcion->paginate(40);
    }

    public function componentesPost(Request $request) // Asegúrate de inyectar Request
    {
        $rules = [
            "id_componente" => "required|exists:clientes.componentes_suscripcions,id",
            "precio" => "required",
            "fecha_inicio" => "required",
            "descuento" => "required",
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('clientes')->beginTransaction();

            $user = $request->user();
            $esDios = $user->rol_portafolio;
            $idEmpresa = $user->id_empresa;

            // Obtener el componente base
            $componente = ComponentesSuscripcion::find($request->get('id_componente'));

            // Determinar la suscripción a la que se agregará (activa por defecto)
            $suscripcion = EmpresaSuscripcion::where('id_empresa', $idEmpresa)
                ->where('estado', '1')
                ->first();

            if (!$suscripcion) {
                throw new \Exception('No se encontró una suscripción activa para esta empresa.');
            }

            // Calcular precio y descuento del nuevo componente
            $fechaInicioSuscripcion = $esDios ? $request->get('fecha_inicio') : Carbon::now()->format('Y-m-d');
            $descuentoComponente = $esDios ? $request->get('descuento') : 0;
            $precioComponente = $esDios ? $request->get('precio') : $componente->precio;
            $precioComponente -= $descuentoComponente;

            // Crear el componente de la suscripción
            $empresaComponentesSuscripcion = EmpresaComponentesSuscripcion::create([
                'id_empresa' => $idEmpresa,
                'id_empresa_suscripcion' => $idEmpresa,
                'id_componente' => $componente->id,
                'cantidad' => 1,
                'precio' => $precioComponente,
                'fecha_siguiente_cobro' => null,
            ]);

            $totalComponentes = $suscripcion->componentes()->sum('precio');
            $suscripcion->precio = $totalComponentes;
            $suscripcion->save();

            DB::connection('clientes')->commit();

            return response()->json([
                "success" => true,
                'data' => $empresaComponentesSuscripcion,
                "message" => "Componente agregado y precio actualizado."
            ]);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }
}
