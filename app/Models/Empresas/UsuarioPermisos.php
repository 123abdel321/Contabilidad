<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioPermisos extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = "usuario_permisos";

    protected $fillable = [
        'id',
        'id_user',
        'id_empresa',
        'ids_permission',
        'ids_bodegas_responsable',
        'ids_resolucion_responsable',
    ];
}
