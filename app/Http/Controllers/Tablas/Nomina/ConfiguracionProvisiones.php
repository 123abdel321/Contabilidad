<?php

namespace App\Http\Controllers\Tablas\Nomina;

use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Nomina\NomConfiguracionProvisiones;

class ConfiguracionProvisiones extends Controller
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
        return view('pages.tablas.configuracion_provisiones.configuracion_provisiones-view');
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

        $nomConfiguracionProvisiones = NomConfiguracionProvisiones::with(
            'cuenta_administrativos',
            'cuenta_operativos',
            'cuenta_ventas',
            'cuenta_otros',
            'cuenta_pagar'
        )
            ->select(
                '*',
                DB::raw("DATE_FORMAT(nom_configuracion_provisiones.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(nom_configuracion_provisiones.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'nom_configuracion_provisiones.created_by',
                'nom_configuracion_provisiones.updated_by'
            )
            ->orderBy('id', 'desc');

        if($searchValue) {
            $nomConfiguracionProvisiones->where('nombre', 'like', '%' .$searchValue . '%')
                ->orWhere('codigo', 'like', '%' .$searchValue . '%');
        }

        $totalNomConfiguracionProvisiones = $nomConfiguracionProvisiones->count();
        $nomConfiguracionProvisiones = $nomConfiguracionProvisiones->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalNomConfiguracionProvisiones,
            'iTotalDisplayRecords' => $totalNomConfiguracionProvisiones,
            'data' => $nomConfiguracionProvisiones->get(),
            'perPage' => $rowperpage,
            'message'=> 'Configuracion provisiones cargados con exito!'
        ]);
    }

    public function update (Request $request)
    {
        $rules = [
			'id' => 'required|exists:sam.nom_configuracion_provisiones,id',
            'id_cuenta_administrativos' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_operativos' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_venta' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_otros' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_por_pagar' => 'nullable|exists:sam.plan_cuentas,id',
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

            $nomConfiguracionProvisiones = NomConfiguracionProvisiones::where('id', $request->get('id'))->first();
            
            $nomConfiguracionProvisiones->porcentaje = $request->get('porcentaje');
            $nomConfiguracionProvisiones->id_cuenta_administrativos = $request->get('id_cuenta_administrativos');
            $nomConfiguracionProvisiones->id_cuenta_operativos = $request->get('id_cuenta_operativos');
            $nomConfiguracionProvisiones->id_cuenta_ventas = $request->get('id_cuenta_ventas');
            $nomConfiguracionProvisiones->id_cuenta_otros = $request->get('id_cuenta_otros');
            $nomConfiguracionProvisiones->id_cuenta_por_pagar = $request->get('id_cuenta_por_pagar');
            $nomConfiguracionProvisiones->updated_by = request()->user()->id;

            $nomConfiguracionProvisiones->save();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $nomConfiguracionProvisiones,
                'message'=> 'Configuracion provisionada creada con exito!'
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