<?php

namespace App\Http\Controllers;

use App\Services\AI\ERPAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AIChatController extends Controller
{
    public function chat(Request $request, ERPAssistantService $assistant)
    {
        $mensaje = $request->get('mensaje', '');
        $conversationId = $request->get('conversation_id', 'default');
        
        $cacheKey = "ia_draft:{$conversationId}";
        $state = Cache::get($cacheKey, []);

        $result = $assistant->chat($mensaje, $state);

        Cache::put($cacheKey, $state, now()->addHours(2));

        return response()->json([
            'respuesta' => $result['respuesta'],
            'skills' => $result['skills'],
            'draft' => [
                'id_cliente' => $state['id_cliente'] ?? null,
                'cliente' => $state['cliente'] ?? null,
            ],
        ]);
    }

    public function reset(Request $request)
    {
        $conversationId = $request->get('conversation_id', 'default');
        Cache::forget("ia_draft:{$conversationId}");

        return response()->json(['ok' => true]);
    }
}