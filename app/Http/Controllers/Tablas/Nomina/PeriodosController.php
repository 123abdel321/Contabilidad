<?php

namespace App\Http\Controllers\Tablas\Nomina;

use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\Nomina\NomPeriodos;

class PeriodosController extends Controller
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
        return view('pages.tablas.periodos.periodos-view');
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 20;

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $searchValue = $request->get('search');
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        $nomPeriodos = NomPeriodos::select(
                '*',
                DB::raw("DATE_FORMAT(nom_periodos.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nom_periodos.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nom_periodos.created_by',
                'nom_periodos.updated_by'
            )
            ->orderBy('id', 'desc');

        if($searchValue) {
            $nomPeriodos->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%');
        }

        $totalNomPeriodos = $nomPeriodos->count();
        $nomPeriodos = $nomPeriodos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalNomPeriodos,
            'iTotalDisplayRecords' => $totalNomPeriodos,
            'data' => $nomPeriodos->get(),
            'perPage' => $rowperpage,
            'message'=> 'Administradores cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'nombre' => 'required|min:2',
            'dias_salario' => 'required',
            'horas_dia' => 'numeric|required|min:0|max:24',
            'tipo_dia_pago' => 'numeric|required|in:0,1',
            'periodo_dias_ordinales' => [
				'string',
				'nullable',
				'required_if:tipo_dia_pago,0',
				function ($attribute, $value, $fail) {
					$values = explode(',', $value);
					$invalidValues = array_filter($values, function ($v) {
						return $v > 31 || $v < 0 || !is_numeric($v);
					});

					if (count($invalidValues)) {
						$fail("El campo $attribute debe tener valores numéricos entre 1 y 31 separados por coma (,).");
					}
				}
			],
            'periodo_dias_calendario' => 'numeric|nullable|min:0|max:9999|required_if:tipo_dia_pago,1'
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
            
            $nomPeriodos = NomPeriodos::create([
                'nombre' => $request->get('nombre'),
                'dias_salario' => $request->get('dias_salario'),
                'horas_dia' => $request->get('horas_dia'),
                'tipo_dia_pago' => $request->get('tipo_dia_pago'),
                'periodo_dias_ordinales' => $request->get('periodo_dias_ordinales'),
                'periodo_dias_calendario' => $request->get('periodo_dias_calendario'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $diasOrdinalesUnique = array_unique(explode(',', $nomPeriodos->periodo_dias_ordinales));
            $nomPeriodos->periodo_dias_ordinales = $nomPeriodos->tipo_dia_pago == 0 ? implode(',', $diasOrdinalesUnique) : '';
		    $nomPeriodos->periodo_dias_calendario = $nomPeriodos->tipo_dia_pago ? $nomPeriodos->periodo_dias_calendario : null;
            $nomPeriodos->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomPeriodos,
                'message'=> 'Periodo creado con exito!'
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
            'id' => 'required|exists:sam.nom_periodos,id',
            'nombre' => 'required|min:2',
            'dias_salario' => 'required',
            'horas_dia' => 'numeric|required|min:0|max:24',
            'tipo_dia_pago' => 'numeric|required|in:0,1',
            'periodo_dias_ordinales' => [
				'string',
				'nullable',
				'required_if:tipo_dia_pago,0',
				function ($attribute, $value, $fail) {
					$values = explode(',', $value);
					$invalidValues = array_filter($values, function ($v) {
						return $v > 31 || $v < 0 || !is_numeric($v);
					});

					if (count($invalidValues)) {
						$fail("El campo $attribute debe tener valores numéricos entre 1 y 31 separados por coma (,).");
					}
				}
			],
            'periodo_dias_calendario' => 'numeric|nullable|min:0|max:9999|required_if:tipo_dia_pago,1'
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

            $nomPeriodos = NomPeriodos::where('id', $request->get('id'))->first();


            $nomPeriodos->nombre = $request->get('nombre');
            $nomPeriodos->dias_salario = $request->get('dias_salario');
            $nomPeriodos->horas_dia = $request->get('horas_dia');
            $nomPeriodos->tipo_dia_pago = $request->get('tipo_dia_pago');
            $nomPeriodos->periodo_dias_ordinales = $request->get('periodo_dias_ordinales');
            $nomPeriodos->periodo_dias_calendario = $request->get('periodo_dias_calendario');
            $nomPeriodos->updated_by = request()->user()->id;
            $diasOrdinalesUnique = array_unique(explode(',', $nomPeriodos->periodo_dias_ordinales));
            $nomPeriodos->periodo_dias_ordinales = $nomPeriodos->tipo_dia_pago == 0 ? implode(',', $diasOrdinalesUnique) : '';
		    $nomPeriodos->periodo_dias_calendario = $nomPeriodos->tipo_dia_pago ? $nomPeriodos->periodo_dias_calendario : null;
            $nomPeriodos->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomPeriodos,
                'message'=> 'Periodo actualizado con exito!'
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

            // $bodegaConProductos = FacProductosBodegas::where('id_bodega', $request->get('id'));

            // if($bodegaConProductos->count() > 0) {
            //     return response()->json([
            //         'success'=>	false,
            //         'data' => '',
            //         'message'=> 'Esta bodega contiene productos, no puede ser eliminada!'
            //     ]);
            // }

            NomPeriodos::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Periodo eliminada con exito!'
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

    public function combo(Request $request)
    {
        $nomPeriodos = NomPeriodos::select(
            \DB::raw('*'),
            \DB::raw("nombre as text")
        );

        if ($request->get("q")) {
            $nomPeriodos->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $nomPeriodos->paginate(40);
    }

    private function dataPeriodos($data, $id_nit)
	{
        $nomPeriodosExist = NomPeriodos::where('codigo', $data[1])->first();
        if (!$nomPeriodosExist) {
            return [
                'tipo' => $this->tipoPeriodo[$data[0]],
                'codigo' => $data[1],
                'id_nit' => $id_nit,
                'descripcion' => $data[4]
            ];
        }
        return null;
	}

}