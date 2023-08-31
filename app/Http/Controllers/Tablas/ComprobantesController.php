<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\DocumentosGeneral;

class ComprobantesController extends Controller
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
        return view('pages.tablas.comprobantes.comprobantes-view');
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

        $comprobante = Comprobantes::orderBy($columnName,$columnSortOrder)
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('codigo', 'like', '%' .$searchValue . '%')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );
            
        $comprobanteTotals = $comprobante->get();

        $comprobantePaginate = $comprobante->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $comprobanteTotals->count(),
            'iTotalDisplayRecords' => $comprobanteTotals->count(),
            'data' => $comprobantePaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Comprobante generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|unique:sam.comprobantes,codigo|max:10',
            'nombre' => 'required|min:3|max:200|string',
            'tipo_comprobante' => 'required',
            'tipo_consecutivo' => 'required',
            'consecutivo_siguiente' => 'required'
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

            $comprobante = Comprobantes::create([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'tipo_comprobante' => $request->get('tipo_comprobante'),
                'tipo_consecutivo' => $request->get('tipo_consecutivo'),
                'imprimir_en_capturas' => $request->get('imprimir_en_capturas'),
                'tipo_impresion' => $request->get('tipo_impresion'),
                'consecutivo_siguiente' => $request->get('consecutivo_siguiente'),
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $comprobante,
                'message'=> 'Comprobante creado con exito!'
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
            'id' => 'required|exists:sam.comprobantes,id',
            'tipo_comprobante' => 'required',
            'tipo_consecutivo' => 'required',
            'consecutivo_siguiente' => 'required'
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

            $comprobante =  Comprobantes::where('id', $request->get('id'))->update([
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'tipo_comprobante' => $request->get('tipo_comprobante'),
                'tipo_consecutivo' => $request->get('tipo_consecutivo'),
                'imprimir_en_capturas' => $request->get('imprimir_en_capturas'),
                'tipo_impresion' => $request->get('tipo_impresion'),
                'consecutivo_siguiente' => $request->get('consecutivo_siguiente'),
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $comprobante,
                'message'=> 'Comprobante actualizado con exito!'
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
        $documentos = DocumentosGeneral::where('id_comprobante', $request->get('id'));

        if($documentos->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'No se puede eliminar un comprobante usado por los documentos!'
            ]);
        }

        DB::connection('sam')->beginTransaction();

        Comprobantes::where('id', $request->get('id'))->delete();

        DB::connection('sam')->commit();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Comprobante eliminado con exito!'
        ]);
    }

    public function comboComprobante(Request $request)
    {
        $comprobantes = Comprobantes::select(
            'id',
            'codigo',
            'nombre',
            'consecutivo_siguiente',
            'tipo_comprobante',
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        )->orderBy('codigo');

        if ($request->get("q")) {
            $comprobantes->where('codigo', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $comprobantes->paginate(20);
    }
}
