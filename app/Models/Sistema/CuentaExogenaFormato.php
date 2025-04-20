<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaExogenaFormato extends Model
{
    use HasFactory;
    
    protected $connection = 'sam';

    protected $table = 'con_cuenta_exogena_formatos';

    protected $fillable = [
        'id_cuenta',
        'id_exogena_formato',
        'id_exogena_formato_concepto',
        'id_exogena_formato_columna',
        'created_by',
        'updated_by',
    ];

    public function cuenta()
    {
        return $this->belongsTo(ConCuenta::class, 'id_cuenta', 'id');
    }

    public function formato()
    {
        return $this->belongsTo(ConExogenaFormato::class, 'id_exogena_formato', 'id');
    }

    public function concepto()
    {
        return $this->belongsTo(ConExogenaFormatoConcepto::class, 'id_exogena_formato_concepto', 'id');
    }

    public function columna()
    {
        return $this->belongsTo(ConExogenaFormatoColumna::class, 'id_exogena_formato_columna', 'id');
    }
}
