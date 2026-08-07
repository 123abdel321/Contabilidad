<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class CompanyBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.company', $this->data)->render();
    }
}