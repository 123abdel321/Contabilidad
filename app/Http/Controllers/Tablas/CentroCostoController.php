<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\CentroCostos;
use App\Models\Sistema\DocumentosGeneral;

class CentroCostoController extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index ()
    {
        return view('pages.tablas.cecos.cecos-view');
    }

    public function generate (Request $request)
    {
        try {
            if($request->get("length")) {
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

                $cecos = CentroCostos::orderBy($columnName,$columnSortOrder)
                    ->where('nombre', 'like', '%' .$searchValue . '%')
                    ->orWhere('codigo', 'like', '%' .$searchValue . '%')
                    ->select(
                        '*',
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                        DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                        'created_by',
                        'updated_by'
                    );

                $cecosTotals = $cecos->get();

                $cuentasPaginate = $cecos->skip($start)
                    ->take($rowperpage);

                return response()->json([
                    'success'=>	true,
                    'draw' => $draw,
                    'iTotalRecords' => $cecosTotals->count(),
                    'iTotalDisplayRecords' => $cecosTotals->count(),
                    'data' => $cuentasPaginate->get(),
                    'perPage' => $rowperpage,
                    'message'=> 'Comprobante generado con exito!'
                ]);
            } else {
                $cecos = CentroCostos::whereNotNull('id');

                return response()->json([
                    'success'=>	true,
                    'data' => $cecos->get(),
                    'message'=> 'Centro de costos generados con exito!'
                ]);
            }
        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
        
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|unique:sam.centro_costos,codigo|max:10',
            'nombre' => 'required|min:3|max:200|string'
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
            
            $cecos = CentroCostos::create([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $cecos,
                'message'=> 'Centro de costos creado con exito!'
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
            'id' => 'required|exists:sam.centro_costos,id',
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

            $cecos =  CentroCostos::where('id', $request->get('id'))->update([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $cecos,
                'message'=> 'Centro de costos creado con exito!'
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
        $documentos = DocumentosGeneral::where('id_centro_costos', $request->get('id'));

        if($documentos->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'Este centro de costos tiene transacciones contables, no puede ser eliminado!'
            ]);
        }

        DB::connection('sam')->beginTransaction();

        CentroCostos::where('id', $request->get('id'))->delete();

        DB::connection('sam')->commit();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Centro de costos eliminado con exito!'
        ]);
    }

    public function comboCentroCostos(Request $request)
    {
        $centroCostos = CentroCostos::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $centroCostos->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $centroCostos->paginate(40);
    }
}
