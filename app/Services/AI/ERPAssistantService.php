<?php

namespace App\Services\AI;

use App\Services\AI\SkillRegistry;
use App\Services\OpenAI\OpenAIService;

class ERPAssistantService
{
    public function __construct(
        protected OpenAIService $openAI,
        protected SkillRegistry $skills,
    ) {}

    public function chat(string $mensaje, array &$state): array
    {
        // Recuperamos el historial guardado (o iniciamos vacío)
        $input = $state['historial'] ?? [];

        // Limpiamos items con content null que puedan venir de antes
        $input = array_values(array_filter($input, function ($item) {
            if (array_key_exists('content', $item) && $item['content'] === null) {
                return false;
            }
            return true;
        }));

        // Agregamos el mensaje del usuario
        $input[] = ['role' => 'user', 'content' => $mensaje];

        $ejecutadas = [];
        $texto      = null;
        $maxVueltas = 5; // Evita loops infinitos

        for ($vuelta = 0; $vuelta < $maxVueltas; $vuelta++) {

            $response = $this->openAI->chat([
                'model' => 'gpt-4o',
                'input' => $input,
                'tools' => $this->skills->definitions(),
            ]);

            $pidioTools = false;

            foreach ($response->output as $item) {
                if ($item->type !== 'function_call') continue;

                $pidioTools = true;

                $args      = json_decode($item->arguments, true) ?: [];
                $resultado = $this->skills->run($item->name, $args, $state);

                $callId = $item->call_id ?? $item->id ?? null;

                $ejecutadas[] = [
                    'skill'     => $item->name,
                    'args'      => $args,
                    'resultado' => $resultado,
                ];

                $input[] = [
                    'type'      => 'function_call',
                    'name'      => $item->name,
                    'arguments' => $item->arguments,
                    'call_id'   => $callId,
                ];

                $input[] = [
                    'type'    => 'function_call_output',
                    'call_id' => $callId,
                    'output'  => json_encode($resultado),
                ];
            }

            // Si el modelo ya no pidió tools, tenemos la respuesta final
            if (!$pidioTools) {
                $texto = $response->outputText;
                break;
            }
        }

        // Fallback por si el modelo nunca dio texto
        if (empty($texto)) {
            $texto = 'No pude generar una respuesta final. Intenta de nuevo.';
        }

        // Guardamos el assistant SOLO con texto válido
        $input[] = ['role' => 'assistant', 'content' => $texto];

        $state['historial'] = $input;

        return [
            'respuesta' => $texto,
            'skills'    => $ejecutadas,
        ];
    }
}