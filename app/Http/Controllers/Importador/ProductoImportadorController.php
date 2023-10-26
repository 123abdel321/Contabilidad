<?php

namespace App\Http\Controllers\Importador;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Imports\ProductosPreciosImport;
use Illuminate\Support\Facades\Validator;
//MODELS

class ProductoImportadorController extends Controller
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
        return view('pages.importador.producto_precios.producto_precios-view');
    }

    public function importar (Request $request)
    {
        $rules = [
            'file' => 'required|mimes:xlsx'
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
            $file = $request->file('file');

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
    
    public function exportar (Request $request)
    {
        return response()->json([
            'success'=>	true,
            'url' => 'https://bucketlistardatos.nyc3.digitaloceanspaces.com/import/importador_precio_productos.xlsx',
            'message'=> 'Url generada con exito'
        ]);
        
    }
}