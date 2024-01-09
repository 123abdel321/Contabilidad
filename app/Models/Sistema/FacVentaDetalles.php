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
        'id_venta_detalle',
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

    public function venta()
	{
		return $this->belongsTo(FacVentas::class, 'id_venta');
	}

    public function producto()
	{
		return $this->belongsTo(FacProductos::class, 'id_producto', 'id');
	}

    public function cuenta_iva()
	{
		return $this->belongsTo(PlanCuentas::class, 'id_cuenta_venta_iva', 'id');
	}
}
