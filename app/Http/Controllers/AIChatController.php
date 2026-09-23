<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
//SERVICES
use App\Services\AI\Estado;
use App\Services\AI\ERPAssistantService;
//MODELS
use App\Models\Empresas\AiConversacion;

class AIChatController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje'    => 'required|string',
            'session_id' => 'nullable|uuid',
        ]);

        $idUser    = $request->user()->id;
        $idEmpresa = $request->user()->id_empresa;

        // ---------------------------------------------------------
        // 1. Buscar o crear la conversación
        // ---------------------------------------------------------
        $sessionId = $request->input('session_id');

        if ($sessionId) {
            $conversacion = AiConversacion::where('session_id', $sessionId)
                ->where('id_user', $idUser)
                ->where('id_empresa', $idEmpresa)
                ->first();
        } else {
            $conversacion = null;
        }

        if (!$conversacion) {
            $sessionId    = (string) Str::uuid();
            $conversacion = AiConversacion::create([
                'session_id' => $sessionId,
                'id_user'    => $idUser,
                'id_empresa' => $idEmpresa,
                'contexto'   => [
                    'id_user'    => $idUser,
                    'id_empresa' => $idEmpresa,
                ],
                'borrador'   => [],
                'candidatos' => [],
                'historial'  => [],
            ]);
        }

        // ---------------------------------------------------------
        // 2. Si la conversación ya está cerrada, arranca una nueva
        // ---------------------------------------------------------
        if ($conversacion->estaCerrada()) {
            $sessionId    = (string) Str::uuid();
            $conversacion = AiConversacion::create([
                'session_id' => $sessionId,
                'id_user'    => $idUser,
                'id_empresa' => $idEmpresa,
                'contexto'   => [
                    'id_user'    => $idUser,
                    'id_empresa' => $idEmpresa,
                ],
                'borrador'   => [],
                'candidatos' => [],
                'historial'  => [],
            ]);
        }

        // ---------------------------------------------------------
        // 3. Construir Estado desde la conversación
        // ---------------------------------------------------------
        $state = new Estado($conversacion->toEstadoArray());

        // Garantizar contexto (por si acaso)
        if (!$state->idUser())    $state->setContexto('id_user', $idUser);
        if (!$state->idEmpresa()) $state->setContexto('id_empresa', $idEmpresa);

        // ---------------------------------------------------------
        // 4. Llamar al asistente
        // ---------------------------------------------------------
        $resultado = app(ERPAssistantService::class)->chat(
            $request->input('mensaje'),
            $state
        );

        // ---------------------------------------------------------
        // 5. Persistir
        // ---------------------------------------------------------
        $conversacion->update([
            'flujo'      => $state->flujo(),
            'contexto'   => $state->toArray()['contexto'],
            'borrador'   => $state->borrador(),
            'candidatos' => $state->toArray()['candidatos'],
            'historial'  => $state->historial(),
            'resultado'  => $state->resultado(),
            'cerrada'    => $state->resultado() !== null,
        ]);

        // ---------------------------------------------------------
        // 6. Respuesta
        // ---------------------------------------------------------
        return response()->json([
            'session_id' => $sessionId,
            'respuesta'  => $resultado['respuesta'],
            'skills'     => $resultado['skills'],
            'draft'      => $resultado['draft'],
            'cerrada'    => $conversacion->estaCerrada(),
        ]);
    }

    /**
     * Reinicia la conversación actual (el front debe mandar session_id).
     */
    public function reset(Request $request)
    {
        $request->validate([
            'session_id' => 'nullable|uuid',
        ]);

        $sessionId = $request->input('session_id');

        if ($sessionId) {
            AiConversacion::where('session_id', $sessionId)
                ->where('id_user', $request->user()->id)
                ->update(['cerrada' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function conversaciones(Request $request)
    {
        $conversaciones = AiConversacion::where('id_user', $request->user()->id)
            ->where('id_empresa', $request->user()->id_empresa)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get(['session_id', 'flujo', 'borrador', 'resultado', 'cerrada', 'updated_at']);

        return response()->json([
            'success' => true,
            'data' => $conversaciones
        ]);
    }

    public function mensajes(Request $request, $sessionId)
    {
        $conversacion = AiConversacion::where('session_id', $sessionId)
            ->where('id_user', $request->user()->id)
            ->firstOrFail();

        // Devolvemos el historial formateado para mostrarlo en el chat
        return response()->json([
            'success' => true,
            'data' => [
                'historial' => $conversacion->historial ?? [],
                'borrador' => $conversacion->borrador ?? [],
                'resultado' => $conversacion->resultado,
            ]
        ]);
    }
}