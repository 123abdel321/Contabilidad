<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\FacCompras;
use App\Models\Sistema\FacResoluciones;

class ResolucionesController extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'id_comprobante.exists' => 'El comprobante seleccionado es incorrecto.',
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'unique' => 'El :attribute :input ya existe en la tabla de resoluciones.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
            'lt' => 'El campo :attribute debe ser menor a :input.',
			'lte' => 'El campo :attribute debe ser menor o igual a :input.',
			'gt' => 'El campo :attribute debe ser mayor a :input.',
			'gte' => 'El campo :attribute debe ser mayor o igual a :input.'
        ];
	}

    public function index ()
    {
        return view('pages.tablas.resoluciones.resoluciones-view');
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
        
        $resolucion = FacResoluciones::orderBy($columnName,$columnSortOrder)
            ->with('comprobante')
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('prefijo', 'like', '%' .$searchValue . '%')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );
             
        $resolucionTotals = $resolucion->get();
        $resolucionPaginate = $resolucion->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $resolucionTotals->count(),
            'iTotalDisplayRecords' => $resolucionTotals->count(),
            'data' => $resolucionPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Resoluciones cargadas con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'nombre'=>'required|string',
			'prefijo'=>'required|string',
			'numero_resolucion'=>'sometimes|required|unique:sam.fac_resoluciones,numero_resolucion',
			'tipo_impresion'=>'sometimes|required|in:0,1,2,3,4,5',
			'tipo_resolucion'=>'sometimes|required|in:0,1,2,3,4,5',
			'fecha'=>'sometimes|required|date',
			'vigencia'=>'sometimes|required|numeric',
			'consecutivo'=>'sometimes|required|numeric|gte:consecutivo_desde|lte:consecutivo_hasta',
			'consecutivo_desde'=>'sometimes|required|numeric|lt:consecutivo_hasta',
			'consecutivo_hasta'=>'sometimes|required|numeric|gt:consecutivo_desde',
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

            $resolucion = FacResoluciones::create([
                'id_comprobante' => $request->get('id_comprobante'),
                'nombre' => $request->get('nombre'),
                'prefijo' => $request->get('prefijo'),
                'consecutivo' => $request->get('consecutivo'),
                'numero_resolucion' => $request->get('numero_resolucion'),
                'tipo_impresion' => $request->get('tipo_impresion'),
                'tipo_resolucion' => $request->get('tipo_resolucion'),
                'fecha' => $request->get('fecha'),
                'vigencia' => $request->get('vigencia'),
                'consecutivo_desde' => $request->get('consecutivo_desde'),
                'consecutivo_hasta' => $request->get('consecutivo_hasta'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            $resolucion->save();

            $resolucion->load('comprobante');

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $resolucion,
                'message'=> 'Resolución creada con exito!'
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
            'id' => 'required|exists:sam.fac_resoluciones,id',
            'id_comprobante' => 'required|exists:sam.comprobantes,id',
            'nombre'=>'required|string',
			'prefijo'=>'required|string',
			'tipo_impresion'=>'sometimes|required|in:0,1,2,3,4,5',
			'tipo_resolucion'=>'sometimes|required|in:0,1,2,3,4,5',
			'fecha'=>'sometimes|required|date',
			'vigencia'=>'sometimes|required|numeric',
			'consecutivo'=>'sometimes|required|numeric|gte:consecutivo_desde|lte:consecutivo_hasta',
			'consecutivo_desde'=>'sometimes|required|numeric|lt:consecutivo_hasta',
			'consecutivo_hasta'=>'sometimes|required|numeric|gt:consecutivo_desde',
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

            FacResoluciones::where('id', $request->get('id'))
                ->update([
                    'id_comprobante' => $request->get('id_comprobante'),
                    'nombre' => $request->get('nombre'),
                    'prefijo' => $request->get('prefijo'),
                    'consecutivo' => $request->get('consecutivo'),
                    'numero_resolucion' => $request->get('numero_resolucion'),
                    'tipo_impresion' => $request->get('tipo_impresion'),
                    'tipo_resolucion' => $request->get('tipo_resolucion'),
                    'fecha' => $request->get('fecha'),
                    'vigencia' => $request->get('vigencia'),
                    'consecutivo_desde' => $request->get('consecutivo_desde'),
                    'consecutivo_hasta' => $request->get('consecutivo_hasta'),
                    'updated_by' => request()->user()->id,
                ]);

            DB::connection('sam')->commit();

            $resolucion = FacResoluciones::where('id', $request->get('id'))
                ->with('comprobante')
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $resolucion,
                'message'=> 'Resolución actualizada con exito!'
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
			'id' => [
				"required",
				"exists:sam.fac_resoluciones,id",
				// function ($attribute, $value, $fail) {
				// 	// $facFactura = FacFactura::whereIdResolucion($value)->first();
				// 	$facCompras = FacCompras::whereIdResolucion($value)->first();
				// 	if ($facCompras) {
				// 		$fail("La Resolución ".$value." no puede ser eliminada porque esta asociado a una factura.");
				// 	}
                // },
			],
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

            FacResoluciones::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Resolución eliminada con exito!'
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

}
