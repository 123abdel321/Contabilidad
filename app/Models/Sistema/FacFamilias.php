<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacFamilias extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = "fac_familias";

    protected $fillable = [
        'codigo',
        'nombre',
        'inventario',
        'id_cuenta_venta',
        'id_cuenta_venta_retencion',
        'id_cuenta_venta_devolucion',
        'id_cuenta_venta_iva',
        'id_cuenta_venta_descuento',
        'id_cuenta_venta_devolucion_iva',
        'id_cuenta_compra',
        'id_cuenta_compra_retencion',
        'id_cuenta_compra_devolucion',
        'id_cuenta_compra_iva',
        'id_cuenta_compra_descuento',
        'id_cuenta_compra_devolucion_iva',
        'id_cuenta_inventario',
        'id_cuenta_costos',
        'created_by',
        'updated_by'
    ];

    public function cuenta_venta()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_venta");
    }

    public function cuenta_venta_retencion()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_venta_retencion");
    }

    public function cuenta_venta_devolucion()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_venta_devolucion");
    }

    public function cuenta_venta_iva()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_venta_iva");
    }

    public function cuenta_venta_descuento()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_venta_descuento");
    }
    
    public function cuenta_venta_devolucion_iva()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_venta_devolucion_iva");
    }

    public function cuenta_compra()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_compra");
    }

    public function cuenta_compra_retencion()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_compra_retencion");
    }

    public function cuenta_compra_devolucion()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_compra_devolucion");
    }

    public function cuenta_compra_iva()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_compra_iva");
    }

    public function cuenta_compra_descuento()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_compra_descuento");
    }
    
    public function cuenta_compra_devolucion_iva()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_compra_devolucion_iva");
    }

    public function cuenta_inventario()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_inventario");
    }
    
    public function cuenta_costos()
    {
        return $this->belongsTo("App\Models\Sistema\PlanCuentas", "id_cuenta_costos");
    }

}
