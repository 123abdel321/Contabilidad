<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCuentasTipo extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "plan_cuentas_tipos";

    protected $fillable = [
        'id_cuenta',
        'id_tipo_cuenta'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoCuenta::class, "id_tipo_cuenta");
    }
}
