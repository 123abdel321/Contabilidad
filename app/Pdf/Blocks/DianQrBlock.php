<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class DianQrBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.dian_qr', $this->data)->render();
    }
}