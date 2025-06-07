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
use App\Models\Sistema\Nomina\NomConceptos;

class ConceptosNominaController extends Controller
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
        $conceptosNomina = [
            ['codigo' => 'basico', 'nombre' => 'Básicos'],
            ['codigo' => 'auxilio_transporte', 'nombre' => 'Auxilio de transporte'],
            ['codigo' => 'viatico_manu_aloj_s', 'nombre' => 'Manutención y/o alojamiento'],
            ['codigo' => 'viatico_manu_aloj_ns', 'nombre' => 'Manutención y/o alojamiento no salariales'],
            ['codigo' => 'heds', 'nombre' => 'Horas extras diurnas'],
            ['codigo' => 'hens', 'nombre' => 'Horas extras nocturnas'],
            ['codigo' => 'hrns', 'nombre' => 'Horas recargo nocturno'],
            ['codigo' => 'heddfs', 'nombre' => 'Horas extras diurnas festivas'],
            ['codigo' => 'hrddfs', 'nombre' => 'Horas recargo diurnas festivas'],
            ['codigo' => 'hendfs', 'nombre' => 'Horas extras nocturnas festivas'],
            ['codigo' => 'hrndfs', 'nombre' => 'Horas recargo nocturno festivas'],
            ['codigo' => 'vacaciones_comunes', 'nombre' => 'Vacaciones comunes'],
            ['codigo' => 'vacaciones_compensadas', 'nombre' => 'Vacaciones compensadas'],
            ['codigo' => 'primas', 'nombre' => 'Primas'],
            ['codigo' => 'cesantias', 'nombre' => 'Cesantías'],
            ['codigo' => 'incapacidades', 'nombre' => 'Incapacidades'],
            ['codigo' => 'licencia_mp', 'nombre' => 'Licencia de maternidad o paternidad'],
            ['codigo' => 'licencia_r', 'nombre' => 'Licencia remunerada'],
            ['codigo' => 'licencia_nr', 'nombre' => 'Licencia no remunerada'],
            ['codigo' => 'bonificaciones', 'nombre' => 'Bonificaciones'],
            ['codigo' => 'bonificacion_s', 'nombre' => 'Bonificación salarial'],
            ['codigo' => 'bonificacion_ns', 'nombre' => 'Bonificación no salarial'],
            ['codigo' => 'auxilios', 'nombre' => 'Auxilios'],
            ['codigo' => 'auxilio_s', 'nombre' => 'Auxilio salarial'],
            ['codigo' => 'auxilio_ns', 'nombre' => 'Auxilio no salarial'],
            ['codigo' => 'huelgas_legales', 'nombre' => 'Huelgas legales'],
            ['codigo' => 'otros_conceptos', 'nombre' => 'Otros conceptos'],
            ['codigo' => 'concepto_s', 'nombre' => 'Concepto salarial'],
            ['codigo' => 'concepto_ns', 'nombre' => 'Concepto no salarial'],
            ['codigo' => 'pago_s', 'nombre' => 'Pago salarial'],
            ['codigo' => 'pago_ns', 'nombre' => 'Pago no salarial'],
            ['codigo' => 'pago_alimentacion_s', 'nombre' => 'Pago alimentación salarial'],
            ['codigo' => 'pago_alimentacion_ns', 'nombre' => 'Pago alimentación no salarial'],
            ['codigo' => 'compensaciones', 'nombre' => 'Compensaciones'],
            ['codigo' => 'bono_epctv_s', 'nombre' => 'Bonos electr, cheques, etc'],
            ['codigo' => 'comisiones', 'nombre' => 'Comisiones'],
            ['codigo' => 'pagos_terceros', 'nombre' => 'Pagos terceros'],
            ['codigo' => 'anticipos', 'nombre' => 'Anticipos'],
            ['codigo' => 'dotacion', 'nombre' => 'Dotación'],
            ['codigo' => 'apoyo_sost', 'nombre' => 'Apoyo sostenible'],
            ['codigo' => 'teletrabajo', 'nombre' => 'Teletrabajo'],
            ['codigo' => 'bonif_retiro', 'nombre' => 'Bonificación retiro'],
            ['codigo' => 'indemnizacion', 'nombre' => 'Indemnización'],
            ['codigo' => 'salud', 'nombre' => 'Salud'],
            ['codigo' => 'fondo_pension', 'nombre' => 'Fondo pensión'],
            ['codigo' => 'fondo_sp', 'nombre' => 'Fondo de seguridad pensional'],
            ['codigo' => 'sindicatos', 'nombre' => 'Sindicatos'],
            ['codigo' => 'sanciones', 'nombre' => 'Sanciones'],
            ['codigo' => 'libranzas', 'nombre' => 'Libranzas'],
            ['codigo' => 'otras_deducciones', 'nombre' => 'Otras deducciones'],
            ['codigo' => 'pension_voluntaria', 'nombre' => 'Pensión voluntaria'],
            ['codigo' => 'retencion_fuente', 'nombre' => 'Retención fuente'],
            ['codigo' => 'ica', 'nombre' => 'ICA'],
            ['codigo' => 'afc', 'nombre' => 'Ahorro fomento a la construcción'],
            ['codigo' => 'cooperativa', 'nombre' => 'Cooperativa'],
            ['codigo' => 'embargo_fiscal', 'nombre' => 'Embargo fiscal'],
            ['codigo' => 'plan_complementarios', 'nombre' => 'Plan complementarios'],
            ['codigo' => 'educacion', 'nombre' => 'Educación'],
            ['codigo' => 'reintegro', 'nombre' => 'Reintegro'],
            ['codigo' => 'deuda', 'nombre' => 'Deuda']
        ];

        $data = [
            'conceptosNomina' => $conceptosNomina
        ];

        return view('pages.tablas.conceptos_nomina.conceptos_nomina-view', $data);
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $searchValue = $request->get('search');
        $searchValue = isset($searchValue) ? $searchValue["value"] : null;

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc

        $nomConceptos = NomConceptos::with('cuenta_administrativos', 'cuenta_operativos', 'cuenta_ventas', 'cuenta_otros')
            ->select(
                '*',
                DB::raw("DATE_FORMAT(nom_conceptos.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nom_conceptos.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nom_conceptos.created_by',
                'nom_conceptos.updated_by'
            )
            ->orderBy('id', 'desc');

        if($searchValue) {
            $nomConceptos->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%');
        }

        $totalNomConceptos = $nomConceptos->count();
        $nomConceptos = $nomConceptos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalNomConceptos,
            'iTotalDisplayRecords' => $totalNomConceptos,
            'data' => $nomConceptos->get(),
            'perPage' => $rowperpage,
            'message'=> 'Administradores cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $rules = [
			"tipo_concepto" => "required",
			"codigo" => "required|string|unique:sam.nom_conceptos,codigo",
			"nombre" => "required|string|unique:sam.nom_conceptos,nombre",
			"id_cuenta_administrativos" => 'nullable|exists:sam.plan_cuentas,id',
			"id_cuenta_operativos" => 'nullable|exists:sam.plan_cuentas,id',
			"id_cuenta_ventas" => 'nullable|exists:sam.plan_cuentas,id',
			"id_cuenta_otros" => 'nullable|exists:sam.plan_cuentas,id',
			"porcentaje" => "nullable",
			"unidad" => "required",
			"id_concepto_porcentaje" => "nullable",
			"base_retencion" => 'required|boolean',
			"base_sena" => 'required|boolean',
			"base_icbf" => 'required|boolean',
			"base_caja_compensacion" => 'required|boolean',
			"base_salud" => 'required|boolean',
			"base_pension" => 'required|boolean',
			"base_arl" => 'required|boolean',
			"base_vacacion" => 'required|boolean',
			"base_prima" => 'required|boolean',
			"base_cesantia" => 'required|boolean',
			"base_interes_cesantia" => 'required|boolean',
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
            
            $nomConceptos = NomConceptos::create([
                'tipo_concepto' => $request->get('tipo_concepto'),
                'codigo' => $request->get('codigo'),
                'nombre' => $request->get('nombre'),
                'id_cuenta_administrativos' => $request->get('id_cuenta_administrativos'),
                'id_cuenta_operativos' => $request->get('id_cuenta_operativos'),
                'id_cuenta_ventas' => $request->get('id_cuenta_ventas'),
                'id_cuenta_otros' => $request->get('id_cuenta_otros'),
                'porcentaje' => $request->get('porcentaje'),
                'id_concepto_porcentaje' => $request->get('id_concepto_porcentaje'),
                'unidad' => $request->get('unidad'),
                'valor_mensual' => $request->get('valor_mensual'),
                'concepto_fijo' => $request->get('concepto_fijo'),
                'base_retencion' => $request->get('base_retencion'),
                'base_sena' => $request->get('base_sena'),
                'base_icbf' => $request->get('base_icbf'),
                'base_caja_compensacion' => $request->get('base_caja_compensacion'),
                'base_salud' => $request->get('base_salud'),
                'base_pension' => $request->get('base_pension'),
                'base_arl' => $request->get('base_arl'),
                'base_vacacion' => $request->get('base_vacacion'),
                'base_prima' => $request->get('base_prima'),
                'base_cesantia' => $request->get('base_cesantia'),
                'base_interes_cesantia' => $request->get('base_interes_cesantia'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomConceptos,
                'message'=> 'Concepto nomina creado con exito!'
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
        $id = $request->input('id');
        
        $rules = [
            "id" => 'required|exists:sam.nom_conceptos,id',
			"tipo_concepto" => "required",
			"codigo" => [
                'required',
                'string',
                Rule::unique('sam.nom_conceptos', 'codigo')->ignore($id)
            ],
            "nombre" => [
                'required',
                'string',
                Rule::unique('sam.nom_conceptos', 'nombre')->ignore($id)
            ],
			"id_cuenta_administrativos" => 'nullable|exists:sam.plan_cuentas,id',
			"id_cuenta_operativos" => 'nullable|exists:sam.plan_cuentas,id',
			"id_cuenta_ventas" => 'nullable|exists:sam.plan_cuentas,id',
			"id_cuenta_otros" => 'nullable|exists:sam.plan_cuentas,id',
			"porcentaje" => "nullable",
			"unidad" => "required",
			"id_concepto_porcentaje" => "nullable",
			"base_retencion" => 'required|boolean',
			"base_sena" => 'required|boolean',
			"base_icbf" => 'required|boolean',
			"base_caja_compensacion" => 'required|boolean',
			"base_salud" => 'required|boolean',
			"base_pension" => 'required|boolean',
			"base_arl" => 'required|boolean',
			"base_vacacion" => 'required|boolean',
			"base_prima" => 'required|boolean',
			"base_cesantia" => 'required|boolean',
			"base_interes_cesantia" => 'required|boolean',
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

            $nomConceptos = NomConceptos::where('id', $request->get('id'))->first();

            $nomConceptos->tipo_concepto = $request->get("tipo_concepto");
            $nomConceptos->codigo = $request->get("codigo");
            $nomConceptos->nombre = $request->get("nombre");
            $nomConceptos->id_cuenta_administrativos = $request->get("id_cuenta_administrativos");
            $nomConceptos->id_cuenta_operativos = $request->get("id_cuenta_operativos");
            $nomConceptos->id_cuenta_ventas = $request->get("id_cuenta_ventas");
            $nomConceptos->id_cuenta_otros = $request->get("id_cuenta_otros");
            $nomConceptos->porcentaje = $request->get("porcentaje");
            $nomConceptos->unidad = $request->get("unidad");
            $nomConceptos->id_concepto_porcentaje = $request->get("id_concepto_porcentaje");
            $nomConceptos->base_retencion = $request->get("base_retencion");
            $nomConceptos->base_sena = $request->get("base_sena");
            $nomConceptos->base_icbf = $request->get("base_icbf");
            $nomConceptos->base_caja_compensacion = $request->get("base_caja_compensacion");
            $nomConceptos->base_salud = $request->get("base_salud");
            $nomConceptos->base_pension = $request->get("base_pension");
            $nomConceptos->base_arl = $request->get("base_arl");
            $nomConceptos->base_vacacion = $request->get("base_vacacion");
            $nomConceptos->base_prima = $request->get("base_prima");
            $nomConceptos->base_cesantia = $request->get("base_cesantia");
            $nomConceptos->base_interes_cesantia = $request->get("base_interes_cesantia");
            $nomConceptos->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomConceptos,
                'message'=> 'Concepto nomina actualizado con exito!'
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

            NomConceptos::where('id', $request->get('id'))->delete();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Concepto nomina eliminado con exito!'
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