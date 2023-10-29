<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\TipoCuenta;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\PlanCuentasTipo;
use App\Models\Sistema\DocumentosGeneral;

class PlanCuentaController extends Controller
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
        $tipoCuenta = TipoCuenta::get();

        $data = [
            'tipoCuenta' => $tipoCuenta,
        ];

        return view('pages.tablas.plan_cuentas.plan_cuentas-view', $data);
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

        $cuentas = PlanCuentas::orderBy($columnName,$columnSortOrder)
            ->with('tipos_cuenta', 'padre', 'impuesto')
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('cuenta', 'like', '%' .$searchValue . '%')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );
        
        $cuentasPaginate = $cuentas->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $cuentas->count(),
            'iTotalDisplayRecords' => $cuentas->count(),
            'data' => $cuentasPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Comprobante generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $padre = $request->get('id_padre');

        $rules = [
			'id_padre' => 'nullable|exists:sam.plan_cuentas,id',
            // 'id_tipo_cuenta' => 'nullable|exists:sam.tipo_cuentas,id',
            'id_impuesto' => 'nullable|exists:sam.impuestos,id',
            'cuenta' => [
				"required",
				function ($attribute, $value, $fail) use ($request) {
                    $cuentaPadre = PlanCuentas::find($request->get('id_padre')); 
                    $cuentaNueva = $cuentaPadre->cuenta.''.$value;
					$search = PlanCuentas::whereCuenta($cuentaNueva)->first();
					if ($search) {
						$fail("La cuenta ".$cuentaNueva." ya existe.");
					}
                },
			],
            'nombre' => 'required',
            'exige_nit'=>'required|boolean',
			'exige_documento_referencia'=>'required|boolean',
			'exige_concepto'=>'required|boolean',
			'exige_centro_costos'=>'required|boolean',
            'naturaleza_ingresos'=>'nullable|boolean',
            'naturaleza_egresos'=>'nullable|boolean',
            'naturaleza_compras'=>'nullable|boolean',
            'naturaleza_ventas'=>'nullable|boolean'
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

            $cuentaPadre = '';

            if($request->get('id_padre')){
                $padre = PlanCuentas::where('id', $request->get('id_padre'))->first();
                $padre->auxiliar = 0;
                $padre->save();
                $cuentaPadre = $padre->cuenta;
            }

            $cuenta = PlanCuentas::create([
                'id_padre' => $request->get('id_padre'),
                'cuenta' => $cuentaPadre.$request->get('cuenta'),
                'nombre' => $request->get('nombre'),
                'auxiliar' => 1,
                'id_impuesto' => $request->get('id_impuesto'),
                'exige_nit' => $request->get('exige_nit'),
                'exige_documento_referencia' => $request->get('exige_documento_referencia'),
                'exige_concepto' => $request->get('exige_concepto'),
                'exige_centro_costos' => $request->get('exige_centro_costos'),
                'naturaleza_cuenta' => $request->get('naturaleza_cuenta'),
                'naturaleza_ingresos' => $request->get('naturaleza_ingresos'),
                'naturaleza_egresos' => $request->get('naturaleza_egresos'),
                'naturaleza_compras' => $request->get('naturaleza_compras'),
                'naturaleza_ventas' => $request->get('naturaleza_ventas'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $tiposCuenta = $request->get('id_tipo_cuenta');

            if (count($tiposCuenta) > 0) {
                PlanCuentasTipo::where('id_cuenta', $request->get('id'))->delete();
                
                foreach ($tiposCuenta as $tipoCuenta) {
                    PlanCuentasTipo::create([
                        'id_cuenta' => $cuenta->id,
                        'id_tipo_cuenta' => $tipoCuenta
                    ]);
                }
            }

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $cuenta,
                'message'=> 'Cuenta creada con exito!'
            ]);

        }  catch (Exception $e) {
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
            'id' => 'required|exists:sam.plan_cuentas,id',
			'id_padre' => 'nullable|exists:sam.plan_cuentas,id',
            // 'id_tipo_cuenta' => 'nullable|exists:sam.tipo_cuentas,id',
            'id_impuesto' => 'nullable|exists:sam.impuestos,id',
            'cuenta' => [
				"nullable",
				function ($attribute, $value, $fail) use ($request) {
                    $cuenta = PlanCuentas::find($request->get('id'));
                    if ($cuenta->cuenta != $request->get('cuenta')) {
                        $cuentaExist = PlanCuentas::where('cuenta', $request->get('cuenta'))->count();
                        if ($cuentaExist > 0) {
                            $fail("La cuenta ".$value." ya existe.");
                        }
                    }
                },
			],
            'nombre' => 'required',
            'exige_nit'=>'required|boolean',
			'exige_documento_referencia'=>'required|boolean',
			'exige_concepto'=>'required|boolean',
			'exige_centro_costos'=>'required|boolean',
            'naturaleza_ingresos'=>'nullable|boolean',
            'naturaleza_egresos'=>'nullable|boolean',
            'naturaleza_compras'=>'nullable|boolean',
            'naturaleza_ventas'=>'nullable|boolean'
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
            
            $cuentaPadre = '';
        
            if($request->get('id_padre')){
                $padre = PlanCuentas::find($request->get('id_padre'));
                $cuentaPadre = $padre->cuenta;
            }

            $auxiliar = 0;
            $auxiliarPadre = PlanCuentas::where('id_padre', $request->get('id'))->first();
            if ($auxiliarPadre) $auxiliar = 1;

            PlanCuentas::where('id', $request->get('id'))
                ->update([
                    'id_padre' => $request->get('id_padre'),
                    'cuenta' => $cuentaPadre.$request->get('cuenta'),
                    'nombre' => $request->get('nombre'),
                    'auxiliar' => $auxiliar,
                    'id_impuesto' => $request->get('id_impuesto'),
                    'exige_nit' => $request->get('exige_nit'),
                    'exige_documento_referencia' => $request->get('exige_documento_referencia'),
                    'exige_concepto' => $request->get('exige_concepto'),
                    'exige_centro_costos' => $request->get('exige_centro_costos'),
                    'naturaleza_cuenta' => $request->get('naturaleza_cuenta'),
                    'naturaleza_ingresos' => $request->get('naturaleza_ingresos'),
                    'naturaleza_egresos' => $request->get('naturaleza_egresos'),
                    'naturaleza_compras' => $request->get('naturaleza_compras'),
                    'naturaleza_ventas' => $request->get('naturaleza_ventas'),
                    'updated_by' => request()->user()->id,
                ]);
            
            $tiposCuenta = $request->get('id_tipo_cuenta');

            if (count($tiposCuenta) > 0) {
                PlanCuentasTipo::where('id_cuenta', $request->get('id'))->delete();
                
                foreach ($tiposCuenta as $tipoCuenta) {
                    PlanCuentasTipo::create([
                        'id_cuenta' => $request->get('id'),
                        'id_tipo_cuenta' => $tipoCuenta
                    ]);
                }
            }

            DB::connection('sam')->commit();

            $planCuenta = PlanCuentas::where('id', $request->get('id'))
                ->with('tipos_cuenta', 'padre')
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $planCuenta,
                'message'=> 'Cuenta actualizada con exito!'
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

            $documentos = DocumentosGeneral::where('id_cuenta', $request->get('id'));

            if($documentos->count() > 0) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'No se puede eliminar una cuenta usado por los documentos!'
                ]);
            }

            $padre = PlanCuentas::where('id_padre', $request->get('id'));

            if($padre->count() > 0) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'No se puede eliminar una cuenta padre!'
                ]);
            }

            PlanCuentas::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Cuenta eliminada con exito!'
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

    public function comboCuenta(Request $request)
    {
        $totalRows = $request->has("totalRows") ? $request->get("totalRows") : 40;
        $comprobante = NULL;
        $naturaleza = "naturaleza_cuenta";
        $select = [
            'id',
            'cuenta',
            'exige_nit',
            'exige_documento_referencia',
            'exige_concepto',
            'exige_centro_costos',
            'nombre',
            'auxiliar',
            DB::raw($naturaleza. ' AS naturaleza_cuenta'),
            DB::raw("CONCAT(cuenta, ' - ', nombre) as text")
        ];

        if($request->has("id_comprobante")) {
            $comprobante = Comprobantes::where('id', $request->get("id_comprobante"))->first();
            if($comprobante){
                $tipoComprobante = $comprobante->tipo_comprobante;
                switch ($tipoComprobante) {
                    case 0:
                        $naturaleza = "naturaleza_ingresos";
                        break;
                    case 1:
                        $naturaleza = "naturaleza_egresos";
                        break;
                    case 2:
                        $naturaleza = "naturaleza_compras";
                        break;
                    case 3:
                        $naturaleza = "naturaleza_ventas";
                        break;
                };
                $select = [
                    'id',
                    'cuenta',
                    'exige_nit',
                    'exige_documento_referencia',
                    'exige_concepto',
                    'exige_centro_costos',
                    'nombre',
                    'auxiliar',
                    DB::raw($naturaleza. ' AS naturaleza_cuenta'),
                    DB::raw('naturaleza_cuenta AS naturaleza_origen'),
                    DB::raw("CONCAT(cuenta, ' - ', nombre) AS text")
                ];
            }
        }

        $planCuenta = PlanCuentas::select($select);

        if ($request->has("id_comprobante") && $comprobante) {
            $planCuenta->whereNotNull($naturaleza);
        }

        if ($request->has("cartera")) {
            $planCuenta->whereIn('id_tipo_cuenta', [3, 4])
                ->where('auxiliar', 1);
        }

        if ($request->has("id_tipo_cuenta")) {
            $planCuenta->where('auxiliar', 1);
            // $planCuenta->whereIn('id_tipo_cuenta',  $request->get('id_tipo_cuenta'));
        }

        if ($request->get("search")) {
            $planCuenta->where('cuenta', 'LIKE', '%' . $request->get("search") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("search") . '%');
        }

        if ($request->get("q")) {
            $planCuenta->where('cuenta', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $planCuenta->orderBy('cuenta')->paginate($totalRows);
    }
}
