<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExogenaFormato extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = 'exogena_formatos';

    public $fillable = [
        'formato',
        'tipo_documento',
        'numero_documento',
        'digito_verificacion',
        'primer_apellido',
        'segundo_apellido',
        'primer_nombre',
        'otros_nombres',
        'razon_social',
        'direccion',
        'departamento',
        'municipio',
        'pais',
        'created_by',
        'updated_by',
    ];

    public function columnas()
    {
        return $this->hasMany(ExogenaFormatoColumna::class, 'id_exogena_formato', 'id');
    }

    public function conceptos()
    {
        return $this->hasMany(ExogenaFormatoConcepto::class, 'id_exogena_formato', 'id');
    }
}
