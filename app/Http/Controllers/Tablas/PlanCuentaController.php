<?php

namespace App\Http\Controllers\Tablas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\TipoCuenta;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\Comprobantes;
use App\Models\Sistema\DocumentosGeneral;

class PlanCuentaController extends Controller
{
    public function index ()
    {
        $tipoCuenta = TipoCuenta::get();

        $data = [
            'tipoCuenta' => $tipoCuenta,
        ];

        return view('pages.tablas.plan_cuentas.plan_cuentas-view', $data);
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
        $totalRecordswithFilter = PlanCuentas::select('count(*) as allcount')
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('cuenta', 'like', '%' .$searchValue . '%')
            ->count();

        $cuentas = PlanCuentas::orderBy($columnName,$columnSortOrder)
            ->with('tipo_cuenta', 'padre')
            ->where('nombre', 'like', '%' .$searchValue . '%')
            ->orWhere('cuenta', 'like', '%' .$searchValue . '%')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'data' => $cuentas,
            'perPage' => $rowperpage,
            'iTotalRecords' => $totalRecordswithFilter,
            'iTotalDisplayRecords' => $totalRecordswithFilter,
            'message'=> 'Comprobante generado con exito!'
        ]);
    }

    public function create (Request $request)
    {
        $cuentaPadre = '';

        if($request->get('id_padre')){
            $padre = PlanCuentas::find($request->get('id_padre'));
            $cuentaPadre = $padre->cuenta;
        }

        $cuenta = PlanCuentas::create([
            'id_padre' => $request->get('id_padre'),
            'id_tipo_cuenta' => $request->get('id_tipo_cuenta'),
            'id_impuesto' => $request->get('id_impuesto'),
            'cuenta' => $cuentaPadre.$request->get('cuenta'),
            'nombre' => $request->get('nombre'),
            'auxiliar' => 1,
            'exige_nit' => $request->get('exige_nit'),
            'exige_documento_referencia' => $request->get('exige_documento_referencia'),
            'exige_concepto' => $request->get('exige_concepto'),
            'exige_centro_costos' => $request->get('exige_centro_costos'),
            'naturaleza_cuenta' => $request->get('naturaleza_cuenta'),
            'naturaleza_ingresos' => $request->get('naturaleza_ingresos'),
            'naturaleza_egresos' => $request->get('naturaleza_egresos'),
            'naturaleza_compras' => $request->get('naturaleza_compras'),
            'naturaleza_ventas' => $request->get('naturaleza_ventas'),
        ]);

        return response()->json([
            'success'=>	true,
            'data' => $cuenta,
            'message'=> 'Cuenta creada con exito!'
        ]);
    }

    public function update (Request $request)
    {
        $cuentaPadre = '';
        
        if($request->get('id_padre')){
            $padre = PlanCuentas::find($request->get('id_padre'));
            $cuentaPadre = $padre->cuenta;
        }

        $auxiliar = PlanCuentas::where('id_padre', $request->get('id_cuenta'));

        PlanCuentas::where('id', $request->get('id_cuenta'))
            ->update([
                'id_padre' => $request->get('id_padre'),
                'id_tipo_cuenta' => $request->get('id_tipo_cuenta'),
                'id_impuesto' => $request->get('id_impuesto'),
                'cuenta' => $cuentaPadre.$request->get('cuenta'),
                'nombre' => $request->get('nombre'),
                'auxiliar' => $auxiliar->count() > 0 ? 1 : 0,
                'exige_nit' => $request->get('exige_nit'),
                'exige_documento_referencia' => $request->get('exige_documento_referencia'),
                'exige_concepto' => $request->get('exige_concepto'),
                'exige_centro_costos' => $request->get('exige_centro_costos'),
                'naturaleza_cuenta' => $request->get('naturaleza_cuenta'),
                'naturaleza_ingresos' => $request->get('naturaleza_ingresos'),
                'naturaleza_egresos' => $request->get('naturaleza_egresos'),
                'naturaleza_compras' => $request->get('naturaleza_compras'),
                'naturaleza_ventas' => $request->get('naturaleza_ventas'),
            ]);

        $planCuenta = PlanCuentas::where('id', $request->get('id_cuenta'))->with('tipo_cuenta', 'padre')->first();

        return response()->json([
            'success'=>	true,
            'data' => $planCuenta,
            'message'=> 'Cuenta actualizada con exito!'
        ]);
    }

    public function delete (Request $request)
    {
        $documentos = DocumentosGeneral::where('id_cuenta', $request->get('id'));
        if($documentos->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'No se puede eliminar una cuenta usado por los documentos!'
            ]);
        }

        $padre = PlanCuentas::where('id_padre', $request->get('id'));
        if($padre->count() > 0) {
            return response()->json([
                'success'=>	false,
                'data' => '',
                'message'=> 'No se puede eliminar una cuenta padre!'
            ]);
        }

        PlanCuentas::where('id', $request->get('id'))->delete();

        return response()->json([
            'success'=>	true,
            'data' => '',
            'message'=> 'Cuenta eliminada con exito!'
        ]);
    }

    public function comboCuenta(Request $request)
    {
        $naturaleza = "naturaleza_cuenta AS naturaleza_cuenta";
        if($request->has("id_comprobante")) {
            $comprobante = Comprobantes::where('id', $request->get("id_comprobante"))->first();
            if($comprobante){
                $tipoComprobante = $comprobante->tipo_comprobante;
                switch ($tipoComprobante) {
                    case 0:
                        $naturaleza = "naturaleza_ingresos AS naturaleza_cuenta";
                        break;
                    case 1:
                        $naturaleza = "naturaleza_egresos AS naturaleza_cuenta";
                        break;
                    case 2:
                        $naturaleza = "naturaleza_compras AS naturaleza_cuenta";
                        break;
                    case 3:
                        $naturaleza = "naturaleza_ventas AS naturaleza_cuenta";
                        break;
                    default:
                        $naturaleza = "naturaleza_cuenta AS naturaleza_cuenta";
                        break;
                }
            }
        }

        $planCuenta = PlanCuentas::where('cuenta', '<', '999999')->select(
            'id',
            'cuenta',
            'exige_nit',
            'exige_documento_referencia',
            'exige_concepto',
            'exige_centro_costos',
            'nombre',
            \DB::raw($naturaleza),
            \DB::raw("CONCAT(cuenta, ' - ', nombre) as text")
        );

        if ($request->get("q")) {
            $planCuenta->where('cuenta', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $planCuenta->orderBy('cuenta')->paginate(40);
    }
}
