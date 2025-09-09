<?php

namespace App\Http\Controllers\Configuracion;

use DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODEL
use App\Models\Sistema\Reunion;
use App\Models\Sistema\ReunionParticipante;

class ReunionesController extends Controller
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

    public function index()
    {
        return view('pages.configuracion.reuniones.reuniones-view');
    }

    public function read(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        
        $reuniones = Reunion::with('participantes')
            ->whereBetween('fecha_inicio', [$start, $end])
            ->orWhereBetween('fecha_fin', [$start, $end])
            ->orWhere(function($query) use ($start, $end) {
                $query->where('fecha_inicio', '<=', $start)
                    ->where('fecha_fin', '>=', $end);
            });

        $data = [];
        foreach ($reuniones->get() as $reunion) {
            $color = "#17a2b8"; // Color azul para reuniones
            
            if ($reunion->estado == 2) $color = "#28a745"; // Finalizada - verde
            if ($reunion->estado == 3) $color = "#dc3545"; // Cancelada - rojo
            
            array_push($data, [
                'id' => $reunion->id,
                'title' => $reunion->titulo,
                'start' => $reunion->fecha_inicio->format('Y-m-d H:i:s'),
                'end' => $reunion->fecha_fin->format('Y-m-d H:i:s'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'lugar' => $reunion->lugar,
                    'participantes' => $reunion->participantes->count(),
                    'estado' => $reunion->estado
                ]
            ]);
        }

        return response()->json($data);
    }

    public function find (Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.reunions,id'
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

            $reunion = Reunion::where('id', $request->get('id'))
                ->with('participantes')
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $reunion,
                'message'=> 'Reunion encontrada con exito!'
            ]);

        } catch (Exception $e) {

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function table(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $reuniones = Reunion::with('participantes')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(fecha_inicio, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(fecha_inicio, '%Y-%m-%d %T') AS fecha_hora_inicio"),
                    DB::raw("DATE_FORMAT(fecha_fin, '%Y-%m-%d %T') AS fecha_hora_fin")
                )
                ->orderBy('fecha_inicio', 'DESC');

            if ($request->get('fecha_desde')) {
                $reuniones->where('fecha_inicio', '>=', $request->get('fecha_desde'));
            }
            
            if ($request->get('fecha_hasta')) {
                $reuniones->where('fecha_fin', '<=', $request->get('fecha_hasta') . ' 23:59:59');
            }
            
            if ($request->get('estado')) {
                $reuniones->where('estado', $request->get('estado'));
            }

            $reunionesTotals = $reuniones->get();
            $reunionesPaginate = $reuniones->skip($start)->take($rowperpage);

            return response()->json([
                'success' => true,
                'draw' => $draw,
                'iTotalRecords' => $reunionesTotals->count(),
                'iTotalDisplayRecords' => $reunionesTotals->count(),
                'data' => $reunionesPaginate->get(),
                'perPage' => $rowperpage,
                'message' => 'Reuniones generadas con exito!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function participantes(Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $reuniones = ReunionParticipante::with('nit')
                ->where('id_reunion', $request->get("id_reunion"))
                ->orderBy('id', 'DESC');

            // if ($request->get('fecha_desde')) {
            //     $reuniones->where('fecha_inicio', '>=', $request->get('fecha_desde'));
            // }
            
            // if ($request->get('fecha_hasta')) {
            //     $reuniones->where('fecha_fin', '<=', $request->get('fecha_hasta') . ' 23:59:59');
            // }
            
            // if ($request->get('estado')) {
            //     $reuniones->where('estado', $request->get('estado'));
            // }

            $reunionesTotals = $reuniones->get();
            $reunionesPaginate = $reuniones->skip($start)->take($rowperpage);

            return response()->json([
                'success' => true,
                'draw' => $draw,
                'iTotalRecords' => $reunionesTotals->count(),
                'iTotalDisplayRecords' => $reunionesTotals->count(),
                'data' => $reunionesPaginate->get(),
                'perPage' => $rowperpage,
                'message' => 'Participantes generados con exito!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function createParticipantes(Request $request)
    {
        $rules = [
			'id_nit' => 'required|exists:sam.nits,id',
            'id_reunion' => 'required|exists:sam.reunions,id'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        DB::connection('sam')->beginTransaction();

        try {

            $existe = ReunionParticipante::where('id_reunion', $request->get('id_reunion'))
                ->where('id_usuario', $request->get('id_nit'))
                ->count();

            if ($existe) {
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'El participante ya se encuentra agregado en la reunión'
                ]);
            }

            ReunionParticipante::create([
                'id_reunion' => $request->get('id_reunion'),
                'id_usuario' => $request->get('id_nit'),
                'asistio' => false
            ]);

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Participante agregado con exito!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function deleteParticipantes(Request $request)
    {
        $rules = [
			'id_nit' => 'required|exists:sam.nits,id',
            'id_reunion' => 'required|exists:sam.reunions,id'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        DB::connection('sam')->beginTransaction();
        
        try {

            ReunionParticipante::where('id_reunion', $request->get('id_reunion'))
                ->where('id_usuario', $request->get('id_nit'))
                ->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Participante eliminado con exito!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function create(Request $request)
    {
        $rules = [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'lugar' => 'nullable|string|max:255'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $reunion = Reunion::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'lugar' => $request->lugar,
                'id_proyecto' => $request->id_proyecto,
                'estado' => 0, // Programada
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id
            ]);

            // // Agregar participantes
            // foreach ($request->participantes as $participanteId) {
            //     ReunionParticipante::create([
            //         'id_reunion' => $reunion->id,
            //         'id_usuario' => $participanteId,
            //         'asistio' => false
            //     ]);
            // }

            // // Agregar al creador como participante si no está en la lista
            // if (!in_array($request->user()->id, $request->participantes)) {
            //     ReunionParticipante::create([
            //         'id_reunion' => $reunion->id,
            //         'id_usuario' => $request->user()->id,
            //         'asistio' => false
            //     ]);
            // }

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data' => $reunion,
                'message' => 'Reunión creada con éxito!'
            ]);
        } catch (\Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|exists:sam.reunions,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'lugar' => 'nullable|string|max:255',
            'estado' => 'nullable|integer|min:0|max:3'
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('sam')->beginTransaction();

            $reunion = Reunion::findOrFail($request->id);
            $reunion->update([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'lugar' => $request->lugar,
                'id_proyecto' => $request->id_proyecto,
                'estado' => $request->estado ?? $reunion->estado,
                'updated_by' => $request->user()->id
            ]);

            // Sincronizar participantes
            // $participantesActuales = $reunion->participantes()->pluck('id_usuario')->toArray();
            // $nuevosParticipantes = $request->participantes;
            
            // // Eliminar participantes que ya no están
            // $participantesAEliminar = array_diff($participantesActuales, $nuevosParticipantes);
            // if (!empty($participantesAEliminar)) {
            //     ReunionParticipante::where('id_reunion', $reunion->id)
            //         ->whereIn('id_usuario', $participantesAEliminar)
            //         ->delete();
            // }
            
            // // Agregar nuevos participantes
            // $participantesAAgregar = array_diff($nuevosParticipantes, $participantesActuales);
            // foreach ($participantesAAgregar as $participanteId) {
            //     ReunionParticipante::create([
            //         'id_reunion' => $reunion->id,
            //         'id_usuario' => $participanteId,
            //         'asistio' => false
            //     ]);
            // }

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'data' => $reunion,
                'message' => 'Reunión actualizada con éxito!'
            ]);
        } catch (\Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function delete(Request $request)
    {
        try {

            DB::connection('sam')->beginTransaction();

            $reunion = Reunion::findOrFail($request->get('id'));
            
            // Eliminar participantes
            ReunionParticipante::where('id_reunion', $request->get('id'))->delete();
            
            $reunion->delete();

            DB::connection('sam')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Reunión eliminada con éxito!'
            ]);
        } catch (\Exception $e) {
            DB::connection('sam')->rollback();
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function getParticipantes($idReunion)
    {
        try {
            $participantes = ReunionParticipante::with('usuario')
                ->where('id_reunion', $idReunion)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $participantes,
                'message' => 'Participantes obtenidos con éxito!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function updateAsistencia(Request $request)
    {
        $rules = [
            'id_reunion' => 'required|exists:sam.reunions,id',
            'id_usuario' => 'required|exists:sam.nits,id',
            'asistio' => 'required|boolean',
            'comentarios' => 'nullable|string'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        try {
            $participante = ReunionParticipante::where('id_reunion', $request->id_reunion)
                ->where('id_usuario', $request->id_usuario)
                ->firstOrFail();

            $participante->update([
                'asistio' => $request->asistio,
                'comentarios' => $request->comentarios
            ]);

            return response()->json([
                'success' => true,
                'data' => $participante,
                'message' => 'Asistencia actualizada con éxito!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }


}