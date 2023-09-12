<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacVariantes;
use App\Models\Sistema\FacVariantesOpciones;

class VariantesController extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'id.exists' => 'El id debe existir en la tabla de centro de costos.',
			'required' => 'El campo :attribute es requerido.',
			'numeric' => 'El campo :attribute debe ser un numero',
			'string' => 'El campo :attribute debe ser texto',
			'unique' => 'El :attribute :input ya existe en la tabla de familias',
			'max' => 'El :attribute no debe tener más de :max caracteres'
        ];
	}

    public function comboVariante (Request $request)
    {
        $variantes = FacVariantes::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre) as text")
        )
        ->with('opciones');

        if ($request->get("q")) {
            $variantes->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $variantes->paginate(40);
    }

    public function create (Request $request)
    {
        $rules = [
            'nombre' => 'required|min:3|max:200|string'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $variante = FacVariantes::where('nombre', $request->get('nombre'))
                ->with('opciones')
                ->first();

            if($variante) {
                return response()->json([
                    'success'=>	true,
                    'data' => $variante,
                    'message'=> 'Variante existente!'
                ]);
            }
            
            $variante = FacVariantes::create([
                'nombre' => $request->get('nombre')
            ]);

            $variante->load('opciones');

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $variante,
                'message'=> 'Variante creada con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function createOpcion (Request $request)
    {
        $rules = [
            'nombre' => 'required|min:1|max:200|string'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $opcion = FacVariantesOpciones::where('nombre', $request->get('nombre'))
                ->where('id_variante', $request->get('id_variante'))
                ->with('variante')
                ->first();

            if($opcion) {
                return response()->json([
                    'success'=>	true,
                    'data' => $opcion,
                    'message'=> 'Opción existente!'
                ]);
            }
            
            $opcion = FacVariantesOpciones::create([
                'id_variante' => $request->get('id_variante'),
                'nombre' => $request->get('nombre')
            ]);

            $opcion->load('variante');

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $opcion,
                'message'=> 'Opción creada con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}