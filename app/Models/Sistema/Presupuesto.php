<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presupuesto extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "presupuestos";

    protected $fillable = [
        'id',
        'periodo',
        'tipo',
        'presupuesto',
    ];

    public function detalle()
    {
        return $this->hasMany(PresupuestoDetalle::class, "id_presupuesto");
    }
}
