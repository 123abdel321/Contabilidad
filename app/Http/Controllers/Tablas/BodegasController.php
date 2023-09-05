<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacBodegas;

class BodegasController extends Controller
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

    public function index ()
    {
        return view('pages.tablas.bodegas.bodegas-view');
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

        $bodegas = FacBodegas::orderBy($columnName,$columnSortOrder)
            ->with('cecos')
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('codigo', 'like', '%' .$searchValue . '%')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        $bodegasTotals = $bodegas->get();

        $bodegasPaginate = $bodegas->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $bodegasTotals->count(),
            'iTotalDisplayRecords' => $bodegasTotals->count(),
            'data' => $bodegasPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Bodegas generadas con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|min:1|max:60',
            'nombre' => 'required|min:1|max:200',
            'ubicacion' => 'required|min:1|max:200',
            'id_centro_costos' => 'nullable|exists:sam.centro_costos,id',
            // 'id_responsable' => 'nullable|exists:sam.centro_costos,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }

        DB::connection('sam')->beginTransaction();

        try {

            $bodega = FacBodegas::create([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'ubicacion' => $request->get('ubicacion'),
                'id_centro_costos' => $request->get('id_centro_costos'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $bodega->save();

            $bodega->load([
                'cecos'
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $bodega,
                'message'=> 'Bodega creada con exito!'
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
            'id' => 'required|exists:sam.fac_bodegas,id',
            'codigo' => 'required|min:1|max:60',
            'nombre' => 'required|min:1|max:200',
            'ubicacion' => 'required|min:1|max:200',
            'id_centro_costos' => 'nullable|exists:sam.centro_costos,id',
            // 'id_responsable' => 'nullable|exists:sam.centro_costos,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);
    
        if ($validator->fails()){
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->messages()
            ], 422);
        }
    
        DB::connection('sam')->beginTransaction();

        try {

            FacBodegas::where('id', $request->get('id'))
                ->update([
                    'codigo' => $request->get('codigo'),
                    'nombre' => $request->get('nombre'),
                    'ubicacion' => $request->get('ubicacion'),
                    'id_centro_costos' => $request->get('id_centro_costos'),
                    'updated_by' => request()->user()->id,
                ]);
            
            $bodegas = FacBodegas::where('id', $request->get('id'))
                ->with('cecos')
                ->first();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $bodegas,
                'message'=> 'Bodegas actualizada con exito!'
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
        try {

            FacBodegas::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Bodega eliminada con exito!'
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