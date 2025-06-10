<?php

namespace App\Models\Sistema\Nomina;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//MODELS
use App\Models\Sistema\PlanCuentas;

class NomConfiguracionProvisiones extends Model
{
    use HasFactory;
    
    protected $connection = 'sam';

    protected $table = "nom_configuracion_provisiones";

    protected $fillable = [
        'tipo',
        'nombre',
        'porcentaje',
        'id_cuenta_administrativos',
        'id_cuenta_operativos',
        'id_cuenta_ventas',
        'id_cuenta_otros',
        'id_cuenta_por_pagar',
    ];

    public function cuenta_administrativos()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_administrativos");
	}

	public function cuenta_operativos()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_operativos");
	}

	public function cuenta_ventas()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_ventas");
	}

	public function cuenta_otros()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_otros");
    }

    public function cuenta_pagar()
    {
        return $this->belongsTo(PlanCuentas::class, "id_cuenta_por_pagar");
    }
}
