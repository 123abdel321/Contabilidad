<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariablesEntorno extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "variables_entornos";

    protected $fillable = [
        'nombre',
        'valor',
        'created_by',
        'updated_by'
    ];
}
