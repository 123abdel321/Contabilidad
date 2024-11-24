<?php

namespace App\Models\Informes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InfBalance extends Model
{
    use HasFactory;

    protected $connection = 'informes';

    protected $table = "inf_balances";

    protected $fillable = [
        'id',
        'id_empresa',
        'id_cuenta',
        'id_nit',
        'fecha_desde',
        'fecha_hasta',
        'cuenta_desde',
        'cuenta_hasta',
        'exporta_excel',
        'archivo_excel',
        'tipo',
        'nivel',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    public function detalle(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Informes\InfBalanceDetalle', 'id_balance');
    }

}
