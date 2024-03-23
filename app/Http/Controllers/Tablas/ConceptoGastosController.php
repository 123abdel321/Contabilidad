<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\ConConceptoGastos;

class ConceptoGastosController extends Controller
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
        return view('pages.tablas.concepto_gastos.concepto_gastos-view');
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

        $conceptosGastos = ConConceptoGastos::orderBy($columnName,$columnSortOrder)
            ->with(
                'cuenta_gasto.impuesto',
                'cuenta_iva.impuesto',
                'cuenta_retencion.impuesto'
            )
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('codigo', 'like', '%' .$searchValue . '%')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );
            
        $conceptosGastosTotals = $conceptosGastos->get();

        $conceptosGastosPaginate = $conceptosGastos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $conceptosGastosTotals->count(),
            'iTotalDisplayRecords' => $conceptosGastosTotals->count(),
            'data' => $conceptosGastosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Conceptos de gasto generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|min:1|max:60|unique:sam.con_concepto_gastos,codigo',
            'nombre' => 'required|min:1|max:200',
			'id_cuenta_gasto' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_retencion' => 'nullable|exists:sam.plan_cuentas,id',
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

            $conceptoGasto = ConConceptoGastos::create([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'inventario' => $request->get('inventario'),
                'id_cuenta_gasto' => $request->get('id_cuenta_gasto'),
                'id_cuenta_iva' => $request->get('id_cuenta_iva'),
                'id_cuenta_retencion' => $request->get('id_cuenta_retencion'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $conceptoGasto->save();
            
            $conceptoGasto->load([
                'cuenta_gasto',
                'cuenta_iva',
                'cuenta_retencion',
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $conceptoGasto,
                'message'=> 'Concepto de gasto creado con exito!'
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
            'id' => 'required|exists:sam.con_concepto_gastos,id',
            'codigo' => 'required|min:1|max:100',
            'nombre' => 'required|min:1|max:100',
			'id_cuenta_gasto' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_retencion' => 'nullable|exists:sam.plan_cuentas,id',
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

            ConConceptoGastos::where('id', $request->get('id'))
                ->update([
                    'codigo' => $request->get('codigo'),
                    'nombre' => $request->get('nombre'),
                    'id_cuenta_gasto' => $request->get('id_cuenta_gasto'),
                    'id_cuenta_iva' => $request->get('id_cuenta_iva'),
                    'id_cuenta_retencion' => $request->get('id_cuenta_retencion'),
                    'updated_by' => request()->user()->id,
                ]);

            DB::connection('sam')->commit();

            $conceptoGasto = ConConceptoGastos::where('id', $request->get('id'))
                ->with(
                    'cuenta_gasto',
                    'cuenta_iva',
                    'cuenta_retencion',
                )->first();

            return response()->json([
                'success'=>	true,
                'data' => $conceptoGasto,
                'message'=> 'Concepto de gasto actualizado con exito!'
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

            // $documentos = DocumentosGeneral::where('id_cuenta', $request->get('id'));

            // if($documentos->count() > 0) {
            //     return response()->json([
            //         'success'=>	false,
            //         'data' => '',
            //         'message'=> 'No se puede eliminar una cuenta usado por los documentos!'
            //     ]);
            // }

            ConConceptoGastos::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Concepto de gastos eliminado con exito!'
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

    public function comboConceptoGasto (Request $request)
    {
        $conceptosGastos = FacFamilias::select(
                \DB::raw('*'),
                \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
            )
            ->with(
                'cuenta_gasto',
                'cuenta_iva',
                'cuenta_retencion',
            );

        if ($request->get("q")) {
            $conceptosGastos->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $conceptosGastos->paginate(40);
    }
}
