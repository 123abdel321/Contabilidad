<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExogenaFormatoColumna extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = 'exogena_formato_columnas';

    protected $fillable = [
        'id_exogena_formato',
        'id_tipo_concepto_nomina',
        'id_columna_porcentaje_base',
        'columna',
        'nombre',
        'acumulado',
        'saldo',
        'naturaleza',
        'created_by',
        'updated_by',
    ];

    public function exogenaFormato()
    {
        return $this->belongsTo(ExogenaFormato::class, 'id_exogena_formato', 'id');
    }

    public function conceptos()
    {
        return $this->belongsTo(NomConceptos::class, 'id_tipo_concepto_nomina', 'tipo_concepto');
    }

    public function columnaPorcentajeBase()
    {
        return $this->belongsTo(ExogenaFormatoColumna::class, 'id_columna_porcentaje_base', 'id');
    }

    public function columnasPorcentajeBase()
    {
        return $this->hasMany(ExogenaFormatoColumna::class, 'id_columna_porcentaje_base', 'id');
    }
}
