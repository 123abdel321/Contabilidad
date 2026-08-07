<?php

namespace App\Pdf\Blocks;

use App\Pdf\Core\Block;

class NotesBlock extends Block
{
    public function render(): string
    {
        return view('pdf.blocks.notes', $this->data)->render();
    }
}