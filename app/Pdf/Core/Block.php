<?php

namespace App\Pdf\Core;

abstract class Block
{
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    abstract public function render(): string;

    public function toArray(): array
    {
        return $this->data;
    }
}