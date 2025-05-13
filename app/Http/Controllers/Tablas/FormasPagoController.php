<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacFormasPago;
use App\Models\Sistema\FacTipoFormasPago;

class FormasPagoController extends Controller
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
        return view('pages.tablas.formas_pago.formas_pago-view');
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

        $formaPago = FacFormasPago::orderBy($columnName,$columnSortOrder)
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->with('cuenta', 'tipoFormaPago')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        $formaPagoTotals = $formaPago->get();

        $formaPagoPaginate = $formaPago->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $formaPagoTotals->count(),
            'iTotalDisplayRecords' => $formaPagoTotals->count(),
            'data' => $formaPagoPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Formas de pago generadas con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_cuenta' => 'required|exists:sam.plan_cuentas,id',
            'id_tipo_formas_pago' => 'required|exists:sam.fac_tipo_formas_pagos,id',
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
            
            $formaPago = FacFormasPago::create([
                'id_cuenta' => $request->get('id_cuenta'),
                'id_tipo_formas_pago' => $request->get('id_tipo_formas_pago'),
                'nombre' => $request->get('nombre'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $formaPago,
                'message'=> 'Forma de pago creada con exito!'
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
            'id' => 'required|exists:sam.fac_formas_pagos,id',
            'id_cuenta' => 'required|exists:sam.plan_cuentas,id',
            'id_tipo_formas_pago' => 'required|exists:sam.fac_tipo_formas_pagos,id',
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
            
            FacFormasPago::where('id', $request->get('id'))
                ->update([
                    'id_cuenta' => $request->get('id_cuenta'),
                    'id_tipo_formas_pago' => $request->get('id_tipo_formas_pago'),
                    'nombre' => $request->get('nombre'),
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('sam')->commit();

            $formaPago = FacFormasPago::where('id', $request->get('id'))
                ->with('cuenta','tipoFormaPago');

            return response()->json([
                'success'=>	true,
                'data' => $formaPago,
                'message'=> 'Forma de pago actualizadas con exito!'
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
            'id' => 'required|exists:sam.fac_formas_pagos,id',
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

            FacFormasPago::where('id', $request->get('id'))->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Forma de pago eliminada con exito!'
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

    public function comboFormasPago (Request $request)
    {
        $formasPago = FacFormasPago::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre) as text")
        )->with('cuenta.tipos_cuenta');

        if ($request->get("q")) {
            $formasPago->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        if ($request->has("type")) {
            $tipoCuenta = $request->get("type");
            if ($request->get("type") == 'gastos') $tipoCuenta = 'compras';
            
            //QUITAR A FUTURO Y BUSCAR LAS CUENTAS TIPO 2: Caja - Bancos
            $formasPago->whereHas('cuenta', function ($query) use ($request) {
                $query->whereNotNull('naturaleza_'.$request->get("type"));
            });
            
            switch ($request->get("type")) {
                case 'gasto':
                    $this->filterTiposCuenta($formasPago, [2, 4, 7]);
                    break;
                case 'compras':
                    $this->filterTiposCuenta($formasPago, [4, 8]);
                    break;
                case 'egresos':
                    $this->filterTiposCuenta($formasPago, [4, 8]);
                    break;
                case 'ingresos':
                    $this->filterTiposCuenta($formasPago, [3, 7]);
                    break;
                case 'ventas':
                    $this->filterTiposCuenta($formasPago, [3, 7]);
                    break;
                    
                default:
                    # code...
                    break;
            }
        }

        return $formasPago->paginate(40);
    }

    public function comboTipoFormasPago (Request $request)
    {
        $tipoFormasPago = FacTipoFormasPago::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $tipoFormasPago->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->Orwhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $tipoFormasPago->paginate(40);
    }

    private function filterTiposCuenta($query, array $tiposCuentaExcluir)
    {
        $query->where(function($q) use ($tiposCuentaExcluir) {
            $q->whereDoesntHave('cuenta')
            ->orWhereHas('cuenta', function($q) use ($tiposCuentaExcluir) {
                $q->whereDoesntHave('tipos_cuenta')
                    ->orWhereHas('tipos_cuenta', function($q) use ($tiposCuentaExcluir) {
                        $q->whereNotIn('id_tipo_cuenta', $tiposCuentaExcluir);
                    });
            });
        });
    }

}
