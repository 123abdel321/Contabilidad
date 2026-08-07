<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class InfoBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.info', $this->data)->render();
    }
}