<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacCargueDescargue extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_cargue_descargues";

    protected $fillable = [
        'id_comprobante',
        'id_nit',
        'id_cuenta_debito',
        'id_cuenta_credito',
        'nombre',
        'tipo',
        'created_by',
        'updated_by',
    ];

    public function nit()
    {
        return $this->belongsTo("App\Models\Sistema\Nits", "id_nit");
    }

    public function comprobante()
	{
		return $this->belongsTo('App\Models\Sistema\Comprobantes', 'id_comprobante');
	}

    public function cuenta_debito()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_debito");
    }

    public function cuenta_credito()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_credito");
    }
    
}
