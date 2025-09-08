<?php

namespace App\Models\Sistema;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReunionParticipante extends Model
{
    use HasFactory;

    protected $connection = 'sam';
    
    protected $table = 'reunion_participantes';
    
    protected $fillable = [
        'id_reunion',
        'id_usuario',
        'asistio',
        'comentarios'
    ];
    
    public function nit()
    {
		return $this->belongsTo(Nits::class, 'id_usuario', 'id');
	}
    
    public function reunion()
    {
        return $this->belongsTo(Reunion::class, 'id_reunion');
    }
}
