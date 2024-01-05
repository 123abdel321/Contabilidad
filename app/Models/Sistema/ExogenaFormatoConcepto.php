<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExogenaFormatoConcepto extends Model
{
    use HasFactory;
    
    protected $connection = 'sam';

    protected $table = 'exogena_formato_conceptos';

    protected $fillable = [
        'id_exogena_formato',
        'concepto',
        'created_by',
        'updated_by',
    ];

    public function formato()
    {
        return $this->belongsTo(ExogenaFormato::class, 'id_exogena_formato', 'id');
    }

    public function cuentaExogenaFormato()
    {
        return $this->hasMany(ExogenaFormato::class, 'id_exogena_formato', 'id');
    }
}
