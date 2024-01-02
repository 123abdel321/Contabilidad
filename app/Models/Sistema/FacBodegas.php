<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacBodegas extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_bodegas";

    protected $fillable = [ 
        'codigo',
        'nombre',
        'ubicacion',
        'id_centro_costos',
        'id_cuenta_cartera',
        'id_responsable',
        'created_by',
        'updated_by',
    ];

    public function cecos()
    {
        return $this->belongsTo(CentroCostos::class, 'id_centro_costos');
    }

    public function cuenta_cartera()
    {
        return $this->belongsTo(PlanCuentas::class, 'id_cuenta_cartera');
    }
}
