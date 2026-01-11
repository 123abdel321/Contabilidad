<?php

namespace App\Http\Controllers\Importador;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//IMPORTS
use App\Imports\ProductosImport;
use App\Imports\ProductosPreciosImport;
//JOBS
use App\Jobs\ProcessImportProductos;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\FacProductos;
use App\Models\Sistema\FacProductosImport;
use App\Models\Sistema\FacProductosPreciosImport;

class ProductoImportadorController extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
        ];
	}

    public function index ()
    {
        return view('pages.importador.productos.productos-view');
    }

	public function indexPrecios ()
    {
        return view('pages.importador.producto_precios.producto_precios-view');
    }

    public function importar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            
            FacProductosImport::query()->truncate();

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();
            $urlNotificacion = $empresa->token_db.'_'.$request->user()['id'];

            $file = $request->file('file');
            $import = new ProductosImport($urlNotificacion);
            $import->import($file);
            
            return response()->json([
                'success' => true,
                'data' => [],
                'message' => 'Importación iniciada correctamente'
            ], 200);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'Error al iniciar la importación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importarPrecios (Request $request)
    {
        $rules = [
            'file' => 'required|mimes:xlsx'
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
            $file = $request->file('file');

            FacProductosPreciosImport::truncate();

            $import = new ProductosPreciosImport();
            $import->import($file);

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Productos actualizados con exito!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al actualizar precio de productos'
            ]);
        }
    }

    public function generateTotales (Request $request)
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

        $productoPrecios = FacProductosPreciosImport::orderBy($columnName,$columnSortOrder)
            ->with('producto')
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('codigo', 'like', '%' .$searchValue . '%')
            ->orWhere('observacion', 'like', '%' .$searchValue . '%');

        $productoPreciosTotals = $productoPrecios->get();

        $productosPaginate = $productoPrecios->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $productoPreciosTotals->count(),
            'iTotalDisplayRecords' => $productoPreciosTotals->count(),
            'data' => $productosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Productos precios generado con exito!'
        ]);
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

        $productoPrecios = FacProductosImport::orderBy('estado', 'DESC')
            ->with('familia', 'bodega');

        $productoPreciosTotals = $productoPrecios->get();

        $productosPaginate = $productoPrecios->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $productoPreciosTotals->count(),
            'iTotalDisplayRecords' => $productoPreciosTotals->count(),
            'data' => $productosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Productos precios generado con exito!'
        ]);
    }
    
    public function exportar (Request $request)
    {
        return response()->json([
            'success'=>	true,
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/importador_precio_productos.xlsx',
            'message'=> 'Url generada con exito'
        ]);
        
    }

    public function actualizarTotales (Request $request)
    {
        $facProductosPreciosImport = FacProductosPreciosImport::get();

        try {
            DB::connection('sam')->beginTransaction();

            if ($facProductosPreciosImport->count()) {
                foreach ($facProductosPreciosImport as $productoImport) {
                    
                    $producto = FacProductos::where('id', $productoImport->id_producto)
                        ->first();

                    if ($productoImport->estado == 2 && $producto) {
                        $producto->precio_inicial = $productoImport->precio_inicial;
                        $producto->precio = $productoImport->precio;
                        $producto->valor_utilidad = $productoImport->precio - $productoImport->precio_inicial;
                        $producto->save();
                    }
                }
            }

            FacProductosPreciosImport::whereIn('estado', [0,1,2])->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Productos precios actualizados con exito!'
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

    public function actualizar (Request $request)
    {
        try {

            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            ProcessImportProductos::dispatch(
                $empresa->id,
                $request->user()->id,
            );

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Importando productos!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al actualizar precio de productos'
            ]);
        }
    }

}