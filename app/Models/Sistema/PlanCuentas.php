<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanCuentas extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "plan_cuentas";

    public const DEBITO = 0;
	public const CREDITO = 1;

    protected $fillable = [
        'id_padre',
        'id_tipo_cuenta',
        'id_impuesto',
        'cuenta',
        'nombre',
        'pasarela', // Check si es una cuenta usada para sacar el extracto de pasarela
        'auxiliar',
        'exige_nit',
        'exige_documento_referencia',
        'exige_concepto',
        'exige_centro_costos',
		'naturaleza_cuenta',
		'naturaleza_ingresos',
		'naturaleza_egresos',
		'naturaleza_compras',
		'naturaleza_ventas',
		'cuenta_corriente',
        'created_by',
        'updated_by'
    ];

    public function impuesto()
    {
        return $this->belongsTo("App\Models\Sistema\Impuestos", "id_impuesto");
    }

    public function padre()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_padre");
    }

	public function tipos_cuenta()
    {
        return $this->hasMany("App\Models\Sistema\PlanCuentasTipo", "id_cuenta");
    }
}
