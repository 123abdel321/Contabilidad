<?php

namespace App\Pdf\Core;

class Document
{
    protected array $blocks = [];
    protected array $config = [];

    public function addBlock(Block $block): self
    {
        $this->blocks[] = $block;
        return $this;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function toArray(): array
    {
        return [
            'config' => $this->config,
            'blocks' => array_map(fn($block) => $block->toArray(), $this->blocks),
        ];
    }
}