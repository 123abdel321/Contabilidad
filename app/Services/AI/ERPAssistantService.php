<?php

namespace App\Services\AI;

use App\Services\AI\Flujos\Flujo;
use App\Services\AI\Flujos\FlujoRegistry;
use App\Services\OpenAI\OpenAIService;

class ERPAssistantService
{
    public function __construct(
        protected OpenAIService $openAI,
        protected SkillRegistry $skills,
        protected FlujoRegistry $flujos,
    ) {}

    public function chat(string $mensaje, Estado $state): array
    {
        // ---------------------------------------------------------
        // 1) Detectar / activar flujo
        // ---------------------------------------------------------
        $flujo = $this->resolverFlujo($state);

        if (!$flujo) {
            return [
                'respuesta' => 'No pude identificar qué operación quieres hacer. Intenta de nuevo.',
                'skills'    => [],
                'draft'     => $state->borrador(),
            ];
        }

        // ---------------------------------------------------------
        // 2) Preparar historial
        // ---------------------------------------------------------
        $input = $this->limpiarHistorial($state->historial());
        $input[] = ['role' => 'user', 'content' => $mensaje];

        // ---------------------------------------------------------
        // 3) Preparar prompt e tools del flujo
        // ---------------------------------------------------------
        $instructions = $this->renderPrompt($flujo, $state);
        $tools        = $this->skills->definitionsParaFlujo($flujo);

        $ejecutadas = [];
        $texto      = null;
        $maxVueltas = 5;

        // ---------------------------------------------------------
        // 4) Loop de tool calling
        // ---------------------------------------------------------
        for ($vuelta = 0; $vuelta < $maxVueltas; $vuelta++) {

            $response = $this->openAI->chat([
                'model'        => 'gpt-4o',
                'instructions' => $instructions,
                'input'        => $input,
                'tools'        => $tools,
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

            // Si no pidió tools, ya tenemos respuesta final
            if (!$pidioTools) {
                $texto = $response->outputText;
                break;
            }

            // Si pidió tools, refrescamos el prompt con el borrador actualizado
            $instructions = $this->renderPrompt($flujo, $state);
        }

        if (empty($texto)) {
            $texto = 'No pude generar una respuesta. Intenta de nuevo.';
        }

        // ---------------------------------------------------------
        // 5) Guardar historial en el Estado
        // ---------------------------------------------------------
        if (!empty($texto)) {
            $input[] = ['role' => 'assistant', 'content' => $texto];
        }

        $state->setHistorial($input);

        return [
            'respuesta' => $texto,
            'skills'    => $ejecutadas,
            'draft'     => $state->borrador(),
        ];
    }

    // =========================================================
    // Helpers
    // =========================================================

    /**
     * Devuelve el flujo activo. Si no hay, activa el por defecto (venta).
     */
    protected function resolverFlujo(Estado $state): ?Flujo
    {
        $nombre = $state->flujo();

        if ($nombre) {
            return $this->flujos->get($nombre);
        }

        // Por ahora forzamos "venta" por defecto.
        // Mañana puedes hacer un router de intención aquí.
        $flujo = $this->flujos->get('venta');

        if ($flujo) {
            $state->iniciarFlujo($flujo);
        }

        return $flujo;
    }

    /**
     * Rellena el system prompt del flujo con borrador y faltantes.
     */
    protected function renderPrompt(Flujo $flujo, Estado $state): string
    {
        $borrador  = json_encode($state->borrador(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $faltantes = $state->faltantes($flujo);

        $faltantesTexto = empty($faltantes)
            ? 'NINGUNO — el borrador está completo. Puedes proceder si el usuario confirma.'
            : '- ' . implode("\n- ", $faltantes);

        // Calcular totales actuales del borrador
        $productos = $state->borrador('productos', []);
        $subtotal = 0.0;
        $iva      = 0.0;
        $total    = 0.0;

        foreach ($productos as $p) {
            $subtotal += (float) ($p['subtotal']  ?? 0);
            $iva      += (float) ($p['iva_valor'] ?? 0);
            $total    += (float) ($p['total']     ?? 0);
        }

        $totalesTexto = sprintf(
            "Subtotal: %s\nIVA: %s\nTOTAL: %s",
            number_format($subtotal, 2, '.', ''),
            number_format($iva, 2, '.', ''),
            number_format($total, 2, '.', '')
        );

        return str_replace(
            ['{borrador}', '{faltantes}', '{totales}'],
            [$borrador, $faltantesTexto, $totalesTexto],
            $flujo->systemPrompt()
        );
    }

    /**
     * Limpia items con content null del historial (bug de OpenAI).
     */
    protected function limpiarHistorial(array $historial): array
    {
        return array_values(array_filter($historial, function ($item) {
            if (array_key_exists('content', $item) && $item['content'] === null) {
                return false;
            }
            return true;
        }));
    }
}