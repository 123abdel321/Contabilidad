<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfResumenCartera extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_resumen_carteras";

    protected $fillable = [
        'id_empresa',
        'fecha_hasta',
        'dias_mora',
        'cuentas',
        'exporte',
        'url_excel',
        'estado'
    ];
}
