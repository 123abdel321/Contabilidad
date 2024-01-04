<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\FacVendedores;

class VendedoresController extends Controller
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
        return view('pages.tablas.vendedores.vendedores-view');
    }

    public function generate (Request $request)
    {
        // dd($request->all());
        try {
            if($request->get("length")) {
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

                $vendedores = FacVendedores::orderBy($columnName,$columnSortOrder)
                    ->with('nit')
                    ->select(
                        '*',
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                        DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                        'created_by',
                        'updated_by'
                    );

                $vendedoresTotals = $vendedores->get();

                $cuentasPaginate = $vendedores->skip($start)
                    ->take($rowperpage);

                return response()->json([
                    'success'=>	true,
                    'draw' => $draw,
                    'iTotalRecords' => $vendedoresTotals->count(),
                    'iTotalDisplayRecords' => $vendedoresTotals->count(),
                    'data' => $cuentasPaginate->get(),
                    'perPage' => $rowperpage,
                    'message'=> 'Vendedores generados con exito!'
                ]);
            } else {
                $vendedores = Vendedores::whereNotNull('id');

                return response()->json([
                    'success'=>	true,
                    'data' => $vendedores->get(),
                    'message'=> 'Vendedores generados con exito!'
                ]);
            }
        } catch (Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
        
    }

    public function create (Request $request)
    {
        $rules = [
            'id_nit' => 'required|exists:sam.nits,id',
            'plazo_dias' => 'required|min:0|max:365',
            'porcentaje_comision' => 'required|min:0|max:100'
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
            
            $FacVendedores = FacVendedores::create([
                'id_nit' => $request->get('id_nit'),
                'plazo_dias' => $request->get('plazo_dias'),
                'porcentaje_comision' => $request->get('porcentaje_comision'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $FacVendedores,
                'message'=> 'Vendedores creado con exito!'
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
            'id' => 'required|exists:sam.fac_vendedores,id',
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

            $vendedores =  FacVendedores::where('id', $request->get('id'))->update([
                'id_nit' => $request->get('id_nit'),
                'plazo_dias' => $request->get('plazo_dias'),
                'porcentaje_comision' => $request->get('porcentaje_comision'),
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $vendedores,
                'message'=> 'Vendedor actualizado con exito!'
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
            $documentos = Nits::where('id_vendedor', $request->get('id'));

            if($documentos->count() > 0) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'Este vendedor tiene nits relacionados contables, no puede ser eliminado!'
                ]);
            }

            DB::connection('sam')->beginTransaction();

            FacVendedores::where('id', $request->get('id'))->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Vendedor eliminado con exito!'
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

    public function comboVendedores(Request $request)
    {
        $vendedores = FacVendedores::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $vendedores->where('plazo_dias', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('porcentaje_comision', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhereHas('nit', function ($query) use ($request){
                    $query->orWhere('primer_apellido', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('segundo_apellido', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('primer_nombre', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('otros_nombres', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('razon_social', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('email', 'LIKE', '%' . $request->get("q") . '%')
                    ->orWhere('numero_documento', 'LIKE', '%' . $request->get("q") . '%');
                });
        }

        return $vendedores->paginate(40);
    }
}
