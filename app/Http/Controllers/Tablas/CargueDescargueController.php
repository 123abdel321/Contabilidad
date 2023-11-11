<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacCargueDescargue;

class CargueDescargueController extends Controller
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
        return view('pages.tablas.cargue_descargue.cargue_descargue-view');
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

        $cargueDescargue = FacCargueDescargue::orderBy($columnName,$columnSortOrder)
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->with('nit', 'comprobante', 'cuenta_debito', 'cuenta_credito')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        $cargueDescargueTotals = $cargueDescargue->get();

        $cuentasPaginate = $cargueDescargue->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $cargueDescargueTotals->count(),
            'iTotalDisplayRecords' => $cargueDescargueTotals->count(),
            'data' => $cuentasPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Cargue / Descargue generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'nombre' => 'required|unique:sam.fac_cargue_descargues,nombre|max:200',
            'id_nit' => 'nullable|exists:sam.nits,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'id_cuenta_debito' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_credito' => 'nullable|exists:sam.plan_cuentas,id',
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

            $cargueDescargue = FacCargueDescargue::create([
                'id_comprobante' => $request->get('id_comprobante'), 
                'id_nit' => $request->get('id_nit'), 
                'id_cuenta_debito' => $request->get('id_cuenta_debito'), 
                'id_cuenta_credito' => $request->get('id_cuenta_credito'), 
                'nombre' => $request->get('nombre'), 
                'tipo' => $request->get('tipo'), 
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            DB::connection('sam')->commit();

            $cargueDescargue->load('nit', 'comprobante', 'cuenta_debito', 'cuenta_credito');

            return response()->json([
                'success'=>	true,
                'data' => $cargueDescargue,
                'message'=> 'Cargue / Descargue creado con exito!'
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
            'id' => 'required|exists:sam.fac_cargue_descargues,id',
            'nombre' => [
                'required','string','max:200',
                function ($attribute, $value, $fail) use ($request) {
                    $cargueDescargue = FacCargueDescargue::find($request->get('id'));
                    if ($cargueDescargue->nombre != $request->get('nombre')) {
                        $cargueDescargueExist =  FacCargueDescargue::where('nombre', $request->get('nombre'))->count();
                        if ($cargueDescargueExist > 0) {
                            $fail("El nombre ".$request->get('nombre')." ya existe.");
                        }
                    }
                },
            ],
            'id_nit' => 'nullable|exists:sam.nits,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'id_cuenta_debito' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_credito' => 'nullable|exists:sam.plan_cuentas,id',
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

            FacCargueDescargue::where('id', $request->get('id'))
                ->update([
                    'id_comprobante' => $request->get('id_comprobante'), 
                    'id_nit' => $request->get('id_nit'), 
                    'id_cuenta_debito' => $request->get('id_cuenta_debito'), 
                    'id_cuenta_credito' => $request->get('id_cuenta_credito'), 
                    'nombre' => $request->get('nombre'), 
                    'tipo' => $request->get('tipo'), 
                    'updated_by' => request()->user()->id,
                ]);

            DB::connection('sam')->commit();

            $cargueDescargue = FacCargueDescargue::where('id', $request->get('id'))
                ->with('nit', 'comprobante', 'cuenta_debito', 'cuenta_credito')
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $cargueDescargue,
                'message'=> 'Cargue / Descargue actualizado con exito!'
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
        $rules = [
            'id' => 'required|exists:sam.fac_cargue_descargues,id',
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

            FacCargueDescargue::where('id', $request->get('id'))->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Cargue / Descargue eliminado con exito!'
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

    public function comboCargueDescargue (Request $request)
    {
        $cargueDescargue = FacCargueDescargue::select(
                \DB::raw('*'),
                \DB::raw("CONCAT(nombre) as text")
            )
            ->with('nit', 'comprobante', 'cuenta_debito', 'cuenta_credito');

        if ($request->get("q")) {
            $cargueDescargue->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        if ($request->has("tipo")) {
            $cargueDescargue->where('tipo', $request->get("tipo"));
        }

        return $cargueDescargue->paginate(40);
    }
}
