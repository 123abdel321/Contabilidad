<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamentos extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'departamentos';

    protected $fillable = [
        'id_pais',
        'codigo',
        'indicativo',
        'nombre',
        'nombre_completo',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
	];
}
