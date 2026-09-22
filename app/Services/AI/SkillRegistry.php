<?php

namespace App\Services\AI;

use App\Services\AI\Skills\Skill;
//BUSCAR
use App\Services\AI\Skills\BuscarBodegaSkill;
use App\Services\AI\Skills\BuscarClienteSkill;
use App\Services\AI\Skills\BuscarProductoSkill;
use App\Services\AI\Skills\BuscarFormaPagoSkill;
use App\Services\AI\Skills\BuscarResolucionSkill;
//CREAR
use App\Services\AI\Skills\CrearVentaSkill;


class SkillRegistry
{
    /** @var array<string, Skill> */
    protected array $skills = [];

    public function __construct()
    {
        //BUSCAR
        $this->register(new BuscarBodegaSkill());
        $this->register(new BuscarClienteSkill());
        $this->register(new BuscarProductoSkill());
        $this->register(new BuscarFormaPagoSkill());
        $this->register(new BuscarResolucionSkill());
        //CREAR
        $this->register(new CrearVentaSkill());
    }

    public function register(Skill $skill): self
    {
        $this->skills[$skill->name()] = $skill;
        return $this;
    }

    public function definitions(): array
    {
        return array_map(fn (Skill $s) => $s->definition(), array_values($this->skills));
    }

    public function has(string $name): bool
    {
        return isset($this->skills[$name]);
    }

    public function run(string $name, array $args, array &$state): array
    {
        if (!$this->has($name)) {
            return ['success' => false, 'message' => "Skill {$name} no registrada."];
        }

        return $this->skills[$name]->run($args, $state);
    }
}