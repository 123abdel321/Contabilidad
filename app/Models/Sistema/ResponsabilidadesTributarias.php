<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConResponsabilidadesTributarias extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "responsabilidades_tributarias";

    protected $fillable = [
        'codigo',
        'nombre'
    ];
    
}
