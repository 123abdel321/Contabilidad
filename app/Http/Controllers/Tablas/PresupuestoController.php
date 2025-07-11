<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Presupuesto;
use App\Models\Sistema\PlanCuentas;
use App\Models\Sistema\PresupuestoDetalle;

class PresupuestoController extends Controller
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
        return view('pages.tablas.presupuesto.presupuesto-view');
    }

    public function generate (Request $request)
    {
        try {
            $presupuestoDetalle = PresupuestoDetalle::whereNotNull('id');
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");
            $presupuesto = null;

            if ($request->get('search')) {
                $presupuestoDetalle->where('cuenta', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('nombre', 'LIKE', '%'.$request->get('search').'%');
            }

            if ($request->get("id_presupuesto")) {
                $presupuestoDetalle->where('id_presupuesto', $request->get("id_presupuesto"));
                $presupuesto = Presupuesto::find($request->get("id_presupuesto"));
            } else {

                $presupuesto = Presupuesto::where('periodo', $request->get("periodo"))
                    ->where('tipo', $request->get("tipo"))
                    ->first();

                if (!$presupuesto) {
                    return response()->json([
                        'success'=>	true,
                        'draw' => $draw,
                        'presupuesto' => null,
                        'iTotalRecords' => 0,
                        'iTotalDisplayRecords' => 0,
                        'data' => [],
                        'perPage' => 0,
                        'message'=> 'Presupuesto generados con exito!'
                    ]);
                }
                $presupuestoDetalle->where('id_presupuesto', $presupuesto->id);
            }

            $presupuestoTotals = $presupuestoDetalle->get();

            $presupuestoPaginate = $presupuestoDetalle->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'presupuesto' => $presupuesto,
                'iTotalRecords' => $presupuestoTotals->count(),
                'iTotalDisplayRecords' => $presupuestoTotals->count(),
                'data' => $presupuestoPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Presupuesto generados con exito!'
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

    public function create (Request $request)
    {
        $rules = [
            'periodo' => 'required',
            'tipo' => 'required'
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
            
            $presupuesto = Presupuesto::create([
                'periodo' => $request->get('periodo'),
                'tipo' => $request->get('tipo'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $cuentasPresupuesto = [];
            $tipoPresupuesto = $request->get('tipo') == '1' ? '4' : '5';

            $planCuentas = PlanCuentas::where('cuenta', 'LIKE', $tipoPresupuesto.'%')
                ->where('auxiliar', 1)
                ->with('padre')
                ->get();

            foreach ($planCuentas as $planCuenta) {
                $cuentasPresupuesto[$planCuenta->cuenta] = $this->generarEstructura($planCuenta, $presupuesto->id);
                if ($planCuenta->id_padre) {
                    $padre = $planCuenta->padre;
                    if ($padre) {
                        $cuentasPresupuesto[$padre->cuenta] = $this->generarEstructura($padre, $presupuesto->id);
                    }
                }
            }

            $queryTotales = PresupuestoDetalle::where('auxiliar', '0');

            $totalPresupuesto = [
                'id_presupuesto' => $presupuesto->id,
                'id_padre' => '',
                'cuenta' => '',
                'nombre' => 'TOTALES',
                'presupuesto' => $queryTotales->sum('presupuesto'),
                'diferencia' => $queryTotales->sum('diferencia'),
                'enero' => $queryTotales->sum('enero'),
                'febrero' => $queryTotales->sum('febrero'),
                'marzo' => $queryTotales->sum('marzo'),
                'abril' => $queryTotales->sum('abril'),
                'mayo' => $queryTotales->sum('mayo'),
                'junio' => $queryTotales->sum('junio'),
                'julio' => $queryTotales->sum('julio'),
                'agosto' => $queryTotales->sum('agosto'),
                'septiembre' => $queryTotales->sum('septiembre'),
                'octubre' => $queryTotales->sum('octubre'),
                'noviembre' => $queryTotales->sum('noviembre'),
                'diciembre' => $queryTotales->sum('diciembre'),
                'auxiliar' => '2',
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ];

            $cuentasPresupuesto['1'] = $totalPresupuesto;
            $cuentasPresupuesto['9'] = $totalPresupuesto;

            ksort($cuentasPresupuesto, SORT_STRING | SORT_FLAG_CASE);

            foreach (array_chunk($cuentasPresupuesto,233) as $cuentasPpts){
                DB::connection('sam')
                    ->table('presupuesto_detalles')
                    ->insert(array_values($cuentasPpts));
            }

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Presupuesto generado con exito!'
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
        try {
            DB::connection('sam')->beginTransaction();
            $presupuestoD = PresupuestoDetalle::where('id', $request->get('id'))->first();
            $presupuesto = Presupuesto::where('id', $presupuestoD->id_presupuesto)->first();
            PresupuestoDetalle::where('id', $request->get('id'))
                ->update([
                    'presupuesto' => $request->get('presupuesto'),
                    'diferencia' => $request->get('diferencia'),
                    'enero' => $request->get('enero'),
                    'febrero' => $request->get('febrero'),
                    'marzo' => $request->get('marzo'),
                    'abril' => $request->get('abril'),
                    'mayo' => $request->get('mayo'),
                    'junio' => $request->get('junio'),
                    'julio' => $request->get('julio'),
                    'agosto' => $request->get('agosto'),
                    'septiembre' => $request->get('septiembre'),
                    'octubre' => $request->get('octubre'),
                    'noviembre' => $request->get('noviembre'),
                    'diciembre' => $request->get('diciembre'),
                ]);

            $data =  [
                'presupuesto' => 0,
                'diferencia' => 0,
                'enero' => 0,
                'febrero' => 0,
                'marzo' => 0,
                'abril' => 0,
                'mayo' => 0,
                'junio' => 0,
                'julio' => 0,
                'agosto' => 0,
                'septiembre' => 0,
                'octubre' => 0,
                'noviembre' => 0,
                'diciembre' => 0,
            ];

            if ($request->get('id_padre')) {
                $padre = PlanCuentas::find($request->get('id_padre'));

                $totalesPadre = PresupuestoDetalle::where('id_padre', $request->get('id_padre'));

                $data = [
                    'presupuesto' => $totalesPadre->sum('presupuesto'),
                    'diferencia' => $totalesPadre->sum('diferencia'),
                    'enero' => $totalesPadre->sum('enero'),
                    'febrero' => $totalesPadre->sum('febrero'),
                    'marzo' => $totalesPadre->sum('marzo'),
                    'abril' => $totalesPadre->sum('abril'),
                    'mayo' => $totalesPadre->sum('mayo'),
                    'junio' => $totalesPadre->sum('junio'),
                    'julio' => $totalesPadre->sum('julio'),
                    'agosto' => $totalesPadre->sum('agosto'),
                    'septiembre' => $totalesPadre->sum('septiembre'),
                    'octubre' => $totalesPadre->sum('octubre'),
                    'noviembre' => $totalesPadre->sum('noviembre'),
                    'diciembre' => $totalesPadre->sum('diciembre'),
                ];

                PresupuestoDetalle::where('cuenta', $padre->cuenta)
                    ->update($data);

                $totalesGeneral = PresupuestoDetalle::where('auxiliar', '0');

                $dataTotal = [
                    'presupuesto' => $totalesGeneral->sum('presupuesto'),
                    'diferencia' => $totalesGeneral->sum('diferencia'),
                    'enero' => $totalesGeneral->sum('enero'),
                    'febrero' => $totalesGeneral->sum('febrero'),
                    'marzo' => $totalesGeneral->sum('marzo'),
                    'abril' => $totalesGeneral->sum('abril'),
                    'mayo' => $totalesGeneral->sum('mayo'),
                    'junio' => $totalesGeneral->sum('junio'),
                    'julio' => $totalesGeneral->sum('julio'),
                    'agosto' => $totalesGeneral->sum('agosto'),
                    'septiembre' => $totalesGeneral->sum('septiembre'),
                    'octubre' => $totalesGeneral->sum('octubre'),
                    'noviembre' => $totalesGeneral->sum('noviembre'),
                    'diciembre' => $totalesGeneral->sum('diciembre'),
                ];

                PresupuestoDetalle::where('auxiliar', '2')
                    ->update($dataTotal);
                    
            } else {
                PresupuestoDetalle::where('cuenta', 'LIKE', $request->get('cuenta').'%')
                    ->where('cuenta', '!=', $request->get('cuenta'))
                    ->update($data);
            }

            DB::connection('sam')->commit();
            return response()->json([
                'success'=>	true,
                'data' => '',
                'presupuesto' => $presupuesto,
                'message'=> 'Presupuesto actualizado con exito!'
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

    public function updateValor (Request $request)
    {
        $rules = [
            'id_presupuesto' => 'required',
            'valor_presupuesto' => 'required',
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
            
            Presupuesto::where('id', $request->get('id_presupuesto'))
                ->update([
                    'presupuesto' => $request->get('valor_presupuesto')
                ]);

            $presupuestoD = PresupuestoDetalle::where('id_presupuesto', $request->get('id_presupuesto'))
                ->where('auxiliar', '2')
                ->first();

            $presupuesto = Presupuesto::where('id', $request->get('id_presupuesto'))
                ->first();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'presupuesto' => $presupuesto,
                'total_presupuesto' => $presupuestoD->presupuesto,
                'message'=> 'Presupuesto actualizado con exitos!'
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

    public function grupo (Request $request)
    {
        try {
            DB::connection('sam')->beginTransaction();

            PresupuestoDetalle::where('cuenta', 'LIKE', $request->get('cuenta').'%')
                ->update([
                    'es_grupo' => $request->get('es_grupo')
                ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Presupuesto actualizado con exito!'
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

    private function generarEstructura($planCuenta, $idPresupuesto)
    {
        return [
            'id_presupuesto' => $idPresupuesto,
            'id_padre' => $planCuenta->auxiliar ? $planCuenta->id_padre : '',
            'cuenta' => $planCuenta->cuenta,
            'nombre' => $planCuenta->nombre,
            'presupuesto' => '',
            'diferencia' => '',
            'enero' => '',
            'febrero' => '',
            'marzo' => '',
            'abril' => '',
            'mayo' => '',
            'junio' => '',
            'julio' => '',
            'agosto' => '',
            'septiembre' => '',
            'octubre' => '',
            'noviembre' => '',
            'diciembre' => '',
            'auxiliar' => $planCuenta->auxiliar,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ];
    }

    private function generarEstructuraTotal($idPresupuesto)
    {
        return [
            'id_presupuesto' => $idPresupuesto,
            'id_padre' => '',
            'cuenta' => '',
            'nombre' => '',
            'presupuesto' => '',
            'diferencia' => '',
            'enero' => '',
            'febrero' => '',
            'marzo' => '',
            'abril' => '',
            'mayo' => '',
            'junio' => '',
            'julio' => '',
            'agosto' => '',
            'septiembre' => '',
            'octubre' => '',
            'noviembre' => '',
            'diciembre' => '',
            'auxiliar' => '2',
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ];
    }
}
