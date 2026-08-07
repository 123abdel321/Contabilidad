<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class TableBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.table', $this->data)->render();
    }
}