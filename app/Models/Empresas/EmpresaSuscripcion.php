<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpresaSuscripcion extends Model
{
    use HasFactory;

    protected $connection = 'clientes';

    protected $table = 'empresa_suscripcions';

    protected $fillable = [
        'id_empresa',
        'id_suscripcion',
        'id_forma_pago',
        'dias_para_pagar',
        'dias_de_gracia',
        'fecha_inicio_suscripcion',
        'fecha_inicio_facturacion',
        'fecha_siguiente_pago',
        'estado',
        'duracion',
        'precio',
        'descuento'
    ];
}
