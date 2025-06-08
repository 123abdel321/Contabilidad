<?php

namespace App\Http\Controllers\Tablas\Nomina;

use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nits;
use App\Models\Sistema\Nomina\NomAdministradoras;

class AdministradorasController extends Controller
{
    protected $messages = null;
    public $tipoAdministradora = [
        'EPS' => 0,
        'AFP' => 1,
        'ARL' => 2,
        'CCF' => 3
    ];

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
        return view('pages.tablas.administradoras.administradoras-view');
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
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        $nomAdministradoras = NomAdministradoras::with('nit')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(nom_administradoras.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nom_administradoras.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nom_administradoras.created_by',
                'nom_administradoras.updated_by'
            )
            ->orderBy('id', 'desc');

        if($searchValue) {
            $nomAdministradoras->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%');
        }

        $totalNomAdministradoras = $nomAdministradoras->count();
        $nomAdministradoras = $nomAdministradoras->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalNomAdministradoras,
            'iTotalDisplayRecords' => $totalNomAdministradoras,
            'data' => $nomAdministradoras->get(),
            'perPage' => $rowperpage,
            'message'=> 'Administradores cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
            'codigo' => 'required|unique:sam.nom_administradoras,codigo|max:10',
            'descripcion' => 'required|min:3|max:200|string',
            'id_nit' => 'required|exists:sam.nits,id'
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
            
            $nomAdministradoras = NomAdministradoras::create([
                'tipo' => $request->get('tipo'),
                'codigo' => $request->get('codigo'),
                'descripcion' => $request->get('descripcion'),
                'id_nit' => $request->get('id_nit'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomAdministradoras,
                'message'=> 'Administradoras creado con exito!'
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
            'codigo' => [
                'required',
                'max:10',
                Rule::unique('sam.nom_administradoras', 'codigo')->ignore($request->get('id'))
            ],
            'descripcion' => 'required|min:3|max:200|string',
            'id_nit' => 'required|exists:sam.nits,id'
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

            NomAdministradoras::where('id', $request->get('id'))
                ->update([
                    'tipo' => $request->get('tipo'),
                    'codigo' => $request->get('codigo'),
                    'id_nit' => $request->get('id_nit'),
                    'descripcion' => $request->get('descripcion'),
                    'liquidada' => 0,
                ]);
            
            $nomAdministradoras = NomAdministradoras::where('id', $request->get('id'))
                ->with('nit')
                ->first();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomAdministradoras,
                'message'=> 'Administradoras actualizada con exito!'
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

            // $bodegaConProductos = FacProductosBodegas::where('id_bodega', $request->get('id'));

            // if($bodegaConProductos->count() > 0) {
            //     return response()->json([
            //         'success'=>	false,
            //         'data' => '',
            //         'message'=> 'Esta bodega contiene productos, no puede ser eliminada!'
            //     ]);
            // }

            NomAdministradoras::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Administradora eliminada con exito!'
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

    public function sincronizar (Request $request)
    {
        try {
            DB::connection('sam')->beginTransaction();

            $urlFile = Storage::disk('do_spaces')->url('import/nom_administradoras.csv');
            $csvFile = fopen($urlFile, "r");

            $count = 0;
            $dataAdministradoras = [];

            while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
                $dataDocumento = explode("-", $data[2]);
                $numero_documento = count($dataDocumento) > 1 ? $dataDocumento[0] : $data[2];

                $nit = Nits::where('numero_documento', $numero_documento)->first();

                if (!$nit) {
                    $nit = Nits::create([
                        'numero_documento' => count($dataDocumento) > 1 ? $dataDocumento[0] : $data[2],
                        'digito_verificacion' => count($dataDocumento) > 1 ? $dataDocumento[1] : NULL,
                        'razon_social' => $data[3],
                        'id_tipo_documento' => 6,
                        'id_ciudad' => 1,
                        'id_departamento' => 1,
                        'id_pais' => 53,
                        'tipo_contribuyente' => 1,
                        'direccion' => 1,
                        'email_recepcion_factura_electronica' => 1,
                        'tipo_cuenta_banco' => 1,
                        'no_calcular_iva' => 1
                    ]);
                }
                $dataNew = $this->dataAdministradoras($data, $nit->id);

                if (isset($dataNew)) {
                    $count++;
                    NomAdministradoras::create($dataNew);
                }
            }
            
            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => "",
                'count' => $count,
                'message'=> 'Administradora sincronizada con exito!'
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

    public function combo(Request $request)
    {
        $nomAdministradoras = NomAdministradoras::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(codigo, ' - ', descripcion) as text")
        );

        if ($request->has("tipo")) {
            $nomAdministradoras->where('tipo', $request->get("tipo"));
        }

        if ($request->get("search")) {
            $nomAdministradoras->where(function($query) use ($request) {
                $query->where('codigo', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('descripcion', 'LIKE', '%' . $request->get("search") . '%');
            });
        }

        return $nomAdministradoras->paginate(40);
    }

    private function dataAdministradoras($data, $id_nit)
	{
        $nomAdministradorasExist = NomAdministradoras::where('codigo', $data[1])->first();
        if (!$nomAdministradorasExist) {
            return [
                'tipo' => $this->tipoAdministradora[$data[0]],
                'codigo' => $data[1],
                'id_nit' => $id_nit,
                'descripcion' => $data[4]
            ];
        }
        return null;
	}


}