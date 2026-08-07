<?php

namespace App\Pdf\Core;

class Table
{
    protected string $title = '';
    protected array $columns = [];
    protected array $rows = [];

    public static function make(): self
    {
        return new self();
    }

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function rows(array $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'columns' => array_map(fn($col) => $col->toArray(), $this->columns),
            'rows' => $this->rows,
        ];
    }
}