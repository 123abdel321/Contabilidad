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
        'id_exogena_formato',
        'id_exogena_formato_concepto',
        'id_exogena_formato_columna',
        'cuenta',
        'nombre',
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
        return $this->belongsTo(Impuestos::class, "id_impuesto");
    }

    public function padre()
    {
        return $this->belongsTo(PlanCuentas::class, "id_padre");
    }

    public function forma_pago()
    {
        return $this->hasOne(FacFormasPago::class, "id_cuenta", "id");
	}

	public function tipos_cuenta()
    {
        return $this->hasMany(PlanCuentasTipo::class, "id_cuenta");
    }

    public function exogena_formato()
    {
        return $this->belongsTo(ExogenaFormato::class, 'id_exogena_formato');
    }

    public function exogena_concepto()
    {
        return $this->belongsTo(ExogenaFormatoConcepto::class, 'id_exogena_formato_concepto');
    }

    public function exogena_columna()
    {
        return $this->belongsTo(ExogenaFormatoColumna::class, 'id_exogena_formato_columna');
    }
}
