<?php

namespace App\Services\AI\Flujos;

class FlujoRegistry
{
    /** @var array<string, Flujo> */
    protected array $flujos = [];

    public function __construct()
    {
        $this->register(new FlujoVenta());
    }

    public function register(Flujo $flujo): self
    {
        $this->flujos[$flujo->nombre()] = $flujo;
        return $this;
    }

    public function get(string $nombre): ?Flujo
    {
        return $this->flujos[$nombre] ?? null;
    }

    public function todos(): array
    {
        return $this->flujos;
    }
}