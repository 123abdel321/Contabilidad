<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'empresas';

    protected $fillable = [
        'id_nit',
        'estado',
        'servidor',
        'token_db',
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'otros_nombres',
        'tipo_contribuyente',
        'razon_social',
        'nit',
        'dv',
        'codigos_responsabilidades',
        'notas_negociacion',
        'logo',
        'fecha_retiro',
        'direccion',
        'telefono',
        'hash',
        'id_empresa_referido',
        'id_usuario_owner'
	];
}
