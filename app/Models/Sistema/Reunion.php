<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reunion extends Model
{
    use HasFactory;

    protected $connection = 'sam';

    protected $table = 'reunions';
    
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'lugar',
        'id_proyecto',
        'estado',
        'created_by',
        'updated_by'
    ];
    
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];
    
    public function participantes()
    {
        return $this->belongsToMany(
            Nits::class, 
            'reunion_participantes', 
            'id_reunion', 
            'id_usuario'
        )->withPivot('asistio', 'comentarios');
    }
}
