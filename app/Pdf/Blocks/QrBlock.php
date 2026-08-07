<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class QrBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.qr', $this->data)->render();
    }
}