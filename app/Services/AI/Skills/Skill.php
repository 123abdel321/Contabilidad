<?php

namespace App\Services\AI\Skills;

use App\Services\AI\Estado;

abstract class Skill
{
    abstract public function name(): string;
    abstract public function description(): string;
    abstract public function parameters(): array;
    abstract public function run(array $args, Estado $state): array;

    /**
     * Definición lista para OpenAI (Responses API).
     */
    public function definition(): array
    {
        $properties = [];
        $required   = [];

        foreach ($this->parameters() as $nombre => $config) {
            $properties[$nombre] = [
                'type'        => $config['type']        ?? 'string',
                'description' => $config['description'] ?? '',
            ];

            if (!empty($config['required'])) {
                $required[] = $nombre;
            }
        }

        return [
            'type'        => 'function',
            'name'        => $this->name(),
            'description' => $this->description(),
            'parameters'  => [
                'type'       => 'object',
                'properties' => $properties,
                'required'   => $required,
            ],
        ];
    }
}