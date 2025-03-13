<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Ubicacion;
use App\Models\Sistema\UbicacionTipo;

class UbicacionesController extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'id.exists' => 'El id debe existir en la tabla de centro de costos.',
			'required' => 'El campo :attribute es requerido.',
			'numeric' => 'El campo :attribute debe ser un numero',
			'string' => 'El campo :attribute debe ser texto',
			'unique' => 'El :attribute :input ya existe en la tabla de ubicaciones',
			'max' => 'El :attribute no debe tener más de :max caracteres'
        ];
	}

    public function index ()
    {
        

        return view('pages.tablas.ubicaciones.ubicaciones-view');
    }

    public function generate (Request $request) 
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $ubicaciones = Ubicacion::with('pedido')
            ->orderBy($columnName,$columnSortOrder)
            ->with('tipo')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        $ubicacionesTotals = $ubicaciones->get();

        $ubicacionesPaginate = $ubicaciones->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $ubicacionesTotals->count(),
            'iTotalDisplayRecords' => $ubicacionesTotals->count(),
            'data' => $ubicacionesPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Ubicaciones generadas con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|unique:sam.ubicacions,codigo|max:10',
            'nombre' => 'required|min:3|max:200|string',
            'id_ubicacion_tipos' => 'required|exists:sam.ubicacion_tipos,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        DB::connection('sam')->beginTransaction();

        try {

            $ubicacion = Ubicacion::create([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'id_ubicacion_tipos' => $request->get('id_ubicacion_tipos'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $ubicacion->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $ubicacion->load('tipo'),
                'message'=> 'Ubicación creada con exito!'
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

    public function update (Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.ubicacions,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $ubicacion =  Ubicacion::where('id', $request->get('id'))->update([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'id_ubicacion_tipos' => $request->get('id_ubicacion_tipos'),
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $ubicacion,
                'message'=> 'Ubicacion actualizada con exito!'
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

    public function delete (Request $request)
    {
        // $documentos = DocumentosGeneral::where('id_centro_costos', $request->get('id'));

        // if($documentos->count() > 0) {
        //     return response()->json([
        //         'success'=>	false,
        //         'data' => '',
        //         'message'=> 'Este centro de costos tiene transacciones contables, no puede ser eliminado!'
        //     ]);
        // }

        DB::connection('sam')->beginTransaction();

        Ubicacion::where('id', $request->get('id'))->delete();

        DB::connection('sam')->commit();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Ubicación eliminada con exito!'
        ]);
    }

    public function combo (Request $request)
    {
        $ubicacionesTipo = UbicacionTipo::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre) as text")
        );

        if ($request->get("search")) {
            $ubicacionesTipo->where('nombre', 'LIKE', '%' . $request->get("search") . '%');
        }

        return $ubicacionesTipo->paginate(40);
    }

    public function comboUbicacion (Request $request)
    {
        $ubicacionesTipo = Ubicacion::with('pedido')
        ->select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre) as text")
        );

        if ($request->get("search")) {
            $ubicacionesTipo->where('nombre', 'LIKE', '%' . $request->get("search") . '%');
        }

        return $ubicacionesTipo->paginate(40);
    }
}
