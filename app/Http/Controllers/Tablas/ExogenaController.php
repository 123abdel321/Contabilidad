<?php

namespace App\Http\Controllers\Tablas;

use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\ProcessInformeExogena;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Informes\InfExogena;
use App\Models\Sistema\ExogenaFormato;
use App\Models\Informes\InfExogenaDetalle;
use App\Models\Sistema\ExogenaFormatoColumna;
use App\Models\Sistema\ExogenaFormatoConcepto;

class ExogenaController extends Controller
{

    public function index ()
    {
        return view('pages.contabilidad.exogena.exogena-view');
    }

    public function generate(Request $request)
    {
        try {
            if (!$request->get('year')) {
                return response()->json([
                    'success'=>	false,
                    'data' => [],
                    'message'=> 'Por favor ingresar un rango de fechas válido.'
                ], Response::HTTP_NO_CONTENT);
            }
            
            $empresa = Empresa::where('token_db', $request->user()['has_empresa'])->first();

            $exogena = InfExogena::where('id_empresa', $empresa->id)
                ->where('year', $request->get('year'))
                ->where('id_nit', $request->get('id_nit', null))
                ->where('id_exogena_formato', $request->get('id_formato'))
                ->where('id_exogena_formato_concepto', $request->get('id_concepto', null))
                ->first();

            if ($exogena && $exogena->estado == 1) {

                $created = Carbon::parse($exogena->created_at);
                $now = Carbon::now();

                $diffInSeconds = $created->diffInSeconds($now);
                $diffFormatted = floor($diffInSeconds / 60) . 'm ' . ($diffInSeconds % 60) . 's';

                return response()->json([
                    'success'=>	true,
                    'time' => $created->format('Y-m-d H:i') . " ({$diffFormatted})",
                    'data' => '',
                    'message'=> 'Generando informe de exogena'
                ], Response::HTTP_OK);
            }
            
            if($exogena) {
                InfExogenaDetalle::where('id_exogena', $exogena->id)->delete();
                $exogena->delete();
            }
            
            $data = $request->except(['columns']);

            $exogena = InfExogena::create([
                'year' => $request->get('year'),
                'id_empresa' => $empresa->id,
                'id_nit' => $request->get('id_nit'),
                'id_exogena_formato' => $request->get('id_formato'),
                'id_exogena_formato_concepto' => $request->get('id_concepto'),
            ]);
            
            ProcessInformeExogena::dispatch(
                $data,
                $request->user()->id,
                $empresa->id,
                $exogena->id
            );

            return response()->json([
                'success'=>	true,
                'time' => null,
                'data' => '',
                'message'=> 'Generando informe de medios magneticos'
            ], Response::HTTP_OK);
            
        } catch (Exception $e) {

			DB::connection('sam')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $exogena = InfExogena::where('id', $request->get('id'))->first();
        
        // Obtener los datos del formato
        $formatoData = null;
        if ($exogena->id_exogena_formato) {
            $formato = ExogenaFormato::with('columnas')->find($exogena->id_exogena_formato);
            if ($formato) {
                $formatoData = [
                    'tipo_documento' => (bool)$formato->tipo_documento,
                    'numero_documento' => (bool)$formato->numero_documento,
                    'digito_verificacion' => (bool)$formato->digito_verificacion,
                    'primer_apellido' => (bool)$formato->primer_apellido,
                    'segundo_apellido' => (bool)$formato->segundo_apellido,
                    'primer_nombre' => (bool)$formato->primer_nombre,
                    'otros_nombres' => (bool)$formato->otros_nombres,
                    'razon_social' => (bool)$formato->razon_social,
                    'direccion' => (bool)$formato->direccion,
                    'departamento' => (bool)$formato->departamento,
                    'municipio' => (bool)$formato->municipio,
                    'pais' => (bool)$formato->pais,
                    'columnas' => $formato->columnas->map(function($columna) {
                        return ['columna' => $columna->columna];
                    })->toArray()
                ];
            }
        }

        $informe = InfExogenaDetalle::where('id_exogena', $exogena->id)
            ->select('*', 
                DB::raw("CONCAT(primer_nombre, ' ', otros_nombres) as nombre"),
                DB::raw("CONCAT(primer_nombre, ' ', otros_nombres, ' ', primer_apellido, ' ', segundo_apellido) as nombre_completo")
            );
        
        $total = InfExogenaDetalle::where('id_exogena', $exogena->id)->orderBy('id', 'desc')->first();
        $filtros = true;

        $informeTotals = $informe->get();
        $informePaginate = $informe->skip($start)->take($rowperpage);

        return response()->json([
            'success'=> true,
            'draw' => $draw,
            'iTotalRecords' => $informeTotals->count(),
            'iTotalDisplayRecords' => $informeTotals->count(),
            'data' => $informePaginate->get(),
            'perPage' => $rowperpage,
            'totales' => $total,
            'filtros' => $filtros,
            'formato_data' => $formatoData, // ← AQUÍ ENVÍAS LOS DATOS DEL FORMATO
            'message'=> 'Balance generado con exito!'
        ]);
    }

    public function getFormatoData($id)
    {
        try {
            $formato = ExogenaFormato::with('columnas')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $formato,
                'message' => 'Formato obtenido con éxito'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener formato: ' . $e->getMessage()
            ], 500);
        }
    }

    public function comboFormato(Request $request)
    {
        $formato = ExogenaFormato::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(formato) as text")
        );

        if ($request->get("q")) {
            $formato->where('formato', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $formato->paginate(40);
    }

    public function comboFormatoConcepto(Request $request)
    {
        $formatoConcepto = ExogenaFormatoConcepto::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(concepto) as text")
        );

        if ($request->get("q")) {
            $formatoConcepto->where('concepto', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $formatoConcepto->paginate(40);
    }

    public function comboFormatoColumna(Request $request)
    {
        $formatoColumna = ExogenaFormatoColumna::select(
            \DB::raw('*'),
            \DB::raw("CONCAT(nombre) as text")
        );

        if ($request->get("q")) {
            $formatoColumna->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $formatoColumna->paginate(40);
    }
}
