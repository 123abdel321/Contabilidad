<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//TRAITS
use App\Http\Controllers\Traits\BegConsecutiveTrait;
use App\Http\Controllers\Traits\BegDocumentHelpersTrait;
//MODELS
use App\Models\Sistema\FacBodegas;
use App\Models\Empresas\UsuarioPermisos;
use App\Models\Sistema\FacProductosBodegas;

class BodegasController extends Controller
{

    use BegConsecutiveTrait; 
    
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'id.exists' => 'El id debe existir en la tabla de centro de costos.',
			'required' => 'El campo :attribute es requerido.',
			'numeric' => 'El campo :attribute debe ser un numero',
			'string' => 'El campo :attribute debe ser texto',
			'unique' => 'El :attribute :input ya existe en la tabla de bodegas',
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
            ->with('cecos', 'cuenta_cartera')
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
            'ubicacion' => 'nullable|min:1|max:200',
            'id_centro_costos' => 'nullable|exists:sam.centro_costos,id',
            // 'id_responsable' => 'nullable|exists:sam.centro_costos,id',
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
            $bodega = FacBodegas::create([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'ubicacion' => $request->get('ubicacion'),
                'id_centro_costos' => $request->get('id_centro_costos'),
                'id_cuenta_cartera' => $request->get('id_cuenta_cartera'),
                'consecutivo' => $request->get('consecutivo'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $bodega->save();

            $bodega->load([
                'cecos',
                'cuenta_cartera'
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
            'ubicacion' => 'nullable|min:1|max:200',
            'id_centro_costos' => 'nullable|exists:sam.centro_costos,id',
            // 'id_responsable' => 'nullable|exists:sam.centro_costos,id',
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

            FacBodegas::where('id', $request->get('id'))
                ->update([
                    'codigo' => $request->get('codigo'),
                    'nombre' => $request->get('nombre'),
                    'ubicacion' => $request->get('ubicacion'),
                    'id_centro_costos' => $request->get('id_centro_costos'),
                    'id_cuenta_cartera' => $request->get('id_cuenta_cartera'),
                    'consecutivo' => $request->get('consecutivo'),
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

            $bodegaConProductos = FacProductosBodegas::where('id_bodega', $request->get('id'));

            if($bodegaConProductos->count() > 0) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'Esta bodega contiene productos, no puede ser eliminada!'
                ]);
            }

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

    public function comboBodega(Request $request)
    {
        $usuarioPermisos = UsuarioPermisos::where('id_user', request()->user()->id)
            ->where('id_empresa', request()->user()->id_empresa)
            ->first();

        $bodegasResponsable = explode(",", $usuarioPermisos->ids_bodegas_responsable);

        $bodega = FacBodegas::select(
            DB::raw('*'),
            DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $bodega->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        $bodega->whereIn('id', $bodegasResponsable);

        return $bodega->paginate(40);
    }

    public function existenciasProducto(Request $request)
    {
        $productoBodega = FacProductosBodegas::where('id_producto', $request->get('id_producto'))
            ->where('id_bodega', $request->get('id_bodega'))
            ->first();

        if ($productoBodega) {
            return response()->json([
                'success'=>	true,
                'data' => $productoBodega,
                'message'=> 'Producto consultado con exito!'
            ]);
        }
        
        return response()->json([
            'success'=>	true,
            'data' => null,
            'message'=> 'Producto consultado con exito!'
        ]);
    }

    public function consecutivo(Request $request)
    {
        $consecutivo = null;
        
		if ($request->get('id_bodega')) {
			$consecutivo = $this->getNextConsecutiveBodega($request->get('id_bodega'));
		}

        return response()->json([
    		'success'=>	true,
    		'data' => $consecutivo,
    		'message'=> 'Consecutivo siguiente generado con exito!'
    	]);
    }
}