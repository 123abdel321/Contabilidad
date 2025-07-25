<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConGastos extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "con_gastos";

    protected $fillable = [
        'id_proveedor',
        'id_comprobante',
        'id_centro_costos',
        'fecha_manual',
        'consecutivo',
        'documento_referencia',
        'subtotal',
        'total_iva',
        'total_descuento',
        'total_rete_fuente',
        'id_cuenta_rete_fuente',
        'porcentaje_rete_fuente',
        'total_gasto',
        'created_by',
        'updated_by'
    ];

    public function nit()
    {
        return $this->belongsTo(Nits::class, 'id_proveedor');
	}

    public function documentos()
    {
        return $this->morphMany(DocumentosGeneral::class, 'relation');
	}

    public function proveedor()
    {
        return $this->belongsTo(Nits::class, 'id_proveedor');
	}

    public function comprobante()
	{
		return $this->belongsTo(Comprobantes::class, 'id_comprobante');
	}

    public function cecos()
	{
		return $this->belongsTo(CentroCostos::class, 'id_centro_costos');
	}

    public function detalles()
	{
		return $this->hasMany(ConGastoDetalles::class, 'id_gasto');
	}

    public function pagos()
	{
		return $this->hasMany(ConGastoPagos::class, 'id_gasto');
	}
}
