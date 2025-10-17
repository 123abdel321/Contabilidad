<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Impuestos;
use App\Models\Sistema\TipoImpuestos;
use App\Models\Sistema\VariablesEntorno;

class ImpuestoController extends Controller
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
        $valor_uvt = VariablesEntorno::where('nombre', 'valor_uvt')->first();
        $valor_uvt = $valor_uvt ? (float)$valor_uvt->valor : 0;

        return view('pages.tablas.impuesto.impuesto-view', [
            'valor_uvt' => $valor_uvt
        ]);
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

                $impuestos = Impuestos::with('tipo_impuesto')
                    ->orderBy($columnName,$columnSortOrder)
                    ->where('nombre', 'like', '%' .$searchValue . '%')
                    ->orWhere('base', 'like', '%' .$searchValue . '%')
                    ->orWhere('porcentaje', 'like', '%' .$searchValue . '%')
                    ->select(
                        '*',
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                        DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion")
                    );

                $impuestosTotals = $impuestos->get();

                $impuestosPaginate = $impuestos->skip($start)
                    ->take($rowperpage);

                return response()->json([
                    'success'=>	true,
                    'draw' => $draw,
                    'iTotalRecords' => $impuestosTotals->count(),
                    'iTotalDisplayRecords' => $impuestosTotals->count(),
                    'data' => $impuestosPaginate->get(),
                    'perPage' => $rowperpage,
                    'message'=> 'Impuestos generado con exito!'
                ]);
            } else {
                $impuestos = CentroCostos::whereNotNull('id');

                return response()->json([
                    'success'=>	true,
                    'data' => $impuestos->get(),
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
            'nombre' => 'required|unique:sam.impuestos,nombre',
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
            
            $impuesto = Impuestos::create([
                'id_tipo_impuesto' => $request->get('id_tipo_impuesto'),
                'nombre' => $request->get('nombre'),
                'base' => $request->get('base'),
                'porcentaje' => $request->get('porcentaje'),
                'total_uvt' => $request->get('total_uvt'),
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $impuesto,
                'message'=> 'Impuesto creado con exito!'
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
            'id' => 'required|exists:sam.impuestos,id',
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

            $impuesto =  Impuestos::where('id', $request->get('id'))->update([
                'id_tipo_impuesto' => $request->get('id_tipo_impuesto'),
                'nombre' => $request->get('nombre'),
                'base' => $request->get('base'),
                'porcentaje' => $request->get('porcentaje'),
                'total_uvt' => $request->get('total_uvt'),
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $impuesto,
                'message'=> 'Impuesto creado con exito!'
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
    
    public function comboImpuesto (Request $request)
    {
        $variantes = Impuestos::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre, ' %', porcentaje) as text")
        );

        if ($request->get("q")) {
            $variantes->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
            $variantes->Orwhere('porcentaje', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $variantes->paginate(40);
    }

    public function comboTipoImpuesto (Request $request)
    {
        $variantes = TipoImpuestos::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $variantes->where('codigo', 'LIKE', '%' . $request->get("q") . '%');
            $variantes->Orwhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $variantes->paginate(40);
    }
}
