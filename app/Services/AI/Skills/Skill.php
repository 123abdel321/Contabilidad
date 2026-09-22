<?php

namespace App\Services\AI\Skills;

abstract class Skill
{
    abstract public function name(): string;
    abstract public function description(): string;
    abstract public function parameters(): array;
    abstract public function run(array $args, array &$state): array;

    public function definition(): array
    {
        $required = [];
        foreach ($this->parameters() as $key => $param) {
            if (!empty($param['required'])) {
                $required[] = $key;
            }
        }

        $properties = [];
        foreach ($this->parameters() as $key => $param) {
            unset($param['required']);
            $properties[$key] = $param;
        }

        return [
            'type' => 'function',
            'name' => $this->name(),
            'description' => $this->description(),
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => array_values($required),
            ],
        ];
    }
}