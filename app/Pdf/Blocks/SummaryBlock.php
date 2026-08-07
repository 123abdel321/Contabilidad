<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class SummaryBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.summary', $this->data)->render();
    }
}