<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConConceptoGastos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_concepto_gastos";

    protected $fillable = [
        'nombre',
        'codigo',
        'id_cuenta_gasto',
        'id_cuenta_iva',
        'id_cuenta_retencion_declarante',
        'created_by',
        'updated_by',
    ];

    public function cuenta_gasto()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_gasto");
    }

    public function cuenta_iva()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_iva");
    }

    public function cuenta_retencion()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_retencion");
    }

    public function cuenta_retencion_declarante()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_retencion_declarante");
    }
}