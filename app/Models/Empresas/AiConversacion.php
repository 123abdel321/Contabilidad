<?php

namespace App\Models\Empresas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiConversacion extends Model
{
    use HasFactory;

    protected $connection = 'clientes';
    
    protected $table = 'ai_conversaciones';

    protected $fillable = [
        'session_id',
        'id_user',
        'id_empresa',
        'flujo',
        'contexto',
        'borrador',
        'candidatos',
        'historial',
        'resultado',
        'cerrada',
    ];

    protected $casts = [
        'contexto'   => 'array',
        'borrador'   => 'array',
        'candidatos' => 'array',
        'historial'  => 'array',
        'resultado'  => 'array',
        'cerrada'    => 'boolean',
    ];

    public function estaCerrada(): bool
    {
        return (bool) $this->cerrada;
    }

    /**
     * Devuelve un array compatible con la clase Estado.
     */
    public function toEstadoArray(): array
    {
        return [
            'contexto'   => $this->contexto   ?? [],
            'flujo'      => $this->flujo,
            'borrador'   => $this->borrador   ?? [],
            'candidatos' => $this->candidatos ?? [],
            'historial'  => $this->historial  ?? [],
            'resultado'  => $this->resultado  ?? null,
        ];
    }
}