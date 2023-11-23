<?php

namespace App\Models\Sistema;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\FacturaElectronica\CodigoDocumentoDianTypes;

class FacVentaDetalles extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_venta_detalles";

    protected $fillable = [
        'id_venta',
        'id_producto',
        'id_cuenta_venta',
        'id_cuenta_venta_retencion',
        'id_cuenta_venta_iva',
        'id_cuenta_venta_descuento',
        'descripcion',
        'cantidad',
        'costo',
        'subtotal',
        'descuento_porcentaje',
        'descuento_valor',
        'iva_porcentaje',
        'iva_valor',
        'total',
        'created_by',
        'updated_by',
    ];

    public function producto()
	{
		return $this->belongsTo(FacProductos::class, 'id_producto', 'id');
	}

    public function scopeWithValoresDevueltos($query) {
		return $query->select([
			'fac_venta_detalles.*',
			DB::raw('COALESCE(SUM(notas_credito_detalle.cantidad), 0) as cantidad_devuelta'),
			DB::raw('COALESCE(SUM(notas_credito_detalle.total), 0) as total_devuelto')
		])
			->leftJoin('fac_ventas AS venta', 'fac_venta_detalles.id_venta', 'venta.id')
			->leftJoin('fac_ventas AS devolucion', function ($join) {
				$join->on('devolucion.id_factura', 'venta.id')
					->where('devolucion.codigo_tipo_documento_dian', CodigoDocumentoDianTypes::NOTA_CREDITO);
			})
			->leftJoin('fac_venta_detalles AS notas_credito_detalle', 'notas_credito_detalle.id_venta', 'devolucion.id')
			->groupBy('fac_venta_detalles.id');
	}
}
