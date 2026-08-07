<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class HeaderBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.header', $this->data)->render();
    }
}