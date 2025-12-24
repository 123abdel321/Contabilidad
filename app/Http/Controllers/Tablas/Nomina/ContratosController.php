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
use App\Models\Sistema\Nomina\NomContratos;

class ContratosController extends Controller
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
        return view('pages.tablas.contratos.contratos-view');
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

        $nomContratos = NomContratos::with(
                'nit',
                'cecos',
                'periodo',
                'concepto_basico',
                'fondo_salud',
                'fondo_pension',
                'fondo_cesantias',
                'fondo_caja_compensacion',
                'fondo_arl',
            )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(nom_contratos.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nom_contratos.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nom_contratos.created_by',
                'nom_contratos.updated_by'
            )
        ->orderBy('id', 'desc');

        if($searchValue) {
            $nomContratos->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%');
        }

        $totalNomContratos = $nomContratos->count();
        $nomContratos = $nomContratos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalNomContratos,
            'iTotalDisplayRecords' => $totalNomContratos,
            'data' => $nomContratos->get(),
            'perPage' => $rowperpage,
            'message'=> 'Administradores cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
			"id_empleado" => "required|exists:sam.nits,id",
			"id_periodo" => "required|exists:sam.nom_periodos,id",
			"id_fondo_salud" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_pension" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_cesantias" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_caja_compensacion" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_arl" => "nullable|exists:sam.nom_administradoras,id",
			"fecha_inicio_contrato" => "required",
			"estado" => "required|in:0,1,2",
			"termino" => "required|in:1,2,3,4,5",
			"tipo_salario" => "required|in:0,1,2,3,4",
			"tipo_empleado" => "nullable|in:0,1,2,3",
			"tipo_cotizante" => "nullable|in:1,12,19",
			"subtipo_cotizante" => "nullable|in:1,3,9",
			"auxilio_transporte" => "nullable|in:0,1",
			"metodo_retencion_compensacion" => "required|in:0,1",
			"nivel_riesgo_arl_compensacion" => "nullable|in:0,1,2,3,4,5",
			"id_centro_costo" => "required|exists:sam.centro_costos,id",
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
            
            $nomContratos = NomContratos::create([
                'id_empleado' => $request->get('id_empleado'),
                'id_periodo' => $request->get('id_periodo'),
                'id_concepto_basico' => $request->get('id_concepto_basico'),
                'fecha_inicio_contrato' => $request->get('fecha_inicio_contrato'),
                'fecha_fin_contrato' => $request->get('fecha_fin_contrato'),
                'estado' => $request->get('estado'),
                'termino' => $request->get('termino'),
                'tipo_salario' => $request->get('tipo_salario'),
                'tipo_empleado' => $request->get('tipo_empleado'),
                'id_centro_costo' => $request->get('id_centro_costo'),
                'id_oficio' => $request->get('id_oficio'),
                'salario' => $request->get('salario'),
                'tipo_cotizante' => $request->get('tipo_cotizante'),
                'subtipo_cotizante' => $request->get('subtipo_cotizante'),
                'id_fondo_salud' => $request->get('id_fondo_salud'),
                'id_fondo_pension' => $request->get('id_fondo_pension'),
                'id_fondo_cesantias' => $request->get('id_fondo_cesantias'),
                'id_fondo_caja_compensacion' => $request->get('id_fondo_caja_compensacion'),
                'id_fondo_arl' => $request->get('id_fondo_arl'),
                'nivel_riesgo_arl' => $request->get('nivel_riesgo_arl_compensacion'),
                'porcentaje_arl' => $request->get('porcentaje_arl'),
                'metodo_retencion' => $request->get('metodo_retencion_compensacion'),
                'porcentaje_fijo' => $request->get('porcentaje_fijo'),
                'disminucion_defecto_retencion' => $request->get('disminucion_defecto_retencion'),
                'auxilio_transporte' => $request->get('auxilio_transporte'),
                'talla_camisa' => $request->get('talla_camisa'),
                'talla_pantalon' => $request->get('talla_pantalon'),
                'talla_zapatos' => $request->get('talla_zapatos'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomContratos,
                'message'=> 'Contrato creado con exito!'
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
            'id' => 'required|exists:sam.nom_contratos,id',
            "id_empleado" => "required|exists:sam.nits,id",
			"id_periodo" => "required|exists:sam.nom_periodos,id",
			"id_fondo_salud" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_pension" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_cesantias" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_caja_compensacion" => "nullable|exists:sam.nom_administradoras,id",
			"id_fondo_arl" => "nullable|exists:sam.nom_administradoras,id",
			"fecha_inicio_contrato" => "required",
			"estado" => "required|in:0,1,2",
			"termino" => "required|in:0,1,2,3",
			"tipo_salario" => "required|in:0,1,2,3,4",
			"tipo_empleado" => "nullable|in:0,1,2,3",
			"tipo_cotizante" => "nullable|in:1,12,19",
			"subtipo_cotizante" => "nullable|in:1,3,9",
			"auxilio_transporte" => "nullable|in:0,1",
			"metodo_retencion_compensacion" => "nullable|in:0,1",
			"nivel_riesgo_arl_compensacion" => "nullable|in:0,1,2,3,4,5",
			"id_centro_costo" => "required|exists:sam.centro_costos,id",
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

            $nomContratos = NomContratos::where('id', $request->get('id'))->first();

            $nomContratos->id_empleado = $request->get('id_empleado');
            $nomContratos->id_periodo = $request->get('id_periodo');
            $nomContratos->id_concepto_basico = $request->get('id_concepto_basico');
            $nomContratos->fecha_inicio_contrato = $request->get('fecha_inicio_contrato');
            $nomContratos->fecha_fin_contrato = $request->get('fecha_fin_contrato');
            $nomContratos->estado = $request->get('estado');
            $nomContratos->termino = $request->get('termino');
            $nomContratos->tipo_salario = $request->get('tipo_salario');
            $nomContratos->tipo_empleado = $request->get('tipo_empleado');
            $nomContratos->id_centro_costo = $request->get('id_centro_costo');
            $nomContratos->id_oficio = $request->get('id_oficio');
            $nomContratos->salario = $request->get('salario');
            $nomContratos->tipo_cotizante = $request->get('tipo_cotizante');
            $nomContratos->subtipo_cotizante = $request->get('subtipo_cotizante');
            $nomContratos->id_fondo_salud = $request->get('id_fondo_salud');
            $nomContratos->id_fondo_pension = $request->get('id_fondo_pension');
            $nomContratos->id_fondo_cesantias = $request->get('id_fondo_cesantias');
            $nomContratos->id_fondo_caja_compensacion = $request->get('id_fondo_caja_compensacion');
            $nomContratos->id_fondo_arl = $request->get('id_fondo_arl');
            $nomContratos->nivel_riesgo_arl = $request->get('nivel_riesgo_arl_compensacion');
            $nomContratos->porcentaje_arl = $request->get('porcentaje_arl');
            $nomContratos->metodo_retencion = $request->get('metodo_retencion_compensacion');
            $nomContratos->porcentaje_fijo = $request->get('porcentaje_fijo');
            $nomContratos->disminucion_defecto_retencion = $request->get('disminucion_defecto_retencion');
            $nomContratos->auxilio_transporte = $request->get('auxilio_transporte');
            $nomContratos->talla_camisa = $request->get('talla_camisa');
            $nomContratos->talla_pantalon = $request->get('talla_pantalon');
            $nomContratos->talla_zapatos = $request->get('talla_zapatos');
            $nomContratos->updated_by = request()->user()->id;
            $nomContratos->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomContratos,
                'message'=> 'Contrato actualizado con exito!'
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

            NomContratos::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Contrato eliminada con exito!'
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