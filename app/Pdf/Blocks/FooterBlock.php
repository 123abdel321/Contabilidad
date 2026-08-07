<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class FooterBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.footer', $this->data)->render();
    }
}