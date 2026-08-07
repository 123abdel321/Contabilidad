<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class PaymentsBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.payments', $this->data)->render();
    }
}