<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacFamilias;
use App\Models\Sistema\FacProductos;

class FamiliasController extends Controller
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
        return view('pages.tablas.familias.familias-view');
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

        $familias = FacFamilias::orderBy($columnName,$columnSortOrder)
            ->with(
                'cuenta_venta.impuesto',
                'cuenta_venta_retencion.impuesto',
                'cuenta_venta_devolucion.impuesto',
                'cuenta_venta_iva.impuesto',
                'cuenta_venta_descuento.impuesto',
                'cuenta_venta_devolucion_iva.impuesto',
                'cuenta_compra.impuesto',
                'cuenta_compra_retencion.impuesto',
                'cuenta_compra_devolucion.impuesto',
                'cuenta_compra_iva.impuesto',
                'cuenta_compra_descuento.impuesto',
                'cuenta_compra_devolucion_iva.impuesto',
                'cuenta_inventario.impuesto',
                'cuenta_costos.impuesto'
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
            
        $familiasTotals = $familias->get();

        $familiasPaginate = $familias->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $familiasTotals->count(),
            'iTotalDisplayRecords' => $familiasTotals->count(),
            'data' => $familiasPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Comprobante generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|min:1|max:60|unique:sam.fac_familias,codigo',
            'nombre' => 'required|min:1|max:200',
			'id_cuenta_venta' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_retencion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_devolucion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_descuento' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_devolucion_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_retencion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_devolucion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_descuento' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_devolucion_iva' => 'nullable|exists:sam.plan_cuentas,id',
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

            $familia = FacFamilias::create([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'inventario' => $request->get('inventario'),
                'id_cuenta_venta' => $request->get('id_cuenta_venta'),
                'id_cuenta_venta_retencion' => $request->get('id_cuenta_venta_retencion'),
                'id_cuenta_venta_devolucion' => $request->get('id_cuenta_venta_devolucion'),
                'id_cuenta_venta_iva' => $request->get('id_cuenta_venta_iva'),
                'id_cuenta_venta_descuento' => $request->get('id_cuenta_venta_descuento'),
                'id_cuenta_venta_devolucion_iva' => $request->get('id_cuenta_venta_devolucion_iva'),
                'id_cuenta_inventario' => $request->get('id_cuenta_inventario'),
                'id_cuenta_costos' => $request->get('id_cuenta_costos'),
                'id_cuenta_compra' => $request->get('id_cuenta_compra'),
                'id_cuenta_compra_retencion' => $request->get('id_cuenta_compra_retencion'),
                'id_cuenta_compra_devolucion' => $request->get('id_cuenta_compra_devolucion'),
                'id_cuenta_compra_iva' => $request->get('id_cuenta_compra_iva'),
                'id_cuenta_compra_descuento' => $request->get('id_cuenta_compra_descuento'),
                'id_cuenta_compra_devolucion_iva' => $request->get('id_cuenta_compra_devolucion_iva'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $familia->save();
            
            $familia->load([
                'cuenta_venta',
                'cuenta_venta_retencion',
                'cuenta_venta_devolucion',
                'cuenta_venta_iva',
                'cuenta_venta_descuento',
                'cuenta_venta_devolucion_iva',
                'cuenta_compra',
                'cuenta_compra_retencion',
                'cuenta_compra_devolucion',
                'cuenta_compra_iva',
                'cuenta_compra_descuento',
                'cuenta_compra_devolucion_iva',
                'cuenta_inventario',
                'cuenta_costos'
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $familia,
                'message'=> 'Familia creada con exito!'
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
            'id' => 'required|exists:sam.fac_familias,id',
            'codigo' => 'required|min:1|max:60',
            'nombre' => 'required|min:1|max:200',
			'id_cuenta_venta' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_retencion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_devolucion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_descuento' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta_devolucion_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_retencion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_devolucion' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_descuento' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_compra_devolucion_iva' => 'nullable|exists:sam.plan_cuentas,id',
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

            FacFamilias::where('id', $request->get('id'))
                ->update([
                    'codigo' => $request->get('codigo'),
                    'nombre' => $request->get('nombre'),
                    'inventario' => $request->get('inventario'),
                    'id_cuenta_venta' => $request->get('id_cuenta_venta'),
                    'id_cuenta_venta_retencion' => $request->get('id_cuenta_venta_retencion'),
                    'id_cuenta_venta_devolucion' => $request->get('id_cuenta_venta_devolucion'),
                    'id_cuenta_venta_iva' => $request->get('id_cuenta_venta_iva'),
                    'id_cuenta_venta_descuento' => $request->get('id_cuenta_venta_descuento'),
                    'id_cuenta_venta_devolucion_iva' => $request->get('id_cuenta_venta_devolucion_iva'),
                    'id_cuenta_inventario' => $request->get('id_cuenta_inventario'),
                    'id_cuenta_costos' => $request->get('id_cuenta_costos'),
                    'id_cuenta_compra' => $request->get('id_cuenta_compra'),
                    'id_cuenta_compra_retencion' => $request->get('id_cuenta_compra_retencion'),
                    'id_cuenta_compra_devolucion' => $request->get('id_cuenta_compra_devolucion'),
                    'id_cuenta_compra_iva' => $request->get('id_cuenta_compra_iva'),
                    'id_cuenta_compra_descuento' => $request->get('id_cuenta_compra_descuento'),
                    'id_cuenta_compra_devolucion_iva' => $request->get('id_cuenta_compra_devolucion_iva'),
                    'updated_by' => request()->user()->id,
                ]);

            DB::connection('sam')->commit();

            $familia = FacFamilias::where('id', $request->get('id'))
                ->with(
                    'cuenta_venta',
                    'cuenta_venta_retencion',
                    'cuenta_venta_devolucion',
                    'cuenta_venta_iva',
                    'cuenta_venta_descuento',
                    'cuenta_venta_devolucion_iva',
                    'cuenta_compra',
                    'cuenta_compra_retencion',
                    'cuenta_compra_devolucion',
                    'cuenta_compra_iva',
                    'cuenta_compra_descuento',
                    'cuenta_compra_devolucion_iva',
                    'cuenta_inventario',
                    'cuenta_costos',
                )->first();

            return response()->json([
                'success'=>	true,
                'data' => $familia,
                'message'=> 'Familia actualizada con exito!'
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

            $productos = FacProductos::where('id_familia', $request->get('id'));

            if($productos->count() > 0) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'No se puede eliminar una familia usada por los productos!'
                ]);
            }

            FacFamilias::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Familia eliminada con exito!'
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

    public function comboFamilia(Request $request)
    {
        $familia = FacFamilias::select(
                \DB::raw('*'),
                \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
            )
            ->with(
                'cuenta_venta',
                'cuenta_venta_retencion',
                'cuenta_venta_devolucion',
                'cuenta_venta_iva.impuesto',
                'cuenta_venta_descuento',
                'cuenta_venta_devolucion_iva',
                'cuenta_compra',
                'cuenta_compra_retencion',
                'cuenta_compra_devolucion',
                'cuenta_compra_iva',
                'cuenta_compra_descuento',
                'cuenta_compra_devolucion_iva',
                'cuenta_inventario',
                'cuenta_costos'
            );

        if ($request->get("q")) {
            $familia->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $familia->paginate(40);
    }


}
