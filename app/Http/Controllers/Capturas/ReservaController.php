<?php

namespace App\Http\Controllers\Capturas;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODEL
use App\Models\Sistema\Reserva;

class ReservaController extends Controller
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
        return view('pages.capturas.reserva.reserva-view');
    }

    public function table (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');

            $reservas = Reserva::with('ubicacion', 'nit')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                )
                ->orderBy('id', 'DESC');

            if ($request->get('fecha_desde')) $reservas->where('fecha_inicio', '>=', $request->get('fecha_desde'));
            if ($request->get('fecha_hasta')) $reservas->where('fecha_fin', '<=', $request->get('fecha_hasta').' 23:59:59');
            if ($request->get('id_nit')) $reservas->where('id_nit', $request->get('id_nit'));
            if ($request->get('id_ubicacion')) $reservas->where('id_ubicacion', $request->get('id_ubicacion'));
            // if ($request->get('tipo') || $request->get('tipo') == '0') $reservas->where('tipo', $request->get('tipo'));
            // if ($request->get('estado') || $request->get('estado') == '0') $reservas->where('estado', $request->get('estado'));

            $reservasTotals = $reservas->get();

            $reservasPaginate = $reservas->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $reservasTotals->count(),
                'iTotalDisplayRecords' => $reservasTotals->count(),
                'data' => $reservasPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Reserva generadas con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function read (Request $request)
    {
        $start =  Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $data = array();
        $estado = $request->estado == 'null' ? null : $request->estado;
        $id_nit = $request->id_nit == 'null' ? null : $request->id_nit;
        $id_ubicacion = $request->id_ubicacion == 'null' ? null : $request->id_ubicacion;

        $reserva = Reserva::with('ubicacion', 'nit')
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('fecha_inicio', [$start, $end])
                    ->orWhereBetween('fecha_fin', [$start, $end])
                    ->orWhere(function($query) use ($start, $end) {
                        $query->where('fecha_inicio', '<=', $start)
                            ->where('fecha_fin', '>=', $end);
                    });
        })
        ->when($estado, function ($query) use($estado) {
            $query->where('estado', $estado);
        })
        ->when($id_nit, function ($query) use($id_nit) {
            $query->where('id_nit', $id_nit);
        })
        ->when($id_ubicacion, function ($query) use($id_ubicacion) {
            $query->where('id_ubicacion', $id_ubicacion);
        });

        $dataReserva = $reserva->get();

        foreach ($dataReserva as $reserva) {
            $fechaInicio = Carbon::parse($reserva->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($reserva->fecha_fin)->format('Y-m-d');

            $horaInicio = Carbon::parse($reserva->fecha_inicio)->format('H:i:s');
            $horaFin = Carbon::parse($reserva->fecha_fin)->format('H:i:s');

            $color = "#055ebe";

            array_push($data, array(
                'backgroundColor' => $color,
                'borderColor' => $color,
                'id' => $reserva->id,
                'ubicacion' => $reserva->ubicacion,
                'nit' => $reserva->nit,
                'title' => $reserva->ubicacion?->codigo .' - '. $reserva->ubicacion?->nombre,
                'start' => $horaInicio == "00:00:00" ? $fechaInicio : $fechaInicio.' '.$horaInicio,
                'end' => $horaFin == "00:00:00" ? $fechaFin : $fechaFin.' '.$horaFin,
            ));
        }

        return response()->json($data);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_nit' => 'required|exists:sam.nits,id',
            'id_ubicacion' => 'required|exists:sam.ubicacions,id',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'observacion' => 'nullable',
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

            $inicio = Carbon::parse($request->get('fecha_inicio'));
            $fin = Carbon::parse($request->get('fecha_fin'));

            $fechaInicio = $inicio->format('Y-m-d').' '.$request->get('hora_inicio');
            $fechaFin = $fin->format('Y-m-d').' '.$request->get('hora_fin');

            $reserva = Reserva::create([
                'id_nit' => $request->get('id_nit'),
                'id_ubicacion' => $request->get('id_ubicacion'),
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'observacion' => $request->get('observacion'),
                'estado' => 1,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $reserva,
                'message'=> 'Reserva creada con exito!'
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
            'id' => 'required|exists:sam.reservas,id',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'observacion' => 'nullable',
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

            $fechaInicio = $request->get("fecha_inicio").' '.$request->get("hora_inicio");
            $fechaFin = $request->get("fecha_fin").' '.$request->get("hora_fin");

            Reserva::where('id', $request->get('id'))
                ->update([
                    'id_nit' => $request->get('id_nit'),
                    'id_ubicacion' => $request->get('id_ubicacion'),
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'observacion' => $request->get('observacion'),
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Reserva actualizada con exito!'
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
            'id' => 'required|exists:sam.reservas,id',
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

            Reserva::where('id', $request->get('id'))->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> 'Reserva eliminada con exito!'
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
