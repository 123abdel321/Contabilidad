<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacBodegas;
use App\Models\Sistema\FacFamilias;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosBodegas;
use App\Models\Sistema\FacVariantesOpciones;
use App\Models\Sistema\FacProductosVariantes;

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
        $data = [
            'familias' => FacFamilias::all(),
            'bodegas' => FacBodegas::first()
        ];
        return view('pages.tablas.productos.productos-view', $data);
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = 15; // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $productos = FacProductos::skip($start)
            ->with('variantes.variante', 'variantes.opcion', 'inventarios.bodega', 'familia', 'hijos.familia', 'hijos.variantes.variante', 'hijos.variantes.opcion', 'hijos.inventarios.bodega')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(fac_productos.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(fac_productos.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'fac_productos.created_by',
                'fac_productos.updated_by'
            )
            ->take($rowperpage);

        if($columnName){
            $productos->orderBy($columnName,$columnSortOrder);
        }

        if($searchValue) {
            $productos->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%');
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $productos->count(),
            'iTotalDisplayRecords' => $productos->count(),
            'data' => $productos->get(),
            'perPage' => $rowperpage,
            'message'=> 'Productos cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|min:1|max:200|string|unique:sam.fac_productos,codigo',
            'nombre' => 'required|min:1|max:200|string|unique:sam.fac_productos,nombre',
            'precio' => 'required|numeric',
            'id_familia' => 'required|exists:sam.fac_familias,id',
            'tipo_producto' => 'required|numeric',
            'precio_inicial' => 'required|numeric',
            'precio_maximo' => 'required|numeric',
            'variante' => 'required|boolean',
            'productos_variantes' => 'array|sometimes|required_if:variante,=,true',
            'productos_variantes.*.codigo' => 'sometimes|required_if:variante,=,true|min:1|max:200|string|unique:sam.fac_productos,codigo',
            'productos_variantes.*.precio' => 'sometimes|required_if:variante,=,true|numeric',
            'productos_variantes.*.precio_inicial' => 'sometimes|required_if:variante,=,true|numeric',
            'productos_variantes.*.precio_maximo' => 'sometimes|required_if:variante,=,true|numeric',
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
                "message"=>$validator->messages()
            ], 422);
        }

        try {

            DB::connection('sam')->beginTransaction();
            //CREAR PRODUCTO PRINCIPAL
            $productoPadre = FacProductos::create([
                'id_familia' => $request->get('id_familia'),
                'id_padre' => null,
                'tipo_producto' => $request->get('tipo_producto'),
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'precio' => $request->get('precio'),
                'precio_inicial' => $request->get('precio_inicial'),
                'precio_maximo' => $request->get('precio_maximo'),
                'variante' => $request->get('variante'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);
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
                        $productoVariante = FacProductos::create([
                            'id_familia' => $request->get('id_familia'),
                            'id_padre' => $productoPadre->id,
                            'tipo_producto' => $request->get('tipo_producto'),
                            'codigo' => $producto['codigo'],
                            'nombre' => $request->get('nombre') .' '. $this->nombreVariante($producto['variantes']),
                            'precio' => $request->get('precio'),
                            'precio_inicial' => $request->get('precio_inicial'),
                            'precio_maximo' => $request->get('precio_maximo'),
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
                        $productoCodeExist =  FacProductos::where()->count();
                        if ($productoCodeExist > 0) {
                            $fail("El codigo ".$producto->codigo." ya existe.");
                        }
                    }
                },
			],
            'nombre' => [
				'required','min:1','max:200','string',
				function ($attribute, $value, $fail) use ($request) {
                    $producto = FacProductos::find($request->get('id')); 
                    if ($producto->nombre != $request->get('nombre')) {
                        $productoCodeExist =  FacProductos::where()->count();
                        if ($productoCodeExist > 0) {
                            $fail("El nombre ".$producto->nombre." ya existe.");
                        }
                    }
                },
			],
            'precio' => 'required|numeric',
            'id_familia' => 'required|exists:sam.fac_familias,id',
            'tipo_producto' => 'required|numeric',
            'precio_inicial' => 'required|numeric',
            'precio_maximo' => 'required|numeric',
            'variante' => 'required|boolean',
            'productos_variantes' => 'array|sometimes|required_if:variante,=,true',
            'productos_variantes.*.codigo' => 'sometimes|required_if:variante,=,true|min:1|max:200|string|unique:sam.fac_productos,codigo',
            'productos_variantes.*.precio' => 'sometimes|required_if:variante,=,true|numeric',
            'productos_variantes.*.precio_inicial' => 'sometimes|required_if:variante,=,true|numeric',
            'productos_variantes.*.precio_maximo' => 'sometimes|required_if:variante,=,true|numeric',
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
                "message"=>$validator->messages()
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
            $producto->precio_maximo = $request->get('precio_maximo');
            $producto->updated_by = request()->user()->id;
            $producto->save();

            //ASOCIAR BODEGAS GENERALES AL PRODUCTO
            $bodegas = $request->get('inventarios');

            if (count($bodegas) > 0 ) {
                foreach ($bodegas as $bodega) {
                    if (array_key_exists('edit', $bodega) && $bodega['edit'] == true) {
                        $this->agregarBodega($producto, $bodega);
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
                        $productoVariante->id_familia = $request->get('id_familia');
                        $productoVariante->codigo = $productoVar['codigo'];
                        $productoVariante->precio = $productoVar['precio'];
                        $productoVariante->precio_inicial = $productoVar['precio_inicial'];
                        $productoVariante->precio_maximo = $productoVar['precio_maximo'];
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
                            'precio_maximo' => $productoVar['precio_maximo'],
                            'variante' => true,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                    }

                    //ASOCIAR BODEGAS NUEVAS AL PRODUCTO
                    foreach ($producto['inventarios'] as $bodega) {
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

    private function agregarVariantes ($producto, $idOpcion) {
        $opcion = FacVariantesOpciones::find($idOpcion);

        $facProductosVariantes = FacProductosVariantes::create([
            'id_producto' => $producto->id,
            'id_producto_padre' => $producto->id_padre,
            'id_variante' => $opcion->id_variante,
            'id_variante_opcion' => $opcion->id
        ]);

        return $facProductosVariantes;
    }

    private function agregarBodega ($producto, $bodega) {

        $facProductosBodegas = FacProductosBodegas::create([
            'id_producto' => $producto->id,
            'id_producto_padre' => $producto->id_padre,
            'id_bodega' => $bodega['id'],
            'cantidad' => $bodega['cantidad'],
            'tipo_tranferencia' => 0,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);

        return $facProductosBodegas;
    }

    private function nombreVariante ($variantes) {
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

}