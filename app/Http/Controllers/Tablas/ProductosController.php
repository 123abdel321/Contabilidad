<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
//MODELS
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacFamilias;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacVariantesOpciones;
use App\Models\Sistema\FacProductosVariantes;
use App\Models\Sistema\FacProductosBodegasMovimiento;

class ProductosController extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'unique' => 'El :attribute ya existe.',
            'string' => 'El campo :attribute debe ser texto',
            'boolean' => 'El campo :attribute debe ser un booleano.',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'max' => 'El campo :attribute no debe tener más de :max caracteres',
        ];
	}

    public function index ()
    {
        $productoTotales = FacBodegas::count();
        $data = [
            'familias' => FacFamilias::all(),
            'bodegas' => FacBodegas::get()
        ];
        return view('pages.tablas.productos.productos-view', $data);
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

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        $productos = FacProductos::with(
                'variantes.variante',
                'variantes.opcion',
                'inventarios.bodega',
                'familia.cuenta_venta_iva.impuesto',
                'hijos.familia',
                'hijos.variantes.variante',
                'hijos.variantes.opcion',
                'hijos.inventarios.bodega'
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(fac_productos.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(fac_productos.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'fac_productos.created_by',
                'fac_productos.updated_by'
            )
            ->orderBy('id', 'desc');

        if($searchValue) {
            $productos->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%')
                ->orWhereHas('inventarios', function ($query) use($searchValue) {
                    $query->whereHas('bodega', function ($q) use($searchValue) {
                        $q->where('nombre', 'like', '%' .$searchValue . '%')
                            ->orWhere('codigo', 'like', '%' .$searchValue . '%');
                    });
                });
        }

        $totalesProductos = [
            'cantidad_productos' => count($this->queryTotalesProducto($searchValue)->groupBy('FP.id')->get()),
            'total_costo' => $this->queryTotalesProducto($searchValue)->select(DB::raw("SUM(FP.precio_inicial * FPB.cantidad) AS precio_inicial"))->first()->precio_inicial,
            'total_precio' => $this->queryTotalesProducto($searchValue)->select(DB::raw("SUM(FP.precio * FPB.cantidad) AS precio"))->first()->precio,
            'total_productos' => $this->queryTotalesProducto($searchValue)->select(DB::raw("SUM(FPB.cantidad) AS total_productos"))->first()->total_productos,
        ];

        $totalProductos = $productos->count();
        $productosPaginate = $productos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'totalesProductos' => $totalesProductos,
            'iTotalRecords' => $totalProductos,
            'iTotalDisplayRecords' => $totalProductos,
            'data' => $productosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Productos cargados con exito!'
        ]);
    }

    private function queryTotalesProducto($searchValue)
    {
        return DB::connection('sam')->table('fac_productos AS FP')
            ->leftJoin('fac_productos_bodegas AS FPB', 'FP.id', 'FPB.id_producto')
            ->leftJoin('fac_bodegas AS FB', 'FPB.id_bodega', 'FB.id')
            ->when($searchValue ? true : false, function ($query) use ($searchValue){
				$query->where('FB.nombre', 'like', '%' .$searchValue. '%')
				    ->orWhere('FB.codigo', 'like', '%' .$searchValue. '%')
                    ->orWhere('FP.nombre', 'like', '%' .$searchValue. '%')
				    ->orWhere('FP.codigo', 'like', '%' .$searchValue. '%');
			});
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|min:1|max:200|string|unique:sam.fac_productos,codigo',
            'nombre' => 'required|min:1|max:200|string|unique:sam.fac_productos,nombre',
            'precio' => 'required|numeric',
            'id_familia' => 'required|exists:sam.fac_familias,id',
            'tipo_producto' => 'required|numeric|min:0',
            'precio_inicial' => 'required|numeric|min:0',
            'precio_minimo' => 'required|numeric|min:0',
            'variante' => 'required|boolean',
            'productos_variantes' => 'array|sometimes|required_if:variante,=,true',
            'productos_variantes.*.codigo' => 'sometimes|required_if:variante,=,true|min:1|max:200|string|unique:sam.fac_productos,codigo',
            'productos_variantes.*.precio' => 'sometimes|required_if:variante,=,true|numeric|min:0',
            'productos_variantes.*.precio_inicial' => 'sometimes|required_if:variante,=,true|numeric|min:0',
            'productos_variantes.*.precio_minimo' => 'sometimes|required_if:variante,=,true|numeric|min:0',
            'productos_variantes.*.variantes' => 'nullable|nullable',
            'productos_variantes.*.variantes.*.id' => 'nullable|exists:sam.fac_variantes_opciones,id',
            'productos_variantes.*.inventarios' => 'array|nullable',
            'productos_variantes.*.inventarios.*.id' => 'nullable|exists:sam.fac_bodegas,id',
            'productos_variantes.*.inventarios.*.cantidad' => 'nullable|numeric',
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

            $porcentajeUtilidad = 100;
            $valorUtilidad = 0;
            
            if ($request->get('precio_inicial') == "0") {
                $valorUtilidad = $request->get('precio');
            } else {
                $porcentajeUtilidad = ((floatval($request->get('precio')) - floatval($request->get('precio_inicial'))) / floatval($request->get('precio_inicial'))) * 100;
                $valorUtilidad = floatval($request->get('precio_inicial')) * ($porcentajeUtilidad / 100);
            }
            //CREAR PRODUCTO PRINCIPAL
            $productoPadre = FacProductos::create([
                'id_familia' => $request->get('id_familia'),
                'id_padre' => null,
                'tipo_producto' => $request->get('tipo_producto'),
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'precio' => $request->get('precio'),
                'precio_minimo' => $request->get('precio_minimo'),
                'precio_inicial' => $request->get('precio_inicial'),
                'porcentaje_utilidad' => $porcentajeUtilidad,
                'valor_utilidad' => $valorUtilidad,
                'variante' => $request->get('variante'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if($request->imagen) {
                $image = $request->imagen;
                $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
                $image = str_replace('data:image/'.$ext.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'producto_'.$request->get('codigo').'_'.uniqid().'.'. $ext;
                
                Storage::disk('do_spaces')->put('imagen/productos/'.$imageName, base64_decode($image), 'public');
                $productoPadre->imagen = 'imagen/productos/'.$imageName;
                $productoPadre->save();
            }

            //ASOCIAR BODEGAS GENERALES AL PRODUCTO
            $bodegas = $request->get('inventarios');

            if (count($bodegas) > 0 ) {
                foreach ($bodegas as $bodega) {
                    $this->agregarBodega($productoPadre, $bodega);
                }
            }

            //ASOCIAR VARIANTES GENERALES AL PRODUCTO
            if ($request->get('variante')) {

                $variantes = $request->get('variantes');

                if (count($variantes) > 0 ) {
                    foreach ($variantes as $variante) {
                        foreach ($variante['opciones'] as $opcion) {
                            $this->agregarVariantes($productoPadre, $opcion['id']);
                        }
                    }
                }

                //CREAR PRODUCTOS VARIANTES
                $productosVariantes = $request->get('productos_variantes');
    
                if (count($productosVariantes) > 0) {
                    foreach ($productosVariantes as $producto) {

                        $porcentajeUtilidadVariante = 100;
                        $valorUtilidadVariable = 0;
                        
                        if ($request->get('precio_inicial') == "0") {
                            $valorUtilidadVariable = $producto['precio'];
                        } else {
                            $porcentajeUtilidadVariante = ((floatval($producto['precio']) - floatval($producto['precio_inicial'])) / floatval($producto['precio_inicial'])) * 100;
                            $valorUtilidadVariable = floatval($producto['precio_inicial']) * ($porcentajeUtilidadVariante / 100);
                        }

                        $productoVariante = FacProductos::create([
                            'id_familia' => $request->get('id_familia'),
                            'id_padre' => $productoPadre->id,
                            'tipo_producto' => $request->get('tipo_producto'),
                            'codigo' => $producto['codigo'],
                            'nombre' => $request->get('nombre') .' '. $this->nombreVariante($producto['variantes']),
                            'precio' => $producto['precio'],
                            'precio_inicial' => $producto['precio_inicial'],
                            'precio_minimo' => $producto['precio_minimo'],
                            'porcentaje_utilidad' => $porcentajeUtilidadVariante,
                            'valor_utilidad' => $valorUtilidadVariable,
                            'variante' => $request->get('variante'),
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);

                        //ASOCIAR VARIANTES AL PRODUCTO
                        foreach ($producto['variantes'] as $opcion) {
                            $this->agregarVariantes($productoVariante, $opcion['id']);
                        }

                        //ASOCIAR BODEGAS AL PRODUCTO
                        foreach ($producto['inventarios'] as $bodega) {
                            $this->agregarBodega($productoVariante, $bodega);
                        }
                    }
                }
            }

            DB::connection('sam')->commit();
    
            return response()->json([
                'success'=>	true,
                'data' => $productoPadre->load('variantes.variante', 'inventarios.bodega', 'familia', 'hijos'),
                'message'=> 'Producto creado con exito!'
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
            'id' => 'required|exists:sam.fac_productos,id',
            'codigo' => [
				'required','min:1','max:200','string',
				function ($attribute, $value, $fail) use ($request) {
                    $producto = FacProductos::find($request->get('id'));
                    if ($producto->codigo != $request->get('codigo')) {
                        $productoCodeExist =  FacProductos::where('codigo', $request->get('codigo'))->count();
                        if ($productoCodeExist > 0) {
                            $fail("El codigo ".$request->get('codigo')." ya existe.");
                        }
                    }
                },
			],
            'nombre' => [
				'required','min:1','max:200','string',
				function ($attribute, $value, $fail) use ($request) {
                    $producto = FacProductos::find($request->get('id')); 
                    if ($producto->nombre != $request->get('nombre')) {
                        $productoCodeExist =  FacProductos::where('nombre', $request->get('nombre'))->count();
                        if ($productoCodeExist > 0) {
                            $fail("El nombre ".$request->get('nombre')." ya existe.");
                        }
                    }
                },
			],
            'precio' => 'required|numeric',
            'id_familia' => 'required|exists:sam.fac_familias,id',
            'tipo_producto' => 'required|numeric',
            'precio_inicial' => 'required|numeric',
            'precio_minimo' => 'required|numeric',
            'variante' => 'required|boolean',
            'productos_variantes' => 'array|sometimes|required_if:variante,=,true',
            'productos_variantes.*.codigo' => 'sometimes|required_if:variante,=,true|min:1|max:200|string|unique:sam.fac_productos,codigo',
            'productos_variantes.*.precio' => 'sometimes|required_if:variante,=,true|numeric',
            'productos_variantes.*.precio_inicial' => 'sometimes|required_if:variante,=,true|numeric',
            'productos_variantes.*.precio_minimo' => 'sometimes|required_if:variante,=,true|numeric',
            'productos_variantes.*.variantes' => 'nullable|nullable',
            'productos_variantes.*.variantes.*.id' => 'nullable|exists:sam.fac_variantes_opciones,id',
            'productos_variantes.*.inventarios' => 'array|nullable',
            'productos_variantes.*.inventarios.*.id' => 'nullable|exists:sam.fac_bodegas,id',
            'productos_variantes.*.inventarios.*.cantidad' => 'nullable|numeric',
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

            //ACTUALIZAR INFORMACION DEL PRODUCTO
            $producto = FacProductos::where('id', $request->get('id'))->first();
            $producto->id_familia = $request->get('id_familia');
            $producto->tipo_producto = $request->get('tipo_producto');
            $producto->codigo = $request->get('codigo');
            $producto->nombre = $request->get('nombre');
            $producto->precio = $request->get('precio');
            $producto->precio_inicial = $request->get('precio_inicial');
            $producto->precio_minimo = $request->get('precio_minimo');
            $producto->porcentaje_utilidad = $request->get('porcentaje_utilidad');
            $producto->valor_utilidad = $request->get('valor_utilidad');
            $producto->updated_by = request()->user()->id;
            
            if($request->imagen) {
                $image = $request->imagen;
                $ext = explode(";", explode("/",explode(",", $image)[0])[1])[0];
                $image = str_replace('data:image/'.$ext.';base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = 'producto_'.$request->get('codigo').'_'.uniqid().'.'. $ext;
                
                Storage::disk('do_spaces')->put('imagen/productos/'.$imageName, base64_decode($image), 'public');
                $producto->imagen = 'imagen/productos/'.$imageName;
            }

            $producto->save();

            //ASOCIAR BODEGAS GENERALES AL PRODUCTO
            if ($producto->utilizado_captura == 0) {
                $bodegas = $request->get('inventarios');
                if (count($bodegas) > 0 ) {
                    foreach ($bodegas as $bodega) {
                        if (array_key_exists('edit', $bodega) && $bodega['edit'] == true) {

                            FacProductosBodegasMovimiento::where('id_producto', $producto->id)
                                ->where('id_bodega', $bodega['id'])
                                ->delete();

                            FacProductosBodegas::where('id_producto', $producto->id)
                                ->where('id_bodega', $bodega['id'])
                                ->update([
                                    'cantidad' => $bodega['cantidad']
                                ]);

                            $movimiento = new FacProductosBodegasMovimiento([
                                'id_producto' => $producto->id,
                                'id_bodega' => $bodega['id'],
                                'cantidad_anterior' => 0,
                                'cantidad' => $bodega['cantidad'],
                                'tipo_tranferencia' => 0,
                                'created_by' => request()->user()->id,
                                'updated_by' => request()->user()->id
                            ]);
                            
                            $movimiento->relation()->associate($producto);
                            $producto->bodegas()->save($movimiento);
                        }
                    }
                }
            }
            
            //ACTUALIZAR INFORMACIÓN DE VARIANTES
            $productosVariantes = $request->get('productos_variantes');

            if (count($productosVariantes) > 0) {
                foreach ($productosVariantes as $productoVar) {

                    $productoVariante = null;
                    if (array_key_exists('id', $productoVar)) {
                        $productoVariante = FacProductos::where('id', $productoVar['id'])->first();
                        $productoVariante->imagen = $producto->imagen;
                        $productoVariante->id_familia = $request->get('id_familia');
                        $productoVariante->codigo = $productoVar['codigo'];
                        $productoVariante->precio = $productoVar['precio'];
                        $productoVariante->precio_inicial = $productoVar['precio_inicial'];
                        $productoVariante->precio_minimo = $productoVar['precio_minimo'];
                        $productoVariante->updated_by = request()->user()->id;
                        $productoVariante->save();
                    } else {
                        $productoVariante = FacProductos::create([
                            'id_familia' => $request->get('id_familia'),
                            'id_padre' => $request->get('id'),
                            'tipo_producto' => 0,
                            'codigo' => $productoVar['codigo'],
                            'nombre' => $producto->nombre .' '. $this->nombreVariante($productoVar['variantes']),
                            'precio' => $productoVar['precio'],
                            'precio_inicial' => $productoVar['precio_inicial'],
                            'precio_minimo' => $productoVar['precio_minimo'],
                            'variante' => true,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                    }

                    //ASOCIAR BODEGAS NUEVAS AL PRODUCTO
                    foreach ($productoVar['inventarios'] as $bodega) {
                        if (array_key_exists('edit', $bodega) && $bodega['edit'] == true) {
                            $this->agregarBodega($productoVariante, $bodega);
                        }
                    }
                }
            }
            
            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $producto->load('variantes.variante', 'inventarios.bodega', 'familia', 'hijos'),
                'message'=> 'Producto creado con exito!'
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
            'id' => 'required|exists:sam.fac_productos,id',
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

            $productoVariante = FacProductos::where('id_padre', $request->get('id'));

            $productoConMovimientos = FacProductosBodegasMovimiento::where('id_producto', $request->get('id'));

            if ($productoVariante->count() > 0) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'No se puede eliminar producto que posee variantes, eliminar primero las variantes!'
                ]);
            }

            if ($productoConMovimientos->count() > 1) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'No se puede eliminar producto con movimientos en inventario'
                ]);
            }

            FacProductos::where('id', $request->get('id'))->delete();
            FacProductosBodegas::where('id_producto', $request->get('id'))->delete();
            FacProductosVariantes::where('id_producto', $request->get('id'))->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Producto eliminado con exito!'
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

    private function agregarVariantes ($producto, $idOpcion)
    {
        $opcion = FacVariantesOpciones::find($idOpcion);

        $facProductosVariantes = FacProductosVariantes::create([
            'id_producto' => $producto->id,
            'id_producto_padre' => $producto->id_padre,
            'id_variante' => $opcion->id_variante,
            'id_variante_opcion' => $opcion->id
        ]);

        return $facProductosVariantes;
    }

    private function agregarBodega ($producto, $bodega)
    {
        $facProductosBodegas = FacProductosBodegas::create([
            'id_producto' => $producto->id,
            'id_bodega' => $bodega['id'],
            'cantidad' => $bodega['cantidad'],
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);
        
        $movimiento = new FacProductosBodegasMovimiento([
            'id_producto' => $producto->id,
            'id_bodega' => $bodega['id'],
            'cantidad_anterior' => 0,
            'cantidad' => $bodega['cantidad'],
            'tipo_tranferencia' => 1,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);
        
        $movimiento->relation()->associate($producto);
        $producto->bodegas()->save($movimiento);
        
        return $facProductosBodegas;
    }

    private function nombreVariante ($variantes)
    {
        $nombreVariante = '';
        $totalVariantes = 0;
        if (count($variantes) > 0) {
            foreach ($variantes as $variante) {
                if ($totalVariantes == 0) $nombreVariante.= ' - ' .$variante['nombre'];
                if ($totalVariantes != 0) $nombreVariante.= ' / ' .$variante['nombre'];
                $totalVariantes++;
            }
        }
        return $nombreVariante;
    }

    public function comboProducto (Request $request)
    {
        $with = [
            'familia.cuenta_compra.impuesto',
            'familia.cuenta_compra_retencion.impuesto',
            'familia.cuenta_compra_devolucion.impuesto',
            'familia.cuenta_compra_iva.impuesto',
            'familia.cuenta_compra_descuento.impuesto',
            'familia.cuenta_compra_devolucion_iva.impuesto',
            'familia.cuenta_venta.impuesto',
            'familia.cuenta_venta_retencion.impuesto',
            'familia.cuenta_venta_devolucion.impuesto',
            'familia.cuenta_venta_iva.impuesto',
            'familia.cuenta_venta_descuento.impuesto',
            'familia.cuenta_venta_devolucion_iva.impuesto',
            'familia.cuenta_inventario.impuesto',
            'familia.cuenta_costos.impuesto'
        ];

        $producto = FacProductos::select(
                \DB::raw('*'),
                \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
            )->with([
                'familia.cuenta_compra.impuesto',
                'familia.cuenta_compra_retencion.impuesto',
                'familia.cuenta_compra_devolucion.impuesto',
                'familia.cuenta_compra_iva.impuesto',
                'familia.cuenta_compra_descuento.impuesto',
                'familia.cuenta_compra_devolucion_iva.impuesto',
                'familia.cuenta_venta.impuesto',
                'familia.cuenta_venta_retencion.impuesto',
                'familia.cuenta_venta_devolucion.impuesto',
                'familia.cuenta_venta_iva.impuesto',
                'familia.cuenta_venta_descuento.impuesto',
                'familia.cuenta_venta_devolucion_iva.impuesto',
                'familia.cuenta_inventario.impuesto',
                'familia.cuenta_costos.impuesto'
            ]);

        if ($request->get("q")) {
            $producto->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        if ($request->has("id_bodega")) {
            $producto->with(['inventarios' => function ($query) use ($request) {
                $query->where('id_bodega', $request->get("id_bodega"));
            }]);
        } else {
            $producto->with('inventarios');
        }

        return $producto->paginate(40);
    }

}