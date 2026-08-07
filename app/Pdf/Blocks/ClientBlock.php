<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class ClientBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.client', $this->data)->render();
    }
}