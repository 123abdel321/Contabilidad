<?php

namespace App\Pdf\Core;

class Column
{
    public string $key;
    public string $label;
    public string $align = 'left';
    public ?string $width = null;
    public ?string $formatter = null;
    public array $extra = [];

    public function __construct(string $key, string $label)
    {
        $this->key = $key;
        $this->label = $label;
    }

    public static function make(string $key, string $label): self
    {
        return new self($key, $label);
    }

    public function align(string $align): self
    {
        $this->align = $align;
        return $this;
    }

    public function width(string $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function format(string $formatter): self
    {
        $this->formatter = $formatter;
        return $this;
    }

    public function extra(array $extra): self
    {
        $this->extra = $extra;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'align' => $this->align,
            'width' => $this->width,
            'formatter' => $this->formatter,
            'extra' => $this->extra,
        ];
    }
}