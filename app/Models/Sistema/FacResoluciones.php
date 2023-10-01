<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacResoluciones extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_resoluciones";

    public const TIPO_RESOLUCION = [
		'POS',
		'facturación electrónica',
		'contingencia',
		'nota débito',
		'nota crédito',
		'documento equivalente/soporte',
	];

    const TIPO_POS = 0;
	const TIPO_FACTURA_ELECTRONICA = 1;
	const TIPO_CONTINGENCIA = 2;
	const TIPO_NOTA_DEBITO = 3;
	const TIPO_NOTA_CREDITO = 4;
	const TIPO_DOCUEMNTO_EQUIVALENTE = 5;

    protected $fillable = [
        'id_comprobante',
        'nombre',
        'prefijo',
        'consecutivo',
        'numero_resolucion',
        'tipo_impresion',
        'tipo_resolucion',
        'fecha',
        'vigencia',
        'consecutivo_desde',
        'consecutivo_hasta',
        'created_by',
        'updated_by',
    ];

    public function comprobante()
	{
		return $this->belongsTo('App\Models\Sistema\Comprobantes', 'id_comprobante');
	}

	public function scopeActive($query)
	{
		return $query->whereRaw('consecutivo BETWEEN consecutivo_desde AND consecutivo_hasta')
			->where('fecha', '<=', date('Y-m-d'))
			->whereRaw('? < DATE_ADD(fecha, INTERVAL vigencia MONTH)', [date('Y-m-d')]);
	}
    
}
